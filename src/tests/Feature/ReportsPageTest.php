<?php

namespace Tests\Feature;

use App\Filament\Pages\Reports;
use App\Mail\ReportPdfMail;
use App\Models\Sede;
use App\Models\SedeRecipient;
use App\Models\User;
use App\Services\ReportPdfService;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class ReportsPageTest extends TestCase
{
    use RefreshDatabase;

    protected bool $isMysql = false;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->isMysql = DB::connection()->getDriverName() === 'mysql';
    }

    public function test_reports_page_requires_auth()
    {
        $response = $this->get('/admin/reports');
        $response->assertRedirect('/admin/login');
    }

    public function test_reports_page_loads()
    {
        $user = User::first();
        $response = $this->actingAs($user)->get('/admin/reports');
        $response->assertStatus(200);
    }

    public function test_pdf_download_requires_auth()
    {
        $response = $this->get('/admin/reportes/pdf');
        $response->assertRedirect('/admin/login');
    }

    public function test_pdf_download_is_available_to_read_only_users(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $pdfService = Mockery::mock(ReportPdfService::class);
        $pdfService->shouldReceive('generate')
            ->once()
            ->andReturn([
                'content' => '%PDF-1.7 read-only user',
                'data' => [],
                'filename' => 'reporte-pqrsf-test.pdf',
            ]);
        $this->app->instance(ReportPdfService::class, $pdfService);

        $response = $this->actingAs($user)->get('/admin/reportes/pdf');

        $response->assertOk();
        $this->assertSame('%PDF-1.7 read-only user', $response->getContent());
    }

    /** @requires extension pdo_mysql */
    public function test_pdf_download()
    {
        if (! $this->isMysql) {
            $this->markTestSkipped('Requires MySQL (JSON functions)');
        }

        $user = User::first();
        $response = $this->actingAs($user)->get('/admin/reportes/pdf');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_pdf_download_uses_the_shared_pdf_service(): void
    {
        $user = User::first();
        $pdfService = Mockery::mock(ReportPdfService::class);
        $pdfService->shouldReceive('generate')
            ->once()
            ->with([
                'date_from' => '2026-01-01',
                'date_to' => '2026-01-31',
            ])
            ->andReturn([
                'content' => '%PDF-1.7 test',
                'data' => [],
                'filename' => 'reporte-pqrsf-2026-01-31.pdf',
            ]);
        $this->app->instance(ReportPdfService::class, $pdfService);

        $response = $this->actingAs($user)->get('/admin/reportes/pdf?date_from=2026-01-01&date_to=2026-01-31');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition', 'attachment; filename="reporte-pqrsf-2026-01-31.pdf"');
        $this->assertSame('%PDF-1.7 test', $response->getContent());
    }

    public function test_reports_page_loads_for_read_only_user(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get('/admin/reports')
            ->assertOk()
            ->assertSee('Reportes');
    }

    public function test_read_only_user_cannot_send_a_report_by_email(): void
    {
        Mail::fake();
        $user = User::factory()->create(['role' => 'user']);
        $pdfService = Mockery::mock(ReportPdfService::class);
        $pdfService->shouldReceive('getData')
            ->once()
            ->andReturn($this->emptyReportData());
        $this->app->instance(ReportPdfService::class, $pdfService);

        Livewire::actingAs($user)
            ->test(Reports::class)
            ->call('generateReport')
            ->assertDontSee('Enviar por correo')
            ->call('sendReport')
            ->assertStatus(403);

        Mail::assertNothingSent();
    }

    public function test_pdf_download_accepts_multiple_sedes(): void
    {
        $user = User::first();
        $sedes = Sede::factory()->count(2)->create();
        $filters = [
            'sede_id' => $sedes->pluck('id')->all(),
            'date_from' => '2026-01-01',
            'date_to' => '2026-01-31',
        ];

        $pdfService = Mockery::mock(ReportPdfService::class);
        $pdfService->shouldReceive('generate')
            ->once()
            ->with($filters)
            ->andReturn([
                'content' => '%PDF-1.7 multi-sede',
                'data' => [],
                'filename' => 'reporte-pqrsf-2026-01-31.pdf',
            ]);
        $this->app->instance(ReportPdfService::class, $pdfService);

        $response = $this->actingAs($user)->get('/admin/reportes/pdf?'.http_build_query($filters));

        $response->assertOk();
        $this->assertSame('%PDF-1.7 multi-sede', $response->getContent());
    }

    public function test_report_download_url_preserves_multiple_sedes_and_dates(): void
    {
        $user = User::first();
        $sedes = Sede::factory()->createMany([
            ['nombre' => 'Sede A'],
            ['nombre' => 'Sede B'],
        ]);
        $filters = [
            'sede_id' => [$sedes[0]->id, $sedes[1]->id],
            'date_from' => '2026-01-01',
            'date_to' => '2026-01-31',
            'option_type' => 'Queja',
            'rating_category' => 'tiempo',
        ];

        $pdfService = Mockery::mock(ReportPdfService::class);
        $pdfService->shouldReceive('getData')
            ->once()
            ->with($filters)
            ->andReturn($this->emptyReportData());
        $this->app->instance(ReportPdfService::class, $pdfService);

        $component = Livewire::actingAs($user)
            ->test(Reports::class)
            ->fillForm([
                'filterData.sede_id' => $filters['sede_id'],
                'filterData.date_from' => $filters['date_from'],
                'filterData.date_to' => $filters['date_to'],
                'filterData.option_type' => $filters['option_type'],
                'filterData.rating_category' => $filters['rating_category'],
            ])
            ->call('generateReport');

        parse_str((string) parse_url($component->instance()->getDownloadUrl('pdf'), PHP_URL_QUERY), $query);

        $this->assertSame($filters['sede_id'], array_map('intval', $query['sede_id']));
        $this->assertSame($filters['date_from'], $query['date_from']);
        $this->assertSame($filters['date_to'], $query['date_to']);
        $this->assertSame($filters['option_type'], $query['option_type']);
        $this->assertSame($filters['rating_category'], $query['rating_category']);
    }

    public function test_report_pdf_does_not_include_removed_status_sections(): void
    {
        $reportData = $this->emptyReportData();
        $reportData['optionsBreakdown'] = collect([(object) [
            'opcion' => 'Queja',
            'total' => 0,
            'porcentaje' => 0,
        ]]);

        $html = view('reports.pdf', [
            ...$reportData,
            'logoSrc' => '',
        ])->render();

        $this->assertStringNotContainsString('Validados', $html);
        $this->assertStringNotContainsString('Enviados', $html);
        $this->assertStringNotContainsString('Pendientes', $html);
        $this->assertStringNotContainsString('Distribución por estado', $html);
        $this->assertStringNotContainsString('Distribución por Estado', $html);
        $this->assertStringContainsString('Distribución por opción', $html);
    }

    public function test_report_pdf_only_shows_sede_comparison_for_multiple_sedes(): void
    {
        $reportData = $this->emptyReportData();
        $reportData['ratingsBySede'] = collect([(object) [
            'sede_nombre' => 'Sede Principal',
            'ambientacion' => 4,
            'atencion' => 4,
            'comida' => 4,
            'tiempo' => 4,
            'promedio' => 4,
        ]]);

        $html = view('reports.pdf', [
            ...$reportData,
            'logoSrc' => '',
        ])->render();

        $this->assertStringNotContainsString('Calificaciones por sede', $html);

        $reportData['showRatingComparison'] = true;
        $html = view('reports.pdf', [
            ...$reportData,
            'logoSrc' => '',
        ])->render();

        $this->assertStringContainsString('Calificaciones por sede', $html);
    }

    public function test_report_email_contains_filters_and_pdf_attachment(): void
    {
        $mail = new ReportPdfMail(
            pdfContent: '%PDF-1.7 report',
            filename: 'reporte-pqrsf-2026-08-25.pdf',
            scopeLabel: 'Sede: Sede Principal',
            filterLabels: ['Sede: Sede Principal', 'Desde: 01/08/2026'],
            generatedAt: '25/08/2026 10:00:00',
        );

        $html = $mail->render();

        $this->assertStringContainsString('Sede Principal', $html);
        $this->assertStringContainsString('Desde: 01/08/2026', $html);
        $this->assertStringContainsString('reporte-pqrsf-2026-08-25.pdf', $mail->attachments()[0]->as);
        $this->assertSame('application/pdf', $mail->attachments()[0]->mime);
    }

    public function test_report_screen_does_not_include_removed_status_sections(): void
    {
        $pdfService = Mockery::mock(ReportPdfService::class);
        $pdfService->shouldReceive('getData')
            ->once()
            ->andReturn($this->emptyReportData());
        $this->app->instance(ReportPdfService::class, $pdfService);

        Livewire::actingAs(User::first())
            ->test(Reports::class)
            ->call('generateReport')
            ->assertSet('showReport', true)
            ->assertSee('Resumen PQRSF')
            ->assertDontSee('Pendientes')
            ->assertDontSee('Validados')
            ->assertDontSee('Enviados')
            ->assertDontSee('Distribución por estado')
            ->assertDontSee('Distribución por Estado');
    }

    public function test_report_screen_hides_sede_comparison_for_a_single_sede(): void
    {
        $reportData = $this->emptyReportData();
        $reportData['ratingsBySede'] = collect([(object) [
            'sede_nombre' => 'Sede Principal',
            'ambientacion' => 4,
            'atencion' => 4,
            'comida' => 4,
            'tiempo' => 4,
            'promedio' => 4,
        ]]);

        $pdfService = Mockery::mock(ReportPdfService::class);
        $pdfService->shouldReceive('getData')
            ->once()
            ->andReturn($reportData);
        $this->app->instance(ReportPdfService::class, $pdfService);

        Livewire::actingAs(User::first())
            ->test(Reports::class)
            ->call('generateReport')
            ->assertSee('Resumen PQRSF')
            ->assertDontSee('Calificaciones por sede');
    }

    public function test_generated_report_keeps_the_applied_filters_for_export(): void
    {
        $sede = Sede::first();
        $filters = [
            'sede_id' => [$sede->id],
            'date_from' => '2026-01-01',
            'date_to' => '2026-01-31',
            'option_type' => 'Queja',
            'rating_category' => 'tiempo',
        ];

        $pdfService = Mockery::mock(ReportPdfService::class);
        $pdfService->shouldReceive('getData')
            ->once()
            ->with($filters)
            ->andReturn($this->emptyReportData());
        $this->app->instance(ReportPdfService::class, $pdfService);

        Livewire::actingAs(User::first())
            ->test(Reports::class)
            ->fillForm([
                'filterData.sede_id' => [$sede->id],
                'filterData.date_from' => '2026-01-01',
                'filterData.date_to' => '2026-01-31',
                'filterData.option_type' => 'Queja',
                'filterData.rating_category' => 'tiempo',
            ])
            ->call('generateReport')
            ->assertSet('appliedFilters', $filters)
            ->assertSeeHtml('&amp;date_from=2026-01-01')
            ->assertSeeHtml('&amp;date_to=2026-01-31')
            ->assertDontSeeHtml('&amp;amp;date_from=2026-01-01')
            ->assertDontSeeHtml('&amp;amp;date_to=2026-01-31');
    }

    public function test_report_is_sent_only_to_active_recipients_of_the_selected_sede(): void
    {
        Mail::fake();

        $user = User::first();
        $selectedSede = Sede::factory()->create(['nombre' => 'Sede Seleccionada']);
        $otherSede = Sede::factory()->create(['nombre' => 'Otra Sede']);
        $activeRecipient = SedeRecipient::create([
            'sede_id' => $selectedSede->id,
            'email' => 'activo@example.com',
            'nombre' => 'Destinatario Activo',
            'activo' => true,
        ]);
        $inactiveRecipient = SedeRecipient::create([
            'sede_id' => $selectedSede->id,
            'email' => 'inactivo@example.com',
            'nombre' => 'Destinatario Inactivo',
            'activo' => false,
        ]);
        $otherRecipient = SedeRecipient::create([
            'sede_id' => $otherSede->id,
            'email' => 'otra-sede@example.com',
            'nombre' => 'Otra Sede',
            'activo' => true,
        ]);

        $pdfService = Mockery::mock(ReportPdfService::class);
        $pdfService->shouldReceive('generate')
            ->once()
            ->andReturn([
                'content' => '%PDF-1.7 report',
                'data' => [
                    'filterLabels' => ['Sede: Sede Seleccionada'],
                    'generatedAt' => '25/08/2026 10:00:00',
                ],
                'filename' => 'reporte-pqrsf-2026-08-25.pdf',
            ]);
        $this->app->instance(ReportPdfService::class, $pdfService);

        Livewire::actingAs($user)
            ->test(Reports::class)
            ->set('reportData', $this->emptyReportData())
            ->set('appliedFilters', ['sede_id' => [$selectedSede->id]])
            ->set('showReport', true)
            ->call('sendReport')
            ->assertHasNoErrors();

        Mail::assertSent(ReportPdfMail::class, 1);
        Mail::assertSent(ReportPdfMail::class, function (ReportPdfMail $mail) use ($activeRecipient): bool {
            return $mail->hasTo($activeRecipient->email)
                && $mail->pdfContent === '%PDF-1.7 report'
                && $mail->filename === 'reporte-pqrsf-2026-08-25.pdf'
                && $mail->attachments()[0]->mime === 'application/pdf';
        });
        Mail::assertNotSent(ReportPdfMail::class, function (ReportPdfMail $mail) use ($inactiveRecipient, $otherRecipient): bool {
            return $mail->hasTo($inactiveRecipient->email) || $mail->hasTo($otherRecipient->email);
        });
    }

    public function test_report_sends_one_consolidated_pdf_per_email_for_assigned_sedes(): void
    {
        Mail::fake();

        $user = User::first();
        $firstSede = Sede::factory()->create(['nombre' => 'Sede A']);
        $secondSede = Sede::factory()->create(['nombre' => 'Sede B']);

        SedeRecipient::create([
            'sede_id' => $firstSede->id,
            'email' => 'shared@example.com',
            'nombre' => 'Destinatario Compartido',
            'activo' => true,
        ]);
        SedeRecipient::create([
            'sede_id' => $secondSede->id,
            'email' => ' SHARED@example.com ',
            'nombre' => 'Destinatario Compartido',
            'activo' => true,
        ]);
        SedeRecipient::create([
            'sede_id' => $secondSede->id,
            'email' => 'sede-b@example.com',
            'nombre' => 'Destinatario Sede B',
            'activo' => true,
        ]);

        $generatedScopes = [];
        $pdfService = Mockery::mock(ReportPdfService::class);
        $pdfService->shouldReceive('generate')
            ->twice()
            ->andReturnUsing(function (array $filters) use (&$generatedScopes): array {
                $scope = array_map('intval', $filters['sede_id']);
                sort($scope, SORT_NUMERIC);
                $generatedScopes[] = $scope;

                return [
                    'content' => 'pdf-'.implode('-', $scope),
                    'data' => [
                        'filterLabels' => [],
                        'generatedAt' => '25/08/2026 10:00:00',
                    ],
                    'filename' => 'reporte-pqrsf-2026-08-25.pdf',
                ];
            });
        $this->app->instance(ReportPdfService::class, $pdfService);

        Livewire::actingAs($user)
            ->test(Reports::class)
            ->set('reportData', $this->emptyReportData())
            ->set('appliedFilters', ['sede_id' => [$firstSede->id, $secondSede->id]])
            ->set('showReport', true)
            ->call('sendReport')
            ->assertHasNoErrors();

        sort($generatedScopes[0], SORT_NUMERIC);
        sort($generatedScopes[1], SORT_NUMERIC);
        $this->assertEqualsCanonicalizing([
            [$firstSede->id, $secondSede->id],
            [$secondSede->id],
        ], $generatedScopes);

        Mail::assertSent(ReportPdfMail::class, 2);
        Mail::assertSent(ReportPdfMail::class, function (ReportPdfMail $mail) use ($firstSede, $secondSede): bool {
            return $mail->hasTo('shared@example.com')
                && $mail->scopeLabel === 'Sedes: Sede A, Sede B'
                && $mail->pdfContent === 'pdf-'.$firstSede->id.'-'.$secondSede->id;
        });
        Mail::assertSent(ReportPdfMail::class, function (ReportPdfMail $mail) use ($secondSede): bool {
            return $mail->hasTo('sede-b@example.com')
                && $mail->scopeLabel === 'Sede: Sede B'
                && $mail->pdfContent === 'pdf-'.$secondSede->id;
        });
    }

    public function test_report_email_requires_a_selected_sede(): void
    {
        Mail::fake();

        $pdfService = Mockery::mock(ReportPdfService::class);
        $pdfService->shouldNotReceive('generate');
        $this->app->instance(ReportPdfService::class, $pdfService);

        Livewire::actingAs(User::first())
            ->test(Reports::class)
            ->set('reportData', $this->emptyReportData())
            ->set('appliedFilters', ['sede_id' => null])
            ->set('showReport', true)
            ->call('sendReport')
            ->assertHasNoErrors();

        Mail::assertNothingSent();
    }

    public function test_report_email_requires_active_recipients(): void
    {
        Mail::fake();

        $sede = Sede::factory()->create();
        SedeRecipient::create([
            'sede_id' => $sede->id,
            'email' => 'inactivo@example.com',
            'nombre' => 'Destinatario Inactivo',
            'activo' => false,
        ]);

        $pdfService = Mockery::mock(ReportPdfService::class);
        $pdfService->shouldNotReceive('generate');
        $this->app->instance(ReportPdfService::class, $pdfService);

        Livewire::actingAs(User::first())
            ->test(Reports::class)
            ->set('reportData', $this->emptyReportData())
            ->set('appliedFilters', ['sede_id' => [$sede->id]])
            ->set('showReport', true)
            ->call('sendReport')
            ->assertHasNoErrors();

        Mail::assertNothingSent();
    }

    public function test_report_email_blocks_when_any_selected_sede_has_no_active_recipients(): void
    {
        Mail::fake();

        $firstSede = Sede::factory()->create();
        $secondSede = Sede::factory()->create();
        SedeRecipient::create([
            'sede_id' => $firstSede->id,
            'email' => 'activo@example.com',
            'nombre' => 'Destinatario Activo',
            'activo' => true,
        ]);
        SedeRecipient::create([
            'sede_id' => $secondSede->id,
            'email' => 'inactivo@example.com',
            'nombre' => 'Destinatario Inactivo',
            'activo' => false,
        ]);

        $pdfService = Mockery::mock(ReportPdfService::class);
        $pdfService->shouldNotReceive('generate');
        $this->app->instance(ReportPdfService::class, $pdfService);

        Livewire::actingAs(User::first())
            ->test(Reports::class)
            ->set('reportData', $this->emptyReportData())
            ->set('appliedFilters', ['sede_id' => [$firstSede->id, $secondSede->id]])
            ->set('showReport', true)
            ->call('sendReport')
            ->assertHasNoErrors();

        Mail::assertNothingSent();
    }

    public function test_report_email_blocks_active_recipients_without_an_email(): void
    {
        Mail::fake();

        $sede = Sede::factory()->create();
        SedeRecipient::create([
            'sede_id' => $sede->id,
            'email' => '   ',
            'nombre' => 'Destinatario Inválido',
            'activo' => true,
        ]);

        $pdfService = Mockery::mock(ReportPdfService::class);
        $pdfService->shouldNotReceive('generate');
        $this->app->instance(ReportPdfService::class, $pdfService);

        Livewire::actingAs(User::first())
            ->test(Reports::class)
            ->set('reportData', $this->emptyReportData())
            ->set('appliedFilters', ['sede_id' => [$sede->id]])
            ->set('showReport', true)
            ->call('sendReport')
            ->assertHasNoErrors();

        Mail::assertNothingSent();
    }

    public function test_report_service_no_longer_exposes_status_distribution(): void
    {
        if (! $this->isMysql) {
            $this->markTestSkipped('Requires MySQL (JSON functions)');
        }

        $data = ReportService::make()->getAll();

        $this->assertArrayNotHasKey('validated', $data['stats']);
        $this->assertArrayNotHasKey('sent', $data['stats']);
        $this->assertArrayNotHasKey('pending', $data['stats']);
        $this->assertArrayNotHasKey('statusDistribution', $data);
    }

    public function test_report_service_normalizes_multiple_sede_filters_and_labels(): void
    {
        $sedes = Sede::factory()->createMany([
            ['nombre' => 'Sede A'],
            ['nombre' => 'Sede B'],
        ]);

        $service = ReportService::make([
            $sedes[1]->id,
            $sedes[0]->id,
            $sedes[1]->id,
        ]);

        $this->assertSame([$sedes[1]->id, $sedes[0]->id], $service->getFilterParams()['sede_id']);
        $this->assertSame(['Sedes: Sede A, Sede B'], $service->getFilterLabels());
        $this->assertSame(2, $service->getComparisonSedeCount());
    }

    private function emptyReportData(): array
    {
        return [
            'filters' => [],
            'filterLabels' => [],
            'stats' => [
                'total' => 0,
                'avg_ambientacion' => 0,
                'avg_atencion' => 0,
                'avg_comida' => 0,
                'avg_tiempo' => 0,
                'avg_general' => 0,
            ],
            'ratingsBySede' => collect(),
            'optionsBreakdown' => collect(),
            'dailySubmissions' => collect(),
            'ratingAverages' => [],
            'pqrsfBySede' => collect(),
            'ratingPercentagesBySede' => collect(),
            'comparisonSedeCount' => 0,
            'showRatingComparison' => false,
            'generatedAt' => '25/08/2026 10:00:00',
        ];
    }
}
