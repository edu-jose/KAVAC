<div class="row">
	<div class="col-12 text-right">
		@if (isset($print))
			{!! Form::button('<i class="fa fa-print"></i>', [
				'class' => 'btn btn-sm btn-primary btn-custom btn-print', 'data-toggle' => 'tooltip', 'type' => 'button',
				'title' => __('Imprimir registro')
			]) !!}
		@endif
	</div>
</div>

@section('extra-js')
    @parent
    <script nonce="{{ session()->get('nonce') }}">
        $(document).ready(function() {
            $('.btn-print').on('click', function() {
                {{ (isset($print['action']))?$print['action']:'print()' }}
            })
        });
    </script>
@endsection
