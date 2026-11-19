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
use Tests\TestCase;

class RouteAccessibilityTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $bendahara;

    private User $siswa;

    private SchoolClass $class;

    private CashBill $bill;

    private Payment $payment;

    private CashTransaction $transaction;

    protected function setUp(): void
    {
        parent::setUp();

        $year = AcademicYear::create([
            'year' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);

        $this->class = SchoolClass::create([
            'class_name' => 'X IPA 1',
            'academic_year_id' => $year->id,
            'academic_year' => '2026/2027',
            'homeroom_teacher' => 'Wali Kelas 1',
        ]);

        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin.route@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->bendahara = User::create([
            'name' => 'Bendahara Test',
            'email' => 'bendahara.route@test.com',
            'password' => bcrypt('password'),
            'role' => 'bendahara_kelas',
            'class_id' => $this->class->id,
        ]);

        $this->siswa = User::create([
            'name' => 'Siswa Test',
            'email' => 'siswa.route@test.com',
            'password' => bcrypt('password'),
            'role' => 'siswa',
            'class_id' => $this->class->id,
            'nisn' => '1234567890',
        ]);

        $cat = CashCategory::create([
            'name' => 'Iuran Rutin',
            'type' => 'in',
        ]);

        $this->bill = CashBill::create([
            'class_id' => $this->class->id,
            'category_id' => $cat->id,
            'title' => 'Tagihan Uji Route',
            'amount' => 50000,
            'due_date' => now()->addDays(7)->toDateString(),
        ]);

        $this->payment = Payment::create([
            'receipt_number' => 'KWT-ROUTE-01',
            'cash_bill_id' => $this->bill->id,
            'student_id' => $this->siswa->id,
            'amount_paid' => 50000,
            'payment_date' => now()->toDateString(),
            'status' => 'verified',
            'payment_method' => 'cash',
            'verified_by' => $this->admin->id,
        ]);

        $this->transaction = CashTransaction::create([
            'transaction_number' => 'TRX-ROUTE-01',
            'class_id' => $this->class->id,
            'category_id' => $cat->id,
            'type' => 'in',
            'title' => 'Pemasukan Uji Route',
            'amount' => 50000,
            'transaction_date' => now()->toDateString(),
            'payment_id' => $this->payment->id,
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_all_admin_pages_render_without_error(): void
    {
        $routes = [
            '/admin/dashboard',
            '/admin/dues',
            '/admin/dues/create',
            "/admin/dues/{$this->bill->id}",
            "/admin/dues/{$this->bill->id}/edit",
            '/admin/payments',
            '/admin/payments/create',
            "/admin/payments/{$this->payment->id}/receipt",
            '/admin/transactions',
            '/admin/transactions/create',
            "/admin/transactions/{$this->transaction->id}",
            '/admin/reports',
            '/admin/students',
            '/admin/students/create',
            "/admin/students/{$this->siswa->id}/edit",
            '/admin/categories',
            '/admin/categories/create',
            '/admin/classes',
            '/admin/classes/create',
            '/admin/academic-years',
            '/admin/academic-years/create',
            '/admin/audit-logs',
            '/change-password',
        ];

        foreach ($routes as $uri) {
            $response = $this->actingAs($this->admin)->get($uri);
            $response->assertOk();
        }
    }

    public function test_all_bendahara_pages_render_without_error(): void
    {
        $routes = [
            '/admin/dashboard',
            '/admin/dues',
            '/admin/dues/create',
            "/admin/dues/{$this->bill->id}",
            "/admin/dues/{$this->bill->id}/edit",
            '/admin/payments',
            '/admin/payments/create',
            "/admin/payments/{$this->payment->id}/receipt",
            '/admin/transactions',
            '/admin/transactions/create',
            '/admin/reports',
            '/admin/students',
            '/admin/students/create',
            "/admin/students/{$this->siswa->id}/edit",
            '/change-password',
        ];

        foreach ($routes as $uri) {
            $response = $this->actingAs($this->bendahara)->get($uri);
            $response->assertOk();
        }
    }

    public function test_all_siswa_pages_render_without_error(): void
    {
        $routes = [
            '/siswa/dashboard',
            '/siswa/payments',
            '/siswa/payments/create',
            "/siswa/payments/{$this->payment->id}/receipt",
            '/siswa/transparency',
            '/change-password',
        ];

        foreach ($routes as $uri) {
            $response = $this->actingAs($this->siswa)->get($uri);
            $response->assertOk();
        }
    }
}
