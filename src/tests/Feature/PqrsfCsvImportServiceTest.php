<?php

namespace Tests\Feature;

use App\Models\PqrsfSubmission;
use App\Models\Sede;
use App\Services\PqrsfCsvImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PqrsfCsvImportServiceTest extends TestCase
{
    use RefreshDatabase;

    private array $tempFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }

        parent::tearDown();
    }

    public function test_it_analyzes_and_imports_new_csv_records(): void
    {
        $existingSede = Sede::factory()->create(['nombre' => 'Sede Existente']);
        PqrsfSubmission::create([
            'sede_id' => $existingSede->id,
            'field_values' => [
                'csv_id' => '100',
            ],
            'status' => 'pending',
        ]);
        $path = $this->writeCsv([
            $this->csvRow('100', 'Sede Existente'),
            $this->csvRow('101', 'Sede Nueva'),
        ]);

        $service = app(PqrsfCsvImportService::class);
        $summary = $service->analyze($path);

        $this->assertSame(2, $summary['records_count']);
        $this->assertSame(1, $summary['new_count']);
        $this->assertSame(1, $summary['duplicate_count']);
        $this->assertSame(['Sede Nueva'], $summary['missing_sedes']);
        $this->assertSame([], $summary['errors']);

        $result = $service->import($path, 'validated');

        $this->assertSame(1, $result['imported']);
        $this->assertSame(1, $result['created_sedes']);
        $this->assertDatabaseHas('sedes', ['nombre' => 'Sede Nueva']);

        $imported = PqrsfSubmission::query()
            ->get()
            ->first(fn (PqrsfSubmission $submission): bool => ($submission->field_values['csv_id'] ?? null) === '101');

        $this->assertNotNull($imported);
        $this->assertSame('validated', $imported->status);
        $this->assertSame('Felicitación', $imported->field_values['opcion_a_calificar']);
        $this->assertSame('Carlos', $imported->field_values['nombre_mesero']);
        $this->assertSame('Muy bien', $imported->field_values['observaciones']);
        $this->assertSame('CSV import', $imported->user_agent);
        $this->assertSame('2026-01-02', $imported->created_at->toDateString());

        $secondResult = $service->import($path);

        $this->assertSame(0, $secondResult['imported']);
        $this->assertSame(2, $secondResult['duplicate_count']);
        $this->assertDatabaseCount('pqrsf_submissions', 2);
    }

    public function test_it_reports_csv_normalization_errors_without_importing(): void
    {
        $path = $this->writeCsv([
            ['200', '1/2/2026 8:00:00 AM'],
        ]);

        $result = app(PqrsfCsvImportService::class)->import($path);

        $this->assertNotEmpty($result['errors']);
        $this->assertSame(0, $result['imported']);
        $this->assertDatabaseCount('pqrsf_submissions', 0);
    }

    public function test_import_command_still_supports_dry_run(): void
    {
        $path = $this->writeCsv([
            $this->csvRow('201', 'Sede Comando'),
        ]);

        $this->artisan('pqrsf:import-csv', [
            'path' => $path,
            '--dry-run' => true,
        ])->assertExitCode(0);

        $this->assertDatabaseCount('pqrsf_submissions', 0);
        $this->assertDatabaseMissing('sedes', ['nombre' => 'Sede Comando']);
    }

    private function writeCsv(array $rows): string
    {
        $path = tempnam(sys_get_temp_dir(), 'pqrsf-import-');
        $this->tempFiles[] = $path;

        $handle = fopen($path, 'wb');
        $this->assertIsResource($handle);

        fputcsv($handle, [
            'ID',
            'Start time',
            'Completion time',
            'Email',
            'Name',
            'Last modified',
            'Fecha',
            'Sede',
            'Nombre completo',
            'Numero movil',
            'Correo electronico',
            'Opcion',
            'Idioma',
            'Nombre mesero',
            'Ambientacion',
            'Atencion',
            'Comida',
            'Tiempo',
            'Recomendaria',
            'Observaciones',
            'Medio',
            'Autorizacion',
        ]);

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);

        return $path;
    }

    private function csvRow(string $csvId, string $sede): array
    {
        return [
            $csvId,
            '1/2/2026 8:00:00 AM',
            '1/2/2026 8:10:00 AM',
            '',
            '',
            '',
            '1/2/2026 8:00:00 AM',
            $sede,
            'Cliente '.$csvId,
            '3001234567',
            'cliente'.$csvId.'@example.com',
            'Felicitaciones',
            '',
            'Carlos',
            '5',
            '4',
            '5',
            '3',
            'Si',
            'Muy bien',
            'Google',
            'Si',
        ];
    }
}
