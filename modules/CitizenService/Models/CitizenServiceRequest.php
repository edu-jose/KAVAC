<?php

namespace Modules\CitizenService\Models;

use App\Models\Image;
use App\Models\Gender;
use App\Models\Document;
use App\Models\Profession;
use App\Traits\ModelsTrait;
use App\Models\InstitutionSector;
use Illuminate\Database\Eloquent\Model;
use Modules\Payroll\Models\PayrollStaff;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Payroll\Models\PayrollNationality;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Payroll\Models\PayrollInstructionDegree;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\CitizenService\Models\CitizenServiceProcedure;

/**
 * @class CitizenService
 * @brief Datos de información de ingresar solicitud
 *
 * Gestiona el modelo de ingresar solicitud
 *
 * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceRequest extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Lista de relaciones a cargar por defecto
     *
     * @var array $with
     */
    protected $with = ['city', 'parish', 'documentFile'];

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
        'gender',
        'nationality',
        'community',
        'location',
        'commune',
        'communal_council',
        'population_size',
        'first_name',
        'last_name',
        'id_number',
        'email',
        'date',
        'birth_date',
        'age',
        'address',
        'motive_request',
        'attribute',
        'state',
        'institution_name',
        'institution_address',
        'rif',
        'web',
        'type_institution',
        'file_counter',
        'date_verification',
        'type_team',
        'brand',
        'model',
        'serial',
        'color',
        'transfer',
        'inventory_code',
        'entryhour',
        'exithour',
        'informationteam',
        'other',
        'family_income',
        'family_burden',
        'is_household_head',
        'has_work',
        'has_venapp_report',
        'venapp_report_number',
        'observations',
        'gender_id',
        'nationality_id',
        'director_id',
        'city_id',
        'parish_id',
        'citizen_service_request_type_id',
        'citizen_service_department_id',
        'sector_id',
        'profession_id',
        'payroll_instruction_degree_id',
        'citizen_service_procedure_id',
        'citizen_transaction_type_id',
        'document_id'
    ];

    public function getObservationsAttribute($value)
    {
        return $value ?? '';
    }

    /**
     * Obtiene todos los número telefónicos asociados a la solicitud
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function phones()
    {
        return $this->morphMany(\App\Models\Phone::class, 'phoneable');
    }

    /**
     * Get the document that owns the CitizenServiceRequest
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Obtiene todos los documentos asociados a la solicitud
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Establece la relación con un archivo de documento
     *
     * @author  Miguel Narvaez <mnarvaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function documentFile()
    {
        return $this->morphOne(Document::class, 'documentable');
    }

    /**
     * Obtiene todas las imágenes asociadas a la solicitud
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    /**
     * Método que obtiene la solicitud asociado a un departamento
     *
     * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function citizenServiceDepartment()
    {
        return $this->belongsTo(CitizenServiceDepartment::class);
    }

    /**
     * Método que obtiene la solicitud asociado a un tipo de solicitud
     *
     * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function citizenServiceRequestType()
    {
        return $this->belongsTo(CitizenServiceRequestType::class);
    }

    /**
     * Método que obtiene la solicitud asociado a una ciudad
     *
     * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Método que obtiene la solicitud asociado a una parroquia
     *
     * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parish()
    {
        return $this->belongsTo(Parish::class);
    }

    /**
     * Establece la relación con los indicadores de la solicitud
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function citizenServiceIndicator()
    {
        return $this->hasMany(CitizenServiceAddIndicator::class, 'request_id', 'id');
    }

    /**
     * Método que obtiene la solicitud asociado a un género
     *
     * @author Oscar González <ojgonzalez@cenditel.gob.ve | xxmaestroyixx@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function requestGender()
    {
        return $this->belongsTo(Gender::class, 'gender_id', 'id');
    }

    /**
     * Método que obtiene la solicitud asociado a una nacionalidad
     *
     * @author Oscar González <ojgonzalez@cenditel.gob.ve | xxmaestroyixx@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function requestNationality()
    {
        return $this->belongsTo(PayrollNationality::class, 'nationality_id', 'id');
    }

    /**
     * Método que obtiene la solicitud asociado a un género
     *
     * @author Oscar González <ojgonzalez@cenditel.gob.ve | xxmaestroyixx@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function requestDirector()
    {
        return $this->belongsTo(PayrollStaff::class, 'director_id', 'id');
    }

    /**
     * Get the sector that owns the CitizenServiceRequest
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sector(): BelongsTo
    {
        return $this->belongsTo(InstitutionSector::class, 'sector_id', 'id');
    }

    /**
     * Get the profession that owns the CitizenServiceRequest
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function profession(): BelongsTo
    {
        return $this->belongsTo(Profession::class);
    }

    /**
     * Get the payrollInstructionDegree that owns the CitizenServiceRequest
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollInstructionDegree(): BelongsTo
    {
        return $this->belongsTo(PayrollInstructionDegree::class);
    }

    /**
     * Get the procedure that owns the CitizenServiceRequest
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function procedure(): BelongsTo
    {
        return $this->belongsTo(CitizenServiceProcedure::class, 'citizen_service_procedure_id', 'id');
    }

    /**
     * Establece la relación con los tipos de transacción
     *
     * @author  Miguel Narvaez <mnarvaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transactionType()
    {
        return $this->belongsTo(CitizenServiceTransactionType::class, 'citizen_transaction_type_id', 'id');
    }

    /**
     * Establece la relación con el código de registro de la solicitud
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function codeCitizenServiceRegister()
    {
        return $this->belongsTo(CitizenServiceRegister::class);
    }

    /**
     * Get all of the teams for the CitizenServiceRequest
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function teams(): HasMany
    {
        return $this->hasMany(CitizenServiceRequestTeam::class);
    }

    /**
     * Scope de búsqueda de solicitudes
     *
     * @param Builder $query Objeto con la consulta
     * @param mixed $request Datos de la petición
     *
     * @return Builder
     */
    public function scopeSearch(
        $query,
        $request
    ) {
        $types = $request->citizen_service_request_types;
        $states = $request->citizen_service_states;
        $citizen_service_request_types = [];
        $citizen_service_states = [];
        $departments = [];
        $procedures = [];

        if (is_array($types)) {
            $citizen_service_request_types = $types;
        } elseif (!empty($types)) {
            $citizen_service_request_types = json_decode($types, true);
        }

        if (is_array($states)) {
            $citizen_service_states = $states;
        } elseif (!empty($states)) {
            $citizen_service_states = json_decode($states);
        }

        if (is_array($request->departments)) {
            $departments = $request->departments;
        } elseif (!empty($request->departments)) {
            $departments = json_decode($request->departments, true);
        }

        if (is_array($request->procedures)) {
            $procedures = $request->procedures;
        } elseif (!empty($request->procedures)) {
            $procedures = json_decode($request->procedures, true);
        }

        $start_date = ($request->type_search == 'date') ? null : $request->start_date;
        $end_date = ($request->type_search == 'date') ? null : $request->end_date;
        $date = ($request->type_search == 'period') ? null : $request->date;

        $listRequestTypes = [];
        if ($citizen_service_request_types) {
            foreach ($citizen_service_request_types as $field) {
                array_push($listRequestTypes, $field->id ?? $field["id"]);
            }
        }
        $listStates = [];
        if ($citizen_service_states) {
            foreach ($citizen_service_states as $field) {
                array_push($listStates, $field->id ?? $field["id"]);
            }
        }
        $listDepartments = [];
        if ($departments) {
            foreach ($departments as $field) {
                array_push($listDepartments, $field->id ?? $field["id"]);
            }
        }
        $listProcedures = [];
        if ($procedures) {
            foreach ($procedures as $field) {
                array_push($listProcedures, $field->id ?? $field["id"]);
            }
        }
        if (isset($start_date) || isset($end_date)) {
            $query->whereBetween("date", [$start_date,$end_date]);
        }
        if (count($listRequestTypes) > 0) {
            $query->whereIn("citizen_service_request_type_id", $listRequestTypes);
        }
        if (count($listDepartments) > 0) {
            $query->whereIn("citizen_service_department_id", $listDepartments);
        }
        if (count($listProcedures) > 0) {
            $query->whereIn("citizen_service_procedure_id", $listProcedures);
        }
        if (count($listStates) > 0) {
            $query->whereIn("state", $listStates);
        }
        if (isset($date)) {
            $query->where("date", $date);
        }


        return $query;
    }
}
