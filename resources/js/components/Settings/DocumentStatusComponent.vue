<template>
	<div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 mt-2 mb-2 text-center">
		<a class="btn-simplex btn-simplex-md btn-simplex-primary" href="javascript:void(0)"
		   title="Registros de estados de los documentos" data-toggle="tooltip"
		   @click="addRecord('add_doc_status', 'document-status', $event)">
		   	<i class="icofont icofont-ui-copy ico-3x"></i>
			<span>Estatus<br>Documentos</span>
		</a>
		<div class="modal fade text-left" tabindex="-1" role="dialog" id="add_doc_status">
			<div class="modal-dialog vue-crud" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
						<h6>
							<i class="icofont icofont-ui-copy inline-block"></i>
							Estatus de Documento
						</h6>
					</div>
					<div v-show="record.id">
						<div class="modal-body">
							<form-errors :listErrors="errors"></form-errors>
							<div class="row">
								<div class="col-12 col-md-2">
									<div class="form-group is-required">
										<label>Color:</label>
										<input type="color" placeholder="Color" data-toggle="tooltip"
											title="Seleccione un color para identificar el estatus de documento (requerido)"
											class="form-control input-sm" v-model="record.color">
									</div>
								</div>
								<div class="col-12 col-md-4">
									<div class="form-group is-required">
										<label>Nombre:</label>
										<input type="text" placeholder="Nombre" data-toggle="tooltip"
											title="Indique el nombre del estatus de documento (requerido)"
											class="form-control input-sm" v-model="record.name" v-is-text>
										<input type="hidden" v-model="record.id">
									</div>
								</div>
								<div class="col-12 col-md-6">
									<div class="form-group is-required">
										<label>Descripción:</label>
										<input type="text" placeholder="Descripción" data-toggle="tooltip"
											title="Indique una descripción breve sobre el estatus de documento (requerido)"
											class="form-control input-sm" v-model="record.description" v-is-text>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-12">
									<div class="form-group is-required">
										<label>Acción a ejecutar:</label>
									</div>
									<div class="form-group">
										<div class="row">
											<div class="col-md-4 mb-2">
												<div class="custom-control custom-switch" data-toggle="tooltip"
													 title="Indica si el estatus del documento aprueba procesos">
													<input type="radio" class="custom-control-input"
															name="action" id="docStatusAP"
															v-model="record.action" value="AP" disabled>
													<label class="custom-control-label" for="docStatusAP">
														Aprueba procesos
													</label>
												</div>
											</div>
											<div class="col-md-4 mb-2">
												<div class="custom-control custom-switch" data-toggle="tooltip"
													 title="Indica si el estatus del documento rechaza procesos">
													<input type="radio" class="custom-control-input"
															name="action" id="docStatusRE" v-model="record.action" value="RE" disabled>
													<label class="custom-control-label" for="docStatusRE">
														Rechaza procesos
													</label>
												</div>
											</div>
											<div class="col-md-4 mb-2">
												<div class="custom-control custom-switch" data-toggle="tooltip"
													 title="Indica si el estatus del documento elimina procesos">
													<input type="radio" class="custom-control-input"
															name="action" id="docStatusEL" v-model="record.action" value="EL" disabled>
													<label class="custom-control-label" for="docStatusEL">
														Elimina procesos
													</label>
												</div>
											</div>
											<div class="col-md-4 mb-2">
												<div class="custom-control custom-switch" data-toggle="tooltip"
													 title="Indica si el estatus del documento inicia procesos">
													<input type="radio" class="custom-control-input"
															name="action" id="docStatusPR" v-model="record.action" value="PR" disabled>
													<label class="custom-control-label" for="docStatusPR">
														Inicia procesos
													</label>
												</div>
											</div>
											<div class="col-md-4 mb-2">
												<div class="custom-control custom-switch" data-toggle="tooltip"
													 title="Indica si el estatus del documento anula procesos">
													<input type="radio" class="custom-control-input"
															name="action" id="docStatusAN" v-model="record.action" value="AN" disabled>
													<label class="custom-control-label" for="docStatusAN">
														Anula procesos
													</label>
												</div>
											</div>
											<div class="col-md-4 mb-2">
												<div class="custom-control custom-switch" data-toggle="tooltip"
													 title="Indica si el estatus del documento cierra procesos">
													<input type="radio" class="custom-control-input"
															name="action" id="docStatusCE" v-model="record.action" value="CE" disabled>
													<label class="custom-control-label" for="docStatusCE">
														Cierra procesos
													</label>
												</div>
											</div>
										</div>
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
								<button type="button" @click="createRecord('document-status')"
										class="btn btn-primary btn-sm btn-round btn-modal-save">
									Guardar
								</button>
							</div>
						</div>
					</div>
	                <div class="modal-body modal-table">
	                	<v-client-table :columns="columns" :data="records" :options="table_options">
	                		<div slot="color" slot-scope="props" class="text-center">
								<i class="ion-android-checkbox-blank" :style="'color:' + props.row.color"
                                   :title="'Código de color: '+props.row.color"></i>
							</div>
							<div slot="action" slot-scope="props" class="text-left">
								<span v-if="props.row.action === 'AP'">Aprobación de procesos</span>
								<span v-if="props.row.action === 'RE'">Rechaza procesos</span>
								<span v-if="props.row.action === 'EL'">Eliminación de procesos</span>
								<span v-if="props.row.action === 'PR'">Inicia Procesos</span>
								<span v-if="props.row.action === 'AN'">Anulación de procesos</span>
							</div>
	                		<div slot="id" slot-scope="props" class="text-center">
	                			<button @click="initUpdate(props.row.id, $event)"
		                				class="btn btn-warning btn-xs btn-icon btn-action"
		                				title="Modificar registro" data-toggle="tooltip" type="button" v-has-tooltip>
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
				columns: ['color', 'name', 'description', 'action', 'id'],
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
					color: '#FFFFFF',
					action: '',
				};
			}
		},
		created() {
			this.table_options.headings = {
				color: 'Color',
				name: 'Nombre',
				description: 'Descripción',
				action: 'Ejecuta',
				id: 'Acción'
			};
			this.table_options.sortable = ['name', 'description'];
			this.table_options.filterable = ['name', 'description'];
			this.table_options.columnsClasses = {
				'color': 'col-md-2',
				'name': 'col-md-2',
				'description': 'col-md-4',
				'action': 'col-md-2',
				'id': 'col-md-2'
			};
		}
	};
</script>
