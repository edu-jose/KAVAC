<?php

namespace Modules\Payroll\Models;

use App\Models\DocumentStatus as BaseDocumentStatus;

/**
 * @class DocumentStatus
 * @brief Modelo que extiende las funcionalidades del modelo base DocumentStatus
 *
 * Modelo que extiende las funcionalidades del modelo base DocumentStatus
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class DocumentStatus extends BaseDocumentStatus
{
    /**
     * Establece la relación con formulaciones presupuestarias asociadas a estatus de documentos
     *
     * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }
}
