<template>
    <section>
        <button
            type="button"
            class="btn btn-info btn-xs btn-icon btn-action"
            title="Ver información"
            data-toggle="tooltip"
            v-has-tooltip
            @click="addRecord('view_request'+permissionId, route_list, $event)"
        >
            <i class="fa fa-eye"></i>
        </button>
        <div
            class="modal fade text-left"
            tabindex="-1"
            :id="'view_request'+permissionId"
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
                            <i class="icofont icofont-wall-clock ico-2x"></i>
                            Información de solicitud de permiso
                        </h6>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="start_date_at"><b>Fecha y hora de Inicio</b></label>
                                    <div class="row">
                                        <div class="col-12">
                                            <span id="start_date_at">
                                                <span class="mr-1" id="start_date_at">{{ infoData.start_date_at }}</span>
                                                <span id="start_time_at">{{ formatTime(infoData.start_time_at) }}</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="end_date_at"><b>Fecha y hora Final</b></label>
                                    <div class="row">
                                        <div class="col-12">
                                            <span class="mr-1" id="end_date_at">{{ infoData.end_date_at }}</span>
                                            <span id="end_time_at">{{ formatTime(infoData.end_time_at) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="payroll_staff"><b>Personal</b></label>
                                    <div class="row">
                                        <div class="col-12">
                                            <span id="payroll_staff">
                                                {{ infoData.payroll_staff.first_name }} {{ infoData.payroll_staff.last_name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="estatus">Estatus</label>
                                    <div class="row">
                                        <div class="col-12">
                                            <span
                                                id="status" class="badge badge-warning"
                                                v-if="(record.status && record.status === 'pending') || infoData.status === 'pending'"
                                            >
                                                Pendiente
                                            </span>
                                            <span
                                                id="status" class="badge badge-success"
                                                v-else-if="(record.status && record.status === 'approved') || infoData.status === 'approved'"
                                            >
                                                Aprobado
                                            </span>
                                            <span
                                                id="status" class="badge badge-danger"
                                                v-else-if="(record.status && record.status === 'rejected') || infoData.status === 'rejected'"
                                            >
                                                Rechazado
                                            </span>
                                            <span id="status" class="badge badge-secondary" v-else>
                                                Desconocido
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12" v-if="infoData.status == 'pending'">
                                <div class="form-group">
                                    <label for="approved_or_rejected">Aprobar / Rechazar</label>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="custom-control custom-switch custom-radio">
                                                <input
                                                    type="radio"
                                                    id="approved"
                                                    name="status"
                                                    class="custom-control-input"
                                                    value="approved"
                                                    v-model="record.status"
                                                    @change="changeStatus"
                                                >
                                                <label class="custom-control-label" for="approved">Aprobar</label>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="custom-control custom-switch custom-radio">
                                                <input
                                                    type="radio"
                                                    id="rejected"
                                                    name="status"
                                                    class="custom-control-input"
                                                    value="rejected"
                                                    v-model="record.status"
                                                    @change="changeStatus"
                                                >
                                                <label class="custom-control-label" for="rejected">Rechazar</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="reason">Motivo</label>
                                    <div class="row">
                                        <div class="col-12">
                                            <div id="reason" v-html="infoData.reason"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="comments">Comentarios / Observaciones</label>
                                    <div class="row">
                                        <div class="col-12">
                                            <div id="comments" v-html="infoData.comments"></div>
                                        </div>
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
                    status: '',
                }
            };
        },
        props: {
            infoData: {
                type: Object,
                required: true,
            },
            permissionId: {
                type: String|Number,
                required: false,
                default: '',
            }
        },
        methods: {
            async changeStatus() {
                const _self = this;
                const url = _self.record.status === 'approved' ?
                    'work-attendance/permissions/approve/' + this.permissionId :
                    'work-attendance/permissions/reject/' + this.permissionId;
                await axios.get(_self.setUrl(url)).then((response) => {
                    if (response.data.result) {
                        _self.showMessage('custom', 'Éxito!', 'success', 'screen-ok', 'Estatus actualizado correctamente.');
                        const parentIndex = _self.$parent._data.data.findIndex((item => item.id === _self.permissionId))
                        _self.$parent._data.data[parentIndex].status = _self.record.status;
                    } else {
                        _self.showMessage('custom', 'Alerta!', 'warning', 'screen-warning', 'Error al actualizar el estatus.');
                    }
                }).catch((error) => {
                    _self.showMessage('custom', 'Alerta!', 'warning', 'screen-error', 'Error al actualizar el estatus.');
                });
            },
        }
    }
</script>