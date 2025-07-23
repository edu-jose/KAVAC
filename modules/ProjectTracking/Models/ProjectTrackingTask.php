<?php

namespace Modules\ProjectTracking\Models;

use App\Models\User;
use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Modules\ProjectTracking\Models\ProjectTrackingTaskTimer;

/**
 * @class ProjectTrackingTask
 * @brief Gestiona la información, procesos, consultas y relaciones asociadas al modelo
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingTask extends Model implements Auditable
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
        'project_name',
        'activity_plan_id',
        'subproject_name',
        'product_name',
        'name',
        'description',
        'employers_id',
        'priority_id',
        'start_date',
        'end_date',
        'new_end_date',
        'cut_off_time',
        'activity_status_id',
        'depending_task_id',
        'dependency_type_id',
        'task_type_id',
        'weight',
        'percentage',
        'reviewer_id',
        'approver_id',
        'is_private',
        'payroll_staffs',
        'tags'
    ];

    /**
     * Establece la relación con el proyecto
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(ProjectTrackingProject::class, 'project_name', 'id');
    }

    /**
     * Establece la relación con el subproyecto
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subproject()
    {
        return $this->belongsTo(ProjectTrackingSubProject::class, 'subproject_name', 'id');
    }

    /**
     * Establece la relación con el producto
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(ProjectTrackingProduct::class, 'product_name', 'id');
    }

    /**
     * Establece la relación con el plan de actividad
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function activityPlan()
    {
        return $this->belongsTo(ProjectTrackingActivityPlan::class);
    }

    /**
     * Establece la relación con la actividad
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function activity()
    {
        return $this->belongsTo(ProjectTrackingActivity::class, 'activity_plan_id', 'id');
    }

    /**
     * Establece la relación con el estatus de la actividad
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function activityStatus()
    {
        return $this->belongsTo(ProjectTrackingActivityStatus::class);
    }

    /**
     * Establece la relación con el responsable de la actividad
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function responsable()
    {
        return $this->belongsTo(ProjectTrackingActivityPlanTeam::class, 'employers_id', 'id');
    }

    /**
     * Establece la relación con el aprobador de la tarea
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function approver()
    {
        return $this->belongsTo(ProjectTrackingActivityPlanTeam::class, 'approver_id', 'id');
    }

    /**
     * Establece la relación con el revisor de la tarea
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reviewer()
    {
        return $this->belongsTo(ProjectTrackingActivityPlanTeam::class, 'reviewer_id', 'id');
    }

    /**
     * Establece la relación con la prioridad
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function priority()
    {
        return $this->belongsTo(ProjectTrackingPriority::class);
    }

    public function subTasks()
    {
        return $this->hasMany(ProjectTrackingSubTask::class, 'task_id', 'id');
    }
    public function taskTimers()
    {
        return $this->hasMany(ProjectTrackingTaskTimer::class, 'project_tracking_task_id', 'id');
    }

    public function dependingTask()
    {
        return $this->belongsTo(ProjectTrackingTask::class);
    }

    public function dependenciesType()
    {
        return $this->belongsTo(ProjectTrackingDependenciesType::class, 'dependency_type_id', 'id');
    }

    public function taskType()
    {
        return $this->belongsTo(ProjectTrackingTaskTypes::class, 'task_type_id', 'id');
    }

    public function tags()
    {
        return $this->belongsToMany(ProjectTrackingTags::class, 'project_tracking_tag_task')->withTimestamps();
    }

    public function taskComments()
    {
        return $this->hasMany(ProjectTrackingTaskComment::class, 'task_comment_id', 'id');
    }
}
