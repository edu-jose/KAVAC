<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Nwidart\Modules\Facades\Module;

/**
 * @class      Payroll
 * @brief      Datos de registros de nómina
 *
 * Gestiona el modelo de registros de nómina
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Payroll extends Model implements Auditable
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
        'code', 'name', 'payroll_parameters', 'payroll_payment_period_id',
        'salary_tabulators', 'concept_types', 'created_at', 'document_status_id',
        'is_vacation_payroll'
    ];

    /**
     * Lista de atributos de relacion consultados automáticamente
     *
     * @var array $with
     */
    protected $with = ['payrollPaymentPeriod'];

    /**
     * Lista de atributos con el tipo de dato a retornar
     *
     * @var array
     */
    protected $casts = [
        'salary_tabulators' => 'array',
        'concept_types' => 'array',
    ];

    public function getTotalSalaryAttribute()
    {
        if (count($this->concept_types) > 0) {
            $total = 0;
            foreach ($this->concept_types as $conceptTypes) {
                foreach ($conceptTypes as $conceptType) {
                    if ($conceptType['sign'] === '+') {
                        $total += (float)$conceptType['value'];
                    } elseif ($conceptType['sign'] === '-') {
                        $total -= (float)$conceptType['value'];
                    }
                }
            }
            return $total;
        }
        return 0;
    }

    /**
     * Método que obtiene la información del período de pago asociado a la nómina
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollPaymentPeriod()
    {
        return $this->belongsTo(PayrollPaymentPeriod::class);
    }

    /**
     * Método que obtiene la información del status asociado a la nómina
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function documentStatus()
    {
        return $this->belongsTo(DocumentStatus::class);
    }

    /**
     * Método que obtiene la información de los trabajadores asociados a la nómina
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollStaffPayrolls()
    {
        return $this->hasMany(PayrollStaffPayroll::class);
    }

    /**
     * Método que obtiene la información de los parámetros reiniciables a cero asociados a la nómina
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollResetParameters()
    {
        return $this->hasMany(PayrollResetParameter::class);
    }

    /**
     * Establece la relación con los datos comunes de la disponibilidad presupuestaria
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function purchaseCommonBudgetaryAvailability()
    {
        return (Module::has('Purchase') && Module::isEnabled('Purchase'))
            ? $this->morphOne(\Modules\Purchase\Models\PurchaseCommonBudgetaryAvailability::class, 'budgetable') : [];
    }
}
