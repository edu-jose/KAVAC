<template>
	<section id="workAttendanceExternalActivity">

        <div class="card-body">
            <form-errors :listErrors="errors"></form-errors>
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group is-required">
                        <label for="startDateAt">Fecha</label>
                        <input
                            type="date" id="startDateAt"
                            class="form-control input-sm"
                            data-toggle="tooltip"
                            title="Indique la fecha de la actividad"
                            v-model="record.start_date_at">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group is-required">
                        <label for="startTimeAt">Hora de inicio</label>
                        <input
                            id="startTimeAt"
                            type="text"
                            class="form-control input-sm"
                            placeholder="HH:MM am/pm"
                            data-toggle="tooltip"
                            title="Indique la hora de inicio de la actividad en formato 12 horas (ej. 08:00 am)"
                            v-model="record.start_time_at"
                            v-input-mask data-inputmask-regex="^(0[1-9]|1[0-2]):[0-5][0-9] (am|pm)$"
                        >
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group is-required">
                        <label for="endTimeAt">Hora de fin</label>
                        <input
                            id="endTimeAt"
                            type="text"
                            class="form-control input-sm"
                            placeholder="HH:MM am/pm"
                            data-toggle="tooltip"
                            title="Indique la hora de fin de la actividad en formato 12 horas (ej. 04:00 pm)"
                            v-model="record.end_time_at"
                            v-input-mask data-inputmask-regex="^(0[1-9]|1[0-2]):[0-5][0-9] (am|pm)$"
                        >
                    </div>
                </div>
                <div class="col-md-12">
                    <div id="reasonText" class="form-group is-required">
                        <label for="reason">Motivo</label>
                        <ckeditor
                            :editor="ckeditor.editor"
                            id="reason"
                            data-toggle="tooltip"
                            title="Indique el motivo de la actividad"
                            :config="ckeditor.editorConfig"
                            class="form-control"
                            name="reason"
                            tag-name="textarea"
                            rows="3"
                            v-model="record.reason"
                        ></ckeditor>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group form-multiselect is-required">
                        <label for="payrollStaff">Personal</label>
                        <v-multiselect
                            id="payrollStaff"
                            :options="payroll_staffs"
                            track_by="full_name"
                            open-direction="top"
                            :hide_selected="true"
                            :selected="record.payroll_staffs"
                            v-model="record.payroll_staffs"
                        >
                        </v-multiselect>
                    </div>
                </div>
                <div class="col-md-6">
                    <upload-documents
                        inputLabel="Soporte (opcional)"
                        inputTooltip="Seleccione el(los) documento(s) o imagen(es) que avala(n) la actividad"
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

		        	<button type="button"  @click="createRecord('work-attendance/external-activities')"
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
					id: '',
                    start_date_at: '',
                    start_time_at: '',
                    end_date_at: '',
                    end_time_at: '',
                    reason: '',
                    payroll_staffs: [],
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
                    payroll_staffs: [],
				};
                this.errors = [];
			},

            async getStaffs() {
                const _self = this;
                await axios.get(`${window.app_url}/work-attendance/get-staffs`)
                    .then(response => {
                        _self.payroll_staffs = response.data.staffs;
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
                if (_self.edit_data) {
                    _self.record = _self.edit_data;
                    _self.record.payroll_staffs = _self.edit_data.staffs.map(staff => {
                        return {
                            id: staff.payroll_staff.id,
                            full_name: staff.payroll_staff.first_name + ' ' + staff.payroll_staff.last_name
                        };
                    });
                    delete _self.record.staffs;
                }
            }
		},
		async mounted() {
            //
        },

		async created() {
            const _self = this;
            await _self.getStaffs();
            await _self.initUpdate();
		},
	};
</script>
