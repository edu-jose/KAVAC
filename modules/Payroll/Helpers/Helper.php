<?php

use Faker\Provider\Base;
use Illuminate\Support\Facades\DB;
use Modules\Payroll\Models\PayrollStaff;
use Illuminate\Database\Eloquent\Builder;
use Modules\Payroll\Models\PayrollRelationship;
use Modules\Payroll\Models\PayrollInactivityType;
use Modules\Payroll\Models\PayrollSalaryTabulator;
use Modules\Payroll\Models\PayrollSalaryTabulatorScale;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Payroll\Transformers\PayrollSalaryTabulatorResource;

if (!function_exists('multiexplode')) {
    /**
     * Divide una cadena de acuerdo a sus delimitadores y construye un arreglo con las subcadenas resultantes
     *
     * @method    multiexplode
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     array     $delimiters    Arreglo con los delimitadores de la cadena
     * @param     string    $string        Cadena de texto a ser procesada
     *
     * @return    array                    Arreglo con las subcadenas generadas
     */
    function multiexplode($delimiters, $string)
    {
        $ready = str_replace($delimiters, $delimiters[0], $string);
        $launch = explode($delimiters[0], $ready);
        return $launch;
    }
}

if (!function_exists('max_length')) {
    /**
     * Devuelve el valor de la cadena mas larga contenida en un arreglo
     *
     * @method    max_length
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     array     $records    Arreglo con los elementos a comparar
     *
     * @return    array                 Arreglo con las subcadenas generadas
     */
    function max_length($records)
    {
        $current = '';
        foreach ($records as $record) {
            if ($current != '') {
                if (strlen($record) > strlen($current)) {
                    $current = $record;
                }
            } else {
                $current = $record;
            }
        }
        return $current;
    }
}

if (!function_exists('str_eval')) {
    /**
     * Evalua una expresión contenida en una cadena
     *
     * @method    str_eval
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     array|string     $string    Cadena que contiene la expresión a evaluar
     *
     * @return    array|boolean
     */
    function str_eval($string)
    {
        $string = str_replace(',', '', $string);
        if (!str_contains($string, 'select(')) {
            $string = 'select(' . $string . ')';
        }

        try {
            $calc = DB::select(DB::raw($string));
        } catch (\Exception $error) {
            return false;
        }
        $col = '?column?';
        $value = $calc[0]->$col ?? $calc[0]->case;
        return $value;
    }
}

