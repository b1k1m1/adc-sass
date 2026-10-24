<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Members Attendance Report</title>
    <style>
        * { box-sizing: border-box; }

        /* Make text a bit smaller and remove default margin */
        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 10px; /* was 11px */
            margin: 0;
        }

        table.report {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 10px;  /* match body */
        }

        thead { display: table-header-group; }
        tfoot { display: table-footer-group; }

        .report th,
        .report td {
            border: 1px solid #000;
            padding: 3px 2px;     /* was 8px 6px */
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
            line-height: 1.2;     /* tighter line height */
            /* remove fixed height so rows can shrink */
            /* height: 36px; */

        }

        .report th {
            font-weight: 700;
        }

        /* Force left align on text columns */
        .report td:nth-child(1),
        .report td:nth-child(2),
        .report td:nth-child(3),
        .report td:nth-child(4) {
            text-align: left !important;
        }

        /* Column widths – keep total at 100% */

        .col-last       { width: 12%; }
        .col-first      { width: 12%; }
        .col-city       { width: 22%; }   /* increased */
        .col-id         { width: 6%; }
        .col-sign       { width: 18%; }   /* increased */
        .col-arrival    { width: 10%; }
        .col-departure  { width: 10%; }
        .col-remarks    { width: 5%; }
        .col-photo      { width: 5%; }
        .text-left { text-align: left !important; }

        .photo {
            width: 40px;   /* slightly smaller image */
            height: 40px;
            object-fit: cover;
            border-radius: 50%;
            border: 1px solid #999;
        }
    </style>
</head>
<body>

<table class="report" width="100%" cellspacing="0" cellpadding="0" border="1">
    <thead>
        <tr>
            <th class="col-last">Last</th>
            <th class="col-first">First</th>
            <th class="col-city">City</th>
            <th class="col-id">ID</th>
            <th class="col-sign">Signature</th>
            <th class="col-arrival">
                Arrival time<br>at center
            </th>
            <th class="col-departure">
                Departure time<br>from center
            </th>
            <th class="col-remarks">Remarks</th>
            <th class="col-photo">Photo</th>
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $member)
            @php
                $cityCol = trim(
                    ($member->address1 ?? '') .
                    (empty($member->address1) || empty($member->city) ? '' : ', ') .
                    ($member->city ?? '')
                );
                $paddedId = str_pad($member->ParticipantId, 3, '0', STR_PAD_LEFT);
            @endphp
            <tr>
                <td class="text-left">{{ $member->last }}</td>
                <td class="text-left">{{ $member->first }}</td>
                <td class="text-left">{{ $cityCol }}</td>
                <td>{{ $paddedId }}</td>
                <td></td> {{-- Signature --}}
                <td></td> {{-- Arrival --}}
                <td></td> {{-- Departure --}}
                <td></td> {{-- Remarks --}}
                <td>
                    <img src="{{ $member->photo_uri }}"
                         class="photo"
                         alt="photo">
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" style="text-align:center;">No records found</td>
            </tr>
        @endforelse
    </tbody>
</table>

</body>
</html>
