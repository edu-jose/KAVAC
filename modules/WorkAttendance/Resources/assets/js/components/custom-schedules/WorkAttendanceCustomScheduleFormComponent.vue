<template>
    <section id="workAttendanceCustomSchedule">
        <div class="card-body">
            <form-errors :listErrors="errors"></form-errors>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group is-required">
                        <label for="payrollStaffId">Personal</label>
                        <select2
                            id="payrollStaffId" :options="payroll_staffs"
                            v-model="record.payroll_staff_id"
                            title="Seleccione el personal al que se le asignará el horario personalizado"
                            data-toggle="tooltip"
                        />
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group is-required">
                        <label for="authorizedStaffId">Autorizado por</label>
                        <select2
                            id="authorizedStaffId" :options="payroll_staffs"
                            v-model="record.authorized_payroll_staff_id"
                            title="Seleccione la persona que autoriza el horario personalizado"
                            data-toggle="tooltip"
                        />
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group is-required">
                        <label for="startDateAt">Fecha de inicio</label>
                        <input
                            type="date" id="startDateAt"
                            class="form-control input-sm no-restrict"
                            data-toggle="tooltip"
                            title="Indique en la que fecha en la que inicia el horario personalizado"
                            v-model="record.start_date_at">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="endDateAt">Fecha de finalización</label>
                        <input
                            type="date" id="endDateAt"
                            class="form-control input-sm no-restrict"
                            data-toggle="tooltip"
                            title="Indique la fecha en la que finaliza el horario personalizado"
                            v-model="record.end_date_at">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="active">Activo</label>
                        <div class="custom-control custom-switch">
                            <input
                                type="checkbox"
                                class="custom-control-input" id="active"
                                v-model="record.active" :value="true"
                            >
                            <label class="custom-control-label" for="active">&nbsp;</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="customScheduleType">Tipo</label>
                        <div class="row">
                            <div class="col-12">
                                <div class="custom-control custom-switch custom-control-inline mr-4">
                                    <input
                                        type="radio"
                                        class="custom-control-input" id="isTeacher"
                                        v-model="record.custom_schedule_type" value="D"
                                    >
                                    <label class="custom-control-label" for="isTeacher">Docencia</label>
                                </div>
                                <div class="custom-control custom-switch custom-control-inline mx-4">
                                    <input
                                        type="radio"
                                        class="custom-control-input" id="isStudent"
                                        v-model="record.custom_schedule_type" value="E"
                                    >
                                    <label class="custom-control-label" for="isStudent">Estudio</label>
                                </div>
                                <div class="custom-control custom-switch custom-control-inline ml-4">
                                    <input
                                        type="radio"
                                        class="custom-control-input" id="isOther"
                                        v-model="record.custom_schedule_type" value="O"
                                    >
                                    <label class="custom-control-label" for="isOther">Otro</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <upload-documents
                        inputLabel="Documento" inputTooltip="Seleccione el documento"
                        :parentRecord="'documents'"
                    />
                    <div class="row" v-if="record.documentUrl">
                        <div class="col-12">
                            <a
                                :href="setUrl(record.documentUrl)"
                                target="_blank"
                                class="btn btn-primary btn-xs btn-icon btn-action btn-tooltip"
                                data-toggle="tooltip" title="Ver documento"
                            >
                                <i class="fa fa-file"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div id="reasonText" class="form-group is-required">
                        <label for="reason">Motivo</label>
                        <ckeditor
                            :editor="ckeditor.editor"
                            id="reason"
                            data-toggle="tooltip"
                            title="Indique el motivo del horario personalizado"
                            :config="ckeditor.editorConfig"
                            class="form-control"
                            name="reason"
                            tag-name="textarea"
                            rows="3"
                            v-model="record.reason"
                        ></ckeditor>
                    </div>
                </div>
                <div class="col-12">
                    <div id="scheduleList" class="form-group">
                        <label for="schedule" class="my-2">Horario Personalizado <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-12">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width:13.571428571%;">Lunes</th>
                                            <th class="text-center" style="width:13.571428571%;">Martes</th>
                                            <th class="text-center" style="width:13.571428571%;">Miércoles</th>
                                            <th class="text-center" style="width:13.571428571%;">Jueves</th>
                                            <th class="text-center" style="width:13.571428571%;">Viernes</th>
                                            <th class="text-center" style="width:13.571428571%;">Sábado</th>
                                            <th class="text-center" style="width:13.571428571%;">Domingo</th>
                                            <th class="text-center">
                                                <button
                                                    class="btn btn-neutral btn-sm" @click="addSchedule()"
                                                    data-toggle="tooltip"
                                                    title="Agregar horario"
                                                >
                                                    <i class="fa fa-plus-circle text-info"></i>
                                                </button>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="text-center" v-for="(schedule, index) in record.schedule" :key="index">
                                            <td>
                                                <div class="d-flex">
                                                    <input
                                                        type="text" class="form-control input-sm col-5 ml-2 p-sm-1"
                                                        v-model="schedule.mon_start_at"
                                                        placeholder="HH:MM am/pm"
                                                        v-input-mask data-inputmask-regex="^(0[1-9]|1[0-2]):[0-5][0-9] (am|pm)$"
                                                    >
                                                    <span class="mx-2">-</span>
                                                    <input
                                                        type="text" class="form-control input-sm col-5 p-sm-1"
                                                        v-model="schedule.mon_end_at"
                                                        placeholder="HH:MM am/pm"
                                                        v-input-mask data-inputmask-regex="^(0[1-9]|1[0-2]):[0-5][0-9] (am|pm)$"
                                                    >
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex">
                                                    <input
                                                        type="text" class="form-control input-sm col-5 ml-2 p-sm-1"
                                                        v-model="schedule.tue_start_at"
                                                        placeholder="HH:MM am/pm"
                                                        v-input-mask data-inputmask-regex="^(0[1-9]|1[0-2]):[0-5][0-9] (am|pm)$"
                                                    >
                                                    <span class="mx-2">-</span>
                                                    <input
                                                        type="text" class="form-control input-sm col-5 p-sm-1"
                                                        v-model="schedule.tue_end_at"
                                                        placeholder="HH:MM am/pm"
                                                        v-input-mask data-inputmask-regex="^(0[1-9]|1[0-2]):[0-5][0-9] (am|pm)$"
                                                    >
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex">
                                                    <input
                                                        type="text" class="form-control input-sm col-5 ml-2 p-sm-1"
                                                        v-model="schedule.wed_start_at"
                                                        placeholder="HH:MM am/pm"
                                                        v-input-mask data-inputmask-regex="^(0[1-9]|1[0-2]):[0-5][0-9] (am|pm)$"
                                                    >
                                                    <span class="mx-2">-</span>
                                                    <input
                                                        type="text" class="form-control input-sm col-5 p-sm-1"
                                                        v-model="schedule.wed_end_at"
                                                        placeholder="HH:MM am/pm"
                                                        v-input-mask data-inputmask-regex="^(0[1-9]|1[0-2]):[0-5][0-9] (am|pm)$"
                                                    >
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex">
                                                    <input
                                                        type="text" class="form-control input-sm col-5 ml-2 p-sm-1"
                                                        v-model="schedule.thu_start_at"
                                                        placeholder="HH:MM am/pm"
                                                        v-input-mask data-inputmask-regex="^(0[1-9]|1[0-2]):[0-5][0-9] (am|pm)$"
                                                    >
                                                    <span class="mx-2">-</span>
                                                    <input
                                                        type="text" class="form-control input-sm col-5 p-sm-1"
                                                        v-model="schedule.thu_end_at"
                                                        placeholder="HH:MM am/pm"
                                                        v-input-mask data-inputmask-regex="^(0[1-9]|1[0-2]):[0-5][0-9] (am|pm)$"
                                                    >
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex">
                                                    <input
                                                        type="text" class="form-control input-sm col-5 ml-2 p-sm-1"
                                                        v-model="schedule.fri_start_at"
                                                        placeholder="HH:MM am/pm"
                                                        v-input-mask data-inputmask-regex="^(0[1-9]|1[0-2]):[0-5][0-9] (am|pm)$"
                                                    >
                                                    <span class="mx-2">-</span>
                                                    <input
                                                        type="text" class="form-control input-sm col-5 p-sm-1"
                                                        v-model="schedule.fri_end_at"
                                                        placeholder="HH:MM am/pm"
                                                        v-input-mask data-inputmask-regex="^(0[1-9]|1[0-2]):[0-5][0-9] (am|pm)$"
                                                    >
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex">
                                                    <input
                                                        type="text" class="form-control input-sm col-5 ml-2 p-sm-1"
                                                        v-model="schedule.sat_start_at"
                                                        placeholder="HH:MM am/pm"
                                                        v-input-mask data-inputmask-regex="^(0[1-9]|1[0-2]):[0-5][0-9] (am|pm)$"
                                                    >
                                                    <span class="mx-2">-</span>
                                                    <input
                                                        type="text" class="form-control input-sm col-5 p-sm-1"
                                                        v-model="schedule.sat_end_at"
                                                        placeholder="HH:MM am/pm"
                                                        v-input-mask data-inputmask-regex="^(0[1-9]|1[0-2]):[0-5][0-9] (am|pm)$"
                                                    >
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex">
                                                    <input
                                                        type="text" class="form-control input-sm col-5 ml-2 p-sm-1"
                                                        v-model="schedule.sun_start_at"
                                                        placeholder="HH:MM am/pm"
                                                        v-input-mask data-inputmask-regex="^(0[1-9]|1[0-2]):[0-5][0-9] (am|pm)$"
                                                    >
                                                    <span class="mx-2">-</span>
                                                    <input
                                                        type="text" class="form-control input-sm col-5 p-sm-1"
                                                        v-model="schedule.sun_end_at"
                                                        placeholder="HH:MM am/pm"
                                                        v-input-mask data-inputmask-regex="^(0[1-9]|1[0-2]):[0-5][0-9] (am|pm)$"
                                                    >
                                                </div>
                                            </td>
                                            <td>
                                                <button
                                                    type="button" class="btn btn-neutral btn-sm"
                                                    @click="removeSchedule(index)"
                                                    data-toggle="tooltip"
                                                    title="Eliminar horario"
                                                    v-if="index > 0"
                                                >
                                                    <i class="fa fa-minus-circle text-danger"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <small class="form-text text-muted">
                                    * Indique la hora de inicio y fin en cada columna del día que le corresponda según el horario especial a registrar para la asistencia a la jornada laboral
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-right">
			<div class="row">
				<div class="col-md-3 offset-md-9" id="helpParamButtons">
					<button type="button" @click="reset()" class="btn btn-default btn-icon btn-round"
							data-toggle="tooltip"
		                    title ="Borrar datos del formulario">
							<i class="fa fa-eraser"></i>
					</button>

		        	<button type="button" @click="redirect_back(route_back)"
                        	class="btn btn-warning btn-icon btn-round" data-toggle="tooltip"
                        	title="Cancelar y regresar">
                    		<i class="fa fa-ban"></i>
            		</button>

		        	<button type="button"  @click="createRecord('work-attendance/custom-schedules')"
		        			class="btn btn-success btn-icon btn-round btn-modal-save"
		        			title="Guardar registro">
		        			<i class="fa fa-save"></i>
		            </button>
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
                    start_date_at: '',
                    end_date_at: '',
                    active: true,
                    custom_schedule_type: '',
                    reason: '',
                    payroll_staff_id: '',
                    authorized_payroll_staff_id: '',
                    documents: [],
                    schedule: [],
                },
                errors: [],
                payroll_staffs: [],
            }
        },
        props: {
            route_back: {
                type: String,
                required: true,
            },
            edit_data: {
                type: Object,
                required: false,
            }
        },
        methods: {
            reset() {
                this.record = {
                    start_date_at: '',
                    end_date_at: '',
                    active: true,
                    custom_schedule_type: '',
                    reason: '',
                    payroll_staff_id: '',
                    authorized_payroll_staff_id: '',
                    documents: [],
                    schedule: [],
                };
                this.errors = [];
            },
            addSchedule() {
                this.record.schedule.push(
                    {
                        mon_start_at: '', mon_end_at: '',
                        tue_start_at: '', tue_end_at: '',
                        wed_start_at: '', wed_end_at: '',
                        thu_start_at: '', thu_end_at: '',
                        fri_start_at: '', fri_end_at: '',
                        sat_start_at: '', sat_end_at: '',
                        sun_start_at: '', sun_end_at: ''
                    },
                );
            },
            removeSchedule(index) {
                this.record.schedule.splice(index, 1);
            },
            async initUpdate() {
                const _self = this;
                if (_self.edit_data) {
                    const {
                        id,
                        start_date_at,
                        end_date_at,
                        active,
                        custom_schedule_type,
                        reason,
                        payroll_staff_id,
                        authorized_payroll_staff_id,
                        document,
                        schedule
                    } = _self.edit_data;
                    _self.record.id = id;
                    _self.record.start_date_at = start_date_at;
                    _self.record.end_date_at = end_date_at;
                    _self.record.active = active;
                    _self.record.custom_schedule_type = custom_schedule_type;
                    _self.record.reason = reason;
                    _self.record.payroll_staff_id = payroll_staff_id;
                    _self.record.authorized_payroll_staff_id = authorized_payroll_staff_id;
                    _self.record.documentUrl = document?.url;
                    _self.record.schedule = schedule;
                }
            }
        },
        async mounted() {
            const _self = this;
            await _self.getStaffs();
            _self.record.schedule = [
                {
                    mon_start_at: '', mon_end_at: '',
                    tue_start_at: '', tue_end_at: '',
                    wed_start_at: '', wed_end_at: '',
                    thu_start_at: '', thu_end_at: '',
                    fri_start_at: '', fri_end_at: '',
                    sat_start_at: '', sat_end_at: '',
                    sun_start_at: '', sun_end_at: ''
                },
            ];
            await _self.initUpdate();
        }
    }
</script>
