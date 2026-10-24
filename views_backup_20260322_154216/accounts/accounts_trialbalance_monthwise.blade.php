@extends('admin.admin_dashboard')
@section('admin')

<div class="page-content">
    <div class="container-fluid">
        <h4 class="mb-3">Trial Balance - Monthwise</h4>
    </div>
    <form method="get" class="mb-3">
        <div class="row g-2 align-items-end">
            <div class="col d-flex gap-2 flex-wrap align-items-end">
            <div class="col-auto">
                <label>Date From</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="form-control">
            </div>
            <div class="col-auto">
                <label>Date To</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="form-control">
            </div>
            <div class="col-auto">
                <button class="btn btn-primary" type="submit">Show</button>
            </div>
            <div class="col-auto">
                <a href="{{ route('trialbalance.monthwise.pdf', request()->all()) }}" class="btn btn-danger no-print">
                    Export to PDF
                </a>
            </div>
            </div>
            <div class="col-auto ms-auto">
                <a href="{{ route('accounts.dashboard') }}#profile1" class="btn btn-primary no-print">
                    Back
                </a>
            </div>
        </div>
    </form>
    <h4 class="mt-3 text-center">{{ $company->comp_nm ?? 'Company Name' }}</h4>
    <h6 class='text-center'>Trial Balance (Monthwise) from {{ \Carbon\Carbon::parse($startDate)->format('m/d/Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('m/d/Y') }}</h6>
    <hr>
    <div style="overflow-x:auto;">
    <table class="table table-bordered table-sm">
        <thead>
            <tr>
                <th>GL ID</th>
                <th>Category</th>
                <th>Account Code</th>
                <th>Account Name</th>
                @foreach($months as $month)
                    <th colspan="2" class="text-center">{{ \Carbon\Carbon::parse($month.'-01')->format('M Y') }}</th>
                @endforeach
            </tr>
            <tr>
                <th colspan="4"></th>
                @foreach($months as $month)
                    <th class="text-end">Debit</th>
                    <th class="text-end">Credit</th>
                @endforeach
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
            @foreach($groupedByType as $type => $accounts)
                <tr class="table-secondary">
                    <td colspan="{{ 4 + (count($months)*2) }}"><strong>{{ $typeNames[$type] ?? $type }}</strong></td>
                </tr>
                @foreach($accounts as $acc)
                    <tr>
                        <td>{{ $acc['info']->gl_id }}</td>
                        <td>{{ $acc['info']->category_name }}</td>
                        <td>{{ $acc['info']->party_cd }}</td>
                        <td>{{ $acc['info']->party_nm }}</td>
                        @foreach($months as $month)
                            <td class="text-end">{{ $acc['months'][$month]['debit'] > 0 ? number_format($acc['months'][$month]['debit'],2) : '' }}</td>
                            <td class="text-end">{{ $acc['months'][$month]['credit'] > 0 ? number_format($acc['months'][$month]['credit'],2) : '' }}</td>
                        @endforeach
                    </tr>
                @endforeach
            @endforeach

            @php
            // Calculate monthwise totals
            $monthTotals = [];
            foreach($months as $month) {
                $monthTotals[$month]['debit'] = 0;
                $monthTotals[$month]['credit'] = 0;
            }
            foreach($data as $acc) {
                foreach($months as $month) {
                    $monthTotals[$month]['debit'] += $acc['months'][$month]['debit'];
                    $monthTotals[$month]['credit'] += $acc['months'][$month]['credit'];
                }
            }
            @endphp
            <tr class="table-dark fw-bold">
                <td colspan="4" class="text-end">Grand Total</td>
                @foreach($months as $month)
                    <td class="text-end">{{ number_format($monthTotals[$month]['debit'],2) }}</td>
                    <td class="text-end">{{ number_format($monthTotals[$month]['credit'],2) }}</td>
                @endforeach
            </tr>
        </tbody>
    </table>
    </div>
</div>
@endsection