if (!function_exists('verify_assignment_old')) {
    /**
     * Evalua si un trabajador cumple con los parámetros establecidos
     *
     * @method    verify_assignment
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     array     $filters    Arreglo que contiene las expresión a evaluar
     * @param     mixed     $assignTo   Arreglo que contiene los campos a comparar
     * @param     object    $assignOptions Arreglo que contiene las opciones de comparación
     * @param     integer   $id         Identificador único del trabajador
     * @param     string    $period_start Fecha de inicio del período
     * @param     string    $period_end Fecha de fin del período
     * @param     array     $exceptions Arreglo que contiene las excepciones
     *
     * @return    boolean
     */
    function verify_assignment_old(
        $filters = [],
        $assignTo = [],
        $assignOptions = [],
        $id = null,
        $period_start = null,
        $period_end = null,
        $exceptions = []
    ) {
        $now = ($period_end) ? new DateTime($period_end) : new DateTime();
        $find = false;

        foreach ($filters as $filter) {
            $rule = null;
            foreach ($assignTo as $field) {
                if ($filter->id == 'staff') {
                    return in_array($id, $exceptions ?? []);
                }
                if ($field['id'] == $filter->id) {
                    $rule = $field;
                    break;
                }
            }
            if (isset($rule)) {
                if ($rule['id'] === 'staff_with_sons_has_scholarships') {
                    $options = [];
                    foreach ($assignOptions->where('key', $rule['id']) as $assign_option) {
                        if ($rule['type'] == 'range') {
                            $options = json_decode($assign_option['value']);
                        } elseif ($rule['type'] == 'list') {
                            array_push($options, $assign_option['assignable_id']);
                        }
                    };
                    $relationshipSon = PayrollRelationship::query()
                        ->where('name', 'Hijo(a)')
                        ->first();
                    if (!isset($relationshipSon)) {
                        $relationshipSon["id"] = 3;
                    }
                    $records = PayrollStaff::select('payroll_staffs.id')
                        ->where('payroll_staffs.id', $id)
                        ->join('payroll_socioeconomics', 'payroll_staffs.id', '=', 'payroll_socioeconomics.payroll_staff_id')
                        ->join('payroll_family_burdens as family', 'payroll_socioeconomics.id', '=', 'family.payroll_socioeconomic_id')
                        ->join('payroll_employments', 'payroll_staffs.id', '=', 'payroll_employments.payroll_staff_id')
                        ->where('payroll_employments.active', true)
                        ->where('family.payroll_relationships_id', $relationshipSon["id"])
                        ->where('family.has_scholarships', true)
                        ->whereIn('family.payroll_scholarship_types_id', $options)
                        ->get();
                } elseif ($rule['id'] === 'all_staff_with_sons') {
                    $options = [];
                    foreach ($assignOptions->where('key', $rule['id']) as $assign_option) {
                        if ($rule['type'] == 'range') {
                            $options = json_decode($assign_option['value']);
                        } elseif ($rule['type'] == 'list') {
                            array_push($options, $assign_option['assignable_id']);
                        }
                    };
                    $relationshipSon = PayrollRelationship::query()
                        ->where('name', 'Hijo(a)')
                        ->first();
                    if (!isset($relationshipSon)) {
                        $relationshipSon["id"] = 3;
                    }
                    $relationship = $rule['whereHas'];
                    //relacion con socioeconomic
                    $records = $rule['model']::query()
                        ->where('id', $id)
                        ->with($relationship['field'])
                        ->whereHas($relationship['field'], function ($q) use ($relationship, $relationshipSon, $options, $period_start, $period_end, $now) {
                            if (isset($relationship['whereHas'])) {
                                /* Filtra la información a obtener mediante relaciones */
                                $relationshipR = $relationship['whereHas'];
                                //relacion con familyburden
                                $q->whereHas($relationshipR['field'], function ($qq) use ($relationshipR, $relationshipSon, $options, $now, $period_end) {
                                    $maxNow = ($period_end)
                                        ? new DateTime($period_end)
                                        : new DateTime();
                                    $min = date('Y-m-d', mktime(0, 0, 0, 1, 1, $now->modify('-' . ($options->maximum ?? 0) . 'year')->format('Y')));
                                    $max = $maxNow->modify('-' . ($options->minimum ?? 0) . 'year');
                                    $max_Date = $max->format('Y-m-d');
                                    $qq->whereBetween("birthdate", [$min, $max_Date])->where('payroll_relationships_id', $relationshipSon["id"]);
                                });
                            }
                        })
                        ->whereHas('payrollEmployment', fn($q) => $q->where('active', true))
                        ->get();
                } elseif ($rule['id'] === 'all_staff_with_sons_studying') {
                    $options = [];
                    foreach ($assignOptions->where('key', $rule['id']) as $assign_option) {
                        if ($rule['type'] == 'range') {
                            $options = json_decode($assign_option['value']);
                        } elseif ($rule['type'] == 'list') {
                            array_push($options, $assign_option['assignable_id']);
                        }
                    };
                    $relationshipSon = PayrollRelationship::query()
                        ->where('name', 'Hijo(a)')
                        ->first();
                    if (!isset($relationshipSon)) {
                        $relationshipSon["id"] = 3;
                    }
                    $relationship = $rule['whereHas'];
                    //relacion con socioeconomic
                    $records = $rule['model']::query()
                        ->where('id', $id)
                        ->with($relationship['field'])
                        ->whereHas($relationship['field'], function ($q) use ($relationship, $relationshipSon, $options, $period_start, $period_end, $now) {
                            if (isset($relationship['whereHas'])) {
                                /* Filtra la información a obtener mediante relaciones */
                                $relationshipR = $relationship['whereHas'];
                                //relacion con familyburden
                                $q->whereHas($relationshipR['field'], function ($qq) use ($relationshipR, $relationshipSon, $options, $now, $period_end) {
                                    if (isset($relationshipR['where'])) {
                                        $qq->where('is_student', true)->where('payroll_relationships_id', $relationshipSon["id"]);
                                    }
                                });
                            }
                        })
                        ->whereHas('payrollEmployment', fn($q) => $q->where('active', true))
                        ->get();
                } elseif ($rule['id'] === 'all_survivor_staff') {
                    $options = [];
                    $id_retired = PayrollInactivityType::where('name', 'ILIKE', 'jubilado')->first();
                    $records = PayrollStaff::query()
                    ->where('has_died', true)
                    ->whereHas('payrollSurvivor', function ($query) use ($options) {
                        $query->whereNotNull('first_name')
                        ->whereNotNull('last_name')
                        ->whereNotNull('payroll_staff_id')
                        ->whereNotNull('id_number')
                        ->whereNotNull('finance_bank_id')
                        ->whereNotNull('finance_account_type_id')
                        ->whereNotNull('payroll_account_number');
                    })
                    ->whereHas('payrollEmployment', function ($query) use ($id_retired) {
                        $query->where('active', false)
                        ->where('payroll_inactivity_type_id', $id_retired->id);
                    })
                    ->get();
                } elseif ($rule['id'] === 'staff_according_position') {
                    $options = [];

                    foreach ($assignOptions->where('key', $rule['id']) as $assign_option) {
                        array_push($options, $assign_option['assignable_id']);
                    }

                    $records = PayrollStaff::query()
                        ->where('id', $id)
                        ->whereHas('payrollEmployment.payrollPositions', function ($query) use ($options) {
                            $query->whereIn('payroll_positions.id', $options);
                        })
                        ->whereHas('payrollEmployment', fn($q) => $q->where('active', true))
                        ->get();
                } elseif ($rule['id'] === 'all_staff_not_in_vacation') {
                    $result = PayrollStaff::query()
                        ->where('id', $id)
                        ->whereHas('payrollEmployment', function ($query) {
                            $query->where('active', true);
                        })
                        ->where(function ($query) use ($period_start, $period_end) {
                            $query->whereDoesntHave('payrollVacationRequests')
                                ->orWhereHas('payrollVacationRequests', function ($query) use ($period_start, $period_end) {
                                    $query
                                        ->where('status', 'approved')
                                        ->whereBetween('end_date', [$period_start, $period_end])
                                        ->orWhere('status', '!=', 'approved')
                                        ->orWhere(function ($query) use ($period_start, $period_end) {
                                            $query
                                                ->where('end_date', '<', $period_start)
                                                ->orwhere('end_date', '>', $period_end);
                                        });
                                });
                        })
                        ->count();

                    return $result > 0;
                } elseif ($rule['id'] === 'all_staff_vacation_return') {
                    $count = PayrollStaff::query()
                        ->whereHas('payrollEmployment', fn($q) => $q->where('active', true))
                        ->whereHas('payrollVacationRequests', function ($query) use ($period_start, $period_end) {
                            $query
                                ->where('status', 'approved')
                                ->where('end_date', '>=', $period_start)
                                ->where('end_date', '<=', $period_end);
                        })
                        ->find($id)
                        ?->count();
                    return $count > 0;
                } elseif ($rule['id'] === 'all') {
                    return true;
                } elseif (str_contains($rule['id'], 'all')) {
                    $options = [];
                    foreach ($assignOptions->where('key', $rule['id']) as $assign_option) {
                        if ($rule['type'] == 'range') {
                            $options = json_decode($assign_option['value']);
                        } elseif ($rule['type'] == 'list') {
                            array_push($options, $assign_option['assignable_id']);
                        }
                    };
                    if ($filter) {
                        if (isset($rule['whereHas'])) {
                            /* Filtra la información a obtener mediante relaciones */
                            $relationship = $rule['whereHas'];
                            $records = $rule['model']::query()
                                ->where('id', $id)
                                ->with($relationship['field'])
                                ->whereHas($relationship['field'], function ($q) use ($relationship, $options, $period_start, $period_end, $now) {
                                    if (isset($relationship['whereHas'])) {
                                        /* Filtra la información a obtener mediante relaciones */
                                        $relationshipR = $relationship['whereHas'];
                                        $q->whereHas($relationshipR['field'], function ($qq) use ($relationshipR, $options, $now, $period_end) {
                                            if (isset($relationshipR['where'])) {
                                                $qq->where($relationshipR['where'][0], $relationshipR['where'][1]);
                                            } elseif (isset($relationshipR['whereRaw'])) {
                                                $raw = $relationshipR['whereRaw'];
                                                $qq->select("id", "birthdate")->whereRaw("(DATE_PART('year',  '" . $now . "'::date) - DATE_PART('year', " . $raw['field'] . "::date))  > " . $options->minimum)
                                                    ->whereRaw("(DATE_PART('year',  '" . $now . "'::date) - DATE_PART('year', " . $raw['field'] . "::date))  < " . $options->maximum);
                                            } elseif (isset($relationshipR['whereYear'])) {
                                                $maxNow = ($period_end)
                                                    ? new DateTime($period_end)
                                                    : new DateTime();
                                                $min = date('Y-m-d', mktime(0, 0, 0, 1, 1, $now->modify('-' . ($options->maximum ?? 0) . 'year')->format('Y')));
                                                $max = $maxNow->modify('-' . ($options->minimum ?? 0) . 'year');
                                                $qq->whereBetween($relationshipR['whereYear'], [$min, $max]);
                                            }
                                        });
                                    } elseif (isset($relationship['where'])) {
                                        $q->where($relationship['where'][0], $relationship['where'][1]);
                                    } elseif (isset($relationship['whereDate'])) {
                                        if (isset($options->maximum)) {
                                            $date = $now->modify('-' . ($options->maximum) . 'year');
                                            $q->whereDate($relationship['whereDate'], '<=', $date);
                                        }
                                        if (($period_start && $period_end)) {
                                            $start = explode('-', $period_start);
                                            $end = explode('-', $period_end);

                                            if ($start[1] > $end[1]) {
                                                $q->whereBetween(
                                                    DB::raw("to_char(" . $relationship['whereDate'] . ", 'MM-DD')"),
                                                    [$start[1] . '-' . $start[2], '12-31']
                                                )
                                                    ->OrWhereBetween(
                                                        DB::raw("to_char(" . $relationship['whereDate'] . ", 'MM-DD')"),
                                                        ['01-01', $end[1] . '-' . $end[2]]
                                                    );
                                            } elseif ($start[1] . '-' . $start[2] == $end[1] . '-' . $end[2]) {
                                                if ($start[0] != $end[0]) {
                                                    $q->WhereBetween(
                                                        DB::raw("to_char(" . $relationship['whereDate'] . ", 'MM-DD')"),
                                                        ['01-01', '12-31']
                                                    );
                                                } else {
                                                    $q->Where(
                                                        DB::raw("to_char(" . $relationship['whereDate'] . ", 'MM-DD')"),
                                                        $start[1] . '-' . $start[2]
                                                    );
                                                }
                                            } else {
                                                $q->whereBetween(
                                                    DB::raw("to_char(" . $relationship['whereDate'] . ", 'MM-DD')"),
                                                    [$start[1] . '-' . $start[2], $end[1] . '-' . $end[2]]
                                                );
                                            }
                                        }
                                    }
                                })
                                ->whereHas('payrollEmployment', fn($q) => $q->where('active', true))
                                ->get();
                        } elseif (isset($rule['where'])) {
                            $records = $rule['model']::query()
                                ->whereHas('payrollEmployment', fn($q) => $q->where('active', true))
                                ->where('id', $id)
                                ->where($rule['where'][0], $rule['where'][1])
                                ->get();
                        }
                    }
                } else {
                    $options = [];
                    foreach ($assignOptions->where('key', $rule['id']) as $assign_option) {
                        if ($rule['type'] == 'range') {
                            $options = json_decode($assign_option['value']);
                        } elseif ($rule['type'] == 'list') {
                            array_push($options, $assign_option['assignable_id']);
                        }
                    }
                    if ((($rule['type'] == null) || ($rule['type'] == '')) && (!empty($rule['whereHas']) && !empty($rule['whereHas']['withCount']))) {
                        $relationship = $rule['whereHas'];
                        $records = PayrollStaff::whereId($id)->with(
                            [
                                $relationship['field'] => function ($q) use ($relationship) {
                                    $q->withCount($relationship['withCount']);
                                },
                            ]
                        )
                            ->whereHas('payrollEmployment', fn($q) => $q->where('active', true))
                            ->first();
                        if (isset($records)) {
                            $fieldCount = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $relationship['withCount']));
                            if ($records->{$relationship['field']}->{$fieldCount . "_count"} > 1) {
                                $find = true;
                                break;
                            }
                        }
                    } else {
                        if (isset($rule['whereHas'])) {
                            $relationship = $rule['whereHas'];
                            $records = PayrollStaff::query()
                                ->where('id', $id)
                                ->whereHas(
                                    $relationship['field'],
                                    function ($query) use ($options, $relationship) {
                                        if (isset($relationship['whereHas'])) {
                                            $relationshipR = $relationship['field'];
                                            $query->whereHas($relationshipR, function ($q) use ($options, $relationshipR) {
                                                $q->whereIn('id', $options)->get();
                                            })->get();
                                        } elseif (isset($relationship['has'])) {
                                            $query->has($relationship['has']['field']);
                                        } elseif (isset($relationship['whereNotIn'])) {
                                            $query->whereNotIn('id', $options)->get();
                                        } elseif (isset($relationship['whereIn'])) {
                                            $query->whereIn($relationship['whereIn'][0], $options);
                                        }
                                    }
                                )
                                ->whereHas('payrollEmployment', fn($q) => $q->where('active', true))
                                ->get();
                        } elseif (isset($rule['whereIn'])) {
                            $records = PayrollStaff::query()
                                ->where('id', $id)
                                ->whereIn($rule['whereIn'][0], $options)
                                ->whereHas('payrollEmployment', fn($q) => $q->where('active', true))
                                ->get();
                        } elseif (isset($rule['whereNotIn'])) {
                            $records = PayrollStaff::query()
                                ->where('id', $id)
                                ->whereNotIn($rule['whereNotIn'][0], $options)
                                ->get();
                        }
                    }
                }
            }
            if (isset($records) && empty($rule['whereHas']['withCount'])) {
                if ($records->find($id) != null) {
                    $find = true;
                    break;
                }
            }
        }

        return $find;
    }
}

