<template>
    <section id="payrollTimeSheetParameterFormComponent">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary" href=""
           title="Registros de parámetros de la hoja de tiempo" data-toggle="tooltip"
           @click="addRecord('add_payroll_time_sheet_parameters', 'payroll/time-sheet-parameters', $event)">
           <i class="icofont icofont-abacus-alt ico-3x"></i>
           <span>Parámetros de<br>Hoja de Tiempo</span>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" id="add_payroll_time_sheet_parameters">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-abacus-alt ico-3x"></i>
                            Parámetros de la hoja de tiempo
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
                        <div class="row">
                            <div class="col-md-6">
                                <!-- Código -->
                                <div class="form-group is-required">
                                    <label for="parameter_code">Código:</label>
                                    <input type="text" id="parameter_code" placeholder="Código"
                                           class="form-control input-sm" v-model="record.code" data-toggle="tooltip"
                                           title="Indique el Código del parámetro (requerido)">
                                    <input type="hidden" name="id" id="id" v-model="record.id">
                                </div>
                                <!-- ./Código -->
                            </div>
                            <div class="col-md-6">
                                <!-- Nombre -->
                                <div class="form-group is-required">
                                    <label for="parameter_name">Nombre:</label>
                                    <input type="text" id="parameter_name" placeholder="nombre"
                                           v-is-text class="form-control input-sm" v-model="record.name"
                                           data-toggle="tooltip" title="Indique el nombre del parámetro (requerido)">
                                </div>
                                <!-- ./Nombre -->
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <!-- Descripción -->
                                <div class="form-group">
                                    <label for="parameter_description">Descripción:</label>
                                    <ckeditor
                                        id="description"
                                        class="form-control"
                                        data-toggle="tooltip"
                                        name="description"
                                        tag-name="textarea"
                                        title="Indique la descripción del parámetro"
                                        :config="ckeditor.editorConfig"
                                        :editor="ckeditor.editor"
                                        v-model="record.description"
                                    ></ckeditor>
                                </div>
                                <!-- ./Descripción -->
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label>Parámetros</label>
                                    <v-multiselect
                                        data-toggle="tooltip"
                                        title="Indique los parámetros a utilizar en la hoja de tiempo"
                                        track_by="text"
                                        :hide_selected="false"
                                        :options="time_parameters"
                                        :group_values="'group'"
                                        :group_label="'label'"
                                        :group_select="true"
                                        v-model="record.time_parameters"
                                    >
                                    </v-multiselect>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label>Tipo de nómina</label>
                                    <v-multiselect
                                        data-toggle="tooltip"
                                        title="Indique los tipos de pago en los que aplican estos parámetros"
                                        track_by="text"
                                        :hide_selected="false"
                                        :options="payroll_payment_types"
                                        v-model="record.payment_types"
                                    >
                                    </v-multiselect>
                                </div>
                            </div>

                            <!-- Categorías de hoja de tiempo -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Categorías de hoja de tiempo que inciden en el total</label>
                                    <v-multiselect
                                        data-toggle="tooltip"
                                        title="Indique las categorías en los que aplican estos parámetros"
                                        track_by="text"
                                        :hide_selected="false"
                                        :options="exception_types"
                                        v-model="record.exception_types"
                                    >
                                    </v-multiselect>
                                </div>
                            </div>

                            <!-- Validar el total respecto al periodo -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="validate_total_for_period">¿Validar el total respecto al periodo?</label>
                                    <div class="col-12">
                                        <div class="custom-control custom-switch" data-toggle="tooltip"
                                                title="¿Validar el total respecto al periodo?">
                                            <input type="checkbox" class="custom-control-input" id="validate_total_for_period"
                                                    v-model="record.validate_total_for_period" :value="true">
                                            <label class="custom-control-label" for="validate_total_for_period"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="breaks_allowed_per_week">Cantidad de descansos permitidos por semana:</label>
                                    <input id="breaks_allowed_per_week" class="form-control input-sm" type="text"
                                           data-toggle="tooltip" placeholder="Valor máximo"
                                           title="Indique el valor máximo del parámetro"
                                           v-model="record.breaks_allowed_per_week"
                                           v-input-mask data-inputmask="
                                                'alias': 'numeric',
                                                'allowMinus': 'false'
                                           "
                                    />
                                </div>
                            </div>

                            <div class="col-md-12">
                                <!-- Orden de evaluación de parámetros -->
                                 <div>
                                    <h6 class="card-title">
                                        Orden de evaluación de parámetros para la resta de excedentes&#160;
                                        <i
                                            class="fa fa-plus-circle cursor-pointer" @click="addEvaluationOrder"
                                            title="Agregar orden de evaluación" data-toggle="tooltip"
                                        ></i>
                                    </h6>
                                    <div class="row">
                                        <div v-for="(order, index) in record.evaluation_orders" :key="index" class="col-md-12">
                                            <label><strong>Orden {{ index + 1 }}:</strong></label>
                                            <div>
                                                <div class="d-flex form-group">
                                                    <select
                                                        v-model="record.evaluation_orders[index]"
                                                        class="form-control select2"
                                                        style="padding-bottom: 0px;"
                                                        data-toggle="tooltip"
                                                        title="Seleccione las clasificaciones para este orden de evaluación"
                                                    >
                                                        <option
                                                            v-for="option in availableClassificationTypes(index)"
                                                            :key="option.id"
                                                            :value="option.id"
                                                        >
                                                            {{ option.text }}
                                                        </option>
                                                    </select>
                                                    <button
                                                        class="btn btn-sm btn-danger btn-action ml-2"
                                                        type="button"
                                                        @click="removeRow(index, 'evaluation_orders')"
                                                        title="Eliminar este dato"
                                                        data-toggle="tooltip"
                                                    >
                                                        <i class="fa fa-minus-circle"></i>
                                                    </button>
                                                </div>
                                                <div class="card-title" v-if="record.evaluation_orders[index] !== ''">
                                                    Agregar un parametro del orden de evaluación
                                                    <i
                                                        class="fa fa-plus-circle cursor-pointer d-inline"
                                                        @click="addEvaluationOrderParameter(index)"
                                                        title="Agregar un parametro del orden de evaluación"
                                                        data-toggle="tooltip"
                                                    ></i>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div
                                                    v-for="(param, idx) in record.evaluation_orders_parameters[index]"
                                                    :key="`order_${index}_${idx}`"
                                                    class="col-md-4"
                                                >
                                                    <div class="d-flex">
                                                        <select
                                                            v-model="record.evaluation_orders_parameters[index][idx]"
                                                            class="form-control select2"
                                                            data-toggle="tooltip"
                                                            title="Seleccione las clasificaciones para este orden de evaluación"
                                                        >
                                                            <option
                                                                v-for="option in availableParametersForOrder(index, idx)"
                                                                :key="option.id"
                                                                :value="option.id"
                                                            >
                                                                {{ option.text }}
                                                            </option>
                                                        </select>
                                                        <button
                                                            class="btn btn-sm btn-danger btn-action ml-2"
                                                            type="button"
                                                            @click="removeRow(idx, 'evaluation_orders_parameters', index)"
                                                            title="Eliminar este dato"
                                                            data-toggle="tooltip"
                                                        >
                                                            <i class="fa fa-minus-circle"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
							<button type="button" @click="createRecord('payroll/time-sheet-parameters')" 
									class="btn btn-primary btn-sm btn-round btn-modal-save">
								Guardar
							</button>
                        </div>
                    </div>
                    <div class="modal-body modal-table">
                        <v-client-table :columns="columns" :data="records" :options="table_options">
                            <div slot="time_parameters" slot-scope="props">
                                <div v-for="param in props.row.payroll_parameter_time_sheet_parameters" :key="param.parameter_id">
                                    <span>
                                        {{
                                            JSON.parse(param.parameter.p_value).acronym + ' - ' +
                                            JSON.parse(param.parameter.p_value).name
                                        }}
                                    </span>
                                </div>
                            </div>
                            <div slot="payment_types" slot-scope="props">
                                <div v-for="p_type in props.row.payroll_payment_type_time_sheet_parameters" :key="p_type.payroll_payment_type_id">
                                    <span>
                                        {{
                                            p_type.payroll_payment_type.code + ' - ' +
                                            p_type.payroll_payment_type.name
                                        }}
                                    </span>
                                </div>
                            </div>
                            <div slot="exception_types" slot-scope="props">
                                <div v-for="p_type in props.row.payroll_exception_type_time_sheet_parameters" :key="p_type.payroll_exception_type_id">
                                    <span>
                                        {{
                                            p_type.payroll_exception_type.name
                                        }}
                                    </span>
                                </div>
                            </div>
                            <div slot="id" slot-scope="props" class="text-center">
                                <button @click="initUpdate(props.row.id, $event)"
                                        class="btn btn-warning btn-xs btn-icon btn-action"
                                        title="Modificar registro" data-toggle="tooltip" type="button">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button @click="deleteRecord(props.row.id, 'payroll/time-sheet-parameters')"
                                        class="btn btn-danger btn-xs btn-icon btn-action"
                                        title="Eliminar registro" data-toggle="tooltip"
                                        type="button">
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
    export default {
        data() {
            return {
                record: {
                    id: '',
                    code: '',
                    name: '',
                    description: '',
                    time_parameters: [],
                    payment_types: [],
                    exception_types: [],
                    validate_total_for_period: false,
                    breaks_allowed_per_week: 0,
                    evaluation_orders: [],
                    evaluation_orders_parameters: [],
                },
                classification_types: [],
                evaluation_orders_parameters: {},
                classification_types_group: {},
                time_parameters_ungroup: [],
                errors:  [],
                records: [],
                time_parameters: [],
                exception_types: [],
                old_record_exception_types: [],
                payroll_payment_types: [],
                columns: ['code', 'name', 'time_parameters', 'payment_types', 'exception_types', 'id'],
            }
        },
        methods: {
            /**
             * Método que elimina un registro de la tabla
             */
            removeRow(index, prop, subIndex = null) {
                console.log(index, prop, subIndex);
                
                const vm = this;
                if (subIndex !== null) {
                    vm.record[prop][subIndex].splice(index, 1);
                    console.log(vm.record[prop][subIndex]);
                }
                else {
                    vm.record[prop].splice(index, 1);
                    console.log(vm.record[prop]);
                }

                vm.$forceUpdate();
            },

            /**
             * Método que borra todos los datos del formulario
             *
             * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
             */
            reset() {
                const vm  = this;
                vm.record = {
                    id: '',
                    code: '',
                    name: '',
                    description: '',
                    time_parameters: [],
                    payment_typess: [],
                    evaluation_orders: [],
                    evaluation_orders_parameters: [],
                    exception_types: [],
                    validate_total_for_period: false,
                    breaks_allowed_per_week: 0,
                };
            },

            /**
             * Obtiene los datos de los trabajadores registrados agrupados por departamento
             *
             * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
             * 
             */
            async getPayrollTimeParameters() {
                this.time_parameters = [];
                await axios.get(`${window.app_url}/payroll/get-time-parameters?setting=true`).then(response => {
                    this.time_parameters = Object.values(response.data);
                });
            },

            /**
             * Obtiene y agrupa los parametros segun su clasificacion
             *
             * @author Juan Rosas <jrosasr@cenditel.gob.ve>
             * 
             */
            async getPayrollTimeParametersUnGroup() {
                this.time_parameters_ungroup = [];
                await axios.get(`${window.app_url}/payroll/get-time-parameters?setting=true&group=false`).then(response => {
                    this.time_parameters_ungroup = Object.values(response.data);
                });

                this.getPayrollParameterByClassification()
            },

            /**
             * Obtiene los datos de las categorias de hoja de tiempo registrados
             * 
             * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
             */
            async getPayrollExceptionTypes() {
				const vm = this;
				await axios.get(`${window.app_url}/payroll/get-exception-types`).then(response => {
					vm.exception_types = response.data;
				});
			},

            async getPayrollClassificationTypes() {
                const vm = this;
                if (vm.record.exception_types.length > 0) {
                    await axios.get(`${window.app_url}/payroll/classification-parameters/vue-list`,
                        {
                        params: {
                            exception_type_id: vm.record.exception_types.map(val => val.id)
                        }
                    }
                    ).then(response => {
                        vm.classification_types = response.data;
                    });
                }
            },

            /**
             * Método que carga el formulario con los datos a modificar
             *
             * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             *
             * @param  {integer} index Identificador del registro a ser modificado
             * @param {object} event   Objeto que gestiona los eventos
             */
            async initUpdate(id, event) {
                let vm = this;
                vm.errors = [];

                let recordEdit = await JSON.parse(JSON.stringify(vm.records.filter((rec) => {
                    return rec.id === id;
                })[0])) || vm.reset();

                vm.record = recordEdit;
                event.preventDefault();
                vm.record.time_parameters = [];
                vm.record.payment_types = [];
                vm.record.exception_types = [];
                vm.record.evaluation_orders = [];
                vm.record.evaluation_orders_parameters = [];

                for (const value of recordEdit.payroll_parameter_time_sheet_parameters) {
                    let pValue = JSON.parse(value.parameter.p_value);
                    vm.record.time_parameters.push({
                        id: value.parameter_id,
                        text: pValue.acronym + ' - ' + pValue.name
                    })
                }

                for (const value of recordEdit.payroll_payment_type_time_sheet_parameters) {
                    let pType = value.payroll_payment_type;
                    vm.record.payment_types.push({
                        id: value.payroll_payment_type_id,
                        text: pType.code + ' - ' + pType.name
                    })
                }

                for (const value of recordEdit.payroll_exception_type_time_sheet_parameters) {
                    let pType = value.payroll_exception_type;
                    vm.record.exception_types.push({
                        id: value.payroll_exception_type_id,
                        text: pType.name
                    })
                }

                for (let key = 0; key < recordEdit.classification_parameters.length; key++) {
                    const value = recordEdit.classification_parameters[key];
                    
                    vm.record.evaluation_orders.push(value.id)

                    if (!vm.record.evaluation_orders_parameters[key]) {
                        vm.record.evaluation_orders_parameters.push([]);
                    }

                    let last = vm.record.evaluation_orders_parameters.length - 1
                    let pivot =  recordEdit.classification_parameter_pivots[last];
                    
                    for (let index = 0; index < pivot.parameter_order.length; index++) {
                        const pivotParam = pivot.parameter_order[index];
                        vm.record.evaluation_orders_parameters[last].push(pivotParam.parameter_id)
                    }
                }
            },
            /**
             * Agrega un nuevo parametro al orden de evaluación
             */
             addEvaluationOrderParameter(index) {
                const vm = this;
                
                if (!vm.record.evaluation_orders_parameters[index]) {
                    vm.record.evaluation_orders_parameters.push([])
                }

                let lastPosition = vm.record.evaluation_orders_parameters[index]?.length || 0;

                vm.errors = [];

                if (!lastPosition || lastPosition == 0) {
                    vm.record.evaluation_orders_parameters[index].push("");
                }
                else if (lastPosition > 0 && vm.record.evaluation_orders_parameters[index][lastPosition - 1] != '') {
                    vm.record.evaluation_orders_parameters[index].push("");
                } else {
                    vm.errors.push('No se pueden agregar más parametros, mientras la última opción este vacia.');
                }
            },
            addEvaluationOrder() {
                const vm = this;
                let lastPosition = vm.record.evaluation_orders.length;
                
                vm.errors = [];

                if (lastPosition == 0) {
                    vm.record.evaluation_orders.push("");
                }
                else if (lastPosition > 0 && vm.record.evaluation_orders[lastPosition - 1] != '') {
                    vm.record.evaluation_orders.push("");
                } else {
                    vm.errors.push('No se pueden agregar más órdenes de evaluación, mientras la última opción este vacia.');
                }
            },
            getPayrollParameterByClassification() {
                const vm = this;

                for (let idx = 0; idx < vm.time_parameters_ungroup.length; idx++) {
                    let tParam = vm.time_parameters_ungroup[idx];

                    if (tParam.classification_type !== null) {
                        vm.classification_types_group[tParam.classification_type] = vm.classification_types_group[tParam.classification_type] || [];

                        vm.classification_types_group[tParam.classification_type].push({
                            id: tParam.id,
                            text: tParam.name
                        });
                    }
                }
            }
        },
        created() {
            const vm = this;
            vm.table_options.headings = {
                'code': 'Código',
                'name': 'Nombre',
                'time_parameters': 'Parámetros',
                'payment_types': 'Tipos de pago',
                'exception_types': 'Categorías',
                'id': 'Acción'
            };
            vm.table_options.sortable       = ['code', 'name', 'time_parameters', 'payment_types', 'exception_types'];
            vm.table_options.filterable     = ['code', 'name', 'time_parameters', 'payment_types', 'exception_types'];
            vm.table_options.columnsClasses = {
                'code': 'col-xs-1',
                'name': 'col-xs-2',
                'time_parameters': 'col-xs-2',
                'payment_types': 'col-xs-3',
                'exception_types': 'col-xs-2',
                'id': 'col-xs-2'
            };
        },
        mounted() {
            const vm = this;
            $("#add_payroll_time_sheet_parameters").on('show.bs.modal', function() {
                vm.getPayrollPaymentTypes();
                vm.getPayrollTimeParameters();
                vm.getPayrollTimeParametersUnGroup();
                vm.getPayrollExceptionTypes();
                vm.getPayrollClassificationTypes();
                $('.select2').select2({});
            });
        },
        computed: {
            classification_types_computed() {
                const vm = this;
                return vm.classification_types.map((type) => {
                    return {
                        ...type,
                        disabled: vm.record.evaluation_orders.includes(String(type.id))
                    };
                });
            },
            availableClassificationTypes() {
                return (currentIndex) => {
                    const selectedIds = this.record.evaluation_orders
                        .filter((item, index) => index !== currentIndex && item !== '')
                        .map(selectedId => {
                            const found = this.classification_types.find(item => item.id === selectedId);
                            return found ? found.id : null;
                        })
                        .filter(id => id !== null);

                    return this.classification_types.filter(option => !selectedIds.includes(option.id));
                };
            },
            // Computed property for the second set of select elements (parameters within each order)
            availableParametersForOrder() {
                return (orderIndex, paramIndex) => {
                    const selectedIdsInOrder = (this.record.evaluation_orders_parameters[orderIndex] || [])
                        .filter((item, index) => index !== paramIndex && item !== '')
                        .map(selectedId => {
                            const found = (this.classification_types_group[this.record.evaluation_orders[orderIndex]] || []).find(item => item.id === selectedId);
                            return found ? found.id : null;
                        })
                        .filter(id => id !== null);

                    const groupOptions = this.classification_types_group[this.record.evaluation_orders[orderIndex]] || [];
                    return groupOptions.filter(option => !selectedIdsInOrder.includes(option.id));
                };
            },

        },
        watch: {
            record: {
                handler: function (newValue) {
                    const vm = this;
                    if (vm.old_record_exception_types.length !== vm.record.exception_types) {
                        vm.getPayrollClassificationTypes()
                        vm.old_record_exception_types = vm.record.exception_types
                    }
                },
                deep: true,
            },
        },
    };
</script>
