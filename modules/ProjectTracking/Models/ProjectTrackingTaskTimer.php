<?php

namespace Modules\ProjectTracking\Models;

use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @class ProjectTrackingTaskTimer
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingTaskTimer extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'project_tracking_task_id',
        'start_time',
        'end_time',
        'initial_status_id',
        'final_status_id',
        'time_spent',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     * Get the task that owns the timer.
     */
    public function task()
    {
        return $this->belongsTo(ProjectTrackingTask::class, 'project_tracking_task_id');
    }

    /**
     * Calculate the time spent.
     *
     * @return float
     */
    public function getTimeSpentAttribute()
    {
        if ($this->end_time && $this->start_time) {
            return $this->end_time->diffInSeconds($this->start_time);
        }
        return null;
    }
}
