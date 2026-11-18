<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require_once __DIR__.'/support/RepartoValoraciones20261118.php';

return new class extends Migration
{
    public function up(): void
    {
        $this->transicion()->reconstruir(derivar: true);
    }

    public function down(): void
    {
        $this->transicion()->reconstruir(derivar: false);
    }

    private function transicion(): RepartoValoraciones20261118
    {
        return new class extends RepartoValoraciones20261118
        {
            protected const DESTINO = 'valoraciones_profesionales';

            protected const ESPECIALIDADES = ['valoraciones_funcionales', 'valoraciones_nutricionales'];

            protected const IDENTIDADES_TIPADAS = false;

            public function reconstruir(bool $derivar): void
            {
                DB::transaction(function () use ($derivar): void {
                    $pgsql = DB::getDriverName() === 'pgsql';
                    if ($pgsql) {
                        DB::statement("SET LOCAL lock_timeout = '5s'");
                        DB::statement('LOCK TABLE atenciones, valoraciones_profesionales IN ACCESS EXCLUSIVE MODE');
                    } elseif (DB::getDriverName() !== 'sqlite') {
                        throw new RuntimeException('La transición requiere PostgreSQL o SQLite.');
                    }

                    $anterior = $this->contrato(! $derivar);
                    $nuevo = $this->contrato($derivar);
                    $campos = Schema::getColumnListing(self::DESTINO);
                    $esperados = array_keys($this->columnasFusion($anterior));
                    sort($campos);
                    sort($esperados);
                    if ($campos !== $esperados || $this->referencias([self::DESTINO]) !== []) {
                        throw new LogicException('El esquema de valoraciones difiere del autorizado.');
                    }

                    if ($derivar && DB::table(self::DESTINO.' as v')->leftJoin('atenciones as a', 'a.cod_atencion', '=', 'v.cod_atencion')
                        ->where(fn ($q) => $q->whereNull('a.cod_residente')->orWhereNull('v.cod_residente')
                            ->orWhereColumn('a.cod_residente', '<>', 'v.cod_residente'))->exists()) {
                        throw new RuntimeException('Una valoración tiene un residente incompatible con su atención.');
                    }

                    if (! $derivar) {
                        $this->retirarGuardas($pgsql);
                    }
                    $copia = self::DESTINO.'_antes_contexto';
                    Schema::rename(self::DESTINO, $copia);
                    $this->crearFusion($nuevo);
                    $this->copiarYVerificar($copia, $derivar, array_keys($this->columnasFusion($nuevo)));
                    Schema::drop($copia);
                    $this->indexarFusion($nuevo);
                    if ($derivar) {
                        DB::statement('CREATE INDEX valoraciones_profesionales_atencion_fecha_i ON valoraciones_profesionales (tipo_evento, cod_atencion, fecha_hora)');
                        $this->instalarGuardas($pgsql);
                    } else {
                        DB::statement('CREATE INDEX valoraciones_profesionales_residente_fecha_i ON valoraciones_profesionales (tipo_evento, cod_residente, fecha_hora)');
                    }
                    if (! $pgsql && DB::select('PRAGMA foreign_key_check') !== []) {
                        throw new RuntimeException('Una referencia SQLite quedó inválida.');
                    }
                });
            }

            private function contrato(bool $derivar): array
            {
                $fuentes = $this->fuentes();
                if ($derivar) {
                    foreach ($fuentes as &$tipo) {
                        unset($tipo['columns']['cod_residente']);
                        $tipo['foreign'] = array_filter($tipo['foreign'], fn ($fk) => ! in_array('cod_residente', $fk[1], true));
                    }
                    unset($tipo);
                }

                return $fuentes;
            }

            private function copiarYVerificar(string $copia, bool $derivar, array $campos): void
            {
                $tabla = self::DESTINO;
                $destino = implode(', ', $campos);
                $proyeccion = implode(', ', array_map(fn ($campo) => $campo === 'cod_residente' ? 'a.cod_residente' : 'v.'.$campo, $campos));
                $fuente = "SELECT $proyeccion FROM $copia v";
                if (! $derivar) {
                    $fuente .= ' JOIN atenciones a ON a.cod_atencion = v.cod_atencion';
                }
                $persistido = "SELECT $destino FROM $tabla";

                DB::statement("INSERT INTO $tabla ($destino) $fuente");
                if (DB::table($copia)->count() !== DB::table($tabla)->count()
                    || DB::scalar("SELECT EXISTS ($fuente EXCEPT $persistido)")
                    || DB::scalar("SELECT EXISTS ($persistido EXCEPT $fuente)")) {
                    throw new RuntimeException('La transición no recupera todas las valoraciones originales.');
                }
            }

            private function instalarGuardas(bool $pgsql): void
            {
                $cambioAtencion = 'OLD.cod_residente <> NEW.cod_residente';
                $hayValoraciones = '(EXISTS (SELECT 1 FROM valoraciones_profesionales WHERE cod_atencion = OLD.cod_atencion))';
                $cambioResidente = '(SELECT cod_residente FROM atenciones WHERE cod_atencion = OLD.cod_atencion)'
                    .' <> (SELECT cod_residente FROM atenciones WHERE cod_atencion = NEW.cod_atencion)';
                if ($pgsql) {
                    DB::unprepared(<<<'SQL'
                        CREATE FUNCTION vpr_contexto_guardar() RETURNS trigger LANGUAGE plpgsql AS $$
                        BEGIN
                            IF TG_OP = 'INSERT' THEN
                                PERFORM 1 FROM atenciones WHERE cod_atencion = NEW.cod_atencion FOR SHARE;
                            ELSE
                                PERFORM 1 FROM atenciones WHERE cod_atencion IN (OLD.cod_atencion, NEW.cod_atencion)
                                    ORDER BY cod_atencion FOR SHARE;
                                IF (SELECT cod_residente FROM atenciones WHERE cod_atencion = OLD.cod_atencion)
                                    <> (SELECT cod_residente FROM atenciones WHERE cod_atencion = NEW.cod_atencion) THEN
                                    RAISE EXCEPTION 'La valoración debe conservar su residente' USING ERRCODE = '23514';
                                END IF;
                            END IF;
                            RETURN NEW;
                        END;
                        $$;
                        SQL);
                    DB::unprepared(<<<'SQL'
                        CREATE FUNCTION vpr_contexto_atencion() RETURNS trigger LANGUAGE plpgsql AS $$
                        BEGIN
                            -- Una instantánea anterior puede ocultar un hijo recién confirmado.
                            IF OLD.cod_residente <> NEW.cod_residente AND (
                                current_setting('transaction_isolation') NOT IN ('read committed', 'read uncommitted')
                                OR EXISTS (SELECT 1 FROM valoraciones_profesionales WHERE cod_atencion = OLD.cod_atencion)
                            ) THEN
                                RAISE EXCEPTION 'La atención debe conservar el residente de sus valoraciones' USING ERRCODE = '23514';
                            END IF;
                            RETURN NEW;
                        END;
                        $$;
                        SQL);
                    DB::statement('CREATE TRIGGER vpr_contexto_guardar BEFORE INSERT OR UPDATE OF cod_atencion ON valoraciones_profesionales FOR EACH ROW EXECUTE FUNCTION vpr_contexto_guardar()');
                    DB::statement('CREATE TRIGGER vpr_contexto_atencion BEFORE UPDATE OF cod_residente ON atenciones FOR EACH ROW EXECUTE FUNCTION vpr_contexto_atencion()');

                    return;
                }
                DB::unprepared("CREATE TRIGGER vpr_contexto_guardar BEFORE UPDATE OF cod_atencion ON valoraciones_profesionales WHEN $cambioResidente BEGIN SELECT RAISE(ABORT, 'La valoración debe conservar su residente'); END");
                DB::unprepared("CREATE TRIGGER vpr_contexto_atencion BEFORE UPDATE OF cod_residente ON atenciones WHEN $cambioAtencion AND $hayValoraciones BEGIN SELECT RAISE(ABORT, 'La atención debe conservar el residente de sus valoraciones'); END");
            }

            private function retirarGuardas(bool $pgsql): void
            {
                foreach (['vpr_contexto_guardar' => self::DESTINO, 'vpr_contexto_atencion' => 'atenciones'] as $nombre => $tabla) {
                    DB::statement('DROP TRIGGER '.$nombre.($pgsql ? ' ON '.$tabla : ''));
                    if ($pgsql) {
                        DB::statement('DROP FUNCTION '.$nombre.'()');
                    }
                }
            }
        };
    }
};
