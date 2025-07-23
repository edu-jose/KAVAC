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
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">{{ __('Agenda de Contactos') }}</h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.new', ['route' => route('citizenservice.contact-books.create')])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <citizenservice-contact-list
                        route_list="{{ url('citizenservice/contact-books/vue-list/all') }}"
                        route_edit="{{ url('citizenservice/contact-books/{id}/edit') }}"
                        route_delete="{{ url('citizenservice/contact-books') }}"
                    ></citizenservice-contact-list>
                </div>
            </div>
        </div>
    </div>
@endsection
