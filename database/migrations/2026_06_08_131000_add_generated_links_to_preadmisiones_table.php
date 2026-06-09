<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('preadmisiones', function (Blueprint $table) {
            if (! Schema::hasColumn('preadmisiones', 'cod_am_generado')) {
                $table->string('cod_am_generado', 20)->nullable()->after('aprobado_por');
            }

            if (! Schema::hasColumn('preadmisiones', 'cod_fam_generado')) {
                $table->string('cod_fam_generado', 20)->nullable()->after('cod_am_generado');
            }
        });

        Schema::table('preadmisiones', function (Blueprint $table) {
            $foreignKeys = collect(Schema::getForeignKeys('preadmisiones'))->pluck('columns')->map(fn ($cols) => implode(',', $cols));

            if (! $foreignKeys->contains('cod_am_generado')) {
                $table->foreign('cod_am_generado')->references('cod_am')->on('adulto_mayor')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! $foreignKeys->contains('cod_fam_generado')) {
                $table->foreign('cod_fam_generado')->references('cod_fam')->on('familiares')->cascadeOnUpdate()->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('preadmisiones', function (Blueprint $table) {
            foreach (['cod_am_generado', 'cod_fam_generado'] as $column) {
                try {
                    $table->dropForeign([$column]);
                } catch (\Throwable) {
                    // Ignore when FK is absent.
                }
            }
        });

        Schema::table('preadmisiones', function (Blueprint $table) {
            foreach (['cod_am_generado', 'cod_fam_generado'] as $column) {
                if (Schema::hasColumn('preadmisiones', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
