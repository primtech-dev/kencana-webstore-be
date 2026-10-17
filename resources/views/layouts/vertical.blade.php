<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>{{ ($title ?? 'Admin') . ' | Kencana Store' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Primtech">

    <!-- App favicon -->
    <link rel="icon" type="image/x-icon" href="/images/favicon.ico?v=2">
    <link rel="icon" type="image/png" sizes="192x192" href="/images/favicon.png?v=2">
    <link rel="apple-touch-icon" href="/images/apple-touch-icon.png?v=2">

    @yield('styles')

    @include('layouts.shared/head-css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    <!-- Begin page -->
    <div class="wrapper">

        @include('layouts.shared/menu')

        <div class="content-page">
            <div class="container-fluid">

                @yield('content')
            </div>

            @include('layouts.shared/footer')

        </div>
    </div>

    @include('layouts.shared/customizer')

    <x-toast />

    <x-flash-message />

    @include('layouts.shared/footer-scripts')
</body>

</html>
