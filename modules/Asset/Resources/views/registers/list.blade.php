@extends('asset::layouts.master')

@section('maproute-icon')
    <i class="ion-ios-pricetags-outline"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion-ios-pricetags-outline"></i>
@stop

@section('maproute-actual')
    Bienes
@stop

@section('maproute-title')
    Gestión de Bienes
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Bienes</h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.new', ['route' => route('asset.register.create')])
                        @permission('asset.import')
                            {!! Form::button('<i class="fa fa-upload"></i>', [
                                'id' => 'btnImport',
                                'class' => 'btn btn-sm btn-primary btn-custom',
                                'data-toggle' => 'tooltip',
                                'type' => 'button',
                                'title' => __('Importar registros')
                            ]) !!}
                            <input
                                id="importFileMueble" name="importFileMueble" type="file" style="display:none"
                            />
                            <input
                                id="importFileAuto" name="importFileAuto" type="file" style="display:none"
                            />
                            <input
                                id="importFileInmueble" name="importFileInmueble" type="file" style="display:none"
                            />
                            <input
                                id="importFileSemoviente" name="importFileSemoviente" type="file" style="display:none"
                            />
                        @endpermission
                        @permission('asset.export')
                            {!! Form::button('<i class="fa fa-download"></i>', [
                                'id' => 'btnExport',
                                'class' => 'btn btn-sm btn-primary btn-custom',
                                'data-toggle' => 'tooltip',
                                'type' => 'button',
                                'title' => __('Exportar registros')
                            ]) !!}
                        @endpermission
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    @permission('asset.import')
                        <section id="assetImportSection" class="with-border with-radius mb-4"
                            style="display:none;padding:15px;">
                            <div class="row">
                                <div class="col-md-12">
                                    {!! Form::button("<span aria-hidden='true'>×</span>", [
                                        'id' => 'btnImportType',
                                        'class' => 'close float-right',
                                        'type' => 'button'
                                    ]) !!}
                                    <h6 class="text-center"> Tipo de registro a importar</h6>
                                </div>
                            </div>
                            <div class="row">
                                <div class="mx-auto d-block" style="cursor: pointer">
                                    <button
                                        type="button" id="btnImportAuto"
                                        class="btn-simplex btn-simplex-md btn-simplex-primary"
                                        title="Carga de vehículos en registros de bienes" data-toggle="tooltip"
                                    >
                                        <i class="icofont icofont-file-excel ico-3x"></i>
                                        <span class="mt-2">Vehículo</span>
                                    </button>
                                </div>
                                <div class="mx-auto d-block" style="cursor: pointer">
                                    <button
                                        type="button" id="btnImportMueble"
                                        class="btn-simplex btn-simplex-md btn-simplex-primary"
                                        title="Carga de muebles en registros de bienes" data-toggle="tooltip"
                                    >
                                        <i class="icofont icofont-file-excel ico-3x"></i>
                                        <span class="mt-2">Mueble</span>
                                    </button>
                                </div>
                                <div class="mx-auto d-block" style="cursor: pointer">
                                    <button
                                        type="button" id="btnImportInmueble"
                                        class="btn-simplex btn-simplex-md btn-simplex-primary"
                                        title="Carga de inmuebles en registros de bienes" data-toggle="tooltip"
                                    >
                                        <i class="icofont icofont-file-excel ico-3x"></i>
                                        <span class="mt-2">Inmueble</span>
                                    </button>
                                </div>
                                <div class="mx-auto d-block" style="cursor: pointer">
                                    <button
                                        type="button" id="btnImportSemoviente"
                                        class="btn-simplex btn-simplex-md btn-simplex-primary"
                                        title="Carga de semovientes en registros de bienes" data-toggle="tooltip"
                                    >
                                        <i class="icofont icofont-file-excel ico-3x"></i>
                                        <span class="mt-2">Semoviente</span>
                                    </button>
                                </div>
                            </div>
                        </section>
                    @endpermission
                    @permission('asset.export')
                        <section id="assetExportSection" class="with-border with-radius mb-4"
                            style="display:none;padding:15px;">
                            <div class="row">
                                <div class="col-md-12">
                                    {!! Form::button("<span aria-hidden='true'>×</span>", [
                                        'id' => 'btnExportType',
                                        'class' => 'close float-right',
                                        'type' => 'button'
                                    ]) !!}
                                    <h6 class="text-center"> Tipo de registro a exportar</h6>
                                </div>
                            </div>
                            <div class="row">
                                <div class="mx-auto d-block" style="cursor: pointer">
                                    <button
                                        type="button" id="btnExportAuto"
                                        class="btn-simplex btn-simplex-md btn-simplex-primary"
                                        title="Descarga de vehículos en registros de bienes" data-toggle="tooltip"
                                    >
                                        <i class="icofont icofont-file-excel ico-3x"></i>
                                        <span class="mt-2">Vehículo</span>
                                    </button>
                                </div>
                                <div class="mx-auto d-block" style="cursor: pointer">
                                    <button
                                        type="button" id="btnExportMueble"
                                        class="btn-simplex btn-simplex-md btn-simplex-primary"
                                        title="Descarga de muebles en registros de bienes" data-toggle="tooltip"
                                    >
                                        <i class="icofont icofont-file-excel ico-3x"></i>
                                        <span class="mt-2">Mueble</span>
                                    </button>
                                </div>
                                <div class="mx-auto d-block" style="cursor: pointer">
                                    <button
                                        type="button" id="btnExportInmueble"
                                        class="btn-simplex btn-simplex-md btn-simplex-primary"
                                        title="Descarga de inmuebles en registros de bienes" data-toggle="tooltip"
                                    >
                                        <i class="icofont icofont-file-excel ico-3x"></i>
                                        <span class="mt-2">Inmueble</span>
                                    </button>
                                </div>
                                <div class="mx-auto d-block" style="cursor: pointer">
                                    <button
                                        type="button" id="btnExportSemoviente"
                                        class="btn-simplex btn-simplex-md btn-simplex-primary"
                                        title="Descarga de semovientes en registros de bienes" data-toggle="tooltip"
                                    >
                                        <i class="icofont icofont-file-excel ico-3x"></i>
                                        <span class="mt-2">Semoviente</span>
                                    </button>
                                </div>
                            </div>
                        </section>
                    @endpermission
                    <asset-list
                        route_list="{{ url('asset/registers/vue-list') }}"
                        route_edit="{{ url('asset/registers/edit/{id}') }}"
                        route_delete="{{ url('asset/registers/delete') }}"
                    ></asset-list>
                </div>
            </div>
        </div>
    </div>
