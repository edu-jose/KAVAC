<template>
	<section id="citizenserviceServedInstitutionComponent">
		<a
            class="btn-simplex btn-simplex-md btn-simplex-primary" href="javascript:void(0)"
            title="Registros de institucionas a atender" data-toggle="tooltip"
            @click="addRecord('add_citizenservice-served-institution', 'citizenservice/institutions', $event)"
        >
           <i class="icofont icofont-building-alt ico-3x"></i>
		   <span>Instituciones</span>
		</a>
		<div class="modal fade text-left" tabindex="-1" id="add_citizenservice-served-institution">
			<div class="modal-dialog vue-crud">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
						<h6>
							<i class="icofont icofont-building-alt ico-3x"></i>
							Institución
						</h6>
					</div>
					<div class="modal-body">
						<form-errors :listErrors="errors"></form-errors>
                        <div class="row">
                            <div class="col-md-4">
        						<div class="form-group is-required">
        							<label for="institutionName">Nombre:</label>
        							<input
                                        type="text" id="institutionName" placeholder="Nombre"
										v-input-mask data-inputmask-regex="[a-zA-ZÁ-ÿ\s]*"
        								class="form-control input-sm" v-model="record.name"
                                        data-toggle="tooltip"
        								title="Indique el nombre de la institución"
                                    >
        	                    </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
        							<label for="institutionDescription">Descripción:</label>
        							<input
                                        type="text" id="institutionDescription" placeholder="Descripción"
										v-input-mask data-inputmask-regex="[a-zA-ZÁ-ÿ\s]*"
        								class="form-control input-sm" v-model="record.description"
                                        data-toggle="tooltip"
        								title="Indique la descripción de la institución"
                                    >
        	                    </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group is-required">
        							<label for="institutionRif">R.I.F.:</label>
        							<input
                                        type="text" id="institutionRif" placeholder="RIF"
        								class="form-control input-sm" v-model="record.rif"
                                        data-toggle="tooltip"
        								title="Indique el RIF de la institución"
                                    >
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
                                @click="createRecord('citizenservice/institutions')"
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
                                    @click="deleteRecord(props.row.id, 'citizenservice/institutions')"
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
                    rif: ''
				},
				errors: [],
				records: [],
				columns: ['rif', 'name', 'description', 'id'],
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
                    description: '',
                    rif: ''
				};
                this.errors = [];
			},

		},
		created() {
			this.table_options.headings = {
                'rif': 'RIF',
				'name': 'Nombre',
                'description': 'Descripción',
				'id': 'Acción'
			};
			this.table_options.sortable = ['name'];
			this.table_options.filterable = ['name'];
			this.table_options.columnsClasses = {
                'rif': 'col-md-2',
				'name': 'col-md-4',
                'description': 'col-md-4',
				'id': 'col-md-2'
			};
		},
	};
</script>
