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
            <div class="card" id="cardSaleServicesForm">
                <div class="card-header">
                    <h6 class="card-title text-uppercase">Nueva Gestión de Clientes</h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                <sale-customer-management-create
                    route_list="{{ url('sale/customer-management/list') }}"
                    route_get="{{ url('sale/customer-management/{id}') }}"
                    route_save="{{ url('sale/customer-management/store') }}"
                    route_delete="{{ url('sale/customer-management/delete/{id}') }}"
                    :customerid="{{ $customer->id ?? 'null' }}"
                    :edit-index="{{ $customer->id ?? 'null' }}">
                </sale-customer-management-create>
            </div>
        </div>
    </div>
@stop




