@php
    $count_total_tasks = 0;
    $count_total_tasks_done = 0;
@endphp
@foreach ($field["workers"] as $key => $record)
    @if (count($tasks[$key]) > 0)
        @php
            $startDateProject = \Carbon\Carbon::parse($field["project"]->start_date)->format("d/m/Y");
            $endDateProject = \Carbon\Carbon::parse($field["project"]->end_date)->format("d/m/Y");
        @endphp
        <h3>{{ $record["first_name"] . " " . $record["last_name"] }}</h3>
        <br><br><br><br>
        <table cellspacing="0" cellpadding="1" border="1">
            <tr style="background-color: #BDBDBD;">
                <th style="text-align: center;" colspan="2">
                    Informacion personal
                </th>
            </tr>
            <tbody>
                <tr>
                    <td rowspan="1">Nombres y apellidos del trabajador: </td>
                    <td rowspan="1">{{ $record["first_name"] . " " . $record["last_name"] }}</td>
                </tr>
                <tr>
                    <td rowspan="1">Cédula: </td>
                    <td rowspan="1">{{ $record["id_number"] }}</td>
                </tr>
            </tbody>
        </table>
        <br><br><br><br>
        <table cellspacing="0" cellpadding="1" border="1">
            <tr style="background-color: #BDBDBD;">
                <th style="text-align: center;" colspan="4">
                    Informacion del {{ $field['report_type'] }}
                </th>
            </tr>
            <tr style="background-color: #BDBDBD;">
                <th style="text-align: center;" colspan="2">
                    Nombre de {{ $field['report_type'] }}
                </th>
                <th style="text-align: center;" colspan="2">
                    {{ $field["project"]->name }}
                </th>
            </tr>
            <tbody>
                <tr>
                    <td rowspan="1">Fecha de inicio</td>
                    <td rowspan="1">{{ $startDateProject }}</td>
                    <td rowspan="1">Fecha final</td>
                    <td rowspan="1">{{ $endDateProject }}</td>
                </tr>
            </tbody>
        </table>
        <br><br><br><br>
        <table cellspacing="0" cellpadding="1" border="1">
            <thead>
                <tr style="background-color: #BDBDBD;">
                    <th style="text-align: center;" colspan="10">Lista de tareas</th>
                </tr>
                <tr style="background-color: #BDBDBD;">
                    <th colspan="1">Número</th>
                    <th colspan="1">Nombre</th>
                    <th colspan="1">Descripcion</th>
                    <th colspan="1">Fecha asignada</th>
                    <th colspan="1">Fecha de entrega</th>
                    <th colspan="1">Peso</th>
                    <th colspan="1">% de avance</th>
                    <th colspan="1">Dias de atraso</th>
                    <th colspan="1">Estado de la actividad</th>
                    <th colspan="1">Completada?</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $count = 0;
                    $count_done = 0;

                @endphp
                @foreach ($tasks[$key] as $id => $task)
                    @php
                        // Obtener dias de atraso
                        $endDateTask = \Carbon\Carbon::parse($task->end_date);
                        $newEndDateTask = $task->new_end_date ? \Carbon\Carbon::parse($task->new_end_date) : null;
                        $late_days = $newEndDateTask ? \Carbon\Carbon::today()->diffInDays($newEndDateTask) : \Carbon\Carbon::today()->diffInDays($endDateTask);
                        if ($task->completed) {
                            $late_days = $newEndDateTask ? $endDateTask->diffInDays($newEndDateTask) : 0;
                        }
                        // Obtener fecha de inicio y fechas finales de tareas
                        $startDateTask = \Carbon\Carbon::parse($task->start_date)->format("d/m/Y");
                        $endDateTask = \Carbon\Carbon::parse($task->end_date)->format("d/m/Y");
                        $newEndDateTask = $task->new_end_date ? \Carbon\Carbon::parse($task->new_end_date)->format("d/m/Y") : null;
                        ++$count;
                        ++$count_total_tasks;

                        if ($task->completed) {
                            ++$count_done;
                            ++$count_total_tasks_done;
                        }
                    @endphp
                <tr>
                    <td>
                        {{ $id + 1 }}
                    </td>
                    <td>
                        {{ $task->name }}
                    </td>
                    <td>
                        {{ $task->description ? strip_tags($task->description) : 'No definido' }}
                    </td>
                    <td>
                        {{ $startDateTask }}
                    </td>
                    <td>
                        {{ $newEndDateTask ? $newEndDateTask : $endDateTask }}
                    </td>
                    <td>
                        {{ $task->weight }}
                    </td>
                    <td>
                        {{ $task->percentage }}
                    </td>
                    <td>
                        {{ $late_days }}
                    </td>
                    <td>
                        {{ $statuses[$key][$id] }}
                    </td>
                    <td>
                        {{ $task->completed ? 'SI' : 'NO' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <br><br>
        <table cellspacing="0" cellpadding="1" border="1">
            <tr>
                <th style="background-color: #BDBDBD; text-align: center;" colspan="2">Totales del trabajador</th>
            </tr>
            <tbody>
                <tr>
                    <td rowspan="1" style="background-color: #BDBDBD; text-align: center;">Total de tareas asignadas</td>
                    <td rowspan="1" style="text-align: center;">{{ $count }}</td>
                </tr>
                <tr>
                    <td rowspan="1" style="background-color: #BDBDBD; text-align: center;">Total de tareas terminadas</td>
                    <td rowspan="1" style="text-align: center;">{{ $count_done }}</td>
                </tr>
                <tr>
                    <td rowspan="1" style="background-color: #BDBDBD; text-align: center;">Rendimiento</td>
                    <td rowspan="1" style="text-align: center;">{{ $count_done * 100 / $count }} %</td>
                </tr>
            </tbody>
        </table>
    @endif
@endforeach
<br><br>
<div class="row">
    <table cellspacing="0" cellpadding="1" border="1">
        <tr>
            <th style="background-color: #BDBDBD; text-align: center;" colspan="2">Estados de las tareas</th>
        </tr>
        <tbody>
            @foreach ($count_statuses as $name => $value)
                <tr>
                    <td rowspan="1" style="background-color: #BDBDBD; text-align: center;">{{ $name }}</td>
                    <td rowspan="1" style="text-align: center;">{{ $value }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <br><br>
    <table cellspacing="0" cellpadding="1" border="1">
        <tr>
            <th style="background-color: #BDBDBD; text-align: center;" colspan="2">Totales</th>
        </tr>
        <tbody>
            <tr>
                <td rowspan="1" style="background-color: #BDBDBD; text-align: center;">Total de tareas asignadas</td>
                <td rowspan="1" style="text-align: center;">{{ $count_total_tasks }}</td>
            </tr>
            <tr>
                <td rowspan="1" style="background-color: #BDBDBD; text-align: center;">Total de tareas terminadas</td>
                <td rowspan="1" style="text-align: center;">{{ $count_total_tasks_done }}</td>
            </tr>
            <tr>
                <td rowspan="1" style="background-color: #BDBDBD; text-align: center;">Rendimiento</td>
                <td rowspan="1" style="text-align: center;">{{ $count_total_tasks_done * 100 / $count_total_tasks }} %</td>
            </tr>
        </tbody>
    </table>
</div>