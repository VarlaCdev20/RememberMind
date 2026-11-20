<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require_once base_path('database/migrations/support/SeparacionContextoResidente20261120.php');

/** Separa declaraciones tipadas; no modifica identidades, reglas ni valores semánticos. */
final class SepararDeclaracionesSemanticas20261120 extends Migration
{
    private const TABLAS = ['nodos_semanticos', 'componentes_regla_experta', 'mapeos_variables_fuente'];

    public const VARIABLE = ['cod_variable_experta' => 'cod_declaracion', 'cod_nodo_semantico' => 'cod_nodo_semantico',
        'cod_nodo_propietario_primario' => 'cod_nodo_propietario_primario', 'cod_dominio_valores' => 'cod_dominio_valores',
        'tipo_semantico' => 'tipo_semantico', 'papel_inferencial' => 'papel_inferencial', 'estado_variable' => 'estado',
        'fecha_hora_creacion_variable' => 'fecha_hora_creacion', 'cod_usuario_creacion_variable' => 'cod_usuario_creacion', 'observacion_variable' => 'observacion'];

    public const CRITERIO = ['cod_criterio_dominio_resultado' => 'cod_declaracion', 'cod_nodo_semantico' => 'cod_nodo_semantico',
        'criterio_cod_dominio_valores' => 'cod_dominio_valores', 'criterio_estado' => 'estado',
        'criterio_fecha_hora_creacion' => 'fecha_hora_creacion', 'criterio_cod_usuario_creacion' => 'cod_usuario_creacion', 'criterio_observacion' => 'observacion'];

    public function up(): void { $this->cambiar(true); }
    public function down(): void { $this->cambiar(false); }

    private function retiradas(): array
    {
        return array_values(array_diff(array_unique([...array_keys(self::VARIABLE), ...array_keys(self::CRITERIO)]), ['cod_nodo_semantico']));
    }

    private function contieneRetirada(string $sql): bool
    {
        return preg_match('/\b('.implode('|', $this->retiradas()).')\b/i', $sql) === 1;
    }

