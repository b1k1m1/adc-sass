<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class MonitoringReportExport implements FromView
{
    protected $company;
    protected $shift;
    protected $rows;

    public function __construct($company, $shift, $rows)
    {
        $this->company = $company;
        $this->shift = $shift;
        $this->rows = $rows;
    }

    public function view(): View
    {
        return view('nursing.reports.excel.monitoring_report_excel', [
            'company' => $this->company,
            'shift'   => $this->shift,
            'rows'    => $this->rows,
        ]);
    }
}
