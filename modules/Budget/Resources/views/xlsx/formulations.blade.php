<!DOCTYPE html>
<html>
<body>
@php
    use Carbon\Carbon;

    function convertCurrency($conversion_history, $amount, $date, $decimal_places, $separator, $decimal_separator) {
        return number_format(($conversion_history[$date] * $amount), $decimal_places, $decimal_separator, $separator);
    }
@endphp
<table>
	<thead>
		<tr>
			<th><h3 align="center" > Reporte de Presupuesto Formulado </h3></th>

		</tr>
	</thead>
	<tbody>
		<tr>
			<td></td>
		</tr>
		<tr>
			<td></td>
		</tr>
		<tr>
			<td></td>
		</tr>
		<tr>
			<td></td>
		</tr>
	</tbody>	
</table>

<table width="100%" cellpadding="4" style="font-size: 8rem">
    <tbody>
        <tr>
            <td width="25%" style="font-weight: bold;">Presupuesto asignado:</td>
            <td width="75%">{{ $formulations[0]->assigned || $formulations[0]->assigned === '1' ? 'Sí' : 'No' }}</td>
        </tr>
        <tr>
            <td width="25%" style="font-weight: bold;">Institución:</td>
            <td width="75%">{{ $formulations[0]->specificAction->institution }}</td>
        </tr>
        <tr>
            <td width="25%" style="font-weight: bold;">Moneda:</td>
            <td width="75%">{{ $currency->description }}</td>
        </tr>
        <tr>
            <td width="25%" style="font-weight: bold;">Año Fiscal:</td>
            <td width="75%">{{ $formulations[0]->year }}</td>
        </tr>
        <tr>
            <td width="25%" style="font-weight: bold;">{{ $formulations[0]->specificAction->type }}:</td>
            <td width="75%">{{ $formulations[0]->specificAction->specificable->code }} -
                {{ $formulations[0]->specificAction->specificable->name }}
            </td>
        </tr>
        <tr>
            <td width="25%" style="font-weight: bold;">Total Formulado:</td>
            <td width="75%">{{ $currency->symbol }}
                {{ number_format($totalFormulations, $currency->decimal_places, ',', '.') }}
            </td>
        </tr>
        <tr>
            <td width="25%" style="font-weight: bold;">Generado por:</td>
            @php
                $analist_name = $profile ? $profile->first_name . ' ' . $profile->last_name : '';
            @endphp
            <td width="75%">{{ $analist_name }}</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
        </tr>
    </tbody>
</table>

