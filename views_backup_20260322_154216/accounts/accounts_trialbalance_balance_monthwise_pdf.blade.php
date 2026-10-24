<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Trial Balance Monthwise (With Opening Balance)</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10pt;
        }

        h4, h6 {
            margin: 0;
            padding: 0;
        }

        .text-center { text-align: center; }
        .text-end    { text-align: right; }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            border: 0.5px solid #000;
            padding: 3px;
        }

        .table-sm th,
        .table-sm td {
            padding: 2px;
        }

        .table-bordered { border: 0.5px solid #000; }

        .table-secondary {
            background-color: #e0e0e0;
            font-weight: bold;
        }

        .table-dark {
            background-color: #b0b0b0;
            font-weight: bold;
        }

        .fw-bold {
            font-weight: bold;
        }
    </style>
</head>
<body>

    <h4 class="text-center">{{ $company->comp_nm ?? 'Company Name' }}</h4>
    <h6 class="text-center">
        Trial Balance (Monthwise - With Opening Balance)
        from {{ \Carbon\Carbon::parse($startDate)->format('m/d/Y') }}
        to {{ \Carbon\Carbon::parse($endDate)->format('m/d/Y') }}
    </h6>
    <hr>

    <table class="table table-bordered table-sm">
        @include('accounts.partials.trialbalance_balance_monthwise_table', [
            'months'        => $months,
            'groupedByType' => $groupedByType,
            'data'          => $data,
        ])
    </table>

</body>
</html>
