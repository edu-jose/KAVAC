<template>
    <section id="PayrollReportAverageConceptsForm">
        <div class="card-body" style="min-height: 400px;">
            <form-errors :listErrors="errors"></form-errors>

            <div class="row">
                <div class="col-12">
                    <strong>Filtros</strong>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group is-required" style="z-index: unset">
                        <label>Tipo de nómina</label>
                        <v-multiselect
                            track_by="text"
                            :options="payroll_payment_types"
                            v-model="record.payroll_payment_types"
                            @input="setPeriods()"
                        />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div v-for="(period, index) in periods"
                        :key="index">
                        <div class="form-group">
                            <label>{{ period[0].payroll_payment_type.name }}:</label>
                            <select2
                                :options="period"
                                v-model="record.periods_by_payment_type[index]"
                            >
                            </select2>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Trabajador:</label>
                        <v-multiselect
                            track_by="text"
                            :options="payroll_staffs"
                            v-model="record.payroll_staffs"
                        />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Conceptos:</label>
                        <v-multiselect
                            track_by="text"
                            :options="payroll_concepts"
                            v-model="record.payroll_concepts"
                        />
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-right">
            <button
                @click.prevent="createReport()" class="btn btn-primary btn-sm" data-toggle="tooltip"
                title="Generar Reporte" type="button"
            >
                <span>Generar reporte</span>
                <i class="fa fa-file-pdf-o"></i>
            </button>
        </div>
    </section>
</template>

<script>
export default {
    data() {
        return {
            errors: [],
            record: {
                period: '',
                payroll_payment_types: [],
                payroll_staffs: [],
                payroll_concepts: [],
                periods_by_payment_type: {}
            },
            periods: [],
            payroll_payment_types: [],
            payroll_staffs: [],
            payroll_concepts: [],
        };
    },
    props: {
        roles: {
            type: Array,
            required: true,
        },
        permissions: {
            type: Array,
            required: true,
        },
        user: {
            type: Object,
            required: true,
        }
    },
    methods: {
        reset() {
            this.record = {
                period: '',
                payroll_payment_types: [],
                payroll_staffs: [],
                payroll_concepts: [],
                periods_by_payment_type: {}
            };
        },
        async setPeriods() {
            const vm = this;
            vm.periods = [];
            vm.record.period = '';
            if (!vm.record.payroll_payment_types) {
                return;
            }
            vm.loading = true;
            await axios.post(
                `${window.app_url}/payroll/get-payment-periods-by-ids`,
                {
                    payment_types: vm.record.payroll_payment_types,
                    status: 'generated'
                }
            ).then(response => {
                vm.periods = response.data.records;

                if (vm.record.periods_by_payment_type == '') {
                    vm.record.periods_by_payment_type = {};
                }

                vm.record.payroll_payment_types.forEach((payment) => {
                    if (!vm.record.periods_by_payment_type[payment.id]) {
                        vm.record.periods_by_payment_type[payment.id] = vm.periods[payment.id][0].id;
                    }
                })
            }).catch(error => {
                console.error(error);
            });
            vm.loading = false;
        },
        setConsultPeriod(id) {
            $('input[type="checkbox"]').each(function() {
                const inputId = $(this).attr('id').replace('period_', '');
                if (inputId != id && $(this).is(':checked')) {
                    $(this).prop('checked', false);
                }
            });
            this.record.period = $('#period_' + id).is(':checked') ? id : '';
        },
        async createReport(current) {
            const vm = this;
            let data = JSON.parse(JSON.stringify(vm.record));

            vm.loading = true;
            vm.errors = [];
            if (!vm.record.payroll_payment_types) {
                vm.errors.push('El tipo de nómina es obligatorio.');
            }
            if (vm.errors.length > 0) {
                vm.loading = false;
                return;
            }

            let baseUrl = `${window.app_url}/payroll/reports/average-concepts-export`;
            let params = new URLSearchParams();

            for (let key in data) {
                if (data.hasOwnProperty(key)) {
                    if ('undefined' !== typeof(data[key])) {
                        params.append(
                            key,
                            (typeof data[key] === 'object')
                                ? JSON.stringify(data[key])
                                : data[key]
                        );
                    }
                }
            }
            let finalUrl = baseUrl + '?' + params;
            vm.loading = false;
            window.open(finalUrl, '_blank');

        },

        /**
         * Método que obtiene un arreglo con los conceptos registrados en la política vacacional
         *
         * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
         */
        async getPayrollVacationPolicyConcepts() {
            const vm = this;
            vm.payroll_concepts = [];
            await axios.get(`${window.app_url}/payroll/get-vacation-policy-concepts`).then(response => {
                vm.payroll_concepts = response.data;
            });
        },
    },
    async mounted() {
        const vm = this;
        vm.loading = true;

        // Obtiene el listado de empleados
        let type = (
            vm.roles.filter(role => role.slug === 'admin' || role.slug === 'payroll').length === 0 &&
            vm.user?.employee_id
        ) ? vm.user.employee_id : '';
        await vm.getPayrollStaffs(type);

        vm.payroll_staffs = await vm.payroll_staffs.filter(el => el.id != '');

        if (type) {
            vm.payroll_staffs = await vm.payroll_staffs.filter(el => el.employee_id == type);
            if (vm.payroll_staffs.length === 1) {
                vm.record.payroll_staffs = await vm.payroll_staffs;
            }
        }

        // Obtiene el listado de tipos de nómina que tengan activa la opción de generar recibos de pago
        await vm.getPayrollPaymentTypes(true);
        // Obtiene el listado de conceptos
        await vm.getPayrollVacationPolicyConcepts();
        await vm.payroll_payment_types.unshift({'id':'', 'text':'Seleccione...'});

        vm.loading = false;
    },
};
</script>
