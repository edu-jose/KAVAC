<?php

namespace Modules\Payroll\Models;

use DateTime;
use Carbon\Carbon;
use App\Models\Phone;
use App\Models\Gender;
use App\Traits\ModelsTrait;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Nwidart\Modules\Facades\Module;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Payroll\Models\PayrollAriRegister;
use Modules\Payroll\Models\PayrollSavingsFund;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Modules\Payroll\Models\PayrollFortnightlyAdvanceDebtor;

/**
 * @class      PayrollStaff
 * @brief      Datos de la información personal del trabajador
 *
 * Gestiona el modelo de datos del personal
 *
 * @author     William Páez <wpaez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollStaff extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Nombre de la tabla en la base de datos
     *
     * @var string $table
     */
    protected $table = "payroll_staffs";

    /**
     * Lista de relaciones a cargar con el modelo
     *
     * @var array $with
     */
    protected $with = [
        'payrollNationality',
        'payrollFinancial',
        'payrollGender',
        'payrollBloodType',
        'payrollDisability',
        'payrollLicenseDegree',
        'payrollEmployment',
        'payrollStaffUniformSize',
        'payrollSocioeconomic',
        'payrollProfessional',
        'payrollResponsibility'
    ];

    /**
     * Lista de atributos para la gestión de fechas
     *
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = [
        'code',
        'first_name',
        'last_name',
        'id_number',
        'passport',
        'email',
        'birthdate',
        'emergency_contact',
        'emergency_phone',
        'address',
        'has_disability',
        'social_security',
        'has_driver_license',
        'has_died',
        'uniform_size',
        'medical_history',
        'payroll_license_degree_id',
        'payroll_blood_type_id',
        'parish_id',
        'payroll_nationality_id',
        'payroll_gender_id',
        'payroll_disability_id',
        'rif',
        'payroll_age_group_id',
        'locality_id',
        'region_id'
    ];

    /**
     * Obtiene el nombre completo de la persona
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    string    Nombre completo de la persona
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Obtiene la relación con los proyectos de presupuesto del módulo de presupuesto si esta presente
     *
     * @return    \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function budgetProjects()
    {
        return (
            Module::has('Budget') && Module::isEnabled('Budget')
        ) ? $this->hasMany(\Modules\Budget\Models\BudgetProject::class) : [];
    }

    /**
     * Obtiene la relación con las acciones centralizadas de presupuesto del módulo de presupuesto si esta presente
     *
     * @return    \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function budgetCentralizedActions()
    {
        return (
            Module::has('Budget') && Module::isEnabled('Budget')
        ) ? $this->hasMany(\Modules\Budget\Models\BudgetCentralizedAction::class) : [];
    }

    /**
     * Método que obtiene la información personal del trabajador asociada a una parroquia
     *
     * @author    William Páez <wpaez@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parish()
    {
        return $this->belongsTo(Parish::class);
    }

    /**
     * Método que obtiene la información personal del trabajador asociada a un género
     *
     * @author    William Páez <wpaez@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollGender()
    {
        return $this->belongsTo(Gender::class);
    }
    /**
     * Método que obtiene la información personal del trabajador asociada a una nacionalidad
     *
     * @author    William Páez <wpaez@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function payrollSurvivor()
    {
        return $this->hasOne(PayrollSurvivor::class);
    }
    /**
     * Método que obtiene la información personal del trabajador asociada a una nacionalidad
     *
     * @author    William Páez <wpaez@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollNationality()
    {
        return $this->belongsTo(PayrollNationality::class);
    }

    // PayrollResetParameter
    public function payrollResetParameters()
    {
        return $this->hasMany(PayrollResetParameter::class);
    }

    // PayrollSupervisedGroupStaff
    public function payrollSupervisedGroupStaffs()
    {
        return $this->hasMany(PayrollSupervisedGroupStaff::class);
    }

    // PayrollArcResponsible
    public function payrollArcResponsibles()
    {
        return $this->hasMany(PayrollArcResponsible::class);
    }

    // PurchasePlan
    public function purchasePlans()
    {
        return (
            Module::has('Purchase') && Module::isEnabled('Purchase')
        ) ? $this->hasMany(\Modules\Purchase\Models\PurchasePlan::class) : [];
    }

    // WorkAttendance
    public function workAttendances()
    {
        return (
            Module::has('WorkAttendance') && Module::isEnabled('WorkAttendance')
        ) ? $this->hasMany(\Modules\WorkAttendance\Models\WorkAttendance::class) : [];
    }

    // WorkAttendanceCustomSchedule
    public function workAttendanceCustomSchedules()
    {
        return (
            Module::has('WorkAttendance') && Module::isEnabled('WorkAttendance')
        ) ? $this->hasMany(\Modules\WorkAttendance\Models\WorkAttendanceCustomSchedule::class) : [];
    }

    // WorkAttendanceExternalActivityStaff
    public function workAttendanceExternalActivityStaffs()
    {
        return (
            Module::has('WorkAttendance') && Module::isEnabled('WorkAttendance')
        ) ? $this->hasMany(\Modules\WorkAttendance\Models\WorkAttendanceExternalActivityStaff::class) : [];
    }

    // WorkAttendancePermission
    public function workAttendancePermissions()
    {
        return (
            Module::has('WorkAttendance') && Module::isEnabled('WorkAttendance')
        ) ? $this->hasMany(\Modules\WorkAttendance\Models\WorkAttendancePermission::class) : [];
    }

    /**
     * Método que obtiene la información personal del trabajador asociada a una información socioeconómica del mismo
     *
     * @author    William Páez <wpaez@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function payrollSocioeconomic()
    {
        return $this->hasOne(PayrollSocioeconomic::class);
    }

    /**
     * Método que obtiene la información personal del trabajador asociada a una información profesional del mismo
     *
     * @author    William Páez <wpaez@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function payrollProfessional()
    {
        return $this->hasOne(PayrollProfessional::class);
    }

    /**
     * Obtiene todos los número telefónicos asociados al trabajador
     *
     * @author    William Páez <wpaez@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function phones()
    {
        return $this->morphMany(Phone::class, 'phoneable');
    }

    /**
     * Método que obtiene la información personal del trabajador asociada a una información laboral del mismo
     *
     * @author    William Páez <wpaez@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function payrollEmployment()
    {
        return $this->hasOne(PayrollEmployment::class);
    }

    /**
     * Obtiene la relación con el personal que no ha sido agregado a la nómina
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function payrollEmploymentNoAppends()
    {
        return $this->hasOne(PayrollEmploymentNoAppends::class);
    }

    /**
     * Método que obtiene la información de la cuenta contable asociada a un trabajador
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function payrollStaffAccount()
    {
        return $this->hasOne(PayrollStaffAccount::class);
    }

    /**
     * Método que obtiene la información personal del trabajador asociada a un grado de licencia de conducir
     *
     * @author    William Páez <wpaez@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollLicenseDegree()
    {
        return $this->belongsTo(PayrollLicenseDegree::class);
    }

    /**
     * Método que obtiene la información personal del trabajador asociada a un tipo de sangre
     *
     * @author    William Páez <wpaez@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollBloodType()
    {
        return $this->belongsTo(PayrollBloodType::class);
    }

    /**
     * Obtiene información de las opciones asignadas asociadas a un trabajador
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function payrollConceptAssignOptions()
    {
        return $this->morphMany(PayrollConceptAssignOption::class, 'assignable');
    }

    /**
     * Método que obtiene la información de las solicitudes de vacaciones asociadas al trabajador
     *
     * @author   F escala <fescala@cenditel.gob.ve>

     * @return    \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollWageGarnishmentRegisters()
    {
        return $this->hasMany(PayrollWageGarnishments::class);
    }
    /**
     * Método que obtiene la información de las solicitudes de vacaciones asociadas al trabajador
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>

     * @return    \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollVacationRequests()
    {
        return $this->hasMany(PayrollVacationRequest::class);
    }

    /**
     * Método que obtiene la información de las solicitudes de adelanto de prestaciones asociadas al trabajador
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>

     * @return    \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollBenefitsRequests()
    {
        return $this->hasMany(PayrollBenefitsRequest::class);
    }

    /**
     * Método que obtiene la información de los registros de nómina asociados al trabajador
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollStaffPayrolls()
    {
        return $this->hasMany(PayrollStaffPayroll::class);
    }

    /**
     * Método que obtiene la información de las solicitudes de permisos asociadas al trabajador
     *
     * @author    Yennifer Ramirez <yramirez@cenditel.gob.ve>

     * @return    \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollPermissionRequests()
    {
        return $this->hasMany(PayrollPermissionRequest::class);
    }

    /**
     * Método que obtiene la información de registros de cronogramas de trabajadores.
     *
     * @author    Yennifer Ramirez <yramirez@cenditel.gob.ve>

     * @return    \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function citizenServiceRegister()
    {
        return (
            Module::has('CitizenService') && Module::isEnabled('CitizenService')
        ) ? $this->hasMany(\Modules\CitizenService\Models\CitizenServiceRegister::class) : [];
    }

    /**
     * Método que obtiene a los trabajadores asociados a muchos departamentos.
     *
     * @author    Oscar González <ojgonzalez@cenditel.gob.ve>

     * @return    \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function citizenServiceDepartments()
    {
        return (
            Module::has('CitizenService') && Module::isEnabled('CitizenService')
        ) ? $this->belongsToMany(\Modules\CitizenService\Models\CitizenServiceDepartment::class) : null;
    }

    /**
     * Obtiene la relación con los departamentos del módulo de OAC si esta presente
     *
     * @return array|\Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function scopeGetCitizenServiceDepartmentDirector()
    {
        return (
            Module::has('CitizenService') && Module::isEnabled('CitizenService')
        ) ? $this->hasMany(\Modules\CitizenService\Models\CitizenServiceDepartment::class, "director_id") : [];
    }

    /**
     * Obtiene la relación con los coordinadores de departamentos del módulo de OAC si esta presente
     *
     * @return array|\Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function scopeGetCitizenServiceDepartmentCoordinator()
    {
        return (
            Module::has('CitizenService') && Module::isEnabled('CitizenService')
        ) ? $this->hasMany(\Modules\CitizenService\Models\CitizenServiceDepartment::class, "coordinator_id") : [];
    }

    /**
     * Método que obtiene el dato personal del trabajador asociada a una discapacidad
     *
     * @author    William Páez <wpaez@cenditel.gob.ve> | <paez.william8@gmail.com>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollDisability()
    {
        return $this->belongsTo(PayrollDisability::class);
    }

    /**
     * Método que obtiene los bienes asignados a un trabajador
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assetAsignation()
    {
        return (
            Module::has('Asset') && Module::isEnabled('Asset')
        ) ? $this->hasMany(\Modules\Asset\Models\AssetAsignation::class) : [];
    }

    /**
     * Método que obtiene las tallas de uniforme asociados al trabajador
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>

     * @return    \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollStaffUniformSize()
    {
        return $this->hasMany(PayrollStaffUniformSize::class);
    }

    /**
     * Método que obtiene los datos financieros asignados a un trabajador
     *
     * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollFinancial()
    {
        return $this->hasMany(PayrollFinancial::class);
    }

    /**
     * Método que obtiene los bienes a comprar del trabajador si el módulo de compras está presente
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function saleGoodsToBeTraded()
    {
        return (
            Module::has('Sale') && Module::isEnabled('Sale')
        ) ? $this->belongsToMany(\Modules\Sale\Models\SaleGoodsToBeTraded::class, 'sale_good_to_be_traded_payroll_staff') : [];
    }

    /**
     * Registro ARI pertenciente al trabajador
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollAriRegisters()
    {
        return $this->hasMany(PayrollAriRegister::class);
    }
    /**
     * Registro ARI pertenciente al trabajador
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollSavingsFundRegisters()
    {
        return $this->hasMany(PayrollSavingsFund::class);
    }

    /**
     * Obtiene la relación con la responsabilidad del trabajador
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function payrollResponsibility()
    {
        return $this->hasOne(PayrollResponsibility::class);
    }

    /**
     * Obtiene la relación con el grupo etario del trabajador
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function payrollAgeGroup()
    {
        return $this->belongsTo(PayrollAgeGroup::class, 'payroll_age_group_id');
    }

    /**
     * Obtiene la relación con el grupo etario del trabajador
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function locality()
    {
        return $this->belongsTo(Locality::class);
    }

    /**
     * Obtiene la relación con el grupo etario del trabajador
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function payrollFortnightlyAdvanceDebtors()
    {
        return $this->hasMany(PayrollFortnightlyAdvanceDebtor::class);
    }

    /**
     * Scope que permite filtrar a los trabajadores bajo cierto parámetros
     *
     * @author    José Briceño <jbricenyo@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterPayrollStaff($query, array|null $request_ = null)
    {
        $request = $request_ ?? request()->toArray();

        $request = array_filter($request);

        $query->without([
            'payrollGender',
            'payrollDisability',
            'payrollLicenseDegree',
            'payrollBloodType',
            'payrollProfessional',
            'payrollSocioeconomic',
            'payrollEmployment',
            'payrollNationality',
            'payrollFinancial',
            'payrollStaffUniformSize',
            'payrollResponsibility'
        ]);

        /*-----------------------
         | Filtrar por personal |
         ----------------------*/
        if (isset($request["payroll_staffs"])) {
            $select = ['id', 'first_name', 'last_name', 'id_number'];
            $payroll_staff = Arr::flatten($request["payroll_staffs"]);

            $query->select($select);

            if (!in_array('todos', $payroll_staff)) {
                $payroll_staff_id = array_column($request["payroll_staffs"], 'id');
                $query->whereIn('id', $payroll_staff_id);
            }

            $query->with(['payrollEmploymentNoAppends' => function ($query) {
                $query->selectRaw("id, payroll_staff_id, start_date, DATE_PART('year', AGE(start_date)) AS time_worked");
                $query->with(['payrollPositions' => function ($query) {
                    $query->select('payroll_position_id', 'name');
                }]);
            }]);
        }

        /*-------------------------------
         | Filtrar por datos personales |
         ------------------------------*/

        if (isset($request["personal_data"]) && filter_var($request["personal_data"], FILTER_VALIDATE_BOOLEAN)) {
            /*---------
             | Género |
             ---------*/
            if (isset($request["payroll_genders"])) {
                $select[] = 'payroll_gender_id';

                $query->select($select)->with(['payrollGender' => function ($query) {
                    $query->select('id', 'name');
                }]);

                $payroll_gender = Arr::flatten($request["payroll_genders"]);

                (!in_array('todos', $payroll_gender))
                    && ($payroll_gender_id = array_column($request["payroll_genders"], 'id'))
                    && ($query->whereIn('payroll_gender_id', $payroll_gender_id));
            }


            /*---------------
             | Discapacidad |
             --------------*/
            if (isset($request["payroll_disabilities"])) {
                $select[] = 'payroll_disability_id';

                $query->select($select)->with(['payrollDisability' => function ($query) {
                    $query->select('id', 'name');
                }]);

                $payroll_disability = Arr::flatten($request["payroll_disabilities"]);

                (in_array('todos', $payroll_disability)
                    ? $query->where('has_disability', true)
                    : (($payroll_disability_id = array_column($request["payroll_disabilities"], 'id'))
                        && ($query->whereIn('payroll_disability_id', $payroll_disability_id))));
            }

            /*-----------------------
             | Licencia de conducir |
             ----------------------*/
            if (isset($request["payroll_license_degrees"])) {
                $select[] = 'payroll_license_degree_id';

                $query->select($select)->with(['payrollLicenseDegree' => function ($query) {
                    $query->select('id', 'name');
                }]);

                $payroll_license_degree = Arr::flatten($request["payroll_license_degrees"]);

                (in_array('todos', $payroll_license_degree)
                    ? $query->where('has_driver_license', true)
                    : (($payroll_license_degree_id = array_column($request["payroll_license_degrees"], 'id'))
                        && ($query->whereIn('payroll_license_degree_id', $payroll_license_degree_id))));
            }

            /*-----------------
             | Tipo de sangre |
             ----------------*/
            if (isset($request["payroll_blood_types"])) {
                $select[] = 'payroll_blood_type_id';

                $query->select($select)->with(['payrollBloodType' => function ($query) {
                    $query->select('id', 'name');
                }]);

                $payroll_blood_type = Arr::flatten($request["payroll_blood_types"]);

                !in_array('todos', $payroll_blood_type)
                    && ($payroll_blood_type_id = array_column($request["payroll_blood_types"], 'id'))
                    && ($query->whereIn('payroll_blood_type_id', $payroll_blood_type_id));
            }

            /*----------------
             | Rango de edad |
             ---------------*/

            /*--------------
             | Edad mínima |
             -------------*/
            if (isset($request["min_age"]) && !is_null($request["min_age"])) {
                $query->whereRaw("DATE_PART('year', AGE(birthdate)) >= ?", [$request["min_age"]]);
            }

            /*--------------
             | Edad máxima |
             -------------*/
            if (isset($request["max_age"]) && !is_null($request["max_age"])) {
                $query->whereRaw("DATE_PART('year', AGE(birthdate)) <= ?", [$request["max_age"]]);
            }

            if ((isset($request["min_age"]) && !is_null($request["min_age"])) || (isset($request["max_age"]) && !is_null($request["max_age"]))) {
                $query->selectRaw("id, first_name, last_name, DATE_PART('year', AGE(birthdate)) AS age");
            }
        }

        /*----------------------------------
         | Filtrar por datos profesionales |
         ---------------------------------*/
        if (isset($request["professional_data"]) && filter_var($request["professional_data"], FILTER_VALIDATE_BOOLEAN)) {
            $selectFromProfessionalData = ['id', 'payroll_staff_id', 'payroll_instruction_degree_id', 'is_student'];

            $query->whereHas(
                'payrollProfessional',
                function ($query) use ($request) {

                    /*-----------------------
                     | Grado de instrucción |
                     ----------------------*/
                    if (isset($request["payroll_instruction_degrees"])) {
                        $payroll_instruction_degrees = Arr::flatten($request["payroll_instruction_degrees"]);
                        if (!in_array('todos', $payroll_instruction_degrees)) {
                            $payroll_instruction_degree_ids = array_column($request["payroll_instruction_degrees"], 'id');
                            $query->whereIn('payroll_instruction_degree_id', $payroll_instruction_degree_ids);
                        } else {
                            $query->wherehas('payrollInstructionDegree');
                        }
                    }

                    /*--------------
                     | Profesiones |
                     -------------*/
                    if (isset($request["payroll_professions"])) {
                        $payroll_professions = Arr::flatten($request["payroll_professions"]);
                        if (!in_array('todos', $payroll_professions)) {
                            $payroll_professions_ids = array_column($request["payroll_professions"], 'id');
                            $query->whereHas('payrollStudies.professions', function ($query) use ($payroll_professions_ids) {
                                $query->whereIn('id', $payroll_professions_ids);
                            });
                        } else {
                            $query->whereHas('payrollStudies.professions');
                        }
                    }

                    /*----------------
                     | Es estudiante |
                     ---------------*/
                    if (isset($request["is_study"]) && filter_var($request["is_study"], FILTER_VALIDATE_BOOLEAN)) {
                        $query->where('is_student', $request["is_study"]);
                    }
                }
            )->with(['payrollProfessional' => function ($query) use ($selectFromProfessionalData, $request) {
                $query->select($selectFromProfessionalData);

                if (isset($request["payroll_instruction_degrees"])) {
                    $query->with(['payrollInstructionDegree' => function ($query) {
                        $query->select('id', 'name');
                    }]);
                } else {
                    $query->without('payrollInstructionDegree');
                }

                if (isset($request["payroll_professions"])) {
                    $query->with(['payrollStudies' => function ($query) {
                        $query->select('id', 'university_name', 'profession_id', 'payroll_professional_id');
                        $query->with(['professions' => function ($query) {
                            $query->select('id', 'name');
                        }]);
                    }]);
                } else {
                    $query->without('payrollStudies.professions');
                }
            }]);
        }

        /*------------------------------------
         | Filtrar por datos socioeconomicos |
         -----------------------------------*/
        if (isset($request["socioeconomic_data"]) && filter_var($request["socioeconomic_data"], FILTER_VALIDATE_BOOLEAN)) {
            $selectFromSocioeconomicData = ['id', 'payroll_staff_id', 'marital_status_id'];
            $children_id = null;
            if (isset($request["has_childs"]) && filter_var($request["has_childs"], FILTER_VALIDATE_BOOLEAN)) {
                // Obtener el id de la relacion del tipo Hijo(a)
                $children_id = PayrollRelationship::childrenId();
            }

            $query->whereHas('payrollSocioeconomic', function ($query) use ($request, $children_id) {
                /*---------------
                 | Estado civil |
                 --------------*/
                if (isset($request["marital_status"])) {
                    if (!in_array('todos', Arr::flatten($request["marital_status"]))) {
                        $marital_status_id = array_column($request["marital_status"], 'id');
                        $query->whereIn('marital_status_id', $marital_status_id);
                    } else {
                        $query->whereHas('maritalStatus');
                    }
                }

                /* Hijos */
                if (isset($request["has_childs"]) && filter_var($request["has_childs"], FILTER_VALIDATE_BOOLEAN)) {
                    // Obtener el id de la relacion del tipo Hijo(a)
                    $query->whereHas('payrollChildrens', function ($query) use ($request, $children_id) {
                        /* Filtrar por la relación hijos */
                        $query->where('payroll_relationships_id', $children_id);

                        /* Mínimo de edad de los hijos */
                        if (isset($request["min_childs_age"]) && !is_null($request["min_childs_age"])) {
                            $query->whereRaw("DATE_PART('year', AGE(birthdate)) >= ?", [$request["min_childs_age"]]);
                        }

                        /* Máximo de edad de los hijos */
                        if (isset($request["max_childs_age"]) && !is_null($request["max_childs_age"])) {
                            $query->whereRaw("DATE_PART('year', AGE(birthdate)) <= ?", [$request["max_childs_age"]]);
                        }

                        /* Filtrar hijos por nivel de estudios */
                        if (isset($request["payroll_schooling_levels"])) {
                            if (!in_array('todos', Arr::flatten($request["payroll_schooling_levels"]))) {
                                $payroll_schooling_level_ids = array_column($request["payroll_schooling_levels"], 'id');
                                $query->whereHas('payrollSchoolingLevel', function ($query) use ($payroll_schooling_level_ids) {
                                    $query->whereIn('id', $payroll_schooling_level_ids);
                                });
                            } else {
                                $query->whereHas('payrollSchoolingLevel');
                            }
                        }
                    });
                }
            })->with(['payrollSocioeconomic' => function ($query) use ($selectFromSocioeconomicData, $request, $children_id) {
                $query->select($selectFromSocioeconomicData);

                if (isset($request["marital_status"])) {
                    $query->with(['maritalStatus' => function ($query) {
                        $query->select('id', 'name');
                    }]);
                } else {
                    $query->without('maritalStatus');
                }

                if (isset($request["has_childs"])) {
                    $query->with(['payrollChildrens' => function ($query) use ($request, $children_id) {
                        $query->without(['payrollDisability', 'payrollRelationship', 'payrollScholarshipType']);
                        $query->select([
                            'id',
                            'first_name',
                            'last_name',
                            'payroll_socioeconomic_id',
                            'payroll_schooling_level_id',
                            'payroll_relationships_id',
                        ]);

                        /* Filtrar por la relación hijos */
                        $query->where('payroll_relationships_id', $children_id);

                        /* Mínimo de edad de los hijos */
                        if (isset($request["min_childs_age"]) && !is_null($request["min_childs_age"])) {
                            $query->whereRaw("DATE_PART('year', AGE(birthdate)) >= ?", [$request["min_childs_age"]]);
                        }

                        /* Máximo de edad de los hijos */
                        if (isset($request["max_childs_age"]) && !is_null($request["max_childs_age"])) {
                            $query->whereRaw("DATE_PART('year', AGE(birthdate)) <= ?", [$request["max_childs_age"]]);
                        }

                        /* calcular la edad de los hijos */
                        $query->selectRaw("DATE_PART('year', AGE(birthdate)) AS age");

                        /* Filtrar hijos por nivel de estudios */
                        if (isset($request["payroll_schooling_levels"])) {
                            $query->with(['payrollSchoolingLevel' => function ($query) {
                                $query->select('id', 'name');
                            }]);
                        }

                        if (isset($request["payroll_schooling_levels"])) {
                            if (!in_array('todos', Arr::flatten($request["payroll_schooling_levels"]))) {
                                $payroll_schooling_level_ids = array_column($request["payroll_schooling_levels"], 'id');
                                $query->whereHas('payrollSchoolingLevel', function ($query) use ($payroll_schooling_level_ids) {
                                    $query->whereIn('id', $payroll_schooling_level_ids);
                                })->with(['payrollSchoolingLevel' => function ($query) use ($payroll_schooling_level_ids) {
                                    $query->whereIn('id', $payroll_schooling_level_ids)
                                        ->select('id', 'name');
                                }]);
                            } else {
                                $query->with(['payrollSchoolingLevel' => function ($query) {
                                    $query->select('id', 'name');
                                }]);
                            }
                        }
                    }]);
                } else {
                    $query->without('payrollChildrens');
                }
            }]);
        }

        /* Filtrar por datos laborales */
        if (isset($request["employment_data"]) && filter_var($request["employment_data"], FILTER_VALIDATE_BOOLEAN)) {
            $query->wherehas('payrollEmploymentNoAppends', function ($query) use ($request) {

                /* Trabajadores activos o inactivos */
                $isActive = isset($request["is_active"]) && filter_var($request["is_active"], FILTER_VALIDATE_BOOLEAN);

                $query->where('active', $isActive);

                if (!$isActive) {
                    /* Tipo de Inactividad */
                    if (isset($request["payroll_inactivity_types"])) {
                        if (!in_array('todos', Arr::flatten($request["payroll_inactivity_types"]))) {
                            $payroll_inactivity_type_ids = array_column($request["payroll_inactivity_types"], 'id');
                            $query->whereIn('payroll_inactivity_type_id', $payroll_inactivity_type_ids);
                        } else {
                            $query->whereHas('payrollInactivityType');
                        }
                    }
                }

                /* Tipo de cargo */
                if (isset($request["payroll_position_types"])) {
                    if (!in_array('todos', Arr::flatten($request["payroll_position_types"]))) {
                        $payroll_position_type_ids = array_column($request["payroll_position_types"], 'id');
                        $query->whereIn('payroll_position_type_id', $payroll_position_type_ids);
                    } else {
                        $query->whereHas('payrollPositionType');
                    }
                }

                /* Cargo */
                if (isset($request["payroll_positions"])) {
                    $query->whereHas('payrollPositions', function ($query) use ($request) {
                        if (!in_array('todos', Arr::flatten($request["payroll_positions"]))) {
                            $payroll_position_ids = array_column($request["payroll_positions"], 'id');
                            $query->whereIn('payroll_position_id', $payroll_position_ids);
                        }
                    });
                } else {
                    $query->whereHas('payrollPositions');
                }

                /* Tipos de personal */
                if (isset($request["payroll_staff_types"])) {
                    if (!in_array('todos', Arr::flatten($request["payroll_staff_types"]))) {
                        $payroll_staff_type_ids = array_column($request["payroll_staff_types"], 'id');
                        $query->whereIn('payroll_staff_type_id', $payroll_staff_type_ids);
                    }
                }

                /* Tipo de contrato */
                if (isset($request["payroll_contract_types"])) {
                    if (!in_array('todos', Arr::flatten($request["payroll_contract_types"]))) {
                        $payroll_contract_type_ids = array_column($request["payroll_contract_types"], 'id');
                        $query->whereIn('payroll_contract_type_id', $payroll_contract_type_ids);
                    }
                }

                /* Departamentos */
                if (isset($request["departments"])) {
                    if (!in_array('todos', Arr::flatten($request["departments"]))) {
                        $department_ids = array_column($request["departments"], 'id');
                        $query->whereIn('department_id', $department_ids);
                    }
                }

                /* Filtrar por tiempo laborado en la institución */

                /* Tiempo mínimo laborado */
                if (isset($request["min_time_worked"]) && !is_null($request["min_time_worked"])) {
                    $query->whereRaw("(DATE_PART('year', AGE(start_date))::int) >= ?", [$request["min_time_worked"]]);
                }

                /* Tiempo máximo laborado */
                if (isset($request["max_time_worked"]) && !is_null($request["max_time_worked"])) {
                    $query->whereRaw("(DATE_PART('year', AGE(start_date))::int) <= ?", [$request["max_time_worked"]]);
                }

                /* Filtrar por tiempo de servicio */


                /* Tiempo mínimo de servicio */
                if (isset($request["min_time_service"]) && !is_null($request["min_time_service"])) {
                    $query->whereRaw("(DATE_PART('year', AGE(start_date))::int) + COALESCE((substring(years_apn from 'Años: ([0-9]+)')::int), 0) >= ?", [$request["min_time_service"]]);
                }

                /* Tiempo máximo de servicio */
                if (isset($request["max_time_service"]) && !is_null($request["max_time_service"])) {
                    $query->whereRaw("(DATE_PART('year', AGE(start_date))::int) + COALESCE((substring(years_apn from 'Años: ([0-9]+)')::int), 0) <= ?", [$request["max_time_service"]]);
                }
            })->with(['payrollEmploymentNoAppends' => function ($query) use ($request) {
                $selectFromPayrollEmploymentNoAppends = "id, active, payroll_inactivity_type_id, start_date, payroll_position_type_id, payroll_coordination_id, department_id, payroll_staff_type_id, payroll_contract_type_id, payroll_staff_id, DATE_PART('year', AGE(start_date)) AS time_worked";
                $query->selectRaw($selectFromPayrollEmploymentNoAppends);

                if (isset($request["min_time_service"]) && !is_null($request["min_time_service"])) {
                    $query->whereRaw("(DATE_PART('year', AGE(start_date))::int) + COALESCE((substring(years_apn from 'Años: ([0-9]+)')::int), 0) >= ?", [$request["min_time_service"]]);
                }

                if (isset($request["max_time_service"]) && !is_null($request["max_time_service"])) {
                    $query->whereRaw("(DATE_PART('year', AGE(start_date))::int) + COALESCE((substring(years_apn from 'Años: ([0-9]+)')::int), 0) <= ?", [$request["max_time_service"]]);
                }

                if ((isset($request["min_time_service"]) && !is_null($request["min_time_service"])) || (isset($request["max_time_service"]) && !is_null($request["max_time_service"]))) {
                    $query->selectRaw("(DATE_PART('year', AGE(start_date))::int) + COALESCE((substring(years_apn from 'Años: ([0-9]+)')::int), 0) AS total");
                }

                if (isset($request["payroll_position_types"])) {
                    $query->with(['payrollPositionType' => function ($query) {
                        $query->select('id', 'name');
                    }]);
                }

                if (isset($request["payroll_inactivity_types"])) {
                    $query->with(['payrollInactivityType' => function ($query) {
                        $query->select('id', 'name');
                    }]);
                }

                $query->with(['payrollPositions' => function ($query) {
                    $query->select('payroll_position_id', 'name');
                }]);

                if (isset($request["payroll_staff_types"])) {
                    $query->with(['payrollStaffType' => function ($query) {
                        $query->select('id', 'name');
                    }]);
                }

                if (isset($request["payroll_contract_types"])) {
                    $query->with(['payrollContractType' => function ($query) {
                        $query->select('id', 'name');
                    }]);
                }

                if (isset($request["departments"])) {
                    $query->with(['department' => function ($query) {
                        $query->without('institution')->select('id', 'name');
                    }]);
                }
            }]);
        }
        return $query->orderBy('first_name', 'asc');
    }

    /**
     * Metodo que aplica los filtros de búsqueda
     *
     * @param \Illuminate\Database\Eloquent\Builder $query Objeto con la consulta
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query)
    {
        return $query->when(request()->has('query'), function ($query) {
            $search = request('query');
            $query->where(function ($query) use ($search) {
                $query->where('id_number', 'ilike', "%$search%")
                    ->orWhere('first_name', 'ilike', "%$search%")
                    ->orWhere('last_name', 'ilike', "%$search%")
                    ->orWhere('email', 'ilike', "%$search%")
                    ->orWhereHas('payrollGender', function ($query) use ($search) {
                        $query->where('name', 'ilike', "%$search%");
                    });
            });
        });
    }

    /**
     * Establece la relación con el modelo del historial de responsables.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function componentManagerHistory()
    {
        return (
            Module::has('Budget') && Module::isEnabled('Budget')
        ) ? $this->morphMany(\Modules\Budget\Models\BudgetComponentManagerHistory::class, 'managerable') : [];
    }

    // --- SCOPES PARA LAS REGLAS ---

    /**
     * Scope: Verifica si el trabajador está activo (tiene empleo activo).
     * Es útil llamarlo desde otros scopes o como base.
     */
    public function scopeOnlyActive($query)
    {
        return $query->whereHas('payrollEmployment', fn($q) => $q->where('active', true));
    }

    /**
     * Scope: Trabajadores con hijos que tienen becas específicas.
     * Rule ID: staff_with_sons_has_scholarships
     */
    public function scopeHasSonsWithScholarships($query, array $scholarshipTypeIds)
    {
        $relationshipSonId = $this->getSonRelationshipId();
        return $query->whereHas('payrollSocioeconomic.payrollChildrens', function ($burdenQuery) use ($relationshipSonId, $scholarshipTypeIds) {
            $burdenQuery->where('payroll_relationships_id', $relationshipSonId)
                ->where('has_scholarships', true)
                ->whereIn('payroll_scholarship_types_id', $scholarshipTypeIds);
        })->onlyActive(); // Asegura que el trabajador esté activo
    }

    /**
     * Scope: Trabajadores con hijos en un rango de edad específico.
     * Rule ID: all_staff_with_sons
     */
    public function scopeHasSonsInAgeRange($query, int $minAge, int $maxAge, string $referenceDate)
    {
        $relationshipSonId = $this->getSonRelationshipId();

        try {
            // Calcula las fechas de nacimiento límite basadas en la fecha de referencia
            $maxBirthDate = Carbon::parse($referenceDate)->subYears($minAge)->endOfDay()->toDateString();
            $minBirthDate = Carbon::parse($referenceDate)->subYears($maxAge + 1)->addDay()->startOfDay()->toDateString(); // +1 para incluir el año completo
        } catch (\Exception $e) {
            // Manejar error de fecha inválida si es necesario, por ahora lanzamos excepción o devolvemos query vacía
            \Log::error("Error calculando rango de fechas para hijos: " . $e->getMessage());
            return $query->whereRaw('1 = 0'); // Devuelve query sin resultados
        }


        return $query->whereHas('payrollSocioeconomic.payrollChildrens', function ($burdenQuery) use ($relationshipSonId, $minBirthDate, $maxBirthDate) {
            $burdenQuery->where('payroll_relationships_id', $relationshipSonId)
                ->whereNotNull('birthdate') // Asegurar que la fecha no sea nula
                ->whereBetween('birthdate', [$minBirthDate, $maxBirthDate]);
        })->onlyActive();
    }

    /**
     * Scope: Trabajadores con hijos que están estudiando.
     * Rule ID: all_staff_with_sons_studying
     */
    public function scopeHasSonsStudying($query)
    {
        $relationshipSonId = $this->getSonRelationshipId();
        return $query->whereHas('payrollSocioeconomic.payrollChildrens', function ($burdenQuery) use ($relationshipSonId) {
            $burdenQuery->where('payroll_relationships_id', $relationshipSonId)
                ->where('is_student', true);
        })->onlyActive();
    }

    /**
     * Scope: Filtra trabajadores que son sobrevivientes.
     * Rule ID: all_survivor_staff
     * Asume un campo booleano 'is_survivor' en la tabla payroll_staffs.
     * Ajusta el nombre del campo si es diferente.
     * Nota: No se aplica ->onlyActive() aquí, asumiendo que 'sobreviviente'
     * podría no requerir un estado laboral activo. Ajustar si es necesario.
     */
    public function scopeIsSurvivor($query)
    {
        $retiredId = PayrollInactivityType::where('name', 'ILIKE', 'jubilado')->value('id');

        return $query->where('has_died', true)
            ->whereHas('payrollSurvivor', function ($query) {
                $query->whereNotNull('first_name')
                    ->whereNotNull('last_name')
                    ->whereNotNull('payroll_staff_id')
                    ->whereNotNull('id_number')
                    ->whereNotNull('finance_bank_id')
                    ->whereNotNull('finance_account_type_id')
                    ->whereNotNull('payroll_account_number');
            })
            ->whereHas('payrollEmployment', function ($query) use ($retiredId) {
                $query->where('payroll_inactivity_type_id', $retiredId);
            });
    }

    /**
     * Scope: Filtra trabajadores activos que pertenecen a un sindicato.
     * Rule ID: all_staff_who_belong_to_a_workers_union
     * Asume un campo booleano 'workers_union' en la tabla payroll_employments.
     */
    public function scopeBelongsToUnion($query)
    {
        // Verifica el campo en la relación y que el empleado esté activo
        return $query->whereHas('payrollEmployment', function ($empQuery) {
            $empQuery->where('workers_union', true);
        })
            ->onlyActive();
    }

    /**
     * Scope: Filtra trabajadores activos afiliados al fondo de ahorro.
     * Rule ID: all_staff_affiliated_with_the_savings_fund
     * Asume un campo booleano 'savings_fund' en la tabla payroll_employments.
     */
    public function scopeIsAffiliatedToSavingsFund($query)
    {
        // Verifica el campo en la relación y que el empleo esté activo
        return $query->whereHas('payrollEmployment', function ($empQuery) {
            $empQuery->where('savings_fund', true);
        })
            ->onlyActive();
    }

    /**
     * Scope: Trabajadores con cargos específicos.
     * Rule ID: staff_according_position
     */
    public function scopePositionInList($query, array $positionIds)
    {
        // Asume relación payrollEmployment->payrollPositions (muchos a muchos)
        return $query->whereHas('payrollEmployment.payrollPositions', function ($posQuery) use ($positionIds) {
            $posQuery->whereIn('payroll_positions.id', $positionIds);
        })->onlyActive();
        // Si la relación es directa (un solo cargo en payrollEmployment):
        // return $query->whereHas('payrollEmployment', fn($q) => $q->whereIn('payroll_position_id', $positionIds)->where('active', true));
    }

    /**
     * Scope: Trabajadores que NO están de vacaciones aprobadas durante el período.
     * Rule ID: all_staff_not_in_vacation
     */
    public function scopeIsNotInApprovedVacationDuring($query, string $periodStart, string $periodEnd)
    {
        // Trabajador activo que NO tiene una solicitud APROBADA que se SOLAPE con el periodo
        return $query->whereDoesntHave('payrollVacationRequests', function ($vacQuery) use ($periodStart, $periodEnd) {
            $vacQuery->where('status', 'approved')
                // Solapamiento: La vacación empieza antes o cuando termina el periodo Y termina después o cuando empieza el periodo
                ->where('start_date', '<=', $periodEnd)
                ->where('end_date', '>=', $periodStart);
        })->onlyActive();
    }

    public function scopeFindStaffWithPaidConcepts($query, array $conceptIds)
    {
        return $query->whereHas('payrollFortnightlyAdvanceDebtors', function ($query) use ($conceptIds) {
            $query->whereIn('payroll_concept_id', $conceptIds)->where('in_debt', true);
        });
    }

    /**
     * Scope: Trabajadores que retornaron de vacaciones aprobadas durante el período.
     * Rule ID: all_staff_vacation_return
     */
    public function scopeReturnedFromVacationDuring($query, string $periodStart, string $periodEnd)
    {
        // Trabajador activo que SÍ tiene una solicitud APROBADA cuya FECHA FIN cae DENTRO del periodo
        return $query->whereHas('payrollVacationRequests', function ($vacQuery) use ($periodStart, $periodEnd) {
            $vacQuery->where('status', 'approved')
                ->where('end_date', '>=', $periodStart)
                ->where('end_date', '<=', $periodEnd);
        })->onlyActive();
    }

    public function scopeAllStaffInVacations($query, string $periodStart)
    {
        // Trabajador activo que SÍ tiene una solicitud APROBADA cuya FECHA FIN cae DENTRO del periodo
        return $query->whereHas('payrollVacationRequests', function ($vacQuery) use ($periodStart) {
            $vacQuery->where('status', 'approved')
                ->where('start_date', '<', $periodStart)
                ->where('end_date', '>=', $periodStart);
        })->onlyActive();
    }

    /**
     * Scope: Trabajadores con o sin discapacidad.
     * Rule IDs: all_disabled_staff, all_except_disabled_staff
     */
    public function scopeHasDisability($query, bool $hasDisability = true)
    {
        // Asume un campo 'has_disability' booleano en la tabla payroll_staffs
        return $query->where('has_disability', $hasDisability)
            ->onlyActive();
    }

    /**
     * Scope: Trabajadores que están estudiando (ellos mismos).
     * Rule ID: all_studying_staff
     */
    public function scopeIsStudying($query)
    {
        return $query->whereHas('payrollProfessional', function ($profQuery) {
            $profQuery->where('is_student', true);
        })->onlyActive();
    }

    /**
     * Scope: Trabajadores que dominan más de un idioma.
     * Rule ID: staff_master_the_languages
     */
    public function scopeMastersMoreThanOneLanguage($query)
    {
        return $query->whereHas('payrollProfessional.payrollLanguages', null, '>', 1) // Verifica que la cuenta de idiomas relacionados sea > 1
            ->onlyActive();
    }

    /**
     * Scope: Trabajadores según tipo de contrato.
     * Rule ID: staff_according_contract_type
     */
    public function scopeContractTypeInList($query, array $contractTypeIds)
    {
        return $query->whereHas('payrollEmployment', function ($empQuery) use ($contractTypeIds) {
            $empQuery->whereIn('payroll_contract_type_id', $contractTypeIds);
        })->onlyActive();
    }

    /**
     * Scope: Trabajadores según departamento.
     * Rule ID: staff_according_department
     */
    public function scopeDepartmentInList($query, array $departmentIds)
    {
        return $query->whereHas('payrollEmployment', function ($empQuery) use ($departmentIds) {
            $empQuery->whereIn('department_id', $departmentIds);
        })->onlyActive();
    }

    /**
     * Scope: Trabajadores según tipo de cargo.
     * Rule ID: staff_according_position_type
     */
    public function scopePositionTypeInList($query, array $positionTypeIds)
    {
        return $query->whereHas('payrollEmployment', function ($empQuery) use ($positionTypeIds) {
            $empQuery->whereIn('payroll_position_type_id', $positionTypeIds);
        })->onlyActive();
    }

    /**
     * Scope: Trabajadores según tipo de personal.
     * Rule ID: staff_according_staff_type
     */
    public function scopeStaffTypeInList($query, array $staffTypeIds)
    {
        return $query->whereHas('payrollEmployment', function ($empQuery) use ($staffTypeIds) {
            $empQuery->whereIn('payroll_staff_type_id', $staffTypeIds);
        })->onlyActive();
    }

    /**
     * Scope: Trabajadores según rango de fecha de ingreso.
     * Rule ID: all_staff_according_start_date
     */
    public function scopeStartDateBetween($query, ?string $maxDate, $periodStart, $periodEnd)
    {
        //convertir la fecha de inio y fin de periodo a un objero de datatime para poder usar la funcion modify
        $now = ($periodEnd) ? new DateTime($periodEnd) : new DateTime();
        return $query->whereHas('payrollEmployment', function ($empQuery) use ($maxDate, $now, $periodStart, $periodEnd) {
            if (isset($maxDate)) {
                $date = $now->modify('-' . ($maxDate) . 'year');
                $empQuery->whereDate('start_date', '<=', $date);
            }
            if (($periodStart && $periodEnd)) {
                $start = explode('-', $periodStart);
                $end = explode('-', $periodEnd);

                if ($start[1] > $end[1]) {
                    $empQuery->whereBetween(
                        DB::raw("to_char(" . 'start_date' . ", 'MM-DD')"),
                        [$start[1] . '-' . $start[2], '12-31']
                    )
                        ->OrWhereBetween(
                            DB::raw("to_char(" . 'start_date' . ", 'MM-DD')"),
                            ['01-01', $end[1] . '-' . $end[2]]
                        );
                } elseif ($start[1] . '-' . $start[2] == $end[1] . '-' . $end[2]) {
                    if ($start[0] != $end[0]) {
                        $empQuery->WhereBetween(
                            DB::raw("to_char(" . 'start_date' . ", 'MM-DD')"),
                            ['01-01', '12-31']
                        );
                    } else {
                        $empQuery->Where(
                            DB::raw("to_char(" . 'start_date' . ", 'MM-DD')"),
                            $start[1] . '-' . $start[2]
                        );
                    }
                } else {
                    $empQuery->whereBetween(
                        DB::raw("to_char(" . 'start_date' . ", 'MM-DD')"),
                        [$start[1] . '-' . $start[2], $end[1] . '-' . $end[2]]
                    );
                }
            }
        })->onlyActive();
    }

    /**
     * Scope: Trabajadores según nivel de instrucción.
     * Rule ID: staff_according_instruction_degree
     */
    public function scopeInstructionDegreeInList($query, array $degreeIds)
    {
        return $query->whereHas('payrollProfessional', function ($profQuery) use ($degreeIds) {
            $profQuery->whereIn('payroll_instruction_degree_id', $degreeIds);
        })->onlyActive();
    }

    /**
     * Scope: Trabajadores según género.
     * Rule ID: staff_according_gender
     */
    public function scopeGenderInList($query, array $genderIds)
    {
        return $query->whereIn('payroll_gender_id', $genderIds)
            ->onlyActive();
    }


    // --- Métodos Auxiliares ---

    /**
     * Obtiene el ID de la relación 'Hijo(a)'.
     */
    protected function getSonRelationshipId(): ?int
    {
        static $sonId = null;
        if ($sonId === null) {
            // Asegúrate que el modelo PayrollRelationship exista en la ruta correcta
            $sonId = \Modules\Payroll\Models\PayrollRelationship::where('name', 'Hijo(a)')->value('id');
            if ($sonId === null) {
                \Log::warning("No se encontró la relación 'Hijo(a)', usando ID 3 por defecto.");
                $sonId = 3; // Valor por defecto del código original
            }
        }
        return $sonId;
    }
}
