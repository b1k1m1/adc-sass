<!DOCTYPE html>

<html>

<head>

    <meta charset="utf-8">

    <title>
        Pending Rebill Report
    </title>


    <style>

        @page {
            margin:
                18px
                18px
                25px
                18px;
        }


        body {
            font-family:
                Arial,
                Helvetica,
                sans-serif;

            font-size: 9px;

            color: #222;
        }


        .report-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 3px;
        }


        .report-subtitle {
            text-align: center;
            font-size: 9px;
            margin-bottom: 12px;
            color: #555;
        }


        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }


        .summary-table td {
            border: 1px solid #bbb;
            padding: 5px;
            text-align: center;
        }


        .summary-label {
            font-size: 8px;
            color: #555;
        }


        .summary-number {
            font-size: 12px;
            font-weight: bold;
        }


        .report-table {
            width: 100%;
            border-collapse: collapse;
        }


        .report-table th {
            background: #eeeeee;
            border: 1px solid #999;
            padding: 4px 3px;
            font-size: 8px;
            text-align: left;
        }


        .report-table td {
            border: 1px solid #bbb;
            padding: 4px 3px;
            vertical-align: top;
            font-size: 8px;
        }


        .center {
            text-align: center;
        }


        .reason-column {
            width: 22%;
        }


        .status-pending {
            font-weight: bold;
        }


        .status-resolved {
            font-weight: bold;
        }


        .status-archived {
            font-weight: bold;
        }


        .footer {
            position: fixed;

            bottom: -15px;

            left: 0;

            right: 0;

            text-align: center;

            font-size: 8px;

            color: #666;
        }

    </style>

</head>


<body>


    {{-- ========================================================= --}}
    {{-- HEADER --}}
    {{-- ========================================================= --}}

    <div class="report-title">

        Pending Rebill Report

    </div>


    <div class="report-subtitle">

        Status:

        <strong>

            {{
                ucfirst(
                    $status
                )
            }}

        </strong>

        &nbsp; | &nbsp;

        Printed:

        {{
            now()->format(
                'm/d/Y h:i A'
            )
        }}

    </div>


    {{-- ========================================================= --}}
    {{-- SUMMARY --}}
    {{-- ========================================================= --}}

    <table class="summary-table">

        <tr>


            <td>

                <div class="summary-number">

                    {{
                        number_format(
                            $summary['pending']
                        )
                    }}

                </div>

                <div class="summary-label">

                    Pending

                </div>

            </td>


            <td>

                <div class="summary-number">

                    {{
                        number_format(
                            $summary['resolved']
                        )
                    }}

                </div>

                <div class="summary-label">

                    Resolved

                </div>

            </td>


            <td>

                <div class="summary-number">

                    {{
                        number_format(
                            $summary['archived']
                        )
                    }}

                </div>

                <div class="summary-label">

                    Archived

                </div>

            </td>


            <td>

                <div class="summary-number">

                    {{
                        number_format(
                            $rows->count()
                        )
                    }}

                </div>

                <div class="summary-label">

                    Displayed

                </div>

            </td>


        </tr>

    </table>


    {{-- ========================================================= --}}
    {{-- REPORT --}}
    {{-- ========================================================= --}}

    <table class="report-table">


        <thead>

            <tr>

                <th>
                    ID
                </th>

                <th>
                    Participant
                </th>

                <th>
                    Member Name
                </th>

                <th>
                    Insurance
                </th>

                <th>
                    Svc From
                </th>

                <th>
                    Svc To
                </th>

                <th>
                    Old PA
                </th>

                <th>
                    Current PA
                </th>

                <th>
                    PA Start
                </th>

                <th>
                    PA End
                </th>

                <th class="reason-column">
                    Reason
                </th>

                <th>
                    Status
                </th>

            </tr>

        </thead>


        <tbody>


            @forelse ($rows as $row)


                <tr>


                    {{-- ID --}}

                    <td>

                        {{
                            $row->id
                        }}

                    </td>


                    {{-- PARTICIPANT --}}

                    <td>

                        {{
                            $row->ParticipantId
                        }}

                    </td>


                    {{-- MEMBER --}}

                    <td>

                        {{
                            $row->member_name
                        }}

                    </td>


                    {{-- INSURANCE --}}

                    <td>

                        {{
                            $row->insurance_name
                        }}

                    </td>


                    {{-- SERVICE FROM --}}

                    <td>

                        @if (
                            $row->service_from
                        )

                            {{
                                \Carbon\Carbon::parse(
                                    $row->service_from
                                )
                                ->format(
                                    'm/d/Y'
                                )
                            }}

                        @endif

                    </td>


                    {{-- SERVICE TO --}}

                    <td>

                        @if (
                            $row->service_to
                        )

                            {{
                                \Carbon\Carbon::parse(
                                    $row->service_to
                                )
                                ->format(
                                    'm/d/Y'
                                )
                            }}

                        @endif

                    </td>


                    {{-- OLD PA --}}

                    <td>

                        {{
                            $row->prior_auth_no
                        }}

                        @if (
                            $row->prior_auth_expiry
                        )

                            <br>

                            Exp:

                            {{
                                \Carbon\Carbon::parse(
                                    $row->prior_auth_expiry
                                )
                                ->format(
                                    'm/d/Y'
                                )
                            }}

                        @endif

                    </td>


                    {{-- CURRENT PA --}}

                    <td>

                        {{
                            $row->current_pa_no
                            ?? ''
                        }}

                    </td>


                    {{-- PA START --}}

                    <td>

                        @if (
                            $row->current_pa_start
                        )

                            {{
                                \Carbon\Carbon::parse(
                                    $row->current_pa_start
                                )
                                ->format(
                                    'm/d/Y'
                                )
                            }}

                        @endif

                    </td>


                    {{-- PA END --}}

                    <td>

                        @if (
                            $row->current_pa_end
                        )

                            {{
                                \Carbon\Carbon::parse(
                                    $row->current_pa_end
                                )
                                ->format(
                                    'm/d/Y'
                                )
                            }}

                        @endif

                    </td>


                    {{-- REASON --}}

                    <td>

                        @if (
                            !empty(
                                $row->error_type
                            )
                        )

                            <strong>

                                {{
                                    $row->error_type
                                }}

                            </strong>

                            <br>

                        @endif


                        {{
                            $row->error_message
                        }}

                    </td>


                    {{-- STATUS --}}

                    <td class="center">

                        @if (
                            $row->status
                            === 'resolved'
                        )

                            <span
                                class="
                                    status-resolved
                                "
                            >
                                Resolved
                            </span>

                        @elseif (
                            $row->status
                            === 'archived'
                        )

                            <span
                                class="
                                    status-archived
                                "
                            >
                                Archived
                            </span>

                        @else

                            <span
                                class="
                                    status-pending
                                "
                            >
                                Pending
                            </span>

                        @endif

                    </td>


                </tr>


            @empty


                <tr>

                    <td
                        colspan="12"
                        class="center"
                    >

                        No records found.

                    </td>

                </tr>


            @endforelse


        </tbody>


    </table>


    {{-- ========================================================= --}}
    {{-- FOOTER --}}
    {{-- ========================================================= --}}

    <div class="footer">

        Pending Rebill Report

    </div>


</body>

</html>