if (!function_exists('verify_assignment')) {
    /**
     * Evalua si un trabajador cumple con los parámetros establecidos usando Scopes.
     *
     * @param array     $filters        Arreglo de objetos filtro (ej. [{ "id": "staff_according_position" }])
     * @param array     $assignToRules  Mapeo de ID de regla a su definición (como el array $this->assignTo)
     * @param EloquentCollection|Collection $assignOptions Colección de PayrollConceptAssignOption para el concepto actual.
     * @param integer   $staffId        Identificador único del trabajador.
     * @param string|null $period_start Fecha de inicio del período.
     * @param string|null $period_end   Fecha de fin del período.
     * @param array     $exceptions     IDs de trabajadores explícitamente incluidos (para la regla 'staff').
     *
     * @return boolean
     */
    function verify_assignment(
        array $filters,
        array $assignToRules,
        $assignOptions, // Colección de PayrollConceptAssignOption
        int $staffId,
        ?string $period_start = null,
        ?string $period_end = null,
        array $exceptions = []
    ): bool {
        $now = $period_end ? \Carbon\Carbon::parse($period_end) : now(); // Usar Carbon para facilidad

        // Mapeo de ID de Regla a Nombre de Scope (simplificado)
        // Puedes generar esto dinámicamente o mantenerlo explícito
        $scopeMap = [
            'all_staff_in_vacations' => 'allStaffInVacations',
            'staff_with_sons_has_scholarships' => 'hasSonsWithScholarships',
            'all_staff_with_sons' => 'hasSonsInAgeRange',
            'all_staff_with_sons_studying' => 'hasSonsStudying',
            'staff_according_position' => 'positionInList',
            'all_staff_not_in_vacation' => 'isNotInApprovedVacationDuring',
            'all_staff_vacation_return' => 'returnedFromVacationDuring',
            'all_disabled_staff' => ['hasDisability', [true]], // Scope + parámetros fijos
            'all_except_disabled_staff' => ['hasDisability', [false]],// Scope + parámetros fijos
            'all_studying_staff' => 'isStudying',
            'staff_master_the_languages' => 'mastersMoreThanOneLanguage',
            'staff_according_contract_type' => 'contractTypeInList',
            'staff_according_department' => 'departmentInList',
            'staff_according_position_type' => 'positionTypeInList',
            'staff_according_staff_type' => 'staffTypeInList',
            'all_staff_according_start_date' => 'startDateBetween',
            'staff_according_instruction_degree' => 'instructionDegreeInList',
            'staff_according_gender' => 'genderInList',
            'all_survivor_staff' => 'isSurvivor',
            'all_staff_who_belong_to_a_workers_union' => 'belongsToUnion',
            'all_staff_affiliated_with_the_savings_fund' => 'isAffiliatedToSavingsFund',
            'all_active_staff' => 'onlyActive', // Podría ser una base
            // 'staff' y 'all' se manejan especialmente
        ];

        // Buscar el definition array ($assignToRules) por ID para fácil acceso
        $rulesById = collect($assignToRules)->keyBy('id');

        foreach ($filters as $filter) {
            $ruleId = $filter->id ?? null;
            if (!$ruleId) {
                continue;
            }

            // --- Casos Especiales ---
            if ($ruleId === 'all') {
                return true; // Si 'all' está presente, siempre coincide
            }
            if ($ruleId === 'staff') {
                // La regla 'staff' depende *solo* de las excepciones pasadas
                return in_array($staffId, $exceptions);
            }
             // La regla 'staff_except_specified' requiere los IDs excluidos
            if ($ruleId === 'staff_except_specified') {
                $ruleDefinition = $rulesById->get($ruleId);
                $excludedIds = extractAssignOptions($assignOptions, $ruleId, $ruleDefinition['type'] ?? 'list');
                return !in_array($staffId, $excludedIds); // Coincide si NO está en la lista de excepciones
            }

            // --- Mapeo a Scope y Parámetros ---
            if (!isset($scopeMap[$ruleId])) {
                \Log::warning("Scope no mapeado para la regla de asignación: {$ruleId}");
                continue; // Saltar regla no mapeada
            }

            $scopeInfo = $scopeMap[$ruleId];
            $scopeName = is_array($scopeInfo) ? $scopeInfo[0] : $scopeInfo;
            $fixedParams = is_array($scopeInfo) ? ($scopeInfo[1] ?? []) : []; // Parámetros fijos del mapeo

            $ruleDefinition = $rulesById->get($ruleId);
            if (!$ruleDefinition) {
                \Log::warning("Definición no encontrada para la regla de asignación: {$ruleId}");
                continue;
            }

            // Extraer opciones (IDs, rangos) de $assignOptions para esta regla
            $options = extractAssignOptions($assignOptions, $ruleId, $ruleDefinition['type'] ?? null);

            // Construir parámetros para el scope
            $scopeParams = [];
            // Añadir opciones extraídas según el scope
            switch ($scopeName) {
                case 'onlyActive':
                case 'hasSonsWithScholarships':
                case 'positionInList':
                case 'contractTypeInList':
                case 'departmentInList':
                case 'positionTypeInList':
                case 'staffTypeInList':
                case 'instructionDegreeInList':
                case 'genderInList':
                     $scopeParams = [$options]; // Espera un array de IDs
                    break;
                case 'hasSonsInAgeRange':
                    $minAge = $options->minimum ?? 0;
                    $maxAge = $options->maximum ?? 30;
                    $scopeParams = [$minAge, $maxAge, $period_end ?? now()->toDateString()];
                    break;
                case 'startDateBetween':
                    // Asumiendo que el rango viene como {minimum: 'YYYY-MM-DD', maximum: 'YYYY-MM-DD'}
                    $maxDate = $options->maximum ?? null;
                    $scopeParams = [$maxDate, $period_start, $period_end ?? now()];
                    break;
                case 'isNotInApprovedVacationDuring':
                case 'returnedFromVacationDuring':
                    if (!$period_start || !$period_end) {
                        \Log::error("Fechas de periodo requeridas para scope {$scopeName} no proporcionadas.");
                         continue 2; // Saltar este filtro si faltan fechas
                    }
                    $scopeParams = [$period_start, $period_end];
                    break;
                // Scopes sin parámetros adicionales desde options (o con fijos)
                case 'hasSonsStudying':
                case 'isStudying':
                case 'mastersMoreThanOneLanguage':
                case 'hasDisability': // Los parámetros fijos se añaden después
                case 'isSurvivor':
                case 'belongsToUnion':
                case 'isAffiliatedToSavingsFund':
                    // No necesitan $options
                    break;
                default:
                    \Log::warning("Parámetros no definidos para scope {$scopeName} (Regla: {$ruleId})");
                    return false;
            }

            // Combinar parámetros extraídos con parámetros fijos
            $finalScopeParams = array_merge($scopeParams, $fixedParams);


            // --- Ejecutar la Verificación con Scope ---
            try {
                $match = PayrollStaff::where('id', $staffId)
                                    ->$scopeName(...$finalScopeParams) // Llamada dinámica al scope
                                    ->exists();

                if ($match) {
                    return true; // Coincidencia encontrada, no necesita seguir verificando
                }
            } catch (\BadMethodCallException $e) {
                \Log::error("Error llamando al scope '{$scopeName}' para la regla '{$ruleId}': {$e->getMessage()}");
                continue;
            } catch (\Exception $e) {
                \Log::error("Error verificando scope '{$scopeName}' para la regla '{$ruleId}' y trabajador ID {$staffId}: {$e->getMessage()}");
                throw $e;
            }
        } // Fin foreach $filters

        // Si se recorrieron todos los filtros y ninguno coincidió
        return false;
    }
}

