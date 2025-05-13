<?php

use Illuminate\Support\Facades\Route;

Route::prefix('iblog/v1')->group(function () {
  Route::apiCrud([
    'module' => 'iblog',
    'prefix' => 'posts',
    'controller' => 'PostApiController',
    'middleware' => ['create' => [], 'index' => [], 'show' => [], 'update' => [], 'delete' => [], 'restore' => []]
  ]);

  Route::apiCrud([
    'module' => 'iblog',
    'prefix' => 'categories',
    'controller' => 'CategoryApiController',
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
