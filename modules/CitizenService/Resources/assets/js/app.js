/**
*--------------------------------------------------------------------------
* App Scripts
*--------------------------------------------------------------------------
*
* Scripts del Modulo de Nomina a compilar por la aplicación
*/

/**
 * Componente para listar, crear, actualizar y borrar datos de tipos de solicitudes
 *
 * @author
 */
Vue.component('citizenservice-request-types', () => import(
    /* webpackChunkName: "citizenservice-request-types" */
    './components/settings/CitizenServiceRequestTypesComponent.vue')
);

Vue.component('citizenservice-procedure-types', () => import(
    /* webpackChunkName: "citizenservice-procedure-types" */
    './components/settings/CitizenServiceProcedureTypeComponent.vue')
);

Vue.component('citizenservice-procedures', () => import(
    /* webpackChunkName: "citizenservice-procedures" */
    './components/settings/CitizenServiceProcedureComponent.vue')
);

Vue.component('citizenservice-communities', () => import(
    /* webpackChunkName: "citizenservice-communities" */
    './components/settings/CitizenServiceCommunityComponent.vue')
);

Vue.component('citizenservice-institutions', () => import(
    /* webpackChunkName: "citizenservice-institutions" */
    './components/settings/CitizenServiceServedInstitutionComponent.vue')
);

Vue.component('citizenservice-request-create', () => import(
    /* webpackChunkName: "citizenservice-request-create" */
    './components/requests/CitizenServiceRequestCreateComponent.vue')
);

Vue.component('citizenservice-request-list', () => import(
    /* webpackChunkName: "citizenservice-request-list" */
    './components/requests/CitizenServiceRequestListComponent.vue')
);

Vue.component('citizenservice-request-info', () => import(
    /* webpackChunkName: "citizenservice-request-info" */
    './components/requests/CitizenServiceRequestInfoComponent.vue')
);

Vue.component('citizenservice-request-pending', () => import(
    /* webpackChunkName: "citizenservice-request-pending" */
    './components/requests/CitizenServiceRequestPendingComponent.vue')
);

Vue.component('citizenservice-request-list-closing', () => import(
    /* webpackChunkName: "citizenservice-request-list-closing" */
    './components/requests/CitizenServiceRequestListClosingComponent.vue')
);

Vue.component('citizenservice-request-close', () => import(
    /* webpackChunkName: "citizenservice-request-close" */
    './components/requests/CitizenServiceRequestCloseComponent.vue')
);

Vue.component('citizenservice-report-create', () => import(
    /* webpackChunkName: "citizenservice-report-create" */
    './components/reports/CitizenServiceReportCreateComponent.vue')
);

Vue.component('citizenservice-register-create', () => import(
    /* webpackChunkName: "citizenservice-register-create" */
    './components/registers/CitizenServiceRegisterCreateComponent.vue')
);

Vue.component('citizenservice-register-list', () => import(
    /* webpackChunkName: "citizenservice-register-list" */
    './components/registers/CitizenServiceRegisterListComponent.vue')
);

Vue.component('citizenservice-register-info', () => import(
    /* webpackChunkName: "citizenservice-register-info" */
    './components/registers/CitizenServiceRegisterInfoComponent.vue')
);

Vue.component('citizenservice-departments', () => import(
    /* webpackChunkName: "citizenservice-departments" */
    './components/settings/CitizenServiceDepartmentsComponent.vue')
);

Vue.component('citizenservice-effect-types', () => import(
    /* webpackChunkName: "citizenservice-effect-types" */
    './components/settings/CitizenServiceEffectTypeComponent.vue')
);

Vue.component('citizenservice-indicators', () => import(
    /* webpackChunkName: "citizenservice-indicators" */
    './components/settings/CitizenServiceIndicatorComponent.vue')
);
Vue.component('citizenservice-add-indicators', () => import(
    /* webpackChunkName: "citizenservice-add-indicators" */
    './components/requests/CitizenServiceRequestAddIndicatorComponent.vue')
);

Vue.component('citizenservice-request-team-modal', () => import(
    /* webpackChunkName: "citizenservice-request-team-modal" */
    './components/requests/CitizenServiceRequestTeamModalComponent.vue')
);

Vue.component('citizenservice-transaction-type', () => import(
    /* webpackChunkName: "citizenservice-transaction-type" */
    './components/settings/CitizenServiceTransactionTypeComponent.vue')
);

Vue.component('citizenservice-contact-form', () => import(
    /* webpackChunkName: "citizenservice-contact-form" */
    './components/contacts/CitizenServiceContactFormComponent.vue')
);

Vue.component('citizenservice-contact-list', () => import(
    /* webpackChunkName: "citizenservice-contact-list" */
    './components/contacts/CitizenServiceContactListComponent.vue')
);

Vue.component('citizenservice-community-profiling-form', () => import(
    /* webpackChunkName: "citizenservice-community-profiling-form" */
    './components/communities/profilings/CitizenServiceCommunityProfilingFormComponent.vue')
);

Vue.component('citizenservice-community-profiling-list', () => import(
    /* webpackChunkName: "citizenservice-community-profiling-list" */
    './components/communities/profilings/CitizenServiceCommunityProfilingListComponent.vue')
);

Vue.component('citizenservice-community-profiling-info', () => import(
    /* webpackChunkName: "citizenservice-community-profiling-info" */
    './components/communities/profilings/CitizenServiceCommunityProfilingInfoComponent.vue')
);


