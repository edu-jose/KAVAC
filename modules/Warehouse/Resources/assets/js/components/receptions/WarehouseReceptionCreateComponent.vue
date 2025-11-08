<template>
    <section id="WarehouseReceptionForm">
        <div class="card-body">

            <div class="alert alert-danger" v-if="errors.length > 0">
                <div class="container">
                    <div class="alert-icon">
                        <i class="now-ui-icons objects_support-17"></i>
                    </div>
                    <strong>Cuidado!</strong> Debe verificar los siguientes errores antes de continuar:
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"
                            @click.prevent="errors = []">
                        <span aria-hidden="true">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </span>
                    </button>
                    <ul>
                        <li v-for="(error, index) in formattedErrors" :key="index">{{ error }}</li>
                    </ul>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <b>Entrega</b>
                </div>
                <div v-if="purchase_existing == 1" class="col-md-2" id="helpInstitution">
                    <div class="form-group">
                        <label>Traer información de compras:</label>
                        <div class="custom-control custom-switch" data-toggle="tooltip"
                            title="Establecer los atributos del insumo para gestionar las variantes">
                            <input type="checkbox" class="custom-control-input" id="bring_purchase_info"
                                    :value="true" v-model="bring_purchase_info" @click="resetPurchaseFields">
                            <label class="custom-control-label" for="bring_purchase_info"></label>
                        </div>
                    </div>
                </div>
                <div v-if="bring_purchase_info == true" class="col-md-3" id="helpInstitution">
                    <div class="form-group is-required">
                        <label for="purchase_direct_hire_id">Código:</label>
                        <select2
                            id="purchase_direct_hire_id"
                            :options="purchase_direct_hires"
                            v-model="record.purchase_direct_hire_id"
                            @input="getDirectHireSupplier()">
                        </select2>
                    </div>
                </div>
                <div v-else class="col-md-3" id="helpWarehouse">
                    <div class="form-group is-required">
                        <label for="direct_hire">Código:</label>
                        <input type="text" class="form-control input-sm"
                               maxlength="20" v-model="record.direct_hire" placeholder="Código"
                               data-toggle="tooltip" title="Código"
                               v-input-mask data-inputmask-regex="^([A-Za-z0-9]*)$">
                    </div>
                </div>
                <div v-if="bring_purchase_info == true" class="col-md-3" id="helpInstitution">
                    <div class="form-group">
                        <label for="purchase_supplier_id">Proveedor:</label>
                        <select2
                            id="purchase_supplier_id"
                            :options="purchase_suppliers"
                            v-model="record.purchase_supplier_id">
                        </select2>
                    </div>
                </div>
                <div v-else class="col-md-3" id="helpWarehouse">
                    <div class="form-group">
                        <label for="supplier">Proveedor:</label>
                        <input type="text" class="form-control input-sm"
                               v-model="record.supplier" placeholder="Proveedor del insumo"
                               data-toggle="tooltip" title="Proveedor del insumo">
                    </div>
                </div>
                <div class="col-md-4" id="helpWarehouseRequestDate">
                    <div class="form-group">
                        <label>Observaciones generales:</label>
                        <ckeditor :editor="ckeditor.editor"
                            :config="ckeditor.editorConfig"
                            class="form-control" tag-name="textarea"
                            rows="3" v-model="record.general_observations">
                        </ckeditor>
                    </div>
                </div>
            </div>

            <div class="row" v-if="record.id == ''">
                <div class="col-md-12">
                    <b>Seleccione el destino de los insumos</b>
                </div>
                <div class="col-md-4" id="helpInstitution">
                    <div class="form-group is-required">
                        <label>Nombre de la organización:</label>
                        <select2
                            :options="institutions"
                            @input="getWarehouses"
                            v-model="record.institution_id">
                        </select2>
                        <input type="hidden" v-model="record.id">
                    </div>
                </div>

                <div class="col-md-4" id="helpWarehouse">
                    <div class="form-group is-required">
                        <label>Nombre del almacén:</label>
                        <select2
                            :options="warehouses"
                            @input="getWarehouseProducts"
                            v-model="record.warehouse_id">
                        </select2>
                    </div>
                </div>
                <div class="col-md-4" id="helpWarehouseRequestDate">
                    <div class="form-group is-required">
                        <label>Fecha de ingreso</label>
                        <input type="date" data-toggle="tooltip" title="Fecha de ingreso" class="form-control input-sm"
                        v-model="record.reception_date">
                    </div>
                </div>
            </div>

            <div class="row" v-if="record.id != ''">
                <div class="col-md-12">
                    <b>Seleccione el destino de los insumos</b>
                </div>
                <div class="col-md-4" id="helpInstitution">
                    <div class="form-group is-required">
                        <label>Nombre de la organización:</label>
                        <select2
                            :options="institutions"
                            v-model="record.institution_id">
                        </select2>
                        <input type="hidden" v-model="record.id">
                    </div>
                </div>

                <div class="col-md-4" id="helpWarehouse">
                    <div class="form-group is-required">
                        <label>Nombre del almacén:</label>
                        <select2
                            :options="warehouses"
                            v-model="record.warehouse_id">
                        </select2>
                    </div>
                </div>
                <div class="col-md-4" id="helpWarehouseRequestDate">
                    <div class="form-group is-required">
                        <label>Fecha de ingreso</label>
                        <input type="date" data-toggle="tooltip" title="Fecha de ingreso" class="form-control input-sm"
                        v-model="record.reception_date">
                    </div>
                </div>
            </div>

            <hr>
            <div class="float-right">
                <button 
                    type="button" 
                    class="btn btn-sm btn-primary btn-custom"
                    title="Exportar plantilla para carga masiva (OJO SE TIENE QUE SELECCIONAR EL ALMACÉN PRIMERO)"
                    data-toggle="tooltip"
                    @click="exportTemplate"
                    :disabled="!record.warehouse_id || loading"
                >
                    <i class="fa fa-file-excel-o"></i> 
                    {{ loading ? 'Generando...' : 'Exportar' }}
                </button>
                <button 
                    type="button" 
                    class="btn btn-sm btn-primary btn-custom"
                    title="Importar datos desde archivo"
                    data-toggle="tooltip"
                    @click="showImportSection = !showImportSection"
                >
                    <i class="fa fa-upload"></i> Importar
                </button>
            </div>
            <br>

            <div class="row" id="helpSectionProducts">
                <div class="row" id="helpSectionProducts" v-if="showManualInputs">
                    <div class="col-md-12">
                        <b>Ingrese los insumos a la solicitud</b>
                    </div>
                    <div class="col-md-3" id="helpProductName">
                        <div class="form-group is-required">
                            <label>Nombre del insumo:</label>
                            <select2 :options="warehouse_products"
                                @input="getWarehouseProductAttributes();getWarehouseProductRules();"
                                v-model="warehouse_inventory_product.warehouse_product_id">
                            </select2>
                        </div>
                    </div>
                    <div class="col-md-3" id="helpProductQuantity">
                        <div class="form-group is-required">
                            <label>Cantidad:</label>
                            <input type="text" placeholder="Cantidad del insumo"
                                   title="Cantidad del insumo" data-toggle="tooltip"
                                    class="form-control input-sm"
                                    v-input-mask data-inputmask="
                                        'alias': 'numeric',
                                        'allowMinus': 'false',
                                        'digits': 2"
                                   v-model="warehouse_inventory_product.quantity">
                        </div>
                    </div>
                    <div class="col-md-3" id="helpProductValue">
                        <div class="form-group">
                            <label>Valor:</label>
                            <input  id="productValue"
                                    type="text" data-toggle="tooltip"
                                    title="Valor por unidad del insumo"
                                    placeholder="Valor por unidad del insumo"
                                    class="form-control input-sm"
                                    v-input-mask data-inputmask="
                                        'alias': 'numeric',
                                        'allowMinus': 'false',
                                        'digits': 2"
                                    v-model="warehouse_inventory_product.unit_value">

                        </div>
                    </div>
                    <div class="col-md-3" id="helpProductCurrency">
                        <div class="form-group is-required">
                            <label>Moneda:</label>
                            <select2 :options="currencies"
                                     v-model="warehouse_inventory_product.currency_id"></select2>
                        </div>
                    </div>
                    <div class="col-md-3" id="helpWarehouseRequestDate">
                        <div class="form-group is-required">
                            <label>Fecha de vencimiento</label>
                            <input type="date" data-toggle="tooltip" title="Fecha de vencimiento" class="form-control input-sm no-restrict"
                            v-model="warehouse_inventory_product.expiration_date">
                        </div>
                    </div>
                    <div class="col-md-3" id="helpWarehouseRequestDate">
                        <div class="form-group is-required">
                            <label>Lote</label>
                            <input type="text" data-toggle="tooltip" title="Lote" class="form-control input-sm"
                            maxlength="20" v-model="warehouse_inventory_product.batch_number"
                            v-input-mask data-inputmask-regex="^([A-Za-z0-9\/\-*]*)$"> <!-- Permitir más caracteres -->
                        </div>
                    </div>
                </div>  
            </div>
            <div class="row">
                <div class="row" v-if="showManualInputs">
                    <hr>
                    <div class="col-md-12">
                        <b>Reglas de abastecimiento del insumo</b>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Minimo:</label>
                            <input  type="text" data-toggle="tooltip"
                                    placeholder="Minimo establecido del insumo"
                                    class="form-control input-sm"
                                    v-input-mask data-inputmask="
                                        'alias': 'numeric',
                                        'allowMinus': 'false',
                                        'digits': 2"
                                    v-model="warehouse_inventory_product.minimum">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Maximo:</label>
                            <input  type="text" data-toggle="tooltip"
                                    placeholder="Maximo establecido del insumo"
                                    class="form-control input-sm"
                                    v-input-mask data-inputmask="
                                        'alias': 'numeric',
                                        'allowMinus': 'false',
                                        'digits': 2"
                                    v-model="warehouse_inventory_product.maximum">
                        </div>
                    </div>
                </div>  
            </div>
            <div class="row" v-show="warehouse_inventory_product.warehouse_product_attributes.length > 0">
                <hr>
                <div class="col-md-12">
                    <b>Características del insumo</b>
                </div>
                <div class="col-md-3" v-for="(attribute, index) in warehouse_inventory_product.warehouse_product_attributes" :key="index">
                    <div class="form-group">
                        <label>{{attribute.name.charAt(0).toUpperCase() + attribute.name.slice(1) }}:</label>
                        <input type="text" placeholder="" data-toggle="tooltip"
                           class="form-control input-sm" :id="attribute.name"
                           v-model="attribute.value">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <button type="button" @click="addProduct($event)" class="btn btn-sm btn-primary btn-custom float-right"
                            title="Agregar registro a la lista"
                            data-toggle="tooltip" v-if="showManualInputs">
                        <i class="fa fa-plus-circle"></i>
                        Agregar
                    </button>
                </div>
            </div>
            <hr>
           

            <!-- Sección de importación (se mostrará solo cuando showImportSection sea true) -->
            <div class="row" v-if="showImportSection">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Importar datos desde hoja de cálculo</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <strong>Instrucciones:</strong> El archivo debe contener los siguientes campos:
                                <ul>
                                    <li>Nombre del insumo (selección)</li>
                                    <li>Cantidad (número)</li>
                                    <li>Valor (número)</li>
                                    <li>Moneda (selección)</li>
                                    <li>Lote (texto)</li>
                                    <li>Fecha de vencimiento (fecha)</li>
                                    <li>Mínimo (número, opcional)</li>
                                    <li>Máximo (número, opcional)</li>
                                </ul>
                                <strong>Todos los campos de Entrega y Selección de destino de insumo tienen que estar completados excepto el proveedor que es opcional</strong> 
                            </div>
                            <h6>EJEMPLO: Formato de hoja de cálculo</h6>
                            <div class="table-responsive">
                                <table  class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <td class="text-center"><strong>Nombre del insumo</strong></td>
                                            <td class="text-center"><strong>Cantidad</strong></td>
                                            <td class="text-center"><strong>Valor</strong></td>
                                            <td class="text-center"><strong>Moneda</strong></td>
                                            <td class="text-center"><strong>Lote</strong></td>
                                            <td class="text-center"><strong>Fecha de vencimiento</strong></td>
                                            <td class="text-center"><strong>Mínimo</strong></td>
                                            <td class="text-center"><strong>Máximo</strong></td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-center">Insumo selección</td>
                                            <td class="text-center">01 a infinito</td>
                                            <td class="text-center">01 a infinito</td>
                                            <td class="text-center">$ selección</td>
                                            <td class="text-center">x lote</td>
                                            <td class="text-center">26/01/1989</td>
                                            <td class="text-center">1</td>
                                            <td class="text-center">9999999</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="form-group">
                                <label>Seleccione el archivo a importar (.xlsx)</label>
                                <input 
                                    type="file" 
                                    class="form-control" 
                                    accept=".xlsx"
                                    ref="fileInput"
                                    @change="handleFileImport"
                                >
                            </div>
                            
                            <div class="text-right">
                                <button 
                                    type="button" 
                                    class="btn btn-sm btn-default"
                                    @click="showImportSection = false"
                                >
                                    Cancelar
                                </button>
                                <button 
                                    type="button" 
                                    class="btn btn-sm btn-primary"
                                    @click="importData"
                                    :disabled="!importFile"
                                >
                                    Procesar Importación
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <v-client-table id="helpTable"
                :columns="columns" :data="records" :options="table_options">
                <div slot="name" slot-scope="props" class="text-center">
                    <span>
                        {{ (props.row.warehouse_product)?props.row.warehouse_product.name:'N/A' }}
                    </span>
                </div>
                <div slot="warehouse_product_attributes" slot-scope="props">
                    <span>
                        <div v-for="(att, index) in props.row.warehouse_product_attributes" :key="index">
                            <b>{{att.name +":"}}</b> {{ att.value}}
                        </div>
                        <div>
                            <b>Valor:</b> {{props.row.unit_value}} {{(props.row.currency)?props.row.currency.name:''}}
                        </div>
                        <div v-if="props.row.minimum != ''">
                            <b>Mínimo:</b> {{ props.row.minimum }}
                        </div>
                        <div v-if="props.row.maximum != ''">
                            <b>Máximo:</b> {{ props.row.maximum }}
                        </div>
                        <div v-if="props.row.batch_number != ''">
                            <b>Lote:</b> {{ props.row.batch_number }}
                        </div>
                        <div v-if="props.row.expiration_date != ''">
                            <b>Fecha de vencimiento:</b> {{ format_date(props.row.expiration_date) }}
                        </div>
                    </span>
                </div>
                <div slot="id" slot-scope="props" class="text-center">
                    <div class="d-inline-flex">
                        <button @click="editProduct(props.index, $event)"
                                class="btn btn-warning btn-xs btn-icon btn-action"
                                title="Modificar registro" data-toggle="tooltip" type="button">
                            <i class="fa fa-edit"></i>
                        </button>

                        <button @click="removeProduct(props.index, $event)"
                                class="btn btn-danger btn-xs btn-icon btn-action"
                                title="Eliminar registro" data-toggle="tooltip"
                                type="button">
                            <i class="fa fa-trash-o"></i>
                        </button>
                    </div>
                </div>
            </v-client-table>
        </div>

        <div class="card-footer text-right">
            <div class="row">
                <div class="col-md-3 offset-md-9" id="helpParamButtons">
                    <button type="button" @click="reset()" data-toggle="tooltip"
                            class="btn btn-default btn-icon btn-round"
                            title ="Borrar datos del formulario">
                            <i class="fa fa-eraser"></i>
                    </button>

                    <button type="button" @click="redirect_back(route_list)"
                            class="btn btn-warning btn-icon btn-round btn-modal-close"
                            data-dismiss="modal"
                            title="Cancelar y regresar">
                            <i class="fa fa-ban"></i>
                    </button>

                    <button type="button"  @click="createReception('warehouse/receptions')"
                            class="btn btn-success btn-icon btn-round btn-modal-save"
                            title="Guardar registro">
                        <i class="fa fa-save"></i>
                    </button>
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
                    institution_id: '',
                    warehouse_id: '',
                    reception_date: '',
                    purchase_supplier_id: '',
                    supplier: '',
                    direct_hire: '',
                    purchase_direct_hire_id: '',
                    general_observations: '',
                    warehouse_inventory_products: [],
                },
                loading: false,
                bring_purchase_info: false,
                warehouse_inventory_product: {
                    id: '',
                    quantity: '',
                    expiration_date: '',
                    batch_number: '',
                    unit_value:'',
                    currency_id: '',
                    minimum: '',
                    maximum: '',
                    warehouse_product_id: '',
                    warehouse_product_attributes: [],
                },

                columns: ['name', 'quantity', 'warehouse_product_attributes', 'id'],
                records: [],
                errors: [],

                setting: {
                    id: '',
                },

                institutions: [],
                warehouses: [],
                warehouse_products: [],
                currencies: [],

                /** Revisar */
                editIndex: null,
                warehouse_product_attributes: [],

                /** importación */
                showImportSection: false,
                importFile: null,
                importErrors: [],
            }
        },
        props: {
            receptionid: Number,
            purchase_suppliers: {
                type: Array,
                default: function() {
                    return [];
                }
            },
            purchase_direct_hires: {
                type: Array,
                default: function() {
                    return [];
                }
            },
            purchase_existing: {
                type: Number,
                default: false
            }
        },
        methods: {
            showSuccessNotification(message) {
                // Si tienes vue-notification instalado
                if (this.$notify) {
                    this.$notify({
                        type: 'success',
                        title: 'Éxito',
                        text: message,
                        duration: 3000
                    });
                } else {
                    // Fallback básico
                    alert(message);
                }
            },
            reset(all = true) {
                if (all) {
                    this.record = {
                        id: '',
                        institution_id: '',
                        warehouse_id: '',
                        reception_date: '',
                        warehouse_inventory_products: [],
                        purchase_supplier_id: '',
                        supplier: '',
                        direct_hire: '',
                        purchase_direct_hire_id: '',
                        general_observations: '',
                    };
                    this.bring_purchase_info = false;
                    this.records = [];

                }
                this.warehouse_inventory_product = {
                    id: '',
                    quantity: '',

                    expiration_date: '',
                    batch_number: '',

                    unit_value:'',
                    currency_id: '',
                    currency_name: '',

                    warehouse_product_id: '',
                    warehouse_product_name: '',

                    minimum: '',
                    maximum: '',

                    warehouse_product_attributes: [],
                },
                this.editIndex = null;
                this.warehouse_product_attributes.map(function (campo, key) {
                    var element = document.getElementById(campo.name);
                    if(element)
                        element.value = '';
                });
                this.getCurrencies();
            },

            resetPurchaseFields() {
                this.record.purchase_direct_hire_id = '';
                this.record.direct_hire = '';
                this.record.supplier = '';
                this.record.purchase_supplier_id = '';
            },

            getWarehouseProducts() {
                const vm = this;
                vm.warehouse_products = [];

                if (vm.record.warehouse_id != '') {
                    axios.get('/warehouse/get-warehouse-products/').then(response => {
                        vm.warehouse_products = response.data;
                    });
                }
            },

            getWarehouseProductAttributes() {
                const vm = this;
                var product_id = vm.warehouse_inventory_product.warehouse_product_id;
                vm.warehouse_product_attributes = [];

                if (product_id != '') {
                    axios.get('/warehouse/attributes/product/' + product_id).then(response => {
                        if (typeof response.data.records !== "undefined") {
                            vm.warehouse_product_attributes = response.data.records;
                            if ((vm.editIndex == null) || (vm.record.id == '')) {
                                vm.warehouse_inventory_product.warehouse_product_attributes = [];
                                $.each(vm.warehouse_product_attributes, function(index, attribute) {
                                    var value = attribute.warehouse_product_value;
                                    vm.warehouse_inventory_product.warehouse_product_attributes.push({
                                        name:attribute.name,
                                        value:""
                                    });
                                });
                            }
                        }
                    });
                }
            },
            getWarehouseProductRules() {
                const vm = this;
                if (typeof vm.editIndex !== 'undefined') return;
                var product_id = vm.warehouse_inventory_product.warehouse_product_id;
                var unit_value = vm.warehouse_inventory_product.unit_value;
                if ((product_id != '') && (vm.record.warehouse_id != '') && (vm.record.institution_id != '')) {
                    let field = {
                        institution_id: vm.record.institution_id,
                        warehouse_id: vm.record.warehouse_id,
                        reception_date: vm.record.reception_date,
                        warehouse_inventory_product: vm.warehouse_inventory_product,
                    };
                    axios.post('/warehouse/products/get-rules', field).then(response => {
                        if (typeof response.data.records !== "undefined") {
                            vm.warehouse_inventory_product.minimum = response.data.records.minimum
                                ? response.data.records.minimum
                                : '';
                            vm.warehouse_inventory_product.maximum = response.data.records.maximum
                                ? response.data.records.maximum
                                : '';
                        }
                    });
                }
            },

            isDuplicateProduct(product, editIndex = null) {
                return this.records.some((p, idx) =>
                    String(p.warehouse_product_id) === String(product.warehouse_product_id) &&
                    p.batch_number.trim().toLowerCase() === product.batch_number.trim().toLowerCase() &&
                    idx !== editIndex
                );
            },

            addProduct(event) {
                const vm = this;

                var att = [];
                var currency_name = '';
                var warehouse_product_name = '';

                vm.warehouse_inventory_product.batch_number = String(vm.warehouse_inventory_product.batch_number || '');


                if (this.isDuplicateProduct(this.warehouse_inventory_product, this.editIndex)) {
                    this.errors.push('Ya existe un insumo registrado con ese mismo lote. Por favor, modifique la cantidad.');
                    this.reset(false);
                    return;
                }

                vm.warehouse_product_attributes.map(function(campo, index) {
                    var element = document.getElementById(campo.name);
                    var field = { name: campo.name, value: element.value };
                    att.push(field);
                });
                event.preventDefault();

                if (vm.warehouse_inventory_product.warehouse_product_id != '') {
                    $.each(vm.warehouse_products, function(index, campo) {
                        if (campo.id == vm.warehouse_inventory_product.warehouse_product_id)
                            warehouse_product_name = campo.text;
                    });
                }
                if (vm.warehouse_inventory_product.currency_id != '') {
                    $.each(vm.currencies, function(index, campo) {
                        if (campo.id == vm.warehouse_inventory_product.currency_id)
                            currency_name = campo.text;
                    });
                }
                vm.warehouse_inventory_product.warehouse_product = {
                    name: warehouse_product_name,
                }
                vm.warehouse_inventory_product.currency = {
                    name: currency_name,
                }
                vm.warehouse_inventory_product.warehouse_product_attributes = att;

                if (!vm.validateErrors(vm.warehouse_inventory_product)) return false;

                if (this.editIndex === null) {
                    vm.records.push(vm.warehouse_inventory_product);
                    vm.reset(false);
                }
                else if (this.editIndex >= 0 ) {
                    vm.records.splice(this.editIndex, 1, vm.warehouse_inventory_product);
                    vm.reset(false);
                }
            },

            editProduct(index, event) {
                this.reset(false);
                this.editIndex = index-1;
                this.warehouse_inventory_product = {...this.records[index - 1]};
                
                this.warehouse_inventory_product.batch_number = String(this.warehouse_inventory_product.batch_number || '');

                $.each(this.warehouse_inventory_product.warehouse_product_attributes, function(index, campo) {
                    var element = document.getElementById(campo.name);
                    if(element)
                        element.value = campo.value;
                });
                event.preventDefault();
            },

            removeProduct(index, event) {
                this.records.splice(index-1, 1);
            },

            async getDirectHireSupplier() {
                const vm = this;
                var direct_hire_id = vm.record.purchase_direct_hire_id;
                if (vm.record.purchase_direct_hire_id != '') {
                    await axios.get('/warehouse/receptions/directhire/supplier/' + direct_hire_id)
                        .then(response => {
                           vm.record.purchase_supplier_id = response.data.records.id;
                        });
                }
            },

            validateErrors(field) {
                const vm = this;
                vm.errors = [];

                if (!field["warehouse_product_id"])
                    vm.errors.push('El campo nombre del insumo es obligatorio.');
                if (!field["quantity"])
                    vm.errors.push('El campo cantidad es obligatorio.');
                if (field["quantity"] == 0)
                    vm.errors.push('El campo cantidad debe ser mayor que cero.');
                if (!field["currency_id"])
                    vm.errors.push('El campo moneda es obligatorio.');
                if (!field["expiration_date"])
                    vm.errors.push('El campo fecha de vencimiento es obligatorio.');
                if (!field["batch_number"])
                    vm.errors.push('El campo lote es obligatorio.');

                if (vm.errors.length > 0)
                    return false;

                return true;
            },

            createReception(url) {
                const vm = this;
                vm.record.warehouse_inventory_products = vm.records;
                
                // Limpiar errores previos
                vm.errors = [];
                
                axios.post(url, vm.record)
                    .then(response => {
                        if (response.data.result) {
                            window.location.href = response.data.redirect;
                        } else {
                            // Error del servidor (año fiscal, configuración, etc.)
                            if (response.data.message) {
                                vm.errors.push(response.data.message);
                            }
                            if (response.data.redirect) {
                                // Si hay redirección específica para error
                                window.location.href = response.data.redirect;
                            }
                        }
                    })
                    .catch(error => {
                        if (error.response) {
                            if (error.response.status === 422) {
                                // Procesar errores de validación
                                const validationErrors = error.response.data.errors;
                                for (const field in validationErrors) {
                                    if (validationErrors.hasOwnProperty(field)) {
                                        // Convertir los errores del campo a mensajes legibles
                                        validationErrors[field].forEach(errorMessage => {
                                            vm.errors.push(errorMessage);
                                        });
                                    }
                                }
                            } else {
                                vm.errors.push('Ocurrió un error al procesar la solicitud');
                            }
                        } else {
                            vm.errors.push('Error de conexión con el servidor');
                        }
                    });
            },
            loadReception(id) {
                const vm = this;

                axios.get('/warehouse/receptions/info/' + id).then(response => {
                    vm.record = response.data.records;
                    vm.record.institution_id = vm.record.warehouse_institution_warehouse_end.institution_id;
                    vm.record.reception_date = vm.record.reception_date;
                    vm.record.direct_hire = vm.record.direct_hire;
                    vm.bring_purchase_info = vm.record.purchase_direct_hire_id ? true : false;
                    vm.record.purchase_direct_hire_id = vm.record.purchase_direct_hire_id;
                    vm.record.purchase_supplier_id = vm.record.purchase_supplier_id;
                    vm.record.supplier = vm.record.supplier;
                    vm.record.general_observations = vm.record.general_observations;
                    const timeOpen = setTimeout(addWarehouseId, 1000);
                    function addWarehouseId () {
                        vm.record.warehouse_id = vm.record.warehouse_institution_warehouse_end.warehouse_id;
                        vm.getWarehouseProducts();
                    }

                    $.each(vm.record.warehouse_inventory_product_movements, function(index, campo) {
                        var atts = [];
                        $.each(campo.warehouse_inventory_product.warehouse_product_values, function(index, field) {
                            var name = field.warehouse_product_attribute.name;
                            var value = field.value;
                            atts.push({name:name, value:value});
                        });
                        var warehouse_inventory_product = {
                            id: '',
                            quantity: campo.quantity,
                            unit_value: campo.new_value,
                            batch_number: campo.batch_number,
                            expiration_date: campo.expiration_date,
                            minimum: campo.warehouse_inventory_product.warehouse_inventory_rule ? campo.warehouse_inventory_product.warehouse_inventory_rule.minimum : '',
                            maximum: campo.warehouse_inventory_product.warehouse_inventory_rule ? campo.warehouse_inventory_product.warehouse_inventory_rule.maximum : '',
                            currency_id: campo.warehouse_inventory_product.currency_id,
                            currency: {
                                name: campo.warehouse_inventory_product.currency.name,
                            },
                            warehouse_product_id: campo.warehouse_inventory_product.warehouse_product_id,
                            warehouse_product: {
                                name: campo.warehouse_inventory_product.warehouse_product.name,
                            },
                            warehouse_product_attributes: atts,
                        };
                        vm.records.push(warehouse_inventory_product);
                    });
                });
            },

            /**
             * Exporta una plantilla Excel con los productos disponibles en el almacén seleccionado
             */
             exportTemplate() {
                if (!this.record.warehouse_id || !this.record.institution_id) {
                    this.errors.push('Debe seleccionar una institución y un almacén antes de exportar la plantilla');
                    return;
                }

                this.loading = true;
                
                // Construye la URL correctamente
                const url = `/warehouse/receptions/export-template?warehouse_id=${this.record.warehouse_id}&institution_id=${this.record.institution_id}`;


                console.log(url);




                axios({
                    url: url.toString(),
                    method: 'GET',
                    responseType: 'blob'
                }).then(response => {
                    const blobUrl = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = blobUrl;
                    
                    // Obtener nombre del archivo del header o usar uno por defecto
                    const contentDisposition = response.headers['content-disposition'];
                    let fileName = 'plantilla_recepcion_almacen.xlsx';
                    
                    if (contentDisposition) {
                        const fileNameMatch = contentDisposition.match(/filename="(.+)"/);
                        if (fileNameMatch && fileNameMatch.length === 2) {
                            fileName = fileNameMatch[1];
                        }
                    }
                    
                    link.setAttribute('download', fileName);
                    document.body.appendChild(link);
                    link.click();
                    
                    // Limpieza
                    window.URL.revokeObjectURL(blobUrl);
                    link.remove();
                }).catch(error => {
                    console.error('Error en la descarga:', error);
                    this.errors.push('Error al descargar la plantilla');
                    
                    if (error.response && error.response.status === 404) {
                        this.errors.push('La ruta de exportación no fue encontrada');
                    }
                }).finally(() => {
                    this.loading = false;
                });
            },

            /**
             * Maneja la selección del archivo a importar
             */
            handleFileImport(event) {
                this.importFile = event.target.files[0];
                this.importErrors = [];
            },
                        
            /**
             * Procesa el archivo importado
             */
             
            importData() {
                if (!this.importFile) {
                    this.errors = ['El campo archivo es obligatorio'];
                    return;
                }
                
                const formData = new FormData();
                formData.append('file', this.importFile);
                formData.append('institution_id', this.record.institution_id);
                formData.append('warehouse_id', this.record.warehouse_id);
                
                // Agregar información de compra según corresponda
                if (this.bring_purchase_info) {
                    formData.append('direct_hire', this.record.purchase_direct_hire_id);
                    if (this.record.purchase_supplier_id) {
                        formData.append('supplier', this.record.purchase_supplier_id);
                    }
                } else {
                    formData.append('direct_hire', this.record.direct_hire);
                    if (this.record.supplier) {
                        formData.append('supplier', this.record.supplier);
                    }
                }
                
                formData.append('reception_date', this.record.reception_date);
                formData.append('general_observations', this.record.general_observations);
                
                this.loading = true;
                this.errors = [];

                axios.post('/warehouse/receptions/import', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }).then(response => {
                    if (response.data.success) {
                        // Agregar los productos importados a la lista
                        response.data.records.forEach(product => {
                            if (!this.isDuplicateProduct(product)) {
                                this.records.push(product);
                            }
                        });
                        
                        //this.showSuccessNotification('Los productos se importaron correctamente');
                        this.showImportSection = false;
                        this.importFile = null;
                    } else {
                        // Mostrar errores de validación
                        if (response.data.errors) {
                            this.errors = response.data.errors;
                        } else {
                            this.errors = ['Ocurrió un error al procesar el archivo'];
                        }
                    }
                }).catch(error => {
                    if (error.response) {
                        if (error.response.status === 422) {
                            // Errores de validación del formulario
                            if (error.response.data.errors) {
                                this.errors = error.response.data.errors;
                            }
                            
                            // Si hay registros válidos a pesar de los errores
                            if (error.response.data.records) {
                                error.response.data.records.forEach(product => {
                                    if (!this.isDuplicateProduct(product)) {
                                        this.records.push(product);
                                    }
                                });
                            }
                        } else {
                            this.errors = ['Ocurrió un error al procesar el archivo'];
                        }
                    } else {
                        this.errors = ['Ocurrió un error al procesar el archivo'];
                    }
                }).finally(() => {
                    this.loading = false;
                    // Limpiar el input de archivo
                    if (this.$refs.fileInput) {
                        this.$refs.fileInput.value = '';
                    }
                });
            },

            /**
             * Oculta los campos de ingreso manual cuando se está importando
             */
            hideManualInputs() {
                return this.showImportSection;
            },
        },

        computed: {
            formattedErrors() {
                return this.errors.flatMap(error => {
                    // Si el error es un string, lo devolvemos tal cual
                    if (typeof error === 'string') {
                        return [error];
                    }
                    
                    // Si el error tiene la estructura { row: {...}, errors: [...] }
                    if (error.row && error.errors) {
                        const row = error.row;
                        const productName = row.nombre_del_insumo || 'Fila sin nombre';
                        const rowNumber = error.row_number ? `Fila ${error.row_number}` : 'Fila desconocida';
                        
                        return error.errors.map(err => {
                            if (err.includes('fecha') || err === 'El formato de fecha debe ser dd/mm/yyyy') {
                                return `${rowNumber} - El insumo "${productName}" tiene el siguiente error en la columna fecha: "El formato de fecha debe ser dd/mm/yyyy"`;
                            }
                            return `${rowNumber} - El insumo "${productName}" tiene el siguiente error: ${err}`;
                        });
                    }
                    
                    // Para cualquier otro formato de error, lo convertimos a string
                    return [JSON.stringify(error)];
                });
            },
            /**
             * Determina si los campos de ingreso manual deben mostrarse
             */
            showManualInputs() {
                return !this.showImportSection;
            }
        },

        created() {
            this.table_options.headings = {
                'name':                         'Insumo',
                'quantity':                     'Cantidad',
                'warehouse_product_attributes': 'Descripción',
                'id':                           'Acción'
            };
            this.table_options.sortable   = ['name', 'quantity'];
            this.table_options.filterable = ['name', 'quantity'];

            this.getInstitutions();
            this.getWarehouses();
            this.getCurrencies();

            if (this.receptionid) {
                this.loadReception(this.receptionid);
            }
        },
        mounted() {
            var typingTimer;                //timer identifier
            var doneTypingInterval = 1000;  //time in ms, 5 second

            const vm = this;
            $('#productValue').keyup(function() {
                clearTimeout(typingTimer);
                if ($('#productValue').val) {
                    typingTimer = setTimeout(function(){
                        vm.getWarehouseProductRules();
                    }, doneTypingInterval);
                }
            });
        }
    };
</script>
