<?php

declare(strict_types=1);

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @class      PayrollWorkload
 * @brief      Datos de las cargas horarias
 *
 * Gestiona el modelo de datos de las cargas horarias
 *
 * @author     Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollWorkload extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    use ModelsTrait {
        ModelsTrait::boot as bootModelTrait;
    }

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
        'hours', 'description'
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // Inicia los eventos del ModelTrait
        static::bootModelTrait();

        static::restored(function (PayrollWorkload $payrollWorkload) {
            // Restaura los cargos asociados a la carga horaria
            $payrollWorkload->payrollWorkloadPositions()->onlyTrashed()->restore();
        });
    }

    /**
     * Método que obtiene los trabajadores asociados a la carga horaria
     *
     * @method  payrollWorkloadPositions
     *
     * @return  \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollWorkloadPositions(): HasMany
    {
        return $this->hasMany(PayrollWorkloadPosition::class);
    }
}
