<style>
    ha1, h2, h3, h4, h5, h6 {
        text-align: center;
    }
    table, th, td {
        border-collapse: collapse;
        padding: 5px;
    }
    .table-employment-data, .table-work-attendance {
        font-size: 8px;
        width:100%
    }
    .table-work-attendance tr th {
        text-align: left;
        font-weight: bold;
    }
    .table-employment-data tr th {
        text-align: left;
        font-weight: bold;
        width:20%;
    }
    .table-work-attendance {
        margin-top: 10px;
    }
    .text-right {
        text-align: right;
    }
    .text-center {
        text-align: center !important;
    }
    .border-top {
        border-top: .5px solid #000;
    }
    .border-bottom {
        border-bottom: .5px solid #000;
    }
</style>
<br>
<h4>Historial de Asistencia</h4>
<table class="table-work-attendance">
    <thead>
        <tr>
            <th class="text-center border-top border-bottom">C.I.</th>
            <th class="text-center border-top border-bottom">Nombres y Apellidos</th>
            <th class="text-center border-top border-bottom">Cargo</th>
            <th class="text-center border-top border-bottom">Día</th>
            <th class="text-center border-top border-bottom">Fecha</th>
            <th class="text-center border-top border-bottom">Hora de Entrada</th>
            <th class="text-center border-top border-bottom">Hora de Salida</th>
            <th class="text-center border-top border-bottom">Total Asistencia</th>
            <th class="text-center border-top border-bottom">% Asistencia</th>
            <th class="text-center border-top border-bottom">% Inasistencia</th>
        </tr>
    </thead>
    <tbody>
        @php
            $totalAttendance = 0;
            $totalAbsence = 0;
        @endphp
        @foreach ($workAttendance as $work)
            @php
                $hours = floor($work->work_time / 60);
                $minutes = $work->work_time % 60;
                $workTime = sprintf('%02d:%02d', $hours, $minutes);
                $totalAttendance += $work->work_percent;
                $totalAbsence += 100 - $work->work_percent;
            @endphp
            <tr>
                <td class="text-center">{{ $work->employee['id_number'] }}</td>
                <td>{{ $work->employee['full_name'] }}</td>
                <td>{{ $work->employee['position'] }}</td>
                <td>{{ $weekDaysName[\Carbon\Carbon::parse($work->date_at)->format('l')] }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($work->date_at)->format('d-m-Y') }}</td>
                <td class="text-center">{{ $work->entry_time ? \Carbon\Carbon::parse($work->entry_time)->format('h:i:s A') : '---' }}</td>
                <td class="text-center">{{ $work->exit_time ? \Carbon\Carbon::parse($work->exit_time)->format('h:i:s A') : '---' }}</td>
                <td class="text-right">{{ $workTime }}</td>
                <td class="text-right">{{ number_format($work->work_percent, 2) }} %</td>
                <td class="text-right">{{ number_format(100 - $work->work_percent, 2) }} %</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="8" class="text-right border-top border-bottom">
                <b>% de Asistencia / % de Inasistencia</b>
            </td>
            <td class="text-right border-top border-bottom">{{ number_format($totalAttendance / $workAttendance->count(), 2) }} %</td>
            <td class="text-right border-top border-bottom">{{ number_format($totalAbsence / $workAttendance->count(), 2) }} %</td>
        </tr>
    </tbody>
</table>
