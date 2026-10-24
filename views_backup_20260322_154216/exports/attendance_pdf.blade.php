<!DOCTYPE html>
<html>
<head>
    <title>Attendance Report</title>
    <style>
         body {
        margin: 0;
        padding: 0;
        font-family: Helvetica, sans-serif;
    }

    table {
        page-break-inside: auto;
        border-collapse: collapse;
        width: 95%;
        margin-left: 40px; /* 👈 Adds left margin for binding */
    }

    th, td {
        border: 1px solid #000;
        padding: 4px;
        text-align: center;
    }

    tr {
        page-break-inside: avoid;
    }

    thead {
        display: table-header-group;
    }

    tfoot {
        display: table-footer-group;
    }

    html, body {
        height: 99%;
    }

    </style>
</head>
<body>
    <div class="header">
        <h3>{{ $company->comp_nm ?? '' }} - Attendance Report </h3>
        <!-- <p>From {{ request('start_date') }} to {{ request('end_date') }}</p> -->
        <p>From {{ \Carbon\Carbon::parse($startDate)->format('m/d/Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('m/d/Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Participant ID</th>
                <th>Last Name</th>
                <th>First Name</th>
                @foreach ($dates as $date)
                    <th>{{ \Carbon\Carbon::parse($date)->format('d M') }}</th>
                @endforeach
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendanceData as $row)
            <tr>
                <td style="text-align: left;">{{ $row['ParticipantId'] }}</td>
                <td style="text-align: left;">{{ $row['LastName'] }}</td>
                <td style="text-align: left;">{{ $row['FirstName'] }}</td>
                @foreach ($dates as $date)
                    <td>{{ $row['attendance'][$date] ?? 0 }}</td>
                @endforeach
                <td><strong>{{ $row['row_total'] }}</strong></td>
            </tr>
            @endforeach
        </tbody>
        @if (!empty($attendanceData) && count($attendanceData))
        <tfoot>
            <tr class="fw-bold text-center table-secondary">
                <td colspan="3">Column Totals</td>
                @foreach ($dates as $date)
                    <td>{{ $columnTotals[$date] ?? 0 }}</td>
                @endforeach
                <td>{{ array_sum($columnTotals) }}</td>
            </tr>
        </tfoot>
    @endif
    </table>
    {{-- Page numbering script --}}

</body>
</html>
<script type="text/php">
    if (isset($pdf)) {
        $pdf->page_script('
            $font = $fontMetrics->get_font("Helvetica", "normal");
            $size = 9;
            $pageText = "Page " . $PAGE_NUM . " of " . $PAGE_COUNT;

            $textWidth = $fontMetrics->get_text_width($pageText, $font, $size);
            $x = $pdf->get_width() - $textWidth - 20;
            $y = $pdf->get_height() - 30;

            $pdf->text($x, $y, $pageText, $font, $size);
        ');
    }
</script>

