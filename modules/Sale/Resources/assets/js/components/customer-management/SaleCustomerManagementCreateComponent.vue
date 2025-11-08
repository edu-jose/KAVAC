<template>
<section>
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
                    <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
                </ul>
            </div>
        </div>

        <div class="row">
            <!-- Tipo de Persona -->
            <div class="col-12 col-lg-6">
                <div class="form-group is-required">
                    <label class="control-label h6">Tipo de Persona</label>
                    <div class="row">
                        <div class="col-12 col-sm-6 radio-inline text-center">
                            <label for="personTypeNatural">Natural</label>
                            <div class="custom-control custom-switch">
                                <input type="radio" id="personTypeNatural" value="natural" 
                                    v-model="personType" class="custom-control-input"
                                    @change="handlePersonTypeChange">
                                <label class="custom-control-label" for="personTypeNatural">&nbsp;</label>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 radio-inline text-center">
                            <label for="personTypeLegal">Jurídica</label>
                            <div class="custom-control custom-switch">
                                <input type="radio" id="personTypeLegal" value="legal" 
                                    v-model="personType" class="custom-control-input"
                                    @change="handlePersonTypeChange">
                                <label class="custom-control-label" for="personTypeLegal">&nbsp;</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Identificador -->
            <div class="col-12 col-lg-6">
                <div class="form-group is-required">
                    <label for="identifier">Identificador</label>
                    <select2 :options="identifierOptions" v-model="record.identifier_type"
                             :disabled="!personType" id="identifier"></select2>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Cédula/RIF -->
            <div class="col-12 col-md-6">
                <div class="form-group is-required">
                    <label for="identification_number">Cédula/RIF</label>
                    <input type="text" class="form-control input-sm" id="identification_number"
                           v-model="record.identification_number" 
                           @input="formatIdentificationNumber"
                           maxlength="10"
                           data-toggle="tooltip" title="Número de identificación (solo números, máximo 10 dígitos)">
                </div>
            </div>

            <!-- Nombre/Razón Social -->
            <div class="col-12 col-md-6">
                <div class="form-group is-required">
                    <label for="name">Nombre/Razón Social</label>
                    <input type="text" class="form-control input-sm" id="name"
                           v-model="record.name" maxlength="100"
                           data-toggle="tooltip" title="Nombre o razón social del cliente">
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Dirección Fiscal -->
            <div class="col-12">
                <div class="form-group is-required">
                    <label for="fiscal_address">Dirección Fiscal</label>
                    <textarea class="form-control input-sm" id="fiscal_address"
                              v-model="record.fiscal_address" maxlength="100" rows="3"
                              data-toggle="tooltip" title="Dirección fiscal del cliente"></textarea>
                </div>
            </div>
        </div>

        <!-- Números Telefónicos -->
        <div class="row">
            <div class="col-12">
                <h6 class="card-title">
                    Números Telefónicos 
                    <i class="fa fa-plus-circle cursor-pointer" @click="addPhone()"></i>
                </h6>
                
                <div class="row" v-for="(phone, index) in record.phones" :key="index">
                    <div class="col-12 col-md-2">
                        <div class="form-group is-required">
                            <label :for="'phone_type_' + index">Tipo</label>
                            <select2 :options="phoneTypes" v-model="phone.type"
                                     :id="'phone_type_' + index"></select2>
                        </div>
                    </div>
                    <div class="col-12 col-md-2">
                        <div class="form-group">
                            <label :for="'area_code_' + index">Código de Área</label>
                            <input type="text" class="form-control input-sm" 
                                   :id="'area_code_' + index" v-model="phone.area_code"
                                   @input="formatNumericField(phone, 'area_code')"
                                   maxlength="5" data-toggle="tooltip" title="Código de área (solo números)">
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="form-group is-required">
                            <label :for="'phone_number_' + index">Número</label>
                            <input type="text" class="form-control input-sm" 
                                   :id="'phone_number_' + index" v-model="phone.number"
                                   @input="formatNumericField(phone, 'number')"
                                   maxlength="15" data-toggle="tooltip" title="Número telefónico (solo números)">
                        </div>
                    </div>
                    <div class="col-12 col-md-2">
                        <div class="form-group">
                            <label :for="'phone_extension_' + index">Extensión</label>
                            <input type="text" class="form-control input-sm" 
                                   :id="'phone_extension_' + index" v-model="phone.extension"
                                   @input="formatNumericField(phone, 'extension')"
                                   maxlength="10" data-toggle="tooltip" title="Extensión telefónica (solo números)">
                        </div>
                    </div>
                    <div class="col-12 col-md-2">
                        <div class="form-group">
                            <button class="mt-4 btn btn-sm btn-danger btn-action" type="button" 
                                @click="removePhone(index)" title="Eliminar este teléfono" 
                                data-toggle="tooltip" :disabled="record.phones.length <= 1">
                                <i class="fa fa-minus-circle"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card-footer text-right">
        <div class="row">
            <div class="col-md-3 offset-md-9">
                <button type="button" @click="resetForm()"
                    class="btn btn-default btn-icon btn-round"
                    title="Borrar datos del formulario">
                    <i class="fa fa-eraser"></i>
                </button>
                <button type="button" @click="cancel()"
                    class="btn btn-warning btn-icon btn-round"
                    title="Cancelar y regresar">
                    <i class="fa fa-ban"></i>
                </button>
                <button type="button" @click="validateForm()"
                    class="btn btn-success btn-icon btn-round"
                    :title="customerid ? 'Actualizar registro' : 'Guardar registro'">
                    <i class="fa fa-save"></i>
                </button>
            </div>
        </div>
    </div>
