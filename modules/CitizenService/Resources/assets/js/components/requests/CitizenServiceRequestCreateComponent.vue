<template>
	<section id="CitizenServiceRequestForm">
        <div class="card-body">
        	<form-errors :listErrors="errors"></form-errors>
            <div class="row">
                <div class="col-md-4" id="helpCitizenServiceRequestDate">
                    <div class="form-group is-required">
                        <label for="date">Fecha</label>
                        <input
                            type="date" id="date"
                            class="form-control input-sm no-restrict" data-toggle="tooltip"
                            title="Indique la fecha de solicitud" v-model="record.date"
                        >
                    </div>
                </div>
                <div class="col-md-4" id="helpCitizenServiceRequestFirstName">
                    <div class="form-group is-required">
                        <label for="first_name">Nombres</label>
                        <input
                            type="text" class="form-control input-sm" data-toggle="tooltip"
							v-input-mask data-inputmask-regex="[a-zA-ZÁ-ÿ\s]*"
                            title="Indique los nombres del solicitante" v-model="record.first_name"
                        >
                    </div>
                </div>
                <div class="col-md-4" id="helpCitizenServiceRequestLastName">
                    <div class="form-group is-required">
                        <label for="last_name">Apellidos</label>
                        <input
                            type="text" id="apellido" class="form-control input-sm" data-toggle="tooltip"
							v-input-mask data-inputmask-regex="[a-zA-ZÁ-ÿ\s]*"
                            title="Indique los apellidos del solicitante" v-model="record.last_name"
                        >
                    </div>
                </div>
                <div class="col-md-4" id="helpCitizenServiceRequestIdNumber">
                    <div class="form-group is-required">
                        <label for="id_number">Cédula de identidad</label>
                        <input
                            type="text" class="form-control input-sm" data-toggle="tooltip"
                            title="Indique la cédula de identidad del solicitante" v-model="record.id_number"
                        >
                    </div>
                </div>
				<div class="col-md-4" id="helpCitizenServiceRequestBirthDate">
                    <div class="form-group">
                        <label for="birth_date">Fecha de nacimiento</label>
                        <input
                            type="date" id="birth_date" placeholder="Fecha de nacimiento"
                            class="form-control input-sm" data-toggle="tooltip"
                            title="Indique la fecha de nacimiento" v-model="record.birth_date"
							:min="mindate" :max="maxdate"
							@change="setAge"
                        >
                    </div>
                </div>
				<div class="col-md-4" id="helpCitizenServiceRequestAge">
                    <div class="form-group">
                        <label for="age">Edad:</label>
                        <input
                            type="text" id="age" data-toggle="tooltip"
                            title="Indique la edad de la persona solicitante" disabled class="form-control input-sm"
                            v-input-mask data-inputmask="'alias': 'numeric', 'allowMinus': 'false', 'digits': 0"
                            v-model="record.age"
                        />
                    </div>
                </div>
                <div class="col-md-4" id="helpCitizenServiceEmail">
                    <div class="form-group is-required">
                        <label for="email">Correo electrónico</label>
                        <input
                            type="email" id="email" class="form-control input-sm" data-toggle="tooltip"
                            title="Indique el correo electrónico del solicitante" v-model="record.email"
                        >
                    </div>
                </div>
				<div v-if="isPayrollActive" class="col-md-4" id="helpCitizenGenderId">
					<div class="form-group is-required">
						<label for="gender_id">Género</label>
						<select2  id="input_gender_id" :options="genders" v-model="record.gender_id"></select2>
					</div>
				</div>
				<div v-else class="col-md-4" id="helpCitizenServiceRequestGender">
                    <div class="form-group is-required">
                        <label for="gender">Género</label>
                        <input
                            type="text" class="form-control input-sm" data-toggle="tooltip"
							v-input-mask data-inputmask-regex="[a-zA-ZÁ-ÿ\s]*"
                            title="Indique el género del solicitante" v-model="record.gender"
                        >
                    </div>
                </div>
				<div v-if="isPayrollActive" class="col-md-4" id="helpCitizenNationalityId">
					<div class="form-group is-required">
						<label for="nationality_id">Nacionalidad</label>
						<select2
                            id="input_nationality_id" :options="nationalities"
                            v-model="record.nationality_id"
                        ></select2>
					</div>
				</div>
				<div v-else class="col-md-4" id="helpCitizenServiceRequestNationality">
                    <div class="form-group is-required">
                        <label for="nationality">Nacionalidad</label>
                        <input
                            type="text" class="form-control input-sm" data-toggle="tooltip"
							v-input-mask data-inputmask-regex="[a-zA-ZÁ-ÿ\s]*"
                            title="Indique la nacionalidad del solicitante" v-model="record.nationality"
                        >
                    </div>
                </div>
                <div class="col-md-4" id="helpCitizenServiceInstructionDegree">
                    <div class="form-group is-required">
                        <label for="payrollInstructionDegree">Grado de Instrucción</label>
                        <select2
                            id="payrollInstructionDegree" :options="payroll_instruction_degrees"
                            v-model="record.payroll_instruction_degree_id"
                            data-toggle="tooltip"
                            title="Seleccione el grado de instrucción del solicitante"
                        ></select2>
                    </div>
                </div>
                <div class="col-md-4" id="helpCitizenServiceProfession">
                    <div class="form-group">
                        <label for="profession">Ocupación u Oficio</label>
                        <select2
                            id="profession" :options="professions"
                            v-model="record.profession_id"
                            data-toggle="tooltip"
                            title="Seleccione la profesión del solicitante"
                        ></select2>
                    </div>
                </div>
                <div class="col-md-2" id="helpCitizenServiceFamilyIncome">
                    <div class="form-group">
                        <label for="familyIncome">Ingreso Familiar</label>
                        <input
                            id="familyIncome"
                            type="text" class="form-control input-sm" data-toggle="tooltip"
                            v-model="record.family_income"
                            title="Indique el ingreso familiar"
                            v-is-numeric
                        />
                    </div>
                </div>
                <div class="col-md-2" id="helpCitizenServiceFamilyBurden">
                    <div class="form-group">
                        <label for="familyBurden">Carga Familiar</label>
                        <input
                            id="familyBurden"
                            type="text" class="form-control input-sm" data-toggle="tooltip"
                            v-input-mask data-inputmask="'alias': 'numeric', 'allowMinus': 'false', 'digits': 0"
                            v-model="record.family_burden"
                            title="Indique la carga familiar"
                        />
                    </div>
                </div>
                <div class="col-md-2" id="helpCitizenServiceIsHouseholdHead">
                    <div class="form-group">
                        <label for="isHouseholdHead">¿Es jefe de hogar?</label>
                        <div class="col-md-12">
                            <div
                                class="custom-control custom-switch" data-toggle="tooltip"
                                title="Indique si la persona solitiante es jefe de hogar"
                            >
                                <input
                                    type="checkbox" class="custom-control-input"
                                    id="isHouseholdHead" v-model="record.is_household_head"
                                    :value="true"
                                >
                                <label class="custom-control-label" for="isHouseholdHead">&nbsp;</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2" id="helpCitizenServiceHasWork">
                    <div class="form-group">
                        <label for="hasWork">¿Trabaja?</label>
                        <div class="col-md-12">
                            <div
                                class="custom-control custom-switch" data-toggle="tooltip"
                                title="Indique si la persona solitiante trabaja"
                            >
                                <input
                                    type="checkbox" class="custom-control-input"
                                    id="hasWork" v-model="record.has_work"
                                    :value="true"
                                >
                                <label class="custom-control-label" for="hasWork">&nbsp;</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" id="helpCitizenServiceSector" v-if="record.has_work">
                    <div class="form-group is-required">
                        <label for="sector">Sector</label>
                        <select2
                            id="sector" :options="sectors"
                            v-model="record.sector_id"
                            data-toggle="tooltip"
                            title="Seleccione el sector económico del solicitante"
                        ></select2>
                    </div>
                </div>
            </div>
            <h6 class="card-title mt-4">
                Números Telefónicos
                <i class="fa fa-plus-circle cursor-pointer ml-2" @click="addPhone"></i>
            </h6>
            <div class="row" v-for="(phone, index) in record.phones" :key="index">
                <div class="col-3" id="helpCitizenServicePhones">
                    <div class="form-group is-required">
                        <select
                            data-toggle="tooltip" v-model="phone.type" class="select2"
                            title="Seleccione el tipo de número telefónico"
                        >
                            <option value="">Seleccione...</option>
                            <option value="M">Móvil</option>
                            <option value="T">Teléfono</option>
                            <option value="F">Fax</option>
                        </select>
                    </div>
                </div>
                <div class="col-2">
                    <div class="form-group is-required">
                        <input
                            type="text" placeholder="Cod. Area" data-toggle="tooltip"
                            title="Indique el código de área" v-model="phone.area_code"
                            class="form-control input-sm"
                        >
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group is-required">
                        <input
                            type="text" placeholder="Número" data-toggle="tooltip"
                            title="Indique el número telefónico"
                            v-model="phone.number" class="form-control input-sm"
                        >
                    </div>
                </div>
                <div class="col-2">
                    <div class="form-group">
                        <input
                            type="text" placeholder="Extensión" data-toggle="tooltip"
                            title="Indique la extención telefónica (opcional)"
                            v-model="phone.extension" class="form-control input-sm"
                        >
                    </div>
                </div>
                <div class="col-1">
                    <div class="form-group">
                        <button
                            class="btn btn-sm btn-danger btn-action" type="button"
                            @click="removeRow(index, record.phones)"
                            title="Eliminar este dato" data-toggle="tooltip"
                        >
                            <i class="fa fa-minus-circle"></i>
                        </button>
                    </div>
                </div>
            </div>
            <hr>
			<h6 class="card-title">
                Datos de Ubicación del Solicitante
            </h6>
			<div class="row">
		    	<div class="col-md-4">
					<div class="form-group is-required" id="helpCitizenServiceCountry">
						<label for="country_id">País</label>
						<select2
                            id="country_id" :options="countries"
                            @input="getRequestEstates()" v-model="record.country_id"
                        ></select2>
					</div>
				</div>
				<div class="col-md-4" id="helpCitizenServiceEstate">
					<div class="form-group is-required">
						<label for="estate_id">Estado</label>
						<select2
                            id="estate_id" :options="estates"
                            @input="getRequestCities()" v-model="record.estate_id"
                        ></select2>
					</div>
				</div>
				<div class="col-md-4" id="helpCitizenServiceCity">
					<div class="form-group is-required">
						<label for="city_id">Ciudad</label>
						<select2
                            id="city_id" :options="cities"
                            @input="getRequestMunicipalities()" v-model="record.city_id"
                        ></select2>
					</div>
				</div>
				<div class="col-md-4" id="helpCitizenServiceMunicipality">
					<div class="form-group is-required">
						<label for="municipality_id">Municipio</label>
						<select2
                            id="municipality" :options="municipalities"
                            @input="getRequestParishes()" v-model="record.municipality_id"
                        ></select2>
					</div>
				</div>
				<div class="col-md-4" id="helpCitizenServiceParish">
					<div class="form-group is-required">
						<label for="parish_id">Parroquia</label>
						<select2
                            id="parish_id"
                            :options="parishes" v-model="record.parish_id"
                        ></select2>
					</div>
				</div>
				<div class="col-4" id="helpCitizenServiceAddress">
					<div class="form-group is-required">
						<label for="address">Dirección</label>
                        <input
                            type="text" name="address" id="address"
                            data-toggle="tooltip"
                            title="Indique la dirección del solicitante del trámite"
                            class="form-control input-sm"
                            v-model="record.address"
                        >
					</div>
				</div>
				<div class="col-md-4" id="helpCitizenServiceRequestCommunity">
                    <div class="form-group">
                        <label for="active_community">Comunidad:</label>
                        <div class="col-md-12">
                            <div
                                class="custom-control custom-switch" data-toggle="tooltip"
                                title="Indique si la persona solitiante pertenece a una comunidad"
                            >
                                <input
                                    type="radio" class="custom-control-input"
                                    @click="resetInfo()" id="active_community"
                                    name="active_community" v-model="record.community"
                                    value="community"
                                >
                                <label class="custom-control-label" for="active_community">&nbsp;</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" id="helpCitizenServiceTypeInstitution">
    				<div class="form-group">
    					<label for="type_institution">Institución</label>
    					<div class="col-md-12">
							<div class="custom-control custom-switch">
								<input
                                    type="checkbox" class="custom-control-input" id="type_institution"
									name="type_institution" v-model="record.type_institution" :value="true"
                                >
								<label class="custom-control-label" for="type_institution">&nbsp;</label>
							</div>
    					</div>
    				</div>
    			</div>
            </div>
            <div class="row" v-if="record.community == 'community'">
				<div class="col-md-12 my-4">
					<b>Datos de la Comunidad</b>
				</div>
				<div class="col-md-4">
                    <div class="form-group is-required">
                        <label for="location">Ubicación</label>
                        <input
                            type="text" class="form-control input-sm" data-toggle="tooltip"
							v-input-mask data-inputmask-regex="[a-zA-ZÁ-ÿ\s]*"
                            title="Indique la ubicación del solicitante" v-model="record.location"
                        >
                    </div>
                </div>
				<div class="col-md-4">
                    <div class="form-group is-required">
                        <label for="commune">Comuna</label>
                        <input
                            type="text" class="form-control input-sm" data-toggle="tooltip"
							v-input-mask data-inputmask-regex="[a-zA-ZÁ-ÿ\s]*"
                            title="Indique la comuna al que pertenece el solicitante" v-model="record.commune"
                        >
                    </div>
                </div>
				<div class="col-md-4">
                    <div class="form-group is-required">
                        <label for="communal_council">Consejo Comunal</label>
                        <input
                            type="text" class="form-control input-sm" data-toggle="tooltip"
							v-input-mask data-inputmask-regex="[a-zA-ZÁ-ÿ\s]*"
                            title="Indique el consejo comunal al que pertenece el solicitante"
                            v-model="record.communal_council"
                        >
                    </div>
                </div>
				<div class="col-md-2">
                    <div class="form-group is-required">
                        <label for="population_size">Cantidad de habitantes</label>
                        <input
                            id="population_size"
                            type="number" class="form-control input-sm" :step="1" min="1"
                            data-toggle="tooltip"
                            title="Indique la cantidad de habitantes de la comunidad"
                            v-model="record.population_size"
                        >
                    </div>
				</div>
            </div>
            <div class="row" v-if="record.type_institution">
                <div class="col-md-12 my-4">
                    <b>Datos de la institución</b>
                </div>
                <div class="col-md-4">
                    <div class="form-group is-required">
                        <label for="institution_name">Nombre de la institución</label>
                        <input
                            type="text" id="institution_name" class="form-control input-sm" data-toggle="tooltip"
                            v-input-mask data-inputmask-regex="[a-zA-ZÁ-ÿ\s]*"
                            title="Indique el nombre de la institución" v-model="record.institution_name"
                        >
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group is-required">
                        <label for="rif">RIF</label>
                        <input
                            type="text" id="rif" class="form-control input-sm" data-toggle="tooltip"
                            placeholder="J000000000"
                            title="Indique el rif de la institución" v-model="record.rif"
                        >
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group is-required">
                        <label for="institution_address">Dirección de la institución</label>
                        <input
                            type="text" id="institution_address" class="form-control input-sm"
                            data-toggle="tooltip" title="Indique la dirección de la institución"
                            v-model="record.institution_address"
                        >
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="web">Dirección web</label>
                        <input
                            type="url" id="web" class="form-control input-sm" data-toggle="tooltip"
                            title="Indique la dirección web" v-model="record.web"
                        >
                    </div>
                </div>
			</div>
			<hr>
			<h6 class="card-title">
                Datos del Trámite
            </h6>
			<div class="row">
                <div class="col-md-4" id="helpCitizenServiceRequestType">
					<div class="form-group is-required">
						<label for="citizenserviceRequestTypes">Tipo de trámite</label>
						<select2
                            :options="citizen_service_request_types"
							@input="getCitizenServiceRequestType()"
							v-model="record.citizen_service_request_type_id"
                        ></select2>
                    </div>
				</div>
                <div class="col-md-4" id="helpCitizenServiceProcedure">
                    <div class="form-group is-required">
                        <label for="procedure">Trámite</label>
                        <select2
                            id="procedure" :options="procedures"
                            v-model="record.citizen_service_procedure_id"
                            data-toggle="tooltip"
                            title="Seleccione el trámite a realizar"
                        ></select2>
                    </div>
                </div>
                <div class="col-md-4" id="helpCitizenServiceHasVenAppReport">
                    <div class="form-group">
                        <label for="hasVenappReport">¿Tiene reporte en VenAPP?</label>
                        <div class="col-md-12">
                            <div
                                class="custom-control custom-switch" data-toggle="tooltip"
                                title="Indique si la persona solitiante tiene algún reporte en VenApp"
                            >
                                <input
                                    type="checkbox" class="custom-control-input"
                                    id="hasVenappReport" v-model="record.has_venapp_report"
                                    :value="true"
                                >
                                <label class="custom-control-label" for="hasVenappReport">&nbsp;</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" id="helpCitizenServiceVenappReportNumber" v-if="record.has_venapp_report">
					<div class="form-group is-required">
						<label for="venappReportNumber">Nro. de reporte en VenApp</label>
    					<input
                            type="text" id="venappReportNumber" class="form-control input-sm" data-toggle="tooltip"
                            title="Indique el nro. de reporte en VenApp" v-model="record.venapp_report_number"
                            v-is-only-numeric
                        >
					</div>
				</div>
				<div class="col-md-4" id="helpCitizenServiceMotiveRequest">
					<div class="form-group is-required">
						<label for="motive_request">Motivo de la solicitud</label>
    					<input
                            type="text" id="motive_request" class="form-control input-sm" data-toggle="tooltip"
                            title="Indique el motivo de la solicitud" v-model="record.motive_request"
                        >
					</div>
				</div>
				<div class="col-md-4" id="helpCitizenServiceAttribute">
					<div class="form-group is-required">
						<label for="attribute">Descripción de la solicitud</label>
    					<input
                            type="text" id="attribute" class="form-control input-sm" data-toggle="tooltip"
                            title="Indique la descripción de la solicitud" v-model="record.attribute"
                        >
					</div>
				</div>
			</div>
			<div v-if="citizenServiceRequestType == 'Soporte técnico'">
				<div class="col-md-12">
					<b>Datos del equipo</b>
				</div>
				<div class="row">
					<div class="col-md-4">
						<div class="form-group is-required">
							<label for="type_team">Tipo de equipo</label>
        					<input
                                type="text" id="type_team" class="form-control input-sm" data-toggle="tooltip"
                                title="Indique el tipo de equipo" v-model="record.type_team"
                            />
						</div>
					</div>
                    <div class="col-md-4">
						<div class="form-group is-required">
							<label for="brand">Marca</label>
        					<input
                                type="text" id="brand" class="form-control input-sm" data-toggle="tooltip"
                                title="Indique la marca del equipo" v-model="record.brand"
                            />
						</div>
					</div>
                    <div class="col-md-4">
						<div class="form-group is-required">
							<label for="model">Modelo</label>
        					<input
                                type="text" id="model" class="form-control input-sm" data-toggle="tooltip"
                                title="Indique el modelo del equipo" v-model="record.model"
                            />
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group is-required">
							<label for="serial">Serial</label>
        					<input
                                type="text" id="serial" class="form-control input-sm" data-toggle="tooltip"
                                title="Indique el serial del equipo" v-model="record.serial"
                            />
						</div>
					</div>
                    <div class="col-md-4">
						<div class="form-group is-required">
							<label for="color">Color</label>
        					<input
                                type="text" id="color" class="form-control input-sm" data-toggle="tooltip"
                                title="Indique el color del equipo" v-model="record.color"
                            />
						</div>
					</div>
                    <div class="col-md-4">
						<div class="form-group is-required">
							<label for="transfer">Motivo de traslado</label>
        					<input
                                type="text" id="transfer" class="form-control input-sm" data-toggle="tooltip"
                                title="Indique el motivo de traslado" v-model="record.transfer"
                            />
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group is-required">
							<label for="inventory_code">Código de inventario</label>
        					<input
                                type="text" id="inventory_code" class="form-control input-sm" data-toggle="tooltip"
                                title="Indique el código de inventario" v-model="record.inventory_code"
                            />
						</div>
					</div>
                    <div class="col-md-4">
						<div class="form-group is-required">
							<label for="entryhour">Hora de entrada</label>
        					<input
                                type="time" id="entryhour" class="form-control input-sm" data-toggle="tooltip"
                                title="Indique la hora de entrada del equipo" v-model="record.entryhour"
                            />
						</div>
					</div>
                    <div class="col-md-4">
						<div class="form-group">
							<label for="exithour">Hora de salida</label>
        					<input
                                type="time" id="exithour" class="form-control input-sm" data-toggle="tooltip"
                                title="Indique la hora de salida del equipo" v-model="record.exithour"
                            />
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group is-required">
							<label for="informationteam">Información adicional del equipo</label>
        					<input
                                type="text" id="informationteam" class="form-control input-sm" data-toggle="tooltip"
                                title="Indique la información adicional del equipo" v-model="record.informationteam"
                            />
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label for="other">Otros</label>
        					<input
                                type="text" id="other" class="form-control input-sm" data-toggle="tooltip"
                                title="Indique otra información no referente al equipo" v-model="record.other"
                            />
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-md-4" id="helpCitizenServiceDepartment">
					<div class="form-group is-required">
						<label for="citizenserviceDepartment">Departamento</label>
						<select2
                            :options="citizen_service_departments"
                            v-model="record.citizen_service_department_id"
                            @input="setDirector()"
                        ></select2>
					</div>
				</div>
				<div v-if="isPayrollActive" class="col-md-4" id="helpCitizenDirectorId">
					<div class="form-group is-required">
						<label for="director_id">Director y/o responsable de la solicitud</label>
						<select2
                            id="input_director_id" :options="payroll_staffs"
                            v-model="record.director_id" disabled
                        ></select2>
					</div>
				</div>

				<div class="col-md-4" id="helpCitizenServiceRequestType">
					<div class="form-group is-required">
						<label for="citizenserviceTransactionTypes">Tipo de transacción</label>
						<select2
                            :options="citizen_transaction_types"
                            v-model="record.citizen_transaction_type_id"
                        ></select2>
					</div>
				</div>

                <div class="col-12 col-md-4">
                    <upload-documents
                        inputLabel="Documento" inputTooltip="Seleccione el documento a adjuntar"
                        :parentRecord="'documentFiles'" :maxUploadSize="2"
                    />
                </div>
                <div
                    class="col-12 col-md-4"
                    v-if="typeof(record.documentUrl) != 'undefined' && record.documentUrl"
                >
                    <div class="form-group">
                        <strong>Ver Documento:</strong>
                        <div class="row" style="margin: 1px 0">
                            <a
                                :href="showDocument()" target="_blank"
                                class="btn btn-primary btn-xs btn-icon btn-action btn-tooltip"
                                data-toggle="tooltip" title="Ver documento"
                            >
                                <i class="fa fa-file" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label for="observations">Observaciones</label>
                        <ckeditor
                            :editor="ckeditor.editor"
                            id="observations"
                            data-toggle="tooltip"
                            title="Indique las observaciones relacionadas a la solicitud de trámite"
                            :config="ckeditor.editorConfig"
                            class="form-control"
                            tag-name="textarea"
                            rows="3"
                            v-model="record.observations"
                        ></ckeditor>
                    </div>
                </div>
			</div>
		</div>
		<div class="card-footer text-right">
        	<button
                type="button" @click="reset()" class="btn btn-default btn-icon btn-round"
				data-toggle="tooltip" v-has-tooltip
                title ="Borrar datos del formulario"
            >
				<i class="fa fa-eraser"></i>
			</button>
        	<button
                type="button" @click="redirect_back(route_list)"
                class="btn btn-warning btn-icon btn-round" data-toggle="tooltip"
                title="Cancelar y regresar"
            >
                <i class="fa fa-ban"></i>
            </button>
			<button
                type="button"  @click="validateForm()"
				class="btn btn-success btn-icon btn-round btn-modal-save"
				title="Guardar registro"
            >
				<i class="fa fa-save"></i>
            </button>
        </div>
   	</section>
