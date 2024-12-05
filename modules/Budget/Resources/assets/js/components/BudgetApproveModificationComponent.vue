<template>
    <div>
        <button
            @click="showModal(`approve_modification${id}`, $event)"
            class="btn btn-success btn-xs btn-icon btn-action"
            title="Aprobar registro"
            data-toggle="tooltip"
            type="button"
        >
            <i class="fa fa-check"></i>
        </button>
        <div
            class="modal fade text-left"
            tabindex="-1"
            role="dialog"
            :id="`approve_modification${id}`"
            data-backdrop="static"
        >
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <!-- modal-header -->
                    <div class="modal-header">
                        <button
                            type="reset"
                            class="close"
                            data-dismiss="modal"
                            aria-label="Close"
                            @click="reset"
                        >
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-ui-check success ico-x2"></i>
                            APROBAR LA MODIFICACION PRESUPUESTARIA {{ code }}
                        </h6>
                    </div>
                    <!-- Final modal-header -->
                    <!-- modal-body -->
                    <div class="modal-body">
                        <div class="alert alert-danger" v-if="errors.length > 0">
                            <div class="alert-icon">
                                <i class="now-ui-icons objects_support-17"></i>
                            </div>
                            <strong>¡Atención!</strong>
                            Debe verificar los siguientes errores antes de continuar:
                            <button
                                type="button"
                                class="close"
                                data-dismiss="alert"
                                aria-label="Close"
                                @click.prevent="errors = []"
                            >
                                <span aria-hidden="true">
                                    <i class="now-ui-icons ui-1_simple-remove"></i>
                                </span>
                            </button>
                            <ul>
                                <li
                                    v-for="(error, index) in errors"
                                    :key="index"
                                >
                                    {{ error }}
                                </li>
                            </ul>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label">
                                        Código de la modificación
                                    </label>
                                    <input
                                        type="tetx"
                                        class="form-control"
                                        v-model="code"
                                        readonly
                                    >
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label">
                                        Fecha de aprobación
                                    </label>
                                    <input
                                        type="date"
                                        class="form-control is-required"
                                        v-model="record.approved_date"
                                    >
                                </div>
                            </div>
                        </div>
                        <!-- Final modal-body -->
                        <!-- modal-footer -->
                        <div class="modal-footer">
                            <div class="form-group">
                                <button
                                    type="button"
                                    class="
                                        btn btn-default btn-sm btn-round btn-modal-close
                                    "
                                    data-dismiss="modal"
                                    @click="reset"
                                >
                                    Cerrar
                                </button>
                                <button
                                    type="button" 
                                    class="btn btn-primary btn-sm btn-round btn-modal-save"
                                    @click="approveModification('AP', id)
                                "
                                >
                                    Aceptar
                                </button>
                            </div>
                        </div>
                        <!-- Final modal-footer -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    props: {
            id: {
                type: Number,
                required: true,
            },
            code: {
                type: String,
                required: false,
            },
            approved_at: {
                type: String,
                required: false,
            },
            fiscal_year: {
                type: String,
                required: false,
            },
        },
    data() {
        return {
            record :{
                id : '',
                approved_date : '',
            },
            errors : [],
        };
    },
    created(){
    },
    async mounted() {
    },
    methods: {
        /**
         * Método que borra todos los datos del formulario
         */
        reset() {
            const vm = this;
            vm.record = {
                id : '',
                approved_date : '',
            },
            vm.errors = [];
        },

        /**
         * Método que obtiene el mensaje de alerta a tomar en cuenta antes de seguir con el proceso de aprobación
         * 
         * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javierrupe19@gmail.com>
         * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
         *
         *  @param {type: String} status  estatus aprobado 'AP'
         *  @param {type: Integer} id  entero que representa el id del registro
         */
        approveModification(status, id) {
            const vm = this;
            vm.errors = [];

            if (vm.record.approved_date < vm.approved_at.slice(0, 10)) {
                vm.errors.push(
                    "La fecha de aprobación debe ser mayor o igual a la fecha de creación."
                );
            }

            if (vm.record.approved_date.slice(0, 4) != vm.fiscal_year) {
                vm.errors.push(
                    "La fecha de aprobación debe de estar dentro del año fiscal."
                );
            }

            if (vm.errors.length > 0) {
                return;
            } else {
                const url = vm.setUrl(
                    `${window.app_url}/budget/modifications/change-status/${id}`
                );
                vm.record.id = id;
                bootbox.confirm({
                    title: "¿Aprobar Modificacion Presupuestaria?",
                    message: "¿Esta seguro de aprobar esta modificacion?. Una vez aprobado el registro no se podrá modificar y/o eliminar.",
                    buttons: {
                            cancel: {
                                label: '<i class="fa fa-times"></i> Cancelar',
                                className: "btn btn-default btn-sm btn-round",
                            },
                            confirm: {
                                label: '<i class="fa fa-check"></i> Confirmar',
                                className: "btn btn-primary btn-sm btn-round",
                            },
                    },

                    callback: function(result) {
                        if (result) {
                            vm.loading = true;
                            axios.post(url, { status: status, approved_date: vm.record.approved_date }).then(response => {
                                if (response.status == 200){
                                    location.reload();
                                    vm.showMessage('custom', '¡Éxito!', 'success', 'screen-ok', 'Modificación presupuestaria aprobada');
                                }
                            }).catch(error => {
                                if (typeof(error.response) !="undefined") {
                                    if (error.response.status == 403) {
                                        vm.showMessage(
                                            'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                                        );
                                    }
                                    if (error.response.status == 500) {
                                        const messages = error.response.data.message;
                                        vm.showMessage(
                                            messages.type, messages.title, messages.class, messages.icon, messages.text
                                        );
                                    }
                                }
                                vm.errors = [];
                                for (let index in error.response.data.errors) {
                                    if (error.response.data.errors[index]) {
                                        vm.errors.push(error.response.data.errors[index][0]);
                                    }
                                }
                            });
                            vm.loading = false;
                        }
                    }
                });
            }
        },

        /**
         * Método que permite levantar el modal
         * @param {*} modal_id 
         * @param {*} event 
         */
        async showModal(modal_id, event){
            const vm = this;
            event.preventDefault();
            vm.loading = true;
            if (modal_id) {
                $(`#${modal_id}`).modal('show');
            }
            vm.loading = false;
        },
    },
};
</script>
