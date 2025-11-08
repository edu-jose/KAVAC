<template>
    <section id="ProjectTrackingTaskForm">
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
                <div class="col-md-1" id="project">
                    <div class="form-group">
                        <label>Proyecto:</label>
                        <div class="col-md-12">
                            <div class="custom-control custom-switch" data-toggle="tooltip"
                                title="Indique si es un proyecto">
                                <input type="radio" class="custom-control-input" id="active_project" name="active"
                                    @click="reset" v-model="record.active" value="project">
                                <label class="custom-control-label" for="active_project"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-1" id="subproject">
                    <div class="form-group">
                        <label>Subproyecto:</label>
                        <div class="col-md-12">
                            <div class="custom-control custom-switch" data-toggle="tooltip"
                                title="Indique si es un subproyecto">
                                <input type="radio" class="custom-control-input" id="active_subproject" name="active"
                                    @click="reset" v-model="record.active" value="subproject">
                                <label class="custom-control-label" for="active_subproject"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2" id="product">
                    <div class="form-group">
                        <label>Producto:</label>
                        <div class="col-md-12">
                            <div class="custom-control custom-switch" data-toggle="tooltip"
                                title="Indique si es un producto">
                                <input type="radio" class="custom-control-input" id="active_product" name="active"
                                    @click="reset" v-model="record.active" value="product">
                                <label class="custom-control-label" for="active_product"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="record.active == 'project'" class="col-md-4">
                    <div class="form-group is-required">
                        <label>Proyecto:</label>
                        <select2 :options="projects_list" id="project_name" data-toggle="tooltip"
                            v-model="record.project_name"
                            @input="getActivitiesByProject(),
                            getMinDate(record.project_name, 'project'),
                            getMaxDate(record.project_name, 'project')">
                        </select2>
                    </div>
                </div>
                <div v-if="record.active == 'subproject'" class="col-md-4">
                    <div class="form-group is-required">
                        <label>Subproyecto:</label>
                        <select2 :options="subprojects_list" id="subproject_name" data-toggle="tooltip"
                            v-model="record.subproject_name"
                            @input="getActivitiesBySubProject(),
                            getMinDate(record.subproject_name, 'sub_project'),
                            getMaxDate(record.subproject_name, 'sub_project')">
                        </select2>
                    </div>
                </div>
                <div v-if="record.active == 'product'" class="col-md-4">
                    <div class="form-group is-required">
                        <label>Producto:</label>
                        <select2 :options="products_list" id="product_name" data-toggle="tooltip"
                            v-model="record.product_name"
                            @input="getActivitiesByProduct(),
                            getMinDate(record.product_name, 'product'),
                            getMaxDate(record.product_name, 'product')">
                        </select2>
                    </div>
                </div>
                <div v-if="activity_plans_list.length > 0" class="col-md-4">
                    <div class="form-group is-required" name="category">
                        <label>Actividad:</label>
                        <select2 :options="activity_plans_list" id="activity" data-toggle="tooltip"
                            title="Seleccione la Actividad (requerido)" v-model="record.activity_plan_id"
                            @input="getTasks()">
                        </select2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group is-required">
                        <label>Nombre:</label>
                        <input type="text" id="name" placeholder="Nombre" data-toggle="tooltip"
                            title="Ingrese el nombre de la tarea (requerido)" class="form-control input-sm"
                            v-model="record.name" />
                    </div>
                </div>
                <div v-if="(activity_employers_list.length > 0)" class="col-md-4">
                    <div class="form-group is-required" name="employer">
                        <label>Responsable de la tarea:</label>
                        <select2 :options="activity_employers_list" id="employer" data-toggle="tooltip"
                            title="Seleccione la persona responsable de la tarea (requerido)"
                            v-model="record.employers_id">
                        </select2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group is-required" name="category">
                        <label>Prioridad:</label>
                        <select2 :options="priorities_list" id="priority" data-toggle="tooltip"
                            title="Seleccione el nivel de prioridad de la tarea (requerido)"
                            v-model="record.priority_id">
                        </select2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group is-required">
                        <label for="start_date">Fecha de inicio:</label>
                        <input type="date" id="start_date" placeholder="Fecha inicial" class="form-control input-sm"
                            data-toggle="tooltip" title="Ingrese la fecha inicial"
                            v-model="record.start_date"
                            :min="minDate"
                            :max="maxDate">
                    </div>
                </div>
                <div v-if="task_id && record.activity_status_id == pausedStatusId" class="col-md-4">
                    <div class="form-group is-required">
                        <label for="new_end_date">Nueva fecha de culminación:</label>
                        <input type="date" id="new_end_date" placeholder="Nueva fecha final"
                            class="form-control input-sm no-restrict" data-toggle="tooltip"
                            title="Ingrese la nueva fecha de culminación" v-model="record.new_end_date"
                            :min="minDate"
                            :max="maxDate">
                    </div>
                </div>
                <div v-if="task_id && record.activity_status_id == pausedStatusId" class="col-md-2">
                    <div class="form-group is-required">
                        <label for="cut_off_time">Hora límite</label>
                        <input
                            id="cut_off_time"
                            type="text"
                            class="form-control input-sm"
                            placeholder="HH:MM am/pm"
                            data-toggle="tooltip"
                            title="Indique la hora límite de la actividad en formato 12 horas (ej. 04:00 pm)"
                            v-model="record.cut_off_time"
                            v-input-mask data-inputmask-regex="^(0[1-9]|1[0-2]):[0-5][0-9] (am|pm)$"
                        >
                    </div>
                </div>
                <div v-else class="col-md-4">
                    <div class="form-group is-required">
                        <label for="end_date">Fecha de culminación:</label>
                        <input
                            type="date"
                            id="end_date"
                            placeholder="Fecha final"
                            class="form-control input-sm no-restrict"
                            data-toggle="tooltip"
                            title="Ingrese la fecha de culminación"
                            v-model="record.end_date"
                            :min="minDate"
                            :max="maxDate"
                            >
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Peso de Importancia:</label>
                        <input type="text" min="1" max="100"
                                id="weight"
                                placeholder="1-100"
                                class="form-control input-sm"
                                data-toggle="tooltip"
                                v-input-mask data-inputmask-regex="^([1-9][0-9]?|100)$"
                                title="Indique el peso de importancia"
                                v-model="record.weight"
                            >
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group is-required" name="category">
                        <label>Estatus de la Actividad:</label>
                        <select2 :options="activity_statuses_list" id="activity_status_id" data-toggle="tooltip"
                            title="Seleccione el Estatus de la actividad (requerido)"
                            v-model="record.activity_status_id" @input="resetFields">
                        </select2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group is-required" name="category">
                        <label>Tipo de tarea:</label>
                        <select2 :options="task_types" id="task_type_id" data-toggle="tooltip"
                            title="Seleccione el Tipo de tarea (requerido)"
                            v-model="record.task_type_id">
                        </select2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group is-required" name="tags">
                        <label>Etiquetas:</label>
                        <v-multiselect track_by="text" :options="tags"
                            :hide_selected="false"
                            :close_on_select="false"
                            id="tags"
                            data-toggle="tooltip"
                            title="Indique las Etiquetas"
                            v-model="record.tags"
                            style="margin-top: -20px;">
                        </v-multiselect>
                    </div>
                </div>
                <div v-if="(activity_employers_list.length > 0)" class="col-md-4">
                    <div class="form-group is-required" name="reviewer">
                        <label>Revisor:</label>
                        <select2 :options="activity_employers_list" id="reviewer" data-toggle="tooltip"
                            title="Seleccione la persona responsable de revisar la tarea (requerido)"
                            v-model="record.reviewer_id">
                        </select2>
                    </div>
                </div>
                <div v-if="(activity_employers_list.length > 0)" class="col-md-4">
                    <div class="form-group is-required" name="approver">
                        <label>Aprobador:</label>
                        <select2 :options="activity_employers_list" id="approver" data-toggle="tooltip"
                            title="Seleccione la persona responsable de aprobar la tarea (requerido)"
                            v-model="record.approver_id">
                        </select2>
                    </div>
                </div>
                <div v-if="record.activity_plan_id && (tasks_list.length > 1)" class="col-md-4">
                    <div class="form-group" name="category">
                        <label>Depende de:</label>
                        <select2 :options="tasks_list" id="depending_task_id" data-toggle="tooltip"
                            title="Seleccione si la tarea depende de otra (opcional)"
                            v-model="record.depending_task_id">
                        </select2>
                    </div>
                </div>
                <div v-if="record.depending_task_id" class="col-md-4">
                    <div class="form-group" name="category">
                        <label>Tipo de dependencia:</label>
                        <select2 :options="dependencies_type" id="dependency_type_id" data-toggle="tooltip"
                            title="Seleccione el tipo de dependencia de la tarea (opcional)"
                            v-model="record.dependency_type_id">
                        </select2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group is-required">
                        <label for="percentage">Porcentaje</label>
                            <input type="text" min="1" max="100"
                                id="percentage"
                                placeholder="1-100"
                                class="form-control input-sm"
                                data-toggle="tooltip"
                                v-input-mask data-inputmask-regex="^([1-9][0-9]?|100)$"
                                title="Indique el porcentaje"
                                v-model="record.percentage"
                            >
                    </div>
                </div>
                <div class="col-md-4" id="helpIsPrivate">
                    <div class="form-group">
                        <label>¿Es privada?</label>
                        <div class="col-md-12">
                            <div class="custom-control custom-switch" data-toggle="tooltip"
                                title="Indique si la tareas es privada">
                                <input type="radio"
                                    @click="tooglePrivacy"
                                    class="custom-control-input"
                                    id="is_private"
                                    v-model="record.is_private" value="true">
                                <label class="custom-control-label" for="is_private"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="record.is_private == 'true' || record.is_private == true" class="col-md-4">
                    <div class="form-group is-required" name="payroll_staffs">
                        <label>Visible para:</label>
                        <v-multiselect track_by="text" :options="payroll_staffs"
                            :hide_selected="false"
                            :close_on_select="false"
                            id="payroll_staffs"
                            data-toggle="tooltip"
                            title="Indique los tipos de productos"
                            v-model="record.payroll_staffs"
                            style="margin-top: -20px;">
                        </v-multiselect>
                    </div>
                </div>
                <div class="col-md-12" id="helpTaskDescription">
                    <div class="form-group is-required">
                        <label>Descripción:</label>
                        <ckeditor
                            :editor="ckeditor.editor"
                            id="description"
                            data-toggle="tooltip"
                            title="Ingrese la descripción de la tarea"
                            :config="ckeditor.editorConfig"
                            class="form-control"
                            tag-name="textarea"
                            rows="3"
                            v-model="record.description"
                        >
                        </ckeditor>
                    </div>
                </div>
                <div class="col-md-12" style="padding-top: 1rem;">
                <h6
                    class="card-title"
                >
                    Subtareas
                    <i
                        class="fa fa-plus-circle cursor-pointer"
                        @click="addSubTasks()"
                    ></i>
                </h6>
                    <div class="row" v-for="(subTask, u) in record.subTasks" :key="u">
                        <div class="col-md-4">
                            <div class="form-group is-required">
                                <label for="subTask_name">Nombre:</label>
                                <input
                                    type="text"
                                    id="subTask_name"
                                    class="form-control input-sm"
                                    data-toggle="tooltip"
                                    title="Nombre de la subtarea"
                                    v-model="subTask.name"
                                >
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group is-required">
                                <label for="subTask_description">Descripción:</label>
                                <input
                                    type="text"
                                    id="subTask_description"
                                    class="form-control input-sm"
                                    data-toggle="tooltip"
                                    title="Descripción de la subtarea"
                                    v-model="subTask.description"
                                >
                            </div>
                        </div>
                        <div class="col-1">
                            <div class="form-group">
                                <button
                                    class="mt-4 btn btn-sm btn-danger btn-action"
                                    type="button"
                                    @click="removeRow(u, record.subTasks)"
                                    title="Eliminar este dato" data-toggle="tooltip"
                                >
                                    <i class="fa fa-minus-circle"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-2" v-if="!isEditMode && !isUpdateMode">
                        <button class="btn btn-xs btn-icon btn-primary btn-custom btn-new" style="width: 5%;"
                            data-toggle="tooltip" title="Agregar tarea" aria-label="Agregar tarea"
                            @click.prevent="addTask">
                            <i class="fa fa-plus-circle"></i>
                        </button>
                    </div>
                    <div class="d-flex justify-content-end mt-2" v-else-if="isEditMode && !isUpdateMode">
                        <button class="btn btn-xs btn-icon btn-primary btn-custom btn-new" style="width: 10%;"
                            data-toggle="tooltip" title="Guardar tarea" aria-label="Guardar tarea"
                            @click.prevent="saveTask(record.id)">
                            <i class="fa fa-plus-circle"></i>
                        </button>
                    </div>
                </div>
                <div class="modal-body modal-table text-center">
                    <div v-if="tasks.length > 0">
                        <v-client-table :columns="columns" :data="tasks" :options="table_options"
                            v-if="tasks.length > 0">
                            <div slot="number" slot-scope="props" class="text-center">
                                {{ getTaskNumber(props.row.id) }}
                            </div>
                            <div slot="associate_to" slot-scope="props" class="text-center">
                                <div v-if="props.row.project_name">
                                    {{ 'Proyecto: ' + getEntityName(props.row.project_name, projects_list) }}
                                </div>
                                <div v-else-if="props.row.product_name">
                                    {{ 'Producto: ' + getEntityName(props.row.product_name, products_list) }}
                                </div>
                                <div v-else>
                                    {{ 'Subproyecto: ' + getEntityName(props.row.subproject_name, subprojects_list) }}
                                </div>
                            </div>
                            <div slot="activity_status" slot-scope="props" class="text-center">
                                {{ getEntityName(props.row.activity_status_id, activity_statuses_list) }}
                            </div>
                            <div slot="priority" slot-scope="props" class="text-center">
                                {{ getEntityName(props.row.priority_id, priorities_list) }}
                            </div>
                            <div slot="id" slot-scope="props" class="text-center">
                                <div class="d-inline-flex">
                                    <button @click="editTask(props.row.id)"
                                        class="btn btn-warning btn-xs btn-icon btn-action btn-tooltip"
                                        title="Modificar registro" aria-label="Modificar registro" data-toggle="tooltip"
                                        data-placement="bottom" type="button">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button @click="deleteTask(props.row.id)"
                                        class="btn btn-danger btn-xs btn-icon btn-action btn-tooltip"
                                        title="Eliminar registro" aria-label="Eliminar registro" data-toggle="tooltip"
                                        data-placement="bottom" type="button">
                                        <i class="fa fa-trash-o"></i>
                                    </button>
                                </div>
                            </div>
                        </v-client-table>
                    </div>

                </div>
            </div>
        </div>
        <div class="card-footer pull-right" id="helpParamButtons">
            <button class="btn btn-default btn-icon btn-round" data-toggle="tooltip" type="button"
                title="Borrar datos del formulario" @click="reset">
                <i class="fa fa-eraser"></i>
            </button>
            <button type="button" class="btn btn-warning btn-icon btn-round" data-toggle="tooltip"
                title="Cancelar y regresar" @click="redirect_back(route_list)">
                <i class="fa fa-ban"></i>
            </button>
            <button type="button" @click="saveRecords()" data-toggle="tooltip" title="Guardar registro"
                class="btn btn-success btn-icon btn-round">
                <i class="fa fa-save"></i>
            </button>
        </div>
    </section>
