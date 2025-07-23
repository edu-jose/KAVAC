<template>
    <section id="CitizenServiceContactForm">
        <div class="card-body">
        	<form-errors :listErrors="errors"></form-errors>
            <div class="row mb-4">
                <div class="col-md-4" id="helpCitizenServiceContactIdentityCard">
                    <div class="form-group is-required">
                        <label for="identityCard">Cédula</label>
                        <input
                            type="text" id="identityCard"
                            class="form-control input-sm" data-toggle="tooltip"
                            title="Indique la cédula de identidad" v-model="record.identity_card"
                        >
                    </div>
                </div>
                <div class="col-md-4" id="helpCitizenServiceContactName">
                    <div class="form-group is-required">
                        <label for="name">Nombres</label>
                        <input
                            type="text" id="name"
                            class="form-control input-sm" data-toggle="tooltip"
                            title="Indique el nombre del contacto" v-model="record.name"
                        >
                    </div>
                </div>
                <div class="col-md-4" id="helpCitizenServiceContactSurname">
                    <div class="form-group is-required">
                        <label for="surname">Apellidos</label>
                        <input
                            type="text" id="surname"
                            class="form-control input-sm" data-toggle="tooltip"
                            title="Indique el apellido del contacto" v-model="record.surname"
                        >
                    </div>
                </div>
                <div class="col-md-4" id="helpCitizenServiceContactInstitution">
                    <div class="form-group is-required">
                        <label for="institution">Institución</label>
                        <select2
                            id="institution"
                            class="form-control input-sm" data-toggle="tooltip"
                            title="Seleccione la institución"
                            v-model="record.citizen_service_served_institution_id"
                            :options="institutions"
                        ></select2>
                    </div>
                </div>
                <div class="col-md-4" id="helpCitizenServiceContactPosition">
                    <div class="form-group">
                        <label for="position">Cargo</label>
                        <input
                            type="text" id="position"
                            class="form-control input-sm" data-toggle="tooltip"
                            title="Indique el cargo del contacto" v-model="record.position"
                        >
                    </div>
                </div>
                <div class="col-md-4" id="helpCitizenServiceContactDescription">
                    <div class="form-group">
                        <label for="description">Descripción</label>
                        <input
                            type="text" id="description"
                            class="form-control input-sm" data-toggle="tooltip"
                            title="Indique el nombre del contacto" v-model="record.description"
                        >
                    </div>
                </div>
            </div>
            <phones @syncPhones="syncPhones" :initial_data="phones" ref="phonesComponent"></phones>
        </div>
        <div class="card-footer text-right">
        	<button
                type="button" @click="reset()" class="btn btn-default btn-icon btn-round"
				data-toggle="tooltip" v-has-tooltip
                title ="Borrar datos del formulario"
            >
				<i class="fa fa-eraser"></i>
			</button>
        	<button
                type="button" @click="redirect_back(route_list)"
                class="btn btn-warning btn-icon btn-round" data-toggle="tooltip"
                title="Cancelar y regresar"
            >
                <i class="fa fa-ban"></i>
            </button>
			<button
                type="button"  @click="createRecord('citizenservice/contact-books')"
                class="btn btn-success btn-icon btn-round btn-modal-save"
				title="Guardar registro"
            >
				<i class="fa fa-save"></i>
            </button>
        </div>
    </section>
</template>

<script>
    export default {
        data() {
            return {
                record: {
                    identity_card: '',
                    name: '',
                    surname: '',
                    citizen_service_served_institution_id: '',
                    position: '',
                    description: '',
                    phones: [],
                },
                errors: [],
                institutions: [],
                phones: null,
            };
        },
        props: ['initial_data'],
        methods: {
            reset() {
                this.record = {
                    identity_card: '',
                    name: '',
                    surname: '',
                    citizen_service_served_institution_id: '',
                    position: '',
                    description: '',
                    phones: [],
                };
                this.phones = null;
                this.$refs.phonesComponent.phones = [];
                this.errors = [];
            },
            syncPhones(phones) {
                this.record.phones = phones;
            },
            async getInstitutions() {
                const _self = this;
                await axios.get('citizenservice/institutions').then(response => {
                    _self.institutions = [
                        { id: '', text: 'Seleccione...' },
                        ...response.data.records.map(institution => ({
                            id: institution.id,
                            text: institution.name
                        }))
                    ]
                }).catch(error => {
                    _self.errors.push(error);
                });
            },
        },
        async mounted() {
            await this.getInstitutions();
            if (this.initial_data) {
                const initialData = JSON.parse(this.initial_data);
                this.record = initialData;
                this.phones = initialData.phones ? JSON.stringify(initialData.phones) : null;
            }
        }
    }
</script>