</template>

<script>
	export default {
		data() {
			return {
				record: {
					id: '',
					date: '',
					gender_id: '',
					gender: '',
					nationality_id: '',
					nationality: '',
					community: 'notCommunity',
					location: '',
					commune: '',
					communal_council: '',
					population_size: '',
					director_id: '',
					first_name: '',
					last_name: '',
					id_number: '',
					email: '',
					birth_date: '',
					age: '',
					phones: [],
					city_id: '',
        			municipality_id: '',
        			parish_id: '',
        			address: '',
        			motive_request: '',
        			attribute: '',
        			citizen_service_request_type_id: '',
        			citizen_transaction_type_id: '',
        			citizen_service_department_id: '',
        			documentFiles: [],
        			documentUrl: '',
                    family_income: '',
                    family_burden: '',
                    is_household_head: false,
                    has_work: false,
                    has_venapp_report: false,
                    venapp_report_number: '',
                    observations: '',
                    sector_id: '',
                    profession_id: '',
                    payroll_instruction_degree_id: '',
                    citizen_service_procedure_id: '',

        			type_institution: '',
					institution_name: '',
					rif: '',
        			institution_address: '',
        			web: '',

                    // Datos del equipo
        			type_team: '',
        			brand: '',
        			model: '',
        			serial: '',
        			color: '',
        			transfer: '',
        			inventory_code: '',
        			entryhour: '',
        			exithour: '',
        			informationteam: '',
        			other: '',
				},
				errors: [],
				records: [],
				genders: [],
				nationalities: [],
				countries: [],
				estates: [],
				cities: [],
				municipalities: [],
				parishes: [],
				citizenServiceRequestType: '',
				citizen_service_request_types: [],
				citizen_transaction_types: [],
				citizen_service_departments: [],
				citizen_service_documents: [],
				payroll_staffs: [],
				department_info: [],
				department_info2: [],
				director_info: [],
                payroll_instruction_degrees: [],
                professions: [],
                sectors: [],
                procedures: [],
				payroll: "",

				mindate: "1900-01-01",
				maxdate: "2099-12-31",

                loadedEstates: false,
                loadedMunicipalities: false,
                loadedParishes: false,
                loadedCities: false,
                formLoaded: false,
			}
		},
		methods: {
            setValidationBeneficiaryFields() {
                const vm = this;
                return [
                    {
                        condition: !vm.record.date,
                        message: "El campo fecha de la solicitud del trámite es obligatorio."
                    },
                    {
                        condition: !vm.record.first_name,
                        message: "El campo nombres del solicitante del trámite es obligatorio."
                    },
                    {
                        condition: !vm.record.last_name,
                        message: "El campo apellidos del solicitante del trámite es obligatorio."
                    },
                    {
                        condition: !vm.record.id_number,
                        message: "El campo cédula del solicitante del trámite es obligatorio."
                    },
                    {
                        condition: vm.record.birth_date && vm.record.age < 18,
                        message: "El solicitante del trámite debe ser mayor de edad."
                    },
                    {
                        condition: !vm.record.email,
                        message: "El campo correo electrónico del solicitante del trámite es obligatorio."
                    },
                    {
                        condition: !vm.record.gender_id && !vm.record.gender,
                        message: "El campo género del solicitante del trámite es obligatorio."
                    },
                    {
                        condition: !vm.record.nationality_id && !vm.record.nationality,
                        message: "El campo nacionalidad del solicitante del trámite es obligatorio."
                    },
                    {
                        condition: !vm.record.payroll_instruction_degree_id,
                        message: "El campo grado de instrucción del solicitante del trámite es obligatorio."
                    },
                    {
                        condition: vm.record.has_work && !vm.record.sector_id,
                        message: "El campo sector laboral del solicitante del trámite es obligatorio"
                    }
                ];
            },
            setValidationLocationFields() {
                const vm = this;
                return [
                    {
                        condition: !vm.record.country_id,
                        message: "El campo país de ubicación del solicitante del trámite es obligatorio."
                    },
                    {
                        condition: !vm.record.estate_id,
                        message: "El campo estado de ubicación del solicitante del trámite es obligatorio."
                    },
                    {
                        condition: !vm.record.city_id,
                        message: "El campo ciudad de ubicación del solicitante del trámite es obligatorio."
                    },
                    {
                        condition: !vm.record.municipality_id,
                        message: "El campo municipio de ubicación del solicitante del trámite es obligatorio."
                    },
                    {
                        condition: !vm.record.parish_id,
                        message: "El campo parroquia de ubicación del solicitante del trámite es obligatorio."
                    },
                    {
                        condition: !vm.record.address,
                        message: "El campo de dirección del solicitante del trámite es obligatorio."
                    },
                    {
                        condition: vm.record.community == 'community' && !vm.record.location,
                        message: "El campo ubicación de la comunidad es obligatorio"
                    },
                    {
                        condition: vm.record.community == 'community' && !vm.record.commune,
                        message: "El campo nombre de la comunidad es obligatorio"
                    },
                    {
                        condition: vm.record.community == 'community' && !vm.record.communal_council,
                        message: "El campo de consejo comunal es obligatorio"
                    },
                    {
                        condition: vm.record.community == 'community' && (!vm.record.population_size || vm.record.population_size == 0),
                        message: "El campo cantidad de habitantes de la comunidad es obligatorio"
                    },
                    {
                        condition: vm.record.type_institution && !vm.record.institution_name,
                        message: "El campo nombre de la institución es obligatorio"
                    },
                    {
                        condition: vm.record.type_institution && !vm.record.rif,
                        message: "El campo rif de la institución es obligatorio"
                    },
                    {
                        condition: vm.record.type_institution && !vm.record.institution_address,
                        message: "El campo dirección de la institución es obligatorio"
                    },
                    {
                        condition: vm.record.type_institution && !vm.record.web,
                        message: "El campo sitio web de la institución es obligatorio"
                    }
                ];
            },
            setValidationProcedureFields() {
                const vm = this;
                return [
                    {
                        condition: !vm.record.citizen_service_procedure_id,
                        message: "El campo trámite es obligatorio."
                    },
                    {
                        condition: vm.record.has_venapp_report && !vm.record.venapp_report_number,
                        message: "El campo número de reporte de VENAPP es obligatorio."
                    },
                    {
                        condition: !vm.record.motive_request,
                        message: "El campo motivo de la solicitud de trámite es obligatorio."
                    },
                    {
                        condition: !vm.record.attribute,
                        message: "El campo de descripción de la solicitud de trámite es obligatorio."
                    },
                    {
                        condition: !vm.record.citizen_service_request_type_id,
                        message: "El campo tipo de solicitud de trámite es obligatorio."
                    },
                    {
                        condition: !vm.record.citizen_service_department_id,
                        message: "El campo departamento es obligatorio."
                    },
                    {
                        condition: !vm.record.director_id,
                        message: "El campo director y/o responsable es obligatorio."
                    },
                    {
                        condition: !vm.record.citizen_transaction_type_id,
                        message: "El campo tipo de transacción es obligatorio."
                    },
                    {
                        condition: vm.citizenServiceRequestType == 'Soporte técnico' && !vm.record.type_team,
                        message: "El campo de tipo de equipo es obligatorio."
                    },
                    {
                        condition: vm.citizenServiceRequestType == 'Soporte técnico' && !vm.record.brand,
                        message: "El campo de marca del equipo es obligatorio."
                    },
                    {
                        condition: vm.citizenServiceRequestType == 'Soporte técnico' && !vm.record.model,
                        message: "El campo de modelo del equipo es obligatorio."
                    },
                    {
                        condition: vm.citizenServiceRequestType == 'Soporte técnico' && !vm.record.serial,
                        message: "El campo de serial del equipo es obligatorio."
                    },
                    {
                        condition: vm.citizenServiceRequestType == 'Soporte técnico' && !vm.record.color,
                        message: "El campo de color del equipo es obligatorio."
                    },
                    {
                        condition: vm.citizenServiceRequestType == 'Soporte técnico' && !vm.record.transfer,
                        message: "El campo de motivo de traslado del equipo es obligatorio."
                    },
                    {
                        condition: vm.citizenServiceRequestType == 'Soporte técnico' && !vm.record.inventory_code,
                        message: "El campo de código de inventario del equipo es obligatorio."
                    },
                    {
                        condition: vm.citizenServiceRequestType == 'Soporte técnico' && !vm.record.entryhour,
                        message: "El campo de hora de entrada del equipo es obligatorio."
                    },
                    {
                        condition: vm.citizenServiceRequestType == 'Soporte técnico' && !vm.record.informationteam,
                        message: "El campo de información del equipo es obligatorio."
                    }
                ];
            },
            setValidationPhoneFields() {
                const vm = this;
                if (vm.record.phones.length > 0) {
                    vm.record.phones.forEach((phone, index) => {
                        const idx = index + 1;
                        if (!phone.type) {
                            vm.errors.push(`El campo de tipo del número telefónico ${idx} es obligatorio`);
                        }
                        if (!phone.area_code) {
                            vm.errors.push(`El campo de código de área del número telefónico ${idx} es obligatorio`);
                        } else if (phone.area_code &&phone.area_code.length > 3) {
                            vm.errors.push(`El campo de código de área del número telefónico ${idx} debe tener 3 dígitos`);
                        }
                        if (!phone.number) {
                            vm.errors.push(`El campo de número telefónico ${idx} es obligatorio`);
                        } else if (phone.number && phone.number.length > 7) {
                            vm.errors.push(`El campo de número telefónico ${idx} debe tener 7 dígitos`);
                        }
                        if (phone.extension && (phone.extension.length < 3 || phone.extension.length > 6)) {
                            vm.errors.push(`El campo de extensión del número telefónico ${idx} es inválido, debe tener entre 3 y 6 dígitos`);
                        }
                    });
                }
            },
            /**
             * Validación de campos requeridos
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             *
             * @return  {boolean|void}
             */
            validateForm() {
                const vm = this;
                const fields = [
                    ...vm.setValidationBeneficiaryFields(),
                    ...vm.setValidationLocationFields(),
                    ...vm.setValidationProcedureFields()
                ];
                vm.errors = [];
                fields.forEach(field => {
                    if (field.condition) {
                        vm.errors.push(field.message);
                    }
                });
                vm.setValidationPhoneFields();
                if (vm.errors.length > 0) {
                    return false;
                }
                vm.createRecord('citizenservice/requests');
            },
            resetLoaded() {
                this.loadedEstates = false;
                this.loadedMunicipalities = false;
                this.loadedParishes = false;
                this.loadedCities = false;
            },
			async loadForm(id){
				const vm = this;
                vm.loading = true;
	            await axios.get(`${window.app_url}/citizenservice/requests/vue-info/${id}`).then(response => {
	                if (typeof(response.data.record != "undefined")) {
                        vm.resetLoaded();
                        vm.record = response.data.record;
						vm.record.country_id = response.data.record.parish.municipality.estate.country_id;
                        vm.getRequestEstates();
                        setTimeout(() => {
                            vm.record.estate_id = response.data.record.parish.municipality.estate.id;
                            vm.loadedMunicipalities = false;
                            vm.loadedCities = false;
                            vm.getRequestCities();
                            vm.getRequestMunicipalities();
                        }, 1000);
                        setTimeout(() => {
                            vm.record.city_id = response.data.record.city.id;
                            vm.record.municipality_id = response.data.record.parish.municipality_id;
                            vm.loadedParishes = false;
                            vm.getRequestParishes();
                        }, 2000);
                        setTimeout(() => {
                            vm.record.parish_id = response.data.record.parish.id;
                            vm.formLoaded = true;
                        }, 3000);
	                }
                    // Asignar initialUrl.url a record.documentUrl
			        if (this.initialUrl && this.initialUrl.url) {
			            this.record.documentUrl = this.initialUrl.url;
			            this.record.documentFiles = this.initialUrl.file;
			        }
	            });

                setTimeout(() => {
                    vm.loading = false;
                }, 1000);
			},
			/**
			 * Método que borra todos los datos del formulario
			 */
			reset() {
				this.record = {
					id: '',
					date: '',
					gender_id: '',
					gender: '',
					nationality_id: '',
					nationality: '',
					community: 'notCommunity',
					location: '',
					commune: '',
					communal_council: '',
					population_size: '',
					first_name: '',
					last_name: '',
					id_number: '',
					email: '',
					birth_date: '',
					age: '',
					phones: [],
					city_id: '',
        			municipality_id: '',
        			parish_id: '',
        			address: '',
        			motive_request: '',
        			attribute: '',
					citizen_service_request_type_id: '',
					citizen_transaction_type_id: '',
					citizen_service_department_id: '',
                    family_income: '',
                    family_burden: '',
                    is_household_head: false,
                    has_work: false,
                    has_venapp_report: false,
                    venapp_report_number: '',
                    observations: '',
                    sector_id: '',
                    profession_id: '',
                    payroll_instruction_degree_id: '',
                    citizen_service_procedure_id: '',

					type_institution: false,
					institution_name: '',
        			rif: '',
        			institution_address: '',
        			web: '',


        			type_team: '',
        			brand: '',
        			model: '',
        			serial: '',
        			color: '',
        			transfer: '',
        			inventory_code: '',
        			entryhour: '',
        			exithour: '',
        			informationteam: '',
        			other: '',
                	documentFiles: []

				};
				this.citizenServiceRequestType = '';
				this.citizenServiceTransactionType = '';
			},
			resetInfo() {
				const vm = this;
				if (vm.record.community == 'community') {
					vm.record.community = 'notCommunity';
				}
			},
			async getCitizenServiceDepartments() {
				this.citizen_service_departments = [];
				await axios.get(`${window.app_url}/citizenservice/get-departments`).then(response => {
					this.citizen_service_departments = response.data;
					this.department_info = response.data;
				});

			},
			setDirector() {
				const vm = this;
				vm.director_info = Object.values(vm.department_info).find(
					department => department.id == vm.record.citizen_service_department_id);
				if (vm.director_info) {
					vm.record.director_id = vm.director_info.director_id;
				}
			},
			getGenders() {
				const vm = this;
				axios.get(`${window.app_url}/get-genders`).then(response => {
        			vm.genders = response.data;
      			});
			},
			getNationalities() {
				const vm = this;
				axios.get(`${window.app_url}/payroll/get-nationalities`).then(response => {
        			vm.nationalities = response.data;
      			});
			},
			getPayrollStaffs() {
      			const vm = this;
      			axios.get(`${window.app_url}/payroll/get-staffs`).then(response => {
        			vm.payroll_staffs = response.data;
      			});
			},
			getCitizenServiceRequestType() {
                const vm = this;
                $.each(vm.citizen_service_request_types, function(index, field) {
                    if (field['id'] == '') {
                        vm.citizenServiceRequestType = '';
                    } else if (field['id'] == vm.record.citizen_service_request_type_id) {
                        vm.citizenServiceRequestType = field['text'];
                    }
                });
            },
			async getCitizenServiceTransactionTypes() {
				this.citizen_transaction_types = [];
				await axios.get(`${window.app_url}/citizenservice/get-transaction-types`).then(response => {
					this.citizen_transaction_types = response.data;
				});

			},
            /**
             * Obtiene los Estados del Pais seleccionado
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            async getRequestEstates() {
                const vm = this;
                if (vm.loadedEstates && !vm.formLoaded) {
                    return;
                }
                vm.loadedEstates = true;
                vm.estates = [
                    { id: '', text: 'Seleccione...' }
                ];
                if (vm.record.country_id) {
                    const url = vm.setUrl(`/get-estates/${vm.record.country_id}`);
                    await axios.get(url).then(response => {
                        if (response.data) {
                            vm.estates = response.data;
                        }
                    }).catch(error => {
                        console.error(error);
                    });
                }
            },
            /**
             * Obtiene los Municipios del Estado seleccionado
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            async getRequestMunicipalities() {
                const vm = this;
                if (vm.loadedMunicipalities && !vm.formLoaded) {
                    return;
                }
                vm.loadedMunicipalities = true;
                vm.municipalities = [];
                if (vm.record.estate_id) {
                    const url = vm.setUrl(`/get-municipalities/${vm.record.estate_id}`);
                    await axios.get(url).then(response => {
                        vm.municipalities = response.data;
                    }).catch(error => {
                        console.error(error);
                    });
                }
            },
            /**
             * Obtiene los Municipios del Estado seleccionado
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            async getRequestCities() {
                const vm = this;
                if (vm.loadedCities && !vm.formLoaded) {
                    return;
                }
                vm.loadedCities = true;
                vm.cities = [];
                if (vm.record.estate_id) {
                    const url = vm.setUrl(`/get-cities/${vm.record.estate_id}`);
                    await axios.get(url).then(response => {
                        vm.cities = response.data;
                    }).catch(error => {
                        console.error(error);
                    });
                }
            },
            /**
             * Obtiene las parroquias del municipio seleccionado
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            async getRequestParishes() {
                const vm = this;
                if (vm.loadedParishes && !vm.formLoaded) {
                    return;
                }
                vm.loadedParishes = true;
                vm.parishes = [];
                if (vm.record.municipality_id) {
                    const url = vm.setUrl(`/get-parishes/${vm.record.municipality_id}`);
                    await axios.get(url).then(response => {
                        vm.parishes = response.data;
                    }).catch(error => {
                        console.error(error);
                    });
                }
            },
			setAge() {
				const vm = this;
				let age = moment().diff(vm.record.birth_date, "years", false);
				vm.record.age = age > -1 ? age : "";
			},
			async loadEditData() {
                let vm = this;
                let editData = JSON.parse(vm.edit_object);
                await vm.getRequestEstates();
                vm.record.id = editData.id;
                vm.record.documentFiles = (editData.document_file) ? [editData.document_file.id] : [];
                vm.record.documentUrl = (editData.document_file) ? editData.document_file.url : '';
			},
	        showDocument() {
                return `${window.app_url}/${this.record.documentUrl}`;
        	},
            async getPayrollInstructionDegrees() {
                const vm = this;
                await axios.get(`${window.app_url}/payroll/get-instruction-degrees`).then(response => {
                    vm.payroll_instruction_degrees = response.data;
                }).catch(error => {
                    vm.payroll_instruction_degrees = [];
                });
            },
		},
		props: {
			requestid: {
                type: Number
            },
	        initialUrl: {
	            type: Object,
	            required: false,
                default: null
	        },
			isPayrollActive: {
				type: String,
                required: true,
			}
		},
		async created() {
			const vm = this;
            vm.loading = true;
			await vm.getCountries();
			await vm.getCitizenServiceRequestTypes();
			await vm.getCitizenServiceTransactionTypes();
			await vm.getCitizenServiceDepartments();
            await vm.getProfessions();
            await vm.getPayrollInstructionDegrees();
            await vm.getProcedures();
            await vm.getInstitutionSectors('sectors');
            vm.getGenders();
			vm.getNationalities();
			vm.getPayrollStaffs();

			if (this.requestid) {
				this.loadForm(this.requestid);
			} else {
                vm.loading = false;
                vm.formLoaded = true;
            }
            if (vm.edit_object) {
	            vm.loadEditData()
            }
            vm.record.phones = [];
            this.record.type_institution = false;
		},
        mounted() {
            const vm = this;
            $('#country_id').on('change', () => {
                vm.resetLoaded();
                vm.getRequestEstates();
                vm.getRequestCities();
                vm.getRequestMunicipalities();
                vm.getRequestParishes();
            });
        }
	};
</script>
