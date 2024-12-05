<h4 align="center">
        Registro {{ $record->documentStatus?->name}}
</h4>
<br>

<table>
    <tbody>
        <tr>
            <td>
                <strong>Institución:</strong> {{ $record->institution->name }}
            </td>
        </tr>
        <tr>
            <td>
                <strong>Fecha de pago:</strong> {{ $record->payment_date->format('d/m/Y') }}
            </td>
        </tr>
        <tr>
            <td>
                <strong>Tipo de transacción:</strong> {{ $record->transaction_type }}
            </td>
        </tr>
        <tr>
            <td>
                <strong>Documento de referencia:</strong> {{ $record->reference }}
            </td>
        </tr>
        <tr>
            <td colspan="2">&#160;</td>
        </tr>
        <tr>
            <td colspan="2" class="text-justify font-weight-bold" style="font-size: 1.2em;font-weight:bold;text-align: center">
                {{
                    convertirNumeros(
                        currency_format(
                            $record->amount,
                            $record->currency->decimal_places,
                            ".",
                            ""
                        ),
                        strtoupper($record->currency->plural_name)
                    )
                }}
            </td>
        </tr>
        <tr>
            <td style="font-size: 1.3em;text-align: center">
                <strong>*** {{ $record->currency->symbol  }} {{ number_format($record->amount, $record->currency->decimal_places, ",", ".") }} ***</strong>
            </td>
        </tr>
        <tr>
            <td colspan="2">&#160;</td>
        </tr>
        <tr>
            <td>
                <strong>Nro. de cuenta:</strong> {{ $record->finance_bank_account_id ? $record->financeBankAccount->ccc_number : '' }}
            </td>
        </tr>
        <tr>
            <td>
                <strong>Banco:</strong> {{ $record->finance_bank_account_id ? $record->financeBankAccount->financeBankingAgency->financeBank->name : '' }}
            </td>
        </tr>
        <tr>
            <td>
                <strong>Tipo de cuenta:</strong> {{ $record->finance_bank_account_id ? $record->financeBankAccount->financeAccountType->name : '' }}
            </td>
        </tr>
        <tr>
            <td colspan="2">&#160;</td>
        </tr>
        <tr>
            <td>
                <strong>Concepto:</strong> {{ $record->concept }}
            </td>
        </tr>
    </tbody>
</table>
<br>
<hr>
&#160;

@if ($record->budgetCompromise && $record->budgetCompromise->budgetCompromiseDetails)
<table style="font-size: 0.85em;">
    <tbody>
        <tr>
            <td colspan="4">&#160;</td>
        </tr>
        <tr>
                <td colspan="4" style="text-align: center"> <strong>PROYECTO O ACCIÓN CENTRALIZADA</strong></td>
        </tr>
        <tr>
            <td colspan="4" style="text-align: center"> <strong>{{
                $record->budgetCompromise->budgetCompromiseDetails[0]->budgetSubSpecificFormulation->specificAction->type }}</strong>: {{ $record->budgetCompromise->budgetCompromiseDetails[0]->budgetSubSpecificFormulation->specificAction->specificable->code . ' - ' . $record->budgetCompromise->budgetCompromiseDetails[0]->budgetSubSpecificFormulation->specificAction->specificable->name }}</td>
        </tr>
    </tbody>
