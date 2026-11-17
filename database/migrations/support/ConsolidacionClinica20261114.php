<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

abstract class ConsolidacionClinica20261114 extends Migration
{
    abstract protected function crearDestino(string $tabla): void;

    abstract protected function restaurarOrigenes(): void;

    abstract protected function indicesDestino(): array;

    protected function antesDeMover(bool $subir): void {}

    protected function protegerReversion(): void {}

    protected function despuesDeMover(bool $subir): void {}

    protected function referencias(): array
    {
        return static::REFERENCIAS;
    }

    final public function up(): void
    {
        DB::transaction(function (): void {
            $tablas = array_keys(static::ORIGENES);
            $this->bloquear($tablas);
            $this->verificarReferencias($tablas, false);
            $this->antesDeMover(true);
            $temporal = static::DESTINO.'_copia';
            $this->crearDestino($temporal);
            foreach (static::ORIGENES as $tabla => [$tipo, $columnas]) {
                if (array_diff($columnas, Schema::getColumnListing($tabla)) || array_diff(Schema::getColumnListing($tabla), $columnas)) {
                    throw new RuntimeException('Cambió la estructura de '.$tabla);
                }
                $this->transferir($tabla, $temporal, $tipo, $columnas, true);
            }
            if (isset(static::ORIGENES[static::DESTINO])) {
                Schema::drop(static::DESTINO);
            }
            Schema::rename($temporal, static::DESTINO);
            foreach ($this->referencias() as [$tabla, $columna, $nombre, $origen, $clave, $claveNueva]) {
                $this->cambiarReferencia($tabla, $columna, $nombre, $origen, $clave, static::DESTINO, $claveNueva);
            }
            foreach (array_diff($tablas, [static::DESTINO]) as $tabla) {
                Schema::drop($tabla);
            }
            $this->indices(static::DESTINO, $this->indicesDestino());
            $this->despuesDeMover(true);
        });
    }

    final public function down(): void
    {
        DB::transaction(function (): void {
            $this->bloquear([static::DESTINO]);
            $this->protegerReversion();
            $this->verificarReferencias([static::DESTINO], true);
            $this->antesDeMover(false);
            $origen = static::DESTINO;
            if (isset(static::ORIGENES[static::DESTINO])) {
                $origen .= '_copia';
                foreach ($this->indicesDestino() as $indice => $columnas) {
                    DB::statement('DROP INDEX '.$indice);
                }
                Schema::rename(static::DESTINO, $origen);
            }
            $this->restaurarOrigenes();
            foreach (static::ORIGENES as $tabla => [$tipo, $columnas]) {
                $this->transferir($tabla, $origen, $tipo, $columnas, false);
            }
            foreach ($this->referencias() as [$tabla, $columna, $nombre, $destino, $clave, $claveNueva]) {
                $this->cambiarReferencia($tabla, $columna, $nombre, static::DESTINO, $claveNueva, $destino, $clave);
            }
            Schema::drop($origen);
            $this->despuesDeMover(false);
        });
    }

    private function transferir(string $tabla, string $consolidada, ?string $tipo, array $columnas, bool $subir): void
    {
        $fisicas = array_replace($columnas, [0 => static::CODIGO]);
        $campos = implode(', ', $columnas);
        $destino = implode(', ', $fisicas);
        $filtro = $tipo === null ? " WHERE tipo_evento IN ('MOVILIDAD', 'CONDUCTA')" : ' WHERE tipo_evento = ?';
        $parametros = $tipo === null ? [] : [$tipo];
        $original = "SELECT $campos FROM $tabla";
        $fusion = "SELECT $destino FROM $consolidada".$filtro;
        if ($subir) {
            if ($tipo === null) {
                DB::insert("INSERT INTO $consolidada ($destino) $original");
            } else {
                DB::insert("INSERT INTO $consolidada ($destino, tipo_evento) SELECT $campos, ? FROM $tabla", $parametros);
            }
        } else {
            DB::insert("INSERT INTO $tabla ($campos) $fusion", $parametros);
        }
        if (DB::scalar("SELECT EXISTS ($original EXCEPT $fusion)", $parametros)
            || DB::scalar("SELECT EXISTS ($fusion EXCEPT $original)", $parametros)) {
            throw new RuntimeException('Los datos copiados difieren de '.$tabla);
        }
    }

    private function bloquear(array $tablas): void
    {
        if (DB::getDriverName() === 'pgsql') {
            $dependientes = array_column($this->referencias(), 0);
            DB::statement("SET LOCAL lock_timeout = '5s'");
            DB::statement('LOCK TABLE '.implode(', ', array_unique([...$tablas, ...$dependientes])).' IN ACCESS EXCLUSIVE MODE');
        } elseif (DB::getDriverName() !== 'sqlite') {
            throw new RuntimeException('La consolidación requiere PostgreSQL o SQLite.');
        }
    }

