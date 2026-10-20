<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion;

use App\Compartido\Infraestructura\Persistencia\GeneradorCodigo;
use Closure;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/** Transición física aprobada: cuatro tablas a bloques fijos, con prueba inversa antes del DROP. */
final class TransicionReduccionConservacion
{
    public const RETIRADAS = [ProyeccionReduccionExperta::COGNITIVAS, ProyeccionReduccionExperta::IADL,
        ProyeccionReduccionExperta::FUNCIONALIDAD, ProyeccionReduccionExperta::SINTETICOS];

    public function subir(): void
    {
        $this->transaccion(function (): void {
            $this->validarReferenciasExternas();
            (new EvaluarReduccionConservacion)->validarEnTransaccion(DB::connection());
            $plan = new ProyeccionReduccionExperta;
            $antes = $this->leer(ProyeccionReduccionExperta::TABLAS);
            $nuevas = $plan->proyectar($antes);
            $this->columnas(true);
            foreach ($nuevas[ProyeccionReduccionExperta::HECHOS] as $h) {
                DB::table(ProyeccionReduccionExperta::HECHOS)->where('cod_hecho_instantanea', $h['cod_hecho_instantanea'])
                    ->update(array_intersect_key($h, array_flip([...array_values(ProyeccionReduccionExperta::COG), ...array_values(ProyeccionReduccionExperta::IAD)])));
            }
            foreach ($nuevas[ProyeccionReduccionExperta::CLASIFICACIONES] as $c) {
                DB::table(ProyeccionReduccionExperta::CLASIFICACIONES)->where('cod_clasificacion_dcl_instantanea', $c['cod_clasificacion_dcl_instantanea'])
                    ->update(array_intersect_key($c, array_flip(array_values(ProyeccionReduccionExperta::FUN))));
            }
            foreach ($nuevas[ProyeccionReduccionExperta::VINCULOS] as $v) {
                if ($v['cod_soporte_sintetico'] !== null) {
                    $v['cod_evidencia_criterio'] = (new GeneradorCodigo)->siguiente(ProyeccionReduccionExperta::VINCULOS);
                    DB::table(ProyeccionReduccionExperta::VINCULOS)->insert($v);
                }
            }
            $actual = $this->leer([ProyeccionReduccionExperta::HECHOS, ProyeccionReduccionExperta::CLASIFICACIONES, ProyeccionReduccionExperta::VINCULOS]);
            $this->iguales($plan->canonizar($antes), $plan->canonizar($plan->restaurar($actual)));
            // Se comprueban los datos antes de cambiar las referencias y retirar los orígenes.
            $this->fkClasificacion(false);
            foreach ([ProyeccionReduccionExperta::IADL, ProyeccionReduccionExperta::COGNITIVAS,
                ProyeccionReduccionExperta::SINTETICOS, ProyeccionReduccionExperta::FUNCIONALIDAD] as $t) {
                Schema::drop($t);
            }
            $this->restricciones();
        });
    }

