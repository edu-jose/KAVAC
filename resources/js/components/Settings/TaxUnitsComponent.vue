<template>
	<div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 mt-2 mb-2 text-center">
		<a class="btn-simplex btn-simplex-md btn-simplex-primary"
		   href="javascript:void(0)" title="Registro de unidades tributarias" data-toggle="tooltip"
		   @click="addRecord('add_tax_unit', 'tax-units', $event)">
			<i class="icofont icofont-chart-line-alt ico-3x"></i>
			<span>Unidades Tributarias</span>
		</a>
		<div class="modal fade text-left" tabindex="-1" role="dialog" id="add_tax_unit">
			<div class="modal-dialog vue-crud" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
						<h6>
							<i class="icofont icofont-chart-line-alt inline-block"></i>
							Unidades Tributarias
						</h6>
					</div>
					<div class="modal-body">
						<form-errors :listErrors="errors"></form-errors>
						<div class="row">
							<div class="col-12 col-md-6 col-md-3">
								<div class="form-group is-required">
									<label>Fecha Inicio:</label>
									<input type="date" placeholder="dd/mm/yyyy" data-toggle="tooltip"
										   title="Seleccione una fecha del calendario (requerido)"
										   class="form-control input-sm" v-model="record.start_date">
			                    </div>
							</div>
							<div class="col-12 col-md-6 col-md-3">
								<div class="form-group">
									<label>Fecha fin:</label>
									<input type="date" placeholder="dd/mm/yyyy" data-toggle="tooltip"
										   title="Seleccione una fecha del calendario"
										   class="form-control input-sm" v-model="record.end_date">
			                    </div>
							</div>
                            <div class="col-12 col-md-6 col-md-3">
                                <div class="form-group is-required">
                                    <label>Valor:</label>
                                    <input type="number" placeholder="0.00" data-toggle="tooltip" step="0.01"
                                           title="Indique el valor de la unidad tributaria (requerido)"
                                           class="form-control input-sm" v-model="record.value">
                                    <input type="hidden" v-model="record.id">
                                </div>
                            </div>
							<div class="col-12 col-md-6 col-md-3">
								<div class="form-group is-required">
									<label class="col-12">Activo:</label>
									<div class="custom-control custom-switch" data-toggle="tooltip" 
										 title="Indique si la unidad tributaria esta o no activa">
										<input type="checkbox" class="custom-control-input" 
											   id="taxUnitActive" v-model="record.active" :value="true">
										<label class="custom-control-label" for="taxUnitActive"></label>
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
							<button type="button" @click="createRecord('tax-units')" 
									class="btn btn-primary btn-sm btn-round btn-modal-save">
								Guardar
							</button>
	                	</div>
	                </div>
	                <div class="modal-body modal-table">
	                	<v-client-table :columns="columns" :data="records" :options="table_options">
	                		<div slot="start_date" slot-scope="props" class="text-center">
	                			<span>{{ format_date(props.row.start_date) }}</span>
	                		</div>
	                		<div slot="end_date" slot-scope="props" class="text-center">
	                			<span v-if="props.row.end_date">{{ format_date(props.row.end_date) }}</span>
	                			<span v-else>VIGENTE</span>
	                		</div>
	                		<div slot="value" slot-scope="props" class="text-right">
	                			<span>{{ props.row.value }}</span>
	                		</div>
	                		<div slot="active" slot-scope="props" class="text-center">
	                			<span v-if="props.row.active === true" class="text-bold text-success">SI</span>
								<span v-else class="text-bold text-danger">NO</span>
	                		</div>
	                		<div slot="id" slot-scope="props" class="text-center">
	                			<button @click="initUpdate(props.row.id, $event)"
		                				class="btn btn-warning btn-xs btn-icon btn-action"
		                				title="Modificar registro" data-toggle="tooltip" type="button">
		                			<i class="fa fa-edit"></i>
		                		</button>
		                		<button @click="deleteRecord(props.row.id, 'tax-units')"
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
					value: '',
					start_date: '',
					end_date: '',
					active: false
				},
				errors: [],
				records: [],
				columns: ['start_date', 'end_date', 'value', 'active', 'id'],
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
					value: '',
					start_date: '',
					end_date: '',
					active: false
				};
			},
		},
		created() {
			this.table_options.headings = {
				'start_date': 'Fecha de Inicio',
				'end_date': 'Fecha Final',
				'value': 'Valor',
				'active': 'Activo',
				'id': 'Acción'
			};
			this.table_options.sortable = ['start_date', 'end_date', 'value'];
			this.table_options.filterable = ['start_date', 'end_date', 'value'];
			this.table_options.columnsClasses = {
				'start_date': 'col-md-2',
				'end_date': 'col-md-2',
				'value': 'col-md-4',
				'active': 'col-md-2',
				'id': 'col-md-2'
			};
		}
	};
</script>
