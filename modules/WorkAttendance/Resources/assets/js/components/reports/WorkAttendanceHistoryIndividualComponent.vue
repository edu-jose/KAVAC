<template>
    <div>
        <form @submit.prevent="searchData">
            <form-errors :listErrors="errors"></form-errors>
            <div class="row">
                <div class="col-12">
                    <b>Filtros</b>
                </div>
                <div class="col-md-2">
                    <div class="form-group is-required">
                        <label for="from_date">Desde</label>
                        <input
                            type="date" name="from_date" id="from_date"
                            class="form-control" v-model="filters.from_date"
                            title="Indique la fecha inicial a consultar"
                            data-toggle="tooltip"
                        >
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group is-required">
                        <label for="to_date">Hasta</label>
                        <input
                            type="date" name="to_date" id="to_date"
                            class="form-control" v-model="filters.to_date"
                            title="Indique la fecha final a consultar"
                            data-toggle="tooltip"
                        >
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group is-required">
                        <label for="positionId">Cargo</label>
                        <select2
                            id="positionId" :options="positions"
                            v-model="filters.position_id"
                            @input="setEmployments('filters');records=[]"
                            title="Seleccione el cargo del personal a consultar"
                            data-toggle="tooltip"
                        />
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group is-required">
                        <label for="payrollEmploymentId">Persona</label>
                        <select2
                            id="payrollEmploymentId" :options="employments"
                            v-model="filters.payroll_employment_id"
                            title="Seleccione el personal a consultar"
                            data-toggle="tooltip"
                            @input="records=[]"
                        />
                    </div>
                </div>
                <div class=" form-group col-md-2 pt-4">
                    <button
                        type="submit" data-toggle="tooltip" title="Buscar registros del sistema"
                        class="btn btn-info btn-icon btn-xs-responsive px-3"
                    >
                        <i class="fa fa-search"></i>
                    </button>
                    <button
                        type="button" aria-label="Clear"
                        class="btn btn-default btn-icon btn-xs-responsive px-3"
                        title="Limpiar filtro"
                        data-toggle="tooltip"
                        @click="resetFilters"
                    >
                        <i class="fa fa-eraser"></i>
                    </button>
                </div>
                <div class="col-12">
                    <small class="form-text text-muted">
                        <i class="fa fa-info-circle mr-2"></i>
                        Si no se indican los filtros de fecha, se mostraran los registros correspondientes al mes actual
                    </small>
                </div>
            </div>
        </form>
        <hr class="mt-3 mb-3">
        <h6 class="h6 table-title mt-3 mb-3">Registros de asistencia</h6>
        <div class="row">
            <div class="form-group col-md-2">
                <label for="graph_type">Tipo de Gráfico</label>
                <select2
                    id="graph_type" :options="graph_types"
                    v-model="graph_type"
                    title="Seleccione el tipo de gráfico"
                    data-toggle="tooltip"
                />
            </div>
            <div class="form-group col-md-2 text-center">
                <label for="show_table">Mostrar Tabla</label>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="show_table" v-model="show_table" :value="true">
                    <label class="custom-control-label" for="show_table">&nbsp;</label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mt-4" v-if="show_table">
                <h6 class="h6 table-title mb-4">Tabla de registros</h6>
                <div class="row">
                    <div class="col-md-6 offset-md-6">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th class="text-left">Total Horas Trabajadas</th>
                                    <td class="text-right">{{ totalWorkedTime }}</td>
                                </tr>
                                <tr>
                                    <th class="text-left">Total % Asistencia</th>
                                    <td class="text-right">{{ parseFloat(total_attendance_percent).toFixed(2) }} %</td>
                                </tr>
                                <tr>
                                    <th class="text-left">Total % Inasistencia</th>
                                    <td class="text-right">{{ parseFloat(total_absence_percent).toFixed(2) }} %</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <v-client-table :columns="columns" :data="records" :options="table_options">
                    <div slot="day" slot-scope="props" class="text-center">
                        {{ getWeekDay(props.row.date_at) }}
                    </div>
                    <div slot="entry_time" slot-scope="props" class="text-center">
                        <span v-if="props.row.entry_time">{{ props.row.entry_time }}</span>
                        <span v-else>---</span>
                    </div>
                    <div slot="exit_time" slot-scope="props" class="text-center">
                        <span v-if="props.row.exit_time">{{ props.row.exit_time }}</span>
                        <span v-else>---</span>
                    </div>
                    <div slot="work_time_formated" slot-scope="props" class="text-right">
                        {{ props.row?.work_time_formated ?? '---' }}
                    </div>
                    <div slot="work_percent" slot-scope="props" class="text-right">
                        {{ (props.row.work_percent).toFixed(2) }} %
                    </div>
                    <div slot="id" slot-scope="props" class="text-right">
                        {{ (100 - props.row.work_percent).toFixed(2) }} %
                    </div>
                </v-client-table>
            </div>
            <div id="graphSection" class="mt-4" :class="{'col-12': !show_table, 'col-6': show_table}">
                <h6 class="h6 table-title mb-4">Gráfico</h6>
                <canvas id="workattendance_chart"></canvas>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                filters: {
                    position_id: '',
                    payroll_employment_id: '',
                    from_date: '',
                    to_date: '',
                },
                records: [],
                workingDays: [],
                errors: [],
                positions: [],
                employments: [],
                show_table: true,
                show_graph: true,
                total_attendance_percent: 0,
                total_absence_percent: 0,
                totalWorkedTime: 0,
                graph_type: 'bar',
                graph_types: [
                    {id: '', text: 'Seleccione...'},
                    //{id: 'line', text: 'Línea'},
                    {id: 'bar', text: 'Barras'},
                    {id: 'pie', text: 'Pastel'},
                ],
                graph_min: 0,
                graph_max: 100,
                graph: null,
                columns: [
                    'day',
                    'date_at',
                    'entry_time',
                    'exit_time',
                    'work_time_formated',
                    'work_percent',
                    'id' //Se utiliza en este caso como columna para mostrar el % de inasistencia
                ]
            }
        },
        watch: {
            graph_type() {
                const _self = this;
                _self.saveGraph();
                _self.setChart();
            },
            records() {
                const _self = this;
                const btnPrint = document.getElementById('print');
                let btnPrintContent = '';
                _self.setPercents();
                if (_self.records.length == 0) {
                    _self.total_attendance_percent = 0;
                    _self.total_absence_percent = 0;
                    _self.graph_min = 0;
                    _self.graph_max = 100;
                } else {
                    _self.loading = true;
                    const min = _self.total_attendance_percent > _self.total_absence_percent ? _self.total_attendance_percent : _self.total_absence_percent;
                    const max = _self.total_attendance_percent < _self.total_absence_percent ? _self.total_attendance_percent : _self.total_absence_percent;
                    _self.graph_min = min < 0 ? min : 0;
                    _self.graph_max = max > 100 ? max : 100;
                    btnPrintContent = `
                        <a
                            href="${window.app_url}/work-attendance/print-history/individual:params"
                            class="btn btn-sm btn-primary btn-custom"
                            data-toggle="tooltip"
                            title="Imprimir registro" target="_blank"
                        >
                            <i class="fa fa-print"></i>
                        </a>
                    `;
                    _self.saveGraph();
                }
                _self.setChart();
                if (btnPrintContent) {
                    const position = `position_id=${_self.filters.position_id}`;
                    const employment = `payroll_employment_id=${_self.filters.payroll_employment_id}`;
                    const fromDate = `from_date=${_self.filters.from_date}`;
                    const toDate = `to_date=${_self.filters.to_date}`;
                    const params = `?${position}&${employment}&${fromDate}&${toDate}`;
                    btnPrintContent = btnPrintContent.replace(':params', params);
                }
                btnPrint.innerHTML = btnPrintContent;
            }
        },
        methods: {
            /**
             * Restablece los filtros de consulta
             *
             * @author  Ing. Roldan Vargas <rvargas<cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            resetFilters() {
                this.filters = {
                    position_id: '',
                    payroll_employment_id: '',
                    from_date: '',
                    to_date: '',
                };
            },
            /**
             * Ejecuta la acción para obtener los datos de acuerdo a la consulta realizada
             *
             * @author  Ing. Roldan Vargas <rvargas<cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            async searchData() {
                const _self = this;
                _self.errors = [];
                if (_self.filters.from_date == '') {
                    _self.errors.push('La fecha inicial es obligatoria.')
                };
                if (_self.filters.to_date == '') {
                    _self.errors.push('La fecha final es obligatoria.')
                };
                if (_self.filters.to_date && _self.filters.from_date && _self.filters.from_date > _self.filters.to_date) {
                    _self.errors.push('La fecha inicial no puede ser mayor a la fecha final.')
                };
                if (_self.filters.position_id == '') {
                    _self.errors.push('El cargo es obligatorio.')
                };
                if (_self.filters.payroll_employment_id == '') {
                    _self.errors.push('El personal es obligatorio.');
                }
                if (_self.errors.length > 0) {
                    return;
                }
                const params = {
                    position_id: _self.filters.position_id,
                    payroll_employment_id: _self.filters.payroll_employment_id,
                    from_date: _self.filters.from_date,
                    to_date: _self.filters.to_date
                };
                return axios.get(_self.route_list, { params })
                            .then(response => {
                                _self.records = response.data.records ?? [];
                                _self.workingDays = response.data.workingDays ?? [];
                                const totalTime = _self.records.reduce((total, record) => {
                                    // Separar horas y minutos
                                    const [horas, minutos] = record.work_time_formated.split(':').map(Number);
                                    // Convertir a minutos para poder realizar el cálculo
                                    return total + (horas * 60 + minutos);
                                }, 0);
                                _self.totalWorkedTime = '00:00';

                                if (totalTime > 0) {
                                    // Convertir el total de minutos de nuevo a formato "00:00"
                                    const totalHours = Math.floor(totalTime / 60);
                                    const totalMinutes = totalTime % 60;

                                    // Formatear a "00:00"
                                    _self.totalWorkedTime = `${String(totalHours).padStart(2, '0')}:${String(totalMinutes).padStart(2, '0')}`;
                                }

                                if (_self.records.length == 0) {
                                    _self.showMessage(
                                        'custom', 'Info', 'warning', 'screen-warning', 'No se encontraron registros.'
                                    );
                                }
                            })
                            .catch(error => console.error(error));
            },
            /**
             * Establece los porcentajes de asistencia e inasistencia
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            setPercents() {
                const _self = this;
                const totalPercent = _self.records.length > 0 ? (_self.records.reduce((a, b) => a + b.work_percent, 0) / _self.records.length).toFixed(2) : 0;
                _self.total_attendance_percent = totalPercent;
                _self.total_absence_percent = parseFloat(100 - totalPercent);
            },
            /**
             * Establece los datos del gráfico
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            setChart() {
                const _self = this;
                const ctx = document.getElementById('workattendance_chart');
                if (_self.graph !== null) {
                    _self.graph.destroy();
                }

                const dataBar = {
                    labels: ['Personal'],
                    datasets: [{
                        label: '% Asistencia',
                        data: [_self.total_attendance_percent],
                        backgroundColor: 'rgba(44, 168, 255, 1)',
                        borderColor: 'rgba(44, 168, 255, 1)',
                        borderWidth: 1
                    }, {
                        label: '% Inasistencia',
                        data: [_self.total_absence_percent],
                        backgroundColor: 'rgba(249, 99, 50, 1)',
                        borderColor: 'rgba(249, 99, 50, 1)',
                        borderWidth: 1
                    }]
                };
                const dataLine = {
                    labels: ['Personal'],
                    datasets: [{
                        label: 'Asistencia',
                        data: [_self.total_attendance_percent],
                        backgroundColor: 'rgba(44, 168, 255, 1)',
                        borderColor: 'rgba(44, 168, 255, 1)',
                        borderWidth: 1
                    }, {
                        label: 'Inasistencia',
                        data: [_self.total_absence_percent],
                        backgroundColor: 'rgba(249, 99, 50, 1)',
                        borderColor: 'rgba(249, 99, 50, 1)',
                        borderWidth: 1
                    }]
                };
                const dataPie = {
                    labels: ['Asistencia', 'Inasistencia'],
                    datasets: [{
                        label: 'Porcentaje',
                        data: [_self.total_attendance_percent, _self.total_absence_percent],
                        backgroundColor: [
                            'rgba(44, 168, 255, 1)',
                            'rgba(249, 99, 50, 1)'
                        ],
                        borderColor: [
                            'rgba(44, 168, 255, 1)',
                            'rgba(249, 99, 50, 1)'
                        ],
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
                            text: 'Porcentaje de asistencia e inasistencia del personal'
                        },
                        responsive: true,
                        tooltips: {
                            enabled: true,
                            callbacks: {
                                label: function(tooltipItem, data) {
                                    const label = data.datasets[tooltipItem.datasetIndex].label || '';
                                    const percent = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                                    const roundedPercent = isNaN(Number(percent)) ? '0.00' : Number(percent).toFixed(2);
                                    return `${label}: ${roundedPercent}%`;
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
                    }
                });
            },
            /**
             * Guarda el gráfico generado
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            async saveGraph() {
                const _self = this;
                setTimeout(async () => {
                    await axios.post(
                        `${window.app_url}/work-attendance/save-graph`,
                        {
                            image: _self.graph.toBase64Image(),
                            employee_id: _self.filters.payroll_employment_id
                        }
                    ).then((response) => {
                        _self.loading = false;
                    }).catch(error => {
                        _self.loading = false;
                    });
                }, 1000);
            }
        },
        created() {
            const sortable = [
                'day',
                'date_at'
            ];
			this.table_options.headings = {
				'day': 'Día',
                'date_at': 'Fecha',
                'entry_time': 'Hora de Entrada',
                'exit_time': 'Hora de Salida',
                'work_time_formated': 'Total Asistencia',
                'work_percent': '% Asistencia',
                'id': '% Inasistencia'
			};
			this.table_options.sortable = sortable;
			this.table_options.filterable = sortable;
			this.table_options.columnsClasses = {
				'day': 'col-2',
                'date_at': 'col-2',
                'entry_time': 'col-2',
                'exit_time': 'col-2',
                'work_time_formated': 'col-2',
                'work_percent': 'col-1',
				'id': 'col-1'
			};
		},
        async mounted() {
            const _self = this;
            await _self.getPositions();
            _self.employments = [{id: '', text: 'Seleccione...'}];
            _self.setChart();
        }
    }
</script>