@extends('admin.admin_dashboard')

@section('admin')

@php
    /*
    |--------------------------------------------------------------------------
    | Helper for accounting-style amount display
    |--------------------------------------------------------------------------
    */
    $formatAmount = function ($amount) {
        $amount = (float) $amount;

        if (abs($amount) < 0.005) {
            return '0.00';
        }

        return $amount < 0
            ? '(' . number_format(abs($amount), 2) . ')'
            : number_format($amount, 2);
    };
@endphp

<div class="page-content">
    <div class="container-fluid">

        {{-- ========================================================= --}}
        {{-- Page title --}}
        {{-- ========================================================= --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">

                    <div>
                        <h4 class="mb-1 font-size-18">
                            Fiscal Year Closing Preview
                        </h4>

                        <div class="text-muted">
                            Review the balances before creating beginning balances
                            for the next fiscal year.
                        </div>
                    </div>

                    <div class="page-title-right">
                        <span class="badge bg-primary font-size-13">
                            {{ $compname ?? config('company.comp_nm') }}
                        </span>
                    </div>

                </div>
            </div>
        </div>


        {{-- ========================================================= --}}
        {{-- Validation errors --}}
        {{-- ========================================================= --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Please correct the following:</strong>

                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Close">
                </button>
            </div>
        @endif


        {{-- ========================================================= --}}
        {{-- Fiscal-year selector --}}
        {{-- ========================================================= --}}
        <div class="card">
            <div class="card-body">

                <form method="GET"
                      action="{{ route('accounts.fiscal-year-closing') }}"
                      class="row g-3 align-items-end">

                    <div class="col-md-3">
                        <label for="fiscal_year" class="form-label">
                            Fiscal Year to Close
                        </label>

                        <select name="fiscal_year"
                                id="fiscal_year"
                                class="form-select"
                                required>

                            @for ($year = now()->year; $year >= now()->year - 10; $year--)
                                <option value="{{ $year }}"
                                    {{ (int) $fiscalYear === $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endfor

                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">
                            Closing Date
                        </label>

                        <input type="text"
                               class="form-control"
                               value="{{ \Carbon\Carbon::parse($closingDate)->format('m/d/Y') }}"
                               readonly>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">
                            New Opening Date
                        </label>

                        <input type="text"
                               class="form-control"
                               value="{{ \Carbon\Carbon::parse($openingDate)->format('m/d/Y') }}"
                               readonly>
                    </div>

                    <div class="col-md-3">
                        <button type="submit"
                                class="btn btn-primary w-100">

                            <i class="fas fa-search me-1"></i>
                            Preview Closing
                        </button>
                    </div>

                </form>

            </div>
        </div>


        {{-- ========================================================= --}}
        {{-- Existing closing warning --}}
        {{-- ========================================================= --}}
        @if ($existingClosing)

            @php
                $existingStatus = strtoupper($existingClosing->status ?? 'CLOSED');

                $existingStatusClass = match ($existingStatus) {
                    'REOPENED' => 'bg-warning text-dark',
                    'PREVIEW'  => 'bg-info text-dark',
                    default    => 'bg-danger',
                };
            @endphp

            <div class="alert alert-warning">
                <div class="d-flex align-items-start">

                    <i class="fas fa-exclamation-triangle font-size-20 me-3 mt-1"></i>

                    <div>
                        <h5 class="alert-heading mb-2">
                            Fiscal year {{ $fiscalYear }} already has a closing record
                        </h5>

                        <div class="mb-1">
                            Status:
                            <span class="badge {{ $existingStatusClass }}">
                                {{ $existingStatus }}
                            </span>
                        </div>

                        <div class="mb-1">
                            Closing date:
                            <strong>
                                {{ \Carbon\Carbon::parse($existingClosing->closing_date)->format('m/d/Y') }}
                            </strong>
                        </div>

                        <div class="mb-1">
                            Opening date:
                            <strong>
                                {{ \Carbon\Carbon::parse($existingClosing->opening_date)->format('m/d/Y') }}
                            </strong>
                        </div>

                        <div>
                            Recorded profit/loss:
                            <strong>
                                {{ $formatAmount($existingClosing->net_profit_loss ?? 0) }}
                            </strong>
                        </div>

                        <hr>

                        <div class="mb-0">
                            The fiscal year cannot be closed again unless the
                            existing closing is reopened or removed through the
                            controlled reopening process.
                        </div>
                    </div>

                </div>
            </div>

        @endif


        {{-- ========================================================= --}}
        {{-- P&L summary --}}
        {{-- ========================================================= --}}
        <div class="row">

            <div class="col-xl-4 col-md-6">
                <div class="card">
                    <div class="card-body">

                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">

                                <p class="text-muted mb-2">
                                    Total Income
                                </p>

                                <h4 class="mb-0 text-success">
                                    {{ $formatAmount($totalIncome) }}
                                </h4>

                            </div>

                            <div class="avatar-sm">
                                <span class="avatar-title rounded-circle bg-success-subtle text-success font-size-20">
                                    <i class="fas fa-arrow-up"></i>
                                </span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>


            <div class="col-xl-4 col-md-6">
                <div class="card">
                    <div class="card-body">

                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">

                                <p class="text-muted mb-2">
                                    Total Expenses
                                </p>

                                <h4 class="mb-0 text-danger">
                                    {{ $formatAmount($totalExpense) }}
                                </h4>

                            </div>

                            <div class="avatar-sm">
                                <span class="avatar-title rounded-circle bg-danger-subtle text-danger font-size-20">
                                    <i class="fas fa-arrow-down"></i>
                                </span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>


            <div class="col-xl-4 col-md-6">
                <div class="card">
                    <div class="card-body">

                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">

                                <p class="text-muted mb-2">
                                    Current Year Profit / Loss
                                </p>

                                <h4 class="mb-0 {{ $netProfitLoss >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $formatAmount($netProfitLoss) }}
                                </h4>

                                <small class="text-muted">
                                    {{ $netProfitLoss >= 0 ? 'Net Profit' : 'Net Loss' }}
                                </small>

                            </div>

                            <div class="avatar-sm">
                                <span class="avatar-title rounded-circle
                                    {{ $netProfitLoss >= 0
                                        ? 'bg-success-subtle text-success'
                                        : 'bg-danger-subtle text-danger' }}
                                    font-size-20">

                                    <i class="fas fa-chart-line"></i>
                                </span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- Opening-balance validation summary --}}
        {{-- ========================================================= --}}
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    Closing Validation Summary
                </h5>
            </div>

            <div class="card-body">

                <div class="row g-3">

                    <div class="col-xl-3 col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted mb-1">
                                Opening Debit Total
                            </div>

                            <div class="font-size-18 fw-semibold">
                                {{ $formatAmount($openingDebitTotal) }}
                            </div>
                        </div>
                    </div>


                    <div class="col-xl-3 col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted mb-1">
                                Opening Credit Total
                            </div>

                            <div class="font-size-18 fw-semibold">
                                {{ $formatAmount($openingCreditTotal) }}
                            </div>
                        </div>
                    </div>


                    <div class="col-xl-3 col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted mb-1">
                                Opening Entry Difference
                            </div>

                            <div class="font-size-18 fw-semibold
                                {{ abs($openingDifference) < 0.01
                                    ? 'text-success'
                                    : 'text-danger' }}">

                                {{ $formatAmount($openingDifference) }}
                            </div>
                        </div>
                    </div>


                    <div class="col-xl-3 col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted mb-1">
                                Opening Records
                            </div>

                            <div class="font-size-18 fw-semibold">
                                {{ number_format($numberOfOpeningRows) }}
                            </div>
                        </div>
                    </div>

                </div>


                <hr>


                <div class="row g-3">

                    <div class="col-md-4">
                        <div class="d-flex justify-content-between">
                            <span>Total Assets:</span>

                            <strong>
                                {{ $formatAmount($totalAssets) }}
                            </strong>
                        </div>
                    </div>


                    <div class="col-md-4">
                        <div class="d-flex justify-content-between">
                            <span>Total Liabilities and Equity:</span>

                            <strong>
                                {{ $formatAmount($totalLiabilities) }}
                            </strong>
                        </div>
                    </div>


                    <div class="col-md-4">
                        <div class="d-flex justify-content-between">
                            <span>Balance Sheet Difference:</span>

                            <strong class="{{ abs($difference) < 0.01
                                ? 'text-success'
                                : 'text-danger' }}">

                                {{ $formatAmount($difference) }}
                            </strong>
                        </div>
                    </div>

                </div>

            </div>
        </div>


        {{-- ========================================================= --}}
        {{-- Validation messages --}}
        {{-- ========================================================= --}}
        @if (abs($difference) >= 0.01)
            <div class="alert alert-danger">
                <i class="fas fa-times-circle me-2"></i>

                The Balance Sheet is out of balance by

                <strong>
                    {{ $formatAmount($difference) }}
                </strong>.

                The fiscal year should not be closed until this difference is
                investigated and corrected.
            </div>
        @endif


        @if (abs($openingDifference) >= 0.01)
            <div class="alert alert-danger">
                <i class="fas fa-times-circle me-2"></i>

                The proposed beginning-balance entry is out of balance by

                <strong>
                    {{ $formatAmount($openingDifference) }}
                </strong>.
            </div>
        @endif


        @if ($numberOfOpeningRows === 0)
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>

                No Balance Sheet balances were found to carry forward.
            </div>
        @endif


        @if ($canClose)
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>

                The closing preview is balanced and ready. Review the account
                details below before proceeding.
            </div>
        @endif


        {{-- ========================================================= --}}
        {{-- Asset carry-forward table --}}
        {{-- ========================================================= --}}
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">

                <h5 class="card-title mb-0">
                    Asset Accounts to Carry Forward
                </h5>

                <span class="badge bg-primary">
                    {{ count($assetRows) }} Accounts
                </span>

            </div>

            <div class="card-body p-0">

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm mb-0 align-middle">

                        <thead class="table-light">
                            <tr>
                                <th style="width: 90px;">GL ID</th>
                                <th style="width: 110px;">Account</th>
                                <th>Account Name</th>
                                <th>Category</th>
                                <th class="text-end" style="width: 140px;">
                                    Closing Balance
                                </th>
                                <th class="text-end" style="width: 140px;">
                                    Opening Debit
                                </th>
                                <th class="text-end" style="width: 140px;">
                                    Opening Credit
                                </th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse ($assetRows as $row)
                                <tr>
                                    <td>{{ $row['gl_id'] }}</td>

                                    <td>
                                        {{ $row['account_id'] }}
                                    </td>

                                    <td>
                                        {{ $row['account_name'] }}
                                    </td>

                                    <td>
                                        {{ $row['category'] }}
                                    </td>

                                    <td class="text-end">
                                        {{ $formatAmount($row['balance']) }}
                                    </td>

                                    <td class="text-end">
                                        {{ $row['opening_debit'] > 0
                                            ? $formatAmount($row['opening_debit'])
                                            : '' }}
                                    </td>

                                    <td class="text-end">
                                        {{ $row['opening_credit'] > 0
                                            ? $formatAmount($row['opening_credit'])
                                            : '' }}
                                    </td>
                                </tr>

                            @empty
                                <tr>
                                    <td colspan="7"
                                        class="text-center text-muted py-4">

                                        No asset balances were found.
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>

                        @if (count($assetRows) > 0)
                            <tfoot class="table-light fw-semibold">
                                <tr>
                                    <td colspan="4" class="text-end">
                                        Asset Totals
                                    </td>

                                    <td class="text-end">
                                        {{ $formatAmount(
                                            collect($assetRows)->sum('balance')
                                        ) }}
                                    </td>

                                    <td class="text-end">
                                        {{ $formatAmount(
                                            collect($assetRows)->sum('opening_debit')
                                        ) }}
                                    </td>

                                    <td class="text-end">
                                        {{ $formatAmount(
                                            collect($assetRows)->sum('opening_credit')
                                        ) }}
                                    </td>
                                </tr>
                            </tfoot>
                        @endif

                    </table>
                </div>

            </div>
        </div>


        {{-- ========================================================= --}}
        {{-- Liability/equity carry-forward table --}}
        {{-- ========================================================= --}}
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">

                <h5 class="card-title mb-0">
                    Liability and Equity Accounts to Carry Forward
                </h5>

                <span class="badge bg-secondary">
                    {{ count($liabilityRows) }} Accounts
                </span>

            </div>

            <div class="card-body p-0">

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm mb-0 align-middle">

                        <thead class="table-light">
                            <tr>
                                <th style="width: 90px;">GL ID</th>
                                <th style="width: 110px;">Account</th>
                                <th>Account Name</th>
                                <th>Category</th>
                                <th class="text-end" style="width: 140px;">
                                    Closing Balance
                                </th>
                                <th class="text-end" style="width: 140px;">
                                    Opening Debit
                                </th>
                                <th class="text-end" style="width: 140px;">
                                    Opening Credit
                                </th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse ($liabilityRows as $row)

                                @php
                                    $isRetainedEarnings =
                                        (string) $row['account_id'] === '164';
                                @endphp

                                <tr class="{{ $isRetainedEarnings
                                    ? 'table-warning'
                                    : '' }}">

                                    <td>{{ $row['gl_id'] }}</td>

                                    <td>
                                        {{ $row['account_id'] }}
                                    </td>

                                    <td>
                                        {{ $row['account_name'] }}

                                        @if ($isRetainedEarnings)
                                            <span class="badge bg-warning text-dark ms-2">
                                                Includes P&amp;L
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        {{ $row['category'] }}
                                    </td>

                                    <td class="text-end">
                                        {{ $formatAmount($row['balance']) }}
                                    </td>

                                    <td class="text-end">
                                        {{ $row['opening_debit'] > 0
                                            ? $formatAmount($row['opening_debit'])
                                            : '' }}
                                    </td>

                                    <td class="text-end">
                                        {{ $row['opening_credit'] > 0
                                            ? $formatAmount($row['opening_credit'])
                                            : '' }}
                                    </td>
                                </tr>

                            @empty
                                <tr>
                                    <td colspan="7"
                                        class="text-center text-muted py-4">

                                        No liability or equity balances were found.
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>

                        @if (count($liabilityRows) > 0)
                            <tfoot class="table-light fw-semibold">
                                <tr>
                                    <td colspan="4" class="text-end">
                                        Liability and Equity Totals
                                    </td>

                                    <td class="text-end">
                                        {{ $formatAmount(
                                            collect($liabilityRows)->sum('balance')
                                        ) }}
                                    </td>

                                    <td class="text-end">
                                        {{ $formatAmount(
                                            collect($liabilityRows)->sum('opening_debit')
                                        ) }}
                                    </td>

                                    <td class="text-end">
                                        {{ $formatAmount(
                                            collect($liabilityRows)->sum('opening_credit')
                                        ) }}
                                    </td>
                                </tr>
                            </tfoot>
                        @endif

                    </table>
                </div>

            </div>
        </div>


        {{-- ========================================================= --}}
        {{-- Final action area --}}
        {{-- ========================================================= --}}
        <div class="card">
            <div class="card-body">

                <div class="row align-items-center">

                    <div class="col-lg-8">
                        <h5 class="mb-2">
                            Proposed Closing
                        </h5>

                        <p class="text-muted mb-0">
                            The final process will create

                            <strong>
                                {{ number_format($numberOfOpeningRows) }}
                            </strong>

                            beginning-balance records dated

                            <strong>
                                {{ \Carbon\Carbon::parse($openingDate)->format('m/d/Y') }}
                            </strong>

                            and transfer the current-year profit or loss to
                            Retained Earnings account 164.
                        </p>
                    </div>


                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">

                        <button type="button"
                                id="closeFiscalYearButton"
                                class="btn btn-success"
                                {{ !$canClose ? 'disabled' : '' }}>

                            <i class="fas fa-lock me-1"></i>
                            Close Fiscal Year {{ $fiscalYear }}
                        </button>

                    </div>

                </div>

                @if ($canClose)
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="fas fa-info-circle me-2"></i>

                        The closing calculations are valid. The button is shown
                        for screen testing only. The final save method and
                        confirmation process will be added in the next step.
                    </div>
                @endif

            </div>
        </div>

    </div>
</div>

@endsection


@push('styles')
<style>
    .table th {
        white-space: nowrap;
        vertical-align: middle;
    }

    .table td {
        vertical-align: middle;
    }

    .font-size-18 {
        font-size: 18px;
    }

    .font-size-20 {
        font-size: 20px;
    }

    .bg-success-subtle {
        background-color: rgba(52, 195, 143, 0.15);
    }

    .bg-danger-subtle {
        background-color: rgba(244, 106, 106, 0.15);
    }

    @media print {
        form,
        #closeFiscalYearButton,
        .page-title-right {
            display: none !important;
        }

        .card {
            break-inside: avoid;
            box-shadow: none !important;
            border: 1px solid #cccccc;
        }
    }
</style>
@endpush


@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const closeButton = document.getElementById('closeFiscalYearButton');

        if (!closeButton || closeButton.disabled) {
            return;
        }

        closeButton.addEventListener('click', function () {
            alert(
                'The preview is balanced and ready. ' +
                'The final fiscal-year closing save method will be added next.'
            );
        });
    });
</script>
@endpush
