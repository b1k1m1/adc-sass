@extends('admin.admin_dashboard')
@section('admin')

<div class="page-content">
    <div class="container-fluid">
        <h4 class="mb-3">Profit & Loss Account</h4>
    </div>
    <form id="pl-form" method="get" action="{{ route('profitloss.new') }}" class="mb-3">
    <div class="row g-2 align-items-end">
        <!-- LEFT SIDE CONTROLS -->
        <div class="col d-flex gap-2 flex-wrap align-items-end">

        <div class="col-auto">
            <label for="report_period" class="form-label">Report period</label>
            <select id="report_period" name="report_period" class="form-select" onchange="setDateRange()">
                <option value="ytd" {{ request('report_period', 'ytd') == 'ytd' ? 'selected' : '' }}>This year to date</option>
                <option value="this_year_to_last_month" {{ request('report_period') == 'this_year_to_last_month' ? 'selected' : '' }}>This year to last month</option>
                <option value="last_fiscal_year" {{ request('report_period') == 'last_fiscal_year' ? 'selected' : '' }}>Last fiscal year</option>
                <option value="this_month" {{ request('report_period') == 'this_month' ? 'selected' : '' }}>This month</option>
                <option value="last_month" {{ request('report_period') == 'last_month' ? 'selected' : '' }}>Last month</option>
                <option value="this_quarter" {{ request('report_period') == 'this_quarter' ? 'selected' : '' }}>This quarter</option>
                <option value="last_quarter" {{ request('report_period') == 'last_quarter' ? 'selected' : '' }}>Last quarter</option>
                <option value="custom" {{ request('report_period') == 'custom' ? 'selected' : '' }}>Custom</option>
            </select>
        </div>
        <div class="col-auto">
            <label for="start_date" class="form-label">From</label>
            <input type="date" id="start_date" name="start_date"
                value="{{ request('start_date', now()->startOfYear()->format('Y-m-d')) }}" class="form-control">
        </div>
        <div class="col-auto">
            <label for="end_date" class="form-label">To</label>
            <input type="date" id="end_date" name="end_date"
                value="{{ request('end_date', now()->format('Y-m-d')) }}" class="form-control">
        </div>
        <div class="col-auto">
            <button type="button" class="btn btn-primary" onclick="showReport()">Show</button>
        </div>
        <div class="col-auto">
            <button type="button" class="btn btn-danger" onclick="exportReport('pdf')">Export to PDF</button>
        </div>
        <div class="col-auto">
            <button type="button" class="btn btn-success" onclick="exportReport('excel')">Export to Excel</button>
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
</div>
@endsection

@section('scripts')
<script>
function setDateRange() {
    const period = document.getElementById('report_period').value;
    const today = new Date();
    const start = document.getElementById('start_date');
    const end = document.getElementById('end_date');

    if (period === 'ytd') {
        start.value = today.getFullYear() + '-01-01';
        end.value = today.toISOString().slice(0, 10);
        start.readOnly = true;
        end.readOnly = true;
    } else if (period === 'this_month') {
        start.value = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-01';
        end.value = today.toISOString().slice(0, 10);
        start.readOnly = true;
        end.readOnly = true;
    } else if (period === 'last_month') {
        let year = today.getFullYear();
        let month = today.getMonth();
        if (month === 0) { year--; month = 12; }
        else { /* JS months 0-based, so 0=Jan, 11=Dec */ }
        start.value = year + '-' + String(month).padStart(2, '0') + '-01';
        end.value = year + '-' + String(month).padStart(2, '0') + '-' + new Date(year, month, 0).getDate();
        start.readOnly = true;
        end.readOnly = true;
    } else if (period === 'this_quarter') {
        let q = Math.floor(today.getMonth() / 3);
        let qStartMonth = q * 3;
        let qEndMonth = q * 3 + 2;
        let startMonth = String(qStartMonth + 1).padStart(2, '0');
        let endMonth = String(qEndMonth + 1).padStart(2, '0');
        let year = today.getFullYear();
        start.value = year + '-' + startMonth + '-01';
        // End date = last day of quarter
        let lastDay = new Date(year, qEndMonth + 1, 0).getDate();
        end.value = year + '-' + endMonth + '-' + lastDay;
        start.readOnly = true;
        end.readOnly = true;
    } else if (period === 'last_quarter') {
        let q = Math.floor(today.getMonth() / 3) - 1;
        let year = today.getFullYear();
        if (q < 0) { q = 3; year--; }
        let qStartMonth = q * 3;
        let qEndMonth = q * 3 + 2;
        let startMonth = String(qStartMonth + 1).padStart(2, '0');
        let endMonth = String(qEndMonth + 1).padStart(2, '0');
        start.value = year + '-' + startMonth + '-01';
        let lastDay = new Date(year, qEndMonth + 1, 0).getDate();
        end.value = year + '-' + endMonth + '-' + lastDay;
        start.readOnly = true;
        end.readOnly = true;
    } else if (period === 'this_year_to_last_month') {
        let year = today.getFullYear();
        start.value = year + '-01-01';
        let lastMonth = today.getMonth();
        let lastMonthYear = year;
        if (lastMonth === 0) { // If Jan, last month is Dec previous year
            lastMonth = 12;
            lastMonthYear = year - 1;
        }
        let endDay = new Date(lastMonthYear, lastMonth, 0).getDate();
        end.value = lastMonthYear + '-' + String(lastMonth).padStart(2, '0') + '-' + endDay;
        start.readOnly = true;
        end.readOnly = true;
    } else if (period === 'last_fiscal_year') {
        let year = today.getFullYear() - 1;
        start.value = year + '-01-01';
        end.value = year + '-12-31';
        start.readOnly = true;
        end.readOnly = true;
    }else { // custom
            start.readOnly = false;
            end.readOnly = false;
    }
}

function exportReport(type) {
    let form = document.getElementById('pl-form');
    if (type === 'pdf') {
        form.action = "{{ route('profitloss.pdf') }}";
    } else if (type === 'excel') {
        form.action = "{{ route('profitloss.excel') }}";
    }
    form.submit();
}

function showReport() {
    let form = document.getElementById('pl-form');
    form.action = "{{ route('profitloss.new') }}";
    form.submit();
}

// Optional: Initialize dates on page load
window.onload = function() {
    setDateRange();
};
</script>
@endsection



