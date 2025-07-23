<?php

namespace Modules\Payroll\Models;

use App\Models\Region as BaseRegion;

/**
 * @class      Region
 * @brief      Modelo que extiende las funcionalidades del modelo base Region
 *
 * Modelo que extiende las funcionalidades del modelo base Region
 *
 * @author     Pedro Contreras <pmcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Region extends BaseRegion
{
    /**
     * Método que obtiene la region asociada a muchas informaciones personales del trabajador
     *
     * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollStaffs()
    {
        return $this->hasMany(PayrollStaff::class);
    }
}
