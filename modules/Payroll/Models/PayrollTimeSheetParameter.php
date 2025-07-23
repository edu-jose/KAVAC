<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class PayrollTimeSheetParameter
 * @brief Gestiona la información, procesos, consultas y relaciones asociadas al modelo
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollTimeSheetParameter extends Model implements Auditable
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
    protected $fillable = ['code', 'name', 'description', 'breaks_allowed_per_week', 'validate_total_for_period'];

    /**
     * Método que obtiene la información de los parámetros de hoja de tiempo
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function payrollParameterTimeSheetParameters()
    {
        return $this->hasMany(PayrollParameterTimeSheetParameter::class);
    }

    /**
     * Método que obtiene la información de los tipos de pago a los que afectan los parámetros de hoja de tiempo
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function payrollPaymentTypeTimeSheetParameters()
    {
        return $this->hasMany(PayrollPaymentTypeTimeSheetParameter::class);
    }

    /**
     * Método que obtiene la información de las categorias de hoja de tiempo a los que afectan los parámetros de hoja de tiempo
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function payrollExceptionTypeTimeSheetParameters()
    {
        return $this->hasMany(PayrollExceptionTypeTimeSheetParameter::class);
    }

    /**
     * Método que obtiene la información de la hoja de tiempo asociada a los parámetros de hoja de tiempo
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function payrollTimeSheet()
    {
        return $this->hasOne(PayrollTimeSheet::class);
    }

    /**
     * Obtiene la relación con las hojas de tiempo
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollTimeSheets()
    {
        return $this->hasMany(PayrollTimeSheet::class);
    }

    /**
     * Obtiene la relación con las hojas de tiempo pendientes
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollTimeSheetsPending()
    {
        return $this->hasMany(PayrollTimeSheetPending::class);
    }

    /**
     * Método que obtiene la información de los parámetros de clasificación
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
    */
    public function classificationParameters()
    {
        return $this->belongsToMany(PayrollClassificationParameter::class)
                ->withTimestamps()
                ->withPivot('order') // Incluye la columna 'order' en la relación
                ->orderBy('pivot_order'); // Ordena por la columna 'order' en la tabla pivote
    }

    /**
     * Obtiene los registros pivot de la relación con classificationParameters
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function classificationParameterPivots()
    {
        return $this->hasMany(
            PayrollClassificationParameterTimeSheetParameter::class,
            "payroll_time_sheet_parameter_id"
        );
    }
}
