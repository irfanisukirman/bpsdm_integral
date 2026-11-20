<!DOCTYPE html>
<html lang="id" class="light-style customizer-hide" dir="ltr" @hasSection('integral_theme') data-integral-theme="@yield('integral_theme')" @endif>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'Login') | INTEGRAL</title>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/inte.png') }}" />
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />
    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-auth.css') }}" />
    @stack('styles')
    @if(auth()->guest() && !request()->routeIs('complaints.guest.*'))
        @include('complaints.widget-assets')
    @endif
    <link rel="stylesheet" href="{{ asset('assets/css/integral-page-loader.css') }}?v=20261118-logo-spin-large">
</head>
<body class="@yield('body_class')">
    @include('layouts.partials.page-loader')
    @if(auth()->guest() && !request()->routeIs('complaints.guest.*'))
        @include('complaints.widget')
    @endif
    <div class="container-xxl">
        @yield('content')
    </div>

    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
</body>
</html>
