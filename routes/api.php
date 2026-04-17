<?php

use App\Http\Controllers\Api\SignageController;
use App\Http\Controllers\Api\DisplayConfigController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\WebhookTicketController; // <-- 1. IMPORT CONTROLLERNYA DI SINI

Route::group([
    'prefix' => 'v1',
    'as' => 'api.',
    'middleware' => ['throttle:api', 'signage.key']
], function () {
    Route::get('signage', [SignageController::class, 'index'])->name('signage.index');
    Route::get('signage/cars', [SignageController::class, 'getCars'])->name('signage.cars');
    Route::get('signage/requests', [SignageController::class, 'getPendingRequests'])->name('signage.requests');
    Route::get('signage/vertical-data', [SignageController::class, 'getVerticalData'])->name('signage.verticalData');
    Route::get('signage/agenda-fakultas', [SignageController::class, 'getAgendaFakultas'])->name('signage.agendaFakultas');
});

Route::get('/v1/display-config/{location}', [DisplayConfigController::class, 'show'])
    ->middleware('throttle:api');
Route::get('/device-command/{location}', [DeviceController::class, 'getCommand']);

Route::post('/webhook/tickets', [WebhookTicketController::class, 'receiveTicket'])
    ->middleware('throttle:api');
    
Route::post('/webhook/ticket-replies', [WebhookTicketController::class, 'receiveReply'])
    ->middleware('throttle:api');