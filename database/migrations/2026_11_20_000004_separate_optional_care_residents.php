<?php

use App\Compartido\Infraestructura\Persistencia\ContextosResidente;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require_once __DIR__.'/support/SeparacionContextoResidente20261120.php';

return new class extends Migration
{
    private const TABLAS = [
        'mediciones_clinicas' => ['cod_registro_medicion', 'residentes_mediciones_clinicas'],
        'registros_observaciones_cuidado' => ['cod_observacion_cuidado', 'residentes_observaciones_cuidado'],
    ];

    public function up(): void
    {
        $this->cambiar(true);
    }

    public function down(): void
    {
        $this->cambiar(false);
    }

    private function cambiar(bool $separar): void
    {
        $todos = require __DIR__.'/support/EsquemasContextoClinico20261120.php';
        $motor = DB::getDriverName();
        if (! isset($todos[$motor])) {
            throw new RuntimeException('La separación requiere un motor verificado.');
        }
        DB::transaction(function () use ($separar, $motor, $todos): void {
            $pg = $motor === 'pgsql';
            $esquemas = $todos[$motor];
            if ($pg) {
                $tablas = ['atenciones', ...array_keys(self::TABLAS)];
                if (! $separar) {
                    $tablas = [...$tablas, ...array_column(self::TABLAS, 1)];
                }
                DB::statement("SET LOCAL lock_timeout='5s'");
                DB::statement('LOCK TABLE '.implode(',', $tablas).' IN ACCESS EXCLUSIVE MODE');
            }
            foreach (self::TABLAS as $tabla => [$pk,$directa]) {
                if (Schema::hasColumn($tabla, 'cod_residente') !== $separar) {
                    throw new LogicException('El contexto de '.$tabla.' no corresponde al punto de partida.');
                }
                $esquema = $esquemas[$tabla];
                $esperadas = array_column($esquema['columnas'], 'name');
                if (! $separar) {
                    $esperadas = [...array_values(array_diff($esperadas, ['cod_residente'])), 'cod_contexto_directo'];
                }
                $actuales = Schema::getColumnListing($tabla);
                sort($esperadas);
                sort($actuales);
                if ($actuales !== $esperadas) {
                    throw new RuntimeException('La tabla clínica contiene campos ajenos a esta transición.');
                }
                if ($separar && $pg) {
                    SeparacionContextoResidente20261120::verificarPostgres($tabla, $esquema);
                }
                if ($separar && ! $pg) {
                    $crearActual = DB::table('sqlite_master')->where('type', 'table')->where('name', $tabla)->value('sql');
                    if (SeparacionContextoResidente20261120::firmaSQL($crearActual) !== SeparacionContextoResidente20261120::firmaSQL($esquema['crear'])) {
                        throw new RuntimeException('El contrato SQLite clínico cambió desde el corte autorizado.');
                    }
                    $indicesActuales = DB::table('sqlite_master')->where('type', 'index')->where('tbl_name', $tabla)->whereNotNull('sql')->pluck('sql')->all();
                    $indicesEsperados = array_column($esquema['indices_sql'], 'sql');
                    sort($indicesActuales);
                    sort($indicesEsperados);
                    if ($indicesActuales !== $indicesEsperados) {
                        throw new RuntimeException('Los índices clínicos contienen cambios ajenos a esta transición.');
                    }
                }
                $lectura = $this->lectura($tabla, $esquema, $separar);
                $columnas = array_column($esquema['columnas'], 'name');
                $nullable = array_values(array_filter($esquema['columnas'], fn ($c) => $c['name'] === 'cod_residente'))[0]['nullable'];
                $validacion = DB::query()->fromSub($lectura, 'original');
                if (! $nullable && (clone $validacion)->whereNull('cod_residente')->exists()) {
                    throw new RuntimeException('Un registro clínico carece de residente.');
                }
                foreach (SeparacionContextoResidente20261120::checks($esquema, $pg) as $check) {
                    $expresion = SeparacionContextoResidente20261120::expresion($check, [], '');
                    if ((clone $validacion)->whereRaw('('.$expresion.') IS NOT TRUE')->exists()) {
                        throw new RuntimeException('Un registro clínico incumple el contrato original.');
                    }
                }
                if ($separar && DB::table($tabla.' as c')->leftJoin('atenciones as a', 'a.cod_atencion', '=', 'c.cod_atencion')
                    ->whereNotNull('c.cod_atencion')->where(fn ($q) => $q->whereNull('a.cod_residente')->orWhereColumn('a.cod_residente', '<>', 'c.cod_residente'))->exists()) {
                    throw new RuntimeException('El residente original no coincide con su atención.');
                }
                if (! $separar && DB::table($tabla.' as c')->join($directa.' as d', 'd.'.$pk, '=', 'c.'.$pk)->whereNotNull('c.cod_atencion')->exists()) {
                    throw new RuntimeException('Un registro conserva dos fuentes de residente.');
                }
                DB::statement('CREATE TEMPORARY TABLE ctx20_copia_'.$tabla.' AS '.$lectura->toSql(), $lectura->getBindings());
            }
            $triggers = SeparacionContextoResidente20261120::suspenderSQLite();
            if (! $separar && $pg) {
                $this->guardas($esquemas, false);
            }
            foreach (self::TABLAS as $tabla => [$pk,$directa]) {
                $e = $esquemas[$tabla];
                if (! $separar) {
                    Schema::drop($directa);
                }
                if ($pg) {
                    $this->alterarPostgres($tabla, $e, $separar);
                } else {
                    $crear = $separar ? SeparacionContextoResidente20261120::crearSinResidente($tabla, $e) : $e['crear'];
                    if ($separar) {
                        $crear = preg_replace('/^(CREATE TABLE\s+"?'.$tabla.'"?\s*\()/i', '$1 cod_contexto_directo VARCHAR(20) GENERATED ALWAYS AS (CASE WHEN cod_atencion IS NULL THEN '.$pk.' ELSE NULL END) STORED UNIQUE, ', $crear, 1);
                    }
                    $campos = array_values(array_filter($e['columnas_insertables'], fn ($c) => ! $separar || $c !== 'cod_residente'));
                    $origen = $this->lectura($tabla, $e, $separar)->select(array_map(fn ($c) => 't.'.$c, $campos));
                    if (! $separar) {
                        $origen = DB::table('ctx20_copia_'.$tabla.' as t')->select(array_map(fn ($c) => 't.'.$c, $campos));
                    }
                    $indices = array_map(fn ($i) => $i['sql'], array_filter($e['indices_sql'], fn ($i) => ! $separar || ! SeparacionContextoResidente20261120::usaResidente($i['sql'])));
                    SeparacionContextoResidente20261120::reconstruirSQLite($tabla, $crear, $campos, $origen->toSql(), $indices);
                }
                if ($separar) {
                    // La FK a la clave generada también impide dos fuentes bajo concurrencia y REPEATABLE READ.
                    DB::statement('CREATE TABLE '.$directa.' ('.$pk.' VARCHAR(20) NOT NULL PRIMARY KEY, cod_residente VARCHAR(20) NOT NULL, FOREIGN KEY ('.$pk.') REFERENCES '.$tabla.'(cod_contexto_directo) ON DELETE RESTRICT DEFERRABLE INITIALLY DEFERRED, FOREIGN KEY (cod_residente) REFERENCES residentes(cod_residente) ON DELETE RESTRICT)');
                    DB::statement('CREATE INDEX '.$directa.'_residente_i ON '.$directa.' (cod_residente)');
                    DB::statement('INSERT INTO '.$directa.' ('.$pk.',cod_residente) SELECT '.$pk.',cod_residente FROM ctx20_copia_'.$tabla.' WHERE cod_atencion IS NULL AND cod_residente IS NOT NULL');
                }
                $nuevoIndice = 'ctx20_'.substr(sha1($tabla), 0, 12).'_atencion_i';
                if ($separar && ! collect(Schema::getIndexes($tabla))->contains(fn ($i) => ($i['columns'][0] ?? null) === 'cod_atencion')) {
                    DB::statement('CREATE INDEX '.$nuevoIndice.' ON '.$tabla.' (cod_atencion)');
                }
                if (! $separar && $pg && collect(Schema::getIndexes($tabla))->contains(fn ($i) => $i['name'] === $nuevoIndice)) {
                    DB::statement('DROP INDEX '.$nuevoIndice);
                }
            }
            $propios = array_map(fn ($tabla) => 'ctx20_'.substr(sha1($tabla), 0, 12), array_keys(self::TABLAS));
            foreach ($triggers as $t) {
                if (! collect($propios)->contains(fn ($prefijo) => str_starts_with($t->name, $prefijo))) {
                    DB::unprepared($t->sql);
                }
            }
            if ($separar) {
                $this->guardas($esquemas, true);
            }
            foreach (self::TABLAS as $tabla => [$pk,$directa]) {
                $consulta = $this->lectura($tabla, $esquemas[$tabla], ! $separar);
                $sql = $consulta->toSql();
                $operador = $pg ? ' EXCEPT ALL ' : ' EXCEPT ';
                foreach (['SELECT * FROM ctx20_copia_'.$tabla.$operador.$sql, $sql.$operador.'SELECT * FROM ctx20_copia_'.$tabla] as $d) {
                    if ((int) DB::selectOne('SELECT COUNT(*) AS n FROM ('.$d.') diferencias', $consulta->getBindings())->n !== 0) {
                        throw new RuntimeException('La reconstrucción clínica no conserva todas las filas.');
                    }
                }
                if (DB::table('ctx20_copia_'.$tabla)->count() !== DB::table($tabla)->count()) {
                    throw new RuntimeException('Cambió el número de registros clínicos.');
                }
                if (! $separar) {
                    if ($pg) {
                        DB::statement('ALTER TABLE '.$tabla.' DROP COLUMN cod_contexto_directo');
                    }
                }
                DB::statement('DROP TABLE ctx20_copia_'.$tabla);
            }
            SeparacionContextoResidente20261120::validarSQLite();
        });
    }

    private function lectura(string $tabla, array $esquema, bool $fisico): Builder
    {
        [$pk] = self::TABLAS[$tabla];
        $q = DB::table($tabla.' as t');
        if (! $fisico) {
            $q->joinSub(ContextosResidente::origen(DB::connection(), $tabla), 's', 's.clave_relacion', '=', 't.'.$pk);
        }

        return $q->select(array_map(fn ($c) => (! $fisico && $c['name'] === 'cod_residente' ? 's.' : 't.').$c['name'], $esquema['columnas']));
    }

    private function alterarPostgres(string $tabla, array $e, bool $separar): void
    {
        $afectadas = array_values(array_filter($e['restricciones'], fn ($r) => in_array($r['contype'], ['f', 'u', 'c'], true) && SeparacionContextoResidente20261120::usaResidente($r['definicion'])));
        if ($separar) {
            foreach (['f', 'u', 'c'] as $tipo) {
                foreach ($afectadas as $r) {
                    if ($r['contype'] === $tipo) {
                        DB::statement('ALTER TABLE '.$tabla.' DROP CONSTRAINT "'.$r['conname'].'"');
                    }
                }
            }
            DB::statement('ALTER TABLE '.$tabla.' DROP COLUMN cod_residente');
            [$pk] = self::TABLAS[$tabla];
            DB::statement('ALTER TABLE '.$tabla.' ADD COLUMN cod_contexto_directo VARCHAR(20) GENERATED ALWAYS AS (CASE WHEN cod_atencion IS NULL THEN '.$pk.' ELSE NULL END) STORED UNIQUE');
        } else {
            DB::statement('ALTER TABLE '.$tabla.' ADD COLUMN cod_residente VARCHAR(20)');
            [$pk] = self::TABLAS[$tabla];
            DB::statement('UPDATE '.$tabla.' SET cod_residente=(SELECT s.cod_residente FROM ctx20_copia_'.$tabla.' s WHERE s.'.$pk.'='.$tabla.'.'.$pk.')');
            if (! array_values(array_filter($e['columnas'], fn ($c) => $c['name'] === 'cod_residente'))[0]['nullable']) {
                DB::statement('ALTER TABLE '.$tabla.' ALTER COLUMN cod_residente SET NOT NULL');
            }
            foreach (['u', 'f', 'c'] as $tipo) {
                foreach ($afectadas as $r) {
                    if ($r['contype'] === $tipo) {
                        DB::statement('ALTER TABLE '.$tabla.' ADD CONSTRAINT "'.$r['conname'].'" '.$r['definicion']);
                    }
                }
            }
            $nombres = array_column($afectadas, 'conname');
            foreach ($e['indices_sql'] as $i) {
                if (SeparacionContextoResidente20261120::usaResidente($i['definicion']) && ! in_array($i['nombre'], $nombres, true)) {
                    DB::statement($i['definicion']);
                }
            }
        }
    }

    private function guardas(array $esquemas, bool $crear): void
    {
        foreach (self::TABLAS as $tabla => [$pk,$directa]) {
            $id = 'ctx20_'.substr(sha1($tabla), 0, 12);
            $pg = DB::getDriverName() === 'pgsql';
            $r = $pg ? 'v_residente' : $this->residenteNuevo($directa, $pk);
            $checks = SeparacionContextoResidente20261120::checks($esquemas[$tabla], $pg);
            $columnas = array_column($esquemas[$tabla]['columnas'], 'name');
            $fallos = array_map(fn ($c) => '('.SeparacionContextoResidente20261120::expresion($c, $columnas, $r).') IS NOT TRUE', $checks);
            if (! array_values(array_filter($esquemas[$tabla]['columnas'], fn ($c) => $c['name'] === 'cod_residente'))[0]['nullable']) {
                $fallos[] = $r.' IS NULL';
            }
            $fallos[] = 'NEW.cod_atencion IS NOT NULL AND EXISTS (SELECT 1 FROM '.$directa.' WHERE '.$pk.'=NEW.'.$pk.')';
            if ($tabla === 'mediciones_clinicas') {
                $fallos[] = 'NEW.cod_valoracion_origen IS NOT NULL AND NOT EXISTS (SELECT 1 FROM mediciones_clinicas o LEFT JOIN atenciones a ON a.cod_atencion=o.cod_atencion LEFT JOIN '.$directa.' d ON d.'.$pk.'=o.'.$pk.' WHERE o.cod_valoracion_dolor=NEW.cod_valoracion_origen AND COALESCE(a.cod_residente,d.cod_residente)='.$r.')';
            }
            $condicion = implode(' OR ', array_map(fn ($v) => '('.$v.')', $fallos));
            $padre = 'OLD.cod_residente <> NEW.cod_residente AND ('.($pg ? "current_setting('transaction_isolation') <> 'read committed' OR " : '').'EXISTS (SELECT 1 FROM '.$tabla.' WHERE cod_atencion=OLD.cod_atencion))';
            if ($pg) {
                if ($crear) {
                    DB::unprepared('CREATE FUNCTION '.$id.'() RETURNS trigger LANGUAGE plpgsql AS $$ DECLARE v_residente TEXT; BEGIN IF TG_OP=\'UPDATE\' AND OLD.cod_atencion IS DISTINCT FROM NEW.cod_atencion THEN RAISE EXCEPTION \'El episodio clínico original se conserva\' USING ERRCODE=\'23514\'; END IF; PERFORM 1 FROM atenciones WHERE cod_atencion=NEW.cod_atencion FOR SHARE; v_residente := '.$this->residenteNuevo($directa, $pk).'; IF ('.$condicion.") THEN RAISE EXCEPTION 'El registro clínico no cumple su contexto y contrato original' USING ERRCODE='23514'; END IF; RETURN NEW; END; $$;");
                    DB::statement('CREATE TRIGGER '.$id.' BEFORE INSERT OR UPDATE ON '.$tabla.' FOR EACH ROW EXECUTE FUNCTION '.$id.'()');
                    DB::unprepared('CREATE FUNCTION '.$id.'_directo() RETURNS trigger LANGUAGE plpgsql AS $$ BEGIN IF TG_OP<>\'INSERT\' OR EXISTS (SELECT 1 FROM '.$tabla.' WHERE '.$pk.'=NEW.'.$pk.") THEN RAISE EXCEPTION 'El sujeto original del registro se conserva' USING ERRCODE='23514'; END IF; RETURN NEW; END; $$;");
                    DB::statement('CREATE TRIGGER '.$id.'_directo BEFORE INSERT OR UPDATE OR DELETE ON '.$directa.' FOR EACH ROW EXECUTE FUNCTION '.$id.'_directo()');
                    DB::unprepared('CREATE FUNCTION '.$id.'_atencion() RETURNS trigger LANGUAGE plpgsql AS $$ BEGIN IF ('.$padre.") THEN RAISE EXCEPTION 'La atención conserva el residente de sus registros' USING ERRCODE='23514'; END IF; RETURN NEW; END; $$;");
                    DB::statement('CREATE TRIGGER '.$id.'_atencion BEFORE UPDATE ON atenciones FOR EACH ROW EXECUTE FUNCTION '.$id.'_atencion()');
                } else {
                    foreach ([$id => $tabla, $id.'_directo' => $directa, $id.'_atencion' => 'atenciones'] as $n => $t) {
                        DB::statement('DROP TRIGGER '.$n.' ON '.$t);
                        DB::statement('DROP FUNCTION '.$n.'()');
                    }
                }
            } elseif ($crear) {
                foreach (['INSERT', 'UPDATE'] as $evento) {
                    DB::unprepared('CREATE TRIGGER '.$id.'_'.strtolower($evento).' BEFORE '.$evento.' ON '.$tabla.' WHEN ('.$condicion.") BEGIN SELECT RAISE(ABORT,'El registro clínico no cumple su contexto y contrato original'); END");
                }
                DB::unprepared('CREATE TRIGGER '.$id.'_episodio BEFORE UPDATE ON '.$tabla." WHEN OLD.cod_atencion IS NOT NEW.cod_atencion BEGIN SELECT RAISE(ABORT,'El episodio clínico original se conserva'); END");
                foreach (['INSERT', 'UPDATE', 'DELETE'] as $evento) {
                    DB::unprepared('CREATE TRIGGER '.$id.'_directo_'.strtolower($evento).' BEFORE '.$evento.' ON '.$directa.' '.($evento === 'INSERT' ? 'WHEN EXISTS (SELECT 1 FROM '.$tabla.' WHERE '.$pk.'=NEW.'.$pk.') ' : '')."BEGIN SELECT RAISE(ABORT,'El sujeto original del registro se conserva'); END");
                }
                DB::unprepared('CREATE TRIGGER '.$id.'_atencion BEFORE UPDATE ON atenciones WHEN ('.$padre.") BEGIN SELECT RAISE(ABORT,'La atención conserva el residente de sus registros'); END");
            }
        }
    }

    private function residenteNuevo(string $directa,string $pk): string
    {
        return '(CASE WHEN NEW.cod_atencion IS NOT NULL THEN (SELECT cod_residente FROM atenciones WHERE cod_atencion=NEW.cod_atencion) ELSE (SELECT cod_residente FROM '.$directa.' WHERE '.$pk.'=NEW.'.$pk.') END)';
    }
};
