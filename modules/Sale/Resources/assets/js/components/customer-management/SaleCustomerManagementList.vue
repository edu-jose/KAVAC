<template>
    <section>
        <v-server-table 
            :columns="columns" 
            :options="table_options" 
            :url="route_list"
            ref="tableResults"
        >
            <!-- Columna ID -->
            <div slot="id" slot-scope="props" class="text-center">
                <span>{{ props.row.id }}</span>
            </div>

            <!-- Columna Identificador -->
            <div slot="identifier" slot-scope="props" class="text-center">
                <span>{{ props.row.identifier_type }}-{{ props.row.identification_number }}</span>
            </div>

            <!-- Columna Nombre -->
            <div slot="name" slot-scope="props">
                <span>{{ props.row.name }}</span>
            </div>

            <!-- Columna Dirección Fiscal -->
            <div slot="fiscal_address" slot-scope="props">
                <span>{{ props.row.fiscal_address }}</span>
            </div>

            <!-- Columna Teléfonos -->
            <div slot="phones" slot-scope="props" class="text-center">
                <span v-for="(phone, index) in props.row.phones" :key="index">
                    {{ phoneTypeText(phone.type) }}: 
                    {{ phone.area_code }}-{{ phone.number }}
                    <span v-if="phone.extension">Ext. {{ phone.extension }}</span>
                    <br v-if="index < props.row.phones.length - 1">
                </span>
                <span v-if="!props.row.phones || props.row.phones.length === 0">
                    N/A
                </span>
            </div>

            <!-- Columna Acciones -->
            <div slot="actions" slot-scope="props" class="text-center">
                <button @click.prevent="setDetails('CustomerInfo', props.row.id, 'SaleCustomerInfo')"
                    class="btn btn-info btn-xs btn-icon btn-action btn-tooltip" 
                    title="Ver registro"
                    data-toggle="tooltip" 
                    data-placement="bottom" 
                    type="button">
                    <i class="fa fa-eye"></i>
                </button>

                <button @click="editForm(props.row.id)"
                    class="btn btn-warning btn-xs btn-icon btn-action btn-tooltip" 
                    title="Modificar registro"
                    data-toggle="tooltip" 
                    data-placement="bottom" 
                    type="button">
                    <i class="fa fa-edit"></i>
                </button>

                <button @click="deleteRecord(props.row.id)"
                    class="btn btn-danger btn-xs btn-icon btn-action btn-tooltip" 
                    title="Eliminar registro"
                    data-toggle="tooltip" 
                    data-placement="bottom" 
                    type="button">
                    <i class="fa fa-trash-o"></i>
                </button>
            </div>
        </v-server-table>
        <!-- Componente Modal para mostrar detalles -->
        <sale-customer-management-show ref="CustomerInfo"></sale-customer-management-show>
    </section>
</template>

