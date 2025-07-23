<template>
    <section id="CitizenServiceCommunityProfilingForm">
        <div class="card-body">
        	<form-errors :listErrors="errors"></form-errors>
            <h6 class="card-title">Datos de la Comunidad</h6>
            <div class="row mb-4">
                <div class="col-md-4" id="helpCitizenServiceCommunity">
                    <div class="form-group is-required">
                        <label for="citizenServiceCommunity">Comunidad</label>
                        <select2
                            id="citizenServiceCommunity"
                            class="form-control input-sm" data-toggle="tooltip"
                            title="Seleccione la comunidad"
                            v-model="record.citizen_service_community_id"
                            :options="communities"
                            @input="setCommunityData"
                        ></select2>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group ckeditor-readonly">
                        <label for="population">Población</label>
                        <ckeditor
                            :editor="ckeditor.editor"
                            id="population"
                            data-toggle="tooltip"
                            title="Datos de la población"
                            :config="ckeditorReadOnly.editorConfig"
                            class="form-control"
                            tag-name="textarea"
                            rows="3"
                            v-model="record.population"
                            disabled
                        ></ckeditor>
                    </div>
                </div>
            </div>
            <h6 class="card-title">Datos de la ubicación</h6>
            <div class="row mb-4">
                <div class="col-md-4" id="helpCitizenServiceCommunityCountry">
                    <div class="form-group">
                        <label for="country">Pais</label>
                        <input
                            type="text" id="country" class="form-control input-sm" data-toggle="tooltip"
                            title="País de la comunidad" v-model="record.country"
                            autocomplete="off"
                            disabled
                        >
                    </div>
                </div>
                <div class="col-md-4" id="helpCitizenServiceCommunityEstate">
                    <div class="form-group">
                        <label for="estate">Estado</label>
                        <input
                            type="text" id="estate" class="form-control input-sm" data-toggle="tooltip"
                            title="Estado de la comunidad" v-model="record.estate"
                            disabled
                        >
                    </div>
                </div>
                <div class="col-md-4" id="helpCitizenServiceCommunityCity">
                    <div class="form-group">
                        <label for="city">Ciudad</label>
                        <input
                            type="text" id="city" class="form-control input-sm" data-toggle="tooltip"
                            title="Ciudad de la comunidad" v-model="record.city"
                            disabled
                        >
                    </div>
                </div>
                <div class="col-md-4" id="helpCitizenServiceCommunityMunicipality">
                    <div class="form-group">
                        <label for="municipality">Municipio</label>
                        <input
                            type="text" id="municipality" class="form-control input-sm" data-toggle="tooltip"
                            title="Municipio de la comunidad" v-model="record.municipality"
                            disabled
                        >
                    </div>
                </div>
                <div class="col-md-4" id="helpCitizenServiceCommunityParish">
                    <div class="form-group">
                        <label for="parish">Parroquia</label>
                        <input
                            type="text" id="parish" class="form-control input-sm" data-toggle="tooltip"
                            title="Parroquia de la comunidad" v-model="record.parish"
                            disabled
                        >
                    </div>
                </div>
                <div class="col-12" id="helpCitizenServiceCommunityAdditionalLocationData">
                    <div class="form-group ckeditor-readonly">
                        <label for="additionalLocationData">Datos complementarios de ubicación</label>
                        <ckeditor
                            :editor="ckeditor.editor"
                            id="additionalLocationData"
                            data-toggle="tooltip"
                            title="Datos complementarios de ubicación"
                            :config="ckeditorReadOnly.editorConfig"
                            class="form-control"
                            tag-name="textarea"
                            rows="3"
                            v-model="record.additional_location_data"
                            disabled
                        ></ckeditor>
                    </div>
                </div>
            </div>
            <h6 class="card-title">Datos de la caracterización</h6>
            <div class="row mb-4">
                <div class="col-12" id="helpCitizenServiceCommunityCitizenServiceMainNeeds">
                    <div class="form-group">
                        <label for="mainNeeds">Principales necesidades de la comunidad</label>
                        <ckeditor
                            :editor="ckeditor.editor"
                            id="mainNeeds"
                            data-toggle="tooltip"
                            title="Indique las principales necesidades de la comunidad"
                            :config="ckeditor.editorConfig"
                            class="form-control"
                            tag-name="textarea"
                            rows="3"
                            v-model="record.main_needs"
                        ></ckeditor>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="isCommunalCouncil">¿Es Concejo Comunal?</label>
                        <div class="col-12">
                            <div class="custom-control custom-switch">
                                <input
                                    id="isCommunalCouncil"
                                    type="checkbox"
                                    class="custom-control-input"
                                    :value="true" v-model="record.is_communal_council"
                                >
                                <label class="custom-control-label" for="isCommunalCouncil">&nbsp;</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="isCommune">¿Es Comuna?</label>
                        <div class="col-12">
                            <div class="custom-control custom-switch">
                                <input
                                    id="isCommune"
                                    type="checkbox"
                                    class="custom-control-input"
                                    :value="true" v-model="record.is_commune"
                                >
                                <label class="custom-control-label" for="isCommune">&nbsp;</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="hasTicCommitte">¿Cuenta con comité de ciencia y tecnología?</label>
                        <div class="col-12">
                            <div class="custom-control custom-switch">
                                <input
                                    id="hasTicCommitte"
                                    type="checkbox"
                                    class="custom-control-input"
                                    :value="true" v-model="record.has_tic_committe"
                                >
                                <label class="custom-control-label" for="hasTicCommitte">&nbsp;</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" v-if="record.has_tic_committe">
                    <div class="form-group is-required">
                        <label for="TicCommitteName">Nombre del comité de ciencia y tecnología</label>
                        <input
                            type="text" id="TicCommitteName"
                            class="form-control input-sm" data-toggle="tooltip"
                            title="Indique el nombre del comité de ciencia y tecnología"
                            v-model="record.tic_committe_name"
                        >
                    </div>
                </div>
            </div>
            <div class="row mb-4" v-if="record.is_communal_council">
                <div class="col-12">
                    <h6 class="card-title">
                        Concejos comunales
                        <button
                            type="button"
                            class="btn btn-neutral btn-sm"
                            @click="addCommunalCouncils()"
                            data-toggle="tooltip"
                            title="Agregar concejo comunal"
                        >
                            <i class="fa fa-plus-circle cursor-pointer text-info"></i>
                        </button>
                    </h6>
                </div>
                <div class="col-12">
                    <div
                        class="row"
                        v-for="(communalCouncil, index) in record.communal_councils"
                        :key="index"
                    >
                        <div class="col-1">{{ index + 1 }}.</div>
                        <div class="col-9">
                            <div class="form-group is-required">
                                <label :for="'communalCouncil' + index">Nombre del Concejo Comunal</label>
                                <input
                                    type="text"
                                    :id="'communalCouncil' + index"
                                    class="form-control input-sm" data-toggle="tooltip"
                                    title="Indique el nombre del concejo comunal"
                                    v-model="communalCouncil.name"
                                >
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <button
                                    type="button"
                                    class="btn btn-neutral btn-sm mt-4"
                                    @click="removeCommunalCouncil(index)"
                                    data-toggle="tooltip"
                                    title="Eliminar concejo comunal"
                                >
                                    <i class="fa fa-minus-circle text-danger"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-4" v-if="record.is_commune">
                <div class="col-12">
                    <h6 class="card-title">
                        Comunas
                        <button
                            type="button"
                            class="btn btn-neutral btn-sm"
                            @click="addCommunes()"
                            data-toggle="tooltip"
                            title="Agregar comuna"
                        >
                            <i class="fa fa-plus-circle cursor-pointer text-info"></i>
                        </button>
                    </h6>
                </div>
                <div class="col-12">
                    <div
                        class="row"
                        v-for="(commune, index) in record.communes"
                        :key="index"
                    >
                        <div class="col-1">{{ index + 1 }}.</div>
                        <div class="col-9">
                            <div class="form-group is-required">
                                <label :for="'commune' + index">Nombre de la Comuna</label>
                                <input
                                    type="text"
                                    :id="'commune' + index"
                                    class="form-control input-sm" data-toggle="tooltip"
                                    title="Indique el nombre de la comuna"
                                    v-model="commune.name"
                                >
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <button
                                    type="button"
                                    class="btn btn-neutral btn-sm mt-4"
                                    @click="removeCommune(index)"
                                    data-toggle="tooltip"
                                    title="Eliminar comuna"
                                >
                                    <i class="fa fa-minus-circle text-danger"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="card-title">
                        Instituciones
                        <button
                            type="button"
                            class="btn btn-neutral btn-sm"
                            @click="addInstitutions()"
                            data-toggle="tooltip"
                            title="Agregar Institución"
                        >
                            <i class="fa fa-plus-circle cursor-pointer text-info"></i>
                        </button>
                    </h6>
                </div>
                <div class="col-12">
                    <div
                        class="row"
                        v-for="(institution, index) in record.institutions"
                        :key="index"
                    >
                        <div class="col-1">{{ index + 1 }}.</div>
                        <div class="col-9">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group is-required">
                                        <label :for="'institution' + index">Nombre de la Institución</label>
                                        <input
                                            type="text"
                                            :id="'institution' + index"
                                            class="form-control input-sm" data-toggle="tooltip"
                                            title="Indique el nombre de la institución"
                                            v-model="institution.name"
                                        >
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group is-required">
                                        <label :for="'rif' + index">RIF</label>
                                        <input
                                            type="text"
                                            :id="'rif' + index"
                                            class="form-control input-sm" data-toggle="tooltip"
                                            title="Indique el RIF de la institución"
                                            v-model="institution.rif"
                                        >
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group is-required">
                                        <label :for="'institutionType' + index">Tipo de Institución</label>
                                        <select2
                                            :id="'institutionType' + index"
                                            class="form-control input-sm" data-toggle="tooltip"
                                            title="Seleccione el tipo de institución"
                                            v-model="institution.institution_type_id"
                                            :options="institutionTypes"
                                        ></select2>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group is-required">
                                        <label :for="'institutionSector' + index">Sector de la Institución</label>
                                        <select2
                                            :id="'institutionSector' + index"
                                            class="form-control input-sm" data-toggle="tooltip"
                                            title="Seleccione el sector de la institución"
                                            v-model="institution.institution_sector_id"
                                            :options="institutionSectors"
                                        ></select2>
                                    </div>
                                </div>
                            </div>
                            <image-gallery
                                :name="'images' + index"
                                :id="'images' + index"
                                label="Fotos"
                                cols="col-6 col-lg-4"
                                @syncImages="(images) => syncImages(index, images)"
                                :ref="'images' + index"
                            />
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <button
                                    type="button"
                                    class="btn btn-neutral btn-sm mt-4"
                                    @click="removeInstitution(index)"
                                    data-toggle="tooltip"
                                    title="Eliminar institución"
                                >
                                    <i class="fa fa-minus-circle text-danger"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
                type="button"  @click="validateForm()"
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
                    population: '',
                    country: '',
                    estate: '',
                    city: '',
                    municipality: '',
                    parish: '',
                    additional_location_data: '',
                    main_needs: '',
                    is_communal_council: false,
                    is_commune: false,
                    has_tic_committe: false,
                    tic_committe_name: '',
                    communal_councils: [{name: ''}],
                    communes: [{name: ''}],
                    institutions: [
                        {
                            name: '',
                            rif: '',
                            institution_type_id: '',
                            institution_sector_id: '',
                            images: [],
                        },
                    ],
                    citizen_service_community_id: '',
                },
                ckeditorReadOnly: {
                    editorConfig: {
                        toolbar: [],
                        readOnly: true,
                    },
                },
                errors: [],
                communities: [],
                institutionTypes: [],
                institutionSectors: [],
            };
        },
        props: ['initial_data'],
        methods: {
            syncImages(index, images) {
                this.record.institutions[index].images = images;
            },
            reset() {
                this.errors = [];
                this.record = {
                    population: '',
                    country: '',
                    estate: '',
                    city: '',
                    municipality: '',
                    parish: '',
                    additional_location_data: '',
                    main_needs: '',
                    is_communal_council: false,
                    is_commune: false,
                    has_tic_committe: false,
                    tic_committe_name: '',
                    communal_councils: [{name: ''}],
                    communes: [{name: ''}],
                    institutions: [
                        {
                            name: '',
                            rif: '',
                            institution_type_id: '',
                            institution_sector_id: '',
                            images: [],
                        },
                    ],
                    citizen_service_community_id: '',
                };
            },
            validateForm() {
                const _self = this;
                _self.errors = [];
                if (_self.record.citizen_service_community_id === '') {
                    _self.errors.push('La comunidad es obligatoria');
                }
                if (_self.record.is_communal_council) {
                    _self.record.communal_councils.forEach((communalCouncil, index) => {
                        if (communalCouncil.name === '') {
                            _self.errors.push(`El nombre del concejo comunal ${index + 1} es obligatorio`);
                        }
                    });
                }
                if (_self.record.is_commune) {
                    _self.record.communes.forEach((commune, index) => {
                        if (commune.name === '') {
                            _self.errors.push(`El nombre de la comuna ${index + 1} es obligatorio`);
                        }
                    });
                }
                if (_self.record.has_tic_committe && _self.record.tic_committe_name === '') {
                    _self.errors.push('El nombre del comité de ciencia y tecnología es obligatorio');
                }
                if (_self.record.institutions.length === 0) {
                    _self.errors.push('Los datos de las instituciones son obligatorios');
                } else {
                    _self.record.institutions.forEach((institution, index) => {
                        if (institution.name === '') {
                            _self.errors.push(`El nombre de la institución ${index + 1} es obligatorio`);
                        }
                        if (institution.rif === '') {
                            _self.errors.push(`El rif de la institución ${index + 1} es obligatorio`);
                        }
                        if (institution.institution_type_id === '') {
                            _self.errors.push(`El tipo de la institución ${index + 1} es obligatorio`);
                        }
                        if (institution.institution_sector_id === '') {
                            _self.errors.push(`El sector de la institución ${index + 1} es obligatorio`);
                        }
                    });
                }
                if (!_self.record.main_needs) {
                    _self.errors.push('Las principales necesidades de la comunidad son obligatorias');
                }
                if (_self.errors.length > 0) {
                    return;
                }
                _self.createRecord('citizenservice/community-profilings');
            },
            async createRecord(route) {
                const vm = this;
                const formData = new FormData();

                // Agregar campos básicos
                formData.append('population', vm.record.population);
                formData.append('country', vm.record.country);
                formData.append('estate', vm.record.estate);
                formData.append('city', vm.record.city);
                formData.append('municipality', vm.record.municipality);
                formData.append('parish', vm.record.parish);
                formData.append('additional_location_data', vm.record.additional_location_data);
                formData.append('main_needs', vm.record.main_needs);
                formData.append('is_communal_council', vm.record.is_communal_council);
                formData.append('is_commune', vm.record.is_commune);
                formData.append('has_tic_committe', vm.record.has_tic_committe);
                formData.append('tic_committe_name', vm.record.tic_committe_name);
                formData.append('citizen_service_community_id', vm.record.citizen_service_community_id);

                // Agregar concejos comunales
                vm.record.communal_councils.forEach((council, index) => {
                    if (council?.id) {
                        formData.append(`communal_councils[${index}][id]`, council.id);
                    }
                    formData.append(`communal_councils[${index}][name]`, council.name);
                });

                // Agregar comunas
                vm.record.communes.forEach((commune, index) => {
                    if (commune?.id) {
                        formData.append(`communes[${index}][id]`, commune.id);
                    }
                    formData.append(`communes[${index}][name]`, commune.name);
                });

                // Agregar instituciones e imágenes
                vm.record.institutions.forEach((institution, instIndex) => {
                    if (institution?.id) {
                        formData.append(`institutions[${instIndex}][id]`, institution.id);
                    }
                    formData.append(`institutions[${instIndex}][name]`, institution.name);
                    formData.append(`institutions[${instIndex}][rif]`, institution.rif);
                    formData.append(`institutions[${instIndex}][institution_type_id]`, institution.institution_type_id);
                    formData.append(`institutions[${instIndex}][institution_sector_id]`, institution.institution_sector_id);

                    // Agregar cada imagen de la institución
                    institution.images.forEach((file, fileIndex) => {
                        formData.append(`institutions[${instIndex}][images][${fileIndex}]`, file);
                    });
                });

                try {
                    if (vm.record.id) {
                        formData.append('_method', 'PUT');
                        route = `${route}/${vm.record.id}`;
                    }
                    const response = await axios.post(route, formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    });
                    if (response.data.result) {
                        location.href = vm.route_list;
                    }
                } catch (error) {
                    console.log(error);
                }
            },
            addCommunalCouncils() {
                this.record.communal_councils.push({name: ''});
            },
            addCommunes() {
                this.record.communes.push({name: ''});
            },
            addInstitutions() {
                this.record.institutions.push({
                    name: '',
                    rif: '',
                    institution_type_id: '',
                    institution_sector_id: '',
                    images: [],
                });
            },
            removeInstitution(index) {
                const _self = this;
                bootbox.confirm({
                    message: '¿Está seguro de eliminar este registro?',
                    buttons: {
                        confirm: {
                            label: 'Eliminar',
                            className: 'btn-danger',
                        },
                        cancel: {
                            label: 'Cancelar',
                            className: 'btn-default',
                        },
                    },
                    callback(result) {
                        if (result) {
                            if (_self.record.institutions.length === 1) {
                                bootbox.alert(
                                    'No se puede eliminar este registro. Debe indicar datos de al menos una institución'
                                );
                                return;
                            }
                            _self.record.institutions.splice(index, 1);
                        }
                    },
                });
            },
            removeCommunalCouncil(index) {
                const _self = this;
                bootbox.confirm({
                    message: '¿Está seguro de eliminar este registro?',
                    buttons: {
                        confirm: {
                            label: 'Eliminar',
                            className: 'btn-danger',
                        },
                        cancel: {
                            label: 'Cancelar',
                            className: 'btn-default',
                        },
                    },
                    callback(result) {
                        if (result) {
                            if (_self.record.communal_councils.length === 1) {
                                bootbox.alert(
                                    'No se puede eliminar este registro. Debe indicar datos de al menos un concejo comunal'
                                );
                                return;
                            }
                            _self.record.communal_councils.splice(index, 1);
                        }
                    },
                });
            },
            removeCommune(index) {
                const _self = this;
                bootbox.confirm({
                    message: '¿Está seguro de eliminar este registro?',
                    buttons: {
                        confirm: {
                            label: 'Eliminar',
                            className: 'btn-danger',
                        },
                        cancel: {
                            label: 'Cancelar',
                            className: 'btn-default',
                        },
                    },
                    callback(result) {
                        if (result) {
                            if (_self.record.communes.length === 1) {
                                bootbox.alert(
                                    'No se puede eliminar este registro. Debe indicar datos de al menos una comuna'
                                );
                                return;
                            }
                            _self.record.communes.splice(index, 1);
                        }
                    },
                });
            },
            upload(index) {
                const vm = this;
                const input = document.getElementById('images' + index);
                const files = input.files;
                if (files.length > 0) {
                    for (const file of files) {
                        vm.record.institutions[index].images.push(file);
                    }
                }
            },
            setCommunityData() {
                const vm = this;
                if (!vm.record.citizen_service_community_id) {
                    vm.record.population = '';
                    vm.record.country = '';
                    vm.record.estate = '';
                    vm.record.city = '';
                    vm.record.municipality = '';
                    vm.record.parish = '';
                    vm.record.additional_location_data = '';
                    return;
                }
                const selectedCommunity = vm.communities.find(
                    (community) => community.id == vm.record.citizen_service_community_id
                );
                if (selectedCommunity) {
                    vm.record.population = selectedCommunity.population;
                    vm.record.country = selectedCommunity.location.country;
                    vm.record.estate = selectedCommunity.location.estate;
                    vm.record.city = selectedCommunity.location.city;
                    vm.record.municipality = selectedCommunity.location.municipality;
                    vm.record.parish = selectedCommunity.location.parish;
                    vm.record.additional_location_data = selectedCommunity.location.additional_location_data;
                }
            }
        },
        async mounted() {
            const vm = this;
            vm.record.communal_councils = [{name: ''}];
            vm.record.communes = [{name: ''}];
            vm.record.institutions = [
                {
                    name: '',
                    rif: '',
                    institution_type_id: '',
                    institution_sector_id: '',
                    images: [],
                }
            ];
            await vm.getCommunities();
            await vm.getInstitutionTypes('institutionTypes');
            await vm.getInstitutionSectors('institutionSectors');
            if (vm.initial_data) {
                const initialData = JSON.parse(vm.initial_data);
                vm.record = initialData;
                vm.record.institutions = vm.record.institutions.map((institution) => {
                    return {
                        ...institution,
                        images: institution.images.map((image) => {
                            return `${window.app_url}/${image.url}`;
                        }),
                    };
                });
                vm.record.institutions.forEach((institution, index) => {
                    if (typeof vm.$refs['images' + index] !== 'undefined' && vm.$refs['images' + index].length > 0) {
                        vm.$refs['images' + index][0].images = institution.images;
                    }
                });
                if (vm.record.communal_councils.length === 0) {
                    vm.record.communal_councils = [{name: ''}];
                }
                if (vm.record.communes.length === 0) {
                    vm.record.communes = [{name: ''}];
                }
            }
        },
    }
</script>
