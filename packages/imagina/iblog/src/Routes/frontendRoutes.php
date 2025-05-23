<?php

use Illuminate\Routing\Router;

$locale = LaravelLocalization::setLocale() ?: App::getLocale();
$customMiddlewares = config('asgard.iblog.config.middlewares') ?? [];

// // if (!App::runningInConsole()) {
//    $categoryRepository = app('Imagina\Icrud\Repositories\CategoryRepository');
//    $categories = $categoryRepository->getItemsBy(json_decode(json_encode(['filter' => [], 'include' => [], 'take' => null])));
//    foreach ($categories as $category) {
//      if(!empty($category->slug) && !$category->internal) {
//        /** @var Router $router */
//        Route::group(['prefix' => $category->slug,
//          'middleware' => $customMiddlewares], function (Router $router) use ($locale, $category) {
//
//          $router->get('/', [
//            'as' => $locale . '.iblog.category.' . $category->slug,
//            'uses' => 'OldPublicController@index',
//            'middleware' => config('asgard.iblog.config.middleware'),
//          ]);
//          $router->get('{slug}', [
//            'as' => $locale . '.iblog.' . $category->slug . '.post',
//            'uses' => 'OldPublicController@show',
//            'middleware' => config('asgard.iblog.config.middleware'),
//          ]);
//        });
//      }
//    }
// // }
//  /** @var Router $router */
//  Route::group(['prefix' => trans('iblog::tag.uri'),
//    'middleware' => $customMiddlewares], function (Router $router) use ($locale) {
//    $router->get('{slug}', [
//      'as' => $locale . '.iblog.tag.slug',
//      'uses' => 'PublicController@tag',
//      //'middleware' => config('asgard.iblog.config.middleware'),
//    ]);
//  });

/** @var Router $router */
Route::prefix('iblog/feed')->middleware($customMiddlewares)->group(function (Router $router) use ($locale) {
    $router->get('{format}', [
        'as' => $locale.'.iblog.feed.format',
        'uses' => 'PublicController@feed',

    ]);
});
