<template>
    <div>
        <!-- card-body -->
        <div class="card-body">
            <!-- mensajes de error -->
            <form-errors :listErrors="localErrors"></form-errors>
            <!-- mensajes de error -->
            <div class="row">
                <div class="col-6 mt-4">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input sel_pry_acc" id="all_params"
                            name="all_params" v-model="params.all" value="true" />
                        <label class="custom-control-label" for="all_params">
                            Todos
                        </label>
                    </div>
                </div>
                <div class="col-6 mt-4">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input sel_pry_acc" id="status"
                            name="document_status" v-model="isStatus" value="true" @change="change" />
                        <label class="custom-control-label" for="status">
                            Estatus
                        </label>
                    </div>
                    <div class="mt-4">
                        <v-multiselect :options="budgetStatusArray" track_by="text" id="status_id"
                            :hide_selected="false" :selected="params.status_id" v-model="params.status_id"
                            :disabled="params.all || (!isStatus && params.all)" @input="handleCompromisesStatuses">
                        </v-multiselect>
                    </div>
                </div>
                <div class="col-6 mt-4">
                    <div class="custom-control custom-switch">
                        <input type="radio" class="custom-control-input sel_pry_acc" id="project"
                            name="centralized_action" v-model="isProject" :value="1" @change="change" />
                        <label class="custom-control-label" for="project">
                            Proyecto
                        </label>
                    </div>
                    <div class="mt-4">
                        <select2 v-model="params.project_id" :options="budgetProjectsArray" id="project_id"
                            :disabled="params.all || (isProject !== 1 && !params.all)">
                        </select2>
                    </div>
                </div>
                <div class="col-6 mt-4">
                    <div class="custom-control custom-switch">
                        <input type="radio" class="custom-control-input sel_pry_acc" id="centralized_action"
                            name="centralized_action" v-model="isProject" :value="0" @change="change" />
                        <label class="custom-control-label" for="centralized_action">
                            Acción Centralizada
                        </label>
                    </div>
                    <div class="mt-4">
                        <select2 v-model="params.centralized_action_id" :options="budgetCentralizedActionsArray"
                            id="centralized_action_id" :disabled="params.all || (isProject !== 0 && !params.all)">
                        </select2>
                    </div>
                </div>
            </div>
            <div class="col-12 mt-4">
                <label for="all_specific_actions">
                    Seleccionar todas las acciones especificas de este
                    Proyecto / Acción Centralizada
                </label>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="all_specific_actions" value="true"
                        name="all_specific_actions" v-model="all_specific_actions" :disabled="params.all">
                    <label class="custom-control-label" for="all_specific_actions"></label>
                </div>
            </div>
            <div class="col-12" id="allSpecificActions" v-if="!all_specific_actions && !params.all">
                <div class="mt-4">
                    <label for="specific_action_id" class="control-label">
                        Acción Específica
                    </label>
                    <div class="form-group is-required" style="margin-top: -1.5rem">
                        <v-multiselect :options="formulations" track_by="text" :hide_selected="false"
                            :selected="params.formulation_id" v-model="params.formulation_id">
                        </v-multiselect>
                    </div>
                </div>
                <br />
                <hr />
            </div>
            <div class="row">
                <div class="col-6">
                    <div class="form-group is-required mt-3">
                        <label class="control-label">Desde</label>
                        <input v-model="params.start_date" class="form-control input-sm" type="date" />
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group is-required mt-3">
                        <label class="control-label">Hasta</label>
                        <input v-model="params.end_date" class="form-control input-sm" type="date" />
                    </div>
                </div>
            </div>
        </div>
        <!-- card-body -->
        <!-- card-footer -->
        <div class="card-footer text-right">
            <button class="btn btn-primary btn-sm" data-toggle="tooltip" v-has-tooltip title="Generar Reporte"
                @click="getReport('pdf')">
                <span>Generar reporte</span>
                <i class="fa fa-print"></i>
            </button>
            <button class="btn btn-primary btn-sm" @click="getReport('xlsx')" data-toggle="tooltip" v-has-tooltip
                title="Exportar Reporte">
                Exportar Reporte
                <i class="fa fa-file-excel-o"></i>
            </button>
        </div>
        <!-- Final card-footer -->
    </div>
</template>

<script>
import axios from 'axios';
import { locale, relativeTimeThreshold } from 'moment';

