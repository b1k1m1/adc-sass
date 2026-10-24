<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: "DejaVu Sans", Arial, Helvetica, sans-serif; font-size:9pt; }
        .header { text-align:center; }
        hr { margin:6px 0; }
        table { width:100%; border-collapse:collapse; font-size:8pt; margin-bottom:12px; }
        th, td { border: 1px solid #444; padding: 3px 5px; }
        th { background-color: #eef3fa; font-weight: bold; }
        .fw-bold { font-weight: bold; }
        .text-end { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h4 style="margin-bottom:2px;">{{ $compname ?? 'Company Name' }}</h4>
        <h6 style="margin:0;">
            Balance Sheet as of {{ \Carbon\Carbon::parse($endDate ?? $asOfDate)->format('m/d/Y') }}
        </h6>
    </div>
    <hr>
    <table width="100%">
        <tr>
            <!-- Assets -->
            <td width="50%" valign="top">
                <b>Assets</b>
                <table>
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Name</th>
                            <th class="text-end">{{ t('common.amount', 'Amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
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
                    </tbody>
                </table>
            </td>
            <!-- Liabilities -->
            <td width="50%" valign="top">
                <b>Liabilities</b>
                <table>
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Name</th>
                            <th class="text-end">{{ t('common.amount', 'Amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($liabilities as $row)
                        <tr>
                            <td>{{ $row->category_name }}</td>
                            <td>{{ $row->party_nm }}</td>
                            <td class="text-end">{{ number_format(-1 * $row->balance,2) }}</td>
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
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
