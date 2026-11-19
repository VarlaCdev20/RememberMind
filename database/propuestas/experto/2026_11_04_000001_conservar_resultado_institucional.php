<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Extensión expresamente autorizada el 04/11/2026. No crea tablas ni transforma historia previa. */
return new class extends Migration
{
    public const DOMINIOS = ['memoria', 'atencion', 'ejecutiva', 'lenguaje', 'visuoespacial'];
    public const REQUISITOS = ['c01', 'c02', 'c03', 'c04', 'c05'];
    private const FUENTES = ['cod_respuesta' => 'respuestas_instrumento', 'cod_aplicacion' => 'aplicaciones_instrumento',
        'cod_valoracion_petersen' => 'valoraciones_petersen_originales'];

    public function up(): void
    {
        $this->transicion(function (): void {
            Schema::table('evaluaciones_expertas', function (Blueprint $t): void {
                foreach (['cod_version_petersen' => ['versiones_modelo_experto', 'cod_version_modelo'], 'cod_usuario_ejecucion' => ['usuarios', 'cod_usuario']] as $campo => [$tabla, $pk]) {
                    $t->string($campo, 20)->nullable();
                    $t->index($campo, 'ri_'.$campo.'_ix');
                    $t->foreign($campo, 'ri_'.$campo.'_fk')->references($pk)->on($tabla)->restrictOnDelete();
                }
                foreach ([...self::DOMINIOS, ...self::REQUISITOS] as $campo) {
                    $t->string('institucional_'.$campo, 1)->nullable();
                }
                $t->string('institucional_resultado', 80)->nullable();
                $t->string('institucional_patron', 10)->nullable();
                $t->text('institucional_explicacion')->nullable();
                $t->char('institucional_ejecutor', 64)->nullable();
            });
            Schema::table('hechos_instantanea_experta', function (Blueprint $t): void {
                foreach (self::FUENTES as $campo => $tabla) {
                    $t->string($campo, 20)->nullable();
                    $t->index($campo, 'ri_'.$campo.'_ix');
                    $t->foreign($campo, 'ri_'.$campo.'_fk')->references($campo)->on($tabla)->restrictOnDelete();
                }
            });
            $dominios = implode(' AND ', array_map(fn ($d) => "institucional_{$d} IN ('A','P','U','I','X')", self::DOMINIOS));
            $requisitos = implode(' AND ', array_map(fn ($d) => "institucional_{$d} IN ('S','N','U','I','X')", self::REQUISITOS));
            $nuevos = ['cod_version_petersen', 'cod_usuario_ejecucion', ...array_map(fn ($d) => 'institucional_'.$d, [...self::DOMINIOS, ...self::REQUISITOS]),
                'institucional_resultado', 'institucional_patron', 'institucional_explicacion', 'institucional_ejecutor'];
            $historico = implode(' AND ', array_map(fn ($c) => $c.' IS NULL', $nuevos));
            $this->check('evaluaciones_expertas', 'ri_cabecera', "(version_conservacion = 'RESULTADO-INSTITUCIONAL-1' AND origen_activacion = 'INSTITUCIONAL' AND estado_ejecucion = 'FINALIZADA' AND cod_version_petersen IS NOT NULL AND cod_usuario_ejecucion IS NOT NULL AND {$dominios} AND {$requisitos} AND institucional_resultado IS NOT NULL AND institucional_explicacion IS NOT NULL AND institucional_ejecutor IS NOT NULL AND huella_memoria_cognicion IS NOT NULL AND fecha_corte_utc IS NOT NULL AND (institucional_patron IS NULL OR institucional_patron IN ('PET-01','PET-02','PET-03','PET-04'))) OR ((version_conservacion IS NULL OR version_conservacion <> 'RESULTADO-INSTITUCIONAL-1') AND {$historico})");
            $this->fuentes(true);
        });
    }

    public function down(): void
    {
        if (DB::table('evaluaciones_expertas')->whereNotNull('cod_usuario_ejecucion')->exists()
            || DB::table('hechos_instantanea_experta')->where(fn ($q) => $q->whereNotNull('cod_respuesta')->orWhereNotNull('cod_aplicacion')->orWhereNotNull('cod_valoracion_petersen'))->exists()) {
            throw new RuntimeException('No se retira conservación institucional con historia poblada.');
        }
        $this->transicion(function (): void {
            $this->retirar('evaluaciones_expertas', 'ri_cabecera');
            Schema::table('hechos_instantanea_experta', function (Blueprint $t): void {
                foreach (self::FUENTES as $campo => $tabla) {
                    $t->dropForeign(DB::getDriverName() === 'sqlite' ? [$campo] : 'ri_'.$campo.'_fk');
                    $t->dropIndex('ri_'.$campo.'_ix');
                }
                $t->dropColumn(array_keys(self::FUENTES));
            });
            $this->fuentes(false);
            Schema::table('evaluaciones_expertas', function (Blueprint $t): void {
                foreach (['cod_version_petersen', 'cod_usuario_ejecucion'] as $campo) {
                    $t->dropForeign(DB::getDriverName() === 'sqlite' ? [$campo] : 'ri_'.$campo.'_fk');
                    $t->dropIndex('ri_'.$campo.'_ix');
                }
                $t->dropColumn(['cod_version_petersen', 'cod_usuario_ejecucion', ...array_map(fn ($d) => 'institucional_'.$d, [...self::DOMINIOS, ...self::REQUISITOS]),
                    'institucional_resultado', 'institucional_patron', 'institucional_explicacion', 'institucional_ejecutor']);
            });
        });
    }

    private function fuentes(bool $ampliar): void
    {
        $obs = "tipo_origen = 'CONTROL_COGNITIVO' AND cod_control_cognitivo IS NOT NULL AND cod_personal_autor IS NOT NULL AND cod_evidencia_evaluacion IS NOT NULL AND cod_actividad IS NULL";
        $previo = "tipo_origen <> 'CONTROL_COGNITIVO' AND cod_control_cognitivo IS NULL AND cod_personal_autor IS NULL";
        $origen = "((tipo_origen = 'D137' AND cod_evidencia_evaluacion IS NOT NULL) OR (tipo_origen IN ('REFERENCIA_EXTERNA_SINTETICA','REFERENCIA_CRITERIO_SINTETICA','REFERENCIA_IADL_SINTETICA','REVISION_TECNICA_SINTETICA') AND cod_evidencia_evaluacion IS NULL)) AND ({$previo})";
        $episodio = "((tipo_origen = 'D137' AND ((cod_atencion IS NOT NULL AND cod_actividad IS NULL) OR (cod_atencion IS NULL AND cod_actividad IS NOT NULL))) OR (tipo_origen <> 'D137' AND cod_atencion IS NULL AND cod_actividad IS NULL)) AND ({$previo})";
        foreach (['snap_origen' => $origen, 'snap_episodio_original' => $episodio] as $nombre => $expr) {
            $base = "(({$expr}) OR ({$obs}))";
            if ($ampliar) {
                $nulos = implode(' AND ', array_map(fn ($c) => $c.' IS NULL', array_keys(self::FUENTES)));
                $ramas = [];
                foreach (['RESPUESTA_INSTRUMENTAL' => 'cod_respuesta', 'APLICACION_INSTRUMENTAL' => 'cod_aplicacion', 'JUICIO_PETERSEN' => 'cod_valoracion_petersen'] as $tipo => $campo) {
                    $otros = implode(' AND ', array_map(fn ($c) => $c.' IS NULL', array_diff(array_keys(self::FUENTES), [$campo])));
                    $ramas[] = "(tipo_origen = '{$tipo}' AND {$campo} IS NOT NULL AND {$otros} AND cod_personal_autor IS NOT NULL AND cod_control_cognitivo IS NULL AND cod_evidencia_evaluacion IS NULL AND cod_atencion IS NULL AND cod_actividad IS NULL)";
                }
                $base = "({$base} AND {$nulos}) OR ".implode(' OR ', $ramas);
            }
            $this->check('hechos_instantanea_experta', $nombre, $base);
        }
    }

    private function retirar(string $tabla, string $nombre): void
    {
        if (DB::getDriverName() === 'sqlite') {
            foreach (['insert', 'update'] as $evento) {
                DB::unprepared("DROP TRIGGER IF EXISTS {$nombre}_{$evento}");
            }
        } else {
            DB::statement("ALTER TABLE {$tabla} DROP CONSTRAINT IF EXISTS {$nombre}");
        }
    }

    private function check(string $tabla, string $nombre, string $expr): void
    {
        $this->retirar($tabla, $nombre);
        if (DB::getDriverName() === 'sqlite') {
            $expr = preg_replace_callback("/'[^']*'|\\b[a-z][a-z0-9_]*\\b/", static fn ($m) => str_starts_with($m[0], "'") || ! str_contains($m[0], '_') ? $m[0] : 'NEW.'.$m[0], $expr);
            foreach (['insert', 'update'] as $evento) {
                DB::unprepared("CREATE TRIGGER {$nombre}_{$evento} BEFORE ".strtoupper($evento)." ON {$tabla} WHEN COALESCE(({$expr}),0) = 0 BEGIN SELECT RAISE(ABORT, '{$nombre}'); END");
            }
        } else {
            DB::statement("ALTER TABLE {$tabla} ADD CONSTRAINT {$nombre} CHECK (({$expr}) IS TRUE)");
        }
    }

    private function transicion(Closure $operacion): void
    {
        if (! in_array(DB::getDriverName(), ['sqlite', 'pgsql'], true)) {
            throw new RuntimeException('Motor no verificado para conservación institucional.');
        }
        DB::transaction(function () use ($operacion): void {
            $sqlite = DB::getDriverName() === 'sqlite';
            $triggers = $sqlite ? DB::select("SELECT name, sql FROM sqlite_master WHERE type = 'trigger'") : [];
            $anterior = $sqlite ? (int) DB::scalar('PRAGMA defer_foreign_keys') : 0;
            if ($sqlite) {
                DB::statement('PRAGMA defer_foreign_keys = ON');
                foreach ($triggers as $t) { DB::unprepared('DROP TRIGGER "'.str_replace('"', '""', $t->name).'"'); }
            }
            try {
                $operacion();
                foreach ($triggers as $t) {
                    if (! str_starts_with($t->name, 'snap_origen_') && ! str_starts_with($t->name, 'snap_episodio_original_') && ! str_starts_with($t->name, 'ri_cabecera_')
                        && ! DB::scalar("SELECT count(*) FROM sqlite_master WHERE type = 'trigger' AND name = ?", [$t->name])) {
                        DB::unprepared($t->sql);
                    }
                }
                if ($sqlite && DB::select('PRAGMA foreign_key_check') !== []) { throw new RuntimeException('FK inconsistentes en conservación institucional.'); }
            } finally {
                if ($sqlite) { DB::statement('PRAGMA defer_foreign_keys = '.($anterior ? 'ON' : 'OFF')); }
            }
        });
    }
};
