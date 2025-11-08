<template>
    <section id="ProjectTrackingReportPersonalRegistersForm">
        <div class="card-body">
            <form-errors :listErrors="errors"></form-errors>
            <h6>Parámetros</h6>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group is-required" style="z-index: unset">
                        <label>Nombre del trabajador:</label>
                        <v-multiselect
                            track_by="text"
                            :options="payroll_staffs"
                            v-model="record.workers"
                        >
                        </v-multiselect>
                    </div>
                </div>
                <div class="col-md-6"></div>
                <div class="col-md-12">
                    <div class="form-group" style="z-index: unset">
                        <label>Tipo de reporte:</label>
                    </div>
                </div>
                <div class="col-md-2">
                    <label><strong>Proyecto</strong></label>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="project_data" name="project_data"
                            :value="false" v-model="record.project_data">
                        <label class="custom-control-label" for="project_data"></label>
                    </div>
                </div>
                <div class="col-md-2">
                    <label><strong>Subproyecto</strong></label>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="subproject_data"
                            name="subproject_data" :value="false" v-model="record.subproject_data">
                        <label class="custom-control-label" for="subproject_data"></label>
                    </div>
                </div>
                <div class="col-md-2">
                    <label><strong>Producto</strong></label>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="product_data"
                            name="product_data" :value="false" v-model="record.product_data">
                        <label class="custom-control-label" for="product_data"></label>
                    </div>
                </div>
                <!-- proyecto -->
                <div v-show="record.project_data" class="col-md-6">
                    <div class="form-group is-required">
                        <label>Proyecto:</label>
                        <select2 :options="projects_list"
                                 v-model="record.project_id">
                        </select2>
                    </div>
                </div>
                <!-- ./proyecto -->
                 <!-- subproyecto -->
                <div v-show="record.subproject_data" class="col-md-6">
                    <div class="form-group is-required">
                        <label>Subproyecto:</label>
                        <select2 :options="subprojects_list"
                                 v-model="record.subproject_id">
                        </select2>
                    </div>
                </div>
                <!-- ./subproyecto -->
                 <!-- producto -->
                <div v-show="record.product_data" class="col-md-6">
                    <div class="form-group is-required">
                        <label>Producto:</label>
                        <select2 :options="products_list"
                                 v-model="record.product_id">
                        </select2>
                    </div>
                </div>
                <!-- ./producto -->
                <div class="col-md-12">
                    <div class="form-group" style="z-index: unset">
                        <label>Formato de fecha:</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <label><strong>Mensual</strong></label>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="monthly_date" name="monthly_date"
                            :value="false" v-model="record.monthly_date">
                        <label class="custom-control-label" for="monthly_date"></label>
                    </div>
                </div>
                <div class="col-md-3">
                    <label><strong>Rango a escoger</strong></label>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="customized_date"
                            name="customized_date" :value="false" v-model="record.customized_date">
                        <label class="custom-control-label" for="customized_date"></label>
                    </div>
                </div>
                <div v-show="record.customized_date" class="col-md-3">
					<div class="form-group is-required">
						<label>Desde:</label>
						<input
							id="start_date"
							type="date"
							name="start_date"
							class="form-control input-sm"
							v-model="record.start_date"
						/>
					</div>
				</div>
				<div v-show="record.customized_date" class="col-md-3">
					<div class="form-group is-required">
						<label>Hasta:</label>
						<input
							id="end_date"
							type="date"
							name="end_date"
							class="form-control input-sm"
							v-model="record.end_date"
						/>
					</div>
				</div>
                <div v-show="record.monthly_date" class="col-md-3">
					<div class="form-group is-required" name="year">
						<label for="year">Año:</label>
						<select2 :options="years_list" id="year" data-toggle="tooltip"
                            title="Seleccione el año (requerido)" v-model="record.year_id">
                        </select2>
					</div>
				</div>
                <div v-show="record.monthly_date" class="col-md-3">
					<div class="form-group is-required" name="month">
						<label for="month">Mes:</label>
						<select2 :options="months_list" id="month" data-toggle="tooltip"
                            title="Seleccione el mes (requerido)" v-model="record.month_id">
                        </select2>
					</div>
				</div>
            </div>
        </div>
        <!-- grafica de reporte -->
        <div v-show="show_graph" class="mt-4" :class="{'col-12': !show_table, 'col-5': show_table}">
            <h6 class="h6 table-title mb-4">Gráfico</h6>
            <canvas id="report_chart"></canvas>
        </div>
        <div  id="report_chart">
        </div>
        <!-- /grafica de reporte -->
        <div class="card-footer text-right">
            <button @click.prevent="obtainGraphData()"
                class="btn btn-primary btn-sm" data-toggle="tooltip" title="Generar Grafico"
                type="button">
                <span>Generar grafico</span>
                <i class="fa fa-bar-chart-o"></i>
            </button>
            <button @click.prevent="createReport('personal-register', $event)"
                class="btn btn-primary btn-sm" data-toggle="tooltip" title="Generar Reporte" id="report_btn"
                :disabled=true
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
                    workers: [],
                    project_data: false,
                    project_id: '',
                    subproject_data: false,
                    subproject_id: '',
                    product_data: false,
                    product_id: '',
                    monthly_date: false,
                    customized_date: false,
                    start_date: '',
                    end_date: '',
                    month_id: '',

                },

                records: [],
                errors: [],
                payroll_staffs: [],
                projects_list: [],
                subprojects_list: [],
                products_list: [],
                statuses_list: [],
                months_list: [
                    {
                        id: '',
                        text: "Seleccione...",
                    },
                    {
                        id: 1,
                        text: "Enero"
                    },
                    {
                        id: 2,
                        text: "Febrero"
                    },
                    {
                        id: 3,
                        text: "Marzo"
                    },
                    {
                        id: 4,
                        text: "Abril"
                    },
                    {
                        id: 5,
                        text: "Mayo"
                    },
                    {
                        id: 6,
                        text: "Junio"
                    },
                    {
                        id: 7,
                        text: "Julio"
                    },
                    {
                        id: 8,
                        text: "Agosto"
                    },
                    {
                        id: 9,
                        text: "Septiembre"
                    },
                    {
                        id: 10,
                        text: "Octubre"
                    },
                    {
                        id: 11,
                        text: "Noviembre"
                    },
                    {   id: 12,
                        text: "Diciembre"
                    }
                ],
                years_list: [],
                show_table: true,
                show_graph: false,
                graph_min: 0,
                graph_max: 10,
                graph: null,
                graph_type: 'pie',
            }
        },
        methods: {
            reset() {
                this.record = {
                    id: '',
                    projects: [],
                    workers: [],
                    project_id: '',
                    subproject_id: '',
                    product_id: '',
                };
                this.projects_list = [];
                this.subprojects_list = [];
                this.products_list = [];
            },

            createReport(current, event) {
                const vm = this;
                const ctx = document.getElementById('report_chart');
                const imageDataURL = ctx.toDataURL('image/png');
                vm.loading = true;
                var fields = {};
                for (var index in this.record) {
                    fields[index] = this.record[index];
                }
                fields['current'] = 'personal-register';
                fields['month_text'] = vm.months_list.filter(element => {return element.id == vm.record.month_id});
                fields['chart'] = imageDataURL;
                event.preventDefault();
                axios.post(`${window.app_url}/projecttracking/reports/${current}/create`, fields).then(response => {
                    if (response.data.result == false)
                        location.href = response.data.redirect;
                    else if (typeof(response.data.redirect) !== "undefined") {
                        let reportWindow = window.open(response.data.redirect, '_blank');
                        reportWindow.focus();
                    }
                    else {
                        vm.reset();
                    }
                    vm.loading = false;
                }).catch(error => {
                    vm.errors = [];

                    if (typeof(error.response) !="undefined") {
                        for (var index in error.response.data.errors) {
                            if (error.response.data.errors[index]) {
                                vm.errors.push(error.response.data.errors[index][0]);
                            }
                        }
                    }
                    vm.loading = false;
                });

            },

            getProjectsResponsable() {
                const vm = this;
                if (vm.record.project_data) {
                    vm.getProjects();
                } else if (vm.record.subproject_data) {
                    vm.getSubprojects();
                } else if (vm.record.product_data) {
                    vm.getProducts();
                } else {
                    return;
                }
            },

            async getProjects() {
                const vm = this;
                await axios.get(`${window.app_url}/projecttracking/get-projects`).then(response => {
                    vm.projects_list = response.data;
                });
            },

            async getSubprojects() {
                const vm = this;
                await axios.get(`${window.app_url}/projecttracking/get-subprojects`).then(response => {
                    vm.subprojects_list = response.data;
                });
            },

            async getProducts() {
                const vm = this;
                await axios.get(`${window.app_url}/projecttracking/get-products`).then(response => {
                    vm.products_list = response.data;
                });
            },

            async getActivityStatuses() {
                const vm = this;
                await axios.get(`${window.app_url}/projecttracking/get-activity-statuses`).then(response => {
                    vm.statuses_list = response.data;
                    vm.statuses_list.shift();
                });
            },

            async obtainGraphData() {
                const vm = this;
                const ctx = vm.graph;
                const fields = {};
                for (var index in vm.record) {
                    fields[index] = vm.record[index];
                }
                fields['current'] = 'personal-register';
                fields['month_text'] = vm.months_list.filter(element => {return element.id == vm.record.month_id});

                vm.loading = true;
                await axios.post(`${window.app_url}/projecttracking/get-graph-data`, fields).then(response => {
                    // Obtener grafico de reporte
                    vm.setChart(response.data.records);
                    for (let index in ctx.data.datasets) {
                        ctx.data.datasets[index] = count[index];
                    }
                }).catch(error => {
                    vm.errors = [];

                    if (typeof(error.response) !="undefined") {
                        for (var index in error.response.data.errors) {
                            if (error.response.data.errors[index]) {
                                vm.errors.push(error.response.data.errors[index][0]);
                            }
                        }
                    }
                    vm.loading = false;
                });
                vm.show_graph = true;
                document.getElementById('report_btn').disabled = false;
                vm.loading = false;
            },

            /**
             * Establece los datos del gráfico
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            setChart(records) {
                const _self = this;

                // Obtener datos y colores de los estados de actividad
                let label_chart_names = _self.statuses_list.map(element => element.text);
                let label_chart_colors = _self.statuses_list.map(element => element.color);
                let chart_data = [];
                for (let chart_name in label_chart_names ) {
                    chart_data.push(records['statuses'][label_chart_names[chart_name]]);
                    label_chart_names[chart_name] = label_chart_names[chart_name] + ' - ' + records['statuses'][label_chart_names[chart_name]];
                }
                const ctx = document.getElementById('report_chart');
                if (_self.graph !== null) {
                    _self.graph.destroy();
                }

                const dataBar = {
                    labels: ['Personal'],
                    datasets: [{
                        label: '% Asistencia',
                        data: [2],
                        backgroundColor: 'rgba(44, 168, 255, 1)',
                        borderColor: 'rgba(44, 168, 255, 1)',
                        borderWidth: 1
                    }, {
                        label: '% Inasistencia',
                        data: [8],
                        backgroundColor: 'rgba(249, 99, 50, 1)',
                        borderColor: 'rgba(249, 99, 50, 1)',
                        borderWidth: 1
                    }]
                };
                const dataLine = {
                    labels: ['Personal'],
                    datasets: [{
                        label: 'Asistencia',
                        data: [1,2,3],
                        backgroundColor: 'rgba(44, 168, 255, 1)',
                        borderColor: 'rgba(44, 168, 255, 1)',
                        borderWidth: 1
                    }, {
                        label: 'Inasistencia',
                        data: [4,5,6],
                        backgroundColor: 'rgba(249, 99, 50, 1)',
                        borderColor: 'rgba(249, 99, 50, 1)',
                        borderWidth: 1
                    }]
                };
                const dataPie = {
                    labels: label_chart_names,
                    datasets: [{
                        label: 'Tareas',
                        data: chart_data,
                        backgroundColor: label_chart_colors,
                        borderColor: label_chart_colors,
                        borderWidth: 1
                    }]
                };
                let graphData = dataBar;
                if (_self.graph_type === 'line') {
                    graphData = dataLine;
                } else if (_self.graph_type === 'pie') {
                    graphData = dataPie;
                }
                _self.graph = new Chart(ctx, {
                    type: _self.graph_type,
                    data: graphData,
                    options: {
                        title: {
                            display: true,
                            text: 'Estados de las tareas',
                            fontSize: 18,
                            fontColor: 'black',
                        },
                        responsive: true,
                        tooltips: {
                            enabled: true,
                            callbacks: {
                                label: function(tooltipItem, data) {
                                    const label = data.datasets[tooltipItem.datasetIndex].label || '';
                                    const percent = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                                    const roundedPercent = isNaN(Number(percent)) ? '0.00' : Number(percent);
                                    return `${label}: ${roundedPercent}`;
                                }
                            }
                        },
                        //maintainAspectRatio: false,
                        scales: (_self.graph_type === 'pie') ? {} : {
                            yAxes: [{
                                ticks: {
                                    beginAtZero: false,
                                    max: _self.graph_max,
                                    min: _self.graph_min
                                }
                            }]
                        },
                        legend: {
                            position: 'right', // or 'bottom', 'left', 'top'
                            labels: {
                                // usePointStyle: true, Optional: for consistent legend item styling
                                fontColor: 'black', // Optional: customize font color
                                fontSize: 14, // Optional: customize font size
                                fontStyle: 'bold',
                            }
                        },
                    }
                });
            },

            addAllToOptions() {
                const vm = this;
                vm.payroll_staffs = vm.payroll_staffs.filter(el => el.id != '');
                vm.payroll_staffs.unshift({'id':'todos', 'text':'Todos'});

            },
        },
        async mounted() {
            const vm = this;
            await vm.getPayrollStaffs();
            await vm.addAllToOptions();

            // Agregar para lista de años desde 100 años atras
            for (let i = 0; i < 100; i++) {
                vm.years_list.push({
                    id: (new Date().getFullYear() - 100) + i + 1,
                    text: (new Date().getFullYear() - 100) + i + 1,
                })
            }
            vm.years_list = vm.years_list.reverse();
            vm.years_list.unshift({
                id: '',
                text: 'Seleccione...'
            });
            vm.getActivityStatuses();
        },

        watch: {
            'record.project_data': function (newVal) {
                const vm = this;
                if (!newVal) {
                    vm.projects_list = [];
                    vm.record.project_id = '';
                } else if(newVal) {
                    vm.record.product_data = false;
                    vm.record.subproject_data = false;
                    vm.getProjectsResponsable();
                }
            },
            'record.subproject_data': function (newVal) {
                const vm = this;
                if (!newVal) {
                    vm.subprojects_list = [];
                    vm.record.subproject_id = '';
                } else if(newVal) {
                    vm.record.project_data = false;
                    vm.record.product_data = false;
                    vm.getProjectsResponsable();
                }
            },
            'record.product_data': function (newVal) {
                const vm = this;
                if (!newVal) {
                    vm.products_list = [];
                    vm.record.product_id = '';
                } else if(newVal) {
                    vm.record.project_data = false;
                    vm.record.subproject_data = false;
                    vm.getProjectsResponsable();
                }
            },
            'record.monthly_date': function (newVal) {
                const vm = this;
                if(newVal) {
                    vm.record.customized_date = false;
                }
            },
            'record.customized_date': function (newVal) {
                const vm = this;
                if(newVal) {
                    vm.record.monthly_date = false;
                }
            },
        },
    };
</script>
