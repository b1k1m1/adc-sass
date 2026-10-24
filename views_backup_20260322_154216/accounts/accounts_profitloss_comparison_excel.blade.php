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
