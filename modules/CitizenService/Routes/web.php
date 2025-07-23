<?php

use Illuminate\Support\Facades\Route;
use Modules\CitizenService\Http\Controllers\CitizenServiceController;
use Modules\CitizenService\Http\Controllers\CitizenServiceReportController;
use Modules\CitizenService\Http\Controllers\CitizenServiceRequestController;
use Modules\CitizenService\Http\Controllers\CitizenServiceSettingController;
use Modules\CitizenService\Http\Controllers\CitizenServiceRegisterController;
use Modules\CitizenService\Http\Controllers\CitizenServiceCommunityController;
use Modules\CitizenService\Http\Controllers\CitizenServiceIndicatorController;
use Modules\CitizenService\Http\Controllers\CitizenServiceProcedureController;
use Modules\CitizenService\Http\Controllers\CitizenServiceDepartmentController;
use Modules\CitizenService\Http\Controllers\CitizenServiceEffectTypeController;
use Modules\CitizenService\Http\Controllers\CitizenServiceContactBookController;
use Modules\CitizenService\Http\Controllers\CitizenServiceRequestTeamController;
use Modules\CitizenService\Http\Controllers\CitizenServiceRequestTypeController;
use Modules\CitizenService\Http\Controllers\CitizenServiceRequestCloseController;
use Modules\CitizenService\Http\Controllers\CitizenServiceProcedureTypeController;
use Modules\CitizenService\Http\Controllers\CitizenServiceTransactionTypeController;
use Modules\CitizenService\Http\Controllers\CitizenServiceServedInstitutionController;
use Modules\CitizenService\Http\Controllers\CitizenServiceCommunityProfilingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group([
    'middleware' => ['web', 'auth', 'verified'],
    'prefix' => 'citizenservice',
], function () {
    Route::get(
        '/',
        [CitizenServiceController::class, 'index']
    );
    Route::get(
        'settings',
        [CitizenServiceSettingController::class, 'index']
    )->name('citizenservice.settings.index');
    Route::post(
        'settings',
        [CitizenServiceSettingController::class, 'store']
    )->name('citizenservice.settings.store');

    /* Ruta para solicitudes*/
    Route::resource(
        'requests',
        CitizenServiceRequestController::class,
        ['as' => 'citizenservice', 'only' => ['update']]
    );

    Route::get(
        'requests/create',
        [CitizenServiceRequestController::class, 'create']
    )->name('citizenservice.request.create');
    Route::post(
        'requests',
        [CitizenServiceRequestController::class, 'store']
    )->name('citizenservice.request.store');
    Route::get(
        'requests',
        [CitizenServiceRequestController::class, 'index']
    )->name('citizenservice.request.index');
    Route::get(
        'requests/edit/{request}',
        [CitizenServiceRequestController::class, 'edit']
    )->name('citizenservice.request.edit');
    Route::delete(
        'requests/delete/{request}',
        [CitizenServiceRequestController::class, 'destroy']
    )->name('citizenservice.request.delete');
    Route::get(
        'requests/vue-list',
        [CitizenServiceRequestController::class, 'vueList']
    );
    Route::get(
        'requests/vue-info/{request}',
        [CitizenServiceRequestController::class, 'vueInfo']
    );

    Route::get(
        'requests/vue-pending-list',
        [CitizenServiceRequestController::class, 'vueListPending']
    );

    Route::get(
        'requests/vue-list-closing',
        [CitizenServiceRequestController::class, 'vueListClosing']
    );


    Route::put(
        'requests/request-approved/{id}',
        [CitizenServiceRequestController::class, 'approved']
    );
    Route::put(
        'requests/request-rejected/{id}',
        [CitizenServiceRequestController::class, 'rejected']
    );
    Route::put(
        'requests/request-add-indicator/{id}',
        [CitizenServiceRequestController::class, 'addIndicator']
    );

    /* Ruta para tipos de solicitudes */
    Route::resource(
        'request-types',
        CitizenServiceRequestTypeController::class,
        ['as' => 'citizenservice', 'except' => ['create','edit','show']]
    );
    Route::get(
        'get-request-types',
        [CitizenServiceRequestTypeController::class, 'getRequestTypes']
    );

    /* Rutas para tipos de trámites */
    Route::resource(
        'procedure-types',
        CitizenServiceProcedureTypeController::class,
        [
            'as' => 'citizenservice',
            'except' => ['create','edit','show'],
            'param' => 'citizenServiceProcedureType'
        ]
    );
    /* Rutas para trámites */
    Route::resource(
        'procedures',
        CitizenServiceProcedureController::class,
        [
            'as' => 'citizenservice',
            'except' => ['create','edit','show'],
            'param' => 'citizenServiceProcedure'
        ]
    );

    /* Rutas para comunidades */
    Route::resource(
        'communities',
        CitizenServiceCommunityController::class,
        [
            'as' => 'citizenservice',
            'except' => ['create','edit','show'],
            'param' => 'citizenServiceCommunity'
        ]
    );

    /* Rutas para comunidades */
    Route::resource(
        'institutions',
        CitizenServiceServedInstitutionController::class,
        [
            'as' => 'citizenservice',
            'except' => ['create','edit','show'],
            'param' => 'citizenServiceServedInstitution'
        ]
    );

    /* Ruta para tipo de departamentos */
    Route::resource(
        'departments',
        CitizenServiceDepartmentController::class,
        ['as' => 'citizenservice', 'except' => ['create','edit','show']]
    );
    Route::get(
        'get-departments',
        [CitizenServiceDepartmentController::class, 'getDepartments']
    );

    /* Rutas para subir archivos*/
    Route::resource(
        'request-close',
        CitizenServiceRequestCloseController::class,
        ['as' => 'citizenservice', 'except' => ['create','edit','show']]
    );

    Route::post(
        'requests/validate-document',
        [CitizenServiceRequestCloseController::class, 'store']
    );
    Route::get(
        'get-documents/show/{code}',
        [CitizenServiceRequestCloseController::class, 'show']
    );
    Route::get(
        'get-documents/{id}/{all?}',
        [CitizenServiceRequestCloseController::class, 'getCitizenServiceRequestDocuments']
    );

    /* Rutas para generar reporte */
    Route::get(
        'reports',
        [CitizenServiceReportController::class, 'index']
    )->name('citizenservice.report.index');

    Route::post(
        'reports',
        [CitizenServiceReportController::class, 'store']
    );


    Route::get(
        'reports/request',
        [CitizenServiceReportController::class, 'request']
    )->name('citizenservice.report.request');
    Route::post(
        'reports/request/create',
        [CitizenServiceReportController::class, 'create']
    );
    Route::get(
        'report/show/{code}',
        [CitizenServiceReportController::class, 'show']
    );
    Route::get(
        'reports/search',
        [CitizenServiceReportController::class, 'search']
    );

    /* Rutas para generar registro de actividades */

    Route::resource(
        'registers',
        CitizenServiceRegisterController::class,
        ['as' => 'citizenservice', 'only' => ['update']]
    );

    Route::get(
        'registers/create',
        [CitizenServiceRegisterController::class, 'create']
    )->name('citizenservice.register.create');

    Route::get(
        'register',
        [CitizenServiceRegisterController::class, 'index']
    )->name('citizenservice.register.index');

    Route::post(
        'registers',
        [CitizenServiceRegisterController::class, 'store']
    );

    Route::get(
        'registers/edit/{register}',
        [CitizenServiceRegisterController::class, 'edit']
    )->name('citizenservice.register.edit');

    Route::delete(
        'registers/delete/{register}',
        [CitizenServiceRegisterController::class, 'destroy']
    )->name('citizenservice.register.delete');

    Route::get(
        'registers/vue-list',
        [CitizenServiceRegisterController::class, 'vueList']
    );

    Route::get(
        'registers/vue-info/{register}',
        [CitizenServiceRegisterController::class, 'vueInfo']
    );

    /* Ruta para los tipos de impacto */
    Route::resource(
        'effect-types',
        CitizenServiceEffectTypeController::class,
        ['as' => 'citizenservice', 'except' => ['create','edit','show']]
    );

    /* Ruta para obtener el listado de los tipos de impacto*/
    Route::get(
        'get-effect-types',
        [CitizenServiceEffectTypeController::class, 'getEffectType']
    )->name('citizenservice.effect-types.get');

    /* Ruta para los indicadores */
    Route::resource(
        'indicators',
        CitizenServiceIndicatorController::class,
        ['as' => 'citizenservice', 'except' => ['create','edit','show']]
    );

    Route::get(
        'get-indicators',
        [CitizenServiceIndicatorController::class, 'getIndicators']
    );

    Route::get(
        'get-request-codes',
        [CitizenServiceRequestController::class, 'getRequestCodes']
    );
    Route::delete(
        'registers/delete/{id}',
        [CitizenServiceRequestController::class, 'destroy']
    )->name('citizenservice.registers.delete');

    /* Ruta para los tipos de transacción */
    Route::resource(
        'transaction-types',
        CitizenServiceTransactionTypeController::class,
        ['as' => 'citizenservice', 'except' => ['create','edit','show']]
    );

    /* Ruta para obtener el listado de los tipos de transacción*/
    Route::get(
        'get-transaction-types',
        [CitizenServiceTransactionTypeController::class, 'getTransactionTypes']
    )->name('citizenservice.transaction-types.get');

    /** Rutas para gestionar la asignación de personal a las solicitudes de trámites */
    Route::apiResource(
        'request/manage/teams',
        CitizenServiceRequestTeamController::class
    );

    Route::get(
        'get-staffs',
        [CitizenServiceController::class, 'getPayrollStaffs']
    )->name('citizenservice.get-payroll-staffs');

    Route::resource(
        'contact-books',
        CitizenServiceContactBookController::class,
        ['as' => 'citizenservice']
    );

    Route::get(
        'contact-books/vue-list/all',
        [CitizenServiceContactBookController::class, 'vueList']
    )->name('citizenservice.contact-books.vue-list');

    Route::resource(
        'community-profilings',
        CitizenServiceCommunityProfilingController::class,
        ['as' => 'citizenservice']
    );
    Route::get(
        'community-profilings/vue-list/all',
        [CitizenServiceCommunityProfilingController::class, 'vueList']
    )->name('citizenservice.community-profiling.vue-list');
});
