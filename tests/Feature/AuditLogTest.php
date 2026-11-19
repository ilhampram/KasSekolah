<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\AuditLog;
use App\Models\CashCategory;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_cash_transaction_creation_is_audited_with_actor_and_amount(): void
    {
        $year = AcademicYear::create(['year' => '2026/2027', 'semester' => 'Ganjil', 'is_active' => true]);
        $class = SchoolClass::create([
            'class_name' => 'X IPA 1', 'academic_year_id' => $year->id,
            'academic_year' => '2026/2027', 'homeroom_teacher' => 'Wali Kelas',
        ]);
        $user = User::create([
            'name' => 'Bendahara', 'email' => 'bendahara@test.com', 'password' => bcrypt('secret'),
            'role' => 'bendahara_kelas', 'class_id' => $class->id,
        ]);
        $category = CashCategory::create(['name' => 'Kas', 'type' => 'in']);

        $this->actingAs($user)->post('/admin/transactions', [
            'type' => 'in', 'category_id' => $category->id, 'title' => 'Iuran',
            'amount' => 25000, 'transaction_date' => now()->toDateString(),
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'event' => 'cash_transaction.created',
        ]);
        $this->assertSame(25000, (int) AuditLog::where('event', 'cash_transaction.created')->first()->properties['amount']);
    }

    public function test_failed_login_is_audited_without_password(): void
    {
        $this->post('/login', ['login' => 'someone@example.com', 'password' => 'never-log-this']);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'auth.login_failed',
            'properties->identifier' => 'someone@example.com',
        ]);
        $this->assertStringNotContainsString('never-log-this', json_encode(AuditLog::first()->properties));
    }

    public function test_admin_can_view_audit_logs_index_page_and_filter(): void
    {
        $admin = User::create([
            'name' => 'Admin Utama',
            'email' => 'admin.audit@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        AuditLog::record('test.custom_event', null, ['detail' => 'testing properties']);

        $res = $this->actingAs($admin)->get('/admin/audit-logs');
        $res->assertOk();
        $res->assertSee('test.custom_event');
        $res->assertSee('Jejak Aktivitas & Audit Log', false);

        // Test filtering
        $resFiltered = $this->actingAs($admin)->get('/admin/audit-logs?event=test.custom_event');
        $resFiltered->assertOk();
        $resFiltered->assertSee('test.custom_event');
    }

    public function test_bendahara_and_siswa_cannot_access_audit_logs(): void
    {
        $year = AcademicYear::create(['year' => '2026/2027', 'semester' => 'Ganjil', 'is_active' => true]);
        $class = SchoolClass::create([
            'class_name' => 'X IPA 1', 'academic_year_id' => $year->id,
            'academic_year' => '2026/2027', 'homeroom_teacher' => 'Wali Kelas',
        ]);
        $bendahara = User::create([
            'name' => 'Bendahara User',
            'email' => 'bendahara.audit@test.com',
            'password' => bcrypt('password'),
            'role' => 'bendahara_kelas',
            'class_id' => $class->id,
        ]);
        $siswa = User::create([
            'name' => 'Siswa User',
            'email' => 'siswa.audit@test.com',
            'password' => bcrypt('password'),
            'role' => 'siswa',
            'class_id' => $class->id,
        ]);

        $this->actingAs($bendahara)->get('/admin/audit-logs')->assertForbidden();
        $this->actingAs($siswa)->get('/admin/audit-logs')->assertForbidden();
    }
}
