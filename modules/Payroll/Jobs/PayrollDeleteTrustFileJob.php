<?php

namespace Modules\Payroll\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;

/**
 * @class PayrollDeleteTrustFileJob
 * @brief Trabajo para eliminar el archivo procesado de hoja de cálculo para generar txt de BNC
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollDeleteTrustFileJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Crea una nueva instancia de trabajo.
     *
     * @return void
     */
    public function __construct(
        protected string $filePath = '',
        protected string $copyFilePath = '',
    ) {
        //
    }

    /**
     * Ejecuta el trabajo.
     *
     * @return void
     */
    public function handle()
    {
        Storage::disk('documents')->delete($this->filePath);
        Storage::disk('documents')->delete($this->copyFilePath);
    }
}
