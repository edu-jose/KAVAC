<template>
	<section id="CitizenServiceRegisterForm">

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
					<div class="col-md-4" id="helpCitizenServiceRegisterDate">
						<div class="form-group is-required">
							<label for="date_register">Fecha del registro</label>
        					<input type="date" id="date_register" class="form-control input-sm" data-toggle="tooltip"
                                   title="Indique la fecha del registro" v-model="record.date_register">
        				</div>
					</div>

                    <div class="col-md-4" id="helpCitizenServiceRegisterFirstName">
					<div class="form-group is-required">
						<label for="payrollStaff">Nombre del director</label>
						<select2 :options="payroll_staffs"
								  v-model="record.payroll_staff_id"></select2>
                    </div>
				    </div>

                    <div class="col-md-4" id="helpCitizenServiceRegisterRequestCode">
						<div class="form-group is-required">
							<label for="code">Código de la solicitud</label>
        					<select2 :options="request_code_list" data-toggle="tooltip" v-model="record.code"></select2>
						</div>
					</div>
					<div class="col-md-4" id="helpCitizenServiceRegisterActivities">
						<div class="form-group is-required">
							<label for="activities">Actividades</label>
        					<input type="text" id="activities" class="form-control input-sm" data-toggle="tooltip"
                                   title="Indique las actividades" v-model="record.activities">
						</div>
					</div>
					<div class="col-md-4" id="helpCitizenServiceRegisterEmail">
						<div class="form-group is-required">
							<label for="email">Correo electrónico</label>
        					<input type="email" id="email" class="form-control input-sm" data-toggle="tooltip"
                                   title="Indique el correo electrónico del responsable" v-model="record.email">
						</div>
					</div>
					<div class="col-md-4" id="helpCitizenServiceRegisterStartDate">
						<div class="form-group is-required">
							<label for="start_date">Fecha de inicio</label>
        					<input type="date" id="start_date" data-toggle="tooltip" title="Indique la fecha de inicio"
								   class="form-control no-restrict"
                                   v-model="record.start_date">
        				</div>
					</div>
					<div class="col-md-4" id="helpCitizenServiceRegisterEndDate">
						<div class="form-group is-required">
							<label for="end_date">Fecha de culminación</label>
        					<input type="date" id="end_date" class="form-control input-sm no-restrict" data-toggle="tooltip"
								   :min="record.start_date"
                                   title="Indique la fecha de culminación" v-model="record.end_date">
        				</div>
					</div>
					<div class="col-md-4" id="helpCitizenServiceRegisterPercent">
						<div class="form-group">
							<label for="percent">Porcentaje de cumplimiento</label>
        					<input type="text" min="1" max="100" id="percent" class="form-control input-sm" data-toggle="tooltip"
								   v-input-mask data-inputmask-regex="[0-9]*"
                                   title="Indique el porcentaje de cumplimiento" v-model="record.percent">
						</div>
					</div>
					<hr>
					<div class="col-md-12" id="helpCitizenServiceRegisterTeam">
						<div class="form-group is-required">
							<label for="team_name">Equipo responsable</label>
						    <v-multiselect :options="payroll_staffs"
						    track_by="text"
									v-model="record.team_name"
								    :hide_selected="false">
						    </v-multiselect>
						</div>
					</div>
				</div>
            </div>

		<div class="card-footer text-right">
			<div class="row">
				<div class="col-md-3 offset-md-9" id="helpParamButtons">
					<button type="button" @click="reset()" class="btn btn-default btn-icon btn-round"
							data-toggle="tooltip"
		                    title ="Borrar datos del formulario">
							<i class="fa fa-eraser"></i>
					</button>

		        	<button type="button" @click="redirect_back(route_list)"
                        	class="btn btn-warning btn-icon btn-round" data-toggle="tooltip"
                        	title="Cancelar y regresar">
                    		<i class="fa fa-ban"></i>
            		</button>

		        	<button type="button"  @click="createRecord('citizenservice/registers')"
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
					date_register: '',
					payroll_staff_id: '',
					code: '',
					team_name: [],
					activities: '',
					start_date: '',
					end_date: '',
					email: '',
					percent: ''
				},

				errors: [],
				records: [],
				payroll_staffs: [],
				request_code_list: []

			}
		},
		methods: {
			loadForm(id){

				const vm = this;

	            axios.get(`${window.app_url}/citizenservice/registers/vue-info/${id}`).then(response => {
	                if(typeof(response.data.record != "undefined")){
						vm.record = response.data.record;
						vm.record.code = response.data.record.code;
	                }
	            });
			},
			/**
			 * Método que borra todos los datos del formulario
			 */
			reset() {
				this.record = {
					id: '',
					date_register: '',
					payroll_staff_id: '',
					code: '',
					team_name: [],
					activities: '',
					start_date: '',
					end_date: '',
					email: '',
					percent: ''
				};
			},

			/**
			 * Obtiene los datos de los trabajadores registrados
			 *
			 * @author William Páez <wpaez@cenditel.gob.ve>
			 */
			getPayrollStaffs() {
				this.payroll_staffs = [];
				axios.get(`${window.app_url}/payroll/get-staffs`).then(response => {
					this.payroll_staffs = response.data;
				});
			},

			getRequestCodes() {
				this.request_code_list = [];
				axios.get(`${window.app_url}/citizenservice/get-request-codes`).then(response => {
					this.request_code_list = response.data;
				});
			},
		},
		async mounted() {
			const vm = this;
            await vm.getRequestCodes();
			if(this.requestid){
				await this.loadForm(this.requestid);
			}
		},
		props: {
			requestid: {
                type: Number
            },
		},

		created() {
			const vm = this;
			vm.getPayrollStaffs();

		},
	};
</script>
