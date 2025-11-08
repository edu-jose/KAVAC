@foreach ($field as $record)
    @if(count($record->payrollChildrens) > 0)
        @php
            $date_staff = $record->payrollStaff?->payrollEmployment?->start_date ?? '';
            $startDate =  $date_staff ? \Carbon\Carbon::parse($date_staff)->format('d/m/Y') : '';
        @endphp
        <p>
            Nombres y apellidos del trabajador: {{ $record->payrollStaff->full_name }} <br>
            Cédula: {{ $record->payrollStaff->id_number }} <br>
            Cargo: {{
                $record->payrollStaff->payrollEmployment->payroll_position->name ??
                $record->payrollStaff->payrollEmployment->payrollPositions[0]['name'] ??
                ''
            }}
            <br>
            Fecha de ingreso: {{ $startDate ?? '' }} <br>
            Departamento: {{ $record->payrollStaff->payrollEmployment->department->name ?? '' }}
        </p>

        <p style="margin-left: 15px; color: #42a4c1;">
            Carga Familiar:
        </p>

        <table cellspacing="0" cellpadding="1" border="1">
            <thead>
                <tr style="background-color: #BDBDBD;">
                    <th style="width: 8.93%;">Nombres</th>
                    <th style="width: 8.93%;">Apellidos</th>
                    <th style="width: 9.82%;">Parentesco</th>
                    <th style="width: 10.71%;">Fecha de Nacimiento</th>
                    <th style="width: 5.36%;">Edad</th>
                    <th style="width: 8.93%;">Cédula</th>
                    <th style="width: 8.93%;">Género</th>
                    <th style="width: 7.14%;">Estudia</th>
                    <th style="width: 9.82%;">Nivel de Escolaridad</th>
                    <th style="width: 9.82%;">Tipo de Beca</th>
                    <th style="width: 11.61%;">Discapacidad</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($record->payrollChildrens as $child)
                    @php
                        $birthdate = \Carbon\Carbon::parse($child->birthdate)->format('d/m/Y');
                    @endphp
                    <tr>
                        <td style="width: 8.93%;">
                            {{ $child->first_name }}
                        </td>
                        <td style="width: 8.93%;">
                            {{ $child->last_name }}
                        </td>
                        <td style="width: 9.82%;">
                            {{ $child->payrollRelationship->name ?? '' }}
                        </td>
                        <td style="width: 10.71%;">
                            {{ $birthdate }}
                        </td>
                        <td style="width: 5.36%;">
                            {{ age($child->birthdate) }}
                        </td>
                        <td style="width: 8.93%;">
                            {{ $child->id_number }}
                        </td>
                        <td style="width: 8.93%;">
                            {{ $child->payrollGender->name ?? '' }}
                        </td>
                        <td style="width: 7.14%;">
                            {{ $child->is_student == 1 ? 'Si' : 'No' }}
                        </td>
                        <td style="width: 9.82%;">
                            {{ $child->payrollSchoolingLevel->name ?? '' }}
                        </td>
                        <td style="width: 9.82%;">
                            {{ $child?->payrollScholarshipType?->name ?? '' }}
                        </td>
                        <td style="width: 11.61%;">
                            {{ $child->payrollDisability->name ?? '' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endforeach