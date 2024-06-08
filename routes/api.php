<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AlimentoController;
use App\Http\Controllers\IngredienteController;

Route::resource('alimento', AlimentoController::class);
Route::resource('ingrediente', IngredienteController::class);



Route::get('/user', function (Request $request) {
    return response()->json(['message' => 'Hello, world!']);
});
