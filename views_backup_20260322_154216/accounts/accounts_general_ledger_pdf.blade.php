<!DOCTYPE html>
<html>
<head>
<style>
    body {
        font-family: Arial, sans-serif;
        font-size: 10pt;
    }

    table.ledger {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed; /* important for mPDF */
    }

    table.ledger th,
    table.ledger td {
        padding: 2px 5px;
        vertical-align: top;
    }

    /* Column headers */
    table.ledger thead th {
        border-bottom: 1.5px solid #000;
        font-size: 10pt;
    }

    /* Date column: do NOT wrap */
    .col-date {
        white-space: nowrap;
    }

    .right {
        text-align: right;
    }

    .small {
        font-size: 8pt;
    }

    .acchead {
        text-align: center;
        font-size: 8pt;
        font-weight: bold;
        border-bottom: 1.5px solid #000;
        padding: 2px 0;
    }
</style>
</head>
<body>

<table class="ledger">
    {{-- fixed column widths for all rows --}}
    <colgroup>
        <col style="width:12%;">  {{-- Date --}}
        <col style="width:40%;">  {{-- Particulars --}}
        <col style="width:18%;">  {{-- Ref # --}}
        <col style="width:15%;">  {{-- Debit --}}
        <col style="width:15%;">  {{-- Credit --}}
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
    @php
        $current_party = null;
        $account_name = '';
        $total_debit = 0;
        $total_credit = 0;
    @endphp

    @foreach($report as $row)
        {{-- Detect start of a new account group --}}
        @if($current_party !== $row->party_cd)
            {{-- Print footer for previous account --}}
            @if($current_party !== null)
                {{-- single line above totals --}}
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style="border-top:1.5px solid #000;"></td>
                    <td style="border-top:1.5px solid #000;"></td>
                </tr>

                {{-- totals row --}}
                <tr>
                    <td></td>
                    <td></td>
                    <td class="right"></td>
                    <td class="right">{{ number_format($total_debit, 2) }}</td>
                    <td class="right">{{ number_format($total_credit, 2) }}</td>
                </tr>

                @php
                    $balance     = $total_debit - $total_credit;
                    $grand_total = max($total_debit, $total_credit);
                @endphp

                {{-- closing balance row --}}
                @if($balance > 0)
                    {{-- Debit > Credit: closing balance on credit side --}}
                    <tr>
                        <td class="col-date">{{ \Carbon\Carbon::parse($endDate)->format('m/d/Y') }}</td>
                        <td>BY BALANCE C/F</td>
                        <td></td>
                        <td></td>
                        <td class="right">{{ number_format($balance,2) }}</td>
                    </tr>
                @elseif($balance < 0)
                    {{-- Credit > Debit: closing balance on debit side --}}
                    <tr>
                        <td class="col-date">{{ \Carbon\Carbon::parse($endDate)->format('m/d/Y') }}</td>
                        <td>TO BALANCE C/F</td>
                        <td></td>
                        <td class="right">{{ number_format(abs($balance),2) }}</td>
                        <td></td>
                    </tr>
                @endif

                {{-- line above grand total --}}
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style="border-top:1.5px solid #000;"></td>
                    <td style="border-top:1.5px solid #000;"></td>
                </tr>

                {{-- grand total row --}}
                <tr>
                    <td></td>
                    <td></td>
                    <td class="right"></td>
                    <td class="right">{{ number_format($grand_total, 2) }}</td>
                    <td class="right">{{ number_format($grand_total, 2) }}</td>
                </tr>

                {{-- double line after grand total --}}
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style="border-top:3px double #000;"></td>
                    <td style="border-top:3px double #000;"></td>
                </tr>
            @endif

            {{-- Start new account --}}
            @php
                $current_party = $row->party_cd;
                $account_name  = $accountNames[$current_party] ?? $current_party;
                $total_debit   = 0;
                $total_credit  = 0;
            @endphp

            {{-- small spacer --}}
            <tr><td colspan="5" style="height:4px;"></td></tr>

            <tr>
                <td colspan="5" class="acchead">
                    {{ $current_party }}&nbsp;&nbsp;&nbsp;{{ $account_name }}
                </td>
            </tr>
        @endif

        {{-- Accumulate totals --}}
        @php
            $total_debit  += $row->debit_amt;
            $total_credit += $row->credit_amt;
        @endphp

        {{-- Transaction row (always 5 tds) --}}
        <tr>
            <td class="col-date">{{ \Carbon\Carbon::parse($row->trans_dt)->format('m/d/Y') }}</td>
            <td>
                {{ $row->payto_name }}
                @if($row->remarks)
                    <br><span class="small">{{ $row->remarks }}</span>
                @endif
            </td>
            <td>{{ $row->ref_no }}</td>
            <td class="right">
                {{ $row->debit_amt > 0 ? number_format($row->debit_amt,2) : '' }}
            </td>
            <td class="right">
                {{ $row->credit_amt > 0 ? number_format($row->credit_amt,2) : '' }}
            </td>
        </tr>
    @endforeach

    {{-- Footer for last account --}}
    @if($current_party !== null)
        {{-- single line above totals --}}
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td style="border-top:1.5px solid #000;"></td>
            <td style="border-top:1.5px solid #000;"></td>
        </tr>

        {{-- totals --}}
        <tr>
            <td></td>
            <td></td>
            <td class="right"></td>
            <td class="right">{{ number_format($total_debit, 2) }}</td>
            <td class="right">{{ number_format($total_credit, 2) }}</td>
        </tr>

        @php
            $balance     = $total_debit - $total_credit;
            $grand_total = max($total_debit, $total_credit);
        @endphp

        {{-- closing balance --}}
        @if($balance > 0)
            <tr>
                <td class="col-date">{{ \Carbon\Carbon::parse($endDate)->format('m/d/Y') }}</td>
                <td>BY BALANCE C/F</td>
                <td></td>
                <td></td>
                <td class="right">{{ number_format($balance,2) }}</td>
            </tr>
        @elseif($balance < 0)
            <tr>
                <td class="col-date">{{ \Carbon\Carbon::parse($endDate)->format('m/d/Y') }}</td>
                <td>TO BALANCE C/F</td>
                <td></td>
                <td class="right">{{ number_format(abs($balance),2) }}</td>
                <td></td>
            </tr>
        @endif

        {{-- line above grand total --}}
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td style="border-top:1.5px solid #000;"></td>
            <td style="border-top:1.5px solid #000;"></td>
        </tr>

        {{-- grand total --}}
        <tr>
            <td></td>
            <td></td>
            <td class="right"></td>
            <td class="right">{{ number_format($grand_total, 2) }}</td>
            <td class="right">{{ number_format($grand_total, 2) }}</td>
        </tr>

        {{-- double line --}}
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
