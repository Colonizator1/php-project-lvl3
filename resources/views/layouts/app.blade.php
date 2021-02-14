<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title')</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="csrf-param" content="_token" />
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
        @stack('header_scripts')
    </head>
    <body>
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light nav-pills">
            <a class="nav-link {{ Template::setClassForActiveRoute(route('home')) }}" href="{{route('home')}}">Home</a>
            <a class="nav-link {{ Template::setClassForActiveRoute(route('domains.index')) }}" href="{{route('domains.index')}}">Domains</a>
        </nav>
        @if ($errors->any())
            <div>
                @foreach ($errors->all() as $error)
                    <p class="alert alert-danger" role="alert">{{ $error }}</p>
                @endforeach
            </div>
        @endif
        @include('flash::message');
        @yield('content')
        <footer class="footer bg-light">
            <div class="container">
                <p class="text-muted small mb-4 mb-lg-0">&copy; Page Analyzer 2021. All Rights Reserved.</p>
            </div>
        </footer>
        <script src="{{ asset('js/app.js') }}"></script>
    </body>
</html>
