<template>
	<section id="citizenserviceCommunityComponent">
		<a
            class="btn-simplex btn-simplex-md btn-simplex-primary" href="javascript:void(0)"
            title="Registros de comunidades" data-toggle="tooltip"
            @click="addRecord('add_citizenservice-community', 'citizenservice/communities', $event)"
        >
           <i class="icofont icofont-bank-alt ico-3x"></i>
		   <span>Comunidades</span>
		</a>
		<div class="modal fade text-left" tabindex="-1" id="add_citizenservice-community">
			<div class="modal-dialog vue-crud">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
						<h6>
							<i class="icofont icofont-bank-alt ico-3x"></i>
							Comunidad
						</h6>
					</div>
					<div class="modal-body">
						<form-errors :listErrors="errors"></form-errors>
                        <div class="row">
                            <div class="col-md-4">
        						<div class="form-group is-required">
        							<label for="communityName">Nombre:</label>
        							<input
                                        type="text" id="communityName" placeholder="Nombre"
										v-input-mask data-inputmask-regex="[a-zA-ZÁ-ÿ\s]*"
        								class="form-control input-sm" v-model="record.name"
                                        data-toggle="tooltip"
        								title="Indique el nombre de la comunidad"
                                        autocomplete="off"
                                    >
        	                    </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group is-required">
                                    <label for="communityCountryId">Pais:</label>
                                    <select2
                                        id="communityCountryId"
                                        :options="countries"
                                        v-model="record.country_id"
                                        @input="getEstates"
                                    />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group is-required">
                                    <label for="communityEstateId">Estado:</label>
                                    <select2
                                        id="communityEstateId"
                                        :options="estates"
                                        v-model="record.estate_id"
                                        @input="getMunicipalities(); getCities()"
                                    />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group is-required">
                                    <label for="communityCityId">Ciudad:</label>
                                    <select2
                                        id="communityCityId"
                                        :options="cities"
                                        v-model="record.city_id"
                                    />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group is-required">
                                    <label for="communityMunicipalityId">Municipio:</label>
                                    <select2
                                        id="communityMunicipalityId"
                                        :options="municipalities"
                                        v-model="record.municipality_id"
                                        @input="getParishes"
                                    />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group is-required">
                                    <label for="communityParishId">Parroquia:</label>
                                    <select2
                                        id="communityParishId"
                                        :options="parishes"
                                        v-model="record.parish_id"
                                    />
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="communityLocation">
                                        Datos complementarios de la ubicación
                                    </label>
                                    <ckeditor
                                        :editor="ckeditor.editor"
                                        id="communityLocation"
                                        data-toggle="tooltip"
                                        title="Indique los datos complementarios de la ubicación de la comunidad"
                                        :config="ckeditor.editorConfig"
                                        class="form-control"
                                        name="communityLocation"
                                        tag-name="textarea"
                                        rows="3"
                                        v-model="record.location"
                                    ></ckeditor>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="communityPopulation">
                                        Población
                                    </label>
                                    <ckeditor
                                        :editor="ckeditor.editor"
                                        id="communityPopulation"
                                        data-toggle="tooltip"
                                        title="Indique los datos de la población de la comunidad"
                                        :config="ckeditor.editorConfig"
                                        class="form-control"
                                        name="communityPopulation"
                                        tag-name="textarea"
                                        rows="3"
                                        v-model="record.population"
                                    ></ckeditor>
                                </div>
                            </div>
                        </div>
	                </div>
					<div class="modal-footer">
	                	<div class="form-group">
	                		<button
                                type="button"
                                class="btn btn-default btn-sm btn-round btn-modal-close"
                                @click="clearFilters"
                                data-dismiss="modal"
                            >
								Cerrar
							</button>
							<button
                                type="button"
                                class="btn btn-warning btn-sm btn-round btn-modal btn-modal-clear"
                                @click="reset()"
                            >
								Cancelar
							</button>
							<button
                                type="button"
                                @click="createRecord('citizenservice/communities')"
                                class="btn btn-primary btn-sm btn-round btn-modal-save"
                            >
								Guardar
							</button>
	                	</div>
	                </div>
	                <div class="modal-body modal-table">
	                	<v-client-table :columns="columns" :data="records" :options="table_options">
	                		<div slot="id" slot-scope="props" class="text-center">
	                			<button
                                    type="button"
                                    @click="initUpdate(props.row.id, $event)"
		                			class="btn btn-warning btn-xs btn-icon btn-action"
		                			title="Modificar registro"
                                    data-toggle="tooltip"
                                    v-has-tooltip
                                >
		                			<i class="fa fa-edit"></i>
		                		</button>
		                		<button
                                    type="button"
                                    @click="deleteRecord(props.row.id, 'citizenservice/communities')"
                                    class="btn btn-danger btn-xs btn-icon btn-action"
                                    title="Eliminar registro"
                                    data-toggle="tooltip"
                                    v-has-tooltip
                                >
									<i class="fa fa-trash-o"></i>
								</button>

	                		</div>
	                	</v-client-table>
	                </div>
		        </div>
		    </div>
		</div>
	</section>
</template>

<script>
	export default {
		data() {
			return {
				record: {
					id: '',
					name: '',
                    description: '',
                    country_id: '',
                    estate_id: '',
                    city_id: '',
                    municipality_id: '',
                    parish_id: '',
                    location: '',
                    population: ''
				},
				errors: [],
				records: [],
                countries: [],
                estates: [],
                cities: [],
                municipalities: [],
                parishes: [],
				columns: [
                    'parish.municipality.estate.country.name',
                    'parish.municipality.estate.name',
                    'city.name',
                    'parish.municipality.name',
                    'parish.name',
                    'name',
                    'id'
                ],
			}
		},
		methods: {
			/**
			 * Método que borra todos los datos del formulario
			 */
			reset() {
				this.record = {
					id: '',
					name: '',
                    country_id: '',
                    estate_id: '',
                    city_id: '',
                    municipality_id: '',
                    parish_id: '',
                    location: '',
                    population: ''
				};
                this.errors = [];
			},

		},
		created() {
			this.table_options.headings = {
                'parish.municipality.estate.country.name': 'Pais',
                'parish.municipality.estate.name': 'Estado',
                'city.name': 'Ciudad',
                'parish.municipality.name': 'Municipio',
                'parish.name': 'Parroquia',
				'name': 'Nombre',
				'id': 'Acción'
			};
			this.table_options.sortable = ['name'];
			this.table_options.filterable = ['name'];
			this.table_options.columnsClasses = {
                'parish.municipality.estate.country.name': 'col-md-2',
                'parish.municipality.estate.name': 'col-md-2',
                'city.name': 'col-md-2',
                'parish.municipality.name': 'col-md-2',
                'parish.name': 'col-md-2',
				'name': 'col-md-2',
				'id': 'col-md-2'
			};
		},
        mounted() {
            const _self = this;
            _self.getCountries();
        }
	};
</script>
