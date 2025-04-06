<?php

namespace Modules\Purchase\Models;

use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @class PurchaseBudgetaryAvailability
 * @brief Gestiona la información, procesos, consultas y relaciones asociadas al modelo
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseBudgetaryAvailability extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Establece las relaciones por defecto que se retornan con las consultas
     *
     * @var array $with
     */
    protected $with = [
        'documentFile'
    ];

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
        'item_code',
        'item_name',
        'purchase_base_budgets_id',
        'amount',
        'description',
        'availability',
        'date',
        'spac_description',
        'budget_account_id',
        'budget_specific_action_id',
        'purchase_common_budgetary_availability_id',
    ];

    /**
     * Establece la relación con el archivo de documento de la disponibilidad presupuestaria
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function documentFile()
    {
        return $this->morphOne(Document::class, 'documentable');
    }

    /**
     * Establece la relación con los datos comunes de la disponibilidad presupuestaria
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchaseCommonBudgetaryAvailability(): BelongsTo
    {
        return $this->belongsTo(PurchaseCommonBudgetaryAvailability::class);
    }

    /**
     * Get the PurchaseBaseBudget that owns the PurchaseBudgetaryAvailability
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchaseBaseBudget(): BelongsTo
    {
        return $this->belongsTo(PurchaseBaseBudget::class, 'purchase_base_budgets_id');
    }
}
