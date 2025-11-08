/**
 * Componente para la gestión de notificaciones
 *
 * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('workattendance-setting-notification', () => import(
    /* webpackChunkName: "workattendance-setting-notification" */
    './components/settings/WorkAttendanceNotificationsComponent.vue'
));

/**
 * Componente para la gestión de notificaciones
 *
 * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('workattendance-setting-holiday', () => import(
    /* webpackChunkName: "workattendance-setting-holiday" */
    './components/settings/WorkAttendanceHolidaysComponent.vue'
));

/**
 * Componente para la gestión de notificaciones
 *
 * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('workattendance-dashboard-graphs', () => import(
    /* webpackChunkName: "workattendance-dashboard-graphs" */
    './components/dashboard/WorkAttendanceGraphsComponent.vue'
));

/**
 * Componente para mostrar el histórico de asistencia
 *
 * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('workattendance-history', () => import(
    /* webpackChunkName: "workattendance-history" */
    './components/reports/WorkAttendanceHistoryListComponent.vue'
));

/**
 * Componente para editar datos de asistencia
 *
 * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('workattendance-history-edit', () => import(
    /* webpackChunkName: "workattendance-history-edit" */
    './components/reports/WorkAttendanceHistoryListEditComponent.vue'
));
/**
 * Componente para registrar datos de asistencia
 *
 * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('workattendance-history-new', () => import(
    /* webpackChunkName: "workattendance-history-new" */
    './components/reports/WorkAttendanceHistoryListNewComponent.vue'
));

/**
 * Componente para mostrar el histórico de asistencia individual
 *
 * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('workattendance-history-individual', () => import(
    /* webpackChunkName: "workattendance-history-individual" */
    './components/reports/WorkAttendanceHistoryIndividualComponent.vue'
));

/**
 * Componente para mostrar el histórico de asistencia por Departamento
 *
 * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('workattendance-history-by-department', () => import(
    /* webpackChunkName: "workattendance-history-by-department" */
    './components/reports/WorkAttendanceHistoryByDepartmentComponent.vue'
));

/**
 * Componente para configurar el horario laboral
 *
 * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('workattendance-schedule', () => import(
    /* webpackChunkName: "workattendance-schedule" */
    './components/settings/schedules/WorkAttendanceScheduleComponent.vue'
));

/**
 * Componente para la gestion de asistencia a actividades externas
 *
 * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('workattendance-external-activity-list', () => import(
    /* webpackChunkName: "workattendance-external-activity-list" */
    './components/external-activities/WorkAttendanceExternalActivityListComponent.vue'
));

/**
 * Componente para el registro y actualización de datos de asistencia a actividades externas
 *
 * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('workattendance-external-activity-form', () => import(
    /* webpackChunkName: "workattendance-external-activity-form" */
    './components/external-activities/WorkAttendanceExternalActivityFormComponent.vue'
));

/**
 * Componente para la gestion de horarios personalizados
 *
 * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('workattendance-custom-schedule-list', () => import(
    /* webpackChunkName: "workattendance-custom-schedule-list" */
    './components/custom-schedules/WorkAttendanceCustomScheduleListComponent.vue'
));

/**
 * Componente para el registro y actualización de datos de horarios personalizados
 *
 * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('workattendance-custom-schedule-form', () => import(
    /* webpackChunkName: "workattendance-custom-schedule-form" */
    './components/custom-schedules/WorkAttendanceCustomScheduleFormComponent.vue'
));

/**
 * Componente para ver información de datos de horarios personalizados
 *
 * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('workattendance-custom-schedule-info', () => import(
    /* webpackChunkName: "workattendance-custom-schedule-info" */
    './components/custom-schedules/WorkAttendanceCustomScheduleInfoComponent.vue'
));

/**
 * Componente para la gestion de permisos de asistencia
 *
 * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('workattendance-permission-list', () => import(
    /* webpackChunkName: "workattendance-permission-list" */
    './components/permissions/WorkAttendancePermissionListComponent.vue'
));

/**
 * Componente para el registro y actualización de datos de permisos de asistencia
 *
 * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('workattendance-permission-form', () => import(
    /* webpackChunkName: "workattendance-permission-form" */
    './components/permissions/WorkAttendancePermissionFormComponent.vue'
));

/**
 * Componente para ver información de datos de solicitudes de permisos de ausencia
 *
 * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('workattendance-permission-info', () => import(
    /* webpackChunkName: "workattendance-permission-info" */
    './components/permissions/WorkAttendancePermissionInfoComponent.vue'
));

