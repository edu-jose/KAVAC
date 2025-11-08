<?php

namespace Modules\Payroll\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * @class PayrollConceptPolicy
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollConceptPolicy
{
    use HandlesAuthorization;

    public function averageView(User $user): bool
    {
        /* @todo: Aqui deberian evaluarse los permisos, para extraer del controlador */
        return config('payroll.features.concept-averages');
    }
}
