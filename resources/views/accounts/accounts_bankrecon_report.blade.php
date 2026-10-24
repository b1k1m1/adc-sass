<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bank Reconciliation</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 8pt;
            color: #222;
        }

        h1 {
            text-align: center;
            font-size: 15pt;
            margin: 0 0 3px 0;
        }

        h2 {
            text-align: center;
            font-size: 10pt;
            font-weight: normal;
            margin: 0 0 10px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            padding: 3px 4px;
        }

        .summary-table {
            margin-top: 7px;
            margin-bottom: 10px;
        }

        .summary-table td {
            border: 1px solid #777;
            padding: 4px 5px;
        }

        .label {
            background-color: #eeeeee;
            font-weight: bold;
        }

        .section-heading {
            font-size: 10pt;
            font-weight: bold;
            background-color: #e9ecef;
            border: 1px solid #666;
            padding: 5px;
            margin-top: 9px;
            margin-bottom: 3px;
        }

        .detail-table {
            margin-bottom: 7px;
        }

        .detail-table th {
            border: 1px solid #666;
            background-color: #f2f2f2;
            padding: 4px;
            font-weight: bold;
            text-align: center;
        }

        .detail-table td {
            border: 1px solid #888;
            padding: 3px 4px;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .nowrap {
            white-space: nowrap;
        }

        .total td {
            font-weight: bold;
            background-color: #f7f7f7;
        }

        .good {
            color: #198754;
            font-weight: bold;
        }

        .bad {
            color: #dc3545;
            font-weight: bold;
        }

        .empty {
            text-align: center;
            font-style: italic;
            padding: 7px;
        }
    </style>
</head>

<body>

@php
    $openingBalance   = (float) ($recon->opening_balance ?? 0);
    $statementBalance = (float) ($recon->closing_balance ?? 0);
    $difference       = (float) ($totals['difference'] ?? 0);

    $periodLabel = \Carbon\Carbon::create(
        (int) $recon->period_year,
        (int) $recon->period_month,
        1
    )->format('F Y');

    $sections = [
        [
            'title' => 'Cleared Deposits',
            'rows' => $clearedDeposits,
            'field' => 'credit_amt',
            'total' => (float) $totals['cleared_deposits'],
        ],
        [
            'title' => 'Uncleared Deposits',
            'rows' => $unclearedDeposits,
            'field' => 'credit_amt',
            'total' => (float) $totals['uncleared_deposits'],
        ],
        [
            'title' => 'Cleared Payments',
            'rows' => $clearedPayments,
            'field' => 'debit_amt',
            'total' => (float) $totals['cleared_payments'],
        ],
        [
            'title' => 'Uncleared Payments',
            'rows' => $unclearedPayments,
            'field' => 'debit_amt',
            'total' => (float) $totals['uncleared_payments'],
        ],
    ];
@endphp

<h1>{{ config('company.comp_nm', config('app.name')) }}</h1>
<h2>Bank Reconciliation - {{ $periodLabel }}</h2>

<table class="header-table">
    <tr>
        <td width="55%">
            <strong>Bank:</strong>
            {{ $bank->account_name ?? '' }}
            @if(!empty($bank->account_id))
                ({{ $bank->account_id }})
            @endif
        </td>

        <td width="25%">
            <strong>Statement Ending:</strong>
            {{ !empty($recon->closing_dt)
                ? \Carbon\Carbon::parse($recon->closing_dt)->format('m/d/Y')
                : '' }}
        </td>

        <td width="20%">
            <strong>Status:</strong>
            {{ strtoupper($recon->status ?? 'OPEN') }}
        </td>
    </tr>
</table>

<table class="summary-table">
    <tr>
        <td class="label" width="16%">Opening Balance</td>
        <td class="right" width="17%">{{ number_format($openingBalance, 2) }}</td>

        <td class="label" width="16%">Cleared Deposits</td>
        <td class="right" width="17%">{{ number_format($totals['cleared_deposits'], 2) }}</td>

        <td class="label" width="16%">Cleared Payments</td>
        <td class="right" width="18%">{{ number_format($totals['cleared_payments'], 2) }}</td>
    </tr>

    <tr>
        <td class="label">Cleared Balance</td>
        <td class="right">{{ number_format($totals['cleared_balance'], 2) }}</td>

        <td class="label">Statement Balance</td>
        <td class="right">{{ number_format($statementBalance, 2) }}</td>

        <td class="label">Difference</td>
        <td class="right {{ abs($difference) < 0.005 ? 'good' : 'bad' }}">
            {{ number_format($difference, 2) }}
        </td>
    </tr>

    <tr>
        <td class="label">Uncleared Deposits</td>
        <td class="right">{{ number_format($totals['uncleared_deposits'], 2) }}</td>

        <td class="label">Uncleared Payments</td>
        <td class="right">{{ number_format($totals['uncleared_payments'], 2) }}</td>

        <td class="label">Net Outstanding</td>
        <td class="right">
            {{ number_format(
                $totals['uncleared_deposits'] - $totals['uncleared_payments'],
                2
            ) }}
        </td>
    </tr>
</table>

@foreach($sections as $section)

    <div class="section-heading">
        {{ $section['title'] }} - Total: {{ number_format($section['total'], 2) }}
    </div>

    <table class="detail-table">
        <thead>
            <tr>
                <th width="10%">Date</th>
                <th width="31%">Description</th>
                <th width="18%">Reference</th>
                <th width="14%">Check No.</th>
                <th width="12%">Trans. No.</th>
                <th width="15%">Amount</th>
            </tr>
        </thead>

        <tbody>
            @forelse($section['rows'] as $row)
                @php
                    $amountField = $section['field'];
                    $amount = (float) ($row->$amountField ?? 0);
                @endphp

                <tr>
                    <td class="nowrap">
                        {{ !empty($row->trans_dt)
                            ? \Carbon\Carbon::parse($row->trans_dt)->format('m/d/Y')
                            : '' }}
                    </td>

                    <td>{{ $row->payto_name ?: ($row->remarks ?? '') }}</td>
                    <td>{{ $row->ref_no ?? '' }}</td>
                    <td>{{ $row->check_no ?? '' }}</td>
                    <td class="center">{{ $row->trans_no ?? '' }}</td>
                    <td class="right">{{ number_format($amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="empty">No transactions in this section.</td>
                </tr>
            @endforelse

            <tr class="total">
                <td colspan="5" class="right">{{ $section['title'] }} Total:</td>
                <td class="right">{{ number_format($section['total'], 2) }}</td>
            </tr>
        </tbody>
    </table>

@endforeach

</body>
</html>
