@extends('admin.admin_dashboard')
@section('title', 'Payment Plan Report')
@section('admin')

<style>
  .rpt-wrap { background:#fff; padding:10px; }

  /* Manual-entry header section */
  .top-row {
    display:flex;
    justify-content:flex-end;
    gap:8px;
    align-items:center;
    margin-bottom:6px;
    font-size:14px;
  }
  .month-box {
    border:1px solid #000;
    padding:2px 6px;
    min-width:140px;
    display:inline-block;
  }

  .manual-table {
    width:100%;
    border-collapse:collapse;
    margin-bottom:10px;
  }
  .manual-table th, .manual-table td{
    border:1px solid #000;
    padding:8px;
    height:36px;
    font-size:14px;
  }
  .manual-table th{
    text-align:center;
    font-weight:700;
  }

  /* Member list section (thin lines like report) */
  .list-table{
    width:100%;
    border-collapse:collapse;
    font-size:13px;
  }
  .list-table thead th{
    border-top:2px solid #000;
    border-bottom:2px solid #000;
    padding:6px 6px;
    font-weight:700;
    text-align:left;
  }
  .list-table tbody td{
    border-bottom:1px solid #000;
    padding:6px 6px;
    vertical-align:top;
  }

  .col-sr { width:55px; }
  .col-id { width:90px; }
  .col-city { width:160px; }
  .col-pay { width:120px; text-align:right; white-space:nowrap; }
  .col-cmt { width:auto; }

  .total-row{
    margin-top:8px;
    display:flex;
    justify-content:flex-end;
    gap:10px;
    font-weight:700;
    font-size:14px;
    border-top:2px solid #000;
    padding-top:6px;
  }

  .list-table tfoot td {
    padding: 6px;
   }
  /* Print friendly */
  @media print {
    .no-print { display:none !important; }
    .page-content, .container-fluid { padding:0 !important; }
  }
</style>

<div class="page-content">
  <div class="container-fluid rpt-wrap">

    <div class="d-flex justify-content-between align-items-center mb-2 no-print">

        {{-- LEFT SIDE : Filters --}}
        <form method="GET"
            action="{{ route('reports.payment_plan') }}"
            class="d-flex align-items-center gap-3">

            <label class="fw-bold mb-0">Month:</label>
            <input type="month" name="month" value="{{ $month }}"
                class="form-control form-control"
                style="max-width:180px;">

            <label class="fw-bold mb-0">Sort By:</label>

            <div class="form-check form-check-inline mb-0">
                <input class="form-check-input" type="radio" name="sort_by" value="id"
                    {{ $sortBy === 'id' ? 'checked' : '' }}>
                <label class="form-check-label">ID</label>
            </div>

            <div class="form-check form-check-inline mb-0">
                <input class="form-check-input" type="radio" name="sort_by" value="name"
                    {{ $sortBy === 'name' ? 'checked' : '' }}>
                <label class="form-check-label">Name</label>
            </div>

            <button class="btn btn-primary">Run</button>
        </form>

    {{-- RIGHT SIDE : Actions --}}
        <div class="d-flex align-items-center gap-2">

            <button type="button"
                    class="btn btn-outline-secondary"
                    onclick="window.print()">
                Print
            </button>

            <a class="btn btn-outline-info"
            target="_blank"
            href="{{ route('reports.payment_plan.pdf', [
                    'month'   => $month,
                    'sort_by'=> $sortBy,
                    'action' => 'preview'
            ]) }}">
                Preview PDF
            </a>

            <a class="btn btn-outline-success"
            href="{{ route('reports.payment_plan.pdf', [
                    'month'   => $month,
                    'sort_by'=> $sortBy,
                    'action' => 'download'
            ]) }}">
                Download PDF
            </a>

            <a href="{{ url()->previous() }}"
            class="btn btn-outline-primary">
                {{ t('common.back','Back') }}
            </a>

        </div>
    </div>


    {{-- TOP RIGHT Month label (like screenshot) --}}
    <div class="top-row">
      <div><strong>Month:</strong></div>
      <div class="month-box">{{ $month }}</div>
    </div>

    {{-- Manual entry grid (fixed) --}}
    <table class="manual-table">
      <thead>
        <tr>
          <th style="width:25%;">Date</th>
          <th style="width:55%;">Description</th>
          <th style="width:20%;">Amount</th>
        </tr>
      </thead>
      <tbody>
        {{-- fixed blank lines for manual writing --}}
        @for($i=0; $i<5; $i++)
          <tr>
            <td></td>
            <td></td>
            <td></td>
          </tr>
        @endfor
      </tbody>
    </table>

    {{-- Member list --}}
    <table class="list-table">
      <thead>
        <tr>
          <th class="col-sr">Sr#</th>
          <th class="col-id">#</th>
          <th>Name of Member's</th>
          <th class="col-city">City</th>
          <th class="col-pay">Pay Plan</th>
          <th class="col-cmt">Comments</th>
        </tr>
      </thead>
      <tbody>
        @php $sr = 1; @endphp
        @forelse($members as $m)
          <tr>
            <td class="col-sr">{{ $sr++ }}</td>
            <td class="col-id">{{ $m->ParticipantId }}</td>
            <td>{{ trim($m->LastName . ' ' . $m->FirstName) }}</td>
            <td class="col-city">{{ $m->City }}</td>
            <td class="col-pay">{{ number_format((float)$m->payment_plan, 2) }}</td>
            <td class="col-cmt">{{ $m->pp_comments }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center">No members found where payment plan is greater than 0.</td>
          </tr>
        @endforelse
      </tbody>
      <tfoot>
        <tr>
            {{-- Total label ends at City column (right aligned) --}}
            <td colspan="4" style="border-top:2px solid #000; font-weight:700; text-align:right;">
            Total:
            </td>

            {{-- Total amount EXACTLY under Pay Plan --}}
            <td class="col-pay" style="border-top:2px solid #000; font-weight:700;">
            {{ number_format((float)$total, 2) }}
            </td>

            {{-- Blank under Comments so it does NOT push total to far right --}}
            <td style="border-top:2px solid #000;"></td>
        </tr>
      </tfoot>

    </table>


  </div>
</div>

@endsection
