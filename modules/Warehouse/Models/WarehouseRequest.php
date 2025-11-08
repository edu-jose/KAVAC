<?php

namespace Modules\Warehouse\Models;

use App\Models\Institution;
use App\Traits\ModelsTrait;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Date;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class WarehouseRequest
 * @brief Datos de las solicitudes de los productos del almacén
 *
 * Gestiona el modelo de datos para las solicitudes de los productos del almacén
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WarehouseRequest extends Model implements Auditable
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
        'code',
        'state',
        'request_date',
        'observations',
        'delivered',
        'delivery_date',
        'motive',
        'budget_specific_action_id',
        'department_id',
        'payroll_staff_id',
        'institution_id',
        'warehouse_id',
    ];

    /**
     * Método que obtiene la institución que realiza la solicitud
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * Método que obtiene el almacén asociado a la solicitud
     *
     * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Método que obtiene el departamento o dependencia que realiza la solicitud
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Department::class);
    }

    /**
     * Método que obtiene la acción Específica
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function budgetSpecificAction()
    {
        return (
            Module::has('Budget') && Module::isEnabled('Budget')
        ) ? $this->belongsTo(\Modules\Budget\Models\BudgetSpecificAction::class) : [];
    }

    /**
     * Método que obtiene el trabajador asociado a la solicitud
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollStaff()
    {
        return (
            Module::has('Payroll') && Module::isEnabled('Payroll')
        ) ? $this->belongsTo(\Modules\Payroll\Models\PayrollStaff::class) : [];
    }

    /**
     * Método que obtiene los productos asociados a la solicitud
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function warehouseInventoryProductRequests(): HasMany
    {
        return $this->hasMany(WarehouseInventoryProductRequest::class);
    }

    /**
     * Obtiene la fecha del registro para usar en el bloqueo del cierre de ejercicio
     *
     * @return Date
     */
    public function getDate()
    {
        return $this->request_date;
    }

    /**
     * Registra la solicitud en la tabla unificada de solicitudes de almacén
     *
     * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
     * @return void
     */
    public function registerUnified(): void
    {
        WarehouseRequestUnified::create([
            'requestable_id' => $this->id,
            'requestable_type' => static::class,
        ]);
    }

    /**
     * Este método filtra las solicitudes de productos del almacén según los parámetros proporcionados.
     *
     * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     * @param mixed $query
     * @param mixed $filterParameters
     * @return void
     */
    public function scopeFilterDataToConsumptionReport($query, $filterParameters)
    {
        $query->where('institution_id', $filterParameters['institution_id'])
            ->where('department_id', $filterParameters['department_id'])
            ->when($filterParameters['warehouse_id'], function ($q) use ($filterParameters) {
                $q->where('warehouse_id', $filterParameters['warehouse_id']);
            })->where('state', 'Aprobado')
            ->orWhere('state', 'Entregado');

        $query->when($filterParameters['type_search'] === 'date', function ($q) use ($filterParameters) {
            if ($filterParameters['start_date']) {
                $q->whereDate('request_date', '>=', $filterParameters['start_date']);
            }
            if ($filterParameters['end_date']) {
                $q->whereDate('request_date', '<=', $filterParameters['end_date']);
            }
        })->when($filterParameters['type_search'] === 'mes', function ($q) use ($filterParameters) {
            if ($filterParameters['mes_id'] == 0) {
                $q->whereMonth('request_date', '>=', 1)
                    ->whereMonth('request_date', '<=', 12);
            }
            if ($filterParameters['mes_id'] > 0) {
                $q->whereMonth('request_date', $filterParameters['mes_id']);
            }
            if ($filterParameters['year']) {
                $q->whereYear('request_date', $filterParameters['year']);
            }
        });

        if (!empty($filterParameters['inventory_product_ids'])) {
            $query->whereHas('warehouseInventoryProductRequests', function ($q) use ($filterParameters) {
                $q->whereHas('warehouseInventoryProduct', function ($qry) use ($filterParameters) {
                    $qry->whereIn('warehouse_product_id', $filterParameters['product_ids'])
                        ->whereHas('warehouseInstitutionWarehouse', function ($qiw) use ($filterParameters) {
                            $qiw->when($filterParameters['warehouse_id'], function ($qiw) use ($filterParameters) {
                                $qiw->where('warehouse_id', $filterParameters['warehouse_id']);
                            });
                        });
                });
            });
        } else {
            $query->whereHas('warehouseInventoryProductRequests', function ($q) use ($filterParameters) {
                $q->whereHas('warehouseInventoryProduct', function ($qip) use ($filterParameters) {
                    $qip->whereHas('warehouseInstitutionWarehouse', function ($qiw) use ($filterParameters) {
                        $qiw->when($filterParameters['warehouse_id'], function ($qiw) use ($filterParameters) {
                            $qiw->where('warehouse_id', $filterParameters['warehouse_id']);
                        });
                    });
                });
            });
        }
    }
}
