<template>
    <span>
      <!-- Botón que activa el modal -->
      <button
        @click="showModal(`currency_report_export${id}`, $event)"
        class="btn btn-sm btn-primary btn-custom btn-print-general" style="margin-right: 1.5rem;"
        title="Generar registro"
        data-toggle="tooltip"
        type="button"
      >
        <i class="fa fa-file-excel-o"></i>
      </button>
  
      <!-- Modal -->
      <div
        class="modal fade text-left"
        tabindex="-1"
        role="dialog"
        :id="`currency_report_export${id}`"
        data-backdrop="static"
      >
        <div class="modal-dialog vue-crud" role="document">
          <div class="modal-content">
            <!-- Encabezado del modal -->
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
                <i class="icofont icofont-ui-file success ico-x2"></i>
                Generar reporte
              </h6>
            </div>
  
            <!-- Cuerpo del modal -->
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
                  <li v-for="(error, index) in errors" :key="index">
                    {{ error }}
                  </li>
                </ul>
              </div>
  
              <!-- Listado de monedas -->
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label class="control-label">Seleccione la moneda:</label>
                    <select2
                        :options="currencies"
                        v-model="selectedCurrency"
                        data-toggle="tooltip"
                        title="Seleccione una cuenta presupuestaria"
                    ></select2>
                  </div>
                </div>
              </div>
            </div>
  
            <!-- Pie del modal -->
            <div class="modal-footer">
              <div class="form-group">
                <button
                  type="button"
                  class="btn btn-default btn-sm btn-round btn-modal-close"
                  data-dismiss="modal"
                  @click="reset"
                >
                  Cerrar
                </button>
                <button
                  type="button"
                  class="btn btn-primary btn-sm btn-round btn-modal-save"
                  @click="generateReport"
                  :disabled="!selectedCurrency || loadingCurrencies"
                >
                  <span v-if="loadingCurrencies">
                    <i class="fa fa-spinner fa-spin"></i> Cargando...
                  </span>
                  <span v-else>Generar Reporte</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </span>
  </template>
  
  <script>
  export default {
    props: {
      id: {
        type: Number,
        required: true,
      },
      report_url: {
        type: String,
        required: true,
      },
    },
    data() {
      return {
        currencies: [],
        selectedCurrency: null,
        loadingCurrencies: false,
        errors: [],
      };
    },
    async created() {
      const vm = this;
      await vm.getCurrencies();

      vm.currencies.unshift({
        id: "",
        text: "Seleccione...",
      });
    },
    methods: {
      /**
       * Genera el reporte con la moneda seleccionada
       */
      generateReport() {
        const vm = this;
        if (!vm.selectedCurrency) {
          vm.errors = ["Debe seleccionar una moneda"];
          return;
        }
  
        const url = `${vm.report_url}?currency=${vm.selectedCurrency}`;
        window.open(url, "_blank");
        $(`#currency_report_export${vm.id}`).modal("hide");
        vm.reset()
      },
  
      /**
       * Muestra el modal
       */
      async showModal(modal_id, event) {
        const vm = this;
        event.preventDefault();
        if (modal_id) {
          $(`#${modal_id}`).modal("show");
        }
      },
  
      /**
       * Reinicia el estado del componente
       */
      reset() {
        const vm = this;
        vm.selectedCurrency = null;
        vm.errors = [];
      },
    },
  };
  </script>
  
  <style scoped>
  /* Estilos personalizados si es necesario */
  </style>