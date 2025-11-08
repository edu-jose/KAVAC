<?php

namespace Modules\Sale\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class SaleCustomerManagement
 * @brief Modelo para la gestión de clientes
 *
 * Modelo para gestionar la información de los clientes del módulo de ventas
 *
 * @author [Tu nombre] <[tu correo]>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleCustomerManagement extends Model implements Auditable
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
        'identifier_type',
        'identification_number',
        'name',
        'fiscal_address',
    ];

    /**
     * Obtiene los números telefónicos asociados al cliente
     *
     * @return HasMany
     */
    public function phones()
    {
        return $this->hasMany(SaleCustomerPhone::class, 'sale_customer_id');
    }

    /**
     * Scope para filtrar clientes activos
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Mutator para formatear el número de identificación con el tipo de identificador
     *
     * @return string
     */
    public function getFullIdentificationAttribute()
    {
        return $this->identifier_type . '-' . $this->identification_number;
    }

    /**
     * Mutator para obtener el nombre en mayúsculas
     *
     * @return string
     */
    public function getNameUpperCaseAttribute()
    {
        return strtoupper($this->name);
    }
}
