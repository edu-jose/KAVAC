<!DOCTYPE html>
<html>
<body>
	<p>(1) CÓDIGO PRESUPUESTARIO DEL ENTE: {{ $institution->onapre_code }}</p>
	<p>DENOMINACIÓN DEL ENTE: {{ $institution->name }} ( {{ $institution->acronym }} )</p>
	<p>ÓRGANO DE ADSCRIPCIÓN: Ministerio del Poder Popular para Ciencia y Tecnología</p>
	<p>{{ $date }}</p>
	<p>MES: {{ $monthFrom.' - '.$monthTo }}</p>
	<p>CODIGO Y DENOMINACION DE LA CATEGORÍA PRESUPUESTARIA: {{ $budgetCategory }}</p>
	<p>EJECUCIÓN MENSUAL DEL PRESUPUESTO DE EGRESOS POR ACCIONES CENTRALIZADAS</p>
	<p>(En Bolivares)</p>
	<hr>

	<table>
		<thead>
			<tr>
				<td></td>
				<td></td>
				<td></td>
				@foreach ($months as $month)
					<th colspan="14"><h3 align="center" >{{ $month }}</h3></th>
				@endforeach
			</tr>
			<tr>
				@foreach ($headers as $header)
					@if (isset($header['months']))
						@foreach ($months as $monthIndex)
							@foreach ($header['months'] as $month)
								@if (isset($month['childs']))
									@foreach ($month['childs'] as $child)
										<th><h3 align="center" >{{ $child['name'] }}</h3></th>
									@endforeach
								@else
									<th><h3 align="center" >{{ $month['name'] }}</h3></th>
								@endif
							@endforeach
						@endforeach
					@else
						<th><h3 align="center" >{{ $header['name'] }}</h3></th>
					@endif
				@endforeach
				<!-- ... otras columnas ... -->
			</tr>
		</thead>
		<tbody>
			@foreach ($records as $record)
				<tr>
					<td>{{ $record['account_code'] }}</td>
					<td>{{ $record['account_denomination'] }}</td>
					<td>{{ number_format((float)$record['approved_budget'], 2, ',', '.') }}</td>
					@foreach ($record['months'] as $month)
						<td>{{ number_format((float)$month['month_modifications'], 2, ',', '.') }}</td>
						<td>{{ number_format((float)$month['budget_modifications'], 2, ',', '.') }}</td>
						<td>{{ number_format((float)$month['month_programmed'], 2, ',', '.') }}</td>
						<td>{{ number_format((float)$month['month_compromised'], 2, ',', '.') }}</td>
						<td>{{ number_format((float)$month['month_caused'], 2, ',', '.') }}</td>
						<td>{{ number_format((float)$month['month_paid'], 2, ',', '.') }}</td>
						<td>{{ number_format((float)$month['absolute_variation_caused_vs_programmed'], 2, ',', '.') }}</td>
						<td>{{ number_format((float)$month['accumulated_programmed'], 2, ',', '.') }}</td>
						<td>{{ number_format((float)$month['accumulated_compromised'], 2, ',', '.') }}</td>
						<td>{{ number_format((float)$month['accumulated_caused'], 2, ',', '.') }}</td>
						<td>{{ number_format((float)$month['accumulated_paid'], 2, ',', '.') }}</td>
						<td>{{ number_format((float)$month['accumulated_absolute_variation_caused_vs_programmed'], 2, ',', '.') }}</td>
						<td>{{ number_format((float)$month['budgetary_availability_compromised'], 2, ',', '.') }}</td>
						<td>{{ number_format((float)$month['budgetary_availability_caused'], 2, ',', '.') }}</td>
					@endforeach
				</tr>
			@endforeach
		</tbody>	
	</table>
</body>
</html>