    public function bajar(): void
    {
        $this->transaccion(function (): void {
            $this->bloquear([ProyeccionReduccionExperta::HECHOS, ProyeccionReduccionExperta::CLASIFICACIONES, ProyeccionReduccionExperta::VINCULOS]);
            $this->validarReferenciasReversion();
            $plan = new ProyeccionReduccionExperta;
            $actual = $this->leer([ProyeccionReduccionExperta::HECHOS, ProyeccionReduccionExperta::CLASIFICACIONES, ProyeccionReduccionExperta::VINCULOS]);
            $antiguas = $plan->restaurar($actual);
            $regenerada = $plan->proyectar($antiguas);
            // PK nueva del vínculo directo no existía en el contrato anterior; su alias sí debe conservarse.
            $normal = $actual;
            foreach ($normal[ProyeccionReduccionExperta::VINCULOS] as &$v) {
                if ($v['cod_soporte_sintetico'] !== null) {
                    $v['cod_evidencia_criterio'] = null;
                }
            }
            unset($v);
            $this->iguales($plan->canonizar($normal), $plan->canonizar($regenerada));
            $this->quitarRestricciones();
            $this->recrearOrigenes();
            foreach ([ProyeccionReduccionExperta::FUNCIONALIDAD, ProyeccionReduccionExperta::COGNITIVAS,
                ProyeccionReduccionExperta::IADL, ProyeccionReduccionExperta::SINTETICOS] as $t) {
                foreach (array_chunk($antiguas[$t], 100) as $lote) {
                    DB::table($t)->insert($lote);
                }
            }
            DB::table(ProyeccionReduccionExperta::VINCULOS)->whereNotNull('cod_soporte_sintetico')->delete();
            $this->columnas(false);
            $this->fkClasificacion(true);
            $this->iguales($plan->canonizar($antiguas), $plan->canonizar($this->leer(ProyeccionReduccionExperta::TABLAS)));
        });
    }

    private function columnas(bool $agregar): void
    {
        $this->alterar(ProyeccionReduccionExperta::HECHOS, function (Blueprint $t) use ($agregar): void {
            $mapa = [...array_values(ProyeccionReduccionExperta::COG), ...array_values(ProyeccionReduccionExperta::IAD)];
            if (! $agregar) {
                $t->dropColumn($mapa);

                return;
            }
            foreach ($mapa as $c) {
                if (str_ends_with($c, '_orden')) {
                    $t->integer($c)->nullable();
                } elseif ($c === 'iadl_atribucion_cognitiva_simulada') {
                    $t->boolean($c)->nullable();
                } elseif (in_array($c, ['cod_evidencia_cognitiva_instantanea', 'cognitiva_dominio', 'cod_evidencia_iadl_instantanea', 'iadl_cod_funcionalidad_instantanea'], true)) {
                    $t->string($c, 20)->nullable();
                } elseif (in_array($c, ['cognitiva_subcomponente', 'iadl_estado', 'iadl_nivel_previo'], true)) {
                    $t->string($c, 30)->nullable();
                } elseif ($c === 'cognitiva_hallazgo') {
                    $t->string($c, 50)->nullable();
                } elseif ($c === 'iadl_oportunidad') {
                    $t->string($c, 40)->nullable();
                } else {
                    $t->text($c)->nullable();
                }
            }
        });
        $this->alterar(ProyeccionReduccionExperta::CLASIFICACIONES, function (Blueprint $t) use ($agregar): void {
            if ($agregar) {
                $t->string('funcionalidad_independencia_relativa', 20)->nullable();
                $t->text('funcionalidad_revisor_sintetico_id')->nullable();
                $t->text('funcionalidad_justificacion')->nullable();
            } else {
                $t->dropColumn(array_values(ProyeccionReduccionExperta::FUN));
            }
        });
        $this->alterar(ProyeccionReduccionExperta::VINCULOS, function (Blueprint $t) use ($agregar): void {
            $t->string('cod_evidencia_evaluacion', 20)->nullable($agregar)->change();
            $t->dateTime('fecha_hora_vinculacion')->nullable($agregar)->change();
            if ($agregar) {
                $t->string('cod_hecho_instantanea', 20)->nullable();
                $t->string('cod_soporte_sintetico', 20)->nullable();
            } else {
                $t->dropColumn(['cod_hecho_instantanea', 'cod_soporte_sintetico']);
            }
        });
    }

    /** Laravel reconstruye tablas SQLite; se conserva cada trigger existente, no solo los conocidos. */
    private function alterar(string $tabla, Closure $cambio): void
    {
        $triggers = DB::getDriverName() === 'sqlite' ? DB::select("SELECT name, sql FROM sqlite_master WHERE type='trigger' AND tbl_name=?", [$tabla]) : [];
        Schema::table($tabla, $cambio);
        foreach ($triggers as $trigger) {
            if (! DB::scalar("SELECT 1 FROM sqlite_master WHERE type='trigger' AND name=?", [$trigger->name])) {
                DB::unprepared($trigger->sql);
            }
        }
    }

