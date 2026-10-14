<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class StudentDuesRecapExport implements FromView, ShouldAutoSize
{
    protected $duesData;
    protected $summary;
    protected $filter;

    public function __construct(array $duesData, array $summary, array $filter)
    {
        $this->duesData = $duesData;
        $this->summary  = $summary;
        $this->filter   = $filter;
    }

    public function view(): View
    {
        return view('admin.reports.excel.student_dues', [
            'duesData' => $this->duesData,
            'summary'  => $this->summary,
            'filter'   => $this->filter,
        ]);
    }
}
