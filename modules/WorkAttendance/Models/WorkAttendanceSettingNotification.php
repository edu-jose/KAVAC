<?php

namespace Modules\WorkAttendance\Models;

use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Payroll\Models\PayrollEmployment;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @class WorkAttendanceSettingNotification
 * @brief Gestiona la información de configuración de notificaciones sobre la asistencia del personal
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WorkAttendanceSettingNotification extends Model implements Auditable
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
        'notify',
        'periodicity',
        'position_id',
        'payroll_employment_id',
    ];

    /**
     * Get the payrollEmployment that owns the WorkAttendanceSettingNotification
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollEmployment(): BelongsTo
    {
        return $this->belongsTo(PayrollEmployment::class);
    }
}