    private function fkClasificacion(bool $agregar): void
    {
        $this->alterar(ProyeccionReduccionExperta::CLASIFICACIONES, function (Blueprint $t) use ($agregar): void {
            if ($agregar) {
                $t->foreign('cod_funcionalidad_instantanea', 'sv2_'.substr(sha1($t->getTable().':cod_funcionalidad_instantanea'), 0, 12).'_fk')
                    ->references('cod_funcionalidad_instantanea')->on(ProyeccionReduccionExperta::FUNCIONALIDAD)->restrictOnDelete();
            } else {
                $t->dropForeign(DB::getDriverName() === 'sqlite' ? ['cod_funcionalidad_instantanea'] : 'sv2_'.substr(sha1($t->getTable().':cod_funcionalidad_instantanea'), 0, 12).'_fk');
            }
        });
    }

    private function restricciones(): void
    {
        $this->alterar(ProyeccionReduccionExperta::CLASIFICACIONES, function (Blueprint $t): void {
            $t->string('funcionalidad_independencia_relativa', 20)->nullable(false)->change();
            $t->text('funcionalidad_justificacion')->nullable(false)->change();
            $t->unique(['cod_funcionalidad_instantanea', 'cod_evaluacion_experta'], 'red_fun_eval_u');
        });
        $this->alterar(ProyeccionReduccionExperta::HECHOS, function (Blueprint $t): void {
            $t->foreign(['iadl_cod_funcionalidad_instantanea', 'cod_evaluacion_experta'], 'red_iadl_fun_f')
                ->references(['cod_funcionalidad_instantanea', 'cod_evaluacion_experta'])->on(ProyeccionReduccionExperta::CLASIFICACIONES)->restrictOnDelete();
            $t->index(['iadl_cod_funcionalidad_instantanea', 'cod_evaluacion_experta'], 'red_iadl_fun_ix');
        });
        $this->alterar(ProyeccionReduccionExperta::VINCULOS, function (Blueprint $t): void {
            $t->foreign('cod_hecho_instantanea', 'red_v_hecho_f')->references('cod_hecho_instantanea')->on(ProyeccionReduccionExperta::HECHOS)->restrictOnDelete();
            $t->index('cod_hecho_instantanea', 'red_v_hecho_ix');
        });
        foreach ([['red_cog_alias_u', ProyeccionReduccionExperta::HECHOS, 'cod_evidencia_cognitiva_instantanea', 'cod_evidencia_cognitiva_instantanea IS NOT NULL'],
            ['red_iadl_alias_u', ProyeccionReduccionExperta::HECHOS, 'cod_evidencia_iadl_instantanea', 'cod_evidencia_iadl_instantanea IS NOT NULL'],
            ['red_cog_orden_u', ProyeccionReduccionExperta::HECHOS, 'cod_evaluacion_experta,cognitiva_orden', 'cod_evidencia_cognitiva_instantanea IS NOT NULL'],
            ['red_iadl_orden_u', ProyeccionReduccionExperta::HECHOS, 'iadl_cod_funcionalidad_instantanea,iadl_orden', 'cod_evidencia_iadl_instantanea IS NOT NULL'],
            ['red_v_alias_u', ProyeccionReduccionExperta::VINCULOS, 'cod_soporte_sintetico', 'cod_soporte_sintetico IS NOT NULL'],
            ['red_v_hecho_u', ProyeccionReduccionExperta::VINCULOS, 'cod_evaluacion_criterio,cod_hecho_instantanea', 'cod_hecho_instantanea IS NOT NULL']] as [$n,$t,$c,$p]) {
            DB::statement("CREATE UNIQUE INDEX {$n} ON {$t} ({$c}) WHERE {$p}");
        }
        foreach (['cog' => [ProyeccionReduccionExperta::COG, ['cognitiva_dominio', 'cognitiva_hallazgo', 'cognitiva_justificacion', 'cognitiva_orden']],
            'iadl' => [ProyeccionReduccionExperta::IAD, ['iadl_cod_funcionalidad_instantanea', 'iadl_actividad_id_dominio', 'iadl_estado', 'iadl_oportunidad', 'iadl_orden']]] as $tipo => [$mapa,$requeridas]) {
            $pk = array_values($mapa)[0];
            $nulas = implode(' AND ', array_map(fn ($c) => "$c IS NULL", array_values($mapa)));
            $llenas = implode(' AND ', array_map(fn ($c) => "$c IS NOT NULL", $requeridas));
            $this->check(ProyeccionReduccionExperta::HECHOS, 'red_'.$tipo.'_bloque', "($nulas) OR ($pk IS NOT NULL AND $llenas AND {$tipo}_orden >= 0)".($tipo === 'cog' ? '' : ''));
        }
        // cognitiva_orden tiene un prefijo distinto al nombre abreviado del bloque.
        $this->check(ProyeccionReduccionExperta::VINCULOS, 'red_v_rama', "(cod_evidencia_evaluacion IS NOT NULL AND cod_hecho_instantanea IS NULL AND cod_soporte_sintetico IS NULL AND fecha_hora_vinculacion IS NOT NULL) OR (cod_evidencia_evaluacion IS NULL AND cod_hecho_instantanea IS NOT NULL AND cod_soporte_sintetico IS NOT NULL AND rol_en_criterio='SOPORTE_EXPERIMENTAL' AND estado_participacion='PRUEBA_TECNICA' AND justificacion IS NULL AND fecha_hora_vinculacion IS NULL AND orden_conservacion IS NOT NULL AND orden_conservacion >= 0)");
        (new GuardasReduccionConservacion)->instalar();
    }

