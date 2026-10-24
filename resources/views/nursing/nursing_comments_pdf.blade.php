<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nursing Comments</title>

    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            color: #000;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .header-table td {
            vertical-align: top;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 4px;
        }

        .subtitle {
            font-size: 13px;
            text-align: center;
            margin-bottom: 10px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .info-table td {
            border: 1px solid #000;
            padding: 6px;
            font-size: 12px;
        }

        .label {
            font-weight: bold;
            background-color: #f2f2f2;
            width: 18%;
        }

        .comments-box {
            border: 1px solid #000;
            min-height: 520px;
            padding: 10px;
            line-height: 1.5;
            white-space: pre-wrap;
            word-wrap: break-word;
            font-size: 12px;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }

        .footer-table td {
            font-size: 12px;
            padding-top: 20px;
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 220px;
            display: inline-block;
            padding-top: 4px;
        }

        .printed {
            text-align: right;
            font-size: 10px;
            margin-top: 15px;
        }
    </style>
</head>

<body>

    <div class="title">
        Nursing Comments
    </div>

    <div class="subtitle">
        {{ $shiftName }}
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Member ID</td>
            <td>
                {{ str_pad($member->ParticipantId, 3, '0', STR_PAD_LEFT) }}
            </td>

            <td class="label">Member Name</td>
            <td>
                {{ trim($member->LastName . ', ' . $member->FirstName) }}
            </td>
        </tr>

        <tr>
            <td class="label">Shift</td>
            <td>
                {{ $shiftName }}
            </td>

            <td class="label">Printed Date</td>
            <td>
                {{ $printedAt }}
            </td>
        </tr>
    </table>

    <div class="comments-box">
@if($comment && trim($comment->comments ?? '') !== '')
{{ $comment->comments }}
@else
No nursing comments found for this member and shift.
@endif
    </div>

    <table class="footer-table">
        <tr>
            <td style="width: 50%;">
                <span class="signature-line">Nurse Signature</span>
            </td>

            <td style="width: 50%; text-align: right;">
                <span class="signature-line">Date</span>
            </td>
        </tr>
    </table>

</body>
</html>
