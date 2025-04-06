<?php

namespace Modules\WorkAttendance\Models;

use App\Traits\ModelsTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Payroll\Models\PayrollStaff;

/**
 * @class WorkAttendance
 * @brief Gestión de la información de la asistencia del personal
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WorkAttendance extends Model implements Auditable
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
        'date_at',
        'entry_time',
        'exit_time',
        'payroll_staff_id'
    ];

    /**
     * Formatos en campos
     *
     * @var array $casts
     */
    protected $casts = [
        'entry_time' => 'datetime:h:i:s a',
        'exit_time' => 'datetime:h:i:s a'
    ];

    protected $appends = ['work_time_formated'];

    public function getWorkTimeFormatedAttribute()
    {
        $entry = Carbon::parse($this->entry_time ?? $this->exit_time ?? '00:00:00')->format('Y-m-d H:i:s');
        $exit = Carbon::parse($this->exit_time ?? $this->entry_time ?? '00:00:00')->format('Y-m-d H:i:s');
        $time = Carbon::parse($exit)->diffInSeconds(Carbon::parse($entry)) / 60;
        $hours = floor($time / 60);
        $minutes = $time % 60;
        return sprintf('%02d:%02d', $hours, $minutes);
    }

    /**
     * Get the payrollStaff that owns the WorkAttendance
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollStaff(): BelongsTo
    {
        return $this->belongsTo(PayrollStaff::class);
    }

    /**
     * Consulta que filtra por empleado
     *
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeFilterByEmployment($query, $value)
    {
        return $query->whereHas('payrollStaff', function ($query) use ($value) {
            $query->whereHas('payrollEmployment', function ($query) use ($value) {
                $query->where('id', $value);
            });
        });
    }

    /**
     * Consulta que filtra por cargo
     *
     * @param mixed $query
     * @param mixed $value
     */
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

    public function scopeFilterByDepartment($query, $value)
    {
        return $query->whereHas('payrollStaff', function ($query) use ($value) {
            $query->whereHas('payrollEmployment', function ($query) use ($value) {
                $query->whereHas('department', function ($query) use ($value) {
                    $query->where('departments.id', $value);
                });
            });
        });
    }
}
