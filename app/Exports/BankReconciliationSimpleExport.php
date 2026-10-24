<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BankReconciliationSimpleExport implements FromArray, ShouldAutoSize, WithStyles
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $recon = $this->data['recon'];
        $bank = $this->data['bank'];
        $totals = $this->data['totals'];

        $unclearedDeposits = $this->data['unclearedDeposits'];
        $unclearedPayments = $this->data['unclearedPayments'];

        $periodLabel = Carbon::create(
            (int) $recon->period_year,
            (int) $recon->period_month,
            1
        )->format('F Y');

        $openingBalance = (float) ($recon->opening_balance ?? 0);
        $statementBalance = (float) ($recon->closing_balance ?? 0);
        $difference = (float) ($totals['difference'] ?? 0);

        $netOutstanding =
            (float) ($totals['uncleared_deposits'] ?? 0)
            - (float) ($totals['uncleared_payments'] ?? 0);

        $rows = [];

        $rows[] = [config('company.comp_nm', config('app.name'))];
        $rows[] = ['Bank Reconciliation - Simplified - ' . $periodLabel];
        $rows[] = [];

        $rows[] = [
            'Bank',
            trim(($bank->account_name ?? '') . ' ' . (!empty($bank->account_id) ? '(' . $bank->account_id . ')' : '')),
            'Statement Ending',
            !empty($recon->closing_dt) ? Carbon::parse($recon->closing_dt)->format('m/d/Y') : '',
            'Status',
            strtoupper($recon->status ?? 'OPEN'),
        ];

        $rows[] = [];

        $rows[] = [
            'Opening Balance',
            number_format($openingBalance, 2, '.', ''),
            'Cleared Deposits',
            number_format((float) ($totals['cleared_deposits'] ?? 0), 2, '.', ''),
            'Cleared Payments',
            number_format((float) ($totals['cleared_payments'] ?? 0), 2, '.', ''),
        ];

        $rows[] = [
            'Cleared Balance',
            number_format((float) ($totals['cleared_balance'] ?? 0), 2, '.', ''),
            'Statement Balance',
            number_format($statementBalance, 2, '.', ''),
            'Difference',
            number_format($difference, 2, '.', ''),
        ];

        $rows[] = [
            'Uncleared Deposits',
            number_format((float) ($totals['uncleared_deposits'] ?? 0), 2, '.', ''),
            'Uncleared Payments',
            number_format((float) ($totals['uncleared_payments'] ?? 0), 2, '.', ''),
            'Net Outstanding',
            number_format($netOutstanding, 2, '.', ''),
        ];

        $rows[] = [];

        $this->addSection(
            $rows,
            'Uncleared Deposits',
            $unclearedDeposits,
            'credit_amt',
            (float) ($totals['uncleared_deposits'] ?? 0)
        );

        $rows[] = [];

        $this->addSection(
            $rows,
            'Uncleared Payments',
            $unclearedPayments,
            'debit_amt',
            (float) ($totals['uncleared_payments'] ?? 0)
        );

        return $rows;
    }

    private function addSection(
        array &$rows,
        string $title,
        Collection $items,
        string $amountField,
        float $total
    ): void {
        $rows[] = [$title . ' - Total: ' . number_format($total, 2)];
        $rows[] = [
            'Date',
            'Description',
            'Reference',
            'Check No.',
            'Trans. No.',
            'Amount',
        ];

        if ($items->isEmpty()) {
            $rows[] = ['No transactions in this section.'];
        } else {
            foreach ($items as $row) {
                $description = $row->payto_name ?: ($row->remarks ?? '');

                $rows[] = [
                    !empty($row->trans_dt)
                        ? Carbon::parse($row->trans_dt)->format('m/d/Y')
                        : '',
                    $description,
                    $row->ref_no ?? '',
                    $row->check_no ?? '',
                    $row->trans_no ?? '',
                    number_format((float) ($row->$amountField ?? 0), 2, '.', ''),
                ];
            }
        }

        $rows[] = [
            '',
            '',
            '',
            '',
            $title . ' Total:',
            number_format($total, 2, '.', ''),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        $sheet->mergeCells('A1:F1');
        $sheet->mergeCells('A2:F2');

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);

        $sheet->getStyle('A1:F2')->getAlignment()->setHorizontal('center');

        $sheet->getStyle('A4:F7')->getFont()->setBold(true);

        for ($row = 1; $row <= $highestRow; $row++) {
            $cellValue = (string) $sheet->getCell('A' . $row)->getValue();

            if (
                str_starts_with($cellValue, 'Uncleared Deposits - Total:')
                || str_starts_with($cellValue, 'Uncleared Payments - Total:')
            ) {
                $sheet->mergeCells('A' . $row . ':F' . $row);
                $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            }

            if ($cellValue === 'Date') {
                $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
            }

            if (
                str_contains((string) $sheet->getCell('E' . $row)->getValue(), 'Total:')
            ) {
                $sheet->getStyle('E' . $row . ':F' . $row)->getFont()->setBold(true);
            }
        }

        $sheet->getStyle('B6:B7')->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('D6:D7')->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('F6:F7')->getNumberFormat()->setFormatCode('#,##0.00');

        $sheet->getStyle('F:F')->getNumberFormat()->setFormatCode('#,##0.00');

        return [];
    }
}
