<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Transición acotada a las cuatro uniones autorizadas el 16/11/2026. */
abstract class ConsolidacionTipada20261116 extends Migration
{
    protected const DISCRIMINADOR = 'tipo_evento';

    protected function protegerFusion(): void {}

    protected function protegerOriginales(): void {}

    protected function declaracionesOriginales(string $tabla, array $declaraciones): array
    {
        return $declaraciones;
    }

    private const DOCUMENTACION_GERENCIA = [
        'cod_usuario_carga', 'fecha_hora_carga', 'fecha_emision',
        'cod_usuario_anulacion', 'fecha_hora_anulacion', 'motivo_anulacion',
    ];

    final public function up(): void
    {
        DB::transaction(function (): void {
            $fuentes = $this->fuentes();
            $referencias = $this->referencias(array_keys($fuentes));
            $this->bloquear([...array_keys($fuentes), ...array_column($referencias, 'tabla')]);
            foreach ($fuentes as $tabla => $definicion) {
                $actuales = Schema::getColumnListing($tabla);
                $esperadas = array_keys($definicion['columns']);
                sort($actuales);
                sort($esperadas);
                if ($actuales !== $esperadas) {
                    throw new RuntimeException('La estructura de '.$tabla.' difiere del contrato autorizado.');
                }
                $this->verificarClavesOriginales($tabla, $definicion);
                Schema::rename($tabla, $tabla.'_antes16');
            }
            $this->crearFusion($fuentes);
            foreach ($fuentes as $tabla => $definicion) {
                $this->copiar($tabla.'_antes16', $definicion, true);
            }
            foreach ($referencias as $referencia) {
                $this->redirigir($referencia, $referencia['origen'].'_antes16', static::DESTINO,
                    $this->clavesDestino($referencia['origen'], $referencia['destinos'], $fuentes));
            }
            foreach (array_reverse(array_keys($fuentes)) as $tabla) {
                $this->retirarCopia($tabla.'_antes16');
            }
            $this->indexarFusion($fuentes);
            $this->protegerFusion();
            $this->finalizarSQLite();
        });
    }

    final public function down(): void
    {
        DB::transaction(function (): void {
            $fuentes = $this->fuentes();
            $referencias = $this->referencias([static::DESTINO]);
            $this->bloquear([static::DESTINO, ...array_column($referencias, 'tabla')]);
            Schema::rename(static::DESTINO, static::DESTINO.'_antes16');
            // Los índices explícitos tienen nombres globales en ambos motores.
            foreach ($this->indicesFusion($fuentes) as $nombre => $_) {
                DB::statement('DROP INDEX '.$nombre);
            }
            foreach ($fuentes as $tabla => $definicion) {
                $this->crearOriginal($tabla, $definicion);
                $this->copiar($tabla, $definicion, false);
            }
            foreach ($referencias as $referencia) {
                [$tabla, $columnas] = $this->origenReferencia($referencia['destinos'], $fuentes);
                $this->redirigir($referencia, static::DESTINO.'_antes16', $tabla, $columnas);
            }
            $this->retirarCopia(static::DESTINO.'_antes16');
            foreach ($fuentes as $tabla => $definicion) {
                $this->indexarOriginal($tabla, $definicion);
            }
            $this->protegerOriginales();
            $this->finalizarSQLite();
        });
    }

    private function fuentes(): array
    {
        $fuentes = static::FUENTES;
        if (isset($fuentes['documentos']) && ! Schema::hasColumn('documentos', 'cod_usuario_carga')) {
            $documentos = &$fuentes['documentos'];
            foreach (self::DOCUMENTACION_GERENCIA as $columna) {
                unset($documentos['columns'][$columna]);
            }
            $documentos['foreign'] = array_values(array_filter($documentos['foreign'],
                fn ($f) => ! array_intersect($f[1], self::DOCUMENTACION_GERENCIA)));
            $documentos['indexes'] = array_values(array_filter($documentos['indexes'],
                fn ($i) => ! array_intersect($i[1], self::DOCUMENTACION_GERENCIA)));
            $documentos['checks'] = [];
        }

        return $fuentes;
    }

    private function codigoAlternativo(string $tabla, array $fuentes): string
    {
        $clave = $fuentes[$tabla]['key'];

        return $clave === static::CODIGO ? $clave.'_ref' : $clave;
    }

