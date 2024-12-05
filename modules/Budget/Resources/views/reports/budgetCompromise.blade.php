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
    Reporte de Compromisos
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" id="budgetAvailability">
                <div class="card-header">
                    <h6 class="card-title">
                        Compromisos
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                <budget-compromise-report 
                url="{{ route('budget.report.formulated.data') }}"
                    pdf="{{ route('budget.report.compromise.pdf') }}"
                    xlsx="{{ route('budget.report.compromise.xlsx') }}"
                    specific-actions-url="{{ route('budget.specific_actions.list') }}"
                    budget-projects="{{ $budgetProjects }}"
                    budget-centralized-actions="{{ $budgetCentralizedActions }}"
                    :errors="{{json_encode($errors->all())}}">
                </budget-compromise-report>
                </budget-compromise-report>

            </div>
        </div>
    </div>
@stop
