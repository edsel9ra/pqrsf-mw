<?php

namespace Tests\Feature;

use App\Filament\Resources\PqrsfSubmissions\Pages\ListPqrsfSubmissions;
use App\Mail\PqrsfSubmissionMail;
use App\Models\PqrsfSubmission;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPqrsfSubmissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pqrsf_submissions_page_loads(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->makeSubmission();

        $response = $this->actingAs($user)->get('/admin/pqrsf-submissions');

        $response->assertStatus(200);
        $response->assertSee('PQRSF');
    }

    public function test_pending_submission_option_can_be_changed_from_detail_action(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $submission = $this->makeSubmission(['status' => 'pending']);

        Livewire::actingAs($user)
            ->test(ListPqrsfSubmissions::class)
            ->callTableAction(['view', 'changeOption'], $submission, [
                'opcion_a_calificar' => 'Queja',
            ])
            ->assertHasNoTableActionErrors();

        $submission->refresh();

        $this->assertSame('Queja', $submission->field_values['opcion_a_calificar']);
        $this->assertDatabaseHas('submission_logs', [
            'submission_id' => $submission->id,
            'user_id' => $user->id,
            'action' => 'option_changed',
        ]);
    }

    public function test_validated_submission_option_change_action_is_hidden(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $submission = $this->makeSubmission(['status' => 'validated']);

        Livewire::actingAs($user)
            ->test(ListPqrsfSubmissions::class)
            ->assertTableActionHidden(['view', 'changeOption'], $submission);
    }

    public function test_submissions_can_be_filtered_by_option(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $praiseSubmission = $this->makeSubmission();
        $complaintSubmission = $this->makeSubmission();
        $values = $complaintSubmission->field_values;
        $values['opcion_a_calificar'] = 'Queja';
        $complaintSubmission->update(['field_values' => $values]);

        Livewire::actingAs($user)
            ->test(ListPqrsfSubmissions::class)
            ->assertTableFilterExists('opcion_a_calificar')
            ->filterTable('opcion_a_calificar', 'Queja')
            ->assertCanSeeTableRecords([$complaintSubmission])
            ->assertCanNotSeeTableRecords([$praiseSubmission]);
    }

    public function test_submission_pdf_requires_signed_url(): void
    {
        $submission = $this->makeSubmission();

        $this->get(route('pqrsf.submissions.pdf', ['submission' => $submission]))
            ->assertForbidden();
    }

    public function test_signed_submission_pdf_opens_inline(): void
    {
        $submission = $this->makeSubmission();

        $response = $this->get(URL::signedRoute('pqrsf.submissions.pdf', ['submission' => $submission]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('inline', $response->headers->get('Content-Disposition'));
    }

    public function test_submission_email_links_to_pdf_instead_of_admin_panel(): void
    {
        $submission = $this->makeSubmission();
        $html = (new PqrsfSubmissionMail($submission))->render();

        $this->assertStringContainsString('Abrir PDF', $html);
        $this->assertStringContainsString('data:image/png;base64,', $html);
        $this->assertStringContainsString('/pqrsf-submissions/'.$submission->id.'/pdf', $html);
        $this->assertStringNotContainsString('Ver en el panel', $html);
        $this->assertStringNotContainsString('/admin/pqrsf-submissions', $html);
        $this->assertStringNotContainsString('autorizacion_datos', $html);
        $this->assertStringNotContainsString('Autorización', $html);
    }

    private function makeSubmission(array $overrides = []): PqrsfSubmission
    {
        $sede = Sede::factory()->create();

        return PqrsfSubmission::create(array_merge([
            'sede_id' => $sede->id,
            'field_values' => [
                'fecha' => '2026-01-01',
                'sede_id' => $sede->id,
                'nombre_completo' => 'Cliente Test',
                'numero_movil' => '3001234567',
                'correo_electronico' => 'cliente@example.com',
                'opcion_a_calificar' => 'Felicitación',
                'nombre_mesero' => 'Carlos',
                'calificacion_ambientacion' => 5,
                'calificacion_atencion' => 5,
                'calificacion_comida' => 5,
                'calificacion_tiempo' => 5,
                'recomendaria' => true,
                'observaciones' => 'Todo bien',
                'medio_conocimiento' => ['Google'],
                'autorizacion_datos' => true,
            ],
            'status' => 'pending',
        ], $overrides));
    }
}
