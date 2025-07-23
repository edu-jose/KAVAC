<template>
	<section class="text-center" id="payroll_workload">
		<a class="btn-simplex btn-simplex-md btn-simplex-primary" href=""
		   title="Registros de rangos de antiguedad" data-toggle="tooltip"
		   @click="addRecord('add_payroll_workload', 'payroll/workload', $event)">
           <i class="icofont icofont icofont icofont-learn ico-3x"></i>
		   <span>Carga horaria</span>
		</a>
		<div class="modal fade text-left" tabindex="-1" role="dialog" id="add_payroll_workload">
			<div class="modal-dialog vue-crud" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
						<h6>
							<i class="icofont icofont icofont icofont-learn ico-3x"></i>
							Carga horaria
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
        							<label for="hours">Carga horaria diaria (horas/turno):</label>
        							<input type="text" id="hours" placeholder="Carga horaria diaria (horas/turno)"
        								    class="form-control input-sm" v-model="record.hours" data-toggle="tooltip"
                                            title="Indique el carga horaria diaria (horas/turno) del proceso (requerido)"
											v-is-digits>
                                    <input type="hidden" hours="id" id="id" v-model="record.id">
        	                    </div>
                            </div>
							<div class="col-md-6">
                                <div class="form-group is-required">
                                    <label>Cargos</label>
                                    <v-multiselect
                                        data-toggle="tooltip"
                                        title="Indique los cargos para está carga horaria"
                                        track_by="text"
                                        :hide_selected="false"
										:limit="5"
										:close_on_select="false"
                                        :options="payroll_positions"
                                        v-model="record.payroll_workload_positions"
                                    >
                                    </v-multiselect>
                                </div>
                            </div>
                            <div class="col-md-6">
        						<div class="form-group">
        							<label for="description">Descripción:</label>
        							<input type="text" id="description" placeholder="Descripción"
        								    class="form-control input-sm" v-model="record.description" data-toggle="tooltip"
        								    title="Indique el descripción del proceso (requerido)">
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
							<button type="button" @click="createRecord('payroll/workload')"
									class="btn btn-primary btn-sm btn-round btn-modal-save">
								Guardar
							</button>
	                	</div>
	                </div>
	                <div class="modal-body modal-table">
	                	<v-client-table :columns="columns" :data="records" :options="table_options">
							<div slot="positions" slot-scope="props" class="text-left">
								<ul>
									<li v-for="position in props.row.payroll_workload_positions" :key="position.payroll_position_id">{{ position.payroll_position.name }}</li>
								</ul>
							</div>
	                		<div slot="id" slot-scope="props" class="text-center">
	                			<button @click="initUpdate(props.row.id, $event)"
		                				class="btn btn-warning btn-xs btn-icon btn-action"
		                				title="Modificar registro" data-toggle="tooltip" type="button">
		                			<i class="fa fa-edit"></i>
		                		</button>
								<button @click="deleteRecord(props.row.id, 'payroll/workload')"
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
					description: '',
					hours: '',
					payroll_workload_positions: []
				},
				errors: [],
				records: [],
				payroll_positions: [],
				columns: ['hours', 'description', 'positions', 'id'],
			}
		},
		methods: {
			/**
			 * Método que borra todos los datos del formulario
			 *
			 * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
			 */
			reset() {
				this.record = {
					id: '',
					description: '',
					hours: '',
					payroll_workload_positions: []
				};

				this.getPayrollPositions();
			},

			/**
			 * Método que obtiene los cargos registrados en el sistema
			 *
			 * @param {array} ids Arreglo con los identificadores de los cargos a obtener al editar un registro
			 */
			 async getPayrollPositions(ids = []) {
                this.payroll_positions = [];
                await axios.post(`${window.app_url}/payroll/get-workload-positions`, {ids})
				.then(response => {
                    this.payroll_positions = Object.values(response.data);
					this.sortList("payroll_positions");
                });
            },

			sortList(key, subkey = null) {
				let listToSort;

				if (subkey) {
					listToSort = this[key][subkey];
				} else {
					listToSort = this[key];
				}

				// Asegúrate de que 'listToSort' sea un array válido antes de proceder
				if (!listToSort || !Array.isArray(listToSort) || listToSort.length === 0) {
					return;
				}

				let selectOption = null;
				// Filtra la opción "Seleccione..." y la guarda si existe
				const filteredList = listToSort.filter(item => {
					if (item.text === "Seleccione...") {
						selectOption = item;
						return false; // Excluye esta opción de la lista que será ordenada
					}
					return true; // Incluye el resto de los elementos
				});

				// Ordena el resto de los elementos (sin "Seleccione...") alfabéticamente
				filteredList.sort((a, b) => {
					const textA = (a.text || '').toUpperCase();
					const textB = (b.text || '').toUpperCase();

					if (textA < textB) return -1;
					if (textA > textB) return 1
					return 0;
				});

				// Si se encontró la opción "Seleccione...", la añade de nuevo al principio del array ya ordenado
				if (selectOption) filteredList.unshift(selectOption);

				// Asigna la lista final (ordenada con "Seleccione..." al inicio) de nuevo a la propiedad original
				if (subkey) {
					this[key][subkey] = filteredList;
				} else {
					this[key] = filteredList;
				}
			},

			/**
             * Método que permite crear o actualizar un registro
             *
             * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             *
             * @param  {string} url    Ruta de la acción a ejecutar para la creación o actualización de datos
             * @param  {string} list   Condición para establecer si se cargan datos en un listado de tabla.
             *                         El valor por defecto es verdadero.
             * @param  {string} reset  Condición que evalúa si se inicializan datos del formulario.
             *                         El valor por defecto es verdadero.
             */
			 async createRecord(url, list = true, reset = true) {
                const vm = this;
                url = vm.setUrl(url);

                if (vm.record.id) {
                    vm.updateRecord(url);
                }
                else {
                    vm.loading = true;
                    var fields = {};

                    for (var index in vm.record) {
                        fields[index] = vm.record[index];
                    }
                    await axios.post(url, fields).then(response => {
                        if (typeof(response.data.redirect) !== "undefined") {
                            location.href = response.data.redirect;
                        }
                        else {
                            vm.errors = [];
                            if (reset) {
                                vm.reset();
                            }
                            if (list) {
                                vm.readRecords(url);
                            }

                            vm.getPayrollPositions();
                            vm.group_select = true;
                            vm.showMessage('store');
                        }
                    }).catch(error => {
                        vm.errors = [];

                        if (typeof(error.response) !="undefined") {
                            if (error.response.status == 403) {
                                vm.showMessage(
                                    'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                                );
                            }
                            for (var index in error.response.data.errors) {
                                if (error.response.data.errors[index]) {
                                    vm.errors.push(error.response.data.errors[index][0]);
                                }
                            }
                        }

                    });

                    vm.loading = false;
                }

            },

			/**
             * Método que permite actualizar información
             *
             * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             *
             * @param  {string} url Ruta de la acci´on que modificará los datos
             */
			 async updateRecord(url) {
                const vm = this;
                vm.loading = true;
                var fields = {};
                url = vm.setUrl(url);

                for (var index in vm.record) {
                    fields[index] = vm.record[index];
                }
                await axios.patch(`${url}${(url.endsWith('/'))?'':'/'}${vm.record.id}`, fields).then(response => {
                    if (typeof(response.data.redirect) !== "undefined") {
                        location.href = response.data.redirect;
                    }
                    else {
                        vm.readRecords(url);
                        vm.reset();
                        vm.getPayrollPositions();
                        vm.group_select = true;
                        vm.showMessage('update');
                    }

                }).catch(error => {
                    vm.errors = [];

                    if (typeof(error.response) !="undefined") {
                        if (error.response.status == 403) {
                                vm.showMessage(
                                    'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                                );
                            }
                        for (var index in error.response.data.errors) {
                            if (error.response.data.errors[index]) {
                                vm.errors.push(error.response.data.errors[index][0]);
                            }
                        }
                    }
                });
                vm.loading = false;
            },

			/**
			 * Método que carga el formulario con los datos a modificar
			 *
			 * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
			 *
			 * @param  {integer} index Identificador del registro a ser modificado
			 * @param {object} event   Objeto que gestiona los eventos
			 */
			async initUpdate(id, event) {
				let vm = this;
				vm.errors = [];

				let recordEdit = await JSON.parse(JSON.stringify(vm.records.filter((rec) => {
					return rec.id === id;
				})[0])) || vm.reset();

				vm.record.id = recordEdit.id;
				vm.record.hours = recordEdit.hours;
				vm.record.description = recordEdit.description;

				let ids = [];

                for (const value of recordEdit.payroll_workload_positions) {
                    ids.push(value.payroll_position_id);
                }

                vm.getPayrollPositions(ids).then(() => {
					vm.record.payroll_workload_positions = [];

					for (const value of recordEdit.payroll_workload_positions) {
						vm.record.payroll_workload_positions.push({
							id: value.payroll_position_id,
							text: value.payroll_position.name
						})
					}
				});


				event.preventDefault();
			},
		},
		created() {
			this.table_options.headings = {
				'hours': 'carga horaria diaria (horas/turno)',
                'description': 'Descripción',
                'positions': 'Cargos',
				'id': 'Acción'
			};
			this.table_options.sortable = ['hours'];
			this.table_options.filterable = ['hours'];
			this.table_options.columnsClasses = {
				'hours': 'col-md-2',
                'description': 'col-md-4',
                'positions': 'col-md-5',
				'id': 'col-md-1'
			};
		},
		mounted () {
			const vm = this;
			$("#add_payroll_workload").on('show.bs.modal', function() {
                vm.reset();
				vm.getPayrollPositions();
            });
		},
	};
</script>