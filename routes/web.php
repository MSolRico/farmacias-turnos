<?php

use App\Http\Controllers\TurnoController;

Route::get('/', [TurnoController::class, 'index'])->name('home');
Route::get('/buscar', [TurnoController::class, 'buscar'])->name('buscar');

