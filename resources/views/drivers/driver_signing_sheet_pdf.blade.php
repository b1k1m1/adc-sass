<!doctype html>
<html>
<head>
    <meta charset="utf-8">

    <title>Driver Signing Sheets</title>

    <style>
        @page {
            size: A4 landscape;
            margin: 5mm 0 5mm 0;
        }

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
            font-size: 12pt;
            font-weight: bold;
            font-style: italic;
            text-decoration: underline;
            white-space: nowrap;
        }

        .title-heading {
            width: 40%;
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            white-space: nowrap;
        }

        .date-heading {
            width: 30%;
            text-align: right;
            font-size: 12pt;
            font-weight: normal;
            white-space: nowrap;
        }

        .page-number {
            font-size: 8pt;
            white-space: nowrap;
        }

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

        .report thead th {
            height: 8.5mm;
            padding: 0.4mm 0.3mm;
            text-align: center;
            vertical-align: middle;
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

        .col-last {
            width: 9.53%;
        }

        .col-first {
            width: 11.70%;
        }

        .col-city {
            width: 14.90%;
        }

        .col-id {
            width: 4.14%;
        }

        .col-signature {
            width: 27.85%;
        }

        .col-arrival {
            width: 7.65%;
        }

        .col-departure {
            width: 9.21%;
        }

        .col-remarks {
            width: 8.49%;
        }

        .col-photo {
            width: 5.18%;
        }

        .col-insulin {
            width: 1.35%;
        }

        .report tbody tr {
            height: 10.35mm;
        }

        .report tbody td {
            height: 10.35mm;
            padding: 0.65mm 0.8mm 0.2mm 0.8mm;
            vertical-align: top;
            line-height: 1;
        }

        .last-value,
        .first-value {
            text-align: left;
            vertical-align: top;
            padding-top: 0.75mm !important;
            padding-left: 1mm !important;
            padding-right: 0.4mm !important;
            font-size: 12pt;
            font-weight: bold;
            line-height: 1;
            white-space: nowrap;
            overflow: hidden;
        }

        .city-value {
            text-align: left;
            vertical-align: top;
            padding-top: 0.55mm !important;
            padding-left: 1mm !important;
            padding-right: 0.6mm !important;
            font-size: 8pt;
            font-weight: bold;
            line-height: 1.05;
            white-space: normal;
            overflow: hidden;
        }

        .id-value {
            text-align: left;
            vertical-align: top;
            padding-top: 0.65mm !important;
            padding-left: 0.7mm !important;
            padding-right: 0.2mm !important;
            font-size: 12pt;
            font-weight: bold;
            line-height: 1;
            white-space: nowrap;
            overflow: hidden;
        }

        .signature-value,
        .arrival-value,
        .departure-value,
        .remarks-value {
            vertical-align: top;
        }

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

        .insulin-value {
            padding-top: 0.65mm !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            text-align: center;
            vertical-align: top;
            font-size: 12pt;
            font-weight: bold;
            line-height: 1;
            white-space: nowrap;
            overflow: hidden;
        }

        .blank-row,
        .blank-row td {
            height: 10.35mm;
        }
    </style>
</head>

<body>

@foreach ($driverReports as $driverReport)

    @foreach ($driverReport['pages'] as $pageIndex => $pageRows)

        @php
            $pageNumber = $pageIndex + 1;

            $isFinalDriver = $loop->parent->last;
            $isFinalPageOfDriver = $loop->last;

            $isFinalPage =
                $isFinalDriver &&
                $isFinalPageOfDriver;
        @endphp

        <div class="pdf-page {{ $isFinalPage ? 'last-page' : '' }}">

                        <table
                class="page-header"
                cellpadding="0"
                cellspacing="0"
            >
                <tr>
                    <td class="company-heading">
                        {{ $companyName }}
                    </td>

                    <td class="title-heading">
                        {{ $driverReport['title'] }}
                    </td>

                    <td class="date-heading">

                        Date: {{ $formattedDate }}

                        &nbsp;&nbsp;

                        <span class="page-number">
                            Page {{ $pageNumber }}
                            of {{ $driverReport['totalPages'] }}
                        </span>

                    </td>
                </tr>
            </table>

            <table
                class="report"
                cellpadding="0"
                cellspacing="0"
            >
                <thead>
                    <tr>

                        <th class="col-last">
                            Last
                        </th>

                        <th class="col-first">
                            First
                        </th>

                        <th class="col-city">
                            City
                        </th>

                        <th class="col-id">
                            ID
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

                        <th class="col-insulin insulin-header"></th>

                    </tr>
                </thead>

                <tbody>

                    @foreach ($pageRows as $member)

                        @php
                            $address = trim(
                                (string) (
                                    $member->address1 ?? ''
                                )
                            );

                            $city = trim(
                                (string) (
                                    $member->city ?? ''
                                )
                            );

                            if (
                                $address !== '' &&
                                $city !== ''
                            ) {
                                $cityColumn =
                                    $address .
                                    ', ' .
                                    $city;
                            } elseif ($address !== '') {
                                $cityColumn = $address;
                            } else {
                                $cityColumn = $city;
                            }

                            $paddedId = str_pad(
                                trim(
                                    (string) $member->ParticipantId
                                ),
                                3,
                                '0',
                                STR_PAD_LEFT
                            );

                            $rawInsulinValue = strtoupper(
                                trim(
                                    (string) (
                                        $member->insulin_yn ?? ''
                                    )
                                )
                            );

                            $insulinValue = in_array(
                                $rawInsulinValue,
                                ['1', 'Y', 'YES', 'TRUE'],
                                true
                            ) ? 'Y' : '';
                        @endphp

                        <tr>

                            <td class="last-value">
                                {{ $member->last }}
                            </td>

                            <td class="first-value">
                                {{ $member->first }}
                            </td>

                            <td class="city-value">
                                {{ $cityColumn }}
                            </td>

                            <td class="id-value">
                                {{ $paddedId }}
                            </td>

                            <td class="signature-value"></td>

                            <td class="arrival-value"></td>

                            <td class="departure-value"></td>

                            <td class="remarks-value"></td>

                            <td class="photo-cell">

                                @if (!empty($member->photo_uri))

                                    <img
                                        src="{{ $member->photo_uri }}"
                                        class="member-photo"
                                        alt=""
                                    >

                                @endif

                            </td>

                            <td class="insulin-value">
                                {{ $insulinValue }}
                            </td>

                        </tr>

                    @endforeach

                    @for (
                        $blankRow = $pageRows->count();
                        $blankRow < $rowsPerPage;
                        $blankRow++
                    )

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
                        </tr>

                    @endfor

                </tbody>
            </table>

        </div>

    @endforeach

@endforeach

</body>
</html>
