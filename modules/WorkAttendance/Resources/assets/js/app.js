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
 * Componente para mostrar el histórico de asistencia individual
 *
 * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('workattendance-history-individual', () => import(
    /* webpackChunkName: "workattendance-history-individual" */
    './components/reports/WorkAttendanceHistoryIndividualComponent.vue'
));

/**
 * Componente para mostrar el histórico de asistencia individual
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

Vue.mixin({
    methods: {
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
                    ...response.data.records];
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
    }
})