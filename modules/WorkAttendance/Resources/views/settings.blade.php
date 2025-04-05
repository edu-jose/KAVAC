@extends('workattendance::layouts.master', ['setting_view' => true])

@section('maproute-icon')
    <i class="ion-settings"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion-settings"></i>
@stop

@section('maproute-actual')
    {{ __('Gestión de Asistencia') }}
@stop

@section('maproute-title')
    {{ __('Configuración') }}
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" id="helpSettingForm">
                <div class="card-header">
                    <h6 class="card-title">
                        {{ __('REGISTROS COMUNES') }}
                        @include('buttons.help', [
                            'helpId' => 'helpSettingForm',
                            'helpSteps' => get_json_resource(
                                'ui-guides/settings/general_settings.json',
                                'workattendance'
                            ),
                        ])
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <workattendance-setting-notification
                            route_list="{{ route('workattendance.setting.notify.list') }}"
                        ></workattendance-setting-notification>
                        <workattendance-schedule
                            route_list="{{ route('workattendance.settings.schedule.index') }}"
                        ></workattendance-schedule>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
