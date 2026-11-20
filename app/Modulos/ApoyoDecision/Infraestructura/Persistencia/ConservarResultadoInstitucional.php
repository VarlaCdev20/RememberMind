<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Persistencia;

use App\Compartido\Aplicacion\Contratos\CodigosEntidad;
use App\Compartido\Infraestructura\Persistencia\CatalogoCodigos;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\ResultadoInstitucionalConservado as R;
use App\Modulos\ApoyoDecision\Aplicacion\Servicios\ContratoFuentesPetersen;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\BloquesExpertos;
use DateTimeImmutable;
use DomainException;
use Illuminate\Support\Facades\DB;
use MotorExperto\conservacion\CodecInstantanea as Codec;
use MotorExperto\conservacion\InstantaneaEvaluacionInstitucional;

/** Escribe la proyección autorizada de resultado. Recibe exclusivamente preparación backend verificada. */
final class ConservarResultadoInstitucional
{
    private array $origenes = [];
    private string $residente;
    private function insertar(string $tabla, array $fila): string
    {
        $id = app(CodigosEntidad::class)->siguiente($tabla);
        BloquesExpertos::insertar($tabla, [CatalogoCodigos::clave($tabla) => $id] + $fila);
        return $id;
    }

    public function guardar(InstantaneaEvaluacionInstitucional $dto, string $personal, string $solicitud): string
    {
        if (DB::transactionLevel() === 0) { throw new DomainException('El guardado requiere una transacción institucional.'); }
        InstantaneaEvaluacionInstitucional::verificarIntegridad($dto->contenido, $dto->huella);
        $i = $dto->contenido['instrumental']['contenido'];
        $p = $dto->contenido['petersen']['contenido'];
        $this->residente = $i['contexto']['residente'];
        $this->origenes = [];
        $apps = array_column($i['capturas']['capturas'], 'cod_aplicacion');
        $respuestas = array_merge(...array_map(fn ($a) => array_column($a['respuestas_originales'], 'cod_respuesta'), $i['capturas']['capturas']));
        $juicios = array_map(fn ($v) => $v['origen']['registro'], $p['valoraciones']);
        foreach (\App\Compartido\Infraestructura\Persistencia\ColumnasRelacionadas::consulta(DB::connection(), 'aplicaciones_instrumento')->whereIn('cod_aplicacion', $apps)->pluck('cod_residente', 'cod_aplicacion') as $pk => $r) { $this->origenes['aplicaciones_instrumento:'.$pk] = $r; }
        foreach (\App\Compartido\Infraestructura\Persistencia\ColumnasRelacionadas::consulta(DB::connection(), 'valoraciones_petersen_originales')->whereIn('cod_valoracion_petersen', $juicios)->pluck('cod_residente', 'cod_valoracion_petersen') as $pk => $r) { $this->origenes['valoraciones_petersen_originales:'.$pk] = $r; }
        foreach (DB::table('respuestas_instrumento as r')->joinSub(\App\Compartido\Infraestructura\Persistencia\ColumnasRelacionadas::consulta(DB::connection(), 'aplicaciones_instrumento'), 'a', 'a.cod_aplicacion', '=', 'r.cod_aplicacion')->whereIn('r.cod_respuesta', $respuestas)->pluck('a.cod_residente', 'r.cod_respuesta') as $pk => $r) { $this->origenes['respuestas_instrumento:'.$pk] = $r; }
        $s = $p['salida'];
        $versionPetersen = $p['gobierno']['conocimiento']['version'] ?? null;
        if ($versionPetersen === null) { throw new DomainException('No hay una versión Petersen identificada para conservar el resultado.'); }
        $fecha = now()->format('Y-m-d H:i:s');
        $cabecera = ['cod_residente' => $i['contexto']['residente'], 'cod_version_modelo' => $i['contexto']['version'],
            'cod_version_petersen' => $versionPetersen, 'cod_usuario_ejecucion' => $i['contexto']['usuario_ejecucion'],
            'cod_personal_solicitante' => $personal, 'origen_activacion' => 'INSTITUCIONAL', 'estado_ejecucion' => 'FINALIZADA',
            'fecha_hora_inicio' => $fecha, 'fecha_hora_fin' => $fecha, 'id_evaluacion_dominio' => 'INST:'.$solicitud,
            'version_conservacion' => R::VERSION, 'institucional_resultado' => $s['estado'], 'institucional_patron' => $s['patron'],
            'institucional_explicacion' => $this->narrativa($i, $p), 'institucional_ejecutor' => $dto->contenido['ejecutor']['huella_src'],
            'huella_memoria_cognicion' => str_repeat('0', 64)] + Codec::fecha('fecha_corte', Codec::leerFecha('corte', (object) $i['contexto']['fecha_corte']));
        foreach (R::DOMINIOS as $codigo => $campo) { $cabecera['institucional_'.$campo] = $s['dominios'][$codigo]['estado']; }
        foreach (['C01', 'C02', 'C03', 'C04', 'C05'] as $codigo) { $cabecera['institucional_'.strtolower($codigo)] = $s['requisitos'][$codigo]['estado']; }
        $id = $this->insertar('evaluaciones_expertas', $cabecera);
        $orden = 0;
        $hechos = [];
        foreach ($i['capturas']['capturas'] as $a) {
            foreach (['estado_original', 'codigo_instrumento', 'nombre_instrumento', 'version_instrumento', 'edad_en_aplicacion'] as $campo) {
                $hechos[] = $this->hecho($id, 'APLICACION_INSTRUMENTAL', 'cod_aplicacion', $a['cod_aplicacion'], $a['cod_personal'], $a['fecha_hora'], $campo, $a[$campo], $orden++);
            }
            foreach (['puntaje_total', 'puntaje_maximo', 'interpretacion', 'observacion'] as $campo) {
                $hechos[] = $this->hecho($id, 'APLICACION_INSTRUMENTAL', 'cod_aplicacion', $a['cod_aplicacion'], $a['cod_personal'], $a['fecha_hora'], $campo, $a['aplicacion_actual'][$campo], $orden++);
            }
            foreach ($a['respuestas_originales'] as $r) {
                foreach (['valor_numero', 'valor_texto', 'valor_logico', 'puntaje', 'observacion', 'codigo_pregunta'] as $campo) {
                    if (array_key_exists($campo, $r)) {
                        $hechos[] = $this->hecho($id, 'RESPUESTA_INSTRUMENTAL', 'cod_respuesta', $r['cod_respuesta'], $a['cod_personal'], $a['fecha_hora'], $campo, $r[$campo], $orden++);
                    }
                }
            }
        }
        // Se conserva el juicio original y su explicación; C02 permanece exclusivamente derivado.
        foreach ($p['valoraciones'] as $v) {
            $o = $v['origen'];
            foreach (['estado' => $v['estado'], 'elemento' => $v['elemento'], 'justificacion' => $v['justificacion'], ...$v['comprobaciones']] as $campo => $valor) {
                $hechos[] = $this->hecho($id, 'JUICIO_PETERSEN', 'cod_valoracion_petersen', $o['registro'], $o['autor'], $o['fecha'], $campo, $valor, $orden++);
            }
            foreach (ContratoFuentesPetersen::FUENTES as $fk => [$modelo, $permiso]) {
                $entidad = new $modelo;
                $tabla = $entidad instanceof \App\Models\RegistroConsumo ? $entidad->entidadCodigo() : $entidad->getTable();
                if (collect($v['soportes'])->contains(fn ($s) => $s['tabla'] === $tabla)) {
                    $hechos[] = $this->hecho($id, 'JUICIO_PETERSEN', 'cod_valoracion_petersen', $o['registro'], $o['autor'], $o['fecha'], 'lectura:'.$fk, true, $orden++);
                }
            }
        }
        foreach ($i['salida']['resultado']['perfil'] as $codigo => $dominio) {
            $this->traza($id, $dominio['criterio'], $dominio['traza']['reglas'], $fecha, $dominio['traza']['evidencias']);
        }
        $criterioPet = $p['gobierno']['conocimiento']['tablas']['criterios_dominios_resultado'][0]['cod_nodo_criterio'];
        $reglasPet = array_map(fn ($r) => ['regla' => $r['regla'], 'estado' => ! $r['habilitada'] ? 'INHABILITADA' : ($r['cumple'] ? 'CUMPLE' : 'NO_CUMPLE'), 'condiciones' => $r['condiciones']], $s['gobierno']['reglas']);
        $this->traza($id, $criterioPet, $reglasPet, $fecha, []);
        return $id;
    }

