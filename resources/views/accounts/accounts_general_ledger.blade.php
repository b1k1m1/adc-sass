@extends('admin.admin_dashboard')
@section('admin')
<style>
    @media print {
        .no-print {
            display: none !important;
        }
    }
</style>
<div class="page-content">
    <div class="container-fluid">
        <form method="get" class="mb-3">
             <div class="row g-2 align-items-end">

        <!-- LEFT SIDE CONTROLS -->
        <div class="col d-flex gap-2 flex-wrap align-items-end">

            <div class="col-auto">
                <label class="no-print">Date From</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="form-control no-print">
            </div>

            <div class="col-auto">
                <label class="no-print">Date To</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="form-control no-print">
            </div>

            <div class="col-auto no-print">
                <button class="btn btn-primary" type="submit">Show</button>
            </div>

            <div class="col-auto">
                <button class="btn btn-secondary no-print" type="button" onclick="window.print()">
                    Print
                </button>
            </div>

            <div class="col-auto">
                <a href="{{ route('general.ledger.pdf', request()->all()) }}" class="btn btn-danger no-print">
                    Export to PDF
                </a>
            </div>

        </div>

        <!-- RIGHT SIDE BACK BUTTON -->
        <div class="col-auto ms-auto">
            <a href="{{ route('accounts.dashboard') }}#profile1" class="btn btn-primary no-print">
                {{ t('common.back','Back') }}
            </a>
        </div>

    </div>
        </form>
        <h4 class="mt-3">{{ $company->comp_nm ?? 'Company Name'  }}</h4>
        <h6>General Ledger from {{ \Carbon\Carbon::parse($startDate)->format('m/d/Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('m/d/Y') }}</h6>
        <hr>
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Particulars</th>
                    <th>Ref #</th>
                    <th>Debit Amount</th>
                    <th>Credit Amount</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $current_party = null;
                    $account_name = '';
                    $total_debit = 0;
                    $total_credit = 0;
                @endphp

                @foreach($report as $row)
                    {{-- Detect start of a new account group --}}
                    @if($current_party !== $row->party_cd)
                        {{-- Print footer for previous account --}}
                        @if($current_party !== null)
                            <tr>
                                <td colspan="3" style="text-align:right">Total</td>
                                <td style="text-align:right">{{ number_format($total_debit, 2) }}</td>
                                <td style="text-align:right">{{ number_format($total_credit, 2) }}</td>
                            </tr>
                            @php
                                $balance = $total_debit - $total_credit;
                                $grand_total = max($total_debit, $total_credit);
                            @endphp

                            @if($balance > 0)
                                {{-- Debit > Credit: BY BALANCE C/F in credit column --}}
                                <tr>
                                    <td colspan="3" style="text-align:right"><b>BY BALANCE C/F</b></td>
                                    <td></td>
                                    <td style="text-align:right"><b>{{ number_format($balance,2) }}</b></td>
                                </tr>
                            @elseif($balance < 0)
                                {{-- Credit > Debit: TO BALANCE C/F in debit column --}}
                                <tr>
                                    <td colspan="3" style="text-align:right"><b>TO BALANCE C/F</b></td>
                                    <td style="text-align:right"><b>{{ number_format(abs($balance),2) }}</b></td>
                                    <td></td>
                                </tr>
                            @endif

                            <tr>
                                <td colspan="3" style="text-align:right"><b>Grand Total</b></td>
                                <td style="text-align:right"><b>{{ number_format($grand_total, 2) }}</b></td>
                                <td style="text-align:right"><b>{{ number_format($grand_total, 2) }}</b></td>
                            </tr>
                        @endif

                        {{-- Start new account --}}
                        @php
                            $current_party = $row->party_cd;
                            $account_name = $accountNames[$current_party] ?? $current_party;
                            $total_debit = 0;
                            $total_credit = 0;
                        @endphp
                        <tr>
                            <td colspan="5" class="fw-bold text-center bg-light">
                                {{ $account_name }} ({{ $current_party }})
                            </td>
                        </tr>
                    @endif

                    {{-- Accumulate totals for this account --}}
                    @php
                        $total_debit += $row->debit_amt;
                        $total_credit += $row->credit_amt;
                    @endphp

                    {{-- Transaction row --}}
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($row->trans_dt)->format('m/d/Y') }}</td>
                        <td>
                            {{ $row->payto_name }}
                            <br>
                            <small>{{ $row->remarks }}</small>
                        </td>
                        <td>{{ $row->ref_no }}</td>
                        <td style="text-align:right">{{ number_format($row->debit_amt,2) }}</td>
                        <td style="text-align:right">{{ number_format($row->credit_amt,2) }}</td>
                    </tr>
                @endforeach

                {{-- Print footer for the last account --}}
                @if($current_party !== null)
                    <tr>
                        <td colspan="3" style="text-align:right">Total</td>
                        <td style="text-align:right">{{ number_format($total_debit, 2) }}</td>
                        <td style="text-align:right">{{ number_format($total_credit, 2) }}</td>
                    </tr>
                    @php
                        $balance = $total_debit - $total_credit;
                        $grand_total = max($total_debit, $total_credit);
                    @endphp

                    @if($balance > 0)
                        {{-- Debit > Credit: BY BALANCE C/F in credit column --}}
                        <tr>
                            <td colspan="3" style="text-align:right"><b>BY BALANCE C/F</b></td>
                            <td></td>
                            <td style="text-align:right"><b>{{ number_format($balance,2) }}</b></td>
                        </tr>
                    @elseif($balance < 0)
                        {{-- Credit > Debit: TO BALANCE C/F in debit column --}}
                        <tr>
                            <td colspan="3" style="text-align:right"><b>TO BALANCE C/F</b></td>
                            <td style="text-align:right"><b>{{ number_format(abs($balance),2) }}</b></td>
                            <td></td>
                        </tr>
                    @endif

                    <tr>
                        <td colspan="3" style="text-align:right"><b>Grand Total</b></td>
                        <td style="text-align:right"><b>{{ number_format($grand_total, 2) }}</b></td>
                        <td style="text-align:right"><b>{{ number_format($grand_total, 2) }}</b></td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection
