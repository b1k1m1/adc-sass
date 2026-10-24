<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">

    <title>Member Statement</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #222;
        }

        .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .subtitle {
            text-align: center;
            font-size: 11px;
            margin-bottom: 15px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .info-table td {
            padding: 3px 5px;
            vertical-align: top;
        }

        .info-label {
            font-weight: bold;
            width: 110px;
        }

        .ledger-table {
            width: 100%;
            border-collapse: collapse;
        }

        .ledger-table th {
            background-color: #eeeeee;
            border: 1px solid #777;
            padding: 5px;
            font-weight: bold;
        }

        .ledger-table td {
            border: 1px solid #999;
            padding: 4px 5px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .totals-row td {
            font-weight: bold;
            background-color: #eeeeee;
        }

        .balance-box {
            font-weight: bold;
        }
    </style>

</head>

<body>

    <div class="title">
        Member Statement / Ledger
    </div>

    <div class="subtitle">
        Statement Period:
        {{ $dateFromFormatted }}
        -
        {{ $dateToFormatted }}
    </div>


    <table class="info-table">

        <tr>

            <td class="info-label">
                Member:
            </td>

            <td>
                {{ $member->LastName ?? '' }},
                {{ $member->FirstName ?? '' }}
            </td>

            <td class="info-label">
                Opening Balance:
            </td>

            <td class="text-right balance-box">
                ${{ number_format($openingBalance, 2) }}
            </td>

        </tr>

        <tr>

            <td class="info-label">
                Participant ID:
            </td>

            <td>
                {{ $member->ParticipantId }}
            </td>

            <td class="info-label">
                Closing Balance:
            </td>

            <td class="text-right balance-box">
                ${{ number_format($closingBalance, 2) }}
            </td>

        </tr>

        <tr>

            <td class="info-label">
                Medicaid #:
            </td>

            <td>
                {{ $member->MedicaidNo ?? '' }}
            </td>

            <td></td>
            <td></td>

        </tr>

    </table>


    <table class="ledger-table">

        <thead>

            <tr>

                <th style="width: 8%;">
                    Date
                </th>

                <th style="width: 8%;">
                    Claim #
                </th>

                <th style="width: 17%;">
                    Service Period
                </th>

                <th style="width: 25%;">
                    Insurance Company
                </th>

                <th style="width: 10%;">
                    Charge
                </th>

                <th style="width: 10%;">
                    Payment
                </th>

                <th style="width: 11%;">
                    Patient Resp.
                </th>

                <th style="width: 11%;">
                    Balance
                </th>

            </tr>

        </thead>

        <tbody>

            @forelse ($transactions as $transaction)
                <tr>

                    <td class="text-center">
                        {{ \Carbon\Carbon::parse($transaction->service_from)->format('m/d/Y') }}
                    </td>

                    <td class="text-center">
                        {{ $transaction->claim_no }}
                    </td>

                    <td class="text-center">

                        {{ \Carbon\Carbon::parse($transaction->service_from)->format('m/d/Y') }}

                        -

                        {{ \Carbon\Carbon::parse($transaction->service_to)->format('m/d/Y') }}

                    </td>

                    <td>
                        {{ $transaction->insurance_name }}
                    </td>

                    <td class="text-right">
                        {{ number_format($transaction->charge_amount, 2) }}
                    </td>

                    <td class="text-right">
                        {{ number_format($transaction->paid_amount, 2) }}
                    </td>

                    <td class="text-right">
                        {{ number_format($transaction->patient_responsibility, 2) }}
                    </td>

                    <td class="text-right">
                        {{ number_format($transaction->balance, 2) }}
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="8" class="text-center">
                        No claim transactions found.
                    </td>
                </tr>
            @endforelse

        </tbody>

        <tfoot>

            <tr class="totals-row">

                <td colspan="4" class="text-right">
                    TOTAL
                </td>

                <td class="text-right">
                    {{ number_format($totalCharge, 2) }}
                </td>

                <td class="text-right">
                    {{ number_format($totalPayment, 2) }}
                </td>

                <td class="text-right">
                    {{ number_format($totalPatientResp, 2) }}
                </td>

                <td class="text-right">
                    {{ number_format($closingBalance, 2) }}
                </td>

            </tr>

        </tfoot>

    </table>

</body>

</html>
