@php
    $currency = get_default_currency();
@endphp
<h4><b>Datos del Organismo</b></h4>
<hr>
<table cellspacing="0" cellpadding="1" border="0">
    <tr>
        <th><b>R.I.F.:</b> {{ $field[0]['institution']['rif'] }}</th>
    </tr>
    <tr>
        <th><b>Nombre:</b> {{ $field[0]['institution']['name'] }}</th>
    </tr>
</table>
<br/>
<h4><b>Datos del Trabajador</b></h4>
<hr>
<table cellspacing="0" cellpadding="1" border="0">
    <tr>
        <th><b>Nombre y Apellido:</b> {{ $field[0]['full_name'] }}</th>
    </tr>
    <tr>
        <th><b>C.I.:</b> {{ $field[0]['id_number'] }}</th>
    </tr>
    <tr>
        <th><b>Fecha de Ingreso:</b> {{ $field[0]['start_date'] }}</th>
    </tr>
</table>
<br/>
<h4><b>Datos históricos de Cargos</b></h4>
<hr>
<table cellspacing="0" cellpadding="1" border="0">
    <tr>
        <th><b>Período</b></th>
        <th><b>Cargo</b></th>
        <th><b>Salario</b></th>
    </tr>
    @foreach ($field as $data)
        <tr>
            <td>{{ $data['position_start_date'] }} - {{ $data['position_end_date'] }}</td>
            <td>{{ $data['position'] }}</td>
            <td>{{ $currency->symbol }} {{ number_format($data['total_salary'], 2, ",", ".") }}</td>
        </tr>
    @endforeach
</table>
