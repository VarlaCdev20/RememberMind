<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Extensión de nueve tablas aprobada por la propietaria el 17/10/2026. */
return new class extends Migration
{
    private const TABLAS = [
        'hechos_instantanea_experta', 'evidencias_cognitivas_instantanea',
        'soportes_sinteticos_criterio', 'funcionalidad_instantanea_experta',
        'evidencias_iadl_instantanea', 'clasificaciones_dcl_instantanea',
        'motivos_dcl_instantanea', 'reglas_aplicadas_dcl_instantanea', 'condiciones_regla_instantanea',
    ];

    public function up(): void
    {
        $this->conIntegridadDiferida(fn () => $this->aplicar());
    }

    private function aplicar(): void
    {
        Schema::table('evaluaciones_expertas', function (Blueprint $t): void {
            $t->text('id_evaluacion_dominio')->nullable()->unique('snap_eval_id');
            $t->string('version_conservacion', 30)->nullable();
            $t->char('huella_memoria_cognicion', 64)->nullable();
            $this->fecha($t, 'fecha_corte', true);
        });
        Schema::table('evaluacion_criterios', function (Blueprint $t): void {
            $t->string('tipo_registro', 30)->default('D137');
            $t->string('version_conservacion', 30)->nullable();
            $t->string('cod_criterio_padre', 20)->nullable();
            $t->unsignedInteger('orden_conservacion')->nullable();
            $t->string('estado_evaluabilidad', 30)->nullable()->change();
            $this->fk($t, 'cod_criterio_padre', 'evaluacion_criterios', 'cod_evaluacion_criterio');
        });
        Schema::table('resultados_criterio', fn (Blueprint $t) => $t->text('justificacion_original')->nullable());
        Schema::table('evidencias_criterio_evaluacion', fn (Blueprint $t) => $t->unsignedInteger('orden_conservacion')->nullable());
        Schema::create(self::TABLAS[0], function (Blueprint $t): void {
            $t->string('cod_hecho_instantanea', 20)->primary();
            $t->string('cod_evaluacion_experta', 20);
            foreach (['id_hecho_dominio', 'fuente_id_dominio', 'origen_id_dominio', 'episodio_id_dominio', 'concepto_dominio'] as $c) {
                $t->text($c);
            }
            $this->fecha($t, 'fecha_hecho');
            $this->escalar($t, 'valor');
            $t->unsignedInteger('orden');
            $t->string('tipo_origen', 40);
            $t->string('cod_evidencia_evaluacion', 20)->nullable();
            $t->string('cod_atencion', 20)->nullable();
            $t->string('cod_actividad', 20)->nullable();
            $t->unique(['cod_evaluacion_experta', 'id_hecho_dominio'], 'snap_h_id');
            $t->unique(['cod_evaluacion_experta', 'orden'], 'snap_h_ord');
            $this->fk($t, 'cod_evaluacion_experta', 'evaluaciones_expertas');
            $this->fk($t, 'cod_evidencia_evaluacion', 'evidencias_evaluacion');
            $this->fk($t, 'cod_atencion', 'atenciones');
            $this->fk($t, 'cod_actividad', 'actividades');
        });
        Schema::create(self::TABLAS[1], function (Blueprint $t): void {
            $t->string('cod_evidencia_cognitiva_instantanea', 20)->primary();
            $t->string('cod_hecho_instantanea', 20)->unique();
            $t->string('dominio', 20);
            $t->string('subcomponente', 30)->nullable();
            $t->string('hallazgo', 50);
            $t->text('instrumento_id_dominio')->nullable();
            $t->text('justificacion');
            $t->unsignedInteger('orden');
            foreach (['instrumento', 'edicion', 'subprueba', 'norma', 'idioma', 'poblacion', 'revisor'] as $c) {
                $t->text('referencia_'.$c)->nullable();
            }
            $this->fk($t, 'cod_hecho_instantanea', self::TABLAS[0]);
        });
        Schema::create(self::TABLAS[2], function (Blueprint $t): void {
            $t->string('cod_soporte_sintetico', 20)->primary();
            $t->string('cod_evaluacion_criterio', 20);
            $t->string('cod_hecho_instantanea', 20);
            $t->unsignedInteger('orden');
            $t->unique(['cod_evaluacion_criterio', 'cod_hecho_instantanea'], 'snap_soporte_id');
            $t->unique(['cod_evaluacion_criterio', 'orden'], 'snap_soporte_ord');
            $this->fk($t, 'cod_evaluacion_criterio', 'evaluacion_criterios');
            $this->fk($t, 'cod_hecho_instantanea', self::TABLAS[0]);
        });
        Schema::create(self::TABLAS[3], function (Blueprint $t): void {
            $t->string('cod_funcionalidad_instantanea', 20)->primary();
            $t->string('cod_evaluacion_experta', 20)->unique();
            $t->string('independencia_relativa', 20);
            $t->text('revisor_sintetico_id')->nullable();
            $t->text('justificacion');
            $this->fk($t, 'cod_evaluacion_experta', 'evaluaciones_expertas');
        });
        Schema::create(self::TABLAS[4], function (Blueprint $t): void {
            $t->string('cod_evidencia_iadl_instantanea', 20)->primary();
            $t->string('cod_funcionalidad_instantanea', 20);
            $t->string('cod_hecho_instantanea', 20);
            $t->text('actividad_id_dominio');
            $t->string('estado', 30);
            $t->string('oportunidad', 40);
            $t->string('nivel_previo', 30)->nullable();
            $t->text('causa_ayuda')->nullable();
            $t->boolean('atribucion_cognitiva_simulada')->nullable();
            $t->text('revisor_sintetico_id')->nullable();
            $t->unsignedInteger('orden');
            $t->unique(['cod_funcionalidad_instantanea', 'cod_hecho_instantanea'], 'snap_iadl_id');
            $t->unique(['cod_funcionalidad_instantanea', 'orden'], 'snap_iadl_ord');
            $this->fk($t, 'cod_funcionalidad_instantanea', self::TABLAS[3]);
            $this->fk($t, 'cod_hecho_instantanea', self::TABLAS[0]);
        });
        Schema::create(self::TABLAS[5], function (Blueprint $t): void {
            $t->string('cod_clasificacion_dcl_instantanea', 20)->primary();
            $t->string('cod_evaluacion_experta', 20)->unique();
            $t->string('cod_funcionalidad_instantanea', 20)->unique();
            $t->string('version_contrato', 80);
            $t->string('diferencial', 40);
            $t->string('patron_candidato', 10)->nullable();
            $t->text('justificacion');
            $t->string('uso', 50);
            $t->char('huella_clasificacion', 64);
            $this->fk($t, 'cod_evaluacion_experta', 'evaluaciones_expertas');
            $this->fk($t, 'cod_funcionalidad_instantanea', self::TABLAS[3]);
        });
        Schema::create(self::TABLAS[6], function (Blueprint $t): void {
            $t->string('cod_motivo_dcl_instantanea', 20)->primary();
            $t->string('cod_clasificacion_dcl_instantanea', 20);
            $t->string('motivo', 80);
            $t->unsignedInteger('orden');
            $t->unique(['cod_clasificacion_dcl_instantanea', 'motivo'], 'snap_motivo_id');
            $t->unique(['cod_clasificacion_dcl_instantanea', 'orden'], 'snap_motivo_ord');
            $this->fk($t, 'cod_clasificacion_dcl_instantanea', self::TABLAS[5]);
        });
        Schema::create(self::TABLAS[7], function (Blueprint $t): void {
            $t->string('cod_regla_aplicada_dcl', 20)->primary();
            $t->string('cod_clasificacion_dcl_instantanea', 20);
            $t->string('cod_regla_experta', 20)->nullable();
            $t->text('id_regla_dominio');
            $t->text('fuente');
            $t->boolean('habilitada');
            $t->text('concepto_consecuencia');
            $this->escalar($t, 'consecuencia');
            $t->unsignedInteger('orden');
            $t->unique(['cod_clasificacion_dcl_instantanea', 'id_regla_dominio'], 'snap_regla_id');
            $t->unique(['cod_clasificacion_dcl_instantanea', 'orden'], 'snap_regla_ord');
            $this->fk($t, 'cod_clasificacion_dcl_instantanea', self::TABLAS[5]);
            $this->fk($t, 'cod_regla_experta', 'reglas_expertas');
        });
        Schema::create(self::TABLAS[8], function (Blueprint $t): void {
            $t->string('cod_condicion_instantanea', 20);
            $t->primary('cod_condicion_instantanea', 'snap_cond_pk');
            $t->string('cod_regla_aplicada_dcl', 20);
            $t->string('cod_condicion_padre', 20)->nullable();
            $t->string('operador', 20);
            $t->text('variable')->nullable();
            $this->escalar($t, 'literal');
            $t->unsignedInteger('orden');
            $this->fk($t, 'cod_regla_aplicada_dcl', self::TABLAS[7]);
            $this->fk($t, 'cod_condicion_padre', self::TABLAS[8], 'cod_condicion_instantanea');
        });
        foreach ([[self::TABLAS[0], 'valor'], [self::TABLAS[7], 'consecuencia'], [self::TABLAS[8], 'literal']] as [$tabla, $p]) {
            $this->check($tabla, 'snap_'.$p.'_tipo', $this->checkEscalar($p));
        }
        foreach ([0, 1, 2, 4, 6, 7, 8] as $i) {
            $this->check(self::TABLAS[$i], 'snap_orden_'.$i, 'orden >= 0');
        }
        $this->check(self::TABLAS[7], 'snap_consecuencia_conocida', "consecuencia_tipo <> 'NULL'");
        $this->check(self::TABLAS[8], 'snap_forma_condicion', "(operador IN ('IGUAL_A','DISTINTO_DE') AND variable IS NOT NULL AND literal_tipo <> 'NULL') OR (operador IN ('Y','O','NO') AND variable IS NULL AND literal_tipo = 'NULL')");
        $this->check(self::TABLAS[0], 'snap_episodio_original', "(tipo_origen = 'D137' AND ((cod_atencion IS NOT NULL AND cod_actividad IS NULL) OR (cod_atencion IS NULL AND cod_actividad IS NOT NULL))) OR (tipo_origen <> 'D137' AND cod_atencion IS NULL AND cod_actividad IS NULL)");
        $this->check('evaluacion_criterios', 'snap_tipo_criterio', "(tipo_registro = 'D137' AND estado_evaluabilidad IS NOT NULL AND version_conservacion IS NULL AND cod_criterio_padre IS NULL) OR (version_conservacion IS NOT NULL AND version_conservacion = 'ORION-SNAP-2' AND orden_conservacion IS NOT NULL AND orden_conservacion >= 0 AND ((tipo_registro = 'DOMINIO' AND estado_evaluabilidad IS NOT NULL AND cod_criterio_padre IS NULL) OR (tipo_registro = 'SUBCOMPONENTE' AND estado_evaluabilidad IS NULL AND cod_criterio_padre IS NOT NULL) OR (tipo_registro = 'DCL' AND estado_evaluabilidad IS NULL AND cod_criterio_padre IS NULL)))");
        $this->check(self::TABLAS[0], 'snap_origen', "(tipo_origen = 'D137' AND cod_evidencia_evaluacion IS NOT NULL) OR (tipo_origen IN ('REFERENCIA_EXTERNA_SINTETICA','REFERENCIA_CRITERIO_SINTETICA','REFERENCIA_IADL_SINTETICA','REVISION_TECNICA_SINTETICA') AND cod_evidencia_evaluacion IS NULL)");
    }

    private function fk(Blueprint $t, string $col, string $tabla, ?string $pk = null): void
    {
        $nombre = 'sv2_'.substr(sha1($t->getTable().':'.$col), 0, 12);
        $t->index($col, $nombre.'_ix');
        $t->foreign($col, $nombre.'_fk')->references($pk ?? $col)->on($tabla)->restrictOnDelete();
    }

    private function fecha(Blueprint $t, string $p, bool $nullable = false): void
    {
        $t->dateTime($p.'_utc', 6)->nullable($nullable);
        $t->string($p.'_zona', 80)->nullable($nullable);
        $t->integer($p.'_offset')->nullable($nullable);
    }

    private function escalar(Blueprint $t, string $p): void
    {
        $t->string($p.'_tipo', 8);
        $t->text($p.'_texto')->nullable();
        $t->bigInteger($p.'_entero')->nullable();
        $t->char($p.'_real_ieee754', 16)->nullable();
        $t->boolean($p.'_logico')->nullable();
    }

    private function checkEscalar(string $p): string
    {
        $ramas = [];
        foreach (['NULL' => null, 'STRING' => 'texto', 'INT' => 'entero', 'FLOAT' => 'real_ieee754', 'BOOL' => 'logico'] as $tipo => $activa) {
            $partes = ["{$p}_tipo = '{$tipo}'"];
            foreach (['texto', 'entero', 'real_ieee754', 'logico'] as $c) {
                $partes[] = $p.'_'.$c.' IS '.($c === $activa ? 'NOT NULL' : 'NULL');
            }
            $ramas[] = '('.implode(' AND ', $partes).')';
        }

        return implode(' OR ', $ramas);
    }

    /** SQLite requiere triggers para agregar un CHECK a tablas ya creadas. */
    private function check(string $tabla, string $nombre, string $expr): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $expr = preg_replace_callback("/'[^']*'|\\b[a-z][a-z0-9_]*\\b/", static fn (array $m): string => str_starts_with($m[0], "'") || (! str_contains($m[0], '_') && ! in_array($m[0], ['orden', 'variable', 'operador'], true))
                    ? $m[0] : 'NEW.'.$m[0], $expr);
            foreach (['INSERT', 'UPDATE'] as $evento) {
                $id = $nombre.'_'.strtolower($evento);
                DB::unprepared("CREATE TRIGGER {$id} BEFORE {$evento} ON {$tabla} WHEN COALESCE(({$expr}), 0) = 0 BEGIN SELECT RAISE(ABORT, '{$nombre}'); END");
            }
        } else {
            DB::statement("ALTER TABLE {$tabla} ADD CONSTRAINT {$nombre} CHECK (({$expr}) IS TRUE)");
        }
    }

    public function down(): void
    {
        $this->conIntegridadDiferida(fn () => $this->retirar());
    }

    /** SQLite reconstruye tablas al cambiar nullability; se validan todas las FK antes de confirmar. */
    private function conIntegridadDiferida(Closure $operacion): void
    {
        DB::transaction(function () use ($operacion): void {
            if (DB::getDriverName() !== 'sqlite') {
                $operacion();

                return;
            }
            $anterior = (int) DB::scalar('PRAGMA defer_foreign_keys');
            DB::statement('PRAGMA defer_foreign_keys = ON');
            try {
                $operacion();
                if (DB::select('PRAGMA foreign_key_check') !== []) {
                    throw new RuntimeException('La migración no puede confirmar referencias inconsistentes.');
                }
            } finally {
                DB::statement('PRAGMA defer_foreign_keys = '.($anterior === 1 ? 'ON' : 'OFF'));
            }
        });
    }

    private function retirar(): void
    {
        $hayDatos = DB::table('evaluaciones_expertas')->whereNotNull('version_conservacion')->exists()
            || DB::table('evaluacion_criterios')->where('tipo_registro', '<>', 'D137')->exists();
        foreach (self::TABLAS as $tabla) {
            $hayDatos = $hayDatos || DB::table($tabla)->exists();
        }
        if ($hayDatos) {
            throw new RuntimeException('No se puede retirar conservación V2 con instantáneas guardadas. Conservar la historia antes de revertir.');
        }
        if (DB::getDriverName() === 'sqlite') {
            foreach (['snap_tipo_criterio'] as $nombre) {
                foreach (['insert', 'update'] as $e) {
                    DB::unprepared("DROP TRIGGER IF EXISTS {$nombre}_{$e}");
                }
            }
        } else {
            DB::statement('ALTER TABLE evaluacion_criterios DROP CONSTRAINT snap_tipo_criterio');
        }
        foreach (array_reverse(self::TABLAS) as $tabla) {
            Schema::dropIfExists($tabla);
        }
        Schema::table('evidencias_criterio_evaluacion', fn (Blueprint $t) => $t->dropColumn('orden_conservacion'));
        Schema::table('resultados_criterio', fn (Blueprint $t) => $t->dropColumn('justificacion_original'));
        Schema::table('evaluacion_criterios', function (Blueprint $t): void {
            $nombre = 'sv2_'.substr(sha1('evaluacion_criterios:cod_criterio_padre'), 0, 12);
            $t->dropForeign(DB::getDriverName() === 'sqlite' ? ['cod_criterio_padre'] : $nombre.'_fk');
            $t->dropIndex($nombre.'_ix');
            $t->dropColumn(['tipo_registro', 'version_conservacion', 'cod_criterio_padre', 'orden_conservacion']);
            $t->string('estado_evaluabilidad', 30)->nullable(false)->change();
        });
        Schema::table('evaluaciones_expertas', function (Blueprint $t): void {
            $t->dropUnique('snap_eval_id');
            $t->dropColumn(['id_evaluacion_dominio', 'version_conservacion', 'huella_memoria_cognicion', 'fecha_corte_utc', 'fecha_corte_zona', 'fecha_corte_offset']);
        });
    }
};
