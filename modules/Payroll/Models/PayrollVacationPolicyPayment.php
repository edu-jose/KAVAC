<?php

declare(strict_types=1);

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @class PayrollVacationPolicyPayment
 * @brief Datos de las condiciones para pago de vacaciones
 *
 * Gestiona los datos de las condiciones para realizar el pago de solicitudes de vacaciones
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollVacationPolicyPayment extends Model implements Auditable
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
        'base_days',
        'from_year',
        'additional_days',
        'additional_max_days',
        'time_number_concepts',
        'sweep_time',
        'time_anticipation_months',
        'payroll_vacation_policy_id'
    ];

    /**
     * Método que obtiene la información de los conceptos asociada a la política vacacional
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollVacationPolicy(): BelongsTo
    {
        return $this->belongsTo(PayrollVacationPolicy::class);
    }

    /**
     * Método que obtiene la información de los conceptos asociada a la política vacacional
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollVacationPolicyPaymentConcepts(): HasMany
    {
        return $this->hasMany(PayrollVacationPolicyPaymentConcept::class);
    }
}
