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
                        <label for="department">Unidad / Departamento</label>
                        <select2
                            id="department" :options="departments"
                            v-model="filters.department_id"
                            title="Seleccione la unidad o departamento a consultar"
                            data-toggle="tooltip"
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
            <div class="col-md-7 mt-4" v-if="show_table">
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
            <div class="mt-4" :class="{'col-12': !show_table, 'col-5': show_table}">
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
                    from_date: '',
                    to_date: '',
                    department_id: ''
                },
                errors: [],
                records: [],
                departments: [],
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
                    'employee.id_number',
                    'employee.full_name',
                    'employee.position',
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
                            href="${window.app_url}/work-attendance/print-history/by-department:params"
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
                    const department = `department_id=${_self.filters.department_id}`;
                    const fromDate = `from_date=${_self.filters.from_date}`;
                    const toDate = `to_date=${_self.filters.to_date}`;
                    const params = `?${department}&${fromDate}&${toDate}`;
                    btnPrintContent = btnPrintContent.replace(':params', params);
                }
                btnPrint.innerHTML = btnPrintContent;
            }
        },
        methods: {
            resetFilters() {
                this.filters = {
                    from_date: '',
                    to_date: '',
                    department_id: ''
                };
                this.records = [];
            },
            searchData() {
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
                if (_self.filters.department_id == '') {
                    _self.errors.push('La unidad o departamento es obligatorio.')
                };
                if (_self.errors.length > 0) {
                    return;
                }
                _self.loading = true;
                const params = {
                    from_date: _self.filters.from_date,
                    to_date: _self.filters.to_date,
                    department_id: _self.filters.department_id
                };
                return axios.get(_self.route_list, { params })
                            .then(response => {
                                _self.records = response.data.records ?? [];
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
                                _self.loading = false;
                            })
                            .catch(error => {
                                console.error(error);
                                _self.loading = false;
                            });
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
                _self.graph_max = _self.total_attendance_percent > _self.total_absence_percent ? _self.total_attendance_percent : _self.total_absence_percent;
                _self.graph_min = _self.total_attendance_percent < _self.total_absence_percent ? _self.total_attendance_percent : _self.total_absence_percent;
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
                        `${window.app_url}/work-attendance/save-graph/by-department`,
                        {
                            image: _self.graph.toBase64Image(),
                            department_id: _self.filters.department_id
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
                'employee.id_number',
                'employee.full_name',
                'employee.position',
                'day',
                'date_at'
            ];
			this.table_options.headings = {
				'employee.id_number': 'C.I.',
                'employee.full_name': 'Nombres y Apellidos',
                'employee.position': 'Cargo',
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
				'employee.id_number': 'col-1',
				'employee.full_name': 'col-2',
                'employee.position': 'col-2',
                'day': 'col-1',
                'date_at': 'col-1',
                'entry_time': 'col-1',
                'exit_time': 'col-1',
                'work_time_formated': 'col-1',
                'work_percent': 'col-1',
				'id': 'col-1'
			};
		},
        async mounted() {
            const _self = this;
            await _self.getDepartments();
            _self.setChart();
        }
    }
</script>