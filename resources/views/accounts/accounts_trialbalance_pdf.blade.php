<!DOCTYPE html>
<html>
<head>
<style>
   body, .page-content, .container-fluid {
    margin-top: 0 !important;
    padding-top: 0 !important;
}
hr {
    margin-top: 0 !important;
    margin-bottom: 2px !important;
}
    table { width:100%; border-collapse:collapse; font-family: "MS Sans Serif", Arial, sans-serif; font-size:8pt; }
    th, td { padding: 2px 4px; }
    th { font-size: 8pt; font-weight: normal; }
    .text-end { text-align: right; }
    .text-center { text-align: center; }
    .section-head { font-weight: bold; font-size: 9pt; padding-top:8px; }
    .gl-head { font-weight:bold; font-size:8.5pt; padding-top:3px; }
    .subtotal { font-weight: bold; }
</style>
</head>
<body>

<table cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <td colspan="8" style="padding:0;">
                <hr style=" margin-top:0; margin-bottom:2px; border:1px solid #111;">
            </td>
        </tr>
        <tr>
            <th rowspan="2" align="left"><b>Account</b></th>
            <th rowspan="2" align="left"> </th>
            <th colspan="2" class="text-center"><b>&nbsp;&nbsp;Begining Balance</b></th>
            <th colspan="2" class="text-center"><b>&nbsp;&nbsp;&nbsp;&nbsp;Transaction</b></th>
            <th colspan="2" class="text-center"><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Ending Balance</b></th>
        </tr>
        <tr>
            <th class="text-end"><b>Debit</b></th>
            <th class="text-end"><b>Credit</b></th>
            <th class="text-end"><b>Debit</b></th>
            <th class="text-end"><b>Credit</b></th>
            <th class="text-end"><b>Debit</b></th>
            <th class="text-end"><b>Credit</b></th>
        </tr>
        <!-- hr after header row, single line, tight margin -->
        <tr>
            <td colspan="8" style="padding:0;">
                <hr style="margin:0 0 3px 0; border:1px solid #111;">
            </td>
        </tr>

    </thead>
    <tbody>
    @php
        $typeNames = [
            'BA' => 'ASSET',
            'BL' => 'LIABILITY',
            'II' => 'INCOME',
            'IE' => 'EXPENSE',
        ];
    @endphp

    @foreach($groupedByType as $type => $typeRows)
        {{-- Section header: IE, BA, BL, II --}}
        <tr>
            <td class="section-head" colspan="8">{{ $type }}</td>
        </tr>
        @php
            $rowsByGL = $typeRows->groupBy('gl_id');
            $typeSubtotal = [
                'bb_debit_amt' => 0, 'bb_credit_amt' => 0,
                'tr_debit_amt' => 0, 'tr_credit_amt' => 0,
                'eb_debit_amt' => 0, 'eb_credit_amt' => 0
            ];
        @endphp

        @foreach($rowsByGL as $gl_id => $glRows)
            {{-- GL ID & Category Name (bold row) --}}
            <tr>
                <td class="gl-head">{{ $gl_id }}</td>
                <td class="gl-head text-center">{{ $glRows[0]->category_name }}</td>
                <td colspan="6"></td>
            </tr>
            @php
                $glSubtotal = [
                    'bb_debit_amt' => 0, 'bb_credit_amt' => 0,
                    'tr_debit_amt' => 0, 'tr_credit_amt' => 0,
                    'eb_debit_amt' => 0, 'eb_credit_amt' => 0
                ];
            @endphp

            @foreach($glRows as $row)
            <tr>
                <td>{{ $row->party_cd }}</td>
                <td>{{ $row->party_nm }}</td>
                <td class="text-end">{{ $row->bb_debit_amt > 0 ? number_format($row->bb_debit_amt,2) : '' }}</td>
                <td class="text-end">{{ $row->bb_credit_amt > 0 ? number_format($row->bb_credit_amt,2) : '' }}</td>
                <td class="text-end">{{ $row->tr_debit_amt > 0 ? number_format($row->tr_debit_amt,2) : '' }}</td>
                <td class="text-end">{{ $row->tr_credit_amt > 0 ? number_format($row->tr_credit_amt,2) : '' }}</td>
                <td class="text-end">{{ $row->eb_debit_amt > 0 ? number_format($row->eb_debit_amt,2) : '' }}</td>
                <td class="text-end">{{ $row->eb_credit_amt > 0 ? number_format($row->eb_credit_amt,2) : '' }}</td>
            </tr>
                @foreach($glSubtotal as $key => $v)
                    @php $glSubtotal[$key] += $row->$key; @endphp
                @endforeach
                @foreach($typeSubtotal as $key => $v)
                    @php $typeSubtotal[$key] += $row->$key; @endphp
                @endforeach
            @endforeach

            {{-- GL Subtotal --}}
            <tr class="subtotal">
                <td>Total for {{ $gl_id }}</td>
                <td colspan="2" class="text-end">{{ number_format($glSubtotal['bb_debit_amt'],2) }}</td>
                <td class="text-end">{{ number_format($glSubtotal['bb_credit_amt'],2) }}</td>
                <td class="text-end">{{ number_format($glSubtotal['tr_debit_amt'],2) }}</td>
                <td class="text-end">{{ number_format($glSubtotal['tr_credit_amt'],2) }}</td>
                <td class="text-end">{{ number_format($glSubtotal['eb_debit_amt'],2) }}</td>
                <td class="text-end">{{ number_format($glSubtotal['eb_credit_amt'],2) }}</td>
            </tr>
        @endforeach

        {{-- Type Subtotal --}}
        <tr class="subtotal">
            <td>Total {{ $typeNames[$type] ?? $type }}</td>
            <td colspan="2" class="text-end">{{ number_format($typeSubtotal['bb_debit_amt'],2) }}</td>
            <td class="text-end">{{ number_format($typeSubtotal['bb_credit_amt'],2) }}</td>
            <td class="text-end">{{ number_format($typeSubtotal['tr_debit_amt'],2) }}</td>
            <td class="text-end">{{ number_format($typeSubtotal['tr_credit_amt'],2) }}</td>
            <td class="text-end">{{ number_format($typeSubtotal['eb_debit_amt'],2) }}</td>
            <td class="text-end">{{ number_format($typeSubtotal['eb_credit_amt'],2) }}</td>
        </tr>
    @endforeach

    @php $all = $rows; @endphp
    <tr class="subtotal">
        <td colspan="2" class="text-end">Grand Total</td>
        <td class="text-end">{{ number_format($all->sum('bb_debit_amt'),2) }}</td>
        <td class="text-end">{{ number_format($all->sum('bb_credit_amt'),2) }}</td>
        <td class="text-end">{{ number_format($all->sum('tr_debit_amt'),2) }}</td>
        <td class="text-end">{{ number_format($all->sum('tr_credit_amt'),2) }}</td>
        <td class="text-end">{{ number_format($all->sum('eb_debit_amt'),2) }}</td>
        <td class="text-end">{{ number_format($all->sum('eb_credit_amt'),2) }}</td>
    </tr>
    </tbody>
</table>
</body>
</html>
