<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OcrFarmaciasService;

class ProcesarAfiche extends Command
{
    protected $signature = 'ocr:procesar {ruta}';
    protected $description = 'Procesar afiche mediante OCR y cargar farmacias/turnos';

    public function handle(OcrFarmaciasService $ocr)
    {
        $ruta = $this->argument('ruta');

        $this->info("Procesando OCR...");
        $resultado = $ocr->procesar($ruta);

        if (isset($resultado['error'])) {
            $this->error("ERROR: " . $resultado['error']);
            return 1;
        } else {
            $this->info("Procesado OK.");
            $this->info("Farmacias procesadas: " . $resultado['farmacias']);
            $this->info("Turnos creados: " . $resultado['turnos']);
            return 0;
        }
    }
}



