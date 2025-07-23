<template>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 mt-2 mb-2 text-center">
        <a
            class="btn-simplex btn-simplex-md btn-simplex-primary"
            href="javascript:void(0)"
            title="Registros de especialidades de proveedores"
            data-toggle="tooltip" v-has-tooltip
            @click="addRecord('add_specialty', '/purchase/supplier-specialties', $event)"
        >
            <i class="icofont icofont-cube ico-3x"></i>
            <span>Especialidad de <br> Proveedor</span>
        </a>
        <div class="modal fade text-left" tabindex="-1" id="add_specialty">
            <div class="modal-dialog vue-crud">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-cube inline-block"></i>
                            Especialidad de Proveedor
                        </h6>
                    </div>
                    <div class="modal-body">
                        <!-- Componente para mostrar errores en el formulario -->
                        <purchase-show-errors ref="purchaseShowError" />
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group is-required">
                                    <label for="purchase_supplier_specialty_name">Nombre:</label>
                                    <input type="hidden" v-model="record.id">
                                    <input
                                        id="purchase_supplier_specialty_name"
                                        type="text"
                                        placeholder="Nombre de la especialidad del proveedor"
                                        data-toggle="tooltip"
                                        v-has-tooltip
                                        v-model="record.name"
                                        title="Indique el nombre de la especialidad del proveedor (requerido)"
                                        class="form-control input-sm"
                                    >
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div
                                    class="form-group" data-toggle="tooltip"
                                    v-has-tooltip
                                    title="Indique la descripción para la especialidad del proveedor"
                                >
                                    <label for="purchase_supplier_specialty_description">Descripción:</label>
                                    <ckeditor
                                        id="purchase_supplier_specialty_description"
                                        :editor="ckeditor.editor"
                                        :config="ckeditor.editorConfig"
                                        class="form-control" tag-name="textarea" rows="3"
                                        v-model="record.description"
                                        placeholder="Descripción de la especialidad del proveedor"
                                    ></ckeditor>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-group">
                            <modal-form-buttons saveRoute="purchase/supplier-specialties"></modal-form-buttons>
                        </div>
                    </div>
                    <div class="modal-body modal-table">
                        <v-client-table :columns="columns" :data="records" :options="table_options">
                            <div slot="description" slot-scope="props">
                                <p v-html="props.row.description"></p>
                            </div>
                            <div slot="id" slot-scope="props" class="text-center">
                                <button
                                    @click="initUpdate(props.row.id, $event)"
                                    class="btn btn-warning btn-xs btn-icon btn-action"
                                    title="Modificar registro"
                                    data-toggle="tooltip"
                                    v-has-tooltip type="button"
                                >
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button
                                    @click="deleteRecord(props.row.id, '/purchase/supplier-specialties')"
                                    class="btn btn-danger btn-xs btn-icon btn-action"
                                    title="Eliminar registro" data-toggle="tooltip"
                                    v-has-tooltip type="button"
                                >
                                    <i class="fa fa-trash-o"></i>
                                </button>
                            </div>
                        </v-client-table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    data() {
        return {
            record: {
                id: '',
                name: '',
                description: ''
            },
            errors: [],
            records: [],
            columns: ['name', 'description', 'id'],
        }
    },
    methods: {
        /**
         * Método que borra todos los datos del formulario
         *
         * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        reset() {
            this.record = {
                id: '',
                name: '',
                description: ''
            };
        },
    },
    created() {
        this.table_options.headings = {
            'name': 'Nombre',
            'description': 'Descripción',
            'id': 'Acción'
        };
        this.table_options.sortable = ['name', 'description'];
        this.table_options.filterable = ['name', 'description'];
        this.table_options.columnsClasses = {
            'name': 'col-md-4',
            'description': 'col-md-6',
            'id': 'col-md-2'
        };
    },
};
</script>
