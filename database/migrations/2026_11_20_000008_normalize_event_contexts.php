<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require_once __DIR__.'/support/SeparacionContextoResidente20261120.php';
require_once __DIR__.'/support/GuardasContextosEventos20261120.php';

return new class extends Migration
{
    // Las vistas conservan el contrato SQL de lectura y escritura de cada módulo.
    private const EVENTOS = [
        'ocupaciones_cama' => ['estancias_cama', 'cod_ocupacion', 'cod_admision', 'admisiones', 'cod_residente', null],
        'respuestas_instrumento' => ['capturas_instrumento', 'cod_respuesta', 'cod_opcion', 'opciones_pregunta', 'cod_pregunta', 'cod_pregunta_libre'],
        'administraciones_medicacion' => ['dosis_registradas', 'cod_administracion', 'cod_horario_prescripcion', 'horarios_prescripcion', 'cod_prescripcion', 'cod_prescripcion_directa'],
    ];

    public function up(): void
    {
        $this->migrar(true);
    }

    public function down(): void
    {
        $this->migrar(false);
    }

    private function migrar(bool $subir): void
    {
        DB::transaction(function () use ($subir) {
            $pg = DB::getDriverName() === 'pgsql';
            if (! $pg && DB::getDriverName() !== 'sqlite') {
                throw new RuntimeException('Motor no soportado para los contextos de eventos.');
            }
            if ($pg) {
                $eventos = $subir ? array_keys(self::EVENTOS) : array_column(self::EVENTOS, 0);
                DB::statement("SET LOCAL lock_timeout='5s'");
                DB::statement('LOCK TABLE '.implode(',', [...$eventos, 'admisiones', 'horarios_prescripcion', 'opciones_pregunta']).' IN ACCESS EXCLUSIVE MODE');
            }
            $original = json_decode(file_get_contents(__DIR__.'/support/contextos_eventos_sqlite_20261120.json'), true, flags: JSON_THROW_ON_ERROR);
            $campos = [];
            foreach (self::EVENTOS as $vista => [$tabla, $pk, $fk, $padre, $dato]) {
                if (! $pg && $subir) {
                    $actual = DB::table('sqlite_master')->where(['type' => 'table', 'name' => $vista])->value('sql');
                    if (! $actual || SeparacionContextoResidente20261120::firmaSQL($actual) !== SeparacionContextoResidente20261120::firmaSQL($original[$vista]['crear'])) {
                        throw new RuntimeException("La definición SQLite de $vista difiere del contrato.");
                    }
                }
                $campos[$vista] = Schema::getColumnListing($vista);
                if (! $campos[$vista]) {
                    throw new RuntimeException("Falta la relación $vista.");
                }
                DB::statement("CREATE TEMPORARY TABLE copia_$vista AS SELECT * FROM $vista");
                if ($subir && DB::scalar("SELECT EXISTS(SELECT 1 FROM $vista e LEFT JOIN $padre p ON p.$fk=e.$fk WHERE e.$fk IS NOT NULL AND (p.$fk IS NULL OR p.$dato<>e.$dato))")) {
                    throw new RuntimeException("Hay vínculos incompatibles en $vista.");
                }
            }
            if (! $subir) {
                GuardasContextosEventos20261120::retirar($pg);
            }
            $otros = $pg ? [] : SeparacionContextoResidente20261120::suspenderSQLite();
            foreach (self::EVENTOS as $vista => [$tabla, $pk, $fk, $padre, $dato, $directo]) {
                $cols = $campos[$vista];
                if ($subir) {
                    DB::statement("ALTER TABLE $vista RENAME TO $tabla");
                } else {
                    DB::statement("DROP VIEW $vista");
                    if ($pg) {
                        DB::statement("DROP FUNCTION escribir_$vista()");
                    }
                }
                $almacenadas = array_values(array_filter(array_map(fn ($c) => $c === $dato ? $directo : $c, $cols)));
                if ($pg) {
                    $this->cambiarPostgres($subir, $vista, $tabla, $pk, $fk, $dato, $directo);
                } else {
                    $ddl = str_replace('"'.$vista.'"', '"'.$tabla.'"', $original[$vista]['crear']);
                    $indices = [];
                    foreach ($original[$vista]['indices'] as $indice) {
                        if ($subir && str_contains($indice, $dato)) {
                            continue;
                        }
                        if (! $subir && $vista === 'administraciones_medicacion' && str_contains($indice, 'uq_administracion_programada_dia')) {
                            // El índice original de SQLite había perdido la expresión de fecha en una reconstrucción.
                            $indice = 'CREATE UNIQUE INDEX uq_administracion_programada_dia ON administraciones_medicacion (cod_prescripcion, cod_horario_prescripcion, date(fecha_hora_programada)) WHERE cod_horario_prescripcion IS NOT NULL AND fecha_hora_programada IS NOT NULL';
                        }
                        $indices[] = preg_replace('/\bon "?'.$vista.'"?/i', 'ON '.$tabla, $indice);
                    }
                    $select = $cols;
                    if ($subir) {
                        if ($directo) {
                            $ddl = str_replace('"'.$dato.'" varchar not null', '"'.$directo.'" varchar', $ddl);
                            $ddl = str_replace('foreign key("'.$dato.'")', 'foreign key("'.$directo.'")', $ddl);
                            $ddl = substr($ddl, 0, -1)." , CHECK (($fk IS NULL) <> ($directo IS NULL)))";
                        } else {
                            $ddl = preg_replace('/"cod_residente" varchar not null, |foreign key\("cod_residente"\) references "residentes"\("cod_residente"\) on delete restrict, /i', '', $ddl);
                        }
                        $select = array_map(fn ($c) => $c === $directo ? "CASE WHEN $fk IS NULL THEN $dato END" : $c, $almacenadas);
                    }
                    SeparacionContextoResidente20261120::reconstruirSQLite($tabla, $ddl, $subir ? $almacenadas : $cols, 'SELECT '.implode(',', $select)." FROM copia_$vista", $indices);
                }
                if ($subir) {
                    $derivado = "(SELECT p.$dato FROM $padre p WHERE p.$fk=e.$fk)";
                    if ($directo) {
                        $derivado = "COALESCE(e.$directo,$derivado)";
                    }
                    $expresiones = array_map(fn ($c) => $c === $dato ? "$derivado AS $dato" : 'e.'.$c, $cols);
                    $seguridad = $pg ? ' WITH (security_invoker=true)' : '';
                    DB::statement("CREATE VIEW $vista$seguridad AS SELECT ".implode(',', $expresiones)." FROM $tabla e");
                    $this->escritura($pg, $vista, $tabla, $pk, $fk, $dato, $directo, $padre, $almacenadas);
                } else {
                    DB::statement("ALTER TABLE $tabla RENAME TO $vista");
                }
                $lista = implode(',', $cols);
                foreach ([[$vista, 'copia_'.$vista], ['copia_'.$vista, $vista]] as [$a,$b]) {
                    if (DB::scalar("SELECT EXISTS(SELECT $lista FROM $a EXCEPT SELECT $lista FROM $b)")) {
                        throw new RuntimeException("La reconstrucción difiere en $vista.");
                    }
                }
                DB::statement("DROP TABLE copia_$vista");
            }
            if (! $pg) {
                foreach ($otros as $trigger) {
                    if ($trigger->name === 'norm18_administracion_update'
                        || (! $subir && preg_match('/^escribir_(ocupaciones_cama|respuestas_instrumento|administraciones_medicacion)_(INSERT|UPDATE|DELETE)$/D', $trigger->name))) {
                        continue;
                    }
                    $sql = $trigger->sql;
                    if ($subir) {
                        foreach (self::EVENTOS as $vista => [$tabla]) {
                            $sql = preg_replace('/\bON "?'.$vista.'"?\s/i', 'ON '.$tabla.' ', $sql);
                        }
                    } else {
                        foreach (self::EVENTOS as $vista => [$tabla]) {
                            $sql = preg_replace('/\bON "?'.$tabla.'"?\s/i', 'ON '.$vista.' ', $sql);
                        }
                    }
                    DB::unprepared($sql);
                }
                if (! $subir) {
                    foreach ($original['administraciones_medicacion']['triggers'] as $sql) {
                        if (str_contains($sql, 'norm18_administracion_update')) {
                            DB::unprepared($sql);
                        }
                    }
                }
            }
            if ($subir) {
                GuardasContextosEventos20261120::instalar($pg);
            }
            SeparacionContextoResidente20261120::validarSQLite();
        });
    }

    private function cambiarPostgres(bool $up, string $vista, string $tabla, string $pk, string $fk, string $dato, ?string $directo): void
    {
        if ($up) {
            if ($vista === 'administraciones_medicacion') {
                DB::statement("DROP TRIGGER norm18_administracion ON $tabla");
                DB::statement('DROP INDEX uq_administracion_programada_dia');
            }
            if ($vista === 'respuestas_instrumento') {
                DB::statement("ALTER TABLE $tabla DROP CONSTRAINT respuestas_instrumento_cod_aplicacion_cod_pregunta_unique");
            }
            if (! $directo) {
                DB::statement("ALTER TABLE $tabla DROP COLUMN $dato");

                return;
            }
            DB::statement("ALTER TABLE $tabla RENAME COLUMN $dato TO $directo");
            DB::statement("ALTER TABLE $tabla ALTER COLUMN $directo DROP NOT NULL");
            DB::statement("UPDATE $tabla SET $directo=NULL WHERE $fk IS NOT NULL");
            DB::statement("ALTER TABLE $tabla ADD CONSTRAINT fuente_exclusiva CHECK (($fk IS NULL) <> ($directo IS NULL))");

            return;
        }
        if (! $directo) {
            DB::statement("ALTER TABLE $tabla ADD COLUMN $dato varchar(20) CONSTRAINT ocupaciones_cama_cod_residente_foreign REFERENCES residentes(cod_residente) ON DELETE RESTRICT");
        } else {
            DB::statement("ALTER TABLE $tabla DROP CONSTRAINT fuente_exclusiva");
            DB::statement("ALTER TABLE $tabla RENAME COLUMN $directo TO $dato");
        }
        DB::statement("UPDATE $tabla e SET $dato=p.$dato FROM copia_$vista p WHERE p.$pk=e.$pk");
        DB::statement("ALTER TABLE $tabla ALTER COLUMN $dato SET NOT NULL");
        if (! $directo) {
            DB::statement("CREATE UNIQUE INDEX uq_ocupacion_residente_activa ON $tabla(cod_residente) WHERE estado IN ('ACTIVA','ACTIVO')");
            DB::statement("CREATE INDEX ocupaciones_cama_cod_residente_estado_index ON $tabla(cod_residente,estado)");
        } elseif ($vista === 'respuestas_instrumento') {
            DB::statement("ALTER TABLE $tabla ADD CONSTRAINT respuestas_instrumento_cod_aplicacion_cod_pregunta_unique UNIQUE(cod_aplicacion,cod_pregunta)");
        } else {
            DB::statement("CREATE UNIQUE INDEX uq_administracion_programada_dia ON $tabla(cod_prescripcion,cod_horario_prescripcion,(fecha_hora_programada::date)) WHERE cod_horario_prescripcion IS NOT NULL AND fecha_hora_programada IS NOT NULL");
            DB::statement("CREATE TRIGGER norm18_administracion BEFORE UPDATE ON $tabla FOR EACH ROW EXECUTE FUNCTION norm18_administracion()");
        }
    }

    private function escritura(bool $pg, string $vista, string $tabla, string $pk, string $fk, string $dato, ?string $directo, string $padre, array $columnas): void
    {
        $valores = array_map(fn ($c) => $c === $directo ? "CASE WHEN NEW.$fk IS NULL THEN NEW.$dato END" : 'NEW.'.$c, $columnas);
        $pares = array_map(fn ($c, $v) => "$c=$v", $columnas, $valores);
        $insert = "INSERT INTO $tabla (".implode(',', $columnas).') VALUES ('.implode(',', $valores).')';
        $update = "UPDATE $tabla SET ".implode(',', $pares)." WHERE $pk=OLD.$pk";
        $delete = "DELETE FROM $tabla WHERE $pk=OLD.$pk";
        $invalido = "NEW.$fk IS NOT NULL AND NEW.$dato IS NOT NULL AND NOT EXISTS(SELECT 1 FROM $padre WHERE $fk=NEW.$fk AND $dato=NEW.$dato)";
        if ($pg) {
            DB::unprepared("CREATE FUNCTION escribir_$vista() RETURNS trigger LANGUAGE plpgsql AS \$\$ DECLARE actual $vista%ROWTYPE; BEGIN
                IF TG_OP<>'INSERT' THEN
                    SELECT * INTO actual FROM $vista WHERE $pk=OLD.$pk FOR UPDATE;
                    IF NOT FOUND OR actual IS DISTINCT FROM OLD THEN
                        RAISE EXCEPTION 'El registro cambió; vuelva a consultarlo' USING ERRCODE='40001';
                    END IF;
                END IF;
                IF TG_OP='DELETE' THEN $delete; RETURN OLD; END IF;
                PERFORM 1 FROM $padre WHERE $fk=NEW.$fk FOR UPDATE;
                IF $invalido THEN RAISE EXCEPTION 'Contexto incompatible' USING ERRCODE='23514'; END IF;
                IF TG_OP='INSERT' THEN $insert; ELSE $update; END IF; RETURN NEW; END \$\$");
            DB::statement("CREATE TRIGGER escribir_$vista INSTEAD OF INSERT OR UPDATE OR DELETE ON $vista FOR EACH ROW EXECUTE FUNCTION escribir_$vista()");
        } else {
            foreach (['INSERT' => $insert, 'UPDATE' => $update, 'DELETE' => $delete] as $evento => $sql) {
                $validar = $evento === 'DELETE' ? '' : "SELECT RAISE(ABORT,'Contexto incompatible') WHERE $invalido;";
                DB::unprepared("CREATE TRIGGER escribir_{$vista}_$evento INSTEAD OF $evento ON $vista BEGIN $validar $sql; END");
            }
        }
    }
};
