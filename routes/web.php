<?php

use App\Http\Controllers\TurnoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReporteController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReporteController;

Route::post('/reportar', [ReporteController::class, 'store'])->name('reportar.cerrada');


/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
*/

Route::get('/', [TurnoController::class, 'index'])->name('dashboard');
Route::get('/buscar', [TurnoController::class, 'buscar'])->name('buscar');
Route::post('/farmacias/cercanas', [TurnoController::class, 'getFarmaciasCercanas']);

// Reportar farmacia cerrada (público + modal pide login)
Route::post('/reportar', [ReporteController::class, 'store'])->name('reportar.cerrada');

/*
|--------------------------------------------------------------------------
| Rutas protegidas por autenticación
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |-------------------------
    | Perfil de usuario
    |-------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |-------------------------
    | Reportes del usuario
    |-------------------------
    */
    Route::post('/reportes', [ReporteController::class, 'store'])->name('reportes.store');
    Route::get('/mis-reportes', [ReporteController::class, 'misReportes'])->name('reportes.mis-reportes');
    Route::delete('/reportes/{id}', [ReporteController::class, 'destroy'])->name('reportes.destroy');

});

/*
|--------------------------------------------------------------------------
| Rutas ADMIN (OCR)
|--------------------------------------------------------------------------
*/



require __DIR__.'/auth.php';

