@extends('admin.admin_dashboard')

@section('admin')

<style>
    @media print {
        .no-print {
            display: none !important;
        }
    }

    .select2-container {
        min-width: 330px;
    }

    .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1px solid #ced4da !important;
    }

    .select2-container
    .select2-selection--single
    .select2-selection__rendered {
        line-height: 36px !important;
        padding-left: 12px;
    }

    .select2-container
    .select2-selection--single
    .select2-selection__arrow {
        height: 36px !important;
    }
</style>

<div class="page-content">
    <div class="container-fluid">

        {{-- =========================================================
             SEARCH CRITERIA
        ========================================================== --}}
        <form method="GET"
              action="{{ route('general.ledger') }}"
              class="mb-3"
              id="accountGeneralLedgerForm">

            <div class="row g-2 align-items-end">

                {{-- LEFT SIDE CONTROLS --}}
                <div class="col d-flex gap-2 flex-wrap align-items-end">

                    {{-- Account dropdown --}}
                    <div class="col-auto no-print">
                        <label for="party_cd">
                            Account Head
                        </label>

                        <select name="party_cd"
                                id="party_cd"
                                class="form-select select2-account"
                                required>

                            <option value="">Select Account</option>

                            @foreach($accounts as $account)
                                <option value="{{ $account->party_cd }}"
                                    {{ (string) $selectedPartyCd ===
                                       (string) $account->party_cd
                                        ? 'selected'
                                        : '' }}>

                                    {{ $account->party_cd }}
                                    -
                                    {{ $account->party_nm }}
                                </option>
                            @endforeach
                        </select>

                        @error('party_cd')
                            <div class="text-danger">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Date From --}}
                    <div class="col-auto">
                        <label for="start_date" class="no-print">
                            Date From
                        </label>

                        <input type="text"
                               name="start_date"
                               id="start_date"
                               value="{{ $startDate }}"
                               class="form-control no-print ledger-date"
                               autocomplete="off"
                               required>

                        @error('start_date')
                            <div class="text-danger no-print">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Date To --}}
                    <div class="col-auto">
                        <label for="end_date" class="no-print">
                            Date To
                        </label>

                        <input type="text"
                               name="end_date"
                               id="end_date"
                               value="{{ $endDate }}"
                               class="form-control no-print ledger-date"
                               autocomplete="off"
                               required>

                        @error('end_date')
                            <div class="text-danger no-print">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Show --}}
                    <div class="col-auto no-print">
                        <button class="btn btn-primary" type="submit">
                            Show
                        </button>
                    </div>

                    {{-- Print --}}
                    @if(!empty($selectedPartyCd))
                        <div class="col-auto">
                            <button class="btn btn-secondary no-print"
                                    type="button"
                                    onclick="window.print()">
                                Print
                            </button>
                        </div>
                    @endif

                    {{-- PDF --}}
                    @if(!empty($selectedPartyCd))
                        <div class="col-auto">
                            <a href="{{ route('general.ledger.party.pdf', [
                                    'party_cd'   => $selectedPartyCd,
                                    'start_date' => $startDate,
                                    'end_date'   => $endDate,
                                ]) }}"
                               class="btn btn-danger no-print"
                               target="_blank">

                                Export to PDF
                            </a>
                        </div>
                    @endif

                </div>

                {{-- RIGHT SIDE BACK BUTTON --}}
                <div class="col-auto ms-auto">
                    <a href="{{ route('accounts.dashboard') }}#profile1"
                       class="btn btn-primary no-print">

                        {{ t('common.back', 'Back') }}
                    </a>
                </div>

            </div>
        </form>

        {{-- =========================================================
             REPORT HEADER
        ========================================================== --}}
        <h4 class="mt-3">
            {{ $company->comp_nm ?? 'Company Name' }}
        </h4>

        @if(!empty($selectedPartyCd) && $selectedAccount)

            <h6 class="mb-1">
                General Ledger from
                {{ \Carbon\Carbon::parse($startDate)->format('m/d/Y') }}
                to
                {{ \Carbon\Carbon::parse($endDate)->format('m/d/Y') }}
            </h6>

            <h6>
                Account:
                {{ $selectedAccount->party_nm }}
                ({{ $selectedAccount->party_cd }})
            </h6>

        @else

            <h6>
                Select an account head and date range to generate
                the General Ledger.
            </h6>

        @endif

        <hr>

        {{-- =========================================================
             REPORT TABLE
        ========================================================== --}}
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

                @if(!empty($selectedPartyCd) && $selectedAccount)

                    {{-- Selected account heading --}}
                    <tr>
                        <td colspan="5"
                            class="fw-bold text-center bg-light">

                            {{ $selectedAccount->party_nm }}
                            ({{ $selectedAccount->party_cd }})
                        </td>
                    </tr>

                    @forelse($report as $row)

                        <tr>
                            {{-- Date --}}
                            <td>
                                {{ \Carbon\Carbon::parse(
                                    $row->trans_dt
                                )->format('m/d/Y') }}
                            </td>

                            {{-- Particulars --}}
                            <td>
                                {{ $row->payto_name }}

                                @if(!empty($row->remarks))
                                    <br>
                                    <small>
                                        {{ $row->remarks }}
                                    </small>
                                @endif
                            </td>

                            {{-- Reference --}}
                            <td>
                                {{ $row->ref_no }}
                            </td>

                            {{-- Debit --}}
                            <td style="text-align:right">
                                @if((float) $row->debit_amt != 0)
                                    {{ number_format(
                                        (float) $row->debit_amt,
                                        2
                                    ) }}
                                @endif
                            </td>

                            {{-- Credit --}}
                            <td style="text-align:right">
                                @if((float) $row->credit_amt != 0)
                                    {{ number_format(
                                        (float) $row->credit_amt,
                                        2
                                    ) }}
                                @endif
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="5"
                                class="text-center text-muted">

                                No transactions found for the selected
                                account and date range.
                            </td>
                        </tr>

                    @endforelse

                    {{-- Only show totals when records exist --}}
                    @if($report->isNotEmpty())

                        {{-- Transaction totals --}}
                        <tr>
                            <td colspan="3"
                                style="text-align:right">

                                Total
                            </td>

                            <td style="text-align:right">
                                {{ number_format($totalDebit, 2) }}
                            </td>

                            <td style="text-align:right">
                                {{ number_format($totalCredit, 2) }}
                            </td>
                        </tr>

                        @php
                            $balance =
                                (float) $totalDebit -
                                (float) $totalCredit;

                            $grandTotal = max(
                                (float) $totalDebit,
                                (float) $totalCredit
                            );
                        @endphp

                        {{-- Debit balance --}}
                        @if($balance > 0)

                            <tr>
                                <td colspan="3"
                                    style="text-align:right">

                                    <b>BY BALANCE C/F</b>
                                </td>

                                <td></td>

                                <td style="text-align:right">
                                    <b>
                                        {{ number_format($balance, 2) }}
                                    </b>
                                </td>
                            </tr>

                        {{-- Credit balance --}}
                        @elseif($balance < 0)

                            <tr>
                                <td colspan="3"
                                    style="text-align:right">

                                    <b>TO BALANCE C/F</b>
                                </td>

                                <td style="text-align:right">
                                    <b>
                                        {{ number_format(
                                            abs($balance),
                                            2
                                        ) }}
                                    </b>
                                </td>

                                <td></td>
                            </tr>

                        @endif

                        {{-- Grand Total --}}
                        <tr>
                            <td colspan="3"
                                style="text-align:right">

                                <b>Grand Total</b>
                            </td>

                            <td style="text-align:right">
                                <b>
                                    {{ number_format($grandTotal, 2) }}
                                </b>
                            </td>

                            <td style="text-align:right">
                                <b>
                                    {{ number_format($grandTotal, 2) }}
                                </b>
                            </td>
                        </tr>

                    @endif

                @else

                    <tr>
                        <td colspan="5"
                            class="text-center text-muted">

                            Please select an account head and click Show.
                        </td>
                    </tr>

                @endif

            </tbody>
        </table>

    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function () {

        /*
        |--------------------------------------------------------------------------
        | Account Select2
        |--------------------------------------------------------------------------
        */
        $('.select2-account').select2({
            placeholder: 'Select Account',
            allowClear: true,
            width: '100%'
        });

        /*
        |--------------------------------------------------------------------------
        | Flatpickr
        |--------------------------------------------------------------------------
        | Display: mm/dd/yyyy
        | Submitted value: yyyy-mm-dd
        */
        flatpickr('.ledger-date', {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'm/d/Y',
            allowInput: true
        });

        /*
        |--------------------------------------------------------------------------
        | Account validation
        |--------------------------------------------------------------------------
        */
        $('#accountGeneralLedgerForm').on('submit', function (event) {

            if (!$('#party_cd').val()) {
                event.preventDefault();

                alert('Please select an account head.');

                $('#party_cd').select2('open');
            }
        });

    });
</script>
@endpush