</section>
</template>

<script>
export default {
    props: {
        route_list: {
            type: String,
            default: ''
        },
        route_get: {
            type: String,
            default: ''
        },
        route_save: {
            type: String,
            default: ''
        },
        route_delete: {
            type: String,
            default: ''
        },
        customerid: {
            type: Number,
            default: null
        },
        editIndex: {
            type: Number,
            default: null
        }
    },
    data() {
        return {
            record: {
                id: '',
                identifier_type: '',
                identification_number: '',
                name: '',
                fiscal_address: '',
                phones: []
            },
            personType: '',
            identifierOptions: [],
            phoneTypes: [
                {id: 'mobile', text: 'Móvil'},
                {id: 'phone', text: 'Teléfono'},
                {id: 'fax', text: 'Fax'}
            ],
            errors: [],
            loading: false,
            isEditing: false,
            originalData: null // Para almacenar los datos originales durante la edición
        }
    },
    mounted() {
        if (this.customerid) {
            this.loadCustomerData();
        } else {
            this.addPhone();
            // Establecer el tipo de persona por defecto inmediatamente
            this.personType = 'natural';
            this.handlePersonTypeChange();
        }
        
        // Si hay un índice de edición, cargar los datos para editar
        if (this.editIndex !== null) {
            this.startEditing();
        }
    },
    watch: {
        personType(newVal) {
            if (newVal === 'natural') {
                this.identifierOptions = [
                    {id: 'V', text: 'V'},
                    {id: 'E', text: 'E'}
                ];
                if (!this.record.identifier_type || !['V', 'E'].includes(this.record.identifier_type)) {
                    this.record.identifier_type = 'V';
                }
            } else if (newVal === 'legal') {
                this.identifierOptions = [
                    {id: 'J', text: 'J'},
                    {id: 'G', text: 'G'}
                ];
                if (!this.record.identifier_type || !['J', 'G'].includes(this.record.identifier_type)) {
                    this.record.identifier_type = 'J';
                }
            } else {
                this.identifierOptions = [];
                this.record.identifier_type = '';
            }
        },
        
        // Observar cambios en editIndex para iniciar/terminar edición
        editIndex(newIndex) {
            if (newIndex !== null) {
                this.startEditing();
            } else {
                this.cancelEditing();
            }
        }
    },
    methods: {
        /**
         * Maneja el cambio de tipo de persona inmediatamente
         */
        handlePersonTypeChange() {
            // Forzar la actualización inmediata del identificador
            if (this.personType === 'natural') {
                this.identifierOptions = [
                    {id: 'V', text: 'V'},
                    {id: 'E', text: 'E'}
                ];
                this.record.identifier_type = 'V';
            } else if (this.personType === 'legal') {
                this.identifierOptions = [
                    {id: 'J', text: 'J'},
                    {id: 'G', text: 'G'}
                ];
                this.record.identifier_type = 'J';
            }
        },

        /**
         * Formatea campos numéricos para aceptar solo números
         */
        formatNumericField(phone, field) {
            // Remover cualquier caracter que no sea número
            phone[field] = phone[field].replace(/[^\d]/g, '');
        },

        /**
         * Formatea el número de identificación para aceptar solo números
         */
        formatIdentificationNumber() {
            // Remover cualquier caracter que no sea número
            this.record.identification_number = this.record.identification_number.replace(/[^\d]/g, '');
        },

        /**
         * Método que permite la edición de un cliente existente
         */
        startEditing() {
            if (this.editIndex === null) return;
            
            // Guardar los datos originales para poder cancelar la edición
            this.originalData = JSON.parse(JSON.stringify(this.record));
            
            // Cargar los datos del cliente a editar
            this.loadCustomerDataForEditing();
            this.isEditing = true;
        },
        
        /**
         * Carga los datos del cliente para editar
         */
        async loadCustomerDataForEditing() {
            try {
                // Usar el nuevo endpoint API
                const url = this.route_get.replace('{id}', this.editIndex);
                const response = await axios.get(url);
                const customer = response.data;

                this.record = {
                    id: customer.id,
                    identifier_type: customer.identifier_type,
                    identification_number: customer.identification_number,
                    name: customer.name,
                    fiscal_address: customer.fiscal_address,
                    phones: customer.phones || []
                };
                
                // Determinar el tipo de persona basado en el identifier_type
                if (['V', 'E'].includes(this.record.identifier_type)) {
                    this.personType = 'natural';
                } else if (['J', 'G'].includes(this.record.identifier_type)) {
                    this.personType = 'legal';
                }
                
                // Asegurar que haya al menos un teléfono
                if (this.record.phones.length === 0) {
                    this.addPhone();
                }
            } catch (error) {
                console.error('Error loading customer data for editing:', error);
                this.errors.push('Error al cargar los datos del cliente para editar');
                
                // Debug: mostrar información del error
                if (error.response) {
                    console.error('Error response:', error.response);
                    console.error('Error status:', error.response.status);
                    console.error('Error data:', error.response.data);
                }
            }
        }, 

        /**
         * Cancela la edición y restaura los datos originales
         */
        cancelEditing() {
            if (this.originalData) {
                this.record = JSON.parse(JSON.stringify(this.originalData));
                
                // Restaurar el tipo de persona
                if (['V', 'E'].includes(this.record.identifier_type)) {
                    this.personType = 'natural';
                } else if (['J', 'G'].includes(this.record.identifier_type)) {
                    this.personType = 'legal';
                }
            }
            
            this.isEditing = false;
            this.originalData = null;
            this.$emit('edit-cancelled');
        },
        
        /**
         * Confirma la edición y guarda los cambios
         */
        confirmEditing() {
            this.validateForm();
        },
        
        async loadCustomerData() {
            try {
                const response = await axios.get(`/sale/customer-management/${this.customerid}`);
                const customer = response.data;
                
                this.record = {
                    id: customer.id,
                    identifier_type: customer.identifier_type,
                    identification_number: customer.identification_number,
                    name: customer.name,
                    fiscal_address: customer.fiscal_address,
                    phones: customer.phones || []
                };
                
                // Determinar el tipo de persona basado en el identifier_type
                if (['V', 'E'].includes(this.record.identifier_type)) {
                    this.personType = 'natural';
                } else if (['J', 'G'].includes(this.record.identifier_type)) {
                    this.personType = 'legal';
                }
                
                // Asegurar que haya al menos un teléfono
                if (this.record.phones.length === 0) {
                    this.addPhone();
                }
            } catch (error) {
                console.error('Error loading customer data:', error);
                this.errors.push('Error al cargar los datos del cliente');
            }
        },
        
        addPhone() {
            if (!Array.isArray(this.record.phones)) {
                this.record.phones = [];
            }
            
            this.record.phones.push({
                type: 'mobile',
                area_code: '',
                number: '',
                extension: ''
            });
        },
        
        removePhone(index) {
            if (this.record.phones.length > 1) {
                this.record.phones.splice(index, 1);
            }
        },
        
        validateForm() {
            this.errors = [];
            
            // Validar tipo de persona
            if (!this.personType) {
                this.errors.push('Debe seleccionar un tipo de persona');
            }
            
            // Validar identificador
            if (!this.record.identifier_type) {
                this.errors.push('El campo Identificador es obligatorio');
            }
            
            // Validar cédula/RIF
            if (!this.record.identification_number) {
                this.errors.push('El campo Cédula/RIF es obligatorio');
            } else if (!/^\d{1,10}$/.test(this.record.identification_number)) {
                this.errors.push('La cédula/RIF debe contener solo números y máximo 10 dígitos');
            }
            
            // Validar nombre/razón social
            if (!this.record.name) {
                this.errors.push('El campo Nombre/Razón Social es obligatorio');
            } else if (this.record.name.length > 100) {
                this.errors.push('El nombre/razón social no puede exceder los 100 caracteres');
            }
            
            // Validar dirección fiscal
            if (!this.record.fiscal_address) {
                this.errors.push('El campo Dirección Fiscal es obligatorio');
            } else if (this.record.fiscal_address.length > 100) {
                this.errors.push('La dirección fiscal no puede exceder los 100 caracteres');
            }
            
            // Validar teléfonos
            this.record.phones.forEach((phone, index) => {
                const phoneNumber = index + 1;
                
                if (!phone.type) {
                    this.errors.push(`El tipo de teléfono ${phoneNumber} es obligatorio`);
                }
                
                if (!phone.number) {
                    this.errors.push(`El número de teléfono ${phoneNumber} es obligatorio`);
                } else if (!/^\d+$/.test(phone.number)) {
                    this.errors.push(`El número de teléfono ${phoneNumber} debe contener solo números`);
                }
                
                if (phone.area_code && !/^\d*$/.test(phone.area_code)) {
                    this.errors.push(`El código de área del teléfono ${phoneNumber} debe contener solo números`);
                }
                
                if (phone.extension && !/^\d*$/.test(phone.extension)) {
                    this.errors.push(`La extensión del teléfono ${phoneNumber} debe contener solo números`);
                }
            });
            
            if (this.errors.length === 0) {
                if (this.isEditing) {
                    // Si está en modo edición, guardar cambios
                    this.saveRecord();
                } else {
                    // Confirmación adicional para actualización normal
                    if (this.customerid) {
                        if (confirm('¿Está seguro de actualizar los datos del cliente?')) {
                            this.saveRecord();
                        }
                    } else {
                        this.saveRecord();
                    }
                }
            }
        },
        
        async saveRecord() {
            if (this.loading) return;
            
            this.loading = true;
            this.errors = [];
            
            try {
                let url = this.route_save;
                let method = 'post';
                
                if (this.record.id) {
                    url = `${url}/${this.record.id}`;
                    method = 'put';
                }
                
                const response = await axios[method](url, this.record);
                
                // Si está en modo edición, emitir evento de edición completada
                if (this.isEditing) {
                    this.$emit('edit-completed', this.record);
                    this.isEditing = false;
                    this.originalData = null;
                } else {
                    // Redirigir después de guardar
                    setTimeout(() => {
                        window.location.href = '/sale/customer-management';
                    }, 1000);
                }
                
            } catch (error) {
                this.handleSaveError(error);
            } finally {
                this.loading = false;
                window.location.href = '/sale/customer-management';    
            }
        },
        
        showSuccessMessage(message) {
            alert(message);
        },
        
        handleSaveError(error) {
            if (error.response && error.response.status === 422) {
                const validationErrors = error.response.data.errors;
                for (const field in validationErrors) {
                    validationErrors[field].forEach(message => {
                        this.errors.push(message);
                    });
                }
            } else if (error.response && error.response.data && error.response.data.message) {
                this.errors.push(error.response.data.message);
            } else if (error.request) {
                this.errors.push('Error de conexión. Por favor, verifique su conexión a internet.');
            } else {
                this.errors.push('Error inesperado al guardar el registro');
            }
            
            window.scrollTo(0, 0);
            console.error('Error saving customer:', error);
        },
        
        resetForm() {
            // Si está en modo edición, cancelar la edición
            if (this.isEditing) {
                this.cancelEditing();
                return;
            }
            
            this.record = {
                id: '',
                identifier_type: '',
                identification_number: '',
                name: '',
                fiscal_address: '',
                phones: []
            };
            this.personType = 'natural';
            this.errors = [];
            
            // Forzar la actualización del identificador
            this.handlePersonTypeChange();
            this.addPhone();
        },
        
        cancel() {
            // Si está en modo edición, cancelar la edición
            if (this.isEditing) {
                this.cancelEditing();
                return;
            }
            
            window.location.href = '/sale/customer-management';
        }
    }
};
</script>