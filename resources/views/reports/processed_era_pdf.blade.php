<!DOCTYPE html>

<html>

<head>

    <meta charset="utf-8">

    <title>
        Processed ERA Report
    </title>


    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #000;
        }

        h2 {
            margin: 0 0 10px 0;
            text-align: center;
        }

        .era-info {
            margin-bottom: 12px;
        }

        .era-info table {
            width: 100%;
            border-collapse: collapse;
        }

        .era-info td {
            padding: 2px 4px;
            vertical-align: top;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .summary-table td {
            border: 1px solid #999;
            padding: 5px;
        }

        .claims-table {
            width: 100%;
            border-collapse: collapse;
        }

        .claims-table th,
        .claims-table td {
            border: 1px solid #777;
            padding: 4px;
        }

        .claims-table th {
            background: #eeeeee;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-row {
            font-weight: bold;
            background: #f5f5f5;
        }
    </style>

</head>


<body>


    <h2>
        Processed ERA Report
    </h2>


    {{-- =========================================================
         ERA Header
    ========================================================== --}}

    <div class="era-info">

        <table>

            <tr>

                <td>
                    <strong>ERA ID:</strong>
                    {{ $era->id }}
                </td>


                <td>
                    <strong>Payment Date:</strong>

                    @if ($era->payment_date)
                        {{ \Carbon\Carbon::parse($era->payment_date)->format('m/d/Y') }}
                    @endif

                </td>

            </tr>


            <tr>

                <td>
                    <strong>ERA File:</strong>
                    {{ $era->file_name ?? '' }}
                </td>


                <td>
                    <strong>Payer:</strong>
                    {{ $era->payer_name ?? '' }}
                </td>

            </tr>


            <tr>

                <td>
                    <strong>Trace #:</strong>
                    {{ $era->trace_number ?? '' }}
                </td>


                <td>

                    <strong>ERA Payment Amount:</strong>

                    ${{ number_format($era->payment_amount ?? 0, 2) }}

                </td>

            </tr>

        </table>

    </div>


    {{-- =========================================================
         Summary
    ========================================================== --}}

    <table class="summary-table">

        <tr>

            <td>
                <strong>Claims:</strong>
                {{ number_format($claims->count()) }}
            </td>


            <td>
                <strong>Total Charge:</strong>

                ${{ number_format($totalCharge, 2) }}
            </td>


            <td>
                <strong>Total Paid:</strong>

                ${{ number_format($totalPaid, 2) }}
            </td>


            <td>
                <strong>Patient Responsibility:</strong>

                ${{ number_format($totalPatientResponsibility, 2) }}
            </td>

        </tr>

    </table>


    {{-- =========================================================
         Claims
    ========================================================== --}}

    <table class="claims-table">

        <thead>

            <tr>

                <th>
                    Patient Control #
                </th>

                <th>
                    Payer Claim #
                </th>

                <th>
                    Member ID
                </th>

                <th>
                    Member Name
                </th>

                <th>
                    From
                </th>

                <th>
                    To
                </th>

                <th>
                    Charge
                </th>

                <th>
                    Paid
                </th>

                <th>
                    Patient Resp.
                </th>

                <th>
                    Status
                </th>

            </tr>

        </thead>


        <tbody>

            @foreach ($claims as $claim)
                @php

                    $memberName = trim(($claim->member_lastname ?? '') . ', ' . ($claim->member_firstname ?? ''));

                    $memberName = trim($memberName, ', ');
                @endphp


                <tr>

                    <td>
                        {{ $claim->patient_control_number }}
                    </td>


                    <td>
                        {{ $claim->payer_claim_number ?? '' }}
                    </td>


                    <td>
                        {{ $claim->member_id ?? '' }}
                    </td>


                    <td>
                        {{ $memberName }}
                    </td>


                    <td class="text-center">

                        @if ($claim->service_from)
                            {{ \Carbon\Carbon::parse($claim->service_from)->format('m/d/Y') }}
                        @endif

                    </td>


                    <td class="text-center">

                        @if ($claim->service_to)
                            {{ \Carbon\Carbon::parse($claim->service_to)->format('m/d/Y') }}
                        @endif

                    </td>


                    <td class="text-right">

                        ${{ number_format($claim->total_charge ?? 0, 2) }}

                    </td>


                    <td class="text-right">

                        ${{ number_format($claim->total_paid ?? 0, 2) }}

                    </td>


                    <td class="text-right">

                        ${{ number_format($claim->patient_responsibility ?? 0, 2) }}

                    </td>


                    <td>

                        {{ $claim->status_description }}

                        @if ($claim->claim_status_code !== null)
                            ({{ $claim->claim_status_code }})
                        @endif

                    </td>

                </tr>
            @endforeach


            <tr class="total-row">

                <td colspan="6" class="text-right">
                    Total
                </td>


                <td class="text-right">

                    ${{ number_format($totalCharge, 2) }}

                </td>


                <td class="text-right">

                    ${{ number_format($totalPaid, 2) }}

                </td>


                <td class="text-right">

                    ${{ number_format($totalPatientResponsibility, 2) }}

                </td>


                <td></td>

            </tr>

        </tbody>

    </table>


</body>

</html>
