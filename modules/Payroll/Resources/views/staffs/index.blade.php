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
    Personal
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Datos Personales</h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.new', ['route' => route('payroll.staffs.create')])
                        @permission('payroll.staffs.import')
                        {!! Form::button('<i class="fa fa-upload"></i>', [
                            'class'       => 'btn btn-sm btn-primary btn-custom',
                            'data-toggle' => 'tooltip',
                            'type'        => 'button',
                            'title'       => __('Importar registros'),
                            'onclick'     => "$('input[name=importFile]').click()"
                        ]) !!}
                        <input
                            id="importFile" name="importFile"
                            type="file"
                            style="display:none"
                            onchange="importData()"
                        >
						@endpermission
                        @permission('payroll.staffs.export')
                        {!! Form::button('<i class="fa fa-download"></i>', [
                            'class'       => 'btn btn-sm btn-primary btn-custom',
                            'data-toggle' => 'tooltip',
                            'type'        => 'button',
                            'title'       => "Presione para descargar el documento con la información de los registros.",
                            'onclick'     => "exportData()"
                        ]) !!}
						@endpermission
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <payroll-staffs-list
                        route_list="{{ url('payroll/staffs/show/vue-list') }}"
                        route_delete="{{ url('payroll/staffs') }}"
                        route_edit="{{ url('payroll/staffs/{id}/edit') }}"
                        route_show="{{ url('payroll/staffs/{id}') }}"
                    >
                    </payroll-staffs-list>
                </div>
            </div>
        </div>
    </div>
@stop

@section('extra-js')
    <script type="text/javascript">
        var records;
        function exportData() {
            location.href = `${window.app_url}/payroll/registers/export/staffs/all`;
        }
        function importData() {
            var url = `${window.app_url}/payroll/registers/import/staffs/all`;
            var formData = new FormData();
            var importFile = document.querySelector('#importFile');
            formData.append("file", importFile.files[0]);
            axios.post(url, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }).then(response => {
                var texterror = 'Su solicitud esta en proceso, esto puede tardar unos minutos. Se le notificara al terminar la operación';
                if (
                    typeof response.data.errors !== 'undefined'
                    && response.data.errors.length > 0
                ) {
                    texterror = "Registros almacenados con exito, se encontraron "
                        + response.data.errors.length
                        + "errores, por favor revise la consola del navegador y/o correo enviado con los errores correspondientes";
                }
                $.gritter.add({
                    title: 'Exito!',
                    text: texterror,
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
        }

        function openmodal($staff) {
            axios.get("{{ url('payroll/staffs') }}/" + $staff).then(response => {
                records = response.data.record;
                $(".modal-body #code").val( records.code );
                $(".modal-body #first_name").val( records.first_name );
                $(".modal-body #last_name").val( records.last_name );
                $(".modal-body #nationality").val( records.nationality );
                $(".modal-body #id_number").val( records.id_number );
                $(".modal-body #passport").val( records.passport );
                $(".modal-body #email").val( records.email );
                $(".modal-body #birthdate").val( records.birthdate );
                $(".modal-body #gender").val( records.gender );
                $(".modal-body #emergency_contact").val( records.emergency_contact );
                $(".modal-body #emergency_phone").val( records.emergency_phone );
                $(".modal-body #country").val( records.country );
                $(".modal-body #estate").val( records.estate );
                $(".modal-body #municipality").val( records.municipality );
                $(".modal-body #parish").val( records.parish );
                $(".modal-body #address").val( records.address );
                $("#show_staff").modal("show");
            })
        }
    </script>
@stop
