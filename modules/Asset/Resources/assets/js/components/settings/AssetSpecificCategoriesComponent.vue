<template>
    <section id="assetSpecificCategoriesComponent">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary"
           href="#" title="Registros de Categorias Específicas de Bienes" data-toggle="tooltip"
           @click="addRecord('add_specific_category', 'asset/specific', $event)">
            <i class="icofont icofont-cube ico-3x"></i>
            <span>Categorias<br>Específicas</span>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" id="add_specific_category">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6 class="d-flex align-items-center">
                            <i class="icofont icofont-cube ico-2x mr-1"></i>
                            Nueva Categoria Específica de Bienes
                        </h6>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger" v-if="errors.length > 0">
                            <div class="container">
                                <div class="alert-icon">
                                    <i class="now-ui-icons objects_support-17"></i>
                                </div>
                                <strong>¡Atención!</strong> Debe verificar los siguientes errores antes de continuar:
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"
                                        @click.prevent="errors = []">
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
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label>Tipo de bien:</label>
                                    <select2 :options="asset_types" @input="getAssetCategories"
                                             v-model="record.asset_type_id"></select2>
                                    <input type="hidden" v-model="record.id">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label>Categoría general:</label>
                                    <select2 :options="asset_categories" @input="getAssetSubcategories"
                                             v-model="record.asset_category_id"></select2>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label>Subcategoría:</label>
                                    <select2 :options="asset_subcategories"
                                             v-model="record.asset_subcategory_id"></select2>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label>Código de la categoría específica:</label>
                                    <input type="text" placeholder="Código de la Categoría Específica" data-toggle="tooltip"
                                           title="Indique el código de la nueva Categoría Específica (requerido)"
                                           class="form-control input-sm" v-model="record.code">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label>Categoría especifica:</label>
                                    <input type="text" placeholder="Nueva Categoría Específica" data-toggle="tooltip"
                                           v-input-mask data-inputmask-regex="[a-zA-ZÁ-ÿ\s]*$"
                                           title="Indique la nueva Categoría Específica (requerido)"
                                           class="form-control input-sm" v-model="record.name">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-group">
                            <button type="button" class="btn btn-default btn-sm btn-round btn-modal-close"
                                    @click="clearFilters" data-dismiss="modal">
                                Cerrar
                            </button>
                            <button type="button" class="btn btn-warning btn-sm btn-round btn-modal btn-modal-clear"
                                    @click="reset()">
                                Cancelar
                            </button>
                            <button type="button" @click="createRecord('asset/specific')"
                                    class="btn btn-primary btn-sm btn-round btn-modal-save">
                                Guardar
                            </button>
                        </div>
                    </div>
                    <div class="modal-body modal-table">
                        <v-client-table :columns="columns" :data="records" :options="table_options">
                            <div slot="id" slot-scope="props">
                                <button @click="initUpdate(props.row.id, $event)"
                                        class="btn btn-warning btn-xs btn-icon btn-action" v-has-tooltip
                                        title="Modificar registro" data-toggle="tooltip" type="button">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button @click="deleteRecord(props.row.id, 'asset/specific')"
                                        class="btn btn-danger btn-xs btn-icon btn-action" v-has-tooltip
                                        title="Eliminar registro" data-toggle="tooltip"
                                        type="button">
                                    <i class="fa fa-trash-o"></i>
                                </button>
                            </div>
                        </v-client-table>
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
                    id: '',
                    asset_type_id: '',
                    asset_category_id: '',
                    asset_subcategory_id: '',
                    name: '',
                    code: ''
                },
                errors: [],
                records: [],
                asset_types: [],
                asset_categories: [],
                asset_subcategories: [],
                columns: ['asset_subcategory.name', 'name', 'code', 'id'],
            }
        },
        created() {
            this.table_options.headings = {
                'asset_subcategory.name': 'Subcategoria',
                'name': 'Categoria Especifica',
                'code': 'Código',
                'id': 'Acción'
            };
            this.table_options.sortable = ['asset_subcategory.name','name', 'code'];
            this.table_options.filterable = ['asset_subcategory.name','name', 'code'];
            this.table_options.columnsClasses = {
                'asset_subcategory.name': 'col-md-5',
                'name':                   'col-md-4',
                'code':                   'col-md-1 text-center',
                'id':                     'col-md-2 text-center'
            };

        },
        mounted() {
            this.getAssetTypes();
        },
        methods: {
            /**
             * Obtiene los datos de las categorias generales de los bienes institucionales registrados
             * (Mixin modificado porque el campo categoría general no se está llenando)
             *
             * @author  Angelo Osorio <adosorio@cenditel.gob.ve> | <danielking.321@gmail.com>
             * @author Henry Paredes <hparedes@cenditel.gob.ve>
             */
            async getAssetCategories() {
                var vm = this;
                vm.asset_categories = [];

                if (vm.record.asset_type_id) {
                    await axios.get(
                        `${window.app_url}/asset/get-categories/${vm.record.asset_type_id}`
                    ).then(function (response) {
                        vm.asset_categories = response.data;
                    });
                    if ((vm.record.asset_subcategory.asset_category) && (vm.record.id)) {
                        vm.record.asset_category_id = vm.record.asset_subcategory.asset_category_id;
                    }
                }
            },

            /**
             * Método que borra todos los datos del formulario
             *
             * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            reset() {
                this.record = {
                    id: '',
                    asset_type_id: '',
                    asset_category_id: '',
                    asset_subcategory_id: '',
                    name: '',
                    code: ''
                };
            },
        },
    };
</script>
