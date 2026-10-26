<?php

namespace App\Http\Controllers\Admin;

use App\Exports\CashTransactionExport;
use App\Exports\StudentDuesRecapExport;
use App\Http\Controllers\Controller;
use App\Models\CashBill;
use App\Models\CashCategory;
use App\Models\CashTransaction;
use App\Models\SchoolClass;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isBendahara = $user->isBendaharaKelas();

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $classId = $isBendahara ? (string) $user->class_id : $request->input('class_id');
        $categoryId = $request->input('category_id');
        $type = $request->input('type');
        $tab = $request->input('tab', 'transactions'); // 'transactions' or 'dues'

        // ─── 1. Rekapitulasi Kas (Saldo Awal, Mutasi, Saldo Akhir) ─
        $summary = $this->calculateSummary($startDate, $endDate, $classId, $categoryId);

        // ─── 2. Transaksi Kas dengan Saldo Berjalan ────────────────
        $transactions = $this->getTransactionsWithRunningBalance(
            $startDate,
            $endDate,
            $classId,
            $categoryId,
            $type,
            $summary['saldo_awal']
        );

        // ─── 3. Rekapitulasi Tagihan & Tunggakan Siswa ─────────────
        $duesData = $this->getStudentDuesData($classId, $categoryId);

        $classes = $isBendahara
            ? SchoolClass::where('id', $user->class_id)->get()
            : SchoolClass::orderBy('class_name')->get();
        $categories = CashCategory::orderBy('type')->orderBy('name')->get();

        return view('admin.reports.index', compact(
            'startDate',
            'endDate',
            'classId',
            'categoryId',
            'type',
            'tab',
            'summary',
            'transactions',
            'duesData',
            'classes',
            'categories'
        ));
    }

    public function exportExcel(Request $request)
    {
        $user = $request->user();
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $classId = $user->isBendaharaKelas() ? (string) $user->class_id : $request->input('class_id');
        $categoryId = $request->input('category_id');
        $type = $request->input('type');

        $summary = $this->calculateSummary($startDate, $endDate, $classId, $categoryId);
        $transactions = $this->getTransactionsWithRunningBalance(
            $startDate,
            $endDate,
            $classId,
            $categoryId,
            $type,
            $summary['saldo_awal']
        );

        $selectedClass = $classId ? SchoolClass::find($classId) : null;
        $selectedCat = $categoryId ? CashCategory::find($categoryId) : null;

        $filter = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'class_name' => $selectedClass?->class_name ?? 'Semua Kelas',
            'category' => $selectedCat?->name ?? 'Semua Kategori',
        ];

        $filename = 'Laporan_Kas_Sekolah_'.date('Ymd_His').'.xlsx';

        return Excel::download(new CashTransactionExport($transactions, $summary, $filter), $filename);
    }

    public function exportPdf(Request $request)
    {
        $user = $request->user();
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $classId = $user->isBendaharaKelas() ? (string) $user->class_id : $request->input('class_id');
        $categoryId = $request->input('category_id');
        $type = $request->input('type');

        $summary = $this->calculateSummary($startDate, $endDate, $classId, $categoryId);
        $transactions = $this->getTransactionsWithRunningBalance(
            $startDate,
            $endDate,
            $classId,
            $categoryId,
            $type,
            $summary['saldo_awal']
        );

        $selectedClass = $classId ? SchoolClass::find($classId) : null;
        $selectedCat = $categoryId ? CashCategory::find($categoryId) : null;

        $filter = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'class_name' => $selectedClass?->class_name ?? 'Semua Kelas',
            'category' => $selectedCat?->name ?? 'Semua Kategori',
        ];

        $pdf = Pdf::loadView('admin.reports.pdf.cash_transactions', [
            'transactions' => $transactions,
            'summary' => $summary,
            'filter' => $filter,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Laporan_Kas_Sekolah_'.date('Ymd_His').'.pdf');
    }

    public function exportDuesExcel(Request $request)
    {
        $user = $request->user();
        $classId = $user->isBendaharaKelas() ? (string) $user->class_id : $request->input('class_id');
        $categoryId = $request->input('category_id');

        $duesData = $this->getStudentDuesData($classId, $categoryId);

        $selectedClass = $classId ? SchoolClass::find($classId) : null;
        $selectedCat = $categoryId ? CashCategory::find($categoryId) : null;

        $filter = [
            'class_name' => $selectedClass?->class_name ?? 'Semua Kelas',
            'category' => $selectedCat?->name ?? 'Semua Kategori',
            'generated_at' => now()->format('d/m/Y H:i'),
        ];

        $filename = 'Rekap_Tunggakan_Siswa_'.date('Ymd_His').'.xlsx';

        return Excel::download(new StudentDuesRecapExport($duesData['items'], $duesData['summary'], $filter), $filename);
    }

    public function exportDuesPdf(Request $request)
    {
        $user = $request->user();
        $classId = $user->isBendaharaKelas() ? (string) $user->class_id : $request->input('class_id');
        $categoryId = $request->input('category_id');

        $duesData = $this->getStudentDuesData($classId, $categoryId);

        $selectedClass = $classId ? SchoolClass::find($classId) : null;
        $selectedCat = $categoryId ? CashCategory::find($categoryId) : null;

        $filter = [
            'class_name' => $selectedClass?->class_name ?? 'Semua Kelas',
            'category' => $selectedCat?->name ?? 'Semua Kategori',
            'generated_at' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('admin.reports.pdf.student_dues', [
            'duesData' => $duesData['items'],
            'summary' => $duesData['summary'],
            'filter' => $filter,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('Rekap_Tunggakan_Siswa_'.date('Ymd_His').'.pdf');
    }

    // ─── Calculation Helpers ───────────────────────────────────

    private function calculateSummary(string $startDate, string $endDate, ?string $classId, ?string $categoryId): array
    {
        // Saldo Awal: prior to start_date
        $priorInQuery = CashTransaction::income()->whereDate('transaction_date', '<', $startDate);
        $priorOutQuery = CashTransaction::expense()->whereDate('transaction_date', '<', $startDate);

        if ($classId) {
            $priorInQuery->where('class_id', $classId);
            $priorOutQuery->where('class_id', $classId);
        }
        if ($categoryId) {
            $priorInQuery->where('category_id', $categoryId);
            $priorOutQuery->where('category_id', $categoryId);
        }

        $priorIn = (float) $priorInQuery->sum('amount');
        $priorOut = (float) $priorOutQuery->sum('amount');
        $saldoAwal = $priorIn - $priorOut;

        // Mutasi Dalam Periode
        $inQuery = CashTransaction::income()->whereBetween('transaction_date', [$startDate, $endDate]);
        $outQuery = CashTransaction::expense()->whereBetween('transaction_date', [$startDate, $endDate]);

        if ($classId) {
            $inQuery->where('class_id', $classId);
            $outQuery->where('class_id', $classId);
        }
        if ($categoryId) {
            $inQuery->where('category_id', $categoryId);
            $outQuery->where('category_id', $categoryId);
        }

        $mutasiMasuk = (float) $inQuery->sum('amount');
        $mutasiKeluar = (float) $outQuery->sum('amount');
        $saldoAkhir = $saldoAwal + $mutasiMasuk - $mutasiKeluar;

        return [
            'saldo_awal' => $saldoAwal,
            'mutasi_masuk' => $mutasiMasuk,
            'mutasi_keluar' => $mutasiKeluar,
            'saldo_akhir' => $saldoAkhir,
        ];
    }

    private function getTransactionsWithRunningBalance(
        string $startDate,
        string $endDate,
        ?string $classId,
        ?string $categoryId,
        ?string $type,
        float $initialBalance
    ): array {
        $query = CashTransaction::with(['category', 'schoolClass', 'creator'])
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc');

        if ($classId) {
            $query->where('class_id', $classId);
        }
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        if ($type && in_array($type, ['in', 'out'])) {
            $query->where('type', $type);
        }

        $rawList = $query->get();

        $running = $initialBalance;
        $result = [];

        foreach ($rawList as $trx) {
            if ($trx->type === 'in') {
                $running += (float) $trx->amount;
            } else {
                $running -= (float) $trx->amount;
            }

            $result[] = [
                'model' => $trx,
                'running_balance' => $running,
            ];
        }

        return $result;
    }

    private function getStudentDuesData(?string $classId, ?string $categoryId): array
    {
        $billsQuery = CashBill::with(['schoolClass.students', 'category', 'payments']);

        if ($classId) {
            $billsQuery->where('class_id', $classId);
        }
        if ($categoryId) {
            $billsQuery->where('category_id', $categoryId);
        }

        $bills = $billsQuery->get();

        $items = [];
        $totalTarget = 0;
        $totalPaid = 0;
        $totalDebt = 0;
        $lunasCount = 0;
        $belumCount = 0;

        foreach ($bills as $bill) {
            $students = $bill->schoolClass ? $bill->schoolClass->students : collect();

            foreach ($students as $student) {
                $status = $bill->studentPaymentStatus($student->id);

                $totalTarget += (float) $bill->amount;
                $totalPaid += $status['paid'];
                $totalDebt += $status['remaining'];

                if ($status['status'] === 'lunas') {
                    $lunasCount++;
                } else {
                    $belumCount++;
                }

                $items[] = [
                    'student_name' => $student->name,
                    'nisn' => $student->nisn ?? '-',
                    'class_name' => $bill->schoolClass->class_name ?? '-',
                    'bill_title' => $bill->title,
                    'category' => $bill->category->name ?? '-',
                    'amount' => (float) $bill->amount,
                    'paid' => $status['paid'],
                    'remaining' => $status['remaining'],
                    'status' => $status['status'],
                ];
            }
        }

        return [
            'items' => $items,
            'summary' => [
                'total_target' => $totalTarget,
                'total_paid' => $totalPaid,
                'total_debt' => $totalDebt,
                'lunas_count' => $lunasCount,
                'belum_count' => $belumCount,
            ],
        ];
    }
}
