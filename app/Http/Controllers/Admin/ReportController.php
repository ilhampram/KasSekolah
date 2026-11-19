<?php

namespace App\Http\Controllers\Admin;

use App\Exports\CashTransactionExport;
use App\Exports\StudentDuesRecapExport;
use App\Http\Controllers\Controller;
use App\Models\CashCategory;
use App\Models\CashTransaction;
use App\Models\SchoolClass;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $baseQuery = CashTransaction::with(['category', 'schoolClass', 'creator'])
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->whereNull('voided_at')
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc');

        if ($classId) {
            $baseQuery->where('class_id', $classId);
        }
        if ($categoryId) {
            $baseQuery->where('category_id', $categoryId);
        }
        if ($type && in_array($type, ['in', 'out'])) {
            $baseQuery->where('type', $type);
        }

        try {
            // Attempt SQL Window function (supported in MySQL 8.0+, MariaDB 10.2+, SQLite 3.25+)
            $windowQuery = clone $baseQuery;
            $windowQuery->select('*')
                ->selectRaw('SUM(CASE WHEN type = \'in\' THEN amount ELSE -amount END) OVER (ORDER BY transaction_date ASC, id ASC) as window_delta');

            $rawList = $windowQuery->get();
            $result = [];

            foreach ($rawList as $trx) {
                $result[] = [
                    'model' => $trx,
                    'running_balance' => $initialBalance + (float) $trx->window_delta,
                ];
            }

            return $result;
        } catch (\Throwable $e) {
            // ponytail: fallback to PHP iterative calculation if older MySQL/MariaDB lacks window functions
            $rawList = $baseQuery->get();
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
    }

    private function getStudentDuesData(?string $classId, ?string $categoryId): array
    {
        $query = DB::table('cash_bills')
            ->join('classes', 'classes.id', '=', 'cash_bills.class_id')
            ->join('users', function ($join) {
                $join->on('users.class_id', '=', 'classes.id')
                    ->where('users.role', '=', 'siswa');
            })
            ->leftJoin('cash_categories', 'cash_categories.id', '=', 'cash_bills.category_id')
            ->leftJoin('payments', function ($join) {
                $join->on('payments.cash_bill_id', '=', 'cash_bills.id')
                    ->on('payments.student_id', '=', 'users.id')
                    ->where('payments.status', '=', 'verified');
            })
            ->select([
                'users.name as student_name',
                'users.nisn',
                'classes.class_name',
                'cash_bills.title as bill_title',
                'cash_categories.name as category',
                'cash_bills.amount',
                DB::raw('COALESCE(SUM(payments.amount_paid), 0) as paid'),
            ])
            ->when($classId, fn ($q, $cid) => $q->where('cash_bills.class_id', $cid))
            ->when($categoryId, fn ($q, $catId) => $q->where('cash_bills.category_id', $catId))
            ->groupBy([
                'cash_bills.id',
                'cash_bills.title',
                'cash_bills.amount',
                'classes.class_name',
                'cash_categories.name',
                'users.id',
                'users.name',
                'users.nisn',
            ])
            ->orderBy('classes.class_name')
            ->orderBy('cash_bills.id')
            ->orderBy('users.name');

        $items = [];
        $totalTarget = 0;
        $totalPaid = 0;
        $totalDebt = 0;
        $lunasCount = 0;
        $belumCount = 0;

        foreach ($query->cursor() as $row) {
            $amount = (float) $row->amount;
            $paid = (float) $row->paid;
            $remaining = max(0, $amount - $paid);

            $status = 'belum_bayar';
            if ($paid >= $amount) {
                $status = 'lunas';
            } elseif ($paid > 0) {
                $status = 'cicilan';
            }

            $totalTarget += $amount;
            $totalPaid += $paid;
            $totalDebt += $remaining;

            if ($status === 'lunas') {
                $lunasCount++;
            } else {
                $belumCount++;
            }

            $items[] = [
                'student_name' => $row->student_name,
                'nisn' => $row->nisn ?? '-',
                'class_name' => $row->class_name ?? '-',
                'bill_title' => $row->bill_title,
                'category' => $row->category ?? '-',
                'amount' => $amount,
                'paid' => $paid,
                'remaining' => $remaining,
                'status' => $status,
            ];
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
