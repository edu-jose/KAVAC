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
    Reporte de Modificaciones
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" id="budgetModifications">
                <div class="card-header">
                    <h6 class="card-title">
                        Modificaciones
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                <budget-modifications
                    url="{{ route('budget.report.budgetModifications') }}"
                    pdf="{{ route('budget.report.budgetModificationsPdf') }}"
                    budget-projects="{{ $budgetProjects }}"
                    budget-centralized-actions="{{ $budgetCentralizedActions }}"
                    document-statuses="{{ $documentStatuses }}"
                    modifications="{{ $modifications }}"
                />
            </div>
        </div>
    </div>
@stop
