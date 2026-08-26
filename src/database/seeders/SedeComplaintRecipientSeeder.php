<?php

namespace Database\Seeders;

use App\Models\Sede;
use App\Models\SedeComplaintRecipient;
use Illuminate\Database\Seeder;

class SedeComplaintRecipientSeeder extends Seeder
{
    public function run(): void
    {
        $recipientsBySede = [
            'Mister Wings Bochalema' => [
                'director.franquicias@misterwings.com',
                'director.administrativosedes@misterwings.com',
            ],
            'Mister Wings Chipichape' => [
                'director.franquicias@misterwings.com',
            ],
            'Mister Wings Ciudad Jardín' => [
                'director.franquicias@misterwings.com',
                'director.administrativosedes@misterwings.com',
            ],
            'Mister Wings Flora' => [
                'director.franquicias@misterwings.com',
            ],
            'Mister Wings Granada' => [
                'director.franquicias@misterwings.com',
            ],
            'Mister Wings Jardín Plaza' => [
                'director.franquicias@misterwings.com',
                'director.administrativosedes@misterwings.com',
            ],
            'Mister Wings Limonar' => [
                'director.franquicias@misterwings.com',
            ],
            'Mister Wings Palmira' => [
                'director.franquicias@misterwings.com',
            ],
            'Mister Wings Pance' => [
                'director.franquicias@misterwings.com',
                'director.administrativosedes@misterwings.com',
            ],
            'Mister Wings San Fernando' => [
                'director.franquicias@misterwings.com',
            ],
            'Mister Wings Unicentro' => [
                'director.franquicias@misterwings.com',
                'director.administrativosedes@misterwings.com',
            ],
        ];

        foreach ($recipientsBySede as $sedeName => $emails) {
            $sede = Sede::firstOrCreate([
                'nombre' => $sedeName,
            ], [
                'activo' => true,
            ]);

            foreach ($emails as $email) {
                SedeComplaintRecipient::firstOrCreate([
                    'sede_id' => $sede->id,
                    'email' => $email,
                ], [
                    'activo' => true,
                ]);
            }
        }
    }
}
