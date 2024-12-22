@if (isset($route))
    <a href="{{ $route }}" class="btn btn-sm btn-primary btn-custom" data-toggle="tooltip"
       title="{{ __('Imprimir registro') }}" target="_blank">
        <i class="fa fa-print"></i>
    </a>
@else
    {!! Form::button('<i class="fa fa-print"></i>', [
        'class' => 'btn btn-sm btn-primary btn-custom btn-print-general',
        'data-toggle' => 'tooltip', 'type' => 'button',
        'title' => __('Imprimir registro')
    ]) !!}
@endif

@section('extra-js')
    @parent
    <script nonce="{{ session()->get('nonce') }}">
        $(document).ready(function() {
            $('.btn-print-general').on('click', function() {
                {{ (isset($print['action']))
                ? "print({$print['action']})"
                : ((isset($print['function']))?$print['function']:'print()') }}
            })
        });
    </script>
@endsection