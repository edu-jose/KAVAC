<?php

namespace Modules\Warehouse\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Modules\Warehouse\Models\WarehouseProduct;
use App\Models\Currency;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/**
 * @class WarehouseReceptionImport
 * @brief Gestiona la importación de los datos de los productos para carga en masa
 *
 * @author Miguel Narvaez <mnarvaez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WarehouseReceptionImport implements ToCollection, WithHeadingRow, SkipsOnError, SkipsOnFailure
{
    use SkipsErrors;
    use SkipsFailures;

    protected $institutionId;
    protected $warehouseId;
    protected $directHire;
    protected $supplier;
    protected $receptionDate;
    protected $generalObservations;
    protected $errors = [];
    protected $validRecords = [];

    public function __construct($institutionId, $warehouseId, $directHire, $receptionDate, $generalObservations = null, $supplier = null)
    {
        $this->institutionId = $institutionId;
        $this->warehouseId = $warehouseId;
        $this->directHire = $directHire;
        $this->supplier = $supplier;
        $this->receptionDate = $receptionDate;
        $this->generalObservations = $generalObservations;
    }

    public function collection(Collection $rows)
    {
        $rowNumber = 2; // Comenzamos en 2 porque la fila 1 es el encabezado

        foreach ($rows as $row) {
            // Saltar filas completamente vacías
            if ($this->isRowEmpty($row)) {
                $rowNumber++;
                continue;
            }

            $this->validateRow($row, $rowNumber);
            $rowNumber++;
        }
    }

    protected function isRowEmpty($row)
    {
        return empty($row['nombre_del_insumo']) &&
               empty($row['cantidad']) &&
               empty($row['valor_unitario']) &&
               empty($row['moneda']) &&
               empty($row['lote']) &&
               empty($row['fecha_de_vencimiento']);
    }

    public function rules(): array
    {
        return [
            '*.nombre_del_insumo' => 'required|exists:warehouse_products,name',
            '*.cantidad' => 'required|numeric|min:0.01',
            '*.valor' => 'required|numeric|min:0',
            '*.moneda' => 'required|exists:currencies,name',
            '*.lote' => 'required|string|max:20',
            '*.fecha_de_vencimiento' => 'required|date',
            '*.minimo' => 'nullable|numeric|min:0',
            '*.maximo' => 'nullable|numeric|min:0|gt:*.minimo',
        ];
    }

    public function customValidationMessages()
    {
        return [
            '*.nombre_del_insumo.required' => 'El campo nombre del insumo es obligatorio',
            '*.nombre_del_insumo.exists' => 'El insumo no existe en el sistema',
            '*.cantidad.required' => 'El campo cantidad es obligatorio',
            '*.cantidad.numeric' => 'El campo cantidad debe ser un número',
            '*.cantidad.min' => 'El campo cantidad debe ser mayor que cero',
            '*.valor.required' => 'El campo valor es obligatorio',
            '*.valor.numeric' => 'El campo valor debe ser un número',
            '*.valor.min' => 'El campo valor debe ser mayor o igual a cero',
            '*.moneda.required' => 'El campo moneda es obligatorio',
            '*.moneda.exists' => 'La moneda no existe en el sistema',
            '*.lote.required' => 'El campo lote es obligatorio',
            '*.lote.string' => 'El campo lote debe ser texto',
            '*.lote.max' => 'El campo lote no debe exceder los 20 caracteres',
            '*.fecha_de_vencimiento.required' => 'El campo fecha de vencimiento es obligatorio',
            '*.fecha_de_vencimiento.date' => 'El campo fecha de vencimiento debe ser una fecha válida',
            '*.minimo.numeric' => 'El campo mínimo debe ser un número',
            '*.minimo.min' => 'El campo mínimo debe ser mayor o igual a cero',
            '*.maximo.numeric' => 'El campo máximo debe ser un número',
            '*.maximo.min' => 'El campo máximo debe ser mayor o igual a cero',
            '*.maximo.gt' => 'El campo máximo debe ser mayor que el mínimo'
        ];
    }

    public function onError(\Throwable $e)
    {
        $this->errors[] = 'Error al procesar el archivo: ' . $e->getMessage();
    }

    protected function validateRow($row, $rowNumber)
    {
        // Convertir fecha de Excel si es necesario
        $expirationDate = $row['fecha_de_vencimiento'];
        if (is_numeric($expirationDate)) {
            try {
                try {
                    if (is_numeric($expirationDate)) {
                        $date = Date::excelToDateTimeObject($expirationDate);
                        $expirationDate = $date->format('d/m/Y');
                    } elseif (is_string($expirationDate)) {
                        // Intenta parsear la fecha si ya está en formato string
                        $expirationDate = Carbon::createFromFormat('d/m/Y', $expirationDate)->format('d/m/Y');
                    }
                } catch (\Exception $e) {
                    $expirationDate = null;
                }
            } catch (\Exception $e) {
                $expirationDate = null;
            }
        }

        // Convertir lote a string si es numérico
        $lote = is_numeric($row['lote']) ? (string)$row['lote'] : $row['lote'];

        $validator = Validator::make(array_merge($row->toArray(), [
            'fecha_de_vencimiento' => $expirationDate,
            'lote' => $lote
        ]), [
            'nombre_del_insumo' => 'required|exists:warehouse_products,name',
            'cantidad' => 'required|numeric|min:0.01',
            'valor_unitario' => 'required|numeric|min:0',
            'moneda' => 'required|exists:currencies,name',
            'lote' => 'required|string|min:1',
            'fecha_de_vencimiento' => 'required|date_format:d/m/Y',
            'minimo' => 'nullable|numeric|min:0',
            'maximo' => 'nullable|numeric|min:0|gte:minimo'
        ], [
            'nombre_del_insumo.exists' => 'El nombre del insumo no existe en el sistema',
            'fecha_de_vencimiento.date_format' => 'El formato de fecha debe ser dd/mm/yyyy'
        ]);

        if ($validator->fails()) {
            $this->errors[] = [
                'row' => $row->toArray(),
                'errors' => $validator->errors()->all(),
                'row_number' => $rowNumber // Añadimos el número de fila al error
            ];
            return;
        }

        // Obtener el producto y la moneda
        $product = WarehouseProduct::where('name', $row['nombre_del_insumo'])->first();
        $currency = Currency::where('name', $row['moneda'])->first();

        // Formatear el registro para el frontend
        $this->validRecords[] = [
            'quantity' => $row['cantidad'],
            'unit_value' => $row['valor_unitario'],
            'currency_id' => $currency->id,
            'currency' => ['name' => $currency->name],
            'warehouse_product_id' => $product->id,
            'warehouse_product' => ['name' => $product->name],
            'batch_number' => $lote,
            'expiration_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $expirationDate)->format('Y-m-d'),
            'minimum' => $row['minimo'] ?? null,
            'maximum' => $row['maximo'] ?? null,
            'warehouse_product_attributes' => []
        ];
    }

    public function hasErrors()
    {
        return !empty($this->errors) || (empty($this->validRecords) && empty($this->errors));
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getValidRecords()
    {
        return $this->validRecords;
    }
}
