<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStudentRequest;
use App\Http\Requests\Admin\UpdateStudentRequest;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'siswa')->with('class');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        if ($classId = $request->input('class_id')) {
            $query->where('class_id', $classId);
        }

        $students = $query->orderBy('name')
                          ->paginate(15)
                          ->withQueryString();

        $classes = SchoolClass::orderBy('class_name')->get();

        return view('admin.students.index', compact('students', 'classes'));
    }

    public function create()
    {
        $classes = SchoolClass::orderBy('class_name')->get();

        return view('admin.students.create', compact('classes'));
    }

    public function store(StoreStudentRequest $request)
    {
        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'nisn'     => $request->nisn,
            'class_id' => $request->class_id,
            'password' => Hash::make($request->password),
            'role'     => 'siswa',
        ]);

        return redirect()->route('admin.students.index')
                         ->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function edit(User $student)
    {
        $classes = SchoolClass::orderBy('class_name')->get();

        return view('admin.students.edit', compact('student', 'classes'));
    }

    public function update(UpdateStudentRequest $request, User $student)
    {
        $student->update($request->validated());

        return redirect()->route('admin.students.index')
                         ->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(User $student)
    {
        $student->delete();

        return redirect()->route('admin.students.index')
                         ->with('success', 'Siswa berhasil dihapus.');
    }

    public function resetPassword(User $student)
    {
        $student->update([
            'password' => Hash::make('password'),
        ]);

        return back()->with('success', "Password siswa {$student->name} berhasil direset ke default (password).");
    }
}
