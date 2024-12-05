<?php

use Illuminate\Support\Str;
use Modules\Budget\Models\BudgetSubSpecificFormulation;
use Modules\Budget\Models\CodeSetting;

if (! function_exists('budget_available')) {
    /**
     * Determina la disponibilidad presupuestaria de una cuenta
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  string  $year               Año de la formulación presupuestaria
     * @param  integer $specific_action_id Identificador de la acción específica
     * @param  integer $account_id         Identificador de la cuenta presupuestaria
     *
     * @return float|integer Devuelve el monto de la disponibilidad de la cuenta
     */
    function budget_available($year, $specific_action_id, $account_id)
    {
        $available = 0;

        $formulation = BudgetSubSpecificFormulation::where(
            [
                'year' => $year, 'budget_specific_action_id' => $specific_action_id
            ]
        )->first();

        if ($formulation) {
            $account_formulated = $formulation->accountOpens()->where('budget_account_id', $account_id)->first();

            if ($account_formulated) {
                $available += $account_formulated->total_year_amount;
            }

            $aditional_credits = $formulation->aditionalCreditAccounts()->where(
                'budget_account_id',
                $account_id
            )->get();

            if ($aditional_credits) {
                foreach ($aditional_credits as $aditionalCredit) {
                    $available += $aditionalCredit->amount;
                }
            }
        }

        return $available ?? 0;
    }
}

if (! function_exists('budget_check_opened_account')) {
    /**
     * Determina si la cuenta esta aperturada para el año de ejecución presupuestaria
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  string  $year               Año de la ejecución presupuestaria
     * @param  integer $specific_action_id Identificador de la acción específica
     * @param  integer $account_id         Identificador de la cuenta presupuestaria
     *
     * @return boolean                     Devuelve verdadero si la cuenta esta aperturada,
     *                                     de lo contrario retorna falso
     */
    function budget_check_opened_account($year, $specific_action_id, $account_id)
    {
        $opened = false;

        $formulation = BudgetSubSpecificFormulation::where(
            [
            'year' => $year, 'budget_specific_action_id' => $specific_action_id
            ]
        )->first();

        if ($formulation) {
            $opened = ($formulation->accountOpens()->where('budget_account_id', $account_id)->first());
        }

        return $opened;
    }
}

if (! function_exists('generate_budget_availability_code')) {
    function generate_budget_availability_code($prefix, $code_length, $suffix, $model, $field)
    {
        $separator = config('budget.budget_availability.separator');
        $newCode = 1;

        $targetModel = $model::select($field)->where($field, 'like', "{$prefix}{$separator}%" . ($suffix ? "{$separator}{$suffix}" : ""))
            ->withTrashed()->orderBy('id', 'desc')->first();

        $codeSetting = CodeSetting::where([
                'module' => 'purchase',
                'table'  => 'purchase_budgetary_availabilities',
                'field'  => 'code',
                'type'   => null
            ])->first();

        if ($targetModel && $codeSetting) {
            $segments = explode($separator, $codeSetting->format_code);
            $position = array_search(true, array_map(fn($segment) => ctype_digit($segment) && $segment == str_repeat('0', strlen($segment)), $segments));

            $segmentsNew = explode($separator, $targetModel?->$field ?? '');

            $newCode += (int) $segmentsNew[$position];
        }

        if (strlen((string)$newCode) > $code_length) {
            return ["error" => "El nuevo código excede la longitud permitida"];
        }

        return "{$prefix}{$separator}{$newCode}" . ($suffix ? "{$separator}{$suffix}" : "");
    }
}
