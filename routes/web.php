<?php

use App\Http\Controllers\TurnoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReporteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TurnoController::class, 'index'])->name('dashboard');

Route::get('/buscar', [TurnoController::class, 'buscar'])->name('buscar');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/reportes', [ReporteController::class, 'store'])->name('reportes.store');
    Route::get('/mis-reportes', [ReporteController::class, 'misReportes'])->name('reportes.mis-reportes');
    Route::delete('/reportes/{id}', [ReporteController::class, 'destroy'])->name('reportes.destroy');
});

require __DIR__.'/auth.php';
