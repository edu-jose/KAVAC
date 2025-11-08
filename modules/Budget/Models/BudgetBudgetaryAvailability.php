<?php

namespace Modules\Budget\Models;

use App\Models\Document;
use App\Traits\ModelsTrait;
use Nwidart\Modules\Facades\Module;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Budget\Models\BudgetCommonBudgetaryAvailability;

/**
 * @class BudgetBudgetaryAvailability
 * @brief Gestiona la información, procesos, consultas y relaciones asociadas al modelo
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetBudgetaryAvailability extends Model implements Auditable
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
        'purchase_base_budget_id',
        'amount',
        'description',
        'availability',
        'date',
        'spac_description',
        'budget_account_id',
        'budget_specific_action_id',
        'budget_common_budgetary_availability_id',
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
    public function budgetCommonBudgetaryAvailability(): BelongsTo
    {
        return $this->belongsTo(BudgetCommonBudgetaryAvailability::class);
    }

    /**
     * Get the PurchaseBaseBudget that owns the PurchaseBudgetaryAvailability
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchaseBaseBudget(): BelongsTo
    {
        return (
            Module::has('Purchase') && Module::isEnabled('Purchase')
        ) ? $this->belongsTo(\Modules\Purchase\Models\PurchaseBaseBudget::class, 'purchase_base_budget_id') : null;
    }
}
