<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClassRequest;
use App\Http\Requests\Admin\UpdateClassRequest;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class ClassRoomController extends Controller
{
    public function index(Request $request)
    {
        $query = SchoolClass::with(['academicYear'])->withCount('students');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('class_name', 'like', "%{$search}%")
                  ->orWhere('homeroom_teacher', 'like', "%{$search}%");
            });
        }

        if ($yearId = $request->input('academic_year_id')) {
            $query->where('academic_year_id', $yearId);
        }

        $classes = $query->orderBy('class_name')
                         ->paginate(15)
                         ->withQueryString();

        $academicYears = AcademicYear::orderByDesc('year')
                            ->orderBy('semester')
                            ->get();

        return view('admin.classes.index', compact('classes', 'academicYears'));
    }

    public function create()
    {
        $academicYears = AcademicYear::orderByDesc('year')->orderBy('semester')->get();
        $activeYear = AcademicYear::getActive();

        return view('admin.classes.create', compact('academicYears', 'activeYear'));
    }

    public function store(StoreClassRequest $request)
    {
        $data = $request->validated();
        $academicYear = AcademicYear::find($data['academic_year_id']);
        $data['academic_year'] = $academicYear?->year;

        SchoolClass::create($data);

        return redirect()->route('admin.classes.index')
                         ->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(SchoolClass $class)
    {
        $academicYears = AcademicYear::orderByDesc('year')->orderBy('semester')->get();

        return view('admin.classes.edit', compact('class', 'academicYears'));
    }

    public function update(UpdateClassRequest $request, SchoolClass $class)
    {
        $data = $request->validated();
        $academicYear = AcademicYear::find($data['academic_year_id']);
        $data['academic_year'] = $academicYear?->year;

        $class->update($data);

        return redirect()->route('admin.classes.index')
                         ->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(SchoolClass $class)
    {
        if ($class->students()->count() > 0) {
            return back()->with('error', 'Kelas tidak bisa dihapus karena masih memiliki siswa.');
        }

        $class->delete();

        return redirect()->route('admin.classes.index')
                         ->with('success', 'Kelas berhasil dihapus.');
    }
}
