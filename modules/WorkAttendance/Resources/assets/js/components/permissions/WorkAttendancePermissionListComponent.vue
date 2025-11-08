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
        <h6 class="h6 table-title mt-3 mb-3">Registros de permisos</h6>
        <v-server-table
            :columns="columns"
            :options="table_options"
            ref="tableResults"
        >
            <div slot="start_date_at" slot-scope="props" class="text-center">
                {{ format_date(props.row.start_date_at, 'DD/MM/YYYY') }}
                <span class="ml-1" v-if="props.row.start_time_at">{{ formatTime(props.row.start_time_at) }}</span>
            </div>
            <div slot="end_date_at" slot-scope="props" class="text-center">
                {{ format_date(props.row.end_date_at, 'DD/MM/YYYY') }}
                <span class="ml-1" v-if="props.row.end_time_at">{{ formatTime(props.row.end_time_at) }}</span>
            </div>

            <div slot="payroll_staff" slot-scope="props" class="text-justify">
                <span>{{ props.row.payroll_staff.first_name }} {{ props.row.payroll_staff.last_name }}</span>
            </div>

            <div slot="reason" slot-scope="props" class="text-justify">
                <div v-html="props.row.reason"></div>
            </div>

            <div slot="status" slot-scope="props" class="text-center">
                <span v-if="props.row.status === 'pending'" class="badge badge-warning">Pendiente</span>
                <span v-else-if="props.row.status === 'approved'" class="badge badge-success">Aprobado</span>
                <span v-else-if="props.row.status === 'rejected'" class="badge badge-danger">Rechazado</span>
                <span v-else class="badge badge-secondary">Desconocido</span>
            </div>

            <div slot="id" slot-scope="props" class="text-center">
                <div class="d-inline-flex">
                    <a
                        :href="showSupport(props.row.document.url)"
                        target="_blank"
                        class="btn btn-primary btn-xs btn-icon btn-action"
                        title="Ver soporte que avala el registro"
                        data-toggle="tooltip"
                        v-if="props.row.document"
                    >
                        <i class="fa fa-file-text-o"></i>
                    </a>
                    <workattendance-permission-info
                            :infoData="props.row"
                            :permissionId="props.row.id"
                    ></workattendance-permission-info>
                    <button
                        type="button"
                        @click="editForm(props.row.id)"
                        class="btn btn-warning btn-xs btn-icon btn-action" title="Modificar registro"
                        data-toggle="tooltip"
                    >
                        <i class="fa fa-edit"></i>
                    </button>
                    <button
                        @click="deleteRecord(props.row.id, 'work-attendance/permissions')"
                        class="btn btn-danger btn-xs btn-icon btn-action"
                        title="Eliminar registro" data-toggle="tooltip"
                        type="button"
                        :disabled="props.row.status !== 'pending'"
                    >
                        <i class="fa fa-trash-o"></i>
                    </button>
                </div>
            </div>
        </v-server-table>
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
                positions: [],
                employments: [],
                columns: [
                    'start_date_at',
                    'end_date_at',
                    'payroll_staff',
                    'reason',
                    'status',
                    'id'
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
                        start_date_at: "Fecha y Hora de Inicio",
                        end_date_at: "Fecha y Hora de Fin",
                        payroll_staff: "Personal",
                        reason: "Motivo",
                        status: "Estatus",
                    },
                    columnsClasses: {
                        start_date_at: 'col-md-2',
                        end_date_at: 'col-md-2',
                        payroll_staff: 'col-md-2',
                        reason: 'col-md-2',
                        status: 'col-md-2',
                    },
                    sortable: ['date_at', 'payroll_staff_id'],
                    filterable: false,
                    orderBy: {column: 'start_date_at', ascending: false},
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
                                return response.data
                            })
                            .catch(error => console.error(error));
            },
            deleteRecord(id, url) {
                const vm = this;
                /** @type {string} URL que atiende la petición de eliminación del registro */
                url = vm.setUrl(url || vm.route_delete);

                bootbox.confirm({
                    title: "¿Eliminar registro?",
                    message: "¿Está seguro de eliminar este registro?",
                    buttons: {
                        cancel: {
                            label: '<i class="fa fa-times"></i> Cancelar'
                        },
                        confirm: {
                            label: '<i class="fa fa-check"></i> Confirmar'
                        }
                    },
                    callback: async function (result) {
                        if (result) {
                            vm.loading = true;

                            await axios.delete(`${url}${url.endsWith('/') ? '' : '/'}${id}`).then(response => {
                                if (typeof (response.data.error) !== "undefined") {
                                    /** Muestra un mensaje de error si sucede algún evento en la eliminación */
                                    vm.showMessage('custom', 'Alerta!', 'warning', 'screen-error', response.data.message);
                                    return false;
                                }
                                vm.$refs.tableResults.refresh();
                                vm.showMessage('destroy');
                            }).catch(error => {
                                if (typeof (error.response) != "undefined") {
                                    if (error.response.status == 403) {
                                        vm.showMessage(
                                            'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                                        );
                                    }
                                }
                                vm.logs('mixins.js', 498, error, 'deleteRecord');
                            });
                            vm.loading = false;
                        }
                    }
                });
            },
            showSupport(url) {
                return `${window.app_url}/${url}`;
            },
        },
        created() {
            this.table_options.headings = {
                start_date_at: "Fecha y Hora de Inicio",
                end_date_at: "Fecha y Hora de Fin",
                payroll_staff: "Personal",
                reason: 'Motivo',
                status: 'Estatus',
                id: 'Acción'
            };
            this.table_options.sortable = [
                "start_date_at",
                "end_date_at",
                "payroll_staff",
                "reason",
                "status",
            ];
            this.table_options.filterable = false;
            this.table_options.orderBy = { column: "id" };
            this.table_options.columnsClasses = {
                'start_date_at': 'col-md-2',
                'end_date_at': 'col-md-2',
                'payroll_staff': 'col-md-2',
                'reason': 'col-md-2',
                'status': 'col-md-2',
                'id': 'col-md-2'
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