<template>
    <section>
        <button
            type="button"
            class="btn btn-warning btn-xs btn-icon btn-action"
            title="Editar registro de asistencia"
            data-toggle="tooltip"
            v-has-tooltip
            @click="addRecord('work_attendance_edit_'+recordId, route_list, $event)"
        >
            <i class="fa fa-edit"></i>
        </button>
        <div
            class="modal fade text-left"
            tabindex="-1"
            :id="'work_attendance_edit_'+recordId"
        >
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <button
                            type="button"
                            class="close"
                            data-dismiss="modal"
                            aria-label="Close"
                        >
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-history ico-2x"></i>
                            Editar información de asistencia
                        </h6>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="day">Día</label>
                                    <div class="row col-12">
                                        <span id="day">{{ getWeekDay(infoData.date_at) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="date_at">Fecha</label>
                                    <div class="row col-12">
                                        <span id="date_at">{{ format_date(infoData.date_at) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="entry_time">Hora de entrada</label>
                                    <div class="row col-12">
                                        <input
                                            id="entryTime"
                                            type="text"
                                            class="form-control input-sm"
                                            placeholder="HH:MM am/pm"
                                            data-toggle="tooltip"
                                            title="Indique la hora de entrada en formato 12 horas (ej. 04:00 pm)"
                                            v-model="record.entry_time"
                                            v-input-mask data-inputmask-regex="^(0[1-9]|1[0-2]):[0-5][0-9] (am|pm)$"
                                            autocomplete="off"
                                        >
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="exit_time">Hora de salida</label>
                                    <div class="row col-12">
                                        <input
                                            id="exitTime"
                                            type="text"
                                            class="form-control input-sm"
                                            placeholder="HH:MM am/pm"
                                            data-toggle="tooltip"
                                            title="Indique la hora de salida en formato 12 horas (ej. 04:00 pm)"
                                            v-model="record.exit_time"
                                            v-input-mask data-inputmask-regex="^(0[1-9]|1[0-2]):[0-5][0-9] (am|pm)$"
                                            autocomplete="off"
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="position">Cargo</label>
                                    <div class="row col-12">
                                        <span id="position">
                                            {{ infoData.payroll_staff.payroll_employment.payrollPosition.name }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="first_name">Nombres y Apellidos</label>
                                    <div class="row col-12">
                                        <span id="first_name" class="mr-1">
                                            {{ infoData.payroll_staff.first_name }}
                                        </span>
                                        <span id="last_name">
                                            {{ infoData.payroll_staff.last_name }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-default btn-sm btn-round btn-modal-close"
                            data-dismiss="modal"
                        >
                            Cerrar
                        </button>
                        <button
                            type="button"
                            @click="updateRecord('work-attendance/history', recordId)"
		        			class="btn btn-primary btn-sm btn-round btn-modal-save"
		        			title="Guardar registro"
                        >
		        			Guardar
		                </button>
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
                    entry_time: '',
                    exit_time: '',
                },
            };
        },
        props: {
            infoData: {
                type: Object,
                required: true,
            },
            recordId: {
                type: String|Number,
                required: false,
                default: '',
            }
        },
        methods: {
            async updateRecord(url, recordId) {
                const _self = this;
                url = `${url}/${recordId}`;
                await axios.post(_self.setUrl(url), _self.record).then((response) => {
                    if (response.data.result) {
                        _self.showMessage('update');
                        const parentIndex = _self.$parent._data.data.findIndex((item => item.id === _self.recordId))
                        _self.$parent._data.data[parentIndex].entry_time = _self.record.entry_time;
                        _self.$parent._data.data[parentIndex].exit_time = _self.record.exit_time;
                        _self.$parent._data.data[parentIndex].work_time_formated = response.data.workTime;
                        $(`#work_attendance_edit_${_self.recordId}`).modal('hide');
                    } else {
                        _self.showMessage(
                            'custom', 'Alerta!', 'warning', 'screen-warning', 'Error al actualizar el registro.'
                        );
                    }
                }).catch((error) => {
                    _self.showMessage(
                        'custom', 'Alerta!', 'warning', 'screen-error', 'Error al actualizar el registro.'
                    );
                });
            }
        },
        mounted() {
            const _self = this;
            $(`#work_attendance_edit_${_self.recordId}`).on('shown.bs.modal', () => {
                _self.record.entry_time = _self.formatTime(_self.infoData.entry_time, 'hh:mm a');
                _self.record.exit_time = _self.formatTime(_self.infoData.exit_time, 'hh:mm a');
            });
        },
    }
</script>