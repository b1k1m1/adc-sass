@extends('admin.admin_dashboard')
@section('admin')

<div class="page-content">
    <div class="container-fluid">
        <h4 class="mb-3">Profit & Loss Account</h4>
    </div>
    <form method="get" class="mb-3">
        <div class="row g-2 align-items-end">

            <!-- LEFT SIDE CONTROLS -->
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
                <a href="{{ route('profitloss.pdf', request()->all()) }}" class="btn btn-danger">Export to PDF</a>
                <a href="{{ route('profitloss.excel', request()->all()) }}" class="btn btn-success">Export to Excel</a>
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
    <h6 class='text-center'>Profit & Loss Account from {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</h6>
    <hr>

    <div class="row">
        <div class="col-md-6">
            <h5 class="text-center">Income</h5>
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>GL ID</th>
                        <th>Category</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($incomeRows as $row)
                        <tr>
                            <td>{{ $row['gl_id'] }}</td>
                            <td>{{ $row['category'] }}</td>
                            <td>{{ $row['code'] }}</td>
                            <td>{{ $row['name'] }}</td>
                            <!--<td class="text-end">{{ number_format($row['amount'], 2) }}</td> -->
                            <td class="text-end">
                                <a href="{{ route('profitloss.details', [
                                    'party_cd' => $row['code'],
                                    'start_date' => $startDate,
                                    'end_date' => $endDate
                                ]) }}" target="_blank">
                                    {{ number_format($row['amount'], 2) }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    <tr class="table-success fw-bold">
                        <td colspan="4" class="text-end">Total Income</td>
                        <td class="text-end">{{ number_format($totalIncome, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-md-6">
            <h5 class="text-center">Expenses</h5>
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>GL ID</th>
                        <th>Category</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expenseRows as $row)
                        <tr>
                            <td>{{ $row['gl_id'] }}</td>
                            <td>{{ $row['category'] }}</td>
                            <td>{{ $row['code'] }}</td>
                            <td>{{ $row['name'] }}</td>
                            <!--<td class="text-end">{{ number_format($row['amount'], 2) }}</td> -->
                            <td class="text-end">
                                <a href="{{ route('profitloss.details', [
                                    'party_cd' => $row['code'],
                                    'start_date' => $startDate,
                                    'end_date' => $endDate
                                ]) }}" target="_blank">
                                    {{ number_format($row['amount'], 2) }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    <tr class="table-danger fw-bold">
                        <td colspan="4" class="text-end">Total Expenses</td>
                        <td class="text-end">{{ number_format($totalExpense, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <hr>
    <h4 class="text-center">
        Net Profit / (Loss): <span class="{{ $netProfit < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($netProfit, 2) }}</span>
    </h4>
</div>
@endsection
