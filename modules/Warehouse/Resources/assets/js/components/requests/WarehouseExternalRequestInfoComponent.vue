<template>
	<div>
		<a class="btn btn-info btn-xs btn-icon btn-action"
		   href="#" title="Ver información del registro" data-toggle="tooltip"
		   @click="addRecord('view_warehouse_external_request' + infoid, route_list , $event)">
			<i class="fa fa-eye"></i>
		</a>
		<div class="modal fade text-left" tabindex="-1" role="dialog" :id="'view_warehouse_external_request' + infoid">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
						<h6>
							<i class="icofont icofont-read-book ico-2x"></i>
							Información de la solicitud registrada
						</h6>
					</div>

					<div class="modal-body">
						<ul class="nav nav-tabs custom-tabs justify-content-center" role="tablist">
	                        <li class="nav-item">
	                            <a class="nav-link active" data-toggle="tab" :id="'request_info_general' + infoid" :href="'#request_general' + infoid" role="tab">
	                                <i class="ion-android-person"></i> Información General
	                            </a>
	                        </li>

	                        <li class="nav-item">
	                            <a class="nav-link" data-toggle="tab" :href="'#external_equipment' + infoid" role="tab" @click="loadProducts()">
	                                <i class="ion-arrow-swap"></i> Insumos Solicitados
	                            </a>
	                        </li>
	                    </ul>

	                    <div class="tab-content">
							<div class="tab-pane active" :id="'request_general' + infoid" role="tabpanel">
	                    		<div class="row">

									<div class="col-md-6">
										<div class="form-group">
											<strong>Fecha de la solicitud</strong>
											<div class="row" style="margin: 1px 0">
												<span class="col-md-12" :id="'date' + infoid">
												</span>
											</div>
											<input type="hidden" :id="'id' + infoid">
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<strong>Nombre del solicitante</strong>
											<div class="row" style="margin: 1px 0">
												<span class="col-md-12" :id="'first_name' + infoid">
												</span>
											</div>
											<input type="hidden" :id="'id' + infoid">
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<strong>Apellidos del solicitante</strong>
											<div class="row" style="margin: 1px 0">
												<span class="col-md-12" :id="'last_name' + infoid">
												</span>
											</div>
											<input type="hidden" :id="'id' + infoid">
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<strong>Organización solicitante</strong>
											<div class="row" style="margin: 1px 0">
												<span class="col-md-12" :id="'institution_name' + infoid">
												</span>
											</div>
											<input type="hidden" :id="'id' + infoid">
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<strong>Almacén</strong>
											<div class="row" style="margin: 1px 0">
												<span class="col-md-12" :id="'warehouse' + infoid">
												</span>
											</div>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<strong>Observaciones generales</strong>
											<div class="row" style="margin: 1px 0">
												<span class="col-md-12" :id="'general_observations' + infoid">
												</span>
											</div>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<strong>Estado de la solicitud</strong>
											<div class="row" style="margin: 1px 0">
												<span class="col-md-12" :id="'request_state' + infoid">
												</span>
											</div>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<strong>Observaciones de entrega</strong>
											<div class="row" style="margin: 1px 0">
												<span class="col-md-12" :id="'delivery_observations' + infoid">
												</span>
											</div>
										</div>
									</div>
							    </div>
	                    	</div>

							<div class="tab-pane" :id="'external_equipment' + infoid" role="tabpanel">
	                    		<div class="modal-table">
									<v-client-table :columns="columns" :data="records" :options="table_options">
										<div slot="code" slot-scope="props" class="text-center">
											<span>
												{{ props.row.warehouse_inventory_product.code }}
											</span>
										</div>
										<div slot="quantity" slot-scope="props">
											<span>
												{{ parseFloat(props.row.quantity).toFixed(2) }}
												{{ (props.row.warehouse_inventory_product.warehouse_product.measurement_unit)
													? props.row.warehouse_inventory_product.warehouse_product.measurement_unit.acronym
													: ''
												}}
											</span>
										</div>
										<div slot="description" slot-scope="props">
											<span>
												{{ (props.row.warehouse_inventory_product.warehouse_product.description)?
														prepareText(
														props.row.warehouse_inventory_product.warehouse_product.description) : ''
												}}<br>
											</span>
										</div>
										<div slot="unit_value" slot-scope="props">
											<span>
												{{ parseFloat(props.row.warehouse_inventory_product.unit_value).toFixed(2) }}
												{{ (props.row.warehouse_inventory_product.currency)
													? props.row.warehouse_inventory_product.currency.symbol
													: ''
												}}
											</span>
										</div>
									</v-client-table>
	                    		</div>
	                    	</div>
	                    </div>
					</div>

	                <div class="modal-footer">

	                	<button type="button" class="btn btn-default btn-sm btn-round btn-modal-close"
	                			data-dismiss="modal">
	                		Cerrar
	                	</button>
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
				records: [],
				errors: [],
				columns: ['code',
					'warehouse_inventory_product.warehouse_product.name',
					'description',
					'quantity',
					'unit_value'],
			}
		},
		props: {
			infoid:Number,
			request: Object,
		},
		created() {
			this.table_options.headings = {
				'code': 'Código',
				'warehouse_inventory_product.warehouse_product.name': 'Nombre',
				'description': 'Descripción',
				'quantity': 'Cantidad Agregada',
				'unit_value': 'Valor por Unidad'
			};
			this.table_options.sortable = [
				'code',
				'warehouse_inventory_product.warehouse_product.name',
				'description',
				'quantity',
				'unit_value'
			];
			this.table_options.filterable = [
				'code',
				'warehouse_inventory_product.warehouse_product.name',
				'description',
				'quantity',
				'unit_value'
			];
		},
		methods: {

			/**
             * Método que borra todos los datos del formulario
             *
             * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve | roldandvg@gmail.com>
             */
            reset() {
            },


            prepareText(text) {
                return text.replace('<p>', '').replace('</p>', '');
            },
            /**
			 * Reescribe el método initRecords para cambiar su comportamiento por defecto
			 * Inicializa los registros base del formulario
			 *
			 * @author Henry Paredes <hparedes@cenditel.gob.ve>
			 * @param {string} url 		Ruta que obtiene los datos a ser mostrado en listados
		 	 * @param {string} modal_id Identificador del modal a mostrar con la información solicitada
			 */

            initRecords(url, modal_id) {
				const vm = this;

				this.errors = [];
				this.reset();
            	var fields = {};

				document.getElementById("request_info_general" + this.infoid).click();
            	axios.get(url).then(response => {
					if (typeof(response.data.records) !== "undefined") {
						fields = response.data.records;
						$(".modal-body #id").val(fields.id);

						document.getElementById('date' + vm.infoid).innerText = (fields.date) ? vm.format_date(fields.date) : '';
						document.getElementById('first_name' + vm.infoid).innerText = (fields.first_name) ? fields.first_name : '';
						document.getElementById('last_name' + vm.infoid).innerText = (fields.last_name) ? fields.last_name : '';
						document.getElementById('institution_name' + vm.infoid).innerText = fields.institution_name ?? '';
						document.getElementById('warehouse' + vm.infoid).innerText = (fields.warehouse) ? fields.warehouse.name : '';
						document.getElementById('general_observations' + vm.infoid).innerText = (fields.general_observations)?fields.general_observations.replace(/(<([^>]+)>)/gi, ""):'No definido';
						document.getElementById('request_state' + vm.infoid).innerText = (fields.state) ? fields.state : '';
						document.getElementById('delivery_observations' + vm.infoid).innerText = (fields.observations)?fields.observations.replace(/(<([^>]+)>)/gi, ""):'No definido';
		            	this.records = fields.warehouse_external_request_inventory_products;
					}
					if ($("#" + modal_id).length) {
						$("#" + modal_id).modal('show');
					}
				}).catch(error => {
					if (typeof(error.response) !== "undefined") {
						if (error.response.status == 403) {
							vm.showMessage(
								'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
							);
						}
						else {
							vm.logs('resources/js/all.js', 343, error, 'initRecords');
						}
					}
				});

			},


			/**
			 * Actualiza los productos asocados a la solicitud
			 *
			 * @author Henry Paredes <hparedes@cenditel.gob.ve>
			 */
			loadProducts() {
				const vm = this;
				axios.get('/warehouse/external/requests/vue-info/' + vm.infoid).then(response => {
					this.records = response.data.records.warehouse_external_request_inventory_products;
				});
			}
		},
	}
</script>