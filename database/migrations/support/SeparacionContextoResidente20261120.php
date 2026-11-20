<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Transformaciones de DDL de este corte; conserva CHECK y columnas generadas. */
final class SeparacionContextoResidente20261120
{
    /** Aborta ante contratos instalados distintos; nunca elimina restricciones desconocidas. */
    public static function verificarPostgres(string $tabla, array $esquema): void
    {
        $actuales = [
            'columnas' => Schema::getColumns($tabla),
            'restricciones' => DB::select('SELECT conname, contype, convalidated, pg_get_constraintdef(oid) AS definicion FROM pg_constraint WHERE conrelid = to_regclass(?) ORDER BY conname', ['public.'.$tabla]),
            'indices_sql' => DB::select('SELECT x.relname AS nombre, i.indisunique AS unico, i.indisprimary AS primario, i.indisvalid AS valido, pg_get_expr(i.indpred, i.indrelid) AS predicado, pg_get_expr(i.indexprs, i.indrelid) AS expresiones, pg_get_indexdef(i.indexrelid) AS definicion FROM pg_index i JOIN pg_class x ON x.oid = i.indexrelid WHERE i.indrelid = to_regclass(?) ORDER BY x.relname', ['public.'.$tabla]),
        ];
        foreach ($actuales as $tipo => $valor) {
            $actual = json_decode(json_encode($valor), true);
            $esperado = $esquema[$tipo];
            if ($tipo === 'columnas') {
                // ADD COLUMN de la reversión cambia el ordinal, nunca el contrato del atributo.
                usort($actual, fn ($a, $b) => $a['name'] <=> $b['name']);
                usort($esperado, fn ($a, $b) => $a['name'] <=> $b['name']);
            }
            if ($actual !== $esperado) {
                throw new RuntimeException('El contrato PostgreSQL clínico cambió desde el corte autorizado: '.$tabla.' ('.$tipo.').');
            }
        }
    }

    /** Tolera el quoting de identificadores que SQLite ajusta durante RENAME; conserva literales. */
    public static function firmaSQL(string $sql): string
    {
        preg_match_all('/\'(?:\'\'|[^\'])*\'|"(?:""|[^"])*"|\s+|[^\'"\s]+/', $sql, $tokens);

        return implode('', array_map(fn ($t) => str_starts_with($t, "'") ? $t : strtolower(str_replace(['"', ' ', "\n", "\r", "\t"], '', $t)), $tokens[0]));
    }

    public static function declaraciones(string $crear): array
    {
        $inicio = strpos($crear, '(');
        $fin = strrpos($crear, ')');
        if ($inicio === false || $fin === false) {
            throw new RuntimeException('El esquema clínico no contiene una declaración completa.');
        }
        $cuerpo = substr($crear, $inicio + 1, $fin - $inicio - 1);
        $partes = [];
        $actual = '';
        $nivel = 0;
        $comilla = null;
        for ($i = 0, $n = strlen($cuerpo); $i < $n; $i++) {
            $c = $cuerpo[$i];
            if ($comilla !== null) {
                $actual .= $c;
                if ($c === $comilla) {
                    if (($cuerpo[$i + 1] ?? null) === $comilla) {
                        $actual .= $cuerpo[++$i];
                    } else {
                        $comilla = null;
                    }
                }

                continue;
            }
            if ($c === "'" || $c === '"' || $c === '`') {
                $comilla = $c;
            } elseif ($c === '(') {
                $nivel++;
            } elseif ($c === ')') {
                $nivel--;
            }
            if ($c === ',' && $nivel === 0 && $comilla === null) {
                $partes[] = trim($actual);
                $actual = '';
            } else {
                $actual .= $c;
            }
        }
        if ($nivel !== 0 || $comilla !== null) {
            throw new RuntimeException('La declaración clínica contiene delimitadores inesperados.');
        }
        if (trim($actual) !== '') {
            $partes[] = trim($actual);
        }

        return $partes;
    }

    public static function checks(array $esquema, bool $pg): array
    {
        if ($pg) {
            return array_values(array_map(fn ($c) => $c['definicion'], array_filter($esquema['restricciones'], fn ($c) => $c['contype'] === 'c' && self::usaResidente($c['definicion']))));
        }

        return array_values(array_filter(self::declaraciones($esquema['crear']), fn ($d) => preg_match('/^(?:CONSTRAINT\s+\S+\s+)?CHECK\b/i', $d) && self::usaResidente($d)));
    }

    public static function usaResidente(string $sql): bool
    {
        return preg_match('/\bcod_residente\b/i', $sql) === 1;
    }