    private function check(string $tabla, string $nombre, string $expr): void
    {
        $expr = str_replace('cog_orden', 'cognitiva_orden', $expr);
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE {$tabla} ADD CONSTRAINT {$nombre} CHECK (({$expr}) IS TRUE)");
        } else {
            $columnas = Schema::getColumnListing($tabla);
            $expr = preg_replace_callback("/'[^']*'|\\b[a-z][a-z0-9_]*\\b/", fn ($m) => in_array($m[0], $columnas, true) ? 'NEW.'.$m[0] : $m[0], $expr);
            foreach (['INSERT', 'UPDATE'] as $evento) {
                DB::unprepared("CREATE TRIGGER {$nombre}_".strtolower($evento)." BEFORE {$evento} ON {$tabla} WHEN COALESCE(({$expr}),0)=0 BEGIN SELECT RAISE(ABORT,'{$nombre}'); END");
            }
        }
    }

    private function quitarRestricciones(): void
    {
        (new GuardasReduccionConservacion)->retirar();
        foreach (['red_cog_bloque', 'red_iadl_bloque', 'red_v_rama'] as $n) {
            if (DB::getDriverName() === 'sqlite') {
                foreach (['insert', 'update'] as $e) {
                    DB::unprepared("DROP TRIGGER IF EXISTS {$n}_{$e}");
                }
            } else {
                $t = $n === 'red_v_rama' ? ProyeccionReduccionExperta::VINCULOS : ProyeccionReduccionExperta::HECHOS;
                DB::statement("ALTER TABLE {$t} DROP CONSTRAINT {$n}");
            }
        }
        foreach (['red_cog_alias_u', 'red_iadl_alias_u', 'red_cog_orden_u', 'red_iadl_orden_u', 'red_v_alias_u', 'red_v_hecho_u'] as $n) {
            DB::statement("DROP INDEX {$n}");
        }
        $this->alterar(ProyeccionReduccionExperta::HECHOS, function (Blueprint $t): void {
            $t->dropForeign(DB::getDriverName() === 'sqlite' ? ['iadl_cod_funcionalidad_instantanea', 'cod_evaluacion_experta'] : 'red_iadl_fun_f');
            $t->dropIndex('red_iadl_fun_ix');
        });
        $this->alterar(ProyeccionReduccionExperta::VINCULOS, function (Blueprint $t): void {
            $t->dropForeign(DB::getDriverName() === 'sqlite' ? ['cod_hecho_instantanea'] : 'red_v_hecho_f');
            $t->dropIndex('red_v_hecho_ix');
        });
        Schema::table(ProyeccionReduccionExperta::CLASIFICACIONES, fn (Blueprint $t) => $t->dropUnique('red_fun_eval_u'));
    }

    private function recrearOrigenes(): void
    {
        Schema::create(ProyeccionReduccionExperta::FUNCIONALIDAD, function (Blueprint $t): void {
            $t->string('cod_funcionalidad_instantanea', 20)->primary();
            $t->string('cod_evaluacion_experta', 20)->unique();
            $t->string('independencia_relativa', 20);
            $t->text('revisor_sintetico_id')->nullable();
            $t->text('justificacion');
            $this->fkOriginal($t, 'cod_evaluacion_experta', 'evaluaciones_expertas');
        });
        Schema::create(ProyeccionReduccionExperta::COGNITIVAS, function (Blueprint $t): void {
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
            $this->fkOriginal($t, 'cod_hecho_instantanea', ProyeccionReduccionExperta::HECHOS);
        });
        Schema::create(ProyeccionReduccionExperta::IADL, function (Blueprint $t): void {
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
            $this->fkOriginal($t, 'cod_funcionalidad_instantanea', ProyeccionReduccionExperta::FUNCIONALIDAD);
            $this->fkOriginal($t, 'cod_hecho_instantanea', ProyeccionReduccionExperta::HECHOS);
        });
        Schema::create(ProyeccionReduccionExperta::SINTETICOS, function (Blueprint $t): void {
            $t->string('cod_soporte_sintetico', 20)->primary();
            $t->string('cod_evaluacion_criterio', 20);
            $t->string('cod_hecho_instantanea', 20);
            $t->unsignedInteger('orden');
            $t->unique(['cod_evaluacion_criterio', 'cod_hecho_instantanea'], 'snap_soporte_id');
            $t->unique(['cod_evaluacion_criterio', 'orden'], 'snap_soporte_ord');
            $this->fkOriginal($t, 'cod_evaluacion_criterio', 'evaluacion_criterios');
            $this->fkOriginal($t, 'cod_hecho_instantanea', ProyeccionReduccionExperta::HECHOS);
        });
        foreach ([ProyeccionReduccionExperta::COGNITIVAS => 1, ProyeccionReduccionExperta::IADL => 4, ProyeccionReduccionExperta::SINTETICOS => 2] as $t => $i) {
            $this->check($t, 'snap_orden_'.$i, 'orden >= 0');
        }
    }

    private function fkOriginal(Blueprint $t, string $c, string $tabla): void
    {
        $n = 'sv2_'.substr(sha1($t->getTable().':'.$c), 0, 12);
        $t->index($c, $n.'_ix');
        $t->foreign($c, $n.'_fk')->references($c)->on($tabla)->restrictOnDelete();
    }

    private function leer(array $tablas): array
    {
        $r = [];
        foreach ($tablas as $t) {
            $r[$t] = DB::table($t)->get()->map(fn ($f) => (array) $f)->all();
        }

        return $r;
    }

    private function iguales(array $a, array $b): void
    {
        if ($a !== $b) {
            throw new RuntimeException('La reducción no conserva exactamente sus filas y tipos originales.');
        }
    }

    private function validarReferenciasExternas(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            $this->bloquear(ProyeccionReduccionExperta::TABLAS);
            $r = DB::select("SELECT 1 FROM pg_constraint c JOIN pg_class o ON o.oid=c.conrelid JOIN pg_namespace n ON n.oid=o.relnamespace JOIN pg_class d ON d.oid=c.confrelid WHERE c.contype='f' AND n.nspname<>'public' AND d.relname IN (?,?,?,?) LIMIT 1", self::RETIRADAS);
            if ($r !== []) {
                throw new RuntimeException('Una FK de otro esquema bloquea la reducción.');
            }
        }
    }

    private function bloquear(array $tablas): void
    {
        if (DB::getDriverName() === 'pgsql') {
            $tablas = array_unique([...$tablas, 'evaluacion_criterios', 'evidencias_evaluacion', 'evaluaciones_expertas']);
            sort($tablas, SORT_STRING);
            DB::statement('LOCK TABLE '.implode(',', $tablas).' IN ACCESS EXCLUSIVE MODE');
        }
    }

    private function validarReferenciasReversion(): void
    {
        if (DB::getDriverName() === 'pgsql' && DB::select("SELECT 1 FROM pg_constraint c JOIN pg_class o ON o.oid=c.conrelid JOIN pg_namespace n ON n.oid=o.relnamespace JOIN pg_class d ON d.oid=c.confrelid WHERE c.contype='f' AND n.nspname<>'public' AND d.relname IN (?,?,?) LIMIT 1", [ProyeccionReduccionExperta::HECHOS, ProyeccionReduccionExperta::CLASIFICACIONES, ProyeccionReduccionExperta::VINCULOS]) !== []) {
            throw new RuntimeException('Una FK de otro esquema bloquea la reversión.');
        }
        $nuevas = [ProyeccionReduccionExperta::HECHOS => [...array_values(ProyeccionReduccionExperta::COG), ...array_values(ProyeccionReduccionExperta::IAD)],
            ProyeccionReduccionExperta::CLASIFICACIONES => ['cod_funcionalidad_instantanea', ...array_values(ProyeccionReduccionExperta::FUN)],
            ProyeccionReduccionExperta::VINCULOS => ['cod_hecho_instantanea', 'cod_soporte_sintetico']];
        foreach (Schema::getTableListing() as $t) {
            $nombre = str_contains($t, '.') ? substr($t, strrpos($t, '.') + 1) : $t;
            foreach (Schema::getForeignKeys($t) as $fk) {
                if (isset($nuevas[$fk['foreign_table']]) && array_intersect($fk['foreign_columns'], $nuevas[$fk['foreign_table']])
                    && ! ($nombre === ProyeccionReduccionExperta::HECHOS && $fk['name'] === 'red_iadl_fun_f')) {
                    // SQLite no conserva nombres de FK: la forma completa identifica exclusivamente nuestra relación.
                    if (! ($nombre === ProyeccionReduccionExperta::HECHOS && $fk['columns'] === ['iadl_cod_funcionalidad_instantanea', 'cod_evaluacion_experta'])) {
                        throw new RuntimeException('Una FK entrante no prevista bloquea la reversión.');
                    }
                }
            }
        }
    }

    private function transaccion(Closure $operacion): void
    {
        DB::transaction(function () use ($operacion): void {
            $sqlite = DB::getDriverName() === 'sqlite';
            $anterior = $sqlite ? (int) DB::scalar('PRAGMA defer_foreign_keys') : 0;
            if ($sqlite) {
                DB::statement('PRAGMA defer_foreign_keys=ON');
            }
            try {
                $operacion();
                if ($sqlite && DB::select('PRAGMA foreign_key_check') !== []) {
                    throw new RuntimeException('La transición conserva referencias inconsistentes.');
                }
            } finally {
                if ($sqlite) {
                    DB::statement('PRAGMA defer_foreign_keys='.($anterior ? 'ON' : 'OFF'));
                }
            }
        });
    }
}