/**
 * Opciones de configuración global del módulo de Atención al Ciudadano
 *
 * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
 */
Vue.mixin({
	methods: {

		async getCitizenServiceRequestTypes() {
			this.citizen_service_request_types = [];
			await axios.get(`${window.app_url}/citizenservice/get-request-types`).then(response => {
				this.citizen_service_request_types = response.data;
			});
		},
		getCitizenServiceDepartments() {
			this.citizen_service_departments = [];
			axios.get(`${window.app_url}/citizenservice/get-departments`).then(response => {
				this.citizen_service_departments = response.data;
			});
		},
        getCitizenServiceIndicators() {
            this.citizen_service_indicators = [];
            axios.get(`${window.app_url}/citizenservice/get-indicators`).then(response => {
                this.citizen_service_indicators = response.data;
            });
        },/*
        getCitizenServiceTransactionTypes() {
            this.citizen_service_transaction_types = [];
            axios.get(`${window.app_url}/citizenservice/get-transaction-types`).then(response => {
                this.citizen_service_transaction_types  = response.data;
            });
        },*/
        /**
         * Obtiene los Estados del Pais seleccionado
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        async getEstates() {
            const vm = this;
            vm.estates = [
                { id: '', text: 'Seleccione...' }
            ];
            if (vm.record.country_id) {
                await axios.get(`${window.app_url}/get-estates/${vm.record.country_id}`).then(response => {
                    vm.estates = response.data;
                });
                if (vm.record.id) {
                    vm.record.estate_id = vm.record.parish.municipality.estate_id;
                }
            }
        },
         /**
         * Obtiene las ciudades del Estado seleccionado
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         * @author Ing. Yennifer RRamirez <yramirez@cenditel.gob.ve>
         */
        async getCities() {
            const vm = this;
            vm.cities = [
                { id: '', text: 'Seleccione...' }
            ];
            if (vm.record.estate_id) {
                await axios.get(`${window.app_url}/get-cities/${vm.record.estate_id}`).then(response => {
                    vm.cities = response.data;
                });
                if (vm.record.id) {
                    vm.record.city_id = vm.record.city.id;
                }
            }
        },
        /**
         * Obtiene los Municipios del Estado seleccionado
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        async getMunicipalities(e) {
            const vm = this;
            vm.municipalities = [
                { id: '', text: 'Seleccione...' }
            ];
            if (vm.record.estate_id) {
                await axios.get(`${window.app_url}/get-municipalities/${vm.record.estate_id}`).then(response => {
                    vm.municipalities = response.data;
                });
                if (vm.record.id) {
                    vm.record.municipality_id = vm.record.parish.municipality_id;
                }
            }
        },
        /**
         * Obtiene las parroquias del municipio seleccionado
         *
         * @author William Páez <wpaez@cenditel.gob.ve>
         */
        async getParishes() {
            const vm = this;
            vm.parishes = [
                { id: '', text: 'Seleccione...' }
            ];
            if (vm.record.municipality_id) {
                await axios.get(`${window.app_url}/get-parishes/${vm.record.municipality_id}`).then(response => {
                    vm.parishes = response.data;
                });
                if (vm.record.id) {
                    vm.record.parish_id = vm.record.parish.id;
                }
            }
        },
        /**
         * Método que establece los datos del registro seleccionado para el cual se desea mostrar detalles
         *
         * @method    setDetails
         *
         * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve>
         *
         * @param     string   ref       Identificador del componente
         * @param     integer  id        Identificador del registro seleccionado
         */
        setDetails(ref, id) {
            const vm = this;
            vm.$refs[ref].record = vm.$refs.tableResults.data.filter(r => {
                return r.id === id;
            })[0];
        },

        /**
         * Obtiene los tipos de procedimientos
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        getProcedureTypes() {
            this.procedureTypes = [
                { id: '', text: 'Seleccione...' }
            ];
            axios.get(`${window.app_url}/citizenservice/procedure-types`).then(response => {
                const records = response.data.records.map(procedureType => {
                    return {
                        id: procedureType.id,
                        text: procedureType.name,
                    };
                }) || {};
                this.procedureTypes = [
                    ...this.procedureTypes,
                    ...records
                ];
            });
        },
        /**
         * Obtiene los procedimientos
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        async getProcedures(requestTypeId = null) {
            this.procedures = [
                { id: '', text: 'Seleccione...' }
            ];
            await axios.get(`${window.app_url}/citizenservice/procedures`).then(response => {
                const records = response.data.records.filter(
                    procedure => !requestTypeId || procedure.citizen_service_procedure_type_id === requestTypeId
                ).map(procedure => {
                    return {
                        id: procedure.id,
                        text: procedure.name,
                    };
                }) || {};

                this.procedures = [
                    ...this.procedures,
                    ...records
                ];
            });
        },
        async getCommunities() {
            const _self = this;
            _self.communities = [
                { id: '', text: 'Seleccione...' }
            ];
            await axios.get(`${window.app_url}/citizenservice/communities`).then(response => {
                const records = response.data.records.map(community => {
                    return {
                        id: community.id,
                        text: community.name,
                        population: community.population,
                        location: {
                            parish: community.parish.name,
                            municipality: community.parish.municipality.name,
                            estate: community.parish.municipality.estate.name,
                            country: community.parish.municipality.estate.country.name,
                            city: community.city.name,
                            additional_location_data: community.location,
                        }
                    };
                }) || {};
                _self.communities = [
                    ..._self.communities,
                    ...records
                ];
            });
        }
	},
});
