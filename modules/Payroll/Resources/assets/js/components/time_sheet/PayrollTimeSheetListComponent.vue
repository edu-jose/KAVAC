<template>
    <div>
        <v-server-table ref="tableResults" :columns="columns" :url="route_list" :options="table_options">
            <div slot="document_status.name" slot-scope="props" class="text-center">
                <span v-if="props.row.document_status.action == 'EL'" class="text-warning">
                    {{ props.row.document_status.name }}
                </span>
                <span v-else-if="props.row.document_status.action == 'AP'" class="text-success">
                    {{ props.row.document_status.name }}
                </span>
                <span v-else-if="props.row.document_status.action == 'RE'" class="text-danger">
                    {{ props.row.document_status.name }}
                </span>
                <span v-else-if="props.row.document_status.action == 'CE'" class="text-default">
                    {{ props.row.document_status.name }}
                </span>
            </div>
            <div slot="id" slot-scope="props" class="text-center">
                <button @click.prevent="setDetails('TimeSheetInfo', props.row.id ,'PayrollTimeSheetInfo')"
                        class="btn btn-info btn-xs btn-icon btn-action btn-tooltip"
                        title="Ver registro"
                        data-toggle="tooltip"
                        data-placement="bottom"
                        type="button">
                    <i class="fa fa-eye"></i>
                </button>
                <button v-if="approve_permission"
                        class="btn btn-success btn-xs btn-icon btn-action"
                        title="Aprobar registro"
                        data-toggle="tooltip"
                        type="button"
                        @click="'CE' == props.row.document_status.action || 'AP' == props.row.document_status.action
                            ? 'javascript:void(0)' : approveTimeSheet(props.row.id)"
                        :disabled="'CE' == props.row.document_status.action || 'AP' == props.row.document_status.action">
                    <i class="fa fa-check"></i>
                </button>
                <button v-if="confirm_permission"
                        class="btn btn-default btn-xs btn-icon btn-action"
                        title="Confirmar registro"
                        data-toggle="tooltip"
                        @click="'EL' == props.row.document_status.action || 'RE' == props.row.document_status.action || 'CE' == props.row.document_status.action
                            ? 'javascript:void(0)' : confirmTimeSheet(props.row.id)"
                        type="button"
                        :disabled="'EL' == props.row.document_status.action || 'RE' == props.row.document_status.action || 'CE' == props.row.document_status.action">
                    <i class="fa fa-check"></i>
                </button>
                <button v-if="reject_permission"
                        class="btn btn-danger btn-xs btn-icon btn-action"
                        title="Rechazar registro"
                        data-toggle="tooltip"
                        @click="'EL' == props.row.document_status.action ||'RE' == props.row.document_status.action || 'CE' == props.row.document_status.action
                            ? 'javascript:void(0)' : rejectTimeSheet(props.row.id)"
                        type="button"
                        :disabled="'EL' == props.row.document_status.action || 'RE' == props.row.document_status.action || 'CE' == props.row.document_status.action">
                    <i class="fa fa-ban"></i>
                </button>
                <button
                        @click="'CE' == props.row.document_status.action
                            ? 'javascript:void(0)' : editForm(props.row.id)"
                        class="btn btn-warning btn-xs btn-icon btn-action"
                        title="Modificar registro" data-toggle="tooltip" type="button"
                        :disabled="'CE' == props.row.document_status.action">
                    <i class="fa fa-edit"></i>
                </button>
                <button
                        @click="'EL' != props.row.document_status.action
                            ? 'javascript:void(0)' : deleteRecord(props.row.id, 'payroll/time-sheet')"
                        class="btn btn-danger btn-xs btn-icon btn-action"
                        title="Eliminar registro" data-toggle="tooltip"
                        type="button" :disabled="'EL' != props.row.document_status.action">
                    <i class="fa fa-trash-o"></i>
                </button>
            </div>
        </v-server-table>
        <payroll-time-sheet-info ref="TimeSheetInfo"></payroll-time-sheet-info>
    </div>
</template>

