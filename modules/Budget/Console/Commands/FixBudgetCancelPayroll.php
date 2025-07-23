<?php

namespace Modules\Budget\Console\Commands;

use App\Models\DocumentStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Budget\Models\BudgetCompromise;

/**
 * @class FixBudgetCancelPayroll
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FixBudgetCancelPayroll extends Command
{
    /**
     * El nombre del comando.
     *
     * @var string
     */
    protected $signature = 'module:budget-fix-budget-compromise-details-cancel-payroll {budgetCompromiseId}';

    /**
     * La descripción del comando.
     *
     * @var string
     */
    protected $description = 'Corrects the budget compromise details for canceled payrolls. budgetCompromiseId is required.';

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
        $this->info('Fixing budget compromise details for canceled payrolls...');

        try {
            DB::beginTransaction();
            $budgetCompromiseId = $this->argument('budgetCompromiseId');
            if (!$budgetCompromiseId) {
                throw new \Exception('No budget compromise ID provided.');
            }
            $this->info("Processing budget compromise ID: {$budgetCompromiseId}");
            $documentStatus = DocumentStatus::getStatus('AN');
            $budgetCompromise = BudgetCompromise::query()
                ->where([
                    'document_status_id' => $documentStatus->id,
                ])
                ->findOrFail($budgetCompromiseId);

            $budgetCompromiseDetails = $budgetCompromise->budgetCompromiseDetails()->where('document_status_id', null)->get();

            if (!$budgetCompromiseDetails->isEmpty()) {
                $budgetCompromise->budgetCompromiseDetails->each(function ($detail) use ($documentStatus) {
                    $detail->update([
                        'document_status_id' => $documentStatus->id,
                    ]);
                    $this->info("Updated compromise detail ID: {$detail->id} and amount: {$detail->amount} with status: {$documentStatus->name}");
                });
                $this->info('Budget compromise details updated successfully.');
            } else {
                $this->info('No budget compromise details to update.');
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->error($th->getMessage());
        }
    }
}
