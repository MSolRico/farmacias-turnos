<?php

namespace App\Console\Commands;

use App\Models\ImportacionTurno;
use App\Services\OcrFarmaciasService;
use App\Services\TurnosPdfDownloader;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ImportarTurnosMensual extends Command
{
    protected $signature = 'turnos:importar {--force : Forzar una nueva descarga e importación}';
    protected $description = 'Descarga el PDF de turnos, ejecuta OCR y actualiza la base de datos';

    public function __construct(
        private TurnosPdfDownloader $downloader,
        private OcrFarmaciasService $ocrService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        // 1. Determinar el período a importar
        $fechaObjetivo = now()->startOfMonth();
        $mes = $fechaObjetivo->month;
        $anio = $fechaObjetivo->year;
        $nombreMes = ucfirst($fechaObjetivo->translatedFormat('F'));

        $this->info("🔄 Importación de turnos: {$nombreMes} {$anio}");

        // 2. Buscar la importación del período
        $importacion = ImportacionTurno::where('mes', $mes)
            ->where('anio', $anio)
            ->first();

        // Si ya terminó correctamente, no volvemos a procesarla salvo que se haya utilizado --force.
        if (
            in_array(
                $importacion?->estado,
                ['completada', 'completada_con_advertencias'],
                true
            )
            && !$this->option('force')
        ) {
            $this->info("✅ Los turnos de {$nombreMes} {$anio} ya fueron importados.");

            return self::SUCCESS;
        }

        // 3. Crear o preparar registro de importación
        if (!$importacion) {

            $importacion = ImportacionTurno::create([
                'mes' => $mes,
                'anio' => $anio,
                'estado' => 'procesando',
                'ultimo_intento' => now(),
            ]);
        } else {

            $importacion->update([
                'estado' => 'procesando',
                'mensaje' => null,
                'ultimo_intento' => now(),
            ]);
        }

        // 4. Descargar PDF
        $this->info('📥 Buscando PDF de turnos...');

        $downloadResult = $this->downloader->downloadForMonth(
            $fechaObjetivo,
            $this->option('force')
        );

        // 5. Procesar resultado de descarga
        if (!$downloadResult['success']) {

            $mensaje = $downloadResult['message'];

            // Que todavía no exista el PDF del mes no es un error. El scheduler podrá volver a intentarlo posteriormente.
            if (
                str_contains(
                    strtolower($mensaje),
                    'todavía no se encontró el pdf'
                )
            ) {
                $importacion->update([
                    'estado' => 'pendiente',
                    'mensaje' => $mensaje,
                ]);

                $this->warn("⏳ {$mensaje}");

                $this->info('🔁 Se volverá a intentar en la próxima ejecución.');

                Log::info(
                    'ImportarTurnosMensual: PDF todavía no publicado',
                    [
                        'mes' => $mes,
                        'anio' => $anio,
                        'mensaje' => $mensaje,
                    ]
                );

                return self::SUCCESS;
            }

            // Cualquier otro error sí debe marcar la importación como fallida.
            $importacion->update([
                'estado' => 'error',
                'mensaje' => $mensaje,
            ]);

            $this->error("❌ {$mensaje}");

            Log::error(
                'ImportarTurnosMensual: descarga fallida',
                [
                    'mes' => $mes,
                    'anio' => $anio,
                    'mensaje' => $mensaje,
                ]
            );

            return self::FAILURE;
        }

        // 6. Guardar información del PDF
        $importacion->update(['pdf_url' => $downloadResult['url'],]);

        $pdfPath = $downloadResult['path'];
        $this->info("✅ PDF descargado: {$pdfPath}");

        // 7. Ejecutar OCR
        $this->info('📝 Ejecutando OCR y procesando farmacias...');
        $ocrResult = $this->ocrService->procesar($pdfPath);

        // 8. Error durante OCR/importación
        if (isset($ocrResult['error'])) {

            $mensaje = $ocrResult['error'];

            $importacion->update([
                'estado' => 'error',
                'mensaje' => $mensaje,
            ]);

            $this->error("❌ Error procesando OCR: {$mensaje}");
            Log::error('ImportarTurnosMensual: OCR fallido',
                [
                    'mes' => $mes,
                    'anio' => $anio,
                    'error' => $mensaje,
                ]
            );

            return self::FAILURE;
        }

        // 9. Obtener estadísticas
        $farmaciasNuevas = $ocrResult['farmacias_nuevas'] ?? 0;
        $farmaciasActualizadas = $ocrResult['farmacias_actualizadas'] ?? 0;
        $farmaciasRechazadas = $ocrResult['farmacias_rechazadas'] ?? 0;
        $turnosNuevos = $ocrResult['turnos_nuevos'] ?? 0;
        $asignacionesCreadas = $ocrResult['asignaciones_creadas'] ?? 0;
        $columnasConError = $ocrResult['columnas_con_error'] ?? 0;

        // 10. Mostrar resumen
        $this->info("🏥 Farmacias nuevas: {$farmaciasNuevas}");
        $this->info("📅 Turnos nuevos: {$turnosNuevos}");
        $this->info("🔗 Asignaciones nuevas: {$asignacionesCreadas}");

        if ($farmaciasActualizadas > 0) {
            $this->info("✏️ Farmacias actualizadas: {$farmaciasActualizadas}");
        }

        if ($farmaciasRechazadas > 0) {
            $this->warn("⚠️ Registros rechazados: {$farmaciasRechazadas}");
        }

        if ($columnasConError > 0) {
            $this->warn("⚠️ Columnas con error de OCR: {$columnasConError}");
        }

        /* 11. Estado final
         * Si hubo errores de columnas pero igualmente obtuvimos datos
         * válidos, la importación queda como completada con advertencias.
        */
        $estadoFinal = $columnasConError > 0
            ? 'completada_con_advertencias'
            : 'completada';

        $mensajeFinal = $columnasConError > 0
            ? "La importación finalizó, pero {$columnasConError} columna(s) no pudieron procesarse."
            : null;

        // 12. Guardar estadísticas de la importación
        $importacion->update([
            'estado' => $estadoFinal,
            'farmacias_nuevas' => $farmaciasNuevas,
            'farmacias_actualizadas' => $farmaciasActualizadas,
            'farmacias_rechazadas' => $farmaciasRechazadas,
            'turnos_nuevos' => $turnosNuevos,
            'asignaciones_creadas' => $asignacionesCreadas,
            'columnas_con_error' => $columnasConError,
            'mensaje' => $mensajeFinal,
        ]);

        // 13. Limpiar PDFs antiguos
        $this->info('🗑 Limpiando PDFs antiguos...');
        $this->downloader->cleanOldPdfs();
        $this->info('✅ Limpieza completada');

        // 14. Finalizar
        $this->info("🎉 Importación de {$nombreMes} {$anio} finalizada correctamente");

        Log::info(
            'ImportarTurnosMensual: importación completada',
            [
                'mes' => $mes,
                'anio' => $anio,
                'farmacias_nuevas' => $farmaciasNuevas,
                'farmacias_actualizadas' => $farmaciasActualizadas,
                'farmacias_rechazadas' => $farmaciasRechazadas,
                'turnos_nuevos' => $turnosNuevos,
                'asignaciones_creadas' => $asignacionesCreadas,
                'columnas_con_error' => $columnasConError,
                'estado' => $estadoFinal,
            ]
        );

        return self::SUCCESS;
    }
}
