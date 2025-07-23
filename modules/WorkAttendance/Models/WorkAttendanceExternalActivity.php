<?php

namespace Modules\WorkAttendance\Models;

use Carbon\Carbon;
use App\Models\Document;
use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Model;
use Modules\Payroll\Models\PayrollStaff;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @class WorkAttendanceExternalActivity
 * @brief Gestiona los datos de la actividad externa
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WorkAttendanceExternalActivity extends Model implements Auditable
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

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = [
        'start_at',
        'end_at',
        'reason',
    ];

    protected $with = ['document', 'staffs'];

    protected $appends = ['start_date_at', 'start_time_at', 'end_date_at', 'end_time_at'];

    public function getStartDateAtAttribute()
    {
        return Carbon::parse($this->start_at)->format('Y-m-d');
    }

    public function getStartTimeAtAttribute()
    {
        return Carbon::parse($this->start_at)->format('H:i');
    }

    public function getEndDateAtAttribute()
    {
        return Carbon::parse($this->end_at)->format('Y-m-d');
    }

    public function getEndTimeAtAttribute()
    {
        return Carbon::parse($this->end_at)->format('H:i');
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
     * Get all of the staffs for the WorkAttendanceExternalActivity
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function staffs(): HasMany
    {
        return $this->hasMany(WorkAttendanceExternalActivityStaff::class);
    }
}
