<template>
    <section id="PayrollStaffForm">
        <div class="card-body">
            <div class="alert alert-danger" v-if="errors.length > 0">
                <div class="container">
                    <div class="alert-icon">
                        <i class="now-ui-icons objects_support-17"></i>
                    </div>
                    <strong>Cuidado!</strong>
                    Debe verificar los siguientes errores antes de continuar:
                    <button
                        type="button" class="close"
                        data-dismiss="alert"
                        aria-label="Close"
                        @click.prevent="errors = []"
                    >
                        <span aria-hidden="true">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </span>
                    </button>
                    <ul>
                        <li v-for="error in errors" :key="error">{{ error }}</li>
                    </ul>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3" id="helpStaffName">
                    <div class="form-group is-required">
                        <label for="staffName">Nombres</label>
                        <input
                            id="staffName"
                            type="text"
                            class="form-control input-sm"
                            v-model="record.first_name"
                        />
                        <input type="hidden" v-model="record.id" v-is-text>
                    </div>
                </div>
                <div class="col-md-3" id="helpStaffLastName">
                    <div class="form-group is-required">
                        <label for="staffLastName">Apellidos</label>
                        <input
                            id="staffLastName"
                            type="text"
                            class="form-control input-sm"
                            v-model="record.last_name"
                            v-is-text
                        />
                    </div>
                </div>
                <div class="col-md-3" id="helpStaffIdNumber">
                    <div class="form-group is-required">
                        <label for="staffIdNumber">Cédula de Identidad</label>
                        <input
                            id="staffIdNumber"
                            type="text"
                            class="form-control input-sm"
                            v-model="record.id_number"
                            v-input-mask
                            data-inputmask="'mask': '9{6,8}'"
                        />
                    </div>
                </div>
                <div class="col-md-3" id="helpStaffRif">
                    <div class="form-group is-required">
                        <label for="staffRif">Rif</label>
                        <input
                            id="staffRif"
                            type="text"
                            class="form-control input-sm"
                            v-model="record.rif"
                            maxlength="10"
                        />
                    </div>
                </div>
                <div class="col-md-3" id="helpStaffPassport">
                    <div class="form-group">
                        <label for="staffPassport">Pasaporte</label>
                        <input
                            id="staffPassport"
                            type="text"
                            class="form-control input-sm"
                            v-model="record.passport"
                            v-is-digits
                            maxlength="20"
                        />
                    </div>
                </div>
                <div class="col-md-3" id="helpStaffNationality">
                    <div class="form-group is-required">
                        <label for="staffNationality">Nacionalidad</label>
                        <select2
                            id="staffNationality"
                            :options="payroll_nationalities"
                            v-model="record.payroll_nationality_id"
                        ></select2>
                    </div>
                </div>
                <div class="col-md-3" id="helpStaffBirthDate">
                    <div class="form-group is-required">
                        <label for="staffBirthDate">Fecha de Nacimiento</label>
                        <input
                            id="staffBirthDate"
                            type="date"
                            class="form-control input-sm"
                            v-model="record.birthdate"
                            @input="setAgeGroup()"
                        />
                    </div>
                </div>
                <div class="col-md-3" id="helpStaffEmail">
                    <div class="form-group">
                        <label for="staffEmail">Correo Electrónico</label>
                        <input
                            id="staffEmail"
                            type="email"
                            class="form-control input-sm"
                            v-model="record.email"
                        />
                    </div>
                </div>
                <div
                    class="col-md-3"
                    id="helpStaffGender"
                    v-if="genders.length > 0"
                >
                    <div class="form-group is-required">
                        <label for="staffGender">Género</label>
                        <select2
                            id="staffGender"
                            :options="genders"
                            v-model="record.payroll_gender_id"
                        ></select2>
                    </div>
                </div>
                <div class="col-md-3" id="helpStaffEmergencyContact">
                    <div class="form-group">
                        <label for="staffEmergencyContact">
                            Nombres y Apellidos de la Persona de Contacto
                        </label>
                        <input
                            id="staffEmergencyContact"
                            type="text"
                            class="form-control input-sm"
                            v-model="record.emergency_contact"
                            v-is-text
                        />
                    </div>
                </div>
                <div class="col-md-3" id="helpStaffEmergencyContactPhone">
                    <div class="form-group">
                        <label for="staffEmergencyContactPhone">Teléfono de la Persona de Contacto</label>
                        <input
                            id="staffEmergencyContactPhone"
                            type="text"
                            class="form-control input-sm"
                            placeholder="+00-000-0000000"
                            v-model="record.emergency_phone"
                            v-input-mask
                            data-inputmask="'mask': '+99-999-9999999'"
                        />
                    </div>
                </div>
                <div
                    class="col-md-3"
                    id="helpStaffBloodType"
                    v-if="payroll_blood_types.length > 0"
                >
                    <div class="form-group">
                        <label for="staffBloodType">Tipo de Sangre</label>
                        <select2
                            id="staffBloodType"
                            :options="payroll_blood_types"
                            v-model="record.payroll_blood_type_id"
                        >
                        </select2>
                    </div>
                </div>
                <div class="col-md-3" id="helpStaffSocialSecurity">
                    <div class="form-group">
                        <label for="staffSocialSecurity">Seguro Social</label>
                        <input
                            id="staffSocialSecurity"
                            type="text"
                            class="form-control input-sm"
                            v-model="record.social_security"
                            title="Indique el número de seguro social"
                        />
                    </div>
                </div>
                <div
                    class="col-md-3"
                    id="helpAgeGroup"
                    v-if="record.payroll_age_group_id != '' && record.birthdate != ''"
                >
                    <div class="form-group">
                        <label for="staffAgeGroup">Grupo Etario</label>
                        <br>
                        <span>{{ record.age_group }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group" id="helpStaffDisability">
                        <label for="has_disability">¿Posee una Discapacidad?</label>
                        <div class="col-md-12">
                            <div
                                class="custom-control custom-switch"
                                data-toggle="tooltip"
                                title="
                                    Indique si el trabajador posee una discapacidad o no
                                "
                            >
                                <input
                                    type="checkbox"
                                    class="
                                        custom-control-input sel_has_disability
                                    "
                                    id="has_disability"
                                    v-model="record.has_disability"
                                    :value="true"
                                    name="has_disability"
                                >
                                <label
                                    class="custom-control-label"
                                    for="has_disability"
                                >&nbsp;</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    class="col-md-3"
                    id="helpStaffDisabilityName"
                    v-if="
                        record.has_disability && payroll_disabilities.length > 0
                    "
                >
                    <div class="form-group is-required">
                        <label for="staffDisability">Discapacidad</label>
                        <select2
                            id="staffDisability"
                            :options="payroll_disabilities"
                            v-model="record.payroll_disability_id"
                        >
                        </select2>
                    </div>
                </div>
                <div
                    class="col-md-3"
                    id="helpStaffDiverLicense"
                >
                    <div class="form-group">
                        <label for="has_driver_license">¿Posee Licencia de Conducir?</label>
                        <div class="col-md-12">
                            <div
                                class="custom-control custom-switch"
                                data-toggle="tooltip"
                                title="
                                    Indique si el trabajador posee licencia de conducir o no
                                "
                            >
                                <input
                                    type="checkbox"
                                    class="custom-control-input sel_has_driver_license"
                                    id="has_driver_license"
                                    v-model="record.has_driver_license"
                                    :value="true"
                                    name="has_driver_license"
                                >
                                <label
                                    class="custom-control-label"
                                    for="has_driver_license"
                                >&nbsp;</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    class="col-md-3"
                    id="helpStaffLicenseDegree"
                    v-if="
                        record.has_driver_license
                        && payroll_license_degrees.length > 0
                    "
                >
                    <div class="form-group is-required">
                        <label for="staffLicenseDegree">Grado de Licencia de Conducir</label>
                        <select2
                            id="staffLicenseDegree"
                            :options="payroll_license_degrees"
                            v-model="record.payroll_license_degree_id"
                        >
                        </select2>
                    </div>
                </div>
                <div
                    class="col-md-3"
                    id="helpStaffDiverLicense"
                >
                    <div class="form-group">
                        <label for="has_died">¿Fallecido?</label>
                        <div class="col-md-12">
                            <div
                                class="custom-control custom-switch"
                                data-toggle="tooltip"
                                title="
                                    Indique si el trabajador ya fallecio
                                "
                            >
                                <input
                                    type="checkbox"
                                    class="custom-control-input sel_has_died"
                                    id="has_died"
                                    v-model="record.has_died"
                                    :value="false"
                                    name="has_died"
                                >
                                <label
                                    class="custom-control-label"
                                    for="has_died"
                                >&nbsp;</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div>
                <h6 class="card-title">Dirección de Habitación</h6>
            </div>
            <div class="row">
                <div
                    class="col-md-3"
                    id="helpStaffCountry"
                    v-if="countries.length > 0"
                >
                    <div class="form-group is-required">
                        <label for="staffCountry">País</label>
                        <select2
                            id="staffCountry"
                            :options="countries"
                            @input="getEstates()"
                            v-model="record.country_id"
                        ></select2>
                    </div>
                </div>
                <div class="col-md-3" id="helpStaffState">
                    <div class="form-group is-required">
                        <label for="staffState">Estado</label>
                        <select2
                            id="staffState"
                            :options="estates"
                            @input="getMunicipalities(), getRegions()"
                            v-model="record.estate_id"
                        ></select2>
                    </div>
                </div>
                <div class="col-md-3" id="helpStaffRegion">
                    <div class="form-group">
                        <label for="staffRegion">Región</label>
                        <select2
                            id="staffRegion"
                            :options="regions"
                            v-model="record.region_id"
                        ></select2>
                    </div>
                </div>
                <div class="col-md-3" id="helpStaffMunicipality">
                    <div class="form-group is-required">
                        <label for="staffMunicipality">Municipio</label>
                        <select2
                            id="staffMunicipality"
                            :options="municipalities"
                            @input="getParishes()"
                            v-model="record.municipality_id"
                        ></select2>
                    </div>
                </div>
                <div class="col-md-3" id="helpStaffParish">
                    <div class="form-group is-required">
                        <label for="staffParish">Parroquia</label>
                        <select2
                            id="staffParish"
                            :options="parishes"
                            @input="getLocalities()"
                            v-model="record.parish_id"
                        ></select2>
                    </div>
                </div>
                <div class="col-md-3" id="helpStaffLocality">
                    <div class="form-group">
                        <label for="staffLocality">Localidad</label>
                        <select2
                            id="staffLocality"
                            :options="localities"
                            v-model="record.locality_id"
                        ></select2>
                    </div>
                </div>
                <div class="col-md-6" id="helpStaffAddress">
                    <div class="form-group">
                        <label for="staffAddress">Dirección</label>
                        <input
                            id="staffAddress"
                            type="text"
                            class="form-control input-sm"
                            v-model="record.address"
                        />
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-12" id="helpStaffMedicalHistory">
                    <div class="form-group">
                        <h6 class="card-title">Historial Médico</h6>
                        <ckeditor
                            :editor="ckeditor.editor"
                            id="medical_history"
                            data-toggle="tooltip"
                            title="Indique el historial médico"
                            :config="ckeditor.editorConfig"
                            class="form-control"
                            tag-name="textarea"
                            rows="2"
                            v-model="record.medical_history"
                        >
                        </ckeditor>
                    </div>
                </div>
            </div>

            <hr>
            <h6
                class="card-title"
                id="helpStaffUniformSize"
            >
                Talla de uniforme
                <i
                    class="fa fa-plus-circle cursor-pointer"
                    @click="addUniformSize()"
                ></i>
            </h6>
            <div class="row" v-for="(uniform, u) in record.uniform_sizes" :key="u">
                <div class="col-md-3">
                    <div class="form-group is-required">
                        <label for="uniform_name">Nombre:</label>
                        <input
                            type="text"
                            id="uniform_name"
                            class="form-control input-sm"
                            data-toggle="tooltip"
                            title="Requerimiento del solicitante"
                            v-model="uniform.name"
                        >
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group is-required">
                        <label for="uniform_name">Talla:</label>
                        <input
                            type="text"
                            id="uniform_name"
                            class="form-control input-sm"
                            data-toggle="tooltip"
                            title="Requerimiento del solicitante"
                            v-model="uniform.size"
                        >
                    </div>
                </div>
                <div class="col-1">
                    <div class="form-group">
                        <button
                            class="mt-4 btn btn-sm btn-danger btn-action"
                            type="button"
                            @click="removeRow(u, record.uniform_sizes)"
                            title="Eliminar este dato" data-toggle="tooltip"
                        >
                            <i class="fa fa-minus-circle"></i>
                        </button>
                    </div>
                </div>
            </div>
            <hr>
            <h6 class="card-title" id="helpStaffPhone">
                Números Telefónicos
                <i class="fa fa-plus-circle cursor-pointer" @click="addPhone"></i>
            </h6>
            <div class="row phone-row" v-for="(phone, i) in record.phones" :key="i">
                <div class="col-3">
                    <div class="form-group is-required">
                        <label for="phone_type">Tipo de número telefónico</label>
                        <select
                            data-toggle="tooltip"
                            v-model="phone.type"
                            class="select2"
                            title="
                                Seleccione el tipo de número telefónico
                            "
                            :data-phone-index="i"
                        >
                            <option value="">Seleccione...</option>
                            <option value="M">Móvil</option>
                            <option value="T">Teléfono</option>
                            <option value="F">Fax</option>
                        </select>
                    </div>
                </div>
                <div class="col-2">
                    <div class="form-group is-required">
                        <label for="area_code">Código de área</label>
                        <input
                            type="text"
                            placeholder="Cod. Area"
                            data-toggle="tooltip"
                            title="Indique el código de área"
                            v-model="phone.area_code"
                            class="form-control input-sm" v-is-digits
                        >
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group is-required">
                        <label for="phone_number">Número telefônico</label>
                        <input
                            type="text"
                            placeholder="Número"
                            data-toggle="tooltip"
                            title="Indique el número telefónico"
                            v-model="phone.number"
                            class="form-control input-sm"
                            v-is-digits
                        >
                    </div>
                </div>
                <div class="col-2">
                    <div class="form-group">
                        <label for="extension">Extensión</label>
                        <input
                            type="text"
                            placeholder="Extensión"
                            data-toggle="tooltip"
                            title="Indique la extención telefónica (opcional)"
                            v-model="phone.extension"
                            class="form-control input-sm"
                            v-is-digits
                        >
                    </div>
                </div>
                <div class="col-1">
                    <div class="form-group">
                        <button
                            class="btn btn-sm btn-danger btn-action"
                            type="button"
                            @click="removeRow(i, record.phones)"
                            title="Eliminar este dato"
                            data-toggle="tooltip"
                        >
                            <i class="fa fa-minus-circle"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer text-right" id="helpParamButtons">
            <button
                class="btn btn-default btn-icon btn-round"
                data-toggle="tooltip" type="button"
                title="Borrar datos del formulario"
                @click="reset()"
            >
                <i class="fa fa-eraser"></i>
            </button>
            <button
                type="button"
                class="btn btn-warning btn-icon btn-round"
                data-toggle="tooltip"
                title="Cancelar y regresar"
                @click="redirect_back(route_list)"
            >
                <i class="fa fa-ban"></i>
            </button>
            <button
                type="button"
                @click="createRecord('payroll/staffs')"
                class="btn btn-success btn-icon btn-round"
                data-toggle="tooltip"
                title="Guardar registro"
            >
                <i class="fa fa-save"></i>
            </button>
        </div>
    </section>
</template>

<script>
    export default {
        props: {
            payroll_staff_id: Number,
        },
        data() {
            return {
                record: {
                    id: '',
                    first_name: '',
                    last_name: '',
                    payroll_nationality_id: '',
                    id_number: '',
                    passport: '',
                    rif: '',
                    email: '',
                    birthdate: '',
                    payroll_gender_id: '',
                    has_disability: '',
                    payroll_disability_id: '',
                    payroll_blood_type_id: '',
                    social_security: '',
                    has_driver_license: '',
                    payroll_license_degree_id: '',
                    payroll_age_group_id: '',
                    emergency_contact: '',
                    emergency_phone: '',
                    country_id: '',
                    estate_id: '',
                    municipality_id: '',
                    parish_id: '',
                    region_id: '',
                    locality_id: '',
                    address: '',
                    medical_history: '',
                    uniform_sizes: [],
                    phones: [],
                    age_group: '',
                },
                errors: [],
                payroll_nationalities: [],
                genders: [],
                countries: [],
                estates: [],
                municipalities: [],
                parishes: [],
                regions: [],
                localities: [],
                payroll_license_degrees: [],
                payroll_blood_types: [],
                payroll_disabilities: [],
                payroll_age_groups: [],
            }
        },
        methods: {
            /**
             * Método que borra todos los datos del formulario
             *
             * @author  William Páez <wpaez@cenditel.gob.ve>
             * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
             */
            reset() {
                const vm = this;
                vm.record = {
                    id: '',
                    first_name: '',
                    last_name: '',
                    payroll_nationality_id: '',
                    id_number: '',
                    passport: '',
                    rif: '',
                    email: '',
                    birthdate: '',
                    payroll_gender_id: '',
                    has_disability: '',
                    payroll_disability_id: '',
                    payroll_blood_type_id: '',
                    social_security: '',
                    has_driver_license: '',
                    payroll_license_degree_id: '',
                    emergency_contact: '',
                    emergency_phone: '',
                    country_id: '',
                    estate_id: '',
                    municipality_id: '',
                    parish_id: '',
                    region_id: '',
                    locality_id: '',
                    payroll_age_group_id: '',
                    address: '',
                    medical_history: '',
                    uniform_sizes: [],
                    phones: [],
                    age_group: '',
                };
            },

            async getStaff() {
                let vm = this;
                await axios.get(`${window.app_url}/payroll/staffs/${vm.payroll_staff_id}`).then(response => {
                    let data = response.data.record;
                    vm.record = {
                        id: data.id,
                        first_name: data.first_name,
                        last_name: data.last_name,
                        payroll_nationality_id: data.payroll_nationality_id,
                        id_number: data.id_number,
                        passport: data.passport ? data.passport : '',
                        rif: data.rif ? data.rif : '',
                        email: data.email ? data.email : '',
                        birthdate: data.birthdate,
                        payroll_gender_id: data.payroll_gender_id,
                        has_disability: data.has_disability ? data.has_disability : false,
                        payroll_disability_id: data.payroll_disability_id,
                        payroll_blood_type_id: data.payroll_blood_type_id,
                        social_security: data.social_security,
                        has_driver_license: data.has_driver_license
                            ? data.has_driver_license : false,
                        payroll_license_degree_id: data.payroll_license_degree_id,
                        has_died: data.has_died ? data.has_died : false,
                        emergency_contact: data.emergency_contact
                            ? data.emergency_contact : '',
                        emergency_phone: data.emergency_phone
                            ? data.emergency_phone : '',
                        country_id: data.country_id,
                        estate_id: data.estate_id,
                        municipality_id: data.municipality_id,
                        parish_id: data.parish_id,
                        locality_id: data.locality_id,
                        region_id: data.region_id,
                        payroll_age_group_id: data.payroll_age_group_id,
                        address: data.address,
                        medical_history: data.medical_history
                            ? data.medical_history : '',
                        uniform_sizes: data.payroll_staff_uniform_size
                            ? data.payroll_staff_uniform_size : [],
                        phones: data.phones ? data.phones : [],
                    }

                    vm.record.parish = data.parish;
                    vm.record.region = data.region;
                    vm.record.locality = data.locality;
                    vm.record.country_id = vm.record.parish.municipality.estate.country_id;
                    setTimeout(() => {
                        vm.record.payroll_nationality_id = data.payroll_nationality_id;
                    }, 100);
                });
            },

            /**
             * Obtiene los Estados del Pais seleccionado
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             * @author  William Páez <wpaez@cenditel.gob.ve> | paez.william8@gmail.com
             */
            async getEstates() {
                const vm = this;
                vm.estates = [];
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
             * Obtiene los Municipios del Estado seleccionado
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             * @author  William Páez <wpaez@cenditel.gob.ve> | paez.william8@gmail.com
             */
            async getMunicipalities() {
                const vm = this;
                vm.municipalities = [];
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
             * Obtiene las Parroquias del Municipio seleccionado
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             * @author  William Páez <wpaez@cenditel.gob.ve> | paez.william8@gmail.com
             */
            async getParishes() {
                const vm = this;
                vm.parishes = [];
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
             * Obtiene las Regiones
             *
             * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
             *
             */
             async getRegions() {
                const vm = this;
                vm.regions = [];
                if (vm.record.estate_id) {
                    await axios.get(`${window.app_url}/get-regions/${vm.record.estate_id}`).then((response) => {
                        vm.regions = response.data?.records || [];
                    });
                    if (vm.record.id) {
                        if(vm.record.region){
                            vm.record.region_id = vm.record.region.id;
                        }
                    }
                }
            },

            /**
             * Obtiene las Localidades
             *
             * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
             *
             */
            async getLocalities() {
            const vm = this;
                vm.localities = [];
                if (vm.record.parish_id) {
                    await axios.get(`${window.app_url}/get-localities/${vm.record.parish_id}`).then((response) => {
                        vm.localities = response.data?.records || [];
                    });
                    if (vm.record.id) {
                        vm.record.locality_id = vm.record.locality?.id;
                    }
                }
            },

            /**
             * Agrega una nueva columna para las tallas de uniformes
             *
             * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
             */
            addUniformSize() {
                const vm = this;
                if (vm.record.uniform_sizes.length == 0) {
                    vm.record.uniform_sizes.push({
                        name: 'Camisa',
                        size: '',
                        payroll_staff_id: '',
                    });
                    vm.record.uniform_sizes.push({
                        name: 'Pantalón',
                        size: '',
                        payroll_staff_id: '',
                    });
                    vm.record.uniform_sizes.push({
                        name: 'Calzado',
                        size: '',
                        payroll_staff_id: '',
                    });
                } else {
                    vm.record.uniform_sizes.push({
                        name: '',
                        size: '',
                        payroll_staff_id: '',
                    });
                }
            },

            /**
             * Selecciona el grupo etario al que pertenece de acuerdo a la fecha de nacimiento
             *
             * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
             */
            setAgeGroup() {
                const vm = this;
                let age = vm.setAge(vm.record.birthdate);

                vm.record.payroll_age_group_id = '';
                vm.record.age_group = '';

                vm.payroll_age_groups.forEach((ageGruop) => {
                    if (age >= ageGruop.minimum && age <= ageGruop.maximum) {
                        vm.record.payroll_age_group_id = ageGruop.id;
                        vm.record.age_group = ageGruop.text;
                    }
                })
            },
        },
        async created() {
            this.loading = true;
            await this.getPayrollAgeGroups();
            await this.getPayrollNationalities();
            await this.getGenders();
            await this.getCountries();
            await this.getEstates();
            await this.getMunicipalities();
            await this.getPayrollLicenseDegrees();
            await this.getPayrollBloodTypes();
            await this.getPayrollDisabilities();
            this.loading = false;
        },
        async mounted() {
            const vm = this;
            vm.loading = true;
            if (vm.payroll_staff_id) {
                await vm.getStaff();
                await vm.setAgeGroup();
            } else {
                this.record.has_disability = false;
                this.record.has_driver_license = false;
                this.record.phones = [];
                this.record.uniform_sizes = [];
            }
            vm.loading = false;
        }
    };
</script>
