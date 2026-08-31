<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('complaint_recipient_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('template_key')->default('full');
            $table->json('excluded_field_keys')->nullable();
            $table->timestamps();
        });

        DB::table('complaint_recipient_profiles')->insert([
            'email' => 'director.administrativosedes@misterwings.com',
            'template_key' => 'without_contact_data',
            'excluded_field_keys' => json_encode([
                'numero_movil',
                'correo_electronico',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_recipient_profiles');
    }
};
