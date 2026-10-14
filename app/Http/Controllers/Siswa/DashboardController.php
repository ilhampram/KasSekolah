<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\CashBill;
use App\Models\CashTransaction;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $classId = $user->class_id;

        $bills = collect();
        $billStatuses = [];
        $totalBillAmount = 0;
        $totalPaidAmount = (float) Payment::where('student_id', $user->id)
            ->where('status', 'verified')
            ->sum('amount_paid');
        $pendingPaymentsCount = Payment::where('student_id', $user->id)
            ->where('status', 'pending')
            ->count();

        $lunasCount = 0;
        $tunggakanCount = 0;

        if ($classId) {
            $bills = CashBill::where('class_id', $classId)
                ->with(['category', 'schoolClass', 'payments'])
                ->orderByDesc('due_date')
                ->get();

            foreach ($bills as $bill) {
                $status = $bill->studentPaymentStatus($user->id);
                $billStatuses[$bill->id] = $status;
                $totalBillAmount += (float) $bill->amount;

                if ($status['status'] === 'lunas') {
                    $lunasCount++;
                } else {
                    $tunggakanCount++;
                }
            }
        }

        $remainingDebt = max(0, $totalBillAmount - $totalPaidAmount);

        // Kas Transparansi Metrics
        $classIncome  = $classId ? (float) CashTransaction::income()->where('class_id', $classId)->sum('amount') : 0;
        $classExpense = $classId ? (float) CashTransaction::expense()->where('class_id', $classId)->sum('amount') : 0;
        $classBalance = $classIncome - $classExpense;

        $schoolIncome  = (float) CashTransaction::income()->sum('amount');
        $schoolExpense = (float) CashTransaction::expense()->sum('amount');
        $schoolBalance = $schoolIncome - $schoolExpense;

        $recentPayments = Payment::where('student_id', $user->id)
            ->with(['cashBill'])
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->take(5)
            ->get();

        return view('siswa.dashboard', compact(
            'user',
            'bills',
            'billStatuses',
            'totalBillAmount',
            'totalPaidAmount',
            'remainingDebt',
            'lunasCount',
            'tunggakanCount',
            'pendingPaymentsCount',
            'classIncome',
            'classExpense',
            'classBalance',
            'schoolIncome',
            'schoolExpense',
            'schoolBalance',
            'recentPayments'
        ));
    }
}
