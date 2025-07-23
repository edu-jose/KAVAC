<template>
    <section id="PayrollEmploymentForm">
        <!-- card-body -->
        <div class="card-body">
            <!-- mensajes de error -->
            <div class="alert alert-danger" v-if="errors.length > 0">
                <div class="m-2">
                    <div class="alert-icon">
                        <i class="now-ui-icons objects_support-17"></i>
                    </div>
                    <strong>Cuidado!</strong> Debe verificar los siguientes
                    errores antes de continuar:
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"
                        @click.prevent="errors = []">
                        <span aria-hidden="true">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </span>
                    </button>
                    <ul>
                        <li v-for="error in errors" :key="error">
                            {{ error }}
                        </li>
                    </ul>
                </div>
            </div>
            <div class="alert alert-danger" v-if="peopleNotFound.length > 0">
                <div class="m-2">
                    <div class="alert-icon">
                        <i class="now-ui-icons objects_support-17"></i>
                    </div>
                    <strong>El listado de trabajadores que se importo no se correponde con el grupo de
                        supervisados</strong> las siguientes personas no se han
                    encontrado al importar:
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"
                        @click.prevent="peopleNotFound = []">
                        <span aria-hidden="true">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </span>
                    </button>
                    <ul>
                        <li v-for="people in peopleNotFound" :key="people">
                            {{ people["Nombre"] }}
                        </li>
                    </ul>
                </div>
            </div>
            <div class="alert alert-danger" v-if="peopleNotOneFound && peopleNotFound.length === 0 && peopleFound.length === 0 ">
                <div class="m-2">
                    <div class="alert-icon">
                        <i class="now-ui-icons objects_support-17"></i>
                    </div>
                    <strong>El listado de trabajadores que se importo no se correponde con el grupo de
                        supervisados</strong>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"
                        @click.prevent="peopleNotOneFound = false">
                        <span aria-hidden="true">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </span>
                    </button>
                </div>
            </div>
            <!-- ./mensajes de error -->

            <div class="row">
                <div class="col-md-6">
                    <!-- Desde -->
                    <div class="form-group is-required">
                        <label for="from_date">Desde:</label>
                        <input type="date" id="from_date" class="form-control no-restrict input-sm"
                            v-model="record.from_date" data-toggle="tooltip" @input="loadDataCompletedPeriods(); getHolidaysByPeriod(); getPayrollTimeSheetParameters(); setDaysInPeriod();"
                            title="Indique el periodo (requerido)" />
                        <input type="hidden" name="id" id="id" v-model="record.id" />
                    </div>
                    <!-- ./Desde -->
                </div>
                <div class="col-md-6">
                    <!-- Hasta -->
                    <div class="form-group is-required">
                        <label for="to_date">Hasta:</label>
                        <input type="date" id="to_date" placeholder="Hasta" class="form-control no-restrict input-sm"
                            @input="loadDataCompletedPeriods(); getHolidaysByPeriod(); getPayrollTimeSheetParameters(); setDaysInPeriod();" v-model="record.to_date" data-toggle="tooltip"
                            title="Indique el periodo (requerido)" />
                    </div>
                    <!-- ./Hasta -->
                </div>
                <div class="col-md-6">
                    <!-- Código -->
                    <div class="form-group is-required">
                        <label>Código grupo de supervisados:</label>
                        <select2 :options="payroll_supervised_groups" v-model="record.payroll_supervised_group_id"
                            @input="
                                getSupervisedGroupData();
                                setTimeSheetData();
                                loadDataCompletedPeriods();
                            ">
                        </select2>
                    </div>
                    <!-- ./Código -->
                </div>
                <div class="col-md-6" v-if="record.payroll_supervised_group_id">
                    <!-- Supervisor -->
                    <div class="form-group">
                        <label for="supervisor">Supervisor:</label>
                        <div class="row" style="margin: 1px 0">
                            <span class="col-md-12" id="Approver">
                                {{ record.supervisor }}
                            </span>
                        </div>
                    </div>
                    <!-- ./Supervisor -->
                </div>
                <div class="col-md-6" v-if="record.payroll_supervised_group_id">
                    <!-- Aprobador -->
                    <div class="form-group">
                        <label for="approver">Aprobador:</label>
                        <div class="row" style="margin: 1px 0">
                            <span class="col-md-12" id="Approver">
                                {{ record.approver }}
                            </span>
                        </div>
                    </div>
                    <!-- ./Aprobador -->
                </div>
                <div class="col-md-6" >
                    <!-- Parámetros de la hoja de tiempo -->
                    <div class="form-group is-required" v-if="payroll_time_sheet_parameters.length != 0">
                        <label>Parámetros de la hoja de tiempo:</label>
                        <select2 :options="payroll_time_sheet_parameters"
                            v-model="record.payroll_time_sheet_parameter_id" 
                            @input="setTimeSheetColumns();
                            loadDataCompletedPeriods();
                            setHolidaysByPeriod();
                            "
                            :disabled="!record.from_date || !record.to_date || !record.payroll_supervised_group_id">
                        </select2>
                    </div>
                    <!-- ./Parámetros de la hoja de tiempo -->
                </div>
            </div>
            <br />
            <br />
            <div class="row">
                <div class="col-md-12" v-if="record.payroll_time_sheet_parameter_id">
                    <!-- Tabla con los datos para la hoja de tiempo -->
                    <div class="float-right">
                        <button class="btn btn-sm btn-primary btn-custom" data-toggle="tooltip" type="button"
                            title="Importar registros" @click="setFile()">
                            <i class="fa fa-upload"></i>
                        </button>
                        <button class="btn btn-sm btn-primary btn-custom" data-toggle="tooltip" type="button"
                            title="Exportar registros" @click="exportTimeSheetData()">
                            <i class="fa fa-download"></i>
                        </button>
                        <input type="file" id="import_data" ref="fileInput" class="nodisplay"
                            @change="importTimeSheetData()" />
                    </div>
                    <!-- ./Tabla con los datos para la hoja de tiempo -->
                </div>
            </div>
            <v-draggable-table ref="draggableTable" :columns="draggableColumns"
                @error="receiveErrors" :data="draggableData" :parameters="parameters" :totalGroups="totalGroups"></v-draggable-table>
        </div>
        <!-- Final card-body -->

        <!-- card-footer -->
        <div class="card-footer text-right" id="helpParamButtons">
            <button class="btn btn-default btn-icon btn-round" data-toggle="tooltip" type="button"
                title="Borrar datos del formulario" @click="reset()">
                <i class="fa fa-eraser"></i>
            </button>
            <button type="button" class="btn btn-warning btn-icon btn-round" data-toggle="tooltip"
                title="Cancelar y regresar" @click="redirect_back(route_list)">
                <i class="fa fa-ban"></i>
            </button>
            <button type="button" @click="createRecord('payroll/time-sheet')" data-toggle="tooltip"
                title="Guardar registro" class="btn btn-success btn-icon btn-round">
                <i class="fa fa-save"></i>
            </button>
        </div>
        <!-- Final card-footer -->
    </section>
