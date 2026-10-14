<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClassRequest;
use App\Http\Requests\Admin\UpdateClassRequest;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function index(Request $request)
    {
        $query = SchoolClass::withCount('students');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('class_name', 'like', "%{$search}%")
                  ->orWhere('homeroom_teacher', 'like', "%{$search}%")
                  ->orWhere('academic_year', 'like', "%{$search}%");
            });
        }

        if ($year = $request->input('year')) {
            $query->where('academic_year', $year);
        }

        $classes = $query->orderBy('academic_year', 'desc')
                         ->orderBy('class_name')
                         ->paginate(15)
                         ->withQueryString();

        $academicYears = SchoolClass::select('academic_year')
                            ->distinct()
                            ->orderByDesc('academic_year')
                            ->pluck('academic_year');

        return view('admin.classes.index', compact('classes', 'academicYears'));
    }

    public function create()
    {
        return view('admin.classes.create');
    }

    public function store(StoreClassRequest $request)
    {
        SchoolClass::create($request->validated());

        return redirect()->route('admin.classes.index')
                         ->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(SchoolClass $class)
    {
        return view('admin.classes.edit', compact('class'));
    }

    public function update(UpdateClassRequest $request, SchoolClass $class)
    {
        $class->update($request->validated());

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
