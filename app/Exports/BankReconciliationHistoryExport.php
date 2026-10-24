<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class BankReconciliationHistoryExport implements FromView
{
    protected array $reportData;

    /**
     * Receive the complete historical reconciliation dataset
     * from AccountController.
     */
    public function __construct(array $reportData)
    {
        $this->reportData = $reportData;
    }

    /**
     * Excel view.
     */
    public function view(): View
    {
        return view(
            'accounts.accounts_bankrecon_history_excel',
            $this->reportData
        );
    }
}