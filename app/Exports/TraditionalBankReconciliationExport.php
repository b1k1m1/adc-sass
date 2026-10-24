<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TraditionalBankReconciliationExport implements
    FromView,
    WithEvents,
    WithTitle
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    // ---------------------------------------------------------------
    // Excel Blade
    // ---------------------------------------------------------------
    public function view(): View
    {
        return view(
            'accounts.accounts_traditional_bank_reconciliation_excel',
            $this->data
        );
    }

    // ---------------------------------------------------------------
    // Worksheet Name
    // ---------------------------------------------------------------
    public function title(): string
    {
        return 'Bank Reconciliation';
    }

    // ---------------------------------------------------------------
    // Excel Formatting
    // ---------------------------------------------------------------
    public function registerEvents(): array
    {
        return [

            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                // ---------------------------------------------------
                // Page setup
                // ---------------------------------------------------
                $sheet->getPageSetup()
                    ->setOrientation(
                        \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
                    );

                $sheet->getPageSetup()
                    ->setPaperSize(
                        \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_LETTER
                    );

                $sheet->getPageSetup()
                    ->setFitToWidth(1);

                $sheet->getPageSetup()
                    ->setFitToHeight(0);

                // ---------------------------------------------------
                // Column Widths
                //
                // A Date
                // B Trans #
                // C Ref #
                // D Check #
                // E Description / Payee
                // F Remarks
                // G Amount
                // ---------------------------------------------------
                $sheet->getColumnDimension('A')->setWidth(13);
                $sheet->getColumnDimension('B')->setWidth(12);
                $sheet->getColumnDimension('C')->setWidth(16);
                $sheet->getColumnDimension('D')->setWidth(14);
                $sheet->getColumnDimension('E')->setWidth(35);
                $sheet->getColumnDimension('F')->setWidth(35);
                $sheet->getColumnDimension('G')->setWidth(16);

                // ---------------------------------------------------
                // Default vertical alignment
                // ---------------------------------------------------
                $highestRow =
                    $sheet->getHighestRow();

                $sheet->getStyle(
                    'A1:G' . $highestRow
                )
                    ->getAlignment()
                    ->setVertical(
                        Alignment::VERTICAL_CENTER
                    );

                // ---------------------------------------------------
                // Amount column
                //
                // IMPORTANT:
                // Bank Book Balance and every transaction amount
                // use the SAME column G.
                // ---------------------------------------------------
                $sheet->getStyle(
                    'G1:G' . $highestRow
                )
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00;[Red](#,##0.00)');

                $sheet->getStyle(
                    'G1:G' . $highestRow
                )
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_RIGHT
                    );

                // ---------------------------------------------------
                // Dates
                // ---------------------------------------------------
                $sheet->getStyle(
                    'A1:A' . $highestRow
                )
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_LEFT
                    );

                // ---------------------------------------------------
                // Print margins
                // ---------------------------------------------------
                $sheet->getPageMargins()
                    ->setTop(0.40);

                $sheet->getPageMargins()
                    ->setBottom(0.40);

                $sheet->getPageMargins()
                    ->setLeft(0.30);

                $sheet->getPageMargins()
                    ->setRight(0.30);
            },

        ];
    }
}
