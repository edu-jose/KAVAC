<template>
	<div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 mt-2 mb-2 text-center">
		<a class="btn-simplex btn-simplex-md btn-simplex-primary pt-0" href="javascript:void(0)"
		   title="Registros de orden de prioridades de compra" data-toggle="tooltip"
		   @click="addRecord('add_purchase_priority_order', 'purchase/priority-orders', $event)">
		   	<i class="fa fa-sort-numeric-asc ico-3x pt-3 pb-2"></i>
			<span class="">Orden de<br>Prioridades</span>
		</a>
		<div class="modal fade text-left" tabindex="-1" id="add_purchase_priority_order">
			<div class="modal-dialog vue-crud">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
						<h6>
							<i class="fa fa-sort-numeric-asc inline-block"></i>
							Orden de Prioridad de compra
						</h6>
					</div>
					<div v-show="record.id">
						<div class="modal-body">
							<form-errors :listErrors="errors"></form-errors>
							<div class="row">
								<div class="col-12 col-md-4">
									<div class="form-group is-required">
										<label for="purchasePriorityOrder">Orden:</label>
										<select2
                                            id="purchasePriorityOrder"
                                            :options="orders"
                                            v-model="record.order"
                                            data-toggle="tooltip"
                                            title="Indique el orden de la prioridad de compra (requerido)"
                                            v-has-tooltip
                                        ></select2>
									</div>
								</div>
								<div class="col-12 col-md-6">
									<div class="form-group is-required">
										<label for="purchasePriorityOrderDescription">Descripción:</label>
										<input
                                            id="purchasePriorityOrderDescription"
                                            type="text" placeholder="Descripción" data-toggle="tooltip"
											title="Indique una descripción breve sobre el orden de la prioridad de compra (requerido)"
											class="form-control input-sm" v-model="record.description"
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
                                    type="button" @click="createRecord('purchase/priority-orders')"
									class="btn btn-primary btn-sm btn-round btn-modal-save"
                                >
									Guardar
								</button>
							</div>
						</div>
					</div>
	                <div class="modal-body modal-table">
	                	<v-client-table :columns="columns" :data="records" :options="table_options">
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
					orden: '',
				},
				/** @type {Array} Inicialización de errores a mostrar */
				errors: [],
				/** @type {Array} Inicialización de atributo que cargara información registrada */
				records: [],
                orders: [
                    {id: '', text: 'Seleccione...'},
                    {id: 1, text: '1'},
                    {id: 2, text: '2'},
                    {id: 3, text: '3'},
                    {id: 4, text: '4'},
                    {id: 5, text: '5'},
                    {id: 6, text: '6'},
                    {id: 7, text: '7'},
                    {id: 8, text: '8'},
                    {id: 9, text: '9'},
                    {id: 10, text: '10'},
                ],
				columns: ['order', 'description', 'id'],
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
					order: '',
				};
				this.errors = [];
			}
		},
		created() {
			this.table_options.headings = {
				order: 'Orden',
				description: 'Descripción',
				id: 'Acción'
			};
			this.table_options.sortable = ['order', 'description'];
			this.table_options.filterable = ['order', 'description'];
			this.table_options.columnsClasses = {
				'order': 'col-md-2',
				'description': 'col-md-8',
				'id': 'col-md-2'
			};
		}
	};
</script>