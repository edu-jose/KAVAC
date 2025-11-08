@extends('sale::layouts.master')

@section('maproute-icon')
    <i class="ion-ios-list-outline"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion-ios-list-outline"></i>
@stop

@section('maproute-actual')
    Comercialización
@stop

@section('maproute-title')
    Gestión de Clientes
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Gestión de Clientes</h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.new', ['route' => route('sale.customer-management.create')])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <sale-customer-management-list 
                        route_list="{{ url('sale/customer-management/vue-list') }}"
                        route_edit="{{ url('sale/customer-management/{id}/edit') }}" 
                        route_delete="{{ url('sale/customer-management') }}"
                        route_show="{{ url('sale/customer-management/{id}') }}">
                    </sale-customer-management-list>
                </div>
            </div>
        </div>
    </div>
@stop