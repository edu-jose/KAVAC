<template>
    <section>
        <v-client-table
            :columns="columns"
            :data="records"
            :options="table_options"
            ref="tableResults"
        >
            <div slot="id" slot-scope="props" class="text-center">
                <button
                    class="btn btn-info btn-xs btn-icon btn-action btn-tooltip"
                    type="button"
                    data-placement="bottom"
                    data-toggle="tooltip"
                    title="Ver registro"
                    @click.prevent="setDetails('SpecificActionInfo', props.row.id, 'BudgetSpecificActionsInfo')"
                >
                    <i class="fa fa-eye"></i>
                </button>
                <template v-if="(lastYear && format_date(props.row.to_date, 'YYYY') <= lastYear)">
                    <button
                        class="btn btn-warning btn-xs btn-icon btn-action"
                        type="button"
                        data-toggle="tooltip"
                        title="Modificar registro"
                        @click="editForm(props.row.id)"
                    >
                        <i class="fa fa-edit"></i>
                    </button>
                    <button
                        class="btn btn-danger btn-xs btn-icon btn-action"
                        type="button"
                        disabled
                    >
                        <i class="fa fa-trash-o"></i>
                    </button>
                </template>
                <template v-else>
                    <button
                        class="btn btn-warning btn-xs btn-icon btn-action"
                        type="button"
                        data-toggle="tooltip"
                        title="Modificar registro"
                        @click="editForm(props.row.id)"
                    >
                        <i class="fa fa-edit"></i>
                    </button>
                    <button
                        v-if="!props.row.disabled"
                        class="btn btn-danger btn-xs btn-icon btn-action"
                        type="button"
                        data-toggle="tooltip"
                        title="Eliminar registro"
                        @click="deleteRecord(props.row.id, '')"
                    >
                        <i class="fa fa-trash-o"></i>
                    </button>
                </template>
            </div>
            <div slot="specificable_type" slot-scope="props">
                <span v-if="props.row.specificable_type=='Modules\\Budget\\Models\\BudgetProject'">
                    Proyecto
                </span>
                <span v-else>Acción Centralizada</span>
            </div>
            <div slot="active" slot-scope="props" class="text-center">
                <span v-if="props.row.active" class="font-weight-bold text-success">SI</span>
                <span v-else class="font-weight-bold text-danger">NO</span>
            </div>
        </v-client-table>
        <budget-info-specific-actions ref="SpecificActionInfo"></budget-info-specific-actions>
    </section>
</template>

<script>
    export default {
        data() {
            return {
                records: [],
                lastYear: "",
                columns: [
                    'code',
                    'name',
                    'specificable_type',
                    'active',
                    'id'
                ]
            }
        },
        created() {
            this.table_options.headings = {
                'code': 'Código',
                'name': 'Acción Específica',
                'specificable_type': 'Proyecto / Acc. Centralizada',
                'active': 'Activa',
                'id': 'Acción'
            };
            this.table_options.sortable = ['code', 'name', 'specificable_type'];
            this.table_options.filterable = ['code', 'name', 'specificable_type'];
            this.table_options.columnsClasses = {
                'code': 'col-md-2',
                'name': 'col-md-4',
                'specificable_type': 'col-md-3',
                'active': 'col-md-1',
                'id': 'col-md-2'
            };
        },
        async mounted() {
            const vm = this;
            vm.initRecords(vm.route_list, '');
            await vm.queryLastFiscalYear();
        },
        methods: {
            /**
             * Inicializa los datos del formulario
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            reset() {},

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
             * @param     object  var_list  Objeto con las variables y valores
             * a asignar en las variables del componente.
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
                }
                vm.$refs[ref].id = id;
                $(`#${modal}`).modal('show');
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
                            /** @type {object} Objeto con los datos del registro a eliminar */
                            let recordDelete = JSON.parse(JSON.stringify(vm.records.filter((rec) => {
                                return rec.id === id;
                            })[0]));

                            await axios.delete(`${url}${url.endsWith('/') ? '' : '/'}${recordDelete.id}`).then(response => {
                                if (typeof (response.data.error) !== "undefined") {
                                    /** Muestra un mensaje de error si sucede algún evento en la eliminación */
                                    vm.showMessage('custom', 'Alerta!', 'warning', 'screen-error', response.data.message);
                                    return false;
                                }
                                // /** @type {array} Arreglo de registros filtrado sin el elemento eliminado */
                                vm.records = JSON.parse(JSON.stringify(vm.records.filter((rec) => {
                                    return rec.id !== id;
                                })));
                                vm.showMessage('destroy');
                            }).catch(error => {
                                if (typeof (error.response) != "undefined") {
                                    if (error.response.status == 403) {
                                        vm.showMessage(
                                            'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                                        );
                                    }
                                }
                                vm.loading = false;
                                vm.logs('mixins.js', 498, error, 'deleteRecord');
                            });
                            vm.loading = false;
                        }
                    }
                });
            },
        }
    };
</script>
