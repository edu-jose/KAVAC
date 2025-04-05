<?php

namespace Modules\WorkAttendance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Carbon\Carbon;

/**
 * @class WorkAttendanceSchedule
 * @brief Gestiona los datos de la jornada laboral
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WorkAttendanceSchedule extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    protected $formatDateTime = 'Y-m-d H:i:s';

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
        'day_name',
        'day_number',
        'description',
        'start_time',
        'end_time',
        'break_time',
        'lunch_time',
        'work_time',
        'active',
        'is_extended'
    ];

    protected $appends = [
        'native_work_time'
    ];

    /*protected $casts = [
        'active' => 'boolean',
        'start_time' => 'datetime:h:i:s a',
        'end_time' => 'datetime:h:i:s a',
    ];*/

    /**
     * Establece el valor del campo work_time
     *
     * @param string $value
     *
     * @return void
     */
    public function setWorkTimeAttribute($value)
    {
        $initial = Carbon::parse($this->attributes['start_time'])->format($this->formatDateTime);
        $final = Carbon::parse($this->attributes['end_time'])->format($this->formatDateTime);
        $this->attributes['work_time'] = Carbon::parse($final)->diffInSeconds(Carbon::parse($initial)) / 60;
    }

    /**
     * Obtiene información del campo work_time en formato HH:MM
     *
     * @param string $value
     *
     * @return string
     */
    public function getWorkTimeAttribute($value)
    {
        $hours = floor($value / 60);
        $minutes = $value % 60;
        return sprintf('%02d:%02d', $hours, $minutes);
    }

    public function getNativeWorkTimeAttribute()
    {
        $initial = Carbon::parse($this->start_time)->format($this->formatDateTime);
        $final = Carbon::parse($this->end_time)->format($this->formatDateTime);
        return Carbon::parse($final)->diffInSeconds(Carbon::parse($initial)) / 60;
    }
}
