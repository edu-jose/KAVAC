<?php

namespace Modules\Payroll\Console\Commands;

use Illuminate\Console\Command;
use Modules\Payroll\Models\PayrollConcept;

/**
 * @class PayrollUpdateConceptFormulaShow
 * @brief Gestiona las instrucciones necesarias para actualizar los conceptos
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollUpdateConceptFormulaShow extends Command
{
    /**
     * El nombre y firma del comando, así como las opciones y argumentos que recibe
     *
     * @var string $signature
     */
    protected $signature = 'module:payroll-concept-formula-show';

    /**
     * Descripción del comando.
     *
     * @var string $description
     */
    protected $description = 'update payroll concepts';

    /**
     * Crea una nueva instancia al comando.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Ejecuta el comando de la consola.
     *
     * @return void
     */
    public function handle()
    {
        $concepts = PayrollConcept::all();

        foreach ($concepts as $concept) {
            $concept->formula_show_history = [$concept->translate_formula];
            $concept->save();
        }
    }
}
