<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashBill;
use App\Models\CashTransaction;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $user = auth()->user();
        $isBendahara = $user->isBendaharaKelas();
        $scopedClassId = $isBendahara ? $user->class_id : null;

        // ─── KPI Widgets ───────────────────────────────────────────
        $totalStudents = User::where('role', 'siswa')
            ->when($scopedClassId, fn ($q, $cid) => $q->where('class_id', $cid))
            ->count();
        $totalClasses = $isBendahara ? 1 : SchoolClass::count();

        $totalIncome = (float) CashTransaction::income()
            ->when($scopedClassId, fn ($q, $cid) => $q->where('class_id', $cid))
            ->sum('amount');
        $totalExpense = (float) CashTransaction::expense()
            ->when($scopedClassId, fn ($q, $cid) => $q->where('class_id', $cid))
            ->sum('amount');
        $totalBalance = $totalIncome - $totalExpense;

        $incomeThisMonth = (float) CashTransaction::income()
            ->when($scopedClassId, fn ($q, $cid) => $q->where('class_id', $cid))
            ->whereMonth('transaction_date', $now->month)
            ->whereYear('transaction_date', $now->year)
            ->sum('amount');

        $expenseThisMonth = (float) CashTransaction::expense()
            ->when($scopedClassId, fn ($q, $cid) => $q->where('class_id', $cid))
            ->whereMonth('transaction_date', $now->month)
            ->whereYear('transaction_date', $now->year)
            ->sum('amount');

        // % Persentase Kelancaran Iuran
        $billsQuery = CashBill::withCount(['schoolClass as students_count' => function ($query) {
            $query->join('users', 'users.class_id', '=', 'classes.id')
                ->where('users.role', 'siswa');
        }]);
        if ($scopedClassId) {
            $billsQuery->where('class_id', $scopedClassId);
        }
        $bills = $billsQuery->get();

        $totalTargetAmount = 0;
        foreach ($bills as $bill) {
            $totalTargetAmount += ((float) $bill->amount * (int) $bill->students_count);
        }

        $collectedDuesQuery = Payment::where('status', 'verified');
        if ($scopedClassId) {
            $collectedDuesQuery->whereHas('cashBill', fn ($qb) => $qb->where('class_id', $scopedClassId));
        }
        $totalCollectedDues = (float) $collectedDuesQuery->sum('amount_paid');

        $collectionRate = $totalTargetAmount > 0
            ? min(100, round(($totalCollectedDues / $totalTargetAmount) * 100, 1))
            : 100.0;

        $pendingPaymentsQuery = Payment::where('status', 'pending');
        if ($scopedClassId) {
            $pendingPaymentsQuery->whereHas('cashBill', fn ($qb) => $qb->where('class_id', $scopedClassId));
        }
        $pendingPayments = $pendingPaymentsQuery->count();

        // ─── Chart 1: Arus Kas Bulanan (6 Bulan Terakhir) ──────────
        $monthlyLabels = [];
        $monthlyIncomeData = [];
        $monthlyExpenseData = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthDate = $now->copy()->subMonths($i);
            $monthlyLabels[] = $monthDate->translatedFormat('M Y');

            $mIncome = (float) CashTransaction::income()
                ->when($scopedClassId, fn ($q, $cid) => $q->where('class_id', $cid))
                ->whereMonth('transaction_date', $monthDate->month)
                ->whereYear('transaction_date', $monthDate->year)
                ->sum('amount');

            $mExpense = (float) CashTransaction::expense()
                ->when($scopedClassId, fn ($q, $cid) => $q->where('class_id', $cid))
                ->whereMonth('transaction_date', $monthDate->month)
                ->whereYear('transaction_date', $monthDate->year)
                ->sum('amount');

            $monthlyIncomeData[] = $mIncome;
            $monthlyExpenseData[] = $mExpense;
        }

        // ─── Chart 2: Perbandingan Kas Per Kelas (Optimized Query) ─
        $classes = $isBendahara
            ? SchoolClass::where('id', $scopedClassId)->get()
            : SchoolClass::orderBy('class_name')->get();

        $incomePerClass = CashTransaction::income()
            ->when($scopedClassId, fn ($q, $cid) => $q->where('class_id', $cid))
            ->selectRaw('class_id, SUM(amount) as total')
            ->groupBy('class_id')
            ->pluck('total', 'class_id');

        $expensePerClass = CashTransaction::expense()
            ->when($scopedClassId, fn ($q, $cid) => $q->where('class_id', $cid))
            ->selectRaw('class_id, SUM(amount) as total')
            ->groupBy('class_id')
            ->pluck('total', 'class_id');

        $classLabels = [];
        $classIncomeData = [];
        $classExpenseData = [];

        foreach ($classes as $class) {
            $classLabels[] = $class->class_name;
            $classIncomeData[] = (float) ($incomePerClass[$class->id] ?? 0);
            $classExpenseData[] = (float) ($expensePerClass[$class->id] ?? 0);
        }

        // ─── Recent Activities ────────────────────────────────────
        $recentTransactions = CashTransaction::with(['category', 'schoolClass'])
            ->when($scopedClassId, fn ($q, $cid) => $q->where('class_id', $cid))
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
