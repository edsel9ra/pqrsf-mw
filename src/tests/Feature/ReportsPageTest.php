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

    public function test_pdf_download_requires_admin_role()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/admin/reportes/pdf');

        $response->assertForbidden();
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
        $this->assertStringNotContainsString('Distribución por estado', $html);
        $this->assertStringNotContainsString('Distribución por Estado', $html);
        $this->assertStringContainsString('Distribución por opción', $html);
    }

    public function test_report_email_contains_filters_and_pdf_attachment(): void
    {
        $mail = new ReportPdfMail(
            pdfContent: '%PDF-1.7 report',
            filename: 'reporte-pqrsf-2026-08-25.pdf',
            sedeName: 'Sede Principal',
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
            ->assertDontSee('Validados')
            ->assertDontSee('Enviados')
            ->assertDontSee('Distribución por estado')
            ->assertDontSee('Distribución por Estado');
    }

    public function test_generated_report_keeps_the_applied_filters_for_export(): void
    {
        $sede = Sede::first();
        $filters = [
            'sede_id' => $sede->id,
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
                'filterData.sede_id' => $sede->id,
                'filterData.date_from' => '2026-01-01',
                'filterData.date_to' => '2026-01-31',
                'filterData.option_type' => 'Queja',
                'filterData.rating_category' => 'tiempo',
            ])
            ->call('generateReport')
            ->assertSet('appliedFilters', $filters);
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
            ->set('appliedFilters', ['sede_id' => $selectedSede->id])
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
            ->set('appliedFilters', ['sede_id' => $sede->id])
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
        $this->assertArrayNotHasKey('statusDistribution', $data);
    }

    private function emptyReportData(): array
    {
        return [
            'filters' => [],
            'filterLabels' => [],
            'stats' => [
                'total' => 0,
                'pending' => 0,
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
            'generatedAt' => '25/08/2026 10:00:00',
        ];
    }
}
