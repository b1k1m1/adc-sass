@extends('admin.admin_dashboard')

@section('admin')

<div class="page-content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-12">

                <div class="card">

                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            Bank Reconciliation History Report
                        </h4>
                    </div>

                    <div class="card-body">

                        {{-- -------------------------------------------------------------
                             Filter Section
                        -------------------------------------------------------------- --}}
                        <form method="GET"
                              action="{{ route('bankrecon.history') }}"
                              class="mb-4">

                            <div class="row align-items-end g-3">

                                {{-- Bank --}}
                                <div class="col-md-4">

                                    <label class="form-label">
                                        Bank
                                    </label>

                                    <select name="bank_id"
                                            id="bank_id"
                                            class="form-select"
                                            required>

                                        <option value="">
                                            Select Bank
                                        </option>

                                        @foreach($banks as $bankRow)

                                            <option value="{{ trim($bankRow->account_id) }}"
                                                {{ $selectedBank == trim($bankRow->account_id) ? 'selected' : '' }}>

                                                {{ trim($bankRow->account_id) }}
                                                -
                                                {{ $bankRow->account_name }}

                                            </option>

                                        @endforeach

                                    </select>

                                </div>


                                {{-- Year --}}
                                <div class="col-md-2">

                                    <label class="form-label">
                                        Year
                                    </label>

                                    <select name="year"
                                            id="year"
                                            class="form-select"
                                            required>

                                        <option value="">
                                            Select Year
                                        </option>

                                        @foreach($years as $year)

                                            <option value="{{ $year }}"
                                                {{ (int) $selectedYear === (int) $year ? 'selected' : '' }}>

                                                {{ $year }}

                                            </option>

                                        @endforeach

                                    </select>

                                </div>


                                {{-- Month --}}
                                <div class="col-md-2">

                                    <label class="form-label">
                                        Month
                                    </label>

                                    <select name="month"
                                            id="month"
                                            class="form-select"
                                            required>

                                        <option value="">
                                            Select Month
                                        </option>

                                        @foreach([
                                            1  => 'January',
                                            2  => 'February',
                                            3  => 'March',
                                            4  => 'April',
                                            5  => 'May',
                                            6  => 'June',
                                            7  => 'July',
                                            8  => 'August',
                                            9  => 'September',
                                            10 => 'October',
                                            11 => 'November',
                                            12 => 'December',
                                        ] as $monthNo => $monthName)

                                            <option value="{{ $monthNo }}"
                                                {{ (int) $selectedMonth === (int) $monthNo ? 'selected' : '' }}>

                                                {{ $monthName }}

                                            </option>

                                        @endforeach

                                    </select>

                                </div>


                                {{-- View --}}
                                <div class="col-md-2">

                                    <button type="submit"
                                            class="btn btn-primary w-100">

                                        View Report

                                    </button>

                                </div>


                                {{-- Clear --}}
                                <div class="col-md-2">

                                    <a href="{{ route('bankrecon.history') }}"
                                       class="btn btn-secondary w-100">

                                        Clear

                                    </a>

                                </div>

                            </div>

                        </form>


                        {{-- -------------------------------------------------------------
                             Message
                        -------------------------------------------------------------- --}}
                        @if($message)

                            <div class="alert alert-warning">
                                {{ $message }}
                            </div>

                        @endif


                        {{-- -------------------------------------------------------------
                             Report
                        -------------------------------------------------------------- --}}
                        @if($reportData)

                            @php
                                $recon             = $reportData['recon'];
                                $bank              = $reportData['bank'];
                                $clearedDeposits   = $reportData['clearedDeposits'];
                                $unclearedDeposits = $reportData['unclearedDeposits'];
                                $clearedPayments   = $reportData['clearedPayments'];
                                $unclearedPayments = $reportData['unclearedPayments'];
                                $totals            = $reportData['totals'];

                                $monthName = \Carbon\Carbon::create(
                                    (int) $recon->period_year,
                                    (int) $recon->period_month,
                                    1
                                )->format('F Y');
                            @endphp


                            {{-- ---------------------------------------------------------
                                 Export Buttons
                            ---------------------------------------------------------- --}}
                            <div class="row mb-3">

                                <div class="col-md-8">

                                    <h5 class="mb-1">
                                        {{ $bank->account_name ?? trim($recon->bank_id) }}
                                    </h5>

                                    <div class="text-muted">
                                        Reconciliation Period:
                                        <strong>{{ $monthName }}</strong>
                                    </div>

                                </div>

                                <div class="col-md-4 text-md-end mt-2 mt-md-0">

                                    <a href="{{ route('bankrecon.history.pdf', [
                                                'bank_id' => trim($recon->bank_id),
                                                'year'    => $recon->period_year,
                                                'month'   => $recon->period_month,
                                            ]) }}"
                                       target="_blank"
                                       class="btn btn-danger">

                                        Export PDF

                                    </a>

                                    <a href="{{ route('bankrecon.history.excel', [
                                                'bank_id' => trim($recon->bank_id),
                                                'year'    => $recon->period_year,
                                                'month'   => $recon->period_month,
                                            ]) }}"
                                       class="btn btn-success">

                                        Export Excel

                                    </a>

                                </div>

                            </div>


                            {{-- ---------------------------------------------------------
                                 Reconciliation Summary
                            ---------------------------------------------------------- --}}
                            <div class="card border mb-4">

                                <div class="card-header bg-light">
                                    <strong>
                                        Reconciliation Summary
                                    </strong>
                                </div>

                                <div class="card-body">

                                    <div class="row">

                                        <div class="col-md-3 mb-3">

                                            <div class="text-muted small">
                                                Opening Balance
                                            </div>

                                            <div class="fs-5 fw-bold">
                                                ${{ number_format((float) $recon->opening_balance, 2) }}
                                            </div>

                                        </div>


                                        <div class="col-md-3 mb-3">

                                            <div class="text-muted small">
                                                Statement Ending Balance
                                            </div>

                                            <div class="fs-5 fw-bold">
                                                ${{ number_format((float) $recon->closing_balance, 2) }}
                                            </div>

                                        </div>


                                        <div class="col-md-3 mb-3">

                                            <div class="text-muted small">
                                                Cleared Balance
                                            </div>

                                            <div class="fs-5 fw-bold">
                                                ${{ number_format((float) $totals['cleared_balance'], 2) }}
                                            </div>

                                        </div>


                                        <div class="col-md-3 mb-3">

                                            <div class="text-muted small">
                                                Difference
                                            </div>

                                            <div class="fs-5 fw-bold
                                                {{ abs((float) $totals['difference']) < 0.005 ? 'text-success' : 'text-danger' }}">

                                                ${{ number_format((float) $totals['difference'], 2) }}

                                            </div>

                                        </div>

                                    </div>


                                    <div class="row">

                                        <div class="col-md-3">

                                            <div class="text-muted small">
                                                Statement Start
                                            </div>

                                            <div>
                                                {{ \Carbon\Carbon::parse($recon->opening_dt)->format('m/d/Y') }}
                                            </div>

                                        </div>


                                        <div class="col-md-3">

                                            <div class="text-muted small">
                                                Statement End
                                            </div>

                                            <div>
                                                {{ \Carbon\Carbon::parse($recon->closing_dt)->format('m/d/Y') }}
                                            </div>

                                        </div>


                                        <div class="col-md-3">

                                            <div class="text-muted small">
                                                Status
                                            </div>

                                            <div>
                                                <span class="badge bg-success">
                                                    {{ $recon->status }}
                                                </span>
                                            </div>

                                        </div>


                                        <div class="col-md-3">

                                            <div class="text-muted small">
                                                Closed On
                                            </div>

                                            <div>
                                                {{ $recon->closed_dt
                                                    ? \Carbon\Carbon::parse($recon->closed_dt)->format('m/d/Y h:i A')
                                                    : '-' }}
                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>


                            {{-- =========================================================
                                 CLEARED DEPOSITS
                            ========================================================== --}}
                            <div class="card border mb-4">

                                <div class="card-header bg-light d-flex justify-content-between">

                                    <strong>
                                        Cleared Deposits
                                    </strong>

                                    <span>
                                        {{ $totals['cleared_deposit_count'] }} Transactions
                                        |
                                        ${{ number_format((float) $totals['cleared_deposits'], 2) }}
                                    </span>

                                </div>

                                <div class="card-body p-0">

                                    <div class="table-responsive">

                                        <table class="table table-sm table-bordered table-striped mb-0">

                                            <thead>
                                                <tr>
                                                    <th style="width:100px;">Date</th>
                                                    <th style="width:90px;">Trans #</th>
                                                    <th style="width:90px;">Ref #</th>
                                                    <th style="width:100px;">Check #</th>
                                                    <th>Description / Payee</th>
                                                    <th>Remarks</th>
                                                    <th class="text-end" style="width:120px;">Amount</th>
                                                </tr>
                                            </thead>

                                            <tbody>

                                                @forelse($clearedDeposits as $row)

                                                    <tr>

                                                        <td>
                                                            {{ \Carbon\Carbon::parse($row->trans_dt)->format('m/d/Y') }}
                                                        </td>

                                                        <td>
                                                            {{ $row->trans_no }}
                                                        </td>

                                                        <td>
                                                            {{ $row->ref_no }}
                                                        </td>

                                                        <td>
                                                            {{ $row->check_no }}
                                                        </td>

                                                        <td>
                                                            {{ $row->payto_name }}
                                                        </td>

                                                        <td>
                                                            {{ $row->remarks }}
                                                        </td>

                                                        <td class="text-end">
                                                            {{ number_format((float) $row->credit_amt, 2) }}
                                                        </td>

                                                    </tr>

                                                @empty

                                                    <tr>
                                                        <td colspan="7"
                                                            class="text-center text-muted">
                                                            No cleared deposits.
                                                        </td>
                                                    </tr>

                                                @endforelse

                                            </tbody>

                                            <tfoot>

                                                <tr class="fw-bold">

                                                    <td colspan="6"
                                                        class="text-end">
                                                        Total Cleared Deposits
                                                    </td>

                                                    <td class="text-end">
                                                        {{ number_format((float) $totals['cleared_deposits'], 2) }}
                                                    </td>

                                                </tr>

                                            </tfoot>

                                        </table>

                                    </div>

                                </div>

                            </div>


                            {{-- =========================================================
                                 UNCLEARED DEPOSITS
                            ========================================================== --}}
                            <div class="card border mb-4">

                                <div class="card-header bg-light d-flex justify-content-between">

                                    <strong>
                                        Uncleared Deposits
                                    </strong>

                                    <span>
                                        {{ $totals['uncleared_deposit_count'] }} Transactions
                                        |
                                        ${{ number_format((float) $totals['uncleared_deposits'], 2) }}
                                    </span>

                                </div>

                                <div class="card-body p-0">

                                    <div class="table-responsive">

                                        <table class="table table-sm table-bordered table-striped mb-0">

                                            <thead>
                                                <tr>
                                                    <th style="width:100px;">Date</th>
                                                    <th style="width:90px;">Trans #</th>
                                                    <th style="width:90px;">Ref #</th>
                                                    <th style="width:100px;">Check #</th>
                                                    <th>Description / Payee</th>
                                                    <th>Remarks</th>
                                                    <th class="text-end" style="width:120px;">Amount</th>
                                                </tr>
                                            </thead>

                                            <tbody>

                                                @forelse($unclearedDeposits as $row)

                                                    <tr>

                                                        <td>
                                                            {{ \Carbon\Carbon::parse($row->trans_dt)->format('m/d/Y') }}
                                                        </td>

                                                        <td>
                                                            {{ $row->trans_no }}
                                                        </td>

                                                        <td>
                                                            {{ $row->ref_no }}
                                                        </td>

                                                        <td>
                                                            {{ $row->check_no }}
                                                        </td>

                                                        <td>
                                                            {{ $row->payto_name }}
                                                        </td>

                                                        <td>
                                                            {{ $row->remarks }}
                                                        </td>

                                                        <td class="text-end">
                                                            {{ number_format((float) $row->credit_amt, 2) }}
                                                        </td>

                                                    </tr>

                                                @empty

                                                    <tr>
                                                        <td colspan="7"
                                                            class="text-center text-muted">
                                                            No uncleared deposits.
                                                        </td>
                                                    </tr>

                                                @endforelse

                                            </tbody>

                                            <tfoot>

                                                <tr class="fw-bold">

                                                    <td colspan="6"
                                                        class="text-end">
                                                        Total Uncleared Deposits
                                                    </td>

                                                    <td class="text-end">
                                                        {{ number_format((float) $totals['uncleared_deposits'], 2) }}
                                                    </td>

                                                </tr>

                                            </tfoot>

                                        </table>

                                    </div>

                                </div>

                            </div>


                            {{-- =========================================================
                                 CLEARED PAYMENTS
                            ========================================================== --}}
                            <div class="card border mb-4">

                                <div class="card-header bg-light d-flex justify-content-between">

                                    <strong>
                                        Cleared Payments
                                    </strong>

                                    <span>
                                        {{ $totals['cleared_payment_count'] }} Transactions
                                        |
                                        ${{ number_format((float) $totals['cleared_payments'], 2) }}
                                    </span>

                                </div>

                                <div class="card-body p-0">

                                    <div class="table-responsive">

                                        <table class="table table-sm table-bordered table-striped mb-0">

                                            <thead>

                                                <tr>
                                                    <th style="width:100px;">Date</th>
                                                    <th style="width:90px;">Trans #</th>
                                                    <th style="width:90px;">Ref #</th>
                                                    <th style="width:100px;">Check #</th>
                                                    <th>Payee</th>
                                                    <th>Remarks</th>
                                                    <th class="text-end" style="width:120px;">Amount</th>
                                                </tr>

                                            </thead>

                                            <tbody>

                                                @forelse($clearedPayments as $row)

                                                    <tr>

                                                        <td>
                                                            {{ \Carbon\Carbon::parse($row->trans_dt)->format('m/d/Y') }}
                                                        </td>

                                                        <td>
                                                            {{ $row->trans_no }}
                                                        </td>

                                                        <td>
                                                            {{ $row->ref_no }}
                                                        </td>

                                                        <td>
                                                            {{ $row->check_no }}
                                                        </td>

                                                        <td>
                                                            {{ $row->payto_name }}
                                                        </td>

                                                        <td>
                                                            {{ $row->remarks }}
                                                        </td>

                                                        <td class="text-end">
                                                            {{ number_format((float) $row->debit_amt, 2) }}
                                                        </td>

                                                    </tr>

                                                @empty

                                                    <tr>

                                                        <td colspan="7"
                                                            class="text-center text-muted">

                                                            No cleared payments.

                                                        </td>

                                                    </tr>

                                                @endforelse

                                            </tbody>

                                            <tfoot>

                                                <tr class="fw-bold">

                                                    <td colspan="6"
                                                        class="text-end">

                                                        Total Cleared Payments

                                                    </td>

                                                    <td class="text-end">
                                                        {{ number_format((float) $totals['cleared_payments'], 2) }}
                                                    </td>

                                                </tr>

                                            </tfoot>

                                        </table>

                                    </div>

                                </div>

                            </div>


                            {{-- =========================================================
                                 UNCLEARED PAYMENTS
                            ========================================================== --}}
                            <div class="card border mb-4">

                                <div class="card-header bg-light d-flex justify-content-between">

                                    <strong>
                                        Uncleared Payments
                                    </strong>

                                    <span>
                                        {{ $totals['uncleared_payment_count'] }} Transactions
                                        |
                                        ${{ number_format((float) $totals['uncleared_payments'], 2) }}
                                    </span>

                                </div>

                                <div class="card-body p-0">

                                    <div class="table-responsive">

                                        <table class="table table-sm table-bordered table-striped mb-0">

                                            <thead>

                                                <tr>
                                                    <th style="width:100px;">Date</th>
                                                    <th style="width:90px;">Trans #</th>
                                                    <th style="width:90px;">Ref #</th>
                                                    <th style="width:100px;">Check #</th>
                                                    <th>Payee</th>
                                                    <th>Remarks</th>
                                                    <th class="text-end" style="width:120px;">Amount</th>
                                                </tr>

                                            </thead>

                                            <tbody>

                                                @forelse($unclearedPayments as $row)

                                                    <tr>

                                                        <td>
                                                            {{ \Carbon\Carbon::parse($row->trans_dt)->format('m/d/Y') }}
                                                        </td>

                                                        <td>
                                                            {{ $row->trans_no }}
                                                        </td>

                                                        <td>
                                                            {{ $row->ref_no }}
                                                        </td>

                                                        <td>
                                                            {{ $row->check_no }}
                                                        </td>

                                                        <td>
                                                            {{ $row->payto_name }}
                                                        </td>

                                                        <td>
                                                            {{ $row->remarks }}
                                                        </td>

                                                        <td class="text-end">
                                                            {{ number_format((float) $row->debit_amt, 2) }}
                                                        </td>

                                                    </tr>

                                                @empty

                                                    <tr>

                                                        <td colspan="7"
                                                            class="text-center text-muted">

                                                            No uncleared payments.

                                                        </td>

                                                    </tr>

                                                @endforelse

                                            </tbody>

                                            <tfoot>

                                                <tr class="fw-bold">

                                                    <td colspan="6"
                                                        class="text-end">

                                                        Total Uncleared Payments

                                                    </td>

                                                    <td class="text-end">
                                                        {{ number_format((float) $totals['uncleared_payments'], 2) }}
                                                    </td>

                                                </tr>

                                            </tfoot>

                                        </table>

                                    </div>

                                </div>

                            </div>


                            {{-- ---------------------------------------------------------
                                 Final Summary
                            ---------------------------------------------------------- --}}
                            <div class="card border">

                                <div class="card-header bg-light">
                                    <strong>
                                        Final Reconciliation Summary
                                    </strong>
                                </div>

                                <div class="card-body">

                                    <div class="row">

                                        <div class="col-md-7 offset-md-5">

                                            <table class="table table-sm table-bordered mb-0">

                                                <tbody>

                                                    <tr>
                                                        <th>
                                                            Beginning Balance
                                                        </th>

                                                        <td class="text-end">
                                                            {{ number_format((float) $recon->opening_balance, 2) }}
                                                        </td>
                                                    </tr>


                                                    <tr>
                                                        <th>
                                                            Cleared Deposits
                                                        </th>

                                                        <td class="text-end">
                                                            {{ number_format((float) $totals['cleared_deposits'], 2) }}
                                                        </td>
                                                    </tr>


                                                    <tr>
                                                        <th>
                                                            Cleared Payments
                                                        </th>

                                                        <td class="text-end">
                                                            ({{ number_format((float) $totals['cleared_payments'], 2) }})
                                                        </td>
                                                    </tr>


                                                    <tr class="fw-bold">
                                                        <th>
                                                            Cleared Balance
                                                        </th>

                                                        <td class="text-end">
                                                            {{ number_format((float) $totals['cleared_balance'], 2) }}
                                                        </td>
                                                    </tr>


                                                    <tr>
                                                        <th>
                                                            Statement Ending Balance
                                                        </th>

                                                        <td class="text-end">
                                                            {{ number_format((float) $recon->closing_balance, 2) }}
                                                        </td>
                                                    </tr>


                                                    <tr class="fw-bold">
                                                        <th>
                                                            Difference
                                                        </th>

                                                        <td class="text-end
                                                            {{ abs((float) $totals['difference']) < 0.005 ? 'text-success' : 'text-danger' }}">

                                                            {{ number_format((float) $totals['difference'], 2) }}

                                                        </td>
                                                    </tr>


                                                    <tr>
                                                        <th>
                                                            Outstanding Deposits
                                                        </th>

                                                        <td class="text-end">
                                                            {{ number_format((float) $totals['uncleared_deposits'], 2) }}
                                                        </td>
                                                    </tr>


                                                    <tr>
                                                        <th>
                                                            Outstanding Payments
                                                        </th>

                                                        <td class="text-end">
                                                            {{ number_format((float) $totals['uncleared_payments'], 2) }}
                                                        </td>
                                                    </tr>


                                                    <tr class="fw-bold">
                                                        <th>
                                                            Adjusted Book Balance
                                                        </th>

                                                        <td class="text-end">
                                                            {{ number_format((float) $totals['adjusted_book_balance'], 2) }}
                                                        </td>
                                                    </tr>

                                                </tbody>

                                            </table>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        @endif

                    </div>

                </div>

            </div>
        </div>

    </div>
</div>

@endsection