<?php

namespace Modules\Payroll\Exports;

use DateTime;
use App\Models\Parameter;
use Modules\Payroll\Models\Payroll;
use Nwidart\Modules\Facades\Module;
use Modules\Payroll\Models\Institution;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Modules\Payroll\Models\PayrollConceptType;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

/**
 * @class PayrollTextFileExport
 * @brief Clase que exporta el listado de nómina en formato TXT
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollTextFileExport implements FromArray, ShouldAutoSize, WithCustomCsvSettings
{
    use Exportable;

    /**
     * Identificador de la nómina
     *
     * @var integer $payrollId
     */
    protected $payrollId;

    /**
     * Modelo de la nómina
     *
     * @var array $model
     */
    protected $model = [];

    /**
     * total de la nómina
     *
     * @var float|array $total
     */
    protected $total;

    /**
     * Número de decimales a mostrar
     *
     * @var Parameter $number_decimals
     */
    protected $number_decimals;

    /**
     * Función de redondeo a usar
     *
     * @var Parameter $round
     */
    protected $round;

    /**
     * Configuración de conceptos en cero
     *
     * @var Parameter $zero_concept
     */
    protected $zero_concept;

    /**
     * Datos de la cuenta bancaria
     *
     * @var string $bank_account
     */
    protected $bank_account;

    /**
     * Número de archivo
     *
     * @var string $file_number
     */
    protected $file_number;

    /**
     * Fecha de la nómina
     *
     * @var string $date
     */
    protected $date;

    /**
     * Bandera
     *
     * @var integer $flag
     */
    protected $flag = 0;

    /**
     * Monto total de la nómina
     *
     * @var float $payroll_total
     */
    protected $payroll_total = 0.00;

    /**
     * Modelo de la nómina
     *
     * @var Payroll $payroll
     */
    protected $payroll;

    /**
     * Resultados para la exportación
     *
     * @var array $result
     */
    protected $result = [];

    /**
     * Resultados para la exportación
     *
     * @var array $errors
     */
    protected $errors = [];

    /**
     * Registros de la nómina
     *
     * @var array $records
     */
    protected $records = [];

    /**
     * Nombre de la función de decimales
     *
     * @var Parameter $nameDecimalFunction
     */
    protected $nameDecimalFunction;

    /**
     * Indica si es un txt de fideicomiso
     *
     * @var Parameter $trust_code
     */
    protected $trust_code;

    /**
     * Método constructor de la clase
     *
     * @return void
     */
    public function __construct()
    {
        $this->number_decimals = Parameter::where('p_key', 'number_decimals')->where('required_by', 'payroll')->first();
        $this->round = Parameter::where('p_key', 'round')->where('required_by', 'payroll')->first();
    }
    /**
     * Establece el identificador del registro de nómina
     *
     * @param integer|array $payrollId Identificador único del tabuldor salarial
     */
    public function setPayrollId($payrollId, $bank_account, $file_number, $date, $trust_code = null)
    {
        $payrolls = Payroll::query()->whereIn('id', $payrollId)->get();

        $mergedCollection = collect([]);

        foreach ($payrolls as $payroll) {
            if ($payroll->payrollPaymentPeriod?->payrollPaymentType?->is_survivor) {
                $mergedCollection = $mergedCollection->merge($payroll->payrollStaffPayrolls->load('payrollStaff.PayrollSurvivor'));
            } else {
                $mergedCollection = $mergedCollection->merge($payroll->payrollStaffPayrolls);
            }
        }

        $this->total = [];
        $this->number_decimals = 2;
        $this->zero_concept = Parameter::where('p_key', 'zero_concept')->where('required_by', 'payroll')->first();
        $this->bank_account = $bank_account;
        $this->file_number = $file_number;
        $this->date = $date;
        $this->trust_code = $trust_code != null ? $trust_code : false;

        $i = 1;
        $data = [];
        foreach ($mergedCollection->toArray() as $model) {
            if (isset($model["payroll_staff"])) {
                if (empty($model["payroll_staff"]["payroll_employment"])) {
                    $this->errors[]['employments'][] = $model["payroll_staff"]["id_number"]
                        . ' - ' . $model["payroll_staff"]["first_name"] . ' '
                        . $model["payroll_staff"]["last_name"];
                    continue;
                }
                array_push($data, $model);
            }
        }

        if (count($this->errors) > 0) {
            throw new \Exception(json_encode($this->errors));
        }

        foreach ($data as &$d) {
            $d['index'] = $i++;
        }
        if ($data[0]["payroll"]["payroll_payment_period"]["payroll_payment_type"]["is_survivor"]) {
            usort($data, function ($a, $b) {
                return $a["payroll_staff"]["payroll_survivor"]["first_name"] > $b["payroll_staff"]["payroll_survivor"]["first_name"];
            });
        } else {
            usort($data, function ($a, $b) {
                return $a["payroll_staff"]["first_name"] > $b["payroll_staff"]["first_name"];
            });
        }

        $this->payroll = collect($data);
    }

    /**
     * Establece las columnas que van a ser exportadas
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
     *
     * @return    array                       Arreglo con los campos estrictamente a ser exportados
     */
    public function array(): array
    {
        foreach ($this->payroll as $payroll) {
            $nameDecimalFunction = $this->round->p_value == 'false' ? 'currency_format' : 'round';
            $values = [];
            foreach ($payroll['concept_type'] as $key => $conceptType) {
                if (!array_key_exists($key, $values)) {
                    $values[$key] = $conceptType;
                } else {
                    $values[$key] += $conceptType;
                }
            }

            $largestConcepType = $values;
            $signsOrder = ['+', '-', 'NA'];
            $signIndex = 0;
            $sortedData = [];

            while (1) {
                foreach ($largestConcepType as $key => $concepType) {
                    $payrollConceptType = PayrollConceptType::where('name', $key)->first();

                    if (isset($payrollConceptType)) {
                        if ($payrollConceptType->sign == $signsOrder[$signIndex]) {
                            $sortedData[$key] = $concepType;
                            unset($largestConcepType[$key]);
                        }
                    } else {
                        unset($largestConcepType[$key]);
                    }
                }

                $signIndex++;

                if (count($largestConcepType) == 0) {
                    break;
                }
            }

            $largestConcepType = $sortedData;

            $concepTypes = $payroll["concept_type"];
            $dateNow = new DateTime("now");
            $startDate = new DateTime($payroll["payroll_staff"]["payroll_employment"]["start_date"]);
            $yearsApn = new DateTime($payroll["payroll_staff"]["payroll_employment"]["startDateApn"]);
            $diff = date_diff($dateNow, $yearsApn);
            $institution_years = $diff->format("%y");
            $financeAccountType = "";

            if (
                Module::has('Finance')
                && Module::isEnabled('Finance')
                && $payroll["payroll"]["payroll_payment_period"]["payroll_payment_type"]["is_survivor"] == true
            ) {
                $financeAccountType =  \Modules\Finance\Models\FinanceAccountType::class::find($payroll["payroll_staff"]["payroll_survivor"]["finance_account_type_id"]);
            }

            $data = [
                $payroll["index"],
                $payroll["payroll"]["payroll_payment_period"]["payroll_payment_type"]["is_survivor"] ? ($payroll["payroll_staff"]["payroll_survivor"]["first_name"] . ' ' . $payroll["payroll_staff"]["payroll_survivor"]["last_name"] ) : ($payroll["payroll_staff"]["first_name"] . ' ' . $payroll["payroll_staff"]["last_name"]),
                $payroll["payroll"]["payroll_payment_period"]["payroll_payment_type"]["is_survivor"] ? ($payroll["payroll_staff"]["payroll_survivor"]["id_number"]) : ($payroll["payroll_staff"]["id_number"]),
                $payroll["payroll_staff"]["payroll_employment"]["payroll_positions"][0]['name'],
                date_format($startDate, 'd/m/Y'),
                $institution_years,
                $payroll["payroll"]["payroll_payment_period"]["payroll_payment_type"]["is_survivor"] ? ($payroll["payroll_staff"]["payroll_survivor"]["payroll_account_number"] ?? '') : ($payroll["payroll_staff"]["payroll_financial"][0]["payroll_account_number"] ?? ''),
                $payroll["payroll_staff"]["payroll_nationality"]["country"]["name"],
                $payroll["payroll"]["payroll_payment_period"]["payroll_payment_type"]["is_survivor"] ? ($financeAccountType->code ?? '') : ($payroll["payroll_staff"]["payroll_financial"][0]["finance_account_type"]["code"] ?? '')
            ];

            if ($this->trust_code != false) {
                $data[] = $payroll["payroll_staff"]["first_name"];
                $data[] = $payroll["payroll_staff"]["last_name"];
            }

            $total = 0;
            $flagSign = '';

            foreach ($largestConcepType as $key => $conceptType) {
                if (count($conceptType) > 0) {
                    $subTotal = 0;

                    foreach ($conceptType as $typeKey => $type) {
                        $typeValue = $concepTypes[$key][$typeKey];
                        if (isset($concepTypes[$key]) && isset($concepTypes[$key][$typeKey])) {
                            $typeValue['value'] = str_replace(',', '', $typeValue['value']);

                            if ($typeValue['sign'] != 'NA') {
                                $value = $typeValue['value'] < 0 ? (float)$typeValue['value'] * -1
                                    : (float)$typeValue['value'];
                                array_push($data, $nameDecimalFunction($value, $this->number_decimals));
                                $flagSign = $typeValue['sign'];
                                if ($typeValue['sign'] == '+') {
                                    $subTotal = $nameDecimalFunction($subTotal + $value, $this->number_decimals);
                                } else {
                                    $subTotal = $nameDecimalFunction($subTotal - $value, $this->number_decimals);
                                }
                            } else {
                                $flagSign = $typeValue['sign'];
                            }
                        } else {
                            if ($type != 'NA') {
                                array_push($data, ' ');
                            } else {
                                $flagSign = $typeValue['sign'];
                            }
                        }
                    }

                    if ($flagSign != 'NA') {
                        $subTotal = $nameDecimalFunction($subTotal, $this->number_decimals);
                        $subTotalValue = $subTotal < 0 ? ($subTotal * -1) : $subTotal;

                        array_push($data, $nameDecimalFunction($subTotalValue, $this->number_decimals));
                        if ($flagSign == '+') {
                            $total = $nameDecimalFunction($total + $subTotalValue, $this->number_decimals);
                        } else {
                            $total = $nameDecimalFunction($total - $subTotalValue, $this->number_decimals);
                        }
                    }
                }
            }

            array_push($data, $nameDecimalFunction($total, $this->number_decimals));
            array_push($data, ' ');

            foreach ($largestConcepType as $key => $conceptType) {
                if (count($conceptType) > 0) {
                    $totalNA = 0;

                    foreach ($conceptType as $typeKey => $type) {
                        if (isset($concepTypes[$key]) && isset($concepTypes[$key][$typeKey])) {
                            $typeValue = $concepTypes[$key][$typeKey];
                            $typeValue['value'] = str_replace(',', '', $typeValue['value']);
                            if ($typeValue['sign'] == 'NA') {
                                $value = $typeValue['value'] < 0 ? (float)$typeValue['value'] * -1
                                    : (float)$typeValue['value'];
                                array_push($data, $nameDecimalFunction($value, $this->number_decimals));

                                $totalNA = $nameDecimalFunction(
                                    $totalNA + $nameDecimalFunction($value, $this->number_decimals),
                                    $this->number_decimals
                                );
                                $flagSign = $typeValue['sign'];
                            }
                        }
                    }

                    if ($flagSign == 'NA') {
                        $totalNA = $nameDecimalFunction($totalNA, $this->number_decimals);
                        $totalNAValue = $totalNA < 0 ? $nameDecimalFunction(($totalNA * -1), $this->number_decimals) : $totalNA;
                        if ($totalNA > 0) {
                            array_push($data, $nameDecimalFunction($totalNAValue, $this->number_decimals));
                            array_push($data, ' ');
                        }
                    }
                }
            }

            foreach ($data as $dataKey => $dataValue) {
                if ($dataKey < 6) {
                    $this->total[$dataKey] = ' ';
                } else {
                    if (!array_key_exists($dataKey, $this->total)) {
                        $this->total[$dataKey] = $nameDecimalFunction((float)$dataValue, $this->number_decimals);
                    } else {
                        $this->total[$dataKey] = $nameDecimalFunction((float)$this->total[$dataKey]
                            + (float)$dataValue, $this->number_decimals);
                    }
                }
            }

            if (!array_key_exists($data[2], $this->records)) {
                $this->records[$data[2]] = $data;
            } else {
                $space_index = array_search(' ', $data);
                $amount = $data[$space_index - 1];
                if (array_key_exists($space_index - 1, $this->records[$data[2]])) {
                    $this->records[$data[2]][$space_index - 1] += $amount;
                }
            }
        }

        foreach ($this->records as $id => $dataArray) {
            if ($this->trust_code != false) {
                $this->buildTrustString($dataArray);
            } else {
                $this->buildString($dataArray);
            }
        }

        if ($this->trust_code != false) {
            $headings = getTrustHeading($this->payroll_total, $this->trust_code, $this->date, $this->file_number, count($this->records));
        } else {
            $headings = getHeading($this->payroll_total, $this->bank_account, $this->date, $this->file_number);
        }

        array_unshift($this->result, [$headings]);

        return $this->result;
    }

    /**
     * Construye la cadena de texto a incluir en el archivo a exportar
     *
     * @param array $data Arreglo de datos a exportar
     *
     * @return void
     */
    public function buildString($data)
    {
        $record = substr($data[8], -1);

        $space_index = array_search(' ', $data);

        $amount = strval($data[$space_index - 1] * 100);

        $amout_lenght = strlen($amount);

        $employ_amount = str_repeat('0', 11 - $amout_lenght) . $amount;

        $employ_dni = str_repeat('0', 10 - strlen($data[2])) . $data[2];

        $full_name = strtoupper(cleanString($data[1]));

        $full_name = strlen($full_name) > 40 ? substr($full_name, 0, 40) : $full_name;

        $name_len = strlen($full_name);

        $full_name = $full_name . str_repeat(' ', (40 - $name_len));

        $record = $record . $data[6] . $employ_amount . ($data[8] == '01' ? '1770' : '0770')
            . $full_name . $employ_dni . '003291';

        array_push($this->result, [$record]);

        $this->payroll_total += $data[$space_index - 1];
    }

    /**
     * Construye la cadena de texto a incluir en el archivo a exportar
     *
     * @param array $data Arreglo de datos a exportar
     *
     * @return void
     */
    public function buildTrustString($data)
    {
        $record = substr($data[8], -1);

        $space_index = array_search(' ', $data);

        $amount = strval($data[$space_index - 1] * 100);

        $amout_lenght = strlen($amount);

        $employ_amount = str_repeat('0', 15 - $amout_lenght) . $amount;

        $type_dni = str_contains($data[7], 'Venezuela') || str_contains($data[7], 'venezuela')
            ? 'V'
            : 'E';

        $employ_dni = $data[2];

        $employ_dni = $type_dni . str_repeat('0', 9 - strlen($employ_dni)) . $employ_dni;

        $first_name = cleanString(str_contains($data[9], ' ') ? substr($data[9], 0, strpos($data[9], ' ')) : $data[9]);
        $last_name = cleanString(str_contains($data[10], ' ') ? substr($data[10], 0, strpos($data[10], ' ')) : $data[10]);

        $first_name_len = strlen($first_name);
        $last_name_len = strlen($last_name);

        $first_name = $first_name . str_repeat(' ', (20 - $first_name_len));
        $last_name = $last_name . str_repeat(' ', (20 - $last_name_len));

        $record = '02' . $employ_dni . $first_name . str_repeat(' ', 20)
            . $last_name . str_repeat(' ', 20) . $this->file_number . $employ_amount;

        array_push($this->result, [$record]);

        $this->payroll_total += $data[$space_index - 1];
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => "\t", // Usar tabulador como delimitador
            'enclosure' => "",   // Dejar el enclosure vacío para no agregar comillas
        ];
    }
}

