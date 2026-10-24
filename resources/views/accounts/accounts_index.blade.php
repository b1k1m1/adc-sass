@extends('admin.admin_dashboard')

@section('title', 'Accounts')

@section('admin')

    <style>
        /*
                |--------------------------------------------------------------------------
                | Compact Module Menu
                |--------------------------------------------------------------------------
                */

        .module-menu-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px;
            table-layout: fixed;
        }

        .module-menu-table td {
            width: 25%;
            vertical-align: top;
            padding: 0;
        }

        .module-menu-item {
            display: flex;
            align-items: center;
            width: 100%;
            min-height: 50px;
            padding: 7px 10px;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            background: #ffffff;
            text-decoration: none !important;
            transition: all 0.2s ease;
            color: inherit;
        }

        .module-menu-item:hover {
            border-color: #556ee6;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.07);
            transform: translateY(-1px);
            text-decoration: none !important;
        }

        /*
                |--------------------------------------------------------------------------
                | Menu Icon
                |--------------------------------------------------------------------------
                */

        .module-menu-icon {
            width: 34px;
            height: 34px;
            min-width: 34px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 6px;
            background: #f3f6ff;

            margin-right: 9px;

            font-size: 16px;
            color: #556ee6;
        }

        /*
                |--------------------------------------------------------------------------
                | Menu Text
                |--------------------------------------------------------------------------
                */

        .module-menu-content {
            flex: 1;
            min-width: 0;
        }

        .module-menu-title {
            font-size: 13px;
            font-weight: 600;
            color: #343a40;
            margin: 0;
            line-height: 1.25;
        }

        /*
                |--------------------------------------------------------------------------
                | Section Heading
                |--------------------------------------------------------------------------
                */

        .module-section-title {
            font-size: 13px;
            font-weight: 600;
            color: #495057;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        /*
                |--------------------------------------------------------------------------
                | Responsive
                |--------------------------------------------------------------------------
                */

        @media (max-width: 768px) {

            .module-menu-table,
            .module-menu-table tbody,
            .module-menu-table tr,
            .module-menu-table td {
                display: block;
                width: 100%;
            }

            .module-menu-table {
                border-spacing: 0;
            }

            .module-menu-table td {
                margin-bottom: 6px;
            }

            .module-menu-item {
                min-height: 48px;
            }
        }
    </style>


    <div class="page-content">

        <div class="container-fluid">

            <div class="row">

                <div class="col-xl-12">

                    <div class="card">

                        <div class="card-body">

                            @include('admin.partials.tabs_3', ['active' => 'data'])


                            <div class="tab-content pt-4">


                                {{-- =====================================================
                                 DATA ENTRY
                            ====================================================== --}}

                                <div class="tab-pane fade show active" id="tab-data" role="tabpanel">

                                    <div class="module-section-title">
                                        Accounting Data Entry & Processing
                                    </div>


                                    <table class="module-menu-table">

                                        <tbody>


                                            {{-- ROW 1 --}}

                                            <tr>


                                                {{-- Enter Payment --}}

                                                <td>

                                                    <a href="{{ route('enter.payment') }}" class="module-menu-item">

                                                        <div class="module-menu-icon">
                                                            <i class="fas fa-money-check-alt"></i>
                                                        </div>

                                                        <div class="module-menu-content">

                                                            <div class="module-menu-title">
                                                                Enter Payment
                                                            </div>

                                                        </div>

                                                    </a>

                                                </td>


                                                {{-- Enter Deposit --}}

                                                <td>

                                                    <a href="{{ route('enter.deposit') }}" class="module-menu-item">

                                                        <div class="module-menu-icon">
                                                            <i class="fas fa-piggy-bank"></i>
                                                        </div>

                                                        <div class="module-menu-content">

                                                            <div class="module-menu-title">
                                                                Enter Deposit
                                                            </div>

                                                        </div>

                                                    </a>

                                                </td>


                                                {{-- Journal Entry --}}

                                                <td>

                                                    <a href="{{ route('journal.entry') }}" class="module-menu-item">

                                                        <div class="module-menu-icon">
                                                            <i class="fas fa-book"></i>
                                                        </div>

                                                        <div class="module-menu-content">

                                                            <div class="module-menu-title">
                                                                Journal Entry
                                                            </div>

                                                        </div>

                                                    </a>

                                                </td>


                                                {{-- List / Edit Payments --}}

                                                <td>

                                                    <a href="{{ route('list.payment') }}" class="module-menu-item">

                                                        <div class="module-menu-icon">
                                                            <i class="fas fa-list"></i>
                                                        </div>

                                                        <div class="module-menu-content">

                                                            <div class="module-menu-title">
                                                                List / Edit Payments
                                                            </div>

                                                        </div>

                                                    </a>

                                                </td>


                                            </tr>



                                            {{-- ROW 2 --}}

                                            <tr>


                                                {{-- List / Edit Deposits --}}

                                                <td>

                                                    <a href="{{ route('list.deposit') }}" class="module-menu-item">

                                                        <div class="module-menu-icon">
                                                            <i class="fas fa-list-alt"></i>
                                                        </div>

                                                        <div class="module-menu-content">

                                                            <div class="module-menu-title">
                                                                List / Edit Deposits
                                                            </div>

                                                        </div>

                                                    </a>

                                                </td>


                                                {{-- List / Edit Journal Entries --}}

                                                <td>

                                                    <a href="{{ route('journal.list') }}" class="module-menu-item">

                                                        <div class="module-menu-icon">
                                                            <i class="fas fa-edit"></i>
                                                        </div>

                                                        <div class="module-menu-content">

                                                            <div class="module-menu-title">
                                                                List / Edit Journal Entries
                                                            </div>

                                                        </div>

                                                    </a>

                                                </td>


                                                {{-- Transaction Categorization --}}

                                                <td>

                                                    <a href="{{ route('bank.categorization') }}" class="module-menu-item">

                                                        <div class="module-menu-icon">
                                                            <i class="fas fa-tags"></i>
                                                        </div>

                                                        <div class="module-menu-content">

                                                            <div class="module-menu-title">
                                                                Transaction Categorization
                                                            </div>

                                                        </div>

                                                    </a>

                                                </td>


                                                {{-- Bank Reconciliation --}}

                                                <td>

                                                    <a href="{{ route('bankrecon') }}" class="module-menu-item">

                                                        <div class="module-menu-icon">
                                                            <i class="fas fa-balance-scale"></i>
                                                        </div>

                                                        <div class="module-menu-content">

                                                            <div class="module-menu-title">
                                                                Bank Reconciliation
                                                            </div>

                                                        </div>

                                                    </a>

                                                </td>


                                            </tr>



                                            {{-- ROW 3 --}}

                                            <tr>


                                                {{-- Bank Feed Upload --}}

                                                <td>

                                                    <a href="{{ route('bankfeed.import.form') }}" class="module-menu-item">

                                                        <div class="module-menu-icon">
                                                            <i class="fas fa-file-upload"></i>
                                                        </div>

                                                        <div class="module-menu-content">

                                                            <div class="module-menu-title">
                                                                Bank Feed Upload
                                                            </div>

                                                        </div>

                                                    </a>

                                                </td>


                                                {{-- Payment Plan --}}

                                                <td>

                                                    <a href="{{ route('mpp.index') }}" class="module-menu-item">

                                                        <div class="module-menu-icon">
                                                            <i class="fas fa-calendar-check"></i>
                                                        </div>

                                                        <div class="module-menu-content">

                                                            <div class="module-menu-title">
                                                                Payment Plan
                                                            </div>

                                                        </div>

                                                    </a>

                                                </td>


                                                <td></td>

                                                <td></td>


                                            </tr>


                                        </tbody>

                                    </table>

                                </div>



                                {{-- =====================================================
                                 REPORTS
                            ====================================================== --}}

                                <div class="tab-pane fade" id="tab-reports" role="tabpanel">

                                    <div class="module-section-title">
                                        Accounting Reports
                                    </div>


                                    @php

                                        $reports = [
                                            ['Print Check', route('print.checklist'), 'fas fa-print'],

                                            [
                                                'Bank Deposit Register',
                                                route('bank.deposit.register'),
                                                'fas fa-university',
                                            ],

                                            [
                                                'Bank Payment Register',
                                                route('bank.payment.register'),
                                                'fas fa-money-check',
                                            ],

                                            ['Bank Report', route('bank.report'), 'fas fa-file-invoice-dollar'],

                                            [
                                                'Bank Reconciliation Report',
                                                route('bankrecon.history'),
                                                'fas fa-balance-scale',
                                            ],

                                            [
                                                'Traditional Bank Reconciliation Report',
                                                route('accounts.traditional.bank.reconciliation'),
                                                'fas fa-balance-scale',
                                            ],

                                            ['Selected General Ledger', route('general.ledger'), 'fas fa-book-open'],

                                            ['General Ledger', route('genledger.report'), 'fas fa-book'],

                                            [
                                                'Trial Balance',
                                                route('trialbalance.report'),
                                                'fas fa-balance-scale-right',
                                            ],

                                            [
                                                'Trial Balance Month-wise',
                                                route('trialbalance.monthwise'),
                                                'fas fa-calendar-alt',
                                            ],

                                            [
                                                'Trial Balance Month-wise With Balances',
                                                route('trialbalance.balance.monthwise'),
                                                'fas fa-chart-bar',
                                            ],

                                            ['Profit & Loss', route('profitloss'), 'fas fa-chart-line'],

                                            ['Profit & Loss New', route('profitloss.new'), 'fas fa-chart-area'],

                                            [
                                                'Profit & Loss Comparison',
                                                route('profitloss.monthwise'),
                                                'fas fa-chart-column',
                                            ],

                                            ['Balance Sheet', route('balancesheet'), 'fas fa-file-invoice'],

                                            [
                                                'Fiscal Year Closing Preview',
                                                route('accounts.fiscal-year-closing'),
                                                'fas fa-calendar-times',
                                            ],

                                            ['Payment Plan', route('reports.payment_plan'), 'fas fa-calendar-check'],
                                        ];

                                    @endphp


                                    <table class="module-menu-table">

                                        <tbody>


                                            @foreach (array_chunk($reports, 4) as $row)
                                                <tr>


                                                    @foreach ($row as [$name, $url, $icon])
                                                        <td>

                                                            <a href="{{ $url }}" class="module-menu-item">

                                                                <div class="module-menu-icon">

                                                                    <i class="{{ $icon }}"></i>

                                                                </div>


                                                                <div class="module-menu-content">

                                                                    <div class="module-menu-title">

                                                                        {{ $name }}

                                                                    </div>

                                                                </div>

                                                            </a>

                                                        </td>
                                                    @endforeach


                                                    @for ($i = count($row); $i < 4; $i++)
                                                        <td></td>
                                                    @endfor


                                                </tr>
                                            @endforeach


                                        </tbody>

                                    </table>

                                </div>



                                {{-- =====================================================
                                 SETTINGS
                            ====================================================== --}}

                                <div class="tab-pane fade" id="tab-settings" role="tabpanel">

                                    <div class="module-section-title">
                                        Accounting Settings
                                    </div>


                                    <table class="module-menu-table">

                                        <tbody>

                                            <tr>


                                                {{-- Account Master --}}

                                                <td>

                                                    <a href="{{ route('accountmasters.index') }}" class="module-menu-item">

                                                        <div class="module-menu-icon">

                                                            <i class="fas fa-sitemap"></i>

                                                        </div>


                                                        <div class="module-menu-content">

                                                            <div class="module-menu-title">
                                                                Account Master
                                                            </div>

                                                        </div>

                                                    </a>

                                                </td>


                                                {{-- Bank Feed Rules --}}

                                                <td>

                                                    <a href="{{ route('bank-rules.index') }}" class="module-menu-item">

                                                        <div class="module-menu-icon">

                                                            <i class="fas fa-random"></i>

                                                        </div>


                                                        <div class="module-menu-content">

                                                            <div class="module-menu-title">
                                                                Bank Feed Rules
                                                            </div>

                                                        </div>

                                                    </a>

                                                </td>


                                                <td></td>

                                                <td></td>


                                            </tr>

                                        </tbody>

                                    </table>

                                </div>


                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection
