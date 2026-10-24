<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: "DejaVu Sans", Arial, Helvetica, sans-serif; font-size:9pt; }
        .header { text-align:center; }
        hr { margin:6px 0; }
        .section-title { font-weight: bold; font-size: 10pt; text-align:center; margin-bottom:4px; }
        table { width:100%; border-collapse:collapse; font-size:8pt; margin-bottom:10px; }
        th, td { border: 1px solid #333; padding: 2px 4px; }
        th { font-weight: bold; background-color: #eef3fa; }
        .subtotal { font-weight: bold; background-color: #e8ffe8; }
        .text-end { text-align: right; }
    </style>
</head>
<body>
<div class="header">
    <h4 style="margin-bottom:2px;">{{ $company->comp_nm ?? 'Company Name' }}</h4>
    <h6 style="margin:0;">
        Profit & Loss Account from {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}
        to {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
    </h6>
</div>
<hr>

<div class="section-title">Income</div>
<table>
    <thead>
        <tr>
            <th>GL ID</th>
            <th>Category</th>
            <th>Code</th>
            <th>Name</th>
            <th class="text-end">{{ t('common.amount', 'Amount') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($incomeRows as $row)
        <tr>
            <td>{{ $row['gl_id'] }}</td>
            <td>{{ $row['category'] }}</td>
            <td>{{ $row['code'] }}</td>
            <td>{{ $row['name'] }}</td>
            <td class="text-end">{{ number_format($row['amount'], 2) }}</td>
        </tr>
        @endforeach
        <tr class="subtotal">
            <td colspan="4" class="text-end">Total Income</td>
            <td class="text-end">{{ number_format($totalIncome, 2) }}</td>
        </tr>
    </tbody>
</table>

<div class="section-title">Expenses</div>
<table>
    <thead>
        <tr>
            <th>GL ID</th>
            <th>Category</th>
            <th>Code</th>
            <th>Name</th>
            <th class="text-end">{{ t('common.amount', 'Amount') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($expenseRows as $row)
        <tr>
            <td>{{ $row['gl_id'] }}</td>
            <td>{{ $row['category'] }}</td>
            <td>{{ $row['code'] }}</td>
            <td>{{ $row['name'] }}</td>
            <td class="text-end">{{ number_format($row['amount'], 2) }}</td>
        </tr>
        @endforeach
        <tr class="subtotal">
            <td colspan="4" class="text-end">Total Expenses</td>
            <td class="text-end">{{ number_format($totalExpense, 2) }}</td>
        </tr>
        <tr class="subtotal">
            <td colspan="4" class="text-end">Net Profit / (Loss)</td>
            <td class="text-end" style="font-weight:bold; color: {{ $netProfit < 0 ? '#b94a48' : '#0c960c' }};">
                {{ number_format($netProfit, 2) }}
            </td>
        </tr>
    </tbody>
</table>
</body>
</html>
