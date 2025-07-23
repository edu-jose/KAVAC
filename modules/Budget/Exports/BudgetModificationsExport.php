<?php

namespace Modules\Budget\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

/**
 * @class BudgetModificationsExport
 * @brief Exporta datos de las modificaciones presupuestarias
 *
 * Gestiona la exportación de datos de las modificaciones presupuestarias
 *
 * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetModificationsExport implements FromView
{
    /**
     * Lista de datos a exportar
     *
     * @var array $data
     */
    protected $data;

    /**
     * Crea una nueva instancia de la clase
     *
     * @return array
     */
    public function __construct(array $view_data)
    {
        $this->data = $view_data;
    }

    /**
     * Muestra el reporte del registro solicitado
     *
     * @throws \Exception
     *
     * @return \Illuminate\View\View
     */
    public function view(): View
    {
        return view('budget::pdf.modifications', [
            'records'        => $this->data['records'],
            'institution'    => $this->data['institution'],
            'currency' => $this->data['currency'],
            'documentStatus'    => $this->data['documentStatus'],
            'modification_accounts'    => $this->data['modification_accounts'],
            'initialDate'    => $this->data['initialDate'],
            'finalDate'      => $this->data['finalDate'],
            'typeReport'        => $this->data['typeReport'],
        ]);

        throw new \Exception('No se encontró una vista válida para retornar');
    }
}
