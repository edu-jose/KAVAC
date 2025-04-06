@extends('workattendance::layouts.master')

@section('maproute-icon')
    <i class="icofont icofont-history"></i>
@stop

@section('maproute-icon-mini')
    <i class="icofont icofont-history"></i>
@stop

@section('maproute-actual')
    {{ __('Gestión de Asistencia') }}
@stop

@section('maproute-title')
    {{ __('Histórico por Unidad / Dependencia') }}
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" id="helpWorkAttendanceHistoryByDepartment">
                <div class="card-header">
                    <h6 class="card-title">
                        {{ __('Histórico de Asistencia por Unidad / Dependencia') }}
                        @include('buttons.help', [
                            'helpId' => 'helpWorkAttendanceHistoryByDepartment',
                            'helpSteps' => get_json_resource(
                                'ui-guides/settings/general_settings.json',
                                'workattendance'
                            ),
                        ])
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        <span id="print"></span>
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <workattendance-history-by-department
                        route_list="{{ route('workattendance.history.by-department.list') }}"
                    ></workattendance-history-by-department>
                </div>
            </div>
        </div>
    </div>
@endsection
