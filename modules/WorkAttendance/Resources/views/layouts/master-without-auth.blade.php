@extends('layouts.app-without-auth')

@section('modules-css')
    @parent
    <link rel="stylesheet" type="text/css" href="{{ asset('modules/workattendance/css/app.css') }}" media="screen" nonce="{{ session('nonce') }}">
@endsection

@section('modules-js')
    @parent
    <script src="{{ asset('modules/workattendance/js/app.js') }}" nonce="{{ session('nonce') }}"></script>
    <script nonce="{{ session()->get('nonce') }}">
        function fullScreen(elem) {
            var elem = (typeof(elem) !== "undefined") ? elem : document.documentElement;
            if (elem.requestFullscreen) {
                elem.requestFullscreen();
            } else if (elem.mozRequestFullScreen) { /* Firefox */
                elem.mozRequestFullScreen();
            } else if (elem.webkitRequestFullscreen) { /* Chrome, Safari and Opera */
                elem.webkitRequestFullscreen();
            } else if (elem.msRequestFullscreen) { /* IE/Edge */
                elem.msRequestFullscreen();
            }
        }
    </script>
@endsection
