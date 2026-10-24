@php
function fmt_amt($val) {
  return abs((float)$val) < 0.00001 ? '' : number_format((float)$val, 2);
}
@endphp

<style>
    body { font-family: sans-serif; font-size: 10.5pt; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #000; padding: 4px 5px; vertical-align: top; }
    th { background: #f2f2f2; font-weight: bold; }
    .num { text-align: right; white-space: nowrap; }
    .nowrap { white-space: nowrap; }
    .desc { width: 40%; }

    body {
        font-family: sans-serif;
        font-size: 10.5pt;
    }

    table.rpt {
        width: 100%;
        border-collapse: collapse;
    }

    /* NO vertical borders */
    table.rpt th,
    table.rpt td {
        padding: 4px 6px;
        vertical-align: top;
        border-left: none;
        border-right: none;
    }

    /* Header: top + bottom line only */
    table.rpt thead th {
        border-top: 1px solid #000;
        border-bottom: 1px solid #000;
        font-weight: bold;
    }

    /* Normal rows: no lines */
    table.rpt tbody td {
        border-top: none;
        border-bottom: none;
    }

    /* Section separators (Balance B/F, My Home Total, Balance C/F) */
    tr.sep-top td {
        border-top: 1px solid #000;
    }

    tr.sep-bottom td {
        border-bottom: 1px solid #000;
    }

    /* Footer totals */
    table.rpt tfoot th {
        border-top: 1px solid #000;
        font-weight: bold;
    }

    .num {
        text-align: right;
        white-space: nowrap;
    }

    .nowrap {
        white-space: nowrap;
    }

</style>

<table class="rpt">
  <thead>
    <tr>
      <th class="nowrap">Date</th>
      <th class="desc">Description</th>
      <th class="nowrap">Via</th>
      <th class="nowrap">Check #</th>
      <th class="num nowrap">My Home Amt</th>
      <th class="num nowrap">Receipts</th>
      <th class="num nowrap">Payments</th>
    </tr>
  </thead>

  <tbody>
    {{-- Balance B/F --}}
    <tr class="sep-top">
      <td class="nowrap">{{ $adt_start_date }}</td>
      <td><strong>Balance B/F</strong></td>
      <td></td>
      <td></td>
      <td class="num"></td>
      <td class="num"><strong>{{ fmt_amt($op_credit_a) }}</strong></td>
      <td class="num"><strong>{{ fmt_amt($op_debit_a) }}</strong></td>
    </tr>

    {{-- Detail --}}
    @foreach($rows as $r)
      <tr>
        <td class="nowrap">{{ $r->trans_dt_fmt }}</td>
        <td>
          {{ $r->payto_name }}
          @if(!empty($r->per) && (float)$r->per > 0)
            <div style="font-size: 9pt;">{{ number_format((float)$r->per, 2) }}% deduction of {{ $r->credit_amt }}</div>
          @endif
        </td>
        <td class="nowrap">{{ $r->pay_type }}</td>
        <td class="nowrap">{{ $r->check_no }}</td>
        <td class="num">{{ fmt_amt($r->my_home_amt) }}</td>
        <td class="num">{{ fmt_amt($r->net_credit) }}</td>
        <td class="num">{{ fmt_amt($r->net_debit) }}</td>
      </tr>
    @endforeach

    {{-- My Home Total --}}
    <tr class="sep-top sep-bottom">
      <td colspan="4" class="num"><strong>My Home Total</strong></td>
      <td class="num"><strong>{{ fmt_amt($sum_myhome_amt) }}</strong></td>
      <td class="num"></td>
      <td class="num"></td>
    </tr>

    {{-- Balance C/F (balancing figure so totals match) --}}
    <tr class="sep-top sep-bottom">
      <td class="nowrap">{{ $adt_end_date }}</td>
      <td><strong>Balance C/F</strong></td>
      <td></td>
      <td></td>
      <td class="num"></td>
      <td class="num"><strong>{{ fmt_amt($cl_credit_amt) }}</strong></td>
      <td class="num"><strong>{{ fmt_amt($cl_debit_amt) }}</strong></td>
    </tr>
  </tbody>

  <tfoot>
    <tr>
      <th colspan="5" class="num">Totals</th>
      <th class="num">{{ fmt_amt($totcr) }}</th>
      <th class="num">{{ fmt_amt($totdr) }}</th>
    </tr>
  </tfoot>
</table>
