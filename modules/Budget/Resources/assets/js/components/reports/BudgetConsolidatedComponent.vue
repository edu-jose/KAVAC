<template>
    <section id="BudgetConsolidated">
        <div class="card-body">
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
            <h6>Parámetros</h6>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group is-required" style="z-index: unset">
                        <label>Proyecto</label>
                        <v-multiselect
                            track_by="text"
                            :options="budget_proyects"
                            v-model="record.proyects"
                        >
                        </v-multiselect>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group is-required" style="z-index: unset">
                        <label>Acción centralizada</label>
                        <v-multiselect
                            track_by="text"
                            :options="budget_centralized_actions"
                            v-model="record.centralized_actions"
                        >
                        </v-multiselect>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group is-required" style="z-index: unset">
                        <label>Acción específica</label>
                        <v-multiselect
                            track_by="text"
                            :options="budget_specific_actions"
                            v-model="record.specific_actions"
                        >
                        </v-multiselect>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group is-required" style="z-index: unset">
                        <label>Cuentas</label>
                        <v-multiselect
                            track_by="text"
                            :options="budget_accounts"
                            v-model="record.accounts"
                            :limit="3"
                        >
                        </v-multiselect>
                    </div>
                </div>
                <div class="col-md-4">
                    <!-- Desde -->
                    <div class="form-group is-required">
                        <label>Desde:</label>
                        <input type="date"
                                data-toggle="tooltip"
                                title="Desde"
                                class="form-control input-sm" v-model="record.from">
                    </div>
                    <!-- ./Desde -->
                </div>
                <div class="col-md-4">
                    <!-- Hasta -->
                    <div class="form-group is-required">
                        <label>Hasta:</label>
                        <input type="date"
                                data-toggle="tooltip"
                                title="Hasta"
                                class="form-control input-sm" v-model="record.to">
                    </div>
                    <!-- ./Hasta -->
                </div>
            </div>
        </div>

        <div class="card-footer text-right">
            <button @click.prevent="exportReport()"
                class="btn btn-primary btn-sm" data-toggle="tooltip" title="Generar Reporte"
                type="button">
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
                record: {
                    id: '',
                    proyects: [],
                    centralized_actions: [],
                    specific_actions: [],
                    accounts: [],
                    from: '',
                    to: '',
                },

                records: [],
                errors: [],
                budget_proyects: [],
                budget_centralized_actions: [],
                budget_specific_actions: [],
                budget_accounts: [],
            }
        },
        props: {
            message: {
                type: String,
            }
        },
        methods: {
            reset() {
                this.record = {
                    proyects: [],
                    centralized_actions: [],
                    specific_actions: [],
                    accounts: [],
                    from: '',
                    to: '',
                };
            },

            async exportReport() {
                const vm = this;
                let data = JSON.parse(JSON.stringify(vm.record));

                if (vm.record.proyects.length > 0) {
                    const allProjects = vm.record.proyects.some(project => project.text == 'Todos');

                    if (allProjects) {
                        data.proyects = vm.budget_proyects.filter(objeto => objeto.text !== "Todos");
                    }
                }

                if (vm.record.centralized_actions.length > 0) {
                    const allCentralizedActions = vm.record.centralized_actions.some(action => action.text == 'Todas');

                    if (allCentralizedActions) {
                        data.centralized_actions = vm.budget_centralized_actions.filter(objeto => objeto.text !== "Todas");
                    }
                }

                if (vm.record.specific_actions.length > 0) {
                    const allSpecificActions = vm.record.specific_actions.some(action => action.text == 'Todas');

                    if (allSpecificActions) {
                        data.specific_actions = vm.budget_specific_actions.filter(objeto => objeto.text !== "Todas");
                    }
                }

                await axios.post(`${window.app_url}/budget/report/consolidated-export`, data).then(() => {
                    vm.loading = false;
                    vm.showMessage(
                        'custom',
                        'Reporte Generado',
                        'primary',
                        'screen-ok',
                        'Su solicitud esta en proceso, esto puede tardar unos minutos. Se le notificara al terminar la operación.'
                    );
                }).catch(() => {
                    vm.loading = false;
                    vm.showMessage(
                        'custom',
                        'Error',
                        'danger',
                        'screen-error',
                        'Ocurrió un error al generar el reporte.'
                    );
                });
            },

            /**
             * Obtiene los datos de los proyectos
             *
             * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
             * 
             */
            async getBudgetProyects() {
                this.budget_proyects = [];
                await axios.get(`${window.app_url}/budget/report/get-proyects`).then(response => {
                    this.budget_proyects = Object.values(response.data);
                });
            },

            /**
             * Obtiene los datos de las acciones centralizadas
             *
             * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
             * 
             */
            async getBudgetCentralizedActions() {
                this.budget_centralized_actions = [];
                await axios.get(`${window.app_url}/budget/report/get-centralized-actions`).then(response => {
                    this.budget_centralized_actions = Object.values(response.data);
                });
            },

            /**
             * Obtiene los datos de las acciones centralizadas
             *
             * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
             * 
             */
            async getBudgetSpecificActions() {
                this.budget_specific_actions = [];
                await axios.get(`${window.app_url}/budget/report/get-specific-actions`).then(response => {
                    this.budget_specific_actions = Object.values(response.data);
                });
            },

            /**
             * Obtiene los datos de las acciones centralizadas
             *
             * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
             * 
             */
            async getBudgetAccounts() {
                this.budget_accounts = [];
                await axios.get(`${window.app_url}/budget/report/get-accounts`).then(response => {
                    this.budget_accounts = Object.values(response.data);
                });
            },
        },

        async mounted() {
            const vm = this;

            await vm.getBudgetAccounts();
            await vm.getBudgetProyects();
            await vm.getBudgetCentralizedActions();
            await vm.getBudgetSpecificActions();

            if (vm.message == 'error') {
                vm.showMessage(
                    'custom',
                    'Error',
                    'danger',
                    'screen-error',
                    'No se encontraron registros con las cuentas seleccionadas, por favor verifique e intente de nuevo.'
                );
            }
        },
    };
</script>