    private function cambiar(bool $subir): void
    {
        $motor = DB::getDriverName();
        $todos = require __DIR__.'/EsquemasDeclaracionesSemanticas20261120.php';
        if (! isset($todos[$motor])) throw new RuntimeException('Motor sin contrato verificado para declaraciones.');
        $esquemas = $todos[$motor];
        DB::transaction(function () use ($subir, $motor, $esquemas): void {
            $pg = $motor === 'pgsql';
            if (Schema::hasColumn('nodos_semanticos', 'cod_variable_experta') !== $subir
                || Schema::hasTable('declaraciones_nodo') === $subir) throw new LogicException('Punto de partida semántico distinto.');
            if ($pg) {
                DB::statement("SET LOCAL lock_timeout='5s'");
                DB::statement('LOCK TABLE nodos_semanticos,componentes_regla_experta,mapeos_variables_fuente,dominios_valores_expertos,fuentes_datos_expertas'.($subir ? '' : ',declaraciones_nodo').' IN ACCESS EXCLUSIVE MODE');
            }
            if ($subir) $this->verificar($esquemas, $pg);
            foreach (self::TABLAS as $tabla) {
                $lectura = $tabla === 'nodos_semanticos' && ! $subir ? $this->nodosOriginales($esquemas[$tabla]) : DB::table($tabla);
                DB::statement('CREATE TEMPORARY TABLE dec20_copia_'.$tabla.' AS '.$lectura->toSql(), $lectura->getBindings());
            }
            $triggers = SeparacionContextoResidente20261120::suspenderSQLite();
            if ($pg) {
                foreach ($subir ? ['nodos_semanticos','mapeos_variables_fuente'] : [] as $tabla) {
                    foreach ($esquemas[$tabla]['triggers'] as $t) {
                        // Las guardas cuyo SQL menciona el bloque deben reemplazarse antes de retirar columnas.
                        if ($this->contieneRetirada($t['funcion'])) DB::statement('DROP TRIGGER "'.$t['tgname'].'" ON '.$tabla);
                    }
                }
                $this->referenciasPostgres($esquemas, false, ! $subir);
            }
            if (! $subir && $pg) $this->guardas(false);
            if ($subir) {
                $this->crearDeclaraciones();
                foreach (['VARIABLE' => self::VARIABLE, 'CRITERIO' => self::CRITERIO] as $tipo => $campos) {
                    DB::statement('INSERT INTO declaraciones_nodo (tipo_declaracion,'.implode(',', array_values($campos)).") SELECT '".$tipo."',".implode(',', array_keys($campos)).' FROM nodos_semanticos WHERE '.array_key_first($campos).' IS NOT NULL');
                }
                $this->validarVersiones();
            }
            foreach (self::TABLAS as $tabla) {
                $e = $esquemas[$tabla];
                if ($pg) {
                    if ($tabla === 'nodos_semanticos') $this->nodoPostgres($e, $subir);
                } else {
                    $crear = $e['crear'];
                    if ($tabla === 'nodos_semanticos' && $subir) {
                        $partes = array_filter(SeparacionContextoResidente20261120::declaraciones($crear), fn ($d) => ! $this->contieneRetirada($d));
                        $crear = 'CREATE TABLE "nodos_semanticos" ('.implode(',', $partes).')';
                    } elseif ($subir) {
                        $crear = preg_replace('/(REFERENCES\s+)nodos_semanticos(\s*\(\s*"?(?:cod_variable_experta|cod_criterio_dominio_resultado)"?\s*\))/i', '$1declaraciones_nodo$2', $crear);
                    }
                    $campos = $e['columnas_insertables'];
                    if ($tabla === 'nodos_semanticos' && $subir) $campos = array_values(array_diff($campos, $this->retiradas()));
                    $indices = array_column(array_filter($e['indices_sql'], fn ($i) => $tabla !== 'nodos_semanticos' || ! $subir || ! $this->contieneRetirada($i['sql'])), 'sql');
                    SeparacionContextoResidente20261120::reconstruirSQLite($tabla, $crear, $campos, 'SELECT '.implode(',', $campos).' FROM dec20_copia_'.$tabla, $indices);
                }
            }
            if ($pg) $this->referenciasPostgres($esquemas, true, $subir);
            if (! $subir) Schema::drop('declaraciones_nodo');
            foreach ($triggers as $t) {
                if (str_starts_with($t->name, 'dec20_')) continue;
                $afectado = $this->contieneRetirada($t->sql) && preg_match('/\b(?:nodos_semanticos|mapeos_variables_fuente)\b/', $t->sql);
                if (! $afectado) DB::unprepared($t->sql);
            }
            if ($subir) {
                $this->guardas(true);
            } else {
                foreach (['nodos_semanticos','mapeos_variables_fuente'] as $tabla) {
                    foreach ($esquemas[$tabla]['triggers'] as $t) {
                        if ($pg) {
                            if ($this->contieneRetirada($t['funcion'])) { DB::unprepared(str_replace('CREATE FUNCTION', 'CREATE OR REPLACE FUNCTION', $t['funcion'])); DB::unprepared($t['definicion']); }
                        } elseif ($this->contieneRetirada($t['sql'])) DB::unprepared($t['sql']);
                    }
                }
            }
            $this->conservacion($esquemas, $subir);
            foreach (self::TABLAS as $tabla) DB::statement('DROP TABLE dec20_copia_'.$tabla);
            SeparacionContextoResidente20261120::validarSQLite();
        });
    }

    private function verificar(array $esquemas, bool $pg): void
    {
        foreach ($esquemas as $tabla => $e) {
            if ($pg) { SeparacionContextoResidente20261120::verificarPostgres($tabla, $e); continue; }
            $sql = DB::table('sqlite_master')->where('type','table')->where('name',$tabla)->value('sql');
            if (SeparacionContextoResidente20261120::firmaSQL($sql) !== SeparacionContextoResidente20261120::firmaSQL($e['crear'])) throw new RuntimeException('Contrato SQLite semántico distinto: '.$tabla);
            foreach (['index' => 'indices_sql', 'trigger' => 'triggers'] as $tipo => $clave) {
                $actual = DB::table('sqlite_master')->where('type', $tipo)->where('tbl_name', $tabla)->whereNotNull('sql')->orderBy('name')->get(['name','sql'])->map(fn($r)=>(array)$r)->all();
                if ($actual !== $e[$clave]) throw new RuntimeException('Índices o guardas semánticas distintos: '.$tabla);
            }
        }
    }

