<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ $title ?? 'Driver Schedule Pickup' }}</title>
  <style>
  *{box-sizing:border-box}
  body{font-family:DejaVu Sans, Helvetica, Arial, sans-serif; font-size:11px}
  table.report{
      width:100%;
      border-collapse:collapse;
      table-layout:fixed;
      font-size:10.5px;
  }
  thead{display:table-header-group}
  tfoot{display:table-footer-group}
  .report th,.report td{
      border:1px solid #000;
      padding:6px 6px;        /* 👈 more vertical padding */
      text-align:center;
      vertical-align:middle;
      word-wrap:break-word;
      height: 28px;           /* 👈 minimum row height for signature space */
    }
  .report th{font-weight:700}
  .text-left { text-align: left !important; }

  .report th, .report td { text-align: center; }

/* 🚩 force LEFT for Last, First, Street, City (cols 3–6) */
.report tbody td:nth-child(3),
.report tbody td:nth-child(4),
.report tbody td:nth-child(5),
.report tbody td:nth-child(6) {
  text-align: left !important;
}
</style>

</head>
<body>

  {{-- Note: Header + Footer are injected by mPDF (controller); keep body minimal --}}
  <table class="report">
    <thead>
      <tr>
        <th class="col-id">ID</th>
        <th class="col-num">#</th>
        <th class="col-last">Last</th>
        <th class="col-first">First</th>
        <th class="col-street">Street</th>
        <th class="col-city">City</th>
        <th class="col-tele">Tele #</th>
        <th class="col-time">Pick up time<br>at home</th>
        <th class="col-time">Arrival time<br>at center</th>
        <th class="col-time">Departure time<br>from center</th>
        <th class="col-time">Drop off time<br>at home</th>
        <th class="col-init">Initials</th>
      </tr>
    </thead>

    <tbody>
      @forelse($rows as $i => $r)
        @php
          // US phone format (XXX) XXX XXXX
          $digits = preg_replace('/\D/', '', $r->tele ?? '');
          $phone  = strlen($digits)===10
              ? sprintf('(%s) %s %s', substr($digits,0,3), substr($digits,3,3), substr($digits,6))
              : ($r->tele ?? '');
        @endphp
        <tr>
          <td>{{ $r->id }}</td>
          <td>{{ $i+1 }}</td>
          <td class="text-left">{{ $r->last }}</td>
          <td class="text-left">{{ $r->first }}</td>
          <td class="text-left">{{ $r->street }}</td>
          <td class="text-left">{{ $r->city }}</td>
          <td>{{ $phone }}</td>
          <td></td>  {{-- Pickup --}}
          <td></td>  {{-- Arrival --}}
          <td></td>  {{-- Departure --}}
          <td></td>  {{-- Drop off --}}
          <td></td>  {{-- Initials --}}
        </tr>
      @empty
        <tr><td colspan="12" style="text-align:center;">No records found</td></tr>
      @endforelse
    </tbody>

    <tfoot>
      <tr>
        <td colspan="12" style="text-align:right; font-size:10px; padding:6px;">

        </td>
      </tr>
    </tfoot>
  </table>

</body>
</html>
