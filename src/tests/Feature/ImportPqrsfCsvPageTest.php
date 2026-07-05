<?php

namespace Tests\Feature;

use App\Filament\Pages\ImportPqrsfCsv;
use App\Models\PqrsfSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ImportPqrsfCsvPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_page_requires_auth(): void
    {
        $this->get('/admin/import-pqrsf-csv')
            ->assertRedirect('/admin/login');
    }

    public function test_import_page_loads_for_admin(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->get('/admin/import-pqrsf-csv')
            ->assertOk()
            ->assertSee('Importar PQRSF desde CSV');
    }

    public function test_admin_can_analyze_and_import_uploaded_csv(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['role' => 'admin']);
        $file = UploadedFile::fake()->createWithContent('pqrsf.csv', $this->csvContents());

        Livewire::actingAs($user)
            ->test(ImportPqrsfCsv::class)
            ->set('data.csv_file', $file)
            ->set('data.status', 'validated')
            ->call('analyzeCsv')
            ->assertHasNoErrors()
            ->assertSet('summary.records_count', 1)
            ->assertSet('summary.new_count', 1)
            ->assertSet('readyToImport', true)
            ->call('confirmImport')
            ->assertHasNoErrors()
            ->assertSet('summary.imported', 1)
            ->assertSet('readyToImport', false);

        $submission = PqrsfSubmission::first();

        $this->assertNotNull($submission);
        $this->assertSame('validated', $submission->status);
        $this->assertSame('3001234567', $submission->field_values['numero_movil']);
        $this->assertSame('102', $submission->field_values['csv_id']);
    }

    private function csvContents(): string
    {
        $rows = [
            [
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
            ],
            [
                '102',
                '1/2/2026 8:00:00 AM',
                '1/2/2026 8:10:00 AM',
                '',
                '',
                '',
                '1/2/2026 8:00:00 AM',
                'Sede Panel',
                'Cliente Panel',
                '3001234567',
                'panel@example.com',
                'Peticion',
                '',
                'Ana',
                '5',
                '5',
                '4',
                '4',
                'Si',
                'Atencion correcta',
                'Instagram',
                'Si',
            ],
        ];

        $handle = fopen('php://temp', 'rb+');
        $this->assertIsResource($handle);

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);

        return $contents ?: '';
    }
}
