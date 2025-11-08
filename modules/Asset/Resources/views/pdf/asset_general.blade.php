<h2 style="font-size: 13rem;" align="center">Reporte General de Bienes</h2>
<h2>
    <br>
</h2>
<h4 style="font-size: 10rem;">Institución: {{ $institution->name }}</h4>
<h4 style="font-size: 10rem;">Generado el: {{ now()->format('d-m-Y') }}</h4>

<table cellspacing="0" cellpadding="1" border="1">
    <thead>
        <tr>
            <th colspan="1" style="background-color: #BDBDBD;" align="center" valign="middle">Código sigecof</th>
            <th colspan="1" style="background-color: #BDBDBD;" align="center" valign="middle">Descripción</th>
            <th colspan="1" style="background-color: #BDBDBD;" align="center" valign="middle">Cantidad</th>
            <th style="background-color: #BDBDBD;" align="center" valign="middle">Código interno</th>
            <th colspan="1" style="background-color: #BDBDBD;" align="center" valign="middle">Estatus de uso</th>
            <th colspan="1" style="background-color: #BDBDBD;" align="center" valign="middle">Ubicación</th>
        </tr>
    </thead>
    <tbody>
        @php
            $codes = [];
            foreach ($assets as $fields) {
                if ($fields->code_sigecof) {
                    if (!array_key_exists($fields->code_sigecof, $codes)) {
                        $codes[$fields->code_sigecof] = 1;
                    } else {
                        $codes[$fields->code_sigecof] += 1;
                    }
                }
            }

            $sigecof_codes = [];
            foreach ($assets as $fields) {
                if ($fields->code_sigecof) {
                    if (!array_key_exists($fields->code_sigecof, $sigecof_codes)) {
                        $sigecof_codes[$fields->code_sigecof] = $fields;
                    }
                }
            }
        @endphp

        @foreach ($sigecof_codes as $fields)
            <tr align="C">
                <td rowspan="1" align="center" valign="middle">
                    {{ $fields->code_sigecof ? $fields->code_sigecof : '' }}
                </td>
                <td rowspan="1" align="center" valign="middle">
                    {{ $fields->assetSpecificCategory ? $fields->assetSpecificCategory['name'] : '' }}
                </td>
                <td rowspan="1" align="center" valign="middle">
                    {{ $codes[$fields->code_sigecof] }}
                </td>
                <td rowspan="1" align="center" valign="middle">
                    {{ $fields->asset_details ? $fields->asset_details['code'] : '' }}
                </td>
                <td rowspan="1" align="center" valign="middle">
                    {{ $fields?->assetStatus ? $fields->assetStatus->name : '' }}
                </td>
                <td rowspan="1" align="center" valign="middle">
                    {{ $fields?->assetAsignationAsset?->assetAsignation?->location_place
                        ? $fields?->assetAsignationAsset?->assetAsignation?->location_place
                        : '' }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
