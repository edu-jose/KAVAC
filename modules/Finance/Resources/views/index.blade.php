@section('modules-js')
    @parent
    {!! Html::script(mix('modules/finance/js/app.js'), [], Request::secure()) !!}
@endsection

@permission('finance.dashboard')
@endpermission