/**
 * Retorna el encabezado de la hoja
 *
 * @param float $total Monto total a pagar
 * @param string $bank_account Cuenta bancaria
 * @param string $date Fecha
 * @param string $file_number Número de archivo
 *
 * @return string
 */
function getHeading($total, $bank_account, $date, $file_number)
{
    $user = auth()->user();
    $profileUser = $user->profile;
    if (($profileUser) && isset($profileUser->institution_id)) {
        $institution = Institution::find($profileUser->institution_id);
    } else {
        $institution = Institution::where('active', true)->where('default', true)->first();
    }

    $total = strval($total * 100);

    $total = str_repeat('0', 13 - strlen($total)) . $total;

    $institution_name_len = strlen($institution->acronym);

    $institution_name = $institution->acronym . str_repeat(' ', 40 - $institution_name_len);

    $bank_number = $bank_account;

    $new_date = date('d-m-Y', strtotime($date));

    $new_date = substr(str_replace('-', '/', $new_date), 0, -4) . substr($new_date, -2);

    $heading = 'H' . $institution_name . $bank_number . $file_number . $new_date . $total . '03291';

    return $heading;
}

/**
 * Retorna el encabezado de la hoja
 *
 * @param float $total Monto total a pagar
 * @param string $trust_code Codigo de fideicomitente
 * @param string $date Fecha
 * @param string $file_number Codigo de proceso
 * @param integer $quantity Cantidad de registros
 *
 * @return string
 */