    private function verificarReferencias(array $tablas, bool $reversion): void
    {
        $esperadas = [];
        foreach ($this->referencias() as [$tabla, $columna, , $origen, $clave, $claveNueva]) {
            $esperadas[] = [$tabla, [$columna], $reversion ? static::DESTINO : $origen, [$reversion ? $claveNueva : $clave]];
        }
        $reales = [];
        foreach (Schema::getTables() as $tabla) {
            foreach (Schema::getForeignKeys($tabla['schema_qualified_name'] ?? $tabla['name']) as $fk) {
                if (in_array($fk['foreign_table'], $tablas, true)) {
                    $reales[] = [$tabla['name'], $fk['columns'], $fk['foreign_table'], $fk['foreign_columns']];
                }
            }
        }
        sort($esperadas);
        sort($reales);
        if ($esperadas !== $reales) {
            throw new RuntimeException('Las referencias entrantes cambiaron antes de la consolidación de '.static::DESTINO);
        }
    }

    protected function cambiarReferencia(
        string $tabla, string $columna, string $nombre, string $origen, string $clave, string $destino, string $claveNueva,
    ): void {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE $tabla DROP CONSTRAINT $nombre, ADD CONSTRAINT $nombre FOREIGN KEY ($columna) REFERENCES $destino($claveNueva) ON DELETE RESTRICT");

            return;
        }
        $sql = DB::table('sqlite_master')->where('type', 'table')->where('name', $tabla)->value('sql');
        $referencia = '/(REFERENCES\s+)"?'.preg_quote($origen, '/').'"?\s*\(\s*"?'.preg_quote($clave, '/').'"?\s*\)/i';
        $definicion = preg_replace($referencia, '$1"'.$destino.'"("'.$claveNueva.'")', $sql, -1, $cambios);
        if ($cambios !== 1) {
            throw new RuntimeException('No se encontró la referencia original de '.$tabla.'.'.$columna);
        }
        $objetos = DB::table('sqlite_master')->where('tbl_name', $tabla)->whereIn('type', ['index', 'trigger'])->whereNotNull('sql')->pluck('sql');
        $columnas = implode(', ', array_map(fn ($c) => '"'.$c.'"', Schema::getColumnListing($tabla)));
        $copia = $tabla.'_fk_copia';
        DB::statement(preg_replace('/^CREATE TABLE\s+"?'.preg_quote($tabla, '/').'"?/i', 'CREATE TABLE "'.$copia.'"', $definicion));
        DB::statement("INSERT INTO $copia ($columnas) SELECT $columnas FROM $tabla");
        // Una tabla hija (por ejemplo un respaldo de condición) puede conservar filas
        // referidas a esta misma tabla mientras la copia ocupa momentáneamente otro nombre.
        $diferidas = (int) DB::scalar('PRAGMA defer_foreign_keys');
        if (! $diferidas && DB::select('PRAGMA foreign_key_check') !== []) {
            throw new RuntimeException('SQLite tiene referencias inválidas antes de reconstruir '.$tabla);
        }
        DB::statement('PRAGMA defer_foreign_keys = ON');
        try {
            Schema::drop($tabla);
            Schema::rename($copia, $tabla);
            foreach ($objetos as $objeto) {
                DB::unprepared($objeto);
            }
            if (DB::select('PRAGMA foreign_key_check') !== []) {
                throw new RuntimeException('La referencia reconstruida de '.$tabla.' es inválida.');
            }
        } finally {
            if (! $diferidas) {
                DB::statement('PRAGMA defer_foreign_keys = OFF');
            }
        }
    }

    protected function restaurarAtencion(string $tabla, string $nombre): void
    {
        if (DB::getDriverName() === 'pgsql') {
            Schema::table($tabla, function (Blueprint $t) use ($nombre): void {
                $t->foreign(['cod_atencion', 'cod_residente'], $nombre)->references(['cod_atencion', 'cod_residente'])->on('atenciones')->restrictOnDelete();
            });

            return;
        }
        $this->triggers($tabla, 'trg_'.$tabla.'_atencion_residente',
            'NEW.cod_atencion IS NOT NULL AND NOT EXISTS (SELECT 1 FROM atenciones WHERE cod_atencion = NEW.cod_atencion AND cod_residente = NEW.cod_residente)');
    }

    protected function restaurarTalla(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE mediciones_antropometricas ADD CONSTRAINT ck_med_ant_talla CHECK (talla IS NULL OR talla BETWEEN 50.0 AND 240.0)');

            return;
        }
        $this->triggers('mediciones_antropometricas', 'trg_ck_med_ant_talla', 'NEW.talla IS NOT NULL AND NEW.talla NOT BETWEEN 50.0 AND 240.0');
    }

    private function triggers(string $tabla, string $nombre, string $condicion): void
    {
        foreach (['INSERT' => '_ins', 'UPDATE' => '_upd'] as $evento => $sufijo) {
            DB::unprepared("CREATE TRIGGER $nombre$sufijo BEFORE $evento ON $tabla WHEN $condicion BEGIN SELECT RAISE(ABORT, 'Restricción de integridad clínica incumplida.'); END");
        }
    }

    protected function indices(string $tabla, array $indices): void
    {
        foreach ($indices as $nombre => $columnas) {
            DB::statement("CREATE INDEX $nombre ON $tabla ($columnas)");
        }
    }
}
