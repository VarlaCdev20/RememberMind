<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('preadmisiones', function (Blueprint $table) {
            if (! Schema::hasColumn('preadmisiones', 'motivo_rechazo')) {
                $table->string('motivo_rechazo', 150)->nullable()->after('observaciones');
            }

            if (! Schema::hasColumn('preadmisiones', 'observacion_rechazo')) {
                $table->text('observacion_rechazo')->nullable()->after('motivo_rechazo');
            }

            if (! Schema::hasColumn('preadmisiones', 'fecha_rechazo')) {
                $table->timestamp('fecha_rechazo')->nullable()->after('observacion_rechazo');
            }

            if (! Schema::hasColumn('preadmisiones', 'rechazado_por')) {
                $table->string('rechazado_por', 20)->nullable()->after('fecha_rechazo');
            }

            if (! Schema::hasColumn('preadmisiones', 'fecha_aprobacion')) {
                $table->timestamp('fecha_aprobacion')->nullable()->after('rechazado_por');
            }

            if (! Schema::hasColumn('preadmisiones', 'aprobado_por')) {
                $table->string('aprobado_por', 20)->nullable()->after('fecha_aprobacion');
            }
        });

        Schema::table('preadmisiones', function (Blueprint $table) {
            $foreignKeys = collect(Schema::getForeignKeys('preadmisiones'))->pluck('columns')->map(fn ($cols) => implode(',', $cols));

            if (! $foreignKeys->contains('rechazado_por')) {
                $table->foreign('rechazado_por')->references('cod_usu')->on('users')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! $foreignKeys->contains('aprobado_por')) {
                $table->foreign('aprobado_por')->references('cod_usu')->on('users')->cascadeOnUpdate()->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('preadmisiones', function (Blueprint $table) {
            foreach (['rechazado_por', 'aprobado_por'] as $column) {
                try {
                    $table->dropForeign([$column]);
                } catch (\Throwable) {
                    // Ignore when FK is absent in local schema.
                }
            }
        });

        Schema::table('preadmisiones', function (Blueprint $table) {
            foreach ([
                'motivo_rechazo',
                'observacion_rechazo',
                'fecha_rechazo',
                'rechazado_por',
                'fecha_aprobacion',
                'aprobado_por',
            ] as $column) {
                if (Schema::hasColumn('preadmisiones', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
