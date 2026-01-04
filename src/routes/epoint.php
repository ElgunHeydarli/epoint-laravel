<?php

use Illuminate\Support\Facades\Route;
use AZPayments\Epoint\Http\Controllers\EpointController;

Route::prefix('api/epoint')->group(function () {
    Route::post('/callback', [EpointController::class, 'callback'])->name('epoint.callback');
});