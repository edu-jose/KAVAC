<template>
    <section id="PayrollTrustTextFileComponent">

        <!-- Archivo de nómina -->
        <div class="card-body">

            <!-- mensajes de error -->
            <div class="alert alert-danger" v-if="errors.length > 0">
                <div class="container">
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
            <!-- mensajes de error -->

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group is-required" style="z-index: unset">
                        <label for="file_name">Trabajadores</label>
                        <v-multiselect
                            data-toggle="tooltip"
                            title="Indique los trabajadores para agregar al archivo"
                            track_by="text"
                            :hide_selected="false"
                            :close_on_select="false"
                            :options="payroll_staffs"
                            :limit="3"
                            v-model="record.payroll_staffs"
                        >
                        </v-multiselect>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group is-required">
                        <label class="control-label" for="uploadDocument">Cuenta bancaria</label>
                        <select2 :options="finance_bank_accounts" v-model="record.finance_bank_account_id">
                        </select2>
                    </div>
                </div>
                <div class="col-md-6" style="z-index: 0;">
                    <div class="form-group is-required">
                        <label class="control-label" for="uploadDocument">Documento</label>
                        <div class="custom-file">
                            <input
                                type="file" name="uploadDocument" id="uploadDocument" class="custom-file-input"
                                data-toggle="tooltip" title="Documento" accept=".xls,.xlsx,.xlsm,.ods,.docx"
                            />
                            <label class="custom-file-label" for="uploadDocument">Adjuntar</label>
                        </div>
                        <small id="customFileHelp" class="form-text text-muted">Archivos permitidos: .xls, .xlsx, .xlsm, .ods</small>
                    </div>
                </div>
            </div>
        </div>
        <!-- Archivo de nómina -->

        <div class="card-footer text-right">
            <button type="button" @click="processFile('payroll/trust-file-staff')" data-toggle="tooltip"
                title="Procesar archivo" class="btn btn-primary btn-sm">
                <span>Procesar archivo</span>
                <i class="fa fa-print"></i>
            </button>
        </div>
    </section>
</template>
<script>

export default {
    props: {
        finance_bank_id: {
            type: Number,
            required: true,
        },
    },
    data() {
        return {
            errors: [],
            payroll_staffs: [],
            finance_bank_accounts: [],
            record: {
                payroll_staffs: [],
                file: '',
                finance_bank_account_id: '',
            }
        };
    },

    async created() {
        const vm = this;
        await vm.getPayrollStaffs();
        await vm.getBankAccounts();
    },

    methods: {
        async reset() {
            const vm = this;
            vm.record = {
                payroll_staffs: [],
                file: '',
                finance_bank_account_id: ''
            }

            document.getElementById('uploadDocument').value = '';
            document.querySelector('.custom-file-label').innerHTML = 'Adjuntar';
        },

        /**
         * Obtiene los datos de las cuentas asociadas a una entidad bancaria
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        async getBankAccounts() {
            const vm = this;
            const bank_id = vm.finance_bank_id || '';

            if (bank_id) {
                await axios.get(`${vm.app_url}/finance/get-accounts/${bank_id}`).then(response => {
                    if (response.data.result) {
                        vm.finance_bank_accounts = response.data.accounts;
                    }
                }).catch(error => {
                    vm.logs('Budget/Resources/assets/js/_all.js', 127, error, 'getBankAccounts');
                });
            }
        },

        async processFile(url) {
            const vm = this;
            url = vm.setUrl(url);
            vm.loading = true;

            const inputFile = document.getElementById('uploadDocument');
            const file = inputFile.files[0];
            const formData = new FormData();

            if (typeof(file) != 'undefined') {
                formData.append("file", file);
            }

            if (vm.record.payroll_staffs.length > 0) {
                formData.append("payroll_staffs", JSON.stringify(vm.record.payroll_staffs));
            }

            if (vm.record.finance_bank_account_id) {
                formData.append("finance_bank_account_id", vm.record.finance_bank_account_id);
            }

            await axios.post(url, formData, {
                headers: {
                    "Content-Type": "multipart/form-data",
                }}
            ).then(response => {
                vm.showMessage(
                    'custom', 'Éxito', 'primary', 'screen-ok',
                    'Su solicitud esta en proceso, esto puede tardar unos ' +
                    'minutos. Se le notificara al terminar la operación',
                );

                vm.reset();
            }).catch(error => {
                vm.errors = [];

                if (typeof(error.response) !="undefined") {
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
};
</script>
