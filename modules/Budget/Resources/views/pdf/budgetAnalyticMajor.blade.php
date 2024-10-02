<table width="100%" cellpadding="4" style="font-size: 8rem">
    <tbody>
        <tr>
            <td width="25%" style="font-weight: bold;">Expresado en:</td>
            <td width="75%">{{ $currencySymbol }}</td>
        </tr>
        <tr>
            <td width="25%" style="font-weight: bold;">Institución:</td>
            <td width="75%">{{ $institution['name'] }}</td>
        </tr>
        <tr>
            <td width="25%" style="font-weight: bold;">Año Fiscal:</td>
            <td width="75%">{{ $fiscal_year }}</td>
        </tr>
        <tr>
            <td width="25%" style="font-weight: bold;">Proyecto / Acción Centralizada:</td>
            <td width="75%">{{ $records[0][1]['specificAction']['specificable']['name'] }}
            </td>
        </tr>
        <tr>
            <td width="25%" style="font-weight: bold;">Código de Proyecto / Acción Centralizada:</td>
            <td width="75%">{{ $records[0][1]['specificAction']['specificable']['code'] }}
            </td>
        </tr>
        <tr>
            <td width="25%" style="font-weight: bold;">Generado por:</td>
            @php
                $analist_name = isset($profile) ? $profile->first_name . ' ' . $profile->last_name : '';
            @endphp
            <td width="75%">{{ $analist_name }}</td>
        </tr>
    </tbody>
</table>

