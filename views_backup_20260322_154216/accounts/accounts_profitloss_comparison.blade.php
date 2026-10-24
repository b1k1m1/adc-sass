@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
    <div class="container-fluid">
        <h4 class="mb-3">{{ $company->comp_nm ?? 'Company Name' }} - Profit & Loss Comparison</h4>
        <form id="pl-compare-form" method="get" action="{{ route('profitloss.monthwise') }}" class="mb-3">
            <div class="row g-2 align-items-end">
                <!-- LEFT SIDE CONTROLS -->
                <div class="col d-flex gap-2 flex-wrap align-items-end">

                <div class="col-auto">
                    <label for="compare_period" class="form-label">Period</label>
                    <select id="compare_period" name="compare_period" class="form-select" onchange="setComparisonDates()">
                        <option value="ytd" {{ request('compare_period', 'ytd') == 'ytd' ? 'selected' : '' }}>This year to date</option>
                        <option value="fiscal_to_last_month" {{ request('compare_period') == 'fiscal_to_last_month' ? 'selected' : '' }}>This fiscal year to last month</option>
                        <option value="last_quarter" {{ request('compare_period') == 'last_quarter' ? 'selected' : '' }}>Last quarter</option>
                        <option value="last_quarter_to_today" {{ request('compare_period') == 'last_quarter_to_today' ? 'selected' : '' }}>Last quarter to today</option>
                        <option value="custom" {{ request('compare_period') == 'custom' ? 'selected' : '' }}>Custom</option>
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
                    <button type="submit" class="btn btn-primary" onclick="showReport()">Show</button>
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
            </div>
        </form>

        <div style="overflow-x:auto;">
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>GL ID</th>
                    <th>Category</th>
                    <th>Code</th>
                    <th>Name</th>
                    @foreach($months as $m)
                        <th>{{ \Carbon\Carbon::parse($m.'-01')->format('M Y') }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                {{-- INCOME --}}
                <tr class="table-info fw-bold"><td colspan="{{ 4 + count($months) }}">Income</td></tr>
                @foreach($incomeRows as $row)
                    <tr>
                        <td>{{ $row['gl_id'] }}</td>
                        <td>{{ $row['category'] }}</td>
                        <td>{{ $row['code'] }}</td>
                        <td>{{ $row['name'] }}</td>
                        @foreach($months as $m)
                            <td class="text-end">{{ number_format($row['amounts'][$m] ?? 0, 2) }}</td>
                        @endforeach
                    </tr>
                @endforeach
                <tr class="table-secondary fw-bold">
                    <td colspan="4" class="text-end">Total Income</td>
                    @foreach($months as $m)
                        <td class="text-end">{{ number_format($totalIncomeByMonth[$m] ?? 0, 2) }}</td>
                    @endforeach
                </tr>
                {{-- EXPENSE --}}
                <tr class="table-info fw-bold"><td colspan="{{ 4 + count($months) }}">Expenses</td></tr>
                @foreach($expenseRows as $row)
                    <tr>
                        <td>{{ $row['gl_id'] }}</td>
                        <td>{{ $row['category'] }}</td>
                        <td>{{ $row['code'] }}</td>
                        <td>{{ $row['name'] }}</td>
                        @foreach($months as $m)
                            <td class="text-end">{{ number_format($row['amounts'][$m] ?? 0, 2) }}</td>
                        @endforeach
                    </tr>
                @endforeach
                <tr class="table-secondary fw-bold">
                    <td colspan="4" class="text-end">Total Expenses</td>
                    @foreach($months as $m)
                        <td class="text-end">{{ number_format($totalExpenseByMonth[$m] ?? 0, 2) }}</td>
                    @endforeach
                </tr>
                {{-- NET PROFIT/LOSS --}}
                <tr class="table-info fw-bold">
                    <td colspan="4" class="text-end">Net Profit / (Loss)</td>
                    @foreach($months as $m)
                        <td class="text-end">{{ number_format($netProfitByMonth[$m] ?? 0, 2) }}</td>
                    @endforeach
                </tr>
            </tbody>
        </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function setComparisonDates() {
    const period = document.getElementById('compare_period').value;
    const today = new Date();
    const start = document.getElementById('start_date');
    const end = document.getElementById('end_date');
    const fiscalYearStartMonth = 1; // Set your fiscal year start month (1=Jan, 4=April, etc.)

    if (period === 'ytd') {
        start.value = today.getFullYear() + '-01-01';
        end.value = today.toISOString().slice(0, 10);
        start.readOnly = true; end.readOnly = true;
    } else if (period === 'fiscal_to_last_month') {
        let fyStartYear = today.getMonth() + 1 < fiscalYearStartMonth ? today.getFullYear() - 1 : today.getFullYear();
        start.value = fyStartYear + '-' + String(fiscalYearStartMonth).padStart(2, '0') + '-01';
        let lastMonth = today.getMonth();
        let lastMonthYear = today.getFullYear();
        if (lastMonth === 0) { lastMonth = 12; lastMonthYear -= 1; }
        let lastDay = new Date(lastMonthYear, lastMonth, 0).getDate();
        end.value = lastMonthYear + '-' + String(lastMonth).padStart(2, '0') + '-' + lastDay;
        start.readOnly = true; end.readOnly = true;
    } else if (period === 'last_quarter') {
        let q = Math.floor(today.getMonth() / 3);
        let qStartMonth = (q - 1) * 3;
        let year = today.getFullYear();
        if (q === 0) { qStartMonth = 9; year--; }
        let startMonth = String(qStartMonth + 1).padStart(2, '0');
        let endMonth = String(qStartMonth + 3).padStart(2, '0');
        start.value = year + '-' + startMonth + '-01';
        let lastDay = new Date(year, qStartMonth + 3, 0).getDate();
        end.value = year + '-' + endMonth + '-' + lastDay;
        start.readOnly = true; end.readOnly = true;
    } else if (period === 'last_quarter_to_today') {
        let q = Math.floor(today.getMonth() / 3);
        let qStartMonth = (q - 1) * 3;
        let year = today.getFullYear();
        if (q === 0) { qStartMonth = 9; year--; }
        let startMonth = String(qStartMonth + 1).padStart(2, '0');
        start.value = year + '-' + startMonth + '-01';
        end.value = today.toISOString().slice(0, 10);
        start.readOnly = true; end.readOnly = true;
    } else { // custom
        start.readOnly = false;
        end.readOnly = false;
    }
}

function exportReport(type) {
    let form = document.getElementById('pl-compare-form');
    if (type === 'pdf') {
        form.action = "{{ route('profitloss.comparison.pdf') }}";
    } else if (type === 'excel') {
        form.action = "{{ route('profitloss.comparison.excel') }}";
    }
    form.submit();
}

function showReport() {
    let form = document.getElementById('pl-compare-form');
    form.action = "{{ route('profitloss.monthwise') }}";
    form.submit();
}

window.onload = setComparisonDates;
</script>
@endsection
