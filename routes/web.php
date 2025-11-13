<?php

use App\Http\Controllers\TurnoController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReporteController;

Route::post('/reportar', [ReporteController::class, 'store'])->name('reportar.cerrada');


Route::get('/', [TurnoController::class, 'index'])->name('dashboard');

Route::get('/buscar', [TurnoController::class, 'buscar'])->name('buscar');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
