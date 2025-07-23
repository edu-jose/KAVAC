<template>
    <section>
        <a
            class="btn btn-warning btn-xs btn-icon btn-action" href="javascript:void(0)"
            title="Asignar Equipo de Trabajo" data-toggle="modal"
            :data-target="'#add_citizenservice-request-teams-'+requestId"
            :disabled="state != 'Aceptado'"
            v-if="state == 'Aceptado'"
        >
           <i class="ion ion-android-person-add ico-3x"></i>
		</a>
        <a
            class="btn btn-warning btn-xs btn-icon btn-action" href="javascript:void(0)"
            title="Asignar Equipo de Trabajo" data-toggle="tooltip"
            :disabled="state != 'Aceptado'"
            v-else
        >
           <i class="ion ion-android-person-add ico-3x"></i>
		</a>
		<div class="modal fade text-left" tabindex="-1" :id="'add_citizenservice-request-teams-'+requestId">
			<div class="modal-dialog vue-crud">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
						<h6>
							<i class="ion ion-android-person-add ico-3x"></i>
							Asignar Equipo de Trabajo
						</h6>
					</div>
					<div class="modal-body">
						<form-errors :listErrors="errors"></form-errors>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group is-required">
                                    <label for="teamStartAt">Fecha:</label>
                                    <input
                                        id="teamStartAt"
                                        type="date" class="form-control input-sm"
                                        v-model="record.start_at"
                                    />
                                </div>
                            </div>
                            <div class="col-md-4">
        						<div class="form-group is-required">
        							<label for="teamEmployeeName">Trabajador:</label>
        							<select2
                                        id="teamEmployeeName"
                                        :options="employees"
                                        v-model="record.payroll_employee_id"
                                        @input="setPayrollPosition()"
                                    />
        	                    </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group is-required">
                                    <label for="teamEmployeePosition">Cargo:</label>
                                    <input
                                        id="teamEmployeePosition"
                                        type="text" class="form-control input-sm"
                                        data-toggle="tooltip" title="Cargo del trabajador"
                                        v-model="record.payroll_position"
                                        disabled
                                    />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group is-required">
                                    <label for="teamTasks">Actividades / Tareas:</label>
                                    <ckeditor
                                        :editor="ckeditor.editor"
                                        id="teamTasks"
                                        data-toggle="tooltip"
                                        title="Indique las actividades que realizará el personal a asignar a esta solicitud de trámite"
                                        :config="ckeditor.editorConfig"
                                        class="form-control"
                                        tag-name="textarea"
                                        rows="3"
                                        v-model="record.tasks"
                                    ></ckeditor>
                                </div>
                            </div>
                            <div class="col-12 text-right">
                                <button
                                    type="button" class="btn btn-info btn-sm"
                                    @click="addTeam()"
                                >
                                    <i class="fa fa-plus-circle"></i>
                                    Agregar
                                </button>
                            </div>
                        </div>
	                </div>
					<div class="modal-body modal-table">
                        <v-client-table :columns="columns" :data="records" :options="table_options">
                            <div slot="payroll_employee.payroll_staff.first_name" slot-scope="props" class="text-left">
                                <span>{{ props.row.payroll_employee.payroll_staff.first_name }}</span>
                                <span class="ml-2">{{ props.row.payroll_employee.payroll_staff.last_name }}</span>
                            </div>
                            <div slot="tasks" slot-scope="props">
                                <div v-html="props.row.tasks"></div>
                            </div>
	                		<div slot="id" slot-scope="props" class="text-center">
		                		<button
                                    :id="'btnDeleteRecord' + props.index + requestId"
                                    type="button"
                                    class="btn btn-neutral btn-xs btn-icon btn-action"
                                    title="Eliminar registro"
                                    data-toggle="tooltip"
                                    @click="removeTeam(props.row.payroll_employee_id)"
                                    v-has-tooltip
                                >
									<i class="fa fa-minus-circle"></i>
								</button>
	                		</div>
	                	</v-client-table>
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
                                @click="assignTeam('citizenservice/request/manage/teams')"
                                class="btn btn-primary btn-sm btn-round btn-modal-save"
                            >
								Guardar
							</button>
	                	</div>
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
                    payroll_employee_id: '',
                    start_at: '',
                    tasks: ''
                },
                errors: [],
                records: [],
                employees: [],
                columns: [
                    'payroll_employee.payroll_staff.first_name',
                    'payroll_employee.payrollPosition.name',
                    'tasks',
                    'id'
                ],
            }
        },
        props: {
            requestId: {
                type: Number,
                required: true
            },
            teams: {
                type: Array,
                required: true
            },
            state: {
                type: String,
                required: true
            }
        },
        methods: {
            /**
             * Limpia el formulario
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            reset() {
                this.record = {
                    payroll_employee_id: '',
                    start_at: '',
                    tasks: ''
                };
                this.errors = [];
            },
            /**
             * Agrega personal al equipo a ser asignado para la solicitud de trámite
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            addTeam() {
                const _self = this;
                _self.errors = [];

                if (!_self.record.start_at) {
                    _self.errors.push('El campo fecha de asignación del trabajador es requerido');
                }
                if (!_self.record.payroll_employee_id) {
                    _self.errors.push('El campo trabajador es requerido');
                }
                if (!_self.record.tasks) {
                    _self.errors.push('El campo actividades / tareas es requerido');
                }
                if (_self.errors.length > 0) {
                    return;
                }

                const employee = _self.employees.find(x => x.id == _self.record.payroll_employee_id);
                _self.records.push({
                    start_at: _self.record.start_at,
                    citizen_service_request_id: _self.requestId,
                    tasks: _self.record.tasks,
                    id: _self.record.payroll_employee_id,
                    payroll_employee: {
                        payroll_staff: {
                            first_name: employee.payroll_staff.first_name,
                            last_name: employee.payroll_staff.last_name
                        },
                        payrollPosition: {
                            name: employee.payrollPosition.name
                        }
                    },
                    payroll_employee_id: employee?.payroll_employee_id,
                });
                _self.reset();
            },
            /**
             * Elimina personal del equipo asignado para la solicitud de trámite
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            removeTeam(id) {
                this.records = this.records.filter(x => x.payroll_employee_id != id);
            },
            /**
             * Obtiene los datos de los trabajadores
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            getPayrollStaffs() {
      			const vm = this;
      			axios.get(`${window.app_url}/citizenservice/get-staffs`).then(response => {
                    vm.employees = response.data;
      			});
			},
            /**
             * Establece la información del cargo del trabajador seleccionado
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            setPayrollPosition() {
                const _self = this;
                this.record.payroll_position = _self.employees.find(
                    x => x.id == _self.record.payroll_employee_id
                )?.payrollPosition?.name;
            },
            /**
             * Realiza la asignación del equipo a la solicitu de trámite
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            async assignTeam(url) {
                const vm = this;
                url = vm.setUrl(url);

                vm.loading = true;
                const fields = vm.records.map(record => {
                    return {
                        start_at: record.start_at,
                        payroll_employee_id: record.payroll_employee_id,
                        citizen_service_request_id: record.citizen_service_request_id,
                        tasks: record.tasks,
                        id: record.id
                    };
                });

                await axios.post(url, fields).then(response => {
                    vm.errors = [];
                    vm.showMessage('store');
                }).catch(error => {
                    vm.errors = [];

                    if (typeof (error.response) != "undefined") {
                        if (error.response.status == 403) {
                            vm.showMessage(
                                'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                            );
                        }
                        for (let index in error.response.data.errors) {
                            if (error.response.data.errors[index]) {
                                vm.errors.push(error.response.data.errors[index][0]);
                            }
                        }
                    }

                });

                vm.loading = false;
            }
        },
        created() {
			this.table_options.headings = {
                'payroll_employee.payroll_staff.first_name': 'Trabajador',
                'payroll_employee.payrollPosition.name': 'Cargo',
                'tasks': 'Actividades / Tareas',
				'id': 'Acción'
			};
			this.table_options.columnsClasses = {
                'payroll_employee.payroll_staff.first_name': 'col-md-3',
                'payroll_employee.payrollPosition.name': 'col-md-3',
                'tasks': 'col-md-5',
				'id': 'col-md-1'
			};
		},
        mounted() {
            this.records = this.teams
            $('#add_citizenservice-request-teams-'+this.requestId).on('shown.bs.modal', () => {
                this.getPayrollStaffs();
            });
        }
    }
</script>