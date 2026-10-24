@inject('carbon', 'Carbon\Carbon')
@php
    $dateObj = \Carbon\Carbon::createFromFormat('Y-m', $monthYear);
    $daysInMonth = $dateObj->daysInMonth;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
@if(empty($forPdf))   {{-- only for browser preview --}}
  <title>ADL Report</title>
  <style>
    /* For browser print; mPDF gets CSS from controller */
    @media print { .no-print { display: none !important; } }
    table { width:100%; border-collapse:collapse; }
    th, td { border:1px solid #000; padding:4px; font-size:11px; text-align:center; }
    thead { display: table-header-group; }
    tfoot { display: table-footer-group; }
    .text-left { text-align:left; }
    .weekend-row { background: rgba(33, 199, 33, 0.15); }
  </style>
@endif
</head>
<body>

{{-- OPTIONAL: simple header for browser print (mPDF uses SetHTMLHeader) --}}
<div class="no-print" style="margin-bottom:8px;">
  <strong>{{ $compname ?? config('app.name') }}</strong> —
  {{ $member->LastName }} {{ $member->FirstName }} |
  Month: {{ $monthYear }}
</div>

<table>
  <thead>
    <tr>
      <th style="width:7%">Day</th>
      <th style="width:13%">Date</th>
      <th style="width:5%">Toilet</th>
      <th style="width:7%">Transfer</th>
      <th style="width:9%">Locomotion</th>
      <th style="width:9%">Eating</th>
    </tr>
  </thead>
  <tbody>
    @for($day = 1; $day <= $daysInMonth; $day++)
        @php
            $current = $dateObj->copy()->day($day);
            $row = $existingData[$day] ?? null;
            $isWeekend = $current->isSaturday() || $current->isSunday();
        @endphp
        <tr class="{{ $isWeekend ? 'weekend-row' : '' }}">
            <td>{{ $day }}</td>
            <td>{{ $current->format('D, M j') }}</td>
            <td>@if(($row->toiletyn ?? 'N') === 'Y') ✓ @endif</td>
            <td>@if(($row->transferyn ?? 'N') === 'Y') ✓ @endif</td>
            <td>@if(($row->locomotionyn ?? 'N') === 'Y') ✓ @endif</td>
            <td>@if(($row->eatingyn ?? 'N') === 'Y') ✓ @endif</td>
        </tr>
    @endfor
  </tbody>
</table>

</body>
</html>
