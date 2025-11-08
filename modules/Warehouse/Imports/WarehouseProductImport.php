<?php

namespace Modules\Warehouse\Imports;

use Modules\Warehouse\Models\WarehouseProduct;
use App\Models\MeasurementUnit;
use App\Notifications\SystemNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\Failure;
use Modules\Warehouse\Exports\FailRegisterImportExport;
use Nwidart\Modules\Facades\Module;

/**
 * @class WarehouseProductImport
 * @brief Gestiona la importación de los datos de los productos
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WarehouseProductImport implements
    ToModel,
    WithChunkReading,
    ShouldQueue,
    WithHeadingRow,
    WithEvents,
    SkipsEmptyRows,
    WithValidation,
    SkipsOnFailure,
    WithMultipleSheets
{
    use SkipsFailures;

    protected array $attributes;
    protected string $errorsFilePath;
    protected object $user;
    protected bool $hasPurchaseModule;
    protected string $filePath;

    /**
     * Constructor de la clase
     * @param object $user Usuario que realiza la importación
     * @param string $errorsFilePath Ruta del archivo donde se almacenan los errores
     * @param array $attributes (opcional) Arreglo con los nombres de los atributos
     */
    public function __construct(
        string $filePath,
        object $user,
        string $errorsFilePath
    ) {
        $this->filePath = $filePath;
        $this->hasPurchaseModule = Module::has('Purchase') && Module::isEnabled('Purchase');
        $this->user = $user;
        $this->errorsFilePath = $errorsFilePath;
        $this->attributes = [
            'catalogo_snc'          =>  'Producto de Catálogo SNC',
            'nombre_del_insumo'     =>  'Nombre del insumo',
            'descripcion_del_insumo' =>  'Descripción del insumo',
            'nombre_de_la_unidad_de_medida' =>  'Nombre de la unidad de medida',
            'purchase_product_missing' => 'Producto de catálogo SNC no existe',
            'supply_name_not_uppercase' => 'Nombre del insumo Mayús',
        ];
    }

    /**
     * Obtiene las hojas a importar
     * @return array Arreglo con las hojas a importar
     */
    public function sheets(): array
    {
        return [
            0 => $this,
        ];
    }

    /**
     * Modelo para importar datos
     * @param array $row Arreglo de columnas a importar
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $measurementUnit = MeasurementUnit::select('id', 'name')
            ->where('name', $row['nombre_de_la_unidad_de_medida'])
            ->toBase()
            ->first();

        $purchaseProduct = null;

        if ($this->hasPurchaseModule) {
            $code = trim(explode('-', $row['catalogo_snc'])[0]);
            $purchaseProduct = \Modules\Purchase\Models\PurchaseProduct::select('id', 'code')
                ->where('code', $code)
                ->toBase()
                ->first();
        }

        $existingProduct = WarehouseProduct::where('name', $row['nombre_del_insumo'])->first();

        if ($existingProduct) {
            $existingProduct->update([
                'description'         => $row['descripcion_del_insumo'],
                'measurement_unit_id' => $measurementUnit?->id,
                'purchase_product_id' => $purchaseProduct?->id,
            ]);

            return null;
        }

        return new WarehouseProduct([
            'name'                => $row['nombre_del_insumo'],
            'description'         => $row['descripcion_del_insumo'],
            'measurement_unit_id' => $measurementUnit?->id,
            'purchase_product_id' => $purchaseProduct?->id,
        ]);
    }

    public function prepareForValidation($data, $index)
    {
        $productName = $data['nombre_del_insumo'] ?? '';
        $data['supply_name_not_uppercase'] = strtoupper($productName) !== $productName;
        $data['catalogo_snc'] = trim($data['catalogo_snc']);

        if ($this->hasPurchaseModule) {
            $code = trim(explode('-', $data['catalogo_snc'])[0]);
            $purchaseProduct = \Modules\Purchase\Models\PurchaseProduct::select('id', 'code')
                ->where('code', $code)
                ->toBase()
                ->first();

            $data['purchase_product_missing'] = $purchaseProduct === null;
        }

        return $data;
    }

    /**
     * Define las reglas de validación para cada fila
     * @return array Arreglo con las reglas de validación
     */
    public function rules(): array
    {
        return [
            'purchase_product_missing' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure('El producto indicado en el catálogo SNC no existe.');
                }
            },
            'supply_name_not_uppercase' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure('El campo "Nombre del insumo" debe estar completamente en mayúsculas.');
                }
            },
            'catalogo_snc' => 'required',
            'nombre_del_insumo' => 'required',
            'descripcion_del_insumo' => 'required',
            'catalogo_snc' => $this->hasPurchaseModule ? ['required'] : ['nullable'],
        ];
    }

    /**
     * Define los mensajes de validación personalizados
     * @return array Arreglo con los mensajes de validación personalizados
     */
    public function customValidationMessages(): array
    {
        return [
            'catalogo_snc.required' => 'El catálogo SNC es obligatorio.',
            'nombre_del_insumo.required' => 'El nombre del insumo es obligatorio.',
            'descripcion_del_insumo.required' => 'La descripción del insumo es obligatoria.',
            'nombre_de_la_unidad_de_medida.required' => 'El nombre de la unidad de medida es obligatorio.',
        ];
    }

    /**
     * Maneja los errores de validación durante la importación
     *
     * @param Failure ...$failures Errores de validación
     *
     * @return void
     */
    public function onFailure(Failure ...$failures): void
    {
        $failures = collect($failures);

        foreach ($failures as $failure) {
            $validationErrors = [
                'row'       => $failure->row(),
                'attribute' => str_replace('_value', '', $this->attributes[$failure->attribute()] ?? $failure->attribute()),
                'error'     => $failure->errors()[0],
                'sheetName' => 'Productos de Almacén',
            ];

            $jsonErrors = json_encode($validationErrors, JSON_UNESCAPED_UNICODE);

            \Illuminate\Support\Facades\Storage::disk('temporary')->append($this->errorsFilePath, $jsonErrors . PHP_EOL);
        }
    }

    /**
     * Define el tamaño del chunk para la lectura de datos
     * @return int Tamaño del chunk
     */
    public function chunkSize(): int
    {
        return 200;
    }

    /**
     * Define los eventos de importación
     * @return array Arreglo con los eventos de importación
     */
    public function registerEvents(): array
    {
        return [
            AfterImport::class => function (AfterImport $event) {
                Storage::delete($this->filePath);
                $errorsFile = Storage::disk('temporary')->get($this->errorsFilePath);
                $lines = explode("\n", $errorsFile);
                $errors = [];

                foreach ($lines as $line) {
                    if (!empty($line)) {
                        array_push($errors, json_decode($line, true));
                    }
                }

                $user = $this->user;

                if (count($errors) > 0) {
                    $fileName = 'Errores_de_importacion_' . uniqid() . '.xlsx';

                    // Guarda el archivo de errores en el disco 'documents'
                    Excel::store(
                        new FailRegisterImportExport($errors),
                        $fileName,
                        'documents',
                        \Maatwebsite\Excel\Excel::XLSX
                    );

                    // Envía notificación con enlace de descarga
                    $user->notify(new SystemNotification(
                        'Fallos de Importación',
                        "Algunos registros fallaron durante la importación. Puedes descargar el archivo con los errores desde este <a href='" . env('APP_URL') . "/storage/documents/{$fileName}' style='color: #0073b7;' download>enlace</a>."
                    ));
                } else {
                    $user->notify(new SystemNotification(
                        'Éxito - Importación de Productos',
                        'La importación se ha completado exitosamente. Todos los registros fueron procesados sin errores.'
                    ));
                }

                // Elimina el archivo de errores
                Storage::disk('temporary')->delete($this->errorsFilePath);
            }
        ];
    }
}
