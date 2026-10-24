<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class QuarterlyAssessmentMembersExport implements FromView, ShouldAutoSize, WithTitle
{
    protected $company;
    protected $rows;
    protected $shift;
    protected $shiftLabel;
    protected $selectedMonth;
    protected $selectedMonthName;
    protected $quarterMonthNames;

    public function __construct(
        $company,
        $rows,
        $shift,
        $shiftLabel,
        $selectedMonth,
        $selectedMonthName,
        $quarterMonthNames
    ) {
        $this->company = $company;
        $this->rows = $rows;
        $this->shift = $shift;
        $this->shiftLabel = $shiftLabel;
        $this->selectedMonth = $selectedMonth;
        $this->selectedMonthName = $selectedMonthName;
        $this->quarterMonthNames = $quarterMonthNames;
    }

    public function view(): View
    {
        return view('nursing.reports.excel.quarterly_assessment_members_excel', [
            'company'           => $this->company,
            'rows'              => $this->rows,
            'shift'             => $this->shift,
            'shiftLabel'        => $this->shiftLabel,
            'selectedMonth'     => $this->selectedMonth,
            'selectedMonthName' => $this->selectedMonthName,
            'quarterMonthNames' => $this->quarterMonthNames,
        ]);
    }

    public function title(): string
    {
        return 'Quarterly Assessment';
    }
}
