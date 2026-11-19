<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\AuditLog;
use App\Models\CashBill;
use App\Models\CashCategory;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashBillTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_payment_status_returns_remaining_balance_for_partially_paid_bill(): void
    {
        $bill = new CashBill(['amount' => '100.00']);
        $bill->setRelation('payments', collect([
            new Payment(['student_id' => 7, 'status' => 'verified', 'amount_paid' => '35.00']),
            new Payment(['student_id' => 7, 'status' => 'pending', 'amount_paid' => '20.00']),
            new Payment(['student_id' => 8, 'status' => 'verified', 'amount_paid' => '100.00']),
        ]));

        $this->assertSame([
            'paid' => 35.0,
            'pending' => 20.0,
            'remaining' => 65.0,
            'status' => 'cicilan',
        ], $bill->studentPaymentStatus(7));
    }

    public function test_deleting_bill_records_audit_entry_for_authenticated_user(): void
    {
        $academicYear = AcademicYear::create([
            'year' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);
        $schoolClass = SchoolClass::create([
            'class_name' => 'X IPA 1',
            'academic_year_id' => $academicYear->id,
            'academic_year' => '2026/2027',
            'homeroom_teacher' => 'Wali Kelas',
        ]);
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'secret',
            'role' => 'admin',
        ]);
        $category = CashCategory::create(['name' => 'Kas', 'type' => 'in']);
        $bill = CashBill::create([
            'class_id' => $schoolClass->id,
            'category_id' => $category->id,
            'title' => 'Iuran kelas',
            'amount' => 25000,
            'due_date' => now()->addDays(7)->toDateString(),
        ]);

        $this->actingAs($user);
        $bill->delete();

        $this->assertDatabaseMissing('cash_bills', ['id' => $bill->id]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'event' => 'cash_bill.deleted',
            'subject_type' => CashBill::class,
            'subject_id' => $bill->id,
        ]);
        $this->assertSame(
            'Iuran kelas',
            AuditLog::query()->where('event', 'cash_bill.deleted')->firstOrFail()->properties['data']['title'],
        );
    }
}
