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
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

use Carbon\Carbon;

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
        $this->data = array_values(array_filter($data, function ($row) use ($dates) {

            foreach ($dates as $d) {
                $val = $row['attendance'][$d] ?? '';

                // Keep row if any date has numeric value greater than 0
                if (is_numeric($val) && (float) $val > 0) {
                    return true;
                }
            }

            // Remove row if all dates are blank, null, empty, or 0
            return false;
        }));
        $this->dates = $dates;
        $this->columnTotals = $columnTotals;
        $this->grandTotal = $grandTotal;
        $this->compname = $compname;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Force selected columns to TEXT.
     * Header now starts at row 1.
     * Data starts from row 2.
     */
    public function bindValue(Cell $cell, $value): bool
    {
        $column = $cell->getColumn();
        $row = $cell->getRow();

        // A = Part ID
        // F = Member ID / HMO ID
        // G = Auth No. / Prior Authorization
        // M = SS#
        if ($row >= 2 && in_array($column, ['A', 'F', 'G', 'M'])) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function array(): array
    {
        $rows = [];

        // Header row now starts at row 1
        $headers = [
            'ID #',
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

        // Data rows start from row 2
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
                    ? Carbon::parse($row['MedicaidPAStart'])->format('m/d/Y')
                    : '',

                !empty($row['MedicaidPAEnd'])
                    ? Carbon::parse($row['MedicaidPAEnd'])->format('m/d/Y')
                    : '',

                $row['PrimaryICD'] ?? '',
                $row['SecondaryICD'] ?? '',

                !empty($row['DOB'])
                    ? Carbon::parse($row['DOB'])->format('m/d/Y')
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

        // Totals row
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
        $lastCol = Coordinate::stringFromColumnIndex($colCount);
        $highestRow = $sheet->getHighestRow();

        // All rows bold Arial 10
        $sheet->getStyle("A1:{$lastCol}{$highestRow}")
            ->getFont()
            ->setName('Arial')
            ->setSize(10)
            ->setBold(true);

        // Header row style
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'name' => 'Arial',
                'size' => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9EAF7'],
            ],
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Left align fixed columns
        foreach (range('A', 'N') as $col) {
            $sheet->getStyle("{$col}2:{$col}{$highestRow}")
                ->getAlignment()
                ->setHorizontal('left');
        }

        // Apply text formatting again for safety
        foreach (['A', 'F', 'G', 'M'] as $textCol) {
            $sheet->getStyle("{$textCol}2:{$textCol}{$highestRow}")
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_TEXT);
        }

        // Center attendance date columns and Total column
        $dateStartCol = 15;
        $dateEndCol = $colCount;

        $dateStartLetter = Coordinate::stringFromColumnIndex($dateStartCol);
        $dateEndLetter = Coordinate::stringFromColumnIndex($dateEndCol);

        $sheet->getStyle("{$dateStartLetter}1:{$dateEndLetter}{$highestRow}")
            ->getAlignment()
            ->setHorizontal('center');

        /*
         * Conditional coloring
         *
         * H = Auth Start Dt / MedicaidPAStart
         * I = Auth End Dt / MedicaidPAEnd
         *
         * Data rows start from row 2.
         * Totals row is after all data rows.
         */
        $reportStart = $this->startDate ? Carbon::parse($this->startDate)->startOfDay() : null;
        $reportEnd   = $this->endDate ? Carbon::parse($this->endDate)->endOfDay() : null;

        $dataStartRow = 2;
        $dataEndRow = count($this->data) + 1;

        for ($row = $dataStartRow; $row <= $dataEndRow; $row++) {

            $paStartValue = $sheet->getCell("H{$row}")->getValue();
            $paEndValue   = $sheet->getCell("I{$row}")->getValue();

            $paStart = !empty($paStartValue) ? Carbon::parse($paStartValue)->startOfDay() : null;
            $paEnd   = !empty($paEndValue) ? Carbon::parse($paEndValue)->startOfDay() : null;

            $fillColor = null;

            // Yellow:
            // PA already expired before report period
            // OR PA starts after report period
            if (
                ($reportStart && $paEnd && $paEnd->lt($reportStart)) ||
                ($reportEnd && $paStart && $paStart->gt($reportEnd))
            ) {
                //$fillColor = 'FFF2CC'; // light yellow
                $fillColor = 'FFFF00'; // regular yellow
            }

            // Blue: PA Start Date or PA End Date falls between report start/end date
            if ($reportStart && $reportEnd) {
                $paStartInRange = $paStart && $paStart->between($reportStart, $reportEnd);
                $paEndInRange   = $paEnd && $paEnd->between($reportStart, $reportEnd);

                if ($paStartInRange || $paEndInRange) {
                    //$fillColor = 'D9EAF7'; // light blue
                    $fillColor = '00B0F0'; // regular/medium blue
                }
            }

            if ($fillColor) {
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB($fillColor);
            }
        }

        // Totals row
        $totalRowIdx = count($this->data) + 2;

        $sheet->getStyle("A{$totalRowIdx}:{$lastCol}{$totalRowIdx}")
            ->getFont()
            ->setBold(true);

        $sheet->getStyle("A{$totalRowIdx}:{$lastCol}{$totalRowIdx}")
            ->getBorders()
            ->getTop()
            ->setBorderStyle(Border::BORDER_THIN);

        $sheet->getStyle("A{$totalRowIdx}:{$lastCol}{$totalRowIdx}")
            ->getBorders()
            ->getBottom()
            ->setBorderStyle(Border::BORDER_DOUBLE);

        // Freeze header row
        $sheet->freezePane('A2');

        // Auto filter
        $sheet->setAutoFilter("A1:{$lastCol}1");

        // Auto-size all columns
        for ($i = 1; $i <= $colCount; $i++) {
            $col = Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }
}
