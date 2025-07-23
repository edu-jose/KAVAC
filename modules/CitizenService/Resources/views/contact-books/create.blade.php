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
    {{ __('Agenda de Contactos') }}
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" id="cardCitizenServiceContactForm">
                <div class="card-header">
                        <h6 class="card-title">
                            {{ __('Agenda de Contacto') }}
                            @include('buttons.help', [
                                'helpId' => 'CitizenServiceContactForm',
                                'helpSteps' => get_json_resource(
                                    'ui-guides/requests/contact_form.json',
                                    'citizenservice'
                                )
                            ])
                        </h6>

                        <div class="card-btns">
                            @include('buttons.previous', ['route' => url()->previous()])
                            @include('buttons.minimize')
                        </div>
                </div>
                <citizenservice-contact-form
                    route_list="{{ url('citizenservice/contact-books') }}"
                    @if (isset($contactBook))
                        initial_data="{{ json_encode($contactBook) }}"
                    @endif
                ></citizenservice-contact-form>
            </div>
        </div>
    </div>
@endsection
