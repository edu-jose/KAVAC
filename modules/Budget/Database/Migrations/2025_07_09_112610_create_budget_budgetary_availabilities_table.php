<?php

use App\Models\CodeSetting;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Budget\Models\BudgetBudgetaryAvailability;
use Modules\Budget\Models\BudgetCommonBudgetaryAvailability;

/**
 * @class CreateBudgetBudgetaryAvailabilitiesTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateBudgetBudgetaryAvailabilitiesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('budget_common_budgetary_availabilities')) {
            Schema::create('budget_common_budgetary_availabilities', function (Blueprint $table) {
                $table->id();
                $table->string('code');
                $table->morphs('budgetable');
                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
            });
        }

        // ------------------------

        if (!Schema::hasTable('budget_budgetary_availabilities')) {
            Schema::create('budget_budgetary_availabilities', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->text('item_code')->nullable()->comment('Codigo de Partida');
                $table->text('item_name')->nullable()->comment('Nombre de Partida');
                $table->text('amount')->nullable()->comment('Monto de Partida');
                $table->text('description')->nullable()
                    ->comment('Descripción o comentario');
                $table->string('availability')->nullable()->comment('Disponibilidad');
    
                /*
                 | -----------------------------------------------------------------------
                 | Clave foránea a la relación del requerimiento
                 | -----------------------------------------------------------------------
                 |
                 | Define la estructura de relación al requerimiento
                 */
                $table->foreignId('purchase_base_budget_id')
                    ->constrained("purchase_base_budgets")
                    ->comment('Identificador del presupuesto base')
                    ->onDelete('restrict')
                    ->onUpdate('cascade');
    
                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
    
                $table->date('date')->nullable()->comment('Fecha de la disponibilidad presupuestaria');
                $table->text('spac_description')->nullable()->comment('Descripción de la accioń específica');
                $table->foreignId('budget_account_id')->nullable()->constrained()->onDelete('restrict')->onUpdate('cascade');
                $table->foreignId('budget_specific_action_id')->nullable()->constrained()->onDelete('restrict')->onUpdate('cascade');
    
                $table->string('code', 20)
                        ->nullable()
                        ->unique()
                        ->comment('Código para la disponibilidad presupuestaria manual');
    
                $table->foreignId('budget_common_budgetary_availability_id')
                    ->nullable()->constrained()
                    ->onDelete('restrict')
                    ->onUpdate('cascade');
            });
        }

        if (Module::has('Purchase') && Module::isEnabled('Purchase')) {
            foreach (\Modules\Budget\Models\BudgetCommonBudgetaryAvailability::withTrashed()->get() as $commonBudgetaryAvailability) {
                BudgetCommonBudgetaryAvailability::create($commonBudgetaryAvailability->toArray());
            }

            foreach (\Modules\Budget\Models\BudgetBudgetaryAvailability::withTrashed()->get() as $commonBudgetaryAvailability) {
                $data = $commonBudgetaryAvailability->toArray();
                $data['budget_common_budgetary_availability_id'] = $commonBudgetaryAvailability->purchase_common_budgetary_availability_id;
                $data['purchase_base_budget_id'] = $commonBudgetaryAvailability->purchase_base_budgets_id;
                BudgetBudgetaryAvailability::create($data);
            }

            if (Schema::hasTable('purchase_budgetary_availabilities')) {
                Schema::table('purchase_budgetary_availabilities', function (Blueprint $table) {
                    $table->dropForeign(['purchase_common_budgetary_availability_id']);
                    $table->dropColumn('purchase_common_budgetary_availability_id');
                });
            }

            Schema::dropIfExists('purchase_budgetary_availabilities');
            Schema::dropIfExists('purchase_common_budgetary_availabilities');

            CodeSetting::where('table', 'purchase_budgetary_availabilities')
            ->update([
                'table' => 'budget_budgetary_availabilities',
                'model' => BudgetBudgetaryAvailability::class,
                'module' => 'budget',
            ]);
        }
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('purchase_common_budgetary_availabilities')) {
            Schema::create('purchase_common_budgetary_availabilities', function (Blueprint $table) {
                $table->id();
                $table->string('code');
                $table->morphs('budgetable');
                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
            });
        }

        if (!Schema::hasTable('purchase_budgetary_availabilities')) {
            Schema::create('purchase_budgetary_availabilities', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->text('item_code')->nullable()->comment('Codigo de Partida');
                $table->text('item_name')->nullable()->comment('Nombre de Partida');
                $table->text('amount')->nullable()->comment('Monto de Partida');
                $table->text('description')->nullable()
                    ->comment('Descripción o comentario');
                $table->string('availability')->nullable()->comment('Disponibilidad');

                /*
                 | -----------------------------------------------------------------------
                 | Clave foránea a la relación del requerimiento
                 | -----------------------------------------------------------------------
                 |
                 | Define la estructura de relación al requerimiento
                 */
                $table->foreignId('purchase_base_budgets_id')
                    ->constrained("purchase_base_budgets")
                    ->comment('Identificador del presupuesto base')
                    ->onDelete('restrict')
                    ->onUpdate('cascade');
    
                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
    
                $table->date('date')->nullable()->comment('Fecha de la disponibilidad presupuestaria');
                $table->text('spac_description')->nullable()->comment('Descripción de la accioń específica');
                $table->foreignId('budget_account_id')->nullable()->constrained()->onDelete('restrict')->onUpdate('cascade');
                $table->foreignId('budget_specific_action_id')->nullable()->constrained()->onDelete('restrict')->onUpdate('cascade');
    
                $table->string('code', 20)
                        ->nullable()
                        ->unique()
                        ->comment('Código para la disponibilidad presupuestaria manual');
    
                $table->foreignId('purchase_common_budgetary_availability_id')
                    ->nullable()->constrained()
                    ->onDelete('restrict')
                    ->onUpdate('cascade');
            });
        }

        // -----------------

        if (Module::has('Purchase') && Module::isEnabled('Purchase')) {
            foreach (BudgetCommonBudgetaryAvailability::withTrashed()->get() as $commonBudgetaryAvailability) {
                \Modules\Purchase\Models\PurchaseCommonBudgetaryAvailability::create($commonBudgetaryAvailability->toArray());
            }

            foreach (BudgetBudgetaryAvailability::withTrashed()->get() as $commonBudgetaryAvailability) {
                $data = $commonBudgetaryAvailability->toArray();
                $data['purchase_common_budgetary_availability_id'] = $commonBudgetaryAvailability->budget_common_budgetary_availability_id;
                $data['purchase_base_budgets_id'] = $commonBudgetaryAvailability->purchase_base_budget_id;
                \Modules\Purchase\Models\PurchaseBudgetaryAvailability::create($data);
            }

            if (Schema::hasTable('budget_budgetary_availabilities')) {
                Schema::table('budget_budgetary_availabilities', function (Blueprint $table) {
                    $table->dropForeign(['budget_common_budgetary_availability_id']);
                    $table->dropColumn('budget_common_budgetary_availability_id');
                });
            }

            Schema::dropIfExists('budget_budgetary_availabilities');
            Schema::dropIfExists('budget_common_budgetary_availabilities');

            CodeSetting::where('table', 'budget_budgetary_availabilities')
            ->update([
                'table' => 'purchase_budgetary_availabilities',
                'model' => \Modules\Purchase\Models\PurchaseBudgetaryAvailability::class,
                'module' => 'purchase',
            ]);
        }
    }
}
