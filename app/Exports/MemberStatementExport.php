<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class MemberStatementExport implements
    FromArray,
    WithStyles,
    WithColumnWidths
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }


    /*
    |--------------------------------------------------------------------------
    | Excel Data
    |--------------------------------------------------------------------------
    */
    public function array(): array
    {
        $member       = $this->data['member'];
        $transactions = $this->data['transactions'];

        $rows = [];

        /*
        |--------------------------------------------------------------------------
        | Report Header
        |--------------------------------------------------------------------------
        */
        $rows[] = [
            'Member Statement / Ledger',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ];

        $rows[] = [
            'Statement Period:',
            $this->data['dateFromFormatted']
                . ' - '
                . $this->data['dateToFormatted'],
            '',
            '',
            '',
            '',
            '',
            ''
        ];

        $rows[] = [
            'Member:',
            trim(
                ($member->LastName ?? '')
                    . ', '
                    . ($member->FirstName ?? '')
            ),
            '',
            '',
            'Opening Balance:',
            $this->data['openingBalance'],
            '',
            ''
        ];

        $rows[] = [
            'Participant ID:',
            $member->ParticipantId ?? '',
            '',
            '',
            'Closing Balance:',
            $this->data['closingBalance'],
            '',
            ''
        ];

        $rows[] = [
            'Medicaid #:',
            $member->MedicaidNo ?? '',
            '',
            '',
            '',
            '',
            '',
            ''
        ];

        /*
        |--------------------------------------------------------------------------
        | Blank Row
        |--------------------------------------------------------------------------
        */
        $rows[] = ['', '', '', '', '', '', '', ''];


        /*
        |--------------------------------------------------------------------------
        | Column Headers
        |--------------------------------------------------------------------------
        */
        $rows[] = [
            'Date',
            'Claim #',
            'Service From',
            'Service To',
            'Insurance Company',
            'Charge',
            'Payment',
            'Patient Resp.',
            'Balance',
        ];


        /*
        |--------------------------------------------------------------------------
        | Transaction Rows
        |--------------------------------------------------------------------------
        */
        foreach ($transactions as $transaction) {

            $rows[] = [
                Carbon::parse($transaction->service_from)
                    ->format('m/d/Y'),

                $transaction->claim_no ?? '',

                Carbon::parse($transaction->service_from)
                    ->format('m/d/Y'),

                Carbon::parse($transaction->service_to)
                    ->format('m/d/Y'),

                $transaction->insurance_name ?? '',

                (float) ($transaction->charge_amount ?? 0),

                (float) ($transaction->paid_amount ?? 0),

                (float) ($transaction->patient_responsibility ?? 0),

                (float) ($transaction->balance ?? 0),
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Totals Row
        |--------------------------------------------------------------------------
        */
        $rows[] = [
            '',
            '',
            '',
            '',
            'TOTAL',
            (float) $this->data['totalCharge'],
            (float) $this->data['totalPayment'],
            (float) $this->data['totalPatientResp'],
            (float) $this->data['closingBalance'],
        ];

        return $rows;
    }


    /*
    |--------------------------------------------------------------------------
    | Column Widths
    |--------------------------------------------------------------------------
    */
    public function columnWidths(): array
    {
        return [
            'A' => 14,
            'B' => 14,
            'C' => 14,
            'D' => 14,
            'E' => 30,
            'F' => 14,
            'G' => 14,
            'H' => 16,
            'I' => 14,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Excel Formatting / Styles
    |--------------------------------------------------------------------------
    */
    public function styles(Worksheet $sheet)
    {
        /*
        |--------------------------------------------------------------------------
        | Merge Report Title
        |--------------------------------------------------------------------------
        */
        $sheet->mergeCells('A1:I1');

        $sheet->getStyle('A1')->getFont()
            ->setBold(true)
            ->setSize(16);

        $sheet->getStyle('A1')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);


        /*
        |--------------------------------------------------------------------------
        | Statement Period
        |--------------------------------------------------------------------------
        */
        $sheet->mergeCells('B2:D2');

        $sheet->getStyle('A2')->getFont()->setBold(true);


        /*
        |--------------------------------------------------------------------------
        | Member Information Labels
        |--------------------------------------------------------------------------
        */
        $sheet->getStyle('A3:A5')->getFont()->setBold(true);

        $sheet->getStyle('E3:E4')->getFont()->setBold(true);


        /*
        |--------------------------------------------------------------------------
        | Find Header / Totals Rows
        |--------------------------------------------------------------------------
        */
        $headerRow = 7;

        /*
         * Transaction rows start at row 8.
         * Last row is always our totals row.
         */
        $lastRow = $sheet->getHighestRow();

        $firstTransactionRow = $headerRow + 1;

        $totalRow = $lastRow;


        /*
        |--------------------------------------------------------------------------
        | Column Header Formatting
        |--------------------------------------------------------------------------
        */
        $sheet->getStyle("A{$headerRow}:I{$headerRow}")
            ->getFont()
            ->setBold(true);

        $sheet->getStyle("A{$headerRow}:I{$headerRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle("A{$headerRow}:I{$headerRow}")
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('E9ECEF');


        /*
        |--------------------------------------------------------------------------
        | Borders
        |--------------------------------------------------------------------------
        */
        $sheet->getStyle("A{$headerRow}:I{$totalRow}")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);


        /*
        |--------------------------------------------------------------------------
        | Numeric Currency Formatting
        |--------------------------------------------------------------------------
        */
        if ($totalRow >= $firstTransactionRow) {

            $sheet->getStyle(
                "F{$firstTransactionRow}:I{$totalRow}"
            )->getNumberFormat()->setFormatCode(
                '$#,##0.00;[Red]-$#,##0.00'
            );

            $sheet->getStyle(
                "F{$firstTransactionRow}:I{$totalRow}"
            )->getAlignment()->setHorizontal(
                Alignment::HORIZONTAL_RIGHT
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Opening / Closing Balance Currency Formatting
        |--------------------------------------------------------------------------
        */
        $sheet->getStyle('F3:F4')
            ->getNumberFormat()
            ->setFormatCode('$#,##0.00;[Red]-$#,##0.00');

        $sheet->getStyle('F3:F4')
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);


        /*
        |--------------------------------------------------------------------------
        | Totals Row
        |--------------------------------------------------------------------------
        */
        $sheet->getStyle("A{$totalRow}:I{$totalRow}")
            ->getFont()
            ->setBold(true);

        $sheet->getStyle("A{$totalRow}:I{$totalRow}")
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('E9ECEF');

        $sheet->getStyle("E{$totalRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);


        /*
        |--------------------------------------------------------------------------
        | Freeze Header
        |--------------------------------------------------------------------------
        */
        $sheet->freezePane('A8');


        /*
        |--------------------------------------------------------------------------
        | Auto Filter
        |--------------------------------------------------------------------------
        */
        if ($totalRow > $headerRow) {
            $sheet->setAutoFilter(
                "A{$headerRow}:I" . ($totalRow - 1)
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Vertical Alignment
        |--------------------------------------------------------------------------
        */
        $sheet->getStyle("A1:I{$totalRow}")
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER);


        /*
        |--------------------------------------------------------------------------
        | Row Heights
        |--------------------------------------------------------------------------
        */
        $sheet->getRowDimension(1)->setRowHeight(24);

        $sheet->getRowDimension($headerRow)->setRowHeight(22);

        return [];
    }
}
