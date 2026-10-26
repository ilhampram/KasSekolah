<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStudentDueRequest;
use App\Http\Requests\Admin\UpdateStudentDueRequest;
use App\Models\CashBill;
use App\Models\CashCategory;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentDueController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isBendahara = $user->isBendaharaKelas();
        $scopedClassId = $isBendahara ? $user->class_id : $request->input('class_id');

        $query = CashBill::with(['schoolClass', 'category', 'verifiedPayments'])->withCount('payments');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('schoolClass', function ($qc) use ($search) {
                        $qc->where('class_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($scopedClassId) {
            $query->where('class_id', $scopedClassId);
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        $bills = $query->orderByDesc('due_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $classes = $isBendahara
            ? SchoolClass::where('id', $user->class_id)->get()
            : SchoolClass::orderBy('class_name')->get();
        $categories = CashCategory::where('type', 'in')->orderBy('name')->get();

        return view('admin.dues.index', compact('bills', 'classes', 'categories'));
    }

    public function create()
    {
        $user = auth()->user();
        $isBendahara = $user->isBendaharaKelas();

        $classes = $isBendahara
            ? SchoolClass::where('id', $user->class_id)->withCount('students')->get()
            : SchoolClass::withCount('students')->orderBy('class_name')->get();
        $categories = CashCategory::where('type', 'in')->orderBy('name')->get();

        return view('admin.dues.create', compact('classes', 'categories'));
    }

    public function store(StoreStudentDueRequest $request)
    {
        $data = $request->validated();
        if (auth()->user()->isBendaharaKelas()) {
            $data['target_type'] = 'single';
            $data['class_id'] = auth()->user()->class_id;
        }

        return DB::transaction(function () use ($data) {
            if ($data['target_type'] === 'all') {
                $classes = SchoolClass::all();
                $createdCount = 0;

                foreach ($classes as $class) {
                    CashBill::create([
                        'class_id' => $class->id,
                        'category_id' => $data['category_id'],
                        'title' => $data['title'],
                        'amount' => $data['amount'],
                        'due_date' => $data['due_date'],
                    ]);
                    $createdCount++;
                }

                return redirect()->route('admin.dues.index')
                    ->with('success', "Berhasil membuat tagihan massal untuk {$createdCount} kelas.");
            }

            // Single class
            $bill = CashBill::create([
                'class_id' => $data['class_id'],
                'category_id' => $data['category_id'],
                'title' => $data['title'],
                'amount' => $data['amount'],
                'due_date' => $data['due_date'],
            ]);

            return redirect()->route('admin.dues.show', $bill)
                ->with('success', "Tagihan massal untuk kelas {$bill->schoolClass->class_name} berhasil dibuat.");
        });
    }

    public function show(CashBill $due)
    {
        if (auth()->user()->isBendaharaKelas() && $due->class_id !== auth()->user()->class_id) {
            abort(403, 'Anda tidak memiliki akses ke tagihan kelas lain.');
        }

        $due->load(['schoolClass.students', 'category', 'payments.student']);

        $students = $due->schoolClass->students()->orderBy('name')->get();

        // Calculate student payment statuses
        $studentStatuses = [];
        $totalCollected = 0;
        $totalLunas = 0;
        $totalCicilan = 0;
        $totalBelum = 0;

        foreach ($students as $student) {
            $statusData = $due->studentPaymentStatus($student->id);
            $studentStatuses[$student->id] = $statusData;

            $totalCollected += $statusData['paid'];
            if ($statusData['status'] === 'lunas') {
                $totalLunas++;
            } elseif ($statusData['status'] === 'cicilan') {
                $totalCicilan++;
            } else {
                $totalBelum++;
            }
        }

        $targetTotal = (float) $due->amount * $students->count();

        return view('admin.dues.show', [
            'bill' => $due,
            'students' => $students,
            'studentStatuses' => $studentStatuses,
            'totalCollected' => $totalCollected,
            'targetTotal' => $targetTotal,
            'totalLunas' => $totalLunas,
            'totalCicilan' => $totalCicilan,
            'totalBelum' => $totalBelum,
        ]);
    }

    public function edit(CashBill $due)
    {
        if (auth()->user()->isBendaharaKelas() && $due->class_id !== auth()->user()->class_id) {
            abort(403, 'Anda tidak memiliki akses ke tagihan kelas lain.');
        }

        $categories = CashCategory::where('type', 'in')->orderBy('name')->get();

        return view('admin.dues.edit', ['bill' => $due, 'categories' => $categories]);
    }

    public function update(UpdateStudentDueRequest $request, CashBill $due)
    {
        if (auth()->user()->isBendaharaKelas() && $due->class_id !== auth()->user()->class_id) {
            abort(403, 'Anda tidak memiliki akses ke tagihan kelas lain.');
        }

        $data = $request->validated();
        $newAmount = (float) $data['amount'];

        // ponytail: prevent retroactive reduction below the maximum already paid by any student on this bill
        $maxPaidByAnyStudent = (float) $due->payments()
            ->where('status', 'verified')
            ->selectRaw('student_id, SUM(amount_paid) as total_paid')
            ->groupBy('student_id')
            ->pluck('total_paid')
            ->max();

        if ($maxPaidByAnyStudent > 0 && $newAmount < $maxPaidByAnyStudent) {
            return back()->withInput()->with(
                'error',
                'Nominal tagihan tidak dapat dikurangi menjadi Rp '.number_format($newAmount, 0, ',', '.').
                ' karena sudah ada siswa yang membayar sebesar Rp '.number_format($maxPaidByAnyStudent, 0, ',', '.').'.'
            );
        }

        return DB::transaction(function () use ($data, $due) {
            $due->update($data);

            return redirect()->route('admin.dues.index')
                ->with('success', 'Tagihan berhasil diperbarui.');
        });
    }

    public function destroy(CashBill $due)
    {
        if (auth()->user()->isBendaharaKelas() && $due->class_id !== auth()->user()->class_id) {
            abort(403, 'Anda tidak memiliki akses ke tagihan kelas lain.');
        }

        if ($due->payments()->exists()) {
            return back()->with('error', 'Tagihan tidak dapat dihapus karena sudah memiliki riwayat atau pengajuan pembayaran siswa.');
        }

        return DB::transaction(function () use ($due) {
            $due->delete();

            return redirect()->route('admin.dues.index')
                ->with('success', 'Tagihan berhasil dihapus.');
        });
    }
}
