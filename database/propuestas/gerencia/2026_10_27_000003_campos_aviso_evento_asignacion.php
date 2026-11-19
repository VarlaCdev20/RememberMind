<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Seis campos sin tablas nuevas, con el alcance compartido adoptado el 27/10/2026; instalación explícita. */
return new class extends Migration
{
    private const COLUMNAS = ['cod_usuario_afectado_aviso', 'estado_aviso_planificacion', 'numero_intentos_aviso',
        'fecha_hora_ultimo_intento_aviso', 'fecha_hora_disponible_aviso', 'categoria_fallo_aviso'];

    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['sqlite', 'pgsql'], true)) throw new LogicException('Motor no admitido.');
        DB::statement('ALTER TABLE eventos_asignacion_personal ADD COLUMN cod_usuario_afectado_aviso VARCHAR(20) NULL REFERENCES usuarios(cod_usuario) ON DELETE RESTRICT');
        DB::statement('ALTER TABLE eventos_asignacion_personal ADD COLUMN estado_aviso_planificacion VARCHAR(16) NULL');
        DB::statement('ALTER TABLE eventos_asignacion_personal ADD COLUMN numero_intentos_aviso INTEGER NULL');
        DB::statement('ALTER TABLE eventos_asignacion_personal ADD COLUMN fecha_hora_ultimo_intento_aviso TIMESTAMP NULL');
        DB::statement('ALTER TABLE eventos_asignacion_personal ADD COLUMN fecha_hora_disponible_aviso TIMESTAMP NULL');
        // CHECK nominal en la última columna: portable sin reconstruir la tabla SQLite.
        DB::statement("ALTER TABLE eventos_asignacion_personal ADD COLUMN categoria_fallo_aviso VARCHAR(40) NULL
            CONSTRAINT eap_aviso_coherencia CHECK(
                (estado_aviso_planificacion IS NULL AND cod_usuario_afectado_aviso IS NULL AND numero_intentos_aviso IS NULL
                    AND fecha_hora_ultimo_intento_aviso IS NULL AND fecha_hora_disponible_aviso IS NULL AND categoria_fallo_aviso IS NULL)
                OR (estado_aviso_planificacion IS NOT NULL AND numero_intentos_aviso IS NOT NULL AND (
                    (estado_aviso_planificacion='PENDIENTE' AND numero_intentos_aviso=0 AND fecha_hora_ultimo_intento_aviso IS NULL AND fecha_hora_disponible_aviso IS NULL AND categoria_fallo_aviso IS NULL)
                    OR (estado_aviso_planificacion='DISPONIBLE' AND numero_intentos_aviso>0 AND fecha_hora_ultimo_intento_aviso IS NOT NULL AND fecha_hora_disponible_aviso IS NOT NULL AND fecha_hora_disponible_aviso=fecha_hora_ultimo_intento_aviso AND categoria_fallo_aviso IS NULL)
                    OR (estado_aviso_planificacion='FALLIDO' AND numero_intentos_aviso>0 AND fecha_hora_ultimo_intento_aviso IS NOT NULL AND fecha_hora_disponible_aviso IS NULL AND categoria_fallo_aviso IS NOT NULL AND categoria_fallo_aviso='FALLO_DISPONIBILIZACION')
                )))
            CONSTRAINT eap_aviso_fecha CHECK(fecha_hora_ultimo_intento_aviso IS NULL OR fecha_hora_ultimo_intento_aviso>=fecha_hora_registro)");
        DB::statement('CREATE INDEX eap_aviso_afectado_idx ON eventos_asignacion_personal(cod_usuario_afectado_aviso,fecha_hora_registro)');
        DB::statement('CREATE INDEX eap_aviso_estado_idx ON eventos_asignacion_personal(estado_aviso_planificacion,fecha_hora_registro)');
        $this->proteger(true);
    }

    private function proteger(bool $metadatos): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP TRIGGER ger_historia_inmutable ON eventos_asignacion_personal');
            if (! $metadatos) {
                DB::statement('CREATE TRIGGER ger_historia_inmutable BEFORE UPDATE OR DELETE ON eventos_asignacion_personal FOR EACH ROW EXECUTE FUNCTION ger_historia_inmutable()');
                DB::statement('DROP FUNCTION ger_historia_asignacion_con_aviso()');
                return;
            }
        } else {
            DB::statement('DROP TRIGGER ger_eventos_asignacion_personal_update');
            if (! $metadatos) {
                DB::unprepared("CREATE TRIGGER ger_eventos_asignacion_personal_update BEFORE UPDATE ON eventos_asignacion_personal BEGIN SELECT RAISE(ABORT,'La historia nominal es inmutable'); END");
                return;
            }
        }
        $originales = ['cod_evento_asignacion', 'cod_asignacion_personal', 'numero_evento', 'cod_evento_anterior', 'tipo_evento', 'estado_anterior',
            'estado_nuevo', 'fecha_hora_efecto', 'fecha_hora_registro', 'cod_usuario_registro', 'motivo', 'cod_usuario_afectado_aviso'];
        $distinto = DB::getDriverName() === 'pgsql' ? 'IS DISTINCT FROM' : 'IS NOT';
        $cambio = implode(' OR ', array_map(fn ($c) => "OLD.{$c} {$distinto} NEW.{$c}", $originales));
        $cambioMeta = implode(' OR ', array_map(fn ($c) => "OLD.{$c} {$distinto} NEW.{$c}", array_slice(self::COLUMNAS, 1)));
        $condicion = "({$cambio}) OR ((OLD.estado_aviso_planificacion IS NULL OR OLD.estado_aviso_planificacion='DISPONIBLE') AND ({$cambioMeta})) OR (OLD.estado_aviso_planificacion IS NOT NULL AND NEW.estado_aviso_planificacion IS NULL) OR NEW.numero_intentos_aviso<OLD.numero_intentos_aviso";
        if (DB::getDriverName() === 'pgsql') {
            DB::unprepared("CREATE FUNCTION ger_historia_asignacion_con_aviso() RETURNS trigger LANGUAGE plpgsql AS \$\$ BEGIN IF TG_OP='DELETE' THEN RAISE EXCEPTION 'La historia nominal es inmutable'; END IF; IF {$condicion} THEN RAISE EXCEPTION 'El acto original es inmutable'; END IF; RETURN NEW; END; \$\$");
            DB::statement('CREATE TRIGGER ger_historia_inmutable BEFORE UPDATE OR DELETE ON eventos_asignacion_personal FOR EACH ROW EXECUTE FUNCTION ger_historia_asignacion_con_aviso()');
        } else {
            DB::unprepared("CREATE TRIGGER ger_eventos_asignacion_personal_update BEFORE UPDATE ON eventos_asignacion_personal WHEN {$condicion} BEGIN SELECT RAISE(ABORT,'El acto original es inmutable'); END");
        }
    }

    public function down(): void
    {
        DB::transaction(function () {
            if (DB::getDriverName() === 'pgsql') {
                DB::statement("SET LOCAL lock_timeout = '10s'");
                DB::statement('LOCK TABLE eventos_asignacion_personal IN ACCESS EXCLUSIVE MODE');
            } else {
                // Reserva la escritura antes del chequeo sin afectar una fila.
                DB::statement('UPDATE eventos_asignacion_personal SET estado_aviso_planificacion=estado_aviso_planificacion WHERE 1=0');
            }
            if (DB::table('eventos_asignacion_personal')->where(function ($q) {
                foreach (self::COLUMNAS as $columna) $q->orWhereNotNull($columna);
            })->exists()) throw new LogicException('Los avisos conservados no se eliminan por rollback.');
            $this->proteger(false);
            DB::statement('DROP INDEX eap_aviso_estado_idx');
            DB::statement('DROP INDEX eap_aviso_afectado_idx');
            foreach (array_reverse(self::COLUMNAS) as $columna) DB::statement('ALTER TABLE eventos_asignacion_personal DROP COLUMN '.$columna);
        });
    }
};
