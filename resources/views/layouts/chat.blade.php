<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Member Chat')</title>

    <!-- Bootstrap (reuse same version as dashboard) -->
    <link href="{{ asset('backend/assets/css/bootstrap.min.css') }}" rel="stylesheet">

    @stack('styles')
</head>

<body style="background:#f5f6fa;">

<div class="container-fluid p-2">
    @yield('content')
</div>

<script src="{{ asset('backend/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

@stack('scripts')
</body>
</html>
