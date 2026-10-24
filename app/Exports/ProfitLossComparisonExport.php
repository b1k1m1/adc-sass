<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ProfitLossComparisonExport implements FromArray, WithTitle, WithStyles, WithColumnFormatting
{
     protected $months, $incomeRows, $expenseRows, $totalIncomeByMonth, $totalExpenseByMonth, $netProfitByMonth, $compname, $startDate, $endDate;

    // We'll store row numbers for borders
    protected $totalIncomeRowNum;
    protected $totalExpenseRowNum;
    protected $netProfitRowNum;

    public function __construct($months, $incomeRows, $expenseRows, $totalIncomeByMonth, $totalExpenseByMonth, $netProfitByMonth, $compname, $startDate, $endDate)
    {
        $this->months = array_values($months);
        $this->incomeRows = $incomeRows;
        $this->expenseRows = $expenseRows;
        $this->totalIncomeByMonth = $totalIncomeByMonth;
        $this->totalExpenseByMonth = $totalExpenseByMonth;
        $this->netProfitByMonth = $netProfitByMonth;
        $this->compname = $compname;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function array(): array
    {
        $rows = [];
        $colCount = 4 + count($this->months);

        // Title row
        $rows[] = array_merge([$this->compname], array_fill(1, $colCount-1, ''));
        $rows[] = array_merge([
            "Profit & Loss Comparison | Period: " .
            date('d-M-Y', strtotime($this->startDate)) .
            " to " .
            date('d-M-Y', strtotime($this->endDate))
        ], array_fill(1, $colCount-1, ''));
        $rows[] = array_fill(0, $colCount, ''); // Blank

        // Header row
        $headers = ['GL ID', 'Category', 'Code', 'Name'];
        foreach ($this->months as $month) {
            $headers[] = \Carbon\Carbon::parse($month.'-01')->format('M Y');
        }
        $rows[] = $headers;

        // Income section
        $rows[] = array_merge(['Income'], array_fill(1, $colCount-1, ''));
        foreach ($this->incomeRows as $row) {
            $r = [
                $row['gl_id'],
                $row['category'],
                $row['code'],
                $row['name'],
            ];
            foreach ($this->months as $month) {
                $r[] = isset($row['amounts'][$month]) ? $row['amounts'][$month] : 0;
            }
            $rows[] = $r;
        }
        // Save Total Income row index
        $this->totalIncomeRowNum = count($rows) + 1;
        $totIncRow = array_merge(['Total Income', '', '', ''], []);
        foreach ($this->months as $month) {
            $totIncRow[] = $this->totalIncomeByMonth[$month] ?? 0;
        }
        $rows[] = $totIncRow;

        // Expenses section
        $rows[] = array_merge(['Expenses'], array_fill(1, $colCount-1, ''));
        foreach ($this->expenseRows as $row) {
            $r = [
                $row['gl_id'],
                $row['category'],
                $row['code'],
                $row['name'],
            ];
            foreach ($this->months as $month) {
                $r[] = isset($row['amounts'][$month]) ? $row['amounts'][$month] : 0;
            }
            $rows[] = $r;
        }
        // Save Total Expenses row index
        $this->totalExpenseRowNum = count($rows) + 1;
        $totExpRow = array_merge(['Total Expenses', '', '', ''], []);
        foreach ($this->months as $month) {
            $totExpRow[] = $this->totalExpenseByMonth[$month] ?? 0;
        }
        $rows[] = $totExpRow;

        // Net Profit row
        $this->netProfitRowNum = count($rows) + 1;
        $netProfitRow = array_merge(['Net Profit/(Loss)', '', '', ''], []);
        foreach ($this->months as $month) {
            $netProfitRow[] = $this->netProfitByMonth[$month] ?? 0;
        }
        $rows[] = $netProfitRow;

        return $rows;
    }

    public function columnFormats(): array
    {
        $formats = [];
        // Amount columns always start at E (5th column)
        $col = 5; // Column E
        foreach ($this->months as $month) {
            $formats[\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col)] = NumberFormat::FORMAT_NUMBER_00;
            $col++;
        }
        return $formats;
    }

    public function title(): string
    {
        return 'P&L Comparison';
    }

    public function styles(Worksheet $sheet)
    {
        $colCount = 4 + count($this->months);
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colCount);

        // Merge title and subtitle, center and bold
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->getStyle("A1:{$lastCol}2")->getAlignment()->setHorizontal('center');
        $sheet->getStyle("A1:{$lastCol}2")->getFont()->setBold(true);

        // Bold header row
        $sheet->getStyle("A4:{$lastCol}4")->getFont()->setBold(true);

        // Auto-size all columns
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Column E (5) to last amount column
        $amountStartCol = 5;
        $amountEndCol = $colCount;

        // Borders: only on amount columns for totals
        foreach (range($amountStartCol, $amountEndCol) as $colIdx) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);

            // Total Income row
            $sheet->getStyle("{$colLetter}{$this->totalIncomeRowNum}")
                ->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $sheet->getStyle("{$colLetter}{$this->totalIncomeRowNum}")
                ->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE);

            // Total Expenses row
            $sheet->getStyle("{$colLetter}{$this->totalExpenseRowNum}")
                ->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $sheet->getStyle("{$colLetter}{$this->totalExpenseRowNum}")
                ->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE);

            // Net Profit row (optional, comment out if you don't want)
            $sheet->getStyle("{$colLetter}{$this->netProfitRowNum}")
                ->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $sheet->getStyle("{$colLetter}{$this->netProfitRowNum}")
                ->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE);
        }

        return [];
    }
}
