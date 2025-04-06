<template>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 mt-2 mb-2 text-center">
		<a class="btn-simplex btn-simplex-md btn-simplex-primary"
		   href="javascript:void(0)" title="Configuración de personas a notificar sobre la asistencia del personal"
		   data-toggle="tooltip" @click="addRecord('add_notify', '/work-attendance/settings/notifications/list', $event)">
			<i class="icofont icofont-notification ico-3x"></i>
			<span>Notificaciones</span>
		</a>
		<div class="modal fade text-left" tabindex="-1" id="add_notify">
			<div class="modal-dialog vue-crud">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
						<h6>
							<i class="icofont icofont-notification inline-block"></i>
							Notificaciones
						</h6>
					</div>
					<div class="modal-body">
						<form-errors :listErrors="errors"></form-errors>
						<div class="row">
                            <div class="col-md-4">
                                <div class="form-group is-required">
                                    <label for="positionId">Cargo</label>
                                    <select2
                                        id="positionId" :options="positions"
                                        v-model="record.position_id"
                                        @input="setEmployments()"
                                    />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group is-required">
                                    <label for="payrollEmploymentId">Persona</label>
                                    <select2
                                        id="payrollEmploymentId"
                                        :options="employments"
                                        v-model="record.payroll_employment_id"
                                    />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group is-required">
                                    <label for="periodicity">Periodicidad</label>
                                    <select2
                                        id="periodicity"
                                        :options="periodicities"
                                        v-model="record.periodicity"
                                        disabled
                                    />
                                </div>
                            </div>
                            <div class="col md-2">
                                <div class="form-group">
                                    <label for="notify">Activar Notificación</label>
                                    <div class="col-md-12">
                                        <div class="custom-control custom-switch" data-toggle="tooltip"
                                             title="Indique si se activa/desactiva la notificacion">
                                            <input
                                                type="checkbox" class="custom-control-input" id="notify"
                                                v-model="record.notify" :value="true"
                                            >
                                            <label class="custom-control-label" for="notify">&nbsp;</label>
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
							<button type="button" @click="createRecord('/work-attendance/notifications/settings')"
									class="btn btn-primary btn-sm btn-round btn-modal-save">
								Guardar
							</button>
	                	</div>
	                </div>
	                <div class="modal-body modal-table">
	                	<v-client-table
                            :columns="columns"
                            :data="records"
                            :options="table_options"
                        >
                            <div slot="position_id" slot-scope="props" class="text-left">
                                {{ props.row.payroll_employment?.payrollPosition?.name || '' }}
                            </div>
                            <div slot="payroll_employment_id" slot-scope="props" class="text-left">
                                {{ props.row.payroll_employment.payroll_staff.first_name }} {{ props.row.payroll_employment.payroll_staff.last_name }}
                            </div>
                            <div slot="email" slot-scope="props" class="text-left">
                                {{ props.row.payroll_employment.institution_email || props.row.payroll_employment.payroll_staff.email }}
                            </div>
                            <div slot="periodicity" slot-scope="props" class="text-left">
                                <span v-if="props.row.periodicity == 'D'">Diario</span>
                                <span v-else-if="props.row.periodicity == 'S'">Semanal</span>
                                <span v-else-if="props.row.periodicity == 'Q'">Quincenal</span>
                                <span v-else-if="props.row.periodicity == 'M'">Mensual</span>
                                <span v-else-if="props.row.periodicity == 'B'">Bimestral</span>
                                <span v-else-if="props.row.periodicity == 'T'">Trimestral</span>
                                <span v-else-if="props.row.periodicity == 'A'">Anual</span>
                            </div>
                            <div slot="notify" slot-scope="props" class="text-center">
                                <span v-if="props.row.notify == true" class="badge badge-success">SI</span>
                                <span v-else class="badge badge-danger">NO</span>
                            </div>
	                		<div slot="id" slot-scope="props" class="text-center">
	                			<button @click="initUpdate(props.row.id, $event)"
		                				class="btn btn-warning btn-xs btn-icon btn-action"
		                				title="Modificar registro" data-toggle="tooltip" type="button">
		                			<i class="fa fa-edit"></i>
		                		</button>
		                		<button @click="deleteRecord(props.row.id, '/work-attendance/notifications/settings')"
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
    /** Componente para la configuración de las notificaciones de asistencia del personal */
    export default {
        data() {
            return {
                record: {
                    id: '',
                    position_id: '',
                    payroll_employment_id: '',
                    periodicity: 'S',
                    notify: true
                },
                errors: [],
                records: [],
                positions: [],
                employments: [],
                periodicities: [
                    { id: '', text: 'Seleccione...' },
                    { id: 'D', text: 'Diaria' },
                    { id: 'S', text: 'Semanal' },
                    { id: 'Q', text: 'Quincenal' },
                    { id: 'M', text: 'Mensual' },
                    { id: 'B', text: 'Bimestral' },
                    { id: 'T', text: 'Trimestral' },
                    { id: 'A', text: 'Anual' }
                ],
                columns: ['position_id', 'payroll_employment_id', 'email', 'periodicity', 'notify', 'id'],
            }
        },
        methods: {
            reset() {
                this.record.id = '';
                this.record.position_id = '';
                this.record.payroll_employment_id = '';
                this.record.periodicity = 'S';
                this.record.notify = true;
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

                vm.record = recordEdit;
                setTimeout(() => {
                    vm.record.payroll_employment_id = recordEdit.payroll_employment.id;
                }, 1000);

                event.preventDefault();
            },
        },
        created() {
            this.table_options.headings = {
                position_id: "Cargo",
                payroll_employment_id: "Nombres y Apellidos",
                email: 'Correo a notificar',
                periodicity: "Periodicidad",
                notify: "Activa",
                id: "Acción",
            };
            this.table_options.sortable = [
                "position_id",
                "payroll_employment_id",
                "email",
                "periodicity",
                "notify"
            ];
            this.table_options.filterable = [
                "position_id",
                "payroll_employment_id",
                "email",
                "periodicity",
                "notify"
            ];
            this.table_options.orderBy = { column: "id" };
            this.table_options.columnsClasses = {
				'position_id': 'col-md-3',
				'payroll_employment_id': 'col-md-3',
                'email': 'col-md-2',
                'periodicity': 'col-md-1',
                'notify': 'col-md-1',
				'id': 'col-md-2'
			};
        },
        async mounted() {
            const _self = this;
            await _self.getPositions();
            _self.employments = [{id: '', text: 'Seleccione...'}];
            _self.reset();
        }
    }
</script>