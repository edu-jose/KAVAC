<?php

namespace Modules\CitizenService\Models;

use App\Models\Parish as BaseParish;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @class Parish
 * @brief Extiende del modelo Parish de la aplicación base
 *
 * @author Yennifer Ramirez <yramirezs@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Parish extends BaseParish
{
    /**
     * Método que obtiene la parroquia asociado con solicitudes
     *
     * @author Yennifer Ramirez <yramirezs@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function citizenServiceRequests()
    {
        return $this->hasMany(CitizenServiceRequest::class);
    }

    /**
     * Get all of the citizenServiceCommunities for the Parish
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function citizenServiceCommunities(): HasMany
    {
        return $this->hasMany(CitizenServiceCommunity::class);
    }
}
