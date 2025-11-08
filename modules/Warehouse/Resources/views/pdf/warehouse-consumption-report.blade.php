<h2 style="font-size: 13rem;" align="center">Reporte de Consumo de Productos</h2>
<h2>
    <br>
</h2>

<h4 style="font-size: 10rem;">Denominación del ente: {{ $institution['name'] }}</h4>
<h4 style="font-size: 10rem;">Dependencia: {{ $department['name'] }}</h4>
@if (is_null($warehouse))
    <h4 style="font-size: 10rem;">Almacén: Todos</h4>
@else
    <h4 style="font-size: 10rem;">Almacén: {{ $warehouse->name }}</h4>
@endif
<h4 style="font-size: 10rem;">Año Fiscal: {{ $fiscal_year }}</h4>
<h4 style="font-size: 10rem;">Período del Reporte: {{ $fields['from'] }} al {{ $fields['to'] }}</h4>
<h4 style="font-size: 10rem;">Generado el: {{ now()->format('d-m-Y H:i:s') }}</h4>
<br>

<table cellspacing="0" cellpadding="1" border="1">
    @if (is_null($warehouse))
    <tr align="C" style="background-color: #cfcfcf;">
                <th colspan="1">Nombre del Producto</th>
        <th colspan="1">Cantidad Consumida</th>
        <th colspan="1">Unidad de Medida</th>
        <th colspan="1">Almacén</th>
    </tr>
    @else
        <tr  align="C" style="background-color: #cfcfcf;">
                <th colspan="1">Nombre del Producto</th>
        <th colspan="1">Cantidad Consumida</th>
        <th colspan="1">Unidad de Medida</th>
    </tr>
 @endif
    @foreach($fields['items'] as $field)
    @if (is_null($warehouse))
                <tr>
            <td rowspan="1"> {{ $field['product_name'] }} </td>
            <td rowspan="1"> {{ $field['consumed_amount'] }} </td>
            <td rowspan="1"> {{ $field['unit_of_measure'] ?? 'Unidad' }} </td>
            <td rowspan="1"> {{ $field['warehouse'] }} </td>
        </tr>
    @else
                        <tr>
            <td rowspan="1"> {{ $field['product_name'] }} </td>
            <td rowspan="1"> {{ $field['consumed_amount'] }} </td>
            <td rowspan="1"> {{ $field['unit_of_measure'] ?? 'Unidad' }} </td>
        </tr>
    @endif
    @endforeach
</table>