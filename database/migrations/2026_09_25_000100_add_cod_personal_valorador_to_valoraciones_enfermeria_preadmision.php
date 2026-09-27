<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // FASE A: Incorporar la columna con su FK e índice, inicialmente nullable
        Schema::table('valoraciones_enfermeria_preadmision', function (Blueprint $table) {
            $table->string('cod_personal_valorador', 20)->nullable()->after('cod_preadmision');
            $table->foreign('cod_personal_valorador')
                ->references('cod_personal')
                ->on('personal')
                ->restrictOnDelete();
            $table->index('cod_personal_valorador');
        });

        // FASE B: Backfill determinista de identidad histrica
        DB::table('valoraciones_enfermeria_preadmision')
            ->whereNull('cod_personal_valorador')
            ->whereNotNull('cod_usuario_registro')
            ->orderBy('cod_valoracion_enfermeria')
            ->each(function (object $row): void {
                $personales = DB::table('personal')
                    ->where('cod_usuario', $row->cod_usuario_registro)
                    ->get();

                // Recuperar la identidad histrica determinista si existe exactamente un personal vinculado
                if ($personales->count() === 1) {
                    DB::table('valoraciones_enfermeria_preadmision')
                        ->where('cod_valoracion_enfermeria', $row->cod_valoracion_enfermeria)
                        ->update(['cod_personal_valorador' => $personales->first()->cod_personal]);
                }
            });

        // FASE C: Verificación de consistencia previa al endurecimiento
        $pendientes = DB::table('valoraciones_enfermeria_preadmision')
            ->where(function ($q) {
                $q->whereNull('cod_personal_valorador')
                  ->orWhereNull('cod_usuario_registro');
            })
            ->count();

        if ($pendientes > 0) {
            throw new \RuntimeException(
                "Existen {$pendientes} registros en 'valoraciones_enfermeria_preadmision' sin autor clínico determinista o sin usuario registrador. Se detiene la migración para regularización manual."
            );
        }

        // FASE D: Endurecimiento a NOT NULL
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE valoraciones_enfermeria_preadmision ALTER COLUMN cod_personal_valorador SET NOT NULL');
            DB::statement('ALTER TABLE valoraciones_enfermeria_preadmision ALTER COLUMN cod_usuario_registro SET NOT NULL');
        } else {
            Schema::table('valoraciones_enfermeria_preadmision', function (Blueprint $table) {
                $table->string('cod_personal_valorador', 20)->nullable(false)->change();
                $table->string('cod_usuario_registro', 20)->nullable(false)->change();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE valoraciones_enfermeria_preadmision ALTER COLUMN cod_personal_valorador DROP NOT NULL');
            DB::statement('ALTER TABLE valoraciones_enfermeria_preadmision ALTER COLUMN cod_usuario_registro DROP NOT NULL');
        } else {
            Schema::table('valoraciones_enfermeria_preadmision', function (Blueprint $table) {
                $table->string('cod_usuario_registro', 20)->nullable()->change();
            });
        }

        Schema::table('valoraciones_enfermeria_preadmision', function (Blueprint $table) {
            $table->dropForeign(['cod_personal_valorador']);
            $table->dropIndex(['cod_personal_valorador']);
            $table->dropColumn('cod_personal_valorador');
        });
    }
};
