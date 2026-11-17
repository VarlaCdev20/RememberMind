<?php

use Illuminate\Support\Facades\DB;

/** Protecciones del acto y del aviso conservadas durante la unión laboral autorizada. */
trait ProteccionHistoriaLaboral20261116
{
    protected function declaracionesOriginales(string $tabla, array $declaraciones): array
    {
        if (DB::getDriverName() !== 'sqlite' || $tabla !== 'eventos_asignacion_personal') {
            return $declaraciones;
        }
        // La extensión histórica retira esta columna y sus dos CHECK juntos.
        $aviso = array_filter($declaraciones, fn ($s) => str_starts_with($s, 'CONSTRAINT eap_aviso_'));
        $cuenta = array_filter($declaraciones, fn ($s) => str_contains($s, 'FOREIGN KEY (cod_usuario_afectado_aviso)'));
        $declaraciones = array_diff($declaraciones, $aviso, $cuenta);
        $cuenta = array_map(fn ($s) => str_replace('FOREIGN KEY (cod_usuario_afectado_aviso) ', '', $s), $cuenta);

        return array_map(fn ($s) => match (true) {
            str_starts_with($s, 'categoria_fallo_aviso ') => $s.' '.implode(' ', $aviso),
            str_starts_with($s, 'cod_usuario_afectado_aviso ') => $s.' '.implode(' ', $cuenta),
            default => $s,
        }, $declaraciones);
    }

    protected function protegerFusion(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP FUNCTION ger_secuencia_eventos_asignacion_personal()');
            DB::statement('DROP FUNCTION ger_historia_asignacion_con_aviso()');
        }
        $this->instalarProteccionesLaborales('historial_laboral_personal', true);
    }

    protected function protegerOriginales(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP FUNCTION hlp_secuencia()');
            DB::statement('DROP FUNCTION hlp_historia()');
        }
        $this->instalarProteccionesLaborales('eventos_asignacion_personal', false);
    }

    private function instalarProteccionesLaborales(string $tabla, bool $fusion): void
    {
        $campos = ['cod_evento_asignacion', 'cod_asignacion_personal', 'numero_evento', 'cod_evento_anterior',
            'tipo_evento', 'estado_anterior', 'estado_nuevo', 'fecha_hora_efecto', 'fecha_hora_registro',
            'cod_usuario_registro', 'motivo', 'cod_usuario_afectado_aviso'];
        if ($fusion) {
            // Los alias generados no son campos editables. Su identidad física sí se protege.
            $campos = array_diff($campos, ['cod_evento_asignacion']);
            $campos = [...$campos, 'cod_historial_laboral', 'clase_acto', 'cod_personal'];
        }
        $meta = ['estado_aviso_planificacion', 'numero_intentos_aviso', 'fecha_hora_ultimo_intento_aviso',
            'fecha_hora_disponible_aviso', 'categoria_fallo_aviso'];
        $distinto = DB::getDriverName() === 'pgsql' ? 'IS DISTINCT FROM' : 'IS NOT';
        $comparar = fn (array $columnas) => implode(' OR ', array_map(fn ($c) => "OLD.$c $distinto NEW.$c", $columnas));
        $cambio = $comparar($campos);
        $cambioMeta = $comparar($meta);
        $condicion = "($cambio) OR ((OLD.estado_aviso_planificacion IS NULL OR OLD.estado_aviso_planificacion='DISPONIBLE')
            AND ($cambioMeta)) OR (OLD.estado_aviso_planificacion IS NOT NULL AND NEW.estado_aviso_planificacion IS NULL)
            OR NEW.numero_intentos_aviso<OLD.numero_intentos_aviso";
        if ($fusion) {
            $condicion = "OLD.clase_acto='LABORAL' OR ($condicion)";
        }
        $asignacion = $fusion ? "NEW.clase_acto='ASIGNACION' AND " : '';
        $padre = "SELECT 1 FROM $tabla p WHERE p.cod_asignacion_personal=NEW.cod_asignacion_personal
            AND p.cod_evento_asignacion=NEW.cod_evento_anterior AND p.numero_evento=NEW.numero_evento-1";
        if (DB::getDriverName() === 'pgsql') {
            $secuencia = $fusion ? 'hlp_secuencia' : 'ger_secuencia_eventos_asignacion_personal';
            $historia = $fusion ? 'hlp_historia' : 'ger_historia_asignacion_con_aviso';
            DB::unprepared("CREATE OR REPLACE FUNCTION $secuencia() RETURNS trigger LANGUAGE plpgsql AS \$\$
                BEGIN IF $asignacion NEW.numero_evento>1 AND NOT EXISTS ($padre)
                THEN RAISE EXCEPTION 'La secuencia histórica requiere el anterior inmediato'; END IF; RETURN NEW; END; \$\$");
            DB::unprepared("CREATE OR REPLACE FUNCTION $historia() RETURNS trigger LANGUAGE plpgsql AS \$\$
                BEGIN IF TG_OP='DELETE' THEN RAISE EXCEPTION 'La historia nominal es inmutable'; END IF;
                IF $condicion THEN RAISE EXCEPTION 'El acto original es inmutable'; END IF; RETURN NEW; END; \$\$");
            DB::statement("CREATE TRIGGER ger_secuencia BEFORE INSERT ON $tabla FOR EACH ROW EXECUTE FUNCTION $secuencia()");
            DB::statement("CREATE TRIGGER ger_historia_inmutable BEFORE UPDATE OR DELETE ON $tabla FOR EACH ROW EXECUTE FUNCTION $historia()");
        } else {
            $prefijo = $fusion ? 'hlp' : 'ger_eventos_asignacion_personal';
            DB::unprepared("CREATE TRIGGER {$prefijo}_secuencia BEFORE INSERT ON $tabla
                WHEN $asignacion NEW.numero_evento>1 AND NOT EXISTS ($padre)
                BEGIN SELECT RAISE(ABORT,'La secuencia histórica requiere el anterior inmediato'); END");
            DB::unprepared("CREATE TRIGGER {$prefijo}_update BEFORE UPDATE ON $tabla WHEN $condicion
                BEGIN SELECT RAISE(ABORT,'El acto original es inmutable'); END");
            DB::unprepared("CREATE TRIGGER {$prefijo}_delete BEFORE DELETE ON $tabla
                BEGIN SELECT RAISE(ABORT,'La historia nominal es inmutable'); END");
        }
    }
}
