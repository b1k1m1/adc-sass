@extends('admin.admin_dashboard')
@section('admin')

<div class="page-content">
    <div class="container-fluid">
        <h4 class="mb-3">Balance Sheet</h4>
        <form method="get" action="{{ route('balancesheet') }}" class="mb-3">
            <div class="row g-2 align-items-end">

                <!-- LEFT SIDE CONTROLS -->
                <div class="col d-flex gap-2 flex-wrap align-items-end">

                <div class="col-auto">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" id="start_date" name="start_date" value="{{ request('start_date', now()->startOfYear()->format('Y-m-d')) }}" class="form-control">
                </div>
                <div class="col-auto">
                    <label for="end_date" class="form-label">As Of Date</label>
                    <input type="date" id="end_date" name="end_date" value="{{ request('end_date', now()->format('Y-m-d')) }}" class="form-control">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Show Balance Sheet</button>
                </div>
                <div class="col-auto">
                    <a href="{{ route('balancesheet.pdf', request()->all()) }}" class="btn btn-danger">Export to PDF</a>
                    <a href="{{ route('balancesheet.excel', request()->all()) }}" class="btn btn-success">Export to Excel</a>
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
        <h6 class='text-center'>Balance Sheet as of {{ \Carbon\Carbon::parse($asOfDate)->format('m/d/Y') }}</h6>
        <hr>
        <table width="100%" class="table table-bordered">
            <tr>
                <td valign="top" width="50%">
                    <b>Assets</b>
                    <table class="table table-sm">
                        @foreach($assets as $row)
                            <tr>
                                <td>{{ $row->category_name }}</td>
                                <td>{{ $row->party_nm }}</td>
                                <td class="text-end">{{ number_format($row->balance,2) }}</td>
                            </tr>
                        @endforeach
                        <tr class="fw-bold">
                            <td colspan="2" class="text-end">Total Assets</td>
                            <td class="text-end">{{ number_format($totalAssets,2) }}</td>
                        </tr>
                    </table>
                </td>
                <td valign="top" width="50%">
                    <b>Liabilities</b>
                    <table class="table table-sm">
                        @foreach($liabilities as $row)
                            <tr>
                                <td>{{ $row->category_name }}</td>
                                <td>{{ $row->party_nm }}</td>
                                <td class="text-end">{{ number_format($row->balance,2) }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="2" class="text-end">Current Year Profit/(Loss)</td>
                            <td class="text-end">{{ number_format($profitLoss,2) }}</td>
                        </tr>
                        <tr class="fw-bold">
                            <td colspan="2" class="text-end">Total Liabilities</td>
                            <td class="text-end">{{ number_format($totalLiabilities,2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</div>
@endsection
