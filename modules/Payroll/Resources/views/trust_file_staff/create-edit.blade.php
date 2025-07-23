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
    Gestión de archivo de fideicomiso
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" style="overflow: visible;">
                <div class="card-header">
                    <h6 class="card-title">Generar archivo para agregar nuevos trabajadores a txt de fideicomiso </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                <payroll-trust-file-staff
                    :finance_bank_id="{!! isset($bankId) ? $bankId : null !!}"
                    route_list="{{ url('payroll/trust-file-staff') }}">
                </payroll-trust-file-staff>
            </div>
        </div>
    </div>
@stop
