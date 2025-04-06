@php

    function isTotalOrSubtotal($record) {
        if (explode('.', $record['code'])[2] == '0') {
            return true;
        }

        return false;
    }

    function toMonth($date) {
        $months = [
            1 => 'ENERO',
            2 => 'FEBRERO',
            3 => 'MARZO',
            4 => 'ABRIL',
            5 => 'MAYO',
            6 => 'JUNIO',
            7 => 'JULIO',
            8 => 'AGOSTO',
            9 => 'SEPTIEMBRE',
            10 => 'OCTUBRE',
            11 => 'NOVIEMBRE',
            12 => 'DICIEMBRE'
        ];

        return $months[(int) explode('-', $date)[1]];
    }
@endphp

<h3 align="center">
    {{$institution->name}}
</h3>
<p align="center">
    Estado de flujo de efectivo del 01 al {{ explode('-', $endDate)[2] }} de {{ toMonth($endDate) }} de {{ explode('-', $endDate)[0] }}
</p>

<p align="center">
    Expresado en: {{ $currency->symbol }}
</p>

<p align="center">
    Efectivo disponible: {{ number_format($beginning_balance, (int) $currency->decimal_places, ',', '.') }}
</p>

<h2>Actividades Operativas</h2>
<table>
    <thead>
        <tr>
            <th style="width: 13%; font-weight: bold">Código</th>
            <th style="width: 77%; font-weight: bold">Denominación</th>
            <th style="width: 10%; font-weight: bold">Total</th>
        </tr>
    </thead>
    <tbody>
        @php
            $total_operational_activity = 0;
        @endphp
        @foreach ($operational_activity as $key => $b)
            @php
                $total_operational_activity += $b['balance'];
            @endphp

                <tr style="margin-bottom: 5px">
                    <td style="width: 13%; font-size: 8px;">
                        @if (isTotalOrSubtotal($b))
                            <strong>{{ $b['code'] }}</strong>
                        @else
                            {{ $b['code'] }}
                        @endif
                    </td>
                    <td style="width: 77%; font-size: 8px">
                        @if (isTotalOrSubtotal($b))
                            <strong>{{ $b['denomination'] }}</strong>
                        @else
                        {{ $b['denomination'] }}
                        @endif
                    </td>
                    <td style="width: 10%; font-size: 8px">{{ number_format($b['balance'], (int) $currency->decimal_places, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr>
                    <td></td>
                    <td></td>
                    <td style="font-weight: bold">{{ number_format($total_operational_activity, (int) $currency->decimal_places, ',', '.') }}</td>
                </tr>
    </tbody>
</table>

<h2>Actividades de Inversión</h2>
<table>
    <thead>
        <tr>
            <th style="width: 13%; font-weight: bold">Código</th>
            <th style="width: 77%; font-weight: bold">Denominación</th>
            <th style="width: 10%; font-weight: bold">Total</th>
        </tr>
    </thead>
    <tbody>
        @php
            $total_investment_activity = 0;
        @endphp
        @foreach ($investment_activity as $key => $b)
            @php
                $total_investment_activity += $b['balance'];
            @endphp

                <tr style="margin-bottom: 5px">
                    <td style="width: 13%; font-size: 8px;">
                        @if (isTotalOrSubtotal($b))
                            <strong>{{ $b['code'] }}</strong>
                        @else
                            {{ $b['code'] }}
                        @endif
                    </td>
                    <td style="width: 77%; font-size: 8px">
                        @if (isTotalOrSubtotal($b))
                            <strong>{{ $b['denomination'] }}</strong>
                        @else
                        {{ $b['denomination'] }}
                        @endif
                    </td>
                    <td style="width: 10%; font-size: 8px">{{ number_format($b['balance'], (int) $currency->decimal_places, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr>
                    <td></td>
                    <td></td>
                    <td style="font-weight: bold">{{ number_format($total_investment_activity, (int) $currency->decimal_places, ',', '.') }}</td>
                </tr>
    </tbody>
</table>

<h2>Actividades de Financiamiento</h2>
<table>
    <thead>
        <tr>
            <th style="width: 13%; font-weight: bold">Código</th>
            <th style="width: 77%; font-weight: bold">Denominación</th>
            <th style="width: 10%; font-weight: bold">Total</th>
        </tr>
    </thead>
    <tbody>
        @php
            $total_financing_activity = 0;
        @endphp
        @foreach ($financing_activity as $key => $b)
            @php
                $total_financing_activity += $b['balance'];
            @endphp

                <tr style="margin-bottom: 5px">
                    <td style="width: 13%; font-size: 8px;">
                        @if (isTotalOrSubtotal($b))
                            <strong>{{ $b['code'] }}</strong>
                        @else
                            {{ $b['code'] }}
                        @endif
                    </td>
                    <td style="width: 77%; font-size: 8px">
                        @if (isTotalOrSubtotal($b))
                            <strong>{{ $b['denomination'] }}</strong>
                        @else
                        {{ $b['denomination'] }}
                        @endif
                    </td>
                    <td style="width: 10%; font-size: 8px">{{ number_format($b['balance'], (int) $currency->decimal_places, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr>
                    <td></td>
                    <td></td>
                    <td style="font-weight: bold">{{ number_format($total_financing_activity, (int) $currency->decimal_places, ',', '.') }}</td>
                </tr>
    </tbody>
</table>

<h2>RESULTADOS DEL REPORTE</h2>
<table>
    <thead>
        <tr>
            <th style="width: 13%; font-weight: bold"></th>
            <th style="width: 77%; font-weight: bold"></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>FLUJO DE EFECTIVO NETO</td>
            <td>
                {{
                    
                    number_format(($total_operational_activity + $total_investment_activity + $total_financing_activity), (int) $currency->decimal_places, ',', '.')
                }}
            </td>
        </tr>
        <tr>
            <td>(+) SALDO INICIAL</td>
            <td>
                {{
                    number_format($beginning_balance, (int) $currency->decimal_places, ',', '.')
                }}
            </td>
        </tr>
        <tr>
            <td>(=) SALDO FINAL</td>
            <td>
                {{
                    number_format(($total_operational_activity + $total_investment_activity + $total_financing_activity + $beginning_balance), (int) $currency->decimal_places, ',', '.')
                }}
            </td>
        </tr>
    </tbody>
</table>