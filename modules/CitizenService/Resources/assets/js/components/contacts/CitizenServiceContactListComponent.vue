<template>
    <section>
        <v-client-table :columns="columns" :data="records" :options="table_options">
            <div slot="name" slot-scope="props">
                {{ props.row.name }} {{ props.row.surname }}
            </div>
            <div slot="position" slot-scope="props">
                <span v-if="props.row.position">
                    {{ props.row.position }}
                </span>
                <span v-else>
                    N/A
                </span>
            </div>
            <div slot="phones" slot-scope="props">
                <ul>
                    <li class="text-left" v-for="phone in props.row.phones" :key="phone.id">
                        <span>{{ phone.area_code }} {{ phone.number }}</span>
                        <span v-if="phone.extension">
                            <span> ext. {{ phone.extension }}</span>
                        </span>
                    </li>
                </ul>
            </div>
            <div slot="id" slot-scope="props" class="text-center">
                <div class="d-inline-flex">
                    <button
                        @click="editForm(props.row.id)"
                        class="btn btn-warning btn-xs btn-icon btn-action"
                        title="Modificar registro" data-toggle="tooltip" type="button" v-has-tooltip
                    >
                        <i class="fa fa-edit"></i>
                    </button>
                    <button
                        @click="deleteRecord(props.row.id, '')"
                        class="btn btn-danger btn-xs btn-icon btn-action"
                        title="Eliminar registro" data-toggle="tooltip" type="button" v-has-tooltip
                    >
                        <i class="fa fa-trash-o"></i>
                    </button>
                </div>
            </div>
        </v-client-table>
    </section>
</template>

<script>
    export default {
        data() {
            return {
                records: [],
                columns: [
                    'identity_card',
                    'name',
                    'served_institution.name',
                    'position',
                    'description',
                    'phones',
                    'id'
                ],
            };
        },
        created() {
            this.table_options.headings = {
                'identity_card': 'Cédula',
                'name': 'Nombres y Apellidos',
                'served_institution.name': 'Institución',
                'position':'Cargo',
                'description': 'Descripción',
                'phones': 'Teléfonos',
                'id': 'Acción'
            };
            this.table_options.sortable = [
                'identity_card',
                'name',
                'served_institution.name',
                'position',
            ];
            this.table_options.filterable = [
                'identity_card',
                'name',
                'served_institution.name',
                'position',
            ];
            this.table_options.columnsClasses = {
                'identity_card': 'col-md-1 text-center',
                'name': 'col-md-2 text-left',
                'served_institution.name': 'col-md-2 text-left',
                'position': 'col-md-1 text-center',
                'description': 'col-md-2 text-left',
                'phones': 'col-md-2 text-center',
                'id': 'col-md-2 text-center',
            };
        },
        async mounted () {
            const _self = this;
            _self.initRecords(_self.route_list, '');
        },
    }
</script>