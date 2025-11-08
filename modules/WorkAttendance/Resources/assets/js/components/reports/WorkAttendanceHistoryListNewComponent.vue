<template>
    <section v-if="can_new">
        <div
            class="modal fade text-left"
            tabindex="-1"
            id="work_attendance_new"
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
                            Nuevo registro de asistencia
                        </h6>
                    </div>
                    <div class="modal-body">
                        <form-errors :list-errors="errors"></form-errors>
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="date_at">Fecha</label>
                                    <input
                                        type="date" name="date_at" id="date_at"
                                        class="form-control input-sm" v-model="record.date_at"
                                    />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="entry_time">Hora de entrada</label>
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
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="exit_time">Hora de salida</label>
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
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="position">Cargo</label>
                                    <select2
                                        id="positionId" :options="positions"
                                        v-model="record.position_id"
                                        @input="setEmployments();"
                                        title="Seleccione el cargo del personal a consultar"
                                        data-toggle="tooltip"
                                    />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="first_name">Personal</label>
                                    <select2
                                        id="payrollEmploymentId" :options="employments"
                                        v-model="record.payroll_employment_id"
                                        title="Seleccione el personal a consultar"
                                        data-toggle="tooltip"
                                    />
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
                            @click="createRecord('work-attendance/manual-entry')"
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
                    date_at: '',
                    entry_time: '',
                    exit_time: '',
                    position_id: '',
                    payroll_employment_id: '',
                },
                errors: [],
                positions: [],
                employments: [],
            }
        },
        props: {
            can_new: {
                type: Boolean|String,
                required: true,
            },
        },
        methods: {
            reset() {
                this.record = {
                    date_at: '',
                    entry_time: '',
                    exit_time: '',
                    position_id: '',
                    payroll_employment_id: '',
                };
            },
            async createRecord(url) {
                const _self = this;
                url = _self.setUrl(url);

                _self.loading = true;
                let fields = {};

                for (let index in _self.record) {
                    fields[index] = _self.record[index];
                }
                await axios.post(url, fields).then(response => {
                    if (response.data.result) {
                        _self.showMessage(
                            'custom', 'Éxito!', 'success', 'screen-ok', response.data.message
                        );
                        $('#work_attendance_new').modal('hide');
                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    }
                }).catch(error => {
                    _self.errors = [];

                    if (typeof (error.response) != "undefined") {
                        if (error.response.status == 403) {
                            _self.showMessage(
                                'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                            );
                        }
                        for (let index in error.response.data.errors) {
                            if (error.response.data.errors[index]) {
                                _self.errors.push(error.response.data.errors[index][0]);
                            }
                        }
                    }

                });

                _self.loading = false;

            },
        },
        async mounted() {
            const _self = this;
            await _self.getPositions();
            _self.employments = [{id: '', text: 'Seleccione...'}];
            $('#work_attendance_new').on('shown.bs.modal', function () {
                $('.select2').select2();
                _self.reset();
            });
            $('#work_attendance_new').on('hidden.bs.modal', function () {
                _self.reset();
            });
        }
    }
</script>