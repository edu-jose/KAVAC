{!! Form::button('<i class="fa fa-trash-o"></i>', [
	'class' => 'btn btn-danger btn-xs btn-icon btn-action btn-delete',
	'data-toggle' => 'tooltip', 'type' => 'button',
	'title' => ((isset($disabled) && !$disabled) || !isset($disabled)) ? __('Eliminar registro') : __('El registro no puede ser eliminado'),
	'disabled' => $disabled ?? false
]) !!}

@section('extra-js')
    @parent
    <script nonce="{{ session()->get('nonce') }}">
        $(document).ready(function() {
            $('.btn-delete').on('click', function() {
                delete_record('{{ $route }}');
            });
            $('.datatable').on('draw.dt', function () {
                $('.btn-delete').on('click', function() {
                    if (!$('.modal').hasClass('show')) {
                        delete_record('{{ $route }}');
                    }
                });
            } );
        });
    </script>
@endsection
