@extends('workattendance::layouts.master')

@section('maproute-icon')
    <i class="icofont icofont-wall-clock"></i>
@stop

@section('maproute-icon-mini')
    <i class="icofont icofont-wall-clock"></i>
@stop

@section('maproute-actual')
    {{ __('Gestión de Permisos') }}
@stop

@section('maproute-title')
    {{ __('Permisos') }}
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" id="helpWorkAttendancePermissions">
                <div class="card-header">
                    <h6 class="card-title">
                        {{ __('Permisos') }}
                        @include('buttons.help', [
                            'helpId' => 'helpWorkAttendancePermissions',
                            'helpSteps' => get_json_resource(
                                'ui-guides/settings/general_settings.json',
                                'workattendance'
                            ),
                        ])
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.new', [
                            'route' => route('workattendance.permissions.create')
                        ])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <workattendance-permission-list
                        route_list="{{ route('workattendance.permissions.list') }}"
                        route_edit="{{ url('work-attendance/permissions/{id}/edit') }}"
                    ></workattendance-permission-list>
                </div>
            </div>
        </div>
    </div>
@endsection
