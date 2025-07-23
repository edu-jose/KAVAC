<template>
    <section>
        <a
            class="btn btn-info btn-xs btn-icon btn-action"
            href="javascript:void(0)"
            title="Ver información"
            data-toggle="tooltip"
            v-has-tooltip
            @click="addRecord('view_request'+requestId, route_list, $event)"
        >
            <i class="fa fa-eye"></i>
        </a>
        <div
            class="modal fade text-left"
            tabindex="-1"
            :id="'view_request'+requestId"
        >
            <div class="modal-dialog modal-lg" style="max-width: 60rem">
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
                            <i class="icofont icofont-read-book ico-2x"></i>
                            Información de la Gestión de Trámite
                        </h6>
                    </div>
                    <div class="modal-body">
                        <ul
                            class="nav nav-tabs custom-tabs justify-content-center"
                        >
                            <li class="nav-item">
                                <a
                                    class="nav-link active"
                                    data-toggle="tab"
                                    :href="'#general'+requestId"
                                    :id="'info_general'+requestId"
                                    role="tab"
                                >
                                    <i class="mdi mdi-format-list-checks mr-2"></i>
                                    Datos del Trámite
                                </a>
                            </li>
                            <li class="nav-item">
                                <a
                                    class="nav-link"
                                    data-toggle="tab"
                                    :href="'#contact'+requestId"
                                    role="tab"
                                >
                                    <i class="fa fa-user-o mr-2"></i>
                                    Contacto
                                </a>
                            </li>
                            <li class="nav-item">
                                <a
                                    class="nav-link"
                                    data-toggle="tab"
                                    :href="'#institution'+requestId"
                                    role="tab"
                                >
                                    <i class="fa fa-building-o mr-2"></i>
                                    Institución
                                </a>
                            </li>
                            <li class="nav-item">
                                <a
                                    class="nav-link"
                                    data-toggle="tab"
                                    :href="'#info'+requestId"
                                    role="tab"
                                >
                                    <i class="icofont icofont-copy-alt mr-2"></i>
                                    Archivos
                                </a>
                            </li>
                            <li class="nav-item">
                                <a
                                    class="nav-link"
                                    data-toggle="tab"
                                    :href="'#indicator'+requestId"
                                    role="tab"
                                >
                                    <i class="icofont icofont-chart-line-alt mr-2"></i>
                                    Indicadores
                                </a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div
                                class="tab-pane active"
                                :id="'general'+requestId"
                                role="tabpanel"
                            >
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Fecha</strong>
                                            <div class="row">
                                                <span class="col-md-12 mt-1" :id="'date'+requestId">
                                                    {{ record.date || "N/A" }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Trámite</strong>
                                            <div class="row">
                                                <span class="col-md-12 mt-1" :id="'procedure'+requestId">
                                                    {{ record.procedure ? record.procedure.name : "N/A" }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>¿Tiene reporte en VenAPP?</strong>
                                            <div class="row">
                                                <span class="col-md-12 mt-1" :id="'has_venapp_report'+requestId">
                                                    {{ record.has_venapp_report ? 'Sí' : "No" }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4" v-if="record.has_venapp_report">
                                        <div class="form-group">
                                            <strong>Nro. de reporte en VenApp</strong>
                                            <div class="row">
                                                <span class="col-md-12 mt-1" :id="'venapp_report'+requestId">
                                                    {{ record.venapp_report_number || "N/A" }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Motivo</strong>
                                            <div class="row">
                                                <span class="col-md-12 mt-1" :id="'motive_request'+requestId">
                                                    {{ record.motive_request || "N/A" }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Descripción</strong>
                                            <div class="row">
                                                <span class="col-md-12 mt-1" :id="'attribute'+requestId">
                                                    {{ record.attribute || "N/A" }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Tipo de solicitud</strong>
                                            <div class="row">
                                                <span
                                                    class="col-md-12 mt-1"
                                                    :id="'citizen_service_request_type_id'+requestId"
                                                >
                                                    {{ record.citizen_service_request_type ? record.citizen_service_request_type.name : "N/A" }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Dirección / Departamento</strong>
                                            <div class="row">
                                                <span
                                                    class="col-md-12 mt-1"
                                                    :id="'citizen_service_department_id'+requestId"
                                                >
                                                    {{ record.citizen_service_department ? record.citizen_service_department.name : "N/A" }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Director y/o responsable</strong>
                                            <div class="row">
                                                <span class="col-md-12 mt-1" :id="'director_id'+requestId">
                                                    {{ record.request_director ? record.request_director.first_name + ' ' + record.request_director.last_name : "N/A" }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Tipo de transacción</strong>
                                            <div class="row">
                                                <span class="col-md-12 mt-1" :id="'transaction_type_id'+requestId">
                                                    {{ record.transaction_type ? record.transaction_type.name : "N/A" }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4" v-if="record.document_file">
                                        <div class="form-group">
                                            <strong>Documento</strong>
                                            <div class="row">
                                                <div class="col-12 mt-1">
                                                    <button
                                                        class="btn btn-primary btn-sm"
                                                        @click="downloadDocument"
                                                    >
                                                        Descargar Documento
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" :id="'contact'+requestId" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Nombre</strong>
                                            <div class="row">
                                                <span class="col-md-12 mt-1" :id="'applicant_name'+requestId">
                                                    {{ record.first_name }} {{ record.last_name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Cédula de identidad</strong>
                                            <div class="row">
                                                <span class="col-md-12 mt-1" :id="'id_number'+requestId">
                                                    {{ record.id_number }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Números teléfonicos</strong>
                                            <div class="row" v-if="record.phones && record.phones.length > 0">
                                                <div
                                                    class="col-md-12 mt-1"
                                                    v-for="(phone, index) in record.phones" :key="index"
                                                >
                                                    <span v-if="phone.type == 'T'">Teléfono:</span>
                                                    <span v-else-if="phone.type == 'F'">Fax:</span>
                                                    <span v-else-if="phone.type == 'M'">Móvil:</span>
                                                    <span class="ml-2">
                                                        {{ phone.area_code }} {{ phone.number }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="row" v-else>
                                                <div class="col-12 mt-1">N/A</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Correo</strong>
                                            <div class="row">
                                                <span class="col-md-12 mt-1" :id="'email'+requestId">
                                                    {{ record.email }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Fecha de nacimiento</strong>
                                            <div class="row">
                                                <span class="col-md-12 mt-1" :id="'birth_date'+requestId">
                                                    {{ record.birth_date || 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Edad</strong>
                                            <div class="row">
                                                <span class="col-md-12 mt-1" >
                                                    {{ record.age || 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Género</strong>
                                            <div class="row">
                                                <span class="col-md-12 mt-1" :id="'gender'+requestId">
                                                    {{ record.gender || (record.request_gender && record.request_gender.name) || 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Nacionalidad</strong>
                                            <div class="row">
                                                <span class="col-md-12 mt-1" :id="'nationality'+requestId">
                                                    {{ (record.request_nationality && record.request_nationality.name) || 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>País</strong>
                                            <div class="row">
                                                <span class="col-md-12 mt-1" :id="'country_id'+requestId">
                                                    {{ (record.parish && record.parish.municipality.estate.country.name) || 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Estado</strong>
                                            <div class="row">
                                                <span class="col-md-12 mt-1" :id="'estate_id'+requestId">
                                                    {{ (record.parish && record.parish.municipality.estate.name) || 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Ciudad</strong>
                                            <div class="row">
                                                <span class="col-md-12 mt-1" :id="'city_id'+requestId">
                                                    {{ (record.city && record.city.name) || 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Municipio</strong>
                                            <div class="row">
                                                <span class="col-md-12 mt-1" :id="'municipality_id'+requestId">
                                                    {{ (record.parish && record.parish.municipality.name) || 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Parroquia</strong>
                                            <div class="row">
                                                <span class="col-md-12 mt-1" :id="'parish_id'+requestId">
                                                    {{ (record.parish && record.parish.name) || 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Dirección</strong>
                                            <div class="row">
                                                <span class="col-md-12 mt-1" :id="'address'+requestId">
                                                    {{ record.address || 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Ubicación</strong>
                                            <div class="row">
                                                <span class="col-md-12 mt-1" :id="'location'+requestId">
                                                    {{ record.location || 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Comuna</strong>
                                            <div class="row">
                                                <span class="col-md-12 mt-1" :id="'commune'+requestId">
                                                    {{ record.commune || 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Consejo comunal</strong>
                                            <div class="row">
                                                <span class="col-md-12 mt-1" :id="'communal_council'+requestId">
                                                    {{ record.communal_council || 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Cantidad de habitantes</strong>
                                            <div class="row">
                                                <span class="col-md-12 mt-1" :id="'population_size'+requestId">
                                                    {{ record.population_size || 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="tab-pane"
                                :id="'institution'+requestId"
                                role="tabpanel"
                            >
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <strong>Nombre de la institución</strong>
                                            <div class="row">
                                                <span class="col-md-12 mt-1" :id="'institution_name'+requestId">
                                                    {{ record.institution_name || 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <strong>R.I.F.</strong>
                                            <div class="row">
                                                <span class="col-md-12 mt-1" :id="'rif'+requestId">
                                                    {{ record.rif || 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <strong>Dirección de la institución</strong>
                                            <div class="row">
                                                <span class="col-md-12 mt-1" :id="'institution_address'+requestId">
                                                    {{ record.institution_address || 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <strong>Dirección web</strong>
                                            <div class="row">
                                                <span class="col-md-12 mt-1" :id="'web'+requestId">
                                                    {{ record.web || 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" :id="'info'+requestId" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <strong>Nombre del archivo</strong>
                                            <div class="row">
                                                <span
                                                    class="col-md-12 mt-1" v-for="(document, index) in record.documents"
                                                    :key="index"
                                                >
                                                    <a :href="'documents/'+document.url">{{ document.file }}</a>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <strong>Fecha de verificación</strong>
                                            <div class="row">
                                                <span class="col-md-12 mt-1" :id="'date_verification'+requestId">
                                                    {{ record.date_verification || 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="tab-pane"
                                :id="'indicator'+requestId"
                                role="tabpanel"
                            >
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <strong>Indicador</strong>
                                            <div class="row">
                                                <span
                                                    class="col-md-12 mt-1"
                                                    v-for="(indicator, index) in record.citizen_service_indicator"
                                                    :key="index"
                                                >
                                                    <span class="d-block">{{ indicator.name }}</span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <strong>Nombre</strong>
                                            <div class="row">
                                                <span
                                                    class="col-md-12 mt-1"
                                                    :id="'name'+requestId"
                                                    v-for="(indicator, index) in record.citizen_service_indicator"
                                                    :key="index"
                                                >
                                                    <span class="d-block">{{ indicator.name }}</span>
                                                </span>
                                            </div>
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
            records: [],
            docs: [],
            record: {
                document_file: null, // Inicializa 'document_file' como null o un objeto vacío
            },
            document_file: null,
        };
    },
    props: {
        infoData: {
            type: Object,
            required: true,
        },
        requestId: {
            type: String|Number,
            required: false,
            default: '',
        }
    },
    methods: {
        /**
         * Método que borra todos los datos del formulario
         *
         * @author  Yennifer Ramirez <yramirez@cenditel.gob.ve>
         */
        reset() {},

        downloadDocument() {
            if (this.document_file) {
                const fileUrl = `${window.app_url}/${this.document_file.url}`;
                const link = document.createElement('a');
                link.href = fileUrl;
                link.download = this.document_file.file; // Nombre del archivo
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            } else {
                this.showMessage(
                    "custom",
                    "No hay documento disponible para descargar.",
                    "danger",
                    "screen-error"
                );
            }
        },

    },
    mounted() {
        $(`#view_request${this.requestId}`).on('show.bs.modal', () => {
            this.record = this.infoData;
        });
        $(`#view_request${this.requestId}`).on('hidden.bs.modal', () => {
            this.record = [];
        });
    },
};
</script>