    /** Original CHECK con el sujeto canónico y los restantes valores NEW, sin reinterpretar sus predicados. */
    public static function expresion(string $check, array $columnas, string $residente): string
    {
        $expr = preg_replace('/^(?:CONSTRAINT\s+\S+\s+)?CHECK\s*/i', '', trim($check), 1, $cambios);
        if ($cambios !== 1) {
            throw new RuntimeException('No se reconoce el CHECK clínico original.');
        }

        return preg_replace_callback('/\'(?:\'\'|[^\'])*\'|"(?:""|[^"])*"|\b[a-z_][a-z0-9_]*\b/i', function ($m) use ($columnas, $residente): string {
            if (str_starts_with($m[0], "'")) {
                return $m[0];
            }
            $campo = trim($m[0], '"');
            if (! in_array($campo, $columnas, true)) {
                return $m[0];
            }

            return $campo === 'cod_residente' ? $residente : 'NEW."'.$campo.'"';
        }, $expr);
    }

    public static function crearSinResidente(string $tabla, array $esquema): string
    {
        $partes = [];
        foreach (self::declaraciones($esquema['crear']) as $d) {
            if (! self::usaResidente($d)) {
                $partes[] = $d;

                continue;
            }
            if (preg_match('/^"?cod_residente"?\s+/i', $d)) {
                continue;
            }
            if (preg_match('/^(?:CONSTRAINT\s+\S+\s+)?(?:FOREIGN KEY|UNIQUE|CHECK)\b/i', $d)) {
                continue;
            }
            throw new RuntimeException('Una dependencia clínica no contemplada utiliza cod_residente.');
        }

        return 'CREATE TABLE "'.$tabla.'" ('.implode(', ', $partes).')';
    }

    public static function reconstruirSQLite(string $tabla, string $crear, array $insertables, string $fuente, array $indices): void
    {
        $copia = $tabla.'_ctx20';
        $crear = preg_replace('/^CREATE TABLE\s+(?:"'.preg_quote($tabla, '/').'"|'.preg_quote($tabla, '/').')\s*\(/i', 'CREATE TABLE "'.$copia.'" (', $crear, 1, $cambios);
        if ($cambios !== 1) {
            throw new RuntimeException('La reconstrucción no identifica la tabla autorizada.');
        }
        $crear = preg_replace('/(REFERENCES\s+)"?'.preg_quote($tabla, '/').'"?\s*\(/i', '$1"'.$copia.'" (', $crear);
        DB::statement($crear);
        $temporales = [];
        // Las autorreferencias necesitan sus claves candidatas al preparar el INSERT,
        // incluso con FK diferidas. Los nombres originales siguen ocupados en la tabla antigua.
        foreach ($indices as $indice) {
            if (! preg_match('/^CREATE UNIQUE INDEX\b/i', $indice)) {
                continue;
            }
            $nombre = 'ctx20_tmp_'.substr(sha1($tabla.$indice), 0, 16);
            $temporal = preg_replace('/^CREATE UNIQUE INDEX\s+(?:"[^"]+"|\w+)\s+ON\s+(?:"[^"]+"|\w+)/i', 'CREATE UNIQUE INDEX "'.$nombre.'" ON "'.$copia.'"', $indice, 1, $n);
            if ($n !== 1) {
                throw new RuntimeException('No se reconoce una clave candidata clínica.');
            }
            DB::statement($temporal);
            $temporales[] = $nombre;
        }
        $campos = implode(',', array_map(fn ($c) => '"'.$c.'"', $insertables));
        DB::statement('INSERT INTO "'.$copia.'" ('.$campos.') '.$fuente);
        DB::statement('DROP TABLE "'.$tabla.'"');
        DB::statement('ALTER TABLE "'.$copia.'" RENAME TO "'.$tabla.'"');
        foreach ($indices as $indice) {
            DB::statement($indice);
        }
        foreach ($temporales as $nombre) {
            DB::statement('DROP INDEX "'.$nombre.'"');
        }
    }

    public static function suspenderSQLite(): array
    {
        if (DB::getDriverName() !== 'sqlite') {
            return [];
        }
        DB::statement('PRAGMA defer_foreign_keys=ON');
        $triggers = DB::select("SELECT name,sql FROM sqlite_master WHERE type='trigger'");
        foreach ($triggers as $t) {
            DB::statement('DROP TRIGGER "'.$t->name.'"');
        }

        return $triggers;
    }

    public static function validarSQLite(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }
        if (DB::select('PRAGMA foreign_key_check') !== []) {
            throw new RuntimeException('La separación dejó referencias clínicas inválidas.');
        }
        DB::statement('PRAGMA defer_foreign_keys=OFF');
    }
}