Vue.mixin({
    methods: {
        /**
         * Obtiene los datos de las Unidades, Dependencias o Departamentos
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        async getDepartments() {
            const _self = this;
            _self.departments = [];
            await axios.get(`${window.app_url}/work-attendance/get-departments`).then(response => {
                /** Obtiene los departamentos */
                _self.departments = response.data;
            }).catch(error => {
                console.error(error);
            });
        },
        /**
         * Obtiene los datos de los cargos registrados
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        getPositions(field = 'positions') {
            const vm = this;
            vm[field] = [];
            axios.get(`${window.app_url}/work-attendance/get-positions`).then(response => {
                vm[field] = [
                    {
                        id: '',
                        text: 'Seleccione...'
                    },
                    ...response.data.records
                ];
            });
        },
        async getStaffs() {
            const _self = this;
            await axios.get(`${window.app_url}/work-attendance/get-staffs`)
                .then(response => {
                    _self.payroll_staffs = [
                        {id: '', text: 'Seleccione...'},
                        ...response.data.staffs.map(staff => {
                            return {
                                id: staff.id,
                                text: staff.full_name
                            };
                        })
                    ];
                })
                .catch(error => {
                    console.error(error);
                });
        },
        setEmployments(field = 'record') {
            this.employments = [{
                id: '',
                text: 'Seleccione...'
            }];
            if (!this[field].position_id) {
                return;
            }
            let employments = this.positions.find(position => position.id == this[field].position_id && position.payroll_employments.length > 0);

            if (employments && employments.payroll_employments.length > 0) {
                this.employments = [{
                    id: '',
                    text: 'Seleccione...'
                },...employments.payroll_employments.map(employment => {
                    return {
                        id: employment.id,
                        text: employment.payroll_staff.first_name + ' ' + employment.payroll_staff.last_name
                    }
                })];
            }
        },
        diffAttendanceTime(entry_time, exit_time) {
            if (entry_time.includes('null') || exit_time.includes('null')) {
                return '00:00';
            }
            const diffTime = this.diff_datetimes(exit_time, entry_time, 'YYYY-MM-DD HH:mm:ss');
            const diffDays = diffTime.days > 0 ? diffTime.days + " día(s) " : "";
            const hours = diffTime.hours.toString().padStart(2, '0');
            const minutes = diffTime.minutes.toString().padStart(2, '0');
            const seconds = diffTime.seconds.toString().padStart(2, '0');
            return `${diffDays}${hours}:${minutes}:${seconds}`;
        },
        /**
         * Establece los datos del gráfico
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        setChart() {
            const _self = this;
            const ctx = document.getElementById('workattendance_chart');
            if (_self.graph !== null) {
                _self.graph.destroy();
            }

            const dataBar = {
                labels: ['Personal'],
                datasets: [{
                    label: '% Asistencia',
                    data: [_self.total_attendance_percent],
                    backgroundColor: 'rgba(44, 168, 255, 1)',
                    borderColor: 'rgba(44, 168, 255, 1)',
                    borderWidth: 1
                }, {
                    label: '% Inasistencia',
                    data: [_self.total_absence_percent],
                    backgroundColor: 'rgba(249, 99, 50, 1)',
                    borderColor: 'rgba(249, 99, 50, 1)',
                    borderWidth: 1
                }]
            };
            const dataLine = {
                labels: ['Personal'],
                datasets: [{
                    label: 'Asistencia',
                    data: [_self.total_attendance_percent],
                    backgroundColor: 'rgba(44, 168, 255, 1)',
                    borderColor: 'rgba(44, 168, 255, 1)',
                    borderWidth: 1
                }, {
                    label: 'Inasistencia',
                    data: [_self.total_absence_percent],
                    backgroundColor: 'rgba(249, 99, 50, 1)',
                    borderColor: 'rgba(249, 99, 50, 1)',
                    borderWidth: 1
                }]
            };
            const dataPie = {
                labels: ['Asistencia', 'Inasistencia'],
                datasets: [{
                    label: 'Porcentaje',
                    data: [_self.total_attendance_percent, _self.total_absence_percent],
                    backgroundColor: [
                        'rgba(44, 168, 255, 1)',
                        'rgba(249, 99, 50, 1)'
                    ],
                    borderColor: [
                        'rgba(44, 168, 255, 1)',
                        'rgba(249, 99, 50, 1)'
                    ],
                    borderWidth: 1
                }]
            };
            let graphData = dataBar;
            if (_self.graph_type === 'line') {
                graphData = dataLine;
            } else if (_self.graph_type === 'pie') {
                graphData = dataPie;
            }
            _self.graph = new Chart(ctx, {
                type: _self.graph_type,
                data: graphData,
                options: {
                    title: {
                        display: true,
                        text: 'Porcentaje de asistencia e inasistencia del personal'
                    },
                    responsive: true,
                    tooltips: {
                        enabled: true,
                        callbacks: {
                            label: function(tooltipItem, data) {
                                const label = data.datasets[tooltipItem.datasetIndex].label || '';
                                const percent = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                                const roundedPercent = isNaN(Number(percent)) ? '0.00' : Number(percent).toFixed(2);
                                return `${label}: ${roundedPercent}%`;
                            }
                        }
                    },
                    //maintainAspectRatio: false,
                    scales: (_self.graph_type === 'pie') ? {} : {
                        yAxes: [{
                            ticks: {
                                beginAtZero: false,
                                max: _self.graph_max,
                                min: _self.graph_min
                            }
                        }]
                    },
                }
            });
        },
    }
})