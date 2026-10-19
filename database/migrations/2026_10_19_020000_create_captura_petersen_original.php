<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Dos relaciones originales aprobadas el 19/10/2026. No contienen resultados esperados ni snapshots genéricos. */
return new class extends Migration
{
    private const CONDICIONES = [
        'DOMINIO' => ['alcance_dominio', 'contexto_interpretado'],
        'C01' => ['cambio_respecto_previo', 'corroboracion_independiente'],
        'C03' => ['revision_integrada', 'oportunidad_adecuada', 'atribucion_revisada'],
        'C04' => ['revision_explicita', 'factores_pertinentes_revisados', 'impedimento_no_resuelto'],
        'C05' => ['suficiencia_conjunto', 'condiciones_revisadas', 'exclusiones_revisadas'],
    ];

    private const FUENTES = ['cod_aplicacion' => ['aplicaciones_instrumento', 'cod_aplicacion'],
        'cod_control_cognitivo' => ['controles_cognitivos', 'cod_control_cognitivo'],
        'cod_control_iadl' => ['controles_funcionales_iadl', 'cod_control_iadl'],
        'cod_valoracion_psicologica' => ['valoraciones_psicologicas', 'cod_valoracion_psicologica'],
        'cod_antecedente' => ['antecedentes_clinicos', 'cod_antecedente'], 'cod_diagnostico' => ['diagnosticos', 'cod_diagnostico'],
        'cod_documento_clinico' => ['documentos_clinicos', 'cod_documento_clinico'],
        'cod_valoracion_revisada' => ['valoraciones_petersen_originales', 'cod_valoracion_petersen']];

    public function up(): void
    {
        Schema::create('valoraciones_petersen_originales', function (Blueprint $t): void {
            $t->string('cod_valoracion_petersen', 20)->primary();
            $t->string('solicitud_uuid', 36)->unique('vpo_solicitud_u');
            foreach (['cod_residente' => ['residentes', 'cod_residente'], 'cod_personal' => ['personal', 'cod_personal'],
                'cod_usuario_registro' => ['usuarios', 'cod_usuario'], 'cod_atencion' => ['atenciones', 'cod_atencion'],
                'cod_documento_clinico' => ['documentos_clinicos', 'cod_documento_clinico']] as $c => [$tabla, $pk]) {
                $t->string($c, 20)->nullable(in_array($c, ['cod_atencion', 'cod_documento_clinico'], true));
                $t->index($c, 'vpo_'.$c.'_i');
                $t->foreign($c, 'vpo_'.$c.'_f')->references($pk)->on($tabla)->restrictOnDelete();
            }
            $t->string('localizador_fuente', 120)->nullable();
            $t->string('cod_valoracion_anterior', 20)->nullable()->unique('vpo_sucesora_u');
            $t->string('tipo_registro', 15);
            $t->string('elemento', 10);
            $t->string('estado_original', 1)->nullable();
            $t->text('justificacion');
            $t->dateTime('fecha_hora', 6);
            $t->dateTime('registrado_en', 6);
            foreach (self::CONDICIONES as $campos) {
                foreach ($campos as $campo) {
                    $t->boolean($campo)->nullable();
                }
            }
            $t->unique(['cod_valoracion_petersen', 'cod_residente', 'elemento'], 'vpo_contexto_u');
            $t->index(['cod_residente', 'fecha_hora'], 'vpo_residente_fecha_i');
            $t->index(['cod_residente', 'elemento', 'fecha_hora'], 'vpo_elemento_fecha_i');
        });
        Schema::table('valoraciones_petersen_originales', function (Blueprint $t): void {
            $t->foreign(['cod_valoracion_anterior', 'cod_residente', 'elemento'], 'vpo_cadena_f')
                ->references(['cod_valoracion_petersen', 'cod_residente', 'elemento'])->on('valoraciones_petersen_originales')->restrictOnDelete();
        });
        Schema::create('soportes_valoracion_petersen', function (Blueprint $t): void {
            $t->string('cod_soporte_petersen', 20)->primary();
            $t->string('cod_valoracion_petersen', 20);
            $t->foreign('cod_valoracion_petersen', 'svp_propietaria_f')->references('cod_valoracion_petersen')->on('valoraciones_petersen_originales')->restrictOnDelete();
            $t->string('tipo_soporte', 25);
            $t->unsignedInteger('orden');
            foreach (self::FUENTES as $columna => [$tabla, $pk]) {
                $t->string($columna, 20)->nullable();
                $t->index($columna, 'svp_'.$columna.'_i');
                $t->foreign($columna, 'svp_'.$columna.'_f')->references($pk)->on($tabla)->restrictOnDelete();
                $t->unique(['cod_valoracion_petersen', $columna], 'svp_'.$columna.'_u');
            }
            $t->string('metodo_original', 80)->nullable();
            $t->char('huella_perfil_original', 64)->nullable();
            $t->unique(['cod_valoracion_petersen', 'orden'], 'svp_orden_u');
        });
        $campos = array_merge(...array_values(self::CONDICIONES));
        $check = "length(trim(justificacion)) > 0 AND elemento IN ('COG-MEM','COG-ATE','COG-EJE','COG-LEN','COG-VIS','C01','C03','C04','C05')"
            ." AND ((tipo_registro='REGISTRO' AND cod_valoracion_anterior IS NULL) OR (tipo_registro IN ('RECTIFICACION','ANULACION') AND cod_valoracion_anterior IS NOT NULL AND cod_valoracion_anterior <> cod_valoracion_petersen))"
            ." AND ((tipo_registro='ANULACION' AND estado_original IS NULL) OR (tipo_registro <> 'ANULACION' AND estado_original IS NOT NULL AND ((elemento LIKE 'COG-%' AND estado_original IN ('A','P','I')) OR (elemento NOT LIKE 'COG-%' AND estado_original IN ('S','N')))))"
            .' AND ((cod_documento_clinico IS NULL AND localizador_fuente IS NULL) OR (cod_documento_clinico IS NOT NULL AND localizador_fuente IS NOT NULL AND length(trim(localizador_fuente))>0))';
        foreach (self::CONDICIONES as $elemento => $propios) {
            foreach ($propios as $campo) {
                $pertenece = $elemento === 'DOMINIO' ? "elemento LIKE 'COG-%'" : "elemento = '$elemento'";
                $check .= " AND ($campo IS NULL OR (tipo_registro <> 'ANULACION' AND ($pertenece)))";
            }
        }
        $this->proteger('valoraciones_petersen_originales', 'vpo', $check, array_merge(['justificacion', 'elemento', 'tipo_registro',
            'estado_original', 'cod_valoracion_anterior', 'cod_valoracion_petersen', 'cod_documento_clinico', 'localizador_fuente'], $campos));
        $cantidad = implode(' + ', array_map(fn ($c) => "(CASE WHEN $c IS NULL THEN 0 ELSE 1 END)", array_keys(self::FUENTES)));
        $check = "($cantidad)=1 AND orden >= 0 AND tipo_soporte IN ('HISTORIA_PREVIA','CORROBORACION','MEDICION','IADL','DIFERENCIAL','CALIDAD','DOMINIO_REVISADO')"
            ." AND ((tipo_soporte='MEDICION' AND cod_aplicacion IS NOT NULL AND metodo_original IS NOT NULL AND length(trim(metodo_original))>0 AND huella_perfil_original IS NOT NULL AND length(huella_perfil_original)=64) OR (tipo_soporte<>'MEDICION' AND metodo_original IS NULL AND huella_perfil_original IS NULL))"
            ." AND (tipo_soporte<>'IADL' OR cod_control_iadl IS NOT NULL) AND ((tipo_soporte='DOMINIO_REVISADO' AND cod_valoracion_revisada IS NOT NULL) OR (tipo_soporte<>'DOMINIO_REVISADO' AND cod_valoracion_revisada IS NULL))";
        $this->proteger('soportes_valoracion_petersen', 'svp', $check, array_merge(array_keys(self::FUENTES), ['orden', 'tipo_soporte', 'metodo_original', 'huella_perfil_original']));
    }

    private function proteger(string $tabla, string $prefijo, string $check, array $columnas): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE $tabla ADD CONSTRAINT {$prefijo}_contrato_ck CHECK ($check)");
            DB::unprepared("CREATE FUNCTION {$prefijo}_inmutable() RETURNS trigger LANGUAGE plpgsql AS \$\$ BEGIN RAISE EXCEPTION 'La historia Petersen es inmutable'; END; \$\$");
            DB::unprepared("CREATE TRIGGER {$prefijo}_inmutable BEFORE UPDATE OR DELETE ON $tabla FOR EACH ROW EXECUTE FUNCTION {$prefijo}_inmutable()");
        } elseif (DB::getDriverName() === 'sqlite') {
            $patron = '/\\b('.implode('|', array_map('preg_quote', $columnas)).')\\b/';
            $predicado = preg_replace($patron, 'NEW.$1', $check);
            DB::unprepared("CREATE TRIGGER {$prefijo}_contrato BEFORE INSERT ON $tabla WHEN NOT ($predicado) BEGIN SELECT RAISE(ABORT, 'Contrato Petersen incompatible'); END");
            foreach (['UPDATE', 'DELETE'] as $evento) {
                $nombre = $prefijo.'_inmutable_'.strtolower($evento);
                DB::unprepared("CREATE TRIGGER $nombre BEFORE $evento ON $tabla BEGIN SELECT RAISE(ABORT, 'La historia Petersen es inmutable'); END");
            }
        } else {
            throw new LogicException('Motor de persistencia no soportado para Petersen.');
        }
    }

    public function down(): void
    {
        if (DB::table('valoraciones_petersen_originales')->exists()) {
            throw new LogicException('No se retira historia clínica Petersen conservada.');
        }
        Schema::dropIfExists('soportes_valoracion_petersen');
        Schema::dropIfExists('valoraciones_petersen_originales');
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP FUNCTION IF EXISTS svp_inmutable()');
            DB::statement('DROP FUNCTION IF EXISTS vpo_inmutable()');
        }
    }
};
