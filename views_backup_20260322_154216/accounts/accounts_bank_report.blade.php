@extends('admin.admin_dashboard')
@section('admin')

<div class="page-content">
    <div class="container-fluid">

<style>
@media print {
        .no-print {
            display: none !important;
        }
    }
</style>
        <form method="get" class="mb-3">
                <div class="row g-2 align-items-end">
                     <!-- LEFT SIDE CONTROLS -->
                    <div class="col d-flex gap-2 flex-wrap align-items-end">
                    <div class="col-auto">
                        <label class="no-print">Date From</label>
                        <input type="date" name="start_date" value="{{ $start_date }}" class="form-control no-print">
                    </div>
                    <div class="col-auto">
                        <label class="no-print">Date To</label>
                            <input type="date" name="end_date" value="{{ $end_date }}" class="form-control no-print">
                    </div>
                    <div class="col-auto">
                        <label for="account_id" class="form-label no-print">Bank Account</label>
                        <select name="account_id" id="account_id" class="form-select no-print">
                            <option value="">Select Bank</option>
                            @foreach($fashbanks as $bank)
                                <option value="{{ $bank->account_id }}"
                                    {{ old('account_id', '1101') == $bank->account_id ? 'selected' : '' }}>
                                    {{ $bank->account_name }}
                                </option>
                            @endforeach
                            </select>
                    </div>
                    <div class="col-auto no-print">
                        <button class="btn btn-primary no-print" type="submit">Show</button>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-secondary no-print" type="button" onclick="window.print()">
                            Print
                        </button>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('bank.book.pdf', request()->all()) }}" class="btn btn-danger no-print">
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
        <h4 class="mt-3">{{ $company->comp_nm ?? 'Company Name' }}</h4>
            <h6>Bank Book from {{ \Carbon\Carbon::parse($start_date)->format('m/d/Y')}} to {{ \Carbon\Carbon::parse($end_date)->format('m/d/Y') }}</h6>
        <hr>
        <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Particulars</th>
                <th>Remarks
                <th>Ref No</th>
                <th>Check #</th>
                <th class="text-end">Debit Amt</th>
                <th class="text-end">Credit Amt</th>
            </tr>
        </thead>
        <tbody>
            {{-- Opening Balance Row --}}
            <tr>
                <td>{{ \Carbon\Carbon::parse($start_date)->format('m/d/Y') }}</td>
                <td>Opening Balance</td>
                <td></td>
                <td></td>
                <td></td>
                <td class="text-end">{{ number_format($op_debit_amt, 2) }}</td>
                <td class="text-end">{{ number_format($op_credit_amt, 2) }}</td>
            </tr>

            {{-- Detail Rows --}}
            @foreach($rows as $row)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($row->trans_dt)->format('m/d/Y') }}</td>
                    <td>{{ $row->payto_name }}</td>
                    <td>{{ $row->remarks }}</td>
                    <td>{{ $row->ref_no }}</td>
                    <td>{{ $row->check_no }}</td>
                    @if($row->debit_amt > 0)
                        <td style="text-align:right;">{{ number_format($row->debit_amt, 2) }}</td>
                    @else
                    <td></td>
                    @endif
                    @if($row->credit_amt > 0)
                        <td class="text-end">{{ number_format($row->credit_amt, 2) }}</td>
                    @else
                    <td></td>
                    @endif

                </tr>
            @endforeach

            {{-- Closing Balance Row --}}
            <tr>
                <td>{{ \Carbon\Carbon::parse($end_date)->format('m/d/Y') }}</td>
                <td>Balance C/f</td>
                <td></td>
                <td></td>
                <td></td>
                <td class="text-end">{{ number_format($cl_debit_amt, 2) }}</td>
                <td class="text-end">{{ number_format($cl_credit_amt, 2) }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-end">Totals</th>
                <th class="text-end">{{ number_format($totdr, 2) }}</th>
                <th class="text-end">{{ number_format($totcr, 2) }}</th>
            </tr>
        </tfoot>
    </table>

    </div>
</div>
@endsection
