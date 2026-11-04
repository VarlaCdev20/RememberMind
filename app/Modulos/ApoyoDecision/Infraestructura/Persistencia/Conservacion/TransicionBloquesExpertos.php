<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion;

use Closure;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/** Transición reversible de cuatro relaciones 0..1. Compara todas las filas antes de retirar sus orígenes. */
final class TransicionBloquesExpertos
{
    public const REFERENCIAS = [
        ['condiciones_regla_experta', ['cod_variable_experta'], 'variables_expertas', ['cod_variable_experta']],
        ['mapeos_variables_fuente', ['cod_variable_experta'], 'variables_expertas', ['cod_variable_experta']],
        ['mapeos_variables_fuente', ['cod_variable_experta', 'cod_version_modelo'], 'variables_expertas', ['cod_variable_experta', 'cod_version_modelo']],
        ['evaluaciones_reglas', ['cod_traza_inferencia'], 'trazas_inferencia', ['cod_traza_inferencia']],
        ['hechos_instantanea_experta', ['iadl_cod_funcionalidad_instantanea', 'cod_evaluacion_experta'], 'clasificaciones_dcl_instantanea', ['cod_funcionalidad_instantanea', 'cod_evaluacion_experta']],
        ['motivos_dcl_instantanea', ['cod_clasificacion_dcl_instantanea'], 'clasificaciones_dcl_instantanea', ['cod_clasificacion_dcl_instantanea']],
        ['reglas_aplicadas_dcl_instantanea', ['cod_clasificacion_dcl_instantanea'], 'clasificaciones_dcl_instantanea', ['cod_clasificacion_dcl_instantanea']],
    ];

    public function subir(?Closure $despuesDeCopiar = null): void
    {
        $this->transaccion(function () use ($despuesDeCopiar): void {
            $this->validarReferencias(false);
            $this->validarOrigenes();
            $originales = $this->leerOriginales();
            $this->columnas(true);
            foreach (BloquesExpertos::DEFINICIONES as $entidad => $d) {
                foreach ($originales[$entidad] as $fila) {
                    BloquesExpertos::insertar($entidad, $fila);
                }
            }
            $this->comparar($originales, $this->leerBloques());
            if ($despuesDeCopiar !== null) {
                $despuesDeCopiar();
            }
            $this->restricciones(true);
            $this->referencias(true);
            foreach (array_keys(BloquesExpertos::DEFINICIONES) as $tabla) {
                Schema::drop($tabla);
            }
            $this->comparar($originales, $this->leerBloques());
        });
    }

    public function bajar(): void
    {
        $this->transaccion(function (): void {
            $this->validarReferencias(true);
            $originales = $this->leerBloques();
            $this->guardas(false);
            foreach (['2026_10_08_120006_create_variables_expertas_table.php', '2026_10_08_120019_create_resultados_criterio_table.php',
                '2026_10_08_120020_create_trazas_inferencia_table.php'] as $archivo) {
                (require database_path('migrations/'.$archivo))->up();
            }
            Schema::table('resultados_criterio', fn (Blueprint $t) => $t->text('justificacion_original')->nullable());
            Schema::create('clasificaciones_dcl_instantanea', function (Blueprint $t): void {
                $t->string('cod_clasificacion_dcl_instantanea', 20)->primary();
                $t->string('cod_evaluacion_experta', 20)->unique();
                $t->string('cod_funcionalidad_instantanea', 20)->unique();
                $t->string('version_contrato', 80);
                $t->string('diferencial', 40);
                $t->string('patron_candidato', 10)->nullable();
                $t->text('justificacion');
                $t->string('uso', 50);
                $t->char('huella_clasificacion', 64);
                $t->string('funcionalidad_independencia_relativa', 20);
                $t->text('funcionalidad_revisor_sintetico_id')->nullable();
                $t->text('funcionalidad_justificacion');
                $t->unique(['cod_funcionalidad_instantanea', 'cod_evaluacion_experta'], 'red_fun_eval_u');
                $t->index('cod_evaluacion_experta', 'sv2_'.substr(sha1('clasificaciones_dcl_instantanea:cod_evaluacion_experta'), 0, 12).'_ix');
                $t->foreign('cod_evaluacion_experta', 'sv2_'.substr(sha1('clasificaciones_dcl_instantanea:cod_evaluacion_experta'), 0, 12).'_fk')
                    ->references('cod_evaluacion_experta')->on('evaluaciones_expertas')->restrictOnDelete();
            });
            foreach ($originales as $tabla => $filas) {
                foreach (array_chunk($filas, 100) as $lote) {
                    DB::table($tabla)->insert($lote);
                }
            }
            $this->comparar($originales, $this->leerOriginales());
            $this->referencias(false);
            $this->restricciones(false);
            $this->columnas(false);
            $this->comparar($originales, $this->leerOriginales());
        });
    }

