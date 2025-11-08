<?php

namespace Modules\Payroll\Jobs;

use App\Models\Institution;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\SystemNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Payroll\Models\PayrollSocioeconomic;
use Modules\Payroll\Repositories\ReportRepository;

/**
 * @class PayrollFamilyBurdenPdfReportJob
 * @brief Trabajo que se encarga de generar el pdf del reporte de cargas familiares
 *
 * Trabajo que se encarga de generar el pdf del reporte de cargas familiares
 *
 * @author Fabian Palmera <fapalmera@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollFamilyBurdenPdfReportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 0;

    /**
     * Objeto de la institución
     *
     * @var Institution $institution
     */
    protected Institution $institution;

    /**
     * Nombre del archivo
     *
     * @var string $filename
     */
    protected string $filename;
    /**
     * Ruta Archivo txt
     *
     * @var string $fileroute
     */
    protected string $fileroute;

    /**
     * Cuerpo del pdf
     *
     * @var string $pdfBody
     */
    protected string $pdfBody;

    /**
     * Array de registros para mostrar en el reporte
     *
     * @var array $records
     */
    protected array $records;

    /**
     * Array de datos del request
     *
     * @var array $request
     */
    protected array $request;

    /**
     * Crea una nueva instancia de trabajo.
     *
     * @return void
     */
    public function __construct(array $data, string $fileroute, protected object $user)
    {
        [
            $this->institution,
            $this->filename,
            $this->pdfBody,
            $this->request
        ]
            = $data;
        $this->fileroute = $fileroute;
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
        $allStaffs = array_search('todos', array_column($this->request['payroll_staffs'], 'id'));
        $allRelationships = array_search('todos', array_column($this->request['payroll_relationships'], 'id'));

        if ($allStaffs !== false) {
            if ($allRelationships !== false) {
                $records = PayrollSocioeconomic::with(
                    ['payrollStaff' => fn($query) => $query->without(
                        [
                            'payrollNationality',
                            'payrollFinancial',
                            'payrollGender',
                            'payrollBloodType',
                            'payrollDisability',
                            'payrollLicenseDegree',
                            'payrollStaffUniformSize',
                            'payrollSocioeconomic',
                            'payrollProfessional',
                            'payrollResponsibility'
                        ]
                    )->select('id', 'first_name', 'last_name', 'id_number')->with(
                        [
                            'payrollEmployment' => fn($query) => $query->without(
                                [
                                    'payrollPositionType',
                                    'payrollCoordination',
                                    'payrollStaffType',
                                    'payrollInactivityType',
                                    'payrollContractType',
                                    'payrollPreviousJob'
                                ]
                            )->select('id', 'start_date', 'payroll_staff_id', 'department_id')->with(['department' => fn($query) => $query->select('id', 'name')])
                        ]
                    )]
                )->has('payrollChildrens')
                    ->without('maritalStatus')
                    ->get();
            } else {
                $realtionshipsIds = array_column($this->request['payroll_relationships'], 'id');

                $records = PayrollSocioeconomic::with(
                    ['payrollStaff' => fn($query) => $query->without(
                        [
                            'payrollNationality',
                            'payrollFinancial',
                            'payrollGender',
                            'payrollBloodType',
                            'payrollDisability',
                            'payrollLicenseDegree',
                            'payrollStaffUniformSize',
                            'payrollSocioeconomic',
                            'payrollProfessional',
                            'payrollResponsibility'
                        ]
                    )->select('id', 'first_name', 'last_name', 'id_number')->with(
                        [
                            'payrollEmployment' => fn($query) => $query->without(
                                [
                                    'payrollPositionType',
                                    'payrollCoordination',
                                    'payrollStaffType',
                                    'payrollInactivityType',
                                    'payrollContractType',
                                    'payrollPreviousJob'
                                ]
                            )->select('id', 'start_date', 'payroll_staff_id', 'department_id')->with(['department' => fn($query) => $query->select('id', 'name')])
                        ]
                    )]
                )->whereHas('payrollChildrens', function ($query) use ($realtionshipsIds) {
                    $query->whereIn('payroll_relationships_id', $realtionshipsIds);
                })
                    ->with(['payrollChildrens' => function ($query) use ($realtionshipsIds) {
                        $query->whereIn('payroll_relationships_id', $realtionshipsIds);
                    }])
                    ->without('maritalStatus')
                    ->get();
            }
        } else {
            if ($allRelationships !== false) {
                $staffIds = array_column($this->request['payroll_staffs'], 'id');

                $records = PayrollSocioeconomic::query()
                    ->has('payrollChildrens')
                    ->without('maritalStatus')
                    ->whereIn('payroll_staff_id', $staffIds)
                    ->get();
            } else {
                $staffIds = array_column($this->request['payroll_staffs'], 'id');
                $realtionshipsIds = array_column($this->request['payroll_relationships'], 'id');

                $records = PayrollSocioeconomic::query()
                    ->whereIn('payroll_staff_id', $staffIds)
                    ->whereHas('payrollChildrens', function ($query) use ($realtionshipsIds) {
                        $query->whereIn('payroll_relationships_id', $realtionshipsIds);
                    })
                    ->with(['payrollChildrens' => function ($query) use ($realtionshipsIds) {
                        $query->whereIn('payroll_relationships_id', $realtionshipsIds);
                    }])
                    ->without('maritalStatus')
                    ->get();
            }
        }
        if ($records->isEmpty()) {
            $this->user->notify(new SystemNotification('Carga Familiar', 'Lo sentimos, no se han encontrado registros que coincidan con los parámetros proporcionados para el reporte de carga familiar.'));
            throw new \Exception('No hay registros que procesar para el reporte de carga familiar');
        }
        $records->chunk(50)->each(function ($chunk, $index) {
            $filename = $this->filename . '-' . $index . '.pdf';
            $this->generatePdf($chunk, $filename, $index);
        });
    }

    private function addFilenameToTxtFile($filename, $fileroute)
    {
        $output = file_get_contents($fileroute);
        $file = fopen($fileroute, 'w');
        $output .= $filename . "\r\n";
        fwrite($file, $output);
        fclose($file);
    }

    private function generatePdf($records, $filename, $index)
    {
        $hasbanner = 0;
        $hasLogo = 0;
        if ($index == "0") {
            $hasbanner = true;
            $hasLogo = true;
        }
        $pdf = new ReportRepository();
        $this->addFilenameToTxtFile($filename, $this->fileroute);
        $pdf->setConfig(
            [
                'institution' => $this->institution,
                'urlVerify' => url(''),
                'orientation' => 'P',
                'filename' => $filename,
            ]
        );

        $pdf->setHeader('Reporte de carga familiar', '', false, false, $hasbanner, $hasLogo);
        $pdf->setFooter(true, strip_tags($this->institution->legal_address));
        $pdf->setBody(
            $this->pdfBody,
            true,
            [
                'pdf' => $pdf,
                'field' => $records,
            ]
        );
    }
}
