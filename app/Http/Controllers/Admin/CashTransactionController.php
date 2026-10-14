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

        if ($classId = $request->input('class_id')) {
            $query->where('class_id', $classId);
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

        $totalIncome  = CashTransaction::income()->sum('amount');
        $totalExpense = CashTransaction::expense()->sum('amount');
        $balance      = $totalIncome - $totalExpense;

        $categories = CashCategory::orderBy('type')->orderBy('name')->get();
        $classes    = SchoolClass::orderBy('class_name')->get();

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
        $incomeCategories  = CashCategory::where('type', 'in')->orderBy('name')->get();
        $expenseCategories = CashCategory::where('type', 'out')->orderBy('name')->get();
        $classes           = SchoolClass::orderBy('class_name')->get();

        return view('admin.transactions.create', compact('incomeCategories', 'expenseCategories', 'classes'));
    }

    public function store(StoreCashTransactionRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();

            if ($request->hasFile('proof_file')) {
                $data['proof_file'] = $request->file('proof_file')->store('transactions', 'public');
            }

            $data['transaction_number'] = CashTransaction::generateTransactionNumber($data['type']);
            $data['created_by']         = Auth::id();

            $transaction = CashTransaction::create($data);

            return redirect()->route('admin.transactions.index')
                             ->with('success', "Transaksi {$transaction->transaction_number} berhasil dicatat.");
        });
    }

    public function show(CashTransaction $transaction)
    {
        $transaction->load(['category', 'schoolClass', 'creator', 'payment.student', 'payment.cashBill']);

        return view('admin.transactions.show', compact('transaction'));
    }

    public function edit(CashTransaction $transaction)
    {
        if ($transaction->payment_id) {
            return redirect()->route('admin.transactions.show', $transaction)
                             ->with('error', 'Transaksi ini terhubung dengan pembayaran iuran siswa dan tidak dapat diedit langsung dari modul ini.');
        }

        $incomeCategories  = CashCategory::where('type', 'in')->orderBy('name')->get();
        $expenseCategories = CashCategory::where('type', 'out')->orderBy('name')->get();
        $classes           = SchoolClass::orderBy('class_name')->get();

        return view('admin.transactions.edit', compact('transaction', 'incomeCategories', 'expenseCategories', 'classes'));
    }

    public function update(UpdateCashTransactionRequest $request, CashTransaction $transaction)
    {
        if ($transaction->payment_id) {
            return redirect()->route('admin.transactions.show', $transaction)
                             ->with('error', 'Transaksi yang berasal dari pembayaran siswa tidak dapat diubah di sini.');
        }

        return DB::transaction(function () use ($request, $transaction) {
            $data = $request->validated();

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
        if ($transaction->payment_id) {
            return back()->with('error', 'Transaksi ini terhubung dengan pembayaran iuran siswa. Batalkan pembayaran terlebih dahulu dari modul kasir/pembayaran.');
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
