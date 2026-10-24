<!DOCTYPE html>

<html>

<head>

    <meta charset="utf-8">

    <title>
        Submitted Claims Report
    </title>


    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #000;
        }

        h2 {
            margin: 0 0 8px 0;
            text-align: center;
        }

        .batch-info {
            margin-bottom: 15px;
        }

        .batch-info table {
            width: 100%;
            border-collapse: collapse;
        }

        .batch-info td {
            padding: 2px 4px;
        }

        .claims-table {
            width: 100%;
            border-collapse: collapse;
        }

        .claims-table th,
        .claims-table td {
            border: 1px solid #777;
            padding: 5px;
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

        .footer-total {
            font-weight: bold;
            background: #f5f5f5;
        }
    </style>

</head>


<body>


    <h2>
        Submitted Claims Report
    </h2>


    <div class="batch-info">

        <table>

            <tr>

                <td>
                    <strong>Batch ID:</strong>
                    {{ $batch->id }}
                </td>

                <td>
                    <strong>Batch Date:</strong>

                    @if ($batch->batch_timestamp)
                        {{ \Carbon\Carbon::parse($batch->batch_timestamp)->format('m/d/Y h:i A') }}
                    @endif

                </td>

            </tr>


            <tr>

                <td colspan="2">

                    <strong>837P File:</strong>
                    {{ $batch->edi_filename }}

                </td>

            </tr>


            <tr>

                <td>

                    <strong>Total Claims:</strong>

                    {{ number_format($batch->total_claims ?? 0) }}

                </td>

                <td>

                    <strong>Total Amount:</strong>

                    ${{ number_format($batch->total_amount ?? 0, 2) }}

                </td>

            </tr>

        </table>

    </div>


    <table class="claims-table">

        <thead>

            <tr>

                <th>
                    Claim #
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
                    Units
                </th>

                <th>
                    Amount
                </th>

                <th>
                    Payer
                </th>

            </tr>

        </thead>


        <tbody>

            @php

                $reportTotal = 0;

            @endphp


            @foreach ($claims as $claim)
                @php

                    $reportTotal += (float) ($claim->total_amount ?? 0);

                    $memberName = trim(($claim->member_lastname ?? '') . ', ' . ($claim->member_firstname ?? ''));

                    $memberName = trim($memberName, ', ');
                @endphp


                <tr>

                    <td>
                        {{ $claim->submitted_claim_no }}
                    </td>


                    <td>
                        {{ $claim->ParticipantId }}
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

                        {{ $claim->units ?? '' }}

                    </td>


                    <td class="text-right">

                        ${{ number_format($claim->total_amount ?? 0, 2) }}

                    </td>


                    <td>

                        {{ $claim->payer_fullname ?? ($claim->insurance_name ?? '') }}

                    </td>

                </tr>
            @endforeach


            <tr class="footer-total">

                <td colspan="6" class="text-right">

                    Total

                </td>

                <td class="text-right">

                    ${{ number_format($reportTotal, 2) }}

                </td>

                <td></td>

            </tr>

        </tbody>

    </table>


</body>

</html>
