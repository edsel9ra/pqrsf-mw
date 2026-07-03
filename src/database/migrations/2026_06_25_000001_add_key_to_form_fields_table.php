<?php

use App\Services\FormFieldService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('form_fields', 'key')) {
            Schema::table('form_fields', function (Blueprint $table) {
                $table->string('key')->nullable()->after('label');
            });
        }

        $used = [];
        $legacyLabels = [
            'Calificación Ambientación' => 'calificacion_ambientacion',
            'Calificación Atención' => 'calificacion_atencion',
            'Calificación Comida' => 'calificacion_comida',
            'Calificación Tiempo de Entrega' => 'calificacion_tiempo',
            'Medio de Conocimiento' => 'medio_conocimiento',
            'Autorización de Datos' => 'autorizacion_datos',
        ];

        $defaultsByLabel = collect(FormFieldService::defaultFields())
            ->mapWithKeys(fn (array $field, string $key) => [$field['label'] => $key]);

        DB::table('form_fields')->orderBy('id')->get()->each(function ($field) use (&$used, $defaultsByLabel) {
            $baseKey = $field->key ?: ($legacyLabels[$field->label] ?? $defaultsByLabel[$field->label] ?? FormFieldService::keyFromLabel($field->label));
            $key = $baseKey;
            $suffix = 2;

            while (in_array($key, $used, true)) {
                $key = $baseKey.'_'.$suffix;
                $suffix++;
            }

            $used[] = $key;

            DB::table('form_fields')->where('id', $field->id)->update(['key' => $key]);
        });

        Schema::table('form_fields', function (Blueprint $table) {
            $table->unique('key');
        });
    }

    public function down(): void
    {
        Schema::table('form_fields', function (Blueprint $table) {
            $table->dropUnique('form_fields_key_unique');
            $table->dropColumn('key');
        });
    }
};
