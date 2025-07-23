<?php

namespace Modules\CitizenService\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * @class CitizenServiceCommunityInstitutionController
 * @brief Controlador para la gestión de las instituciones de las comunidades
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceCommunityInstitutionController extends Controller
{
    /**
     * Muestra el listado de instituciones de las comunidades
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
     * Muestra el formulario para crear una nueva institucion
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
     * Registra una nueva institución
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
     * Muestra el detalle de una institución
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
     * Muestra el formulario para editar una institución
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
     * Actualiza una institución
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
     * Elimina una institución
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
