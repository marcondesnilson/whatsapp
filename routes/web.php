<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\teste;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/teste', [teste::class, 'index']);