    private function verificarClavesOriginales(string $tabla, array $definicion): void
    {
        $actuales = array_map(fn ($fk) => [$fk['columns'], $fk['foreign_table'], $fk['foreign_columns']], Schema::getForeignKeys($tabla));
        $esperadas = array_map(fn ($fk) => [$fk[1], $fk[2], $fk[3]], $definicion['foreign']);
        $atencion = [['cod_atencion', 'cod_residente'], 'atenciones', ['cod_atencion', 'cod_residente']];
        if (DB::getDriverName() === 'sqlite' && in_array($atencion, $esperadas, true) && ! in_array($atencion, $actuales, true)) {
            // El esquema SQLite anterior puede representar esta regla con triggers.
            // La copia valida cada fila contra la FK compuesta del nuevo destino.
            $actuales[] = $atencion;
        }
        sort($actuales);
        sort($esperadas);
        if ($actuales !== $esperadas) {
            throw new RuntimeException('Las claves de '.$tabla.' difieren del contrato autorizado.');
        }
    }

    private function columnasFusion(array $fuentes): array
    {
        $columnas = [static::CODIGO => ['varchar(20)', false, null], static::DISCRIMINADOR => ['varchar(20)', false, null]];
        foreach ($fuentes as $definicion) {
            foreach ($definicion['columns'] as $columna => [$tipo, , $generada]) {
                if ($columna === $definicion['key'] || $columna === static::DISCRIMINADOR) {
                    continue;
                }
                if (isset($columnas[$columna]) && $columnas[$columna][0] !== $tipo) {
                    throw new RuntimeException('Tipos incompatibles en '.$columna);
                }
                $columnas[$columna] = [$tipo, true, $generada];
            }
        }
        foreach ($fuentes as $tabla => $definicion) {
            if ($definicion['type'] !== null) {
                $tipo = $definicion['type'];
                $columnas[$this->codigoAlternativo($tabla, $fuentes)] = ['varchar(20)', true,
                    'CASE WHEN '.static::DISCRIMINADOR." = '$tipo' THEN ".static::CODIGO.' END'];
            }
        }

        return $columnas;
    }

    private function crearFusion(array $fuentes): void
    {
        $columnas = $this->columnasFusion($fuentes);
        $sql = $this->declaracionesColumnas($columnas, [static::CODIGO]);
        $ramas = [];
        $foraneas = [];
        foreach ($fuentes as $tabla => $definicion) {
            $campos = [];
            $tipo = $definicion['type'];
            if ($tipo !== null) {
                $campos[] = static::DISCRIMINADOR." = '$tipo'";
            }
            foreach ($definicion['columns'] as $campo => [$tipoSQL, $nullable, $generada]) {
                if ($campo === $definicion['key'] || $generada !== null) {
                    continue;
                }
                if (! $nullable) {
                    $campos[] = "$campo IS NOT NULL";
                }
                if (preg_match('/(?:character varying|varchar)\((\d+)\)/', $tipoSQL, $longitud)) {
                    $campos[] = "($campo IS NULL OR length($campo) <= $longitud[1])";
                }
            }
            foreach ($definicion['checks'] as [, $check]) {
                $campos[] = substr($check, strlen('CHECK '));
            }
            foreach ($columnas as $campo => [, , $generada]) {
                if ($generada === null && $campo !== static::CODIGO && $campo !== static::DISCRIMINADOR
                    && ! isset($definicion['columns'][$campo])) {
                    $campos[] = "$campo IS NULL";
                }
            }
            $ramas[] = '('.implode(' AND ', $campos).')';
            foreach ($definicion['foreign'] as [, $camposFK, $destino, $claves]) {
                if (isset($fuentes[$destino])) {
                    $claves = $this->clavesDestino($destino, $claves, $fuentes);
                    $destino = static::DESTINO;
                }
                $foraneas[] = 'FOREIGN KEY ('.implode(', ', $camposFK).') REFERENCES '.$destino.'('.implode(', ', $claves).') ON DELETE RESTRICT';
            }
            foreach ($definicion['indexes'] as [, $camposIndice, $unico]) {
                if ($unico) {
                    $camposIndice = $this->clavesDestino($tabla, $camposIndice, $fuentes);
                    $sql[] = 'UNIQUE ('.implode(', ', $camposIndice).')';
                }
            }
        }
        foreach ($columnas as $campo => [, , $generada]) {
            if ($generada !== null) {
                $sql[] = 'UNIQUE ('.$campo.')';
            }
        }
        $sql = array_unique([...$sql, ...$foraneas, 'CHECK (('.implode(' OR ', $ramas).') IS TRUE)']);
        $unicasSQLite = [];
        if (DB::getDriverName() === 'sqlite') {
            // Laravel necesita nombres explícitos al reconstruir tablas con columnas nuevas.
            $unicasSQLite = array_filter($sql, fn ($declaracion) => str_starts_with($declaracion, 'UNIQUE ('));
            $sql = array_diff($sql, $unicasSQLite);
        }
        DB::statement('CREATE TABLE '.static::DESTINO.' ('.implode(', ', $sql).')');
        foreach ($unicasSQLite as $unica) {
            $nombre = 'u16_key_'.substr(hash('sha256', static::DESTINO.$unica), 0, 20);
            DB::statement('CREATE UNIQUE INDEX '.$nombre.' ON '.static::DESTINO.substr($unica, strlen('UNIQUE')));
        }
    }

