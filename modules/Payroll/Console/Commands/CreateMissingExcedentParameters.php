<?php

namespace Modules\Payroll\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Modules\Payroll\Models\Parameter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @class CreateMissingExcedentParameters
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateMissingExcedentParameters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:parameters_create_missing_excedents';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea los parámetros de excedente que faltan en la base de datos.';

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
        $this->info('Starting the creation of missing excedent parameters...');

        DB::transaction(function () {
            $parameters = Parameter::where([
                'required_by' => 'payroll',
                'active'      => true,
            ])
                ->where('p_key', 'like', 'global_parameter_%')
                ->where('p_value', 'like', '%time_parameter%')
                ->withTrashed()
                ->get()
                ->filter(function ($parameter) {
                    $pValue = json_decode($parameter->p_value);
                    return !isset($pValue->is_excedent);
                });

            $this->info('Checking ' . $parameters->count() . ' global parameters...');

            foreach ($parameters as $parameter) {
                $paramData = json_decode($parameter->p_value, true);
                $baseParameterName = $paramData['name'];

                $this->info('Checking parameter: ' . $baseParameterName);

                // Check if an excedent parameter already exists
                $existingExcedent = Parameter::where([
                    'required_by' => 'payroll',
                ])
                    ->where('p_key', 'like', 'global_parameter_%')
                    ->whereJsonContains('p_value', ['name' => "EX-" . $baseParameterName])
                    ->withTrashed()
                    ->first();

                if (!$existingExcedent) {
                    $this->info('Excedent parameter missing for ' . $baseParameterName . '. Creating...');

                    // Determine the next available ID
                    $lastParameter = Parameter::where([
                        'required_by' => 'payroll',
                    ])
                        ->where('p_key', 'like', 'global_parameter_%')
                        ->withTrashed()
                        ->orderByRaw("substring(p_key from '\d+$')::integer DESC")
                        ->first();

                    $nextId = $lastParameter ? json_decode($lastParameter->p_value, true)['id'] + 1 : 1;

                    // Create the excedent parameter data
                    $excedentData = [
                        "id"             => $nextId,
                        'name'           => "EX-" . $paramData['name'],
                        'code'           => "EX-" . ($paramData['code'] ?? ''),
                        'acronym'        => "EX-" . ($paramData['acronym'] ?? ''),
                        'exception_type' => $paramData['exception_type'] ?? '',
                        'active'         => false,
                        'value_max'      => $paramData['value_max'] ?? '',
                        'max_value_allowed_per_time_sheet' => $paramData['max_value_allowed_per_time_sheet'] ?? '',
                        'list_in_schema' => $paramData['list_in_schema'] ?? false,
                        'description'    => $paramData['description'] ?? '',
                        'parameter_type' => $paramData['parameter_type'],
                        'percentage'     => $paramData['percentage'] ?? false,
                        'value'          => $paramData['value'] ?? '',
                        'formula'        => "parameter(" . $paramData['id'] . ')-' . ($paramData['max_value_allowed_per_time_sheet'] ?? 0),
                        'classification_type'        => $paramData['classification_type'] ?? '',
                        'is_excedent' => true,
                    ];

                    // Create the excedent parameter in the database
                    Parameter::create([
                        'p_key'       => 'global_parameter_' . $nextId,
                        'required_by' => 'payroll',
                        'active'      => true,
                        'p_value'     => json_encode($excedentData)
                    ]);

                    $this->info('Excedent parameter created successfully for ' . $baseParameterName);
                } else {
                    $this->info('Excedent parameter already exists for ' . $baseParameterName . '. Skipping.');
                }
            }
        });

        $this->info('Command finished.');

        return 0;
    }
}
