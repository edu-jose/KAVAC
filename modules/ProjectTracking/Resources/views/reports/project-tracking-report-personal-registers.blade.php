@extends('projecttracking::layouts.master')

@section('maproute-icon')
    <i class="ion-ios-folder-outline"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion-ios-folder-outline"></i>
@stop

@section('maproute-actual')
    Seguimiento
@stop

@section('maproute-title')
    Reportes
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" style="overflow: visible">
                <div class="card-header">
                    <h6 class="card-title">Reporte de Trabajadores</h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <project-tracking-report-personal-register route_list="{{ url('/projecttracking/reports/vue-list') }}">
                    </project-tracking-report-personal-register>
                </div>
            </div>
        </div>
    </div>
@stop