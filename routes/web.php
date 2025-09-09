<?php

use App\Http\Controllers\TurnoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TurnoController::class, 'index'])->name('home');
Route::get('/buscar', [TurnoController::class, 'buscar'])->name('buscar');

