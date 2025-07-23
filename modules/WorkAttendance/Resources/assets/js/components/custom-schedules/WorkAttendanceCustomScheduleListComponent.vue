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
                    <label for="payrollEmploymentId">Persona</label>
                    <select2
                        id="payrollEmploymentId" :options="payroll_staffs"
                        v-model="filters.payroll_staff_id"
                        title="Seleccione el personal a consultar"
                        data-toggle="tooltip"
                    />
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
            </div>
        </form>
        <hr class="mt-3 mb-3">
        <h6 class="h6 table-title mt-3 mb-3">Registros de horarios personalizados</h6>
        <v-client-table
            :columns="columns"
            :options="table_options"
            :data="records"
        >
            <div slot="start_date_at" slot-scope="props" class="text-center">
                {{ format_date(props.row.start_date_at, 'DD/MM/YYYY') }}
            </div>
            <div slot="end_date_at" slot-scope="props" class="text-center">
                {{ format_date(props.row.end_date_at, 'DD/MM/YYYY') }}
            </div>

            <div slot="payroll_staff" slot-scope="props" class="text-justify">
                {{ props.row.payroll_staff.first_name }} {{ props.row.payroll_staff.last_name }}
            </div>

            <div slot="reason" slot-scope="props" class="text-justify">
                <div v-html="props.row.reason"></div>
            </div>

            <div slot="id" slot-scope="props" class="text-center">
                <div class="d-inline-flex">
                    <workattendance-custom-schedule-info
                        :infoData="props.row"
                        :customScheduleId="props.row.id"
                    ></workattendance-custom-schedule-info>
                    <button
                        type="button"
                        @click="editForm(props.row.id)"
                        class="btn btn-warning btn-xs btn-icon btn-action" title="Modificar registro"
                        data-toggle="tooltip"
                    >
                        <i class="fa fa-edit"></i>
                    </button>
                    <button @click="deleteRecord(props.row.id, 'work-attendance/custom-schedules')"
                            class="btn btn-danger btn-xs btn-icon btn-action"
                            title="Eliminar registro" data-toggle="tooltip"
                            type="button">
                        <i class="fa fa-trash-o"></i>
                    </button>
                </div>
            </div>
        </v-client-table>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                filters: {
                    position_id: '',
                    payroll_staff_id: '',
                    from_date: '',
                    to_date: '',
                },
                records: [],
                positions: [],
                payroll_staffs: [],
                columns: [
                    'start_date_at',
                    'end_date_at',
                    'payroll_staff',
                    'reason',
                    'id'
                ],
            }
        },
        methods: {
            resetFilters() {
                this.filters = {
                    position_id: '',
                    payroll_staff_id: '',
                    from_date: '',
                    to_date: '',
                };
            },
            async searchData() {
                const vm = this;
                const params = {
                    ascending: 1,
                    page: 1,
                    byColumn: 0,
                    orderBy: 'id',
                    limit: 10,
                    payroll_staff_id: vm.filters.payroll_staff_id,
                    from_date: vm.filters.from_date,
                    to_date: vm.filters.to_date
                };
                await axios.get(vm.route_list, { params }).then(response => {
                    vm.records = response.data.records;
                }).catch(error => console.error(error));
            },
        },
        created() {
            this.table_options.headings = {
                start_date_at: "Fecha de Inicio",
                end_date_at: "Fecha de Finalización",
                payroll_staff: "Personal",
                reason: 'Motivo',
                id: 'Acción'
            };
            this.table_options.sortable = [
                "start_date_at",
                "end_date_at",
                "payroll_staff",
                "reason"
            ];
            this.table_options.filterable = false;
            this.table_options.orderBy = { column: "id" };
            this.table_options.columnsClasses = {
                'start_date_at': 'col-md-2',
                'end_date_at': 'col-md-2',
                'payroll_staff': 'col-md-4',
                'reason': 'col-md-2',
                'id': 'col-md-2'
			};
        },
        async mounted() {
            const _self = this;
            await _self.getStaffs();
            await _self.initRecords(_self.route_list);
        }
    }
</script>
