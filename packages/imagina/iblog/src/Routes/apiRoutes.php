<?php

use Illuminate\Support\Facades\Route;
use Imagina\Iblog\Http\Controllers\Api\PostApiController;
use Imagina\Iblog\Http\Controllers\Api\CategoryApiController;

Route::prefix('iblog/v1')->group(function () {
  Route::apiCrud([
    'module' => 'iblog',
    'prefix' => 'posts',
    'controller' => PostApiController::class,
    'middleware' => ['create' => [], 'index' => [], 'show' => [], 'update' => [], 'delete' => [], 'restore' => []]
  ]);

  Route::apiCrud([
    'module' => 'iblog',
    'prefix' => 'categories',
    'controller' => CategoryApiController::class,
    'middleware' => ['create' => [], 'index' => [], 'show' => [], 'update' => [], 'delete' => [], 'restore' => []]
  ]);

  Route::apiCrud([
    'module' => 'iblog',
    'prefix' => 'statuses',
    'staticEntity' => 'Imagina\Icrud\Entities\Status',
    'middleware' => ['create' => [], 'index' => [], 'show' => [], 'update' => [], 'delete' => [], 'restore' => []]
  ]);
  //append
});
