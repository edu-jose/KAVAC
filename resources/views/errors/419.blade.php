@extends('layouts.app')

@section('custom-page')
    <div class="notfoundpanel">
        <h1>419!</h1>
        <h3>{{ __('La página ha expirado debido a inactividad.') }}</h3>
        <p>{{ __('Por favor, actualice y pruebe de nuevo.') }}</p>
        <button type="button" id="btn-419" class="btn btn-sm bt-primary">
        	{{ __('Actualizar') }}
        </button>
    </div>
@stop

@section('extra-js')
    @parent
    <script nonce="{{ session()->get('nonce') }}">
        $(document).ready(function() {
            $('#btn-419').on('click', function() {
                window.location.assign(window.location.href);
            });
        });
    </script>
@endsection
