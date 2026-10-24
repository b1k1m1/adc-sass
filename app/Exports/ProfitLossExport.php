<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProfitLossExport implements FromArray, WithTitle, WithStyles
{
    protected $vars;
    protected $incomeTotalRow;
    protected $incomeHeaderRow;
    protected $expenseTotalRow;
    protected $expenseHeaderRow;

    public function __construct($vars)
    {
        $this->vars = (array)$vars;
    }

    public function array(): array
    {
        $rows = [];
        // Row 1: Company name (will merge/center later)
        $rows[] = [$this->vars['compname'], '', '', '', ''];
        // Row 2: Subtitle (merge/center later)
        $rows[] = [
            "Profit & Loss Account | Period: " .
            date('d-M-Y', strtotime($this->vars['startDate'])) .
            " to " .
            date('d-M-Y', strtotime($this->vars['endDate'])),
            '', '', '', ''
        ];
        // Row 3: Blank
        $rows[] = ['', '', '', '', ''];

        // Row 4: Income section
        $rows[] = ['Income', '', '', '', ''];
        // Row 5: Income table header
        $this->incomeHeaderRow = count($rows) + 1;
        $rows[] = ['GL ID', 'Category', 'Code', 'Name', 'Amount'];
        // Income Data
        foreach ($this->vars['incomeRows'] as $row) {
            $rows[] = [
                $row['gl_id'], $row['category'], $row['code'], $row['name'], $row['amount']
            ];
        }
        // Record the income total row index (1-based for Excel) - Income Total Row.
        $this->incomeTotalRow = count($rows) + 1;
        $rows[] = ['', '', '', 'Total Income', $this->vars['totalIncome']];

        // Blank row
        $rows[] = ['', '', '', '', ''];
        // Expenses Section
        $rows[] = ['Expenses', '', '', '', ''];
        // Expense table header
        $this->expenseHeaderRow = count($rows) + 1;
        $rows[] = ['GL ID', 'Category', 'Code', 'Name', 'Amount'];
        // Expense Data
        foreach ($this->vars['expenseRows'] as $row) {
            $rows[] = [
                $row['gl_id'], $row['category'], $row['code'], $row['name'], $row['amount']
            ];
        }
         // Expense total row index
        $this->expenseTotalRow = count($rows) + 1;
        $rows[] = ['', '', '', 'Total Expenses', $this->vars['totalExpense']];

         // Blank row
        $rows[] = ['', '', '', '', ''];
        $rows[] = ['Net Profit/(Loss)', '', '', '', $this->vars['netProfit']];

        return $rows;
    }

    public function title(): string
    {
        return 'Profit & Loss';
    }

    public function styles(Worksheet $sheet)
    {
       // Merge/center company name and subtitle
        $sheet->mergeCells('A1:E1');
        $sheet->mergeCells('A2:E2');
        $sheet->getStyle('A1:E2')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A1:E2')->getFont()->setBold(true);

        // Bold Income and Expense headers only (row with column names)
        $sheet->getStyle('A'.$this->incomeHeaderRow.':E'.$this->incomeHeaderRow)->getFont()->setBold(true);
        $sheet->getStyle('A'.$this->expenseHeaderRow.':E'.$this->expenseHeaderRow)->getFont()->setBold(true);

        // Borders only in column E for totals
        // Income total (single above, double below)
        $sheet->getStyle('E'.$this->incomeTotalRow)
            ->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('E'.$this->incomeTotalRow)
            ->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE);

        // Expense total (single above, double below)
        $sheet->getStyle('E'.$this->expenseTotalRow)
            ->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('E'.$this->expenseTotalRow)
            ->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE);


        // Optionally: make Amount column two decimals and auto-size
        $sheet->getStyle("E1:E{$sheet->getHighestRow()}")
            ->getNumberFormat()->setFormatCode('#,##0.00');
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }
 }