    private function declaracionesColumnas(array $columnas, array $pk): array
    {
        $sql = [];
        foreach ($columnas as $nombre => [$tipo, $nullable, $generada]) {
            $declaracion = $nombre.' '.$tipo;
            if ($generada !== null) {
                $declaracion .= ' GENERATED ALWAYS AS ('.$generada.') STORED';
            } elseif (! $nullable) {
                $declaracion .= ' NOT NULL';
            }
            $sql[] = $declaracion;
        }
        $sql[] = 'PRIMARY KEY ('.implode(', ', $pk).')';

        return $sql;
    }

    private function crearOriginal(string $tabla, array $definicion): void
    {
        $sql = $this->declaracionesColumnas($definicion['columns'], [$definicion['key']]);
        foreach ($definicion['foreign'] as [$nombre, $columnas, $destino, $claves]) {
            $sql[] = 'CONSTRAINT '.$nombre.' FOREIGN KEY ('.implode(', ', $columnas).') REFERENCES '.$destino.'('.implode(', ', $claves).') ON DELETE RESTRICT';
        }
        foreach ($definicion['checks'] as [$nombre, $check]) {
            $sql[] = 'CONSTRAINT '.$nombre.' '.$check;
        }
        foreach ($definicion['indexes'] as [$nombre, $columnas, $unico]) {
            if ($unico && DB::getDriverName() === 'pgsql') {
                $sql[] = 'CONSTRAINT '.$nombre.' UNIQUE ('.implode(', ', $columnas).')';
            }
        }
        DB::statement('CREATE TABLE '.$tabla.' ('.implode(', ', $this->declaracionesOriginales($tabla, $sql)).')');
        // Una FK puede apuntar a una clave candidata generada de este original.
        if (DB::getDriverName() === 'sqlite') {
            foreach ($definicion['indexes'] as [$nombre, $columnas, $unico]) {
                if ($unico) {
                    DB::statement('CREATE UNIQUE INDEX '.$nombre.' ON '.$tabla.' ('.implode(', ', $columnas).')');
                }
            }
        }
    }

    private function copiar(string $origen, array $definicion, bool $subir): void
    {
        $columnas = array_keys(array_filter($definicion['columns'], fn ($c) => $c[2] === null));
        $destinos = array_map(fn ($c) => $c === $definicion['key'] ? static::CODIGO : $c, $columnas);
        $tablaFusion = static::DESTINO.($subir ? '' : '_antes16');
        $filtro = $definicion['type'] !== null ? ' WHERE '.static::DISCRIMINADOR." = '".$definicion['type']."'"
            : ' WHERE '.static::DISCRIMINADOR.' IN ('.($this->tipoDeFamilia()).')';
        $original = 'SELECT '.implode(', ', $columnas).' FROM '.$origen;
        $fusion = 'SELECT '.implode(', ', $destinos).' FROM '.$tablaFusion.$filtro;
        if ($subir) {
            $tipo = $definicion['type'];
            $seleccion = 'SELECT '.implode(', ', $columnas).($tipo !== null ? ", '$tipo'" : '').' FROM '.$origen;
            DB::statement('INSERT INTO '.$tablaFusion.' ('.implode(', ', $destinos).($tipo !== null ? ', '.static::DISCRIMINADOR : '').') '.$seleccion);
        } else {
            DB::statement('INSERT INTO '.$origen.' ('.implode(', ', $columnas).') '.$fusion);
        }
        if (DB::scalar("SELECT EXISTS ($original EXCEPT $fusion)") || DB::scalar("SELECT EXISTS ($fusion EXCEPT $original)")) {
            throw new RuntimeException('La copia difiere del contenido original de '.$origen);
        }
    }

