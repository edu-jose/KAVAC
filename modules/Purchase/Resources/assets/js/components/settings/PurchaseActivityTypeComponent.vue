<template>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 mt-2 mb-2 text-center">
        <a
            class="btn-simplex btn-simplex-md btn-simplex-primary"
            href="javascript:void(0)"
            title="Registros de tipos de actividad"
            data-toggle="tooltip" v-has-tooltip
            @click="addRecord('add_activity_type', '/purchase/activity-types', $event)"
        >
            <i class="icofont icofont-tasks ico-3x"></i>
            <span>Tipos de <br>Actividad</span>
        </a>
        <div class="modal fade text-left" tabindex="-1" id="add_activity_type">
            <div class="modal-dialog vue-crud">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-tasks inline-block"></i>
                            Tipos de Actividad
                        </h6>
                    </div>
                    <div class="modal-body">
                        <!-- Componente para mostrar errores en el formulario -->
                        <purchase-show-errors ref="purchaseShowError" />
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group is-required">
                                    <label for="purchase_activity_type_name">Nombre:</label>
                                    <input
                                        id="purchase_activity_type_name"
                                        type="text"
                                        placeholder="Nombre del tipo de actividad"
                                        data-toggle="tooltip"
                                        v-has-tooltip
                                        v-model="record.name"
                                        title="Indique el nombre del tipo de actividad (requerido)"
                                        class="form-control input-sm"
                                        v-is-only-text
                                    >
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div
                                    class="form-group"
                                    data-toggle="tooltip" v-has-tooltip
                                    title="Indique la descripción para el tipo de actividad"
                                >
                                    <label for="purchase_activity_type_description">Descripción:</label>
                                    <ckeditor
                                        id="purchase_activity_type_description"
                                        :editor="ckeditor.editor"
                                        :config="ckeditor.editorConfig"
                                        class="form-control" tag-name="textarea" rows="3"
                                        v-model="record.description"
                                        placeholder="Descripción del tipo de actividad"
                                    ></ckeditor>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-group">
                            <modal-form-buttons saveRoute="purchase/activity-types"></modal-form-buttons>
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
                                    @click="deleteRecord(props.row.id, '/purchase/activity-types')"
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
