@extends('layouts.app')

@section('modules-css')
    @parent
    <link rel="stylesheet" type="text/css" href="{{ asset('modules/workattendance/css/app.css') }}" media="screen" nonce="{{ session('nonce') }}">
@endsection

@section('modules-js')
    @parent
    <script src="{{ asset('modules/workattendance/js/app.js') }}" nonce="{{ session('nonce') }}"></script>
@endsection
