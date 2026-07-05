<?php

namespace App\Console\Commands;

use App\Services\PqrsfCsvImportService;
use Illuminate\Console\Command;

class ImportPqrsfCsv extends Command
{
    protected $signature = 'pqrsf:import-csv
        {path : Ruta del CSV dentro del contenedor app}
        {--dry-run : Analiza el archivo sin guardar registros}
        {--status=pending : Estado inicial: pending, validated o sent}';

    protected $description = 'Importa respuestas PQRSF desde el CSV exportado de Microsoft Forms.';

    public function handle(PqrsfCsvImportService $importer): int
    {
        $path = (string) $this->argument('path');
        $status = (string) $this->option('status');
        $dryRun = (bool) $this->option('dry-run');

        if (! is_readable($path)) {
            $this->error("No se puede leer el archivo: {$path}");

            return self::FAILURE;
        }

        if (! in_array($status, PqrsfCsvImportService::VALID_STATUSES, true)) {
            $this->error('El estado debe ser uno de: '.implode(', ', PqrsfCsvImportService::VALID_STATUSES));

            return self::FAILURE;
        }

        $summary = $dryRun
            ? $importer->analyze($path)
            : $importer->import($path, $status);

        if ($summary['errors'] !== []) {
            $this->error('El CSV no se pudo normalizar por completo.');
            $this->table(
                ['Linea', 'Error', 'Columnas'],
                array_slice($summary['errors'], 0, 20),
            );

            if (count($summary['errors']) > 20) {
                $this->line('Errores restantes: '.(count($summary['errors']) - 20));
            }

            return self::FAILURE;
        }

        $this->line('Registros leidos: '.$summary['records_count']);
        $this->line('Registros nuevos: '.$summary['new_count']);
        $this->line('Duplicados omitidos: '.$summary['duplicate_count']);
        $this->line('Sedes nuevas: '.$summary['missing_sedes_count']);

        if ($summary['missing_sedes'] !== []) {
            $this->line('Sedes que se crearan: '.implode(', ', $summary['missing_sedes']));
        }

        if ($dryRun) {
            $this->info('Dry-run completado. No se guardaron datos.');

            return self::SUCCESS;
        }

        $this->info("Importacion completada. Registros importados: {$summary['imported']}. Sedes creadas: {$summary['created_sedes']}.");

        return self::SUCCESS;
    }
}
