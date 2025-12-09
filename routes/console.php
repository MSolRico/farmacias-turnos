<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule; // Importante para el cron

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// --- TAREA PROGRAMADA ---
// Ejecuta la importación todos los LUNES a las 03:00 AM.
// El comando verificará automáticamente si hay un PDF nuevo.
// Si el PDF es el mismo de la semana pasada, no hará nada.
Schedule::command('turnos:importar')
    ->weeklyOn(1, '03:00')
    ->timezone('America/Argentina/Buenos_Aires')
    ->appendOutputTo(storage_path('logs/cron_importacion.log'));
