<?php

namespace Modules\Budget\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class BudgetComponentManagerHistory
 *
 * @brief Modelo que maneja la tabla del historial de los responasbles de los
 * Proyectos y Acciones centralizadas cuando estos son guardados o actualizados.
 *
 * Gestiona el modelo de datos de la tabla budget_component_manager_histories.
 *
 * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetComponentManagerHistory extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Nombre de la tabla en la base de datos
     *
     * @var string $table
     */
    protected $table = 'budget_component_manager_histories';

    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = [
        // Relaciones con Empleados.
        'managerable_type',
        'managerable_id',
        // Relaciones con Proyectos y AC.
        'componentable_type',
        'componentable_id',
        'created_at',
        'updated_at'
    ];

    /**
     * Establece la relación con el responsable
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function managerable()
    {
        return $this->morphTo();
    }

    /**
     * Establece la relación con el componente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function componentable()
    {
        return $this->morphTo();
    }
}
