<?php

namespace Tests\Feature;

use App\Helpers\SpreadsheetHelper;
use App\Models\AcademicYear;
use App\Models\CashBill;
use App\Models\CashCategory;
use App\Models\CashTransaction;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase2IntegrityTest extends TestCase
{
    use RefreshDatabase;

    private AcademicYear $academicYear;

    private SchoolClass $classA;

    private SchoolClass $classB;

    private User $admin;

    private User $bendaharaA;

    private User $bendaharaB;

    private User $siswaA;

    private User $siswaB;

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
            'password' => 'password',
            'role' => 'admin',
        ]);

        $this->bendaharaA = User::create([
            'name' => 'Bendahara A',
            'email' => 'bendahara.a@sekolah.test',
            'password' => 'password',
            'role' => 'bendahara_kelas',
            'class_id' => $this->classA->id,
        ]);

        $this->bendaharaB = User::create([
            'name' => 'Bendahara B',
            'email' => 'bendahara.b@sekolah.test',
            'password' => 'password',
            'role' => 'bendahara_kelas',
            'class_id' => $this->classB->id,
        ]);

        $this->siswaA = User::create([
            'name' => 'Siswa A',
            'email' => 'siswa.a@siswa.test',
            'nisn' => '0012345001',
            'password' => 'password',
            'role' => 'siswa',
            'class_id' => $this->classA->id,
        ]);

        $this->siswaB = User::create([
            'name' => 'Siswa B',
            'email' => 'siswa.b@siswa.test',
            'nisn' => '0012345002',
            'password' => 'password',
            'role' => 'siswa',
            'class_id' => $this->classB->id,
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

    public function test_void_transaction_updates_balance_and_retains_history(): void
    {
        // 1. Pemasukan 100.000
        CashTransaction::create([
            'transaction_number' => 'TRX-IN-VOID-001',
            'class_id' => $this->classA->id,
            'category_id' => $this->catIncome->id,
            'type' => 'in',
            'title' => 'Pemasukan Awal',
            'amount' => 100000,
            'transaction_date' => now()->toDateString(),
            'created_by' => $this->admin->id,
        ]);

        // 2. Pengeluaran 30.000
        $expense = CashTransaction::create([
            'transaction_number' => 'TRX-OUT-VOID-001',
            'class_id' => $this->classA->id,
            'category_id' => $this->catExpense->id,
            'type' => 'out',
            'title' => 'Beli Spidol Salah Input',
            'amount' => 30000,
            'transaction_date' => now()->toDateString(),
            'created_by' => $this->bendaharaA->id,
        ]);

        // Sisa saldo sebelum void = 70.000
        $this->assertSame(70000.0, (float) CashTransaction::income()->sum('amount') - (float) CashTransaction::expense()->sum('amount'));

        // 3. Void pengeluaran dengan alasan
        $response = $this->actingAs($this->bendaharaA)->delete("/admin/transactions/{$expense->id}", [
            'void_reason' => 'Salah nominal, dibatalkan untuk dibuat ulang',
        ]);

        $response->assertRedirect('/admin/transactions');
        $response->assertSessionHas('success');

        // 4. Verifikasi baris tetap ada (tidak hard delete)
        $freshExpense = $expense->fresh();
        $this->assertNotNull($freshExpense);
        $this->assertTrue($freshExpense->isVoided());
        $this->assertSame('Salah nominal, dibatalkan untuk dibuat ulang', $freshExpense->void_reason);
        $this->assertSame($this->bendaharaA->id, $freshExpense->voided_by);

        // 5. Saldo kas kembali menjadi 100.000 (pengeluaran void diabaikan)
        $balanceAfterVoid = (float) CashTransaction::income()->where('class_id', $this->classA->id)->sum('amount')
            - (float) CashTransaction::expense()->where('class_id', $this->classA->id)->sum('amount');
        $this->assertSame(100000.0, $balanceAfterVoid);
    }

    public function test_void_income_prevented_if_spent_funds_would_make_balance_negative(): void
    {
        $income = CashTransaction::create([
            'transaction_number' => 'TRX-IN-VOID-002',
            'class_id' => $this->classA->id,
            'category_id' => $this->catIncome->id,
            'type' => 'in',
            'title' => 'Pemasukan Kas',
            'amount' => 50000,
            'transaction_date' => now()->toDateString(),
            'created_by' => $this->admin->id,
        ]);

        CashTransaction::create([
            'transaction_number' => 'TRX-OUT-VOID-002',
            'class_id' => $this->classA->id,
            'category_id' => $this->catExpense->id,
            'type' => 'out',
            'title' => 'Pengeluaran Kas',
            'amount' => 40000,
            'transaction_date' => now()->toDateString(),
            'created_by' => $this->bendaharaA->id,
        ]);

        // Attempting to void the income of 50.000 when remaining balance is only 10.000
        $response = $this->actingAs($this->bendaharaA)->delete("/admin/transactions/{$income->id}", [
            'void_reason' => 'Ingin dibatalkan',
        ]);

        $response->assertSessionHas('error');
        $this->assertFalse($income->fresh()->isVoided());
    }

    public function test_void_requires_reason(): void
    {
        $expense = CashTransaction::create([
            'transaction_number' => 'TRX-OUT-VOID-003',
            'class_id' => $this->classA->id,
            'category_id' => $this->catExpense->id,
            'type' => 'out',
            'title' => 'Beli Lakban',
            'amount' => 10000,
            'transaction_date' => now()->toDateString(),
            'created_by' => $this->bendaharaA->id,
        ]);

        $response = $this->actingAs($this->bendaharaA)->delete("/admin/transactions/{$expense->id}", [
            'void_reason' => '',
        ]);

        $response->assertSessionHasErrors('void_reason');
        $this->assertFalse($expense->fresh()->isVoided());
    }

    public function test_double_void_and_editing_voided_transaction_rejected(): void
    {
        $expense = CashTransaction::create([
            'transaction_number' => 'TRX-OUT-VOID-004',
            'class_id' => $this->classA->id,
            'category_id' => $this->catExpense->id,
            'type' => 'out',
            'title' => 'Pengeluaran Di-Void',
            'amount' => 15000,
            'transaction_date' => now()->toDateString(),
            'voided_at' => now(),
            'voided_by' => $this->admin->id,
            'void_reason' => 'Salah input',
            'created_by' => $this->admin->id,
        ]);

        // 1. Double void rejected
        $resVoid = $this->actingAs($this->admin)->delete("/admin/transactions/{$expense->id}", [
            'void_reason' => 'Void ulang',
        ]);
        $resVoid->assertSessionHas('error');

        // 2. Edit voided transaction rejected
        $resEdit = $this->actingAs($this->admin)->get("/admin/transactions/{$expense->id}/edit");
        $resEdit->assertRedirect("/admin/transactions/{$expense->id}");
        $resEdit->assertSessionHas('error');

        // 3. Update voided transaction rejected
        $resUpdate = $this->actingAs($this->admin)->put("/admin/transactions/{$expense->id}", [
            'type' => 'out',
            'category_id' => $this->catExpense->id,
            'title' => 'Coba ubah',
            'amount' => 15000,
            'transaction_date' => now()->toDateString(),
        ]);
        $resUpdate->assertRedirect("/admin/transactions/{$expense->id}");
        $resUpdate->assertSessionHas('error');
    }

    public function test_bendahara_authorization_scoping_across_transactions_bills_payments(): void
    {
        $billB = CashBill::create([
            'class_id' => $this->classB->id,
            'category_id' => $this->catIncome->id,
            'title' => 'Iuran Kelas B',
            'amount' => 25000,
            'due_date' => now()->addDays(7)->toDateString(),
        ]);

        $paymentB = Payment::create([
            'receipt_number' => 'KWT-CLS-B-001',
            'cash_bill_id' => $billB->id,
            'student_id' => $this->siswaB->id,
            'amount_paid' => 25000,
            'payment_date' => now()->toDateString(),
            'status' => 'pending',
            'payment_method' => 'transfer',
        ]);

        $trxB = CashTransaction::create([
            'transaction_number' => 'TRX-IN-CLS-B-001',
            'class_id' => $this->classB->id,
            'category_id' => $this->catIncome->id,
            'type' => 'in',
            'title' => 'Pemasukan Kas Kelas B',
            'amount' => 50000,
            'transaction_date' => now()->toDateString(),
            'created_by' => $this->bendaharaB->id,
        ]);

        // Bendahara A cannot view, update, delete transaction of Class B
        $this->actingAs($this->bendaharaA)->get("/admin/transactions/{$trxB->id}")->assertForbidden();
        $this->actingAs($this->bendaharaA)->get("/admin/transactions/{$trxB->id}/edit")->assertForbidden();
        $this->actingAs($this->bendaharaA)->delete("/admin/transactions/{$trxB->id}", ['void_reason' => 'Test'])->assertForbidden();

        // Bendahara A cannot view, update, delete bill of Class B
        $this->actingAs($this->bendaharaA)->get("/admin/dues/{$billB->id}")->assertForbidden();
        $this->actingAs($this->bendaharaA)->get("/admin/dues/{$billB->id}/edit")->assertForbidden();
        $this->actingAs($this->bendaharaA)->delete("/admin/dues/{$billB->id}")->assertForbidden();

        // Bendahara A cannot verify, reject, view receipt of payment of Class B
        $this->actingAs($this->bendaharaA)->post("/admin/payments/{$paymentB->id}/verify")->assertForbidden();
        $this->actingAs($this->bendaharaA)->post("/admin/payments/{$paymentB->id}/reject")->assertForbidden();
        $this->actingAs($this->bendaharaA)->get("/admin/payments/{$paymentB->id}/receipt")->assertForbidden();
        $this->actingAs($this->bendaharaA)->get("/admin/payments/{$paymentB->id}/receipt-pdf")->assertForbidden();
    }

    public function test_dead_code_models_are_removed(): void
    {
        $this->assertFileDoesNotExist(app_path('Models/CashExpense.php'));
        $this->assertFileDoesNotExist(app_path('Models/StudentDue.php'));
        $this->assertFileDoesNotExist(app_path('Models/DuesPayment.php'));
        $this->assertFileDoesNotExist(app_path('Models/LogActivity.php'));
        $this->assertFalse(class_exists('App\Models\CashExpense', false));
        $this->assertFalse(class_exists('App\Models\StudentDue', false));
        $this->assertFalse(class_exists('App\Models\DuesPayment', false));
        $this->assertFalse(class_exists('App\Models\LogActivity', false));
    }

    public function test_spreadsheet_helper_sanitizes_formula_injection(): void
    {
        $this->assertSame("'=1+1", SpreadsheetHelper::sanitize('=1+1'));
        $this->assertSame("'+cmd", SpreadsheetHelper::sanitize('+cmd'));
        $this->assertSame("'-calc", SpreadsheetHelper::sanitize('-calc'));
        $this->assertSame("'@sum(A1:A5)", SpreadsheetHelper::sanitize('@sum(A1:A5)'));
        $this->assertSame('Andi Saputra', SpreadsheetHelper::sanitize('Andi Saputra'));
    }

    public function test_student_deletion_blocked_if_student_has_payments(): void
    {
        $bill = CashBill::create([
            'class_id' => $this->classA->id,
            'category_id' => $this->catIncome->id,
            'title' => 'Tagihan Uji Hapus Siswa',
            'amount' => 10000,
            'due_date' => now()->toDateString(),
        ]);

        $payment = Payment::create([
            'receipt_number' => 'KWT-DEL-STU-001',
            'cash_bill_id' => $bill->id,
            'student_id' => $this->siswaA->id,
            'amount_paid' => 10000,
            'payment_date' => now()->toDateString(),
            'status' => 'verified',
            'payment_method' => 'cash',
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/students/{$this->siswaA->id}");

        $response->assertSessionHas('error');
        $this->assertModelExists($this->siswaA);
        $this->assertModelExists($payment);
    }

    public function test_class_deletion_blocked_if_class_has_bills_or_transactions(): void
    {
        $emptyClass = SchoolClass::create([
            'class_name' => 'XI IPS Kosong',
            'academic_year_id' => $this->academicYear->id,
            'academic_year' => '2026/2027',
            'homeroom_teacher' => 'Wali Kelas Kosong',
        ]);

        CashBill::create([
            'class_id' => $emptyClass->id,
            'category_id' => $this->catIncome->id,
            'title' => 'Tagihan Kelas Kosong',
            'amount' => 15000,
            'due_date' => now()->toDateString(),
        ]);

        $res = $this->actingAs($this->admin)->delete("/admin/classes/{$emptyClass->id}");
        $res->assertSessionHas('error');
        $this->assertModelExists($emptyClass);
    }

    public function test_void_transaction_preserves_proof_file_on_disk(): void
    {
        Storage::fake('local');
        $fakeFile = UploadedFile::fake()->image('bukti_nota.jpg');
        $proofPath = $fakeFile->store('transactions', 'local');

        $expense = CashTransaction::create([
            'transaction_number' => 'TRX-OUT-VOID-PROOF-01',
            'class_id' => $this->classA->id,
            'category_id' => $this->catExpense->id,
            'type' => 'out',
            'title' => 'Beli Alat Kebersihan',
            'amount' => 10000,
            'transaction_date' => now()->toDateString(),
            'proof_file' => $proofPath,
            'created_by' => $this->bendaharaA->id,
        ]);

        Storage::disk('local')->assertExists($proofPath);

        $res = $this->actingAs($this->bendaharaA)->delete("/admin/transactions/{$expense->id}", [
            'void_reason' => 'Nota direvisi oleh toko',
        ]);

        $res->assertSessionHas('success');

        $freshExpense = $expense->fresh();
        $this->assertTrue($freshExpense->isVoided());
        $this->assertSame($proofPath, $freshExpense->proof_file);
        Storage::disk('local')->assertExists($proofPath);
    }

    public function test_student_endpoints_abort_404_for_non_student_users(): void
    {
        // Admin or Bendahara user IDs should be 404 when targeted via /admin/students endpoints
        $this->actingAs($this->admin)->get("/admin/students/{$this->admin->id}/edit")->assertNotFound();
        $this->actingAs($this->admin)->put("/admin/students/{$this->admin->id}", [
            'name' => 'Admin Baru',
            'email' => 'admin.baru@sekolah.test',
            'nisn' => '9999999999',
            'class_id' => $this->classA->id,
        ])->assertNotFound();
        $this->actingAs($this->admin)->post("/admin/students/{$this->admin->id}/reset-password")->assertNotFound();
        $this->actingAs($this->admin)->delete("/admin/students/{$this->admin->id}")->assertNotFound();
    }

    public function test_admin_cannot_record_payment_for_student_of_different_class(): void
    {
        $billA = CashBill::create([
            'class_id' => $this->classA->id,
            'category_id' => $this->catIncome->id,
            'title' => 'Tagihan Kelas A',
            'amount' => 30000,
            'due_date' => now()->toDateString(),
        ]);

        // Attempting to record payment on Class A's bill for Siswa B (who belongs to Class B)
        $response = $this->actingAs($this->admin)->post('/admin/payments', [
            'cash_bill_id' => $billA->id,
            'student_id' => $this->siswaB->id,
            'amount_paid' => 30000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('payments', [
            'cash_bill_id' => $billA->id,
            'student_id' => $this->siswaB->id,
        ]);
    }
}
