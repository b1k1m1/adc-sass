<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <link href="{{ asset('backend/assets/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('backend/assets/css/icons.min.css') }}" rel="stylesheet">
  <link href="{{ asset('backend/assets/css/custom.css') }}" rel="stylesheet">

  <style>
    html, body { height:100%; }
    body { margin:0; background:#fff; }
    /* embed spacing */
    .page-content { padding: 0 !important; }
    .container-fluid { padding: 10px !important; }
  </style>
</head>
<body>
  @yield('content')

  <script src="{{ asset('backend/assets/libs/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('backend/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
