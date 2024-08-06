<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\MessageController;

//Route::resource('webhook', WebhookController::class); quero usar apenas post e redirecionar para index
Route::middleware(['throttle:1000,1'])->group(function () {
    Route::post('webhook', [WebhookController::class, 'index']);
});


Route::resource('message', MessageController::class);


