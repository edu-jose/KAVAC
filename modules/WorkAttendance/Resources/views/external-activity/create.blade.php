@extends('workattendance::layouts.master')

@section('maproute-icon')
    <i class="icofont icofont-history"></i>
@stop

@section('maproute-icon-mini')
    <i class="icofont icofont-history"></i>
@stop

@section('maproute-actual')
    {{ __('Gestión de Asistencia a Actividades Externas') }}
@stop

@section('maproute-title')
    {{ __('Asistencia a Actividades Externas') }}
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" id="cardWorkAttendanceExternalActivityForm">
                <div class="card-header">
                    <h6 class="card-title">Actividades Externas
                        @include('buttons.help', [
                            'helpId' => 'WorkAttendanceExternalActivityForm',
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
                <workattendance-external-activity-form
                    route_list="{{ route('workattendance.external.activity.list') }}"
                    route_back="{{ route('workattendance.external.activity.index') }}"
                    @if (isset($externalActivity))
                        :edit_data="{{ $externalActivity }}"
                    @endif
                ></workattendance-external-activity-form>
            </div>
        </div>
    </div>
@endsection

@section('extra-css')
    <style nonce="{{ session('nonce') }}">
        .multiselect {
            margin: 0 auto !important;
        }
        .multiselect, .multiselect__input, .multiselect__single {
            font-size: 12px;
        }
        .multiselect__tags {
            font-size: 12px;
            padding: 4px 40px 0 8px;
            min-height: 20px;
        }
        .multiselect__placeholder {
            margin-bottom: 3px;
            font-size: 12px;
        }
        .multiselect__select {
            height: 28px;
        }
        .form-multiselect.is-required:after {
            bottom: 16.75px;
        }
    </style>
@endsection
