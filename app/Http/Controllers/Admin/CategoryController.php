<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\CashCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = CashCategory::query();

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        $categories = $query->orderBy('type')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(StoreCategoryRequest $request)
    {
        CashCategory::create($request->validated());

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori kas berhasil ditambahkan.');
    }

    public function edit(CashCategory $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, CashCategory $category)
    {
        $category->update($request->validated());

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori kas berhasil diperbarui.');
    }

    public function destroy(CashCategory $category)
    {
        if ($category->cashBills()->exists() || $category->cashTransactions()->exists()) {
            return back()->with('error', 'Kategori tidak bisa dihapus karena masih digunakan dalam tagihan atau transaksi kas.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori kas berhasil dihapus.');
    }
}
