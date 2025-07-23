<table style="font-size: 8rem;" cellpadding="4" width="30%">
    <tbody>
        @if ($documentStatus != 'Todos')
        <tr>
            <td style="font-weight: bold;">Estatus:</td>
            <td width="175%" style="color: {{ $documentStatus->color }};">
                {{  $documentStatus->name }}
            </td>
        </tr>
        @else
        <tr>
            <td style="font-weight: bold;">Estatus:</td>
            <td width="175%">
                {{  $documentStatus }}
            </td>
        </tr>
        @endif
        <tr>
            <td style="font-weight: bold;">Institución:</td>
            <td width="175%">
                {{ $institution['name'] }}
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Fecha Desde:</td>
            <td width="175%">
                {{ date_format(new DateTime($initialDate), 'd-m-Y') }}
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Fecha Hasta:</td>
            <td width="175%">
                {{ date_format(new DateTime($finalDate), 'd-m-Y') }}
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Moneda:</td>
            <td width="175%">
                {{ $currency['symbol'] . ' - ' . $currency['name'] }}
            </td>
        </tr>
    </tbody>
</table>

<table style="font-size: 7rem;" cellpadding="10" cellspacing="10" align="center">
    <tr>
        <th style="font-weight: bold;">
            {{ 'CUENTAS PRESUPUESTARIAS' }}
        </th>
    </tr>
</table>
@if ($typeReport == 'C' || $typeReport == 'R')
    <table style="font-size: 8rem;" cellpadding="4" cellspacing="0" align="center">
        <thead>
            <tr>
                <th style="border: solid 1px #000; font-weight: bold; background-color: #D3D3D3;">Fecha de la modificación</th>
                <th style="border: solid 1px #000; font-weight: bold; background-color: #D3D3D3;">Acción específica</th>
                <th style="border: solid 1px #000; font-weight: bold; background-color: #D3D3D3;">Cuenta</th>
                <th style="border: solid 1px #000; font-weight: bold; background-color: #D3D3D3;">Descripción</th>
                <th style="border: solid 1px #000; font-weight: bold; background-color: #D3D3D3;">Monto</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $record)
                @foreach ($record?->budgetModificationAccounts as $budgetAccount)
                    <tr>
                        <td style="border: solid 1px #808080;">
                            {{ date_format($record->approved_at, "d/m/Y") }}
                        </td>
                        <td style="border: solid 1px #808080;">
                            {{
                                $budgetAccount['budgetSubSpecificFormulation']['specificAction']['code'] . ' | ' .
                                $budgetAccount['budgetSubSpecificFormulation']['specificAction']['name']
                            }}
                        </td>
                        <td style="border: solid 1px #808080;">
                            {{ $budgetAccount['budgetAccount']['code'] }}
                        </td>
                        <td style="border: solid 1px #808080;">
                            {{ $budgetAccount['budgetAccount']['denomination'] }}
                        </td>
                        <td style="border: solid 1px #808080;">
                            {{ number_format($budgetAccount['amount'], 2, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            @endforeach
        <tr>
            <td colspan="4" style="border: solid 1px #000; font-weight: bold; background-color: #D3D3D3;" align="right">
                <strong>
                    TOTAL {{ $currency['symbol'] . '.' }}
                </strong>
            </td>
            <td class="text-right" style="border: solid 1px #808080;">
                <strong>
                    @php
                        $total = 0;
                        foreach ($records as $record) {
                            foreach ($record->budgetModificationAccounts as $budgetAccount) {
                                $total += $budgetAccount->amount;
                            }
                        }
                    @endphp
                    {{ number_format($total, 2, ',', '.') }}
                </strong>
            </td>
        </tr>
        </tbody>
    </table>
@else
<table style="font-size: 7rem;" cellpadding="4" cellspacing="0" align="center">
    <tr>
        <th style="width:318; border: solid 1px #000; font-weight: bold; background-color: #D3D3D3;">
            CUENTA CEDENTES
        </th>
        <th style="width:254.4; border: solid 1px #000; font-weight: bold; background-color: #D3D3D3;">
            CUENTA A ACREDITAR
        </th>
    </tr>
</table>

<table style="font-size: 8rem;" cellpadding="4" cellspacing="0" align="center">
    <thead>
        <tr>
            <th style="border: solid 1px #000; font-weight: bold; background-color: #D3D3D3;">Fecha de la modificación</th>
            <th style="border: solid 1px #000; font-weight: bold; background-color: #D3D3D3;">Acción específica</th>
            <th style="border: solid 1px #000; font-weight: bold; background-color: #D3D3D3;">Cuenta</th>
            <th style="border: solid 1px #000; font-weight: bold; background-color: #D3D3D3;">Descripción</th>
            <th style="border: solid 1px #000; font-weight: bold; background-color: #D3D3D3;">Monto</th>
            <th style="border: solid 1px #000; font-weight: bold; background-color: #D3D3D3;">Acción específica</th>
            <th style="border: solid 1px #000; font-weight: bold; background-color: #D3D3D3;">Cuenta</th>
            <th style="border: solid 1px #000; font-weight: bold; background-color: #D3D3D3;">Descripción</th>
            <th style="border: solid 1px #000; font-weight: bold; background-color: #D3D3D3;">Monto</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($modification_accounts as $budgetAccount)
            <tr>
                <td style="border: solid 1px #808080;">
                    {{ date_format($budgetAccount['approved_at'], "d/m/Y") }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $budgetAccount['from_spac_description'] }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $budgetAccount['from_code'] }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $budgetAccount['from_description'] }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ number_format($budgetAccount['from_amount'], 2, ',', '.') }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $budgetAccount['to_spac_description'] }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $budgetAccount['to_code'] }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $budgetAccount['to_description'] }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ number_format($budgetAccount['to_amount'], 2, ',', '.') }}
                </td>
            </tr>
        @endforeach
        <tr>
            <td colspan="4" style="border: solid 1px #000; font-weight: bold; background-color: #D3D3D3;" align="right">
                <strong>
                    TOTAL {{ $currency['symbol'] . '.' }}
                </strong>
            </td>
            <td class="text-right" style="border: solid 1px #808080;">
                <strong>
                    @php
                        $totalFrom = 0;
                        $totalTo = 0;
                        foreach ($modification_accounts as $budgetAccount) {
                            $totalFrom += $budgetAccount['from_amount'];
                            $totalTo += $budgetAccount['to_amount'];
                        }
                    @endphp
                    {{ number_format($totalFrom, 2, ',', '.') }}
                </strong>
            </td>
            <td colspan="3" style="border: solid 1px #000; font-weight: bold; background-color: #D3D3D3;" align="right">
                <strong>
                    TOTAL {{ $currency['symbol'] . '.' }}
                </strong>
            </td>
            <td class="text-right" style="border: solid 1px #808080;">
                <strong>
                    {{ number_format($totalTo, 2, ',', '.') }}
                </strong>
            </td>
        </tr>
    </tbody>
</table>
@endif
