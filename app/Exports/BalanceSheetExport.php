<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class BalanceSheetExport implements FromView, ShouldAutoSize, WithColumnFormatting
{
    protected $assets;
    protected $liabilities;
    protected $profitLoss;
    protected $totalAssets;
    protected $totalLiabilities;
    protected $compname;
    protected $endDate;

    public function __construct($assets, $liabilities, $profitLoss, $totalAssets, $totalLiabilities, $compname, $endDate)
    {
        $this->assets = $assets;
        $this->liabilities = $liabilities;
        $this->profitLoss = $profitLoss;
        $this->totalAssets = $totalAssets;
        $this->totalLiabilities = $totalLiabilities;
        $this->compname = $compname;
        $this->endDate = $endDate;
    }

    public function view(): View
    {
        return view('accounts.accounts_balancesheet_excel', [
            'assets' => $this->assets,
            'liabilities' => $this->liabilities,
            'profitLoss' => $this->profitLoss,
            'totalAssets' => $this->totalAssets,
            'totalLiabilities' => $this->totalLiabilities,
            'compname' => $this->compname,
            'endDate' => $this->endDate,
        ]);
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_NUMBER_00, // Asset Amount
        ];
    }

}
