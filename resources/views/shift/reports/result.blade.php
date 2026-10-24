@extends('admin.admin_dashboard')
@section('title', 'SHIFT 2 Receipts & Payments')
@section('admin')

<style>
  .rpt-title { font-weight: 700; font-size: 18px; }
  .rpt-sub   { font-size: 13px; }
  .rpt-table th, .rpt-table td { font-size: 13px; }
  .rpt-table th { background: #f3f5f7; }
  .num { text-align: right; white-space: nowrap; }
  .nowrap { white-space: nowrap; }
</style>

@php
function fmt_amt($val) {
  return abs((float)$val) < 0.00001 ? '' : number_format((float)$val, 2);
}
@endphp

<div class="page-content">
  <div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-2">
      <div>
        <div class="rpt-title">{{ $reportTitle }}</div>
        <div class="rpt-sub">
          From <strong>{{ $adt_start_date }}</strong> to <strong>{{ $adt_end_date }}</strong>
        </div>
      </div>

      <div class="d-flex gap-2">

        <form method="POST" action="{{ route('reports.shift2.pdf') }}" target="_blank" class="d-flex gap-2">
        @csrf
            <input type="hidden" name="adt_start_date" value="{{ $raw_start_date }}">
            <input type="hidden" name="adt_end_date" value="{{ $raw_end_date }}">
            <button class="btn btn-outline-danger" type="submit" title="Opens in new tab"
            name="pdf_action"
            value="preview">Preview PDF</button>

            <button type="submit" name="pdf_action" value="download" class="btn btn-outline-secondary">
            Download PDF
            </button>
        </form>

        <a href="{{ route('reports.shift2.index') }}" class="btn btn-outline-primary">{{ t('common.back','Back') }}</a>
      </div>
    </div>

    <div class="card">
      <div class="card-body table-responsive">

        <table class="table table-bordered rpt-table align-middle" style="min-width:1100px;">
          <thead>
            <tr>
              <th class="nowrap">Date</th>
              <th>Description</th>
              <th>Remarks</th>
              <th class="nowrap">Via</th>
              <th class="nowrap">Check #</th>
              <th class="num nowrap">My Home Amt</th>
              <th class="num nowrap">Receipts</th>
              <th class="num nowrap">Payments</th>
            </tr>
          </thead>

          <tbody>
            {{-- Balance B/F --}}
            <tr>
              <td class="nowrap">{{ $adt_start_date }}</td>
              <td><strong>Balance B/F</strong></td>
              <td></td>
              <td></td>
              <td class="num"></td>
              <td class="num"><strong>{{ fmt_amt($op_credit_a, 2) }}</strong></td>
              <td class="num"><strong>{{ fmt_amt($op_debit_a, 2) }}</strong></td>
            </tr>

            {{-- Detail rows --}}
            @forelse($rows as $r)
              <tr>
                <td class="nowrap">{{ $r->trans_dt_fmt }}</td>
                <td>
                  {{ $r->payto_name }}
                  @if(!empty($r->per) && (float)$r->per > 0)
                    <div class="text-muted" style="font-size:12px;">
                      {{ number_format((float)$r->per, 2) }}% deduction of {{ $r->credit_amt }}
                    </div>
                  @endif
                </td>
                <td class="nowrap">{{ $r->remarks }}</td>
                <td class="nowrap">{{ $r->pay_type }}</td>
                <td class="nowrap">{{ $r->check_no }}</td>

                {{-- Legacy shows credit_amt as "My Home Amt" --}}

                @php
                    $myHomeAmt = (float)($r->credit_amt ?? 0) - (float)($r->net_credit ?? 0);
                @endphp
                <td class="num">{{ fmt_amt($myHomeAmt, 2) }}</td>
                <td class="num">{{ fmt_amt((float)($r->net_credit ?? 0), 2) }}</td>
                <td class="num">{{ fmt_amt((float)($r->net_debit ?? 0), 2) }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center text-muted">No transactions for selected dates.</td>
              </tr>
            @endforelse

            {{-- Optional middle summary similar to legacy: sum(credit_amt) shown near bottom --}}
            <tr>
               <td class="num"></td>
                <td colspan="4" class="text-end"><strong>My Home Total</strong></td>
                <td class="num"><strong>{{ fmt_amt($sum_myhome_amt, 2) }}</strong></td>
                <td class="num"></td>
                <td class="num"></td>
            </tr>

            {{-- Balance C/F --}}
            <tr>
              <td class="nowrap">{{ $adt_end_date }}</td>
              <td><strong>Balance C/F</strong></td>
              <td></td>
              <td></td>
              <td class="num"></td>
              <td class="num"></td>
              <td class="num"><strong>{{ fmt_amt($cl_credit_amt, 2) }}</strong></td>
              <td class="num"><strong>{{ fmt_amt($cl_debit_amt, 2) }}</strong></td>
            </tr>
          </tbody>

          <tfoot>
            <tr>
              <td class="num"></td>
              <th colspan="5" class="text-end">Totals</th>
              <th class="num">{{ fmt_amt($totcr, 2) }}</th>
              <th class="num">{{ fmt_amt($totdr, 2) }}</th>
            </tr>
          </tfoot>
        </table>

      </div>
    </div>

  </div>
</div>
@endsection
