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
                <a href="{{ route('withbalancetrialbalance.monthwise.pdf', request()->all()) }}" class="btn btn-danger">
                    Export to PDF
                </a>
            </div>
            <div class="col-auto">
                <a href="{{ route('withbalancetrialbalance.monthwise.excel', request()->all()) }}" class="btn btn-success no-print">
                    Export to Excel
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
            @include('accounts.partials.trialbalance_balance_monthwise_table', [
            'months' => $months,
            'groupedByType' => $groupedByType,
            'data' => $data
            ])
        </table>
    </div>
</div>
@endsection