<table cellspacing="0" cellpadding="2" style="font-size: 7rem;" align="center">
    @php
        $total_programmed = 0;
        $total_compromised = 0;
        $total_amount_available = 0;
        $increment = 0;
        $decrement = 0;
        $current = 0;
        $caused = 0;
        $paid = 0;
        $real = 0;
        $count = 1;
    @endphp
    @foreach ($records as $budgetAccounts)
        <tr>
            <th colspan="12" rowspan="1" scope="rowgroup" style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">
                {{ $budgetAccounts['specific_action_name'] }}
            </th>
        </tr>

        @if (count($budgetAccounts[0]) < 0)
            @php
                break;
            @endphp
        @endif
        @php
            usort($budgetAccounts[0], function ($budgetItemOne, $budgetItemTwo) {
                $codeOne = str_replace('.', '', $budgetItemOne->budgetAccount->code ?? $budgetItemOne->code);
                $codeTwo = str_replace('.', '', $budgetItemTwo->budgetAccount->code ?? $budgetItemTwo->code);

                return $codeOne > $codeTwo;
            });
        @endphp
        @if ($count == 1)
           <thead>
               <tr>
                   <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Fecha</th>
                   <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Código</th>
                   <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Denominación</th>
                   <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Detalle</th>
                   <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Asignado</th>
                   <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Aumento</th>
                   <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Disminución</th>
                   <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Modificado</th>
                   <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Comprometido</th>
                   <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Causado</th>
                   <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Pagado</th>
                   <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Disponible</th>
               </tr>
           </thead>
        @endif

        @foreach ($budgetAccounts[0] as $budgetAccount)
            @php
                $compromised_des = join(',', $budgetAccount['compromised_descriptions'] ?? []);
                $increment_des = $budgetAccount['increment_descriptions'] ?? '';
                $decrement_des = $budgetAccount['decrement_descriptions'] ?? '';
                $specific = $budgetAccount->specific ?? $budgetAccount->budgetAccount->specific;
                $styles = $specific === '00' ? 'font-weight: bold;' : '';
            @endphp
            @if ($budgetAccount['self_amount'] > 0 || $budgetAccount['self_available'] > 0)
            <tbody>
                <tr>
                    <td style="border: solid 1px #808080; {{ $styles }}">
                        {{ date_format(new DateTime($budgetAccount['date'] ?? $budgetAccount['created_at']), 'd-m-Y') }}</td>

                    <td style="border: solid 1px #808080; {{ $styles }}">
                        {{ $budgetAccount['budgetAccount']['code'] ?? $budgetAccount['code'] }}</td>

                    <td style="border: solid 1px #808080; {{ $styles }}">
                        {{ $budgetAccount['budgetAccount']['denomination'] ?? $budgetAccount['denomination'] }}</td>

                    <td style="border: solid 1px #808080; {{ $styles }}">
                        {!! $increment_des . "\r" . $decrement_des . "\r" . $compromised_des !!}
                    </td>

                    <td style="border: solid 1px #808080; {{ $styles }}">
                        {{ number_format($budgetAccount['self_amount'], 2, ',', '.') }}</td>

                    <td style="border: solid 1px #808080; {{ $styles }}">
                        {{ number_format($budgetAccount['increment_total'], 2, ',', '.') }}</td>

                    <td style="border: solid 1px #808080; {{ $styles }}">
                        {{ number_format($budgetAccount['decrement_total'], 2, ',', '.') }}</td>

                    <td style="border: solid 1px #808080; {{ $styles }}">
                        {{ number_format($budgetAccount['current'], 2, ',', '.') }}
                    </td>

                    <td style="border: solid 1px #808080; {{ $styles }}">
                        {{ number_format($budgetAccount['compromised_total'], 2, ',', '.') }}</td>

                    <td style="border: solid 1px #808080; {{ $styles }}">
                        {{ number_format($budgetAccount['compromised_caused'], 2, ',', '.') }}</td>

                    <td style="border: solid 1px #808080; {{ $styles }}">
                        {{ number_format($budgetAccount['compromised_paid'], 2, ',', '.') }}</td>

                    <td style="border: solid 1px #808080; {{ $styles }}">
                        {{ number_format($budgetAccount['self_available'], 2, ',', '.') }}</td>
                </tr>
            @endif
            @if (isset($budgetAccount['modifications']) && count($budgetAccount['modifications']) > 0)
                @foreach ($budgetAccount['modifications'] as $modification)
                    <tr>
                        <td style="border: solid 1px #808080; {{ $styles }}">
                            {{ date_format(new DateTime($modification['date']), 'd-m-Y') }}</td>

                        <td style="border: solid 1px #808080; {{ $styles }}">
                            {{ $budgetAccount['budgetAccount']['code'] ?? $budgetAccount['code'] }}</td>

                        <td style="border: solid 1px #808080; {{ $styles }}">
                            {{ $budgetAccount['budgetAccount']['denomination'] ?? $budgetAccount['denomination'] }}</td>

                        <td style="border: solid 1px #808080; {{ $styles }}">
                            {!! $modification['increment_descriptions'] ?? $modification['decrement_descriptions'] !!}
                        </td>

                        <td style="border: solid 1px #808080; {{ $styles }}">
                            {{ number_format(0, 2, ',', '.') }}</td>

                        <td style="border: solid 1px #808080; {{ $styles }}">
                            {{ number_format($modification['increment'] ?? 0, 2, ',', '.') }}</td>

                        <td style="border: solid 1px #808080; {{ $styles }}">
                            {{ number_format($modification['decrement'] ?? 0, 2, ',', '.') }}</td>

                        <td style="border: solid 1px #808080; {{ $styles }}">
                            {{ number_format($modification['current'], 2, ',', '.') }}
                        </td>

                        <td style="border: solid 1px #808080; {{ $styles }}">
                            {{ number_format($modification['compromised'] ?? 0, 2, ',', '.') }}</td>

                        <td style="border: solid 1px #808080; {{ $styles }}">
                            {{ number_format($modification['caused'], 2, ',', '.') }}</td>

                        <td style="border: solid 1px #808080; {{ $styles }}">
                            {{ number_format($modification['paid'], 2, ',', '.') }}</td>

                        <td style="border: solid 1px #808080; {{ $styles }}">
                            {{ number_format($modification['self_available'], 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            @endif

            @php
                $item = $budgetAccount->budgetAccount->item ?? $budgetAccount->item;
                if ($item === '00') {
                    $real += $budgetAccount['total_real_amount'];
                    $total_programmed += $budgetAccount['self_amount'];
                    $current += $budgetAccount['current'];
                    $total_compromised += $budgetAccount['compromised_total'];
                    $caused += $budgetAccount['compromised_caused'];
                    $paid += $budgetAccount['compromised_paid'];
                    $total_amount_available += $budgetAccount['self_available'];
                    $increment += $budgetAccount['increment_total'];
                    $decrement += $budgetAccount['decrement_total'];
                }
                $count++;
            @endphp
        @endforeach
    @endforeach
    </tbody>
</table>

<table cellspacing="0" cellpadding="1" border="1" style="font-weight: bold; font-size: 8rem">
    <tr>
        <td style="font-size: 8rem; border-bottom: 1px solid #999;" align="Left" width="8.3%">
            Total
        </td>
        <td style="font-size: 8rem; border-bottom: 1px solid #999;" width="8.6%">
        </td>
        <td style="font-size: 8rem; border-bottom: 1px solid #999;" width="8.4%">
        </td>
        <td style="font-size: 8rem; border-bottom: 1px solid #999;" align="center" width="8.3%">
        </td>
        <td style="font-size: 8rem; border-bottom: 1px solid #999; " align="center" width="8.3%">
            {{ number_format($total_programmed, 2, ',', '.') }}
        </td>
        <td style="font-size: 8rem; border-bottom: 1px solid #999;" align="center" width="8.3%">
            {{ number_format($increment, 2, ',', '.') }}
        </td>
        <td style="font-size: 8rem; border-bottom: 1px solid #999;" align="center" width="8.3%">
            {{ number_format($decrement, 2, ',', '.') }}
        </td>
        <td style="font-size: 8rem; border-bottom: 1px solid #999;" align="center" width="8.3%">
            {{ number_format($current, 2, ',', '.') }}
        </td>
        <td style="font-size: 8rem; border-bottom: 1px solid #999;" align="center" width="8.3%">
            {{ number_format($total_compromised, 2, ',', '.') }}
        </td>
        <td style="font-size: 8rem; border-bottom: 1px solid #999;" align="center" width="8.3%">
            {{ number_format($caused, 2, ',', '.') }}
        </td>
        <td style="font-size: 8rem; border-bottom: 1px solid #999;" align="center" width="8.3%">
            {{ number_format($paid, 2, ',', '.') }}
        </td>
        <td style="font-size: 8rem; border-bottom: 1px solid #999;" align="center" width="8.3%">
            {{ number_format($total_amount_available, 2, ',', '.') }}
        </td>
    </tr>
</table>

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
