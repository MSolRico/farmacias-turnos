<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TurnosPdfDownloader;
use App\Services\OcrFarmaciasService;
use Illuminate\Support\Facades\Log;

class ImportarTurnosMensual extends Command
{
    // Agregamos la opción {--local}
    protected $signature = 'turnos:importar {--force : Forzar descarga} {--local : Usar archivo existente sin descargar}';
    protected $description = 'Procesa turnos de farmacias (descarga o local)';

    public function __construct(
        private TurnosPdfDownloader $downloader,
        private OcrFarmaciasService $ocrService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('🔄 Iniciando proceso de importación...');

        $pdfPath = null;

        // MODO LOCAL: Usar archivo existente
        if ($this->option('local')) {
            $this->info('📂 Modo LOCAL activado. Buscando archivo existente...');
            $pdfPath = $this->downloader->getLatestLocalPdfPath();

            if (!$pdfPath) {
                $this->error('❌ No se encontró ningún PDF local en storage/app/turnos/pdfs');
                return 1;
            }
            $this->info("✅ Archivo local encontrado: $pdfPath");
        }
        // MODO ONLINE: Descargar
        else {
            $this->info('📥 Verificando PDF en la web...');
            $downloadResult = $this->downloader->downloadLatest($this->option('force'));

            if (!$downloadResult['success']) {
                $this->error('❌ Error descarga: ' . $downloadResult['message']);
                return 1;
            }

            if (isset($downloadResult['skipped']) && $downloadResult['skipped']) {
                $this->info('⏭️  ' . $downloadResult['message']);
                return 0;
            }
            $pdfPath = $downloadResult['path'];
        }

        // 2️⃣ Ejecutar OCR
        $this->info('📝 Procesando con IA/OCR...');
        $ocrResult = $this->ocrService->procesar($pdfPath);

        if (isset($ocrResult['error'])) {
            $this->error('❌ Error OCR: ' . $ocrResult['error']);
            return 1;
        }

        $this->info("✅ Importación Exitosa.");
        $this->info("📊 Farmacias detectadas: {$ocrResult['farmacias']}");
        $this->info("📅 Turnos creados: {$ocrResult['turnos']}");

        return 0;
    }
}
