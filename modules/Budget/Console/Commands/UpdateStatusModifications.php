<?php

namespace Modules\Budget\Console\Commands;

use App\Models\DocumentStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Budget\Models\BudgetModification;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @class UpdateStatusModifications
 * @brief Comando para actualizar el estatus de las modificaciones presupuestarias
 *
 * Comando para actualizar el estatus de las modificaciones presupuestarias
 *
 * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateStatusModifications extends Command
{
    /**
     * El nombre del comando.
     *
     * @var string
     */
    protected $signature = 'module:budget-update-status-modifications';

    /**
     * La descripción del comando.
     *
     * @var string
     */
    protected $description = 'Comando para actualizar el estatus de las modificaciones presupuestarias.';

    /**
     * Crea una nueva instancia del comando.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Ejecuta la consola de comandos.
     *
     * @return mixed [descripción sobre los datos devueltos por el método]
     */
    public function handle()
    {
        try {
            $this->info('Se actualizarán los estatus y las fechas de aprobación de las modificaciones presupuestarias.');
            $modifications = BudgetModification::all();
            $documentStatus = DocumentStatus::getStatus('AP');
            $is_modification = false;

            DB::beginTransaction();

            foreach ($modifications as $modification) {
                if ($modification->status === 'PE') {
                    $is_modification = true;
                    $modification->document_status_id = $documentStatus->id;
                    $modification->status = 'AP';
                    $modification->approved_date = $modification->approved_at;
                    $modification->save();
                    $this->info('Se actualizó el estatus de la modificación presupuestaria: ' . $modification->code);
                } elseif ($modification->status == 'AP' && is_null($modification->approved_date)) {
                    $is_modification = true;
                    $modification->approved_date = $modification->approved_at;
                    $modification->save();
                    $this->info('Se actualizó la fecha de aprobación de la modificación presupuestaria: ' . $modification->code);
                }
            }

            $message = $is_modification ?
            'Se actualizaron los estatus y las fechas de aprobación de las modificaciones presupuestarias.'
            : 'No se encontraron modificaciones presupuestarias pendientes de aprobación.';

            DB::commit();
            $this->info($message);
        } catch (\Throwable $tr) {
            DB::rollBack();
            $this->error($tr->getMessage());
        }
    }

    /**
     * Obtiene los argumentos del comando.
     *
     * @return array [descripción sobre los datos devueltos por el método]
     */
    protected function getArguments()
    {
        return [
            ['example', InputArgument::REQUIRED, 'An example argument.'],
        ];
    }

    /**
     * Obtiene las opciones del comando.
     *
     * @return array [descripción sobre los datos devueltos por el método]
     */
    protected function getOptions()
    {
        return [
            ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
        ];
    }
}
