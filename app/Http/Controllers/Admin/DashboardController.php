<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashBill;
use App\Models\CashTransaction;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        // ─── KPI Widgets ───────────────────────────────────────────
        $totalStudents = User::where('role', 'siswa')->count();
        $totalClasses  = SchoolClass::count();

        $totalIncome   = (float) CashTransaction::income()->sum('amount');
        $totalExpense  = (float) CashTransaction::expense()->sum('amount');
        $totalBalance  = $totalIncome - $totalExpense;

        $incomeThisMonth = (float) CashTransaction::income()
            ->whereMonth('transaction_date', $now->month)
            ->whereYear('transaction_date', $now->year)
            ->sum('amount');

        $expenseThisMonth = (float) CashTransaction::expense()
            ->whereMonth('transaction_date', $now->month)
            ->whereYear('transaction_date', $now->year)
            ->sum('amount');

        // % Persentase Kelancaran Iuran
        // Calculate total target of all bills across student counts
        $bills = CashBill::withCount(['schoolClass as students_count' => function ($query) {
            $query->join('users', 'users.class_id', '=', 'classes.id')
                  ->where('users.role', 'siswa');
        }])->get();

        $totalTargetAmount = 0;
        foreach ($bills as $bill) {
            $totalTargetAmount += ((float) $bill->amount * (int) $bill->students_count);
        }

        $totalCollectedDues = (float) Payment::where('status', 'verified')->sum('amount_paid');
        $collectionRate = $totalTargetAmount > 0
            ? min(100, round(($totalCollectedDues / $totalTargetAmount) * 100, 1))
            : 100.0;

        $pendingPayments = Payment::where('status', 'pending')->count();

        // ─── Chart 1: Arus Kas Bulanan (6 Bulan Terakhir) ──────────
        $monthlyLabels = [];
        $monthlyIncomeData = [];
        $monthlyExpenseData = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthDate = $now->copy()->subMonths($i);
            $monthlyLabels[] = $monthDate->translatedFormat('M Y');

            $mIncome = (float) CashTransaction::income()
                ->whereMonth('transaction_date', $monthDate->month)
                ->whereYear('transaction_date', $monthDate->year)
                ->sum('amount');

            $mExpense = (float) CashTransaction::expense()
                ->whereMonth('transaction_date', $monthDate->month)
                ->whereYear('transaction_date', $monthDate->year)
                ->sum('amount');

            $monthlyIncomeData[] = $mIncome;
            $monthlyExpenseData[] = $mExpense;
        }

        // ─── Chart 2: Perbandingan Kas Per Kelas ───────────────────
        $classes = SchoolClass::orderBy('class_name')->get();
        $classLabels = [];
        $classIncomeData = [];
        $classExpenseData = [];

        foreach ($classes as $class) {
            $classLabels[] = $class->class_name;
            $classIncomeData[] = (float) CashTransaction::income()
                ->where('class_id', $class->id)
                ->sum('amount');
            $classExpenseData[] = (float) CashTransaction::expense()
                ->where('class_id', $class->id)
                ->sum('amount');
        }

        // ─── Recent Activities ────────────────────────────────────
        $recentTransactions = CashTransaction::with(['category', 'schoolClass'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalStudents',
            'totalClasses',
            'totalIncome',
            'totalExpense',
            'totalBalance',
            'incomeThisMonth',
            'expenseThisMonth',
            'collectionRate',
            'totalTargetAmount',
            'totalCollectedDues',
            'pendingPayments',
            'monthlyLabels',
            'monthlyIncomeData',
            'monthlyExpenseData',
            'classLabels',
            'classIncomeData',
            'classExpenseData',
            'recentTransactions'
        ));
    }
}
