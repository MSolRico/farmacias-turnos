<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TurnosPdfDownloader;
use App\Services\OcrFarmaciasService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportarTurnosMensual extends Command
{
    protected $signature = 'turnos:importar {--force : Forzar descarga del PDF aunque exista uno reciente}';
    protected $description = 'Descarga el PDF de turnos de farmacias, ejecuta OCR y actualiza la base de datos';

    private TurnosPdfDownloader $downloader;
    private OcrFarmaciasService $ocrService;

    public function __construct(TurnosPdfDownloader $downloader, OcrFarmaciasService $ocrService)
    {
        parent::__construct();
        $this->downloader = $downloader;
        $this->ocrService = $ocrService;
    }

    public function handle(): int
    {
        $this->info('🔄 Iniciando importación de turnos mensuales');

        $force = $this->option('force') ?? false;

        // 1️⃣ Descargar PDF
        $this->info('📥 Descargando PDF...');
        $downloadResult = $this->downloader->downloadLatest($force);

        if (!$downloadResult['success']) {
            $this->error('❌ Error descargando PDF: ' . $downloadResult['message']);
            Log::error('ImportarTurnosMensual: descarga PDF fallida', $downloadResult);
            return 1;
        }

        $pdfPath = $downloadResult['path'];
        $this->info("✅ PDF descargado en: $pdfPath");

        // 2️⃣ Ejecutar OCR y procesar PDF
        $this->info('📝 Ejecutando OCR y procesando farmacias...');
        $ocrResult = $this->ocrService->procesar($pdfPath);

        if (isset($ocrResult['error'])) {
            $this->error('❌ Error procesando OCR: ' . $ocrResult['error']);
            Log::error('ImportarTurnosMensual: OCR fallido', $ocrResult);
            return 1;
        }

        $this->info("✅ Farmacias importadas: {$ocrResult['farmacias']}, Turnos: {$ocrResult['turnos']}");
        Log::info('ImportarTurnosMensual: importación completada', $ocrResult);

        // 3️⃣ Limpiar PDFs antiguos (opcional)
        $this->info('🗑 Limpiando PDFs antiguos...');
        $this->downloader->cleanOldPdfs();
        $this->info('✅ Limpieza completada');

        $this->info('🎉 Importación de turnos finalizada correctamente');

        return 0;
    }
}
