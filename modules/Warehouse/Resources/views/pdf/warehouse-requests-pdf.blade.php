<h4 style="font-size: 10rem;">Fecha de registro: {{ date_format(new DateTime($record->request_date), 'd-m-Y') }}</h4>
<h4 style="font-size: 10rem;">Departamento solicitante: {{ $record?->department?->name }}</h4>
@if ($record->budget_specific_action_id != null)
    <h4 style="font-size: 10rem;">Proyecto/Acción centralizada: {{ $record?->budgetSpecificAction?->specificable?->name }}</h4>
    <h4 style="font-size: 10rem;">Acción específica: {{ $record->budgetSpecificAction?->name }}</h4>
@endif
<h4 style="font-size: 10rem;">Motivo de la solicitud: {{ strip_tags(str_replace('&nbsp;', ' ', $record->motive)) }}</h4>
<h4 style="font-size: 10rem;">Estado de la solicitud: {{ $record->state }}</h4>
<h4 style="font-size: 10rem;">Observaciones: {{ strip_tags(str_replace('&nbsp;', ' ', $record->observations)) }}</h4>

<h2>
    <br>
</h2>

<table cellspacing="0" cellpadding="1" border="1">
    <tr align="C" style="background-color: #cfcfcf;">

        <th width="20%">Código</th>
        <th width="20%">Nombre</th>
        <th width="20%">Descripción</th>
        <th width="20%">Cantidad agregada</th>
        <th width="20%">Valor por unidad</th>
    </tr>
    @foreach($record->warehouseInventoryProductRequests as $productRequest)
        <tr>
            <td width="20%"> {{ $productRequest->warehouseInventoryProduct
                ? $productRequest->warehouseInventoryProduct->code
                : $productRequest->code }} </td>
            <td width="20%"> {{ $productRequest->warehouseInventoryProduct
                ? $productRequest->warehouseInventoryProduct->warehouseProduct->name
                : $productRequest->warehouseProduct->name  }} </td>
            <td width="20%"> {{ $productRequest->warehouseInventoryProduct
                ? strip_tags($productRequest->warehouseInventoryProduct->warehouseProduct->description)
                : strip_tags($productRequest->warehouseProduct->description)   }} </td>
            <td width="20%"> {{ $productRequest->quantity . ' ' . $productRequest->warehouseInventoryProduct->warehouseProduct->measurementUnit->acronym }} </td>
            <td width="20%"> {{ $productRequest->warehouseInventoryProduct->unit_value . ' ' . $productRequest->warehouseInventoryProduct->currency->symbol }} </td>
        </tr>
    @endforeach
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
                Entregado por:
            </td>
            <td width="35%">
                &nbsp;
            </td>
            <td align="center" width="30%" style="border-top: solid 1px #000;">
                Recibido por:
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
            <td width="30%">
                &nbsp;
            </td>
            <td align="center" width="35%" style="border-top: solid 1px #000;">
                Jefe de almacén:
            </td>
        </tr>
    </table>
</div>