@foreach ($formulations as $formulation)
    @php
        $exchangeRates = \Modules\Budget\Facades\CurrencyHistory::getExchangeRateHistory($formulation->date, $formulation->date, $formulation->currency, $currency);
    @endphp

    <table align="center" class="table table-bordered table-hover" width="100%" cellpadding="5" style="font-size: 7rem;">
        <thead>
            <tr>
                <td width="99.4%" style="border: solid 1px #000;">{{ $formulation->specificAction->code }} -
                    {{ strip_tags($formulation->specificAction->name) . ' ' . '(' . Carbon::parse($formulation->date)->format("d-m-Y") . ')' }}
                </td>
            </tr>
            <tr style="font-weight: bold;">
                <th width="10%" style="border: solid 1px #000;" bgcolor="#D3D3D3">Código</th>
                <th width="12%" style="border: solid 1px #000;" bgcolor="#D3D3D3">Denominación</th>
                <th width="6%" style="border: solid 1px #000;" bgcolor="#D3D3D3">Total Año</th>
                @foreach (['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'] as $month)
                    <th width="5.95%" style="border: solid 1px #000;" bgcolor="#D3D3D3">{{ $month }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($formulation->accountOpens->sortBy('budgetAccount.code')  as $accountOpen)
                @php
                    $style = $accountOpen->budgetAccount->specific === '00' ? 'font-weight:bold;' : '';
                @endphp
                <tr style="{{ $style }}">
                    <td width="10%" style="border: solid 1px #808080;">{{ $accountOpen->budgetAccount->code }}</td>
                    <td width="12%" align="left" style="border: solid 1px #808080;">
                        {{ $accountOpen->budgetAccount->denomination }}</td>
                    <td width="6%" style="border: solid 1px #808080;">
                        {{ convertCurrency($exchangeRates, $accountOpen->total_year_amount, $formulation->date, $currency->decimal_places, '.', ',') }}
                    </td>
                    <td width="5.95%" style="border: solid 1px #808080;">
                        {{ convertCurrency($exchangeRates, $accountOpen->jan_amount, $formulation->date, $currency->decimal_places, '.', ',') }}</td>
                    <td width="5.95%" style="border: solid 1px #808080;">
                        {{ convertCurrency($exchangeRates, $accountOpen->feb_amount, $formulation->date, $currency->decimal_places, '.', ',') }}</td>
                    <td width="5.95%" style="border: solid 1px #808080;">
                        {{ convertCurrency($exchangeRates, $accountOpen->mar_amount, $formulation->date, $currency->decimal_places, '.', ',') }}</td>
                    <td width="5.95%" style="border: solid 1px #808080;">
                        {{ convertCurrency($exchangeRates, $accountOpen->apr_amount, $formulation->date, $currency->decimal_places, '.', ',') }}</td>
                    <td width="5.95%" style="border: solid 1px #808080;">
                        {{ convertCurrency($exchangeRates, $accountOpen->may_amount, $formulation->date, $currency->decimal_places, '.', ',') }}</td>
                    <td width="5.95%" style="border: solid 1px #808080;">
                        {{ convertCurrency($exchangeRates, $accountOpen->jun_amount, $formulation->date, $currency->decimal_places, '.', ',') }}</td>
                    <td width="5.95%" style="border: solid 1px #808080;">
                        {{ convertCurrency($exchangeRates, $accountOpen->jul_amount, $formulation->date, $currency->decimal_places, '.', ',') }}</td>
                    <td width="5.95%" style="border: solid 1px #808080;">
                        {{ convertCurrency($exchangeRates, $accountOpen->aug_amount, $formulation->date, $currency->decimal_places, '.', ',') }}</td>
                    <td width="5.95%" style="border: solid 1px #808080;">
                        {{ convertCurrency($exchangeRates, $accountOpen->sep_amount, $formulation->date, $currency->decimal_places, '.', ',') }}</td>
                    <td width="5.95%" style="border: solid 1px #808080;">
                        {{ convertCurrency($exchangeRates, $accountOpen->oct_amount, $formulation->date, $currency->decimal_places, '.', ',') }}</td>
                    <td width="5.95%" style="border: solid 1px #808080;">
                        {{ convertCurrency($exchangeRates, $accountOpen->nov_amount, $formulation->date, $currency->decimal_places, '.', ',') }}</td>
                    <td width="5.95%" style="border: solid 1px #808080;">
                        {{ convertCurrency($exchangeRates, $accountOpen->dec_amount, $formulation->date, $currency->decimal_places, '.', ',') }}</td>
                </tr>
            @endforeach
            @foreach ($formulation->accountOpens as $accountOpen)
                @php
                    $style = $accountOpen->budgetAccount->specific === '00' ? 'font-weight:bold;' : '';
                    $totalAccount = $accountOpen->budgetAccount->where('egress', true)->whereNull('parent_id')->get();
                @endphp
                @if($totalAccount[0]['code'] == $accountOpen->budgetAccount['code'])
                <tr style="{{ $style }}">
                    <th width="22%" style="border: solid 1px #000;" bgcolor="#D3D3D3">Total {{ $currency->symbol }}</th>
                    <td style="border: solid 1px #808080;" bgcolor="#D3D3D3"></td>
                    <td width="6%" style="border: solid 1px #808080;" bgcolor="#D3D3D3">
                        {{ convertCurrency($exchangeRates, $accountOpen->total_year_amount, $formulation->date, $currency->decimal_places, '.', ',') }}
                    </td>
                    <td width="5.95%" style="border: solid 1px #808080;" bgcolor="#D3D3D3">
                        {{ convertCurrency($exchangeRates, $accountOpen->jan_amount, $formulation->date, $currency->decimal_places, '.', ',') }}</td>
                    <td width="5.95%" style="border: solid 1px #808080;" bgcolor="#D3D3D3">
                        {{ convertCurrency($exchangeRates, $accountOpen->feb_amount, $formulation->date, $currency->decimal_places, '.', ',') }}</td>
                    <td width="5.95%" style="border: solid 1px #808080;" bgcolor="#D3D3D3">
                        {{ convertCurrency($exchangeRates, $accountOpen->mar_amount, $formulation->date, $currency->decimal_places, '.', ',') }}</td>
                    <td width="5.95%" style="border: solid 1px #808080;" bgcolor="#D3D3D3">
                        {{ convertCurrency($exchangeRates, $accountOpen->apr_amount, $formulation->date, $currency->decimal_places, '.', ',') }}</td>
                    <td width="5.95%" style="border: solid 1px #808080;" bgcolor="#D3D3D3">
                        {{ convertCurrency($exchangeRates, $accountOpen->may_amount, $formulation->date, $currency->decimal_places, '.', ',') }}</td>
                    <td width="5.95%" style="border: solid 1px #808080;" bgcolor="#D3D3D3">
                        {{ convertCurrency($exchangeRates, $accountOpen->jun_amount, $formulation->date, $currency->decimal_places, '.', ',') }}</td>
                    <td width="5.95%" style="border: solid 1px #808080;" bgcolor="#D3D3D3">
                        {{ convertCurrency($exchangeRates, $accountOpen->jul_amount, $formulation->date, $currency->decimal_places, '.', ',') }}</td>
                    <td width="5.95%" style="border: solid 1px #808080;" bgcolor="#D3D3D3">
                        {{ convertCurrency($exchangeRates, $accountOpen->aug_amount, $formulation->date, $currency->decimal_places, '.', ',') }}</td>
                    <td width="5.95%" style="border: solid 1px #808080;" bgcolor="#D3D3D3">
                        {{ convertCurrency($exchangeRates, $accountOpen->sep_amount, $formulation->date, $currency->decimal_places, '.', ',') }}</td>
                    <td width="5.95%" style="border: solid 1px #808080;" bgcolor="#D3D3D3">
                        {{ convertCurrency($exchangeRates, $accountOpen->oct_amount, $formulation->date, $currency->decimal_places, '.', ',') }}</td>
                    <td width="5.95%" style="border: solid 1px #808080;" bgcolor="#D3D3D3">
                        {{ convertCurrency($exchangeRates, $accountOpen->nov_amount, $formulation->date, $currency->decimal_places, '.', ',') }}</td>
                    <td width="5.95%" style="border: solid 1px #808080;" bgcolor="#D3D3D3">
                        {{ convertCurrency($exchangeRates, $accountOpen->dec_amount, $formulation->date, $currency->decimal_places, '.', ',') }}</td>
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>
@endforeach
<div>
    <table style="font-size: 8rem; margin-top: 100px" cellpadding="3">
        <tr>
            <td>
                &nbsp;
            </td>
        </tr>
        <tr>
            <td>
                &nbsp;
            </td>
        </tr>
        <tr>
            <td>
                &nbsp;
            </td>
        </tr>
        <tr>
            <td>
                &nbsp;
            </td>
        </tr>
        <tr>
            <td align="center" width="30%">
                Atentamente
            </td>
        </tr>
        <tr>
            <td>
                &nbsp;
            </td>
        </tr>
        <tr>
            <td>
                &nbsp;
            </td>
        </tr>
        <tr>
            <td align="center" width="30%" style="border-top: solid 1px #000;">
                {{ $analist_name }}
            </td>
            <td width="70%">
                &nbsp;
            </td>
        </tr>
    </table>
</div>
</body>
</html>