    public static function columnasNuevas(): array
    {
        $r = [];
        foreach (BloquesExpertos::DEFINICIONES as $d) {
            foreach ($d['campos'] as $origen => $destino) {
                if ($origen !== $d['propietario'] && $origen !== 'cod_version_modelo') {
                    $r[$d['tabla']][$destino] = $origen;
                }
            }
        }

        return $r;
    }

    private function validarOrigenes(): void
    {
        foreach (BloquesExpertos::DEFINICIONES as $entidad => $d) {
            $columnas = Schema::getColumnListing($entidad);
            $esperadas = array_keys($d['campos']);
            sort($columnas);
            sort($esperadas);
            $singular = array_filter(Schema::getIndexes($entidad), static fn ($i) => $i['unique'] && $i['columns'] === [$d['propietario']]);
            if ($columnas !== $esperadas || $singular === []) {
                throw new RuntimeException('Una estructura de origen distinta requiere transición explícita: '.$entidad);
            }
            if (DB::getDriverName() === 'pgsql' && DB::scalar('SELECT count(*) FROM pg_trigger WHERE tgrelid=to_regclass(?) AND NOT tgisinternal', [$entidad]) > 0) {
                throw new RuntimeException('Un trigger propio de una tabla retirada requiere transición explícita.');
            }
        }
    }

    private function columnas(bool $agregar): void
    {
        foreach (self::columnasNuevas() as $tabla => $campos) {
            $this->alterar($tabla, function (Blueprint $t) use ($agregar, $campos): void {
                if (! $agregar) {
                    $t->dropColumn(array_keys($campos));

                    return;
                }
                foreach ($campos as $c => $origen) {
                    if (str_contains($c, 'fecha_hora')) {
                        $t->dateTime($c)->nullable();
                    } elseif (in_array($origen, ['justificacion_original', 'justificacion', 'observacion', 'funcionalidad_justificacion', 'funcionalidad_revisor_sintetico_id'], true)) {
                        $t->text($c)->nullable();
                    } elseif ($origen === 'huella_clasificacion') {
                        $t->char($c, 64)->nullable();
                    } else {
                        $longitud = match ($origen) {
                            'tipo_semantico', 'estado_inferencia' => 30,
                            'papel_inferencial', 'diferencial' => 40,
                            'version_contrato' => 80, 'uso' => 50, 'patron_candidato' => 10,
                            default => 20,
                        };
                        $t->string($c, $longitud)->nullable();
                    }
                }
            });
        }
    }

