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
Vacaciones
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title">Solicitudes de vacaciones</h6>
                <div class="card-btns">
                    @include('buttons.previous', ['route' => url()->previous()])
                    @include('buttons.new', ['route' => route('payroll.vacation-requests.create')])
                    <!-- @permission('payroll.professionals.import') -->
                    {!! Form::button('<i class="fa fa-upload"></i>', [
                    'class' => 'btn btn-sm btn-primary btn-custom',
                    'data-toggle' => 'tooltip',
                    'type' => 'button',
                    'title' => __('Importar registros'),
                    'onclick' => "$('input[name=importFile]').click()"
                    ]) !!}
                    <input id="importFile" name="importFile" type="file" style="display:none" onchange="importData()">
                    <!-- @endpermission -->
                    <!-- @permission('payroll.professionals.export') -->
                    {!! Form::button('<i class="fa fa-download"></i>', [
                    'class' => 'btn btn-sm btn-primary btn-custom',
                    'data-toggle' => 'tooltip',
                    'type' => 'button',
                    'title' => "Presione para descargar el documento con la información de los registros.",
                    'onclick' => "exportData()"
                    ]) !!}
                    <!-- @endpermission -->
                    @include('buttons.minimize')
                </div>
            </div>
            <div class="card-body">
                <payroll-vacation-request-list route_list="{{ url('payroll/vacation-requests/vue-list') }}" route_show="{{ url('payroll/vacation-requests/show/{id}') }}" route_edit="{{ url('payroll/vacation-requests/edit/{id}') }}" route_update="{{ url('payroll/vacation-requests') }}" route_delete="{{ url('payroll/vacation-requests') }}">
                </payroll-vacation-request-list>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
    <div class="card">
            <div class="card-header">
                <h6 class="card-title">Solicitudes de suspensión de vacaciones</h6>
                </div>
                            <div class="card-body">
                <payroll-vacation-request-pending-list route_list="{{ url('payroll/suspension-vacation-requests/vue-list') }}" route_show="{{ url('payroll/vacation-requests/show/{id}') }}" route_edit="{{ url('payroll/vacation-requests/edit/{id}') }}" route_update="{{ url('payroll/vacation-requests') }}" route_delete="{{ url('payroll/vacation-requests') }}">
                </payroll-vacation-request-pending-list>
            </div>
</div>

    </div>
</div>
@stop
@section('extra-js')
<script type="text/javascript">
    function exportData() {
        axios.get(`${window.app_url}/payroll/vacations-request/export`).then(response => {
            var text = 'Su solicitud esta en proceso, esto puede tardar unos minutos. Se le notificara al terminar la operación';
            $.gritter.add({
                title: 'Exito!',
                text: text,
                class_name: 'growl-primary',
                image: "/images/screen-ok.png",
                sticky: false,
                time: 3500
            })
        }).catch(error => {
            console.log('failure');
        });
    }

    function importData() {
        var url = `${window.app_url}/payroll/vacations-request/import`;
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