</table>
<br>
&#160;
<br>
    <h5 style="text-align: center">PARTIDAS PRESUPUESTARIAS</h5>
    <table style="font-size: 0.85em;background-color:#adbfd3;padding:10px;">
        <thead>
            <tr>
                <th style="text-align: center; font-weight:bold;">ACCIÓN ESPECÍFICA</th>
                <th style="text-align: center; font-weight:bold;">CÓDIGO DE LA PARTIDA</th>
                <th style="text-align: center; font-weight:bold;">PARTIDA PRESUPUESTARIA</th>
                <th style="text-align: center; font-weight:bold;">CONCEPTO</th>
                <th style="text-align: center; font-weight:bold;">MONTO</th>
            </tr>
        </thead>
        @php
            $accountAmount = 0;
        @endphp
        <tbody>
            @foreach ($record->budgetCompromise->budgetCompromiseDetails as $accountableAccount)
                <tr>
                    <td style="text-align: center;">
                        {{ $accountableAccount->budgetSubSpecificFormulation->specificAction?->code . ' - ' . $accountableAccount->budgetSubSpecificFormulation->specificAction?->name }}
                    </td>
                    <td style="text-align: center;">
                        {{ $accountableAccount->budgetAccount?->code }}
                    </td>
                    <td style="text-align: center;">
                        {{ $accountableAccount->budgetAccount?->denomination }}
                    </td>
                    <td style="text-align: center;">
                        {{ $accountableAccount->description }}
                    </td>
                    <td style="text-align: center;">
                        {{ number_format($accountableAccount->amount, $record->currency->decimal_places, ",", ".") }}
                    </td>
                </tr>
                @php
                    $accountAmount += $accountableAccount->amount;
                @endphp
            @endforeach
            <tr>
                <td colspan="4" style="font-weight:bold;text-align: right">TOTAL {{$record->currency->symbol}}</td>
                <td style="font-weight:bold;text-align: center;border-top:solid 1px #000;">
                    {{ number_format($accountAmount, $record->currency->decimal_places, ",", ".") }}
                </td>
            </tr>
        </tbody>
    </table>
@endif

@if ($record->accountingEntryPivot && $record->accountingEntryPivot->accountingEntry)
<br>
&#160;
<hr>
<br>
    <h5 style="text-align: center">ASIENTO CONTABLE</h5>
    <table style="font-size: 0.85em;background-color:#adbfd3;padding:10px;">
        <thead>
            <tr>
                <th style="text-align: center; font-weight:bold;">CÓDIGO CUENTA</th>
                <th style="text-align: center; font-weight:bold;">NOMBRE CUENTA</th>
                <th style="text-align: right; font-weight:bold;">DEBE</th>
                <th style="text-align: right; font-weight:bold;">HABER</th>
            </tr>
        </thead>
        <tbody>

            @foreach ($record->accountingEntryPivot->accountingEntry->accountingAccounts as $accountEntry)
                <tr>
                    <td style="text-align: center;">{{ $accountEntry->account->code }}</td>
                    <td style="text-align: center;">{{ $accountEntry->account->denomination }}</td>
                    <td style="text-align: right;">
                        {{ number_format($accountEntry->debit, $record->currency->decimal_places, ",", ".") }}
                    </td>
                    <td style="text-align: right;">
                        {{ number_format($accountEntry->assets, $record->currency->decimal_places, ",", ".") }}
                    </td>
                </tr>
            @endforeach
            <tr>
                <td colspan="2" style="font-weight:bold;text-align: right">TOTAL {{$record->currency->symbol}}</td>
                <td style="font-weight:bold;text-align: right;border-top:solid 1px #000;">
                    {{ number_format($record->accountingEntryPivot->accountingEntry->tot_debit, $record->currency->decimal_places, ",", ".") }}
                </td>
                <td style="font-weight:bold;text-align: right;border-top:solid 1px #000;">
                    {{ number_format($record->accountingEntryPivot->accountingEntry->tot_assets, $record->currency->decimal_places, ",", ".") }}
                </td>
            </tr>
        </tbody>
    </table>
@endif

<br>
<hr>
&#160;
<br>
<h5 style="text-align: center">DATOS DE LOS RESPONSABLES</h5>
&#160;
<br>
<table>
    <tbody>
        <tr>
            <td>Preparado por: ______________________________________</td>
            <td>Autorizado por: _____________________________________</td>
        </tr>
        <br><br>
        <tr>
            <td>Revisado por: _______________________________________</td>
            <td>Presidencia: ________________________________________</td>
        </tr>
    </tbody>
</table>