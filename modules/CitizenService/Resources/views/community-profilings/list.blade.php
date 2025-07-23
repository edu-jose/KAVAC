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
    {{ __('Caracterización de Comunidades') }}
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">{{ __('Caracterización de Comunidades') }}</h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.new', [
                            'route' => route('citizenservice.community-profilings.create')
                        ])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <citizenservice-community-profiling-list
                        route_list="{{ url('citizenservice/community-profilings/vue-list/all') }}"
                        route_edit="{{ url('citizenservice/community-profilings/{id}/edit') }}"
                        route_delete="{{ url('citizenservice/community-profilings') }}"
                    ></citizenservice-community-profiling-list>
                </div>
            </div>
        </div>
    </div>
@endsection
