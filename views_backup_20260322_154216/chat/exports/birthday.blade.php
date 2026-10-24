<!doctype html>
@php
function ordinal($n) {
    $n = (int)$n;
    $mod100 = $n % 100;
    if ($mod100 >= 11 && $mod100 <= 13) return $n . 'th';
    return match ($n % 10) {
        1 => $n . 'st',
        2 => $n . 'nd',
        3 => $n . 'rd',
        default => $n . 'th',
    };
}
@endphp

<html>
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    .title { font-weight: 700; font-size: 16px; margin-bottom: 10px; }
    table { width:100%; border-collapse: collapse; }
    th, td { border:1px solid #ddd; padding:6px; }
    th { background:#f3f5f7; }

  </style>
</head>
<body>
  <div class="title">{{ $title }}</div>
  <table>
    <thead>
      <tr>
        <th>ParticipantId</th>
        <th>Name</th>
        <th>DOB</th>
        @if(isset($rows[0]) && isset($rows[0]->NextBirthday))
          <th>Next Birthday</th>
        @endif
        <th>Age</th>
      </tr>
    </thead>
    <tbody>
      @forelse($rows as $r)
        <tr>
          <td>{{ $r->ParticipantId }}</td>
          <td>{{ $r->MemberName }}</td>
          <td>{{ $r->DateOfBirth }}</td>
          @if(isset($r->NextBirthday))
            <td>{{ $r->NextBirthday }}</td>
          @endif
          <td class="center">
            {{ isset($r->Age) && $r->Age !== null ? ordinal($r->Age).' Birthday' : '' }}
          </td>
        </tr>
      @empty
        <tr><td colspan="5">No records</td></tr>
      @endforelse
    </tbody>
  </table>
</body>
</html>