export default {
    props: {
        url: {
            type: String,
            required: true,
        },
        pdf: {
            type: String,
            required: true,
        },
        xlsx: {
            type: String,
            required: true,
        },
        specificActionsUrl: {
            type: String,
            required: true,
        },
        // years: {
        //     type: Array,
        //     required: true,
        // },
        errors: {
            type: Array,
            required: true,
        },
        budgetProjects: {
            type: String,
            default: "[]",
        },
        budgetCentralizedActions: {
            type: String,
            default: "[]",
        },
    },
    data() {
        return {
            isProject: '',
            isStatus: false,
            projects: [],
            centralizedActions: [],
            formulations: [],
            all_specific_actions: false,
            localErrors: this.errors,
            params: {
                all: false,
                all_statuses: "",
                year: "",
                start_date: "",
                end_date: "",
                project_id: "",
                centralized_action_id: "",
                formulation_id: "",
                status_id: "",
            },
            budgetProjectsArray: JSON.parse(this.budgetProjects),
            budgetStatusArray: [
                { id: '', text: "Todos" },
                { id: 'PE', text: 'Pendiente' },
                { id: 'AP', text: 'Aprobado' },
                { id: 'CAU', text: 'Causado' },
                { id: 'PA', text: 'Pagado' },
                { id: 'AN', text: 'Anulado' },
            ],
            budgetCentralizedActionsArray: JSON.parse(
                this.budgetCentralizedActions
            ),
            loading: false,
        };
    },
    async created() {
        await this.getFormulations();
        this.all_specific_actions = false;
    },
    watch: {
        "params.project_id": async function (newValue, _) {
            if (newValue === "") {
                this.formulations = [];
                return;
            }
            this.loading = true;
            this.formulations = await this.getFormulations();
            this.loading = false;
        },

        "params.centralized_action_id": async function (newValue, _) {
            if (newValue === "") {
                this.formulations = [];
                return;
            }
            this.loading = true;
            this.formulations = await this.getFormulations();
            this.loading = false;
        },
    },
    computed: {
        isFormulationsDisabled() {
            return this.formulations.length === 0;
        },
    },
    methods: {
        validateParams() {
            let isValid = false;

            if (this.params.start_date === '') {
                this.localErrors.push('El campo Desde es obligatorio');
            }

            if (this.params.end_date === '') {
                this.localErrors.push('El campo Hasta es obligatorio');
            }

            isValid = this.localErrors.length > 0 ? false : true;
            // this.errors = [];

            return isValid
        },
        handleCompromisesStatuses() {
            const vm = this;
            const allStatuses = vm.params.status_id?.some(status => status.id === "");

            if (allStatuses) {
                vm.params.all_statuses = true;
                return;
            }

            vm.params.all_statuses = false;
        },
        async getFormulations() {
            const config = {
                params: {
                    is_project: this.isProject,
                    id: this.isProject
                        ? this.params.project_id
                        : this.params.centralized_action_id,
                },
            };
            const { data } = await axios.get(this.specificActionsUrl, config);
            return data;
        },

        change() {
            if (this.isProject) {
                this.params.centralized_action_id = "";
                this.params.formulation_id = [];
            } else {
                this.params.project_id = "";
                this.params.formulation_id = [];
            }
        },

        async getData() {
            if (
                this.params.formulation_id == null ||
                this.params.formulation_id === ""
            )
                return;
            const config = {
                params: {
                    start_date: this.params.start_date,
                    end_date: this.params.end_date,
                    formulation_id: this.params.formulation_id,
                },
            };
            this.loading = true;
            const { data } = await axios.get(this.url, config);
            this.loading = false;
            this.records = data.data;
        },

        reset() {
            const vm = this;
            vm.isProject = '';
            vm.all_specific_actions = false;
            vm.params = {
                year: "",
                start_date: "",
                end_date: "",
                project_id: "",
                centralized_action_id: "",
                formulation_id: "",
            };
        },

        async getReport(type) {
            let formulationIds = [];
            let compromiseStatusIds = [];

            if (!this.validateParams()) {
                return;
            }

            this.errors = [];

            if (!this.params.all_statuses) {
                for (const status of this.params.status_id) {
                    compromiseStatusIds.push(status.id);
                }
            }

            for (this.params.formulation_id of this.params.formulation_id) {
                formulationIds.push(this.params.formulation_id['id']);
            }

            if (type === "pdf") {
                window.open(
                    `${this.pdf}?formulation_id[]=${formulationIds}` +
                    `&all=${this.params.all}` +
                    `&all_statuses=${this.params.all_statuses}` +
                    `&start_date=${this.params.start_date}` +
                    `&end_date=${this.params.end_date}` +
                    `&all_specific_actions=${this.all_specific_actions}` +
                    `&is_project=${this.isProject}` +
                    `&project_id=${this.params.project_id}` +
                    `&centralized_action_id=${this.params.centralized_action_id}` +
                    `&is_status=${this.isStatus}` +
                    `&status_id[]=${compromiseStatusIds}`
                );
            } else if (type === "xlsx") {
                window.open(
                    `${this.xlsx}?formulation_id[]=${formulationIds}` +
                    `&all=${this.params.all}` +
                    `&all_statuses=${this.params.all_statuses}` +
                    `&start_date=${this.params.start_date}` +
                    `&end_date=${this.params.end_date}` +
                    `&all_specific_actions=${this.all_specific_actions}` +
                    `&is_project=${this.isProject}` +
                    `&project_id=${this.params.project_id}` +
                    `&centralized_action_id=${this.params.centralized_action_id}` +
                    `&is_status=${this.isStatus}` +
                    `&status_id[]=${compromiseStatusIds}`
                );
            }

            this.reset();
        },
    },
};
</script>
