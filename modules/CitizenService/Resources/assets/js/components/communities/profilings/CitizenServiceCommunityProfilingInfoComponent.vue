<template>
    <section>
        <!-- Button trigger modal -->
        <button
            type="button"
            class="btn btn-info btn-xs btn-icon btn-action"
            data-toggle="modal" :data-target="'#' + id"
            title="Ver información"
            v-has-tooltip
        >
            <i class="fa fa-eye"></i>
        </button>

        <!-- Modal -->
        <div class="modal fade" :id="id" tabindex="-1" aria-labelledby="modalTitleId" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detalles de caracterización de la comunidad</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                    </div>
                    <div class="modal-body text-justify">
                        <h6 class="h6">Datos de la Comunidad</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Comunidad:</strong>
                                <div>
                                    {{ initial_data.community.name }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <strong>Población:</strong>
                                <div v-html="initial_data.community.population"></div>
                            </div>
                        </div>
                        <h6 class="h6 mt-2">Datos de Ubicación</h6>
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Pais:</strong>
                                <div>{{ initial_data.community.parish.municipality.estate.country.name }}</div>
                            </div>
                            <div class="col-md-3">
                                <strong>Estado:</strong>
                                <div>{{ initial_data.community.parish.municipality.estate.name }}</div>
                            </div>
                            <div class="col-md-3">
                                <strong>Ciudad:</strong>
                                <div>{{ initial_data.community.city.name }}</div>
                            </div>
                            <div class="col-md-3">
                                <strong>Municipio:</strong>
                                <div>{{ initial_data.community.parish.municipality.name }}</div>
                            </div>
                            <div class="col-md-3">
                                <strong>Parroquia:</strong>
                                <div>{{ initial_data.community.parish.name }}</div>
                            </div>
                            <div class="col-md-3">
                                <strong>Datos complementarios de ubicación:</strong>
                                <div v-html="initial_data.community.location"></div>
                            </div>
                        </div>
                        <h6 class="h6 mt-2">Datos de la caracterización</h6>
                        <div class="row">
                            <div class="col-12">
                                <strong>Principales necesidades de la comunidad:</strong>
                                <div v-html="initial_data.main_needs"></div>
                            </div>
                            <div class="col-md-3">
                                <strong>Consejos Comunales:</strong>
                                <div v-if="initial_data.is_communal_council">
                                    <ul>
                                        <li
                                            class="text-left"
                                            v-for="communal_council in initial_data.communal_councils"
                                            :key="communal_council.id"
                                        >
                                            {{ communal_council.name }}
                                        </li>
                                    </ul>
                                </div>
                                <div v-else>
                                    <span>N/A</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <strong>Comunas:</strong>
                                <div v-if="initial_data.is_commune">
                                    <ul>
                                        <li
                                            class="text-left"
                                            v-for="commune in initial_data.communes"
                                            :key="commune.id"
                                        >
                                            {{ commune.name }}
                                        </li>
                                    </ul>
                                </div>
                                <div v-else>
                                    <span>N/A</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <strong>Comité de Ciencia y Tecnología:</strong>
                                <div v-if="initial_data.has_tic_committe" v-html="initial_data.tic_committe_name"></div>
                                <div v-else>
                                    <span>N/A</span>
                                </div>
                            </div>
                        </div>
                        <h6 class="h6 mt-2">Instituciones</h6>
                        <div
                            class="row"
                            v-for="(institution, index) in initial_data.institutions"
                            :key="institution.id"
                        >
                            <div class="col-md-3">
                                <strong>Nombre:</strong>
                                <div>{{ institution.name }}</div>
                            </div>
                            <div class="col-md-3">
                                <strong>RIF:</strong>
                                <div>{{ institution.rif }}</div>
                            </div>
                            <div class="col-md-3">
                                <strong>Tipo:</strong>
                                <div>{{ institution.institution_type.name }}</div>
                            </div>
                            <div class="col-md-3">
                                <strong>Sector:</strong>
                                <div>{{ institution.institution_sector.name }}</div>
                            </div>
                            <div class="col-12">
                                <h6 class="h6 text-center my-2">Fotos</h6>
                                <div class="row">
                                    <div class="col-md-2 mb-2" v-for="image in institution.images" :key="image.id">
                                        <a
                                            :href="setUrl(image.url)"
                                            target="_blank"
                                            data-toggle="tooltip"
                                            title="Presione para ver la imagen en tamaño real"
                                        >
                                            <img
                                                :src="setUrl(image.url)"
                                                alt=""
                                                class="img-fluid img-thumbnail-gallery"
                                            />
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <hr v-if="index < initial_data.institutions.length - 1" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button
                            :id="'closeButton' + id"
                            type="button"
                            class="btn btn-secondary"
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
        props: ['initial_data', 'id'],
        mounted() {
            const _self = this;
            _self.$nextTick(() => {
                document.getElementById('closeButton' + _self.id).addEventListener('click', (event) => {
                    event.preventDefault();
                    event.target.blur(); // Quita el foco del botón
                });
            });
        },
    }
</script>