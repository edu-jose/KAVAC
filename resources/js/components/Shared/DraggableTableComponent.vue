<template>
    <section class="row" id="draggableTable">
        <div class="row col-md-12 justify-content-between d-flex">
            <div class="form-group form-inline">
                <div class="VueTables__search-field">
                    <label class="">Buscar:</label>
                    <input
                        type="text"
                        class="form-control"
                        placeholder="Buscar..."
                        v-model="search"
                    />
                </div>
            </div>
            <div class="form-group form-inline">
                <div class="VueTables__limit-field">
                    <label class="">Registros</label>
                    <select2
                        :options="perPageValues"
                        v-model="perPage"
                    ></select2>
                </div>
            </div>
        </div>
        <div class="col-md-12" style="overflow-x: auto">
            <table
                ref="draggableTable"
                class="table table-hover table-striped draggable-table table-responsive"
                style="display: table"
            >
                <thead>
                    <tr>
                        <th
                            v-for="(column, index) in columns"
                            :key="index"
                            :class="{
                                'draggable-column': true,
                                'highlighted-column':
                                    index !== dragIndex && dragIndex !== null,
                            }"
                            style="min-width: 100px"
                        >
                            <span>{{ column.name }}</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, rIndex) in visibleRows" :key="rIndex">
                        <td v-for="(column, index) in columns" :key="index">
                            <span v-if="column.type == 'text'">
                                {{ row[column.name] }}
                            </span>
                            <span
                                v-else-if="
                                    column.type == 'input' && column.group
                                "
                            >
                                <input
                                    type="text"
                                    class="form-control input-sm"
                                    data-toggle="tooltip"
                                    title="Indique el valor del campo"
                                    @input="validateInput($event, column); setOriginalInputValue(column, row.staff_id); calculateFormula(row.staff_id, row.days);"
                                    v-model="
                                        inputValues[
                                            column.name + '-' + row.staff_id
                                        ]
                                    "
                                    v-input-mask
                                    data-inputmask="'alias': 'integer', 'allowMinus': 'false'"
                                    maxlength="3"
                                    :disabled="column.disabled"
                                />
                            </span>
                            <span v-else-if="column.type == 'input_text'">
                                <input
                                    type="text"
                                    class="form-control input-sm"
                                    data-toggle="tooltip"
                                    title="Indique el valor del campo"
                                    v-model="
                                        inputValues[
                                            column.name + '-' + row.staff_id
                                        ]
                                    "
                                    v-is-text
                                    :disabled="column.disabled"
                                />
                            </span>
                            <span
                                v-else-if="column.type == 'formula'"
                            >
                                <input
                                    type="text"
                                    class="form-control input-sm"
                                    data-toggle="tooltip"
                                    title="Valor del campo según la formula"
                                    v-model="
                                        inputValues[
                                            column.name + '-' + row.staff_id
                                        ]
                                    "
                                    v-input-mask
                                    data-inputmask="'alias': 'integer', 'allowMinus': 'false'"
                                    maxlength="3"
                                    :disabled="column.disabled"
                                />
                            </span>
                            <span v-else-if="column.type == 'custom'">
                                <div
                                    class="d-flex text-center"
                                    v-for="(component, i) in custom_components"
                                    :key="i"
                                >
                                    <div
                                        class="col-6"
                                        v-if="
                                            column.name == component['column']
                                        "
                                    >
                                        <i
                                            class="fa fa-plus-circle cursor-pointer"
                                            v-if="
                                                !inputValues[
                                                    column.name +
                                                        '-' +
                                                        row.staff_id
                                                ]
                                            "
                                            @click="
                                                setCustomComponent(
                                                    component['ref'],
                                                    row.staff_id,
                                                    component['modalId']
                                                )
                                            "
                                        >
                                        </i>
                                        <i
                                            class="fa fa-eye cursor-pointer"
                                            v-else
                                            @click="
                                                setCustomComponent(
                                                    component['ref'],
                                                    row.staff_id,
                                                    component['modalId'],
                                                    inputValues[
                                                        column.name +
                                                            '-' +
                                                            row.staff_id
                                                    ],
                                                    column.field
                                                )
                                            "
                                        >
                                        </i>
                                    </div>
                                </div>
                            </span>
                            <span
                                v-else-if="column.type == 'formula_extra'"
                                :class="{
                                    'form-control': true,
                                    'text-center': true,
                                    'align-middle': true,
                                    'info-danger':
                                        column.max &&
                                        calculatedFormulaExtra[
                                            column.group + '-' + row.staff_id
                                        ] > column.max,
                                }"
                            >
                                <input
                                    type="hidden"
                                    class="form-control input-sm"
                                    data-toggle="tooltip"
                                    title="Valor calculado extra"
                                    :v-model="
                                        inputValues[
                                            index + '-' +
                                                columns[index].group + // Use current column's group
                                                '-' +
                                                row.staff_id
                                        ]
                                    "
                                    disabled
                                />
                                {{
                                    calculatedFormulaExtra[index+'-'+column.group + '-' + row.staff_id]
                                }}
                            </span>
                            <span
                                v-else-if="column.type == 'subtotal'"
                                :class="
                                    (column.max &&
                                    calculate[
                                        column.group + '-' + row.staff_id
                                    ] > column.max)
                                        ? 'form-control text-center align-middle info-danger'
                                        : 'form-control text-center align-middle'
                                "
                            >
                                <input
                                    type="hidden"
                                    class="form-control input-sm"
                                    data-toggle="tooltip"
                                    title="Indique el valor del campo"
                                    :v-model="
                                        (inputValues[
                                            'subtotal - ' +
                                                columns[index - 1].group +
                                                '-' +
                                                row.staff_id
                                        ] =
                                            calculate[
                                                columns[index - 1].group +
                                                    '-' +
                                                    row.staff_id
                                            ])
                                    "
                                    disabled
                                />
                                {{
                                    calculate[
                                        columns[index - 1].group +
                                            "-" +
                                            row.staff_id
                                    ]
                                }}
                            </span>
                            <span v-else-if="column.type == 'total'">
                                <input
                                    type="hidden"
                                    class="form-control input-sm"
                                    data-toggle="tooltip"
                                    title="Indique el valor del campo"
                                    :v-model="
                                        (inputValues['total-' + row.staff_id] =
                                            calculate['total-' + row.staff_id])
                                    "
                                    disabled
                                />
                                {{ calculate["total-" + row.staff_id] }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="VuePagination-2 row col-md-12">
                <nav class="text-center">
                    <ul class="pagination VuePagination__pagination">
                        <li
                            class="VuePagination__pagination-item page-item VuePagination__pagination-item-prev-chunk"
                            v-if="page != 1"
                        >
                            <a class="page-link" @click="changePage(1)"
                                >PRIMERO</a
                            >
                        </li>
                        <li
                            class="VuePagination__pagination-item page-item VuePagination__pagination-item-prev-chunk disabled"
                        >
                            <a class="page-link">&lt;&lt;</a>
                        </li>
                        <li
                            class="VuePagination__pagination-item page-item VuePagination__pagination-item-prev-page"
                            v-if="page > 1"
                        >
                            <a class="page-link" @click="changePage(page - 1)"
                                >&lt;</a
                            >
                        </li>
                        <li
                            :class="
                                page == number
                                    ? 'VuePagination__pagination-item page-item active'
                                    : 'VuePagination__pagination-item page-item'
                            "
                            v-for="(number, index) in filteredPageValues"
                            :key="index"
                        >
                            <a
                                class="page-link active"
                                role="button"
                                @click="changePage(number)"
                                >{{ number }}</a
                            >
                        </li>
                        <li
                            class="VuePagination__pagination-item page-item VuePagination__pagination-item-next-page"
                            v-if="page < lastPage"
                        >
                            <a class="page-link" @click="changePage(page + 1)"
                                >&gt;</a
                            >
                        </li>
                        <li
                            class="VuePagination__pagination-item page-item VuePagination__pagination-item-next-chunk disabled"
                        >
                            <a class="page-link">&gt;&gt;</a>
                        </li>
                        <li
                            class="VuePagination__pagination-item page-item VuePagination__pagination-item-prev-chunk"
                            v-if="lastPage != page"
                        >
                            <a class="page-link" @click="changePage(lastPage)"
                                >ÚLTIMO</a
                            >
                        </li>
                    </ul>
                    <p
                        class="VuePagination__count text-center col-md-12"
                    ></p>
                </nav>
            </div>
        </div>
    </section>
</template>

<script>
export default {
    data() {
        return {
            dragIndex: null,
            inputValues: {},
            originalInputValues: {},
            pageValues: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
            lastPage: "",
            page: 1,
            search: "",
            perPage: 10,
            params: {},
            perPageValues: [
                {
                    id: 10,
                    text: "10",
                },
                {
                    id: 25,
                    text: "25",
                },
                {
                    id: 50,
                    text: "50",
                },
            ],
            tmpData: [],
            diff: 0,
            maxByHolidays: {
                'domingo': 0,
                'descanso': 0,
                'feriado': 0,
            },
            maxUpdate: false
        };
    },
    props: {
        parameters: {
            type: Object,
            required: false,
            default: function () {
                return {};
            },
        },
        totalGroups: {
            type: Array,
            required: false,
            default: function () {
                return [];
            },
        },
        /**
         * @brief Estable el formato de las celdas de la tabla
         * @param {string} name Nombre de la columna
         * @param {string} type por defecto texto y permite como otro tipo de campo input
         * @param {boolean} isDraggable establece si la columna se puede mover
         * {
         *		'name': 'code',
         *		'type': 'text',
         *		'isDraggable': false
         *	},
         */
        columns: {
            type: Array,
            required: true,
            default: function () {
                return [];
            },
        },
        data: {
            type: Array,
            required: true,
            default: function () {
                return [];
            },
        },
        value: {
            type: [String, Array, Object],
            required: false,
            default: function () {
                return [];
            },
        },
        custom_components: {
            type: [Array, Object],
            required: false,
            default: function () {
                return [];
            },
        },
        subTotal: {
            type: Boolean,
            required: false,
            default: false,
        },
        total: {
            type: Boolean,
            required: false,
            default: false,
        },
        options: {
            type: Object,
            required: false,
            default: function () {
                return {
                    draggableBy: this.columns
                        .filter(function (option) {
                            return true === (option.isDraggable ?? true);
                        })
                        .map(function (option) {
                            return option.name;
                        }),
                    inputBy: this.columns
                        .filter(function (option) {
                            return "input" === option.type;
                        })
                        .map(function (option) {
                            return option.name;
                        }),
                    subTotals: [],
                    totals: [],
                };
            },
        },
    },
    watch: {
        parameters: function (parameters) {
            this.params = parameters;
        },
        inputValues: function () {
            this.$emit("input", this.inputValues);
        },
        value: function (selected) {
            this.inputValues = selected;
        },
        perPage(res) {
            this.lastPage = Math.ceil(this.data.length / this.perPage);
        },
        page(res) {
            this.changePage(res);
        },
        search(res) {
            this.page = 1;
            if (res == "") {
                this.tmpData = this.data;
            } else {
                this.tmpData = this.data.filter((item) => {
                    const nombre = item.Nombre;
                    const ficha = item.Ficha;
                    const n = item["N°"].toString();

                    return (
                        nombre.includes(res) ||
                        ficha.includes(res) ||
                        n.includes(res)
                    );
                });
            }
            this.lastPage = Math.ceil(this.tmpData.length / this.perPage);
        },
        data(newData) {
            this.tmpData = newData;
            this.lastPage = Math.ceil(this.data.length / this.perPage);
        },
    },
    methods: {
        validateInput(event, column) {
            const params = Object.keys(this.params).map(key => ({ [key]: this.params[key] }));
            const flat_params = params.flatMap((groupObject) => {
                return Object.values(groupObject);
            });

            let group = null;

            for (let i = 0; i < flat_params.length; i++) {
                for (let j = 0; j < flat_params[i].length; j++) {
                    if (flat_params[i][j].text === column.name) {
                        group = flat_params[i][j];
                        break;
                    }
                }
            }
            if (group) {
                if (parseInt(event.target.value) > parseInt(group.max_value_allowed_per_time_sheet)) {
                    this.$emit('error', [`El valor no debe ser mayor a ${group.max_value_allowed_per_time_sheet}`]);
                }
            }
        },
        handleDragStart(event, index) {
            const vm = this;
            event.dataTransfer.setData("text/plain", index);
            vm.dragIndex = index;
        },
        handleDragDrop(event, index) {
            const vm = this;
            event.preventDefault();
            const sourceIndex = event.dataTransfer.getData("text/plain");

            if (
                sourceIndex !== index &&
                vm.columns[index]["isDraggable"] === true
            ) {
                const columnToMove = vm.columns[sourceIndex];
                vm.columns.splice(sourceIndex, 1);
                vm.columns.splice(index, 0, columnToMove);
            }

            vm.dragIndex = null;
            event.target.classList.remove("over-column");
        },
        handleDragEnter(event) {
            event.preventDefault();
            event.target.classList.add("over-column");
        },

        handleDragLeave(event) {
            event.target.classList.remove("over-column");
        },

        handleDragEnd() {
            const ths = this.$refs.draggableTable.querySelectorAll("thead th");
            ths.forEach((th) => {
                th.classList.remove("highlighted-column");
                th.classList.remove("over-column");
            });
        },

        setCustomComponent(ref, id, modal, values = null, field = null) {
            const vm = this;

            if (values && field) {
                vm.$parent.$refs[ref].record[field] = values;
            }

            vm.$parent.$refs[ref].record.id = id;

            $(`#${modal}`).modal("show");
        },

        changePage(page) {
            const vm = this;
            vm.page = page;
            var pag = 0;
            while (1) {
                if (pag + 10 >= vm.page) {
                    pag += 1;
                    break;
                } else {
                    pag += 10;
                }
            }
            vm.pageValues = [];
            for (var i = 0; i < 10; i++) {
                vm.pageValues.push(pag + i);
            }
        },

        calculateFormula(staffId, days) {
            const vm = this;
            const processedFormulas = {}; // Objeto para almacenar las fórmulas procesadas
            const overflowValues = {}; // Objeto para manejar los excedentes por columna

            // Paso 1: Ordenar las columnas por el parámetro `order`
            const formulaColumns = vm.columns
                .filter((col) => col.type === 'formula');

            // Paso 2: Procesar las fórmulas en el orden especificado
            formulaColumns.forEach((col) => {
                let formula = col.formula;
                let max = col.max || Infinity; // Si no hay un `max`, se asume que es infinito
                let columnKey = `${col.name}-${staffId}`;
                let maxByClassification = {};

                vm.setMaxPerColumn(staffId, days);

                // Reemplazar todas las referencias en la fórmula con sus valores
                vm.columns.forEach((refCol) => {
                    let refName = refCol.name.split(' - ')[1]; // Obtener el nombre de referencia
                    refName = typeof(refName) != 'undefined' ? refName.replace(/\s/g, "") : refName;
                    const refValue = vm.originalInputValues[`${refCol.name}-${staffId}`]
                        ||vm.inputValues[`${refCol.name}-${staffId}`]
                        || 0;

                    const staff = vm.data.find(d => d.staff_id == staffId);

                    if (formula.includes("WORKLOAD")) {
                        formula = formula.replace(new RegExp(`\\bWORKLOAD\\b`, 'g'), staff.workload ?? 0);
                    }

                    // Reemplazar el nombre de referencia en la fórmula
                    formula = formula.replace(/\s/g, "").replace(
                        new RegExp(`\\b${refName}\\b`, 'g'), // Coincidencias exactas
                        refValue
                    );

                    if (!maxByClassification[refCol.classification_type]) {
                        maxByClassification[refCol.classification_type] = {
                            'excess': 0,
                            'values': 0
                        };
                    }

                    maxByClassification[refCol.classification_type]['values'] += parseInt(vm.inputValues[`${refCol.name}-${staffId}`] || 0);

                    // Verificar si el valor excede el máximo permitido para la referencia
                    if (vm.inputValues[`${refCol.name}-${staffId}`] > 0 && refCol.max && maxByClassification[refCol.classification_type]['values'] > refCol.max) {
                        maxByClassification[refCol.classification_type]['excess'] += parseInt(maxByClassification[refCol.classification_type]['values'] - (refCol.max + maxByClassification[refCol.classification_type]['excess']));
                    }
                });

                Object.entries(maxByClassification).forEach((maxByClass) => {
                    const orderedColumns = vm.columns
                        .filter((colFiltered) => colFiltered.classification_type == maxByClass[0])
                        .sort((a, b) => a.order - b.order);

                    let excess = maxByClass[1].excess;

                    let colIdx = 0;

                    while (excess > 0 && colIdx < orderedColumns.length) {
                        const colOrdered = orderedColumns[colIdx];
                        const key = `${colOrdered.name}-${staffId}`;
                        let value = vm.inputValues[key] || 0;

                        if (value > 0 && maxByClass[1].values > colOrdered.max) {
                            // Restar el menor entre el excess y el valor actual
                            const subtraction = Math.min(value, excess);

                            vm.inputValues[key] = value - subtraction;
                            excess -= subtraction;
                        }
                        colIdx++;
                    }
                });

                formula = formula.replace(/([A-Z]+MAX)\((\d+)\)/g, (match, funcName, value) => {
                    // Buscar la columna cuyo nombre coincida con funcName (sin el 'MAX')
                    let col = vm.columns.find(c => {
                        let refName = c.name.split(' - ')[1];
                        refName = typeof refName !== 'undefined' ? refName.replace(/\s/g, "") : refName;
                        return funcName === refName + 'MAX';
                    });

                    let newValue = parseInt(value);
                    const key = `${col.name}-${staffId}`;

                    if (col) {
                        newValue = parseInt(vm.inputValues[key] || 0);
                    }

                    return `${funcName}(${newValue})`;
                });

                formula = formula.replace(/[A-Z]+MAX\((\d+)\)/g, '$1');

                // Evaluar las expresiones entre paréntesis y ajustar valores menores a 0
                formula = formula.replace(/\(([^()]+)\)/g, (match, innerExpression) => {
                    try {
                        let innerResult = new Function(`return ${innerExpression}`)();
                        return `(${Math.max(innerResult, 0)})`; // Si el resultado es menor que 0, ajustarlo a 0
                    } catch (error) {
                        return `(0)`; // En caso de error, devolver 0
                    }
                });

                // Evaluar la fórmula
                try {
                    let result = new Function(`return ${formula}`)(); // Evaluar la fórmula
                    // Agregar cualquier excedente previo
                    if (overflowValues[columnKey]) {
                        result += overflowValues[columnKey];
                        delete overflowValues[columnKey]; // Limpiar el excedente procesado
                    }

                    // Verificar si el resultado excede el `max`
                    if (result > max) {
                        const overflow = result - max; // Calcular el excedente
                        result = max; // Limitar el resultado al máximo permitido

                        // Guardar el excedente para la siguiente columna con el siguiente `order`
                        const nextColumn = formulaColumns.find((nextCol) => nextCol.order === col.order + 1);

                        if (nextColumn) {
                            const nextColumnKey = `${nextColumn.name}-${staffId}`;
                            overflowValues[nextColumnKey] = (overflowValues[nextColumnKey] || 0) + overflow;
                        }
                    }

                    // Asignar el resultado a la columna actual
                    vm.inputValues[columnKey] = result < 0 && vm.inputValues[columnKey] == 0
                        ? 0
                        : result == 0 && vm.inputValues[columnKey] > 0
                        ? vm.inputValues[columnKey]
                        : result;
                } catch (error) {
                    vm.inputValues[columnKey] = 0; // Asignar 0 en caso de error
                }
            });
        },

        setMaxPerColumn(staffId, days) {
            const vm = this;
            const turnoSencilloRegex = /turno(s)? sencillo(s)?/i;
            const descansoRegex = /descanso(s)?/i;
            const domingoRegex = /domingo(s)?/i;
            const feriadoRegex = /feriado(s)?/i;

            vm.maxByHolidays.descanso = 0;
            vm.maxByHolidays.domingo = 0;
            vm.maxByHolidays.feriado = 0;

            vm.columns.forEach((refCol) => {
                let inputKey = `${refCol.name}-${staffId}`;
                let inputValue = vm.inputValues[inputKey];

                let value = (typeof inputValue === 'number' || typeof inputValue === 'string') && !isNaN(inputValue) ? parseInt(inputValue) : 0;

                if (descansoRegex.test(refCol.classification_type)) {
                    vm.maxByHolidays.descanso += value;
                }

                if (domingoRegex.test(refCol.classification_type)) {
                    vm.maxByHolidays.domingo += value;
                }

                if (feriadoRegex.test(refCol.classification_type)) {
                    vm.maxByHolidays.feriado += value;
                }

                if (isNaN(vm.maxByHolidays.descanso)) vm.maxByHolidays.descanso = 0;
                if (isNaN(vm.maxByHolidays.domingo)) vm.maxByHolidays.domingo = 0;
                if (isNaN(vm.maxByHolidays.feriado)) vm.maxByHolidays.feriado = 0;
            });

            ['descanso', 'domingo', 'feriado'].forEach(tipo => {
                if (vm.maxByHolidays[tipo] < 0) {
                    vm.maxByHolidays[tipo] = 0;
                }
            });

            let totalExtra = vm.maxByHolidays.descanso + vm.maxByHolidays.domingo + vm.maxByHolidays.feriado;
            totalExtra = days - totalExtra;

            vm.columns.forEach((refCol) => {
                if (turnoSencilloRegex.test(refCol.classification_type)) {
                    refCol.max = refCol.originalMax >= totalExtra ? refCol.originalMax : totalExtra;
                }
            });

            vm.maxByHolidays = {
                'domingo': 0,
                'descanso': 0,
                'feriado': 0,
            };
        },

        setOriginalInputValue(column, staffId) {
            const vm = this;
            // Inicializar originalInputValues si no existe
            if (!vm.originalInputValues) {
                vm.originalInputValues = {};
            }

            vm.originalInputValues[column.name + '-' + staffId] = vm.inputValues[column.name + '-' + staffId];
        }
    },
    created() {
    },
    mounted() {
        const vm = this;
        const intervalId = setInterval(() => {
            const ths = vm.$refs.draggableTable.querySelectorAll("thead th");
            if (ths.length > 0) {
                ths.forEach((th, index) => {
                    th.draggable = vm.columns[index]["isDraggable"];
                    th.addEventListener("dragstart", (e) =>
                        vm.handleDragStart(e, index)
                    );
                    th.addEventListener("dragenter", (e) =>
                        vm.handleDragEnter(e, index)
                    );
                    th.addEventListener("dragleave", (e) =>
                        vm.handleDragLeave(e, index)
                    );
                    th.addEventListener("dragover", (e) => e.preventDefault());
                    th.addEventListener("drop", (e) =>
                        vm.handleDragDrop(e, index)
                    );
                    th.setAttribute("contenteditable", false);
                    th.onselectstart = function () {
                        return false;
                    };
                });
                clearInterval(intervalId);
            }
        }, 1000);

        vm.lastPage = Math.ceil(vm.data.length / vm.perPage);
        vm.tmpData = vm.data;
    },
    computed: {
		filteredPageValues() {
			return this.pageValues.filter(number => number <= this.lastPage);
		},
        calculate: function () {
            const vm = this;
            let groups = {};
            vm.data.forEach((row, rIndex) => {
                vm.columns.forEach((column) => {
                    if (column.group) {
                        groups[column.group + "-" + row.staff_id] =
                            (vm.inputValues[column.name + "-" + row.staff_id]
                                ? parseFloat(
                                      vm.inputValues[
                                          column.name + "-" + row.staff_id
                                      ]
                                  )
                                : 0) +
                            (groups[column.group + "-" + row.staff_id]
                                ? parseFloat(
                                      groups[column.group + "-" + row.staff_id]
                                  )
                                : 0);

                        if (vm.totalGroups.includes(column.group)) {
                            groups["total-" + row.staff_id] =
                                (groups["total-" + row.staff_id]
                                    ? parseFloat(groups["total-" + row.staff_id])
                                    : 0) +
                                (vm.inputValues[column.name + "-" + row.staff_id]
                                    ? parseFloat(
                                          vm.inputValues[
                                              column.name + "-" + row.staff_id
                                          ]
                                      )
                                    : 0);
                        }
                    }
                });
            });
            return groups;
        },
        calculatedFormulaExtra: function() {
            const vm = this;
            const formulaExtraValues = {};

            vm.data.forEach((row, dataIndex) => {
                vm.columns.forEach((column, index) => {
                    if (column.type === 'formula_extra') {
                        const currentFormulaExtraKey = `${index}-${column.group}-${row.staff_id}`;
                        let previousValue = 0;
                        let previousMax = 0;

                        if (index > 0) {
                            const previousColumn = vm.columns[index - 1];
                            if (previousColumn.group === 'TURNOS' && previousColumn.type === 'subtotal') {
                                const prevSubtotalKey = `subtotal - ${previousColumn.group}-${row.staff_id}`;
                                previousValue = parseFloat(vm.inputValues[prevSubtotalKey]) || 0;
                                previousMax = previousColumn.max || 0;
                                vm.diff = previousValue - previousMax;
                            } else if (previousColumn.type === 'formula_extra') {
                                const prevFormulaExtraKey = `${index-1}-${previousColumn.group}-${row.staff_id}`;
                                previousValue = formulaExtraValues[prevFormulaExtraKey] || 0;
                                previousMax = previousColumn.max || 0;
                            }
                        }

                        const currentMax = column.max || 0;
                        let calculatedValue = 0;

                        if (vm.diff > currentMax) {
                            calculatedValue = currentMax;
                            vm.diff = vm.diff - currentMax;
                        } else if (vm.diff > 0) {
                            calculatedValue = vm.diff;
                            vm.diff = 0;
                        }

                        formulaExtraValues[currentFormulaExtraKey] = calculatedValue;
                        vm.inputValues[`formula_extra - ${column.group}-${row.staff_id}`] = calculatedValue;
                    }
                });
            });
            return formulaExtraValues;
        },

        headers: function () {
            return this.columns.map((option) => option.name);
        },
        visibleRows() {
            const vm = this;
            let records = vm.tmpData;
            const startIndex = (vm.page - 1) * vm.perPage;
            const endIndex = startIndex + parseInt(vm.perPage);

            return records.slice(startIndex, endIndex).map((item, index) => ({
                ...item,
                index: startIndex + index + 1,
            }));
        },
    },
};
</script>