@stop

@section('extra-js')
    @parent
    <script nonce="{{ session()->get('nonce') }}">
        $(document).ready(function() {
            const btnImport = document.querySelector('#btnImport');
            const importFileMueble = document.querySelector('#importFileMueble');
            const importFileAuto = document.querySelector('#importFileAuto');
            const importFileInmueble = document.querySelector('#importFileInmueble');
            const importFileSemoviente = document.querySelector('#importFileSemoviente');
            const btnExport = document.querySelector('#btnExport');
            const btnImportType = document.querySelector('#btnImportType');
            const btnImportAuto = document.querySelector('#btnImportAuto');
            const btnImportMueble = document.querySelector('#btnImportMueble');
            const btnImportInmueble = document.querySelector('#btnImportInmueble');
            const btnImportSemoviente = document.querySelector('#btnImportSemoviente');
            const btnExportType = document.querySelector('#btnExportType');
            const btnExportAuto = document.querySelector('#btnExportAuto');
            const btnExportMueble = document.querySelector('#btnExportMueble');
            const btnExportInmueble = document.querySelector('#btnExportInmueble');
            const btnExportSemoviente = document.querySelector('#btnExportSemoviente');

            btnImport.addEventListener('click', function() {
                toggleSection('assetImportSection', 'assetExportSection');
            });
            importFileMueble.addEventListener('change', function() {
                importData('mueble', 'importFileMueble');
            });
            importFileAuto.addEventListener('change', function() {
                importData('vehiculo', 'importFileAuto');
            });
            importFileInmueble.addEventListener('change', function() {
                importData('inmueble', 'importFileInmueble');
            });
            importFileSemoviente.addEventListener('change', function() {
                importData('semoviente', 'importFileSemoviente');
            });
            btnExport.addEventListener('click', function() {
                toggleSection('assetExportSection', 'assetImportSection');
            });
            btnImportType.addEventListener('click', function() {
                toggleSection('assetImportSection');
            });
            btnImportAuto.addEventListener('click', function() {
                $('input[name=importFileAuto]').click();
            });
            btnImportMueble.addEventListener('click', function() {
                $('input[name=importFileMueble]').click();
            });
            btnImportInmueble.addEventListener('click', function() {
                $('input[name=importFileInmueble]').click();
            });
            btnImportSemoviente.addEventListener('click', function() {
                $('input[name=importFileSemoviente]').click();
            });
            btnExportType.addEventListener('click', function() {
                toggleSection('assetExportSection');
            });
            btnExportAuto.addEventListener('click', function() {
                exportData('vehiculo');
            });
            btnExportMueble.addEventListener('click', function() {
                exportData('mueble');
            });
            btnExportInmueble.addEventListener('click', function() {
                exportData('inmueble');
            });
            btnExportSemoviente.addEventListener('click', function() {
                exportData('semoviente');
            });
        });

        function exportData(type) {
            location.href = `${window.app_url}/asset/registers/export/all?type=${type}`;
        };

        function importData(type, input) {
            var url = `${window.app_url}/asset/registers/import/all?type=${type}`;
            var formData = new FormData();
            var importFile = document.querySelector('#' + input);
            formData.append("file", importFile.files[0]);
            axios.post(url, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }).then(response => {
                $.gritter.add({
                    title: 'Exito!',
                    text: 'Su solicitud esta en proceso, esto puede tardar unos minutos. Se le notificara al terminar la operación',
                    class_name: 'growl-primary',
                    image: "/images/screen-ok.png",
                    sticky: false,
                    time: 3500
                });

                importFile.value = '';
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
        };

        function toggleSection($sectionName, $sectionNameOther) {
            var section = document.getElementById($sectionName);
            var sectionOther = document.getElementById($sectionNameOther);
            if (section.style.display === "none") {
                section.style.display = "block";
                sectionOther.style.display = "none";
            } else {
                section.style.display = "none";
            }
        }
    </script>
@endsection
