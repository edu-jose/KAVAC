<template>
    <div class="col-md-12" id="workingHours">
        <form-errors :listErrors="errors">
        </form-errors>

        <div class="row" v-if="recordexists == false">
            <div class="col-md-12" id="workingHours">
                <p>Seleccione los días laborales</p>
                <div class="day-checkboxes" style="
                    display: flex;
    flex-wrap: wrap;
    gap: 10px;">
                    <!-- Loop through days of the week and create a checkbox for each -->
                    <label v-for="day in daysOfWeek" :key="day" class="checkbox-label" style="
                        display: flex;
    align-items: left;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    color: #333;">
                        <input type="checkbox" :value="day" v-model="selected_days" />
                        <span class="custom-checkbox" style="
                            width: 18px;
    height: 18px;
    /* border: 2px solid #4CAF50; */
    /* border-radius: 4px; */
    display: inline-block;
    /* margin-right: 8px; */
    "></span>
                        {{ day }}
                    </label>
                </div>
                <div class="col-md-4">
                    <div class="form-group is-required">
                        <label for="from">Desde:</label>
                        <input type="time" id="from" class="form-control input-sm" v-model="record.from"
                            @input="timeDifference">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group is-required">
                        <label for="to">Hasta:</label>
                        <input type="time" id="to" class="form-control input-sm" v-model="record.to"
                            @input="timeDifference">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="working_hours">Cantidad de horas laborales:</label>
                        <input type="number" id="working_hours" class="form-control input-sm"
                            v-model="record.working_hours" default="0" disabled>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="recordexists == false">
            <div class="d-flex justify-content-end mt-2">
                <button class="btn btn-default btn-icon btn-round" data-toggle="tooltip" type="button"
                    title="Borrar datos del formulario" aria-label="Borrar datos del formulario" @click="reset">
                    <i class="fa fa-eraser"></i>
                </button>
                <button class="btn btn-warning btn-icon btn-round" data-toggle="tooltip" type="button"
                    title="Cancelar y regresar" aria-label="Cancelar y regresar" @click="redirect_back(back_url)">
                    <i class="fa fa-ban"></i>
                </button>
                <button class="btn btn-success btn-icon btn-round" data-toggle="tooltip" type="button"
                    title="Guardar registro " aria-label="Guardar registro" @click="saveRecord">
                    <i class="fa fa-save"></i>
                </button>
            </div>
        </div>
        <div>
            <div class="row">
                <v-client-table :columns="columns" :data="records" :options="table_options">
                    <div slot="working_days" slot-scope="props">
                        {{ getStringOfDays() }}
                    </div>
                    <div slot="from" slot-scope="props">
                        {{ convertTo12HourFormat(props.row?.from) }}
                    </div>
                    <div slot="to" slot-scope="props">
                        {{ convertTo12HourFormat(props.row?.to) }}
                    </div>
                    <div slot="working_hours" slot-scope="props">
                        {{ parseInt(props.row?.working_hours) }}
                    </div>
                    <div slot="id" slot-scope="props">
                        <div class="d-inline-flex">
                            <button @click="updateRecord
                                " class="btn btn-warning btn-xs btn-icon btn-action" v-has-tooltip
                                title="Modificar registro" aria-label="Modificar registro" data-toggle="tooltip"
                                type="button" :disabled="isUpdating == true">
                                <i class="fa fa-edit"></i>
                            </button>
                        </div>
                    </div>
                </v-client-table>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            record: {
                working_days: [], // To store selected days
                from: '',
                to: '',
                working_hours: 0,
            },
            recordexists: false,
            isUpdating: false,
            selected_days: [],
            daysOfWeek: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo',],
            records: [],
            columns: ['working_days', 'from', 'to', 'working_hours', 'id'],
            errors: [],
        };
    },
    props: {
        back_url: {
            required: true,
            type: String,
        },
    },
    methods: {
        updateRecord() {
            const vm = this;
            vm.isUpdating = true;
            vm.recordexists = false;
            vm.selected_days = vm.record.working_days.map(item => item.day);
        },
        convertTo12HourFormat(time24) {
            if (!time24 || time24 === '') {
                return 'Hora no valida';
            }
            const [hours, minutes] = time24.split(':');
            const hour = parseInt(hours);
            const ampm = hour < 12 ? 'AM' : 'PM';
            const newHour = hour % 12 === 0 ? 12 : hour % 12;
            return `${newHour}:${minutes} ${ampm}`;
        },
        getStringOfDays() {
            if (!this.record.working_days || this.record.working_days.length === 0) {
                return '';
            }
            const vm = this;
            let days = [];
            days = vm.record.working_days.map(item => item.day);

            return days.join(', ');
        },
        reset() {
            this.record = {
                selected_days: [],
                from: '',
                to: '',
            };
            this.errors = [];
            this.selected_days = [];
        },
        redirect_back(url) {
            window.location.href = url;
        },
        timeDifference() {
            if (!this.record.from || !this.record.to) {
                return 0; // Return 0 if any time is missing
            }

            const momentStartTime = moment(this.record.from, 'hh:mm');
            const momentEndTime = moment(this.record.to, 'hh:mm');
            const diffInMinutes = momentEndTime.diff(momentStartTime, 'minutes');
            const hours = Math.floor(diffInMinutes / 60);
            const minutes = diffInMinutes % 60;
            this.record.working_hours = hours < 0 ? -1 * hours : hours;

            // const currentDate = new Date().now();
            // const startTime = new Date(`{currentDate.getFullYear()}-01-01T${this.record.from}:00`);
            // const endTime = new Date(`{currentDate.getFullYear()}-01-01T${this.record.to}:00`);

            // Calculate difference in milliseconds
            // const diffInMs = endTime - startTime;

            // Convert milliseconds to hours
            // const diffInHours = diffInMs / (1000 * 60 * 60);
            // this.record.working_hours = diffInHours >= 0 ? Math.floor(diffInHours) : 0;
        },
        validate() {
            this.errors = [];
            let isValid = false;

            if (this.selected_days.length < 1) {
                this.errors.push("Debe elegir al menos un dia laboral.");
            }
            // Ensure both times are set
            if (!this.record.from) {
                this.errors.push("La hora de inicio ('Desde') debe estar definida.");
            }
            if (!this.record.to) {
                this.errors.push("La hora de fin ('Hasta') debe estar definida.");
                return isValid;
            }

            // Convert `from` and `to` to Date objects for comparison
            const startTime = new Date(`1970-01-01T${this.record.from}:00`);
            const endTime = new Date(`1970-01-01T${this.record.to}:00`);

            // Validation: "from" and "to" should be different
            if (this.record.from == this.record.to) {
                this.errors.push("La hora de inicio ('Desde') y la hora de fin ('Hasta') deben ser diferentes.");
            }

            // Validation: "from" should not be greater than "to"
            if (this.record.from && this.record.to && startTime > endTime) {
                this.errors.push("La hora de inicio ('Desde') no puede ser mayor que la hora de fin ('Hasta').");
            }

            // Validation: "to" should not be less than "from"
            if (this.record.from && this.record.to && endTime < startTime) {
                this.errors.push("La hora de fin ('Hasta') no puede ser menor que la hora de inicio ('Desde').");
            }

            isValid = this.errors.length > 0 ? false : true;

            return isValid;
        },
        saveRecord() {
            const vm = this;
            const days = [];
            if (vm.validate()) {
                for (let day of vm.selected_days) {
                    days.push({ day });
                }
                vm.record.working_days = days;
            }
            if (vm.isUpdating) {
                axios.put(vm.setUrl('/projecttracking/work-days/' + vm.record.id), vm.record)
                    .then(response => {
                        location.href = vm.setUrl('/projecttracking/settings');
                    })
                    .catch(error => {
                        console.error(error);
                    });
            } else {
                axios.post(vm.setUrl('/projecttracking/work-days'), vm.record)
                    .then(response => {
                        location.href = vm.setUrl('/projecttracking/settings');
                    })
                    .catch(error => {
                        console.error(error);
                    });
            }
        },
    },
    mounted() {
        const vm = this;
        axios.get(`${window.app_url}/projecttracking/work-days`)
            .then(response => {
                vm.records = response.data.records;
                vm.record = vm.records.length > 0 ? vm.records[0] : vm.record;
                vm.record.working_days = vm.records.length > 0 ? JSON.parse(response.data.records[0].working_days) : [];
                vm.recordexists = vm.records.length > 0 ? true : false;
            })
            .catch(error => {
                console.error(error);
            });
    },
    created() {
        this.table_options.headings = {
            from: "Desde",
            to: "Hasta",
            working_days: "Dias Laborales",
            working_hours: "Horas Laborales",
            id: "Acción",
        };
        this.table_options.columnsClasses = {
            from: "col-md-2",
            to: "col-md-2",
            working_days: "col-md-2",
            working_hours: "col-md-2",
            id: "col-md-2",
        };
    },
};
</script>
