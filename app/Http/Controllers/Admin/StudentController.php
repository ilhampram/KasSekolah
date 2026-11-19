<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStudentRequest;
use App\Http\Requests\Admin\UpdateStudentRequest;
use App\Models\AuditLog;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $student = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'nisn' => $request->nisn,
            'class_id' => $classId,
            'password' => Hash::make($request->password),
            'role' => 'siswa',
        ]);
        AuditLog::record('student.created', $student, ['class_id' => $student->class_id]);

        return redirect()->route('admin.students.index')
            ->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function edit(User $student)
    {
        abort_if(! $student->isSiswa(), 404);

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
        abort_if(! $student->isSiswa(), 404);

        if (auth()->user()->isBendaharaKelas() && $student->class_id !== auth()->user()->class_id) {
            abort(403, 'Anda tidak memiliki akses ke data siswa kelas lain.');
        }

        $data = $request->validated();
        if (auth()->user()->isBendaharaKelas()) {
            $data['class_id'] = auth()->user()->class_id;
        }

        $before = $student->only(['name', 'email', 'nisn', 'class_id']);
        $student->update($data);
        AuditLog::record('student.updated', $student, [
            'before' => $before,
            'after' => $student->fresh()->only(array_keys($before)),
        ]);

        return redirect()->route('admin.students.index')
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(User $student)
    {
        if (auth()->user()->isBendaharaKelas()) {
            abort(403, 'Bendahara kelas tidak memiliki izin menghapus data siswa.');
        }

        abort_if(! $student->isSiswa(), 404);

        if ($student->payments()->exists()) {
            return back()->with('error', 'Siswa tidak dapat dihapus karena memiliki riwayat pembayaran kas.');
        }

        AuditLog::record('student.deleted', $student, [
            'name' => $student->name,
            'class_id' => $student->class_id,
        ]);
        $student->delete();

        return redirect()->route('admin.students.index')
            ->with('success', 'Siswa berhasil dihapus.');
    }

    public function resetPassword(User $student)
    {
        abort_if(! $student->isSiswa(), 404);

        if (auth()->user()->isBendaharaKelas() && $student->class_id !== auth()->user()->class_id) {
            abort(403, 'Anda tidak memiliki akses ke data siswa kelas lain.');
        }

        $temporaryPassword = Str::random(16);

        $student->update([
            'password' => Hash::make($temporaryPassword),
            'must_change_password' => true,
        ]);
        AuditLog::record('student.password_reset', $student, [
            'student_name' => $student->name,
            'nisn' => $student->nisn,
        ]);

        return back()->with('success', "Password sementara untuk {$student->name}: {$temporaryPassword}. Sampaikan langsung kepada siswa; password wajib diganti saat login.");
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

        // Pre-fetch existing emails and NISNs for in-memory O(1) checks and pre-hash password once
        $existingEmails = User::pluck('email')->flip()->toArray();
        $existingNisns = User::whereNotNull('nisn')->pluck('nisn')->flip()->toArray();
        $defaultPassword = Hash::make('password');

        DB::transaction(function () use (
            $rows,
            $targetClassId,
            $defaultPassword,
            &$existingEmails,
            &$existingNisns,
            &$imported,
            &$skipped,
            &$isFirstRow
        ) {
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

                if (isset($existingEmails[$email]) || ($nisn !== '' && isset($existingNisns[$nisn]))) {
                    $skipped++;

                    continue;
                }

                $existingEmails[$email] = true;
                if ($nisn !== '') {
                    $existingNisns[$nisn] = true;
                }

                User::create([
                    'name' => $name,
                    'email' => $email,
                    'nisn' => $nisn !== '' ? $nisn : null,
                    'class_id' => $targetClassId,
                    'password' => $defaultPassword,
                    'must_change_password' => true,
                    'role' => 'siswa',
                ]);

                $imported++;
            }
        });

        AuditLog::record('student.bulk_imported', null, [
            'class_id' => $targetClassId,
            'imported' => $imported,
            'skipped' => $skipped,
        ]);

        $msg = "Berhasil mengimpor {$imported} siswa.";
        if ($skipped > 0) {
            $msg .= " ({$skipped} siswa dilewati karena email/NISN sudah terdaftar).";
        }

        return redirect()->route('admin.students.index')->with('success', $msg);
    }
}
