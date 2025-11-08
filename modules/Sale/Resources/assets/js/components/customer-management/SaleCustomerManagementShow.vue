<template>
    <div>
        <div id="SaleCustomerInfo" class="modal fade" tabindex="-1" role="dialog" 
             aria-labelledby="SaleCustomerInfoModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <!-- modal-header -->
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="fa fa-user ico-2x"></i>
                            Información Detallada del Cliente
                        </h6>
                    </div>
                    <!-- Final modal-header -->

                    <!-- modal-body -->
                    <div class="modal-body">
                        <div class="tab-content">
                            <div class="tab-pane active" id="customer-general" role="tabpanel">
                                <h6 class="text-center">Datos básicos del cliente</h6><br>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <strong>Tipo de Persona:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    <span v-if="record.identifier_type in ['V', 'E']" class="badge badge-info">
                                                        Natural
                                                    </span>
                                                    <span v-else class="badge badge-primary">
                                                        Jurídica
                                                    </span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <strong>Identificador:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12 font-weight-bold">
                                                    {{ record.identifier_type }}-{{ record.identification_number }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <strong>Nombre/Razón Social:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12 h6">
                                                    {{ record.name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <strong>Dirección Fiscal:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ record.fiscal_address }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <h6 class="text-center">Información de Contacto</h6><br>
                                
                                <div class="row" v-if="record.phones && record.phones.length > 0">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <strong>Teléfonos:</strong>
                                            <div v-for="(phone, index) in record.phones" :key="index" 
                                                 class="row" style="margin: 5px 0">
                                                <span class="col-md-12">
                                                    <i class="fa fa-phone"></i>
                                                    {{ phoneTypeText(phone.type) }}: 
                                                    {{ phone.area_code }}-{{ phone.number }}
                                                    <span v-if="phone.extension" class="text-muted">
                                                        Ext. {{ phone.extension }}
                                                    </span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" v-else>
                                    <div class="col-md-12">
                                        <div class="alert alert-warning text-center">
                                            No hay números telefónicos registrados
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Final modal-body -->

                    <!-- modal-footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-sm btn-round btn-modal-close" 
                                data-dismiss="modal">
                            Cerrar
                        </button>
                        <button type="button" class="btn btn-warning btn-sm btn-round" 
                                @click="editCustomer" data-dismiss="modal">
                            <i class="fa fa-edit"></i> Editar
                        </button>
                    </div>
                    <!-- Final modal-footer -->
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
                identifier_type: '',
                identification_number: '',
                name: '',
                fiscal_address: '',
                phones: []
            },
            id: ''
        }
    },
    methods: {
        /**
         * Redirige a la edición del cliente
         */
        editCustomer() {
            if (this.record.id) {
                // Buscar la ruta de edición en el componente padre
                const parent = this.$parent;
                if (parent && parent.route_edit) {
                    const editUrl = parent.route_edit.replace('{id}', this.record.id);
                    window.location.href = editUrl;
                }
            }
        },

        /**
         * Retorna el texto del tipo de teléfono
         */
        phoneTypeText(type) {
            const types = {
                'mobile': 'Móvil',
                'phone': 'Teléfono',
                'fax': 'Fax'
            };
            return types[type] || type;
        }
    }
}
</script>