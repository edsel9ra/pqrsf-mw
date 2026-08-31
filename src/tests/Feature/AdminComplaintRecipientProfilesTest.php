<?php

namespace Tests\Feature;

use App\Filament\Resources\ComplaintRecipientProfiles\Pages\ListComplaintRecipientProfiles;
use App\Models\ComplaintRecipientProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminComplaintRecipientProfilesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_complaint_recipient_profiles(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $profile = ComplaintRecipientProfile::create([
            'email' => 'recipient@example.com',
            'template_key' => ComplaintRecipientProfile::TEMPLATE_FULL,
            'excluded_field_keys' => [],
        ]);

        Livewire::actingAs($user)
            ->test(ListComplaintRecipientProfiles::class)
            ->assertCanSeeTableRecords([$profile]);
    }
}
