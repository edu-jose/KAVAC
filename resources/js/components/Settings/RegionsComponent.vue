<template>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 mt-2 mb-2 text-center">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary"
           href="javascript:void(0)" title="Registros de Regiones" data-toggle="tooltip"
           @click="addRecord('add_region', 'regions', $event)">
            <i class="icofont icofont-globe-alt ico-3x"></i>
            <span>Regiones</span>
        </a>
        <div id="add_region" class="modal fade text-left" tabindex="-1" role="dialog">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-globe-alt inline-block"></i>
                            Regiones
                        </h6>
                    </div>
                    <div class="modal-body">
                        <form-errors :listErrors="errors"></form-errors>
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group is-required">
                                    <label>Código:</label>
                                    <input
                                        class="form-control input-sm" type="text" data-toggle="tooltip"
                                        maxlength="20" placeholder="Código de Región"
                                        title="Indique el código de la Región (requerido)"
                                        v-model="record.code"
                                    />
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group is-required">
                                    <label>Nombre:</label>
                                    <input
                                        class="form-control input-sm" type="text" data-toggle="tooltip"
                                        placeholder="Nombre de Región"
                                        title="Indique el nombre de la Región (requerido)"
                                        v-model="record.name"
                                    />
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group is-required">
                                    <label>País:</label>
                                    <select2 :options="countries" @input="getEstates" v-model="record.country_id"/>
                                </div>
                            </div>
                            <div class="col-12 col-md-12">
                                <div class="form-group is-required">
                                    <label>Estados:</label>
                                    <v-multiselect
                                        track_by="text" :options="estates"
                                        :hide_selected="true"
                                        :close_on_select="true"
                                        v-model="record.estates"
                                    ></v-multiselect>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-group">
                            <button type="button" class="btn btn-default btn-sm btn-round btn-modal-close"
                                    @click="clearFilters" data-dismiss="modal">
                                Cerrar
                            </button>
                            <button type="button" class="btn btn-warning btn-sm btn-round btn-modal btn-modal-clear"
                                    @click="reset()">
                                Cancelar
                            </button>
                            <button type="button" @click="createRecord('regions')"
                                    class="btn btn-primary btn-sm btn-round btn-modal-save">
                                Guardar
                            </button>
                        </div>
                    </div>
                    <div class="modal-body modal-table">
                        <v-server-table
                            :url="'regions'" :columns="columns" :options="table_options"
                            ref="tableResults"
                        >
                            <div slot="estates" slot-scope="props" class="text-center">
                                <span v-for="(recordEstate, index) in props.row.estates" :key="index" class="badge badge-primary mx-1">
                                    {{ recordEstate.name }}
                                </span>
                            </div>
                            <div slot="id" slot-scope="props" class="text-center">
                                <button @click="initUpdate(props.row.id, $event)"
                                        class="btn btn-warning btn-xs btn-icon btn-action"
                                        title="Modificar registro" data-toggle="tooltip" type="button">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button @click="deleteRecord(props.row.id, 'regions')"
                                        class="btn btn-danger btn-xs btn-icon btn-action"
                                        title="Eliminar registro" data-toggle="tooltip"
                                        type="button">
                                    <i class="fa fa-trash-o"></i>
                                </button>
                            </div>
                        </v-server-table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                record: {
                    id: '',
                    country_id: '0',
                    estates: [],
                    code: '',
                    name: '',
                },
                errors: [],
				records: [],
                countries: [],
                estates: [],
				columns: [
                    'code',
                    'name',
                    'country.name',
                    'estates',
                    'id'
                ],
            }
        },
        methods: {
            reset() {
				this.record = {
					id: '',
					country_id: '0',
					estates: [],
                    code: '',
					name: '',
				};
                this.errors = [];
			},
            initUpdate(id, event) {
                const vm = this;

                vm.errors = [];
                let recordEdit = JSON.parse(JSON.stringify(vm.$refs.tableResults.data.filter((rec) => {
                    return rec.id === id;
                })[0])) || vm.reset();

                const estates = recordEdit.estates.map((estate) => {
                    return {
                        id: estate.id,
                        text: estate.name
                    }
                })
                recordEdit.estates = estates;
                vm.record = recordEdit;
                event.preventDefault();
            },
        },
		created() {
			this.table_options.headings = {
				'code': 'Código',
				'name': 'Región',
                'country.name': 'Pais',
                'estates': 'Estados',
				'id': 'Acción'
			};
			this.table_options.sortable = [
                'code',
                'name',
                'country.name',
                'estates'
            ];
			this.table_options.filterable = [
                'code',
                'name',
                'country.name',
                'estates'
            ];
			this.table_options.columnsClasses = {
				'code': 'col-md-2',
				'name': 'col-md-3',
                'country.name': 'col-md-2',
                'estates': 'col-md-3',
				'id': 'col-md-2'
			};
		},
		mounted() {
			const vm = this;
			$("#add_region").on('show.bs.modal', function() {
				vm.getCountries();
			});
		}
    }
</script>