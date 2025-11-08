@extends('workattendance::layouts.master')

@section('maproute-icon')
    <i class="icofont icofont-wall-clock"></i>
@stop

@section('maproute-icon-mini')
    <i class="icofont icofont-wall-clock"></i>
@stop

@section('maproute-actual')
    {{ __('Gestión de Asistencia') }}
@stop

@section('maproute-title')
    {{ __('Permisos') }}
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" id="cardWorkAttendancePermissionForm">
                <div class="card-header">
                    <h6 class="card-title">Solicitud de permiso
                        @include('buttons.help', [
                            'helpId' => 'WorkAttendancePermissionForm',
                            'helpSteps' => get_json_resource(
                                'ui-guides/registers/register_form.json',
                                'workattendance'),
                        ])
                    </h6>

                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                <workattendance-permission-form
                    route_list="{{ route('workattendance.permissions.list') }}"
                    route_back="{{ route('workattendance.permissions.index') }}"
                    @if (isset($requestPermission))
                        :edit_data="{{ $requestPermission }}"
                    @endif
                ></workattendance-permission-form>
            </div>
        </div>
    </div>
@endsection
