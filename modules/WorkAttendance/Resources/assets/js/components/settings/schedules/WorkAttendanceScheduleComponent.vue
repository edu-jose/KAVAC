<template>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 mt-2 mb-2 text-center">
		<a class="btn-simplex btn-simplex-md btn-simplex-primary"
		   href="javascript:void(0)" title="Configuración de personas a notificar sobre la asistencia del personal"
		   data-toggle="tooltip" @click="addRecord('add_schedule', '/work-attendance/schedule/settings', $event)">
			<i class="icofont icofont-calendar ico-3x"></i>
			<span>Horario Laboral</span>
		</a>
		<div class="modal fade text-left" tabindex="-1" id="add_schedule">
			<div class="modal-dialog vue-crud">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
						<h6>
							<i class="icofont icofont-calendar inline-block"></i>
							Horario Laboral
						</h6>
					</div>
					<div class="modal-body">
						<form-errors :listErrors="errors"></form-errors>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group is-required">
                                    <label for="dayName">Día</label>
                                    <select2 id="dayName" :options="days" v-model="record.day_name"/>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group required">
                                    <label for="startTime">Hora de inicio</label>
                                    <input
                                        type="time" class="form-control"
                                        id="startTime" v-model="record.start_time"
                                        title="Indique la hora de inicio de la jornada laboral"
                                        data-toggle="tooltip"
                                    >
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group required">
                                    <label for="endTime">Hora de fin</label>
                                    <input
                                        type="time" class="form-control" id="endTime" v-model="record.end_time"
                                        title="Indique la hora de fin de la jornada laboral"
                                        data-toggle="tooltip"
                                    >
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="breakTime">Tiempo de descanso</label>
                                    <input
                                        type="text" class="form-control" id="breakTime"
                                        v-model="record.break_time"
                                        v-input-mask
                                        data-inputmask="'mask': '99:99'"
                                        title="Indique el tiempo de descanso en horas y minutos (opcional)"
                                        data-toggle="tooltip"
                                        autocomplete="off"
                                    >
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="lunchTime">Tiempo de almuerzo</label>
                                    <input
                                        type="text" class="form-control" id="lunchTime" v-model="record.lunch_time"
                                        v-input-mask
                                        data-inputmask="'mask': '99:99'"
                                        title="Indique el tiempo de almuerzo en horas y minutos (opcional)"
                                        data-toggle="tooltip"
                                        autocomplete="off"
                                    >
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="active">Activo</label>
                                    <div class="col-md-12">
                                        <div
                                            class="custom-control custom-switch" data-toggle="tooltip"
                                            title="Indique si el registro se encuentra activo"
                                        >
                                            <input
                                                type="checkbox" class="custom-control-input" id="active"
                                                v-model="record.active" :value="true"
                                            >
                                            <label class="custom-control-label" for="active">&nbsp;</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="is_extended">Es horario extendido?</label>
                                    <div class="col-md-12">
                                        <div
                                            class="custom-control custom-switch" data-toggle="tooltip"
                                            title="Indique si es un horario extendido (mayor a la jornada normal)"
                                        >
                                            <input
                                                type="checkbox" class="custom-control-input" id="is_extended"
                                                v-model="record.is_extended" :value="true"
                                            >
                                            <label class="custom-control-label" for="is_extended">&nbsp;</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
	                	<div class="form-group">
	                		<button
                                type="button" class="btn btn-default btn-sm btn-round btn-modal-close"
								@click="reset" data-dismiss="modal"
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
                                type="button" @click="validateForm()"
								class="btn btn-primary btn-sm btn-round btn-modal-save"
                            >
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
                            <div slot="active" slot-scope="props" class="text-center">
                                <span v-if="props.row.active" class="badge badge-success">SI</span>
                                <span v-else class="badge badge-danger">NO</span>
                            </div>
                            <div slot="is_extended" slot-scope="props" class="text-center">
                                <span v-if="props.row.is_extended" class="badge badge-success">SI</span>
                                <span v-else class="badge badge-danger">NO</span>
                            </div>
                            <div slot="start_time" slot-scope="props" class="text-center">
                                {{ setTimeFormat(props.row.start_time) }}
                            </div>
                            <div slot="end_time" slot-scope="props" class="text-center">
                                {{ setTimeFormat(props.row.end_time) }}
                            </div>
                            <div slot="id" slot-scope="props" class="text-center">
	                			<button @click="initUpdate(props.row.id, $event)"
		                				class="btn btn-warning btn-xs btn-icon btn-action"
		                				title="Modificar registro" data-toggle="tooltip" type="button">
		                			<i class="fa fa-edit"></i>
		                		</button>
		                		<button @click="deleteRecord(props.row.id, '/work-attendance/schedule/settings')"
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
                    day_name: '',
                    day_number: '',
                    start_time: '',
                    end_time: '',
                    break_time: '',
                    lunch_time: '',
                    work_time: '',
                    active: true,
                    is_extended: false
                },
                records: [],
                errors: [],
                days: [
                    { id: '', text: 'Seleccione...', day_number: '' },
                    { id: 'Lunes', text: 'Lunes', day_number: '1' },
                    { id: 'Martes', text: 'Martes', day_number: '2' },
                    { id: 'Miércoles', text: 'Miércoles', day_number: '3' },
                    { id: 'Jueves', text: 'Jueves', day_number: '4' },
                    { id: 'Viernes', text: 'Viernes', day_number: '5' },
                    { id: 'Sábado', text: 'Sábado', day_number: '6' },
                    { id: 'Domingo', text: 'Domingo', day_number: '7' },
                ],
                columns: [
                    'day_name',
                    'start_time',
                    'end_time',
                    'work_time',
                    'active',
                    'is_extended',
                    'id'
                ],
            }
        },
        watch: {
            'record.day_name': function (value) {
                const _self = this;
                _self.record.day_number = '';
                if (value) {
                    _self.record.day_number = _self.days.find(day => day.id == value)?.day_number ?? '';
                }
            }
        },
        methods: {
            reset() {
                this.record = {
                    day_name: '',
                    day_number: '',
                    start_time: '',
                    end_time: '',
                    break_time: '',
                    lunch_time: '',
                    work_time: '',
                    active: true,
                    is_extended: false
                };
                this.errors = [];
            },
            setTimeFormat(time) {
                return moment(time, 'HH:mm').format("hh:mm a");
            },
            validateForm() {
                const _self = this;
                _self.errors = [];
                if (!_self.record.day_name) {
                    _self.errors.push('El día de la semana es requerido.');
                }
                if (!_self.record.start_time) {
                    _self.errors.push('La hora de inicio es requerida.');
                }
                if (!_self.record.end_time) {
                    _self.errors.push('La hora de fin es requerida.');
                }
                if (_self.record.break_time && _self.record.break_time.indexOf('_') > -1) {
                    _self.errors.push('El tiempo de descanso no tiene un formato válido, debe indicar horas y minutos.');
                }
                if (_self.record.lunch_time && _self.record.lunch_time.indexOf('_') > -1) {
                    _self.errors.push('El tiempo de almuerzo no tiene un formato válido, debe indicar horas y minutos.');
                }
                if (_self.errors.length > 0) {
                    return false;
                }
                _self.createRecord('/work-attendance/schedule/settings');
            }
        },
        created() {
            this.table_options.headings = {
                day_name: "Día",
                start_time: "Hora de inicio",
                end_time: 'Hora de fin',
                work_time: "Tiempo de trabajo",
                active: "Activo",
                is_extended: "Horario Extendido",
                id: "Acción",
            };
            this.table_options.sortable = [
                'day_name',
                'start_time',
                'end_time',
                'work_time',
            ];
            this.table_options.filterable = [
                'day_name',
                'start_time',
                'end_time',
                'work_time',
            ];
            this.table_options.orderBy = { column: "id" };
            this.table_options.columnsClasses = {
				'day_name': 'col-md-2',
				'start_time': 'col-md-2 text-center',
                'end_time': 'col-md-2 text-center',
                'work_time': 'col-md-2 text-center',
                'active': 'col-md-1 text-center',
                'is_extended': 'col-md-1 text-center',
				'id': 'col-md-2 text-center'
			};
        },
    }
</script>