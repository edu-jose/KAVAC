<template>
    <section id="payrollParametersFormComponent">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary" href="" title="Registros de parámetros"
            data-toggle="tooltip" @click="addRecord('add_payroll_parameter', 'payroll/parameters', $event)">
            <i class="icofont icofont-globe ico-3x"></i>
            <span>Parámetros<br>Globales</span>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" id="add_payroll_parameter">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-globe ico-3x"></i>
                            Parámetro Global de Nómina
                        </h6>
                    </div>
                    <div class="modal-body">
                        <!-- mensajes de error -->
                        <div class="alert alert-danger" v-if="errors.length > 0">
                            <div class="container">
                                <div class="alert-icon">
                                    <i class="now-ui-icons objects_support-17"></i>
                                </div>
                                <strong>Cuidado!</strong> Debe verificar los siguientes errores antes de continuar:
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"
                                    @click.prevent="errors = []">
                                    <span aria-hidden="true">
                                        <i class="now-ui-icons ui-1_simple-remove"></i>
                                    </span>
                                </button>
                                <ul>
                                    <li v-for="error in errors" :key="error">{{ error }}</li>
                                </ul>
                            </div>
                        </div>
                        <!-- ./mensajes de error -->

                        <!-- nombre, tipo de parámetro y descripción -->
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <!-- nombre -->
                                <div class="form-group is-required">
                                    <label for="name">Nombre</label>
                                    <input type="text" id="name" placeholder="Nombre" v-is-text
                                        @input="normalizeParameterText($event.target.value, 'name')"
                                        class="form-control input-sm" v-model="record.name" data-toggle="tooltip"
                                        title="Indique el nombre del parámetro (requerido)">
                                    <input id="id" type="hidden" name="id" v-model="record.id">
                                </div>
                                <!-- ./nombre -->

                                <!-- tipo de parámetro -->
                                <div class="form-group is-required">
                                    <label for="parameter_type">Tipo de parámetro</label>
                                    <select2 :options="parameter_types" @input="changeParameterType()"
                                        v-model="record.parameter_type"></select2>
                                </div>
                                <!-- ./tipo de parámetro -->
                            </div>

                            <!-- descripción -->
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="description">Descripción</label>
                                    <ckeditor id="description" class="form-control" data-toggle="tooltip"
                                        name="description" tag-name="textarea"
                                        title="Indique la descripción del parámetro" :config="ckeditor.editorConfig"
                                        :editor="ckeditor.editor" v-model="record.description"></ckeditor>
                                </div>
                            </div>
                            <!-- ./descripción -->
                        </div>
                        <!-- ./nombre, tipo de parámetro y descripción -->


                        <!-- valor global -->
                        <div v-if="record.parameter_type == 'global_value'" class="row">
                            <!-- valor -->
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label for="value">Valor:</label>
                                    <input id="value" class="form-control input-sm" type="text" data-toggle="tooltip"
                                        placeholder="Valor" @focus="selectText"
                                        title="Indique el valor del parámetro (requerido)" v-model="record.value"
                                        v-input-mask data-inputmask="
                                                'alias': 'numeric',
                                                'allowMinus': 'false'
                                           " />
                                </div>
                            </div>
                            <!-- ./valor -->

                            <!-- porcentaje -->
                            <div class="form-group col-md-6">
                                <label for="percentage">¿Porcentaje?</label>
                                <p-check class="d-block pretty p-switch p-fill p-bigger" color="success"
                                    off-color="text-gray" toggle data-toggle="tooltip"
                                    title="Indique si el valor indicado está expresado en porcentaje (requerido)"
                                    v-model="record.percentage">
                                    <label slot="off-label"></label>
                                </p-check>
                            </div>
                            <!-- ./porcentaje -->
                        </div>
                        <!-- ./valor global -->

                        <!-- variable procesada -->
                        <div v-show="record.parameter_type == 'processed_variable'" class="row">
                            <div class="col-md-6">
                                <!-- expediente, parámetro y registros -->
                                <div class="row">
                                    <div class="form-group col-6">
                                        <label for="worker_record">¿Expediente del Trabajador?</label>
                                        <p-radio class="d-block pretty p-switch p-fill p-bigger" color="success"
                                            off-color="text-gray" toggle data-toggle="tooltip"
                                            title="Indique si desea utilizar una variable del expediente del Trabajador"
                                            v-model="variable" value="worker_record">
                                            <label slot="off-label"></label>
                                        </p-radio>
                                    </div>

                                    <div class="form-group col-6">
                                        <label for="parameter">¿Parámetro?</label>
                                        <p-radio class="d-block pretty p-switch p-fill p-bigger" color="success"
                                            off-color="text-gray" toggle data-toggle="tooltip"
                                            title="Indique si desea utilizar un parámetro previamente registrado"
                                            v-model="variable" value="parameter">
                                            <label slot="off-label"></label>
                                        </p-radio>
                                    </div>
                                </div>
                                <!-- ./expediente, parámetro y registros -->

                                <!-- opciones -->
                                <div v-if="variable" class="form-group">
                                    <label for="register">Registro</label>
                                    <select2 :options="options" @input="getNameOption" v-model="variable_option">
                                    </select2>
                                </div>
                                <!-- ./opciones -->
                            </div>


                            <!-- fórmula -->
                            <div class="col-12 col-xl-6">
                                <!-- ./calculadora -->
                                <div class="form-group is-required" style="z-index: 0;" v-if="!useFunction">
                                    <label>Fórmula</label>
                                    <textarea type="text" id="formulaShow" style="font-size: 1rem; font-weight: bold;"
                                        class="form-control input-sm" data-toggle="tooltip" disabled
                                        title="Fórmula a aplicar para el concepto. Utilice la siguiente calculadora para establecer los parámetros de la fórmula"
                                        rows="3" v-model="record.formulaShow">
                                    </textarea>
                                </div>
                                <div class="formula-calculator">
                                    <formula-calculator formulaInput='formulaShow' :withDisplay="false"
                                        ref="formulaResults" />
                                </div>
                                <div class="form-group row mb-n1">
                                    <div class="col-12 col-md-8 col-md-6 text-center mx-auto">
                                        <button type="button" class="btn btn-info btn-sm btn-formula btn-function"
                                            data-toggle="tooltip" title="presione para mover a la izquierda"
                                            @click="highlightPosition(false)"
                                            :style="{ opacity: useFunction ? 0.5 : 1 }">
                                            <i class="fa fa-long-arrow-left"></i>
                                        </button>
                                        <button type="button" class="btn btn-info btn-sm btn-formula btn-function"
                                            data-toggle="tooltip" title="presione para mover a la derecha"
                                            @click="highlightPosition(true)"
                                            :style="{ opacity: useFunction ? 0.5 : 1 }">
                                            <i class="fa fa-long-arrow-right"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group row" v-if="variable_option">
                                    <div class="col-12 col-md-8 text-center mx-auto">
                                        <button type="button" class="btn btn-info btn-sm btn-formula btn-variable"
                                            data-toggle="tooltip" title="Variable a usar cuando se realice el cálculo"
                                            @click="setVariable()">
                                            {{ updateNameVariable }}
                                        </button>
                                    </div>
                                </div>
                                <!-- ./calculadora -->
                            </div>
                            <!-- ./fórmula -->
                        </div>
                        <!-- ./variable procesada -->

                        <!-- variable reiniciable a cero -->
                        <div v-show="record.parameter_type == 'time_parameter'" class="row">
                            <!-- código -->
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label for="code">Código</label>
                                    <input type="text" id="name" placeholder="Código" class="form-control input-sm"
                                        v-model="record.code" data-toggle="tooltip"
                                        title="Indique el código del parámetro (requerido)" v-input-mask
                                        data-inputmask-regex="[0-9\s]*$">
                                </div>
                            </div>
                            <!-- ./código -->

                            <!-- activo -->
                            <div class="form-group col-md-3">
                                <label for="percentage">¿Activo?</label>
                                <p-check class="d-block pretty p-switch p-fill p-bigger" color="success"
                                    off-color="text-gray" toggle data-toggle="tooltip"
                                    title="Indique si el parámetro está activo (requerido)" v-model="record.active">
                                    <label slot="off-label"></label>
                                </p-check>
                            </div>
                            <!-- ./activo -->

                            <!-- listar en esquemas -->
                            <div class="form-group col-md-3">
                                <label for="percentage">¿Listar en esquemas?</label>
                                <p-check class="d-block pretty p-switch p-fill p-bigger" color="success"
                                    off-color="text-gray" toggle data-toggle="tooltip"
                                    title="Indique si el parámetro debe listarse en esquema de guardias (requerido)"
                                    v-model="record.list_in_schema">
                                    <label slot="off-label"></label>
                                </p-check>
                            </div>
                            <!-- ./listar en esquemas -->

                            <!-- acrónimo -->
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label for="acronym">Acrónimo</label>
                                    <input type="text" id="acronym" placeholder="Acrónimo" v-is-text
                                        @input="normalizeText($event.target.value, 'acronym')"
                                        class="form-control input-sm" v-model="record.acronym" data-toggle="tooltip"
                                        title="Indique el acrónimo del parámetro (requerido)">
                                </div>
                            </div>
                            <!-- ./acrónimo -->

                            <!-- valor máximo permitido -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="value_max">Valor máximo permitido:</label>
                                    <input id="value_max" class="form-control input-sm" type="text"
                                        data-toggle="tooltip" placeholder="Valor máximo permitido" @focus="selectText"
                                        title="Indique el valor máximo del parámetro" v-model="record.value_max"
                                        v-input-mask data-inputmask="
                                                'alias': 'numeric',
                                                'allowMinus': 'false',
                                                'digits': '0'
                                           " />
                                </div>
                            </div>
                            <!-- ./valor máximo permitido -->

                            <!-- Categorías de hoja de tiempo -->
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label for="exception_type">Categorías de hoja de tiempo</label>
                                    <select2 id="exception_type" @input="getPayrollClassificationTypes()" :options="exception_types" v-model="record.exception_type">
                                    </select2>
                                </div>
                            </div>
                            <!-- ./Categorías de hoja de tiempo -->

                            <!-- Valor máximo permitido en hoja de tiempo -->
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label for="max_value_allowed_per_time_sheet">Valor máximo permitido en hoja de
                                        tiempo:</label>
                                    <input id="max_value_allowed_per_time_sheet" class="form-control input-sm"
                                        type="text" data-toggle="tooltip"
                                        placeholder="valor máximo permitido en hoja de tiempo" @focus="selectText"
                                        :disabled="!record.exception_type"
                                        title="Indique el valor máximo permitido en hoja de tiempo"
                                        v-model="record.max_value_allowed_per_time_sheet" v-input-mask data-inputmask="
                                                'alias': 'numeric',
                                                'allowMinus': 'false',
                                                'digits': '0'
                                           " />
                                </div>
                            </div>
                            <!-- ./valor máximo permitido en hoja de tiempo -->

                            <!-- Clasificación de parametro -->
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label for="classification_type">Clasificación de parámetro</label>
                                    <select v-model="record.classification_type" id="classification_type" class="form-control" style="padding-bottom: 0px; padding-left: -3px;">
                                        <option v-for="option in classification_types" :key="option.id" v-bind:value="option.id">
                                            {{ option.text }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <!-- ./Clasificación de parametro -->

                            <div class="col-md-6">
                                <!-- expediente, parámetro y registros -->
                                <div class="row">
                                    <div class="form-group col-6">
                                        <label for="worker_record">¿Expediente del Trabajador?</label>
                                        <p-radio class="d-block pretty p-switch p-fill p-bigger" color="success"
                                            off-color="text-gray" toggle data-toggle="tooltip"
                                            title="Indique si desea utilizar una variable del expediente del Trabajador"
                                            v-model="variable" value="worker_record">
                                            <label slot="off-label"></label>
                                        </p-radio>
                                    </div>

                                    <div class="form-group col-6">
                                        <label for="parameter">¿Parámetro?</label>
                                        <p-radio class="d-block pretty p-switch p-fill p-bigger" color="success"
                                            off-color="text-gray" toggle data-toggle="tooltip"
                                            title="Indique si desea utilizar un parámetro previamente registrado"
                                            v-model="variable" value="parameter">
                                            <label slot="off-label"></label>
                                        </p-radio>
                                    </div>
                                </div>
                                <!-- ./expediente, parámetro y registros -->

                                <!-- opciones -->
                                <div v-if="variable" class="form-group">
                                    <label for="register">Registro</label>
                                    <select2 :options="options" @input="getNameOption" v-model="variable_option">
                                    </select2>
                                </div>
                                <!-- ./opciones -->
                            </div>

                            <!-- fórmula -->
                            <div class="col-12 col-xl-6">
                                <!-- ./calculadora -->
                                <div class="form-group is-required" style="z-index: 0;" v-if="!useFunction">
                                    <label>Fórmula</label>
                                    <textarea type="text" id="formulaShow" style="font-size: 1rem; font-weight: bold;"
                                        class="form-control input-sm" data-toggle="tooltip" disabled
                                        title="Fórmula a aplicar para el concepto. Utilice la siguiente calculadora para establecer los parámetros de la fórmula"
                                        rows="3" v-model="record.formulaShow">
                                    </textarea>
                                </div>
                                <div class="formula-calculator">
                                    <formula-calculator formulaInput='formulaShow' :withDisplay="false"
                                        ref="formulaResults" />
                                </div>
                                <div class="form-group row mb-n1">
                                    <div class="col-12 col-md-8 col-md-6 text-center mx-auto">
                                        <button type="button" class="btn btn-info btn-sm btn-formula btn-function"
                                            data-toggle="tooltip" title="presione para mover a la izquierda"
                                            @click="highlightPosition(false)"
                                            :style="{ opacity: useFunction ? 0.5 : 1 }">
                                            <i class="fa fa-long-arrow-left"></i>
                                        </button>
                                        <button type="button" class="btn btn-info btn-sm btn-formula btn-function"
                                            data-toggle="tooltip" title="presione para mover a la derecha"
                                            @click="highlightPosition(true)"
                                            :style="{ opacity: useFunction ? 0.5 : 1 }">
                                            <i class="fa fa-long-arrow-right"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group row" v-if="variable_option">
                                    <div class="col-12 col-md-8 text-center mx-auto">
                                        <button type="button" class="btn btn-info btn-sm btn-formula btn-variable"
                                            data-toggle="tooltip" title="Variable a usar cuando se realice el cálculo"
                                            @click="setVariable()">
                                            {{ updateNameVariable }}
                                        </button>
                                    </div>
                                </div>
                                <!-- ./calculadora -->
                            </div>
                            <!-- ./fórmula -->
                        </div>
                        <!-- ./variable reiniciable a cero -->
                    </div>
                    <div class="modal-footer">
                        <div class="form-group">
                            <button type="button" class="btn btn-default btn-sm btn-round btn-modal-close"
                                @click="clearFilters" data-dismiss="modal">
                                Cerrar
                            </button>
                            <button type="button" class="btn btn-warning btn-sm btn-round btn-modal btn-modal-clear"
                                @click="reset()">
                                Cancelar
                            </button>
                            <button type="button" @click="createRecord('payroll/parameters')"
                                class="btn btn-primary btn-sm btn-round btn-modal-save">
                                Guardar
                            </button>
                        </div>
                    </div>
                    <div class="modal-body modal-table">
                        <v-client-table :columns="columns"
                            :data="records.filter(el => el.name != 'Numero de lunes del mes')" :options="table_options">
                            <div slot="description" slot-scope="props" v-html="props.row.description"></div>
                            <div slot="id" slot-scope="props" class="text-center">
                                <button @click="initUpdate(props.row.id, $event)"
                                    class="btn btn-warning btn-xs btn-icon btn-action" title="Modificar registro"
                                    data-toggle="tooltip" type="button">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button @click="deleteRecord(props.row.id, 'payroll/parameters')"
                                    class="btn btn-danger btn-xs btn-icon btn-action" title="Eliminar registro"
                                    data-toggle="tooltip" type="button">
                                    <i class="fa fa-trash-o"></i>
                                </button>
                            </div>
                        </v-client-table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
// Se importó el componente para que funcione bien
import Formula from '../../../../../../../resources/js/components/Shared/FormulaCalculatorComponent.vue';

export default {
    components: { 'formula-calc': Formula },
    data() {
        return {
            record: {
                id: '',
                name: '',
                description: '',
                parameter_type: '',
                percentage: false,
                value: '',
                formula: '',
                formulaShow: '',
                max_value_per_period: '',
                classification_type: '',
            },
            formula: '',
            variable: '',
            variable_option: '',
            variable_option_name: '',
            variable_option_bool: false,
            formulaFunction: '',
            formulaFunctionShow: '',
            type: '',
            value: '',
            operator: '',
            operators: [
                { "id": "", "text": "Ninguno" },
                { "id": "==", "text": "Igualdad (==)" },
                { "id": "!=", "text": "Desigualdad (!=)" },
                { "id": ">", "text": "Mayor estricto (>)" },
                { "id": "<", "text": "Menor estricto (<)" },
                { "id": ">=", "text": "Mayor o igual (>=)" },
                { "id": "<=", "text": "Menor o igual (<=)" }
            ],
            position: 0,
            positionShow: 0,
            useFunction: false,
            idFunction: '',
            subOptions: [],
            errors: [],
            records: [],
            columns: ['parameter_type_value', 'code', 'acronym', 'name', 'description', 'id'],
            options: [],
            parameter_types: [],
            exception_types: [],
            classification_types: [],
            recordOptions: {},
            functions: [
                { "id": "", "text": "Ninguno" },
                {
                    "id": "if",
                    "text": "SI",
                    "format": "(inputFormTest;inputFormValueIf;inputFormValueElse)",
                    "formatShow": "(Prueba; Valor <<Entonces>>; Valor <<De lo contrario>>)",
                    "description": "Especfica una prueba logica que se desea efectuar.",
                    "parameters": [
                        {
                            "required": "is-required",
                            "id": "inputFormTest",
                            "name": "Prueba",
                            "description": "Cualqueir valor o expresión que pueda evaluarse como VERDADERO o FALSO.",
                            "value": "select case when"
                        },
                        {
                            "required": "",
                            "id": "inputFormValueIf",
                            "name": "Valor <<Entonces>>",
                            "description": "El resultado de la función si la prueba lógica devuelve VERDADERO.",
                            "value": "then"
                        },
                        {
                            "required": "",
                            "id": "inputFormValueElse",
                            "name": "Valor <<De lo contrario>>",
                            "description": "El resultado de la función si la prueba lógica devuelve FALSO.",
                            "value": "else"
                        }
                    ]
                },
                {
                    "id": "sum",
                    "text": "SUM",
                    "format": "(number1;number2)",
                    "formatShow": "(Número 1;Número 2)",
                    "description": "Devuelve la suma de los argumentos.",
                    "parameters": [
                        {
                            "required": "is-required",
                            "id": "number1",
                            "name": "Número 1",
                            "description": "Número 1, número 2,... son argumentos cuyo total se calculará.",
                            "value": "+"
                        },
                        {
                            "required": "",
                            "id": "number2",
                            "name": "Número 2",
                            "description": "Número 1, número 2,... son argumentos cuyo total se calculará.",
                            "value": "+"
                        }
                    ]
                },
            ],
        }
    },
    created() {
        const vm = this;
        vm.table_options.headings = {
            'parameter_type_value': 'Tipo de parámetro',
            'code': 'Código',
            'acronym': 'Acrónimo',
            'name': 'Nombre',
            'description': 'Descripción',
            'id': 'Acción'
        };
        vm.table_options.sortable = ['parameter_type_value', 'code', 'acronym', 'name', 'description'];
        vm.table_options.filterable = ['parameter_type_value', 'code', 'acronym', 'name', 'description'];
        vm.table_options.columnsClasses = {
            'parameter_type_value': 'col-xs-2',
            'code': 'col-xs-1',
            'acronym': 'col-xs-1',
            'name': 'col-xs-2',
            'description': 'col-xs-4',
            'id': 'col-xs-2'
        };
    },
    mounted() {
        const vm = this;
        $("#add_payroll_parameter").on('show.bs.modal', function () {
            vm.reset();
            vm.getPayrollParameterTypes();
            vm.getPayrollExceptionTypes();
            vm.getOptions('payroll/get-associated-records');

            vm.$refs.formulaResults.setFormula = function (value) {
                let formulaDisplay = (!vm.useFunction) ? vm.record['formula'] : vm.formulaFunction;
                let formulaDisplayShow = (!vm.useFunction) ? vm.record['formulaShow'] : vm.formulaFunctionShow;

                formulaDisplay = formulaDisplay.replace(' | ', '');
                formulaDisplayShow = formulaDisplayShow.replace(' | ', '');

                let symbols = ['+', '-', '/', '*', '%'];

                if (value === 'backspace') {
                    //vm.formulaHistory.pop();
                    //vm.formulaShowHistory.pop();
                    //let dataF = vm.formulaHistory.pop();
                    //let dataSF = vm.formulaShowHistory.pop();
                    if (!vm.useFunction) {
                        let newFormula = vm.getLastFormula();

                        vm.record['formula'] = newFormula['formula'];
                        vm.record['formulaShow'] = newFormula['formulaShow'];
                        vm.highlightPosition();

                        //vm.record['formula'] = ("undefined" != typeof(dataF)) ? dataF : "";
                        //vm.record['formulaShow'] = ("undefined" != typeof(dataSF)) ? dataSF : "";
                    } else {
                        vm.formulaFunction = formulaDisplay.substring(0, formulaDisplay.length - 1);
                        vm.formulaFunctionShow = formulaDisplayShow.substring(0, formulaDisplayShow.length - 1);
                    }
                    return false;
                } else if (value === 'C') {
                    vm.variable = '';
                    vm.variable_option = '';
                    vm.position = 0;
                    vm.positionShow = 0;
                    $.each(vm.functions, function (index, field) {
                        if (field['id'] == "sum") {
                            $.each(vm.functions[index]['parameters'], function (index, field) {
                                let input = document.getElementById('number' + (index + 1));
                                if (input) input.value = '';
                            });
                        } else if (field['id'] == "if") {
                            $.each(vm.functions[index]['parameters'], function (index, field) {
                                let input = document.getElementById(field['id']);
                                if (input) input.value = '';
                            });

                        }
                    });
                    if (!vm.useFunction) {
                        vm.record['formula'] = '';
                        vm.record['formulaShow'] = '';
                        //vm.formulaHistory = [];
                        //vm.formulaShowHistory = [];
                    } else {
                        vm.formulaFunction = '';
                        vm.formulaFunctionShow = '';
                    }
                    return false;
                }

                if (formulaDisplay.length === 0 && symbols.includes(value)) {
                    vm.showMessage(
                        'custom', 'Fórmula Inválida', 'warning', 'screen-warning',
                        'No esta permitido indicar símbolos como primer elemento de la fórmula'
                    );
                    return false;
                } else if (symbols.includes(formulaDisplay.slice(-1)) && symbols.includes(value)) {
                    vm.showMessage(
                        'custom', 'Fórmula Inválida', 'warning', 'screen-warning',
                        'No esta permitido indicar símbolos de forma consecutiva'
                    );
                    return false;
                }

                if (value === 0 && formulaDisplay.slice(-1) === '/') {
                    vm.showMessage(
                        'custom', 'Fórmula Inválida', 'warning', 'screen-warning', 'La división por cero no esta permitida'
                    );
                    return false;
                }
                /** Se asigna los valores al campo determinado */
                if (!vm.useFunction) {

                    formulaDisplay = formulaDisplay.substring(0, vm.position) + value + formulaDisplay.substring(vm.position);
                    formulaDisplayShow = formulaDisplayShow.substring(0, vm.positionShow) + value + formulaDisplayShow.substring(vm.positionShow);
                    vm.record['formula'] = formulaDisplay;
                    vm.record['formulaShow'] = formulaDisplayShow;
                    vm.highlightPosition(true);
                } else {
                    if (vm.idFunction != "") {
                        $.each(vm.getInfoFunction["parameters"] ?? [], function (index, field) {
                            if (vm.getInfoFunction["currentParamenter"]) {
                                if (vm.getInfoFunction["currentParamenter"]["id"] == field["id"]) {
                                    let element = document.getElementById(field["id"]);
                                    if (element) {
                                        element.value += value;
                                        vm.getFormulaFunction();
                                    }
                                }
                            }
                        });
                    } else {
                        formulaDisplay += value;
                        formulaDisplayShow += value;
                        vm.formulaFunction = formulaDisplay;
                        vm.formulaFunctionShow = formulaDisplayShow;
                    }
                }
            };
        });
    },
    watch: {
        'record.exception_type': async function (exception_type) {
            const vm = this;
            if (exception_type) {
                await vm.getExceptionTypeMaxValue();
            }
        },
        'record.formulaShow': async function (formulaShow) {
            const vm = this;
            if (formulaShow) {
                vm.getFormulaFunction(formulaShow)
            }
        },

        /**
         * Método que supervisa los cambios en el campo variable y actualiza el listado de opciones
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve> | <henryp2804@gmail.com>
         */
        variable: function (variable) {
            const vm = this;
            vm.operator = vm.value = '';
            if (vm.variable == 'parameter') {
                vm.getOptions('payroll/get-parameters?withExcedents=true');
            } else if (vm.variable == 'worker_record') {
                if (vm.record.parameter_type === "time_parameter") {
                    vm.getOptions('payroll/get-associated-records', {
                        params: {
                            onlyParams: ["WORKLOAD"]
                        }
                    });
                } else {
                    vm.getOptions('payroll/get-associated-records');
                }
            }
        },

        /**
         * Método que supervisa los cambios en el campo type y actualiza el listado de opciones
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve> | <henryp2804@gmail.com>
         */
        type: function (type) {
            const vm = this;
            if (vm.type == 'list') {
                axios.get(`${window.app_url}/payroll/get-parameter-options/${vm.variable_option}`).then(response => {
                    vm.subOptions = response.data;
                });
            } else if (vm.type == 'boolean') {
                vm.value = false;
            }
        }
    },
    computed: {
        getInfoFunction() {
            const vm = this;
            let objectFunction = null;

            $.each(vm.functions, function (index, field) {
                if (field['id'] == vm.idFunction) {
                    objectFunction = field;
                }
            });

            if (vm.idCurrentInput != '') {
                $.each(objectFunction['parameters'], function (index, field) {
                    if (field['id'] == vm.idCurrentInput) {
                        objectFunction['currentParamenter'] = field;
                    }
                });
            }
            return objectFunction;
        },
        /**
         * Método que actualiza el nombre de la variable a emplear en el cálculo
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve> | <henryp2804@gmail.com>
         * @return    {string}
         */
        updateNameVariable: function () {
            const vm = this;
            let response = '';
            if (vm.variable_option != '') {
                vm.options.forEach(function (value, index) {
                    if (value.id == vm.variable_option) {
                        response = value.text;
                    } else if (typeof value.children !== 'undefined') {
                        value.children.forEach(function (value, index) {
                            if (value.id == vm.variable_option) {
                                response = value.text;
                            }
                        });
                    }
                });
            }
            return response;
        },
    },
    methods: {
        /**
         * Extendiendo la funcionalidad original
         * Método que permite crear o actualizar un registro
         *
         * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         * @param  {string} url    Ruta de la acción a ejecutar para la creación o actualización de datos
         * @param  {string} list   Condición para establecer si se cargan datos en un listado de tabla.
         *                         El valor por defecto es verdadero.
         * @param  {string} reset  Condición que evalúa si se inicializan datos del formulario.
         *                         El valor por defecto es verdadero.
         */
        async createRecord(url, list = true, reset = true) {
            const vm = this;
            url = vm.setUrl(url);

            // Asignar el tipo de clasificación
            vm.record.classification_type = document.getElementById('classification_type').value;
            vm.record.formula = vm.record.formula.replace(/\s/g, '').replace('|', "");

            if (vm.record.id) {
                vm.updateRecord(url);
            }
            else {
                vm.loading = true;
                let fields = {};

                for (let index in vm.record) {
                    fields[index] = vm.record[index];
                }
                await axios.post(url, fields).then(response => {
                    if (typeof (response.data.redirect) !== "undefined") {
                        location.href = response.data.redirect;
                    }
                    else {
                        vm.errors = [];
                        if (reset) {
                            vm.reset();
                        }
                        if (list) {
                            vm.readRecords(url);
                        }

                        vm.showMessage('store');
                    }
                }).catch(error => {
                    vm.errors = [];

                    if (typeof (error.response) != "undefined") {
                        if (error.response.status == 403) {
                            vm.showMessage(
                                'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                            );
                        }
                        for (let index in error.response.data.errors) {
                            if (error.response.data.errors[index]) {
                                vm.errors.push(error.response.data.errors[index][0]);
                            }
                        }
                    }

                });

                vm.loading = false;
            }

        },
        /**
         * Método que obtiene el acrónimo de la variable a emplear en el cálculo
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve> | <henryp2804@gmail.com>
         * @return    {string}
         */
        setVariable() {
            const vm = this;
            let variables = ['parameter', 'tabulator', 'concept'];
            let formulaDisplay = (!vm.useFunction) ? vm.record['formula'] : vm.formulaFunction;
            let formulaDisplayShow = (!vm.useFunction) ? vm.record['formulaShow'] : vm.formulaFunctionShow;

            formulaDisplay = formulaDisplay.replace(' | ', '');
            formulaDisplayShow = formulaDisplayShow.replace(' | ', '');

            /** Se asigna los valores al campo determinado */
            if (!vm.useFunction) {
                let value = (variables.includes(vm.variable))
                    ? (vm.variable + '(' + vm.variable_option + ')')
                    : vm.variable_option;
                formulaDisplay = formulaDisplay.substring(0, vm.position) + value + formulaDisplay.substring(vm.position);
                formulaDisplayShow = formulaDisplayShow.substring(0, vm.positionShow) + vm.updateNameVariable + formulaDisplayShow.substring(vm.positionShow);

                if (variables.includes(vm.variable)) {
                    vm.position += (vm.variable + '(' + vm.variable_option + ')').length;
                    vm.positionShow += vm.updateNameVariable.length;
                    vm.recordOptions[(vm.variable + '(' + vm.variable_option + ')')] = vm.updateNameVariable;
                } else {
                    vm.position += vm.variable_option.length;
                    vm.positionShow += vm.updateNameVariable.length;
                    vm.recordOptions[vm.variable_option] = vm.updateNameVariable;
                }

                vm.record['formula'] = formulaDisplay;
                vm.record['formulaShow'] = formulaDisplayShow;
            } else {
                if (vm.idFunction == "") {
                    formulaDisplay += (variables.includes(vm.variable))
                        ? (vm.variable + '(' + vm.variable_option + ')')
                        : vm.variable_option;
                    formulaDisplayShow += vm.updateNameVariable;
                    vm.formulaFunction = formulaDisplay;
                    vm.formulaFunctionShow = formulaDisplayShow;
                } else {
                    let element = document.getElementById(vm.getInfoFunction["currentParamenter"]["id"]);
                    if (element) {
                        element.value += vm.updateNameVariable;
                        vm.getFormulaFunction(((variables.includes(vm.variable))
                            ? (vm.variable + '(' + vm.variable_option + ')')
                            : vm.variable_option));
                    }
                }
            }
            vm.highlightPosition();
        },
        addParameter() {
            const vm = this;
            $.each(vm.functions, function (index, field) {
                if (field['id'] == "sum") {
                    let format = '(';
                    let formatShow = '(';
                    let element = {
                        required: "",
                        id: "number" + (vm.functions[index]['parameters'].length + 1),
                        name: "Número " + (vm.functions[index]['parameters'].length + 1),
                        description: "Número 1, número 2,... son argumentos cuyo total se calculará.",
                        value: "+"
                    };
                    for (let i = 1; i <= (vm.functions[index]['parameters'].length + 1); i++) {
                        format += "number" + i + (i != (vm.functions[index]['parameters'].length + 1) ? ";" : "");
                        formatShow += "Número " + i + (i != (vm.functions[index]['parameters'].length + 1) ? ";" : "");
                    }
                    format += ")";
                    formatShow += ")";
                    vm.functions[index]['format'] = format;
                    vm.functions[index]['formatShow'] = formatShow;
                    vm.functions[index]['parameters'].push(element);
                    vm.functions[index]['currentParamenter'] = element;
                    vm.idCurrentInput = "number" + (vm.functions[index]['parameters'].length + 1);
                }
            });
        },
        deleteParameter() {
            const vm = this;
            $.each(vm.functions, function (index, field) {
                if (field['id'] == "sum") {
                    let format = '(';
                    let formatShow = '(';
                    for (let i = 1; i <= (vm.functions[index]['parameters'].length - 1); i++) {
                        format += "number" + i + (i != (vm.functions[index]['parameters'].length - 1) ? ";" : "");
                        formatShow += "Número " + i + (i != (vm.functions[index]['parameters'].length - 1) ? ";" : "");
                    }
                    format += ")";
                    formatShow += ")";
                    vm.functions[index]['format'] = format;
                    vm.functions[index]['formatShow'] = formatShow;
                    vm.functions[index]['currentParamenter'] = vm.functions[index]['parameters'].slice(vm.functions[index]['parameters'].length - 2, vm.functions[index]['parameters'].length - 1)[0];
                    vm.functions[index]['parameters'] = vm.functions[index]['parameters'].slice(0, vm.functions[index]['parameters'].length - 1);
                    vm.idCurrentInput = "number" + vm.functions[index]['parameters'].length - 1;
                }
            });
        },
        getLastFormula() {
            const vm = this;

            let formula = vm.record['formula'].substring(0, vm.position);
            let formulaShow = vm.record['formulaShow'].substring(0, vm.positionShow);
            let regexs = [
                /concept\(\d+\)$/,
                /parameter\(\d+\)$/,
                /tabulator\(\d+\)$/,
            ];

            const modifiedKeys = Object.keys(vm.recordOptions).map(key => new RegExp(`${key}$`)).sort((a, b) => a.length - b.length);
            regexs = [...regexs, ...modifiedKeys];

            let value = regexs.some(regex => {
                let result = formula.match(regex);
                if (result) {
                    formula = formula.substring(0, formula.length - result[0].length) + vm.record['formula'].substring(vm.position);
                    formulaShow = formulaShow.substring(0, formulaShow.length - vm.recordOptions[result[0]].length) + vm.record['formulaShow'].substring(vm.positionShow);
                    vm.position -= result[0].length;
                    vm.positionShow -= vm.recordOptions[result[0]].length;
                    return true;
                }
                return false;
            });

            if (!value) {
                formula = vm.record['formula'].substring(0, vm.position - 1) + vm.record['formula'].substring(vm.position);
                formulaShow = vm.record['formulaShow'].substring(0, vm.positionShow - 1) + vm.record['formulaShow'].substring(vm.positionShow);

                if (vm.position > 0) {
                    vm.position--;
                }
                if (vm.positionShow > 0) {
                    vm.positionShow--;
                }
            }

            return {
                'formula': formula,
                'formulaShow': formulaShow
            };
        },
        highlightPosition(sum = null) {
            const vm = this;
            vm.record['formula'] = vm.record['formula'].replace(' | ', '');
            vm.record['formulaShow'] = vm.record['formulaShow'].replace(' | ', '');

            if (sum === true) {
                let formula = vm.record['formula'].substring(vm.position);

                let regexs = [
                    /^concept\(\d+\)/,
                    /^parameter\(\d+\)/,
                    /^tabulator\(\d+\)/,
                ];

                const modifiedKeys = Object.keys(vm.recordOptions).map(key => new RegExp(`^${key}`)).sort((a, b) => a.length - b.length);
                regexs = [...regexs, ...modifiedKeys];

                let value = regexs.some(regex => {
                    let result = formula.match(regex);
                    if (result) {

                        vm.position += result[0].length;
                        vm.positionShow += vm.recordOptions[result[0]].length;
                        return true;
                    }
                    return false;
                });

                if (!value) {
                    if (vm.position < vm.record['formula'].length) {
                        vm.position++;
                    }
                    if (vm.positionShow < vm.record['formulaShow'].length) {
                        vm.positionShow++;
                    }
                }
            } else if (sum === false) {

                let formula = vm.record['formula'].substring(0, vm.position);

                let regexs = [
                    /concept\(\d+\)$/,
                    /parameter\(\d+\)$/,
                    /tabulator\(\d+\)$/,
                ];

                const modifiedKeys = Object.keys(vm.recordOptions).map(key => new RegExp(`${key}$`)).sort((a, b) => a.length - b.length);
                regexs = [...regexs, ...modifiedKeys];

                let value = regexs.some(regex => {
                    let result = formula.match(regex);
                    if (result) {
                        vm.position -= result[0].length;
                        vm.positionShow -= vm.recordOptions[result[0]].length;
                        return true;
                    }
                    return false;
                });

                if (!value) {
                    if (vm.position > 0) {
                        vm.position--;
                    }
                    if (vm.positionShow > 0) {
                        vm.positionShow--;
                    }
                }
            }

            if (vm.position !== 0 && vm.position !== vm.record['formula'].length) {

                vm.record['formula'] = vm.record['formula'].substring(0, vm.position) + ' | ' + vm.record['formula'].substring(vm.position);
            }
            if (vm.positionShow !== 0 && vm.positionShow !== vm.record['formulaShow'].length) {

                vm.record['formulaShow'] = vm.record['formulaShow'].substring(0, vm.positionShow) + ' | ' + vm.record['formulaShow'].substring(vm.positionShow);
            }
        },
        getFormulaFunction(value = '') {
            const vm = this;
            let result = '';

            let resultShow = vm.getInfoFunction["format"] ?? '';

            $.each(vm.getInfoFunction["parameters"] ?? [], function (index, field) {
                if ((field["id"] == 'inputFormTest') && (vm.variable_option != '')) {
                    let elementOp = document.getElementById(field["id"] + 'Operator');
                    let elementVal = document.getElementById(field["id"] + 'Value');
                    if ((elementOp) && (elementVal)) {
                        resultShow = resultShow.replace(
                            field["id"], vm.updateNameVariable + ' ' + elementOp.value + ' ' + (
                                (typeof elementVal.options !== "undefined")
                                    ? elementVal.options[elementVal.selectedIndex].text
                                    : elementVal.value)
                        );
                        result += field["value"] + " " + vm.variable_option + " " + elementOp.value + " " + elementVal.value + " ";
                    }
                } else {
                    let element = document.getElementById(field["id"]);
                    if (element) {
                        resultShow = resultShow.replace(field["id"], element.value);
                        result += ((index > 0) ? (field["value"] + " ") : "") + element.value + " ";
                    }
                }
            });
            vm.formulaFunction = result.trim();
            vm.formulaFunctionShow = vm.getInfoFunction["text"] + resultShow;
        },
        async getExceptionTypeMaxValue() {
            const vm = this;
            vm.record.max_value_per_period = await new Promise((resolve, reject) => {
                let max_value = vm.exception_types.find((type) => {
                    return type.id == vm.record.exception_type;
                })
                resolve(max_value.value_max);
            });
        },

        /**
         * Obtiene los datos de clasificacion de parámetros
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        getPayrollClassificationTypes() {
            const vm = this;

            axios
                .get(
                    `${window.app_url}/payroll/classification-parameters/vue-list`,
                    {
                        params: {
                            exception_type_id: vm.record.exception_type
                        }
                    }
                )
                .then(response => {
                    vm.classification_types = response.data;
                });
        },

        initUpdate(id, event) {
            let vm = this;
            vm.errors = [];
            event.preventDefault();

            let recordEdit = JSON.parse(JSON.stringify(vm.records.filter((rec) => {
                return rec.id === id;
            })[0])) || vm.reset();

            recordEdit.formulaShow = recordEdit.translate_formula ?? recordEdit.formula;

            vm.record = recordEdit;

            vm.recordOptions = recordEdit.parameter_options;
            vm.position = vm.record['formula'].length;
            vm.positionShow = vm.record['formulaShow'].length;

            // Remove the class from the select element
            const selectElement = document.getElementById('classification_type');
            if (selectElement) {
                selectElement.classList.remove('select2-hidden-accessible');
            }
            // Hide the span with the class 'select2'
            $(document).ready(function() {
                // Hide the span using its data-select2-id attribute
                $('span[aria-labelledby="select2-classification_type-container"]').hide();
            });

            // vm.setVariable();
        },
        /**
         * Método que borra todos los datos del formulario
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve>
         */
        reset() {
            const vm = this;
            // vm.position = 0;
            // vm.positionShow = 0;
            vm.variable = '';
            vm.variable_option = '';
            vm.errors = [];
            vm.useFunction = false;

            // vm.formula = '';
            // vm.formulaShow = '';
            vm.variable_option_name = '';
            vm.variable_option_bool = false;

            $.each(vm.functions, function (index, field) {
                if (field['id'] == "sum") {
                    $.each(vm.functions[index]['parameters'], function (index, field) {
                        let input = document.getElementById('number' + (index + 1));
                        if (input) input.value = '';
                    });
                } else if (field['id'] == "if") {
                    $.each(vm.functions[index]['parameters'], function (index, field) {
                        let input = document.getElementById(field['id']);
                        if (input) input.value = '';
                    });

                }
            });

            vm.record = {
                id: '',
                name: '',
                description: '',
                parameter_type: '',
                percentage: false,
                value: '',
                formula: '',
                formulaShow: '',
            };

            if (!vm.useFunction) {
                vm.record['formula'] = '';
                vm.record['formulaShow'] = '';
                //vm.formulaHistory = [];
                //vm.formulaShowHistory = [];
            } else {
                vm.formulaFunction = '';
                vm.formulaFunctionShow = '';
            }
            return false;
        },
        /**
         * Método que borra los campos comunes del formulario
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve>
         */
        changeParameterType() {
            const vm = this;

            if (vm.record.parameter_type == 'processed_variable') {
                vm.variable = '';
                vm.record.list_in_schema = '';
            } else if (vm.record.parameter_type == 'global_value') {
                vm.variable = '';
                vm.record.formula = '';
                vm.record.list_in_schema = '';
            } else if (vm.record.parameter_type == 'resettable_variable') {
                vm.variable = '';
                vm.record.formula = '';
                vm.record.percentage = false;
                vm.record.list_in_schema = '';
            }

            if (vm.record.parameter_type == 'time_parameter' && !vm.record.id) {
                vm.variable = '';
                vm.record.formula = '';
                vm.record.list_in_schema = true;
            }
        },
        /**
         * Método que obtiene un arreglo con los tipos de parámetros
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve>
         */
        getPayrollParameterTypes() {
            const vm = this;
            vm.parameter_types = [];
            axios.get(`${window.app_url}/payroll/get-parameter-types`).then(response => {
                vm.parameter_types = response.data;
            });
        },
        /**
         * Método que obtiene un arreglo con las opciones a listar
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve>
         */
        getOptions(url, body = {}) {
            const vm = this;
            vm.options = [];
            url = vm.setUrl(url);
            axios.get(url, body).then(response => {
                vm.options = response.data;
            });
        },
        /**
         * Método que obtiene el acrónimo de la variable a emplear en el cálculo
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve> | <henryp2804@gmail.com>
         * @return    {string}
         */
        getCodeVariable() {
            const vm = this;
            let response = '';
            let showFormula = '';
            if ((vm.variable != 'worker_record') ||
                ((vm.operator != '') && (vm.value != '')) ||
                ((vm.operator != '') && (vm.type == 'boolean')) ||
                ((vm.operator == '') && (vm.type == 'number'))) {
                if (vm.variable_option != '') {
                    $.each(vm.options, function (index, field) {
                        if (field['id'] == vm.variable_option) {
                            if (typeof field['id'] !== 'undefined') {
                                response = vm.variable + '(' + field['id'] + ')';
                                showFormula = field['text'];
                            } else if (typeof field['id'] !== 'undefined') {
                                response = 'if(' + field['id'] + ' ' + vm.operator + ' ' + vm.value + '){}';
                                showFormula = 'Si(' + field['text'] + ' ' + vm.operator + ' ' + vm.value + '){}';
                            }
                        } else if (typeof field['children'] !== 'undefined') {
                            $.each(field['children'], function (index, field) {
                                if (typeof field['id'] !== 'undefined') {
                                    if (field['id'] == vm.variable_option) {
                                        if (vm.operator == '') {
                                            response = field['id'];
                                            showFormula = field['text'];
                                        } else {
                                            response = 'if(' + field['id'] + ' ' + vm.operator + ' ' + vm.value + '){}';
                                            showFormula = 'Si(' + field['text'] + ' ' + vm.operator + ' ' + vm.value + '){}';
                                        }
                                    }
                                }
                            });
                        }
                    });
                }
                if (response != '') {
                    if (vm.record.formula != '') {
                        let keys = vm.record.formula.indexOf('}');
                        if (keys > 0) {
                            let firstFormula = vm.record.formula.substr(0, keys);
                            let lastFormula = vm.record.formula.substr(keys, vm.record.formula.length);
                            vm.record.formula = firstFormula + response + lastFormula;
                        } else {
                            vm.record.formula += response;
                        }
                    } else {
                        vm.record.formula += response;
                    }
                }
                if (showFormula != '') {
                    if (vm.formula != '') {
                        let keys = vm.formula.indexOf('}');
                        if (keys > 0) {
                            let firstFormula = vm.formula.substr(0, keys);
                            let lastFormula = vm.formula.substr(keys, vm.formula.length);
                            vm.formula = firstFormula + showFormula + lastFormula;
                        } else {
                            vm.formula += showFormula;
                        }
                    } else {
                        vm.formula += showFormula;
                    }
                }
            }
        },
        getOptionType() {
            const vm = this;
            //vm.type = '';
            if (vm.variable_option != '') {
                $.each(vm.options, function (index, field) {
                    if (field['id'] == vm.variable_option) {
                        if (vm.type == field['type']) {
                            axios.get(`${window.app_url}/payroll/get-parameter-options/${vm.variable_option}`)
                                .then(response => {
                                    vm.subOptions = response.data;
                                });
                        }
                        if (typeof field['type'] !== 'undefined') {
                            vm.type = field['type'];
                            return;
                        }
                    } else if (typeof field['children'] !== 'undefined') {
                        $.each(field['children'], function (index, field) {
                            if (field['id'] == vm.variable_option) {
                                if (vm.type == field['type']) {
                                    axios.get(`${window.app_url}/payroll/get-parameter-options/${vm.variable_option}`)
                                        .then(response => {
                                            vm.subOptions = response.data;
                                        });
                                }
                                if (typeof field['type'] !== 'undefined') {
                                    vm.type = field['type'];
                                    return;
                                }
                            }
                        });
                    }
                });
            }
        },
        /**
         * Método para asignar el nombre de la variable en uso a la calculadora
         *
         * @author    Angelo Osorio <adosorio@cenditel.gob.ve>
         */
        getNameOption() {
            const vm = this;
            if (vm.variable_option != '') {
                if (vm.variable == "parameter") {
                    let filter = vm.options.find(({ id }) => id == vm.variable_option)
                    vm.variable_option_name = filter.text
                    vm.variable_option_bool = true
                }
                if (vm.variable == "worker_record") {
                    vm.options.forEach(element => {
                        if (element.children) {
                            let filter = element.children.find(({ id }) => id == vm.variable_option)
                            if (filter && filter.text && filter.text !== undefined) {
                                vm.variable_option_name = filter.text
                                vm.variable_option_bool = true
                            }
                        }
                    });
                }
            }
        },
        /**
         * Método que obtiene el estado de la propiedad is-invalid para elementos del formulario
         *
         * @method    isInvalid
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve> | <henryp2804@gmail.com>
         *
         * @param     {string}    elName    Nombre del elemento a buscar
         * @param     {string}    model     Nombre del modelo donde buscar
         */
        isInvalid(elName, model = 'record') {
            const vm = this;

            if (typeof vm[model][elName] != 'undefined') {
                let keys = vm[model][elName].indexOf('/0');
                return (keys > 0) ? 'is-invalid' : '';
            } else {
                return '';
            }
        },
    }
};
</script>
