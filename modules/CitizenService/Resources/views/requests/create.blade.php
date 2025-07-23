@extends('citizenservice::layouts.master')

@section('maproute-icon')
    <i class="icofont icofont-users-social"></i>
@stop

@section('maproute-icon-mini')
    <i class="icofont icofont-users-social"></i>
@stop

@section('maproute-actual')
    {{ __('Atención / Ciudadano') }}
@stop

@section('maproute-title')
    {{ __('Solicitud de Atención al Ciudadano') }}
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" id="cardCitizenServiceRequestForm">
                <div class="card-header">
                        <h6 class="card-title">
                            {{ __('Datos del Beneficiario/Solicitante') }}
                            @include('buttons.help', [
                                'helpId' => 'CitizenServiceRequestForm',
                                'helpSteps' => get_json_resource('ui-guides/requests/request_form.json', 'citizenservice')
                            ])
                        </h6>

                        <div class="card-btns">
                            @include('buttons.previous', ['route' => url()->previous()])
                            @include('buttons.minimize')
                        </div>
                </div>
                <citizenservice-request-create
                    route_list="{{ url('citizenservice/requests') }}"
                    is-payroll-active="{{ Module::has('Payroll') && Module::isEnabled('Payroll') }}"
                    :requestid="{!! (isset($request)) ? $request->id : 'null' !!}"
                    @if (isset($docattributes))
                        :initial-url="{{ json_encode($docattributes) }}"
                    @endif
                ></citizenservice-request-create>

            </div>
        </div>
    </div>
@stop

@section('modules-js')
    @parent
    {!! Html::script(mix('modules/payroll/js/app.js'), [], Request::secure()) !!}
@endsection