    private function crearDeclaraciones(): void
    {
        DB::statement("CREATE TABLE declaraciones_nodo (
            tipo_declaracion VARCHAR(8) NOT NULL, cod_declaracion VARCHAR(20) NOT NULL,
            cod_nodo_semantico VARCHAR(20) NOT NULL REFERENCES nodos_semanticos(cod_nodo_semantico) ON DELETE RESTRICT,
            cod_nodo_propietario_primario VARCHAR(20) REFERENCES nodos_semanticos(cod_nodo_semantico) ON DELETE RESTRICT,
            cod_dominio_valores VARCHAR(20) REFERENCES dominios_valores_expertos(cod_dominio_valores) ON DELETE RESTRICT,
            tipo_semantico VARCHAR(30), papel_inferencial VARCHAR(40), estado VARCHAR(20) NOT NULL,
            fecha_hora_creacion TIMESTAMP(0) NOT NULL, cod_usuario_creacion VARCHAR(20) NOT NULL REFERENCES usuarios(cod_usuario) ON DELETE RESTRICT,
            observacion TEXT,
            cod_variable_experta VARCHAR(20) GENERATED ALWAYS AS (CASE WHEN tipo_declaracion='VARIABLE' THEN cod_declaracion ELSE NULL END) STORED UNIQUE,
            cod_criterio_dominio_resultado VARCHAR(20) GENERATED ALWAYS AS (CASE WHEN tipo_declaracion='CRITERIO' THEN cod_declaracion ELSE NULL END) STORED UNIQUE,
            PRIMARY KEY (tipo_declaracion,cod_declaracion), UNIQUE(cod_nodo_semantico,tipo_declaracion),
            CONSTRAINT dec20_forma CHECK (((tipo_declaracion='VARIABLE' AND tipo_semantico IS NOT NULL AND papel_inferencial IS NOT NULL)
                OR (tipo_declaracion='CRITERIO' AND cod_dominio_valores IS NOT NULL AND cod_nodo_propietario_primario IS NULL AND tipo_semantico IS NULL AND papel_inferencial IS NULL)) IS TRUE))");
        foreach (['cod_nodo_propietario_primario','cod_dominio_valores','cod_usuario_creacion'] as $campo) DB::statement('CREATE INDEX dec20_'.substr(sha1($campo),0,10).'_i ON declaraciones_nodo('.$campo.')');
    }

    private function nodosOriginales(array $e): \Illuminate\Database\Query\Builder
    {
        $q = DB::table('nodos_semanticos as n')->leftJoin('declaraciones_nodo as v',fn($j)=>$j->on('v.cod_nodo_semantico','=','n.cod_nodo_semantico')->where('v.tipo_declaracion','VARIABLE'))
            ->leftJoin('declaraciones_nodo as c',fn($j)=>$j->on('c.cod_nodo_semantico','=','n.cod_nodo_semantico')->where('c.tipo_declaracion','CRITERIO'));
        return $q->select(array_map(function($campo) {
            if ($campo !== 'cod_nodo_semantico' && isset(self::VARIABLE[$campo])) return 'v.'.self::VARIABLE[$campo].' as '.$campo;
            if ($campo !== 'cod_nodo_semantico' && isset(self::CRITERIO[$campo])) return 'c.'.self::CRITERIO[$campo].' as '.$campo;
            return 'n.'.$campo;
        },array_column($e['columnas'],'name')));
    }

    private function nodoPostgres(array $e, bool $subir): void
    {
        if ($subir) {
            foreach ($e['restricciones'] as $r) if ($r['contype'] !== 'n' && $this->contieneRetirada($r['definicion'])) DB::statement('ALTER TABLE nodos_semanticos DROP CONSTRAINT "'.$r['conname'].'"');
            foreach ($this->retiradas() as $campo) DB::statement('ALTER TABLE nodos_semanticos DROP COLUMN '.$campo);
        } else {
            foreach ($e['columnas'] as $c) if (in_array($c['name'],$this->retiradas(),true)) DB::statement('ALTER TABLE nodos_semanticos ADD COLUMN '.$c['name'].' '.$c['type']);
            $campos = $this->retiradas();
            DB::statement('UPDATE nodos_semanticos n SET '.implode(',',array_map(fn($c)=>$c.'=o.'.$c,$campos)).' FROM dec20_copia_nodos_semanticos o WHERE n.cod_nodo_semantico=o.cod_nodo_semantico');
            foreach ($e['restricciones'] as $r) if ($r['contype'] !== 'n' && $this->contieneRetirada($r['definicion'])) DB::statement('ALTER TABLE nodos_semanticos ADD CONSTRAINT "'.$r['conname'].'" '.$r['definicion']);
            $porRestriccion = array_column($e['restricciones'],'conname');
            foreach ($e['indices_sql'] as $i) if ($this->contieneRetirada($i['definicion']) && ! in_array($i['nombre'],$porRestriccion,true)) DB::statement($i['definicion']);
        }
    }

