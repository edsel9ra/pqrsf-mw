<?php

namespace Tests\Feature;

use App\Mail\PqrsfSubmissionMail;
use App\Models\FormField;
use App\Models\PqrsfSubmission;
use App\Models\Sede;
use App\Models\SedeComplaintRecipient;
use App\Models\SedeRecipient;
use Database\Seeders\FormFieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PqrsfSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Sede::factory()->create(['id' => 1, 'nombre' => 'Sede Test']);
        $this->seed(FormFieldSeeder::class);
    }

    public function test_form_page_loads(): void
    {
        $response = $this->get('/pqrsf');

        $response->assertStatus(200);
        $response->assertSee('Nombre Completo');
        $response->assertSee('Opción a Calificar');
    }

    public function test_form_requires_sede(): void
    {
        $response = $this->post('/pqrsf', [
            'nombre_completo' => 'Juan Pérez',
            'numero_movil' => '3001234567',
            'correo_electronico' => 'juan@example.com',
            'opcion_a_calificar' => 'Queja',
            'calificacion_ambientacion' => 4,
            'calificacion_atencion' => 4,
            'calificacion_comida' => 4,
            'calificacion_tiempo' => 4,
            'recomendaria' => '1',
            'autorizacion_datos' => '1',
        ]);

        $response->assertSessionHasErrors('sede_id');
    }

    public function test_form_requires_required_fields(): void
    {
        $response = $this->post('/pqrsf', [
            'sede_id' => 1,
        ]);

        $response->assertSessionHasErrors([
            'nombre_completo', 'numero_movil', 'correo_electronico',
            'opcion_a_calificar', 'calificacion_ambientacion',
            'calificacion_atencion', 'calificacion_comida',
            'calificacion_tiempo', 'recomendaria', 'autorizacion_datos',
        ]);
    }

    public function test_valid_submission_creates_record(): void
    {
        Mail::fake();

        $response = $this->post('/pqrsf', [
            'sede_id' => 1,
            'nombre_completo' => 'Juan Pérez',
            'numero_movil' => '3001234567',
            'correo_electronico' => 'juan@example.com',
            'opcion_a_calificar' => 'Queja',
            'nombre_mesero' => 'Carlos',
            'calificacion_ambientacion' => 5,
            'calificacion_atencion' => 4,
            'calificacion_comida' => 3,
            'calificacion_tiempo' => 2,
            'recomendaria' => '1',
            'observaciones' => 'Buena experiencia',
            'medio_conocimiento' => ['Google', 'Recomendación'],
            'autorizacion_datos' => '1',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/pqrsf/gracias');

        $this->assertDatabaseHas('pqrsf_submissions', [
            'sede_id' => 1,
            'status' => 'pending',
        ]);

        $submission = PqrsfSubmission::first();
        $this->assertEquals('Juan Pérez', $submission->field_values['nombre_completo']);
        $this->assertEquals('Queja', $submission->field_values['opcion_a_calificar']);
        $this->assertEquals('juan@example.com', $submission->field_values['correo_electronico']);
        $this->assertEquals(5, $submission->field_values['calificacion_ambientacion']);
        $this->assertTrue($submission->field_values['recomendaria']);
        $this->assertTrue($submission->field_values['autorizacion_datos']);
        $this->assertIsArray($submission->field_values['medio_conocimiento']);
    }

    public function test_complaint_is_sent_to_active_complaint_recipients(): void
    {
        Mail::fake();

        Sede::findOrFail(1)->update(['nombre' => 'Mister Wings Bochalema']);
        SedeRecipient::create([
            'sede_id' => 1,
            'email' => 'recipient-from-other-flow@example.com',
            'nombre' => 'Destinatario del flujo administrativo',
            'activo' => true,
        ]);
        SedeComplaintRecipient::create([
            'sede_id' => 1,
            'email' => 'director.franquicias@misterwings.com',
            'activo' => true,
        ]);
        SedeComplaintRecipient::create([
            'sede_id' => 1,
            'email' => 'director.administrativosedes@misterwings.com',
            'activo' => true,
        ]);
        SedeComplaintRecipient::create([
            'sede_id' => 1,
            'email' => 'inactive@misterwings.com',
            'activo' => false,
        ]);

        $response = $this->post('/pqrsf', $this->validPayload());

        $response->assertRedirect('/pqrsf/gracias');
        Mail::assertSent(PqrsfSubmissionMail::class, 2);
        Mail::assertSent(PqrsfSubmissionMail::class, function (PqrsfSubmissionMail $mail): bool {
            return $mail->hasTo('director.franquicias@misterwings.com');
        });
        Mail::assertSent(PqrsfSubmissionMail::class, function (PqrsfSubmissionMail $mail): bool {
            return $mail->hasTo('director.administrativosedes@misterwings.com');
        });
        Mail::assertNotSent(PqrsfSubmissionMail::class, function (PqrsfSubmissionMail $mail): bool {
            return $mail->hasTo('recipient-from-other-flow@example.com')
                || $mail->hasTo('inactive@misterwings.com');
        });
    }

    public function test_non_complaint_submission_does_not_send_email(): void
    {
        Mail::fake();

        Sede::findOrFail(1)->update(['nombre' => 'Mister Wings Bochalema']);
        SedeComplaintRecipient::create([
            'sede_id' => 1,
            'email' => 'director.franquicias@misterwings.com',
            'activo' => true,
        ]);

        $response = $this->post('/pqrsf', $this->validPayload([
            'opcion_a_calificar' => 'Petición',
        ]));

        $response->assertRedirect('/pqrsf/gracias');
        Mail::assertNothingSent();
    }

    public function test_complaint_without_recipients_is_saved_and_logged(): void
    {
        Mail::fake();
        Log::spy();

        $response = $this->post('/pqrsf', $this->validPayload());

        $response->assertRedirect('/pqrsf/gracias');
        $this->assertDatabaseHas('pqrsf_submissions', [
            'sede_id' => 1,
            'status' => 'pending',
        ]);
        Mail::assertNothingSent();
        Log::shouldHaveReceived('warning')->once();
    }

    public function test_authorization_data_can_be_declined(): void
    {
        $response = $this->post('/pqrsf', $this->validPayload([
            'autorizacion_datos' => '0',
        ]));

        $response->assertSessionHasNoErrors();

        $submission = PqrsfSubmission::first();

        $this->assertFalse($submission->field_values['autorizacion_datos']);
    }

    public function test_inactive_sede_cannot_receive_submission(): void
    {
        Sede::factory()->create(['id' => 2, 'nombre' => 'Sede Inactiva', 'activo' => false]);

        $response = $this->post('/pqrsf', $this->validPayload(['sede_id' => 2]));

        $response->assertSessionHasErrors('sede_id');
        $this->assertDatabaseCount('pqrsf_submissions', 0);
    }

    public function test_inactive_form_field_is_not_stored(): void
    {
        FormField::where('key', 'nombre_mesero')->update(['activo' => false]);

        $response = $this->post('/pqrsf', $this->validPayload(['nombre_mesero' => 'Carlos']));

        $response->assertSessionHasNoErrors();

        $submission = PqrsfSubmission::first();
        $this->assertArrayNotHasKey('nombre_mesero', $submission->field_values);
    }

    public function test_thanks_page_loads(): void
    {
        $response = $this->get('/pqrsf/gracias');

        $response->assertStatus(200);
        $response->assertSee('Gracias');
    }

    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'sede_id' => 1,
            'nombre_completo' => 'Juan Pérez',
            'numero_movil' => '3001234567',
            'correo_electronico' => 'juan@example.com',
            'opcion_a_calificar' => 'Queja',
            'nombre_mesero' => 'Carlos',
            'calificacion_ambientacion' => 5,
            'calificacion_atencion' => 4,
            'calificacion_comida' => 3,
            'calificacion_tiempo' => 2,
            'recomendaria' => '1',
            'observaciones' => 'Buena experiencia',
            'medio_conocimiento' => ['Google', 'Recomendación'],
            'autorizacion_datos' => '1',
        ], $overrides);
    }
}
