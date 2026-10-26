<?php

use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\CashTransactionController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ClassRoomController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\DuesPaymentController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\StudentDueController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Siswa\DashboardController as SiswaDashboard;
use App\Http\Controllers\Siswa\PaymentHistoryController;
use App\Http\Controllers\Siswa\TransparencyController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ─── Public / Guest ───────────────────────────────────────────
Route::get('/', fn () => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->middleware('throttle:3,1')->name('password.email');

    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1')->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/change-password', [AuthController::class, 'showChangePassword'])->name('password.change');
    Route::put('/change-password', [AuthController::class, 'updatePassword'])->middleware('throttle:5,1')->name('password.update.auth');
});

// ─── Dashboard Redirect ──────────────────────────────────────
Route::get('/dashboard', function () {
    $user = Auth::user();

    return match ($user->role) {
        'admin', 'bendahara_kelas' => redirect()->route('admin.dashboard'),
        'siswa' => redirect()->route('siswa.dashboard'),
        default => redirect('/login'),
    };
})->middleware('auth')->name('dashboard');

// ─── Admin & Bendahara Routes ────────────────────────────────
Route::prefix('admin')
    ->middleware(['auth', 'role:admin,bendahara_kelas'])
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

        // Master Data Sekolah (Khusus Admin)
        Route::middleware('role:admin')->group(function () {
            Route::post('/academic-years/{academicYear}/activate', [AcademicYearController::class, 'activate'])->name('academic-years.activate');
            Route::resource('academic-years', AcademicYearController::class)->except(['show']);
            Route::resource('classes', ClassRoomController::class)->except(['show']);
            Route::resource('categories', CategoryController::class)->except(['show']);
        });

        // Master Siswa
        Route::get('/students/template', [StudentController::class, 'downloadTemplate'])->name('students.template');
        Route::post('/students/import', [StudentController::class, 'import'])->name('students.import');
        Route::post('/students/{student}/reset-password', [StudentController::class, 'resetPassword'])->name('students.reset-password');
        Route::resource('students', StudentController::class)->except(['show']);

        // ─── Modul Keuangan & Transaksi (Tahap 4) ─────────────
        // Transaksi Kas Umum (Pemasukan & Pengeluaran)
        Route::resource('transactions', CashTransactionController::class);

        // Manajemen Tagihan & Iuran Siswa (Generate Massal)
        Route::resource('dues', StudentDueController::class);

        // Modul Pembayaran Kasir & Kwitansi
        Route::get('/payments/bill-status', [DuesPaymentController::class, 'getBillStatus'])->name('payments.bill-status');
        Route::get('/payments/{payment}/receipt', [DuesPaymentController::class, 'receipt'])->name('payments.receipt');
        Route::get('/payments/{payment}/receipt-pdf', [DuesPaymentController::class, 'receiptPdf'])->name('payments.receipt-pdf');
        Route::post('/payments/{payment}/verify', [DuesPaymentController::class, 'verify'])->name('payments.verify');
        Route::post('/payments/{payment}/reject', [DuesPaymentController::class, 'reject'])->name('payments.reject');
        Route::resource('payments', DuesPaymentController::class)->except(['edit', 'update', 'destroy']);

        // ─── Modul Laporan Keuangan & Export (Tahap 6) ────────
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.export-excel');
        Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export-pdf');
        Route::get('/reports/export-dues-excel', [ReportController::class, 'exportDuesExcel'])->name('reports.export-dues-excel');
        Route::get('/reports/export-dues-pdf', [ReportController::class, 'exportDuesPdf'])->name('reports.export-dues-pdf');
    });

// ─── Siswa Routes ────────────────────────────────────────────
Route::prefix('siswa')
    ->middleware(['auth', 'role:siswa'])
    ->name('siswa.')
    ->group(function () {
        Route::get('/dashboard', [SiswaDashboard::class, 'index'])->name('dashboard');

        // Histori Pembayaran & Upload Bukti Bayar
        Route::get('/payments', [PaymentHistoryController::class, 'index'])->name('payments.index');
        Route::get('/payments/create', [PaymentHistoryController::class, 'create'])->name('payments.create');
        Route::post('/payments', [PaymentHistoryController::class, 'store'])->name('payments.store');
        Route::get('/payments/{payment}/receipt', [PaymentHistoryController::class, 'receipt'])->name('payments.receipt');
        Route::get('/payments/{payment}/receipt-pdf', [PaymentHistoryController::class, 'receiptPdf'])->name('payments.receipt-pdf');

        // Laporan Kas Transparan
        Route::get('/transparency', [TransparencyController::class, 'index'])->name('transparency.index');
    });
