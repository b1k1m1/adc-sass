<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Trial Balance Monthwise</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10pt;
        }

        h4, h6 {
            margin: 0;
            padding: 0;
        }

        .text-center { text-align: center; }
        .text-end { text-align: right; }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            border: 0.5px solid #000;
            padding: 3px;
        }

        .table-sm th,
        .table-sm td {
            padding: 2px;
        }

        .table-bordered { border: 0.5px solid #000; }

        .table-secondary {
            background-color: #e0e0e0;
            font-weight: bold;
        }

        .table-dark {
            background-color: #b0b0b0;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <h4 class="text-center">{{ $company->comp_nm ?? 'Company Name' }}</h4>
    <h6 class="text-center">
        Trial Balance (Monthwise)
        from {{ \Carbon\Carbon::parse($startDate)->format('m/d/Y') }}
        to {{ \Carbon\Carbon::parse($endDate)->format('m/d/Y') }}
    </h6>
    <hr>

    <table class="table table-bordered table-sm">
        <thead>
            <tr>
                <th>GL ID</th>
                <th>Category</th>
                <th>Account Code</th>
                <th>Account Name</th>
                @foreach($months as $month)
                    <th colspan="2" class="text-center">
                        {{ \Carbon\Carbon::parse($month.'-01')->format('M Y') }}
                    </th>
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
                    <td colspan="{{ 4 + (count($months)*2) }}">
                        <strong>{{ $typeNames[$type] ?? $type }}</strong>
                    </td>
                </tr>
                @foreach($accounts as $acc)
                    <tr>
                        <td>{{ $acc['info']->gl_id }}</td>
                        <td>{{ $acc['info']->category_name }}</td>
                        <td>{{ $acc['info']->party_cd }}</td>
                        <td>{{ $acc['info']->party_nm }}</td>
                        @foreach($months as $month)
                            <td class="text-end">
                                {{ $acc['months'][$month]['debit'] > 0 ? number_format($acc['months'][$month]['debit'],2) : '' }}
                            </td>
                            <td class="text-end">
                                {{ $acc['months'][$month]['credit'] > 0 ? number_format($acc['months'][$month]['credit'],2) : '' }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            @endforeach

            @php
                // Calculate monthwise totals (same logic as screen)
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

            <tr class="table-dark">
                <td colspan="4" class="text-end">Grand Total</td>
                @foreach($months as $month)
                    <td class="text-end">{{ number_format($monthTotals[$month]['debit'],2) }}</td>
                    <td class="text-end">{{ number_format($monthTotals[$month]['credit'],2) }}</td>
                @endforeach
            </tr>
        </tbody>
    </table>

</body>
</html>
