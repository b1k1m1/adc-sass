<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
    .title { font-weight: 700; font-size: 14px; margin-bottom: 10px; }
    table { width:100%; border-collapse: collapse; }
    th, td { border:1px solid #ddd; padding:5px; }
    th { background:#f3f5f7; }
    .nowrap { white-space: nowrap; }
    th, td {
    text-align: center;
    vertical-align: middle;
}
  </style>
</head>
<body>
  <div class="title">{{ $title }}</div>

  <table>
    <thead>
      <tr>
        <th class="nowrap">Date</th>
        <th>LowBP</th><th>HighBP</th><th>Sugar</th>
        <th>Pulse</th><th>Temp</th>
        <th>Weight</th>
      </tr>
    </thead>
    <tbody>
      @forelse($rows as $r)
        <tr>
          <td class="nowrap">{{ $r->CalenderDate }}</td>
          <td>{{ $r->LowBP }}</td>
          <td>{{ $r->HighBP }}</td>
          <td>{{ $r->Sugar }}</td>
          <td>{{ $r->Pulse }}</td>
          <td>{{ $r->Temperature }}</td>
          <td>{{ $r->Weight }}</td>

        </tr>
      @empty
        <tr><td colspan="7">No records found</td></tr>
      @endforelse
    </tbody>
  </table>
</body>
</html>
