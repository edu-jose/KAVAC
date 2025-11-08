@extends('warehouse::layouts.master')

@section('maproute-icon')
    <i class="ion-ios-list-outline"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion-ios-list-outline"></i>
@stop

@section('maproute-actual')
    Almacén
@stop

@section('maproute-title')
    Gestión de Almacenes
@stop


@section('content')
    <div class="row">
        <div class="col-12">
            @if (!isset($staff) && !isset($externalRequest))
                <div class="card" id="cardWarehouseRequestForm">
                    <div class="card-header">
                        <h6 class="card-title text-uppercase">Solicitud de Almacén
                            @include('buttons.help', [
                                'helpId' => 'WarehouseRequestForm',
                                'helpSteps' => get_json_resource(
                                    'ui-guides/requests/request_form.json',
                                    'warehouse'),
                            ])
                        </h6>
                        </h6>
                        <div class="card-btns">
                            @include('buttons.previous', ['route' => url()->previous()])
                            @include('buttons.minimize')
                        </div>
                    </div>
                    <warehouserequest-create route_list="{{ url('warehouse/requests') }}"
                        :requestid="{!! isset($warehouse_request) ? $warehouse_request->id : 'null' !!}">
                    </warehouserequest-create>
                </div>
            @elseif (isset($externalRequest) && $externalRequest == 'true' && !isset($staff))
                <div class="card" id="cardWarehouseExternalRequestForm">
                    <div class="card-header">
                        <h6 class="card-title text-uppercase">Solicitud Externa de Almacén
                            @include('buttons.help', [
                                'helpId' => 'WarehouseExternalRequestForm',
                                'helpSteps' => get_json_resource(
                                    'ui-guides/requests/external_request_form.json',
                                    'warehouse'),
                            ])
                        </h6>
                        </h6>
                        <div class="card-btns">
                            @include('buttons.previous', ['route' => url()->previous()])
                            @include('buttons.minimize')
                        </div>
                    </div>
                    <warehouse-external-request-create route_list="{{ url('warehouse/external/requests') }}"
                        :requestid="{!! isset($warehouse_ext_req_id) ? $warehouse_ext_req_id : 'null' !!}">
                    </warehouse-external-request-create>
                </div>
            @else
                <div class="card" id="cardWarehouseRequestStaffForm">
                    <div class="card-header">
                        <h6 class="card-title text-uppercase">Solicitud de Almacén
                            @include('buttons.help', [
                                'helpId' => 'WarehouseRequestStaffForm',
                                'helpSteps' => get_json_resource(
                                    'ui-guides/requests/request_staff_form.json',
                                    'warehouse'),
                            ])
                        </h6>
                        </h6>
                        <div class="card-btns">
                            @include('buttons.previous', ['route' => url()->previous()])
                            @include('buttons.minimize')
                        </div>
                    </div>
                    <warehouse-request-staff-create route_list="{{ url('warehouse/requests') }}"
                        :requestid="{!! isset($warehouse_request) ? $warehouse_request->id : 'null' !!}">
                    </warehouse-request-staff-create>
                </div>
            @endif
        </div>
    </div>
@stop
