<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registros_eliminacion', function (Blueprint $table) {
            $table->string('cantidad_cualitativa', 20)->nullable();
            $table->decimal('volumen_ml', 8, 2)->nullable();
            $table->string('color_orina', 30)->nullable();
            $table->string('aspecto_orina', 30)->nullable();
            $table->string('olor_orina', 30)->nullable();
            $table->string('tipo_miccion', 30)->nullable();
            $table->unsignedTinyInteger('tipo_bristol')->nullable();
            $table->string('color_heces', 30)->nullable();
            $table->string('esfuerzo_defecacion', 30)->nullable();
            $table->boolean('presencia_sangre')->nullable();
            $table->boolean('presencia_moco')->nullable();
            $table->boolean('molestia_eliminacion')->nullable();
            $table->string('descripcion_molestia', 250)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('registros_eliminacion', function (Blueprint $table) {
            $table->dropColumn(['cantidad_cualitativa', 'volumen_ml', 'color_orina', 'aspecto_orina',
                'olor_orina', 'tipo_miccion', 'tipo_bristol', 'color_heces', 'esfuerzo_defecacion',
                'presencia_sangre', 'presencia_moco', 'molestia_eliminacion', 'descripcion_molestia']);
        });
    }
};
