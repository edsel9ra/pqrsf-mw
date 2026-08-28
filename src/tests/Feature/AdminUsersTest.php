<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AdminUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_users_and_create_page(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk()
            ->assertSee('Usuarios');

        $this->actingAs($admin)
            ->get('/admin/users/create')
            ->assertOk()
            ->assertSee('Crear Usuario');
    }

    public function test_admin_can_create_a_user_with_a_hashed_password(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(CreateUser::class)
            ->fillForm([
                'name' => 'Usuario de Consulta',
                'email' => 'consulta@example.com',
                'role' => 'user',
                'password' => 'consulta-secret',
                'password_confirmation' => 'consulta-secret',
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $user = User::where('email', 'consulta@example.com')->firstOrFail();

        $this->assertSame('Usuario de Consulta', $user->name);
        $this->assertSame('user', $user->role);
        $this->assertTrue(Hash::check('consulta-secret', $user->password));
        $this->assertNotSame('consulta-secret', $user->password);
    }

    public function test_admin_can_create_another_admin(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(CreateUser::class)
            ->fillForm([
                'name' => 'Administrador Adicional',
                'email' => 'admin-adicional@example.com',
                'role' => 'admin',
                'password' => 'admin-secret',
                'password_confirmation' => 'admin-secret',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'admin-adicional@example.com',
            'role' => 'admin',
        ]);
    }

    public function test_user_role_cannot_access_user_management(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get('/admin/users')
            ->assertForbidden();

        $this->actingAs($user)
            ->get('/admin/users/create')
            ->assertForbidden();
    }

    public function test_user_management_has_no_edit_page_or_table_actions(): void
    {
        $admin = User::factory()->admin()->create();
        $managedUser = User::factory()->create();

        $this->assertSame(['index', 'create'], array_keys(UserResource::getPages()));

        Livewire::actingAs($admin)
            ->test(ListUsers::class)
            ->assertCanSeeTableRecords([$managedUser])
            ->assertTableActionDoesNotExist('edit')
            ->assertTableActionDoesNotExist('delete');
    }

    public function test_user_creation_validates_unique_email_and_password_confirmation(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['email' => 'existing@example.com']);

        Livewire::actingAs($admin)
            ->test(CreateUser::class)
            ->fillForm([
                'name' => 'Usuario inválido',
                'email' => 'existing@example.com',
                'role' => 'invalid',
                'password' => 'short',
                'password_confirmation' => 'different',
            ])
            ->call('create')
            ->assertHasFormErrors(['email', 'password', 'role']);
    }
}
