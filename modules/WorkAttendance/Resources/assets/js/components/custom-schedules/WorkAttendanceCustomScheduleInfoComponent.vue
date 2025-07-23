<template>
    <section>
        <a
            class="btn btn-info btn-xs btn-icon btn-action"
            href="javascript:void(0)"
            title="Ver información"
            data-toggle="tooltip"
            v-has-tooltip
            @click="addRecord('view_request'+customScheduleId, route_list, $event)"
        >
            <i class="fa fa-eye"></i>
        </a>
        <div
            class="modal fade text-left"
            tabindex="-1"
            :id="'view_request'+customScheduleId"
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
                            <i class="icofont icofont-calendar ico-2x"></i>
                            Información de Horario Personalizado
                        </h6>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="start_date_at"><b>Fecha de Inicio</b></label>
                                    <div class="row">
                                        <div class="col-12">
                                            <span id="start_date_at">{{ infoData.start_date_at }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="end_date_at"><b>Fecha de Finalización</b></label>
                                    <div class="row">
                                        <div class="col-12">
                                            <span id="end_date_at">{{ infoData.end_date_at }}</span>
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
                                    <label for="authorized_payroll_staff"><b>Autorizado por</b></label>
                                    <div class="row">
                                        <div class="col-12">
                                            <span id="authorized_payroll_staff">
                                                {{ infoData.authorized_payroll_staff.first_name }}&nbsp;
                                                {{ infoData.authorized_payroll_staff.last_name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="active"><b>Activo</b></label>
                                    <div class="row">
                                        <div class="col-12">
                                            <span id="active">{{ infoData.active ? 'Si' : 'No' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="type"><b>Tipo</b></label>
                                    <div class="row">
                                        <div class="col-12">
                                            <span id="type" v-if="infoData.custom_schedule_type == 'D'">
                                                Docencia
                                            </span>
                                            <span id="type" v-else-if="infoData.custom_schedule_type == 'E'">
                                                Estudio
                                            </span>
                                            <span id="type" v-else-if="infoData.custom_schedule_type == 'O'">
                                                Otro
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="active"><b>Documento</b></label>
                                    <div class="row">
                                        <div class="col-12">
                                            <a
                                                :href="setUrl(infoData.document.url)"
                                                target="_blank"
                                                class="btn btn-primary btn-xs btn-icon btn-action btn-tooltip"
                                                data-toggle="tooltip" title="Ver documento"
                                                v-if="infoData.document?.url"
                                            >
                                                <i class="fa fa-file"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="reason"><b>Motivo</b></label>
                                    <div class="row">
                                        <div class="col-12">
                                            <div id="reason" v-html="infoData.reason"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="schedule"><b>Horario Personalizado</b></label>
                                    <table class="table table-bordered" id="schedule">
                                        <thead>
                                            <tr>
                                                <th class="text-center" style="width:14.285714286;">Lunes</th>
                                                <th class="text-center" style="width:14.285714286;">Martes</th>
                                                <th class="text-center" style="width:14.285714286;">Miércoles</th>
                                                <th class="text-center" style="width:14.285714286;">Jueves</th>
                                                <th class="text-center" style="width:14.285714286;">Viernes</th>
                                                <th class="text-center" style="width:14.285714286;">Sábado</th>
                                                <th class="text-center" style="width:14.285714286;">Domingo</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(schedule, index) in infoData.schedule" :key="index">
                                                <td class="text-center">
                                                    <span v-if="schedule.mon_start_at && schedule.mon_end_at">
                                                        {{ schedule.mon_start_at }} - {{ schedule.mon_end_at }}
                                                    </span>
                                                    <span v-else>---</span>
                                                </td>
                                                <td class="text-center">
                                                    <span v-if="schedule.tue_start_at && schedule.tue_end_at">
                                                        {{ schedule.tue_start_at }} - {{ schedule.tue_end_at }}
                                                    </span>
                                                    <span v-else>---</span>
                                                </td>
                                                <td class="text-center">
                                                    <span v-if="schedule.wed_start_at && schedule.wed_end_at">
                                                        {{ schedule.wed_start_at }} - {{ schedule.wed_end_at }}
                                                    </span>
                                                    <span v-else>---</span>
                                                </td>
                                                <td class="text-center">
                                                    <span v-if="schedule.thu_start_at && schedule.thu_end_at">
                                                        {{ schedule.thu_start_at }} - {{ schedule.thu_end_at }}
                                                    </span>
                                                    <span v-else>---</span>
                                                </td>
                                                <td class="text-center">
                                                    <span v-if="schedule.fri_start_at && schedule.fri_end_at">
                                                        {{ schedule.fri_start_at }} - {{ schedule.fri_end_at }}
                                                    </span>
                                                    <span v-else>---</span>
                                                </td>
                                                <td class="text-center">
                                                    <span v-if="schedule.sat_start_at && schedule.sat_end_at">
                                                        {{ schedule.sat_start_at }} - {{ schedule.sat_end_at }}
                                                    </span>
                                                    <span v-else>---</span>
                                                </td>
                                                <td class="text-center">
                                                    <span v-if="schedule.sun_start_at && schedule.sun_end_at">
                                                        {{ schedule.sun_start_at }} - {{ schedule.sun_end_at }}
                                                    </span>
                                                    <span v-else>---</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
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
            return {}
        },
        props: {
            infoData: {
                type: Object,
                required: true,
            },
            customScheduleId: {
                type: String|Number,
                required: false,
                default: '',
            }
        },
    }
</script>