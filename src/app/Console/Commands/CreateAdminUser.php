<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    protected $signature = 'pqrsf:create-admin
        {email? : Correo electrónico del administrador}
        {--name=Admin : Nombre del administrador}
        {--password= : Contraseña del administrador}';

    protected $description = 'Crea o actualiza un usuario administrador para acceder al panel Filament.';

    public function handle(): int
    {
        $email = trim((string) ($this->argument('email') ?: env('ADMIN_EMAIL')));
        $name = trim((string) ($this->option('name') ?: env('ADMIN_NAME', 'Admin')));
        $password = (string) ($this->option('password') ?: env('ADMIN_PASSWORD'));

        $validator = Validator::make([
            'email' => $email,
            'name' => $name,
            'password' => $password,
        ], [
            'email' => ['required', 'email'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'role' => 'admin',
                'email_verified_at' => now(),
            ],
        );

        $this->info(($user->wasRecentlyCreated ? 'Admin creado' : 'Admin actualizado').': '.$user->email);

        return self::SUCCESS;
    }
}
