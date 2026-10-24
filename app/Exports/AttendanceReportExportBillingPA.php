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

class AttendanceReportExportBillingPA extends DefaultValueBinder implements FromArray, WithTitle, WithStyles, WithColumnFormatting, WithCustomValueBinder
{
    protected $data;
    protected $dates;
    protected $columnTotals;
    protected $grandTotal;
    protected $compname;
    protected $startDate;
    protected $endDate;

    public function __construct(
        array $data,
        array $dates,
        array $columnTotals,
        int $grandTotal,
        $compname = null,
        $startDate = null,
        $endDate = null
    ) {
        $this->dates = $dates;
        $this->columnTotals = $columnTotals;
        $this->grandTotal = $grandTotal;
        $this->compname = $compname;
        $this->startDate = $startDate;
        $this->endDate = $endDate;

        /*
        |--------------------------------------------------------------------------
        | Build billing rows by applicable PA.
        |--------------------------------------------------------------------------
        | This keeps only rows with attendance > 0.
        | If PA changes during the selected week, the same participant will be
        | split into separate rows by applicable PA.
        |--------------------------------------------------------------------------
        */
        $this->data = $this->buildBillingRowsByApplicablePA($data, $dates);
    }

    /**
     * Force selected columns to TEXT.
     *
     * A = Part ID
     * F = Member ID / HMO ID
     * G = Auth No. / Prior Authorization
     * O = SS#
     */
    public function bindValue(Cell $cell, $value): bool
    {
        $column = $cell->getColumn();
        $row = $cell->getRow();

        if ($row >= 2 && in_array($column, ['A', 'F', 'G', 'O'], true)) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    private function buildBillingRowsByApplicablePA(array $data, array $dates): array
    {
        $billingRows = [];

        foreach ($data as $row) {

            /*
            |--------------------------------------------------------------------------
            | Skip participants with no attendance in selected date range.
            |--------------------------------------------------------------------------
            */
            $hasAttendance = false;

            foreach ($dates as $d) {
                $val = $row['attendance'][$d] ?? '';

                if (is_numeric($val) && (float) $val > 0) {
                    $hasAttendance = true;
                    break;
                }
            }

            if (!$hasAttendance) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Group attendance dates by applicable PA.
            |--------------------------------------------------------------------------
            */
            $groups = [];

            foreach ($dates as $d) {
                $val = $row['attendance'][$d] ?? '';

                if (!is_numeric($val) || (float) $val <= 0) {
                    continue;
                }

                $pa = $this->getApplicablePA($row, $d);

                $groupKey = implode('|', [
                    $pa['hmo_id'] ?? '',
                    $pa['pa_no'] ?? '',
                    $pa['pa_start'] ?? '',
                    $pa['pa_end'] ?? '',
                    $pa['source'] ?? '',
                    $pa['status'] ?? '',
                ]);

                if (!isset($groups[$groupKey])) {
                    $groups[$groupKey] = [
                        'pa' => $pa,
                        'attendance' => array_fill_keys($dates, ''),
                    ];
                }

                $groups[$groupKey]['attendance'][$d] = $val;
            }

            $groupCount = count($groups);

            foreach ($groups as $group) {
                $pa = $group['pa'];

                $newRow = $row;
                $newRow['attendance'] = $group['attendance'];

                /*
                |--------------------------------------------------------------------------
                | Replace printed Medicaid/Auth fields with applicable PA values.
                |--------------------------------------------------------------------------
                */
                $newRow['MedicaidHMOId']   = $pa['hmo_id'];
                $newRow['MedicaidPANo']    = $pa['pa_no'];
                $newRow['MedicaidPAStart'] = $pa['pa_start'];
                $newRow['MedicaidPAEnd']   = $pa['pa_end'];
                $newRow['PASource']        = $pa['source'];

                if ($pa['status'] !== 'OK') {
                    $newRow['PAWarning'] = $pa['status'];
                } elseif ($groupCount > 1) {
                    $newRow['PAWarning'] = 'PA changes during selected week';
                } elseif ($pa['source'] === 'Renewal PA') {
                    $newRow['PAWarning'] = 'Renewal PA used';
                } else {
                    $newRow['PAWarning'] = '';
                }

                $billingRows[] = $newRow;
            }
        }

        return $billingRows;
    }

    private function getApplicablePA(array $row, string $serviceDate): array
    {
        $serviceDate = Carbon::parse($serviceDate)->toDateString();

        /*
        |--------------------------------------------------------------------------
        | Check renewal PA first.
        |--------------------------------------------------------------------------
        */
        $renewPaNo  = $this->field($row, ['Renew1PANo', 'renew1_PA_no']);
        $renewHmoId = $this->field($row, ['Renew1HMOId', 'renew1_HMO_id']);
        $renewStart = $this->field($row, ['Renew1PAStart', 'renew1_PA_Start']);
        $renewEnd   = $this->field($row, ['Renew1PAEnd', 'renew1_PA_End']);

        if (!empty($renewPaNo) && !empty($renewStart) && !empty($renewEnd)) {
            $start = Carbon::parse($renewStart)->toDateString();
            $end   = Carbon::parse($renewEnd)->toDateString();

            if ($serviceDate >= $start && $serviceDate <= $end) {
                return [
                    'hmo_id'   => $renewHmoId,
                    'pa_no'    => $renewPaNo,
                    'pa_start' => $renewStart,
                    'pa_end'   => $renewEnd,
                    'source'   => 'Renewal PA',
                    'status'   => 'OK',
                ];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Check current PA second.
        |--------------------------------------------------------------------------
        */
        $currentPaNo  = $this->field($row, ['MedicaidPANo', 'medicaid_PA_no']);
        $currentHmoId = $this->field($row, ['MedicaidHMOId', 'medicaid_HMO_id']);
        $currentStart = $this->field($row, ['MedicaidPAStart', 'medicaid_PA_Start']);
        $currentEnd   = $this->field($row, ['MedicaidPAEnd', 'medicaid_PA_End']);

        if (!empty($currentPaNo) && !empty($currentStart) && !empty($currentEnd)) {
            $start = Carbon::parse($currentStart)->toDateString();
            $end   = Carbon::parse($currentEnd)->toDateString();

            if ($serviceDate >= $start && $serviceDate <= $end) {
                return [
                    'hmo_id'   => $currentHmoId,
                    'pa_no'    => $currentPaNo,
                    'pa_start' => $currentStart,
                    'pa_end'   => $currentEnd,
                    'source'   => 'Current PA',
                    'status'   => 'OK',
                ];
            }
        }

       /*
        |--------------------------------------------------------------------------
        | No valid PA found.
        |--------------------------------------------------------------------------
        | Show available PA values for reference, even though they are not valid
        | for the selected attendance/service date.
        |--------------------------------------------------------------------------
        */
        $displayHmoId = $currentHmoId ?: $renewHmoId;
        $displayPaNo = $currentPaNo ?: $renewPaNo;
        $displayStart = $currentStart ?: $renewStart;
        $displayEnd = $currentEnd ?: $renewEnd;

        $displaySource = '';

        if (!empty($currentPaNo)) {
            $displaySource = 'Current PA - Not valid for service date';
        } elseif (!empty($renewPaNo)) {
            $displaySource = 'Renewal PA - Not valid for service date';
        }

        return [
            'hmo_id'   => $displayHmoId,
            'pa_no'    => $displayPaNo,
            'pa_start' => $displayStart,
            'pa_end'   => $displayEnd,
            'source'   => $displaySource,
            'status'   => 'No valid PA for service date',
        ];
    }

    private function field(array $row, array $keys)
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') {
                return $row[$key];
            }
        }

        return '';
    }

