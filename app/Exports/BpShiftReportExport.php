<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class BpShiftReportExport implements FromView
{
    protected $company;
    protected $rows;
    protected $shift;
    protected $startDate;
    protected $endDate;

    public function __construct($company, $rows, $shift, $startDate, $endDate)
    {
        $this->company = $company;
        $this->rows = $rows;
        $this->shift = $shift;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function view(): View
    {
        return view('nursing.reports.excel.bp_shift_report_excel', [
            'company'   => $this->company,
            'rows'      => $this->rows,
            'shift'     => $this->shift,
            'startDate' => $this->startDate,
            'endDate'   => $this->endDate,
        ]);
    }
}
