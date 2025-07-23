<?php

namespace Modules\CitizenService\Models;

use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @class CitizenServiceContactBook
 * @brief Modelo para la tabla de agenda de contactos de la OAC
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceContactBook extends Model implements Auditable
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
        'identity_card',
        'name',
        'surname',
        'position',
        'description',
        'citizen_service_served_institution_id',
    ];

    /**
     * Get the servedInstitution that owns the CitizenServiceContactBook
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function servedInstitution(): BelongsTo
    {
        return $this->belongsTo(
            CitizenServiceServedInstitution::class,
            'citizen_service_served_institution_id'
        );
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
}
