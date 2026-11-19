<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\CashCategory;
use App\Models\CashTransaction;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase1SecurityTest extends TestCase
{
    use RefreshDatabase;

    private AcademicYear $academicYear;

    private SchoolClass $classA;

    private SchoolClass $classB;

    private User $admin;

    private User $bendaharaA;

    private User $bendaharaB;

    private User $siswaA;

    private CashCategory $catIncome;

    private CashCategory $catExpense;

    protected function setUp(): void
    {
        parent::setUp();

        $this->academicYear = AcademicYear::create([
            'year' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);

        $this->classA = SchoolClass::create([
            'class_name' => 'X IPA 1',
            'academic_year_id' => $this->academicYear->id,
            'academic_year' => '2026/2027',
            'homeroom_teacher' => 'Wali Kelas 1',
        ]);

        $this->classB = SchoolClass::create([
            'class_name' => 'X IPA 2',
            'academic_year_id' => $this->academicYear->id,
            'academic_year' => '2026/2027',
            'homeroom_teacher' => 'Wali Kelas 2',
        ]);

        $this->admin = User::create([
            'name' => 'Admin Sekolah',
            'email' => 'admin@sekolah.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->bendaharaA = User::create([
            'name' => 'Bendahara A',
            'email' => 'bendahara.a@sekolah.test',
            'password' => bcrypt('password'),
            'role' => 'bendahara_kelas',
            'class_id' => $this->classA->id,
        ]);

        $this->bendaharaB = User::create([
            'name' => 'Bendahara B',
            'email' => 'bendahara.b@sekolah.test',
            'password' => bcrypt('password'),
            'role' => 'bendahara_kelas',
            'class_id' => $this->classB->id,
        ]);

        $this->siswaA = User::create([
            'name' => 'Siswa A',
            'email' => 'siswa.a@siswa.test',
            'nisn' => '0012345001',
            'password' => bcrypt('password'),
            'role' => 'siswa',
            'class_id' => $this->classA->id,
        ]);

        $this->catIncome = CashCategory::create([
            'name' => 'Kas Mingguan',
            'type' => 'in',
        ]);

        $this->catExpense = CashCategory::create([
            'name' => 'ATK dan Kebersihan',
            'type' => 'out',
        ]);
    }

    public function test_transaction_proof_stores_privately_and_protects_access(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        // Create transaction with proof via store endpoint
        $file = UploadedFile::fake()->image('nota.png');
        $response = $this->actingAs($this->bendaharaA)->post('/admin/transactions', [
            'type' => 'in',
            'category_id' => $this->catIncome->id,
            'title' => 'Iuran Awal Kas',
            'amount' => 50000,
            'transaction_date' => now()->toDateString(),
            'proof_file' => $file,
        ]);

        $response->assertRedirect('/admin/transactions');
        $trx = CashTransaction::where('title', 'Iuran Awal Kas')->firstOrFail();

        // 1. Verify file is in local (private) disk and NOT in public disk
        $this->assertNotNull($trx->proof_file);
        Storage::disk('local')->assertExists($trx->proof_file);
        Storage::disk('public')->assertMissing($trx->proof_file);

        // 2. Guest user cannot access proof
        auth()->logout();
        $this->get(route('transactions.proof', $trx))
            ->assertRedirect('/login');

        // 3. Bendahara of another class gets 403 Forbidden
        $this->actingAs($this->bendaharaB)
            ->get(route('transactions.proof', $trx))
            ->assertForbidden();

        // 4. Bendahara of the same class gets 200 OK with correct security headers
        $this->actingAs($this->bendaharaA)
            ->get(route('transactions.proof', $trx))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Cache-Control', 'no-store, private');

        // 5. Admin gets 200 OK
        $this->actingAs($this->admin)
            ->get(route('transactions.proof', $trx))
            ->assertOk();
    }

    public function test_transaction_proof_pdf_is_downloaded_as_attachment(): void
    {
        Storage::fake('local');

        $trx = CashTransaction::create([
            'transaction_number' => 'TRX-IN-PDF-001',
            'class_id' => $this->classA->id,
            'category_id' => $this->catIncome->id,
            'type' => 'in',
            'title' => 'Nota PDF',
            'amount' => 20000,
            'transaction_date' => now()->toDateString(),
            'proof_file' => 'transactions/nota.pdf',
            'created_by' => $this->admin->id,
        ]);

        Storage::disk('local')->put($trx->proof_file, "%PDF-1.4\nnota content");

        $response = $this->actingAs($this->bendaharaA)->get(route('transactions.proof', $trx));
        $response->assertOk();
        $this->assertStringStartsWith('attachment;', $response->headers->get('Content-Disposition'));
    }

    public function test_transaction_proof_returns_404_when_missing(): void
    {
        $trx = CashTransaction::create([
            'transaction_number' => 'TRX-IN-NOFILE-001',
            'class_id' => $this->classA->id,
            'category_id' => $this->catIncome->id,
            'type' => 'in',
            'title' => 'Tanpa Bukti',
            'amount' => 10000,
            'transaction_date' => now()->toDateString(),
            'proof_file' => null,
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->bendaharaA)
            ->get(route('transactions.proof', $trx))
            ->assertNotFound();
    }

    public function test_transaction_update_removes_old_proof_file_and_stores_new_in_local(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $oldFile = 'transactions/old-nota.png';
        Storage::disk('local')->put($oldFile, 'old content');

        $trx = CashTransaction::create([
            'transaction_number' => 'TRX-IN-UPD-001',
            'class_id' => $this->classA->id,
            'category_id' => $this->catIncome->id,
            'type' => 'in',
            'title' => 'Pemasukan Kas',
            'amount' => 30000,
            'transaction_date' => now()->toDateString(),
            'proof_file' => $oldFile,
            'created_by' => $this->admin->id,
        ]);

        $newFile = UploadedFile::fake()->image('new-nota.png');

        $response = $this->actingAs($this->bendaharaA)->put("/admin/transactions/{$trx->id}", [
            'type' => 'in',
            'category_id' => $this->catIncome->id,
            'title' => 'Pemasukan Kas (Updated)',
            'amount' => 30000,
            'transaction_date' => now()->toDateString(),
            'proof_file' => $newFile,
        ]);

        $response->assertRedirect('/admin/transactions');
        Storage::disk('local')->assertMissing($oldFile);

        $freshTrx = $trx->fresh();
        $this->assertNotSame($oldFile, $freshTrx->proof_file);
        Storage::disk('local')->assertExists($freshTrx->proof_file);
    }

    public function test_secure_transaction_proofs_artisan_command_moves_files(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $trx = CashTransaction::create([
            'transaction_number' => 'TRX-LEGACY-001',
            'class_id' => $this->classA->id,
            'category_id' => $this->catIncome->id,
            'type' => 'in',
            'title' => 'Legacy Trx',
            'amount' => 15000,
            'transaction_date' => now()->toDateString(),
            'proof_file' => 'transactions/legacy-nota.jpg',
            'created_by' => $this->admin->id,
        ]);

        Storage::disk('public')->put($trx->proof_file, 'legacy nota content');
        Storage::disk('public')->put('transactions/orphan-nota.jpg', 'orphan nota content');

        $this->artisan('transactions:secure-proofs --dry-run')->assertExitCode(0);
        Storage::disk('public')->assertExists($trx->proof_file);
        Storage::disk('local')->assertMissing($trx->proof_file);

        $this->artisan('transactions:secure-proofs')->assertExitCode(0);
        Storage::disk('public')->assertMissing($trx->proof_file);
        Storage::disk('public')->assertMissing('transactions/orphan-nota.jpg');
        Storage::disk('local')->assertExists($trx->proof_file);
        Storage::disk('local')->assertExists('transactions/orphan-nota.jpg');
        $this->assertSame('legacy nota content', Storage::disk('local')->get($trx->proof_file));
    }

    public function test_consecutive_expenses_prevent_negative_balance_with_validation_exception(): void
    {
        // 1. Initial income of 50.000 for Class A
        CashTransaction::create([
            'transaction_number' => 'TRX-IN-RACE-001',
            'class_id' => $this->classA->id,
            'category_id' => $this->catIncome->id,
            'type' => 'in',
            'title' => 'Pemasukan Kas Kelas',
            'amount' => 50000,
            'transaction_date' => now()->toDateString(),
            'created_by' => $this->admin->id,
        ]);

        // 2. First expense of 35.000 (leaves 15.000 available)
        $res1 = $this->actingAs($this->bendaharaA)->post('/admin/transactions', [
            'type' => 'out',
            'category_id' => $this->catExpense->id,
            'title' => 'Pengeluaran Pertama',
            'amount' => 35000,
            'transaction_date' => now()->toDateString(),
        ]);
        $res1->assertRedirect('/admin/transactions');
        $this->assertDatabaseHas('cash_transactions', ['title' => 'Pengeluaran Pertama']);

        // 3. Second expense of 20.000 (exceeds remaining 15.000)
        $res2 = $this->actingAs($this->bendaharaA)->post('/admin/transactions', [
            'type' => 'out',
            'category_id' => $this->catExpense->id,
            'title' => 'Pengeluaran Kedua',
            'amount' => 20000,
            'transaction_date' => now()->toDateString(),
        ]);

        $res2->assertSessionHasErrors(['amount']);
        $this->assertDatabaseMissing('cash_transactions', ['title' => 'Pengeluaran Kedua']);

        // 4. Verify total expense did not exceed income
        $totalExpense = (float) CashTransaction::expense()->where('class_id', $this->classA->id)->sum('amount');
        $this->assertSame(35000.0, $totalExpense);
    }

    public function test_imported_student_must_change_password_before_accessing_dashboard(): void
    {
        $csvContent = "Nama Siswa,NISN,Email\nBudi Santoso,0099887766,budi@siswa.test";
        $csvFile = UploadedFile::fake()->createWithContent('budi.csv', $csvContent);

        $importRes = $this->actingAs($this->admin)->post('/admin/students/import', [
            'class_id' => $this->classA->id,
            'file' => $csvFile,
        ]);
        $importRes->assertRedirect('/admin/students');

        $student = User::where('nisn', '0099887766')->firstOrFail();
        $this->assertTrue($student->must_change_password);

        // Student tries to access dashboard
        $dashRes = $this->actingAs($student)->get('/siswa/dashboard');
        $dashRes->assertRedirect(route('password.change'));

        // Student changes password
        $changeRes = $this->actingAs($student)->put('/change-password', [
            'current_password' => 'password',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $changeRes->assertRedirect('/siswa/dashboard');
        $this->assertFalse($student->fresh()->must_change_password);
        $this->assertTrue(Hash::check('newpassword123', $student->fresh()->password));

        // Student can now access dashboard without redirect
        $this->actingAs($student->fresh())->get('/siswa/dashboard')->assertOk();
    }

    public function test_admin_reset_password_sets_must_change_password_flag(): void
    {
        $this->siswaA->update(['must_change_password' => false]);

        $response = $this->actingAs($this->admin)->post("/admin/students/{$this->siswaA->id}/reset-password");
        $response->assertSessionHas('success');

        $flash = (string) session('success');
        preg_match('/Password sementara untuk [^:]+: ([a-zA-Z0-9]+)\./', $flash, $matches);
        $tempPassword = $matches[1] ?? '';

        $freshStudent = $this->siswaA->fresh();
        $this->assertTrue($freshStudent->must_change_password);
        $this->assertNotEmpty($tempPassword);
        $this->assertTrue(Hash::check($tempPassword, $freshStudent->password));

        // When logging in, student is blocked from accessing dashboard
        $this->actingAs($freshStudent)->get('/siswa/dashboard')
            ->assertRedirect(route('password.change'));
    }
}
