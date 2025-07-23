@extends('budget::layouts.master')

@section('maproute-icon')
    <i class="ion-arrow-graph-up-right"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion-arrow-graph-up-right"></i>
@stop

@section('maproute-actual')
    {{ __('Presupuesto') }}
@stop

@section('maproute-title')
    {{ __('Acciones Centralizadas') }}
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">
                        {{ __('Acción Centralizada') }}
                        @include('buttons.help',[
                        'helpId' => 'institution',
                        'helpSteps' => get_json_resource('ui-guides/budget_centralized_action.json','budget')
                    ])
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                {!! (!isset($model)) ? Form::open($header) : Form::model($model, $header) !!}
                    <div class="card-body">
                        @include('layouts.form-errors')
                        <div class="row">
                            <!-- Institución -->
                            <div id="helpInstitution" class="col-3">
                                <div class="form-group is-required">
                                    {!! Form::label('institution_id', __('Institución'), ['class' => 'control-label']) !!}
                                    {!! Form::select('institution_id', $institutions, null, [
                                        'id' => 'institution_id',
                                        'class' => 'select2',
                                        'data-toggle' => 'tooltip',
                                        'title' => __('Seleccione una institución')
                                    ]) !!}
                                </div>
                            </div>
                            <!-- Fin de Institución -->
                            <!-- Departamento o Dependencia -->
                            <div id="helpDepartament" class="col-3">
                                <div class="form-group is-required">
                                    {!! Form::label('department_id', __('Dependencia'), ['class' => 'control-label']) !!}
                                    {!! Form::select('department_id', $departments, null, [
                                        'id' => 'department_id',
                                        'class' => 'select2',
                                        'data-toggle' => 'tooltip',
                                        'title' => __('Seleccione un departamento o dependencia'),
                                    ]) !!}
                                </div>
                            </div>
                            <!-- Fin de Departamento o Dependencia -->
                            @if (Module::has('Payroll') && Module::isEnabled('Payroll'))
                                <!-- Responsable -->
                                <div id="helpStaff" class="col-3">
                                    <div class="form-group is-required">
                                        {!! Form::label('payroll_staff_id', __('Responsable'), ['class' => 'control-label']) !!}
                                        {!! Form::select('payroll_staff_id', $staffs, null, [
                                            'id' => 'payroll_staff_id',
                                            'class' => 'select2', 'data-toggle' => 'tooltip',
                                            'title' => __('Seleccione una persona responsable del proyecto')
                                        ]) !!}
                                    </div>
                                </div>
                                <!-- Fin de Responsable -->
                                <!-- Cargo de Responsable -->
                                <div id="helpPosition" class="col-3">
                                    <div id="help_payroll_position_id" class="form-group is-required">
                                        {!! Form::label('payroll_position_id', __('Cargo de Responsable'), [
                                            'class' => 'control-label'
                                        ]) !!}
                                        {!! Form::select('payroll_position_id', $positions, null, [
                                            'id' => 'payroll_position_id',
                                            'class' => 'select2', 'data-toggle' => 'tooltip',
                                            'title' => __('Seleccione el cargo de la persona responsable del proyecto')
                                        ]) !!}
                                    </div>
                                </div>
                                <!-- Fin de Cargo de Responsable -->
                            @endif
                        </div>
                        <div class="row">
                            <div class="col-3">
                                <div id="helpStartDateResponsible" class="form-group is-required" style="display: none;">
                                    {!! Form::label('start_date_responsible', __('Fecha de inicio del Responsable'), ['class' => 'control-label']) !!}
                                    {!! Form::date('start_date_responsible', (isset($model))?$model->start_date_responsible:date('Y-m-d'), [
                                        'class' => 'form-control input-sm',
                                        'data-toggle' => 'tooltip',
                                        'placeholder' => 'dd/mm/YYYY',
                                        'title' => __('Fecha de inicio del Responsable')
                                    ]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <!-- Código -->
                            <div class="col-3">
                                <div id="code" class="form-group is-required">
                                    <div class="form-group is-required">
                                        {!! Form::label('code', __('Código de la acción centralizada'), ['class' => 'control-label']) !!}
                                        {!! Form::text('code', old('code'), [
                                            'class' => 'form-control input-sm',
                                            'data-toggle' => 'tooltip',
                                            'placeholder' => __('Código de la acción centralizada'),
                                            'title' => __('Código que identifica la acción centralizada')
                                        ]) !!}
                                    </div>
                                </div>
                            </div>
                            <!-- Fin de Código -->
                            <!-- Nombre de la acción centralizada -->
                            <div class="col-9">
                                <div class="form-group is-required">
                                    {!! Form::label('name', __('Nombre de la acción centralizada'), ['class' => 'control-label']) !!}
                                    {!! Form::text('name', old('name'), [
                                        'class' => 'form-control input-sm',
                                        'data-toggle' => 'tooltip',
                                        'placeholder' => __('Nombre de la acción centralizada'),
                                        'title' => __('Nombre que identifica la acción centralizada')
                                    ]) !!}
                                </div>
                            </div>
                            <!-- Fin del Nombre de la acción centralizada -->
                        </div>
                        <div class="row">
                            <!-- Fecha de inicio -->
                            <div id="helpStartDate" class="col-3">
                                <div class="form-group is-required">
                                    {!! Form::label('from_date', __('Fecha de inicio'), ['class' => 'control-label']) !!}
                                    {!! Form::date('from_date', (isset($model))?$model->from_date:date('Y-m-d'), [
                                        'class' => 'form-control input-sm',
                                        'data-toggle' => 'tooltip',
                                        'placeholder' => 'dd/mm/YYYY',
                                        'title' => __('Fecha en la que inicia la acción centralizada')
                                    ]) !!}
                                </div>
                            </div>
                            <!-- Fin de la Fecha de inicio -->
                            <!-- Fecha de finalización -->
                            <div id="helpEndDate" class="col-3">
                                <div class="form-group">
                                    {!! Form::label('to_date', __('Fecha de finalización'), ['class' => 'control-label']) !!}
                                    {!! Form::date('to_date', (isset($model))?$model->to_date:old('to_date'), [
                                        'class' => 'form-control input-sm no-restrict',
                                        'data-toggle' => 'tooltip',
                                        'placeholder' => 'dd/mm/YYYY',
                                        'title' => __('Fecha en la que finaliza la acción centralizada')
                                    ]) !!}
                                </div>
                            </div>
                            <!-- Fin de la Fecha de finalización -->
                            <!-- Activo -->
                            <div id="helpStatus" class="col-6">
                                <div class="form-group">
                                    <label for="" class="control-label">{{ __('Activo') }}</label>
                                    <div class="custom-control custom-switch">
                                        {!! Form::checkbox('active', true, (isset($model))?$model->active:null, [
                                            'id' => 'active',
                                            'class' => 'custom-control-input'
                                        ]) !!}
                                        <label class="custom-control-label" for="active">&nbsp;</label>
                                    </div>
                                </div>
                            </div>
                            <!-- Fin de Activo -->
                        </div>
                        <div class="row">
                            <!-- Descripción -->
                            <div id="helpDescription" class="col-12">
                                <div class="form-group is-required">
                                    {!! Form::label('ca_description', __('Descripción'), ['class' => 'control-label']) !!}
                                    <ckeditor
                                        id="ca_description"
                                        class="form-control"
                                        name="ca_description"
                                        data-toggle="tooltip"
                                        placeholder="{!! __('Descripción de la acción centralizada') !!}"
                                        ref="ca_descriptionEditor"
                                        rows="4"
                                        tag-name="textarea"
                                        title="{!! __('Descripción de la acción centralizada') !!}"
                                        :config="ckeditor.editorConfig"
                                        :editor="ckeditor.editor"
                                        v-model="ckeditor.editorData">
                                    </ckeditor>
                                </div>
                            </div>
                            <!-- Fin de Descripción -->
                        </div>
                    </div>
                    <!-- Botones del formulario -->
                    <div class="card-footer text-right">
                        @if (!isset($hide_clear) || !$hide_clear)
                            {!! Form::button('<i class="fa fa-eraser"></i>', [
                                'id' => 'reset-select',
                                'class' => 'btn btn-default btn-icon btn-round',
                                'type' => 'reset',
                                'data-toggle' => 'tooltip',
                                'title' => __('Borrar datos del formulario'),
                            ]) !!}
                        @endif
                        @if (!isset($hide_previous) || !$hide_previous)
                            {!! Form::button('<i class="fa fa-ban"></i>', [
                                'class' => 'btn btn-warning btn-icon btn-round redirect-back',
                                'type' => 'button',
                                'data-toggle' => 'tooltip',
                                'title' => __('Cancelar y regresar')
                            ]) !!}
                        @endif
                        @if (!isset($hide_save) || !$hide_save)
                            {!! Form::button('<i class="fa fa-save"></i>', [
                                'class' => 'btn btn-success btn-icon btn-round',
                                'type' => 'submit',
                                'data-toggle' => 'tooltip',
                                'title' => __('Guardar registro')
                            ]) !!}
                        @endif
                    </div>
                    <!-- Fin de Botones del formulario -->
                {!! Form::close() !!}
            </div>
        </div>
    </div>
@stop

@section('extra-js')
    @parent
    <script nonce="{{ session()->get('nonce') }}">
        /**
         * Lógica de interacción entre elementos dropdowns (select) y un editor
         * CKEditor para actualizar dinámicamente los campos.
         */
        $(document).ready(function() {
            /**
             * Inicializar el contenido del editor CKEditor con datos del servidor.
             */
            app.ckeditor.editorData = "{!! (isset($model))?$model->ca_description:old('ca_description')  !!}";
            $('#institution_id').on('change', function() {
                updateSelectActive($(this), $("#department_id"),
                "Department", undefined, undefined,
                [$("#payroll_position_id"), $("#payroll_staff_id")] );
            });

            $('#department_id').on('change', function() {
                updateStaffSelect($(this), $("#payroll_staff_id"),
                "PayrollEmployment", "Payroll", "payrollStaff",
                [$("#payroll_position_id")]);
            });

            $('#payroll_staff_id').on('change', function() {
                updateSelectCustomPosition($(this), $("#payroll_position_id"),
                "PayrollEmployment", "Payroll", "");
            });

            let date = new Date().toISOString();

            let newDate = moment(String(date)).format('YYYY-MM-DD');

            if ($('#custom_date').val() == '') {
                $('#custom_date').val(newDate).change();
            }

            /**
             * Evento click para el botón de reset (#reset-select).
             */
            $("#reset-select").on('click', function() {
                $('#institution_id').val('').change();
                $('#department_id').val('').change();
                $('#payroll_staff_id').val('').change();
                $('#payroll_position_id').val('').change();
                app.ckeditor.editorData = "";
                $('#name').attr('disabled', false);
                $('#code').attr('disabled', false);
                $('#active').attr('disabled', false);
                $('#from_date').attr('disabled', false);
                $('#to_date').attr('disabled', false);
            });

            // Determina si el formulario está en modo crear o actualizar
            const isEditMode = $('#institution_id').val();

            if (isEditMode) {
                /* Hace visible el campo Fecha de inicio del Responsable cuando
                * el campo responsable cambia en el formulario de edición.
                */
                $('#payroll_staff_id').on('change', function() {
                    document.getElementById('helpStartDateResponsible').style.display = 'block';
                });
            }
        });

        /**
         * Lógica para bloquear campos cuando el año fiscal es superior
         * a la fecha fin del proyecto si el formulario está en modo update.
         */
        const institutionField = $('#institution_id'); // Campo Institución
        const codeField = $('#code'); // Campo código
        const nameField = $('#name'); // Campo nombre
        const fromDateField = $('#from_date'); // Campo fecha inicio
        const endDateField = $('#to_date'); // Campo Fecha de finalización
        const activeField = $('#active'); // Campo Activo
        const activeFiscalYear = "{{ $activeFiscalYear ?? '' }}"; // Año fiscal activo
        const ckeditorEditable = document.querySelector('.ck-editor__editable'); // Campo Descripción
        const ckeditorToolbar = document.querySelector('.ck-toolbar'); // Campo Descripción
        const fromDate = fromDateField.val(); // Obtener la fecha de inicio.
        const fromYear = fromDate.split("-")[0]; // Obtener el año de la fecha de inicio.

        // Entra si el formulario es update.
        if (activeFiscalYear) {
            // Si el año fiscal es igual al año de inicio del proyecto.
            if (activeFiscalYear === fromYear) {
                // Habilitar campos.
                institutionField.attr('disabled', false);
                codeField.attr('disabled', false);
                nameField.attr('disabled', false);
                fromDateField.attr('disabled', false);
                // endDateField.attr('disabled', false);
                activeField.attr('disabled', false);
            } else {
                // Función para comparar el año fiscal con el año de fin del proyecto.
                function checkEndDate() {
                    const endDate = endDateField.val(); // Obtener la fecha de finalización.
                    // Verificar si endDate está vacío, null o mayor que activeFiscalYear.
                    if ((!endDate || endDate.trim() === "")
                        || (activeFiscalYear &&
                        new Date(endDate).getFullYear() > activeFiscalYear)) {
                        // Deshabilitar campos.
                        institutionField.attr('disabled', true);
                        codeField.attr('disabled', true);
                        nameField.attr('disabled', true);
                        fromDateField.attr('disabled', true);
                        // endDateField.attr('disabled', true);
                        activeField.attr('disabled', true);

                        // Campo ckeditor.
                        if (ckeditorEditable) {
                            ckeditorEditable.style.backgroundColor = '#e9ecef';
                            ckeditorEditable.style.cursor = 'not-allowed';
                            ckeditorEditable.contentEditable = false;
                        }

                        // Barra de herramientas de ckeditor.
                        if (ckeditorToolbar) {
                            ckeditorToolbar.style.pointerEvents = 'none';
                            ckeditorToolbar.style.opacity = '0.5';
                        }
                    } else {
                        // Habilitar campos.
                        institutionField.attr('disabled', false);
                        codeField.attr('disabled', false);
                        nameField.attr('disabled', false);
                        fromDateField.attr('disabled', false);
                        // endDateField.attr('disabled', false);
                        activeField.attr('disabled', false);
                    }
                }

                // Ejecutar la función al cambiar la Fecha de finalización
                endDateField.on('change', function() {
                    checkEndDate();
                });

                // Ejecutar la función checkEndDate al cargar la página.
                checkEndDate();
            }
        }

        /**
         * Evento submit para el formulario.
         */
        $(document).on('submit', function() {
            $('#institution_id').attr('disabled', false);
            $('#department_id').attr('disabled', false);
            $('#payroll_staff_id').attr('disabled', false);
            $('#payroll_position_id').attr('disabled', false);
            $('#custom_date').attr('disabled', false);
            $('#name').attr('disabled', false);
            $('#code').attr('disabled', false);
            $('#active').attr('disabled', false);
            $('#from_date').attr('disabled', false);
            $('#to_date').attr('disabled', false);
        })
    </script>
@endsection
