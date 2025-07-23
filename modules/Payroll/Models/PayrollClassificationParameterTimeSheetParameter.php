<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Modules\Payroll\Models\Parameter;

/**
 * @class PayrollClassificationParameterTimeSheetParameter
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollClassificationParameterTimeSheetParameter extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    protected $table = "payroll_classification_parameter_payroll_time_sheet_parameter";

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
    protected $fillable = ['payroll_classification_parameter_id', 'payroll_time_sheet_parameter_id', 'order'];

    /**
     * Método que obtiene la información de los parámetros de hoja de tiempo
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollTimeSheetParameters()
    {
        return $this->belongsTo(PayrollTimeSheetParameter::class);
    }

    /**
     * Método que obtiene la información de categorias de hoja de tiempo al que aplica los parámetros de la hoja de tiempo
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollClassificationParameter()
    {
        return $this->belongsTo(PayrollClassificationParameter::class);
    }

    /**
     * Get all of the parameterOrder for the PayrollClassificationParameterTimeSheetParameter
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function parameterOrder()
    {
        return $this->hasMany(
            PayrollClassificationParameterTimeSheetOrder::class,
            'payroll_classification_parameter_payroll_time_sheet_parameter_id'
        )->orderBy('order'); // Ordenamos por el campo order;
    }

    /**
     * Get the parameters ordered for this pivot
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function orderedParameters()
    {
        return $this->belongsToMany(
            Parameter::class,
            'payroll_classification_parameter_time_sheet_orders',
            'payroll_classification_parameter_payroll_time_sheet_parameter_id',
            'parameter_id'
        )
        ->using(PayrollClassificationParameterTimeSheetOrder::class)
        ->withPivot('order')
        ->orderBy('pivot_order');
    }
}