    private function restricciones(bool $agregar): void
    {
        $unicas = [
            ['evaluacion_criterios', ['cod_resultado_criterio'], 'b4_resultado_u'],
            ['evaluacion_criterios', ['cod_traza_inferencia'], 'b4_traza_u'],
            ['nodos_semanticos', ['cod_variable_experta'], 'b4_variable_u'],
            ['nodos_semanticos', ['cod_variable_experta', 'cod_version_modelo'], 'b4_variable_version_u'],
            ['evaluaciones_expertas', ['cod_clasificacion_dcl_instantanea'], 'b4_clasificacion_u'],
            ['evaluaciones_expertas', ['cod_funcionalidad_instantanea'], 'b4_funcionalidad_u'],
            ['evaluaciones_expertas', ['cod_funcionalidad_instantanea', 'cod_evaluacion_experta'], 'b4_funcionalidad_eval_u'],
        ];
        $fks = [
            ['evaluacion_criterios', ['resultado_cod_valor_semantico'], 'valores_semanticos', ['cod_valor_semantico'], 'b4_resultado_valor_f'],
            ['nodos_semanticos', ['cod_nodo_propietario_primario'], 'nodos_semanticos', ['cod_nodo_semantico'], 'b4_propietario_f'],
            ['nodos_semanticos', ['cod_nodo_propietario_primario', 'cod_version_modelo'], 'nodos_semanticos', ['cod_nodo_semantico', 'cod_version_modelo'], 'b4_propietario_version_f'],
            ['nodos_semanticos', ['cod_dominio_valores'], 'dominios_valores_expertos', ['cod_dominio_valores'], 'b4_dominio_f'],
            ['nodos_semanticos', ['cod_dominio_valores', 'cod_version_modelo'], 'dominios_valores_expertos', ['cod_dominio_valores', 'cod_version_modelo'], 'b4_dominio_version_f'],
            ['nodos_semanticos', ['cod_usuario_creacion_variable'], 'usuarios', ['cod_usuario'], 'b4_variable_autor_f'],
        ];
        if ($agregar) {
            foreach ($unicas as [$tabla, $cs, $n]) {
                $this->alterar($tabla, fn (Blueprint $t) => $t->unique($cs, $n));
            }
        }
        foreach ($fks as [$tabla, $cs, $destino, $ds, $nombre]) {
            $this->alterar($tabla, function (Blueprint $t) use ($agregar, $cs, $destino, $ds, $nombre): void {
                if ($agregar) {
                    $t->foreign($cs, $nombre)->references($ds)->on($destino)->restrictOnDelete()->noActionOnUpdate();
                    $t->index($cs, $nombre.'_i');
                } else {
                    $t->dropForeign(DB::getDriverName() === 'sqlite' ? $cs : $nombre);
                    $t->dropIndex($nombre.'_i');
                }
            });
        }
        if ($agregar) {
            $this->guardas(true);
            Schema::table('evaluacion_criterios', fn (Blueprint $t) => $t->index('traza_estado_inferencia', 'b4_traza_estado_i'));
        } else {
            Schema::table('evaluacion_criterios', fn (Blueprint $t) => $t->dropIndex('b4_traza_estado_i'));
            foreach (array_reverse($unicas) as [$tabla, $cs, $nombre]) {
                $this->alterar($tabla, fn (Blueprint $t) => $t->dropUnique($nombre));
            }
        }
    }

    private function referencias(bool $subir): void
    {
        foreach (self::REFERENCIAS as [$tabla, $columnas, $entidad, $destinos]) {
            $encontradas = array_values(array_filter(Schema::getForeignKeys($tabla), static fn ($f) => $f['columns'] === $columnas));
            if (count($encontradas) !== 1) {
                throw new RuntimeException('FK entrante ausente o ambigua: '.$tabla);
            }
            $fk = $encontradas[0];
            $destino = $subir ? BloquesExpertos::DEFINICIONES[$entidad]['tabla'] : $entidad;
            $this->alterar($tabla, function (Blueprint $t) use ($fk, $columnas, $destino, $destinos): void {
                $t->dropForeign(DB::getDriverName() === 'sqlite' ? $columnas : $fk['name']);
                $nombre = $fk['name'] ?? 'b4_'.substr(hash('sha256', $t->getTable().implode('_', $columnas)), 0, 16).'_f';
                $t->foreign($columnas, $nombre)->references($destinos)->on($destino)->restrictOnDelete()->noActionOnUpdate();
            });
        }
    }

