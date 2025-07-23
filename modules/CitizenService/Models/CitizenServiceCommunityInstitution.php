<?php

namespace Modules\CitizenService\Models;

use App\Models\Image;
use App\Models\InstitutionSector;
use App\Models\InstitutionType;
use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @class CitizenServiceCommunityInstitution
 * @brief Modelo para la gestión de las instituciones de las comunidades
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceCommunityInstitution extends Model implements Auditable
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
        'rif',
        'institution_type_id',
        'institution_sector_id',
        'citizen_service_community_profiling_id',
    ];
    protected $with = ['images', 'institutionType', 'institutionSector'];

    /**
     * Get the communityProfiling that owns the CitizenServiceCommunityInstitution
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function communityProfiling(): BelongsTo
    {
        return $this->belongsTo(CitizenServiceCommunityProfiling::class);
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    /**
     * Get the institutionType that owns the CitizenServiceCommunityInstitution
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institutionType(): BelongsTo
    {
        return $this->belongsTo(InstitutionType::class);
    }

    /**
     * Get the institutionSector that owns the CitizenServiceCommunityInstitution
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institutionSector(): BelongsTo
    {
        return $this->belongsTo(InstitutionSector::class);
    }
}
