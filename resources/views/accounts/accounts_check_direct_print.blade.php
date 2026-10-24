<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">

    <title>Direct Print Check</title>

    <style>
        @page {
            size: Letter portrait;
            margin: 0;
        }

        html,
        body {
            width: 8.5in;
            height: 11in;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            overflow: hidden;
        }

        .check-layout {
            position: relative;
            width: 8.5in;
            height: 11in;
        }

        /* Top Check Section */
        .date {
            position: absolute;
            left: 7.30in;
            top: 0.65in;
            font-size: 14px;
        }

        .payee {
            position: absolute;
            left: 1.10in;
            top: 1.15in;
            font-size: 14px;
        }

        .amount-num {
            position: absolute;
            left: 7.25in;
            top: 1.20in;
            font-size: 15px;
        }

        .amount-text {
            position: absolute;
            left: 0.60in;
            top: 1.50in;
            width: 6.25in;
            font-size: 13px;
        }

        .desc {
            position: absolute;
            left: 0.65in;
            top: 2.50in;
            width: 4in;
            font-size: 13px;
        }

        /* Stub 1 */
        .stub1-date {
            position: absolute;
            left: 6.5in;
            top: 3.80in;
            font-size: 12px;
        }

        .stub1-payee {
            position: absolute;
            left: 1.4in;
            top: 4.50in;
            font-size: 13px;
        }

        .stub1-amount-num {
            position: absolute;
            left: 6.5in;
            top: 4.55in;
            font-size: 13px;
        }

        .stub1-desc {
            position: absolute;
            left: 1.4in;
            top: 5.00in;
            width: 4in;
            font-size: 12px;
        }

        /* Stub 2 */
        .stub2-date {
            position: absolute;
            left: 6.5in;
            top: 7.75in;
            font-size: 12px;
        }

        .stub2-payee {
            position: absolute;
            left: 1.4in;
            top: 8.25in;
            font-size: 13px;
        }

        .stub2-amount-num {
            position: absolute;
            left: 6.5in;
            top: 8.30in;
            font-size: 13px;
        }

        .stub2-desc {
            position: absolute;
            left: 1.4in;
            top: 9.60in;
            width: 4in;
            font-size: 12px;
        }

        @media print {
            html,
            body {
                width: 8.5in;
                height: 11in;
                margin: 0 !important;
                padding: 0 !important;
            }

            .check-layout {
                width: 8.5in;
                height: 11in;
            }
        }
    </style>
</head>

<body>

<div class="check-layout">

    {{-- Top Check --}}
    <div class="date">
        {{ \Carbon\Carbon::parse($check->trans_dt)->format('m/d/Y') }}
    </div>

    <div class="payee">
        {{ $check->payto_name }}
    </div>

    <div class="amount-num">
        {{ number_format((float) $check->debit_amt, 2) }}
    </div>

    <div class="amount-text">
        {{ amount_in_words($check->debit_amt) }} ONLY
    </div>

    <div class="desc">
        {{ $check->ref_no ?? '' }}
    </div>

    {{-- Stub 1 --}}
    <div class="stub1-date">
        {{ \Carbon\Carbon::parse($check->trans_dt)->format('m/d/Y') }}
    </div>

    <div class="stub1-payee">
        {{ $check->payto_name }}
    </div>

    <div class="stub1-amount-num">
        ${{ number_format((float) $check->debit_amt, 2) }}
    </div>

    <div class="stub1-desc">
        {{ $check->ref_no ?? '' }}
    </div>

    {{-- Stub 2 --}}
    <div class="stub2-date">
        {{ \Carbon\Carbon::parse($check->trans_dt)->format('m/d/Y') }}
    </div>

    <div class="stub2-payee">
        {{ $check->payto_name }}
    </div>

    <div class="stub2-amount-num">
        ${{ number_format((float) $check->debit_amt, 2) }}
    </div>

    <div class="stub2-desc">
        {{ $check->ref_no ?? '' }}
    </div>

</div>

<script>
    window.addEventListener('load', function () {
        window.print();
    });

    window.addEventListener('afterprint', function () {
        window.close();
    });
</script>

</body>
</html>
