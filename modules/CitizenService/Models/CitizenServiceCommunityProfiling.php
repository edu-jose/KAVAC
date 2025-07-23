<?php

namespace Modules\CitizenService\Models;

use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @class CitizenServiceCommunityProfiling
 * @brief Modelo para la gestión de las caracterizaciones de las comunidades
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceCommunityProfiling extends Model implements Auditable
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
        'is_communal_council',
        'is_commune',
        'has_tic_committe',
        'tic_committe_name',
        'main_needs',
        'citizen_service_community_id',
    ];

    protected $with = ['communalCouncils', 'communes', 'institutions', 'community'];

    /**
     * Get the community that owns the CitizenServiceCommunityProfiling
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function community(): BelongsTo
    {
        return $this->belongsTo(CitizenServiceCommunity::class, 'citizen_service_community_id');
    }

    /**
     * Get all of the communityCouncils for the CitizenServiceCommunityProfiling
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function communalCouncils(): HasMany
    {
        return $this->hasMany(CitizenServiceCommunalCouncil::class);
    }

    /**
     * Get all of the communes for the CitizenServiceCommunityProfiling
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function communes(): HasMany
    {
        return $this->hasMany(CitizenServiceCommune::class);
    }

    /**
     * Get all of the institutions for the CitizenServiceCommunityProfiling
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function institutions(): HasMany
    {
        return $this->hasMany(CitizenServiceCommunityInstitution::class);
    }
}
