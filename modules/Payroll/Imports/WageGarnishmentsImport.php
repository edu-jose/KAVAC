<?php

namespace Modules\Payroll\Imports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Modules\Payroll\Models\PayrollStaff;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Modules\Payroll\Models\PayrollWageGarnishments;

/**
 * @class AriRegisterImport
 * @brief Importa un archivo de registros ARI
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WageGarnishmentsImport implements
    ToModel,
    WithValidation,
    WithHeadingRow,
    SkipsEmptyRows,
    SkipsOnError,
    SkipsOnFailure
{
    use Importable;
    use SkipsErrors;
    use SkipsFailures;

    /**
     * Modelo para importar datos
     *
     * @param array $row Arreglo de columnas a importar
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $payrollStaff = PayrollStaff::query()
            ->where('id_number', $row['cedula'])
            ->toBase()
            ->first();

        if (!$payrollStaff) {
            return null;
        }

        $from_date = isset($row['desde'])
            ? (is_string($row['desde']) ? Carbon::parse($row['desde']) : Carbon::instance(
                \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['desde'])
            ))
            : null;

        $to_date = isset($row['hasta'])
            ? (is_string($row['hasta']) ? Carbon::parse($row['hasta']) : Carbon::instance(
                \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['hasta'])
            ))
            : null;

        $id = null;
        if (!empty($row['codigo']) && preg_match('/-(\d+)$/', $row['codigo'], $matches)) {
            $id = $matches[1];
        }

        $garnishment = $id ? PayrollWageGarnishments::find($id) : null;

        if ($garnishment) {
            $garnishment->from_date = $from_date;
            $garnishment->to_date = $to_date;
            $garnishment->percetage = $row['porcentaje'] / 100;
            $garnishment->save();
            return null;
        }

        $lastRegister = PayrollWageGarnishments::query()
            ->where('payroll_staff_id', $payrollStaff->id)
            ->orderBy('from_date', 'desc')
            ->first();

        if ($lastRegister && $lastRegister->to_date === null && $from_date) {
            $lastRegister->to_date = $from_date->copy()->subDay()->format('Y-m-d');
            $lastRegister->save();
        }

        return new PayrollWageGarnishments([
            'payroll_staff_id' => $payrollStaff->id,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'percetage' => $row['porcentaje'] / 100,
        ]);
    }

    /**
     * Preparar los datos para ser importados (validaciones)
     *
     * @param array $data Arreglo con los datos
     * @param integer $index Indice de la fila
     *
     * @return array
     */
    public function prepareForValidation($data, $index)
    {
        $payrollStaff = PayrollStaff::query()
            ->where('id_number', $data['cedula'])
            ->toBase()
            ->first();

        $from_date = isset($data['desde'])
            ? (is_string($data['desde']) ? Carbon::parse($data['desde']) : Carbon::instance(
                \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($data['desde'])
            ))
            : null;

        $to_date = isset($data['hasta']) && $data['hasta'] !== ''
            ? (is_string($data['hasta']) ? Carbon::parse($data['hasta']) : Carbon::instance(
                \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($data['hasta'])
            ))
            : null;

        $id = null;
        if (!empty($data['codigo']) && preg_match('/-(\d+)$/', $data['codigo'], $matches)) {
            $id = $matches[1];
        }

        $data['unique_from_date'] = false;
        $data['overlap_period'] = false;
        $data['invalid_date_range'] = false;

        if ($payrollStaff && $from_date) {
            $allRegisters = PayrollWageGarnishments::query()
                ->where('payroll_staff_id', $payrollStaff->id)
                ->when($id, fn($q) => $q->where('id', '!=', $id))
                ->whereNull('deleted_at')
                ->orderBy('from_date')
                ->get();

            foreach ($allRegisters as $reg) {
                $regFrom = Carbon::parse($reg->from_date);
                $regTo = $reg->to_date ? Carbon::parse($reg->to_date) : null;

                if ($from_date->equalTo($regFrom) && !$id) {
                    $data['unique_from_date'] = true;
                    break;
                }

                if ($regTo && $from_date->between($regFrom, $regTo)) {
                    $data['overlap_period'] = true;
                    break;
                }

                if (!$regTo && $from_date->lessThanOrEqualTo($regFrom) && $allRegisters->count() > 1) {
                    $data['overlap_period'] = true;
                    break;
                }
            }
        }

        if ($from_date && $to_date && $to_date->lt($from_date)) {
            $data['invalid_date_range'] = true;
        }

        $data['desde'] = $from_date?->format('Y-m-d');
        $data['hasta'] = $to_date?->format('Y-m-d');

        return $data;
    }

    /**
     * Reglas de validación
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'cedula' => ['required'],
            'porcentaje' => ['required'],
            'desde' => ['required'],

            'unique_from_date' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure('La fecha inicial ya ha sido registrada por este empleado.');
                }
            },

            'overlap_period' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure('La fecha de inicio se encuentra dentro del periodo de un registro anterior.');
                }
            },

            'invalid_date_range' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure('La fecha de fin no puede ser anterior a la fecha de inicio.');
                }
            },
        ];
    }
}
