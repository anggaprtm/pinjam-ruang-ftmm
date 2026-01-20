<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SignageController;


Route::group(['prefix' => 'v1', 'as' => 'api.'], function () {
    Route::get('signage', [SignageController::class, 'index'])->name('signage.index');
});