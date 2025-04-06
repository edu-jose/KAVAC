<template>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 mt-2 mb-2 text-center">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary"
           href="javascript:void(0)" title="Registro para sedes"
           data-toggle="tooltip" @click="addRecord('add_headquarter', 'headquarters', $event)">
            <i class="icofont icofont-institution ico-3x"></i>
            <span style="font-size: 11px;" class="mx-1">Sedes / Negocios / Filiales</span>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" id="add_headquarter">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-institution inline-block"></i>
                            Sedes / Negocios / Filiales
                        </h6>
                    </div>
                    <div class="modal-body">
                        <form-errors :listErrors="errors"></form-errors>
                        <div class="row">
                            <div class="col-2">
                                <div class="form-group">
                                    <label for="rif">R.I.F.:</label>
                                    <input
                                        type="text" placeholder="Número de RIF" data-toggle="tooltip"
                                        title="Indique el número de RIF" id="rif"
                                        class="form-control input-sm" v-model="record.rif"
                                    >
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group is-required">
                                    <label for="name">Nombre:</label>
                                    <input
                                        type="text" placeholder="Nombre de la sede" data-toggle="tooltip"
                                        title="Indique el nombre de la sede (requerido)" id="name"
                                        class="form-control input-sm" v-model="record.name"
                                    >
                                    <input type="hidden" v-model="record.id">
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label for="country">País:</label>
                                    <select2 :options="countries" @input="getEstates" v-model="record.country_id" id="country"/>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label for="estate">Estado:</label>
                                    <select2 :options="estates" v-model="record.estate_id" @input="getEstateRelations" id="estate"/>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label for="municipality">Municipio:</label>
                                    <select2 :options="municipalities" v-model="record.municipality_id" id="municipality"/>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label for="city">Ciudad:</label>
                                    <select2 :options="cities" v-model="record.city_id" id="city"/>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label for="region">Region:</label>
                                    <select2 :options="regions" v-model="record.region_id" id="region"/>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="address">Dirección fiscal:</label>
                                    <ckeditor
                                        :editor="ckeditor.editor" data-toggle="tooltip"
                                        title="Indique la dirección fiscal" id="address"
                                        :config="ckeditor.editorConfig" class="form-control"
                                        tag-name="textarea" rows="3" v-model="record.address"
                                    ></ckeditor>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-group">
                            <button type="button" class="btn btn-default btn-sm btn-round btn-modal-close"
                                    @click="clearFilters()" data-dismiss="modal">
                                Cerrar
                            </button>
                            <button type="button" class="btn btn-warning btn-sm btn-round btn-modal btn-modal-clear"
                                    @click="reset()">
                                Cancelar
                            </button>
                            <button type="button" @click="createRecord('headquarters')"
                                    class="btn btn-primary btn-sm btn-round btn-modal-save">
                                Guardar
                            </button>
                        </div>
                    </div>
                    <div class="modal-body modal-table">
                        <v-client-table :columns="columns" :data="records" :options="table_options">
                            <div slot="municipality" slot-scope="props" class="text-center">
                                {{ props.row.municipality ? `${props.row.municipality.estate.country.name} / ${props.row.municipality.estate.name} / ${props.row.municipality.name}` : '' }}
                            </div>
                            <div slot="id" slot-scope="props" class="text-center">
                                <button @click="initUpdate(props.row.id, $event)"
                                        class="btn btn-warning btn-xs btn-icon btn-action"
                                        title="Modificar registro" data-toggle="tooltip" type="button">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button @click="deleteRecord(props.row.id, 'headquarters')"
                                        class="btn btn-danger btn-xs btn-icon btn-action"
                                        title="Eliminar registro" data-toggle="tooltip"
                                        type="button">
                                    <i class="fa fa-trash-o"></i>
                                </button>
                            </div>
                        </v-client-table>
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
                    rif: '',
                    name: '',
                    country_id: '',
                    estate_id: '',
                    municipality_id: '',
                    city_id: '',
                    region_id: '',
                    address: ''
                },
                errors: [],
                records: [],
                countries: [],
                estates: [],
                municipalities: [],
                cities: [],
                regions: [],
                columns: [
                    'rif',
                    'name',
                    'municipality',
                    'city.name',
                    'region.name',
                    'id'
                ],
            }
        },
        methods: {
            /**
             * Método que borra todos los datos del formulario
             *
             * @author  Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
             */
            reset() {
                this.record = {
                    id: '',
                    rif: '',
                    name: '',
                    country_id: '',
                    estate_id: '',
                    municipality_id: '',
                    city_id: '',
                    region_id: '',
                    address: ''
                };
                this.errors = [];
            },
            async getEstateRelations() {
                await this.getMunicipalities();
                await this.getCities();
                await this.getRegions(this.record.estate_id);
            },
            initUpdate(id, event) {
                const vm = this;

                vm.errors = [];
                let recordEdit = JSON.parse(JSON.stringify(vm.records.filter((rec) => {
                    return rec.id === id;
                })[0])) || vm.reset();
                vm.record = recordEdit;
                vm.record.country_id = recordEdit.municipality?.estate?.country?.id;
                vm.getEstates(vm.record.country_id);
                setTimeout(() => {
                    vm.record.estate_id = recordEdit.municipality?.estate_id;
                }, 1000);
                setTimeout(() => {
                    vm.record.municipality_id = recordEdit.municipality?.id;
                }, 2000);
                setTimeout(() => {
                    vm.record.city_id = recordEdit.city?.id;
                }, 3000);
                setTimeout(() => {
                    vm.record.region_id = recordEdit.region?.id || '';
                }, 3000);
                event.preventDefault();
            },
        },
        created() {
            this.table_options.headings = {
                'rif': 'R.I.F.',
                'name': 'Nombre',
                'municipality': 'Pais / Estado / Municipio',
                'city.name': 'Ciudad',
                'region.name': 'Región',
                'id': 'Acción'
            };
            this.table_options.sortable = ['rif', 'name'];
            this.table_options.filterable = ['rif', 'name'];
            this.table_options.columnsClasses = {
                'rif': 'col-md-2',
                'name': 'col-md-2',
                'municipality': 'col-md-2',
                'city.name': 'col-md-2',
                'region.name': 'col-md-2',
                'id': 'col-md-2'
            };
        },
		mounted() {
			const vm = this;
			$("#add_headquarter").on('show.bs.modal', function() {
				vm.getCountries();
			});
		}
    };
</script>
