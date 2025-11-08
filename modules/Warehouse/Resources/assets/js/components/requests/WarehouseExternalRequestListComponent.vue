<template>
  <v-client-table :columns="columns" :data="records" :options="table_options">
    <div slot="code" slot-scope="props" class="text-center">
      <span>
        {{ props.row.code }}
      </span>
    </div>
    <div slot="name" slot-scope="props">
      <span>
        {{ props.row.first_name + " " + props.row.last_name }}
      </span>
    </div>
    <div slot="warehouse" slot-scope="props">
      <span>
        {{ props.row.warehouse ? props.row.warehouse.name : "N/A" }}
      </span>
    </div>
    <div slot="date" slot-scope="props">
      <span>
        {{
          props.row.date
            ? format_date(props.row.date)
            : format_date(props.row.created_at)
        }}
      </span>
    </div>
    <div slot="id" slot-scope="props" class="text-center">
      <div class="d-inline-flex">
        <warehouse-ext-req-info
          :route_list="
            app_url + '/warehouse/external/requests/vue-info/' + props.row.id
          "
          :infoid="props.row.id"
        >
        </warehouse-ext-req-info>

        <template
          v-if="
            lastYear && format_date(props.row.created_at, 'YYYY') <= lastYear
          "
        >
          <button
            class="btn btn-warning btn-xs btn-icon btn-action"
            type="button"
            disabled
          >
            <i class="fa fa-edit"></i>
          </button>
          <button
            class="btn btn-danger btn-xs btn-icon btn-action"
            type="button"
            disabled
          >
            <i class="fa fa-trash-o"></i>
          </button>
        </template>
        <template v-else>
          <a
            class="btn btn-primary btn-xs btn-icon"
            title="Imprimir registro"
            data-toggle="tooltip"
            target="_blank"
            :href="warehouse_external_request_pdf + props.row.id"
            v-has-tooltip
          >
            <i class="fa fa-print"></i>
          </a>
          <button
            @click="editForm(props.row.id)"
            class="btn btn-warning btn-xs btn-icon btn-action"
            title="Modificar registro"
            data-toggle="tooltip"
            type="button"
            :disabled="props.row.state != 'Pendiente'"
          >
            <i class="fa fa-edit"></i>
          </button>
          <button
            @click="deleteRecord(props.row.id, '')"
            class="btn btn-danger btn-xs btn-icon btn-action"
            title="Eliminar registro"
            data-toggle="tooltip"
            type="button"
            :disabled="props.row.state != 'Pendiente'"
          >
            <i class="fa fa-trash-o"></i>
          </button>
        </template>
      </div>
    </div>
  </v-client-table>
</template>

<script>
export default {
  data() {
    return {
      records: [],
      warehouse_external_request_pdf: `${window.app_url}/warehouse/external/requests/pdf/`,
      lastYear: "",
      columns: ["code", "name", "warehouse", "state", "date", "id"],
    };
  },
  created() {
    this.table_options.headings = {
      code: "Código",
      name: "Solicitante",
      warehouse: "Almacén",
      state: "Estado de la solicitud",
      date: "Fecha de la solicitud",
      id: "Acción",
    };
    this.table_options.sortable = [
      "code",
      "name",
      "state",
      "date",
      "warehouse",
      "created_at",
    ];
    this.table_options.filterable = [
      "code",
      "name",
      "state",
      "date",
      "warehouse",
      "created_at",
    ];
  },
  async mounted() {
    console.log(this.route_list);
    this.initRecords(this.route_list, "");
    const vm = this;
    await vm.queryLastFiscalYear();
  },
  methods: {
    /**
     * Inicializa los datos del formulario
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve | roldandvg@gmail.com>
     */
    prepareText(text) {
      return text.substr(3, text.length - 4);
    },
    reset() {},
    deleteRecord(id, url) {
      url = url ? url : this.route_delete;
      // Reemplaza {request} por el id
      if (url.indexOf("{request}") >= 0) {
        url = url.replace("{request}", id);
      } else {
        url += "/" + id;
      }
      url = this.setUrl(url);

      const vm = this;
      bootbox.confirm({
        title: "¿Eliminar registro?",
        message: "¿Está seguro de eliminar este registro?",
        buttons: {
          cancel: { label: '<i class="fa fa-times"></i> Cancelar' },
          confirm: { label: '<i class="fa fa-check"></i> Confirmar' },
        },
        callback: function (result) {
          if (result) {
            const recordIndex = vm.records.findIndex((item) => item.id === id);
            if (recordIndex === -1) {
              vm.showMessage(
                "custom",
                "Alerta!",
                "warning",
                "screen-error",
                "Registro no encontrado."
              );
              return;
            }
            axios
              .delete(url)
              .then((response) => {
                if (typeof response.data.error !== "undefined") {
                  vm.showMessage(
                    "custom",
                    "Alerta!",
                    "warning",
                    "screen-error",
                    response.data.message
                  );
                  return false;
                }
                vm.records.splice(recordIndex, 1);
                vm.showMessage("destroy");
              })
              .catch((error) => {
                vm.logs("mixins.js", 498, error, "deleteRecord");
              });
          }
        },
      });
    },
  },
};
</script>
