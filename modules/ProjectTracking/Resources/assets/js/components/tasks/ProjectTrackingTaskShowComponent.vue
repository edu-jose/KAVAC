<template>
    <section id="ProjectTrackingTaskShow">
        <div class="task-details"
            style="display: grid;
            grid-template-columns: 4fr 1fr;
            grid-gap: 60px;
            padding-bottom: 40px;
            padding-top: 20px;
            padding-left: 40px;
            padding-right: 40px;"
        >
            <div class="task-info">
                <h3>{{ project_tracking_task.name }}</h3>
                <span
                    class="badge rounded-pill"
                    v-bind:style="{
                        width: 'auto',
                        color: 'white',
                        backgroundColor: project_tracking_task.activity_status.color,
                    }"
                >
                    {{ project_tracking_task.activity_status.name }}
                </span>
                <span
                    class="badge rounded-pill"
                    v-bind:style="{
                        width: 'auto',
                        color: 'white',
                        backgroundColor: project_tracking_task.task_type.color,
                    }"
                >
                    {{ project_tracking_task.task_type.name }}
                </span>
                <br>
                <br>
                <div v-html="project_tracking_task.description"></div>
                <br>
                <div class="task-comments">
                    <h5>Comentarios publicados:</h5>
                    <div class="col-md-12"
                        style="
                            max-height: 67.5vh;
                            max-width: 120vh;
                            overflow-y: scroll;
                            overflow-x: hidden;"
                    >
                        <div v-for="(comment, index) in task_comments"
                            :key="index"
                            style="
                                border: 1px solid #2ca8ff;
                                padding: 10px;
                                margin-bottom: 10px;
                                border-radius: 5px;
                                background-color: #fbfafd;"
                        >
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="row" style="margin: 0px 0;">
                                        <h6 class="col-md-12">
                                            {{ comment.user_name + ' ' + setTimeFormat(comment.date) }}
                                            <button
                                                v-if="comment.user_id == user_id"
                                                @click="editComment(comment.id, comment.comment, index)"
                                                class="btn btn-primary btn-xs btn-icon btn-action btn-tooltip"
                                                title="Editar comentario" aria-label="Editar comentario"
                                                data-toggle="tooltip" data-placement="bottom" type="button"
                                                style="float: right;"
                                            >
                                                <i class="fa fa-edit"></i>
                                            </button>
                                        </h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="row" style="margin: 1px 0;">
                                        <span class="col-md-12" v-html="comment.comment"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="col-md-12">
                        <div class="form-group">
                            <ckeditor
                                :editor="ckeditor.editor"
                                id="description"
                                data-toggle="tooltip"
                                placeholder="Escribe un comentario"
                                title="Ingrese un comentario"
                                :config="ckeditor.editorConfig"
                                class="form-control"
                                tag-name="textarea"
                                rows="3"
                                v-model="record.comment"
                            >
                            </ckeditor>
                        </div>
                    </div>
                    <div class="col-md-12 form-group">
                        <button
                            type="button"
                            class="btn btn-primary btn-sm btn-round btn-modal-save"
                            @click="createRecord('projecttracking/task-comment'), getTaskComments()"
                        >
                            Comentar
                        </button>
                    </div>
                </div>
            </div>
            <div class="task-meta">
                <div class="meta-section">
                    <div>
                        <strong>Prioridad:</strong>
                        <br>
                        <span
                            class="badge rounded-pill"
                            v-bind:style="{
                                width: 'auto',
                                color: 'white',
                                backgroundColor: project_tracking_task.priority.color,
                            }"
                        >
                            {{ project_tracking_task.priority.name }}
                        </span>
                        <hr>
                    </div>
                    <div>
                    <strong>Etiquetas:</strong>
                    <div v-for="(tag, index) in project_tracking_task.tags" :key="index">
                        <span
                            class="badge rounded-pill"
                            v-bind:style="{
                                width: 'auto',
                                color: 'white',
                                backgroundColor: tag.color,
                            }"
                        >
                            {{ tag.name }}
                        </span>
                    </div>
                    <hr>
                    </div>
                    <div>
                        <strong>Proyecto:</strong>
                        <p>{{ project_tracking_task.project.name }}</p>
                        <hr>
                    </div>
                    <div>
                        <strong>Actividad Macro:</strong>
                        <p>{{ project_tracking_task.activity.name_activity }}</p>
                        <hr>
                    </div>
                    <div>
                        <strong>Fechas:</strong>
                        <p>{{ 'Inicio: ' + format_date(project_tracking_task.start_date, 'DD/MM/YYYY') }}</p>
                        <p>{{project_tracking_task.new_end_date ?
                            'Nueva fecha de culminación: ' +
                                format_date(project_tracking_task.new_end_date, 'DD/MM/YYYY')
                                    + ' a la hora ' + project_tracking_task.cut_off_time :
                                    'Culminación: ' + format_date(project_tracking_task.end_date, 'DD/MM/YYYY') }}</p>
                        <hr>
                    </div>
                    <div>
                        <strong>Responsable:</strong>
                        <p>{{ project_tracking_task.responsable.project_tracking_personal_register.first_name + ' ' + project_tracking_task.responsable.project_tracking_personal_register.last_name }}</p>
                        <hr>
                    </div>
                    <div>
                        <strong>Revisor:</strong>
                        <p>{{ project_tracking_task.approver.project_tracking_personal_register.first_name + ' ' + project_tracking_task.approver.project_tracking_personal_register.last_name }}</p>
                        <hr>
                    </div>
                    <div>
                        <strong>Aprobador:</strong>
                        <p>{{ project_tracking_task.reviewer.project_tracking_personal_register.first_name + ' ' + project_tracking_task.reviewer.project_tracking_personal_register.last_name }}</p>
                        <hr>
                    </div>
                    <div>
                        <strong>Peso de importancia:</strong>
                        <p>{{ project_tracking_task.weight }}</p>
                        <hr>
                    </div>
                    <div>
                        <strong>Tiempo en estatus {{ project_tracking_task.activity_status.name  }}:</strong>
                        <p>{{ project_tracking_task.time_spent}} Horas</p>
                        <hr>
                    </div>
                    <div>
                        <strong>Porcentaje:</strong>
                        <p>{{ project_tracking_task.percentage }}%</p>
                        <hr>
                    </div>
                    <div v-if="project_tracking_task.depending_task_id != null">
                        <strong>Depende de:</strong>
                        <p>{{ project_tracking_task.depending_task.name }}</p>
                        <hr>
                    </div>
                    <div v-if="project_tracking_task.dependency_type_id != null">
                        <strong>Tipo de dependencia:</strong>
                        <p>{{ project_tracking_task.dependencies_type.name }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
<script>
export default {
    props: {
        project_tracking_task: {
            type: Object
        },
        user_id: {
            type: Number
        }
    },
    data() {
        return {
            record: {
                id: '',
                comment: '',
                task_comment_id: '',
                user_id: '',
            },
            task_comments: [],
            comment_index: ''
        };
    },
    created() {
        //
    },
    mounted() {
        const vm = this;

        vm.record.task_comment_id = vm.project_tracking_task.id;
        vm.getTaskComments();
    },
    methods: {
        /**
         * Método que obtiene todos los comentarios a ser mostrados
         *
         * @author  Pedro Contreras <pmcontreras@cenditel.gob.ve> | <pdrocont@gmail.com>
         *
         * @return void
         *
         */
        async getTaskComments() {
            const vm = this;
            if (vm.record.id) {
                vm.task_comments[vm.comment_index].comment = vm.record.comment;
                return;
            }
            const response = await axios.get(`${window.app_url}/projecttracking/get-task-comments`, {
                    params: {
                        task_comment_id: this.project_tracking_task.id,
                    }
            });

            vm.task_comments = response.data;
        },

        /**
         * Método que edita un comentario
         *
         * @author  Pedro Contreras <pmcontreras@cenditel.gob.ve> | <pdrocont@gmail.com>
         *
         * @return void
         */
        editComment(id, comment, index) {
            const vm = this;

            vm.record.comment = comment;
            vm.record.id = id;
            vm.comment_index = index;
        },

        reset() {
            const vm = this;

            vm.record.id = '';
            vm.record.comment = '';
            vm.errors = [];
        },

        setTimeFormat(time) {
            return moment(time).format("DD/MM/YYYY HH:mm:ss a");
        },
    },
};
</script>