    private function validarReferencias(bool $reversion): void
    {
        $permitidas = [];
        foreach (self::REFERENCIAS as [$tabla, $cs, $origen, $ds]) {
            $permitidas[$tabla.'|'.implode(',', $cs)] = [$reversion ? BloquesExpertos::DEFINICIONES[$origen]['tabla'] : $origen, $ds];
        }
        $objetivos = $reversion ? array_unique(array_column(BloquesExpertos::DEFINICIONES, 'tabla')) : array_keys(BloquesExpertos::DEFINICIONES);
        foreach (Schema::getTables(DB::getDriverName() === 'pgsql' ? null : 'main') as $t) {
            foreach (Schema::getForeignKeys(($t['schema'] ?? null) && $t['schema'] !== 'main' ? $t['schema'].'.'.$t['name'] : $t['name']) as $fk) {
                if (! in_array($fk['foreign_table'], $objetivos, true)) {
                    continue;
                }
                if ($reversion && ! array_intersect($fk['foreign_columns'], array_merge(...array_values(array_map('array_keys', self::columnasNuevas()))))) {
                    continue;
                }
                // FK propias del bloque agregadas por esta misma transición.
                if ($reversion && str_starts_with($fk['name'] ?? '', 'b4_') && in_array($t['name'], $objetivos, true)) {
                    continue;
                }
                $esperada = $permitidas[$t['name'].'|'.implode(',', $fk['columns'])] ?? null;
                if ($esperada !== [$fk['foreign_table'], $fk['foreign_columns']] || ! in_array($t['schema'] ?? 'main', ['main', 'public'], true)) {
                    throw new RuntimeException('Una referencia externa no prevista impide consolidar o revertir: '.$t['name']);
                }
            }
        }
    }

