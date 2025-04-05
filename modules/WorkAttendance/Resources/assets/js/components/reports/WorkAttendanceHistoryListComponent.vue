<template>
    <div>
        <form @submit.prevent="searchData">
            <div class="row">
                <div class="col-12">
                    <b>Filtros</b>
                </div>
                <div class="form-group col-md-2">
                    <label for="from_date">Desde</label>
                    <input
                        type="date" name="from_date" id="from_date"
                        class="form-control" v-model="filters.from_date"
                        title="Indique la fecha inicial a consultar"
                        data-toggle="tooltip"
                    >
                </div>
                <div class="form-group col-md-2">
                    <label for="to_date">Hasta</label>
                    <input
                        type="date" name="to_date" id="to_date"
                        class="form-control" v-model="filters.to_date"
                        title="Indique la fecha final a consultar"
                        data-toggle="tooltip"
                    >
                </div>
                <div class="form-group col-md-3">
                    <label for="positionId">Cargo</label>
                    <select2
                        id="positionId" :options="positions"
                        v-model="filters.position_id"
                        @input="setEmployments('filters')"
                        title="Seleccione el cargo del personal a consultar"
                        data-toggle="tooltip"
                    />
                </div>
                <div class="form-group col-md-3">
                    <label for="payrollEmploymentId">Persona</label>
                    <select2
                        id="payrollEmploymentId" :options="employments"
                        v-model="filters.payroll_employment_id"
                        title="Seleccione el personal a consultar"
                        data-toggle="tooltip"
                    />
                </div>
                <div class=" form-group col-md-2">
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
            </div>
        </form>
        <hr class="mt-3 mb-3">
        <h6 class="h6 table-title mt-3 mb-3">Registros de asistencia</h6>
        <v-server-table
            :columns="columns"
            :options="table_options"
            ref="tableResults"
        >
            <div slot="day_name" slot-scope="props" class="text-center">
                {{ getWeekDay(props.row.date_at) }}
            </div>
            <div slot="date_at" slot-scope="props" class="text-center">
                {{ format_date(props.row.date_at) }}
            </div>
            <div slot="payroll_staff_id" slot-scope="props" class="text-center">
                {{ props.row.payroll_staff.first_name }} {{ props.row.payroll_staff.last_name }}
            </div>
            <div slot="entry_time" slot-scope="props" class="text-center">
                {{ props.row.entry_time ?? '---' }}
            </div>
            <div slot="exit_time" slot-scope="props" class="text-center">
                {{ props.row.exit_time ?? '---' }}
            </div>
            <div slot="attendance_time" slot-scope="props" class="text-center">
                {{ props.row.work_time_formated ?? '---' }}
            </div>
        </v-server-table>
    </div>
</template>

<script>
    /** Componente mostrar el listado de asistencia del personal */
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
                positions: [],
                employments: [],
                workingDays: [],
                columns: [
                    'day_name',
                    'date_at',
                    'payroll_staff.payroll_employment.payrollPosition.name',
                    'payroll_staff_id',
                    'entry_time',
                    'exit_time',
                    'attendance_time'
                ],
                table_options: {
                    requestFunction: this.customRequestFunction,
                    responseAdapter: function(response) {
                        return {
                            data: response.data,
                            count: response.count
                        };
                    },
                    headings: {
                        day_name: "Día",
                        date_at: "Fecha",
                        'payroll_staff.payroll_employment.payrollPosition.name': "Cargo",
                        payroll_staff_id: "Nombres y Apellidos",
                        entry_time: 'Hora de entrada',
                        exit_time: 'Hora de salida',
                        attendance_time: 'Total Asistencia'
                    },
                    columnsClasses: {
                        day_name: 'col-md-2',
                        date_at: 'col-md-2',
                        'payroll_staff.payroll_employment.payrollPosition.name': 'col-md-2',
                        payroll_staff_id: 'col-md-2',
                        entry_time: 'col-md-1',
                        exit_time: 'col-md-1',
                        attendance_time: 'col-md-2'
                    },
                    sortable: ['date_at', 'payroll_staff_id'],
                    filterable: false,
                    orderBy: {column: 'date_at', ascending: false},
                    perPageValues: [5, 10, 20, 50]
                }
            }
        },
        methods: {
            resetFilters() {
                this.filters = {
                    position_id: '',
                    payroll_employment_id: '',
                    from_date: '',
                    to_date: '',
                };
            },
            searchData() {
                this.$refs['tableResults'].refresh();
            },
            /**
             * Método que obtiene los registros a mostrar
             *
             * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             *
             * @param  {string} url Ruta que obtiene todos los registros solicitados
             */
            customRequestFunction(data) {
                const vm = this;
                const { page, limit, orderBy, ascending } = data;
                const params = {
                    ascending: ascending,
                    page: page,
                    byColumn: 0,
                    orderBy: orderBy,
                    limit: limit,
                    position_id: vm.filters.position_id,
                    payroll_employment_id: vm.filters.payroll_employment_id,
                    from_date: vm.filters.from_date,
                    to_date: vm.filters.to_date
                };
                return axios.get(vm.route_list, { params })
                            .then(response => {
                                vm.workingDays = response.data.workingDays ?? [];
                                return response.data
                            })
                            .catch(error => console.error(error));
            },
        },
        created() {
            this.table_options.headings = {
                day_name: "Día",
                date_at: "Fecha",
                'payroll_staff.payroll_employment.payrollPosition.name': "Cargo",
                payroll_staff_id: "Nombres y Apellidos",
                entry_time: 'Hora de entrada',
                exit_time: "Hora de salida",
                attendance_time: 'Total Asistencia'
            };
            this.table_options.sortable = [
                "day_name",
                "date_at",
                "payroll_staff.payroll_employment.payrollPosition.name",
                "payroll_staff_id",
                "entry_time",
                "exit_time",
            ];
            this.table_options.filterable = false;
            this.table_options.orderBy = { column: "id" };
            this.table_options.columnsClasses = {
                'day_name': 'col-md-2',
				'date_at': 'col-md-2',
                'payroll_staff.payroll_employment.payrollPosition.name': 'col-md-2',
				'payroll_staff_id': 'col-md-2',
                'entry_time': 'col-md-1',
                'exit_time': 'col-md-1',
                'attendance_time': 'col-md-2',
			};
            this.table_options.requestFunction = this.customRequestFunction;
        },
        async mounted() {
            const _self = this;
            await _self.getPositions();
            _self.employments = [{id: '', text: 'Seleccione...'}];
        }
    }
</script>
