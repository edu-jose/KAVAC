<table width="100%" cellpadding="4" style="font-size: 8rem">
    <tbody>
        <tr>
            <td width="10%" style="font-weight: bold;">Institución:</td>
            <td width="75%">{{ $institution->name }}</td>
        </tr>
        <tr>
            <td width="12%" style="font-weight: bold;">Expresado en:</td>
            <td width="25%">{{ $currency->description }}</td>
            <td width="10%" style="font-weight: bold;">Año Fiscal:</td>
            <td width="25%">{{ $fiscal_year }}</td>
        </tr>
        <tr>
            <td colspan="2">&nbsp;</td>
        </tr>  
    </tbody>
</table>
<br>
<table cellspacing="0" cellpadding="4" border="1" style="font-size: 7rem;">
    <thead>
        <tr style="border: solid 1px #000;" bgcolor="#D3D3D3" align="center">
            <th width="10%">Fecha</th>
            <th width="10%">Código</th>
            <th width="20%">Nro. Documento de Origen</th>
            <th width="20%">Proveedor / Beneficiario</th>
            <th width="15%">Concepto / Observación</th>
            <th width="10%">Estatus</th>
            <th width="15%">Monto</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($reportData as $index => $report)
        <tr align="center">
            <td style="border: solid 1px #808080;" width="10%">{{ $report->$reportDate->format('d/m/Y') }}</td>
            <td style="border: solid 1px #808080;" width="10%">{{ $report->code }}</td>
            <td style="border: solid 1px #808080;" width="20%"> 
                @if ($report_type == 'execute')
                    @foreach($report->financePayOrders as $payOrd)
                        {{ $payOrd->code }} <br>
                    @endforeach
                    @else
                    {{ $report->documentSourceable->reference ?? $report->documentSourceable->code ?? $report->documentSourceable->name ?? '' }}
                    @endif
            </td>
            <td style="border: solid 1px #808080; text-align: justify;" width="20%">{{ $report->receiver_name }}</td>
            <td style="border: solid 1px #808080; text-align: justify;" width="15%">{{ strip_tags($report->observations) }}</td>
            <td style="border: solid 1px #808080; color:{{ $report->documentStatus->color }}" width="10%">{{ $report->documentStatus->name }}</td>
            <td style="border: solid 1px #808080;" width="15%">{{ number_format($nameDecimalFunction($report->$amountField, $number_decimals), 2, ',', '.') }}</td>
        </tr>
            @endforeach
    </tbody>
</table>