<?php

namespace Modules\CitizenService\Models;

use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @class CitizenServiceProcedure
 * @brief Gestiona los datos de los trámites
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceProcedure extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

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
        'name',
        'description',
        'citizen_service_procedure_type_id',
    ];

    protected $with = ['citizenServiceProcedureType'];

    /**
     * Get the citizenServiceProcedureType that owns the CitizenServiceProcedure
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function citizenServiceProcedureType(): BelongsTo
    {
        return $this->belongsTo(CitizenServiceProcedureType::class);
    }
}
