<template>
	<div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 mt-2 mb-2 text-center">
		<a class="btn-simplex btn-simplex-md btn-simplex-primary" href="javascript:void(0)"
		   title="Registros de prioridades de compra" data-toggle="tooltip"
		   @click="addRecord('add_purchase_priority', 'purchase/priorities', $event)">
		   	<i class="fa fa-sort-amount-desc ico-3x mb-3"></i>
			<span>Prioridades</span>
		</a>
		<div class="modal fade text-left" tabindex="-1" id="add_purchase_priority">
			<div class="modal-dialog vue-crud">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
						<h6>
							<i class="fa fa-sort-amount-desc inline-block"></i>
							Prioridad de compra
						</h6>
					</div>
					<div v-show="record.id">
						<div class="modal-body">
							<form-errors :listErrors="errors"></form-errors>
							<div class="row">
								<div class="col-12 col-md-2">
									<div class="form-group is-required">
										<label for="purchasePriorityColor">Color:</label>
										<input
                                            id="purchasePriorityColor" type="color" placeholder="Color" data-toggle="tooltip"
											title="Seleccione un color para identificar la prioridad de compra (requerido)"
											class="form-control input-sm" v-model="record.color"
                                        >
									</div>
								</div>
								<div class="col-12 col-md-4">
									<div class="form-group is-required">
										<label for="purchasePriorityName">Nombre:</label>
										<input
                                            id="purchasePriorityName"
                                            type="text" placeholder="Nombre" data-toggle="tooltip"
											title="Indique el nombre de la prioridad de compra (requerido)"
											class="form-control input-sm" v-model="record.name" v-is-text
                                        >
									</div>
								</div>
								<div class="col-12 col-md-6">
									<div class="form-group is-required">
										<label for="purchasePriorityDescription">Descripción:</label>
										<input
                                            id="purchasePriorityDescription"
                                            type="text" placeholder="Descripción" data-toggle="tooltip"
											title="Indique una descripción breve sobre la prioridad de compra (requerido)"
											class="form-control input-sm" v-model="record.description" v-is-text
                                        >
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<div class="form-group">
								<button
                                    type="button" class="btn btn-default btn-sm btn-round btn-modal-close"
									@click="clearFilters" data-dismiss="modal"
                                >
									Cerrar
								</button>
								<button
                                    type="button" class="btn btn-warning btn-sm btn-round btn-modal btn-modal-clear"
									@click="reset()"
                                >
									Cancelar
								</button>
								<button
                                    type="button" @click="createRecord('purchase/priorities')"
									class="btn btn-primary btn-sm btn-round btn-modal-save"
                                >
									Guardar
								</button>
							</div>
						</div>
					</div>
	                <div class="modal-body modal-table">
	                	<v-client-table :columns="columns" :data="records" :options="table_options">
	                		<div slot="color" slot-scope="props" class="text-center">
								<i
                                    class="ion-android-checkbox-blank"
                                    :style="'color:' + props.row.color"
                                    :title="'Código de color: '+props.row.color"
                                ></i>
							</div>
							<div slot="id" slot-scope="props" class="text-center">
	                			<button
                                    @click="initUpdate(props.row.id, $event)"
		                			class="btn btn-warning btn-xs btn-icon btn-action"
		                			title="Modificar registro" data-toggle="tooltip" type="button" v-has-tooltip
                                >
		                			<i class="fa fa-edit"></i>
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
				/** @type {json} Inicialización de atributos */
				record: {
					id: '',
					description: '',
					name: '',
					color: '#FFFFFF',
					action: '',
				},
				/** @type {Array} Inicialización de errores a mostrar */
				errors: [],
				/** @type {Array} Inicialización de atributo que cargara información registrada */
				records: [],
				columns: ['color', 'name', 'description', 'id'],
			}
		},
		methods: {
			/**
			 * Método que borra todos los datos del formulario
			 *
			 * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
			 */
			reset() {
				this.record = {
					id: '',
					description: '',
					name: '',
					color: '#FFFFFF'
				};
				this.errors = [];
			}
		},
		created() {
			this.table_options.headings = {
				color: 'Color',
				name: 'Nombre',
				description: 'Descripción',
				id: 'Acción'
			};
			this.table_options.sortable = ['name', 'description'];
			this.table_options.filterable = ['name', 'description'];
			this.table_options.columnsClasses = {
				'color': 'col-md-2',
				'name': 'col-md-2',
				'description': 'col-md-6',
				'id': 'col-md-2'
			};
		}
	};
</script>
