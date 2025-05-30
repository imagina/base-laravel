<?php

use Illuminate\Support\Facades\Route;
use Modules\Iuser\Http\Controllers\Api\UsersApiController;
use Modules\Iuser\Http\Controllers\Api\UserApiController;
use Modules\Iuser\Http\Controllers\Api\RoleApiController;
// add-use-controller

Route::prefix('/iuser/v1')->group(function () {

    Route::apiCrud([
      'module' => 'iuser',
      'prefix' => 'users',
      'controller' => UserApiController::class,
      'permission' => 'iuser.users',
      //'middleware' => ['create' => [], 'index' => [], 'show' => [], 'update' => [], 'delete' => [], 'restore' => []],
      'customRoutes' => [ // Include custom routes if needed
        [
            'method' => 'post', // get,post,put....
            'path' => '/register', // Route Path
            'uses' => 'register', //Name of the controller method to use
            'middleware' => [] // if not set up middleware, auth:api will be the default
       ]
      ]

    ]);
    Route::apiCrud([
      'module' => 'iuser',
      'prefix' => 'roles',
      'controller' => RoleApiController::class,
      'permission' => 'iuser.roles',
      //'middleware' => ['create' => [], 'index' => [], 'show' => [], 'update' => [], 'delete' => [], 'restore' => []],
      // 'customRoutes' => [ // Include custom routes if needed
      //  [
      //    'method' => 'post', // get,post,put....
      //    'path' => '/some-path', // Route Path
      //    'uses' => 'ControllerMethodName', //Name of the controller method to use
      //    'middleware' => [] // if not set up middleware, auth:api will be the default
      //  ]
      // ]
    ]);

    /**
     * Authentication routes
     */
    Route::prefix('/auth')->group(function () {
        $locale = \LaravelLocalization::setLocale() ?: \App::getLocale();

        //Login
        Route::post('/login', [Modules\Iuser\Http\Controllers\Api\AuthApiController::class, 'login'])
            ->name($locale.'api.iuser.auth.login');

        //Logout
        Route::post('/logout', [Modules\Iuser\Http\Controllers\Api\AuthApiController::class, 'logout'])
            ->name($locale.'api.iuser.auth.logout')
            ->middleware('auth:api');

        //Reset Process
        Route::post('/reset', [Modules\Iuser\Http\Controllers\Api\AuthApiController::class, 'reset'])
            ->name($locale.'api.iuser.auth.reset');
        //'middleware' => ['captcha'], //TODO: Check if captcha is needed

        Route::post('/reset-complete', [Modules\Iuser\Http\Controllers\Api\AuthApiController::class, 'resetComplete'])
            ->name($locale.'api.iuser.auth.reset-complete');

    });


// append

});
