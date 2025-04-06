@extends('payroll::layouts.master')

@section('maproute-icon')
    <i class="ion-settings"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion-settings"></i>
@stop

@section('maproute-actual')
    Talento Humano
@stop

@section('maproute-title')
    Datos Socioeconómicos
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Datos Socioeconómicos</h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.new', ['route' => route('payroll.socioeconomics.create')])
                        @permission('payroll.socioeconomics.import')
                            {!! Form::button('<i class="fa fa-upload"></i>', [
                                'id'          => 'btnImport',
                                'class'       => 'btn btn-sm btn-primary btn-custom',
                                'data-toggle' => 'tooltip',
                                'type'        => 'button',
                                'title'       => __('Importar registros')
                            ]) !!}
                            <input
                                id="importFile" name="importFile"
                                type="file" style="display:none"
                            >
                        @endpermission
                        @permission('payroll.socioeconomics.export')
                            {!! Form::button('<i class="fa fa-download"></i>', [
                                'id'          => 'btnExport',
                                'class'       => 'btn btn-sm btn-primary btn-custom',
                                'data-toggle' => 'tooltip',
                                'type'        => 'button',
                                'title'       => "Presione para descargar el documento con la información de los registros."
                            ]) !!}
                        @endpermission
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <payroll-socioeconomic-list
                        route_list="{{ url('payroll/socioeconomics/show/vue-list') }}"
                        route_delete="{{ url('payroll/socioeconomics') }}"
                        route_edit="{{ url('payroll/socioeconomics/{id}/edit') }}"
                        route_show="{{ url('payroll/socioeconomics/{id}') }}"
                    ></payroll-socioeconomic-lit>
                </div>
            </div>
        </div>
    </div>
@stop
@section('extra-js')
    <script type="text/javascript" nonce="{{ session()->get('nonce') }}">
        $(document).ready(function() {
            const btnImport = document.querySelector('#btnImport');
            const importFile = document.querySelector('#importFile');
            const btnExport = document.querySelector('#btnExport');
            btnImport.addEventListener('click', function() {
                $('input[name=importFile]').click();
            });
            importFile.addEventListener('change', importData);
            btnExport.addEventListener('click', exportData);
        });

        var records;

        function exportData() {
            location.href = `${window.app_url}/payroll/registers/export/socioeconomics/all`;
        }

        function importData() {
            var url = `${window.app_url}/payroll/registers/import/socioeconomics/all`;
            var formData = new FormData();
            var importFile = document.querySelector('#importFile');
            formData.append("file", importFile.files[0]);
            axios.post(url, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }).then(response => {
                console.log(response.data);
                var texterror = 'Su solicitud esta en proceso, esto puede tardar unos minutos. Se le notificara al terminar la operación';
                if (typeof response.data.errors !== 'undefined' && response.data.errors.length > 0) {
                    texterror = "Registros almacenados con exito, se encontraron " + response.data.errors.length + "errores, por favor revise la consola del navegador y/o correo enviado con los errores correspondientes";
                }
                $.gritter.add({
                    title: 'Exito!',
                    text: texterror,
                    class_name: 'growl-primary',
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
