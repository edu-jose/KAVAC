<?php

namespace Modules\Budget\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Budget\Models\BudgetBudgetaryAvailability;
use Modules\Budget\Models\BudgetCommonBudgetaryAvailability;
use Nwidart\Modules\Facades\Module;
use Modules\Purchase\Models\PurchaseBaseBudget;
use Throwable;

/**
 * @class UpdateStatusBudgetaryAvailability
 * @brief Comando para actualizar los estatus de las disponibilidades presupuestarias en los presupuestos base y las nóminas.
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateDataBudgetaryAvailability extends Command
{
    /**
     * El nombre del comando.
     *
     * @var string $signature
     */
    protected $signature = 'module:update-data-budgetary-availability';

    /**
     * La descripción del comando.
     *
     * @var string $description
     */
    protected $description = 'Actualizar los códigos de las disponibilidades presupuestarias en los presupuestos base y las nóminas.';

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
     * @return integer|void
     */
    public function handle()
    {
        $this->info("Inicio de actualización de los códigos de disponibilidad presupuestaria...\n");

        try {
            $purchaseRecords = PurchaseBaseBudget::query()
                ->where('send_notify', true)
                ->with('budgetCommonBudgetaryAvailability')
                ->get()
                ->map(fn ($baseBudget) => [
                    'id' => $baseBudget->id,
                    'model' => PurchaseBaseBudget::class,
                    'date' => $baseBudget->date,
                ]);

            if (Module::has('Payroll') && Module::isEnabled('Payroll') && Module::has('Budget') && Module::isEnabled('Budget')) {
                $payrollRecords = \Modules\Payroll\Models\Payroll::query()
                    ->with('budgetCommonBudgetaryAvailability')
                    ->whereHas('payrollPaymentPeriod', function ($query) {
                        $query->where('availability_status', 'send')
                            ->orWhere('availability_status', 'available')
                            ->orWhere('availability_status', 'not_available')
                            ->orWhere('availability_status', 'AP')
                            ->orWhere('availability_status', 'AN');
                    })
                    ->get()
                    ->map(fn ($payroll) => [
                        'id' => $payroll->id,
                        'model' => \Modules\Payroll\Models\Payroll::class,
                        'date' => $payroll->created_at,
                    ]);
            }
            $mergedRecords = $purchaseRecords
                ->when(isset($payrollRecords), fn ($query) => $query->merge($payrollRecords))
                ->sortBy('date')
                ->values();

            if (Module::has('Budget') && Module::isEnabled('Budget')) {
                $codeSetting = \Modules\Budget\Models\CodeSetting::query()
                    ->where('table', 'budget_budgetary_availabilities')->first();
                if (!$codeSetting) {
                    throw new Exception("Error. Debe configurar previamente el formato para el código de disponibilidad presupuestaria a generar", 1);
                }
            }

            foreach ($mergedRecords as $record) {
                list($year, $month, $day) = explode("-", $record['date']);

                $code = generate_budget_availability_code(
                    $codeSetting->format_prefix,
                    strlen($codeSetting->format_digits),
                    (empty($codeSetting->format_year)) ? '' : ((strlen($codeSetting->format_year) == 2) ? substr($year, 2, 2) : $year),
                    BudgetCommonBudgetaryAvailability::class,
                    'code'
                );

                BudgetCommonBudgetaryAvailability::firstOrCreate([
                    'budgetable_id' => $record['id'],
                    'budgetable_type' => $record['model'],
                ], [
                    'code' => $code,
                ]);
            }

            foreach (
                BudgetBudgetaryAvailability::query()
                    ->whereNull('budget_common_budgetary_availability_id')
                    ->with('purchaseBaseBudget.budgetCommonBudgetaryAvailability')->get() as $record
            ) {
                $record->update([
                    'budget_common_budgetary_availability_id' => $record->purchaseBaseBudget?->budgetCommonBudgetaryAvailability?->id
                ]);
            }
        } catch (Throwable $th) {
            Log::error($th->getMessage());
            $this->info($th->getMessage());
        }
    }
}
