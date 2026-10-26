<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\CashBill;
use App\Models\CashCategory;
use App\Models\CashTransaction;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class CashSecurityAndScopingTest extends TestCase
{
    use RefreshDatabase;

    private AcademicYear $academicYear;

    private SchoolClass $classA;

    private SchoolClass $classB;

    private User $admin;

    private User $bendaharaA;

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

    public function test_student_can_login_using_nisn(): void
    {
        $response = $this->post('/login', [
            'login' => '0012345001',
            'password' => 'password',
        ]);

        $response->assertRedirect('/siswa/dashboard');
        $this->assertAuthenticatedAs($this->siswaA);
    }

    public function test_bendahara_cannot_access_academic_years_master(): void
    {
        $response = $this->actingAs($this->bendaharaA)->get('/admin/academic-years');
        $response->assertForbidden();
    }

    public function test_bendahara_cannot_access_categories_master(): void
    {
        $response = $this->actingAs($this->bendaharaA)->get('/admin/categories');
        $response->assertForbidden();
    }

    public function test_bendahara_cannot_access_other_class_transaction(): void
    {
        $trxClassB = CashTransaction::create([
            'transaction_number' => 'TRX-IN-TEST-001',
            'class_id' => $this->classB->id,
            'category_id' => $this->catIncome->id,
            'type' => 'in',
            'title' => 'Pemasukan Kas Kelas B',
            'amount' => 50000,
            'transaction_date' => now()->toDateString(),
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->bendaharaA)->get("/admin/transactions/{$trxClassB->id}");
        $response->assertForbidden();
    }

    public function test_cannot_delete_category_in_use_by_cash_transaction(): void
    {
        CashTransaction::create([
            'transaction_number' => 'TRX-IN-TEST-002',
            'class_id' => $this->classA->id,
            'category_id' => $this->catIncome->id,
            'type' => 'in',
            'title' => 'Iuran Kas',
            'amount' => 100000,
            'transaction_date' => now()->toDateString(),
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/categories/{$this->catIncome->id}");
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('cash_categories', ['id' => $this->catIncome->id]);
    }

    public function test_expense_cannot_exceed_available_cash_balance(): void
    {
        // Class A starts with 0 cash balance, attempting to spend 20000
        $response = $this->actingAs($this->bendaharaA)->post('/admin/transactions', [
            'type' => 'out',
            'category_id' => $this->catExpense->id,
            'title' => 'Beli Spidol',
            'amount' => 20000,
            'transaction_date' => now()->toDateString(),
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('cash_transactions', [
            'title' => 'Beli Spidol',
        ]);
    }

    public function test_transaction_rejects_mismatching_category_type(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/transactions', [
            'type' => 'out',
            'category_id' => $this->catIncome->id, // income category for an expense
            'title' => 'Invalid Category Type',
            'amount' => 5000,
            'transaction_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('category_id');
    }

    public function test_bendahara_cannot_verify_payment_of_another_class(): void
    {
        $billB = CashBill::create([
            'class_id' => $this->classB->id,
            'category_id' => $this->catIncome->id,
            'title' => 'Tagihan Kas B',
            'amount' => 10000,
            'due_date' => now()->addDays(7)->toDateString(),
        ]);

        $siswaB = User::create([
            'name' => 'Siswa B',
            'email' => 'siswa.b@siswa.test',
            'nisn' => '0012345002',
            'password' => bcrypt('password'),
            'role' => 'siswa',
            'class_id' => $this->classB->id,
        ]);

        $paymentB = Payment::create([
            'cash_bill_id' => $billB->id,
            'student_id' => $siswaB->id,
            'amount_paid' => 10000,
            'payment_date' => now()->toDateString(),
            'status' => 'pending',
            'payment_method' => 'cash',
        ]);

        $response = $this->actingAs($this->bendaharaA)->post("/admin/payments/{$paymentB->id}/verify");
        $response->assertForbidden();
    }

    public function test_admin_can_download_student_template(): void
    {
        $responseXlsx = $this->actingAs($this->admin)->get('/admin/students/template');
        $responseXlsx->assertOk();
        $responseXlsx->assertHeader('content-disposition', 'attachment; filename=template_siswa.xlsx');

        $responseCsv = $this->actingAs($this->admin)->get('/admin/students/template?format=csv');
        $responseCsv->assertOk();
        $responseCsv->assertHeader('content-disposition', 'attachment; filename="template_siswa.csv"');
    }

    public function test_admin_can_import_students_via_excel_and_csv(): void
    {
        // 1. Test CSV Import
        $csvContent = "Nama Siswa,NISN,Email\nRizky Pratama,0012345099,rizky@siswa.test\nNabila Putri,0012345098,";
        $csvFile = UploadedFile::fake()->createWithContent('students.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post('/admin/students/import', [
            'class_id' => $this->classA->id,
            'file' => $csvFile,
        ]);

        $response->assertRedirect('/admin/students');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'Rizky Pratama',
            'nisn' => '0012345099',
            'email' => 'rizky@siswa.test',
            'class_id' => $this->classA->id,
        ]);

        // 2. Test Excel (.xlsx) Import
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Nama Siswa');
        $sheet->setCellValue('B1', 'NISN');
        $sheet->setCellValue('C1', 'Email');
        $sheet->setCellValue('A2', 'Excel Siswa');
        $sheet->setCellValue('B2', '0012345999');
        $sheet->setCellValue('C2', 'excel@siswa.test');

        $tempPath = tempnam(sys_get_temp_dir(), 'xlsx_test_').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $uploadedXlsx = new UploadedFile(
            $tempPath,
            'test.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $responseXlsx = $this->actingAs($this->admin)->post('/admin/students/import', [
            'class_id' => $this->classA->id,
            'file' => $uploadedXlsx,
        ]);

        @unlink($tempPath);

        $responseXlsx->assertRedirect('/admin/students');
        $responseXlsx->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'Excel Siswa',
            'nisn' => '0012345999',
            'email' => 'excel@siswa.test',
            'class_id' => $this->classA->id,
        ]);
    }

    public function test_student_cannot_double_submit_pending_payment(): void
    {
        $bill = CashBill::create([
            'class_id' => $this->classA->id,
            'category_id' => $this->catIncome->id,
            'title' => 'Uang Kas Maret',
            'amount' => 15000,
            'due_date' => now()->addDays(7)->toDateString(),
        ]);

        // First upload of full remaining amount (Rp 15.000)
        $file = UploadedFile::fake()->create('bukti1.jpg', 100, 'image/jpeg');
        $this->actingAs($this->siswaA)->post('/siswa/payments', [
            'cash_bill_id' => $bill->id,
            'amount_paid' => 15000,
            'payment_date' => now()->toDateString(),
            'proof_file' => $file,
        ])->assertRedirect('/siswa/payments');

        // Second attempt to upload while first is still pending
        $file2 = UploadedFile::fake()->create('bukti2.jpg', 100, 'image/jpeg');
        $response = $this->actingAs($this->siswaA)->post('/siswa/payments', [
            'cash_bill_id' => $bill->id,
            'amount_paid' => 15000,
            'payment_date' => now()->toDateString(),
            'proof_file' => $file2,
        ]);

        $response->assertSessionHas('error');
    }

    public function test_authenticated_user_can_change_password(): void
    {
        $response = $this->actingAs($this->siswaA)->put('/change-password', [
            'current_password' => 'password',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHas('success');
        $this->assertTrue(Hash::check('newpassword123', $this->siswaA->fresh()->password));
    }

    public function test_user_cannot_change_password_with_wrong_current_password(): void
    {
        $response = $this->actingAs($this->siswaA)->put('/change-password', [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('current_password');
    }

    public function test_student_and_admin_can_download_receipt_pdf(): void
    {
        $bill = CashBill::create([
            'class_id' => $this->classA->id,
            'category_id' => $this->catIncome->id,
            'title' => 'Iuran April',
            'amount' => 20000,
            'due_date' => now()->addDays(5)->toDateString(),
        ]);

        $payment = Payment::create([
            'receipt_number' => 'KWT-TEST-PDF-001',
            'cash_bill_id' => $bill->id,
            'student_id' => $this->siswaA->id,
            'amount_paid' => 20000,
            'payment_date' => now()->toDateString(),
            'status' => 'verified',
            'payment_method' => 'cash',
            'verified_by' => $this->admin->id,
        ]);

        // 1. Student download their verified receipt PDF
        $responseSiswa = $this->actingAs($this->siswaA)->get("/siswa/payments/{$payment->id}/receipt-pdf");
        $responseSiswa->assertOk();
        $responseSiswa->assertHeader('content-type', 'application/pdf');

        // 2. Admin download the receipt PDF
        $responseAdmin = $this->actingAs($this->admin)->get("/admin/payments/{$payment->id}/receipt-pdf");
        $responseAdmin->assertOk();
        $responseAdmin->assertHeader('content-type', 'application/pdf');
    }

    public function test_admin_payment_show_redirects_to_receipt(): void
    {
        $bill = CashBill::create([
            'class_id' => $this->classA->id,
            'category_id' => $this->catIncome->id,
            'title' => 'Iuran Mei',
            'amount' => 10000,
            'due_date' => now()->addDays(5)->toDateString(),
        ]);

        $payment = Payment::create([
            'receipt_number' => 'KWT-TEST-SHOW-001',
            'cash_bill_id' => $bill->id,
            'student_id' => $this->siswaA->id,
            'amount_paid' => 10000,
            'payment_date' => now()->toDateString(),
            'status' => 'verified',
            'payment_method' => 'cash',
        ]);

        $response = $this->actingAs($this->admin)->get("/admin/payments/{$payment->id}");
        $response->assertRedirect(route('admin.payments.receipt', $payment));
    }

    public function test_cannot_delete_income_transaction_if_funds_already_spent(): void
    {
        // 1. Income Rp 50.000
        $income = CashTransaction::create([
            'transaction_number' => 'TRX-IN-TEST-DELETE',
            'class_id' => $this->classA->id,
            'category_id' => $this->catIncome->id,
            'type' => 'in',
            'title' => 'Donasi Kas',
            'amount' => 50000,
            'transaction_date' => now()->toDateString(),
            'created_by' => $this->admin->id,
        ]);

        // 2. Expense Rp 40.000 (remaining balance = Rp 10.000)
        CashTransaction::create([
            'transaction_number' => 'TRX-OUT-TEST-SPENT',
            'class_id' => $this->classA->id,
            'category_id' => $this->catExpense->id,
            'type' => 'out',
            'title' => 'Beli Kertas',
            'amount' => 40000,
            'transaction_date' => now()->toDateString(),
            'created_by' => $this->admin->id,
        ]);

        // 3. Attempting to delete the income of Rp 50.000 should be rejected
        $response = $this->actingAs($this->bendaharaA)->delete("/admin/transactions/{$income->id}");
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('cash_transactions', ['id' => $income->id]);
    }

    public function test_cannot_reject_already_verified_payment(): void
    {
        $bill = CashBill::create([
            'class_id' => $this->classA->id,
            'category_id' => $this->catIncome->id,
            'title' => 'Iuran Juni',
            'amount' => 25000,
            'due_date' => now()->addDays(5)->toDateString(),
        ]);

        $payment = Payment::create([
            'receipt_number' => 'KWT-TEST-REJECT-001',
            'cash_bill_id' => $bill->id,
            'student_id' => $this->siswaA->id,
            'amount_paid' => 25000,
            'payment_date' => now()->toDateString(),
            'status' => 'verified',
            'payment_method' => 'cash',
        ]);

        $response = $this->actingAs($this->bendaharaA)->post("/admin/payments/{$payment->id}/reject");
        $response->assertSessionHas('error');
        $this->assertEquals('verified', $payment->fresh()->status);
    }

    public function test_cannot_reduce_bill_amount_below_student_paid_amount(): void
    {
        $bill = CashBill::create([
            'class_id' => $this->classA->id,
            'category_id' => $this->catIncome->id,
            'title' => 'Iuran Studi Banding',
            'amount' => 50000,
            'due_date' => now()->addDays(10)->toDateString(),
        ]);

        Payment::create([
            'receipt_number' => 'KWT-TEST-REDUCE-001',
            'cash_bill_id' => $bill->id,
            'student_id' => $this->siswaA->id,
            'amount_paid' => 40000,
            'payment_date' => now()->toDateString(),
            'status' => 'verified',
            'payment_method' => 'cash',
        ]);

        // Attempting to reduce the bill amount from Rp 50.000 to Rp 30.000 (below Rp 40.000 paid)
        $response = $this->actingAs($this->admin)->put("/admin/dues/{$bill->id}", [
            'title' => 'Iuran Studi Banding (Diskon)',
            'category_id' => $this->catIncome->id,
            'amount' => 30000,
            'due_date' => now()->addDays(10)->toDateString(),
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(50000, (float) $bill->fresh()->amount);
    }

    public function test_student_bank_receipt_proof_is_protected_in_transparency(): void
    {
        $bill = CashBill::create([
            'class_id' => $this->classA->id,
            'category_id' => $this->catIncome->id,
            'title' => 'Iuran Acara',
            'amount' => 20000,
            'due_date' => now()->addDays(5)->toDateString(),
        ]);

        $siswaB = User::create([
            'name' => 'Siswa B',
            'email' => 'siswa.b.test@siswa.test',
            'password' => bcrypt('password'),
            'role' => 'siswa',
            'class_id' => $this->classA->id,
        ]);

        $paymentB = Payment::create([
            'receipt_number' => 'KWT-TEST-PRIVACY-001',
            'cash_bill_id' => $bill->id,
            'student_id' => $siswaB->id,
            'amount_paid' => 20000,
            'payment_date' => now()->toDateString(),
            'status' => 'verified',
            'payment_method' => 'transfer',
            'proof_file' => 'payments/secret_receipt.jpg',
        ]);

        CashTransaction::create([
            'transaction_number' => 'TRX-IN-PRIVACY-001',
            'class_id' => $this->classA->id,
            'category_id' => $this->catIncome->id,
            'type' => 'in',
            'title' => 'Pembayaran Iuran Acara - Siswa B',
            'amount' => 20000,
            'transaction_date' => now()->toDateString(),
            'payment_id' => $paymentB->id,
            'proof_file' => 'payments/secret_receipt.jpg',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->siswaA)->get('/siswa/transparency?scope=class');
        $response->assertOk();
        $response->assertDontSee('secret_receipt.jpg');
        $response->assertSee('Terverifikasi');
    }

    public function test_login_endpoint_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'login' => 'invalid@login.test',
                'password' => 'wrong',
            ]);
        }

        $response = $this->post('/login', [
            'login' => 'invalid@login.test',
            'password' => 'wrong',
        ]);

        $response->assertStatus(429);
    }
}
