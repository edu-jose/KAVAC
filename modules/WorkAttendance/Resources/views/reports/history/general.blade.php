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
    {{ __('Histórico de Asistencia') }}
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" id="helpWorkAttendanceHistory">
                <div class="card-header">
                    <h6 class="card-title">
                        {{ __('Histórico de Asistencia') }}
                        @include('buttons.help', [
                            'helpId' => 'helpWorkAttendanceHistory',
                            'helpSteps' => get_json_resource(
                                'ui-guides/settings/general_settings.json',
                                'workattendance'
                            ),
                        ])
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @if ($canRegister)
                            <button
                                type="button"
                                class="btn btn-sm btn-primary btn-custom btn-new"
                                title="Crear nuevo registro"
                                data-toggle="modal"
                                data-target="#work_attendance_new"
                                v-has-tooltip
                            >
                                <i class="fa fa-plus-circle"></i>
                            </button>
                        @endif
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <workattendance-history
                        route_list="{{ route('workattendance.history.list') }}"
                        can_update="{{ $canUpdate ? true : false }}"
                    ></workattendance-history>
                    <workattendance-history-new
                        can_new="{{ $canRegister ? true : false }}"
                    ></workattendance-history-new>
                </div>
            </div>
        </div>
    </div>
@endsection
