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
        <h4 class="mb-3 no-print">Trial Balance</h4>
    </div>
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
                    <button class="btn btn-primary no-print" type="submit">Show</button>
                </div>
                <div class="col-auto">
                    <button class="btn btn-secondary no-print" type="button" onclick="window.print()">
                        Print
                    </button>
                </div>
                <div class="col-auto">
                    <a href="{{ route('trialbalance.pdf', request()->all()) }}" class="btn btn-danger no-print">
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
        <h4 class="mt-3 text-center">{{ $company->comp_nm ?? 'Company Name' }}</h4>
        <h6 class='text-center'>Trial Balance from {{ \Carbon\Carbon::parse($startDate)->format('m/d/Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('m/d/Y') }}</h6>
        <hr>
         <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th rowspan="2">GL ID</th>
                    <th rowspan="2">Category</th>
                    <th rowspan="2">Account Code</th>
                    <th rowspan="2">Account Name</th>
                    <th colspan="2" class="text-center">Beginning Balance</th>
                    <th colspan="2" class="text-center">Transaction</th>
                    <th colspan="2" class="text-center">Ending Balance</th>
                </tr>
                <tr>
                    <th class="text-end">Debit</th>
                    <th class="text-end">Credit</th>
                    <th class="text-end">Debit</th>
                    <th class="text-end">Credit</th>
                    <th class="text-end">Debit</th>
                    <th class="text-end">Credit</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $typeNames = [
                    'BA' => 'Balance Sheet Asset',
                    'BL' => 'Balance Sheet Liability',
                    'II' => 'Income',
                    'IE' => 'Expense',
                     ];
                @endphp
                @foreach($groupedByType as $type => $typeRows)
                    {{-- Group Header (Account Type) --}}
                    <tr class="table-secondary">
                        <td colspan="10"><strong>{{ $typeNames[$type] ?? $type }}</strong></td>
                    </tr>
                    @php
                        $typeSubtotal = [
                        'bb_debit_amt' => 0, 'bb_credit_amt' => 0,
                        'tr_debit_amt' => 0, 'tr_credit_amt' => 0,
                        'eb_debit_amt' => 0, 'eb_credit_amt' => 0
                            ];
                        $rowsByGL = $typeRows->groupBy('gl_id');
                    @endphp

                    @foreach($rowsByGL as $gl_id => $glRows)
                        {{-- GL Header --}}
                        <tr class="table-light">
                            <td><strong>{{ $gl_id }}</strong></td>
                            <td><strong>{{ $glRows[0]->category_name }}</strong></td>
                            <td colspan="8"></td>
                        </tr>
                        @php
                            $glSubtotal = [
                            'bb_debit_amt' => 0, 'bb_credit_amt' => 0,
                            'tr_debit_amt' => 0, 'tr_credit_amt' => 0,
                            'eb_debit_amt' => 0, 'eb_credit_amt' => 0
                            ];
                        @endphp

                    {{-- Accounts under this GL --}}
                        @foreach($glRows as $row)
                        <tr>
                            <td></td>
                            <td></td>
                            <td>{{ $row->party_cd }}</td>
                            <td>{{ $row->party_nm }}</td>
                            <td class="text-end">{{ $row->bb_debit_amt > 0 ? number_format($row->bb_debit_amt,2) : '' }}</td>
                            <td class="text-end">{{ $row->bb_credit_amt > 0 ? number_format($row->bb_credit_amt,2) : '' }}</td>
                            <td class="text-end">{{ $row->tr_debit_amt > 0 ? number_format($row->tr_debit_amt,2) : '' }}</td>
                            <td class="text-end">{{ $row->tr_credit_amt > 0 ? number_format($row->tr_credit_amt,2) : '' }}</td>
                            <td class="text-end">{{ $row->eb_debit_amt > 0 ? number_format($row->eb_debit_amt,2) : '' }}</td>
                            <td class="text-end">{{ $row->eb_credit_amt > 0 ? number_format($row->eb_credit_amt,2) : '' }}</td>
                        </tr>
                            @foreach($glSubtotal as $key => $v)
                                @php $glSubtotal[$key] += $row->$key; @endphp
                            @endforeach
                            @foreach($typeSubtotal as $key => $v)
                                @php $typeSubtotal[$key] += $row->$key; @endphp
                            @endforeach
                        @endforeach

                        {{-- GL Subtotal --}}
                        <tr class="table-light fw-bold">
                            <td colspan="4" class="text-end">Total for GL {{ $gl_id }}</td>
                            <td class="text-end">{{ number_format($glSubtotal['bb_debit_amt'],2) }}</td>
                            <td class="text-end">{{ number_format($glSubtotal['bb_credit_amt'],2) }}</td>
                            <td class="text-end">{{ number_format($glSubtotal['tr_debit_amt'],2) }}</td>
                            <td class="text-end">{{ number_format($glSubtotal['tr_credit_amt'],2) }}</td>
                            <td class="text-end">{{ number_format($glSubtotal['eb_debit_amt'],2) }}</td>
                            <td class="text-end">{{ number_format($glSubtotal['eb_credit_amt'],2) }}</td>
                        </tr>
                    @endforeach

                    {{-- Account Type Subtotal --}}
                    <tr class="table-secondary fw-bold">
                        <td colspan="4" class="text-end">Total {{ $typeNames[$type] ?? $type }}</td>
                        <td class="text-end">{{ number_format($typeSubtotal['bb_debit_amt'],2) }}</td>
                        <td class="text-end">{{ number_format($typeSubtotal['bb_credit_amt'],2) }}</td>
                        <td class="text-end">{{ number_format($typeSubtotal['tr_debit_amt'],2) }}</td>
                        <td class="text-end">{{ number_format($typeSubtotal['tr_credit_amt'],2) }}</td>
                        <td class="text-end">{{ number_format($typeSubtotal['eb_debit_amt'],2) }}</td>
                        <td class="text-end">{{ number_format($typeSubtotal['eb_credit_amt'],2) }}</td>
                    </tr>
                @endforeach

                @php
                    $all = $rows;
                @endphp
                <tr class="table-dark fw-bold">
                    <td colspan="4" class="text-end">Grand Total</td>
                    <td class="text-end">{{ number_format($all->sum('bb_debit_amt'),2) }}</td>
                    <td class="text-end">{{ number_format($all->sum('bb_credit_amt'),2) }}</td>
                    <td class="text-end">{{ number_format($all->sum('tr_debit_amt'),2) }}</td>
                    <td class="text-end">{{ number_format($all->sum('tr_credit_amt'),2) }}</td>
                    <td class="text-end">{{ number_format($all->sum('eb_debit_amt'),2) }}</td>
                    <td class="text-end">{{ number_format($all->sum('eb_credit_amt'),2) }}</td>
                </tr>
            </tbody>
         </table>
</div>
@endsection
