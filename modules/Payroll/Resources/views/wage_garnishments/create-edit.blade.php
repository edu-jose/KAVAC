@extends('payroll::layouts.master')

@section('maproute-icon')
    <i class="ion-ios-folder-outline"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion-ios-folder-outline"></i>
@stop

@section('maproute-actual')
    Talento Humano
@stop

@section('maproute-title')
    Gestión de registro
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <payroll-garnishments-form route_list="{{ url('payroll/wage-garnishments') }}"
                :register="{{ $register ?? json_encode('') }}">
            </payroll-garnishments-form>
        </div>
    </div>
@stop
