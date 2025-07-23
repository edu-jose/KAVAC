@if (isset($from_date) && isset($to_date))
<table>
    <thead>
        <tr>
            <td width="100%" style="font-weight: bold;">
                Periodo:
                {{ date_format(new DateTime($from_date), 'd-m-Y') }}
                / {{ date_format(new DateTime($to_date), 'd-m-Y') }}
            </td>
        </tr>
    </thead>
</table>
<table width="35%" cellpadding="4" style="font-size: 8rem">
    <thead style="display: flex;">
        <tr>
            <td width="50%" style="font-weight: bold; flex: 1;">Código grupo de supervisados:</td>
            <td width="75%">
                {{ $code }}
            </td>
        </tr>
        <tr>
            <td width="20%" style="font-weight: bold; flex: 1;">Supervisor:</td>
            <td width="78%">
                {{ $supervisor }}
            </td>
            <td width="20%" style="font-weight: bold; flex: 1;">Aprobador:</td>
            <td width="78%">
                {{ $approver }}
            </td>
        </tr>
    </thead>
    <tr>
        <td>
            &nbsp;
        </td>
    </tr>
</table>
@endif
<table border="1" style="font-size: 7px; width: 100%;">
    <thead>
        <tr align="C">
            <th width="5%" rowspan="3">
                <div style="width: 100%;">
                    <div style="width: 100%;">
                        <span style="text-transform: uppercase;">Ficha</span>
                    </div>
                </div>
            </th>
            <th width="10%" rowspan="3">
                <div style="width: 100%;">
                    <div style="width: 100%;">
                        <span style="text-transform: uppercase;">Trabajador</span>
                    </div>
                </div>
            </th>
            @foreach ($months as $month)
                <th width="{{ (86 / count($totalDays)) * count($daysPerMonth[$month]) }}%"
                    colspan="{{ count($daysPerMonth[$month]) }}">
                    <span style="text-transform: uppercase;">
                        {{ $month }}
                    </span>
                </th>
            @endforeach
        </tr>
        <tr align="C">
            @foreach ($totalDays as $field)
                <th width="{{86/(count($totalDays))}} . '%'">
                    <span>{{ $field['day'] }}</span>
                </th>
            @endforeach
        </tr>
        <tr align="C">
            @foreach ($totalDays as $field)
                <th width="{{86/(count($totalDays))}} . '%'">
                    <span style="text-transform: uppercase;">
                        {{ $field['day_code'] }}
                    </span>
                </th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @php
            $currentPage = 1;
            $pageWidth = 279.4;
            $usableHeight = 115;
            $currentHeight = 0;
            $charWidth = 2;
            $charHeight = 3.3;
            $cellWidth = ($pageWidth * 0.86) / count($daysPerMonth[$month]);
            $cellWidthStaff = $pageWidth * 0.10;
        @endphp

        @foreach ($staffs as $staff)
            @php
                $rowHeight = 0;
                $maxCellCharacters = 0;
                $maxStaffCharacters = 0;

                $maxStaffCharacters = max($maxStaffCharacters, strlen($staff['id_number'] . ' - ' . $staff['name']));

                $totalStaffCharacters = strlen($staff['id_number'] . ' - ' . $staff['name']);
                $charsPerLineStaff = floor($cellWidthStaff / $charWidth);
                $LineCountStaff = ($charsPerLineStaff > 0) ? ceil($totalStaffCharacters / $charsPerLineStaff) : 1;
                $cellStaffHeight = $LineCountStaff * $charHeight;
            @endphp

            @foreach ($totalDays as $fIndex => $field)
                @php
                    $cellContent = '';
                    if (
                        array_key_exists($staff['id'] . '-' . $field['month'] . '-' . $field['day'], $dataSource) &&
                        count($dataSource[$staff['id'] . '-' . $field['month'] . '-' . $field['day']]) > 0
                    ) {
                        $contents = $dataSource[$staff['id'] . '-' . $field['month'] . '-' . $field['day']];
                        foreach ($contents as $index => $selection) {
                            $cellContent .= ($selection['count'] > 1 ? $selection['count'] : '') . $selection['acronym'];
                            $cellContent .= ($index < count($contents) - 1) ? ' / ' : '';
                        }
                    }

                    $maxCellCharacters = max($maxCellCharacters, strlen($cellContent));

                    $totalCharacters = strlen($cellContent);
                    $charsPerLine = floor($cellWidth / $charWidth);
                    $lineCount = ($charsPerLine > 0) ? ceil($totalCharacters / $charsPerLine) : 1;
                    $cellHeight = $lineCount * $charHeight;

                    $rowHeight = max($rowHeight, $cellHeight);
                @endphp
            @endforeach
            @php
                $rowHeight = max($rowHeight, $cellStaffHeight);
            @endphp

            @if ($currentHeight + $rowHeight > $usableHeight)
                <br pagebreak="true" />
                @php
                    $currentHeight = 0;
                    $usableHeight = 135;
                @endphp
            @endif
            @php
                $currentHeight += $rowHeight;
            @endphp

            <tr>
                <td width="5%">
                    <span style="text-transform: uppercase;">
                        {{ ('' != $staff['worksheet_code']) ? $staff['worksheet_code'] : $staff['id_number'] }}
                    </span>
                </td>
                <td width="10%">
                    <span style="text-transform: uppercase;">
                        {{ $staff['id_number'] . ' - ' . $staff['name'] }}
                    </span>
                </td>
                @foreach ($totalDays as $fIndex => $field)
                    @php
                        $cellContent = '';
                        if (
                            array_key_exists($staff['id'] . '-' . $field['month'] . '-' . $field['day'], $dataSource) &&
                            count($dataSource[$staff['id'] . '-' . $field['month'] . '-' . $field['day']]) > 0
                        ) {
                            $contents = $dataSource[$staff['id'] . '-' . $field['month'] . '-' . $field['day']];
                            foreach ($contents as $index => $selection) {
                                $cellContent .= ($selection['count'] > 1 ? $selection['count'] : '') . $selection['acronym'];
                                $cellContent .= ($index < count($contents) - 1) ? ' / ' : '';
                            }
                        }
                    @endphp

                    <td width="{{ 86 / count($totalDays) }}%"
                        class="td-with-border"
                        style="font-size: 6px; width: 100%;">
                        @if (!empty($cellContent))
                            <strong>{{ $cellContent }}</strong>
                        @endif
                    </td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>

<div>
    <table style="font-size: 8rem; margin-top: 100px" cellpadding="3">
        <tr>
            <td>
                &nbsp;
            </td>
        </tr>
        <tr>
            <td>
                &nbsp;
            </td>
        </tr>
        <tr>
            <td>
                &nbsp;
            </td>
        </tr>
        <tr>
            <td>
                &nbsp;
            </td>
        </tr>
        <tr>
            <td>
                &nbsp;
            </td>
        </tr>
        <tr>
            <td>
                &nbsp;
            </td>
        </tr>
        <tr>
            <td align="center" width="30%" style="border-top: solid 1px #000;">
                Elaborado por:
            </td>
            <td width="35%">
                &nbsp;
            </td>
            <td align="center" width="30%" style="border-top: solid 1px #000;">
                Aprobado por:
            </td>
        </tr>
        <tr>
            <td align="center" width="30%">
                {{ $profile_name  }}
            </td>
            <td width="35%">
                &nbsp;
            </td>
            <td align="center" width="30%">
                {{ $approver }}
            </td>
        </tr>
    </table>
</div>