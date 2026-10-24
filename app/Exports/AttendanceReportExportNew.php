<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceReportExportNew implements FromArray, WithTitle, WithStyles
{
    protected $data, $dates, $columnTotals, $grandTotal, $compname, $startDate, $endDate;

    public function __construct(
        array $data,
        array $dates,
        array $columnTotals,
        int $grandTotal,
        $compname = null,
        $startDate = null,
        $endDate = null
    ) {
        //$this->data = $data; // Each $row: participant_id, last_name, first_name, attendance => ['2024-07-01'=>1,...]
        // Remove rows where all attendance date values are blank, null, empty, or 0
        $this->data = array_values(array_filter($data, function ($row) use ($dates) {

            foreach ($dates as $d) {
                $val = $row['attendance'][$d] ?? '';

                // Keep this row if any date has value greater than 0
                if (is_numeric($val) && (float) $val > 0) {
                    return true;
                }
            }

            // Remove this row
            return false;
        }));
        $this->dates = $dates;
        $this->columnTotals = $columnTotals;
        $this->grandTotal = $grandTotal;
        $this->compname = $compname;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function array(): array
    {
        $colCount = 4 + count($this->dates); // 3 fixed + date columns + Row Total
        $rows = [];
        // 1. Title row (merged/centered)
        $rows[] = array_merge(
            [$this->compname . " | " .
                ($this->startDate ? date('m/d/Y', strtotime($this->startDate)) : '') .
                ($this->endDate ? " to " . date('m/d/Y', strtotime($this->endDate)) : '')
            ],
            array_fill(1, $colCount - 1, '')
        );
        // 2. Blank row
        $rows[] = array_fill(0, $colCount, '');

        // 3. Header row
        $headers = ['ID #', 'Last Name', 'First Name'];
        foreach ($this->dates as $d) {
            $headers[] = date('d', strtotime($d)); // "01", "02", etc.
        }
        $headers[] = "Total";
        $rows[] = $headers;

        // 4. Data rows
        foreach ($this->data as $row) {
            $r = [
                $row['ParticipantId'],
                $row['LastName'],
                $row['FirstName'],
            ];
            $rowTotal = 0;
            foreach ($this->dates as $d) {
                $val = isset($row['attendance'][$d]) ? $row['attendance'][$d] : '';
                $r[] = $val;
                if (is_numeric($val)) $rowTotal += $val;
            }
            $r[] = $rowTotal;
            $rows[] = $r;
        }

        // 5. Column totals row (starts at D)
        $totalRow = ['', '', 'Total'];
        foreach ($this->dates as $d) {
            $totalRow[] = $this->columnTotals[$d] ?? '';
        }
        $totalRow[] = $this->grandTotal;
        $rows[] = $totalRow;

        return $rows;
    }

    public function title(): string
    {
        return 'Attendance';
    }

    public function styles(Worksheet $sheet)
    {
        $colCount = 4 + count($this->dates);
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colCount);

        // Merge/center the title row
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->getStyle("A1:{$lastCol}1")->getAlignment()->setHorizontal('center');
        $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);

        // Bold header row (row 3)
        $sheet->getStyle("A3:{$lastCol}3")->getFont()->setBold(true);

        // Right align first three columns (A, B, C) for all rows
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle("A4:A{$highestRow}")->getAlignment()->setHorizontal('left');
        $sheet->getStyle("B4:B{$highestRow}")->getAlignment()->setHorizontal('left');
        $sheet->getStyle("C4:C{$highestRow}")->getAlignment()->setHorizontal('left');

        // Center all date columns and row totals (from D to last)
        $dateStartCol = 4; // 'D'
        $dateEndCol = $colCount; // includes Row Total
        $dateStartLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($dateStartCol);
        $dateEndLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($dateEndCol);
        $sheet->getStyle("{$dateStartLetter}3:{$dateEndLetter}{$highestRow}")
            ->getAlignment()->setHorizontal('center');

        // Bold and line for totals row (last row)
        $totalRowIdx = count($this->data) + 4;
        $sheet->getStyle("A{$totalRowIdx}:{$lastCol}{$totalRowIdx}")->getFont()->setBold(true);
        $sheet->getStyle("A{$totalRowIdx}:{$lastCol}{$totalRowIdx}")
            ->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle("A{$totalRowIdx}:{$lastCol}{$totalRowIdx}")
            ->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE);

        // Auto-size all columns
       // foreach (range('A', $lastCol) as $col) {
       //     $sheet->getColumnDimension($col)->setAutoSize(true);
       // }

        // Set default font for everything
       // $sheet->getStyle("A1:{$lastCol}{$sheet->getHighestRow()}")
       //     ->getFont()->setName('Arial')->setSize(10);

        // Compact: set all columns to 7 width, except Name (C) to 15
       // foreach (range('A', $lastCol) as $col) {
       //     $width = ($col == 'C') ? 15 : 4;
       //     $sheet->getColumnDimension($col)->setWidth($width);
       // }

       // Set fixed narrow width only for attendance date columns
        // Date columns start from D and go up to the column before Total
        $dateStartCol = 4; // D
        $totalColIndex = $colCount; // Last column is Total

        for ($i = $dateStartCol; $i < $totalColIndex; $i++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($col)->setWidth(7.5);
        }

        // Small font
        $sheet->getStyle("A1:{$lastCol}{$sheet->getHighestRow()}")
            ->getFont()->setName('Arial')->setSize(8);


            return [];
    }
}
