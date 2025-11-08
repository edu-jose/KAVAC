<template>
	<section id="workAttendancePermission">

        <div class="card-body">
            <form-errors :listErrors="errors"></form-errors>
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group is-required">
                        <label for="startDateAt">Fecha de inicio</label>
                        <input
                            type="date" id="startDateAt"
                            class="form-control input-sm no-restrict"
                            data-toggle="tooltip"
                            title="Indique la fecha del permiso"
                            v-model="record.start_date_at">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="startTimeAt">Hora de inicio</label>
                        <input
                            id="startTimeAt"
                            type="text"
                            class="form-control input-sm"
                            placeholder="HH:MM am/pm"
                            data-toggle="tooltip"
                            title="Indique la hora de inicio del permiso en formato 12 horas (ej. 08:00 am)"
                            v-model="record.start_time_at"
                            v-input-mask data-inputmask-regex="^(0[1-9]|1[0-2]):[0-5][0-9] (am|pm)$"
                            autocomplete="off"
                        >
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group is-required">
                        <label for="startDateAt">Fecha de finalización</label>
                        <input
                            type="date" id="endDateAt"
                            class="form-control input-sm no-restrict"
                            data-toggle="tooltip"
                            title="Indique la fecha de finalización del permiso"
                            v-model="record.end_date_at">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="endTimeAt">Hora de finalización</label>
                        <input
                            id="endTimeAt"
                            type="text"
                            class="form-control input-sm"
                            placeholder="HH:MM am/pm"
                            data-toggle="tooltip"
                            title="Indique la hora de finalización del permiso en formato 12 horas (ej. 04:00 pm)"
                            v-model="record.end_time_at"
                            v-input-mask data-inputmask-regex="^(0[1-9]|1[0-2]):[0-5][0-9] (am|pm)$"
                            autocomplete="off"
                        >
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group is-required">
                        <label for="payrollStaff">Personal</label>
                        <select2
                            id="payrollStaffId" :options="payroll_staffs"
                            v-model="record.payroll_staff_id"
                            title="Seleccione el personal que solicita el permiso"
                            data-toggle="tooltip"
                        />
                    </div>
                </div>
                <div class="col-md-6">
                    <upload-documents
                        inputLabel="Soporte (opcional)"
                        inputTooltip="Seleccione el(los) documento(s) o imagen(es) que avala(n) la solicitud de permiso"
                        :parentRecord="'documentFiles'"
                        acceptFiles=".docx,.doc,.odt,.pdf,.jpg,.jpeg,.png"
                    />
                </div>
                <div class="col-2 col-md-4" v-if="record.document?.url">
                    <div class="form-group">
                        <strong>Ver Documento:</strong>
                        <div class="row" style="margin: 1px 0">
                            <a
                                :href="showDocument()" target="_blank"
                                class="btn btn-primary btn-xs btn-icon btn-action btn-tooltip"
                                data-toggle="tooltip" title="Ver documento"
                            >
                                <i class="fa fa-file" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="endTimeAt">Estatus</label>
                        <div class="row">
                            <div class="col-12">
                                <div class="custom-control custom-switch custom-control-inline mr-4">
                                    <input
                                        type="radio"
                                        class="custom-control-input" id="isPending"
                                        v-model="record.status" value="pending"
                                    >
                                    <label class="custom-control-label" for="isPending">Pendiente</label>
                                </div>
                                <div class="custom-control custom-switch custom-control-inline mx-4">
                                    <input
                                        type="radio"
                                        class="custom-control-input" id="isApproved"
                                        v-model="record.status" value="approved"
                                    >
                                    <label class="custom-control-label" for="isApproved">Aprobado</label>
                                </div>
                                <div class="custom-control custom-switch custom-control-inline ml-4">
                                    <input
                                        type="radio"
                                        class="custom-control-input" id="isRejected"
                                        v-model="record.status" value="rejected"
                                    >
                                    <label class="custom-control-label" for="isRejected">Rechazado</label>
                                </div>
                            </div>
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
                            title="Indique el motivo del permiso"
                            :config="ckeditor.editorConfig"
                            class="form-control"
                            name="reason"
                            tag-name="textarea"
                            rows="3"
                            v-model="record.reason"
                        ></ckeditor>
                    </div>
                </div>
                <div class="col-md-12">
                    <div id="commentText" class="form-group">
                        <label for="comments">Comentarios / Observaciones</label>
                        <ckeditor
                            :editor="ckeditor.editor"
                            id="comments"
                            data-toggle="tooltip"
                            title="Indique algún comentario adicional sobre el permiso"
                            :config="ckeditor.editorConfig"
                            class="form-control"
                            name="comments"
                            tag-name="textarea"
                            rows="3"
                            v-model="record.comments"
                        ></ckeditor>
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

		        	<button type="button"  @click="createRecord('work-attendance/permissions')"
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
                    start_time_at: '',
                    end_date_at: '',
                    end_time_at: '',
                    status: 'pending',
                    reason: '',
                    comments: '',
                    payroll_staff_id: '',
				},
                payroll_staffs: [],
				errors: [],
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
			/**
			 * Método que borra todos los datos del formulario
			 */
			reset() {
				this.record = {
					id: '',
					start_date_at: '',
                    start_time_at: '',
                    end_date_at: '',
                    end_time_at: '',
                    reason: '',
                    status: 'pending',
                    comments: '',
                    payroll_staff_id: '',
				};
                this.errors = [];
			},

            async getStaffs() {
                const _self = this;
                _self.payroll_staffs = [{
                    id: '',
                    text: 'Seleccione...'
                }];
                await axios.get(`${window.app_url}/work-attendance/get-staffs`)
                    .then(response => {
                        _self.payroll_staffs = [
                            ..._self.payroll_staffs,
                            ...response.data.staffs.map(staff => {
                                return {
                                    id: staff.id,
                                    text: staff.full_name
                                };
                            })
                        ];
                    })
                    .catch(error => {
                        console.error(error);
                    });
            },

            showDocument() {
                return `${window.app_url}/${this.record.document.url}`;
            },

            async initUpdate() {
                const _self = this;
                _self.reset();
                if (_self.edit_data) {
                    _self.record = _self.edit_data;
                    if (_self.record.start_time_at) {
                        _self.record.start_time_at = _self.formatTime(_self.edit_data.start_time_at, 'hh:mm a');
                    } else {
                        _self.record.start_time_at = '';
                    }
                    if (_self.record.end_time_at) {
                        _self.record.end_time_at = _self.formatTime(_self.edit_data.end_time_at, 'hh:mm a');
                    } else {
                        _self.record.end_time_at = '';
                    }
                }
            },
		},
		async mounted() {
            const _self = this;
            await _self.getStaffs();
            await _self.initUpdate();
		},
	};
</script>
