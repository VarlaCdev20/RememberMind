<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Garantías físicas de bloques y soportes; PostgreSQL serializa por evaluación. */
final class GuardasReduccionConservacion
{
    public function instalar(): void
    {
        $h = ProyeccionReduccionExperta::HECHOS;
        $v = ProyeccionReduccionExperta::VINCULOS;
        $iad = array_values(ProyeccionReduccionExperta::IAD);
        $base = array_diff(Schema::getColumnListing($h), $iad);
        $igual = implode(' AND ', array_map(fn ($c) => DB::getDriverName() === 'pgsql' ? "NEW.{$c} IS NOT DISTINCT FROM OLD.{$c}" : "NEW.{$c} IS OLD.{$c}", $base));
        $vacia = implode(' AND ', array_map(fn ($c) => "OLD.{$c} IS NULL", $iad));
        $anexo = "($vacia) AND NEW.cod_evidencia_iadl_instantanea IS NOT NULL AND ($igual)";
        $duplicado = "EXISTS (SELECT 1 FROM {$h} h JOIN evidencias_criterio_evaluacion s ON s.cod_evidencia_evaluacion=h.cod_evidencia_evaluacion JOIN evaluacion_criterios c ON c.cod_evaluacion_criterio=s.cod_evaluacion_criterio WHERE c.version_conservacion='ORION-SNAP-2' AND h.cod_hecho_instantanea<>NEW.cod_hecho_instantanea AND h.cod_evidencia_evaluacion=NEW.cod_evidencia_evaluacion)";
        $contexto = "NOT EXISTS (SELECT 1 FROM evaluacion_criterios c WHERE c.cod_evaluacion_criterio=NEW.cod_evaluacion_criterio AND
            ((NEW.cod_evidencia_evaluacion IS NOT NULL AND EXISTS (SELECT 1 FROM evidencias_evaluacion e WHERE e.cod_evidencia_evaluacion=NEW.cod_evidencia_evaluacion AND e.cod_evaluacion_experta=c.cod_evaluacion_experta))
            OR (NEW.cod_hecho_instantanea IS NOT NULL AND c.version_conservacion='ORION-SNAP-2' AND EXISTS (SELECT 1 FROM {$h} h WHERE h.cod_hecho_instantanea=NEW.cod_hecho_instantanea AND h.cod_evaluacion_experta=c.cod_evaluacion_experta))))";
        $snap = "EXISTS (SELECT 1 FROM evaluacion_criterios c WHERE c.cod_evaluacion_criterio=NEW.cod_evaluacion_criterio AND c.version_conservacion='ORION-SNAP-2')";
        $incompleto = "NEW.orden_conservacion IS NULL OR NEW.orden_conservacion<0 OR NEW.rol_en_criterio<>'SOPORTE_EXPERIMENTAL' OR NEW.estado_participacion<>'PRUEBA_TECNICA' OR (NEW.cod_evidencia_evaluacion IS NOT NULL AND (SELECT count(*) FROM {$h} h JOIN evaluacion_criterios c ON c.cod_evaluacion_criterio=NEW.cod_evaluacion_criterio WHERE h.cod_evidencia_evaluacion=NEW.cod_evidencia_evaluacion AND h.cod_evaluacion_experta=c.cod_evaluacion_experta)<>1)";
        $propietario = '(SELECT c.cod_evaluacion_experta FROM evaluacion_criterios c WHERE c.cod_evaluacion_criterio=NEW.cod_evaluacion_criterio)';
        $hechoNuevo = "COALESCE(NEW.cod_hecho_instantanea,(SELECT h.cod_hecho_instantanea FROM {$h} h WHERE h.cod_evidencia_evaluacion=NEW.cod_evidencia_evaluacion AND h.cod_evaluacion_experta={$propietario}))";
        $repetido = "EXISTS (SELECT 1 FROM {$v} s WHERE s.cod_evaluacion_criterio=NEW.cod_evaluacion_criterio AND s.cod_evidencia_criterio<>NEW.cod_evidencia_criterio AND (s.orden_conservacion=NEW.orden_conservacion OR COALESCE(s.cod_hecho_instantanea,(SELECT h.cod_hecho_instantanea FROM {$h} h WHERE h.cod_evidencia_evaluacion=s.cod_evidencia_evaluacion AND h.cod_evaluacion_experta={$propietario}))={$hechoNuevo}))";
        if (DB::getDriverName() === 'pgsql') {
            DB::unprepared("CREATE FUNCTION red_hecho_guard() RETURNS trigger LANGUAGE plpgsql AS \$\$ BEGIN
                IF TG_OP='DELETE' THEN RAISE EXCEPTION 'red_hecho_inmutable'; END IF;
                IF current_setting('transaction_isolation') <> 'read committed' THEN RAISE EXCEPTION 'red_hecho_requiere_read_committed'; END IF;
                PERFORM 1 FROM evidencias_evaluacion WHERE cod_evidencia_evaluacion=NEW.cod_evidencia_evaluacion FOR UPDATE;
                PERFORM 1 FROM evaluaciones_expertas WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta FOR UPDATE;
                IF TG_OP='UPDATE' AND NOT ($anexo) THEN RAISE EXCEPTION 'red_hecho_anexo_unico'; END IF;
                IF NEW.cod_evidencia_evaluacion IS NOT NULL AND ($duplicado) THEN RAISE EXCEPTION 'red_soporte_ambiguo'; END IF;
                RETURN NEW; END \$\$");
            DB::unprepared("CREATE TRIGGER red_hecho_guard BEFORE INSERT OR UPDATE OR DELETE ON {$h} FOR EACH ROW EXECUTE FUNCTION red_hecho_guard()");
            DB::unprepared("CREATE FUNCTION red_vinculo_guard() RETURNS trigger LANGUAGE plpgsql AS \$\$ BEGIN
                IF current_setting('transaction_isolation') <> 'read committed' THEN RAISE EXCEPTION 'red_vinculo_requiere_read_committed'; END IF;
                PERFORM 1 FROM evaluacion_criterios WHERE cod_evaluacion_criterio=NEW.cod_evaluacion_criterio FOR UPDATE;
                PERFORM 1 FROM evidencias_evaluacion WHERE cod_evidencia_evaluacion=NEW.cod_evidencia_evaluacion FOR UPDATE;
                PERFORM 1 FROM evaluaciones_expertas e JOIN evaluacion_criterios c ON c.cod_evaluacion_experta=e.cod_evaluacion_experta WHERE c.cod_evaluacion_criterio=NEW.cod_evaluacion_criterio FOR UPDATE OF e;
                IF ($contexto) THEN RAISE EXCEPTION 'red_vinculo_contexto'; END IF;
                IF ($snap) AND (($incompleto) OR ($repetido)) THEN RAISE EXCEPTION 'red_vinculo_coleccion'; END IF;
                RETURN NEW; END \$\$");
            DB::unprepared("CREATE TRIGGER red_vinculo_guard BEFORE INSERT OR UPDATE ON {$v} FOR EACH ROW EXECUTE FUNCTION red_vinculo_guard()");
        } else {
            DB::unprepared("CREATE TRIGGER red_hecho_delete BEFORE DELETE ON {$h} BEGIN SELECT RAISE(ABORT,'red_hecho_inmutable'); END");
            DB::unprepared("CREATE TRIGGER red_hecho_update BEFORE UPDATE ON {$h} WHEN NOT ($anexo) BEGIN SELECT RAISE(ABORT,'red_hecho_anexo_unico'); END");
            foreach (['INSERT', 'UPDATE'] as $evento) {
                DB::unprepared('CREATE TRIGGER red_hecho_'.strtolower($evento)."_origen BEFORE {$evento} ON {$h} WHEN NEW.cod_evidencia_evaluacion IS NOT NULL AND ($duplicado) BEGIN SELECT RAISE(ABORT,'red_soporte_ambiguo'); END");
                DB::unprepared('CREATE TRIGGER red_vinculo_'.strtolower($evento)." BEFORE {$evento} ON {$v} WHEN ($contexto) OR (($snap) AND (($incompleto) OR ($repetido))) BEGIN SELECT RAISE(ABORT,'red_vinculo_contexto_coleccion'); END");
            }
        }
        $this->protegerContextoPadres();
    }

    /** Un cambio en el padre tampoco puede invalidar el contexto ya vinculado. */
    private function protegerContextoPadres(): void
    {
        $pg = DB::getDriverName() === 'pgsql';
        $iguales = fn (array $cs) => implode(' AND ', array_map(fn ($c) => $pg ? "NEW.{$c} IS NOT DISTINCT FROM OLD.{$c}" : "NEW.{$c} IS OLD.{$c}", $cs));
        $padres = [
            'criterio' => ['evaluacion_criterios', $iguales(['cod_evaluacion_experta', 'version_conservacion', 'tipo_registro']),
                'EXISTS (SELECT 1 FROM evidencias_criterio_evaluacion WHERE cod_evaluacion_criterio=OLD.cod_evaluacion_criterio)'],
            'evidencia' => ['evidencias_evaluacion', $iguales(['cod_evaluacion_experta']),
                'EXISTS (SELECT 1 FROM evidencias_criterio_evaluacion WHERE cod_evidencia_evaluacion=OLD.cod_evidencia_evaluacion) OR EXISTS (SELECT 1 FROM hechos_instantanea_experta WHERE cod_evidencia_evaluacion=OLD.cod_evidencia_evaluacion)'],
        ];
        foreach ($padres as $nombre => [$tabla, $igual, $referencias]) {
            if ($pg) {
                DB::unprepared("CREATE FUNCTION red_padre_{$nombre}_guard() RETURNS trigger LANGUAGE plpgsql AS \$\$ BEGIN
                    IF NOT ({$igual}) THEN
                        IF current_setting('transaction_isolation') <> 'read committed' THEN RAISE EXCEPTION 'red_contexto_requiere_read_committed'; END IF;
                        PERFORM 1 FROM evaluaciones_expertas WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta FOR UPDATE;
                        IF ({$referencias}) THEN RAISE EXCEPTION 'red_contexto_padre_inmutable'; END IF;
                    END IF; RETURN NEW; END \$\$");
                DB::unprepared("CREATE TRIGGER red_padre_{$nombre}_guard BEFORE UPDATE ON {$tabla} FOR EACH ROW EXECUTE FUNCTION red_padre_{$nombre}_guard()");
            } else {
                DB::unprepared("CREATE TRIGGER red_padre_{$nombre}_guard BEFORE UPDATE ON {$tabla} WHEN NOT ({$igual}) AND ({$referencias}) BEGIN SELECT RAISE(ABORT,'red_contexto_padre_inmutable'); END");
            }
        }
    }

    public function retirar(): void
    {
        foreach (['criterio' => 'evaluacion_criterios', 'evidencia' => 'evidencias_evaluacion'] as $nombre => $tabla) {
            DB::unprepared('DROP TRIGGER IF EXISTS red_padre_'.$nombre.'_guard'.(DB::getDriverName() === 'pgsql' ? ' ON '.$tabla : ''));
            if (DB::getDriverName() === 'pgsql') {
                DB::unprepared('DROP FUNCTION IF EXISTS red_padre_'.$nombre.'_guard()');
            }
        }
        if (DB::getDriverName() === 'pgsql') {
            DB::unprepared('DROP TRIGGER red_hecho_guard ON '.ProyeccionReduccionExperta::HECHOS);
            DB::unprepared('DROP TRIGGER red_vinculo_guard ON '.ProyeccionReduccionExperta::VINCULOS);
            DB::unprepared('DROP FUNCTION red_hecho_guard()');
            DB::unprepared('DROP FUNCTION red_vinculo_guard()');
        } else {
            foreach (['red_hecho_delete', 'red_hecho_update', 'red_hecho_insert_origen', 'red_hecho_update_origen', 'red_vinculo_insert', 'red_vinculo_update'] as $n) {
                DB::unprepared("DROP TRIGGER IF EXISTS {$n}");
            }
        }
    }
}
