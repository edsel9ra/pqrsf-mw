<?php

namespace Tests\Feature;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\ImportPqrsfCsv;
use App\Filament\Pages\ObservationsReport;
use App\Filament\Pages\Reports;
use App\Filament\Pages\SubmissionReport;
use App\Models\FormField;
use App\Models\Sede;
use App\Models\SedeComplaintRecipient;
use App\Models\SedeRecipient;
use App\Models\SubmissionLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ReadOnlyUserAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_role_can_access_the_read_only_panel_pages(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user);

        $this->assertTrue($user->canAccessReadOnlyPanel());
        $this->assertTrue(Dashboard::canAccess());
        $this->assertTrue(Reports::canAccess());
        $this->assertTrue(SubmissionReport::canAccess());
        $this->assertTrue(ObservationsReport::canAccess());
        $this->assertFalse(ImportPqrsfCsv::canAccess());

        $this->get('/admin/reports')
            ->assertOk()
            ->assertSee('Escritorio')
            ->assertSee('PQRSF')
            ->assertSee('Reportes')
            ->assertDontSee('Configuración')
            ->assertDontSee('Historial')
            ->assertDontSee('Importar CSV');
    }

    public function test_user_role_cannot_access_admin_only_resources(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);

        foreach ([
            FormField::class,
            Sede::class,
            SedeComplaintRecipient::class,
            SedeRecipient::class,
            SubmissionLog::class,
        ] as $model) {
            $this->assertFalse(Gate::allows('viewAny', $model));
        }

        foreach ([
            '/admin/form-fields',
            '/admin/import-pqrsf-csv',
            '/admin/sede-complaint-recipients',
            '/admin/sede-recipients',
            '/admin/sedes',
            '/admin/submission-logs',
        ] as $url) {
            $this->get($url)->assertForbidden();
        }
    }

    public function test_unknown_role_cannot_access_the_panel(): void
    {
        $user = User::factory()->create(['role' => 'manager']);

        $this->assertFalse($user->canAccessReadOnlyPanel());
        $this->actingAs($user)
            ->get('/admin/reports')
            ->assertForbidden();
    }
}