    private function referenciasPostgres(array $esquemas, bool $crear, bool $declaraciones): void
    {
        foreach (['componentes_regla_experta','mapeos_variables_fuente'] as $tabla) foreach ($esquemas[$tabla]['restricciones'] as $r) {
            if ($r['contype'] !== 'f' || ! preg_match('/REFERENCES nodos_semanticos\((cod_variable_experta|cod_criterio_dominio_resultado)\)/',$r['definicion'])) continue;
            if (! $crear) DB::statement('ALTER TABLE '.$tabla.' DROP CONSTRAINT "'.$r['conname'].'"');
            else DB::statement('ALTER TABLE '.$tabla.' ADD CONSTRAINT "'.$r['conname'].'" '.($declaraciones ? str_replace('REFERENCES nodos_semanticos(', 'REFERENCES declaraciones_nodo(', $r['definicion']) : $r['definicion']));
        }
    }

    private function validarVersiones(): void
    {
        if (DB::table('declaraciones_nodo as d')->join('nodos_semanticos as n','n.cod_nodo_semantico','=','d.cod_nodo_semantico')
            ->leftJoin('nodos_semanticos as p','p.cod_nodo_semantico','=','d.cod_nodo_propietario_primario')
            ->leftJoin('dominios_valores_expertos as v','v.cod_dominio_valores','=','d.cod_dominio_valores')
            ->where(fn($q)=>$q->whereColumn('p.cod_version_modelo','<>','n.cod_version_modelo')->orWhereColumn('v.cod_version_modelo','<>','n.cod_version_modelo'))->exists()) throw new RuntimeException('La declaración y sus referencias tienen versiones diferentes.');
    }

    private function conservacion(array $esquemas, bool $subir): void
    {
        foreach (self::TABLAS as $tabla) {
            $q = $subir && $tabla === 'nodos_semanticos' ? $this->nodosOriginales($esquemas[$tabla]) : DB::table($tabla)->select(array_column($esquemas[$tabla]['columnas'],'name'));
            $a = 'SELECT * FROM dec20_copia_'.$tabla; $b = $q->toSql();
            if (DB::getDriverName() === 'pgsql') {
                $mal = DB::select('('.$a.' EXCEPT ALL '.$b.') UNION ALL ('.$b.' EXCEPT ALL '.$a.')', [...$q->getBindings(),...$q->getBindings()]);
            } else {
                $mal = DB::select('SELECT * FROM ('.$a.' EXCEPT '.$b.') UNION ALL SELECT * FROM ('.$b.' EXCEPT '.$a.')', [...$q->getBindings(),...$q->getBindings()]);
            }
            if ($mal !== []) throw new RuntimeException('La transición semántica no conserva todas las filas: '.$tabla);
        }
    }

