@extends('layouts.app')

@section('custom-page')
    <div class="notfoundpanel">
        <h1>403!</h1>
        <h3>{{ __('¡Prohibido!') }}</h3>
        <p>{{ __('Se ha denegado el acceso ya que no cuenta con los permisos necesarios para acceder a este recurso.') }}</p>
        <button type="button" class="btn btn-sm bt-primary btn-back">
            {{ __('Regresar') }}
        </button>
    </div>
@stop
