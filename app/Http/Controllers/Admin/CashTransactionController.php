<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCashTransactionRequest;
use App\Http\Requests\Admin\UpdateCashTransactionRequest;
use App\Models\CashCategory;
use App\Models\CashTransaction;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CashTransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isBendahara = $user->isBendaharaKelas();
        $scopedClassId = $isBendahara ? $user->class_id : $request->input('class_id');

        $query = CashTransaction::with(['category', 'schoolClass', 'creator', 'payment.student']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('transaction_number', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($scopedClassId) {
            $query->where('class_id', $scopedClassId);
        }

        if ($startDate = $request->input('start_date')) {
            $query->whereDate('transaction_date', '>=', $startDate);
        }

        if ($endDate = $request->input('end_date')) {
            $query->whereDate('transaction_date', '<=', $endDate);
        }

        $transactions = $query->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $incomeStats = CashTransaction::income();
        $expenseStats = CashTransaction::expense();
        if ($scopedClassId) {
            $incomeStats->where('class_id', $scopedClassId);
            $expenseStats->where('class_id', $scopedClassId);
        }
        $totalIncome = (float) $incomeStats->sum('amount');
        $totalExpense = (float) $expenseStats->sum('amount');
        $balance = $totalIncome - $totalExpense;

        $categories = CashCategory::orderBy('type')->orderBy('name')->get();
        $classes = $isBendahara
            ? SchoolClass::where('id', $user->class_id)->get()
            : SchoolClass::orderBy('class_name')->get();

        return view('admin.transactions.index', compact(
            'transactions',
            'totalIncome',
            'totalExpense',
            'balance',
            'categories',
            'classes'
        ));
    }

    public function create()
    {
        $user = Auth::user();
        $isBendahara = $user->isBendaharaKelas();

        $incomeCategories = CashCategory::where('type', 'in')->orderBy('name')->get();
        $expenseCategories = CashCategory::where('type', 'out')->orderBy('name')->get();
        $classes = $isBendahara
            ? SchoolClass::where('id', $user->class_id)->get()
            : SchoolClass::orderBy('class_name')->get();

        $scopedClassId = $isBendahara ? $user->class_id : null;
        $incomeSum = CashTransaction::income()
            ->when($scopedClassId, fn ($q, $cid) => $q->where('class_id', $cid))
            ->sum('amount');
        $expenseSum = CashTransaction::expense()
            ->when($scopedClassId, fn ($q, $cid) => $q->where('class_id', $cid))
            ->sum('amount');
        $currentBalance = (float) $incomeSum - (float) $expenseSum;

        return view('admin.transactions.create', compact('incomeCategories', 'expenseCategories', 'classes', 'currentBalance'));
    }

    public function store(StoreCashTransactionRequest $request)
    {
        $user = Auth::user();
        $data = $request->validated();

        if ($user->isBendaharaKelas()) {
            $data['class_id'] = $user->class_id;
        }

        // Validasi saldo kas untuk pengeluaran (mencegah kas negatif)
        if ($data['type'] === 'out') {
            $incomeSum = CashTransaction::income()
                ->when($data['class_id'] ?? null, fn ($q, $cid) => $q->where('class_id', $cid))
                ->sum('amount');
            $expenseSum = CashTransaction::expense()
                ->when($data['class_id'] ?? null, fn ($q, $cid) => $q->where('class_id', $cid))
                ->sum('amount');
            $availableBalance = (float) $incomeSum - (float) $expenseSum;

            if ((float) $data['amount'] > $availableBalance) {
                return back()->withInput()->with(
                    'error',
                    'Saldo kas tidak mencukupi untuk pengeluaran ini. Saldo saat ini: Rp '.number_format($availableBalance, 0, ',', '.')
                );
            }
        }

        return DB::transaction(function () use ($request, $data) {
            if ($request->hasFile('proof_file')) {
                $data['proof_file'] = $request->file('proof_file')->store('transactions', 'public');
            }

            $data['transaction_number'] = CashTransaction::generateTransactionNumber($data['type']);
            $data['created_by'] = Auth::id();

            $transaction = CashTransaction::create($data);

            return redirect()->route('admin.transactions.index')
                ->with('success', "Transaksi {$transaction->transaction_number} berhasil dicatat.");
        });
    }

    public function show(CashTransaction $transaction)
    {
        if (Auth::user()->isBendaharaKelas() && $transaction->class_id !== Auth::user()->class_id) {
            abort(403, 'Anda tidak memiliki akses ke transaksi kelas lain.');
        }

        $transaction->load(['category', 'schoolClass', 'creator', 'payment.student', 'payment.cashBill']);

        return view('admin.transactions.show', compact('transaction'));
    }

    public function edit(CashTransaction $transaction)
    {
        if (Auth::user()->isBendaharaKelas() && $transaction->class_id !== Auth::user()->class_id) {
            abort(403, 'Anda tidak memiliki akses ke transaksi kelas lain.');
        }

        if ($transaction->payment_id) {
            return redirect()->route('admin.transactions.show', $transaction)
                ->with('error', 'Transaksi ini terhubung dengan pembayaran iuran siswa dan tidak dapat diedit langsung dari modul ini.');
        }

        $incomeCategories = CashCategory::where('type', 'in')->orderBy('name')->get();
        $expenseCategories = CashCategory::where('type', 'out')->orderBy('name')->get();
        $classes = Auth::user()->isBendaharaKelas()
            ? SchoolClass::where('id', Auth::user()->class_id)->get()
            : SchoolClass::orderBy('class_name')->get();

        return view('admin.transactions.edit', compact('transaction', 'incomeCategories', 'expenseCategories', 'classes'));
    }

    public function update(UpdateCashTransactionRequest $request, CashTransaction $transaction)
    {
        if (Auth::user()->isBendaharaKelas() && $transaction->class_id !== Auth::user()->class_id) {
            abort(403, 'Anda tidak memiliki akses ke transaksi kelas lain.');
        }

        if ($transaction->payment_id) {
            return redirect()->route('admin.transactions.show', $transaction)
                ->with('error', 'Transaksi yang berasal dari pembayaran siswa tidak dapat diubah di sini.');
        }

        $data = $request->validated();
        if (Auth::user()->isBendaharaKelas()) {
            $data['class_id'] = Auth::user()->class_id;
        }

        // Validasi saldo kas untuk pengeluaran (mencegah kas negatif)
        if ($data['type'] === 'out') {
            $targetClassId = $data['class_id'] ?? null;
            $incomeSum = CashTransaction::income()
                ->when($targetClassId, fn ($q, $cid) => $q->where('class_id', $cid))
                ->sum('amount');
            $expenseSum = CashTransaction::expense()
                ->when($targetClassId, fn ($q, $cid) => $q->where('class_id', $cid))
                ->where('id', '!=', $transaction->id)
                ->sum('amount');
            $availableBalance = (float) $incomeSum - (float) $expenseSum;

            if ((float) $data['amount'] > $availableBalance) {
                return back()->withInput()->with(
                    'error',
                    'Saldo kas tidak mencukupi untuk pengeluaran ini. Saldo saat ini: Rp '.number_format($availableBalance, 0, ',', '.')
                );
            }
        }

        // ponytail: prevent reducing income amount if funds are already exhausted by expenses
        if ($transaction->type === 'in' && $data['type'] === 'in') {
            $delta = (float) $transaction->amount - (float) $data['amount'];
            if ($delta > 0) {
                $targetClassId = $data['class_id'] ?? $transaction->class_id;
                $incomeSum = CashTransaction::income()
                    ->when($targetClassId, fn ($q, $cid) => $q->where('class_id', $cid))
                    ->sum('amount');
                $expenseSum = CashTransaction::expense()
                    ->when($targetClassId, fn ($q, $cid) => $q->where('class_id', $cid))
                    ->sum('amount');
                $availableBalance = (float) $incomeSum - (float) $expenseSum;

                if ($availableBalance - $delta < 0) {
                    return back()->withInput()->with(
                        'error',
                        'Nominal pemasukan tidak dapat dikurangi menjadi Rp '.number_format($data['amount'], 0, ',', '.').' karena dana sudah terpakai oleh pengeluaran lain.'
                    );
                }
            }
        }

        return DB::transaction(function () use ($request, $transaction, $data) {
            if ($request->hasFile('proof_file')) {
                if ($transaction->proof_file && Storage::disk('public')->exists($transaction->proof_file)) {
                    Storage::disk('public')->delete($transaction->proof_file);
                }
                $data['proof_file'] = $request->file('proof_file')->store('transactions', 'public');
            }

            $transaction->update($data);

            return redirect()->route('admin.transactions.index')
                ->with('success', "Transaksi {$transaction->transaction_number} berhasil diperbarui.");
        });
    }

    public function destroy(CashTransaction $transaction)
    {
        if (Auth::user()->isBendaharaKelas() && $transaction->class_id !== Auth::user()->class_id) {
            abort(403, 'Anda tidak memiliki akses ke transaksi kelas lain.');
        }

        if ($transaction->payment_id) {
            return back()->with('error', 'Transaksi ini terhubung dengan pembayaran iuran siswa. Batalkan pembayaran terlebih dahulu dari modul kasir/pembayaran.');
        }

        // ponytail: prevent deleting income transaction if spent funds would make cash balance negative
        if ($transaction->type === 'in') {
            $classId = $transaction->class_id;
            $incomeSum = CashTransaction::income()
                ->when($classId, fn ($q, $cid) => $q->where('class_id', $cid))
                ->sum('amount');
            $expenseSum = CashTransaction::expense()
                ->when($classId, fn ($q, $cid) => $q->where('class_id', $cid))
                ->sum('amount');
            $availableBalance = (float) $incomeSum - (float) $expenseSum;

            if ($availableBalance - (float) $transaction->amount < 0) {
                return back()->with(
                    'error',
                    'Transaksi pemasukan ini tidak dapat dihapus karena dana sebesar Rp '.
                    number_format($transaction->amount, 0, ',', '.').
                    ' sudah terpakai untuk pengeluaran kas (sisa saldo kas saat ini hanya Rp '.
                    number_format($availableBalance, 0, ',', '.').').'
                );
            }
        }

        return DB::transaction(function () use ($transaction) {
            if ($transaction->proof_file && Storage::disk('public')->exists($transaction->proof_file)) {
                Storage::disk('public')->delete($transaction->proof_file);
            }

            $transaction->delete();

            return redirect()->route('admin.transactions.index')
                ->with('success', 'Transaksi berhasil dihapus.');
        });
    }
}