</template>
<script>
export default {
    props: {
        payroll_time_sheet_id: Number,
    },
    data() {
        return {
            record: {
                id: "",
                from_date: "",
                to_date: "",
                payroll_supervised_group_id: "",
                payroll_time_sheet_parameter_id: "",
                supervisor: "",
                approver: "",
                time_sheet_data: {},
                time_sheet_original_data: {},
                time_sheet_columns: {},
            },
            parameters: {},
            peopleNotOneFound: false,
            payroll_supervised_groups: [],
            payroll_time_sheet_parameters: [],
            payroll_staffs_reference: [],
            peopleNotFound: [],
            peopleFound: [],
            errors: [],
            draggableColumns: [],
            draggableData: [],
            maxHolidays: [],
            totalGroups: [],
            daysInPeriod: 0,
        };
    },
    watch: {
        'record.payroll_time_sheet_parameter_id': function (payrollTimeSheetParameterId) {
            const vm = this;
            if (payrollTimeSheetParameterId && vm.payroll_time_sheet_parameters.length != 0) {
                vm.parameters = vm.payroll_time_sheet_parameters.filter((parameter) => {
                    return parameter.id == payrollTimeSheetParameterId
                    })[0].parameters;
                }
            }
    },
    methods: {
        receiveErrors(errors) {
            const vm = this;

            let selectedParam = vm.payroll_time_sheet_parameters.find(param => vm.record.payroll_time_sheet_parameter_id == param["id"]);

            if (selectedParam && !selectedParam.total_for_period) {
                vm.errors = errors;
            }
        },
        /**
         * Método que borra todos los datos del formulario
         *
         * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
         */
        reset() {
            const vm = this;
            vm.record = {
                id: vm.record.id > 0 ? vm.record.id : "",
                from_date: "",
                to_date: "",
                payroll_supervised_group_id: "",
                payroll_time_sheet_parameter_id: "",
                supervisor: "",
                approver: "",
                time_sheet_data: {},
                time_sheet_original_data: {},
                time_sheet_columns: {},
            };
        },

        /**
         * Método que establece la cantidad de dias seleccionado en el periodo
         * para calcular los maximos
         *
         * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
         */
        setDaysInPeriod() {
            const vm = this;

            let startDate = new Date(vm.record.from_date);
            let endDate = new Date(vm.record.to_date);
            let timeDiff = Math.abs(endDate - startDate);
            let diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24));
            vm.daysInPeriod = diffDays + 1;
        },

        /**
         * Método que carga los datos del supervisor y el aprobador
         * a partir del grupo de supervisados seleccionado
         *
         * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
         */
        getSupervisedGroupData() {
            const vm = this;

            if (vm.record.payroll_supervised_group_id) {
                let group = vm.payroll_supervised_groups.find(function (
                    $group
                ) {
                    return (
                        vm.record.payroll_supervised_group_id == $group["id"]
                    );
                });

                vm.record.supervisor = group.supervisor.name;
                vm.record.approver = group.approver.name;
            } else {
                vm.record.supervisor = null;
                vm.record.approver = null;
            }
        },

        /**
         * Método que obitiene los parámetros de hoja de tiempo
         *
         * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
         */
        async getPayrollTimeSheetParameters(paramId = null) {
            const vm = this;
            vm.payroll_time_sheet_parameters = [];
            await axios
                .get(
                    `${window.app_url}/payroll/get-time-sheet-parameters`,
                    {
                        params : {
                            'from_date': vm.record.from_date,
                            'to_date': vm.record.to_date
                        }
                    }
                )
                .then((response) => {
                    vm.payroll_time_sheet_parameters = response.data;

                    if (paramId !== null) {
                        setTimeout(() => {
                            vm.record.payroll_time_sheet_parameter_id = paramId;
                        }, 500);
                    }
                });
        },

        /**
         * Método que obitiene los días feriados y domingos que hay dentro del periodo seleccionado
         *
         * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
         */
         async getHolidaysByPeriod() {
            const vm = this;
            vm.maxHolidays = [];
            await axios
                .get(`${window.app_url}/payroll/get-time-sheet-holidays`, {
                    params: {
                        from_date: vm.record.from_date,
                        to_date: vm.record.to_date,
                    },
                })
                .then((response) => {
                    vm.maxHolidays = response.data;
                });

            if (vm.record.payroll_time_sheet_parameter_id) {
                vm.setHolidaysByPeriod();
            }
        },

        /**
         * Método que establece los días feriados y domingos que hay dentro del periodo seleccionado
         * en la hoja de tiempo
         *
         * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
         */
        setHolidaysByPeriod() {
            const vm = this;

            if (!vm.$refs.draggableTable) {
                return;
            }

            Vue.set(
                vm.$refs.draggableTable,
                "maxHolidays",
                vm.maxHolidays
            );
        },

        /**
         * Método que carga las columnas de la hoja de tiempo
         *
         * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
         */
        setTimeSheetColumns() {
            const vm = this;

            if (vm.record.payroll_time_sheet_parameter_id) {
                if (!vm.record.id || vm.record.time_sheet_columns == null) {
                    let draggableColumns = [
                        {
                            name: "N°",
                            type: "text",
                            isDraggable: false,
                        },
                        {
                            name: "Ficha",
                            type: "text",
                            isDraggable: false,
                        },
                        {
                            name: "Nombre",
                            type: "text",
                            isDraggable: false,
                        },
                    ];

                    let params = vm.payroll_time_sheet_parameters.find(
                        function ($param) {
                            return (
                                vm.record.payroll_time_sheet_parameter_id ==
                                $param["id"]
                            );
                        }
                    );

                    vm.totalGroups = params.total_groups;

                    Object.values(params.parameters).forEach(
                        (param, index, array) => {
                            let group = "";
                            let groupMax = "";
                            param.forEach((p) => {
                                if ("" === group) {
                                    group = p.group;
                                    groupMax = p.max;
                                }

                                draggableColumns.push({
                                    name: p.text,
                                    group: p.group,
                                    type: p.formula != "" && params.total_for_period ? "formula" : "input",
                                    isDraggable: true,
                                    formula: p.formula,
                                    order: p.order,
                                    classification_type: p.classification_type,
                                    max: vm.setMaxPerParameter(
                                        params.total_for_period,
                                        params.breaks_allowed_per_week,
                                        p.max_value_allowed_per_time_sheet,
                                        p.classification_type,
                                    ),
                                    originalMax: vm.setMaxPerParameter(
                                        params.total_for_period,
                                        params.breaks_allowed_per_week,
                                        p.max_value_allowed_per_time_sheet,
                                        p.classification_type,
                                    )
                                });
                            });

                            draggableColumns.push({
                                name: "Subtotal",
                                type: "subtotal",
                                group: group,
                                max: groupMax,
                                isDraggable: false,
                            });
                            if (index == array.length - 1) {
                                draggableColumns.push({
                                    name: "Total",
                                    type: "total",
                                    isDraggable: false,
                                });
                            }
                        }
                    );

                    vm.draggableColumns = draggableColumns;
                } else {
                    let draggableColumns = [];

                    vm.record.time_sheet_columns.forEach((column, index) => {
                        if (column.name.includes("subtotal")) {
                            column.name = "Subtotal";
                        }

                        if (column.name == "total") {
                            column.name = "Total";
                        }

                        draggableColumns.push(column);
                    });
                    vm.draggableColumns = vm.record.time_sheet_columns;
                }
            } else {
                vm.draggableColumns = [];
            }

            vm.$refs.draggableTable.data.forEach((d, index) => {
                vm.$refs.draggableTable.setMaxPerColumn(d.staff_id, vm.daysInPeriod);
            });

            if (vm.record.id) {
                Vue.set(
                    vm.$refs.draggableTable,
                    "inputValues",
                    vm.record.time_sheet_data
                );

                Vue.set(
                    vm.$refs.draggableTable,
                    "originalInputValues",
                    vm.record.time_sheet_original_data
                );
            }
        },

        /**
         * Método que establece el máximo de un parámetro dependiendo del periodo
         *
         * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
         */
        setMaxPerParameter(total_for_period, breaks_allowed_per_week, max_parameter_value, classification_type) {
            const vm = this;

            if (!total_for_period) {
                return max_parameter_value;
            }

            const descansoRegex = /descanso(s)?/i;
            const domingoRegex = /domingo(s)?/i;
            const feriadoRegex = /feriado(s)?/i;
            const turnoSencilloRegex = /turno(s)? sencillo(s)?/i;
            let domingoMax = 0;
            let feriadoMax = 0;

            if (descansoRegex.test(classification_type) && breaks_allowed_per_week > 0) {
                // Calcular la cantidad de semanas que hay en el periodo de acuerdo al record.from_date y record.to_date
                let startDate = new Date(vm.record.from_date);
                let endDate = new Date(vm.record.to_date);
                let timeDiff = Math.abs(endDate - startDate);
                let diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24));
                let weeks = Math.floor(diffDays / 7);

                return breaks_allowed_per_week * weeks;
            }

            for (let[i, v] of Object.entries(vm.maxHolidays)) {
                domingoMax = v['domingos'];
                feriadoMax = v['feriados'];

                if (domingoRegex.test(classification_type)) {
                    // Si se encuentra una coincidencia, usar el valor correspondiente
                    return v['domingos'];
                }

                if (feriadoRegex.test(classification_type)) {
                    // Si se encuentra una coincidencia, usar el valor correspondiente
                    return v['feriados'];
                }
            }

            if (turnoSencilloRegex.test(classification_type)) {
                let startDate = new Date(vm.record.from_date);
                let endDate = new Date(vm.record.to_date);
                let timeDiffDays = Math.abs(endDate - startDate);
                let diffDays = (Math.ceil(timeDiffDays / (1000 * 3600 * 24))) + 1;

                let timeDiff = Math.abs(endDate - startDate);
                let diffDaysWeek = Math.ceil(timeDiff / (1000 * 3600 * 24));
                let weeks = Math.floor(diffDaysWeek / 7);
                let weekDays = breaks_allowed_per_week * weeks;

                return diffDays - (weekDays + feriadoMax + domingoMax);
            }

            return max_parameter_value;
        },

        /**
         * Método que carga los datos de la hoja de tiempo
         *
         * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
         */
        setTimeSheetData() {
            const vm = this;

            let draggableData = [];
            let payroll_staffs = [];
            if (vm.record.payroll_supervised_group_id) {
                let group = vm.payroll_supervised_groups.find(function (
                    $group
                ) {
                    return (
                        vm.record.payroll_supervised_group_id == $group["id"]
                    );
                });
                let index = 1;

                group.payroll_staffs.forEach((staff, indexof) => {
                    draggableData.push({
                        "N°": index++,
                        Ficha: staff.worksheet_code,
                        Nombre: staff.name,
                        staff_id: staff.id,
                        id_number: staff.id_number,
                        workload: staff.workload,
                        days: vm.daysInPeriod,
                    });
                    payroll_staffs.push({
                        "N°": index,
                        index: indexof,
                        Nombre: staff.name,
                        id_number: staff.id_number,
                        staff_id: staff.id,
                        workload: staff.workload,
                        days: vm.daysInPeriod,
                    });
                });
            }

            vm.draggableData = draggableData;
            vm.payroll_staffs_reference = payroll_staffs;
        },

        contarDiasEntreFechas(from_date_str, to_date_str) {

            let from_date = moment(from_date_str);
            let to_date = moment(to_date_str);

            return to_date.diff(from_date, 'days') + 1
        },

        /**
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

            if (vm.record.id) {
                vm.updateRecord(url);
            } else {
                vm.loading = true;
                let fields = {};

                if (
                    vm.$refs.draggableTable &&
                    typeof vm.$refs.draggableTable != "undefined"
                ) {
                    let columns = [];

                    vm.$refs.draggableTable.columns.forEach((column, index) => {
                        let updatedColumn = { ...column };

                        if (updatedColumn.name.includes("Subtotal")) {
                            updatedColumn.name =
                                "subtotal" + " - " + updatedColumn.group;
                        }

                        if (updatedColumn.name == "Total") {
                            updatedColumn.name = "total";
                        }

                        columns.push({
                            position: index,
                            name: updatedColumn.name,
                            group: updatedColumn.group,
                            type: updatedColumn.type,
                            isDraggable: updatedColumn.isDraggable,
                            max: updatedColumn.max,
                            formula: updatedColumn.formula || '',
                            order: updatedColumn.order || '',
                            classification_type: updatedColumn.classification_type || '',
                        });
                    });

                    vm.record.time_sheet_data =
                        vm.$refs.draggableTable.inputValues;
                    vm.record.time_sheet_columns = columns;
                    vm.record.time_sheet_original_data =
                        vm.$refs.draggableTable.originalInputValues;
                } else {
                    vm.record.time_sheet_data = [];
                    vm.record.time_sheet_columns = [];
                }

                for (let index in vm.record) {
                    fields[index] = vm.record[index];
                }

                if (vm.parameters) {
                    fields["parameters"] = vm.parameters;
                }

                let selectedParam = vm.payroll_time_sheet_parameters.find(param => vm.record.payroll_time_sheet_parameter_id == param["id"]);

                if (selectedParam) {
                    fields["total_for_period"] = selectedParam.total_for_period
                }

                if (selectedParam && fields["total_for_period"]) {
                    let maxtotal = vm.contarDiasEntreFechas(fields.from_date, fields.to_date);

                    const totalKeys = Object.keys(fields.time_sheet_data).filter(key => key.startsWith('total-'));

                    totalKeys.forEach(key => {
                        const value = fields.time_sheet_data[key];
                        if (value > maxtotal) {
                            vm.errors.push('El total no puede ser mayor al periodo de ' + maxtotal + ' dias establecido el rango de fechas.');
                        }
                    });
                }

                await axios
                    .post(url, fields)
                    .then((response) => {
                        if (typeof response.data.redirect !== "undefined") {
                            location.href = response.data.redirect;
                        } else {
                            vm.errors = [];
                            if (reset) {
                                vm.reset();
                            }
                            if (list) {
                                vm.readRecords(url);
                            }

                            vm.showMessage("store");
                        }
                    })
                    .catch((error) => {
                        vm.errors = [];

                        if (typeof error.response != "undefined") {
                            if (error.response.status == 403) {
                                vm.showMessage(
                                    "custom",
                                    "Acceso Denegado",
                                    "danger",
                                    "screen-error",
                                    error.response.data.message
                                );
                            }
                            for (var index in error.response.data.errors) {
                                if (error.response.data.errors[index]) {
                                    vm.errors.push(
                                        error.response.data.errors[index][0]
                                    );
                                }
                            }
                        }
                    });

                vm.loading = false;
            }
        },

        /**
         * Método que permite actualizar información
         *
         * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         * @param  {string} url Ruta de la acci´on que modificará los datos
         */
        async updateRecord(url) {
            const vm = this;
            vm.loading = true;
            var fields = {};
            url = vm.setUrl(url);

            let columns = [];

            vm.$refs.draggableTable.columns.forEach((column, index) => {
                let updatedColumn = { ...column }; // Crear una copia del objeto column
                if (updatedColumn.name.includes("Subtotal")) {
                    updatedColumn.name =
                        "subtotal" + " - " + updatedColumn.group;
                }

                if (updatedColumn.name == "Total") {
                    updatedColumn.name = "total";
                }

                columns.push({
                    position: index,
                    name: updatedColumn.name,
                    group: updatedColumn.group,
                    type: updatedColumn.type,
                    isDraggable: updatedColumn.isDraggable,
                    max: updatedColumn.max,
                    formula: updatedColumn.formula || '',
                    order: updatedColumn.order || '',
                    classification_type: updatedColumn.classification_type || '',
                });
            });

            vm.record.time_sheet_columns = columns;
            vm.record.time_sheet_data = vm.$refs.draggableTable.inputValues;
            vm.record.time_sheet_original_data = vm.$refs.draggableTable.originalInputValues;

            for (var index in vm.record) {
                fields[index] = vm.record[index];
            }

            if (vm.parameters) {
                fields["parameters"] = vm.parameters;
            }

            await axios
                .patch(
                    `${url}${url.endsWith("/") ? "" : "/"}${vm.record.id}`,
                    fields
                )
                .then((response) => {
                    if (typeof response.data.redirect !== "undefined") {
                        location.href = response.data.redirect;
                    } else {
                        vm.readRecords(url);
                        vm.reset();
                        vm.showMessage("update");
                    }
                })
                .catch((error) => {
                    vm.errors = [];

                    if (typeof error.response != "undefined") {
                        if (error.response.status == 403) {
                            vm.showMessage(
                                "custom",
                                "Acceso Denegado",
                                "danger",
                                "screen-error",
                                error.response.data.message
                            );
                        }
                        for (var index in error.response.data.errors) {
                            if (error.response.data.errors[index]) {
                                vm.errors.push(
                                    error.response.data.errors[index][0]
                                );
                            }
                        }
                    }
                });
            vm.loading = false;
        },

        /**
         * Método que carga el formulario con los datos a modificar
         *
         * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
         *
         * @param  {integer} index Identificador del registro a ser modificado
         */
        async loadForm(id) {
            let vm = this;
            vm.errors = [];
            vm.getPayrollSupervisedGroups(id, "active");
            let recordEdit = await axios
                .get(`${window.app_url}/payroll/time-sheet/vue-info/${id}`)
                .then((response) => {
                    return response.data.record;
                });

            vm.record = recordEdit;
            vm.totalGroups = recordEdit.total_groups;

            await vm.setDaysInPeriod();
            await vm.getPayrollTimeSheetParameters(recordEdit.payroll_time_sheet_parameter_id);

            if (vm.record.payroll_time_sheet_parameter_id) {
                vm.parameters = vm.payroll_time_sheet_parameters.filter((parameter) => {
                    return parameter.id == vm.record.payroll_time_sheet_parameter_id
                    })[0].parameters;
                vm.setTimeSheetColumns();
                vm.loadDataCompletedPeriods();
                vm.setHolidaysByPeriod();
                            
            }
        },

        /**
         * Metodo que permite exportar la hoja de tiempo
         *
         * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
         *
         */
        exportTimeSheetData() {
            const vm = this;
            const originalColumns = vm.$refs.draggableTable.columns;
            let columns = originalColumns.slice(0, 1);
            columns.splice(1, 0, {
                name: "Cedula",
                type: "text",
                isDraggable: true,
            });
            for (let i = 1; i < originalColumns.length; i++) {
                columns.push(originalColumns[i]);
            }
            let data = {
                columns: columns,
                data: vm.$refs.draggableTable.data,
                inputValues: vm.$refs.draggableTable.inputValues,
            };

            axios
                .post(
                    `${window.app_url}/payroll/time-sheet/export`,
                    { params: data },
                    { responseType: "blob" }
                )
                .then((response) => {
                    const url = window.URL.createObjectURL(
                        new Blob([response.data])
                    );
                    const link = document.createElement("a");
                    link.href = url;
                    link.setAttribute("download", "payroll-time-sheet.xlsx");
                    document.body.appendChild(link);
                    link.click();
                    window.URL.revokeObjectURL(url);
                })
                .catch((error) => {
                    console.error(error);
                });
        },

        /**
         * Metodo que permite importar la hoja de tiempo
         *
         * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
         *
         */
        importTimeSheetData() {
            const vm = this;
            const inputFile = document.getElementById("import_data");
            const file = inputFile.files[0];
            vm.peopleNotFound = [];
            vm.peopleFound = [];
            const formData = new FormData();
            formData.append("file", file);
            if(typeof vm.$refs.draggableTable.originalInputValues === 'undefined' ){
                vm.$refs.draggableTable.originalInputValues = vm.$refs.draggableTable.inputValues;
            }

            axios
                .post(`${window.app_url}/payroll/time-sheet/import`, formData, {
                    headers: {
                        "Content-Type": "multipart/form-data",
                    },
                })
                .then((response) => {
                    let formattedColumns = [];
                    const avalibilityItems = vm.$refs.draggableTable.data;
                    let peopleMissing = [];
                    let columns = vm.$refs.draggableTable.columns;
                    columns.forEach((column) => {
                        let columnName = column.name.toLowerCase();
                        columnName = columnName
                            .replace(/ - /g, "_")
                            .replace(/ /g, "_");

                        if (columnName === "n°") {
                            columnName = "n";
                        }
                        vm.$refs.draggableTable.data.forEach((d, index) => {
                            formattedColumns.push({
                                staff_id: d.staff_id,
                                column_name: columnName,
                                id_number: d.id_number,
                                index: index,
                                name: column.name + "-" + d.staff_id,
                                n: d["N°"],
                                disabled: column.disabled,
                                days: vm.daysInPeriod,
                            });
                        });
                    });

                    for (let i = 0; i < formattedColumns.length; i++) {
                        let col_name = formattedColumns[i].name;
                        let n = formattedColumns[i].n;
                        let id_number = formattedColumns[i].id_number;
                        let column_name = formattedColumns[i].column_name;
                        let column_disabled = formattedColumns[i].disabled;

                        let valor = response.data.find(
                            (item) =>
                                item.cedula == id_number &&
                                item[column_name] !== null
                        );


                        if (
                            valor &&
                            !col_name.includes("N°") &&
                            !col_name.includes("Nombre") &&
                            !col_name.includes("total") &&
                            !col_name.includes("Total") &&
                            column_disabled !== true
                        ) {
                            vm.peopleNotOneFound = false;
                            function removeValue(value) {
                                // Si el valor en el índice actual del array coincide con el valor especificado (2)

                                if (value.id_number === id_number) {
                                    return true; // Indica que se debe eliminar
                                }
                                return false; // Indica que se debe incluir
                            }
                            // Recorrer el array original y crear uno nuevo filtrado
                            avalibilityItems.forEach(function (value, index) {
                                if (!vm.peopleFound.some(item => item.id_number === value.id_number)) {
                                    if (
                                        vm.peopleNotFound.length == 0 &&
                                        !removeValue(value)
                                    ) {
                                        peopleMissing.push(value);
                                    } else {
                                        if (
                                            removeValue(value) &&
                                            vm.peopleNotFound.includes(value)
                                        ) {
                                            vm.peopleNotFound.splice(
                                                peopleMissing.indexOf(value),
                                                1
                                            );
                                        }
                                    }

                                    if (removeValue(value)) {
                                        vm.peopleFound.push(value)
                                    }
                                }
                            });

                            if (vm.peopleNotFound.length == 0) {
                                vm.peopleNotFound = peopleMissing;
                            }
                            Vue.set(
                                vm.$refs.draggableTable.inputValues,
                                col_name,
                                parseInt(valor[column_name])
                            );
                            Vue.set(
                                vm.$refs.draggableTable.originalInputValues,
                                col_name,
                                parseInt(valor[column_name])
                            );

                            vm.$refs.draggableTable.calculateFormula(formattedColumns[i].staff_id, vm.daysInPeriod);
                        } else {
                            vm.peopleNotOneFound = true;
                        }

                        let calcTotals = Object.entries(vm.$refs.draggableTable.calculate);

                        if (calcTotals.length > 0) {
                            calcTotals.forEach(element => {
                                if (!element[0].includes('total')) {
                                    element[0] = 'subtotal - ' + element[0];
                                }

                                Vue.set(
                                    vm.$refs.draggableTable.inputValues,
                                    element[0],
                                    parseInt(element[1])
                                );

                                if(vm.$refs.draggableTable.originalInputValues){
                                    Vue.set(
                                        vm.$refs.draggableTable.originalInputValues,
                                        element[0],
                                        parseInt(element[1])
                                    );
                                }
                            });
                        }
                    }
                    vm.showMessage(
                        "custom",
                        "!Éxito!",
                        "success",
                        "screen-ok",
                        "Se ha realizado el proceso de importación exitosamente"
                    );
                })
                .catch((error) => {
                    console.error(error);
                });
        },

        /**
         * Activa el método para importar la hoja de tiempo
         *
         * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
         *
         */
        setFile() {
            this.$refs.fileInput.click();
        },

        /**
         * Activa el método para importar la hoja de tiempo
         *
         * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
         *
         */
        async loadDataCompletedPeriods() {
            const vm = this;
            if (
                vm.record.id == '' &&
                vm.record.from_date &&
                vm.record.to_date &&
                vm.record.payroll_supervised_group_id &&
                vm.record.payroll_supervised_group_id != 0 &&
                vm.record.payroll_time_sheet_parameter_id &&
                vm.record.payroll_time_sheet_parameter_id != 0
            ) {
                if (vm.loadingData) {
                    return false;
                }
                vm.loadingData = true;
                if (
                    vm.$refs.draggableTable &&
                    typeof vm.$refs.draggableTable != "undefined"
                ) {
                    await axios
                        .get(
                            `${window.app_url}/payroll/guard-schemes/get-payroll-confirmed-periods`,
                            {
                                params: {
                                    from_date: vm.record.from_date,
                                    to_date: vm.record.to_date,
                                    payroll_supervised_group_id:
                                        vm.record.payroll_supervised_group_id,
                                    payroll_time_sheet_parameter_id:
                                        vm.record
                                            .payroll_time_sheet_parameter_id,
                                },
                            }
                        )
                        .then((response) => {
                            if (
                                response.data.result !== null
                            ) {
                                bootbox.confirm({
                                    title: "¿Cargar registros de periodos confirmados?",
                                    message: `<p class='text-justify'>
                                                Se han encontrado registros de periodos confirmados que coinciden con los datos suministrados para la hoja de tiempo. 
                                                ¿Desea cargar estos registros?
                                            </p>
                                            <p class='text-justify'>
                                                <b class='text-red'>¡ATENCIÓN!</b>
                                                Este proceso puede ocasionar perdida parcial o total de los datos, proceda si esta
                                                totalmente seguro.
                                            </p>`,
                                    buttons: {
                                        cancel: {
                                            label: '<i class="fa fa-times"></i> Cancelar',
                                        },
                                        confirm: {
                                            label: '<i class="fa fa-check"></i> Confirmar',
                                        },
                                    },
                                    callback: (result) => {
                                        if (result) {
                                            /** Muestra el mensaje de espera */
                                            $(".preloader").show();
                                            vm.draggableColumns.forEach(
                                                (column) => {
                                                    if (
                                                        column.type == "input"
                                                    ) {
                                                        Vue.set(
                                                            column,
                                                            "disabled",
                                                            (response.data
                                                                .confirmed
                                                                ? response.data
                                                                    .confirmed
                                                                : []
                                                            ).includes(
                                                                column.name
                                                            )
                                                        );
                                                    }
                                                }
                                            );
                                            for (let key in response.data
                                                .result) {
                                                const lastIndex =
                                                    key.lastIndexOf("-");
                                                const index = key
                                                    .slice(0, lastIndex)
                                                    .trim();
                                                const staffId = key
                                                    .slice(lastIndex + 1)
                                                    .trim();
                                                const foundColumn =
                                                    vm.draggableColumns.some(
                                                        (obj) =>
                                                            obj.name == index
                                                    );
                                                const foundStaff =
                                                    vm.draggableData.some(
                                                        (obj) =>
                                                            obj.staff_id ==
                                                            staffId
                                                    );

                                                if (foundColumn && foundStaff) {
                                                    Vue.set(
                                                        vm.$refs.draggableTable
                                                            .inputValues,
                                                        key,
                                                        response.data.result[
                                                        key
                                                        ]
                                                    );

                                                    Vue.set(
                                                        vm.$refs.draggableTable
                                                            .originalInputValues,
                                                        key,
                                                        response.data.result[
                                                        key
                                                        ]
                                                    );

                                                    vm.$refs.draggableTable.calculateFormula(staffId, vm.daysInPeriod);
                                                }
                                            }

                                            /** Oculta el mensaje de espera */
                                            $(".preloader").fadeOut(2000);
                                            vm.loadingData = false;
                                        } else {
                                            vm.loadingData = false;
                                        }
                                    },
                                });
                            }
                        });
                }
            } else {
                vm.loadingData = false;
                return false;
            }
            vm.loadingData = false;
        },
    },

    created() {
        const vm = this;

        if (vm.payroll_time_sheet_id) {
            vm.loadForm(vm.payroll_time_sheet_id);
        } else {
            vm.getPayrollSupervisedGroups();
        }
    },
};
</script>
