@extends('workattendance::layouts.master')

@section('maproute-icon')
    <i class="icofont icofont-calendar"></i>
@stop

@section('maproute-icon-mini')
    <i class="icofont icofont-calendar"></i>
@stop

@section('maproute-actual')
    {{ __('Gestión de Horario Personalizado') }}
@stop

@section('maproute-title')
    {{ __('Horario Personalizado') }}
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" id="cardWorkAttendanceCustomScheduleForm">
                <div class="card-header">
                    <h6 class="card-title">Horario Personalizado
                        @include('buttons.help', [
                            'helpId' => 'WorkAttendanceCustomScheduleForm',
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
                <workattendance-custom-schedule-form
                    route_list="{{ route('workattendance.custom.schedule.list') }}"
                    route_back="{{ route('workattendance.custom.schedule.index') }}"
                    @if (isset($customSchedule))
                        :edit_data="{{ $customSchedule }}"
                    @endif
                ></workattendance-custom-schedule-form>
            </div>
        </div>
    </div>
@endsection
