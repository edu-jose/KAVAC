<?php

namespace Modules\WorkAttendance\Models;

use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @class WorkAttendanceExternalActivityStaffs
 * @brief Gestiona los datos del personal asociado a una asistencia externa
 *
 * @author Ing. Roldan Vargas <roldandvg@gmail.com> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WorkAttendanceExternalActivityStaff extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    protected $table = 'work_attendance_external_activity_staffs';

    /**
     * Lista de atributos para la gestión de fechas
     *
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    protected $with = ['payrollStaff'];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = [
        'payroll_staff_id',
        'work_attendance_external_activity_id',
        'work_attendance_id'
    ];

    /**
     * Get the payrollStaff that owns the WorkAttendanceExternalActivityStaffs
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
     * Get the WorkattendanceExternalActivity that owns the WorkAttendanceExternalActivityStaffs
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function externalActivity(): BelongsTo
    {
        return $this->belongsTo(WorkattendanceExternalActivity::class);
    }

    /**
     * Get the workAttendance that owns the WorkAttendanceExternalActivityStaff
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workAttendance(): BelongsTo
    {
        return $this->belongsTo(WorkAttendance::class);
    }
}
