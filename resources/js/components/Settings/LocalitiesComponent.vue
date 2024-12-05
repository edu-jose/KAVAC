<template>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 mt-2 mb-2 text-center">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary"
           href="javascript:void(0)" title="Registros de Localidades" data-toggle="tooltip"
           @click="addRecord('add_locality', 'localities', $event)">
            <i class="icofont icofont-pin ico-3x"></i>
            <span>Localidades</span>
        </a>
        <div id="add_locality" class="modal fade text-left" tabindex="-1" role="dialog">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-pin inline-block"></i>
                            Localidades
                        </h6>
                    </div>
                    <div class="modal-body">
                        <form-errors :listErrors="errors"></form-errors>
                        <div class="row">
                            <div class="col-12 col-md-3">
                                <div class="form-group is-required">
                                    <label>País:</label>
                                    <select2 :options="countries" @input="getEstates" v-model="record.country_id"/>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group is-required">
                                    <label>Estados:</label>
                                    <select2 :options="estates" v-model="record.estate_id" @input="getMunicipalities"/>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group is-required">
                                    <label>Municipio:</label>
                                    <select2 :options="municipalities" v-model="record.municipality_id" @input="getParishes"/>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group is-required">
                                    <label>Parroquia:</label>
                                    <select2 :options="parishes" v-model="record.parish_id"/>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group is-required">
                                    <label>Código:</label>
                                    <input
                                        class="form-control input-sm" type="text" data-toggle="tooltip"
                                        maxlength="20" placeholder="Código de Localidad"
                                        title="Indique el código de la Localidad (requerido)"
                                        v-model="record.code"
                                    />
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group is-required">
                                    <label>Nombre:</label>
                                    <input
                                        class="form-control input-sm" type="text" data-toggle="tooltip"
                                        placeholder="Nombre de Localidad"
                                        title="Indique el nombre de la Localidad (requerido)"
                                        v-model="record.name"
                                    />
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
                            <button type="button" @click="createRecord('localities')"
                                    class="btn btn-primary btn-sm btn-round btn-modal-save">
                                Guardar
                            </button>
                        </div>
                    </div>
                    <div class="modal-body modal-table">
                        <v-server-table
                            :url="'localities'" :columns="columns" :options="table_options"
                            ref="tableResults"
                        >
                            <div slot="id" slot-scope="props" class="text-center">
                                <button @click="initUpdate(props.row.id, $event)"
                                        class="btn btn-warning btn-xs btn-icon btn-action"
                                        title="Modificar registro" data-toggle="tooltip" type="button">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button @click="deleteRecord(props.row.id, 'localities')"
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
import { set } from 'lodash';

    export default {
        data() {
            return {
                record: {
                    id: '',
                    country_id: '0',
                    estate_id: '0',
                    municipality_id: '0',
                    parish_id: '0',
                    code: '',
                    name: '',
                },
                errors: [],
				records: [],
                countries: [],
                estates: ['0'],
                municipalities: ['0'],
                parishes: ['0'],
				columns: [
                    'code',
                    'name',
                    'parish.municipality.estate.country.name',
                    'parish.municipality.estate.name',
                    'parish.municipality.name',
                    'parish.name',
                    'id'
                ],
            }
        },
        methods: {
            reset() {
				this.record = {
					id: '',
					country_id: '0',
					estate_id: '0',
                    municipality_id: '0',
                    parish_id: '0',
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
                vm.record = recordEdit;
                vm.record.country_id = recordEdit.parish.municipality.estate.country.id;
                vm.getEstates(vm.record.country_id);
                setTimeout(() => {
                    vm.record.estate_id = recordEdit.parish.municipality.estate_id;
                }, 1000);
                setTimeout(() => {
                    vm.record.municipality_id = recordEdit.parish.municipality_id;
                }, 2000);
                setTimeout(() => {
                    vm.record.parish_id = recordEdit.parish.id;
                }, 3000);
                event.preventDefault();
            },
        },
		created() {
			this.table_options.headings = {
				'code': 'Código',
				'name': 'Localidad',
                'parish.municipality.estate.country.name': 'Pais',
                'parish.municipality.estate.name': 'Estado',
                'parish.municipality.name': 'Municipio',
                'parish.name': 'Parroquia',
				'id': 'Acción'
			};
			this.table_options.sortable = [
                'code',
                'name',
                'parish.municipality.estate.country.name',
                'parish.municipality.estate.name',
                'parish.municipality.name',
                'parish.name'
            ];
			this.table_options.filterable = [
                'code',
                'name',
                'parish.municipality.estate.country.name',
                'parish.municipality.estate.name',
                'parish.municipality.name',
                'parish.name'
            ];
			this.table_options.columnsClasses = {
				'code': 'col-md-1',
				'name': 'col-md-2',
                'parish.municipality.estate.country.name': 'col-md-1',
                'parish.municipality.estate.name': 'col-md-2',
                'parish.municipality.name': 'col-md-2',
                'parish.name': 'col-md-2',
				'id': 'col-md-2'
			};
		},
		mounted() {
			const vm = this;
			$("#add_locality").on('show.bs.modal', function() {
				vm.getCountries();
			});
		}
    }
</script>