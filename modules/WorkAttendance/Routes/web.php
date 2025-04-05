<?php

use Illuminate\Support\Facades\Route;
use Modules\WorkAttendance\Http\Controllers\WorkAttendanceController;
use Modules\WorkAttendance\Http\Controllers\WorkAttendanceHistoryController;
use Modules\WorkAttendance\Http\Controllers\WorkAttendanceSettingController;
use Modules\WorkAttendance\Http\Controllers\WorkAttendanceScheduleController;
use Modules\WorkAttendance\Http\Controllers\WorkAttendanceSettingNotificationController;

/*
|--------------------------------------------------------------------------
| Rutas para Web
|--------------------------------------------------------------------------
|
| Aquí es donde puede registrar las rutas web para su aplicación. Estas
| rutas son cargadas por el RouteServiceProvider dentro de un grupo que
| contiene el grupo "web" middleware.
|
*/

Route::group([
    'middleware' => ['web'],
    'prefix' => 'work-attendance'
], function () {
    Route::get('/', [WorkAttendanceController::class, 'index'])->name('workattendance.index');
    Route::post('/', [WorkAttendanceController::class, 'store'])->name('workattendance.store');
    Route::get(
        'search/id_card/{id_card}',
        [WorkAttendanceController::class, 'searchIdCard']
    )->name('workattendance.search.idCard');
});

/** Descripción corta del grupo de rutas */
Route::group([
    'middleware' => ['web', 'auth', 'verified'],
    'prefix' => 'work-attendance'
], function () {
    Route::get('get-positions', [WorkAttendanceController::class, 'getPositions'])->name('workattendance.positions');
    /** Configuración general del módulo */
    Route::get('settings', WorkAttendanceSettingController::class)->name('workattendance.setting.index');
    Route::apiResource(
        'notifications/settings',
        WorkAttendanceSettingNotificationController::class
    )->names('workattendance.settings.notifications');
    Route::get(
        'settings/notifications/list',
        [WorkAttendanceSettingNotificationController::class, 'notifyList']
    )->name('workattendance.setting.notify.list');
    Route::apiResource(
        'schedule/settings',
        WorkAttendanceScheduleController::class
    )->parameters(
        ['settings' => 'schedule']
    )->names('workattendance.settings.schedule');
    Route::get('history', [WorkAttendanceHistoryController::class, 'index'])->name('workattendance.history.index');
    Route::get(
        'history/individual',
        [WorkAttendanceHistoryController::class, 'individualIndex']
    )->name('workattendance.history.individual');
    Route::get(
        'history/by-department',
        [WorkAttendanceHistoryController::class, 'byDepartmentIndex']
    )->name('workattendance.history.by-department');
    Route::get(
        'get-history',
        [WorkAttendanceHistoryController::class, 'getList']
    )->name('workattendance.history.list');
    Route::get(
        'get-history/individual',
        [WorkAttendanceHistoryController::class, 'getAttendanceData']
    )->name('workattendance.history.individual.list');
    Route::get(
        'get-history/by-department',
        [WorkAttendanceHistoryController::class, 'getAttendanceData']
    )->name('workattendance.history.by-department.list');
    Route::get(
        'get-departments',
        [WorkAttendanceController::class, 'getDepartments']
    )->name('workattendance.departments');
    Route::get(
        'print-history/individual',
        [WorkAttendanceHistoryController::class, 'printIndividual']
    )->name('workattendance.history.individual.print');
    Route::get(
        'print-history/by-department',
        [WorkAttendanceHistoryController::class, 'printByDepartment']
    )->name('workattendance.history.by-department.print');
    Route::post(
        'save-graph',
        [WorkAttendanceHistoryController::class, 'saveGraph']
    )->name('workattendance.history.save-graph');
    Route::post(
        'save-graph/by-department',
        [WorkAttendanceHistoryController::class, 'saveGraphByDepartment']
    )->name('workattendance.history.save-graph-by-department');
});