    private function guardas(bool $agregar): void
    {
        $opcionales = ['resultados_criterio' => ['justificacion_original'], 'trazas_inferencia' => ['fecha_hora_fin'],
            'variables_expertas' => ['cod_nodo_propietario_primario', 'cod_dominio_valores', 'observacion'],
            'clasificaciones_dcl_instantanea' => ['patron_candidato', 'funcionalidad_revisor_sintetico_id']];
        $pg = DB::getDriverName() === 'pgsql';
        foreach (BloquesExpertos::DEFINICIONES as $nombre => $d) {
            $nombreCorto = 'b4_'.substr(hash('sha256', $nombre), 0, 12);
            $cols = array_filter($d['campos'], fn ($col, $k) => $k !== $d['propietario'] && $k !== 'cod_version_modelo', ARRAY_FILTER_USE_BOTH);
            if (! $agregar) {
                foreach (['insert', 'update'] as $op) {
                    DB::unprepared('DROP TRIGGER IF EXISTS '.$nombreCorto.'_'.$op.($pg ? ' ON '.$d['tabla'] : ''));
                }
                if ($pg) {
                    DB::unprepared('DROP FUNCTION IF EXISTS '.$nombreCorto.'()');
                }

                continue;
            }
            $nulos = implode(' AND ', array_map(fn ($c) => 'NEW.'.$c.' IS NULL', $cols));
            $requeridos = array_diff_key($cols, array_flip($opcionales[$nombre]));
            $llenos = implode(' AND ', array_map(fn ($c) => 'NEW.'.$c.' IS NOT NULL', $requeridos));
            $igual = implode(' AND ', array_map(fn ($c) => 'NEW.'.$c.($pg ? ' IS NOT DISTINCT FROM OLD.' : ' IS OLD.').$c, array_unique($d['campos'])));
            $forma = "(($nulos) OR ($llenos))";
            $inmutable = "OLD.{$d['id']} IS NOT NULL AND NOT ($igual)";
            if ($pg) {
                DB::unprepared("CREATE OR REPLACE FUNCTION $nombreCorto() RETURNS trigger LANGUAGE plpgsql AS \$\$ BEGIN
                    IF NOT ($forma) THEN RAISE EXCEPTION 'bloque_experto_incompleto' USING ERRCODE='23514'; END IF;
                    IF TG_OP='UPDATE' AND ($inmutable) THEN RAISE EXCEPTION 'bloque_experto_inmutable' USING ERRCODE='55000'; END IF;
                    RETURN NEW; END \$\$");
                foreach (['insert', 'update'] as $op) {
                    DB::unprepared("CREATE TRIGGER {$nombreCorto}_$op BEFORE $op ON {$d['tabla']} FOR EACH ROW EXECUTE FUNCTION $nombreCorto()");
                }
            } else {
                DB::unprepared("CREATE TRIGGER {$nombreCorto}_insert BEFORE INSERT ON {$d['tabla']} WHEN NOT $forma BEGIN SELECT RAISE(ABORT,'bloque_experto_incompleto'); END");
                DB::unprepared("CREATE TRIGGER {$nombreCorto}_update BEFORE UPDATE ON {$d['tabla']} WHEN NOT $forma OR ($inmutable) BEGIN SELECT RAISE(ABORT,'bloque_experto_inmutable_o_incompleto'); END");
            }
        }
    }

    private function leerOriginales(): array
    {
        $r = [];
        foreach (BloquesExpertos::DEFINICIONES as $tabla => $d) {
            $r[$tabla] = DB::table($tabla)->orderBy($d['id'])->get()->map(fn ($f) => (array) $f)->all();
        }

        return $r;
    }

    private function leerBloques(): array
    {
        $r = [];
        foreach (BloquesExpertos::DEFINICIONES as $entidad => $d) {
            $r[$entidad] = BloquesExpertos::consulta($entidad)->orderBy($d['id'])->get()->map(fn ($f) => (array) $f)->all();
        }

        return $r;
    }

    private function comparar(array $a, array $b): void
    {
        $normalizar = static function (array $tablas): array {
            foreach ($tablas as &$filas) {
                foreach ($filas as &$fila) {
                    ksort($fila);
                }
                unset($fila);
            }
            unset($filas);

            return $tablas;
        };
        if ($normalizar($a) !== $normalizar($b)) {
            throw new RuntimeException('La consolidación no preserva exactamente los cuatro bloques originales.');
        }
    }

    private function alterar(string $tabla, Closure $cambio): void
    {
        $triggers = DB::getDriverName() === 'sqlite' ? DB::select("SELECT name,sql FROM sqlite_master WHERE type='trigger' AND tbl_name=?", [$tabla]) : [];
        Schema::table($tabla, $cambio);
        foreach ($triggers as $t) {
            if (! DB::scalar("SELECT 1 FROM sqlite_master WHERE type='trigger' AND name=?", [$t->name])) {
                DB::unprepared($t->sql);
            }
        }
    }

    private function transaccion(Closure $operacion): void
    {
        DB::transaction(function () use ($operacion): void {
            if (DB::getDriverName() === 'pgsql') {
                $tablas = array_merge(array_keys(BloquesExpertos::DEFINICIONES), array_column(BloquesExpertos::DEFINICIONES, 'tabla'), array_column(self::REFERENCIAS, 0));
                $tablas = array_values(array_unique(array_filter($tablas, fn ($t) => Schema::hasTable($t))));
                sort($tablas);
                DB::statement("SET LOCAL lock_timeout='5s'");
                DB::statement('LOCK TABLE '.implode(',', $tablas).' IN ACCESS EXCLUSIVE MODE');
                $operacion();
            } elseif (DB::getDriverName() === 'sqlite') {
                $previo = (int) DB::scalar('PRAGMA defer_foreign_keys');
                DB::statement('PRAGMA defer_foreign_keys=ON');
                try {
                    // SQLite valida también triggers de otras tablas al renombrar una tabla reconstruida.
                    // Se suspenden y restauran TODOS dentro de la misma transacción, nunca fuera de ella.
                    $triggers = DB::select("SELECT name,tbl_name,sql FROM sqlite_master WHERE type='trigger'");
                    foreach ($triggers as $trigger) {
                        if (isset(BloquesExpertos::DEFINICIONES[$trigger->tbl_name])) {
                            throw new RuntimeException('Un trigger propio de una tabla retirada requiere transición explícita.');
                        }
                        DB::unprepared('DROP TRIGGER "'.str_replace('"', '""', $trigger->name).'"');
                    }
                    $operacion();
                    foreach ($triggers as $trigger) {
                        if (! str_starts_with($trigger->name, 'b4_') && ! DB::scalar("SELECT 1 FROM sqlite_master WHERE type='trigger' AND name=?", [$trigger->name])) {
                            DB::unprepared($trigger->sql);
                        }
                    }
                    if (DB::select('PRAGMA foreign_key_check') !== []) {
                        throw new RuntimeException('Referencias inconsistentes después de la transición.');
                    }
                } finally {
                    DB::statement('PRAGMA defer_foreign_keys='.($previo ? 'ON' : 'OFF'));
                }
            } else {
                throw new RuntimeException('Motor de transición no admitido.');
            }
        });
    }
}
