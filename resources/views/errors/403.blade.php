<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>403 | Access Denied</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="{{ asset('backend/assets/css/bootstrap.min.css') }}" rel="stylesheet">

    <style>
        body {
            background: #f8f9fa;
        }
        .error-box {
            max-width: 520px;
            margin: 10% auto;
            text-align: center;
            background: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0,0,0,.08);
        }
        .error-code {
            font-size: 72px;
            font-weight: 700;
            color: #dc3545;
        }
        .error-text {
            font-size: 18px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="error-box">
    <div class="error-code">403</div>
    <h4>Access Denied</h4>

    <p class="error-text">
        You do not have permission to access this page.
    </p>

    <div class="d-flex justify-content-center gap-2">
        <a href="{{ route('dashboard') }}"
            class="btn btn-outline-secondary"
            onclick="event.preventDefault(); if (window.history.length > 1) { window.history.back(); } else { window.location.href=this.href; }">
            Go Back
        </a>

        <a href="{{ route('dashboard') }}" class="btn btn-primary">
            Dashboard
        </a>
    </div>
</div>

</body>
</html>
