<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;


class TrialBalanceMonthwiseExcelExport implements FromArray, WithTitle, WithStyles,  WithColumnFormatting
{
   protected $months, $data, $compname, $startDate, $endDate;

   public function __construct($months, $data, $compname, $startDate, $endDate)
    {
        $this->months = $months;
        $this->data = $data;
        $this->compname = $compname;
        $this->startDate = $startDate;
        $this->endDate = $endDate;

    }

    public function array(): array
    {
        $rows = [];

        // First rows: Company, Report Title, Blank
        $rows[] = [$this->compname];
        $rows[] = ["Trial Balance Month-wise", "From: ".date('d-M-Y', strtotime($this->startDate)), "To: ".date('d-M-Y', strtotime($this->endDate))];
        $rows[] = []; // Blank row

        // Fourth row: Column headings
        $headers = ['GL ID', 'Category', 'Account Code', 'Account Name', 'Opening Debit', 'Opening Credit'];
        foreach ($this->months as $month) {
            $headers[] = "Debit " . \Carbon\Carbon::parse($month.'-01')->format('M Y');
            $headers[] = "Credit " . \Carbon\Carbon::parse($month.'-01')->format('M Y');
        }
        $headers[] = 'Ending Debit';
        $headers[] = 'Ending Credit';
        $rows[] = $headers;

        // Data rows
        foreach ($this->data as $acc) {
            $row = [
                $acc['info']->gl_id,
                $acc['info']->category_name,
                $acc['info']->party_cd,
                $acc['info']->party_nm,
                $acc['op_dr'],
                $acc['op_cr']
            ];

            foreach ($this->months as $month) {
                $row[] = $acc['months'][$month]['tr_dr'];
                $row[] = $acc['months'][$month]['tr_cr'];
            }

            $row[] = $acc['bl_dr'];
            $row[] = $acc['bl_cr'];

            $rows[] = $row;
        }

        // Add Grand Total row
        $grand_op_dr = $grand_op_cr = $grand_bl_dr = $grand_bl_cr = 0;
        $monthTotals = [];
        foreach($this->months as $month) {
            $monthTotals[$month]['tr_dr'] = 0;
            $monthTotals[$month]['tr_cr'] = 0;
        }

        foreach ($this->data as $acc) {
            $grand_op_dr += $acc['op_dr'];
            $grand_op_cr += $acc['op_cr'];
            $grand_bl_dr += $acc['bl_dr'];
            $grand_bl_cr += $acc['bl_cr'];
            foreach($this->months as $month) {
                $monthTotals[$month]['tr_dr'] += $acc['months'][$month]['tr_dr'];
                $monthTotals[$month]['tr_cr'] += $acc['months'][$month]['tr_cr'];
            }
        }
        $grandRow = ['Grand Total', '', '', '', $grand_op_dr, $grand_op_cr];
        foreach ($this->months as $month) {
            $grandRow[] = $monthTotals[$month]['tr_dr'];
            $grandRow[] = $monthTotals[$month]['tr_cr'];
        }
        $grandRow[] = $grand_bl_dr;
        $grandRow[] = $grand_bl_cr;
        $rows[] = $grandRow;

        return $rows;
    }

    public function columnFormats(): array
    {
        $formats = [
            'E' => NumberFormat::FORMAT_NUMBER_00, // Opening Debit
            'F' => NumberFormat::FORMAT_NUMBER_00, // Opening Credit
        ];
        $col = 7; // 'G' for the 7th column
        foreach ($this->months as $month) {
            $formats[\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col)] = NumberFormat::FORMAT_NUMBER_00; // Debit
            $col++;
            $formats[\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col)] = NumberFormat::FORMAT_NUMBER_00; // Credit
            $col++;
        }
        $formats[\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col)] = NumberFormat::FORMAT_NUMBER_00; // Ending Debit
        $col++;
        $formats[\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col)] = NumberFormat::FORMAT_NUMBER_00; // Ending Credit

        return $formats;
    }

    public function title(): string
    {
        return 'Trial Balance Monthwise';
    }

    public function styles(Worksheet $sheet)
    {
        // Make header row bold
        $sheet->getStyle('A3:Z3')->getFont()->setBold(true);
        // Optional: Auto-size columns
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        return [];
    }
}


