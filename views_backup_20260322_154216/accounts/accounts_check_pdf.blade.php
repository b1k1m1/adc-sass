<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Print Check</title>
    <style>
        @page { margin: 0; }
        body { margin: 0; font-family: Arial, sans-serif; font-size: 12pt; }
        .check-layout { position: relative; width: 8.5in; height: 11in; }
        /* -- Adjust these as needed after testing -- */
        /* Top Check Section */
        .date           { position: absolute; left: 7.45in; top: 0.80in; font-size: 14px; }
        .payee          { position: absolute; left: 1.2in; top: 1.35in; font-size: 14px; }
        .amount-num     { position: absolute; left: 7.35in; top: 1.40in; font-size: 15px; }
        .amount-text    { position: absolute; left: 0.75in; top: 1.75in; font-size: 13px; width: 6.25in;}
        .desc           { position: absolute; left: 1.0in; top: 2.85in; font-size: 13px; width: 4in;}
        /* Stub 1 (middle section) */
        .stub1-date        { position: absolute; left: 6.5in; top: 3.80in; font-size: 12px; }
        .stub1-payee       { position: absolute; left: 1.4in; top: 4.50in; font-size: 13px; }
        .stub1-amount-num  { position: absolute; left: 6.5in; top: 4.55in; font-size: 13px; }
        .stub1-desc        { position: absolute; left: 1.4in; top: 5.00in; font-size: 12px; width: 4in;}
        /* Stub 2 (bottom section) */
        .stub2-date        { position: absolute; left: 6.5in; top: 7.75in; font-size: 12px; }
        .stub2-payee       { position: absolute; left: 1.4in; top: 8.25in; font-size: 13px; }
        .stub2-amount-num  { position: absolute; left: 6.5in; top: 8.30in; font-size: 13px; }
        .stub2-desc        { position: absolute; left: 1.4in; top: 9.60in; font-size: 12px; width: 4in;}
    </style>
</head>
<body>
<div class="check-layout">

    {{-- Top Check --}}
    <div class="date">{{ \Carbon\Carbon::parse($check->trans_dt)->format('m/d/Y') }}</div>
    <div class="payee">{{ $check->payto_name }}</div>
    <div class="amount-num">{{ number_format($check->debit_amt, 2) }}</div>
    <div class="amount-text">{{ amount_in_words($check->debit_amt) }} ONLY</div>
    <div class="desc">{{ $check->ref_no ?? '' }}</div>

    {{-- Stub 1 --}}
    <div class="stub1-date">{{ \Carbon\Carbon::parse($check->trans_dt)->format('m/d/Y') }}</div>
    <div class="stub1-payee">{{ $check->payto_name }}</div>
    <div class="stub1-amount-num">${{ number_format($check->debit_amt, 2) }}</div>
    <div class="stub1-desc">{{ $check->ref_no ?? '' }}</div>

    {{-- Stub 2 --}}
    <div class="stub2-date">{{ \Carbon\Carbon::parse($check->trans_dt)->format('m/d/Y') }}</div>
    <div class="stub2-payee">{{ $check->payto_name }}</div>
    <div class="stub2-amount-num">${{ number_format($check->debit_amt, 2) }}</div>
    <div class="stub2-desc">{{ $check->ref_no ?? '' }}</div>
</div>
</body>
</html>
