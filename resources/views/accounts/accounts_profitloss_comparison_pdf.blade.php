<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-size: 8pt; font-family: Arial, sans-serif; }
        .text-end { text-align: right; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #888; padding: 1px 2px; font-size: 8pt; }
        th { background: #eee; }
    </style>
</head>
<body>
    <h3 style="text-align:center;">{{ $company->comp_nm ?? 'Company Name'}}</h3>
    <h5 style="text-align:center;">
        Profit &amp; Loss Comparison<br>
        From {{ \Carbon\Carbon::parse($startDate)->format('m/d/Y') }}
        to {{ \Carbon\Carbon::parse($endDate)->format('m/d/Y') }}
    </h5>
    <table>
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
            <!-- Income -->
            <tr style="background:#d9edf7; font-weight:bold;">
                <td colspan="{{ 4 + count($months) }}">Income</td>
            </tr>
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
            <tr style="font-weight:bold; background:#eee;">
                <td colspan="4" class="text-end">Total Income</td>
                @foreach($months as $m)
                    <td class="text-end">{{ number_format($totalIncomeByMonth[$m] ?? 0, 2) }}</td>
                @endforeach
            </tr>
            <!-- Expenses -->
            <tr style="background:#d9edf7; font-weight:bold;">
                <td colspan="{{ 4 + count($months) }}">Expenses</td>
            </tr>
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
            <tr style="font-weight:bold; background:#eee;">
                <td colspan="4" class="text-end">Total Expenses</td>
                @foreach($months as $m)
                    <td class="text-end">{{ number_format($totalExpenseByMonth[$m] ?? 0, 2) }}</td>
                @endforeach
            </tr>
            <!-- Net Profit/Loss -->
            <tr style="background:#b2ebc6; font-weight:bold;">
                <td colspan="4" class="text-end">Net Profit / (Loss)</td>
                @foreach($months as $m)
                    <td class="text-end">{{ number_format($netProfitByMonth[$m] ?? 0, 2) }}</td>
                @endforeach
            </tr>
        </tbody>
    </table>
    <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_script('
                if ($PAGE_COUNT > 1) {
                    $font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
                    $size = 9;
                    $pdf->text(520, 820, "Page $PAGE_NUM of $PAGE_COUNT", $font, $size);
                }
            ');
        }
    </script>
</body>
</html>
