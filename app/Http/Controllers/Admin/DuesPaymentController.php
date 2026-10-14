<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Terbilang;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDuesPaymentRequest;
use App\Models\CashBill;
use App\Models\CashTransaction;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DuesPaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['cashBill.schoolClass', 'cashBill.category', 'student', 'verifier']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                  ->orWhereHas('student', function ($qs) use ($search) {
                      $qs->where('name', 'like', "%{$search}%")
                         ->orWhere('nisn', 'like', "%{$search}%");
                  })
                  ->orWhereHas('cashBill', function ($qb) use ($search) {
                      $qb->where('title', 'like', "%{$search}%");
                  });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($classId = $request->input('class_id')) {
            $query->whereHas('cashBill', function ($qb) use ($classId) {
                $qb->where('class_id', $classId);
            });
        }

        if ($startDate = $request->input('start_date')) {
            $query->whereDate('payment_date', '>=', $startDate);
        }

        if ($endDate = $request->input('end_date')) {
            $query->whereDate('payment_date', '<=', $endDate);
        }

        $payments = $query->orderByDesc('payment_date')
                          ->orderByDesc('id')
                          ->paginate(15)
                          ->withQueryString();

        $totalVerifiedAmount = Payment::where('status', 'verified')->sum('amount_paid');
        $pendingCount        = Payment::where('status', 'pending')->count();
        $classes             = SchoolClass::orderBy('class_name')->get();

        return view('admin.payments.index', compact(
            'payments',
            'totalVerifiedAmount',
            'pendingCount',
            'classes'
        ));
    }

    public function create(Request $request)
    {
        $selectedBillId    = $request->query('bill_id');
        $selectedStudentId = $request->query('student_id');

        $selectedBill    = $selectedBillId ? CashBill::with('schoolClass')->find($selectedBillId) : null;
        $selectedStudent = $selectedStudentId ? User::with('class')->find($selectedStudentId) : null;

        $studentRemaining = 0;
        if ($selectedBill && $selectedStudent) {
            $status = $selectedBill->studentPaymentStatus($selectedStudent->id);
            $studentRemaining = $status['remaining'];
        }

        $classes = SchoolClass::with('students')->orderBy('class_name')->get();
        $bills   = CashBill::with(['schoolClass', 'payments'])->orderByDesc('due_date')->get();

        return view('admin.payments.create', compact(
            'classes',
            'bills',
            'selectedBill',
            'selectedStudent',
            'studentRemaining'
        ));
    }

    public function store(StoreDuesPaymentRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $bill    = CashBill::with(['schoolClass', 'category'])->findOrFail($request->cash_bill_id);
            $student = User::where('role', 'siswa')->findOrFail($request->student_id);

            // Calculate remaining balance
            $statusInfo = $bill->studentPaymentStatus($student->id);
            $remaining  = $statusInfo['remaining'];

            if ($remaining <= 0) {
                return back()->withInput()->with('error', "Siswa {$student->name} sudah LUNAS untuk tagihan {$bill->title}.");
            }

            if ((float) $request->amount_paid > $remaining) {
                return back()->withInput()->with(
                    'error',
                    "Nominal pembayaran (Rp " . number_format($request->amount_paid, 0, ',', '.') .
                    ") melebihi sisa tagihan siswa (Rp " . number_format($remaining, 0, ',', '.') . ")."
                );
            }

            $proofPath = null;
            if ($request->hasFile('proof_file')) {
                $proofPath = $request->file('proof_file')->store('payments', 'public');
            }

            $receiptNumber = Payment::generateReceiptNumber();

            // 1. Create Payment
            $payment = Payment::create([
                'receipt_number' => $receiptNumber,
                'cash_bill_id'   => $bill->id,
                'student_id'     => $student->id,
                'amount_paid'    => $request->amount_paid,
                'payment_date'   => $request->payment_date,
                'status'         => 'verified',
                'payment_method' => $request->payment_method,
                'proof_file'     => $proofPath,
                'notes'          => $request->notes,
                'verified_by'    => Auth::id(),
            ]);

            // 2. Automatically accumulate into cash_transactions
            CashTransaction::create([
                'transaction_number' => CashTransaction::generateTransactionNumber('in'),
                'class_id'           => $bill->class_id,
                'category_id'        => $bill->category_id,
                'type'               => 'in',
                'title'              => "Pembayaran {$bill->title} - {$student->name} ({$bill->schoolClass->class_name})",
                'amount'             => $payment->amount_paid,
                'transaction_date'   => $payment->payment_date,
                'payment_id'         => $payment->id,
                'proof_file'         => $payment->proof_file,
                'notes'              => "Kwitansi: {$payment->receipt_number}. Metode: {$payment->payment_method}. " . ($payment->notes ?? ''),
                'created_by'         => Auth::id(),
            ]);

            return redirect()->route('admin.payments.receipt', $payment)
                             ->with('success', "Pembayaran berhasil dicatat. No. Kwitansi: {$payment->receipt_number}.");
        });
    }

    public function verify(Payment $payment)
    {
        if ($payment->status === 'verified') {
            return back()->with('info', 'Pembayaran ini sudah terverifikasi sebelumnya.');
        }

        return DB::transaction(function () use ($payment) {
            $payment->load(['cashBill.schoolClass', 'student']);

            if (! $payment->receipt_number) {
                $payment->receipt_number = Payment::generateReceiptNumber();
            }

            $payment->status      = 'verified';
            $payment->verified_by = Auth::id();
            $payment->save();

            // Check if cash_transaction already exists, else create it
            if (! $payment->cashTransaction) {
                CashTransaction::create([
                    'transaction_number' => CashTransaction::generateTransactionNumber('in'),
                    'class_id'           => $payment->cashBill->class_id,
                    'category_id'        => $payment->cashBill->category_id,
                    'type'               => 'in',
                    'title'              => "Pembayaran {$payment->cashBill->title} - {$payment->student->name} ({$payment->cashBill->schoolClass->class_name})",
                    'amount'             => $payment->amount_paid,
                    'transaction_date'   => $payment->payment_date,
                    'payment_id'         => $payment->id,
                    'proof_file'         => $payment->proof_file,
                    'notes'              => "Verifikasi pembayaran online. Kwitansi: {$payment->receipt_number}.",
                    'created_by'         => Auth::id(),
                ]);
            }

            return back()->with('success', "Pembayaran {$payment->receipt_number} berhasil diverifikasi.");
        });
    }

    public function reject(Request $request, Payment $payment)
    {
        return DB::transaction(function () use ($request, $payment) {
            $payment->status = 'rejected';
            $payment->notes  = ($payment->notes ? $payment->notes . "\n" : '') . 'Alasan Ditolak: ' . $request->input('reason', 'Bukti tidak valid');
            $payment->save();

            // Delete associated cash transaction if any
            if ($payment->cashTransaction) {
                $payment->cashTransaction->delete();
            }

            return back()->with('success', 'Pembayaran ditolak.');
        });
    }

    public function receipt(Payment $payment)
    {
        $payment->load(['cashBill.schoolClass', 'cashBill.category', 'student', 'verifier']);

        // Calculate student's overall status on this bill up to and including this payment
        $bill = $payment->cashBill;
        $statusInfo = $bill->studentPaymentStatus($payment->student_id);

        $terbilang = Terbilang::rupiah($payment->amount_paid);

        return view('admin.payments.receipt', compact('payment', 'bill', 'statusInfo', 'terbilang'));
    }

    /**
     * AJAX helper to query bill balance for student.
     */
    public function getBillStatus(Request $request)
    {
        $billId    = $request->query('bill_id');
        $studentId = $request->query('student_id');

        if (! $billId || ! $studentId) {
            return response()->json(['error' => 'Missing parameter'], 400);
        }

        $bill = CashBill::find($billId);
        if (! $bill) {
            return response()->json(['error' => 'Bill not found'], 404);
        }

        $status = $bill->studentPaymentStatus((int) $studentId);

        return response()->json([
            'bill_title'  => $bill->title,
            'bill_amount' => (float) $bill->amount,
            'paid'        => $status['paid'],
            'remaining'   => $status['remaining'],
            'status'      => $status['status'],
        ]);
    }
}
