<!doctype html>
<html>

<head>
    <meta charset="utf-8">

    <title>Members Attendance Signature Sheet</title>

    <style>
        /*
        |--------------------------------------------------------------------------
        | PDF page
        |--------------------------------------------------------------------------
        */



        * {
            box-sizing: border-box;
        }

        html,
        body {
            width: 297mm;
            margin: 0;
            padding: 0;

            font-family:
                "Microsoft Sans Serif",
                "MS Sans Serif",
                Arial,
                Helvetica,
                sans-serif;

            color: #000;
        }

        /*
        |--------------------------------------------------------------------------
        | One fixed report sheet per page
        |--------------------------------------------------------------------------
        */

        .pdf-page {
            width: 267mm;
            margin-left: 15mm;
            margin-right: 15mm;
            padding: 0;
            page-break-after: always;

        }

        .pdf-page.last-page {
            page-break-after: auto;
        }

        /*
        |--------------------------------------------------------------------------
        | Page heading
        |--------------------------------------------------------------------------
        */

        table.page-header {
            width: 267mm;
            max-width: 267mm;
            border-collapse: collapse;
            table-layout: fixed;
            margin: 0 0 1mm 0;
            padding: 0;
        }


        .page-header td {
            height: 8mm;
            padding: 0;
            vertical-align: bottom;
            border: 0;
        }

        .company-heading {
            width: 30%;
            text-align: left;

            font-family:
                "Microsoft Sans Serif",
                "MS Sans Serif",
                Arial,
                Helvetica,
                sans-serif;

            font-size: 12pt;
            font-weight: bold;
            font-style: italic;
            text-decoration: underline;
            white-space: nowrap;
        }

        .title-heading {
            width: 40%;
            text-align: center;

            font-family:
                "Microsoft Sans Serif",
                "MS Sans Serif",
                Arial,
                Helvetica,
                sans-serif;

            font-size: 12pt;
            font-weight: bold;
            white-space: nowrap;
        }

        .date-heading {
            width: 30%;
            text-align: right;

            font-family:
                "Microsoft Sans Serif",
                "MS Sans Serif",
                Arial,
                Helvetica,
                sans-serif;

            font-size: 12pt;
            font-weight: normal;
            white-space: nowrap;
        }

        .page-number {
            font-size: 8pt;
            white-space: nowrap;
        }

        /*
        |--------------------------------------------------------------------------
        | Attendance table
        |--------------------------------------------------------------------------
        */

        table.report {
            width: 267mm;
            max-width: 267mm;
            border-collapse: collapse;
            table-layout: fixed;
            margin: 0;
            padding: 0;
        }

        .report th,
        .report td {
            border: 1px solid #000;
            overflow: hidden;
        }

        /*
        |--------------------------------------------------------------------------
        | Column headings
        |--------------------------------------------------------------------------
        */

        .report thead th {
            height: 8.5mm;
            padding: 0.4mm 0.3mm;

            text-align: center;
            vertical-align: middle;

            font-family:
                "Microsoft Sans Serif",
                "MS Sans Serif",
                Arial,
                Helvetica,
                sans-serif;

            font-size: 9pt;
            font-weight: bold;
            line-height: 0.95;
        }

        .time-header {
            font-size: 8.5pt !important;
            line-height: 0.9 !important;
        }

        .insulin-header {
            font-size: 7pt !important;
            line-height: 0.85 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        /*
        |--------------------------------------------------------------------------
        | Fixed legacy column proportions
        |--------------------------------------------------------------------------
        |
        | Total legacy width = 4417
        |
        */

        .col-last {
            width: 9.00%;
        }

        .col-first {
            width: 10.50%;
        }

        .col-korean {
            width: 5.50%;
        }

        .col-city {
            width: 15.50%;
        }

        .col-id {
            width: 4.50%;
        }

        .col-signature {
            width: 27.00%;
        }

        .col-arrival {
            width: 7.50%;
        }

        .col-departure {
            width: 9.00%;
        }

        .col-remarks {
            width: 6.50%;
        }

        .col-photo {
            width: 4.00%;
        }

        .col-insulin {
            width: 1.00%;
        }

        .korean-value {
            text-align: center;
            vertical-align: top;

            padding-top: 0.75mm !important;
            padding-left: 0.2mm !important;
            padding-right: 0.2mm !important;

            font-family: 'NotoSansKR', sans-serif;

            font-size: 11pt;
            font-weight: bold;
            line-height: 1;

            white-space: nowrap;
            overflow: hidden;
        }

        /*
        |--------------------------------------------------------------------------
        | Exactly 17 detail rows
        |--------------------------------------------------------------------------
        |
        | Header 8mm + column header 8.5mm + 17 x 10.35mm remains safely
        | within the A4 landscape printable height.
        |
        */

        .report tbody tr {
            height: 10.35mm;
        }

        .report tbody td {
            height: 10.35mm;
            padding: 0.65mm 0.8mm 0.2mm 0.8mm;
            vertical-align: top;
            line-height: 1;
        }

        /*
        |--------------------------------------------------------------------------
        | Last name
        |--------------------------------------------------------------------------
        |
        | Do not wrap.
        |
        */

        .last-value {
            text-align: left;
            vertical-align: top;

            padding-top: 0.75mm !important;
            padding-left: 1mm !important;
            padding-right: 0.4mm !important;

            font-family:
                "Microsoft Sans Serif",
                "MS Sans Serif",
                Arial,
                Helvetica,
                sans-serif;

            font-size: 12pt;
            font-weight: bold;
            line-height: 1;

            white-space: nowrap;
            overflow: hidden;
        }

        /*
        |--------------------------------------------------------------------------
        | First name
        |--------------------------------------------------------------------------
        |
        | Do not wrap.
        |
        */

        .first-value {
            text-align: left;
            vertical-align: top;

            padding-top: 0.75mm !important;
            padding-left: 1mm !important;
            padding-right: 0.4mm !important;

            font-family:
                "Microsoft Sans Serif",
                "MS Sans Serif",
                Arial,
                Helvetica,
                sans-serif;

            font-size: 12pt;
            font-weight: bold;
            line-height: 1;

            white-space: nowrap;
            overflow: hidden;
        }

        /*
        |--------------------------------------------------------------------------
        | Address and city
        |--------------------------------------------------------------------------
        |
        | Wrapping is permitted to mimic the legacy report.
        |
        */

        .city-value {
            text-align: left;
            vertical-align: top;

            padding-top: 0.55mm !important;
            padding-left: 1mm !important;
            padding-right: 0.6mm !important;

            font-family:
                "Microsoft Sans Serif",
                "MS Sans Serif",
                Arial,
                Helvetica,
                sans-serif;

            font-size: 12pt;
            font-weight: bold;
            line-height: 1.05;

            white-space: normal;
            overflow: hidden;
        }

        /*
        |--------------------------------------------------------------------------
        | Participant ID
        |--------------------------------------------------------------------------
        */

        .id-value {
            text-align: left;
            vertical-align: top;

            padding-top: 0.65mm !important;
            padding-left: 0.7mm !important;
            padding-right: 0.2mm !important;

            font-family:
                "Microsoft Sans Serif",
                "MS Sans Serif",
                Arial,
                Helvetica,
                sans-serif;

            font-size: 12pt;
            font-weight: bold;
            line-height: 1;

            white-space: nowrap;
            overflow: hidden;
        }

        /*
        |--------------------------------------------------------------------------
        | Signature and writing fields
        |--------------------------------------------------------------------------
        */

        .signature-value,
        .arrival-value,
        .departure-value,
        .remarks-value {
            vertical-align: top;
        }

        /*
        |--------------------------------------------------------------------------
        | Member photo
        |--------------------------------------------------------------------------
        */

        .photo-cell {
            padding: 0.2mm !important;
            text-align: center;
            vertical-align: middle !important;
        }

        .member-photo {
            width: 11mm;
            height: 9.3mm;
            border: 0;
            vertical-align: middle;
        }

        /*
        |--------------------------------------------------------------------------
        | Insulin Y/N
        |--------------------------------------------------------------------------
        */

        .insulin-value {
            padding-top: 0.65mm !important;
            padding-left: 0 !important;
            padding-right: 0 !important;

            text-align: center;
            vertical-align: top;

            font-family:
                "Microsoft Sans Serif",
                "MS Sans Serif",
                Arial,
                Helvetica,
                sans-serif;

            font-size: 12pt;
            font-weight: bold;
            line-height: 1;

            white-space: nowrap;
            overflow: hidden;
        }

        /*
        |--------------------------------------------------------------------------
        | Blank detail rows
        |--------------------------------------------------------------------------
        */

        .blank-row,
        .blank-row td {
            height: 10.35mm;
        }
    </style>
</head>

<body>

    @foreach ($pages as $pageIndex => $pageRows)
        @php
            $pageNumber = $pageIndex + 1;
        @endphp

        <div class="pdf-page {{ $loop->last ? 'last-page' : '' }}">
            {{-- ============================================================
             REPORT HEADER
        ============================================================ --}}

            <table class="page-header" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="company-heading">
                        {{ $companyName }}
                    </td>

                    <td class="title-heading">
                        {{ $title }}
                    </td>

                    <td class="date-heading">
                        Date: ____________________

                        &nbsp;&nbsp;

                        <span class="page-number">
                            Page {{ $pageNumber }} of {{ $totalPages }}
                        </span>
                    </td>
                </tr>
            </table>

            {{-- ============================================================
             ATTENDANCE TABLE
        ============================================================ --}}

            <table class="report" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <th class="col-id">
                            ID#
                        </th>

                        <th class="col-last">
                            Last
                        </th>

                        <th class="col-first">
                            First
                        </th>

                        <th class="col-korean">
                            Korean<br>Name
                        </th>

                        <th class="col-city">
                            City
                        </th>

                        <th class="col-signature">
                            Signature
                        </th>

                        <th class="col-arrival time-header">
                            Arrival Time<br>
                            at Center
                        </th>

                        <th class="col-departure time-header">
                            Departure Time<br>
                            from Center
                        </th>

                        <th class="col-remarks">
                            Remarks
                        </th>

                        <th class="col-photo">
                            Photo
                        </th>

                        <th class="col-insulin insulin-header">

                        </th>
                    </tr>
                </thead>

                <tbody>

                    @foreach ($pageRows as $member)
                        @php
                            /*
                        |--------------------------------------------------------------------------
                        | Address and City
                        |--------------------------------------------------------------------------
                        */

                            $cityColumn = trim((string) ($member->city ?? ''));

                            /*
                        |--------------------------------------------------------------------------
                        | Participant ID
                        |--------------------------------------------------------------------------
                        */

                            $paddedId = str_pad(trim((string) $member->ParticipantId), 3, '0', STR_PAD_LEFT);

                            /*
                        |--------------------------------------------------------------------------
                        | Insulin Y/N
                        |--------------------------------------------------------------------------
                        */

                            $rawInsulinValue = strtoupper(trim((string) ($member->insulin_yn ?? '')));

                            $insulinValue = in_array($rawInsulinValue, ['1', 'Y', 'YES', 'TRUE'], true) ? 'Y' : '';
                        @endphp

                        <tr>
                            <td class="id-value">
                                {{ $paddedId }}
                            </td>

                            <td class="last-value">
                                {{ $member->last }}
                            </td>

                            <td class="first-value">
                                {{ $member->first }}
                            </td>

                            <td class="korean-value">
                                {{ $member->comments ?? '' }}
                            </td>

                            <td class="city-value">
                                {{ $cityColumn }}
                            </td>

                            <td class="signature-value"></td>

                            <td class="arrival-value"></td>

                            <td class="departure-value"></td>

                            <td class="remarks-value"></td>

                            <td class="photo-cell">
                                @if (!empty($member->photo_uri))
                                    <img src="{{ $member->photo_uri }}" class="member-photo" alt="">
                                @endif
                            </td>

                            <td class="insulin-value">
                                {{ $insulinValue }}
                            </td>
                        </tr>
                    @endforeach

                    {{--
                |--------------------------------------------------------------------------
                | Complete every page to exactly 17 rows
                |--------------------------------------------------------------------------
                --}}

                    @for ($blankRow = $pageRows->count(); $blankRow < $rowsPerPage; $blankRow++)
                        <tr class="blank-row">
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endfor

                </tbody>
            </table>
        </div>
    @endforeach

</body>

</html>
