<?php

declare(strict_types=1);

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @class PayrollVacationPolicyPaymentConcept
 * @brief Datos de los conceptos asociados a las políticas vacacionales
 *
 * Gestiona los datos de los conceptos asociados a las condiciones de pago de las políticas vacacionales
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollVacationPolicyPaymentConcept extends Model implements Auditable
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
        'payroll_vacation_policy_payment_id',
        'payroll_concept_id'
    ];

    /**
     * Lista de atributos personalizados para mostrar en consultas
     *
     * @var array $appends
     */
    protected $appends = ['text'];

    /**
     * Metodo que obtiene el nombre del concepto utilizado
     *
     * @return string
     */
    public function getTextAttribute(): string
    {
        return $this->payrollConcept?->name ?? '';
    }

    /**
     * Método que obtiene la información de las condiciones de pago asociada a la política vacacional
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollVacationPolicyPayment(): BelongsTo
    {
        return $this->belongsTo(PayrollVacationPolicyPayment::class);
    }

    /**
     * Método que obtiene la información de los conceptos para asociarlos a las condiciones de pago
     * de la política vacacional
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollConcept(): BelongsTo
    {
        return $this->belongsTo(PayrollConcept::class);
    }
}
