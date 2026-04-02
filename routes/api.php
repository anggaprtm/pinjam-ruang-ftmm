<?php

use App\Http\Controllers\Api\SignageController;
use App\Http\Controllers\Api\DisplayConfigController;

Route::group([
    'prefix' => 'v1',
    'as' => 'api.',
    'middleware' => ['throttle:api', 'signage.key']
], function () {
    Route::get('signage', [SignageController::class, 'index'])->name('signage.index');
    Route::get('signage/cars', [SignageController::class, 'getCars'])->name('signage.cars');
    Route::get('signage/requests', [SignageController::class, 'getPendingRequests'])->name('signage.requests');
    Route::get('signage/vertical-data', [SignageController::class, 'getVerticalData'])->name('signage.verticalData');
});

// ⬇️ PISAHKAN INI
Route::get('/v1/display-config/{location}', [DisplayConfigController::class, 'show'])
    ->middleware('throttle:api');
