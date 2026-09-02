<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('turnos:importar')
    ->dailyAt('03:00')
    ->withoutOverlapping();