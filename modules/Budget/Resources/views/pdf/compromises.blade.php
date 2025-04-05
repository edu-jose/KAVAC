<meta charset="UTF-8">
@php
    $totalByCompromise = 0;
    $total = 0;
@endphp

<table width="100%" cellpadding="4" style="font-size: 10rem">
    <tbody>
        <tr>
            <td width="25%" style="font-weight: bold;">Institución:</td>
            <td width="75%">{{ $institution->name }}</td>
        </tr>
        <tr>
            <td width="25%" style="font-weight: bold;">Expresado en:</td>
            <td width="75%">{{ $currencySymbol }}</td>
        </tr>
        <tr>
            <td width="25%" style="font-weight: bold;">Año Fiscal:</td>
            <td width="75%">{{ $fiscal_year }}</td>
        </tr>
        <tr>
            <td width="25%" style="font-weight: bold;">Generado por:</td>
            <td width="75%">{{ $profile?->name }}</td>
        </tr>
        <tr>
            <td colspan="2">&nbsp;</td>
        </tr>
    </tbody>
</table>

<br>

<table width="100%" cellspacing="0" cellpadding="4" border="1" style="font-size: 7rem; text-align: center;">
    <thead>
        <tr style="border: solid 1px #000;" bgcolor="#D3D3D3" align="center">
            <th align="center" style="font-weight: bold;">Fecha de generación</th>
            <th align="center" style="font-weight: bold;">Código del compromiso</th>
            <th align="center" style="font-weight: bold;">Documento origen</th>
            <th align="center" style="font-weight: bold;">Código de la Acción Específica</th>
            <th align="center" style="font-weight: bold;">Descripción</th>
            <th style="font-weight: bold; text-align: center;">Estatus</th>
            <th style="font-weight: bold; text-align: center;">Monto</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($records as $record)
        <tr>
            <td style="border: solid 1px #808080;">{{ date('d-m-Y', strtotime($record['compromised_at'])) }}</td>
            <td style="border: solid 1px #808080;">
                {{ $record->code }}
            </td>
            <td style="border: solid 1px #808080;">
            {{ $record['document_number'] }}
            </td>
            <td style="border: solid 1px #808080;">
                @foreach ($record->budgetCompromiseDetails as $budgetCompromiseDetail)
                        <!-- @if ($loop->first) -->
                             {{ $budgetCompromiseDetail?->budgetSubSpecificFormulation?->specificAction?->code}} 
                        <!-- @endif -->
                @endforeach
            </td>
            <td style="border: solid 1px #808080;">
            {{ 
        preg_replace('/&[a-zA-Z0-9#]+;/', '',
        strip_tags($record['description']))
        }}
        </td>
            <td style="border: solid 1px #808080;">
            @if ($record->status === 'CAU')
                Causado(a)            
            @elseif ($record->status === 'PA')
                Pagado(a)
            @elseif ($record->status === 'PE')
                Pendiente
            @else
                {{ $record?->documentStatus?->name }}
            @endif
        </td>
            <td style="border: solid 1px #808080;">
            @php
                    $totalByCompromise = 0;
                @endphp
                @if ($record->sourceable_type === 'Modules\Purchase\Models\PurchaseDirectHire')
                @foreach ($record->budgetCompromiseDetails as $budgetCompromiseDetail)
                @php
                    $totalByCompromise += $budgetCompromiseDetail->amount;
      
                    $total +=\Modules\Budget\Facades\CurrencyConverter::convert(
                            $budgetCompromiseDetail->amount,
                            $budgetCompromiseDetail->created_at,
                            $budgetCompromiseDetail['budgetSubSpecificFormulation']['currency'],
                            $currency
                            );
                @endphp
                @endforeach
                @else                @foreach ($record->budgetCompromiseDetails as $budgetCompromiseDetail)
                @php
                    $totalByCompromise += $budgetCompromiseDetail->total;

                    $total += \Modules\Budget\Facades\CurrencyConverter::convert(
                            $budgetCompromiseDetail->total,
                            $budgetCompromiseDetail->created_at,
                            $budgetCompromiseDetail['budgetSubSpecificFormulation']['currency'],
                            $currency
                            );
                @endphp
                @endforeach
                @endif
                {{ number_format(
                        \Modules\Budget\Facades\CurrencyConverter::convert(
                            $totalByCompromise,
                            $budgetCompromiseDetail->created_at,
                            $budgetCompromiseDetail['budgetSubSpecificFormulation']['currency'],
                            $currency
                        ),
                        $budgetCompromiseDetail['budgetSubSpecificFormulation']['currency']['decimal_places'], ",", "."
                    ) }}

            </td>
        </tr>
        @endforeach
        <tr>
            <td style="border-bottom-style: hidden;"></td>
            <td style="border-bottom-style: hidden;"></td>
            <td style="border-bottom-style: hidden;"></td>
            <td style="border-bottom-style: hidden;"></td>
            <td style="border-bottom-style: hidden;"></td>
            <td colspan="1" style="border: solid 1px #808080;">Total</td>
            <td style="border: solid 1px #808080;">{{ number_format($total , 2, ',', '.')}} </td>
        </tr>
    </tbody>
</table>
