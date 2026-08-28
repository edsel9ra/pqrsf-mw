<?php

namespace Tests\Feature;

use App\Filament\Pages\ObservationsReport;
use App\Filament\Pages\SubmissionReport;
use App\Models\PqrsfSubmission;
use App\Models\Sede;
use App\Models\User;
use App\Services\Reports\ObservationsReportService;
use App\Services\Reports\ReportPdfService as DetailedReportPdfService;
use App\Services\Reports\ReportXlsxService;
use App\Services\Reports\SubmissionReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Mockery;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class AdditionalReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_report_pages_require_authentication(): void
    {
        $this->get('/admin/submission-report')->assertRedirect('/admin/login');
        $this->get('/admin/observations-report')->assertRedirect('/admin/login');
    }

    public function test_new_report_downloads_are_available_to_read_only_users(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $submissionService = Mockery::mock(SubmissionReportService::class);
        $submissionService->shouldReceive('getData')
            ->once()
            ->andReturn(['rows' => collect()]);
        $this->app->instance(SubmissionReportService::class, $submissionService);

        $observationsService = Mockery::mock(ObservationsReportService::class);
        $observationsService->shouldReceive('getData')
            ->once()
            ->andReturn(['groups' => collect()]);
        $this->app->instance(ObservationsReportService::class, $observationsService);

        $pdfService = Mockery::mock(DetailedReportPdfService::class);
        $pdfService->shouldReceive('generate')
            ->once()
            ->andReturn([
                'content' => '%PDF-1.7 read-only user',
                'filename' => 'reporte-test.pdf',
            ]);
        $this->app->instance(DetailedReportPdfService::class, $pdfService);

        $xlsxService = Mockery::mock(ReportXlsxService::class);
        $xlsxService->shouldReceive('generate')
            ->once()
            ->andReturn([
                'content' => 'xlsx-read-only-user',
                'filename' => 'reporte-test.xlsx',
            ]);
        $this->app->instance(ReportXlsxService::class, $xlsxService);

        $this->actingAs($user)
            ->get(route('admin.reportes.registros.xlsx'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->actingAs($user)
            ->get(route('admin.reportes.observaciones.pdf'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_new_report_pages_load_for_read_only_users(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get('/admin/submission-report')
            ->assertOk()
            ->assertSee('Reporte de registros PQRSF');

        $this->actingAs($user)
            ->get('/admin/observations-report')
            ->assertOk()
            ->assertSee('Reporte de observaciones por sede');
    }

    public function test_submission_report_uses_created_at_and_applies_filters(): void
    {
        $selectedSede = Sede::factory()->create(['nombre' => 'Sede Seleccionada']);
        $otherSede = Sede::factory()->create(['nombre' => 'Otra Sede']);

        $included = $this->createSubmission($selectedSede, '2026-01-15 10:30:00', [
            'fecha' => '2030-12-31',
            'nombre_completo' => 'Cliente Incluido',
            'nombre_mesero' => 'Mesero Incluido',
        ]);
        $this->createSubmission($selectedSede, '2026-02-01 10:30:00', [
            'nombre_completo' => 'Cliente Fuera de Fecha',
        ]);
        $this->createSubmission($otherSede, '2026-01-15 10:30:00', [
            'nombre_completo' => 'Cliente de Otra Sede',
        ]);

        $data = app(SubmissionReportService::class)->getData([
            'sede_id' => [$selectedSede->id],
            'date_from' => '2026-01-01',
            'date_to' => '2026-01-31',
        ]);

        $this->assertSame(1, $data['total']);
        $this->assertSame([
            'fecha' => '15/01/2026 10:30',
            'sede' => 'Sede Seleccionada',
            'nombre_completo' => 'Cliente Incluido',
            'nombre_mesero' => 'Mesero Incluido',
        ], $data['rows']->first());
        $this->assertNotSame($included->field_values['fecha'], '2026-01-15');
    }

    public function test_submission_pdf_export_applies_date_and_multiple_sede_filters(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $firstSede = Sede::factory()->create(['nombre' => 'Sede A']);
        $secondSede = Sede::factory()->create(['nombre' => 'Sede B']);

        $this->createSubmission($firstSede, '2026-01-10 09:00:00', [
            'nombre_completo' => 'Cliente Enero A',
        ]);
        $this->createSubmission($secondSede, '2026-01-20 09:00:00', [
            'nombre_completo' => 'Cliente Enero B',
        ]);
        $this->createSubmission($firstSede, '2026-02-01 09:00:00', [
            'nombre_completo' => 'Cliente Febrero A',
        ]);

        $filters = [
            'sede_id' => [$firstSede->id, $secondSede->id],
            'date_from' => '2026-01-01',
            'date_to' => '2026-01-31',
        ];
        $pdfService = Mockery::mock(DetailedReportPdfService::class);
        $pdfService->shouldReceive('generate')
            ->once()
            ->with(
                'reports.submission-report-pdf',
                Mockery::on(function (array $data) use ($filters): bool {
                    return $data['filters'] === $filters
                        && $data['total'] === 2
                        && $data['rows']->pluck('nombre_completo')->all() === [
                            'Cliente Enero B',
                            'Cliente Enero A',
                        ];
                }),
                Mockery::type('string'),
            )
            ->andReturn([
                'content' => '%PDF-1.7 filtered',
                'filename' => 'reporte-registros-pqrsf-2026-01-31.pdf',
            ]);
        $this->app->instance(DetailedReportPdfService::class, $pdfService);

        $response = $this->actingAs($admin)->get(route('admin.reportes.registros.pdf', $filters));

        $response->assertOk();
        $this->assertSame('%PDF-1.7 filtered', $response->getContent());
    }

    public function test_observations_xlsx_export_applies_date_and_sede_filters(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $selectedSede = Sede::factory()->create(['nombre' => 'Sede Seleccionada']);
        $otherSede = Sede::factory()->create(['nombre' => 'Otra Sede']);

        $this->createSubmission($selectedSede, '2026-01-15 09:00:00', [
            'nombre_completo' => 'Observación Incluida',
            'opcion_a_calificar' => 'Queja',
            'observaciones' => 'Debe aparecer',
        ]);
        $this->createSubmission($selectedSede, '2026-02-15 09:00:00', [
            'nombre_completo' => 'Observación Fuera de Fecha',
            'observaciones' => 'No debe aparecer',
        ]);
        $this->createSubmission($otherSede, '2026-01-15 09:00:00', [
            'nombre_completo' => 'Observación de Otra Sede',
            'observaciones' => 'No debe aparecer',
        ]);

        $filters = [
            'sede_id' => [$selectedSede->id],
            'date_from' => '2026-01-01',
            'date_to' => '2026-01-31',
        ];
        $xlsxService = Mockery::mock(ReportXlsxService::class);
        $xlsxService->shouldReceive('generate')
            ->once()
            ->with(
                ['Sede', 'Nombre Completo', 'Opción Calificada', 'Observaciones', 'Nombre de Mesero'],
                Mockery::on(function ($rows): bool {
                    return collect($rows)->values()->all() === [[
                        'Sede Seleccionada',
                        'Observación Incluida',
                        'Queja',
                        'Debe aparecer',
                        'Mesero de Prueba',
                    ]];
                }),
                Mockery::type('string'),
            )
            ->andReturn([
                'content' => 'xlsx-filtered',
                'filename' => 'reporte-observaciones-pqrsf-2026-01-31.xlsx',
            ]);
        $this->app->instance(ReportXlsxService::class, $xlsxService);

        $response = $this->actingAs($admin)->get(route('admin.reportes.observaciones.xlsx', $filters));

        $response->assertOk();
        $this->assertSame('xlsx-filtered', $response->getContent());
    }

    public function test_observations_pdf_export_applies_date_and_sede_filters(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $selectedSede = Sede::factory()->create(['nombre' => 'Sede Seleccionada']);
        $otherSede = Sede::factory()->create(['nombre' => 'Otra Sede']);

        $this->createSubmission($selectedSede, '2026-01-15 09:00:00', [
            'nombre_completo' => 'Observación Incluida',
            'observaciones' => 'Debe aparecer',
        ]);
        $this->createSubmission($selectedSede, '2026-02-15 09:00:00', [
            'nombre_completo' => 'Observación Fuera de Fecha',
            'observaciones' => 'No debe aparecer',
        ]);
        $this->createSubmission($otherSede, '2026-01-15 09:00:00', [
            'nombre_completo' => 'Observación de Otra Sede',
            'observaciones' => 'No debe aparecer',
        ]);

        $filters = [
            'sede_id' => [$selectedSede->id],
            'date_from' => '2026-01-01',
            'date_to' => '2026-01-31',
        ];
        $pdfService = Mockery::mock(DetailedReportPdfService::class);
        $pdfService->shouldReceive('generate')
            ->once()
            ->with(
                'reports.observations-report-pdf',
                Mockery::on(function (array $data) use ($filters): bool {
                    return $data['filters'] === $filters
                        && $data['total'] === 1
                        && $data['groups']->pluck('sede')->all() === ['Sede Seleccionada']
                        && $data['rows']->pluck('nombre_completo')->all() === ['Observación Incluida'];
                }),
                Mockery::type('string'),
            )
            ->andReturn([
                'content' => '%PDF-1.7 filtered',
                'filename' => 'reporte-observaciones-pqrsf-2026-01-31.pdf',
            ]);
        $this->app->instance(DetailedReportPdfService::class, $pdfService);

        $response = $this->actingAs($admin)->get(route('admin.reportes.observaciones.pdf', $filters));

        $response->assertOk();
        $this->assertSame('%PDF-1.7 filtered', $response->getContent());
    }

    public function test_observations_report_excludes_empty_observations_and_groups_by_sede(): void
    {
        $firstSede = Sede::factory()->create(['nombre' => 'Sede A']);
        $secondSede = Sede::factory()->create(['nombre' => 'Sede B']);

        $this->createSubmission($firstSede, '2026-01-01 09:00:00', [
            'nombre_completo' => 'Cliente Visible',
            'opcion_a_calificar' => 'Queja',
            'observaciones' => 'Comentario visible',
        ]);
        $this->createSubmission($firstSede, '2026-01-02 09:00:00', [
            'nombre_completo' => 'Cliente Nulo',
            'observaciones' => null,
        ]);
        $this->createSubmission($firstSede, '2026-01-03 09:00:00', [
            'nombre_completo' => 'Cliente Vacio',
            'observaciones' => '',
        ]);
        $this->createSubmission($firstSede, '2026-01-04 09:00:00', [
            'nombre_completo' => 'Cliente Espacios',
            'observaciones' => '   ',
        ]);
        $this->createSubmission($secondSede, '2026-01-05 09:00:00', [
            'nombre_completo' => 'Cliente Sede B',
            'observaciones' => 'Otro comentario',
        ]);

        $data = app(ObservationsReportService::class)->getData([
            'sede_id' => null,
            'date_from' => null,
            'date_to' => null,
        ]);

        $this->assertSame(2, $data['total']);
        $this->assertSame(['Sede A', 'Sede B'], $data['groups']->pluck('sede')->all());
        $this->assertSame('Comentario visible', $data['groups'][0]['rows']->first()['observaciones']);
        $this->assertSame('Otro comentario', $data['groups'][1]['rows']->first()['observaciones']);
    }

    public function test_submission_report_page_keeps_filters_and_shows_rows(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $sede = Sede::factory()->create(['nombre' => 'Sede Reporte']);

        $this->createSubmission($sede, '2026-01-15 10:30:00', [
            'nombre_completo' => 'Cliente de Pantalla',
        ]);

        Livewire::actingAs($admin)
            ->test(SubmissionReport::class)
            ->fillForm([
                'filterData.sede_id' => [$sede->id],
                'filterData.date_from' => '2026-01-01',
                'filterData.date_to' => '2026-01-31',
            ])
            ->call('generateReport')
            ->assertSet('showReport', true)
            ->assertSet('appliedFilters', [
                'sede_id' => [$sede->id],
                'date_from' => '2026-01-01',
                'date_to' => '2026-01-31',
            ])
            ->assertSee('Cliente de Pantalla')
            ->assertSee('date_from=2026-01-01')
            ->assertSee('date_to=2026-01-31')
            ->assertSeeHtml('&amp;date_from=2026-01-01')
            ->assertSeeHtml('&amp;date_to=2026-01-31')
            ->assertDontSeeHtml('&amp;amp;date_from=2026-01-01')
            ->assertDontSeeHtml('&amp;amp;date_to=2026-01-31')
            ->assertSee('Descargar XLSX');
    }

    public function test_submission_report_download_url_preserves_multiple_sedes_and_dates(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $sedes = Sede::factory()->createMany([
            ['nombre' => 'Sede A'],
            ['nombre' => 'Sede B'],
        ]);

        $component = Livewire::actingAs($admin)
            ->test(SubmissionReport::class)
            ->fillForm([
                'filterData.sede_id' => [$sedes[0]->id, $sedes[1]->id],
                'filterData.date_from' => '2026-01-01',
                'filterData.date_to' => '2026-01-31',
            ])
            ->call('generateReport');

        parse_str((string) parse_url($component->instance()->getDownloadUrl('pdf'), PHP_URL_QUERY), $query);

        $this->assertSame([$sedes[0]->id, $sedes[1]->id], array_map('intval', $query['sede_id']));
        $this->assertSame('2026-01-01', $query['date_from']);
        $this->assertSame('2026-01-31', $query['date_to']);
    }

    public function test_observations_report_download_url_preserves_multiple_sedes_and_dates(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $sedes = Sede::factory()->createMany([
            ['nombre' => 'Sede A'],
            ['nombre' => 'Sede B'],
        ]);

        $component = Livewire::actingAs($admin)
            ->test(ObservationsReport::class)
            ->fillForm([
                'filterData.sede_id' => [$sedes[0]->id, $sedes[1]->id],
                'filterData.date_from' => '2026-01-01',
                'filterData.date_to' => '2026-01-31',
            ])
            ->call('generateReport');

        parse_str((string) parse_url($component->instance()->getDownloadUrl('xlsx'), PHP_URL_QUERY), $query);

        $this->assertSame([$sedes[0]->id, $sedes[1]->id], array_map('intval', $query['sede_id']));
        $this->assertSame('2026-01-01', $query['date_from']);
        $this->assertSame('2026-01-31', $query['date_to']);
    }

    public function test_observations_report_page_shows_only_observations(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $sede = Sede::factory()->create(['nombre' => 'Sede Observaciones']);

        $this->createSubmission($sede, '2026-01-15 10:30:00', [
            'nombre_completo' => 'Cliente con Comentario',
            'observaciones' => 'Comentario de pantalla',
        ]);
        $this->createSubmission($sede, '2026-01-16 10:30:00', [
            'nombre_completo' => 'Cliente sin Comentario',
            'observaciones' => ' ',
        ]);

        Livewire::actingAs($admin)
            ->test(ObservationsReport::class)
            ->fillForm([
                'filterData.sede_id' => [$sede->id],
                'filterData.date_from' => '2026-01-01',
                'filterData.date_to' => '2026-01-31',
            ])
            ->call('generateReport')
            ->assertSet('showReport', true)
            ->assertSee('Comentario de pantalla')
            ->assertDontSee('Cliente sin Comentario')
            ->assertSee('Sede Observaciones')
            ->assertSeeHtml('&amp;date_from=2026-01-01')
            ->assertSeeHtml('&amp;date_to=2026-01-31')
            ->assertDontSeeHtml('&amp;amp;date_from=2026-01-01')
            ->assertDontSeeHtml('&amp;amp;date_to=2026-01-31');
    }

    public function test_observations_report_renders_tabs_only_for_sedes_with_observations(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $firstSede = Sede::factory()->create(['nombre' => 'Sede Tab A']);
        $secondSede = Sede::factory()->create(['nombre' => 'Sede Tab B']);
        $emptySede = Sede::factory()->create(['nombre' => 'Sede Sin Observaciones']);

        $this->createSubmission($firstSede, '2026-01-15 10:30:00', [
            'nombre_completo' => 'Cliente de la pestaña A',
            'observaciones' => 'Comentario de la sede A',
        ]);
        $this->createSubmission($secondSede, '2026-01-16 10:30:00', [
            'nombre_completo' => 'Cliente de la pestaña B',
            'observaciones' => 'Comentario de la sede B',
        ]);
        $this->createSubmission($emptySede, '2026-01-17 10:30:00', [
            'nombre_completo' => 'Cliente sin observación',
            'observaciones' => ' ',
        ]);

        Livewire::actingAs($admin)
            ->test(ObservationsReport::class)
            ->fillForm([
                'filterData.date_from' => '2026-01-01',
                'filterData.date_to' => '2026-01-31',
            ])
            ->call('generateReport')
            ->assertSeeHtml('id="sede-'.$firstSede->id.'"')
            ->assertSeeHtml('id="sede-'.$secondSede->id.'"')
            ->assertSeeHtml('role="tabpanel"')
            ->assertSeeHtml('aria-controls="sede-'.$firstSede->id.'-panel"')
            ->assertSeeHtml('aria-controls="sede-'.$secondSede->id.'-panel"')
            ->assertSee('Sede Tab A')
            ->assertSee('Sede Tab B')
            ->assertSee('Comentario de la sede A')
            ->assertSee('Comentario de la sede B')
            ->assertDontSeeHtml('id="sede-'.$emptySede->id.'"')
            ->assertDontSeeHtml('aria-controls="sede-'.$emptySede->id.'-panel"')
            ->assertDontSee('Cliente sin observación');
    }

    public function test_submission_xlsx_download_contains_filtered_columns(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $sede = Sede::factory()->create(['nombre' => 'Sede XLSX']);
        $otherSede = Sede::factory()->create(['nombre' => 'Otra Sede XLSX']);

        $this->createSubmission($sede, '2026-01-15 10:30:00', [
            'nombre_completo' => 'Cliente XLSX',
            'nombre_mesero' => 'Mesero XLSX',
        ]);
        $this->createSubmission($sede, '2026-02-15 10:30:00', [
            'nombre_completo' => 'Cliente Fuera de Fecha',
        ]);
        $this->createSubmission($otherSede, '2026-01-15 10:30:00', [
            'nombre_completo' => 'Cliente de Otra Sede',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reportes.registros.xlsx', [
            'sede_id' => [$sede->id],
            'date_from' => '2026-01-01',
            'date_to' => '2026-01-31',
        ]));

        $response
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->assertHeader('Content-Disposition', 'attachment; filename="reporte-registros-pqrsf-'.now()->format('Y-m-d').'.xlsx"');

        $rows = $this->readXlsx($response->getContent());

        $this->assertSame(['Fecha', 'Sede', 'Nombre Completo', 'Nombre de Mesero'], $rows[0]);
        $this->assertCount(2, $rows);
        $this->assertSame('Cliente XLSX', $rows[1][2]);
        $this->assertSame('Mesero XLSX', $rows[1][3]);
    }

    public function test_observations_xlsx_download_includes_sede_and_omits_empty_rows(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $sede = Sede::factory()->create(['nombre' => 'Sede A Observaciones XLSX']);
        $otherSede = Sede::factory()->create(['nombre' => 'Sede B Observaciones XLSX']);

        $this->createSubmission($sede, '2026-01-15 10:30:00', [
            'nombre_completo' => 'Cliente Observación XLSX',
            'opcion_a_calificar' => 'Sugerencia',
            'observaciones' => 'Comentario XLSX',
            'nombre_mesero' => 'Mesero XLSX',
        ]);
        $this->createSubmission($sede, '2026-01-16 10:30:00', [
            'nombre_completo' => 'Cliente sin observación',
            'observaciones' => null,
        ]);
        $this->createSubmission($otherSede, '2026-01-20 10:30:00', [
            'nombre_completo' => 'Cliente Sede B',
            'observaciones' => 'Comentario Sede B',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reportes.observaciones.xlsx'));
        $response->assertOk();

        $rows = $this->readXlsx($response->getContent());

        $this->assertSame([
            'Sede',
            'Nombre Completo',
            'Opción Calificada',
            'Observaciones',
            'Nombre de Mesero',
        ], $rows[0]);
        $this->assertCount(3, $rows);
        $this->assertSame('Sede A Observaciones XLSX', $rows[1][0]);
        $this->assertSame('Comentario XLSX', $rows[1][3]);
        $this->assertSame('Sede B Observaciones XLSX', $rows[2][0]);
    }

    public function test_both_new_pdf_downloads_are_available(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $sede = Sede::factory()->create(['nombre' => 'Sede PDF']);

        $this->createSubmission($sede, '2026-01-15 10:30:00', [
            'observaciones' => 'Comentario PDF',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.reportes.registros.pdf'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->actingAs($admin)
            ->get(route('admin.reportes.observaciones.pdf'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    private function createSubmission(Sede $sede, string $createdAt, array $values): PqrsfSubmission
    {
        $submission = PqrsfSubmission::create([
            'sede_id' => $sede->id,
            'field_values' => array_merge([
                'fecha' => '2026-01-01',
                'nombre_completo' => 'Cliente de Prueba',
                'nombre_mesero' => 'Mesero de Prueba',
                'opcion_a_calificar' => 'Queja',
                'observaciones' => null,
            ], $values),
            'status' => 'pending',
        ]);

        $timestamp = Carbon::parse($createdAt);
        $submission->forceFill([
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ])->saveQuietly();

        return $submission->refresh();
    }

    private function readXlsx(string $content): array
    {
        $path = tempnam(sys_get_temp_dir(), 'pqrsf-test-');
        $this->assertNotFalse($path);
        file_put_contents($path, $content);

        try {
            $spreadsheet = IOFactory::load($path);

            return array_map(
                'array_values',
                $spreadsheet->getActiveSheet()->toArray(null, true, true, false),
            );
        } finally {
            if (isset($spreadsheet)) {
                $spreadsheet->disconnectWorksheets();
            }

            unlink($path);
        }
    }
}
