<template>
    <section id="PayrollSocioeconomicForm">
        <div class="card-body">
            <div class="alert alert-danger" v-if="errors.length > 0">
                <div class="container">
                    <div class="alert-icon">
                        <i class="now-ui-icons objects_support-17"></i>
                    </div>
                    <strong>Cuidado!</strong>
                        Debe verificar los siguientes errores antes de continuar:
                    <button
                        type="button"
                        class="close"
                        data-dismiss="alert"
                        aria-label="Close"
                        @click.prevent="errors = []"
                    >
                        <span aria-hidden="true">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </span>
                    </button>
                    <ul>
                        <li
                            v-for="(error, index) in errors"
                            :key="index"
                        >
                            {{ error }}
                        </li>
                    </ul>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4" id="helpSocioeconomicStaff">
                    <div class="form-group is-required">
                        <label for="payroll_staff_id">Trabajador:</label>
                        <select2
                            id="payroll_staff_id"
                            :options="payroll_socioeconomic"
                            v-model="record.payroll_staff_id"
                            :disabled="isEditMode"
                        ></select2>
                        <input type="hidden" v-model="record.id">
                    </div>
                </div>

                <div
                    class="col-md-4"
                    id="helpSocioeconomicMaritalStatus"
                    v-if="marital_status.length > 0"
                >
                    <div class="form-group is-required">
                        <label for="marital_status_id">Estado Civil:</label>
                        <select2
                            id="marital_status_id"
                            :options="marital_status"
                            v-model="record.marital_status_id"
                        ></select2>
                    </div>
                </div>
            </div>
            <hr>
            <h6 class="card-title" id="helpSocioeconomicChildren">
                Carga Familiar
                <i
                    class="cursor-pointer fa fa-plus-circle"
                    @click="addPayrollChildren"
                ></i>
            </h6>
            <div
                class="row"
                v-for="(payroll_children, index) in record.payroll_childrens"
                :key="index"
            >
                <div class="col-4">
                    <div
                        class="form-group is-required"
                        id="helpChildSchoolingLevelname"
                        v-if="payroll_relationships.length > 0"
                    >
                        <label for="payroll_relationships_id">Parentesco</label>
                        <select2
                            id="payroll_relationships_id"
                            :options="payroll_relationships"
                            v-model="payroll_children.payroll_relationships_id"
                            @input="
                                setRelationships(
                                    index,
                                    payroll_children.payroll_relationships_id
                                )
                            "
                        >
                        </select2>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group is-required">
                        <label for="payroll_children_first_name">Nombres:</label>
                        <input
                            id="payroll_children_first_name"
                            type="text"
                            placeholder="Nombres de familiar"
                            data-toggle="tooltip"
                            title="Indique nombres de familiar"
                            v-model="payroll_children.first_name"
                            class="form-control input-sm"
                        >
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group is-required">
                        <label for="payroll_children_last_name">Apellidos:</label>
                        <input
                            id="payroll_children_last_name"
                            type="text"
                            placeholder="Apellidos de familiar"
                            data-toggle="tooltip"
                            title="Indique apellidos de familiar"
                            v-model="payroll_children.last_name"
                            class="form-control input-sm"
                        >
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group is-required">
                        <label for="birthdate">Fecha de Nacimiento:</label>
                        <input
                            id="birthdate"
                            type="date"
                            placeholder="Fecha de Nacimiento"
                            data-toggle="tooltip"
                            title="Indique la fecha de nacimiento"
                            v-model="payroll_children.birthdate"
                            @change="setAge(index)"
                            class="form-control input-sm"
                        >
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group is-required">
                        <label for="age">Edad:</label>
                        <input
                            type="text"
                            data-toggle="tooltip"
                            readonly tabindex="-1"
                            title="Indique la Edad"
                            id="age"
                            name="age"
                            min="1"
                            max="3"
                            placeholder="0"
                            v-model="payroll_children.age"
                            class="form-control input-sm"
                        >
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label for="id_number">Cédula de identidad :</label>
                        <input
                            id="id_number"
                            type="text"
                            placeholder="Cédula de Identidad"
                            data-toggle="tooltip"
                            title="Indique la cédula de indentidad "
                            v-model="payroll_children.id_number"
                            class="form-control input-sm"
                            v-is-digits
                        >
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group is-required" id="helpChildSchoolingLevelname" v-if="genders.length > 0">
                        <div class="form-group is-required">
                        <label for="payroll_gender_id">Género</label>
                        <select2
                            id="payroll_gender_id"
                            :options="genders"
                            v-model="payroll_children.payroll_gender_id"
                        ></select2>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label for="address">Direccion:</label>
                        <input
                            id="address"
                            type="text"
                            placeholder="Direccion"
                            data-toggle="tooltip"
                            title="Indique los Direccion"
                            v-model="payroll_children.address"
                            class="form-control input-sm"
                        >
                    </div>
                </div>
                <div
                    class="col-4"
                    v-if="payroll_children.payroll_relationship.name == 'Hijo(a)'"
                >
                </div>
                <div
                    class="col-3"
                    v-if="payroll_children.payroll_relationship.name == 'Hijo(a)'"
                >
                    <div class="row col-md-6">
                        <div class="form-group">
                            <label :for="`mySwicth${index}`">¿Es estudiante?</label>
                            <div
                                class="custom-control custom-switch"
                                data-toggle="tooltip"
                                title="Indique si el hijo es estudiante o no"
                            >
                                <input
                                    type="checkbox"
                                    class="custom-control-input"
                                    :id="`mySwicth${index}`"
                                    v-model="payroll_children.is_student"
                                    :value="true"
                                >
                                <label
                                    class="custom-control-label"
                                    :for="`mySwicth${index}`"
                                >&nbsp;</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-14" v-if="payroll_children.is_student">
                        <div
                            class="form-group is-required"
                            id="helpChildSchoolingLevelname"
                            v-if="payroll_schooling_levels.length > 0"
                        >
                            <label for="payroll_schooling_level_id">¿Nivel de escolaridad?</label>
                            <select2
                                id="payroll_schooling_level_id"
                                :options="payroll_schooling_levels"
                                v-model="
                                    payroll_children.payroll_schooling_level_id
                                "
                            >
                            </select2>
                        </div>
                        <div class="form-group is-required">
                            <label for="study_center">Centro de estudio</label>
                            <input
                                id="study_center"
                                type="text"
                                placeholder="Nombre del centro de estudio"
                                data-toggle="tooltip"
                                title="Indique el nombre del centro de estudio"
                                v-model="payroll_children.study_center"
                                class="form-control input-sm"
                            >
                        </div>
                    </div>
                    <br>
                </div>
                <div
                    class="col-3"
                    v-if="
                        payroll_children.payroll_relationship.name
                        == 'Hijo(a)' && payroll_children.is_student
                    "
                >
                    <div class="row col-md-6">
                        <div class="form-group">
                            <label>¿Posee una Beca?</label>
                            <div
                                class="custom-control custom-switch"
                                data-toggle="tooltip"
                                title="Indique si el hijo Posee una beca o no"
                            >
                                <input
                                    type="checkbox"
                                    class="custom-control-input"
                                    :id="`has_scholarships${index}`"
                                    v-model="payroll_children.has_scholarships"
                                    :value="true"
                                >
                                <label
                                    class="custom-control-label"
                                    :for="`has_scholarships${index}`"
                                ></label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-14" v-if="payroll_children.has_scholarships">
                        <div
                            class="form-group is-required"
                            id="helphas_scholarships"
                            v-if="payroll_scholarship_types.length > 0"
                        >
                            <label for="payroll_scholarship_types_id">¿Tipo de beca?</label>
                            <select2
                                id="payroll_scholarship_types_id"
                                :options="payroll_scholarship_types"
                                v-model="
                                    payroll_children.payroll_scholarship_types_id
                                "
                            >
                            </select2>
                        </div>
                    </div>
                    <br>
                </div>
                <div class="col-3">
                    <div class="row col-md-10">
                        <div class="form-group">
                            <label>¿Posee una Discapacidad?</label>
                            <div
                                class="custom-control custom-switch"
                                data-toggle="tooltip"
                                title="
                                    Indique si el trabajador posee una discapacidad o no
                                "
                            >
                                <input
                                    type="checkbox"
                                    class="custom-control-input sel_has_disability"
                                    :id="`has_disability${index}`"
                                    :name="`has_disability${index}`"
                                    v-model="payroll_children.has_disability"
                                    :value="true"
                                >
                                <label
                                    class="custom-control-label"
                                    :for="`has_disability${index}`"
                                ></label>
                            </div>
                        </div>
                    </div>
                    <div
                        class="col-md-14"
                        id="helpChildDisabilityName"
                        v-if="
                            payroll_children.has_disability
                            && payroll_disabilities.length > 0
                        "
                    >
                        <div class="form-group is-required">
                            <label for="payroll_disability_id">Discapacidad</label>
                            <select2
                                id="payroll_disability_id"
                                :options="payroll_disabilities"
                                v-model="payroll_children.payroll_disability_id"
                            >
                            </select2>
                        </div>
                    </div>
                </div>
                <div class="row col-1">
                    <div class="form-group">
                        <br>
                        <button
                            class="btn btn-sm btn-danger btn-action"
                            type="button"
                            @click="removeRow(index, record.payroll_childrens)"
                            title="Eliminar este dato"
                            data-toggle="tooltip"
                            data-placement="right"
                        >
                            <i class="fa fa-minus-circle"></i>
                        </button>
                    </div>
                </div>
            </div>
            <h6 class="card-title" id="helpSocioeconomicChildren">
               Familiar Sobreviviente
            </h6>
        <div class="row">
                <div class="col-4">
                    <div class="form-group is-required">
                        <label for="survivor_first_name">Nombres:</label>
                        <input
                            id="survivor_first_name"
                            type="text"
                            placeholder="Nombres de familiar"
                            data-toggle="tooltip"
                            title="Indique nombres de familiar"
                            v-model="record.survivor_first_name"
                            class="form-control input-sm"
                        >
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group is-required">
                        <label for="survivor_last_name">Apellidos:</label>
                        <input
                            id="survivor_last_name"
                            type="text"
                            placeholder="Apellidos de familiar"
                            data-toggle="tooltip"
                            title="Indique apellidos de familiar"
                            v-model="record.survivor_last_name"
                            class="form-control input-sm"
                        >
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group is-required">
                        <label for="survivor_id_number">Cédula de identidad :</label>
                        <input
                            id="survivor_id_number"
                            type="text"
                            placeholder="Cédula de Identidad"
                            data-toggle="tooltip"
                            title="Indique la cédula de indentidad "
                            v-model="record.survivor_id_number"
                            class="form-control input-sm"
                            v-is-digits
                        >
                    </div>
                </div>
                <div
                    class="col-md-4"
                    id="helpFinancialBank"
                >
                    <div class="form-group is-required">
                        <label for="finance_bank_id">Banco:</label>
                        <select2
                            :options="banks"
                            id="finance_bank_id"
                            v-model="record.survivor_finance_bank_id">
                        </select2>
                    </div>
                </div>
                <div class="col-md-4" id="helpFinancialTypeAccount">
                    <div
                        class="form-group is-required"
                    >
                        <label for="finance_account_type_id">Tipo de Cuenta:</label>
                        <select2
                            :options="account_types"
                            id="finance_account_type_id"
                            v-model="record.survivor_finance_account_type_id">
                        </select2>
                    </div>
                </div>
                <div class="col-md-4" id="helpFinancialAccountNumber">
                    <div class="form-group is-required">
                        <label for="bank_code">Número de cuenta:</label>
                        <input
                            type="text"
                            class="form-control input-sm"
                            id="bank_code"
                            v-model="record.survivor_payroll_account_number"
                            v-input-mask data-inputmask-regex="[0-9]*"
                        >
                    </div>
                </div>
            </div>
        </div>

        <div class="text-right card-footer" id="helpParamButtons">
            <button
                class="btn btn-default btn-icon btn-round"
                data-toggle="tooltip"
                type="button"
                title="Borrar datos del formulario"
                @click="reset"
            >
                <i class="fa fa-eraser"></i>
            </button>
            <button
                type="button"
                class="btn btn-warning btn-icon btn-round"
                data-toggle="tooltip"
                title="Cancelar y regresar"
                @click="redirect_back(route_list)"
            >
                <i class="fa fa-ban"></i>
            </button>
            <button
                type="button"
                @click="createRecord('payroll/socioeconomics')"
                data-toggle="tooltip"
                title="Guardar registro"
                class="btn btn-success btn-icon btn-round"
            >
                <i class="fa fa-save"></i>
            </button>
        </div>
    </section>
