<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CashTransactionExport implements FromView, ShouldAutoSize
{
    protected $transactions;
    protected $summary;
    protected $filter;

    public function __construct($transactions, array $summary, array $filter)
    {
        $this->transactions = $transactions;
        $this->summary      = $summary;
        $this->filter       = $filter;
    }

    public function view(): View
    {
        return view('admin.reports.excel.cash_transactions', [
            'transactions' => $this->transactions,
            'summary'      => $this->summary,
            'filter'       => $this->filter,
        ]);
    }
}