</template>
<script>
export default {
    props: {
        task_id: {
            type: Number
        },
        datesToValidate: {
            type: Array,
        }
    },
    data() {
        return {
            record: {
                id: '',
                project_name: '',
                subproject_name: '',
                product_name: '',
                activity_plan_id: '',
                name: '',
                description: '',
                employers_id: '',
                priority_id: '',
                start_date: '',
                end_date: '',
                new_end_date: '',
                cut_off_time: '',
                weight: '1',
                activity_status_id: '',
                depending_task_id: '',
                dependency_type_id: '',
                task_type_id: '',
                tags: '',
                active: '',
                subTasks: [],
                tasks: [],
                percentage: '1',
                reviewer_id: '',
                approver_id: '',
                is_private: false,
                payroll_staffs: [],
            },
            minDate: '',
            maxDate: '',
            isEditMode: false,
            isUpdateMode: false,
            tasks_added: 0,
            pausedStatusId: null,
            tasks: [],
            errors: [],
            projects_list: [],
            subprojects_list: [],
            products_list: [],
            activity_plans_list: [],
            activity_employers_list: [],
            priorities_list: [],
            activity_statuses_list: [],
            tasks_list: [],
            dependencies_type: [],
            task_types: [],
            tags: [],
            payroll_staffs: [],
            records: [],
            columns: [
                'number',
                'name',
                'associate_to',
                'employer_name',
                'end_date',
                'activity_status',
                'priority',
                'weight',
                'id'
            ],
        }
    },
    methods: {
        saveRecords() {
            const vm = this;
            if (vm.isEditMode) {
                vm.errors.push('Debes culminar la ediciòn del registro para guardar.');
                return;
            }
            if (!vm.record.tasks && !vm.isUpdateMode) {
                if (!vm.validateForm()) {
                    vm.errors.push('Debes agregar al menos una tarea.');
                    return;
                }
            }
            vm.createRecord('projecttracking/tasks');
        },
        validateForm() {
            const vm = this;
            vm.errors = [];
            let isValid = true;
            if (vm.isEditMode) {
                isValid = false;
                vm.push('Debe guardar los cambios de la tarea actual antes de guardar.');
            }
            if (!vm.record.active) {
                isValid = false;
                vm.errors.push('Debe elegir una opción entre proyecto, subproyecto o producto.');
            }
            if (!vm.record.project_name && vm.record.active == 'project') {
                isValid = false;
                vm.errors.push('El campo Proyecto es obligatorio.');
            }
            if (!vm.record.subproject_name && vm.record.active == 'subproject') {
                isValid = false;
                vm.errors.push('El campo Subproyecto es obligatorio.');
            }
            if (!vm.record.product_name && vm.record.active == 'product') {
                isValid = false;
                vm.errors.push('El campo Producto es obligatorio.');
            }
            if (!vm.record.employers_id) {
                isValid = false;
                vm.errors.push('El campo responsable de la tarea es obligatorio.');
            }
            if (!vm.record.name) {
                isValid = false;
                vm.errors.push('El campo Nombre de la tarea es obligatorio.');
            }
            if (!vm.record.description) {
                isValid = false;
                vm.errors.push('El campo Descripción de la tarea es obligatorio.');
            }
            if (!vm.record.priority_id) {
                isValid = false;
                vm.errors.push('El campo Prioridad es obligatorio.');
            }
            if (!vm.record.start_date) {
                isValid = false;
                vm.errors.push('El campo Fecha de inicio es obligatorio.');
            }
            if (!vm.record.end_date) {
                isValid = false;
                vm.errors.push('El campo Fecha de finalización es obligatorio.');
            }
            if (vm.record.new_end_date !== "") {
                isValid = false;
                vm.errors.push('El campo Nueva fecha de culminación es obligatorio.');
            }
            if (vm.record.cut_off_time !== "") {
                isValid = false;
                vm.errors.push('El campo Hora límite es obligatorio.');
            }
            if (!vm.record.activity_status_id) {
                isValid = false;
                vm.errors.push('El campo estatus de la actividad es obligatorio.');
            }
            if (!vm.record.weight) {
                isValid = false;
                vm.errors.push('El campo Peso es obligatorio.');
            }
            if (vm.record.subTasks.length > 0) {
                for (let subTask of vm.record.subTasks) {
                    if (!subTask.name) {
                        isValid = false;
                        vm.errors.push('El campo Nombre de la subtarea es obligatorio.');
                    }
                    if (!subTask.description) {
                        isValid = false;
                        vm.errors.push('El campo Descripción de la subtarea es obligatorio.');
                    }
                }
            }
            if (!vm.record.reviewer_id) {
                isValid = false;
                vm.errors.push('El campo Revisor de la tarea es obligatorio.');
            }
            if (!vm.record.approver_id) {
                isValid = false;
                vm.errors.push('El campo Aprobador de la tarea es obligatorio.');
            }
            if (!vm.record.percentage) {
                isValid = false;
                vm.errors.push('El campo Porcentaje de la tarea es obligatorio.');
            }
            if (vm.record.is_private == "true" && vm.record.payroll_staffs.length == 0) {
                isValid = false;
                vm.errors.push('El campo Visible para es obligatorio.');
            }
            return isValid;
        },
        getEntityName(entityId, entitiesList) {
            const entity = entitiesList.find(entity => entity.id == entityId);
            return entity ? entity.text : '';
        },

        /**
         * Agrega una nueva columna para las subtareas
         *
         * @author Pedro Contreras <pmcontreras@cenditel.gob.ve> | <pdrocont@gmail.com>
         */
        addSubTasks() {
            const vm = this;

            vm.record.subTasks.push({
                name: '',
                description: '',
            });
        },
        saveTask(taskId) {
            const vm = this;
            const editedTask = {
                id: taskId,
                project_name: vm.record.project_name,
                subproject_name: vm.record.subproject_name,
                product_name: vm.record.product_name,
                activity_plan_id: vm.record.activity_plan_id,
                name: vm.record.name,
                description: vm.record.description,
                priority_id: vm.record.priority_id,
                start_date: vm.record.start_date,
                end_date: vm.record.end_date,
                new_end_date: vm.record.new_end_date,
                cut_off_time: vm.record.cut_off_time,
                activity_status_id: vm.record.activity_status_id,
                depending_task_id: vm.record.depending_task_id,
                dependency_type_id: vm.record.dependency_type_id,
                task_type_id: vm.record.task_type_id,
                tags: vm.record.tags,
                weight: vm.record.weight,
                subTasks: vm.record.subTasks,
                percentage: vm.record.percentage,
                reviewer_id: vm.record.reviewer_id,
                approver_id: vm.record.approver_id,
                employers_id: vm.record.employers_id,
                employer_name: vm.getEntityName(vm.record.employers_id, vm.activity_employers_list),
                is_private: vm.record.is_private,
                payroll_staffs: vm.record.payroll_staffs
            };
            vm.tasks = vm.tasks.map(task => task.id == taskId ? editedTask : task);
            vm.reset();
            vm.record.tasks = vm.tasks;
            vm.isEditMode = false;
        },
        async editTask(taskId) {
            const vm = this;
            vm.isEditMode = true;
            const task = vm.tasks.find(task => task.id == taskId);
            vm.record.id = task.id;
            vm.record.project_name = task.project_name;
            vm.record.subproject_name = task.subproject_name;
            vm.record.product_name = task.product_name;
            vm.record.activity_plan_id = task.activity_plan_id;
            vm.record.name = task.name;
            vm.record.description = task.description;
            vm.record.priority_id = task.priority_id;
            vm.record.start_date = task.start_date;
            vm.record.end_date = task.end_date;
            vm.record.active = task.selector;
            if (task.project_name) {
                await vm.getActivitiesByProject();
            } else if (task.subproject_name) {
                vm.getActivitiesBySubProject();
            } else {
                vm.getActivitiesByProduct();
            }
            vm.record.activity_status_id = task.activity_status_id;
            vm.record.new_end_date = task.new_end_date,
            vm.record.cut_off_time = task.cut_off_time,
            vm.record.depending_task_id = task.depending_task_id;
            vm.record.dependency_type_id = task.dependency_type_id;
            vm.record.task_type_id = task.task_type_id;
            vm.record.tags = task.tags;
            vm.record.weight = task.weight;
            vm.record.tasks = vm.tasks;
            vm.record.subTasks = task.subTasks;
            vm.record.percentage = task.percentage;
            vm.record.reviewer_id = task.reviewer_id;
            vm.record.approver_id = task.approver_id;
            vm.record.employers_id = task.employers_id;
            vm.record.is_private = task.is_private;
            setTimeout(function () {
                vm.record.payroll_staffs = task.payroll_staffs;
            }, 1000);
        },
        deleteTask(taskId) {
            const vm = this;
            vm.tasks = vm.tasks.filter(task => task.id != taskId);
            vm.record.tasks = vm.tasks;
        },
        addTask() {
            const vm = this;
            if (!vm.validateForm()) {
                return;
            }
            vm.tasks.push({
                id: vm.tasks_added,
                selector: vm.record.active,
                project_name: vm.record.project_name,
                subproject_name: vm.record.subproject_name,
                product_name: vm.record.product_name,
                activity_plan_id: vm.record.activity_plan_id,
                name: vm.record.name,
                description: vm.record.description,
                priority_id: vm.record.priority_id,
                start_date: vm.record.start_date,
                end_date: vm.record.end_date,
                new_end_date: vm.record.new_end_date,
                cut_off_time: vm.record.cut_off_time,
                activity_status_id: vm.record.activity_status_id,
                depending_task_id: vm.record.depending_task_id,
                dependency_type_id: vm.record.dependency_type_id,
                task_type_id: vm.record.task_type_id,
                tags: vm.record.tags,
                weight: vm.record.weight,
                subTasks: vm.record.subTasks,
                percentage: vm.record.percentage,
                reviewer_id: vm.record.reviewer_id,
                approver_id: vm.record.approver_id,
                employers_id: vm.record.employers_id,
                employer_name: vm.getEntityName(vm.record.employers_id, vm.activity_employers_list),
                is_private: vm.record.is_private,
                payroll_staffs: vm.record.payroll_staffs
            });
            vm.tasks_added++;
            vm.reset();
            vm.record.tasks = vm.tasks;
        },
        getTaskNumber(param) {
            const vm = this
            let index = vm.tasks.findIndex(task => task.id == param);
            return index + 1
        },
        reset() {
            const vm = this;
            vm.record = {
                id: '',
                project_name: '',
                subproject_name: '',
                product_name: '',
                activity_plan_id: '',
                name: '',
                description: '',
                employers_id: '',
                priority_id: '',
                start_date: '',
                end_date: '',
                new_end_date: '',
                cut_off_time: '',
                active: '',
                weight: '1',
                activity_status_id: '',
                depending_task_id: '',
                dependency_type_id: '',
                task_type_id: '',
                tags: '',
                subTasks: [],
                percentage: '1',
                reviewer_id: '',
                approver_id: '',
                is_private: false,
                payroll_staffs: []
            };
            vm.isEditMode = false;
            vm.pausedStatusId = null,
            vm.errors = [];
            vm.activity_plans_list = [];
            vm.activity_employers_list = [];
            vm.records = [];
        },
        resetForm() {
            const vm = this;
            vm.record = {
                id: '',
                project_name: '',
                subproject_name: '',
                product_name: '',
                activity_plan_id: '',
                name: '',
                description: '',
                employers_id: '',
                priority_id: '',
                start_date: '',
                end_date: '',
                new_end_date: '',
                cut_off_time: '',
                active: '',
                weight: '1',
                activity_status_id: '',
                pausedStatusId: '',
                activity_status: '',
                depending_task_id: '',
                dependency_type_id: '',
                subTasks: [],
                percentage: '1',
                reviewer_id: '',
                approver_id: '',
                is_private: false,
                payroll_staffs: []
            };
            vm.isEditMode = false;
            vm.pausedStatusId = null,
            vm.errors = [];
            vm.activity_plans_list = [];
            vm.activity_employers_list = [];
            vm.records = [];
        },

        resetFields() {
            const vm = this;
            if (vm.record.activity_status_id != vm.pausedStatusId) {
                vm.record.new_end_date = '';
                vm.record.cut_off_time = '';
            }
        },
        tooglePrivacy() {
            this.record.is_private = !this.record.is_private;
        },
        async getActivitiesByProject() {
            const vm = this;
            if (!vm.record.project_name) {
                return;
            }
            vm.activity_plans_list = [];
            await axios.get(`${window.app_url}/projecttracking/get-activities-by-project/${vm.record.project_name}`).then(response => {
                vm.activity_plans_list = response.data.activities_by_project;
            });
            await vm.getPersonalByProject();
        },
        async getPersonalByProject() {
            const vm = this;
            vm.activity_employers_list = [],
                await axios.get(`${window.app_url}/projecttracking/get-personal-by-project/${vm.record.project_name}`).then(response => {
                    vm.activity_employers_list = response.data.personal_by_project;
                });
        },

        async getActivitiesBySubProject() {
            const vm = this;
            if (!vm.record.subproject_name) {
                return;
            }
            vm.activity_plans_list = [],
                await axios.get(`${window.app_url}/projecttracking/get-activities-by-subproject/${vm.record.subproject_name}`).then(response => {
                    vm.activity_plans_list = response.data.activities_by_subproject;
                });
            await vm.getPersonalBySubProject();
        },

        async getPersonalBySubProject() {
            const vm = this;
            vm.activity_employers_list = [],
            await axios.get(`${window.app_url}/projecttracking/get-personal-by-subproject/${vm.record.subproject_name}`).then(response => {
                vm.activity_employers_list = response.data.personal_by_subproject;
            });
        },

        async getActivitiesByProduct() {
            const vm = this;
            if (!vm.record.product_name) {
                return;
            }
            vm.activity_plans_list = [],
            await axios.get(`${window.app_url}/projecttracking/get-activities-by-product/${vm.record.product_name}`).then(response => {
                vm.activity_plans_list = response.data.activities_by_product;
            });
            await vm.getPersonalByProduct();
        },

        async getPersonalByProduct() {
            const vm = this;
            vm.activity_employers_list = [],
            await axios.get(`${window.app_url}/projecttracking/get-personal-by-product/${vm.record.product_name}`).then(response => {
                vm.activity_employers_list = response.data.personal_by_product;
            });
        },

        async getProjectsByActivityPlan() {
            const vm = this;
            await axios.get(`${window.app_url}/projecttracking/get-projects-by-activity-plan`).then(response => {
                vm.projects_list = response.data;
            });
        },
        async getSubprojectsByActivityPlan() {
            const vm = this;
            await axios.get(`${window.app_url}/projecttracking/get-subprojects-by-activity-plan`).then(response => {
                vm.subprojects_list = response.data;
            });
        },
        async getProductsByActivityPlan() {
            const vm = this;
            await axios.get(`${window.app_url}/projecttracking/get-products-by-activity-plan`).then(response => {
                vm.products_list = response.data;
            });
        },
        async getPriorities() {
            const vm = this;
            await axios.get(`${window.app_url}/projecttracking/get-priorities`).then(response => {
                vm.priorities_list = response.data;
            });
        },
        async getActivityStatuses() {
            const vm = this;
            await axios.get(`${window.app_url}/projecttracking/get-activity-statuses`).then(response => {
                vm.activity_statuses_list = response.data;
                const pausedStatus = vm.activity_statuses_list.find(status => status.text == 'Pausada ');
                if (pausedStatus) {
                    vm.pausedStatusId = pausedStatus.id;
                }
            });
        },
        async getTasks() {
            const vm = this;
            const response = await axios.get(`${window.app_url}/projecttracking/get-tasks`, {
                    params: {
                        id: vm.task_id,
                        activity_plan_id: vm.record.activity_plan_id
                    }
            });

            vm.tasks_list = response.data;
        },
        async getDependenciesType() {
            const vm = this;
            await axios.get(`${window.app_url}/projecttracking/get-dependencies-type`).then(response => {
                vm.dependencies_type = response.data;
            });
        },
        async getTaskTypes() {
            const vm = this;
            await axios.get(`${window.app_url}/projecttracking/get-task-types`).then(response => {
                vm.task_types = response.data;
            });
        },
        async getTags() {
            const vm = this;
            const response = await axios.get(`${window.app_url}/projecttracking/get-tags`);

            vm.tags = response.data;
        },

        async getMinDate(id, type = null) {
            const date = this.datesToValidate.find(date => date.id === parseInt(id) && (date.type === type));
            this.minDate = date ? date.start_date : null;
        },
        async getMaxDate(id, type = null) {
            const date = this.datesToValidate.find(date => date.id === parseInt(id) && (date.type === type));
            this.maxDate = date ? date.end_date : null;
        },

        async loadForm(id) {
            const vm = this;
            vm.loading = true;
            vm.isUpdateMode = true;
            vm.record.id = id;
            try {
                const response = await axios.get(`${window.app_url}/projecttracking/task/vue-info/${id}`);
                const records = response.data.records;
                if (typeof (records != "undefined")) {

                    vm.record.is_private = records.is_private;
                    vm.record.activity_plan_id = records.activity_plan_id;
                    vm.record.subTasks = records.sub_tasks ? records.sub_tasks : [];
                    
                    if (records.project_name) {
                        vm.record.active = 'project';
                        vm.record.project_name = records.project_name;
                        await vm.getActivitiesByProject();
                        await vm.getMinDate(vm.record.project_name, 'project');
                        await vm.getMaxDate(vm.record.project_name, 'project');
                    } else if (records.subproject_name) {
                        vm.record.active = 'subproject';
                        vm.record.subproject_name = records.subproject_name;
                        await vm.getActivitiesBySubProject();
                        await vm.getMinDate(vm.record.subproject_name, 'sub_project');
                        await vm.getMaxDate(vm.record.subproject_name, 'sub_project');
                    } else {
                        vm.record.active = 'product';
                        vm.record.product_name = records.product_name;
                        await vm.getActivitiesByProduct();
                        await vm.getMinDate(vm.record.product_name, 'product');
                        await vm.getMaxDate(vm.record.product_name, 'product');
                    }
                    
                    vm.record.activity_status_id = records.activity_status_id;
                    await vm.getTasks();
                    vm.record.name = records.name;
                    vm.record.description = records.description;
                    vm.record.priority_id = records.priority_id;
                    vm.record.start_date = records.start_date;
                    vm.record.end_date = records.end_date;
                    vm.record.weight = records.weight;
                    vm.record.dependency_type_id = records.dependency_type_id;
                    vm.record.task_type_id = records.task_type_id;
                    vm.record.percentage = records.percentage;
                    vm.record.reviewer_id = records.reviewer_id;
                    vm.record.approver_id = records.approver_id;
                    vm.record.employers_id = records.employers_id;
                    vm.record.new_end_date = response.data.records.new_end_date;
                    vm.record.cut_off_time = response.data.records.cut_off_time;
                    vm.record.tags = records.tags ? records.tags : [];
                    vm.record.payroll_staffs = records.payroll_staffs ? records.payroll_staffs : [];
                    vm.record.depending_task_id = records.depending_task_id;
                }
            } catch (error) {
                console.log(error);
            } finally {
                vm.loading = false;
            }
        },
    },
    async created() {
        const vm = this;
        vm.table_options.headings = {
            'number': 'N°',
            'name': 'Nombre de la Tarea',
            'associate_to': 'Asociada a',
            'employer_name': 'Responsable de la Tarea',
            'end_date': 'Fecha de Entrega',
            'activity_status': 'Estatus',
            'priority': 'Prioridad',
            'weight': 'Peso',
            'id': 'Acción'
        };
        vm.table_options.sortable = [
            'number',
            'name',
            'associate_to',
            'employers_name',
            'end_date',
            'activity_status',
            'priority',
            'weight'
        ];
        vm.table_options.filterable = [
            'number',
            'name',
            'associate_to',
            'employers_name',
            'end_date',
            'activity_status',
            'priority',
            'weight'
        ];
        vm.table_options.columnsClasses = {
            'number': 'text-center',
            'name': 'text-center',
            'associate_to': 'text-center',
            'employers_name': 'text-center',
            'end_date': 'text-center',
            'activity_status': 'text-center',
            'priority': 'text-center',
            'weight': 'text-center',
            'id': 'text-center'
        };
    },
    async mounted() {
        const vm = this;
        if (!vm.isUpdateMode) {
            vm.reset();
        }

        await Promise.all([
            vm.getProjectsByActivityPlan(),
            vm.getSubprojectsByActivityPlan(),
            vm.getProductsByActivityPlan(),
            vm.getPriorities(),
            vm.getDependenciesType(),
            vm.getTaskTypes(),
            vm.getTags(),
            vm.getPayrollStaffs(),
            vm.getActivityStatuses()
        ]);

        if (vm.task_id) {
            await vm.loadForm(vm.task_id);
        }
    }
};
</script>