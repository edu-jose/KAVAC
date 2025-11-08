<?php

declare(strict_types=1);

namespace Modules\Payroll\Actions;

use App\Exports\MultiSheetExport;
use App\Models\Institution;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Payroll\Exports\PayrollAverageConceptSheetExport;
use Modules\Payroll\Models\PayrollPaymentPeriod;
use Modules\Payroll\Models\PayrollStaffPayroll;
use Modules\Payroll\Models\PayrollVacationPolicyPayment;

final class ExportPayrollAverageConceptReportAction
{
    public function __construct(
        private MultiSheetExport $worksheet,
    ) {
    }

    public function invoke(
        array $data,
        string $file = 'data'
    ) {
        $institution = auth()->user()->institution ?? Institution::query()
            ->toBase()
            ->select(['id', 'acronym', 'name'])
            ->where('active', true)
            ->where('default', true)
            ->first();

        $payrollVacationPolicyPayment = PayrollVacationPolicyPayment::query()
            ->with(['payrollVacationPolicy', 'payrollVacationPolicyPaymentConcepts'])
            ->whereHas('payrollVacationPolicy', fn ($query) => $query
                ->where([
                    'institution_id' => $institution->id,
                    'active' => true
                ]))->first();

        $sheets = [];
        foreach ($data['payroll_payment_types'] as $key => $value) {
            $payrollPaymentPeriod = PayrollPaymentPeriod::toBase()->find((int) $data['periods_by_payment_type'][$key]);

            $period = $payrollPaymentPeriod
                ? $payrollPaymentPeriod ->start_date . ' - ' . $payrollPaymentPeriod->end_date
                : '';

            $sheets[$value] =  new PayrollAverageConceptSheetExport(
                [
                    'institution' => $institution,
                    'period' => $period,
                    'records' => $this->getDataPayrollStaffPayroll(
                        (int) $key,
                        (int) $data['periods_by_payment_type'][$key],
                        $data['payroll_staffs'],
                        $data['payroll_concepts'],
                        $payrollVacationPolicyPayment
                    ),
                ],
                $value
            );
        }
        $this->worksheet->setSheets($sheets);

        return Excel::download($this->worksheet, $file . '.xlsx');
    }

    public function getDataPayrollStaffPayroll(
        int $payrollPaymentTypeId,
        int $payrollPaymentPeriodId,
        array $payrollStaffs,
        array $payrollConcepts,
        ?PayrollVacationPolicyPayment $payrollVacationPolicyPayment,
    ) {
        $payrollPaymentPeriod = PayrollPaymentPeriod::toBase()->find($payrollPaymentPeriodId);
        $payrollPaymentAction = new PayrollPaymentRelationshipAction();

        $fechaInicio = Carbon::createFromFormat('Y-m-d', $payrollPaymentPeriod->start_date);
        $fechaFin = Carbon::createFromFormat('Y-m-d', $payrollPaymentPeriod->end_date);



        $fechaInicio = $fechaInicio->subMonthNoOverflow(
            $payrollVacationPolicyPayment
                ? ($payrollVacationPolicyPayment->sweep_time ?? 0)
                : 0
        );
        $fechaFin->subMonthNoOverflow($payrollVacationPolicyPayment?->time_anticipation_months ?? 0);

        /** Datos de paga historico por periodo */
        $payrollStaffPayroll = PayrollStaffPayroll::query()
            ->with(['payroll' => function ($query) {
                $query->with(['payrollPaymentPeriod' => function ($query) {
                    $query->without('payrollPaymentType');
                }]);
            }, 'payrollStaff' => function ($query) {
                $query->with(['payrollEmployment' => function ($query) {
                    $query->without([
                        'payrollPositionType',
                        'payrollPositions',
                        'payrollCoordination',
                        'department',
                        'payrollStaffType',
                        'payrollInactivityType',
                        'payrollContractType',
                        'payrollPreviousJob'
                    ]);
                }])->without([
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
                ]);
            }])
            ->whereHas('payroll', fn ($query) => $query
                ->whereHas('payrollPaymentPeriod', fn ($query) => $query
                    ->where('payroll_payment_type_id', $payrollPaymentTypeId)
                    ->whereBetween('start_date', [$fechaInicio, $fechaFin])))->get();

        if (count($payrollConcepts) === 0) {
            $payrollConcepts = $payrollVacationPolicyPayment
                ?->payrollVacationPolicyPaymentConcepts
                ->pluck('payroll_concept_id')
                ->toArray();
        }

        $payrollStaffPayrollData = collect($payrollStaffPayroll->filter(function ($item) use ($payrollStaffs) {
            return in_array($item->payroll_staff_id, $payrollStaffs) || empty($payrollStaffs);
        })->reduce(function ($carry, $item) use ($payrollConcepts, $payrollPaymentAction) {
            $parameters = $payrollPaymentAction->getPayrollParameters($item->payroll_id, true);
            $filterParameters = array_filter($parameters, function ($param) use ($item) {
                return $param['staff_id'] == $item->payroll_staff_id;
            });

            foreach ($item->concept_type as $values) {
                foreach ($values as $value) {
                    if (in_array($value['id'], $payrollConcepts) && $value['value'] > 0) {
                        $filterParameter = array_filter($filterParameters, function ($parameter) use ($value) {
                            return str_contains($value['formula'], $parameter['name']);
                        });

                        $carry[] = [
                            'id' => $value['id'],
                            'value' => $value['value'],
                            'id_number' => ($item['payrollStaff']) ? $item['payrollStaff']['id_number'] : '',
                            'worksheet_code' => ($item['payrollStaff'] && $item['payrollStaff']['payrollEmployment']) ? $item['payrollStaff']['payrollEmployment']['worksheet_code'] : '',
                            'payroll_concept' => $value['name'],
                            'payroll_staff' => ($item['payrollStaff']) ? $item['payrollStaff']['full_name'] : '',
                            'position' => $item->payrollStaff->payrollEmployment->payrollPosition->name ?? '',
                            'date' => ($item['payroll']['payrollPaymentPeriod']) ? $item['payroll']['payrollPaymentPeriod']['start_date'] : '',
                            'parameter_value' => count($filterParameter) > 0 ? collect($filterParameter)->first()['value'] : 0,
                        ];
                    }
                }
            }

            return $carry;
        }, []));

        return  $payrollStaffPayrollData->groupBy(function ($item) {
            return $item['id_number'] . '-' . $item['payroll_concept'];
        })->map(function ($items, $key) use ($payrollVacationPolicyPayment) {
            $firstItem = $items->first();

            $average = 0;
            $totalParameters = $items->sum(function ($item) {
                return (int) $item['parameter_value'];
            });

            if ($items->count() >= $payrollVacationPolicyPayment->time_number_concepts ?? INF) {
                $average = currency_format((string) ($totalParameters / ($items->count() > 0 ? $items->count() : 1)), 2, true);
            }

            return [
                'worksheet_code' => $firstItem['worksheet_code'],
                'id_number' => $firstItem['id_number'],
                'payroll_staff' => $firstItem['payroll_staff'],
                'position' => $firstItem['position'] ?? 'Cargo',
                'payroll_concept' => $firstItem['payroll_concept'],
                'average' => $average,
                'concept_count' => $items->count()
            ];
        })->values();
    }
}
