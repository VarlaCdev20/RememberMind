<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. CORRECCIÓN DE TIPOS PARA COMPATIBILIDAD CON FOREIGN KEYS (POSTGRESQL SPECIFIC)
        
        // obs_adulto.registrado_por — ya es string(20), no necesita conversión

        // familiar_adulto.cod_fam y cod_am
        if (Schema::hasTable('familiar_adulto')) {
            if (Schema::hasColumn('familiar_adulto', 'cod_fam')) {
                DB::statement('ALTER TABLE familiar_adulto ALTER COLUMN cod_fam TYPE INTEGER USING cod_fam::integer');
            }
            if (Schema::hasColumn('familiar_adulto', 'cod_am')) {
                DB::statement('ALTER TABLE familiar_adulto ALTER COLUMN cod_am TYPE INTEGER USING cod_am::integer');
            }
        }

        // actividades_adulto y atenciones_adulto responsable_id — ya es string(20), no necesita conversión

        // 2. ADICIÓN DE FOREIGN KEYS DEFENSIVA
        
        $foreignKeyExists = function ($table, $foreignKey) {
            $exists = DB::select("
                SELECT COUNT(*) 
                FROM information_schema.table_constraints 
                WHERE constraint_name = ? AND table_name = ?
            ", [$foreignKey, $table]);
            return $exists[0]->count > 0;
        };

        // Relaciones principales
        Schema::table('adulto_mayor', function (Blueprint $table) use ($foreignKeyExists) {
            if (!$foreignKeyExists('adulto_mayor', 'adulto_mayor_cod_est_adul_foreign')) {
                if (Schema::hasTable('estado_adulto')) {
                    $table->foreign('cod_est_adul')->references('cod_est_adul')->on('estado_adulto')->onUpdate('cascade')->onDelete('restrict');
                }
            }
        });

        Schema::table('documentos_adulto_mayor', function (Blueprint $table) use ($foreignKeyExists) {
            if (!$foreignKeyExists('documentos_adulto_mayor', 'documentos_adulto_mayor_cod_am_foreign')) {
                $table->foreign('cod_am')->references('cod_am')->on('adulto_mayor')->onUpdate('cascade')->onDelete('cascade');
            }
        });

        Schema::table('obs_adulto', function (Blueprint $table) use ($foreignKeyExists) {
            if (!$foreignKeyExists('obs_adulto', 'obs_adulto_cod_am_foreign')) {
                $table->foreign('cod_am')->references('cod_am')->on('adulto_mayor')->onUpdate('cascade')->onDelete('cascade');
            }
            if (!$foreignKeyExists('obs_adulto', 'obs_adulto_cod_est_adul_foreign')) {
                if (Schema::hasTable('estado_adulto')) {
                    $table->foreign('cod_est_adul')->references('cod_est_adul')->on('estado_adulto')->onUpdate('cascade')->onDelete('restrict');
                }
            }
            if (!$foreignKeyExists('obs_adulto', 'obs_adulto_registrado_por_foreign')) {
                $table->foreign('registrado_por')->references('cod_usu')->on('users')->onUpdate('cascade')->onDelete('set null');
            }
        });

        Schema::table('actividades_adulto', function (Blueprint $table) use ($foreignKeyExists) {
            if (!$foreignKeyExists('actividades_adulto', 'actividades_adulto_cod_am_foreign')) {
                $table->foreign('cod_am')->references('cod_am')->on('adulto_mayor')->onUpdate('cascade')->onDelete('cascade');
            }
            if (!$foreignKeyExists('actividades_adulto', 'actividades_adulto_cod_tipo_act_foreign')) {
                $table->foreign('cod_tipo_act')->references('cod_tipo_act')->on('tipo_actividades_adulto')->onUpdate('cascade')->onDelete('restrict');
            }
        });

        Schema::table('atenciones_adulto', function (Blueprint $table) use ($foreignKeyExists) {
            if (!$foreignKeyExists('atenciones_adulto', 'atenciones_adulto_cod_am_foreign')) {
                $table->foreign('cod_am')->references('cod_am')->on('adulto_mayor')->onUpdate('cascade')->onDelete('cascade');
            }
            if (!$foreignKeyExists('atenciones_adulto', 'atenciones_adulto_cod_tipo_aten_foreign')) {
                $table->foreign('cod_tipo_aten')->references('cod_tipo_aten')->on('tipo_atenciones_adulto')->onUpdate('cascade')->onDelete('restrict');
            }
        });

        Schema::table('documentos_usuarios', function (Blueprint $table) use ($foreignKeyExists) {
            if (!$foreignKeyExists('documentos_usuarios', 'documentos_usuarios_cod_usu_foreign')) {
                $table->foreign('cod_usu')->references('cod_usu')->on('users')->onUpdate('cascade')->onDelete('cascade');
            }
        });

        Schema::table('voluntarios', function (Blueprint $table) use ($foreignKeyExists) {
            if (!$foreignKeyExists('voluntarios', 'voluntarios_cod_usu_foreign')) {
                $table->foreign('cod_usu')->references('cod_usu')->on('users')->onUpdate('cascade')->onDelete('cascade');
            }
        });

        Schema::table('familiares', function (Blueprint $table) use ($foreignKeyExists) {
            if (!$foreignKeyExists('familiares', 'familiares_cod_usu_foreign')) {
                $table->foreign('cod_usu')->references('cod_usu')->on('users')->onUpdate('cascade')->onDelete('cascade');
            }
        });

        Schema::table('familiar_adulto', function (Blueprint $table) use ($foreignKeyExists) {
            if (!$foreignKeyExists('familiar_adulto', 'familiar_adulto_cod_fam_foreign')) {
                $table->foreign('cod_fam')->references('cod_fam')->on('familiares')->onUpdate('cascade')->onDelete('cascade');
            }
            if (!$foreignKeyExists('familiar_adulto', 'familiar_adulto_cod_am_foreign')) {
                $table->foreign('cod_am')->references('cod_am')->on('adulto_mayor')->onUpdate('cascade')->onDelete('cascade');
            }
        });

        Schema::table('asignacion_voluntarios', function (Blueprint $table) use ($foreignKeyExists) {
            if (!$foreignKeyExists('asignacion_voluntarios', 'asignacion_voluntarios_cod_am_foreign')) {
                $table->foreign('cod_am')->references('cod_am')->on('adulto_mayor')->onUpdate('cascade')->onDelete('cascade');
            }
            if (!$foreignKeyExists('asignacion_voluntarios', 'asignacion_voluntarios_cod_vol_foreign')) {
                $table->foreign('cod_vol')->references('cod_vol')->on('voluntarios')->onUpdate('cascade')->onDelete('cascade');
            }
        });

        Schema::table('asistencia_voluntarios', function (Blueprint $table) use ($foreignKeyExists) {
            if (!$foreignKeyExists('asistencia_voluntarios', 'asistencia_voluntarios_cod_vol_foreign')) {
                $table->foreign('cod_vol')->references('cod_vol')->on('voluntarios')->onUpdate('cascade')->onDelete('cascade');
            }
        });

        Schema::table('disponibilidad_voluntarios', function (Blueprint $table) use ($foreignKeyExists) {
            if (!$foreignKeyExists('disponibilidad_voluntarios', 'disponibilidad_voluntarios_cod_vol_foreign')) {
                $table->foreign('cod_vol')->references('cod_vol')->on('voluntarios')->onUpdate('cascade')->onDelete('cascade');
            }
        });

        Schema::table('personal_salud', function (Blueprint $table) use ($foreignKeyExists) {
            if (!$foreignKeyExists('personal_salud', 'personal_salud_cod_usu_foreign')) {
                $table->foreign('cod_usu')->references('cod_usu')->on('users')->onUpdate('cascade')->onDelete('cascade');
            }
            if (!$foreignKeyExists('personal_salud', 'personal_salud_cod_esp_foreign')) {
                $table->foreign('cod_esp')->references('cod_esp')->on('especialidades')->onUpdate('cascade')->onDelete('restrict');
            }
        });

        Schema::table('horarios_personal_salud', function (Blueprint $table) use ($foreignKeyExists) {
            if (!$foreignKeyExists('horarios_personal_salud', 'horarios_personal_salud_cod_per_sal_foreign')) {
                $table->foreign('cod_per_sal')->references('cod_per_sal')->on('personal_salud')->onUpdate('cascade')->onDelete('cascade');
            }
        });

        Schema::table('personal_admin', function (Blueprint $table) use ($foreignKeyExists) {
            if (!$foreignKeyExists('personal_admin', 'personal_admin_cod_usu_foreign')) {
                $table->foreign('cod_usu')->references('cod_usu')->on('users')->onUpdate('cascade')->onDelete('cascade');
            }
        });

        Schema::table('horarios_personal_admin', function (Blueprint $table) use ($foreignKeyExists) {
            if (!$foreignKeyExists('horarios_personal_admin', 'horarios_personal_admin_cod_per_adm_foreign')) {
                $table->foreign('cod_per_adm')->references('cod_per_adm')->on('personal_admin')->onUpdate('cascade')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
