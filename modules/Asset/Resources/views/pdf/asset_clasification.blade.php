<h2 style="font-size: 13rem;" align="center">Reporte por Clasificación de Bienes</h2>
<h2>
    <br>
</h2>
<h4 style="font-size: 10rem;">Institución: {{ $institution->name }}</h4>
<h4 style="font-size: 10rem;">Generado el: {{ Carbon\Carbon::now()->format('d-m-Y') }}</h4>


@if (array_key_exists('serial', $assets[0]['asset_details']))
    <table cellspacing="0" cellpadding="1" border="1">
        <tr>
            <th colspan="1" style="background-color: #BDBDBD;" align="center" valign="middle">Código Interno del bien
            </th>
            <th colspan="1" style="background-color: #BDBDBD;" align="center" valign="middle">Descripción</th>
            <th colspan="1" style="background-color: #BDBDBD;" align="center" valign="middle">Marca</th>
            <th colspan="1" style="background-color: #BDBDBD;" align="center" valign="middle">Modelo</th>
            <th colspan="1" style="background-color: #BDBDBD;" align="center" valign="middle">Color</th>
            <th colspan="1" style="background-color: #BDBDBD;" align="center" valign="middle">Serial</th>
            <th colspan="1" style="background-color: #BDBDBD;" align="center" valign="middle">Estatus de uso</th>
            <th colspan="1" style="background-color: #BDBDBD;" align="center" valign="middle">Dependencia</th>
            <th colspan="1" style="background-color: #BDBDBD;" align="center" valign="middle">Unidad administrativa
            </th>
            <th colspan="1" style="background-color: #BDBDBD;" align="center" valign="middle">Lugar de ubicación</th>
            <th colspan="1" style="background-color: #BDBDBD;" align="center" valign="middle">Valor de adquisición
            </th>
        </tr>
        @foreach ($assets as $fields)
            <tr align="C">
                <td rowspan="1" align="center" valign="middle">
                    {{ $fields->asset_details ? $fields->asset_details['code'] : 'N/P' }}
                </td>
                <td rowspan="1" align="center" valign="middle">
                    {{ $fields->assetSpecificCategory ? $fields->assetSpecificCategory['name'] : 'N/P' }}
                </td>
                <td rowspan="1" align="center" valign="middle">
                    {{ $fields->asset_details ? $fields->asset_details['brand'] : 'N/P' }}
                </td>
                <td rowspan="1" align="center" valign="middle">
                    {{ $fields->asset_details ? $fields->asset_details['model'] : 'N/P' }}
                </td>
                <td rowspan="1" align="center" valign="middle">
                    {{ $fields->color }}
                </td>
                <td rowspan="1" align="center" valign="middle">
                    {{ $fields->asset_details ? $fields->asset_details['serial'] : 'N/P' }}
                </td>
                <td rowspan="1" align="center" valign="middle">
                    {{ $fields->assetStatus ? $fields->assetStatus['name'] : 'N/P' }}
                </td>
                <td rowspan="1" align="center" valign="middle">
                    {{ $fields->assetAsignationAsset &&
                    $fields->assetAsignationAsset->assetAsignation &&
                    $fields->assetAsignationAsset->assetAsignation->payrollStaff &&
                    $fields->assetAsignationAsset->assetAsignation->payrollStaff->payrollEmployment
                        ? $fields->assetAsignationAsset->assetAsignation->payrollStaff->payrollEmployment->department->name
                        : 'N/P' }}
                </td>
                <td rowspan="1" align="center" valign="middle">
                    {{ $fields->department ? $fields->department->name : 'N/P' }}
                </td>
                <td rowspan="1" align="center" valign="middle">
                    {{ $fields->assetAsignationAsset ? $fields->assetAsignationAsset->assetAsignation->location_place : 'N/P' }}
                </td>
                <td rowspan="1" align="center" valign="middle">
                    {{ $fields->acquisition_value ? $fields->acquisition_value : 'N/P' }}
                </td>
            </tr>
        @endforeach
    </table>
@endif
