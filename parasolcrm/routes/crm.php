<?php

use ParasolCRM\Http\Controllers\ResourceController;

// use App\Http\Controllers\Api\Crm\PackageController as ResourceController;

Route::group(['middleware' => ['auth:backoffice_user']], function () {
    Route::get('{resource}/form', [ResourceController::class, 'form']);
    Route::get('{resource}/document', [ResourceController::class, 'document']);
    Route::get('{resource}/filters', [ResourceController::class, 'filter']);
    Route::get('{resource}/chart', [ResourceController::class, 'charts']);
    Route::get('{resource}/status', [ResourceController::class, 'status']);
    Route::get('{resource}/table', [ResourceController::class, 'table']);
    Route::get('{resource}/log/{id}', [ResourceController::class, 'log']);
    Route::get('{resource}/relation-options/{field}', [ResourceController::class, 'relationOptions']);

    Route::get('{resource}', [ResourceController::class, 'table']);
    Route::get('{resource}/{id}', [ResourceController::class, 'show'])->where('id', '[0-9]+');

    Route::post('{resource}', [ResourceController::class, 'store']);
    Route::match(['put', 'patch'], '{resource}/{id}', [ResourceController::class, 'update']);
    Route::delete('{resource}/{id}', [ResourceController::class, 'destroy'])->where('id', '[0-9]+');
});
