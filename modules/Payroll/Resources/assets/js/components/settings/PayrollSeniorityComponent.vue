<template>
	<section class="text-center" id="payroll_seniority">
		<a class="btn-simplex btn-simplex-md btn-simplex-primary" href=""
		   title="Registros de rangos de antiguedad" data-toggle="tooltip"
		   @click="addRecord('add_payroll_seniority', 'payroll/seniorities', $event)">
           <i class="icofont icofont-man-in-glasses ico-3x"></i>
		   <span>Rango de Antiguedad</span>
		</a>
		<div class="modal fade text-left" tabindex="-1" role="dialog" id="add_payroll_seniority">
			<div class="modal-dialog vue-crud" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
						<h6>
							<i class="icofont icofont-man-in-glasses ico-3x"></i>
							Rango de Antiguedad
						</h6>
					</div>
					<div class="modal-body">
						<div class="alert alert-danger" v-if="errors.length > 0">
                            <div class="container">
                                <div class="alert-icon">
                                    <i class="now-ui-icons objects_support-17"></i>
                                </div>
                                <strong>Cuidado!</strong> Debe verificar los siguientes errores antes de continuar:
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"
                                        @click.prevent="errors = []">
                                    <span aria-hidden="true">
                                        <i class="now-ui-icons ui-1_simple-remove"></i>
                                    </span>
                                </button>
                                <ul>
                                    <li v-for="error in errors" :key="error">{{ error }}</li>
                                </ul>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
        						<div class="form-group is-required">
        							<label for="code">Codigo:</label>
        							<input type="text" id="code" placeholder="Codigo"
        								   class="form-control input-sm" v-model="record.code" data-toggle="tooltip"
        								   title="Indique el codigo de la antiguedad (requerido)">
        							<input type="hidden" name="code" id="code" v-model="record.code">
        	                    </div>
                            </div>
							<div class="col-md-6">
        						<div class="form-group is-required">
        							<label for="name">Nombre:</label>
        							<input type="text" id="name" placeholder="Nombre"
        								   class="form-control input-sm" v-model="record.name" data-toggle="tooltip"
        								   title="Indique el nombre de la antiguedad (requerido)">
        							<input type="hidden" name="id" id="id" v-model="record.id">
        	                    </div>
                            </div>
							<div class="col-md-6">
                                <div class="form-group is-required">
                                    <label for="minimum_age">Mínimo:</label>
                                    <input type="text" data-toggle="tooltip" id="minimum_age"
                                           placeholder="Rango minimo de edad"
                                           title="Indique el rango mínimo permitido (requerido)"
                                           class="form-control input-sm"
                                           v-input-mask data-inputmask-regex="^([0-9]|[0-9][0-9])$"
										   v-model="record.minimum_age">
                                </div>
                            </div>
							<div class="col-md-6">
								<div class="form-group is-required">
									<label for="maximum_age">Máximo:</label>
									<input type="text" data-toggle="tooltip" id="maximum_age"
										placeholder="Rango maximo de edad"
										title="Indique el rango maximo permitido (requerido)"
										:min="record.minimum_age"
										:disabled="record.minimum_age == ''"
										class="form-control input-sm"
										v-input-mask data-inputmask-regex="^([1-9]|[1-9][0-9])$"
										v-model="record.maximum_age">
								</div>
							</div>
							<div class="col-md-12">
                                <div class="form-group">
        							<label for="description">Descripción:</label>
        							<input type="text" id="description" placeholder="Descripción"
        								   class="form-control input-sm" v-model="record.description" data-toggle="tooltip"
        								   title="Indique la descripción de la antiguedad">
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
							<button type="button" @click="createRecord('payroll/seniorities')"
									class="btn btn-primary btn-sm btn-round btn-modal-save">
								Guardar
							</button>
	                	</div>
	                </div>
	                <div class="modal-body modal-table">
	                	<v-client-table :columns="columns" :data="records" :options="table_options">
	                		<div slot="id" slot-scope="props" class="text-center">
	                			<button @click="initUpdate(props.row.id, $event)"
		                				class="btn btn-warning btn-xs btn-icon btn-action"
		                				title="Modificar registro" data-toggle="tooltip" type="button">
		                			<i class="fa fa-edit"></i>
		                		</button>
								<button @click="deleteRecord(props.row.id, 'payroll/seniorities')"
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
	</section>
</template>

<script>
	export default {
		data() {
			return {
				record: {
					id: '',
					code: '',
					name: '',
                    description: '',
					minimum_age: '',
					maximum_age: ''
				},
				errors: [],
				records: [],
				columns: ['name', 'description', 'minimum_age', 'maximum_age', 'id'],
			}
		},
		methods: {
			/**
			 * Método que borra todos los datos del formulario
			 *
			 * @author  Pedro Contreras <pmcontreras@cenditel.gob.ve>
			 */
			reset() {
				this.record = {
					id: '',
					code: '',
					name: '',
                    description: '',
					minimum_age: '',
					maximum_age: ''
				};
			},
		},
		created() {
			this.table_options.headings = {
				'name': 'Nombre',
                'description': 'Descripción',
				'minimum_age': 'Mínimo',
				'maximum_age': 'Máximo',
				'id': 'Acción'
			};
			this.table_options.sortable = ['name'];
			this.table_options.filterable = ['name'];
			this.table_options.columnsClasses = {
				'name': 'col-md-3',
                'description': 'col-md-4',
				'minimum_age': 'col-md-1',
				'maximum_age': 'col-md-1',
				'id': 'col-md-1'
			};
		},
		mounted () {
			const vm = this;
			$("#add_payroll_seniority").on('show.bs.modal', function() {
                vm.reset();
            });
		},
	};
</script>