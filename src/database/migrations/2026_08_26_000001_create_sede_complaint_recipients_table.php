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
        Schema::create('sede_complaint_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sede_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('nombre')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['sede_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sede_complaint_recipients');
    }
};
