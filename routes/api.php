<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SignageController;


Route::group(['prefix' => 'v1', 'as' => 'api.'], function () {
    Route::get('signage', [SignageController::class, 'index'])->name('signage.index');
    Route::get('signage/cars', [SignageController::class, 'getCars'])->name('signage.cars');
    Route::get('signage/requests', [SignageController::class, 'getPendingRequests'])->name('signage.requests');
    Route::get('signage/vertical-data', [SignageController::class, 'getVerticalData'])->name('signage.verticalData');
});