</template>

<script>
    export default {
        props: {
            payroll_socioeconomic_id: Number,
        },
        data() {
            return {
                record: {
                    id: '',
                    payroll_staff_id: '',
                    marital_status_id: '',
                    payroll_childrens: [],
                    survivor_first_name: '',
                    survivor_last_name: '',
                    survivor_id_number: '',
                    survivor_finance_account_type_id: '',
                    survivor_payroll_account_number: '',
                    survivor_finance_bank_id: '',
                },
                errors: [],
                payroll_socioeconomic: [],
                marital_status: [],
                payroll_relationships: [],
                payroll_scholarship_types: [],
                payroll_schooling_levels: [],
                genders: [],
                banks: [],
                account_types: [],
                payroll_childrens: [],
                payroll_disabilities: [],
                isEditMode: false,
            }
        },
        methods: {
            /**
             * Método que borra todos los datos del formulario
             *
             * @author  William Páez <wpaez@cenditel.gob.ve>
             */
            reset() {
                this.record = {
                    id: '',
                    payroll_staff_id: '',
                    marital_status_id: '',
                    payroll_childrens: [],
                    survivor_first_name: '',
                    survivor_last_name: '',
                    survivor_id_number: '',
                    survivor_finance_account_type_id: '',
                    survivor_payroll_account_number: '',
                    survivor_finance_bank_id: '',
                };
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
                }
                else {
                    vm.loading = true;
                    var fields = {};

                    for (var index in vm.record) {
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
                            if (error.response.data.error_code == "ACC_DEL_EXISTS_001") {
                                vm.showMessage('custom', 'Acción no permitida', 'danger', 'screen-error', error.response.data.message);

                                bootbox.confirm({
                                    title: "¿Desea restaurar el registro?",
                                    message: error.response.data.message,
                                    buttons: {
                                        cancel: {
                                            label: '<i class="fa fa-times"></i> Cancelar',
                                        },
                                        confirm: {
                                            label: '<i class="fa fa-check"></i> Restaurar',
                                        },
                                    },
                                    callback: function(result) {
                                        if (result) {
                                            vm.restoreRecord('payroll/financials/restore/' + error.response.data.deleted_id);
                                        }
                                    },
                                });
                            }

                            if (error.response.data.error_code == "ACC_DEL_EXISTS_002") {
                                vm.showMessage('custom', 'Acción no permitida', 'danger', 'screen-error', error.response.data.message);
                            }

                            if (error.response.status == 403) {
                                vm.showMessage('custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message);
                            }

                            for (var index in error.response.data.errors) {
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
             * Método que carga los datos guardados.
             */
            async getSocioeconomic() {
                await axios.get(`${window.app_url}/payroll/socioeconomics/${this.payroll_socioeconomic_id}`).then(response => {
                    this.record = response.data.record;
                    // Bloquear el select del trabajador cuando esté en modo edit.
                    this.isEditMode = true;
                });
            },

            setRelationships(index,id) {
                var newArray = this.payroll_relationships.filter(function (el) {
                    if(el.id == id) {
                        return el ;
                    }
                });
                this.record.payroll_childrens[index].payroll_relationship = {
                        name: newArray[0].text,
                        id: newArray[0].id,
                        };
            },

            setAge(index) {
                const vm = this;
                let age = moment().diff(
                    vm.record.payroll_childrens[index].birthdate,
                    "years",
                    false
                );
                vm.record.payroll_childrens[index].age = age > -1 ? age : "";
            },

            /**
             * Agrega una nueva columna para el registro de hijos del trabajador
             *
             * @author William Páez <wpaez@cenditel.gob.ve>
             */
            addPayrollChildren() {
                this.record.payroll_childrens.push({
                    first_name: '',
                    last_name: '',
                    id_number: '',
                    birthdate: '',
                    is_student: false,
                    has_scholarships: false,
                    has_disability: false,
                    payroll_schooling_level_id: '',
                    payroll_relationships_id: '',
                    payroll_scholarship_types_id: '',
                    payroll_disability_id:'',
                    payroll_relationship: {
                    name: '',
                    },
                    age:'0',
                    study_center: ''
                });
            },
        },

        async created() {
            this.loading = true;
            await this.getAccountTypes();
            await this.getBanks();
            if (this.payroll_socioeconomic_id) {
                await this.getPayrollSocioeconomic(this.payroll_socioeconomic_id);
            } else {
                await this.getPayrollSocioeconomic('filter');
                this.record.payroll_childrens = [];
            }
            await this.getMaritalStatus();
            await this.getGenders();
            await this.getPayrollRelationships();
            await this.getPayrollScholarshipTypes();
            await this.getPayrollSchoolingLevels();
            await this.getPayrollDisabilities();
            this.record.payroll_staff_id = this.record?.payroll_staff?.id || '';
            const survivor = (this.record?.payroll_staff?.payroll_survivor);
            this.record.survivor_first_name = survivor ? this.record?.payroll_staff?.payroll_survivor.first_name : '';
            this.record.survivor_last_name = survivor ? this.record?.payroll_staff?.payroll_survivor.last_name : '';
            this.record.survivor_id_number = survivor ? this.record?.payroll_staff?.payroll_survivor.id_number : '';
            this.record.survivor_finance_account_type_id = survivor ? this.record?.payroll_staff?.payroll_survivor.finance_account_type_id : '';
            this.record.survivor_payroll_account_number = survivor ? this.record?.payroll_staff?.payroll_survivor.payroll_account_number : '';
            this.record.survivor_finance_bank_id = survivor ? this.record?.payroll_staff?.payroll_survivor.finance_bank_id : '';
            this.loading = false;
        },

        async mounted() {
            this.loading = true;
            if (this.payroll_socioeconomic_id) {
                await this.getSocioeconomic();
            }
            this.loading = false;
        },
    };
</script>
