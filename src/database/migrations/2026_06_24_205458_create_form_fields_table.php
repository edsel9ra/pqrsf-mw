<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('type'); // text, select, rating, boolean, email, tel, textarea
            $table->json('options')->nullable(); // for select types
            $table->json('validation_rules')->nullable();
            $table->integer('orden')->default(0);
            $table->boolean('requerido')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
