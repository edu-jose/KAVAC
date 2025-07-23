<?php

namespace Modules\CitizenService\Models;

use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @class CitizenServiceCommunalCouncil
 * @brief Modelo para la gestión de los consejos comunales
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceCommunalCouncil extends Model implements Auditable
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

    protected $hidden = ['created_at', 'updated_at'];

    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = [
        'name',
        'citizen_service_community_profiling_id',
    ];

    /**
     * Get the communityProfiling that owns the CitizenServiceCommunalCouncil
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function communityProfiling(): BelongsTo
    {
        return $this->belongsTo(CitizenServiceCommunityProfiling::class);
    }
}