<script>
export default {
    data() {
        return {
            records: [],
            columns: [
                'date',
                'payroll_supervised_group.code',
                'payroll_supervised_group.supervisor',
                'payroll_supervised_group.approver',
                'document_status.name',
                'id'
            ],
            route_list_vue: '',
        };
    },
    created() {
        const vm = this;
        vm.table_options.headings = {
            'payroll_supervised_group.supervisor': 'Supervisor',
            'payroll_supervised_group.approver': 'Aprobador',
            'payroll_supervised_group.code': 'Código',
            'document_status.name': 'Estatus',
            'date': 'Periodo',
            'id': 'Acción'
        };
        vm.table_options.sortable       = [
            'payroll_supervised_group.supervisor',
            'payroll_supervised_group.approver',
            'payroll_supervised_group.code',
            'document_status.name',
            'institution',
            'date',
        ];
        vm.table_options.filterable     = [
            'payroll_supervised_group.supervisor',
            'payroll_supervised_group.approver',
            'payroll_supervised_group.code',
            'document_status.name',
            'institution',
            'date',
        ];
        vm.table_options.columnsClasses = {
            'payroll_supervised_group.supervisor': 'col-xs-2 text-center',
            'payroll_supervised_group.approver': 'col-xs-2 text-center',
            'payroll_supervised_group.code': 'col-xs-2 text-center',
            'document_status.name': 'col-xs-2 text-center',
            'date': 'col-xs-2 text-center',
            'id': 'col-xs-2'
        };
        vm.table_options.orderBy = {
            column: 'id'
        };
    },
    mounted() {
    //    this.readRecords(this.route_list);
    //    this.route_list_vue = `${window.app_url}/payroll/time-sheet/vue-list`; // Set the vueList route

    },
    props: {
        approve_permission: String,
        reject_permission: String,
        confirm_permission: String,
    },
    methods: {
        /**
         * Inicializa los datos del formulario
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve | roldandvg@gmail.com>
         */
        reset() {
            // 
        },

                /**
         * Método para la eliminación de registros
         *
         * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         * @param  {integer} id    ID del Elemento seleccionado para su eliminación
         * @param  {string}  url   Ruta que ejecuta la acción para eliminar un registro
         */
        deleteRecord(id, url) {
            const vm = this;
            /** @type {string} URL que atiende la petición de eliminación del registro */
            var url = vm.setUrl((url) ? url : vm.route_delete);

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
                            vm.showMessage('destroy');
                            /** @type {array} Arreglo de registros filtrado sin el elemento eliminado */
                            vm.records = JSON.parse(JSON.stringify(vm.records.filter((rec) => {
                                return rec.id !== id;
                            })));
                            if (typeof (vm.$refs.tableResults) !== "undefined") {
                                vm.$refs.tableResults.refresh();
                            }
                        }).catch(error => {
                            if (typeof (error.response) != "undefined") {
                                if (error.response.status == 403) {
                                    vm.showMessage(
                                        'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                                    );
                                }
                            }
                        });
                        vm.loading = false;
                    }
                }
            });
        },
        /**
             * Método para reestablecer valores iniciales del formulario de filtros.
             *
             * @method resetFilters
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             * @author Argenis Osorio <aosorio@cenditel.gob.ve> | <aosorio@cenditel.gob.ve>
             */
            resetFilters() {
                const vm = this;
                vm.filterBy = {
                    compromised_at: '',
                    code: '',
                };
                vm.$refs.tableResults.refresh();
            },

            /**
             * Método que permite filtrar los datos de la tabla.
             *
             * @method filterTable
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            filterTable() {
                const vm = this;

                let params = {
                    query: vm.filterBy.compromised_at ? vm.format_date(vm.filterBy.compromised_at) : vm.filterBy.code,
                    limit: 10,
                    ascending: 1,
                    page: 1,
                    byColumn: 0
                }

                axios.get(`${window.app_url}/budget/compromises/list/all`, {params: params})
                .then(response => {
                        vm.$refs.tableResults.data = response.data.data;
                        vm.$refs.tableResults.count = response.data.count;
                    });
            },

        /**
         * Método que establece los datos del registro seleccionado para el
         * cual se desea mostrar detalles.
         *
         * @method    setDetails
         *
         * @author     Daniel Contreras <dcontreras@cenditel.gob.ve>
         *
         * @param     string   ref       Identificador del componente
         * @param     integer  id        Identificador del registro seleccionado
         * @param     object  var_list  Objeto con las variables y valores a
         * asignar en las variables del componente
         */
        setDetails(ref, id, modal ,var_list = null) {
            const vm = this;
            if (var_list) {
                for(var i in var_list){
                    vm.$refs[ref][i] = var_list[i];
                }
            }else{
                vm.$refs[ref].record = vm.$refs.tableResults.data.filter(r => {
                    return r.id === id;
                })[0];

                vm.$refs[ref].record.observations = vm.$refs[ref].record.observations?.split('<br>');
            }
            vm.$refs[ref].id = id;

            $(`#${modal}`).modal('show');
        },

        /**
         * Método que permite aprobar la hoja de tiempo
         *
         * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
         */
        approveTimeSheet(id) {
            const vm = this;
            const url = vm.setUrl(`payroll/time-sheet/approve/${id}`);

            bootbox.confirm({
                title: "Aprobar registro",
                className: 'mt-lg-5',
                message: "¿Está seguro? Una vez aprobado el registro no se podrá eliminar.",
                buttons: {
                    cancel: {
                        label: '<i class="fa fa-times"></i> No',
                        className: 'btn btn-default btn-sm'
                    },
                    confirm: {
                        label: '<i class="fa fa-check"></i> Si',
                        className: 'btn btn-primary btn-sm'
                    }
                },
                callback: function(result) {
                    if (result) {
                        vm.loading = true;

                        axios.put(url).then(response => {
                            if (response.status == 200){
                                location.reload();
                            }
                        }).catch(error => {
                            if (typeof(error.response) !="undefined") {
                                if (error.response.status == 403) {
                                    vm.showMessage(
                                        'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                                    );
                                }
                            }
                        });
                        vm.loading = false;
                    }
                }
            });
        },

        /**
         * Método que permite confirmar la hoja de tiempo
         *
         * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
         */
        confirmTimeSheet(id) {
            const vm = this;
            const url = vm.setUrl(`payroll/time-sheet/confirm/${id}`);

            bootbox.confirm({
                title: "Confirmar registro",
                className: 'mt-lg-5',
                message: "¿Está seguro? Una vez confirmado el registro no se podrá modificar.",
                buttons: {
                    cancel: {
                        label: '<i class="fa fa-times"></i> No',
                        className: 'btn btn-default btn-sm'
                    },
                    confirm: {
                        label: '<i class="fa fa-check"></i> Si',
                        className: 'btn btn-primary btn-sm'
                    }
                },
                callback: function(result) {
                    if (result) {
                        vm.loading = true;

                        axios.put(url).then(response => {
                            if (response.status == 200){
                                location.reload();
                            }
                        }).catch(error => {
                            if (typeof(error.response) !="undefined") {
                                if (error.response.status == 403) {
                                    vm.showMessage(
                                        'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                                    );
                                }
                            }
                        });
                        vm.loading = false;
                    }
                }
            });
        },

        /**
         * Método que permite rechazar la hoja de tiempo
         *
         * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
         */
        rejectTimeSheet(id) {
            const vm = this;
            const url = vm.setUrl(`payroll/time-sheet/reject/${id}`);

            let dialog = bootbox.confirm({
                title: '¿Rechazar hoja de tiempo?',
                size: 'medium',
                message:"<div class='row'>"+
                            "<div class='col-md-12'>"+
                                "<div class='form-group'>"+
                                    "<label>Observaciones</label>"+
                                    "<textarea data-toggle='tooltip' class='form-control input-sm'"+
                                        " title='Indique las observaciones presentadas en la hoja de tiempo'"+
                                        " id='observation'>"+
                                    "</textarea>"+
                                "</div>"+
                            "</div>"+
                        "</div>",
                buttons: {
                    cancel: {
                        label: '<i class="fa fa-times"></i> No',
                        className: 'btn btn-default btn-sm'
                    },
                    confirm: {
                        label: '<i class="fa fa-check"></i> Si',
                        className: 'btn btn-primary btn-sm'
                    }
                },
                callback: function(result) {
                    if (result) {
                        const observation = document.getElementById('observation').value;
                        vm.loading = true;

                        axios.put(url, {
                            observation: observation
                        }).then(response => {
                            if (response.status == 200){
                                location.reload();
                            }
                        }).catch(error => {
                            if (typeof(error.response) !="undefined") {
                                if (error.response.status == 403) {
                                    vm.showMessage(
                                        'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                                    );
                                }
                            }
                        });
                        vm.loading = false;
                    }
                }
            });
        }
    },
};
</script>