if (!function_exists('findAssignableStaff')) {
    /**
     * Encuentra los IDs de los trabajadores que cumplen con al menos una de las reglas de filtro.
     * Utiliza Query Scopes definidos en el modelo PayrollStaff.
     *
     * @param array     $filters        Arreglo de objetos filtro (ej. [{ "id": "staff_according_position" }])
     * @param array     $assignToRules  Mapeo de ID de regla a su definición (como el array $this->assignTo)
     * @param EloquentCollection|Collection $assignOptions Colección de PayrollConceptAssignOption para el concepto actual.
     * @param string|null $period_start Fecha de inicio del período.
     * @param string|null $period_end   Fecha de fin del período.
     * @param array     $exceptions     IDs de trabajadores explícitamente incluidos (para la regla 'staff').
     * @param boolean   $isRestricted   Indica si la búsqueda es restringida (Si se aplican todas las reglas de filtrado).
     *
     * @return Illuminate\Database\Eloquent\Builder
     */
    function findAssignableStaff(
        array $filters = [],
        array $assignToRules = [],
        $assignOptions = null,
        ?string $period_start = null,
        ?string $period_end = null,
        array $exceptions = [],
        bool $isRestricted = false
    ): Builder {
        // Extraer los IDs de los filtros activos
        $activeFilterIds = collect($filters)->pluck('id')->filter()->unique()->all();

         // --- Construcción de la Consulta Principal ---
         $query = PayrollStaff::query(); // Empezar consulta base

        if (empty($activeFilterIds)) {
            return $query->whereRaw('1 = 0'); // No hay filtros, no hay trabajadores asignables
        }

        // --- Manejo de Casos Especiales que Anulan Otros Filtros si isRestricted es false---
        if (in_array('staff', $activeFilterIds)) {
            // Si 'staff' está presente, SOLO devuelve los IDs en $exceptions
            $query->whereIn('id', $exceptions ?? []);
        }
        if (in_array('all', $activeFilterIds)) {
             // Si 'all' está presente (y 'staff' no), devuelve todos los trabajadores sin importar su estado
            $query->whereRaw('1 = 1');
        }

        if (!$isRestricted) {
            return $query;
        }

        // Mapeo de ID de Regla a Nombre de Scope
        $scopeMap = [
            'all_staff_in_vacations' => 'allStaffInVacations',
            'all_active_staff' => 'onlyActive',
            'staff_with_sons_has_scholarships' => 'hasSonsWithScholarships',
            'all_staff_with_sons' => 'hasSonsInAgeRange',
            'all_staff_with_sons_studying' => 'hasSonsStudying',
            'staff_according_position' => 'positionInList',
            'all_staff_not_in_vacation' => 'isNotInApprovedVacationDuring',
            'all_staff_vacation_return' => 'returnedFromVacationDuring',
            'all_disabled_staff' => ['hasDisability', [true]], // Scope + parámetros fijos
            'all_except_disabled_staff' => ['hasDisability', [false]],// Scope + parámetros fijos
            'all_studying_staff' => 'isStudying',
            'staff_master_the_languages' => 'mastersMoreThanOneLanguage',
            'staff_according_contract_type' => 'contractTypeInList',
            'staff_according_department' => 'departmentInList',
            'staff_according_position_type' => 'positionTypeInList',
            'staff_according_staff_type' => 'staffTypeInList',
            'all_staff_according_start_date' => 'startDateBetween',
            'staff_according_instruction_degree' => 'instructionDegreeInList',
            'staff_according_gender' => 'genderInList',
            'all_survivor_staff' => 'isSurvivor',
            'all_staff_who_belong_to_a_workers_union' => 'belongsToUnion',
            'all_staff_affiliated_with_the_savings_fund' => 'isAffiliatedToSavingsFund',
            // 'staff_except_specified' se maneja al final
        ];

        // Buscar el definition array ($assignToRules) por ID para fácil acceso
        $rulesById = collect($assignToRules)->keyBy('id');

        // --- Construcción de la Consulta ---

        // Definir si es una consulta where o orWhere a partir de $isRestricted
        $scopeMethod = $isRestricted ? 'where' : 'orWhere';

        // Construir cláusulas para cada regla activa
        $query->where(function ($subQuery) use (
            $activeFilterIds,
            $scopeMap,
            $assignOptions,
            $rulesById,
            $period_start,
            $period_end,
            $scopeMethod,
        ) {
            foreach ($activeFilterIds as $ruleId) {
                if (!isset($scopeMap[$ruleId]) || in_array($ruleId, ['staff_except_specified', 'all', 'staff'])) {
                    // Saltar reglas sin scope mapeado o la de exclusión (se aplica después), all y staff ya han sido manejados.
                    if (!isset($scopeMap[$ruleId]) && (!in_array($ruleId, ['staff_except_specified', 'all', 'staff']))) {
                        \Log::warning("Scope no mapeado o regla no soportada en OR: {$ruleId}");
                    }
                    continue;
                }

                $scopeInfo = $scopeMap[$ruleId];
                $scopeName = is_array($scopeInfo) ? $scopeInfo[0] : $scopeInfo;
                $fixedParams = is_array($scopeInfo) ? ($scopeInfo[1] ?? []) : [];

                $ruleDefinition = $rulesById->get($ruleId) ?? null;
                if (!$ruleDefinition) {
                    \Log::warning("Definición no encontrada para la regla: {$ruleId}");
                    continue;
                }

                $options = extractAssignOptions($assignOptions, $ruleId, $ruleDefinition['type'] ?? null);

                // Construir parámetros para el scope
                $scopeParams = [];
                try {
                    switch ($scopeName) {
                        case 'onlyActive':
                        case 'hasSonsWithScholarships':
                        case 'positionInList':
                        case 'contractTypeInList':
                        case 'departmentInList':
                        case 'positionTypeInList':
                        case 'staffTypeInList':
                        case 'instructionDegreeInList':
                        case 'genderInList':
                            $scopeParams = [$options]; // Espera un array de IDs
                            break;
                        case 'allStaffInVacations':
                            $scopeParams = [$period_start];
                            break;
                        case 'hasSonsInAgeRange':
                            $minAge = $options->minimum ?? 0;
                            $maxAge = $options->maximum ?? 30;
                            $scopeParams = [$minAge, $maxAge, $period_end ?? now()->toDateString()];
                            break;
                        case 'startDateBetween':
                            // Asumiendo que el rango viene como {minimum: 'YYYY-MM-DD', maximum: 'YYYY-MM-DD'}
                            $maxDate = $options->maximum ?? null;
                            $scopeParams = [$maxDate, $period_start, $period_end ?? now()];
                            break;
                        case 'isNotInApprovedVacationDuring':
                        case 'returnedFromVacationDuring':
                            if (!$period_start || !$period_end) {
                                \Log::error("Fechas de periodo requeridas para scope {$scopeName} no proporcionadas.");
                                continue 2; // Saltar este filtro si faltan fechas
                            }
                            $scopeParams = [$period_start, $period_end];
                            break;
                        // Scopes sin parámetros adicionales desde options (o con fijos)
                        case 'hasSonsStudying':
                        case 'isStudying':
                        case 'mastersMoreThanOneLanguage':
                        case 'hasDisability': // Los parámetros fijos se añaden después
                        case 'isSurvivor':
                        case 'belongsToUnion':
                        case 'isAffiliatedToSavingsFund':
                            // No necesitan $options
                            break;
                        default:
                            \Log::warning("Parámetros no definidos para scope {$scopeName} (Regla: {$ruleId})");
                            return false;
                    }

                    // Combinar parámetros calculados y fijos
                    $finalScopeParams = array_merge($scopeParams, $fixedParams);

                    // Aplicar el scope dentro
                    $subQuery->$scopeMethod(function ($q) use ($scopeName, $finalScopeParams) {
                        $q->$scopeName(...$finalScopeParams);
                    });
                } catch (\InvalidArgumentException $e) {
                    \Log::error("Error preparando parámetros para scope {$scopeName} (Regla {$ruleId}): " . $e->getMessage());
                     continue; // Saltar este filtro si hay error
                } catch (\Exception $e) {
                    \Log::error("Error aplicando scope {$scopeName} (Regla {$ruleId}): " . $e->getMessage());
                     continue; // Saltar este filtro si hay error
                }
            } // end foreach
        }); // end $query->where (grupo OR)

        // --- Aplicar Exclusiones Finales ---
        if (in_array('staff_except_specified', $activeFilterIds)) {
            $ruleId = 'staff_except_specified';
            $ruleDefinition = $rulesById->get($ruleId) ?? null;
            if ($ruleDefinition) {
                $excludedIds = extractAssignOptions($assignOptions, $ruleId, $ruleDefinition['type'] ?? 'list');
                if (!empty($excludedIds)) {
                    $query->whereNotIn('id', $excludedIds);
                }
            }
        }

        return $query;
    }
}

