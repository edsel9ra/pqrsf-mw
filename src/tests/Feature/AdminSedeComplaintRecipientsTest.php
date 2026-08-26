<?php

namespace Tests\Feature;

use App\Filament\Resources\SedeComplaintRecipients\Pages\ListSedeComplaintRecipients;
use App\Models\Sede;
use App\Models\SedeComplaintRecipient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminSedeComplaintRecipientsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_complaint_recipients(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $sede = Sede::factory()->create(['nombre' => 'Mister Wings Bochalema']);
        SedeComplaintRecipient::create([
            'sede_id' => $sede->id,
            'email' => 'director.franquicias@misterwings.com',
            'activo' => true,
        ]);

        Livewire::actingAs($user)
            ->test(ListSedeComplaintRecipients::class)
            ->assertCanSeeTableRecords([SedeComplaintRecipient::first()]);
    }
}
