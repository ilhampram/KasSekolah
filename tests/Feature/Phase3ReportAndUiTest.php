<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\ReportController;
use App\Models\AcademicYear;
use App\Models\CashBill;
use App\Models\CashCategory;
use App\Models\CashTransaction;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase3ReportAndUiTest extends TestCase
{
    use RefreshDatabase;

    private AcademicYear $academicYear;

    private SchoolClass $classA;

    private User $admin;

    private User $siswa1;

    private User $siswa2;

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

        $this->admin = User::create([
            'name' => 'Admin Sekolah',
            'email' => 'admin@sekolah.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->siswa1 = User::create([
            'name' => 'Ahmad Siswa',
            'email' => 'ahmad@siswa.test',
            'nisn' => '0011223344',
            'password' => bcrypt('password'),
            'role' => 'siswa',
            'class_id' => $this->classA->id,
        ]);

        $this->siswa2 = User::create([
            'name' => 'Budi Siswa',
            'email' => 'budi@siswa.test',
            'nisn' => '0011223355',
            'password' => bcrypt('password'),
            'role' => 'siswa',
            'class_id' => $this->classA->id,
        ]);

        $this->catIncome = CashCategory::create([
            'name' => 'Kas Mingguan',
            'type' => 'in',
        ]);

        $this->catExpense = CashCategory::create([
            'name' => 'Kebersihan',
            'type' => 'out',
        ]);
    }

    public function test_receipt_view_contains_responsive_and_accessible_markup(): void
    {
        $bill = CashBill::create([
            'class_id' => $this->classA->id,
            'category_id' => $this->catIncome->id,
            'title' => 'Iuran Desember',
            'amount' => 25000,
            'due_date' => now()->addDays(5)->toDateString(),
        ]);

        $payment = Payment::create([
            'receipt_number' => 'KWT-VIEW-001',
            'cash_bill_id' => $bill->id,
            'student_id' => $this->siswa1->id,
            'amount_paid' => 25000,
            'payment_date' => now()->toDateString(),
            'status' => 'verified',
            'payment_method' => 'cash',
            'verified_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.payments.receipt', $payment));
        $response->assertOk();

        // Responsive flex wrappers for narrow displays <= 360px
        $response->assertSee('flex-col sm:flex-row', false);
        $response->assertSee('w-full sm:w-40', false);
        $response->assertSee('aria-hidden="true"', false);
    }

    public function test_layout_has_no_cdn_and_includes_accessible_hamburger(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $response->assertOk();

        // No external CDN scripts for Alpine or Chart.js
        $response->assertDontSee('cdn.jsdelivr.net', false);

        // Accessible drawer/hamburger attributes
        $response->assertSee('id="hamburger-btn"', false);
        $response->assertSee('aria-controls="sidebar"', false);
        $response->assertSee('aria-expanded="false"', false);
        $response->assertSee('aria-label="Buka menu navigasi"', false);
        $response->assertSee('id="sidebar"', false);
    }

    public function test_report_running_balance_and_sql_aggregation_dues_data(): void
    {
        // 1. Transactions: Income 100k, Expense 30k, Voided Expense 20k
        CashTransaction::create([
            'transaction_number' => 'TRX-REP-001',
            'class_id' => $this->classA->id,
            'category_id' => $this->catIncome->id,
            'type' => 'in',
            'title' => 'Pemasukan Awal',
            'amount' => 100000,
            'transaction_date' => now()->toDateString(),
            'created_by' => $this->admin->id,
        ]);

        CashTransaction::create([
            'transaction_number' => 'TRX-REP-002',
            'class_id' => $this->classA->id,
            'category_id' => $this->catExpense->id,
            'type' => 'out',
            'title' => 'Beli Sapu',
            'amount' => 30000,
            'transaction_date' => now()->toDateString(),
            'created_by' => $this->admin->id,
        ]);

        CashTransaction::create([
            'transaction_number' => 'TRX-REP-003',
            'class_id' => $this->classA->id,
            'category_id' => $this->catExpense->id,
            'type' => 'out',
            'title' => 'Voided Expense',
            'amount' => 20000,
            'transaction_date' => now()->toDateString(),
            'voided_at' => now(),
            'voided_by' => $this->admin->id,
            'void_reason' => 'Batal',
            'created_by' => $this->admin->id,
        ]);

        // 2. Bills: Bill 50k for Class A (2 students -> total target 100k)
        $bill = CashBill::create([
            'class_id' => $this->classA->id,
            'category_id' => $this->catIncome->id,
            'title' => 'Iuran November',
            'amount' => 50000,
            'due_date' => now()->addDays(5)->toDateString(),
        ]);

        Payment::create([
            'receipt_number' => 'KWT-REP-001',
            'cash_bill_id' => $bill->id,
            'student_id' => $this->siswa1->id,
            'amount_paid' => 50000,
            'payment_date' => now()->toDateString(),
            'status' => 'verified',
            'payment_method' => 'cash',
            'verified_by' => $this->admin->id,
        ]);

        Payment::create([
            'receipt_number' => 'KWT-REP-002',
            'cash_bill_id' => $bill->id,
            'student_id' => $this->siswa2->id,
            'amount_paid' => 20000,
            'payment_date' => now()->toDateString(),
            'status' => 'verified',
            'payment_method' => 'cash',
            'verified_by' => $this->admin->id,
        ]);

        $reportController = new ReportController;
        $reflector = new \ReflectionClass(ReportController::class);

        // Test Running Balance (Window Function / Fallback)
        $methodRunning = $reflector->getMethod('getTransactionsWithRunningBalance');
        $methodRunning->setAccessible(true);
        $runningResult = $methodRunning->invoke(
            $reportController,
            now()->subDay()->toDateString(),
            now()->addDay()->toDateString(),
            (string) $this->classA->id,
            null,
            null,
            0.0
        );

        $this->assertCount(2, $runningResult);
        $this->assertEquals(100000.0, $runningResult[0]['running_balance']);
        $this->assertEquals(70000.0, $runningResult[1]['running_balance']);

        // Test Dues SQL Aggregation Data
        $methodDues = $reflector->getMethod('getStudentDuesData');
        $methodDues->setAccessible(true);
        $duesResult = $methodDues->invoke(
            $reportController,
            (string) $this->classA->id,
            null
        );

        $summary = $duesResult['summary'];
        $this->assertEquals(100000.0, $summary['total_target']);
        $this->assertEquals(70000.0, $summary['total_paid']);
        $this->assertEquals(30000.0, $summary['total_debt']);
        $this->assertEquals(1, $summary['lunas_count']);
        $this->assertEquals(1, $summary['belum_count']);

        // Test Report index view renders successfully
        $response = $this->actingAs($this->admin)->get('/admin/reports');
        $response->assertOk();
        $response->assertSee('TRX-REP-001');
        $response->assertSee('TRX-REP-002');
        $response->assertDontSee('TRX-REP-003');
    }

    public function test_bendahara_does_not_see_student_delete_button_while_admin_sees_it(): void
    {
        $bendahara = User::create([
            'name' => 'Bendahara Kelas',
            'email' => 'bendahara.test@sekolah.test',
            'password' => bcrypt('password'),
            'role' => 'bendahara_kelas',
            'class_id' => $this->classA->id,
        ]);

        $resBendahara = $this->actingAs($bendahara)->get('/admin/students');
        $resBendahara->assertOk();
        $resBendahara->assertDontSee('🗑️ Hapus');

        $resAdmin = $this->actingAs($this->admin)->get('/admin/students');
        $resAdmin->assertOk();
        $resAdmin->assertSee('🗑️ Hapus');
    }

    public function test_all_reports_exports_download_without_errors(): void
    {
        $this->actingAs($this->admin)->get('/admin/reports/export-excel')->assertDownload();
        $this->actingAs($this->admin)->get('/admin/reports/export-pdf')->assertDownload();
        $this->actingAs($this->admin)->get('/admin/reports/export-dues-excel')->assertDownload();
        $this->actingAs($this->admin)->get('/admin/reports/export-dues-pdf')->assertDownload();
    }
}
