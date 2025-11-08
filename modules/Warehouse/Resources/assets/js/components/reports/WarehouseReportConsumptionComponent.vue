<template>
    <section id="WarehouseReportConsumptionForm">
        <div class="card-body">
            <form-errors :listErrors="errors">
            </form-errors>
            <div class="row">
                <div class="col-md-12">
                    <strong>Filtros</strong>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Organización:</label>
                        <select2 :options="institutions"
                        @input="getWarehouses"
                        v-model="record.institution_id">
                    </select2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Almacén:</label>
                        <select2 :options="warehouses"
                        @input="getWarehouseProducts"
                        :disabled="warehouses.length == 0"
                                 v-model="record.warehouse_id"></select2>
                    </div>
                </div>
                <div class="col-md-4" id="helpDepartment">
                    <div class="form-group is-required">
                        <label>Departamento</label>
                        <select2 :options="departments"
                        v-model="record.department_id">
                        </select2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label >Insumo:</label>
                        <v-multiselect data-toggle="tooltip"
                        title="Indique los insumos"
                        track_by="text"
                        :hide_selected="false"
                        :options="warehouse_products"
                        v-model="record.warehouse_product_ids"
                        style="margin-top: -26px"
                        :disabled="warehouse_products.length == 0">
                    </v-multiselect>
                    </div>
                </div>
            </div>

            <div class="row text-center">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Busqueda por periodo</label>
                        <div class="col-12">
                            <div class="custom-control custom-switch">
                                <input type="radio" class="custom-control-input sel_type_search" id="sel_search_date"
                                        name="type_search" value="date" v-model="record.type_search">
                                <label class="custom-control-label" for="sel_search_date"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class=" form-group">
                        <label>Busqueda por mes</label>
                        <div class="col-12">
                            <div class="custom-control custom-switch">
                                <input type="radio" class="custom-control-input sel_type_search" id="sel_search_mes"
                                        name="type_search" value="mes" v-model="record.type_search">
                                <label class="custom-control-label" for="sel_search_mes"></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-show="this.record.type_search == 'mes'">
                <div class="row justify-content-center">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Mes:</label>
                            <select2 :options="mes"
                                     v-model="record.mes_id"></select2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Año:</label>
                            <input type="number" data-toggle="tooltip" min="0"
                                       title="Indique el año de busqueda"
                                       class="form-control input-sm" v-model="record.year">
                        </div>
                    </div>
                </div>
            </div>

            <div v-show="this.record.type_search == 'date'">
                <div class="row justify-content-center">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Desde:</label>
                            <div class="input-group input-sm">
                                <span class="input-group-addon">
                                    <i class="now-ui-icons ui-1_calendar-60"></i>
                                </span>
                                <input type="date" data-toggle="tooltip"
                                       title="Indique la fecha minima de busqueda"
                                       class="form-control input-sm" v-model="record.start_date">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Hasta:</label>
                            <div class="input-group input-sm">
                                <span class="input-group-addon">
                                    <i class="now-ui-icons ui-1_calendar-60"></i>
                                </span>
                                <input type="date" data-toggle="tooltip"
                                       title="Indique la fecha maxima de busqueda"
                                       class="form-control input-sm" v-model="record.end_date">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <button type="button" @click="getConsumptionData"
                            class='btn btn-sm btn-info float-right' data-toggle="tooltip"
                            title="Realizar la búsqueda de acuerdo a los filtros establecidos en el formulario"
                    >
                        Realizar búsqueda
                        <i class="fa fa-search"></i>
                    </button>
                </div>
            </div>

            <v-client-table :columns="columns" :data="records" :options="table_options">
                <div slot="product_name" slot-scope="props">
                    <span>
                        {{ props.row.product_name }}
                    </span>
                </div>
                <div slot="consumed_amount" slot-scope="props">
                    <span>
                        {{ props.row.consumed_amount }}
                    </span>
                </div>
                 <div slot="unit_of_measure" slot-scope="props">
                        <span >
                            {{ props.row.unit_of_measure }}
                        </span>
                </div>
                                <div slot="warehouse" slot-scope="props">
                    <span>
                        {{ props.row.warehouse }}
                    </span>
                </div>
            </v-client-table>
        </div>
<div class="card-footer text-right">
            <button class="btn btn-primary btn-sm" data-toggle="tooltip" v-has-tooltip title="Generar Reporte"
                @click.prevent="createReport('pdf')">
                <span>Generar reporte</span>
                <i class="fa fa-print"></i>
            </button>
            <button class="btn btn-primary btn-sm"
            @click.prevent="exportReport" data-toggle="tooltip" v-has-tooltip
                title="Exportar Reporte">
                Exportar Reporte
                <i class="fa fa-file-excel-o"></i>
            </button>
        </div>
    </section>
</template>

