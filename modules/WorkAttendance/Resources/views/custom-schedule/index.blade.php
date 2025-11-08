@extends('workattendance::layouts.master')

@section('maproute-icon')
    <i class="icofont icofont-calendar"></i>
@stop

@section('maproute-icon-mini')
    <i class="icofont icofont-calendar"></i>
@stop

@section('maproute-actual')
    {{ __('Gestión de Asistencia') }}
@stop

@section('maproute-title')
    {{ __('Horarios Personalizados') }}
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" id="helpWorkAttendanceCustomSchedule">
                <div class="card-header">
                    <h6 class="card-title">
                        {{ __('Horarios Personalizados') }}
                        @include('buttons.help', [
                            'helpId' => 'helpWorkAttendanceCustomSchedule',
                            'helpSteps' => get_json_resource(
                                'ui-guides/settings/general_settings.json',
                                'workattendance'
                            ),
                        ])
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.new', [
                            'route' => route('workattendance.custom.schedule.create')
                        ])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <workattendance-custom-schedule-list
                        route_list="{{ route('workattendance.custom.schedule.list') }}"
                        route_edit="{{ url('work-attendance/custom-schedules/{id}/edit') }}"
                    ></workattendance-custom-schedule-list>
                </div>
            </div>
        </div>
    </div>
@endsection
