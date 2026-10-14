<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\CashCategory;
use App\Models\CashTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransparencyController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $classId = $user->class_id;

        $scope = $request->input('scope', 'class'); // 'class' or 'all'

        $query = CashTransaction::with(['category', 'schoolClass', 'creator']);

        if ($scope === 'class' && $classId) {
            $query->where('class_id', $classId);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('transaction_number', 'like', "%{$search}%");
            });
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
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

        // Calculate summary for selected scope
        $statsQuery = CashTransaction::query();
        if ($scope === 'class' && $classId) {
            $statsQuery->where('class_id', $classId);
        }

        $totalIncome  = (float) (clone $statsQuery)->where('type', 'in')->sum('amount');
        $totalExpense = (float) (clone $statsQuery)->where('type', 'out')->sum('amount');
        $balance      = $totalIncome - $totalExpense;

        $categories = CashCategory::orderBy('type')->orderBy('name')->get();

        return view('siswa.transparency.index', compact(
            'user',
            'scope',
            'transactions',
            'totalIncome',
            'totalExpense',
            'balance',
            'categories'
        ));
    }
}