    private function hecho(string $evaluacion, string $tipo, string $fk, string $origen, string $autor, string $fecha, string $campo, mixed $valor, int $orden): string
    {
        $tabla = match ($fk) { 'cod_respuesta' => 'respuestas_instrumento', 'cod_aplicacion' => 'aplicaciones_instrumento', default => 'valoraciones_petersen_originales' };
        if (($this->origenes[$tabla.':'.$origen] ?? null) !== $this->residente) {
            throw new DomainException('La fuente no pertenece al residente evaluado.');
        }
        return $this->insertar('hechos_evaluacion_experta', ['cod_evaluacion_experta' => $evaluacion, 'id_hecho_dominio' => $tipo.':'.$origen.':'.$campo,
            'fuente_id_dominio' => $tabla, 'origen_id_dominio' => $origen, 'episodio_id_dominio' => $tabla.':'.$origen, 'concepto_dominio' => $campo,
            'tipo_origen' => $tipo, $fk => $origen, 'cod_personal_autor' => $autor, 'orden' => $orden]
            + Codec::escalar('valor', $valor) + Codec::fecha('fecha_hecho', new DateTimeImmutable($fecha)));
    }

    private function traza(string $evaluacion, string $criterio, array $reglas, string $fecha, array $evidencias): void
    {
        $ec = $this->insertar('evaluacion_criterios', ['cod_evaluacion_experta' => $evaluacion, 'cod_nodo_criterio' => $criterio,
            'estado_evaluabilidad' => 'INSTITUCIONAL', 'fecha_hora_determinacion' => $fecha]);
        $t = $this->insertar('trazas_inferencia', ['cod_evaluacion_criterio' => $ec, 'estado_inferencia' => 'FINALIZADA', 'fecha_hora_inicio' => $fecha, 'fecha_hora_fin' => $fecha]);
        $ids = [];
        foreach ($evidencias as $e) {
            // Un registro y mapeo auténticos son obligatorios: nunca se fabrica evidencia para un juicio Petersen.
            $ev = DB::table('evidencias_evaluacion')->where('cod_evaluacion_experta', $evaluacion)
                ->where('cod_mapeo_variable_fuente', $e['mapeo_variable'])->where('cod_registro_fuente', $e['registro'])->value('cod_evidencia_evaluacion');
            $ids[$e['id']] = $ev ?? $this->insertar('evidencias_evaluacion', ['cod_evaluacion_experta' => $evaluacion,
                'cod_mapeo_variable_fuente' => $e['mapeo_variable'], 'cod_registro_fuente' => $e['registro'], 'cod_valor_semantico' => $e['valor'],
                'estado_representacion' => $e['representacion'], 'estado_admisibilidad' => $e['admisibilidad'],
                'motivo_admisibilidad' => implode('; ', $e['motivos']), 'fecha_hora_incorporacion' => $fecha]);
            $this->insertar('evidencias_criterio_evaluacion', ['cod_evaluacion_criterio' => $ec, 'cod_evidencia_evaluacion' => $ids[$e['id']],
                'rol_en_criterio' => 'INSTRUMENTAL', 'estado_participacion' => $e['admisibilidad'], 'fecha_hora_vinculacion' => $fecha]);
        }
        foreach ($reglas as $r) {
            $er = $this->insertar('evaluaciones_reglas', ['cod_traza_inferencia' => $t, 'cod_regla_experta' => $r['regla'], 'estado_regla' => $r['estado'], 'fecha_hora_evaluacion' => $fecha]);
            foreach ($r['condiciones'] as $c) {
                $condicion = $this->insertar('evaluaciones_condiciones_regla', ['cod_evaluacion_regla' => $er, 'cod_condicion_regla' => $c['condicion'], 'estado_condicion' => $c['estado'], 'fecha_hora_evaluacion' => $fecha]);
                foreach (array_unique($c['soportes']) as $soporte) {
                    if (isset($ids[$soporte])) { $this->insertar('evidencias_soporte_condicion', ['cod_evaluacion_condicion' => $condicion, 'cod_evidencia_evaluacion' => $ids[$soporte]]); }
                }
            }
        }
    }