    public function array(): array
    {
        $rows = [];

        /*
        |--------------------------------------------------------------------------
        | Fixed columns A through P.
        | Attendance date columns start from Q.
        |--------------------------------------------------------------------------
        */
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
            'PA Source',
            'PA Warning',
            'Primary ICD',
            'Scondary ICD',
            'DOB',
            'SS#',
            'Sex',
        ];

        foreach ($this->dates as $d) {
            $headers[] = date('d', strtotime($d));
        }

        $headers[] = 'Total';

        $rows[] = $headers;

        foreach ($this->data as $row) {
            $r = [
                (string) ($row['ParticipantId'] ?? ''),
                $row['LastName'] ?? '',
                $row['FirstName'] ?? '',
                $row['MiddleName'] ?? '',
                $row['HmoEnrolled'] ?? '',

                (string) ($row['MedicaidHMOId'] ?? ''),
                (string) ($row['MedicaidPANo'] ?? ''),

                !empty($row['MedicaidPAStart'])
                    ? Carbon::parse($row['MedicaidPAStart'])->format('m/d/Y')
                    : '',

                !empty($row['MedicaidPAEnd'])
                    ? Carbon::parse($row['MedicaidPAEnd'])->format('m/d/Y')
                    : '',

                $row['PASource'] ?? '',
                $row['PAWarning'] ?? '',

                $row['PrimaryICD'] ?? '',
                $row['SecondaryICD'] ?? '',

                !empty($row['DOB'])
                    ? Carbon::parse($row['DOB'])->format('m/d/Y')
                    : '',

                (string) ($row['SS#'] ?? ''),

                $row['Sex'] ?? '',
            ];

            $rowTotal = 0;

            foreach ($this->dates as $d) {
                $val = $row['attendance'][$d] ?? '';
                $r[] = $val;

                if (is_numeric($val)) {
                    $rowTotal += $val;
                }
            }

            $r[] = $rowTotal;

            $rows[] = $r;
        }

