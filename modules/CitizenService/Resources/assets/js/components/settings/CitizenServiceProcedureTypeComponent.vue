<template>
	<section id="citizenserviceProcedureTypeComponent">
		<a
            class="btn-simplex btn-simplex-md btn-simplex-primary" href="javascript:void(0)"
            title="Registros de tipos de trámites" data-toggle="tooltip"
            @click="addRecord('add_citizenservice-procedure-type', 'citizenservice/procedure-types', $event)"
        >
           <i class="icofont icofont-list ico-3x"></i>
		   <span>Tipos de Trámites</span>
		</a>
		<div class="modal fade text-left" tabindex="-1" id="add_citizenservice-procedure-type">
			<div class="modal-dialog vue-crud">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
						<h6>
							<i class="icofont icofont-list ico-3x"></i>
							Tipo de Trámite
						</h6>
					</div>
					<div class="modal-body">
						<form-errors :listErrors="errors"></form-errors>
                        <div class="row">
                            <div class="col-md-5">
        						<div class="form-group is-required">
        							<label for="procedureTypeName">Nombre:</label>
        							<input
                                        type="text" id="procedureTypeName" placeholder="Nombre"
										v-input-mask data-inputmask-regex="[a-zA-ZÁ-ÿ\s]*"
        								class="form-control input-sm" v-model="record.name"
                                        data-toggle="tooltip"
        								title="Indique el nombre del tipo de trámite"
                                    >
        	                    </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group is-required">
        							<label for="procedureTypeDescription">Descripción:</label>
        							<input
                                        type="text" id="procedureTypeDescription" placeholder="Descripción"
										v-input-mask data-inputmask-regex="[a-zA-ZÁ-ÿ\s]*"
        								class="form-control input-sm" v-model="record.description"
                                        data-toggle="tooltip"
        								title="Indique la descripción del tipo de trámite"
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
                                @click="createRecord('citizenservice/procedure-types')"
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
                                    @click="deleteRecord(props.row.id, 'citizenservice/procedure-types')"
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
				},
				errors: [],
				records: [],
				columns: ['name', 'description', 'id'],
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
                    description: ''
				};
                this.errors = [];
			},

		},
		created() {
			this.table_options.headings = {
				'name': 'Nombre',
                'description': 'Descripción',
				'id': 'Acción'
			};
			this.table_options.sortable = ['name'];
			this.table_options.filterable = ['name'];
			this.table_options.columnsClasses = {
				'name': 'col-md-3',
                'description': 'col-md-3',
				'id': 'col-md-2'
			};
		},
	};
</script>
