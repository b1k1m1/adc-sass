<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            color: #000;
        }

        table.ledger {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.ledger th,
        table.ledger td {
            padding: 2px 5px;
            vertical-align: top;
        }

        table.ledger thead th {
            border-bottom: 1.5px solid #000;
            font-size: 10pt;
            font-weight: bold;
        }

        .col-date {
            white-space: nowrap;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .small {
            font-size: 8pt;
        }

        .account-heading {
            text-align: center;
            font-size: 9pt;
            font-weight: bold;
            border-bottom: 1.5px solid #000;
            padding: 3px 0;
        }
    </style>
</head>

<body>

<table class="ledger">

    <colgroup>
        <col style="width:12%;">
        <col style="width:40%;">
        <col style="width:18%;">
        <col style="width:15%;">
        <col style="width:15%;">
    </colgroup>

    <thead>
        <tr>
            <th class="col-date">Date</th>
            <th>Particulars</th>
            <th>Ref #</th>
            <th class="right">Debit Amount</th>
            <th class="right">Credit Amount</th>
        </tr>
    </thead>

    <tbody>

        {{-- Account code and account name --}}
        <tr>
            <td colspan="5" style="height:4px;"></td>
        </tr>

        <tr>
            <td colspan="5" class="account-heading">
                {{ $selectedAccount->party_cd }}
                &nbsp;&nbsp;&nbsp;
                {{ $selectedAccount->party_nm }}
            </td>
        </tr>

        {{-- Transactions --}}
        @forelse($report as $row)

            <tr>
                <td class="col-date">
                    {{ \Carbon\Carbon::parse($row->trans_dt)->format('m/d/Y') }}
                </td>

                <td>
                    {{ $row->payto_name ?? '' }}

                    @if(!empty($row->remarks))
                        <br>
                        <span class="small">
                            {{ $row->remarks }}
                        </span>
                    @endif
                </td>

                <td>
                    {{ $row->ref_no ?? '' }}
                </td>

                <td class="right">
                    @if((float) ($row->debit_amt ?? 0) > 0)
                        {{ number_format((float) $row->debit_amt, 2) }}
                    @endif
                </td>

                <td class="right">
                    @if((float) ($row->credit_amt ?? 0) > 0)
                        {{ number_format((float) $row->credit_amt, 2) }}
                    @endif
                </td>
            </tr>

        @empty

            <tr>
                <td colspan="5"
                    class="center"
                    style="padding-top:10px;">

                    No transactions found for the selected account
                    and date range.
                </td>
            </tr>

        @endforelse

        @if($report->isNotEmpty())

            {{-- Line above totals --}}
            <tr>
                <td></td>
                <td></td>
                <td></td>

                <td style="border-top:1.5px solid #000;"></td>
                <td style="border-top:1.5px solid #000;"></td>
            </tr>

            {{-- Totals --}}
            <tr>
                <td></td>
                <td></td>
                <td></td>

                <td class="right">
                    {{ number_format((float) $totalDebit, 2) }}
                </td>

                <td class="right">
                    {{ number_format((float) $totalCredit, 2) }}
                </td>
            </tr>

            @php
                $closingBalance =
                    (float) $totalDebit -
                    (float) $totalCredit;

                $equalGrandTotal = max(
                    (float) $totalDebit,
                    (float) $totalCredit
                );
            @endphp

            {{-- Closing balance --}}
            @if($closingBalance > 0)

                <tr>
                    <td class="col-date">
                        {{ \Carbon\Carbon::parse($endDate)->format('m/d/Y') }}
                    </td>

                    <td>BY BALANCE C/F</td>
                    <td></td>
                    <td></td>

                    <td class="right">
                        {{ number_format($closingBalance, 2) }}
                    </td>
                </tr>

            @elseif($closingBalance < 0)

                <tr>
                    <td class="col-date">
                        {{ \Carbon\Carbon::parse($endDate)->format('m/d/Y') }}
                    </td>

                    <td>TO BALANCE C/F</td>
                    <td></td>

                    <td class="right">
                        {{ number_format(abs($closingBalance), 2) }}
                    </td>

                    <td></td>
                </tr>

            @endif

            {{-- Line above grand total --}}
            <tr>
                <td></td>
                <td></td>
                <td></td>

                <td style="border-top:1.5px solid #000;"></td>
                <td style="border-top:1.5px solid #000;"></td>
            </tr>

            {{-- Grand totals --}}
            <tr>
                <td></td>
                <td></td>
                <td></td>

                <td class="right">
                    {{ number_format($equalGrandTotal, 2) }}
                </td>

                <td class="right">
                    {{ number_format($equalGrandTotal, 2) }}
                </td>
            </tr>

            {{-- Double line --}}
            <tr>
                <td></td>
                <td></td>
                <td></td>

                <td style="border-top:3px double #000;"></td>
                <td style="border-top:3px double #000;"></td>
            </tr>

        @endif

    </tbody>
</table>

</body>
</html>
