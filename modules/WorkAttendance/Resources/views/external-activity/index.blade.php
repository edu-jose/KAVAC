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
    {{ __('Actividades Externas') }}
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" id="helpWorkAttendanceExternalActivity">
                <div class="card-header">
                    <h6 class="card-title">
                        {{ __('Asistencia a Actividades Externas') }}
                        @include('buttons.help', [
                            'helpId' => 'helpWorkAttendanceExternalActivity',
                            'helpSteps' => get_json_resource(
                                'ui-guides/settings/general_settings.json',
                                'workattendance'
                            ),
                        ])
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.new', [
                            'route' => route('workattendance.external.activity.create')
                        ])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <workattendance-external-activity-list
                        route_list="{{ route('workattendance.external.activity.list') }}"
                        route_edit="{{ url('work-attendance/external-activities/{id}/edit') }}"
                    ></workattendance-external-activity-list>
                </div>
            </div>
        </div>
    </div>
@endsection
