@if (!isset($hide_clear) || !$hide_clear)
    {!! Form::button('<i class="fa fa-eraser"></i>', [
        'class' => 'btn btn-default btn-icon btn-round btn-eraser',
        'data-toggle' => 'tooltip', 'type' => 'reset',
        'title' => __('Borrar datos del formulario'),
    ]) !!}
@endif
@if (!isset($hide_previous) || !$hide_previous)
    {!! Form::button('<i class="fa fa-ban"></i>', [
        'class' => 'btn btn-warning btn-icon btn-round btn-cancel', 'data-toggle' => 'tooltip', 'type' => 'button',
        'title' => __('Cancelar y regresar'),
    ]) !!}
@endif
@if (!isset($hide_save) || !$hide_save)
    {!! Form::button('<i class="fa fa-save"></i>', [
        'class' => 'btn btn-success btn-icon btn-round', 'data-toggle' => 'tooltip',
        'title' => __('Guardar registro'), 'type' => 'submit'
    ]) !!}
@endif

@section('extra-js')
    @parent
    <script nonce="{{ session()->get('nonce') }}">
        $(document).ready(function() {
            $('.btn-cancel').on('click', function() {
                window.location.href='{{ url()->previous() }}';
            })
        });
    </script>
@endsection
