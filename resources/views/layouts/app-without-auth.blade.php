<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- CSRF Token --}}
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name') }} | {{ __('Sistema de Gestión Administrativa') }}</title>
        <link rel="shortcut icon" href="{{ asset('images/favicon.png', Request::secure()) }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('css/app.css') }}" media="screen" nonce="{{ session()->get('nonce') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('vendor/jquery.gritter/css/jquery.gritter.css') }}" media="screen" nonce="{{ session()->get('nonce') }}">
        @yield('modules-css')
        @yield('extra-css')
        <script nonce="{{ session()->get('nonce') }}">
            window.app_url = `{{ env('APP_URL') }}`;
        </script>
    </head>
    <body>
        <div class="d-flex flex-column justify-content-center">
            <div id="app" class="align-self-center col-12">
                @yield('content')
            </div>
        </div>
        @section('with-footer')
            <footer class="align-self-end">
                @include('layouts.footer')
            </footer>
        @endsection
        <script src="{{ asset('js/app.js') }}" nonce="{{ session()->get('nonce') }}"></script>
        <script src="{{ asset('vendor/jquery.gritter/js/jquery.gritter.min.js') }}" nonce="{{ session()->get('nonce') }}" defer></script>
        @yield('modules-js')
        @yield('extra-js')
        @include('layouts.messages')
    </body>
</html>
