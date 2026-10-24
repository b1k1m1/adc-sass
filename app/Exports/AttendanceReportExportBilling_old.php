<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class AttendanceReportExportBilling extends DefaultValueBinder implements FromArray, WithTitle, WithStyles, WithColumnFormatting, WithCustomValueBinder
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
        $this->data = $data;
        $this->dates = $dates;
        $this->columnTotals = $columnTotals;
        $this->grandTotal = $grandTotal;
        $this->compname = $compname;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * IMPORTANT:
     * This forces selected columns to be stored as TEXT inside Excel.
     * This prevents values like 251390000000000 from showing as 2.5139E+14.
     */
    public function bindValue(Cell $cell, $value): bool
    {
        $column = $cell->getColumn();
        $row = $cell->getRow();

        // Data starts from row 4.
        // A = Part ID
        // F = Member ID / HMO ID
        // G = Auth No. / Prior Authorization
        // M = SS#
        if ($row >= 4 && in_array($column, ['A', 'F', 'G', 'M'])) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function array(): array
    {
        $colCount = 15 + count($this->dates);
        $rows = [];

        // 1. Title row
        $rows[] = array_merge(
            [
                $this->compname . " | " .
                ($this->startDate ? date('m/d/Y', strtotime($this->startDate)) : '') .
                ($this->endDate ? " to " . date('m/d/Y', strtotime($this->endDate)) : '')
            ],
            array_fill(1, $colCount - 1, '')
        );

        // 2. Blank row
        $rows[] = array_fill(0, $colCount, '');

        // 3. Header row
        $headers = [
            'Part ID',
            'Last Name',
            'First Name',
            'M',
            'Insurance',
            'Member ID',
            'Auth No.',
            'Auth Start Dt',
            'Auth End Dt',
            'Primary ICD',
            'Scondary ICD',
            'DOB',
            'SS#',
            'Sex'
        ];

        foreach ($this->dates as $d) {
            $headers[] = date('d', strtotime($d));
        }

        $headers[] = "Total";
        $rows[] = $headers;

        // 4. Data rows
        foreach ($this->data as $row) {
            $r = [
                (string) ($row['ParticipantId'] ?? ''),
                $row['LastName'] ?? '',
                $row['FirstName'] ?? '',
                $row['MiddleName'] ?? '',
                $row['HmoEnrolled'] ?? '',

                // These must stay as text
                (string) ($row['MedicaidHMOId'] ?? ''),
                (string) ($row['MedicaidPANo'] ?? ''),

                !empty($row['MedicaidPAStart'])
                    ? \Carbon\Carbon::parse($row['MedicaidPAStart'])->format('m/d/Y')
                    : '',

                !empty($row['MedicaidPAEnd'])
                    ? \Carbon\Carbon::parse($row['MedicaidPAEnd'])->format('m/d/Y')
                    : '',

                $row['PrimaryICD'] ?? '',
                $row['SecondaryICD'] ?? '',

                !empty($row['DOB'])
                    ? \Carbon\Carbon::parse($row['DOB'])->format('m/d/Y')
                    : '',

                // SS# must stay as text
                (string) ($row['SS#'] ?? ''),

                $row['Sex'] ?? '',
            ];

            $rowTotal = 0;

            foreach ($this->dates as $d) {
                $val = isset($row['attendance'][$d]) ? $row['attendance'][$d] : '';
                $r[] = $val;

                if (is_numeric($val)) {
                    $rowTotal += $val;
                }
            }

            $r[] = $rowTotal;
            $rows[] = $r;
        }

        // 5. Column totals row
        $totalRow = ['Total', '', '', '', '', '', '', '', '', '', '', '', '', ''];

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

    public function columnFormats(): array
    {
        return [
            // Text columns
            'A' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_TEXT,
            'G' => NumberFormat::FORMAT_TEXT,
            'M' => NumberFormat::FORMAT_TEXT,

            // Date columns
            'H' => 'mm/dd/yyyy',
            'I' => 'mm/dd/yyyy',
            'L' => 'mm/dd/yyyy',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $colCount = 15 + count($this->dates);
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colCount);

        // Merge/center the title row
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->getStyle("A1:{$lastCol}1")->getAlignment()->setHorizontal('center');
        $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);

        // Bold header row
        $sheet->getStyle("A3:{$lastCol}3")->getFont()->setBold(true);

        $highestRow = $sheet->getHighestRow();

        // Left align fixed columns
        $sheet->getStyle("A4:A{$highestRow}")->getAlignment()->setHorizontal('left');
        $sheet->getStyle("B4:B{$highestRow}")->getAlignment()->setHorizontal('left');
        $sheet->getStyle("C4:C{$highestRow}")->getAlignment()->setHorizontal('left');
        $sheet->getStyle("D4:D{$highestRow}")->getAlignment()->setHorizontal('left');
        $sheet->getStyle("E4:E{$highestRow}")->getAlignment()->setHorizontal('left');
        $sheet->getStyle("F4:F{$highestRow}")->getAlignment()->setHorizontal('left');
        $sheet->getStyle("G4:G{$highestRow}")->getAlignment()->setHorizontal('left');
        $sheet->getStyle("H4:H{$highestRow}")->getAlignment()->setHorizontal('left');
        $sheet->getStyle("I4:I{$highestRow}")->getAlignment()->setHorizontal('left');
        $sheet->getStyle("J4:J{$highestRow}")->getAlignment()->setHorizontal('left');
        $sheet->getStyle("K4:K{$highestRow}")->getAlignment()->setHorizontal('left');
        $sheet->getStyle("L4:L{$highestRow}")->getAlignment()->setHorizontal('left');
        $sheet->getStyle("M4:M{$highestRow}")->getAlignment()->setHorizontal('left');
        $sheet->getStyle("N4:N{$highestRow}")->getAlignment()->setHorizontal('left');

        // Apply text formatting again for safety
        $sheet->getStyle("A4:A{$highestRow}")
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_TEXT);

        $sheet->getStyle("F4:F{$highestRow}")
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_TEXT);

        $sheet->getStyle("G4:G{$highestRow}")
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_TEXT);

        $sheet->getStyle("M4:M{$highestRow}")
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_TEXT);

        // Center all date columns and row totals
        $dateStartCol = 15;
        $dateEndCol = $colCount;

        $dateStartLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($dateStartCol);
        $dateEndLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($dateEndCol);

        $sheet->getStyle("{$dateStartLetter}3:{$dateEndLetter}{$highestRow}")
            ->getAlignment()
            ->setHorizontal('center');

        // Bold and line for totals row
        $totalRowIdx = count($this->data) + 4;

        $sheet->getStyle("A{$totalRowIdx}:{$lastCol}{$totalRowIdx}")
            ->getFont()
            ->setBold(true);

        $sheet->getStyle("A{$totalRowIdx}:{$lastCol}{$totalRowIdx}")
            ->getBorders()
            ->getTop()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $sheet->getStyle("A{$totalRowIdx}:{$lastCol}{$totalRowIdx}")
            ->getBorders()
            ->getBottom()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE);

        // Auto-size all columns
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Font
        $sheet->getStyle("A1:{$lastCol}{$sheet->getHighestRow()}")
            ->getFont()
            ->setName('Arial')
            ->setSize(10);

        return [];
    }
}
