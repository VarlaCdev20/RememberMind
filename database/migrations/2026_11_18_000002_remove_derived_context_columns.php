<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const COLUMNAS = [
        'administraciones_medicacion' => 'cod_residente',
        'prescripciones' => 'cod_residente',
        'estudios_clinicos' => 'cod_residente',
        'relaciones_semanticas' => 'cod_version_modelo',
        'mapeos_variables_fuente' => 'cod_version_modelo',
    ];

    public function up(): void
    {
        DB::transaction(function (): void {
            $this->bloquear();
            foreach (self::COLUMNAS as $tabla => $campo) {
                $origen = ($tabla === 'mapeos_variables_fuente' ? DB::table('nodos_semanticos')->whereNotNull('cod_variable_experta')->select(['cod_variable_experta as clave_relacion', 'cod_version_modelo']) : \App\Compartido\Infraestructura\Persistencia\ColumnasRelacionadas::origen(DB::connection(), $tabla));
                $clave = \App\Compartido\Infraestructura\Persistencia\ColumnasRelacionadas::CONTRATOS[$tabla][1];
                if (DB::table($tabla.' as actual')->leftJoinSub($origen, 'origen', 'origen.clave_relacion', '=', 'actual.'.$clave)
                    ->where(fn ($q) => $q->whereNull('origen.'.$campo)->orWhereColumn('actual.'.$campo, '<>', 'origen.'.$campo))->exists()) {
                    throw new RuntimeException('El contexto almacenado difiere de su vínculo obligatorio en '.$tabla);
                }
            }
            $triggers = $this->suspenderSQLite();
            foreach (self::COLUMNAS as $tabla => $campo) {
                if (DB::getDriverName() === 'sqlite') {
                    $foraneas = array_filter(Schema::getForeignKeys($tabla), fn ($fk) => in_array($campo, $fk['columns'], true));
                    if ($foraneas) {
                        Schema::table($tabla, function (Blueprint $t) use ($foraneas): void {
                            foreach ($foraneas as $fk) {
                                $t->dropForeign($fk['columns']);
                            }
                        });
                    }
                    foreach (Schema::getIndexes($tabla) as $indice) {
                        if (in_array($campo, $indice['columns'], true)) {
                            DB::statement('DROP INDEX "'.$indice['name'].'"');
                        }
                    }
                }
                Schema::table($tabla, fn (Blueprint $t) => $t->dropColumn($campo));
            }
            DB::statement('CREATE UNIQUE INDEX se03_u1 ON relaciones_semanticas (cod_nodo_origen, tipo_relacion, cod_nodo_destino)');
            $this->restaurarSQLite($triggers, retirarAnteriores: true);
            $this->instalarGuardas();
            $this->verificarSQLite();
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            $this->bloquear();
            $this->retirarGuardas();
            $triggers = $this->suspenderSQLite();
            DB::statement('DROP INDEX se03_u1');
            foreach (array_reverse(self::COLUMNAS, true) as $tabla => $campo) {
                Schema::table($tabla, fn (Blueprint $t) => $t->string($campo, 20)->nullable());
                $origen = ($tabla === 'mapeos_variables_fuente' ? DB::table('nodos_semanticos')->whereNotNull('cod_variable_experta')->select(['cod_variable_experta as clave_relacion', 'cod_version_modelo']) : \App\Compartido\Infraestructura\Persistencia\ColumnasRelacionadas::origen(DB::connection(), $tabla));
                $clave = \App\Compartido\Infraestructura\Persistencia\ColumnasRelacionadas::CONTRATOS[$tabla][1];
                $sql = $origen->toSql();
                DB::update('UPDATE '.$tabla.' SET '.$campo.' = (SELECT '.$campo.' FROM ('.$sql.') AS origen WHERE origen.clave_relacion = '.$tabla.'.'.$clave.')', $origen->getBindings());
                Schema::table($tabla, fn (Blueprint $t) => $t->string($campo, 20)->nullable(false)->change());
                $nombre = match ($tabla) {
                    'relaciones_semanticas' => 'se03_f1',
                    'mapeos_variables_fuente' => 'se08_f1',
                    default => $tabla.'_'.$campo.'_foreign',
                };
                Schema::table($tabla, fn (Blueprint $t) => $t->foreign($campo, $nombre)->references($campo)
                    ->on($campo === 'cod_residente' ? 'residentes' : 'versiones_modelo_experto')->restrictOnDelete());
            }
            Schema::table('prescripciones', fn (Blueprint $t) => $t->unique(['cod_prescripcion', 'cod_residente'], 'uq_prescripcion_residente_par'));
            foreach (['prescripciones' => 'prescripcion', 'estudios_clinicos' => 'estudio'] as $tabla => $nombre) {
                Schema::table($tabla, fn (Blueprint $t) => $t->foreign(['cod_atencion', 'cod_residente'], 'fk_'.$nombre.'_atencion_residente')
                    ->references(['cod_atencion', 'cod_residente'])->on('atenciones')->restrictOnDelete());
            }
            Schema::table('administraciones_medicacion', fn (Blueprint $t) => $t->foreign(['cod_prescripcion', 'cod_residente'], 'fk_administracion_prescripcion_residente')
                ->references(['cod_prescripcion', 'cod_residente'])->on('prescripciones')->restrictOnDelete());
            foreach ([['relaciones_semanticas', 'cod_nodo_origen', 'nodos_semanticos', 'cod_nodo_semantico', 'se03_f5'],
                ['relaciones_semanticas', 'cod_nodo_destino', 'nodos_semanticos', 'cod_nodo_semantico', 'se03_f6'],
                ['mapeos_variables_fuente', 'cod_variable_experta', 'nodos_semanticos', 'cod_variable_experta', 'se08_f5'],
                ['mapeos_variables_fuente', 'cod_fuente_dato_experta', 'fuentes_datos_expertas', 'cod_fuente_dato_experta', 'se08_f6']] as [$tabla, $columna, $destino, $clave, $nombre]) {
                Schema::table($tabla, fn (Blueprint $t) => $t->foreign([$columna, 'cod_version_modelo'], $nombre)
                    ->references([$clave, 'cod_version_modelo'])->on($destino)->restrictOnDelete());
            }
            foreach ([
                ['administraciones_medicacion', ['cod_residente', 'fecha_hora_programada'], 'administraciones_medicacion_cod_residente_fecha_hora_programada'],
                ['prescripciones', ['cod_residente', 'estado'], 'prescripciones_cod_residente_estado_index'],
                ['estudios_clinicos', ['cod_residente', 'fecha_solicitud'], 'estudios_clinicos_cod_residente_fecha_solicitud_index'],
                ['mapeos_variables_fuente', ['cod_version_modelo'], 'se08_i1'],
            ] as [$tabla, $columnas, $nombre]) {
                Schema::table($tabla, fn (Blueprint $t) => $t->index($columnas, $nombre));
            }
            DB::statement('CREATE UNIQUE INDEX se03_u1 ON relaciones_semanticas (cod_version_modelo, cod_nodo_origen, tipo_relacion, cod_nodo_destino)');
            $this->restaurarSQLite($triggers);
            $this->verificarSQLite();
        });
    }

    private function bloquear(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SET LOCAL lock_timeout='5s'");
            DB::statement('LOCK TABLE atenciones, prescripciones, administraciones_medicacion, estudios_clinicos, nodos_semanticos, fuentes_datos_expertas, relaciones_semanticas, mapeos_variables_fuente IN ACCESS EXCLUSIVE MODE');
        } elseif (DB::getDriverName() !== 'sqlite') {
            throw new RuntimeException('La normalización requiere PostgreSQL o SQLite.');
        }
    }

    private function reglas(): array
    {
        return [
            'atencion' => ['atenciones', 'UPDATE', 'OLD.cod_residente <> NEW.cod_residente AND (EXISTS (SELECT 1 FROM prescripciones WHERE cod_atencion=OLD.cod_atencion) OR EXISTS (SELECT 1 FROM estudios_clinicos WHERE cod_atencion=OLD.cod_atencion))'],
            'nodo' => ['nodos_semanticos', 'UPDATE', 'OLD.cod_version_modelo <> NEW.cod_version_modelo AND (EXISTS (SELECT 1 FROM relaciones_semanticas WHERE cod_nodo_origen=OLD.cod_nodo_semantico OR cod_nodo_destino=OLD.cod_nodo_semantico) OR EXISTS (SELECT 1 FROM mapeos_variables_fuente WHERE cod_variable_experta=OLD.cod_variable_experta))'],
            'fuente' => ['fuentes_datos_expertas', 'UPDATE', 'OLD.cod_version_modelo <> NEW.cod_version_modelo AND EXISTS (SELECT 1 FROM mapeos_variables_fuente WHERE cod_fuente_dato_experta=OLD.cod_fuente_dato_experta)'],
            'relacion' => ['relaciones_semanticas', 'INSERT OR UPDATE', '(SELECT cod_version_modelo FROM nodos_semanticos WHERE cod_nodo_semantico=NEW.cod_nodo_origen) <> (SELECT cod_version_modelo FROM nodos_semanticos WHERE cod_nodo_semantico=NEW.cod_nodo_destino)'],
            'mapeo' => ['mapeos_variables_fuente', 'INSERT OR UPDATE', '(SELECT cod_version_modelo FROM nodos_semanticos WHERE cod_variable_experta=NEW.cod_variable_experta) <> (SELECT cod_version_modelo FROM fuentes_datos_expertas WHERE cod_fuente_dato_experta=NEW.cod_fuente_dato_experta)'],
            'prescripcion' => ['prescripciones', 'UPDATE', '(SELECT cod_residente FROM atenciones WHERE cod_atencion=OLD.cod_atencion) <> (SELECT cod_residente FROM atenciones WHERE cod_atencion=NEW.cod_atencion)'],
            'estudio' => ['estudios_clinicos', 'UPDATE', '(SELECT cod_residente FROM atenciones WHERE cod_atencion=OLD.cod_atencion) <> (SELECT cod_residente FROM atenciones WHERE cod_atencion=NEW.cod_atencion)'],
            'administracion' => ['administraciones_medicacion', 'UPDATE', '(SELECT a.cod_residente FROM prescripciones p JOIN atenciones a ON a.cod_atencion=p.cod_atencion WHERE p.cod_prescripcion=OLD.cod_prescripcion) <> (SELECT a.cod_residente FROM prescripciones p JOIN atenciones a ON a.cod_atencion=p.cod_atencion WHERE p.cod_prescripcion=NEW.cod_prescripcion)'],
        ];
    }

    private function instalarGuardas(): void
    {
        foreach ($this->reglas() as $nombre => [$tabla, $eventos, $condicion]) {
            $nombre = 'norm18_'.$nombre;
            if (DB::getDriverName() === 'pgsql') {
                $bloqueo = match ($tabla) {
                    'relaciones_semanticas' => 'PERFORM 1 FROM nodos_semanticos WHERE cod_nodo_semantico IN (NEW.cod_nodo_origen, NEW.cod_nodo_destino) ORDER BY cod_nodo_semantico FOR SHARE;',
                    'mapeos_variables_fuente' => 'PERFORM 1 FROM nodos_semanticos WHERE cod_variable_experta=NEW.cod_variable_experta FOR SHARE; PERFORM 1 FROM fuentes_datos_expertas WHERE cod_fuente_dato_experta=NEW.cod_fuente_dato_experta FOR SHARE;',
                    'prescripciones', 'estudios_clinicos' => 'PERFORM 1 FROM atenciones WHERE cod_atencion IN (OLD.cod_atencion, NEW.cod_atencion) ORDER BY cod_atencion FOR SHARE;',
                    'administraciones_medicacion' => 'PERFORM 1 FROM prescripciones WHERE cod_prescripcion IN (OLD.cod_prescripcion, NEW.cod_prescripcion) ORDER BY cod_prescripcion FOR SHARE;',
                    default => '',
                };
                DB::unprepared('CREATE FUNCTION '.$nombre.'() RETURNS trigger LANGUAGE plpgsql AS $$ BEGIN '.$bloqueo.' IF ('.$condicion.") THEN RAISE EXCEPTION 'El vínculo no puede cambiar el residente o la versión histórica' USING ERRCODE='23514'; END IF; RETURN NEW; END; $$;");
                DB::unprepared('CREATE TRIGGER '.$nombre.' BEFORE '.$eventos.' ON '.$tabla.' FOR EACH ROW EXECUTE FUNCTION '.$nombre.'()');
            } else {
                foreach (explode(' OR ', $eventos) as $evento) {
                    DB::unprepared('CREATE TRIGGER '.$nombre.'_'.strtolower($evento).' BEFORE '.$evento.' ON '.$tabla.' FOR EACH ROW WHEN '.$condicion.
                        " BEGIN SELECT RAISE(ABORT, 'El vínculo no puede cambiar el residente o la versión histórica'); END");
                }
            }
        }
        if (DB::getDriverName() === 'pgsql') {
            foreach (['prescripciones', 'estudios_clinicos'] as $tabla) {
                DB::unprepared('CREATE FUNCTION norm18_insert_'.$tabla.'() RETURNS trigger LANGUAGE plpgsql AS $$ BEGIN PERFORM 1 FROM atenciones WHERE cod_atencion=NEW.cod_atencion FOR SHARE; RETURN NEW; END; $$;');
                DB::unprepared('CREATE TRIGGER norm18_insert_'.$tabla.' BEFORE INSERT ON '.$tabla.' FOR EACH ROW EXECUTE FUNCTION norm18_insert_'.$tabla.'()');
            }
        }
    }

    private function retirarGuardas(): void
    {
        foreach ($this->reglas() as $nombre => [$tabla, $eventos]) {
            if (DB::getDriverName() === 'pgsql') {
                DB::statement('DROP TRIGGER norm18_'.$nombre.' ON '.$tabla);
                DB::statement('DROP FUNCTION norm18_'.$nombre.'()');
            } else {
                foreach (explode(' OR ', $eventos) as $evento) {
                    DB::statement('DROP TRIGGER norm18_'.$nombre.'_'.strtolower($evento));
                }
            }
        }
        if (DB::getDriverName() === 'pgsql') {
            foreach (['prescripciones', 'estudios_clinicos'] as $tabla) {
                DB::statement('DROP TRIGGER norm18_insert_'.$tabla.' ON '.$tabla);
                DB::statement('DROP FUNCTION norm18_insert_'.$tabla.'()');
            }
        }
    }

    private function suspenderSQLite(): array
    {
        if (DB::getDriverName() !== 'sqlite') {
            return [];
        }
        DB::statement('PRAGMA defer_foreign_keys = ON');
        $triggers = DB::select("SELECT name,tbl_name,sql FROM sqlite_master WHERE type='trigger'");
        foreach ($triggers as $trigger) {
            DB::statement('DROP TRIGGER "'.$trigger->name.'"');
        }

        return $triggers;
    }

    private function restaurarSQLite(array $triggers, bool $retirarAnteriores = false): void
    {
        foreach ($triggers as $trigger) {
            $campo = self::COLUMNAS[$trigger->tbl_name] ?? null;
            if ($retirarAnteriores && $campo && preg_match('/\b'.preg_quote($campo, '/').'\b/', $trigger->sql)) {
                continue;
            }
            DB::unprepared($trigger->sql);
        }
    }

    private function verificarSQLite(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            if (DB::select('PRAGMA foreign_key_check') !== []) {
                throw new RuntimeException('Una referencia quedó inválida después de normalizar.');
            }
            DB::statement('PRAGMA defer_foreign_keys = OFF');
        }
    }
};
