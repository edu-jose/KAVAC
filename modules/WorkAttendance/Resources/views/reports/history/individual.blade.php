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
    {{ __('Histórico de Asistencia Individual') }}
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" id="helpWorkAttendanceIndividualHistory">
                <div class="card-header">
                    <h6 class="card-title">
                        {{ __('Histórico de Asistencia Individual') }}
                        @include('buttons.help', [
                            'helpId' => 'helpWorkAttendanceIndividualHistory',
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
                    <workattendance-history-individual
                        route_list="{{ route('workattendance.history.individual.list') }}"
                    ></workattendance-history-individual>
                </div>
            </div>
        </div>
    </div>
@endsection