/**
 * Extrae las opciones de asignación para una regla específica
 * @param Collection $assignOptions Colección de opciones de asignación
 * @param string $ruleId ID de la regla
 * @param string|null $ruleType Tipo de regla (ej. 'range', 'list')
 * @return mixed
 */
if (!function_exists('extractAssignOptions')) {
    function extractAssignOptions($assignOptions, string $ruleId, ?string $ruleType)
    {
        $optionsData = ($ruleType === 'range') ? (object)['minimum' => null, 'maximum' => null] : [];
        $filteredOptions = $assignOptions->where('key', $ruleId); // $assignOptions es una colección

        switch ($ruleType) {
            case 'range':
                $rangeData = json_decode($filteredOptions->first()['value']);
                if (isset($rangeData->minimum)) {
                    $optionsData->minimum = $rangeData->minimum;
                }
                if (isset($rangeData->maximum)) {
                    $optionsData->maximum = $rangeData->maximum;
                }
                break;
            case 'list':
                $optionsData = $filteredOptions->pluck('assignable_id')->filter()->all(); // Filtrar IDs no nulos
                break;
            default:
                break;
        }
        // Si no se encuentra el tipo de regla, se devuelve un array vacío o un objeto vacío
        return $optionsData;
    }
}

if (!function_exists('expression_format')) {
    /**
     * Convierte los parámetros de una expresión matemática en su representacion flotante
     *
     * @method    expression_format
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     string   $expression         Expresion a revisar y transformar en formato flotante
     *
     * @return    string
     */
    function expression_format($expression)
    {
        return preg_replace_callback('/\d+(\.\d+)?/', function ($match) {
            return currency_format($match[0], 4, true);
        }, $expression);
    }
}

