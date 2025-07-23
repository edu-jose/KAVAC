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
            <payroll-savings-form route_list="{{ url('payroll/savings-fund') }}"
                :register="{{ $register ?? json_encode('') }}">
            </payroll-savings-form>
        </div>
    </div>
@stop
