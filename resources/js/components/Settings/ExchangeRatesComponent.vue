<template>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 mt-2 mb-2 text-center">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary"
           href="javascript:void(0)" title="Registro de tipos de cambio"
           data-toggle="tooltip" @click="addRecord('add_exchange_rate', 'exchange-rates', $event)">
            <i class="icofont icofont-random ico-3x"></i>
            <span>Tipos de Cambio</span>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" id="add_exchange_rate">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-random inline-block"></i>
                            Tipos de Cambio
                        </h6>
                    </div>
                    <div class="modal-body">
                        <form-errors :listErrors="errors"></form-errors>
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group is-required">
                                    <label>De:</label>
                                    <select2 :options="currencies"
                                             v-model="record.from_currency_id"></select2>
                                    <input type="hidden" v-model="record.id">
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group is-required">
                                    <label>A:</label>
                                    <select2 :options="currencies" v-model="record.to_currency_id"></select2>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-3">
                                <div class="form-group is-required">
                                    <label>Fecha inicio:</label>
                                    <input type="date" class="form-control input-sm" v-model="record.start_at"
                                           placeholder="dd/mm/aaaa" data-toggle="tooltip"
                                           title="Fecha de inicio del tipo de cambio">
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label>Fecha fin:</label>
                                    <input type="date" class="form-control input-sm" v-model="record.end_at"
                                           placeholder="dd/mm/aaaa" data-toggle="tooltip"
                                           title="Fecha fin del tipo de cambio">
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label>Monto:</label>
                                    <input
                                        type="text" class="form-control input-sm" v-model="record.amount" data-toggle="tooltip"
                                        title="monto del tipo de cambio" v-is-numeric
                                    >
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group is-required">
                                    <label>Activo:</label>
                                    <div class="custom-control custom-switch" data-toggle="tooltip"
                                         title="Indique si el tipo de cambio está o no activo">
										<input type="checkbox" class="custom-control-input"
											   id="exchangeActive" v-model="record.active" :value="true">
										<label class="custom-control-label" for="exchangeActive"></label>
									</div>
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
							<button type="button" @click="createRecord('exchange-rates')"
									class="btn btn-primary btn-sm btn-round btn-modal-save">
								Guardar
							</button>
                        </div>
                    </div>
                    <div class="modal-body modal-table">
                        <v-client-table :columns="columns" :data="records" :options="table_options">
                            <div slot="start_at" slot-scope="props">
                                {{ format_date(props.row.start_at) }}
                            </div>
                            <div slot="end_at" slot-scope="props">
                                <span v-if="props.row.end_at">{{ format_date(props.row.end_at) }}</span>
                            </div>
                            <div slot="from_currency" slot-scope="props">
                                {{ props.row.from_currency.symbol }} - {{ props.row.from_currency.name }}
                            </div>
                            <div slot="to_currency" slot-scope="props">
                                {{ props.row.to_currency.symbol }} - {{ props.row.to_currency.name }}
                            </div>
                            <div slot="amount" slot-scope="props">
                                {{ props.row.to_currency.symbol }} {{ props.row.amount }}
                            </div>
                            <div slot="active" slot-scope="props" class="text-center">
                                <span v-if="props.row.active === true" class="text-bold text-success">SI</span>
                                <span v-else class="text-bold text-danger">NO</span>
                            </div>
                            <div slot="id" slot-scope="props" class="text-center">
                                <button @click="initUpdate(props.row.id, $event)"
                                        class="btn btn-warning btn-xs btn-icon btn-action"
                                        title="Modificar registro" data-toggle="tooltip" type="button">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button @click="deleteRecord(props.row.id, 'exchange-rates')"
                                        class="btn btn-danger btn-xs btn-icon btn-action"
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
    </div>
</template>

<script>
    export default {
        data() {
            return {
                record: {
                    id: '',
                    start_at: '',
                    end_at: null,
                    active: true,
                    amount: 0,
                    from_currency_id: '0',
                    to_currency_id: '0',
                },
                errors: [],
                records: [],
                currencies: [],
                columns: ['start_at', 'end_at', 'from_currency', 'to_currency', 'amount', 'active', 'id'],
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
                    start_at: '',
                    end_at: null,
                    active: true,
                    amount: 0,
                    from_currency_id: '0',
                    to_currency_id: '0',
                };
            },
        },
        created() {
            this.table_options.headings = {
                'start_at': 'Fecha inicio',
                'end_at': 'Fecha fin',
                'from_currency': 'De',
                'to_currency': 'A',
                'amount': 'Tipo de cambio',
                'active': 'Activo',
                'id': 'Acción'
            };
            this.table_options.sortable = ['start_at', 'end_at', 'from_currency', 'to_currency'];
            this.table_options.filterable = ['start_at', 'end_at', 'from_currency', 'to_currency'];
            this.table_options.columnsClasses = {
                'start_at': 'col-md-1 text-center',
                'end_at': 'col-md-1 text-center',
                'from_currency': 'col-md-2',
                'to_currency': 'col-md-2',
                'amount': 'col-md-2 text-right',
                'active': 'col-md-2 text-center',
                'id': 'col-md-2'
            };
        },
        mounted() {
            const vm = this;

            $("#add_exchange_rate").on('show.bs.modal', async function() {
                await vm.getCurrencies();
                vm.currencies.unshift({
                        default: true,
                        id: '',
                        text: 'Seleccione...'
                });
            });
        }
    };
</script>

