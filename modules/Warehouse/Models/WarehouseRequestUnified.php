<?php

namespace Modules\Warehouse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @class WarehouseRequestUnified
 * @brief Modelo unificado para solicitudes de almacén internas y externas.
 *
 * Este modelo representa una abstracción polimórfica que permite acceder de forma
 * centralizada a las solicitudes de almacén, tanto internas (WarehouseRequest)
 * como externas (WarehouseExternalRequest). Utiliza una relación morphTo para
 * vincular dinámicamente el modelo correspondiente.
 *
 * Es útil para realizar consultas globales, reportes, filtros por estado,
 * y operaciones que involucren ambos tipos de solicitudes sin duplicar lógica.
 *
 *
 * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WarehouseRequestUnified extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Nombre de la tabla asociada al modelo
     *
     * @var string $table
     */
    protected $table = 'warehouse_requests_unified';

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
        'requestable_id',
        'requestable_type',
    ];

    /**
     * Relación polimórfica con el modelo relacionado
     *
     * @return MorphTo
     */
    public function requestable(): MorphTo
    {
        return $this->morphTo();
    }
}
