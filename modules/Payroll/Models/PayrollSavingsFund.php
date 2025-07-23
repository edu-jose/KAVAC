<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class PayrollSavingsFund
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSavingsFund extends Model implements Auditable
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
    protected $fillable = ['payroll_staff_id', 'from_date', 'to_date', 'percetage'];

    protected $casts = [
        //'date_at' => 'date:d-m-Y',
        'entry_time' => 'datetime:h:i:s a',
        'exit_time' => 'datetime:h:i:s a'
    ];

    /**
     * Trabajador asociado
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollStaff()
    {
        return $this->belongsTo(PayrollStaff::class);
    }

    public function scopeFilterByEmployment($query, $value)
    {
        return $query->whereHas('payrollStaff', function ($query) use ($value) {
            $query->whereHas('payrollEmployment', function ($query) use ($value) {
                $query->where('id', $value);
            });
        });
    }

    public function scopeFilterByPosition($query, $value)
    {
        return $query->whereHas('payrollStaff', function ($query) use ($value) {
            $query->whereHas('payrollEmployment', function ($query) use ($value) {
                $query->whereHas('payrollPositions', function ($query) use ($value) {
                    $query->where('payroll_positions.id', $value);
                });
            });
        });
    }
}
