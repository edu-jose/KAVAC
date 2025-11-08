<?php

namespace Modules\Sale\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class SaleCustomerPhone
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleCustomerPhone extends Model implements Auditable
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
        'sale_customer_id', 'type', 'area_code', 'number', 'extension'
    ];

    protected $casts = [
        'type' => 'string',
        'area_code' => 'string',
        'number' => 'string',
        'extension' => 'string'
    ];

    public function customer()
    {
        return $this->belongsTo(SaleCustomerManagement::class, 'sale_customer_id');
    }

    /** Los métodos con relaciones a otros métodos se debe indicar el tipo de relación a retornar, Ej. public function myRelation(): BelongsTo */
}
