<?php

namespace Modules\CitizenService\Models;

use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Payroll\Models\PayrollEmployment;

/**
 * @class CitizenServiceRequestTeam
 * @brief Gestiona la información de los equipos asignados a solicitudes de trámites
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceRequestTeam extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Lista de atributos para la gestión de fechas
     *
     * @var array $dates
     */
    protected $dates = ['deleted_at', 'start_at'];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = [
        'start_at',
        'tasks',
        'payroll_employee_id',
        'citizen_service_request_id',
    ];

    /**
     * Get the citizenServiceRequest that owns the CitizenServiceRequestTeam
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function citizenServiceRequest(): BelongsTo
    {
        return $this->belongsTo(CitizenServiceRequest::class);
    }

    /**
     * Get the payrollEmployee that owns the CitizenServiceRequestTeam
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollEmployee(): BelongsTo
    {
        return $this->belongsTo(
            PayrollEmployment::class,
            'payroll_employee_id',
            'id'
        );
    }
}
