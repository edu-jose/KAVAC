<?php

namespace Modules\Payroll\Models;

use App\Models\Locality as BaseLocality;

/**
 * @class      Locality
 * @brief      Modelo que extiende las funcionalidades del modelo base Locality
 *
 * Modelo que extiende las funcionalidades del modelo base Locality
 *
 * @author     Pedro Contreras <pmcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Locality extends BaseLocality
{
    /**
     * Método que obtiene la localidad asociada a muchas informaciones personales del trabajador
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
