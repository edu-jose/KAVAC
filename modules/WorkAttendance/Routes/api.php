<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas para APIs
|--------------------------------------------------------------------------
|
| Aquí es donde puede registrar las rutas API para su aplicación. Estas
| rutas son cargadas por el RouteServiceProvider dentro de un grupo que
| es asignado al grupo "api" middleware.
|
*/
Route::group([
    'middleware' => 'auth:api', 'prefix' => 'work-attendance'
], function () {
    /** Descripción corta de la acción que ejecuta la ruta */
});
