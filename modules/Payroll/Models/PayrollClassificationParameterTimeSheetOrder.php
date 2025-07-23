<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use App\Models\Parameter;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @class PayrollClassificationParameterTimeSheetOrder
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollClassificationParameterTimeSheetOrder extends Pivot implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    protected $table = 'payroll_classification_parameter_time_sheet_orders';
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
    protected $fillable = ['payroll_classification_parameter_payroll_time_sheet_parameter_id', 'parameter_id', 'order'];

    /**
     * Get the PayrollClassificationParameterTimeSheetParameter that owns the PayrollClassificationParameterTimeSheetOrder
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollClassificationParameterTimeSheetParameter()
    {
        return $this->belongsTo(PayrollClassificationParameterTimeSheetParameter::class, 'payroll_classification_parameter_payroll_time_sheet_parameter_id');
    }

    /**
     * Get the parameter that owns the PayrollClassificationParameterTimeSheetOrder
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parameter()
    {
        return $this->belongsTo(Parameter::class, 'parameter_id');
    }
}
