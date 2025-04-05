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
 * @class      PayrollWorkloadPosition
 * @brief      Datos de los trabajadores asociados a una carga horaria
 *
 * Gestiona el modelo de datos de los trabajadores asociados a una carga horaria
 *
 * @author     Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollWorkloadPosition extends Model implements Auditable
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
        'payroll_workload_id', 'payroll_position_id'
    ];

    /**
     * Lista de atributos personalizados para mostrar en consultas
     *
     * @var array $appends
     */
    protected $appends = ['text'];

    /**
     * Método que obtiene la carga horaria asociada al trabajador
     *
     * @method  payrollWorkload
     *
     * @return  \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollWorkload(): BelongsTo
    {
        return $this->belongsTo(PayrollWorkload::class);
    }

    /**
     * Método que obtiene el trabajador asociado a la carga horaria
     *
     * @method  payrollPosition
     *
     * @return  \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollPosition(): BelongsTo
    {
        return $this->belongsTo(PayrollPosition::class);
    }

    /**
     * Método que devuelve el nombre del cargo del trabajador asociado a la carga horaria
     *
     * @method  getTextAttribute
     *
     * @return  string
     */
    public function getTextAttribute()
    {
        return $this->payrollPosition->name;
    }
}