<script>
export default {
    props: {
        route_list: {
            type: String,
            required: true
        },
        route_edit: {
            type: String,
            default: ''
        },
        route_delete: {
            type: String,
            default: ''
        },
        route_show: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            record: [],
            columns: ['id', 'identifier', 'name', 'fiscal_address', 'phones', 'actions'],
            table_options: {
                headings: {
                    'id': 'Código',
                    'identifier': 'Identificador',
                    'name': 'Nombre/Razón Social',
                    'fiscal_address': 'Dirección Fiscal',
                    'phones': 'Teléfonos',
                    'actions': 'Acciones'
                },
                sortable: ['id', 'name'],
                filterable: ['id', 'name', 'identification_number'],
                columnsClasses: {
                    'id': 'col-md-1 text-center',
                    'identifier': 'col-md-2 text-center',
                    'name': 'col-md-2',
                    'fiscal_address': 'col-md-3',
                    'phones': 'col-md-2 text-center',
                    'actions': 'col-md-2 text-center'
                },
                texts: {
                    filter: 'Buscar:',
                    count: 'Mostrando {from} a {to} de {count} registros|{count} registros|Un registro',
                    first: 'Primero',
                    last: 'Último',
                    filterPlaceholder: 'Buscar...',
                    limit: 'Registros:',
                    page: 'Página:',
                    noResults: 'No se encontraron registros',
                    filterBy: 'Filtrar por {column}',
                    loading: 'Cargando...',
                    defaultOption: 'Seleccionar {column}',
                    columns: 'Columnas'
                },
                skin: 'table table-hover table-striped table-bordered',
                pagination: {
                    chunk: 7,
                    edge: true,
                    nav: 'scroll'
                },
                perPage: 10,
                perPageValues: [10, 25, 50, 100],
                requestFunction: function (data) {
                    return axios.get(this.url, {
                        params: data
                    }).catch(function (e) {
                        this.dispatch('error', e);
                    }.bind(this));
                },
                responseAdapter: function (resp) {
                    var data = resp.data;
                    return {
                        data: data.data,
                        count: data.count
                    };
                }
            }
        }
    },
    methods: {
        /**
         * Método que establece los datos del registro seleccionado para mostrar detalles
         */
        setDetails(ref, id, modal, var_list = null) {
            const vm = this;
            
            // Buscar el registro en los datos de la tabla
            const record = vm.$refs.tableResults.data.filter(r => {
                return r.id === id;
            })[0];
            
            if (record) {
                vm.$refs[ref].record = record;
                vm.$refs[ref].id = id;

                // Mostrar el modal
                $(`#${modal}`).modal('show');
            }
        },

        /**
         * Redirige al formulario de edición
         */
        editForm(id) {
            if (this.route_edit) {
                window.location.href = this.route_edit.replace('{id}', id);
            }
        },

        /**
         * Método para eliminar registros
         */
        deleteRecord(id) {
            const vm = this;
            const deleteUrl = `${vm.route_delete}/${id}`;

            bootbox.confirm({
                title: "¿Eliminar registro?",
                message: "¿Está seguro de eliminar este cliente?",
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
                        try {
                            await axios.delete(deleteUrl);
                            vm.showMessage('destroy');
                            vm.$refs.tableResults.refresh();
                        } catch (error) {
                            if (error.response && error.response.status == 403) {
                                vm.showMessage(
                                    'custom', 'Acceso Denegado', 'danger', 'screen-error', 
                                    error.response.data.message || 'No tiene permisos para esta acción'
                                );
                            } else {
                                vm.showMessage(
                                    'custom', 'Error', 'warning', 'screen-error', 
                                    error.response?.data?.message || 'Error al eliminar el registro'
                                );
                            }
                        }
                    }
                }
            });
        },

        /**
         * Retorna el texto del tipo de teléfono
         */
        phoneTypeText(type) {
            const types = {
                'mobile': 'Móvil',
                'phone': 'Teléfono',
                'fax': 'Fax'
            };
            return types[type] || type;
        },

        /**
         * Muestra mensajes al usuario
         */
        showMessage(type, title = '', style = 'success', icon = '', text = '') {
            const messages = {
                'destroy': { 
                    title: 'Éxito', 
                    text: 'Cliente eliminado exitosamente.', 
                    style: 'success', 
                    icon: 'fa-check' 
                }
            };
            
            if (messages[type]) {
                const msg = messages[type];
                // Usar el sistema de notificaciones de tu aplicación
                $.gritter.add({
                    title: msg.title,
                    text: msg.text,
                    class_name: `growl-${msg.style}`,
                    image: `/images/screen-ok.png`,
                    sticky: false,
                    time: 3500
                });
            } else if (type === 'custom') {
                $.gritter.add({
                    title: title,
                    text: text,
                    class_name: `growl-${style}`,
                    image: style === 'danger' ? '/images/screen-error.png' : '/images/screen-warning.png',
                    sticky: false,
                    time: 3500
                });
            }
        }
    }
};
</script>
