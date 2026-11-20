<?php

use Illuminate\Support\Facades\DB;

/** Integridad de las tablas físicas; también se aplica al SQL directo. */
final class GuardasContextosEventos20261120
{
    private static function reglas(): array
    {
        $residente = fn ($fila) => "(SELECT cod_residente FROM admisiones WHERE cod_admision=$fila.cod_admision)";
        $pregunta = fn ($fila) => "COALESCE($fila.cod_pregunta_libre,(SELECT cod_pregunta FROM opciones_pregunta WHERE cod_opcion=$fila.cod_opcion))";
        $orden = fn ($fila) => "COALESCE($fila.cod_prescripcion_directa,(SELECT cod_prescripcion FROM horarios_prescripcion WHERE cod_horario_prescripcion=$fila.cod_horario_prescripcion))";
        $paciente = fn ($fila) => '(SELECT a.cod_residente FROM prescripciones p JOIN atenciones a USING(cod_atencion) WHERE p.cod_prescripcion='.$orden($fila).')';
        $activo = "IN ('ACTIVA','ACTIVO')";
        // Versionar la fila común también hace que PostgreSQL rechace una instantánea
        // desactualizada bajo REPEATABLE READ. Solo bloquearla no cubre ese caso.
        $bloquearCama = 'UPDATE admisiones SET estado=estado WHERE cod_admision=NEW.cod_admision; '
            .'UPDATE residentes SET estado=estado WHERE cod_residente='.$residente('NEW').';';
        $bloquearPregunta = 'UPDATE opciones_pregunta SET estado=estado WHERE cod_opcion=NEW.cod_opcion; '
            .'UPDATE preguntas_instrumento SET estado=estado WHERE cod_pregunta='.$pregunta('NEW').'; '
            .'PERFORM 1 FROM aplicaciones_instrumento WHERE cod_aplicacion=NEW.cod_aplicacion FOR SHARE;';
        $bloquearDosis = 'UPDATE horarios_prescripcion SET estado=estado WHERE cod_horario_prescripcion=NEW.cod_horario_prescripcion; '
            .'PERFORM 1 FROM prescripciones WHERE cod_prescripcion='.$orden('NEW').' FOR SHARE;';

        return [
            'estancia_activa' => ['estancias_cama', 'INSERT OR UPDATE', "NEW.estado $activo AND EXISTS(SELECT 1 FROM estancias_cama e JOIN admisiones a USING(cod_admision) WHERE e.estado $activo AND e.cod_ocupacion<>NEW.cod_ocupacion AND a.cod_residente=".$residente('NEW').')', $bloquearCama],
            'estancia_historia' => ['estancias_cama', 'UPDATE', $residente('OLD').'<>'.$residente('NEW'), ''],
            'captura' => ['capturas_instrumento', 'INSERT OR UPDATE',
                'EXISTS(SELECT 1 FROM respuestas_instrumento r WHERE r.cod_aplicacion=NEW.cod_aplicacion AND r.cod_pregunta='.$pregunta('NEW').' AND r.cod_respuesta<>NEW.cod_respuesta) OR '
                .'(SELECT cod_instrumento FROM aplicaciones_instrumento WHERE cod_aplicacion=NEW.cod_aplicacion)<>(SELECT cod_instrumento FROM preguntas_instrumento WHERE cod_pregunta='.$pregunta('NEW').')', $bloquearPregunta],
            'dosis' => ['dosis_registradas', 'INSERT OR UPDATE', 'FALSE', $bloquearDosis],
            'dosis_historia' => ['dosis_registradas', 'UPDATE', $paciente('OLD').'<>'.$paciente('NEW'),
                'PERFORM 1 FROM prescripciones WHERE cod_prescripcion IN ('.$orden('OLD').','.$orden('NEW').') ORDER BY cod_prescripcion FOR SHARE;'],
            'admision' => ['admisiones', 'UPDATE OF cod_residente', 'OLD.cod_residente<>NEW.cod_residente AND EXISTS(SELECT 1 FROM estancias_cama WHERE cod_admision=OLD.cod_admision)', ''],
            'opcion' => ['opciones_pregunta', 'UPDATE OF cod_pregunta', 'OLD.cod_pregunta<>NEW.cod_pregunta AND EXISTS(SELECT 1 FROM capturas_instrumento WHERE cod_opcion=OLD.cod_opcion)', ''],
            'horario' => ['horarios_prescripcion', 'UPDATE OF cod_prescripcion', 'OLD.cod_prescripcion<>NEW.cod_prescripcion AND EXISTS(SELECT 1 FROM dosis_registradas WHERE cod_horario_prescripcion=OLD.cod_horario_prescripcion)', ''],
        ];
    }

    public static function instalar(bool $pg): void
    {
        DB::statement('CREATE INDEX estancias_admision_estado ON estancias_cama(cod_admision,estado)');
        DB::statement('CREATE INDEX capturas_aplicacion ON capturas_instrumento(cod_aplicacion)');
        DB::statement('CREATE INDEX capturas_opcion ON capturas_instrumento(cod_opcion)');
        $fecha = $pg ? '(fecha_hora_programada::date)' : 'date(fecha_hora_programada)';
        DB::statement("CREATE UNIQUE INDEX dosis_horario_fecha ON dosis_registradas(cod_horario_prescripcion,$fecha) WHERE cod_horario_prescripcion IS NOT NULL AND fecha_hora_programada IS NOT NULL");
        foreach (self::reglas() as $nombre => [$tabla,$eventos,$condicion,$bloqueo]) {
            $nombre = 'evt20_'.$nombre;
            if ($pg) {
                DB::unprepared("CREATE FUNCTION $nombre() RETURNS trigger LANGUAGE plpgsql AS \$\$ BEGIN $bloqueo IF $condicion THEN RAISE EXCEPTION 'Contexto histórico o unicidad incompatible ($nombre)' USING ERRCODE='23514'; END IF; RETURN NEW; END \$\$");
                DB::statement("CREATE TRIGGER $nombre BEFORE $eventos ON $tabla FOR EACH ROW EXECUTE FUNCTION $nombre()");
            } else {
                foreach (explode(' OR ', $eventos) as $evento) {
                    $sufijo = strtolower(explode(' ', $evento)[0]);
                    DB::unprepared("CREATE TRIGGER {$nombre}_$sufijo BEFORE $evento ON $tabla WHEN $condicion BEGIN SELECT RAISE(ABORT,'Contexto histórico o unicidad incompatible ($nombre)'); END");
                }
            }
        }
    }

    public static function retirar(bool $pg): void
    {
        foreach (self::reglas() as $nombre => [$tabla,$eventos]) {
            $nombre = 'evt20_'.$nombre;
            if ($pg) {
                DB::statement("DROP TRIGGER $nombre ON $tabla");
                DB::statement("DROP FUNCTION $nombre()");
            } else {
                foreach (explode(' OR ', $eventos) as $evento) {
                    DB::statement('DROP TRIGGER '.$nombre.'_'.strtolower(explode(' ', $evento)[0]));
                }
            }
        }
        foreach (['estancias_admision_estado', 'capturas_aplicacion', 'capturas_opcion', 'dosis_horario_fecha'] as $indice) {
            DB::statement("DROP INDEX $indice");
        }
    }
}
