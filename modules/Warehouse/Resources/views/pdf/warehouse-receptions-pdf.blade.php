<h4 style="font-size: 10rem;">Fecha de registro: {{ date_format(new DateTime($record->reception_date), 'd-m-Y') }}</h4>
<h4 style="font-size: 10rem;">Descripción: {{ $record->description }}</h4>
<h4 style="font-size: 10rem;">Almacén: {{ $record->warehouseInstitutionWarehouseEnd->warehouse->name }}</h4>
<h4 style="font-size: 10rem;">Estado de la solicitud: {{ $record->state }}</h4>
<h4 style="font-size: 10rem;">Código: {{ $record?->purchaseDirectHire ? $record?->purchaseDirectHire->code : $record->direct_hire }}</h4>
<h4 style="font-size: 10rem;">Proveedor: {{ $record?->purchaseSupplier ? $record?->purchaseSupplier->name : $record->supplier ?? 'N/A' }}</h4>
<h4 style="font-size: 10rem;">Oberservaciones generales: {{ strip_tags(str_replace('&nbsp;', ' ', $record->general_observations)) }}</h4>

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
    @foreach($record->warehouseInventoryProductMovements as $product)
        <tr>
            <td width="20%"> {{ $product->warehouseInventoryProduct
                ? $product->warehouseInventoryProduct->code
                : $product->code }} </td>
            <td width="20%"> {{ $product->warehouseInventoryProduct
                ? $product->warehouseInventoryProduct->warehouseProduct->name
                : $product->warehouseProduct->name  }} </td>
            <td width="20%"> {{ $product->warehouseInventoryProduct
                ? strip_tags($product->warehouseInventoryProduct->warehouseProduct->description) 
                : strip_tags($product->warehouseProduct->description)   }} </td>
            <td width="20%"> {{ $product->quantity . ' ' . $product->warehouseInventoryProduct->warehouseProduct->measurementUnit->acronym }} </td>
            <td width="20%"> {{ $product->new_value . ' ' . $product->warehouseInventoryProduct->currency->symbol }} </td>
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
