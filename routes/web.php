<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
  $bootTime = round((microtime(true) - LARAVEL_START) * 1000, 2);

  return response()->json([
    'app' => config('app.name'),
    'status' => 'OK',
    'boot_time_ms' => $bootTime,
    'php_version' => PHP_VERSION,
    'laravel_version' => app()->version(),
    'timestamp' => now()->toIso8601String(),
  ]);
});
