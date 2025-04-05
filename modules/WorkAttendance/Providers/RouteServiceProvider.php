<?php

namespace Modules\WorkAttendance\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

/**
 * @class RouteServiceProvider
 * @brief Define la configuración del provvedor de las rutas del módulo
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * El namespace del módulo que se debe asumir al generar las URL.
     *
     * @var string
     */
    protected $moduleNamespace = '';

    /**
     * Define las rutas del módulo.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();
    }

    /**
     * Define las rutas "web" del módulo.
     *
     * Todas estas rutas reciben el estado de sesión, protección CSRF, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->moduleNamespace)
            ->group(module_path('WorkAttendance', '/Routes/web.php'));
    }

    /**
     * Define las rutas "api" del módulo.
     *
     * Estas rutas son típicamente sin estado.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->moduleNamespace)
            ->group(module_path('WorkAttendance', '/Routes/api.php'));
    }
}
