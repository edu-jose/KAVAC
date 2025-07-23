@extends('payroll::layouts.master')

@section('maproute-icon')
    <i class="ion-ios-folder-outline"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion-ios-folder-outline"></i>
@stop

@section('maproute-actual')
    Talento Humano
@stop

@section('maproute-title')
    Registro Embargos de sueldo
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Registros </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.new', ['route' => route('payroll.wage-garnishments.create')])
                        @permission('payroll.savingsfund.import')
                            {!! Form::button('<i class="fa fa-upload"></i>', [
                                'class' => 'btn btn-sm btn-primary btn-custom',
                                'data-toggle' => 'tooltip',
                                'type' => 'button',
                                'id' => 'importButton',
                                'title' => __('Importar registros'),
                            ]) !!}
                            <input id="importFile" name="importFile" type="file" style="display:none">
                        @endpermission
                        @permission('payroll.savingsfund.export')
                            {!! Form::button('<i class="fa fa-download"></i>', [
                                'class' => 'btn btn-sm btn-primary btn-custom',
                                'data-toggle' => 'tooltip',
                                'type' => 'button',
                                'id' => 'exportButton',
                                'title' => 'Presione para descargar el documento con la información de los registros.',
                            ]) !!}
                        @endpermission
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <payroll-garnishments-list route_list="{{ url('payroll/get-wage-garnishments') }}"
                        route_edit="{{ url('payroll/edit-wage-garnishments') }}"
                        route_delete="{{ url('payroll/delete-wage-garnishments') }}">
                    </payroll-garnishments-list>
                </div>
            </div>
        </div>
    </div>
@stop
@section('extra-js')
    <script type="text/javascript" nonce="{{ session()->get('nonce') }}">
        var records;

        $(document).ready(function() {
            $('#importButton').on('click', function() {
                $('input[name=importFile]').click();
            });
            $('#importFile').on('change', importData);
            $('#exportButton').on('click', function() {
                exportData();
            });
        });

        function exportData() {
            location.href = `${window.app_url}/payroll/wage-garnishments/export`;
        }

        function importData() {
            var url = `${window.app_url}/payroll/wage-garnishments/import`;
            var formData = new FormData();
            var importFile = document.querySelector('#importFile');
            formData.append("file", importFile.files[0]);
            axios.post(url, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }).then(response => {
                console.log(response.data);
                var texterror = 'Registro almacenado con exito';
                if (typeof response.data.errors !== 'undefined' && response.data.errors.length > 0) {
                    texterror = "Registros almacenados con exito, se encontraron " + response.data.errors.length +
                        "errores, por favor revise la consola del navegador y/o correo enviado con los errores correspondientes";
                }
                $.gritter.add({
                    title: 'Exito!',
                    text: texterror,
                    class_name: 'growl-success',
                    image: "/images/screen-ok.png",
                    sticky: false,
                    time: 3500
                });
            }).catch(error => {
                console.log('failure');
                $.gritter.add({
                    title: 'Advertencia!',
                    text: 'Error al importar el archivo',
                    class_name: 'growl-danger',
                    image: "{{ asset('images/screen-warning.png') }}",
                    sticky: false,
                    time: 2000
                });
                console.log(error);
            });
        }
    </script>
@stop