    private function tipoDeFamilia(): string
    {
        return match (static::DESTINO) {
            'valoraciones_profesionales' => "'PSICOLOGICA', 'NUTRICIONAL', 'FUNCIONAL'",
            'mediciones_clinicas' => "'SIGNOS', 'ANTROPOMETRIA'",
            default => throw new RuntimeException('Familia consolidada desconocida.'),
        };
    }

    private function clavesDestino(string $tabla, array $claves, array $fuentes): array
    {
        return array_map(fn ($c) => $c === $fuentes[$tabla]['key'] && $fuentes[$tabla]['type'] !== null
            ? $this->codigoAlternativo($tabla, $fuentes) : $c, $claves);
    }

    private function origenReferencia(array $claves, array $fuentes): array
    {
        foreach ($fuentes as $tabla => $definicion) {
            $alternativa = $definicion['type'] === null ? $definicion['key'] : $this->codigoAlternativo($tabla, $fuentes);
            $identificadores = [$alternativa, ...array_keys(array_filter($definicion['columns'], fn ($c) => $c[2] !== null))];
            if (array_intersect($claves, $identificadores)) {
                return [$tabla, array_map(fn ($c) => $c === $alternativa ? $definicion['key'] : $c, $claves)];
            }
        }
        throw new RuntimeException('No se puede reconstruir una referencia entrante.');
    }

    private function referencias(array $tablas): array
    {
        if (DB::getDriverName() === 'pgsql') {
            $parametros = implode(', ', array_fill(0, count($tablas), '?'));
            $filas = DB::select("SELECT t.relname AS tabla, r.conname AS nombre, d.relname AS origen,
                json_agg(a.attname ORDER BY p.orden)::text AS columnas,
                json_agg(b.attname ORDER BY p.orden)::text AS destinos
                FROM pg_constraint r
                JOIN pg_class t ON t.oid=r.conrelid
                JOIN pg_namespace nt ON nt.oid=t.relnamespace
                JOIN pg_class d ON d.oid=r.confrelid
                JOIN pg_namespace nd ON nd.oid=d.relnamespace
                CROSS JOIN LATERAL unnest(r.conkey,r.confkey) WITH ORDINALITY AS p(columna,referencia,orden)
                JOIN pg_attribute a ON a.attrelid=r.conrelid AND a.attnum=p.columna
                JOIN pg_attribute b ON b.attrelid=r.confrelid AND b.attnum=p.referencia
                WHERE r.contype='f' AND nt.nspname=current_schema() AND nd.nspname=current_schema()
                    AND d.relname IN ($parametros) AND t.relname NOT IN ($parametros)
                GROUP BY t.relname,r.conname,d.relname ORDER BY t.relname,r.conname", [...$tablas, ...$tablas]);

            return array_map(fn ($r) => ['tabla' => $r->tabla, 'nombre' => $r->nombre, 'origen' => $r->origen,
                'columnas' => json_decode($r->columnas, true, flags: JSON_THROW_ON_ERROR),
                'destinos' => json_decode($r->destinos, true, flags: JSON_THROW_ON_ERROR)], $filas);
        }
        $referencias = [];
        foreach (Schema::getTables() as $tabla) {
            if (in_array($tabla['name'], $tablas, true)) {
                continue;
            }
            foreach (Schema::getForeignKeys($tabla['schema_qualified_name'] ?? $tabla['name']) as $fk) {
                if (in_array($fk['foreign_table'], $tablas, true)) {
                    $referencias[] = ['tabla' => $tabla['name'], 'nombre' => $fk['name'], 'columnas' => $fk['columns'],
                        'origen' => $fk['foreign_table'], 'destinos' => $fk['foreign_columns']];
                }
            }
        }

        return $referencias;
    }

    private function redirigir(array $r, string $origen, string $destino, array $claves): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE '.$r['tabla'].' DROP CONSTRAINT '.$r['nombre'].', ADD CONSTRAINT '.$r['nombre']
                .' FOREIGN KEY ('.implode(', ', $r['columnas']).') REFERENCES '.$destino.'('.implode(', ', $claves).') ON DELETE RESTRICT');

