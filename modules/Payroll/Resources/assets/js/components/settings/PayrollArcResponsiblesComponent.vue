<template>
	<section class="text-center" id="payroll_arc_responsible">
		<a class="btn-simplex btn-simplex-md btn-simplex-primary" href=""
		   title="Registros de responsables de ARC" data-toggle="tooltip"
		   @click="addRecord('add_payroll_arc_responsible', 'payroll/arc-responsible', $event)">
           <i class="icofont icofont-users ico-3x"></i>
			<span>Responsables de ARC</span>
		</a>
		<div class="modal fade text-left" tabindex="-1" role="dialog" id="add_payroll_arc_responsible">
			<div class="modal-dialog vue-crud" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
						<h6>
							<i class="icofont icofont-users ico-3x"></i>
							Responsables de ARC
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
							<div class="col-md-4" id="helpEmploymentStaff">
								<div class="form-group is-required">
									<label>Trabajador:</label>
									<select2
										:options="payroll_staffs"
										v-model="record.payroll_staff_id">
									</select2>
									<input type="hidden" v-model="record.id">
								</div>
							</div>
							<div class="col-md-4" id="helpFiscalYear">
								<div class="form-group">
									<label>Período fiscal:</label>
									<select2
										:options="fiscal_years"
										v-model="record.fiscal_year"></select2>
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
							<button type="button" @click="createRecord('payroll/arc-responsible')" 
									class="btn btn-primary btn-sm btn-round btn-modal-save">
								Guardar
							</button>
	                	</div>
	                </div>
	                <div class="modal-body modal-table">
	                	<v-client-table :columns="columns" :data="records" :options="table_options">
							<div slot="payroll_staff" slot-scope="props" class="text-center">
								{{ props.row.payroll_staff.first_name + ' ' + props.row.payroll_staff.last_name }}
							</div>
							<div slot="start_date" slot-scope="props" class="text-center">
								{{ props.row.start_date ? format_date(props.row.start_date) : '' }}
							</div>
							<div slot="end_date" slot-scope="props" class="text-center">
								{{ props.row.end_date ? format_date(props.row.end_date) : '' }}
							</div>
	                		<div slot="id" slot-scope="props" class="text-center">
	                			<button @click="initUpdate(props.row.id, $event)"
										:disabled="props.row.blocked_at ? 'disabled' : null"
		                				class="btn btn-warning btn-xs btn-icon btn-action"
		                				title="Modificar registro" data-toggle="tooltip" type="button">
		                			<i class="fa fa-edit"></i>
		                		</button>
		                		<button @click="deleteRecord(props.row.id, 'payroll/arc-responsible')"
										:disabled="props.row.blocked_at ? 'disabled' : null"
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
					payroll_staff_id: '',
					fiscal_year: '',
				},
				fiscal_years: [],
				payroll_staffs: [],
				errors: [],
				records: [],
				columns: ['payroll_staff', 'fiscal_year', 'id'],
			}
		},
		methods: {
			/**
			 * Método que borra todos los datos del formulario
			 *
			 * @author  Henry Paredes <hparedes@cenditel.gob.ve>
			 */
			reset() {
				this.record = {
					id: '',
					payroll_staff_id: '',
					fiscal_year: '',
				};
				this.errors = [];
			},
			/**
			 * Listado de años fiscales
			 *
			 * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
			 */
			async getFiscalYears() {
				const vm = this;
				const url = vm.setUrl('fiscal-years/closed/list');
				await axios.get(url).then(response => {
					vm.fiscal_years = response.data.records;
					vm.fiscal_years.unshift({"id": "", "text": "Seleccione..."});
				}).catch(error => {
					console.error(error);
				});
			},
			/**
			 * Método para la eliminación de registros
			 *
			 * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
			 *
			 * @param  {integer} id    ID del Elemento seleccionado para su eliminación
			 * @param  {string}  url   Ruta que ejecuta la acción para eliminar un registro
			 */
			deleteRecord(id, url) {
				const vm = this;
				/** @type {string} URL que atiende la petición de eliminación del registro */
				var url = vm.setUrl((url) ? url : vm.route_delete);

				bootbox.confirm({
					title: "¿Eliminar registro?",
					message: "¿Está seguro de eliminar este registro?",
					buttons: {
						cancel: {
							label: '<i class="fa fa-times"></i> Cancelar'
						},
						confirm: {
							label: '<i class="fa fa-check"></i> Confirmar'
						}
					},
					callback: async function (result) {
						if (result) {
							vm.loading = true;
							/** @type {object} Objeto con los datos del registro a eliminar */
							let recordDelete = JSON.parse(JSON.stringify(vm.records.filter((rec) => {
								return rec.id === id;
							})[0]));

							await axios.delete(`${url}${url.endsWith('/') ? '' : '/'}${recordDelete.id}`).then(response => {
								if (typeof (response.data.error) !== "undefined") {
									/** Muestra un mensaje de error si sucede algún evento en la eliminación */
									vm.showMessage('custom', 'Alerta!', 'warning', 'screen-error', response.data.message);
									return false;
								}
								/** @type {array} Arreglo de registros filtrado sin el elemento eliminado */
								vm.records = JSON.parse(JSON.stringify(vm.records.filter((rec) => {
									return rec.id !== id;
								})));
								if (typeof (vm.$refs.tableResults) !== "undefined") {
									vm.$refs.tableResults.refresh();
								}
								vm.showMessage('destroy');
							}).catch(error => {
								if (typeof (error.response) != "undefined") {
									if (error.response.status == 403) {
										vm.showMessage(
											'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
										);
									}
									if (error.response.status == 422) {
										vm.errors = [];
										for (var index in error.response.data.errors) {
											if (error.response.data.errors[index]) {
												vm.errors.push(error.response.data.errors[index][0]);
											}
										}
									}
								}
								vm.logs('mixins.js', 498, error, 'deleteRecord');
							});
							vm.loading = false;
						}
					}
				});
			},
		},
		created() {
			this.table_options.headings = {
                'payroll_staff': 'Trabajador',
                'start_date': 'Desde',
                'end_date': 'Hasta',
				'id': 'Acción'
			};
			this.table_options.sortable = ['payroll_staff', 'start_date', 'end_date'];
			this.table_options.filterable = ['payroll_staff', 'start_date', 'end_date'];
			this.table_options.columnsClasses = {
				'payroll_staff': 'col-md-6',
                'start_date': 'col-md-2',
                'end_date': 'col-md-2',
				'id': 'col-md-2'
			};
		},
		mounted () {
			const vm = this;
			$("#add_payroll_arc_responsible").on('show.bs.modal', function() {
				vm.getPayrollStaffs();
				vm.getFiscalYears();
                vm.reset();
            });
		},
	};
</script>
