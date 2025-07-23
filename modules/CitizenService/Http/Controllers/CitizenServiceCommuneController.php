<?php

namespace Modules\CitizenService\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * @class CitizenServiceCommuneController
 * @brief Controlador para la gestión de las comunas
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceCommuneController extends Controller
{
    /**
     * Muestra el listado de comunas
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    Renderable
     */
    public function index()
    {
        return view('citizenservice::index');
    }

    /**
     * Muestra el formulario para crear una nueva comuna
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    Renderable
     */
    public function create()
    {
        return view('citizenservice::create');
    }

    /**
     * Registra una nueva comuna
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     object    Request    $request    Objeto con información de la petición
     *
     * @return    void
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Muestra el detalle de una comuna
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable
     */
    public function show($id)
    {
        return view('citizenservice::show');
    }

    /**
     * Muestra el formulario para editar una comuna
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable
     */
    public function edit($id)
    {
        return view('citizenservice::edit');
    }

    /**
     * Actualiza una comuna existente
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    void
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Elimina una comuna existente
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    void
     */
    public function destroy($id)
    {
        //
    }
}
