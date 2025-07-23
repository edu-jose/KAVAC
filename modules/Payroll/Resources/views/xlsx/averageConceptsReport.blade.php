<!DOCTYPE html>
<html>
<body>
	<p>{{ $institution->name }}</p>
	<p>Periodo: {{ $period }}</p>
	<br>
	<br>
	<table>
		<thead>
			<tr>
				@foreach ($headers as $header)
                    <th><h3 align="center" >{{ $header }}</h3></th>
				@endforeach
				<!-- ... otras columnas ... -->
			</tr>
		</thead>
		<tbody>
			@foreach ($records as $record)
                <tr>
                    @foreach ($record as $rec)
                        <td>{{ $rec }}</td>
                    @endforeach
                </tr>
				<tr></tr>
			@endforeach
		</tbody>
	</table>
</body>
</html>