if (!function_exists('loadBasicPayrollStaffData')) {
    /**
     * Loads basic payroll staff data from the given payroll staff and period end date.
     *
     * @method    loadBasicPayrollStaffData
     *
     * @author    Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javierrupe19@gmail.com>
     *
     * @param PayrollStaff $payrollStaff The payroll staff object.
     * @param string $period_end The end date of the period.
     *
     * @return array An array containing the following data:
     *               - full_name: The full name of the payroll staff.
     *               - id_number: The ID number of the payroll staff.
     *               - instruction_degree: The name of the payroll staff's instruction degree, or an empty string if not available.
     *               - position: The name of the payroll staff's position, or the name of the first inactive position if not available.
     *               - start_date: The start date of the payroll staff's employment.
     *               - institution_years: The number of years between the current date and the start date of the payroll staff's APN.
     */
    function loadBasicPayrollStaffData(PayrollStaff $payrollStaff, string $period_end): array
    {
        $dateNow = new DateTime($period_end);
        $yearsApn = new DateTime($payrollStaff->payrollEmployment->startDateApn);
        $institutionYears = date_diff($dateNow, $yearsApn)->format("%y");

        return [
            'full_name' => $payrollStaff->first_name . ' ' . $payrollStaff->last_name,
            'id_number' => $payrollStaff->id_number,
            'instruction_degree' => $payrollStaff->payrollProfessional->payrollInstructionDegree?->name ?? '',
            'position' => $payrollStaff->payrollEmployment->payrollPosition->name
                ?? $payrollStaff->payrollEmployment->payrollPositions()->where('active', false)->first()?->name ?? '',
            'start_date' => $payrollStaff->payrollEmployment?->start_date ?? '',
            'institution_years' => $institutionYears,
        ];
    }
}
if (!function_exists('getPayrollSalaryTabulators')) {
    /**
     * Obtiene los tabuladores salariales usados en la nómina
     *
     * @param array $concepts Conjunto de conceptos
     *
     * @return AnonymousResourceCollection Tabuladores salariales de la nómina
     */
    function getPayrollSalaryTabulators($concepts): AnonymousResourceCollection
    {
        $salaryTabulatorIds = [];
        foreach ($concepts as $concept) {
            $conceptId = $concept['field']->id;
            /* Se hace la busqueda de los tabuladores */
            $results = DB::select("SELECT tabulator_id FROM payroll_concept_tabulators_view WHERE id = $conceptId");
            foreach ($results as $row) {
                $salaryTabulatorIds[] = $row->tabulator_id;
            }
        }

        $salaryTabulatorIds = array_unique($salaryTabulatorIds);
        $payrollSalaryTabulators = PayrollSalaryTabulatorResource::collection(
            PayrollSalaryTabulator::query()
                ->whereIn('id', $salaryTabulatorIds)
                ->with(
                    'payrollSalaryTabulatorScales.payrollHorizontalScale',
                    'payrollSalaryTabulatorScales.payrollVerticalScale',
                )->get()
        );
        return $payrollSalaryTabulators;
    }
}

