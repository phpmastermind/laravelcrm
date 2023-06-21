<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

    $router->get('/', 'CasesController@index')->name('cases');
    $router->resource('customers', CustomerController::class);
    $router->resource('engineers', EngineerController::class);
    $router->resource('cases', CasesController::class);
    $router->resource('closed-cases', ClosedCasesController::class);
    $router->resource('areas', AreasController::class);
    $router->resource('articles', ArticleController::class);
    $router->resource('case-types', CaseTypeController::class);
    $router->resource('case-histories', CaseHistoryController::class);

});
