<?php

use App\Compartido\Infraestructura\Persistencia\ColumnasRelacionadas;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLAS = ['consentimientos', 'ejecuciones_cuidado'];

    public function up(): void
    {
        $this->cambiar(true);
    }

    public function down(): void
    {
        $this->cambiar(false);
    }

    private function cambiar(bool $derivar): void
    {
        DB::transaction(function () use ($derivar): void {
            $pg = DB::getDriverName() === 'pgsql';
            if ($pg) {
                DB::statement("SET LOCAL lock_timeout='5s'");
                DB::statement('LOCK TABLE admisiones, planes_cuidado, intervenciones_cuidado, consentimientos, ejecuciones_cuidado IN ACCESS EXCLUSIVE MODE');
            } elseif (DB::getDriverName() !== 'sqlite') {
                throw new RuntimeException('La normalización requiere PostgreSQL o SQLite.');
            }
            $copias = [];
            foreach (self::TABLAS as $tabla) {
                if (Schema::hasColumn($tabla, 'cod_residente') !== $derivar) {
                    throw new LogicException('El esquema de '.$tabla.' no coincide con el punto de partida.');
                }
                [$campo, $clave] = ColumnasRelacionadas::CONTRATOS[$tabla];
                $origen = ColumnasRelacionadas::origen(DB::connection(), $tabla);
                if ($derivar && DB::table($tabla.' as t')->leftJoinSub($origen, 'o', 'o.clave_relacion', '=', 't.'.$clave)
                    ->where(fn ($q) => $q->whereNull('o.cod_residente')->orWhereColumn('t.cod_residente', '<>', 'o.cod_residente'))->exists()) {
                    throw new RuntimeException('Hay un residente incompatible con el vínculo de '.$tabla.'.');
                }
                $columnas = Schema::getColumnListing($tabla);
                $fuente = DB::table($tabla.' as t')->select(array_map(fn ($c) => 't.'.$c, $columnas));
                if (! $derivar) {
                    $fuente->joinSub($origen, 'o', 'o.clave_relacion', '=', 't.'.$clave)->addSelect('o.cod_residente');
                    $columnas[] = 'cod_residente';
                }
                DB::statement('CREATE TEMPORARY TABLE fn5_20_'.$tabla.' AS '.$fuente->toSql(), $fuente->getBindings());
                $copias[$tabla] = $columnas;
            }
            $triggers = $this->suspenderSQLite();
            if ($derivar) {
                if ($pg) {
                    DB::statement('DROP TRIGGER trg_ejecucion_residente_plan ON ejecuciones_cuidado');
                    DB::statement('DROP FUNCTION validar_ejecucion_residente_plan()');
                }
                foreach (self::TABLAS as $tabla) {
                    if (! $pg) {
                        Schema::table($tabla, fn (Blueprint $t) => $t->dropForeign(['cod_residente']));
                    }
                    Schema::table($tabla, fn (Blueprint $t) => $t->dropColumn('cod_residente'));
                }
                $this->restaurarSQLite($triggers);
                $this->guardas(true);
            } else {
                if ($pg) {
                    $this->guardas(false);
                }
                foreach (self::TABLAS as $tabla) {
                    Schema::table($tabla, fn (Blueprint $t) => $t->string('cod_residente', 20)->nullable());
                    $clave = ColumnasRelacionadas::CONTRATOS[$tabla][1];
                    $origen = ColumnasRelacionadas::origen(DB::connection(), $tabla);
                    DB::update('UPDATE '.$tabla.' SET cod_residente=(SELECT cod_residente FROM ('.$origen->toSql().') o WHERE o.clave_relacion='.$tabla.'.'.$clave.')', $origen->getBindings());
                    Schema::table($tabla, function (Blueprint $t) use ($tabla): void {
                        $t->string('cod_residente', 20)->nullable(false)->change();
                        $t->foreign('cod_residente', $tabla.'_cod_residente_foreign')->references('cod_residente')->on('residentes')->restrictOnDelete();
                    });
                }
                $this->restaurarSQLite($triggers);
                $this->restaurarPertenenciaAnterior();
            }
            foreach ($copias as $tabla => $campos) {
                $persistido = $derivar ? ColumnasRelacionadas::consulta(DB::connection(), $tabla) : DB::table($tabla);
                $sql = $persistido->select($campos)->toSql();
                $antes = 'SELECT '.implode(',', $campos).' FROM fn5_20_'.$tabla;
                if (DB::scalar('SELECT EXISTS ('.$antes.' EXCEPT '.$sql.')', $persistido->getBindings())
                    || DB::scalar('SELECT EXISTS ('.$sql.' EXCEPT '.$antes.')', $persistido->getBindings())) {
                    throw new RuntimeException('La normalización no reconstruye '.$tabla.'.');
                }
                DB::statement('DROP TABLE fn5_20_'.$tabla);
            }
            if (! $pg && DB::select('PRAGMA foreign_key_check') !== []) {
                throw new RuntimeException('La normalización dejó referencias inválidas.');
            }
        });
    }

    private function reglas(): array
    {
        $snapshot = DB::getDriverName() === 'pgsql' ? "current_setting('transaction_isolation') NOT IN ('read committed','read uncommitted') OR " : '';
        $contacto = '(NEW.firma_residente = (NEW.cod_residente_contacto IS NOT NULL)) OR (NEW.cod_residente_contacto IS NOT NULL AND NOT EXISTS (SELECT 1 FROM residentes_contactos rc JOIN admisiones a ON a.cod_residente=rc.cod_residente WHERE rc.cod_residente_contacto=NEW.cod_residente_contacto AND a.cod_admision=NEW.cod_admision))';
        $residenteEjecucion = fn ($prefijo) => '(SELECT p.cod_residente FROM intervenciones_cuidado i JOIN planes_cuidado p ON p.cod_plan=i.cod_plan WHERE i.cod_intervencion='.$prefijo.'.cod_intervencion)';
        $bloqueoEjecucion = "IF TG_OP='UPDATE' THEN PERFORM 1 FROM intervenciones_cuidado WHERE cod_intervencion IN (OLD.cod_intervencion,NEW.cod_intervencion) ORDER BY cod_intervencion FOR SHARE; PERFORM 1 FROM planes_cuidado WHERE cod_plan IN (SELECT cod_plan FROM intervenciones_cuidado WHERE cod_intervencion IN (OLD.cod_intervencion,NEW.cod_intervencion)) ORDER BY cod_plan FOR SHARE; ELSE PERFORM 1 FROM intervenciones_cuidado WHERE cod_intervencion=NEW.cod_intervencion FOR SHARE; PERFORM 1 FROM planes_cuidado WHERE cod_plan IN (SELECT cod_plan FROM intervenciones_cuidado WHERE cod_intervencion=NEW.cod_intervencion) FOR SHARE; END IF;";

        return [
            'consentir_insert' => ['consentimientos', 'INSERT', $contacto, 'PERFORM 1 FROM admisiones WHERE cod_admision=NEW.cod_admision FOR SHARE;'],
            'consentir_update' => ['consentimientos', 'UPDATE', '('.$contacto.') OR (SELECT cod_residente FROM admisiones WHERE cod_admision=OLD.cod_admision) <> (SELECT cod_residente FROM admisiones WHERE cod_admision=NEW.cod_admision)', 'PERFORM 1 FROM admisiones WHERE cod_admision IN (OLD.cod_admision,NEW.cod_admision) ORDER BY cod_admision FOR SHARE;'],
            'ejecutar_insert' => ['ejecuciones_cuidado', 'INSERT', 'FALSE', $bloqueoEjecucion],
            'ejecutar_update' => ['ejecuciones_cuidado', 'UPDATE', $residenteEjecucion('OLD').' <> '.$residenteEjecucion('NEW'), $bloqueoEjecucion],
            'admision' => ['admisiones', 'UPDATE', 'OLD.cod_residente <> NEW.cod_residente AND ('.$snapshot.'EXISTS (SELECT 1 FROM consentimientos WHERE cod_admision=OLD.cod_admision))', ''],
            'intervencion' => ['intervenciones_cuidado', 'UPDATE', 'OLD.cod_plan <> NEW.cod_plan AND ('.$snapshot.'EXISTS (SELECT 1 FROM ejecuciones_cuidado WHERE cod_intervencion=OLD.cod_intervencion))', ''],
            'plan' => ['planes_cuidado', 'UPDATE', 'OLD.cod_residente <> NEW.cod_residente AND ('.$snapshot.'EXISTS (SELECT 1 FROM ejecuciones_cuidado e JOIN intervenciones_cuidado i ON i.cod_intervencion=e.cod_intervencion WHERE i.cod_plan=OLD.cod_plan))', ''],
        ];
    }

    private function guardas(bool $crear): void
    {
        foreach ($this->reglas() as $id => [$tabla, $evento, $condicion, $bloqueo]) {
            $nombre = 'fn5_20_'.$id;
            if (DB::getDriverName() === 'pgsql') {
                if ($crear) {
                    DB::unprepared('CREATE FUNCTION '.$nombre.'() RETURNS trigger LANGUAGE plpgsql AS $$ BEGIN '.$bloqueo.' IF ('.$condicion.") THEN RAISE EXCEPTION 'El registro debe conservar su residente y vínculo autorizado' USING ERRCODE='23514'; END IF; RETURN NEW; END; $$;");
                    DB::statement('CREATE TRIGGER '.$nombre.' BEFORE '.$evento.' ON '.$tabla.' FOR EACH ROW EXECUTE FUNCTION '.$nombre.'()');
                } else {
                    DB::statement('DROP TRIGGER '.$nombre.' ON '.$tabla);
                    DB::statement('DROP FUNCTION '.$nombre.'()');
                }
            } elseif ($crear) {
                DB::unprepared('CREATE TRIGGER '.$nombre.' BEFORE '.$evento.' ON '.$tabla.' FOR EACH ROW WHEN ('.$condicion.") BEGIN SELECT RAISE(ABORT, 'El registro debe conservar su residente y vínculo autorizado'); END");
            }
        }
    }

    private function suspenderSQLite(): array
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

    private function restaurarSQLite(array $triggers): void
    {
        foreach ($triggers as $t) {
            if (str_starts_with($t->name, 'fn5_20_') || str_starts_with($t->name, 'trg_ejecucion_residente_plan')) {
                continue;
            }
            DB::unprepared($t->sql);
        }
    }

    private function restaurarPertenenciaAnterior(): void
    {
        $condicion = 'NOT EXISTS (SELECT 1 FROM intervenciones_cuidado i JOIN planes_cuidado p ON p.cod_plan=i.cod_plan WHERE i.cod_intervencion=NEW.cod_intervencion AND p.cod_residente=NEW.cod_residente)';
        if (DB::getDriverName() === 'pgsql') {
            DB::unprepared('CREATE FUNCTION validar_ejecucion_residente_plan() RETURNS trigger LANGUAGE plpgsql AS $$ BEGIN IF ('.$condicion.") THEN RAISE EXCEPTION 'La ejecución de cuidado no corresponde al residente del plan.' USING ERRCODE='23503'; END IF; RETURN NEW; END; $$;");
            DB::statement('CREATE TRIGGER trg_ejecucion_residente_plan BEFORE INSERT OR UPDATE OF cod_intervencion,cod_residente ON ejecuciones_cuidado FOR EACH ROW EXECUTE FUNCTION validar_ejecucion_residente_plan()');
        } else {
            foreach (['INSERT', 'UPDATE'] as $evento) {
                DB::unprepared('CREATE TRIGGER trg_ejecucion_residente_plan_'.strtolower($evento).' BEFORE '.$evento.' ON ejecuciones_cuidado FOR EACH ROW WHEN ('.$condicion.") BEGIN SELECT RAISE(ABORT, 'La ejecución de cuidado no corresponde al residente del plan.'); END");
            }
        }
    }
};
