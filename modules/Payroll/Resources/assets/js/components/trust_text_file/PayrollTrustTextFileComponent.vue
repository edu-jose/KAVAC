<template>
    <section id="PayrollTrustTextFileComponent" style="overflow: overlay">

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
                <div class="col-md-4">
                    <div class="form-group is-required">
                        <label for="file_name">Nombre del archivo</label>
                        <input type="text" class="form-control input-sm" id="file_name"
                            v-model="record.file_name">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group is-required">
                        <label>Código de proceso:</label>
                        <select2
                            :options="payroll_process_codes"
                            v-model="record.process_code"
                        >
                        </select2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group is-required">
                        <label>Fecha de pago</label>
                        <input
                            type="date"
                            class="form-control input-sm"
                            v-model="record.date"
                        />
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group is-required">
                        <label for="payroll" class="is-required">Tipo de Nómina</label>
                        <select2
                            :options="payroll_payment_types"
                            v-model="record.payroll_payment_type_id"
                            style="margin-top: -5px;"
                            @input="setPeriods()">
                        </select2>
                    </div>
                </div>
                <div class="col-md-4" v-show="record.payroll_payment_type_id">
                    <div class="form-group is-required">
                        <label for="payroll" class="is-required">Periodos</label>
                        <select2
                            :options="periods"
                            v-model="record.payroll_id"
                            style="margin-top: -5px;">
                        </select2>
                    </div>
                </div>
            </div>
        </div>
        <!-- Archivo de nómina -->

        <div class="card-footer text-right">
            <button type="button" @click="createRecord('payroll/validate-trust-txt-data')" data-toggle="tooltip"
                title="Generar archivo txt de nómina" class="btn btn-primary btn-sm">
                <span>Generar archivo txt de nómina</span>
                <i class="fa fa-print"></i>
            </button>
        </div>
    </section>
</template>
<script>

export default {
    data() {
        return {
            errors: [],
            payroll_payment_types: [],
            payroll_process_codes: [],
            periods: [],
            paymentTypePeriods: [],
            record: {
                file_name: '',
                process_code: '',
                date: '',
                payroll_id: '',
                payroll_payment_type_id: '',
            }
        };
    },

    async created() {
        const vm = this;
        await vm.getPayrollPaymentTypes();
        await vm.getPayrollProcessCodes();
    },

    methods: {
        async reset() {
            const vm = this;
            vm.record = {
                file_name: '',
                process_code: '',
                date: '',
                payroll_id: '',
                payroll_payment_type_id: '',
            }
        },

        setPeriods() {
            const vm = this;
            axios.post(
                `${window.app_url}/payroll/get-trust-payment-periods`,
                {
                    payment_type: vm.record.payroll_payment_type_id,
                    status: 'closed'
                }
            ).then(response => {
                vm.periods = response.data;
            }).catch(error => {
                console.error(error);
            });
        },

        async createRecord(url) {
            const vm = this;
            try {
                vm.loading = true;
                const validateResponse = await axios.post(`${window.app_url}/payroll/validate-trust-txt-data`, vm.record);

                if (validateResponse.data.result == false) {
                    location.href = `${window.app_url}/payroll/settings`;
                    return;
                }

                if (validateResponse.status == 200) {
                    const response = await axios.get(`${window.app_url}/payroll/trust-file-generate`, {
                        responseType: 'blob', params: {
                            file_name: this.record.file_name,
                            payroll_id: this.record.payroll_id,
                            date: this.record.date,
                            process_code: this.record.process_code,
                        }
                    });
                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');

                    link.setAttribute('href', url);
                    link.setAttribute('download', `${this.record.process_code}_${this.record.file_name}.txt`);
                    link.setAttribute('target', '_blank');
                    link.click();

                    window.URL.revokeObjectURL(url);

                    // vm.reset();
                    vm.errors = [];
                    vm.loading = false;
                }
            } catch (error) {
                vm.errors = [];
                for (let index in error.response.data.errors) {
                    if (error.response.data.errors[index]) {
                        for (let errorIndex in error.response.data.errors[index]) {
                            vm.errors.push(error.response.data.errors[index][errorIndex]);
                        }
                    }
                }

                vm.loading = false;
            }
        },

        getPayrollPaymentTypes() {
            const vm = this;
            axios.get(`${window.app_url}/payroll/get-trust-payment-types`).then(response => {
                vm.payroll_payment_types = response.data;
            }).catch(error => {
                console.error(error);
            });
        },

        getPayrollProcessCodes() {
            const vm = this;
            axios.get(`${window.app_url}/payroll/get-payroll-process-code`).then(response => {
                vm.payroll_process_codes = response.data;
            }).catch(error => {
                console.error(error);
            });
        }
    },
};
</script>