            return;
        }
        $tabla = $r['tabla'];
        $sql = DB::table('sqlite_master')->where('type', 'table')->where('name', $tabla)->value('sql');
        $patron = '/(REFERENCES\s+)"?'.preg_quote($origen, '/').'"?\s*\(\s*"?'.implode('"?\s*,\s*"?', array_map(fn ($c) => preg_quote($c, '/'), $r['destinos'])).'"?\s*\)/i';
        $sql = preg_replace($patron, '$1'.$destino.'('.implode(', ', $claves).')', $sql, -1, $cambios);
        if ($cambios !== 1) {
            throw new RuntimeException('No se encontró la referencia de '.$tabla);
        }
        $copia = $tabla.'_fk16';
        $sql = preg_replace('/^CREATE TABLE\s+"?'.preg_quote($tabla, '/').'"?/i', 'CREATE TABLE '.$copia, $sql);
        $objetos = DB::table('sqlite_master')->where('tbl_name', $tabla)->whereIn('type', ['index', 'trigger'])->whereNotNull('sql')->pluck('sql');
        $columnas = array_column(array_filter(Schema::getColumns($tabla), fn ($c) => empty($c['generation'])), 'name');
        DB::statement($sql);
        DB::statement('INSERT INTO '.$copia.' ('.implode(', ', $columnas).') SELECT '.implode(', ', $columnas).' FROM '.$tabla);
        DB::statement('PRAGMA defer_foreign_keys = ON');
        Schema::drop($tabla);
        Schema::rename($copia, $tabla);
        foreach ($objetos as $objeto) {
            DB::unprepared($objeto);
        }
        $this->verificarSQLite();
        DB::statement('PRAGMA defer_foreign_keys = OFF');
    }

    private function indicesFusion(array $fuentes): array
    {
        $indices = [];
        foreach ($fuentes as $tabla => $definicion) {
            foreach ($definicion['foreign'] as [, $columnasFK]) {
                $indices['u16_'.substr(hash('sha256', static::DESTINO.implode(',', $columnasFK)), 0, 20)] = $columnasFK;
            }
            foreach ($definicion['indexes'] as [$nombre, $columnas, $unico]) {
                if (! $unico) {
                    $indices['u16_'.substr(hash('sha256', static::DESTINO.$tabla.$nombre), 0, 20)] =
                        $this->clavesDestino($tabla, $columnas, $fuentes);
                }
            }
        }
        foreach (['cod_residente', 'cod_personal', 'cod_usuario'] as $contexto) {
            if (isset($this->columnasFusion($fuentes)[$contexto])) {
                $indices['u16_'.substr(hash('sha256', static::DESTINO.$contexto.'_tipo'), 0, 20)] = [$contexto, static::DISCRIMINADOR];
            }
        }

        return $indices;
    }

    private function indexarFusion(array $fuentes): void
    {
        foreach ($this->indicesFusion($fuentes) as $nombre => $columnas) {
            DB::statement('CREATE INDEX '.$nombre.' ON '.static::DESTINO.' ('.implode(', ', $columnas).')');
        }
    }

    private function indexarOriginal(string $tabla, array $definicion): void
    {
        foreach ($definicion['indexes'] as [$nombre, $columnas, $unico]) {
            if (! $unico) {
                DB::statement('CREATE INDEX '.$nombre.' ON '.$tabla.' ('.implode(', ', $columnas).')');
            }
        }
    }

    private function bloquear(array $tablas): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SET LOCAL lock_timeout = '5s'");
            DB::statement('LOCK TABLE '.implode(', ', array_unique($tablas)).' IN ACCESS EXCLUSIVE MODE');
        } elseif (DB::getDriverName() !== 'sqlite') {
            throw new RuntimeException('La transición requiere PostgreSQL o SQLite.');
        }
    }

    private function verificarSQLite(): void
    {
        if (DB::getDriverName() === 'sqlite' && DB::select('PRAGMA foreign_key_check') !== []) {
            throw new RuntimeException('Una referencia SQLite quedó inválida.');
        }
    }

    private function retirarCopia(string $tabla): void
    {
        // SQLite comprueba también la autorreferencia de las filas que retira DROP.
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA defer_foreign_keys = ON');
        }
        Schema::drop($tabla);
    }

    private function finalizarSQLite(): void
    {
        $this->verificarSQLite();
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA defer_foreign_keys = OFF');
        }
    }
}