<script>
import axios from 'axios';

    export default {
        data() {
            return {
                record: {
                    id: '',
                    warehouse_id: '',

                    type_search: '',
                    institution_id: '',
                    department_id: '',
                    warehouse_product_ids: [],

                    mes_id: '',
                    year: '',
                    start_date: '',
                    end_date: ''
                },
                consumptionData: [],
                warehouses: [],
                departments: [],
                warehouse_products: [],
                records: [],
                errors: [],
                columns: [
                    'product_name',
                    'consumed_amount',
                    'unit_of_measure',
                    'warehouse',
                ],
                mes: [
                    {"id":"0","text":"Todos"},
                    {"id":1,"text":"Enero"},
                    {"id":2,"text":"Febrero"},
                    {"id":3,"text":"Marzo"},
                    {"id":4,"text":"Abril"},
                    {"id":5,"text":"Mayo"},
                    {"id":6,"text":"Junio"},
                    {"id":7,"text":"Julio"},
                    {"id":8,"text":"Agosto"},
                    {"id":9,"text":"Septiempre"},
                    {"id":10,"text":"Octubre"},
                    {"id":11,"text":"Noviembre"},
                    {"id":12,"text":"Diciembre"}
                ],
                institutions: []
            }
        },
        props: {
            institution_id: {
                type: Number,
                required: true,
                default: null
            },
        },
        methods: {
            async getConsumptionData() {
                const vm = this;
                vm.loading = true;
                let fields = {};
                for (let index in this.record) {
                    fields[index] = this.record[index];
                }
                vm.loading = true;

                try {
                    const response = await axios.post(`${window.app_url}/warehouse/reports/get-consumption-data`, fields);
                    if (response.status == 200) {
                        vm.records = response.data.records;
                    }
                } catch (error) {
                    vm.errors = [];
                     for(const index in error.response.data.errors) {
                        if (error.response.data.errors[index]) {
                            vm.errors.push(error.response.data.errors[index][0]);
                        }
                }
                } finally {
                    vm.loading = false;
                }
            },
            async exportReport() {

                const vm = this;
                vm.loading = true;
                let fields = {};
                for (let index in this.record) {
                    fields[index] = this.record[index];
                }

                try {
                    const response = await axios.post(
                        `${window.app_url}/warehouse/reports/consumption/export`, 
                        fields, 
                        {
                            responseType: 'blob', 
                        }
                    );

                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = url;

                    link.setAttribute('download', 'reporte_consumo.xlsx'); 
                    
                    document.body.appendChild(link);
                    link.click();

                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(url);
                } catch (error) {
                    vm.errors = [];
                    if (error.response && error.response.data && error.response.data.errors) {
                        vm.errors = Object.values(error.response.data.errors).flat();
                    } else {
                        vm.errors.push('Ocurrio un error inesperado.');
                    }
                } finally {
                    vm.loading = false;
                }
            },
            async getDepartments(id) {
            let vm = this;
            vm.departments = [];
            if (typeof(vm.record.institution_id) !== "undefined" && vm.record.institution_id !== '') {
                await axios.get(`/get-departments/${vm.record.institution_id}`).then(response => {
                    /** Obtiene los departamentos */
                    vm.departments = (typeof(id) === "undefined" || !id)
                    ? response.data
                    : response.data.filter((department) => {
                    return department.id === id;
                    });
                }).catch(error => {
                    console.error(error);
                });
            }
        },
            getWarehouseProducts() {
                const vm = this;
                vm.warehouse_products = [];

                                axios.get(`/warehouse/get-warehouse-full-info-products-vue-select/${vm.record.warehouse_id}`).then(response => {
                        vm.warehouse_products = response.data.records;
                    });    
            },
            reset() {
                this.record = {
                    id: '',
                    warehouse_product_id: '',
                    warehouse_id: '',

                    type_search: '',
                    institution_id: '',

                    mes_id: '',
                    year: '',
                    start_date: '',
                    end_date: ''
                }
            },
            createReport(current) {
                const vm = this;
                vm.loading = true;
                var fields = {};
                for (var index in this.record) {
                    fields[index] = this.record[index];
                }
                fields["current"] = current;
                axios.post("/warehouse/reports/consumption/create", fields).then(response => {
                    if (response.data.result == false)
                        location.href = response.data.redirect;
                    else if (typeof(response.data.redirect) !== "undefined") {
                        window.open(response.data.redirect, '_blank');
                    }
                    else {
                        vm.reset();
                    }
                    vm.loading = false;
                }).catch(error => {
                    if (typeof(error.response) != "undefined") {
                        let errors = [];
                        
                        for (var index in error.response.data.errors) {
                            if (error.response.data.errors[index]) {
                                vm.errors.push(error.response.data.errors[index][0]);
                            }
                        }
                    }
                    vm.loading = false;
                });
            },
            loadInventoryProduct(current) {
                const vm = this;
                vm.loading = true;
                var fields = {};
                for (var index in this.record) {
                    fields[index] = this.record[index];
                }
                fields["current"] = current;
                axios.post("/warehouse/reports/inventory-products/vue-list", fields).then(response => {
                    if (typeof(response.data.records) != "undefined") {
                        vm.records = response.data.records;
                    }
                    vm.loading = false;
                }).catch(error => {
                    if (typeof(error.response) != "undefined") {
                    }
                    vm.loading = false;
                });
            },
        },
        created() {
            this.table_options.headings = {
                    'product_name': 'Producto',
                    'consumed_amount': 'Cantidad consumida',
                    'unit_of_measure': 'Unidad de medida',
                    'warehouse': 'Almacén',
            };
            this.table_options.sortable = [
                'product_name',
                'consumed_amount',
                'unit_of_measure',
            ];
            this.table_options.filterable = [
                'product_name',
                'consumed_amount',
                'unit_of_measure',
            ];
        },
        async mounted() {
            const vm = this;
            this.switchHandler('type_search');
            await vm.getInstitutions();
            await vm.getWarehouses();
            vm.record.institution_id = vm.institution_id;
            await vm.getDepartments();
        }
    };
</script>