<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAcademicYearRequest;
use App\Http\Requests\Admin\UpdateAcademicYearRequest;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    public function index(Request $request)
    {
        $query = AcademicYear::withCount('classes');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('year', 'like', "%{$search}%")
                  ->orWhere('semester', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $academicYears = $query->orderByDesc('year')
                               ->orderBy('semester')
                               ->paginate(10)
                               ->withQueryString();

        return view('admin.academic-years.index', compact('academicYears'));
    }

    public function create()
    {
        return view('admin.academic-years.create');
    }

    public function store(StoreAcademicYearRequest $request)
    {
        $data = $request->validated();
        $isActive = $request->boolean('is_active');

        if ($isActive) {
            AcademicYear::where('is_active', true)->update(['is_active' => false]);
        }

        $data['is_active'] = $isActive;
        AcademicYear::create($data);

        return redirect()->route('admin.academic-years.index')
                         ->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }

    public function edit(AcademicYear $academicYear)
    {
        return view('admin.academic-years.edit', compact('academicYear'));
    }

    public function update(UpdateAcademicYearRequest $request, AcademicYear $academicYear)
    {
        $data = $request->validated();
        $isActive = $request->boolean('is_active');

        if ($isActive && ! $academicYear->is_active) {
            AcademicYear::where('id', '!=', $academicYear->id)->update(['is_active' => false]);
        }

        $data['is_active'] = $isActive;
        $academicYear->update($data);

        return redirect()->route('admin.academic-years.index')
                         ->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    public function activate(AcademicYear $academicYear)
    {
        $academicYear->activate();

        return redirect()->route('admin.academic-years.index')
                         ->with('success', "Tahun ajaran {$academicYear->full_name} berhasil diaktifkan.");
    }

    public function destroy(AcademicYear $academicYear)
    {
        if ($academicYear->is_active) {
            return back()->with('error', 'Tahun ajaran yang sedang aktif tidak dapat dihapus.');
        }

        if ($academicYear->classes()->count() > 0) {
            return back()->with('error', 'Tahun ajaran tidak dapat dihapus karena memiliki kelas terkait.');
        }

        $academicYear->delete();

        return redirect()->route('admin.academic-years.index')
                         ->with('success', 'Tahun ajaran berhasil dihapus.');
    }
}
