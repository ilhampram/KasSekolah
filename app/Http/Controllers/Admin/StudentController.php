<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStudentRequest;
use App\Http\Requests\Admin\UpdateStudentRequest;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isBendahara = $user->isBendaharaKelas();

        $query = User::where('role', 'siswa')->with('class');

        if ($isBendahara) {
            $query->where('class_id', $user->class_id);
        } elseif ($classId = $request->input('class_id')) {
            $query->where('class_id', $classId);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $classes = $isBendahara
            ? SchoolClass::where('id', $user->class_id)->get()
            : SchoolClass::orderBy('class_name')->get();

        return view('admin.students.index', compact('students', 'classes'));
    }

    public function create()
    {
        $user = auth()->user();
        $classes = $user->isBendaharaKelas()
            ? SchoolClass::where('id', $user->class_id)->get()
            : SchoolClass::orderBy('class_name')->get();

        return view('admin.students.create', compact('classes'));
    }

    public function store(StoreStudentRequest $request)
    {
        $classId = auth()->user()->isBendaharaKelas() ? auth()->user()->class_id : $request->class_id;

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'nisn' => $request->nisn,
            'class_id' => $classId,
            'password' => Hash::make($request->password),
            'role' => 'siswa',
        ]);

        return redirect()->route('admin.students.index')
            ->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function edit(User $student)
    {
        if (auth()->user()->isBendaharaKelas() && $student->class_id !== auth()->user()->class_id) {
            abort(403, 'Anda tidak memiliki akses ke data siswa kelas lain.');
        }

        $classes = auth()->user()->isBendaharaKelas()
            ? SchoolClass::where('id', auth()->user()->class_id)->get()
            : SchoolClass::orderBy('class_name')->get();

        return view('admin.students.edit', compact('student', 'classes'));
    }

    public function update(UpdateStudentRequest $request, User $student)
    {
        if (auth()->user()->isBendaharaKelas() && $student->class_id !== auth()->user()->class_id) {
            abort(403, 'Anda tidak memiliki akses ke data siswa kelas lain.');
        }

        $data = $request->validated();
        if (auth()->user()->isBendaharaKelas()) {
            $data['class_id'] = auth()->user()->class_id;
        }

        $student->update($data);

        return redirect()->route('admin.students.index')
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(User $student)
    {
        if (auth()->user()->isBendaharaKelas()) {
            abort(403, 'Bendahara kelas tidak memiliki izin menghapus data siswa.');
        }

        $student->delete();

        return redirect()->route('admin.students.index')
            ->with('success', 'Siswa berhasil dihapus.');
    }

    public function resetPassword(User $student)
    {
        if (auth()->user()->isBendaharaKelas() && $student->class_id !== auth()->user()->class_id) {
            abort(403, 'Anda tidak memiliki akses ke data siswa kelas lain.');
        }

        $student->update([
            'password' => Hash::make('password'),
        ]);

        return back()->with('success', "Password siswa {$student->name} berhasil direset ke default (password).");
    }

    public function downloadTemplate(Request $request)
    {
        $format = $request->query('format', 'xlsx');

        if ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="template_siswa.csv"',
            ];

            return response()->stream(function () {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Nama Siswa', 'NISN', 'Email']);
                fputcsv($handle, ['Ahmad Dani', '0012345010', 'dani@siswa.test']);
                fputcsv($handle, ['Siti Nurhaliza', '0012345011', 'siti@siswa.test']);
                fclose($handle);
            }, 200, $headers);
        }

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Siswa');

        $sheet->setCellValue('A1', 'Nama Siswa');
        $sheet->setCellValue('B1', 'NISN');
        $sheet->setCellValue('C1', 'Email');

        $sheet->setCellValue('A2', 'Ahmad Dani');
        $sheet->setCellValue('B2', '0012345010');
        $sheet->setCellValue('C2', 'dani@siswa.test');

        $sheet->setCellValue('A3', 'Siti Nurhaliza');
        $sheet->setCellValue('B3', '0012345011');
        $sheet->setCellValue('C3', 'siti@siswa.test');

        foreach (['A', 'B', 'C'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 'template_siswa.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function import(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:5120'],
        ];

        if (! $user->isBendaharaKelas()) {
            $rules['class_id'] = ['required', 'exists:classes,id'];
        }

        $request->validate($rules, [
            'file.required' => 'Pilih file Excel atau CSV untuk diimpor.',
            'file.mimes' => 'Format file harus berupa Excel (.xlsx, .xls) atau CSV (.csv).',
            'class_id.required' => 'Pilih kelas tujuan untuk siswa yang diimpor.',
            'class_id.exists' => 'Kelas yang dipilih tidak valid.',
        ]);

        $targetClassId = $user->isBendaharaKelas() ? $user->class_id : (int) $request->class_id;

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $rows = [];

        if (in_array($extension, ['xlsx', 'xls'])) {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
        } else {
            $handle = fopen($file->getRealPath(), 'r');
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                if (count($data) === 1 && str_contains($data[0], ';')) {
                    $data = explode(';', $data[0]);
                }
                $rows[] = $data;
            }
            fclose($handle);
        }

        $imported = 0;
        $skipped = 0;
        $isFirstRow = true;

        foreach ($rows as $row) {
            if ($isFirstRow) {
                $isFirstRow = false;
                if (isset($row[0]) && in_array(strtolower(trim((string) $row[0])), ['nama', 'nama siswa', 'name'])) {
                    continue;
                }
            }

            $name = trim((string) ($row[0] ?? ''));
            $nisn = trim((string) ($row[1] ?? ''));
            $email = trim((string) ($row[2] ?? ''));

            if ($name === '') {
                continue;
            }

            if ($email === '') {
                $prefix = $nisn !== '' ? $nisn : Str::slug($name, '');
                $email = $prefix.'@siswa.test';
            }

            $exists = User::where('email', $email)
                ->when($nisn !== '', fn ($q) => $q->orWhere('nisn', $nisn))
                ->exists();

            if ($exists) {
                $skipped++;

                continue;
            }

            User::create([
                'name' => $name,
                'email' => $email,
                'nisn' => $nisn !== '' ? $nisn : null,
                'class_id' => $targetClassId,
                'password' => Hash::make('password'),
                'role' => 'siswa',
            ]);

            $imported++;
        }

        $msg = "Berhasil mengimpor {$imported} siswa.";
        if ($skipped > 0) {
            $msg .= " ({$skipped} siswa dilewati karena email/NISN sudah terdaftar).";
        }

        return redirect()->route('admin.students.index')->with('success', $msg);
    }
}
