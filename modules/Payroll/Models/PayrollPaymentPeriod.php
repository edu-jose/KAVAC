<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class     PayrollPaymentPeriod
 * @brief     Datos de los períodos de pago de nómina
 *
 * Gestiona el modelo de períodos de pago de nómina
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollPaymentPeriod extends Model implements Auditable
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
     * Lista de relaciones a cargar con el modelo
     *
     * @var array $with
     */
    protected $with = ['payrollPaymentType'];

    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = [
        'number', 'start_date', 'start_day', 'end_date', 'end_day', 'payment_status', 'payroll_payment_type_id', 'availability_status'
    ];

    /**
     * Append de atributos
     *
     * @var array $appends
     */

    protected $appends = [
        'number_of_days_monday',
    ];

    /**
     * Método que obtiene el numero de días Lunes del periodo en cuestión
     *
     * @author    Francisco J. P. Ruiz <fpenya@cenditel.gob.ve>
     *
     * @return    integer
     */
    public function getNumberOfDaysMondayAttribute()
    {
        // Convertir start_date y end_date a instancias de Carbon
        $startDate = \Carbon\Carbon::parse($this->start_date); // Fecha de inicio del período
        $endDate = \Carbon\Carbon::parse($this->end_date); // Fecha de fin del período

        $mondaysCount = 0;

        // Contar los Lunes dentro del período
        while ($startDate->lte($endDate)) {
            if ($startDate->isMonday()) {
                $mondaysCount++;
            }
            $startDate->addDay();
        }

        return $mondaysCount;
    }

    /**
     * Método que obtiene la información del tipo de pago asociado al período de pago de nómina
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollPaymentType()
    {
        return $this->belongsTo(PayrollPaymentType::class);
    }

    /**
     * Método que obtiene la información del registro de nómina asociado al período de pago
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function payroll()
    {
        return $this->hasOne(Payroll::class);
    }
}