function getTrustHeading($total, $trust_code, $date, $file_number, $quantity)
{
    $total = strval($total * 100);
    $total = str_repeat('0', 15 - strlen($total)) . $total;
    $new_date = date('d-m-Y', strtotime($date));
    $new_date = substr(str_replace('-', '', $new_date), 0);
    $quantity = str_repeat('0', 7 - strlen($quantity)) . $quantity;
    $trust_code = str_repeat('0', 7 - strlen($trust_code)) . $trust_code;
    $file_number = str_repeat('0', 3 - strlen($file_number)) . $file_number;

    $heading = '01' . $trust_code . $new_date . $quantity . $total . 'OTRO' . str_repeat(' ', 49) . $file_number;

    return $heading;
}

/**
 * Limpia la cadena de texto
 *
 * @param string $text Cadena a limpiar
 *
 * @return array|string|null
 */
function cleanString($text)
{
    $utf8 = array(
        '/[áàâãªä]/u'   =>   'a',
        '/[ÁÀÂÃÄ]/u'    =>   'A',
        '/[ÍÌÎÏ]/u'     =>   'I',
        '/[íìîï]/u'     =>   'i',
        '/[éèêë]/u'     =>   'e',
        '/[ÉÈÊË]/u'     =>   'E',
        '/[óòôõºö]/u'   =>   'o',
        '/[ÓÒÔÕÖ]/u'    =>   'O',
        '/[úùûü]/u'     =>   'u',
        '/[ÚÙÛÜ]/u'     =>   'U',
        '/ç/'           =>   'c',
        '/Ç/'           =>   'C',
        '/ñ/'           =>   'n',
        '/Ñ/'           =>   'N',
        '/–/'           =>   '-', // UTF-8 hyphen to "normal" hyphen
        '/[’‘‹›‚]/u'    =>   ' ', // Literally a single quote
        '/[“”«»„]/u'    =>   ' ', // Double quote
        '/ /'           =>   ' ', // nonbreaking space (equiv. to 0x160)
    );
    return preg_replace(array_keys($utf8), array_values($utf8), $text);
}