    private function narrativa(array $i, array $p): string
    {
        $s = $p['salida'];
        $texto = [R::PATRONES[$s['patron'] ?? ''] ?? 'No se puede establecer un perfil cognitivo con la información disponible.',
            'Resultado orientativo. No constituye un diagnóstico médico confirmado.'];
        foreach (['dominios', 'requisitos'] as $grupo) {
            foreach ($s[$grupo] as $codigo => $d) {
                $texto[] = (R::NOMBRES[$codigo] ?? $codigo).': '.R::ESTADOS[$d['estado']].'.';
                foreach ($d['valoraciones'] as $v) { $texto[] = $v['justificacion']; }
                foreach ($d['motivos'] as $motivo) { $texto[] = 'Limitación de '.(R::NOMBRES[$codigo] ?? $codigo).': '.$motivo.'.'; }
            }
        }
        foreach ($s['impedimentos'] as $motivo) { $texto[] = 'Motivo concurrente de abstención: '.$motivo.'.'; }
        foreach ($s['valoraciones_excluidas'] as $v) {
            $texto[] = 'Valoración excluida de '.(R::NOMBRES[$v['elemento']] ?? $v['elemento']).': '.$v['justificacion'].'. '.implode('; ', $v['motivos']).'.';
        }
        foreach ($p['adquisicion']['exclusiones'] as $v) { $texto[] = 'Registro excluido en la adquisición: '.($v['motivo'] ?? 'No admisible').'.'; }
        foreach ($i['salida']['fuentes_excluidas'] as $v) { $texto[] = 'Fuente instrumental excluida: '.implode('; ', $v['motivos'] ?? [$v['motivo'] ?? 'No admisible']).'.'; }
        return implode("\n\n", $texto);
    }
}
