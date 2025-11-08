@extends('layouts.app')

@section('maproute-icon')
    <i class="fa fa-user"></i>
@stop

@section('maproute-icon-mini')
    <i class="fa fa-user"></i>
@stop

@section('maproute-actual')
    {{ __('Usuarios') }}
@stop

@section('maproute-title')
    {{ __('Usuarios') }}
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h6 class="card-title">
            @if (isset($model))
                {{ __('Actualizar usuario') }}
            @else
                {{ __('Registrar usuario') }}
            @endif
            </h6>
            <div class="card-btns">
                @include('buttons.previous', ['route' => url()->previous()])
                @include('buttons.minimize')
            </div>
        </div>
        @if (!isset($model)) {!! Form::open($header) !!} @else {!! Form::model($model, $header) !!} @endif
            {!! Form::token() !!}
            <div class="card-body">
                @include('layouts.form-errors')
                <div class="row">
                    <div class="col-6">
                        <div class="form-group is-required">
                            {!! Form::label('institution_id', __('Institución'), []) !!}
                            {!! Form::select('institution_id', (isset($institutions))?$institutions:[], isset($model) && $model->profile ? $model->profile->institution_id : old('institution_id'), [
                                    'class' => 'form-control select2',
                                    'id' => 'institution_id',
                                    'disabled' => (isset($model) && $model->profile!==null && $model->profile->institution_id!==null) ? true : false
                                ]
                            ) !!}
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group" id="user">
                            {!! Form::label('employee', __('Empleado'), []) !!}

                            @php
                                $employee = old('employee_id');
                                $employees = [];
                                $employees = (isset($model))? $allPersons->toArray() : $allPersons->mapWithKeys(function ($person) {
                                    return [
                                        $person->employee_id => $person->full_name
                                    ];
                                })->toArray();
                                $employeeList = [
                                    '' => 'Seleccione...',
                                ];
                                $disabled = true;
                                if (isset($model) && $model->profile) {
                                    if ($model->profile->employee_id) {
                                        $employee = $model->profile->employee_id;
                                    }
                                    $disabled = false;
                                }
                            @endphp

                            {!! Form::select('employee_id', $employeeList+$employees, $employee, [
                                'class' => 'form-control select2',
                                'id' => 'employee',
                                'disabled' => $disabled,
                                'data-old' => old('employee_id')
                            ]) !!}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 employee_name">
                        <div class="form-group is-required" id="user_first_name">
                            {!! Form::label('first_name', __('Nombre'), ['id' => 'first_name_label']) !!}
                            {!! Form::text('first_name', (isset($model) && $model->profile!==null)?trim($model->profile->first_name . ' ' . $model->profile->last_name):old('first_name'), [
                                'class' => 'form-control input-sm', 'id' => 'first_name', 'data-toggle' => 'tooltip',
                                'title' => __('Indique el Nombre completo de la persona')
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group is-required" id="email">
                            {!! Form::label('email', __('Correo electrónico'), []) !!}
                            {!! Form::text('email', (isset($model))?$model->email:old('email'), [
                                'class' => 'form-control input-sm',
                                'data-toggle' => 'tooltip',
                                'title' => __('Indique el correo electrónico al cual envíar los datos de acceso')
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group is-required" id="user_name">
                            {!! Form::label('username', __('Usuario'), []) !!}
                            {!! Form::text('username', (isset($model))?$model->username:old('username'), [
                                'class' => 'form-control input-sm',
                                'data-toggle' => 'tooltip',
                                'title' => __('Indique el nombre de usuario')
                            ]) !!}
                        </div>
                    </div>
                </div>
                @include('auth.roles-permissions', ['user' => $model ?? null])
            </div>
            <div class="card-footer text-right" id="buttons">
                @include('buttons.form-display')
                @include('layouts.form-buttons')
            </div>
        {!! Form::close() !!}
    </div>
@endsection

@section('extra-js')
    @parent
    <script nonce="{{ session()->get('nonce') }}">
        $(document).ready(function() {
            $('#institution_id').on('input', function() {
                updateEmployeeSelect($(this), $("#employee"));
            });
            $('#employee').on('change', function() {
                hasEmployee();
            });
        })
        /**
         * Muestra u oculta el campo de nombre si no se ha seleccionado un empleado
         *
         * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        var hasEmployee = () => {
            $(".employee_name").show();
            if ($('#employee').val() !== "") {
                document.getElementById("first_name").value = '';
                $(".employee_name").hide();
            }
        }

        /**
         * Actualiza información de un select a partir de otro
         *
         * @param  {object}  parent_element Objeto con los datos del elemento que genera la acción
         * @param  {object}  target_element Objeto que se cargara con la información
         * @param  {string}  target_model   Modelo en el cual se va a realizar la consulta
         * @param  {string}  module_name    Nombre del módulo que ejecuta la acción
         */
        function updateEmployeeSelect(parent_element, target_element, edit) {
            var module_name = (typeof(module_name) !== "undefined") ? '/' + module_name : '';
            var parent_id = parent_element.val();
            var parent_name = parent_element.attr('id');

            target_element.empty();

            if (parent_id) {
                axios.get(
                    `/get-select-data-staff/${parent_name}/${parent_id}`
                ).then(response => {
                    if (response.data.result) {
                        target_element.attr('disabled', false);
                        target_element.empty().append('<option value="">{{ __('Seleccione...') }}</option>');
                        var filteredRecords = response.data.records.filter(record => record.employee_id !== null && record.user_id === null);
                        $.each(filteredRecords, function(index, record) {
                            target_element.append(
                                `<option value="${record['id']}">${record['first_name']} ${record['last_name']}</option>`
                            );
                            if (edit) {
                                let employee = document.getElementById('employee');
                                let employeeOld = employee.getAttribute('data-old');
                                let employeeValues = Object.values(employee);
                                for (let value of employeeValues) {
                                    if (record['id'] == employeeOld && employeeOld == value.value) {
                                        value.selected = true;
                                    }
                                }
                            }
                        });
                    }
                }).catch(error => {
                    logs('app', 244, error, 'updateSelect');
                })
            } else {
                target_element.attr('disabled', true);
            }
        }
        @if (old('employee_id'))
            const timeOpen = setTimeout(addInstitutionId, 3000);
            function addInstitutionId () {
                updateEmployeeSelect($('#institution_id'), $('#employee'), true);
            }
        @endif
    </script>
@endsection
