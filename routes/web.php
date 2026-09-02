<?php

use App\Http\Controllers\TurnoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FarmaciaController;
use App\Http\Controllers\Admin\TurnoController as AdminTurnoController;
use App\Http\Controllers\Admin\ImportacionController;
use App\Http\Controllers\Admin\ReporteController as AdminReporteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
*/

Route::get('/', [TurnoController::class, 'index'])->name('dashboard');
Route::get('/buscar', [TurnoController::class, 'buscar'])->name('buscar');
Route::post('/farmacias/cercanas', [TurnoController::class, 'getFarmaciasCercanas']);
Route::view('/terminos-y-condiciones', 'terminos')->name('terminos');
Route::view('/politica-de-privacidad', 'privacidad')->name('privacidad');

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
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/farmacias', [FarmaciaController::class, 'index'])->name('farmacias.index');
    Route::get('/farmacias/{farmacia}/editar', [FarmaciaController::class, 'edit'])->name('farmacias.edit');
    Route::put('/farmacias/{farmacia}', [FarmaciaController::class, 'update'])->name('farmacias.update');
    Route::get('/turnos', [AdminTurnoController::class, 'index'])->name('turnos.index');
    Route::get('/turnos/{turno}', [AdminTurnoController::class, 'show'])->name('turnos.show');
    Route::get('/importaciones', [ImportacionController::class, 'index'])->name('importaciones.index');
    Route::get('/importaciones/{importacion}', [ImportacionController::class, 'show'])->name('importaciones.show');
    Route::get('/reportes', [AdminReporteController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/{reporte}', [AdminReporteController::class, 'show'])->name('reportes.show');
    Route::put('/reportes/{reporte}', [AdminReporteController::class, 'update'])->name('reportes.update');

});


require __DIR__.'/auth.php';

