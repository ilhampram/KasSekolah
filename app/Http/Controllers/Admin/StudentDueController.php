<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStudentDueRequest;
use App\Http\Requests\Admin\UpdateStudentDueRequest;
use App\Models\CashBill;
use App\Models\CashCategory;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentDueController extends Controller
{
    public function index(Request $request)
    {
        $query = CashBill::with(['schoolClass', 'category', 'verifiedPayments'])->withCount('payments');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('schoolClass', function ($qc) use ($search) {
                      $qc->where('class_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($classId = $request->input('class_id')) {
            $query->where('class_id', $classId);
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        $bills = $query->orderByDesc('due_date')
                       ->orderByDesc('id')
                       ->paginate(15)
                       ->withQueryString();

        $classes    = SchoolClass::orderBy('class_name')->get();
        $categories = CashCategory::where('type', 'in')->orderBy('name')->get();

        return view('admin.dues.index', compact('bills', 'classes', 'categories'));
    }

    public function create()
    {
        $classes    = SchoolClass::withCount('students')->orderBy('class_name')->get();
        $categories = CashCategory::where('type', 'in')->orderBy('name')->get();

        return view('admin.dues.create', compact('classes', 'categories'));
    }

    public function store(StoreStudentDueRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();

            if ($data['target_type'] === 'all') {
                $classes = SchoolClass::all();
                $createdCount = 0;

                foreach ($classes as $class) {
                    CashBill::create([
                        'class_id'    => $class->id,
                        'category_id' => $data['category_id'],
                        'title'       => $data['title'],
                        'amount'      => $data['amount'],
                        'due_date'    => $data['due_date'],
                    ]);
                    $createdCount++;
                }

                return redirect()->route('admin.dues.index')
                                 ->with('success', "Berhasil membuat tagihan massal untuk {$createdCount} kelas.");
            }

            // Single class
            $bill = CashBill::create([
                'class_id'    => $data['class_id'],
                'category_id' => $data['category_id'],
                'title'       => $data['title'],
                'amount'      => $data['amount'],
                'due_date'    => $data['due_date'],
            ]);

            return redirect()->route('admin.dues.show', $bill)
                             ->with('success', "Tagihan massal untuk kelas {$bill->schoolClass->class_name} berhasil dibuat.");
        });
    }

    public function show(CashBill $due)
    {
        $due->load(['schoolClass.students', 'category', 'payments.student']);

        $students = $due->schoolClass->students()->orderBy('name')->get();

        // Calculate student payment statuses
        $studentStatuses = [];
        $totalCollected  = 0;
        $totalLunas      = 0;
        $totalCicilan    = 0;
        $totalBelum      = 0;

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
            'bill'            => $due,
            'students'        => $students,
            'studentStatuses' => $studentStatuses,
            'totalCollected'  => $totalCollected,
            'targetTotal'     => $targetTotal,
            'totalLunas'      => $totalLunas,
            'totalCicilan'    => $totalCicilan,
            'totalBelum'      => $totalBelum,
        ]);
    }

    public function edit(CashBill $due)
    {
        $categories = CashCategory::where('type', 'in')->orderBy('name')->get();

        return view('admin.dues.edit', ['bill' => $due, 'categories' => $categories]);
    }

    public function update(UpdateStudentDueRequest $request, CashBill $due)
    {
        return DB::transaction(function () use ($request, $due) {
            $due->update($request->validated());

            return redirect()->route('admin.dues.index')
                             ->with('success', 'Tagihan berhasil diperbarui.');
        });
    }

    public function destroy(CashBill $due)
    {
        if ($due->payments()->where('status', 'verified')->count() > 0) {
            return back()->with('error', 'Tagihan tidak dapat dihapus karena sudah ada siswa yang melakukan pembayaran terverifikasi.');
        }

        return DB::transaction(function () use ($due) {
            $due->delete();

            return redirect()->route('admin.dues.index')
                             ->with('success', 'Tagihan berhasil dihapus.');
        });
    }
}
