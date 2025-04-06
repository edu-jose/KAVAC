<?php

namespace App\Models;

use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @class IstitutionSector
 * @brief Datos de las sedes
 *
 * Gestiona el modelo de datos para las sedes
 *
 * @property  string  $name
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Headquarter extends Model implements Auditable
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
        'rif',
        'address',
        'city_id',
        'municipality_id',
        'region_id',
    ];

    protected function getAddressAttribute($value)
    {
        return $value ?? '';
    }

    protected function getRifAttribute($value)
    {
        return $value ?? '';
    }

    protected function getCityIdAttribute($value)
    {
        return $value ?? '';
    }

    protected function getMunicipalityIdAttribute($value)
    {
        return $value ?? '';
    }

    protected function getRegionIdAttribute($value)
    {
        return $value ?? '';
    }

    /**
     * Get the city that owns the Headquarter
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the municipoality that owns the Headquarter
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }

    /**
     * Get the region that owns the Headquarter
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
}
