<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class BpMemberReportExport implements FromView
{
    protected $company;
    protected $rows;
    protected $avg;
    protected $member;
    protected $startDate;
    protected $endDate;

    public function __construct($company, $rows, $avg, $member, $startDate, $endDate)
    {
        $this->company = $company;
        $this->rows = $rows;
        $this->avg = $avg;
        $this->member = $member;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function view(): View
    {
        return view('nursing.reports.excel.bp_member_report_excel', [
            'company'   => $this->company,
            'rows'      => $this->rows,
            'avg'       => $this->avg,
            'member'    => $this->member,
            'startDate' => $this->startDate,
            'endDate'   => $this->endDate,
        ]);
    }
}
