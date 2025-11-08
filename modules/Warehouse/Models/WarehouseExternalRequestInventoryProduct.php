<?php

namespace Modules\Warehouse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @class WarehouseExternalRequestInventoryProduct
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WarehouseExternalRequestInventoryProduct extends Model implements Auditable
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
        'warehouse_external_request_id',
        'warehouse_inventory_product_id',
        'quantity',
        'new_exist',
    ];

    /** Los métodos con relaciones a otros métodos se debe indicar el tipo de relación a retornar, Ej. public function myRelation(): BelongsTo */

    /**
     * Método que obtiene el producto asociado al inventario
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function warehouseInventoryProduct(): BelongsTo
    {
        return $this->belongsTo(WarehouseInventoryProduct::class);
    }

    /**
     * Método que obtiene la solicitud registrada
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function warehouseExternalRequest(): BelongsTo
    {
        return $this->belongsTo(WarehouseExternalRequest::class);
    }
}
