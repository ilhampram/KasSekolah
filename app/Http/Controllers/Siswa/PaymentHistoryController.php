<?php

namespace App\Http\Controllers\Siswa;

use App\Helpers\Terbilang;
use App\Http\Controllers\Controller;
use App\Models\CashBill;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PaymentHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Payment::where('student_id', $user->id)
            ->with(['cashBill.schoolClass', 'cashBill.category', 'verifier']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($startDate = $request->input('start_date')) {
            $query->whereDate('payment_date', '>=', $startDate);
        }

        if ($endDate = $request->input('end_date')) {
            $query->whereDate('payment_date', '<=', $endDate);
        }

        $payments = $query->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $totalPaid = (float) Payment::where('student_id', $user->id)
            ->where('status', 'verified')
            ->sum('amount_paid');

        $pendingCount = Payment::where('student_id', $user->id)
            ->where('status', 'pending')
            ->count();

        return view('siswa.payments.index', compact('payments', 'totalPaid', 'pendingCount'));
    }

    public function create(Request $request)
    {
        $user = Auth::user();

        if (! $user->class_id) {
            return redirect()->route('siswa.dashboard')
                ->with('error', 'Kamu belum terdaftar di kelas manapun.');
        }

        // Only bills for this student's class that still have remaining balance
        $allBills = CashBill::where('class_id', $user->class_id)
            ->with(['category', 'payments'])
            ->orderByDesc('due_date')
            ->get();

        $activeBills = [];
        $pendingBills = [];

        foreach ($allBills as $bill) {
            $status = $bill->studentPaymentStatus($user->id);
            $effectiveRemaining = max(0, $status['remaining'] - $status['pending']);

            if ($effectiveRemaining > 0) {
                $activeBills[] = [
                    'bill' => $bill,
                    'remaining' => $effectiveRemaining,
                    'paid' => $status['paid'],
                    'pending' => $status['pending'],
                ];
            } elseif ($status['pending'] > 0 && $status['remaining'] > 0) {
                $pendingBills[] = [
                    'bill' => $bill,
                    'pending' => $status['pending'],
                ];
            }
        }

        $selectedBillId = $request->query('bill_id');

        return view('siswa.payments.create', compact('activeBills', 'pendingBills', 'selectedBillId'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'cash_bill_id' => ['required', 'exists:cash_bills,id'],
            'amount_paid' => ['required', 'numeric', 'min:1'],
            'payment_date' => ['required', 'date'],
            'proof_file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'cash_bill_id.required' => 'Pilih tagihan yang ingin dibayar.',
            'amount_paid.required' => 'Nominal pembayaran wajib diisi.',
            'amount_paid.numeric' => 'Nominal harus berupa angka.',
            'proof_file.required' => 'Foto bukti transfer/resi pembayaran wajib diunggah.',
            'proof_file.mimes' => 'Bukti pembayaran harus berupa gambar (JPG, PNG) atau PDF.',
            'proof_file.max' => 'Ukuran bukti pembayaran maksimal 5MB.',
        ]);

        $bill = CashBill::where('class_id', $user->class_id)->findOrFail($request->cash_bill_id);
        $statusInfo = $bill->studentPaymentStatus($user->id);

        $effectiveRemaining = max(0, $statusInfo['remaining'] - $statusInfo['pending']);

        if ($effectiveRemaining <= 0) {
            return back()->withInput()->with(
                'error',
                'Kamu sudah memiliki pengajuan pembayaran sebesar Rp '.number_format($statusInfo['pending'], 0, ',', '.')." yang sedang menunggu verifikasi bendahara untuk tagihan {$bill->title}."
            );
        }

        if ((float) $request->amount_paid > $effectiveRemaining) {
            return back()->withInput()->with(
                'error',
                'Nominal pembayaran (Rp '.number_format($request->amount_paid, 0, ',', '.').
                ') melebihi sisa tagihan yang belum diajukan (Rp '.number_format($effectiveRemaining, 0, ',', '.').').'
            );
        }

        $proofPath = $request->file('proof_file')->store('payments', 'public');

        Payment::create([
            'cash_bill_id' => $bill->id,
            'student_id' => $user->id,
            'amount_paid' => $request->amount_paid,
            'payment_date' => $request->payment_date,
            'status' => 'pending',
            'payment_method' => 'transfer',
            'proof_file' => $proofPath,
            'notes' => $request->notes,
        ]);

        return redirect()->route('siswa.payments.index')
            ->with('success', 'Bukti pembayaran berhasil diunggah! Mohon menunggu verifikasi dari bendahara/admin.');
    }

    public function receipt(Payment $payment)
    {
        // Security check via Policy: Student can only view their own verified payment receipts
        Gate::authorize('receipt', $payment);

        $payment->load(['cashBill.schoolClass', 'cashBill.category', 'student', 'verifier']);
        $bill = $payment->cashBill;
        $statusInfo = $bill->studentPaymentStatus($payment->student_id);
        $terbilang = Terbilang::rupiah($payment->amount_paid);

        return view('admin.payments.receipt', compact('payment', 'bill', 'statusInfo', 'terbilang'));
    }

    public function receiptPdf(Payment $payment)
    {
        Gate::authorize('receipt', $payment);

        $payment->load(['cashBill.schoolClass', 'cashBill.category', 'student', 'verifier']);
        $bill = $payment->cashBill;
        $statusInfo = $bill->studentPaymentStatus($payment->student_id);
        $terbilang = Terbilang::rupiah($payment->amount_paid);

        $pdf = Pdf::loadView('admin.payments.pdf.receipt', compact('payment', 'bill', 'statusInfo', 'terbilang'))
            ->setPaper('a5', 'portrait');

        return $pdf->download('Kwitansi_'.($payment->receipt_number ?: $payment->id).'.pdf');
    }
}
