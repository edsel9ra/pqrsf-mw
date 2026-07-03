<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pqrsf_submissions', function (Blueprint $table) {
            $table->index('status', 'pqrsf_submissions_status_index');
            $table->index('created_at', 'pqrsf_submissions_created_at_index');
        });

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY role VARCHAR(255) NOT NULL DEFAULT 'user'");

        Schema::table('pqrsf_submissions', function (Blueprint $table) {
            $table->dropForeign(['sede_id']);
            $table->foreign('sede_id')->references('id')->on('sedes')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            Schema::table('pqrsf_submissions', function (Blueprint $table) {
                $table->dropForeign(['sede_id']);
                $table->foreign('sede_id')->references('id')->on('sedes')->cascadeOnDelete();
            });

            DB::statement("ALTER TABLE users MODIFY role VARCHAR(255) NOT NULL DEFAULT 'admin'");
        }

        Schema::table('pqrsf_submissions', function (Blueprint $table) {
            $table->dropIndex('pqrsf_submissions_status_index');
            $table->dropIndex('pqrsf_submissions_created_at_index');
        });
    }
};
