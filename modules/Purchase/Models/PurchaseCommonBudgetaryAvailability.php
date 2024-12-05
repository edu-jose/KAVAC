<?php

namespace Modules\Purchase\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @class PurchaseCommonBudgetaryAvailability
 * @brief Gestiona la información, procesos, consultas y relaciones asociadas al modelo
 *
 * [descripción corta]
 *
 * @author  Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseCommonBudgetaryAvailability extends Model implements Auditable
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
        'budgetable_id',
        'budgetable_type',
    ];

    /**
     * PurchaseCommonBudgetaryAvailability morphs to models in budgetable_type
     *
     * @author  Ing. Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function budgetable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the PurchaseCommonBudgetaryAvailability that owns the PurchaseBudgetaryAvailability
     *
     * @author  Ing. Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function purchaseBudgetaryAvailability(): HasOne
    {
        return $this->hasOne(PurchaseBudgetaryAvailability::class);
    }
}
