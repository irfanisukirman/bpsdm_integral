<!doctype html>
<html lang="id" data-integral-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | Integral</title>
    <script src="{{ asset('assets/js/integral-theme.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/integral-theme.css') }}">
    @include('complaints.widget-assets')
</head>
<body class="complaint-embedded">
    @yield('content')
</body>
</html>
