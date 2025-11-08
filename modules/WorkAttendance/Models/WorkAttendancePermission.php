<?php

namespace Modules\WorkAttendance\Models;

use Carbon\Carbon;
use App\Models\Document;
use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @class WorkAttendancePermission
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WorkAttendancePermission extends Model implements Auditable
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
        'start_date_at',
        'start_time_at',
        'end_date_at',
        'end_time_at',
        'reason',
        'status',
        'comments',
        'payroll_staff_id'
    ];

    protected $casts = [
        /*'start_date_at' => 'date',
        'end_date_at' => 'date',*/
        'status' => 'string'
    ];

    protected $with = ['document'];

    public function getStartTimeAtAttribute($value)
    {
        if (!$value) {
            $dayOfWeek = Carbon::parse($this->start_date_at)->dayOfWeek;
            $schedule = WorkAttendanceSchedule::where('day_number', $dayOfWeek)
                ->first();
            if ($schedule) {
                return Carbon::parse($schedule->start_time)->format('H:i');
            }
        }
        return $value;
    }

    public function getEndTimeAtAttribute($value)
    {
        if (!$value) {
            $dayOfWeek = Carbon::parse($this->end_date_at)->dayOfWeek;
            $schedule = WorkAttendanceSchedule::where('day_number', $dayOfWeek)
                ->first();
            return $schedule->end_time ?? null;
        }
        return $value;
    }

    /**
     * Get the payrollStaff that owns the WorkAttendancePermission
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollStaff(): BelongsTo
    {
        return $this->belongsTo(PayrollStaff::class);
    }

    /**
     * Establece la relación con un archivo de documento
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function document(): MorphOne
    {
        return $this->morphOne(Document::class, 'documentable');
    }
}