if (!function_exists('addTabulatorValuetoFormula')) {
    /**
     * Se identifica el valor según el expediente del trabajador y se sustituye por su valor en el tabulador.
     *
     * @param object $salaryTabulator Tabulador salarial
     * @param object $salaryAdjustment Ajuste en tabla salarial
     * @param object|null $scale Escala horizontal
     * @param object|null $scaleV Escala vertical
     * @param array|Modules\Payroll\Models\PayrollConcept $concept Concepto de nomina
     * @param array $match Match
     * @param object $formula Formula
     *
     * @return array
     */
    function addTabulatorValuetoFormula($salaryTabulator, $salaryAdjustment, $scale, $scaleV, $concept, $match, $formula)
    {
        $tabScale = PayrollSalaryTabulatorScale::query()
            ->where('payroll_salary_tabulator_id', $salaryTabulator->id)
            ->where('payroll_horizontal_scale_id', $scale['id'] ?? null)
            ->where('payroll_vertical_scale_id', $scaleV['id'] ?? null)
            ->first();

        // Si hay ajuste salarial añadir valores correspondientes a la escala del tabulador
        if ($salaryAdjustment) {
            if ($salaryAdjustment->increase_of_type == 'absolute_value') {
                $tabScale['value'] = json_encode($tabScale['value'] + $salaryAdjustment->value);
            } elseif ($salaryAdjustment->increase_of_type == 'percentage') {
                $tabScale['value'] = json_encode($tabScale['value'] * $salaryAdjustment->value / 100);
            } else {
                $salary_values = $salaryAdjustment->salary_values ? json_decode($salaryAdjustment->salary_values) : null;
                if ($salary_values) {
                    foreach ($salary_values as $salary) {
                        if ($tabScale['id'] == $salary->id) {
                            $tabScale['value'] = $salary->value;
                            break;
                        }
                    }
                }
            }
        }

        if (isset($tabScale)) {
            if ($salaryTabulator->percentage) {
                $formula = str_replace(
                    $match,
                    $tabScale['value'] / 100,
                    $formula ?? $concept['formula']
                );
            } else {
                $formula = str_replace(
                    $match,
                    $tabScale['value'],
                    $formula ?? $concept['formula']
                );
            }
        }

        return $formula;
    }
}
