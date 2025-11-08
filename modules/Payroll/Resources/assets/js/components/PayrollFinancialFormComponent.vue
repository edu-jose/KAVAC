<template>
    <section id="PayrollFinancialFormComponent">
        <div class="card-body">
            <form-errors :listErrors="errors"></form-errors>
            <div class="row">
                <div class="col-md-4" id="helpFinancialStaff" v-if="payroll_staffs.length > 0">
                    <div class="form-group is-required">
                        <label>Trabajador:</label>
                        <select2 :options="payroll_staffs" id="payroll_staff_id" v-model="record.payroll_staff_id"
                            :disabled="isEditMode">
                        </select2>
                    </div>
                </div>
                <div class="col-md-4" id="helpFinancialBank" v-if="banks.length > 0">
                    <div class="form-group is-required">
                        <label>Banco:</label>
                        <select2 :options="banks" id="finance_bank_id" v-model="record.finance_bank_id"
                            @input="getAgencies">
                        </select2>
                    </div>
                </div>
                <div class="col-md-4" id="helpFinancialTypeAccount">
                    <div class="form-group is-required" v-if="account_types.length > 0">
                        <label>Tipo de Cuenta:</label>
                        <select2 :options="account_types" id="finance_account_type_id"
                            v-model="record.finance_account_type_id">
                        </select2>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6 col-md-6" id="helpFinancialAccountNumber">
                    <div class="form-group is-required" >
                        <label>Código Cuenta Cliente</label>
                        <div class="row">
                            <div class="col-sm-4 col-md-5">
                                <input type="text" style="text-align: center;" class="form-control" id="bank_code"
                                    v-model="record.bank_code" readonly>
                            </div>
                            <div class="col-sm-8 col-md-7">
                                <input type="text" class="form-control input-sm" data-toggle="tooltip"
                                    v-model="record.payroll_account_number"
                                    title="Indique el número de cuenta sin guiones o espacios" maxlength="16"
                                    v-input-mask data-inputmask-regex="[0-9]*">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-right card-footer" id="helpParamButtons">
            <button class="btn btn-default btn-icon btn-round" data-toggle="tooltip" type="button"
                title="Borrar datos del formulario" @click="reset">
                <i class="fa fa-eraser"></i>
            </button>
            <button type="button" class="btn btn-warning btn-icon btn-round" data-toggle="tooltip"
                title="Cancelar y regresar" @click="redirect_back(route_list)">
                <i class="fa fa-ban"></i>
            </button>
            <button type="button" @click="createRecord('payroll/financials')" class="btn btn-success btn-icon btn-round"
                data-toggle="tooltip" title="Guardar registro">
                <i class="fa fa-save"></i>
            </button>
        </div>
    </section>
</template>
<script>
export default {
    props: {
        payrollfinancial_edit: {
            type: Object,
            default: function () {
                return null
            }
        },
    },
    data() {
        return {
            record: {
                id: '',
                payroll_staff_id: '',
                finance_bank_id: '',
                finance_account_type_id: '',
                payroll_account_number: '',
            },
            errors: [],
            payroll_staffs: [],
            banks: [],
            account_types: [],
            isDisable: false,
            isEditMode: false,
        }
    },
    methods: {
        reset() {
            this.record = {
                id: '',
                payroll_staff_id: '',
                finance_bank_id: '',
                finance_account_type_id: '',
                payroll_account_number: '',
            };
            this.isDisable = false;
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
         * Obtiene las agencias bancarias registradas.
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        async getAgencies() {
            const vm = this;
            vm.agencies = [];
            const bank_id = this.record.finance_bank_id || '';

            if (bank_id) {
                if ($("#bank_code").length) {
                    axios.get(`${vm.app_url}/finance/get-bank-info/${bank_id}`).then(response => {
                        if (response.data.result) {
                            vm.record.bank_code = response.data.bank.code;
                            // asignar el codigo del banco a #bank_code input
                             $("#bank_code").val(response.data.bank.code);
                        }
                    }).catch(error => {
                        vm.logs('Finance/Resources/assets/js/_all.js', 97, error, 'getAgencies');
                    });
                }
            }

        },

        /**
         * Método que permite restaurar un registro eliminado
         *
         * @author  Ing. Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosar01@gmail.com>
         */
        restoreRecord(url) {
            const vm = this;
            url = vm.setUrl(url);

            vm.loading = true;
            axios.put(url).then(response => {
                if (typeof (response.data.redirect) !== "undefined") {
                    vm.showMessage('custom', 'Registro restaurado con éxito', 'success', 'screen-ok', 'La información se ha restaurado correctamente.');
                    setTimeout(function () {
                        location.href = response.data.redirect;
                    }, 2000);
                }
                else {
                    vm.errors = [];
                    vm.reset();
                    vm.readRecords('payroll/financials');
                    vm.showMessage('custom', 'Registro restaurado con éxito', 'success', 'screen-ok', 'La información se ha restaurado correctamente.');
                }
            }).catch(error => {
                vm.errors = [];
                if (typeof (error.response) != "undefined") {
                    if (error.response.status == 403) {
                        vm.showMessage(
                            'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                        );
                    }
                    for (var index in error.response.data.errors) {
                        if (error.response.data.errors[index]) {
                            vm.errors.push(error.response.data.errors[index][0]);
                        }
                    }
                }

            });

            vm.loading = false;
        },
    },
    async created() {
        const vm = this;
        await vm.getPayrollStaffs('financial');
        await vm.getBanks();
        await vm.getAccountTypes();
    },
    async mounted() {
        const vm = this;
        if (vm.payrollfinancial_edit) {
            await vm.getPayrollStaffs();
            vm.record.id = vm.payrollfinancial_edit.id;
            vm.record.payroll_staff_id
                = vm.payrollfinancial_edit.payroll_staff_id;
            vm.record.finance_bank_id
                = vm.payrollfinancial_edit.finance_bank_id;
            await vm.getAgencies();
            vm.record.finance_account_type_id
                = vm.payrollfinancial_edit.finance_account_type_id;
            vm.record.payroll_account_number
                = vm.payrollfinancial_edit.payroll_account_number.substr(4);
            vm.isDisable = true;
            // Bloquear el select del trabajador cuando esté en modo edit.
            vm.isEditMode = true;
        }
    },
};
</script>
