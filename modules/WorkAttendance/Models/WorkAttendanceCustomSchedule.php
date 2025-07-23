<?php

namespace Modules\WorkAttendance\Models;

use Carbon\Carbon;
use App\Models\Document;
use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\WorkAttendance\Models\PayrollStaff;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @class WorkAttendanceCustomSchedule
 * @brief Modelo para la gestión de horarios personalizados
 *
 * @author Ing. Roldan Vargas <roldandvg@gmail.com> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WorkAttendanceCustomSchedule extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Lista de atributos para la gestión de fechas
     *
     * @var array $dates
     */
    protected $dates = ['deleted_at', 'start_date_at', 'end_date_at'];

    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = [
        'start_date_at',
        'end_date_at',
        'reason',
        'active',
        'is_teacher',
        'is_student',
        'is_other',
        'schedule',
        'custom_schedule_type',
        'payroll_staff_id',
        'authorized_payroll_staff_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $with = [
        'document',
        'payrollStaff',
        'authorizedPayrollStaff'
    ];

    protected $casts = [
        'schedule' => 'json',
    ];

    public function getStartDateAtAttribute($value)
    {
        $startDate = Carbon::parse($value);
        return $startDate->format('Y-m-d');
    }

    public function getEndDateAtAttribute($value)
    {
        $endDate = Carbon::parse($value);
        return $endDate->format('Y-m-d');
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

    /**
     * Get the payrollStaff that owns the WorkAttendanceCustomSchedule
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollStaff(): BelongsTo
    {
        return $this->belongsTo(PayrollStaff::class)->select(
            'first_name',
            'last_name',
            'id'
        )->orderBy('first_name')->orderBy('last_name');
    }

    /**
     * Get the payrollStaff that owns the WorkAttendanceCustomSchedule
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function authorizedPayrollStaff(): BelongsTo
    {
        return $this->belongsTo(PayrollStaff::class, 'authorized_payroll_staff_id')->select(
            'first_name',
            'last_name',
            'id'
        )->orderBy('first_name')->orderBy('last_name');
    }

    public function scopeFilterByEmployment($query, $value)
    {
        return $query->whereHas('payrollStaff', function ($query) use ($value) {
            $query->whereHas('payrollEmployment', function ($query) use ($value) {
                $query->where('id', $value);
            });
        });
    }
}