    private function guardas(bool $crear): void
    {
        $reglas = [
            'declaracion' => ['declaraciones_nodo','INSERT OR UPDATE', "(NEW.cod_nodo_propietario_primario IS NOT NULL AND (SELECT cod_version_modelo FROM nodos_semanticos WHERE cod_nodo_semantico=NEW.cod_nodo_propietario_primario) <> (SELECT cod_version_modelo FROM nodos_semanticos WHERE cod_nodo_semantico=NEW.cod_nodo_semantico)) OR (NEW.cod_dominio_valores IS NOT NULL AND (SELECT cod_version_modelo FROM dominios_valores_expertos WHERE cod_dominio_valores=NEW.cod_dominio_valores) <> (SELECT cod_version_modelo FROM nodos_semanticos WHERE cod_nodo_semantico=NEW.cod_nodo_semantico))"],
            'nodo' => ['nodos_semanticos','UPDATE', "OLD.cod_version_modelo <> NEW.cod_version_modelo AND (EXISTS(SELECT 1 FROM relaciones_semanticas WHERE cod_nodo_origen=OLD.cod_nodo_semantico OR cod_nodo_destino=OLD.cod_nodo_semantico) OR EXISTS(SELECT 1 FROM declaraciones_nodo WHERE cod_nodo_semantico=OLD.cod_nodo_semantico OR cod_nodo_propietario_primario=OLD.cod_nodo_semantico))"],
            'dominio' => ['dominios_valores_expertos','UPDATE', "OLD.cod_version_modelo <> NEW.cod_version_modelo AND EXISTS(SELECT 1 FROM declaraciones_nodo WHERE cod_dominio_valores=OLD.cod_dominio_valores)"],
            'mapeo' => ['mapeos_variables_fuente','INSERT OR UPDATE', "(SELECT n.cod_version_modelo FROM declaraciones_nodo d JOIN nodos_semanticos n ON n.cod_nodo_semantico=d.cod_nodo_semantico WHERE d.cod_variable_experta=NEW.cod_variable_experta) <> (SELECT cod_version_modelo FROM fuentes_datos_expertas WHERE cod_fuente_dato_experta=NEW.cod_fuente_dato_experta)"],
            'variable' => ['declaraciones_nodo','UPDATE', 'OLD.tipo_declaracion=\'VARIABLE\' AND NOT (%IGUAL%)'],
            'retirar_variable' => ['declaraciones_nodo','DELETE', "OLD.tipo_declaracion='VARIABLE'"],
        ];
        $campos = ['tipo_declaracion','cod_declaracion','cod_nodo_semantico','cod_nodo_propietario_primario','cod_dominio_valores','tipo_semantico','papel_inferencial','estado','fecha_hora_creacion','cod_usuario_creacion','observacion'];
        $pg = DB::getDriverName() === 'pgsql';
        foreach ($reglas as $nombre => [$tabla,$eventos,$condicion]) {
            $id = 'dec20_'.$nombre;
            $condicion = str_replace('%IGUAL%', implode(' AND ',array_map(fn($c)=>'NEW.'.$c.($pg?' IS NOT DISTINCT FROM ':' IS ').'OLD.'.$c,$campos)), $condicion);
            if ($pg) {
                if (! $crear) { DB::statement('DROP TRIGGER '.$id.' ON '.$tabla); DB::statement('DROP FUNCTION '.$id.'()'); continue; }
                $bloqueo = match($nombre) {
                    'declaracion' => 'PERFORM 1 FROM nodos_semanticos WHERE cod_nodo_semantico IN (NEW.cod_nodo_semantico,NEW.cod_nodo_propietario_primario) ORDER BY cod_nodo_semantico FOR SHARE; PERFORM 1 FROM dominios_valores_expertos WHERE cod_dominio_valores=NEW.cod_dominio_valores FOR SHARE;',
                    'mapeo' => 'PERFORM 1 FROM declaraciones_nodo WHERE cod_variable_experta=NEW.cod_variable_experta FOR SHARE; PERFORM 1 FROM fuentes_datos_expertas WHERE cod_fuente_dato_experta=NEW.cod_fuente_dato_experta FOR SHARE;',
                    // Evita una decisión sobre una instantánea anterior a un vínculo concurrente.
                    'nodo','dominio' => "IF OLD.cod_version_modelo <> NEW.cod_version_modelo AND current_setting('transaction_isolation') <> 'read committed' THEN RAISE EXCEPTION 'El cambio de versión requiere una transacción con lectura actual' USING ERRCODE='40001'; END IF;",
                    default => '',
                };
                DB::unprepared('CREATE FUNCTION '.$id.'() RETURNS trigger LANGUAGE plpgsql AS $$ BEGIN '.$bloqueo.' IF ('.$condicion.") THEN RAISE EXCEPTION 'La declaración no puede cambiar su identidad o versión histórica' USING ERRCODE='23514'; END IF; RETURN ".($eventos === 'DELETE' ? 'OLD' : 'NEW').'; END; $$;');
                DB::unprepared('CREATE TRIGGER '.$id.' BEFORE '.$eventos.' ON '.$tabla.' FOR EACH ROW EXECUTE FUNCTION '.$id.'()');
            } else foreach (explode(' OR ',$eventos) as $evento) {
                if (! $crear) DB::statement('DROP TRIGGER '.$id.'_'.strtolower($evento));
                else DB::unprepared('CREATE TRIGGER '.$id.'_'.strtolower($evento).' BEFORE '.$evento.' ON '.$tabla.' WHEN ('.$condicion.") BEGIN SELECT RAISE(ABORT,'La declaración no puede cambiar su identidad o versión histórica'); END");
            }
        }
    }
}
