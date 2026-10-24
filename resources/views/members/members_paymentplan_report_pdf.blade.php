<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: sans-serif; font-size: 12px; }
    .top-row { text-align:right; margin-bottom:6px; }
    .month-box { border:1px solid #000; padding:2px 6px; display:inline-block; min-width:140px; }

    .manual-table { width:100%; border-collapse:collapse; margin-bottom:10px; }
    .manual-table th, .manual-table td { border:1px solid #000; padding:8px; height:28px; }
    .manual-table th { text-align:center; font-weight:bold; }

    .list-table { width:100%; border-collapse:collapse; }
    .list-table thead th { border-top:2px solid #000; border-bottom:2px solid #000; padding:5px; text-align:left; }
    .list-table tbody td { border-bottom:1px solid #000; padding:5px; vertical-align:top; }

    .col-pay { text-align:right; white-space:nowrap; }

    .total-row { margin-top:8px; border-top:2px solid #000; padding-top:6px; text-align:right; font-weight:bold; }
  </style>
</head>
<body>

  <div class="top-row">
    <strong>Month:</strong> <span class="month-box">{{ $month }}</span>
  </div>

  <table class="manual-table">
    <thead>
      <tr>
        <th style="width:25%;">Date</th>
        <th style="width:55%;">Description</th>
        <th style="width:20%;">Amount</th>
      </tr>
    </thead>
    <tbody>
      @for($i=0; $i<5; $i++)
        <tr><td></td><td></td><td></td></tr>
      @endfor
    </tbody>
  </table>

  <table class="list-table">
    <thead>
      <tr>
        <th style="width:45px;">Sr#</th>
        <th style="width:80px;">#</th>
        <th>Name of Member's</th>
        <th style="width:140px;">City</th>
        <th style="width:95px;">Pay Plan</th>
        <th>Comments</th>
      </tr>
    </thead>
    <tbody>
      @php $sr=1; @endphp
      @foreach($members as $m)
        <tr>
          <td>{{ $sr++ }}</td>
          <td>{{ $m->ParticipantId }}</td>
          <td>{{ trim($m->LastName.' '.$m->FirstName) }}</td>
          <td>{{ $m->City }}</td>
          <td class="col-pay">{{ number_format((float)$m->payment_plan, 2) }}</td>
          <td>{{ $m->pp_comments }}</td>
        </tr>
      @endforeach
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

</body>
</html>
