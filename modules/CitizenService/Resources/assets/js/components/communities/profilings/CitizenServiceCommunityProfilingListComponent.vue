<template>
    <section>
        <v-client-table :columns="columns" :data="records" :options="table_options">
            <div slot="communal_councils" slot-scope="props">
                <ul v-if="props.row.is_communal_council">
                    <li
                        class="text-left"
                        v-for="communal_council in props.row.communal_councils"
                        :key="communal_council.id"
                    >
                        {{ communal_council.name }}
                    </li>
                </ul>
                <span v-else>
                    N/A
                </span>
            </div>
            <div slot="communes" slot-scope="props">
                <ul v-if="props.row.is_commune">
                    <li
                        class="text-left"
                        v-for="commune in props.row.communes"
                        :key="commune.id"
                    >
                        {{ commune.name }}
                    </li>
                </ul>
                <span v-else>
                    N/A
                </span>
            </div>
            <div slot="tic_committe_name" slot-scope="props">
                <span v-if="props.row.has_tic_committe">{{ props.row.tic_committe_name }}</span>
                <span v-else>N/A</span>
            </div>
            <div slot="main_needs" slot-scope="props">
                <div class="text-justify" v-html="props.row.main_needs"></div>
            </div>
            <div slot="id" slot-scope="props" class="text-center">
                <div class="d-inline-flex">
                    <citizenservice-community-profiling-info
                        :id="'modal'+props.row.id"
                        :initial_data="props.row"
                    />
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
                    'community.name',
                    'communal_councils',
                    'communes',
                    'tic_committe_name',
                    'main_needs',
                    'id'
                ],
            };
        },
        created() {
            this.table_options.headings = {
                'community.name': 'Nombre de la Comunidad',
                'communal_councils': 'Consejos Comunales',
                'communes': 'Comunas',
                'tic_committe_name': 'Comité de Ciencia y Tecnología',
                'main_needs': 'Principales Necesidades',
                'id': 'Acción'
            };
            this.table_options.sortable = [
                'community.name',
                'tic_committe_name',
                'main_needs',
            ];
            this.table_options.filterable = [
                'community.name',
                'tic_committe_name',
                'main_needs',
            ];
            this.table_options.columnsClasses = {
                'community.name': 'col-md-2 text-center',
                'communal_councils': 'col-md-2 text-center',
                'communes': 'col-md-2 text-center',
                'tic_committe_name': 'col-md-2 text-center',
                'main_needs': 'col-md-2 text-center',
                'id': 'col-md-2 text-center',
            };
        },
        async mounted () {
            const _self = this;
            _self.initRecords(_self.route_list, '');
        },
    }
</script>
