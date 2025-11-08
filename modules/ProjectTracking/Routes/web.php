<?php

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

use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['web', 'auth', 'verified'],
    'prefix' => 'projecttracking'
], function () {
    /* Ruta para visualizar la sección de configuración del módulo */
    Route::resource('settings', 'ProjectTrackingSettingsController');
    Route::get('settings', 'ProjectTrackingSettingsController@index')->name('projecttracking.setting.index');
    Route::post('settings', 'ProjectTrackingSettingsController@store')->name('projecttracking.settings.store');

    Route::get('/', 'ProjectTrackingController@index');

    /* Ruta que obtiene un array con los tipos de productos */
    Route::get('get-type-products', 'ProjectTrackingTypeProductsController@getTypeProducts');

    /* Rutas para gestionar los tipos de productos */
    Route::resource('type-products', 'ProjectTrackingTypeProductsController', ['except' => ['show']]);

    /* Ruta para gestionar los tipos de proyectos */
    Route::resource('project-type', 'ProjectTrackingProjectTypeController', ['except' => ['show']]);

    /* Ruta que obtiene un array con los tipos de proyectos */
    Route::get('get-type-projects', 'ProjectTrackingProjectTypeController@getTypeProjects');

    /* Ruta para gestionar la configuración de proyectos */
    Route::resource('projects', 'ProjectTrackingProjectController');

    /* Ruta para detallar la configuración de proyectos */
    Route::get('get-project-info/{id}', 'ProjectTrackingProjectController@recordInfo')->name('projecttracking.project.info.get');

    /* Ruta para obtener los proyectos registrados en el modulo Budget */
    Route::get('get-budget-projects', 'ProjectTrackingProjectController@getBudgetProjects')->name('projecttracking.get_budget_projects');

    /* Ruta para gestionar la configuración de productos */
    Route::resource('products-config', 'ProjectTrackingProductController');

    /* Ruta para detallar la configuración de productos */
    Route::get('get-product-info/{id}', 'ProjectTrackingProductController@recordInfo')->name('projecttracking.product.info.get');

    /* Ruta para obtener el listado de subproyectos */
    Route::get('get-subprojects', 'ProjectTrackingSubProjectController@getSubprojects')->name('projecttracking.subprojects.get');

    /* Ruta para obtener el listado del personal */
    Route::get('get-personal', 'ProjectTrackingPersonalRegisterController@getPersonal')->name('projecttracking.personal.get');

    /* Ruta para obtener el listado de Tipos de Proyecto */
    Route::get('get-project-types', 'ProjectTrackingProjectTypeController@getProjectTypes')->name('projecttracking.project.types.get');

    /* Ruta para obtener el listado de Tipo de Productos */
    Route::get('get-product-types', 'ProjectTrackingTypeProductsController@getProductTypes')->name('projecttracking.product.types.get');

    /* Ruta para obtener el listado de las Dependencias */
    Route::get('get-dependencies', 'ProjectTrackingDependencyController@getDependencies')->name('projecttracking.dependencies.get');

    /* Ruta para obtener el listado de los Tipos de dependencias */
    Route::get('get-dependencies-type', 'ProjectTrackingDependenciesTypeController@getDependenciesType')->name('projecttracking.dependencies.type.get');

    /* Rutas para gestionar los cargos */
    Route::resource('positions', 'ProjectTrackingPositionController', ['except' => ['show']]);

    /* Rutas para gestionar el registro del personal */
    Route::resource('personal-register', 'ProjectTrackingPersonalRegisterController', ['except' => ['show']]);

    /* Ruta para obtener el listado de cargos */
    Route::get('get-positions', 'ProjectTrackingPositionController@getPositions')->name('projecttracking.positions.get');

    /* Rutas para gestionar las dependencias */
    Route::resource('dependencies', 'ProjectTrackingDependencyController', ['except' => ['show']]);

    /* Ruta para gestionar los roles */
    Route::resource('staff-classification', 'ProjectTrackingStaffClassificationController', ['except' => ['show']]);

    /* Ruta para obtener el listado de los Roles */
    Route::get('get-staff_classifications', 'ProjectTrackingStaffClassificationController@getStaffClassifications')->name('projecttracking.staff_classifications.get');

    /* Rutas para gestionar las prioridades */
    Route::resource('priorities', 'ProjectTrackingPriorityController', ['except' => ['show']]);

    /* Ruta para obtener el listado de las actividades */
    Route::get('get-priorities', 'ProjectTrackingPriorityController@getPriorities')->name('projecttracking.priorities.get');

    /* Ruta para gestionar Estatus de actividades */
    Route::resource('activity-status', 'ProjectTrackingActivityStatusController', ['except' => ['show']]);

    /* Ruta para obtener los Estatus de actividades */
    Route::get('get-activity-statuses', 'ProjectTrackingActivityStatusController@getActivityStatuses')->name('projecttracking.activity_statuses.get');

    /* Ruta para gestionar Estatus de actividades */
    Route::resource('activity', 'ProjectTrackingActivitysController', ['except' => ['show']]);

    /* Ruta para obtener el listado de las actividades */
    Route::get('get-activities', 'ProjectTrackingActivitysController@getActivities')->name('projecttracking.activities.get');

    /** Ruta para gestionar Tipo de Tareas*/
    Route::resource('task-types', 'ProjectTrackingTaskTypesController', ['except' => ['show']]);

    /** Ruta para obtener los tipos de tareas */
    Route::get(
        'get-task-types',
        'ProjectTrackingTaskTypesController@getTaskTypes'
    )->name('projecttracking.task_types.get');

    /** Ruta para gestionar etiquetas */
    Route::resource('tags', 'ProjectTrackingTagsController', ['except' => ['show']]);

    /** Ruta para obtener las etiquetas */
    Route::get('get-tags', 'ProjectTrackingTagsController@getTags')->name('projecttracking.tags.get');

    /** Ruta para gestionar tipos de dependencias */
    Route::resource('dependencies-type', 'ProjectTrackingDependenciesTypeController', ['except' => ['show']]);

    /* Ruta para obtener el listado de las actividades segun un id de tipo de producto*/
    Route::get(
        'get-activities-by-product-type/{product_type_id}',
        'ProjectTrackingActivitysController@getActivityesByProductType'
    )->name('projecttracking.activities.get_by_product_id');

    /* Ruta para obtener el listado de las actividades segun varios id de tipo de producto*/
    Route::get(
        'get-activities-by-product-types/{product_type_ids}',
        'ProjectTrackingActivitysController@getActivityesByProductTypes'
    )->name('projecttracking.activities.get_by_product_ids');

    /* Rutas para gestionar los subproyectos */
    Route::resource('subprojects', 'ProjectTrackingSubProjectController');

    /* Rutas para gestionar los subproyectos */
    Route::resource('subprojects', 'ProjectTrackingSubProjectController', ['except' => ['show']]);

    /* Ruta para obtener el listado de subproyectos asociados a un responsable en específico */
    Route::get('get-subprojects-by-responsable/{id}', 'ProjectTrackingSubProjectController@getSubProjectsByResponsable')->name('projecttracking.subprojects_by_responsable.get');

    /* Ruta para obtener el listado de proyectos */
    Route::get('get-projects', 'ProjectTrackingProjectController@getProjects')->name('projecttracking.projects.get');

    /* Ruta para obtener el listado de proyectos asociados a un responsable en específico */
    Route::get('get-projects-by-responsable/{id}', 'ProjectTrackingProjectController@getProjectsByResponsable')->name('projecttracking.projects_by_responsable.get');

    Route::get('subprojects/get-detail-subproject/{id?}', 'ProjectTrackingSubProjectController@getDetailSubProject')->name('projecttracking.subprojects.getDetailSubProject');

    /* Rutas para gestionar el plan de actividades */
    Route::resource('activity_plans', 'ProjectTrackingActivityPlanController', ['as' => 'projecttracking']);

    /* Ruta que muestra el formulario para actualizar la información de un plan de actividades */
    Route::get('activity_plans/edit/{id}', 'ProjectTrackingActivityPlanController@edit')->name('projecttracking.activity_plan.edit');

    /* Ruta que elimina un plan de actividades */
    Route::delete('activity_plans/delete/{id}', 'ProjectTrackingActivityPlanController@destroy')->name('projecttracking.activity_plan.destroy');

    /* Ruta que obtiene la información de un plan de activiades registrado */
    Route::get('activity_plans/vue-info/{id}', 'ProjectTrackingActivityPlanController@vueInfo')->name('projecttracking.activity_plan.vue-info');

    /* Ruta que obtiene un listado del plan de actividades */
    Route::get('activity_plans/show/vue-list', 'ProjectTrackingActivityPlanController@vueList')->name('projecttracking.activity_plans.vue-list');

    /* Ruta para detallar la información del trabajador en el plan de actividades*/
    Route::get('get-activity-plans-team-info/{employer}/{staffClassification}', 'ProjectTrackingActivityPlanController@recordTeamInfo')->name('projecttracking.team_info.get');

    /* Ruta para obtener el listado de personas asociadas a un proyecto en un plan de actividad en específico */
    Route::get('get-projects-by-activity-plan', 'ProjectTrackingActivityPlanController@getProjectsByActivityPlan')->name('projecttracking.projects_by_activity_plan.get');

    /* Ruta para obtener el listado de Activitades macro asociadas a un proyecto en específico */
    Route::get('get-activities-by-project/{id}', 'ProjectTrackingActivityPlanController@getActivitiesByProject')->name('projecttracking.activities_by_project.get');

    /* Ruta para obtener el listado de personas asociadas a un proyecto en un plan de actividad en específico */
    Route::get('get-personal-by-project/{id}', 'ProjectTrackingActivityPlanController@getPersonalByProject')->name('projecttracking.personal_by_project.get');

    /* Ruta para obtener el listado de subproyectos asociados a un proyecto en un plan de actividad en específico */
    Route::get('get-subprojects-by-project/{id}', 'ProjectTrackingActivityPlanController@getSubProjectsByProject')->name('projecttracking.subprojects_by_project.get');

    /* Ruta para obtener el listado de personas asociadas a un proyecto en un plan de actividad en específico */
    Route::get('get-subprojects-by-activity-plan', 'ProjectTrackingActivityPlanController@getSubProjectsByActivityPlan')->name('projecttracking.subprojects_by_activity_plan.get');

    /* Ruta para obtener el listado de Activitades macro asociadas a un subproyecto en específico */
    Route::get('get-activities-by-subproject/{id}', 'ProjectTrackingActivityPlanController@getActivitiesBySubProject')->name('projecttracking.activities_by_subproject.get');

    /* Ruta para obtener el listado de personas asociadas a un subproyecto en un plan de actividad en específico */
    Route::get('get-personal-by-subproject/{id}', 'ProjectTrackingActivityPlanController@getPersonalBySubProject')->name('projecttracking.personal_by_subproject.get');

    /* Ruta para obtener el listado de personas asociadas a un proyecto en un plan de actividad en específico */
    Route::get('get-products-by-activity-plan', 'ProjectTrackingActivityPlanController@getProductsByActivityPlan')->name('projecttracking.products_by_activity_plan.get');

    /* Ruta para obtener el listado de Activitades macro asociadas a un subproyecto en específico */
    Route::get('get-activities-by-product/{id}', 'ProjectTrackingActivityPlanController@getActivitiesByProduct')->name('projecttracking.activities_by_product.get');

    /* Ruta para obtener el listado de personas asociadas a un proyecto en específico */
    Route::get('get-personal-by-product/{id}', 'ProjectTrackingActivityPlanController@getPersonalByProduct')->name('projecttracking.personal_by_product.get');

    /* Ruta para detallar la información de la actividad en el plan de actividades */
    Route::get('get-activity-plans-activity-info/{activity}', 'ProjectTrackingActivityPlanController@recordActivityInfo')->name('projecttracking.activity.info.get');

    /* Ruta para obtener el listado de productos */
    Route::get('get-products', 'ProjectTrackingProductController@getProducts')->name('projecttracking.products.get');

    /* Ruta para obtener el listado de proyectos asociados a un responsable en específico */
    Route::get('get-products-by-responsable/{id}', 'ProjectTrackingProductController@getProductsByResponsable')->name('projecttracking.products_by_responsable.get');

    /* Rutas para gestionar las tareas */
    Route::resource('tasks', 'ProjectTrackingTaskController', ['as' => 'projecttracking', 'except' => ['show']]);

    /* Ruta para detallar la información de una tarea */
    Route::get(
        'tasks/show/{id}',
        'ProjectTrackingTaskController@show'
    )->name('projecttracking.tasks.show');

    /* Ruta para detallar la configuración de proyectos */
    Route::get('get-task-info/{id}', 'ProjectTrackingTaskController@recordInfo')->name('projecttracking.task.info.get');

    /* Ruta para obtener la ruta de las tareas */
    Route::get('tasks/vue-list', 'ProjectTrackingTaskController@vueList')->name('projecttracking.tasks.vue-list');

    /* Ruta para obtener la función de editar las tareas */
    Route::get('/tasks/edit/{id}', 'ProjectTrackingTaskController@edit')->name('projecttracking.task.edit');

    /* Ruta que obtiene la información de una tarea registrada */
    Route::get('/task/vue-info/{id}', 'ProjectTrackingTaskController@vueInfo')->name('projecttracking.task.vue-info');

    /* Ruta para ejecutar la función de eliminar las tareas */
    Route::delete('/tasks/delete/{project_tracking_task}', 'ProjectTrackingTaskController@destroy')->name('projecttracking.task.delete');

    /** Ruta para obtener las tareas */
    Route::get('get-tasks', 'ProjectTrackingTaskController@getTasks')->name('projecttracking.tasks.get');

    /**Ruta para gestionar los estados de entrega */
    Route::resource('delivery-status', 'ProjectTrackingDeliveryStatusController');

    /* Ruta para gestionar las jornadas laborales */
    Route::resource('work-days', 'ProjectTrackingWorkDayController');

    /* Rutas para gestionar los comentarios de las tareas */
    Route::resource('task-comment', 'ProjectTrackingTaskCommentController');

    /** Ruta para obtener los comentarios de las tareas */
    Route::get('get-task-comments', 'ProjectTrackingTaskCommentController@getTaskComments')->name('projecttracking.task.comment.get');

    /** Ruta para obtener los datos para crear el gráfico del reporte */
    Route::post('get-graph-data', 'ProjectTrackingReportController@getGraphData');

    /*
     | --------------------------------------------------------------------------------------
     | Grupo de rutas para gestionar la generación de reportes en el módulo de talento humano
     | --------------------------------------------------------------------------------------
     */

    Route::group([
        'middleware' => ['web', 'auth', 'verified'],
        'prefix' => 'reports'
    ], function () {
        Route::get('show/{filename}', 'ProjectTrackingReportController@show')
            ->name('project-tracking.reports.show');

        /* Rutas que permiten generar el reporte de trabajadores */

        Route::get('personal-registers', 'ProjectTrackingReportController@personalRegisters')
            ->name('project-tracking.reports.personal-registers');

        Route::post('personal-register/create', 'ProjectTrackingReportController@create')
            ->name('payroll.reports.personal-register.create');
    });
});
