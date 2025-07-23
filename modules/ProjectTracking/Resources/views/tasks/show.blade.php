@extends('projecttracking::layouts.master')

@section('maproute-icon')
    <i class="ion-settings"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion-settings"></i>
@stop

@section('maproute-actual')
    Seguimiento
@stop

@section('maproute-title')
    Tareas
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" id="cardProjectTrackingTaskShow">
                <div class="card-header">
                    <h6 class="card-title">Información Detallada de la tarea
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                <project-tracking-task-show
                    :project_tracking_task="{{ $projectTrackingTask }}"
                    :user_id="{{ $user_id }}"
                ></project-tracking-task-show>
            </div>
        </div>
    </div>
@stop
