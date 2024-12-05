@extends('budget::layouts.master')

@section('maproute-icon')
    <i class="ion-arrow-graph-up-right"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion-arrow-graph-up-right"></i>
@stop

@section('maproute-actual')
    Presupuesto
@stop

@section('maproute-title')
    Reporte Consolidado
@stop
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" id="budgetAnalyticalMajor" style="overflow: visible">
                <div class="card-header">
                    <h6 class="card-title">
                        Consolidado
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                <budget-consolidated
                    url="{{ route('budget.report.consolidated') }}"
                    message="{{ $errorMessage }}"
                />
            </div>
        </div>
    </div>
@stop
