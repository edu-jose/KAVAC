<?php

namespace Modules\Warehouse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @class WarehouseExternalRequest
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WarehouseExternalRequest extends Model implements Auditable
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
        'code',
        'state',
        'date',
        'first_name',
        'last_name',
        'institution_name',
        'warehouse_id',
        'general_observations',
        'delivered',
        'delivery_date',
        'observations'
    ];

    /** Los métodos con relaciones a otros métodos se debe indicar el tipo de relación a retornar, Ej. public function myRelation(): BelongsTo */
    /**
     * Método que obtiene el almacén asociado a la solicitud
     *
     * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(\Modules\Warehouse\Models\Warehouse::class);
    }

    /**
     * Método que obtiene los productos asociados a la solicitud
     *
     * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function warehouseExternalRequestInventoryProducts(): HasMany
    {
        return $this->hasMany(
            \Modules\Warehouse\Models\WarehouseExternalRequestInventoryProduct::class,
            'warehouse_external_request_id'
        );
    }

    /**
     * Registra la solicitud en la tabla unificada de solicitudes de almacén
     *
     * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
     * @return void
     */
    public function registerUnified(): void
    {
        WarehouseRequestUnified::create([
            'requestable_id' => $this->id,
            'requestable_type' => static::class,
        ]);
    }
}
