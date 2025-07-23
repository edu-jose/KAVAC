<?php

declare(strict_types=1);

namespace Modules\Payroll\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Institution;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Payroll\Exports\PayrollTrustFileStaffExport;
use Modules\Payroll\Models\PayrollStaff;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * @class PayrollTrustFileJob
 * @brief Trabajo para procesar archivo de hoja de cálculo para generar txt de BNC
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollTrustFileJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Variable que contiene el tiempo de espera para la ejecución del trabajo,
     * si no se quiere limite de tiempo, se define en 0
     *
     * @var integer $timeout
     */
    public $timeout = 0; //300; /** 5min */

    /**
     * Crea una nueva instancia de trabajo.
     *
     * @return void
     */
    public function __construct(
        protected Institution $institution,
        protected int $userId,
        protected string $documentName,
        protected string $institutionAccountCode,
        protected string $documentNameWithoutExtension,
        protected string $filePath = '',
        protected array $payrollStaffs = [],
        protected array $data = [],
    ) {
        if ('local' !== @env('APP_ENV')) {
            $this->onQueue('bulk');
        }
    }

    /**
     * Ejecuta el trabajo.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $institution = $this->institution;
            $phone = $institution->phones->where('type', 'T')->first();

            // Ruta del archivo en el storage
            $filePath = storage_path($this->filePath);

            $spreadsheet = IOFactory::load($filePath);
            $countriesSheet = $spreadsheet->getSheetByName('Paises');
            $nationalitiesSheet = $spreadsheet->getSheetByName('Nacionalidades');
            $this->data = [
                [$this->setHeading()],
                ['C00002<<<USO INTERNO DEL BANCO>>>'],
                ['']
            ];

            $staffIndex = 1;

            // Se obtienen los datos de los trabajadores
            foreach ($this->payrollStaffs as $payrollStaff) {
                $this->buildString($payrollStaff, $countriesSheet, $nationalitiesSheet, $phone, $institution, $staffIndex);
                $staffIndex++;
            }

            Excel::store(
                new PayrollTrustFileStaffExport($this->data),
                $this->documentNameWithoutExtension . '.txt',
                'documents',
                \Maatwebsite\Excel\Excel::CSV
            );

            $user = User::without(['roles', 'permissions'])->where('id', $this->userId)->first();
            $user->notify(new SystemNotification(
                'Exito',
                "Archivo procesado con éxito, puede proceder a descargarlo desde este <a href='" . env('APP_URL') . '/storage/documents/' . $this->documentNameWithoutExtension . '.txt' . "' class='link-download' download>enlace</a>"
            ));
        } catch (\Exception $e) {
            $user = User::without(['roles', 'permissions'])->where('id', $this->userId)->first();
            Log::critical("Se generó un error en el procesamiento del archivo [{$e->getFile()}] en la línea [{$e->getLine()}]. Código del error: {$e->getCode()}, Detalles: {$e->getMessage()}.\n Se muestra a continuación una traza de los archivos que generaron el error: {$e->getTraceAsString()}");
            $user->notify(new SystemNotification(
                'Fallido',
                "Se ha generado un error al procesar el archivo, por favor verifique el archivo e intente de nuevo"
            ));
        }
    }

    public function setMaritalStatusCode(string $name): string
    {
        $maritalStatusMap = [
            'casa' => '2',
            'divo' => '3',
            'solte' => '4',
            'viud' => '5',
            'concubi' => '6',
            'separa' => '7',
        ];

        $name = strtolower($name);

        foreach ($maritalStatusMap as $key => $value) {
            if (str_contains($name, $key)) {
                return str_repeat('0', 5 - strlen((string) $value)) . $value;
            }
        }

        return '00000';
    }

    public function setGenderCode(string $gender): string
    {
        $genderMap = [
            'femenino' => '2',
            'masculino' => '3',
        ];

        $gender = strtolower($gender);

        if (array_key_exists($gender, $genderMap)) {
            return str_repeat('0', 5 - strlen($genderMap[$gender])) . $genderMap[$gender];
        }

        return '00000';
    }

    public function setBirthPlaceCode(string $nationality, Worksheet $sheet)
    {
        $nationality = substr($nationality, 0, 4);

        foreach ($sheet->getRowIterator() as $row) {
            $rowIndex = $row->getRowIndex();
            $cellValue = $sheet->getCell('B' . $rowIndex)->getValue();

            if ($cellValue != null && str_contains(strtolower($cellValue), strtolower($nationality))) {
                $code = $sheet->getCell('A' . $rowIndex)->getValue();
                return str_repeat('0', 5 - strlen((string) $code)) . $code;
            }
        }

        return '00000';
    }

    public function setPhoneAreaCode(string $code, Worksheet $sheet)
    {
        foreach ($sheet->getRowIterator() as $row) {
            $rowIndex = $row->getRowIndex();
            $cellValue = $sheet->getCell('A' . $rowIndex)->getValue();

            if ($cellValue != null && str_contains(strtolower($cellValue), strtolower($code))) {
                return $cellValue . '-' . $sheet->getCell('B' . $rowIndex)->getValue();
            }
        }

        return '00000';
    }

    public function setNationalityCode(string $nationality, Worksheet $sheet)
    {
        $nationality = substr($nationality, 0, 4);

        foreach ($sheet->getRowIterator() as $row) {
            $rowIndex = $row->getRowIndex();
            $cellValue = explode('-', $sheet->getCell('A' . $rowIndex)->getValue());
            if ($cellValue != null && str_contains(strtolower($cellValue[1]), strtolower($nationality))) {
                return str_repeat('0', 5 - strlen((string) $cellValue[0])) . $cellValue[0];
            }
        }

        return '00520';
    }

    public function setHeading(): string
    {
        $total = (string) count($this->payrollStaffs);
        $total = str_repeat('0', 5 - strlen($total)) . $total;

        $rif = $this->institution->rif;
        $rifType = substr($rif, 0, 1);
        $rifNumber = str_repeat('0', 9 - strlen(substr($rif, 1))) . substr($rif, 1);

        $phone = $this->institution->phones->where('type', 'T')->first();
        $phoneNumber = strlen((string) $phone?->number ?? '') > 7 ? substr($phone?->number ?? '', 0, 7) : $phone?->number ?? '';
        $phoneAreaCode = strlen((string) $phone?->area_code ?? '') > 4 ? substr($phone?->area_code ?? '', 0, 4) : $phone?->area_code ?? '';
        $areaCode = str_repeat('0', 4 - strlen($phoneAreaCode)) . $phoneAreaCode;
        $phoneNumber = str_repeat('0', 7 - strlen((string) $phoneNumber)) . $phoneNumber;
        $phone = $areaCode . $phoneNumber;

        $heading = 'C00001' . $total . $this->institution->acronym . str_repeat(' ', 229) . $rifType . $rifNumber . $phone . $this->institutionAccountCode;
        return $heading;
    }

    public function setBasicSalary($staff): string
    {
        $employment = $staff->payrollEmployment->toArray();
        $employment['previous_jobs'] = $employment['payroll_previous_job'];
        $request = new Request();
        $request->merge($employment);
        $tabController = new \Modules\Payroll\Http\Controllers\PayrollSalaryTabulatorController();
        $tabCalc = $tabController->getBaseSalaryByTabulator($request);
        $basicSalary = ($tabCalc != 'error' ? strval(floatval($tabCalc) * 100) : '00000000000000000') ?? '00000000000000000';
        return str_repeat('0', 17 - strlen($basicSalary)) . $basicSalary;
    }

    public function buildString($payrollStaff, $countriesSheet, $nationalitiesSheet, $phone, $institution, $staffIndex)
    {
        $staff = PayrollStaff::query()
            ->without([
                'payrollNationality',
                'payrollFinancial',
                'payrollGender',
                'payrollBloodType',
                'payrollDisability',
                'payrollLicenseDegree',
                'payrollEmployment',
                'payrollStaffUniformSize',
                'payrollSocioeconomic',
                'payrollProfessional',
                'payrollResponsibility'
            ])
            ->find($payrollStaff['id']);
        $birthplaceCode = $this->setBirthPlaceCode($staff->payrollNationality?->name ?? '', $countriesSheet);
        $nationality = $this->setNationalityCode($staff->payrollNationality?->name ?? '', $nationalitiesSheet);
        $maritalStatusCode = $this->setMaritalStatusCode($staff->payrollSocioeconomic?->maritalStatus->name ?? '');
        $genderCode = $this->setGenderCode($staff->payrollGender?->name ?? '');
        $basicSalary = $this->setBasicSalary($staff);

        $names = explode(' ', $staff->first_name, 2);
        $firstName = count($names) > 0 ? $this->cleanString($names[0]) . str_repeat(' ', 50 - strlen($this->cleanString($names[0]))) : str_repeat(' ', 50);
        $secondName = count($names) > 1 ? $this->cleanString($names[1]) . str_repeat(' ', 50 - strlen($this->cleanString($names[1]))) : str_repeat(' ', 50);
        $lastNames = explode(' ', $staff->last_name, 2);
        $firstLastName = count($lastNames) > 0 ? $this->cleanString($lastNames[0]) . str_repeat(' ', 50 - strlen($this->cleanString($lastNames[0]))) : str_repeat(' ', 50);
        $secondLastName = count($lastNames) > 1 ? $this->cleanString($lastNames[1]) . str_repeat(' ', 50 - strlen($this->cleanString($lastNames[1]))) : str_repeat(' ', 50);

        $birthdate = $staff->birthdate
            ? Carbon::parse($staff->birthdate)->format('dmY')
            : '00000000';
        $position = substr($this->cleanString($staff->payrollEmployment?->payrollPosition->name ?? ''), 0, 30);
        $profession = substr($this->cleanString($staff->payrollProfessional?->payrollStudies->first()?->professions->name ?? ''), 0, 30);
        $position = strtoupper($position) . str_repeat(' ', 30 - strlen($position));
        $profession = strtoupper($profession) . str_repeat(' ', 30 - strlen($profession));
        $phoneNumber = substr($phone?->number ?? '', 0, 19);
        $phoneAreaCode = substr($phone?->area_code ?? '', 0, 5);
        $areaCode = str_repeat('0', 5 - strlen($phoneAreaCode)) . $phoneAreaCode;
        $phoneNumber = $phoneNumber . str_repeat('0', 19 - strlen((string) $phoneNumber));
        $typeDNI = str_contains($staff->payrollNationality->name, 'Vene') ? 'V' : 'E';
        $staffIndex = str_repeat('0', 5 - strlen((string) $staffIndex)) . $staffIndex;

        $employDNI = $typeDNI . str_repeat('0', 9 - strlen($staff->id_number)) . $staff->id_number;
        $email = $institution->email . str_repeat(' ', 100 - strlen($institution->email ?? ''));
        $usaResident = $nationality == '00482' ? '00001' : '00002';

        $string = 'D' . $staffIndex . $employDNI . $firstName . $secondName . $firstLastName . $secondLastName . $birthdate . $birthplaceCode . $maritalStatusCode . $genderCode . $profession . $position . $areaCode . $phoneNumber . $email . $basicSalary . '00004' . '00095' . $nationality . $usaResident . strtoupper($this->cleanString($staff->address));

        array_push($this->data, [$string]);
    }

    /**
     * Limpia la cadena de texto
     *
     * @param string $text Cadena a limpiar
     *
     * @return array|string|null
     */
    public function cleanString($text)
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
}