        /*
        |--------------------------------------------------------------------------
        | Totals row.
        |--------------------------------------------------------------------------
        */
        $totalRow = [
            'Total', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',
        ];

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
            'O' => NumberFormat::FORMAT_TEXT,

            // Date columns
            'H' => 'mm/dd/yyyy',
            'I' => 'mm/dd/yyyy',
            'N' => 'mm/dd/yyyy',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        /*
        |--------------------------------------------------------------------------
        | Fixed columns A through P = 16 columns.
        | Attendance starts at Q = column 17.
        | Total column is after the attendance dates.
        |--------------------------------------------------------------------------
        */
        $fixedColumnCount = 16;
        $colCount = $fixedColumnCount + count($this->dates) + 1; // +1 for Total
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

        // Left align fixed columns A through P
        foreach (range('A', 'P') as $col) {
            $sheet->getStyle("{$col}2:{$col}{$highestRow}")
                ->getAlignment()
                ->setHorizontal('left');
        }

        // Apply text formatting again for safety
        foreach (['A', 'F', 'G', 'O'] as $textCol) {
            $sheet->getStyle("{$textCol}2:{$textCol}{$highestRow}")
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_TEXT);
        }

        // Center attendance date columns and Total column
        $dateStartCol = 17; // Q
        $dateEndCol = $colCount;

        $dateStartLetter = Coordinate::stringFromColumnIndex($dateStartCol);
        $dateEndLetter = Coordinate::stringFromColumnIndex($dateEndCol);

        $sheet->getStyle("{$dateStartLetter}1:{$dateEndLetter}{$highestRow}")
            ->getAlignment()
            ->setHorizontal('center');

        /*
        |--------------------------------------------------------------------------
        | Conditional coloring.
        |--------------------------------------------------------------------------
        | H = Auth Start Dt
        | I = Auth End Dt
        | K = PA Warning
        |--------------------------------------------------------------------------
        */
        $reportStart = $this->startDate ? Carbon::parse($this->startDate)->startOfDay() : null;
        $reportEnd = $this->endDate ? Carbon::parse($this->endDate)->endOfDay() : null;

        $dataStartRow = 2;
        $dataEndRow = count($this->data) + 1;

        for ($row = $dataStartRow; $row <= $dataEndRow; $row++) {

            $paStartValue = $sheet->getCell("H{$row}")->getValue();
            $paEndValue = $sheet->getCell("I{$row}")->getValue();
            $paWarning = trim((string) $sheet->getCell("K{$row}")->getValue());

            $paStart = !empty($paStartValue) ? Carbon::parse($paStartValue)->startOfDay() : null;
            $paEnd = !empty($paEndValue) ? Carbon::parse($paEndValue)->startOfDay() : null;

            $fillColor = null;

            /*
            |--------------------------------------------------------------------------
            | Yellow = no valid PA / PA outside selected report period.
            |--------------------------------------------------------------------------
            */
            if ($paWarning === 'No valid PA for service date') {
                $fillColor = 'FFFF00';
            }

            if (
                ($reportStart && $paEnd && $paEnd->lt($reportStart)) ||
                ($reportEnd && $paStart && $paStart->gt($reportEnd))
            ) {
                $fillColor = 'FFFF00';
            }

            /*
            |--------------------------------------------------------------------------
            | Blue = PA start/end date falls inside selected range,
            | or PA changed during selected week.
            |--------------------------------------------------------------------------
            */
            if ($reportStart && $reportEnd) {
                $paStartInRange = $paStart && $paStart->between($reportStart, $reportEnd);
                $paEndInRange = $paEnd && $paEnd->between($reportStart, $reportEnd);

                if (
                    $paStartInRange ||
                    $paEndInRange ||
                    $paWarning === 'PA changes during selected week'
                ) {
                    $fillColor = '00B0F0';
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
