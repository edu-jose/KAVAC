<?php

namespace Modules\Warehouse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @class Warehouse
 * @brief Datos de los almacenes registrados
 *
 * Gestiona el modelo de datos para los almacenes
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Warehouse extends Model implements Auditable
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
        'active',
        'address',
        'parish_id',
        'responsibles',
    ];

        /**
     * Lista de atributos para moldear.
     *
     * @var array<string, string> $casts
     */
    protected $casts = [
        'responsibles' => AsArrayObject::class,
    ];

    /**
     * Método que obtiene la parroquia donde esta ubicado el almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parish()
    {
        return $this->belongsTo(\App\Models\Parish::class);
    }

    /**
     * Método que obtiene las instituciones que gestionan el almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function warehouseInstitutionWarehouses()
    {
        return $this->hasMany(WarehouseInstitutionWarehouse::class);
    }

    /**
     * Método que obtiene las solicitudes de los productos del almacén
     *
     * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function warehouseRequests(): HasMany
    {
        return $this->hasMany(WarehouseRequest::class);
    }

    /**
     * Método que obtiene las solicitudes de productos del almacén
     *
     * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function warehouseExternalRequests(): HasMany
    {
        return $this->hasMany(WarehouseExternalRequest::class);
    }
}
