<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Consultas;

use App\Models\EvaluacionExperta;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\ConocimientoObservacional;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\ConservacionObservacional;
use App\SistemaExperto\Infrastructure\Persistence\Conservacion\CodecInstantanea as Codec;
use DomainException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use MotorExperto\dominio\conocimiento\reglas\EvaluadorObservaciones as Motor;

/** Recupera únicamente la instantánea guardada; verifica huella, conocimiento y decisiones originales. */
final class ConsultarObservacionesConservadas
{
    public function consultar(User $usuario, Residente $residente, ?string $id = null, int $pagina = 1, int $paginaHistorial = 1): ?array
    {
        $usuario = User::query()->findOrFail($usuario->getKey());
        Gate::forUser($usuario)->authorize('consultarResultados', [EvaluacionExperta::class, $residente]);
        $base = EvaluacionExperta::query()->where('cod_residente', $residente->getKey())->where('origen_activacion', 'OBSERVACIONAL')
            ->whereIn('version_conservacion', ['OBS-SNAP-1', 'OBS-SNAP-2'])->where('estado_ejecucion', 'FINALIZADA');
        $historial = (clone $base)->orderByDesc('fecha_hora_inicio')->orderByDesc('cod_evaluacion_experta')
            ->paginate(8, ['*'], 'historiaObservaciones', max(1, $paginaHistorial));
        $cabecera = $id !== null ? (clone $base)->whereKey($id)->firstOrFail()
            : (clone $base)->orderByDesc('fecha_hora_inicio')->orderByDesc('cod_evaluacion_experta')->first();
        if (! $cabecera) {
            return null;
        }
        $versionada = $cabecera->version_conservacion === 'OBS-SNAP-2';
        $idsReglas = $versionada ? DB::table('evaluaciones_reglas as r')
            ->join('trazas_inferencia as t', 't.cod_traza_inferencia', '=', 'r.cod_traza_inferencia')
            ->join('evaluacion_criterios as c', 'c.cod_evaluacion_criterio', '=', 't.cod_evaluacion_criterio')
            ->where('c.cod_evaluacion_experta', $cabecera->getKey())->pluck('r.cod_regla_experta')->all() : [];
        $paquete = $versionada ? app(ConocimientoObservacional::class)->historico($cabecera->cod_version_modelo, $idsReglas)
            : app(ConocimientoObservacional::class)->verificar($cabecera->cod_version_modelo);
        try {
            $corte = Codec::leerFecha('fecha_corte', $cabecera);
            $registros = $this->reconstruir($cabecera->getKey(), $residente->getKey(), $paquete);
        } catch (\DateInvalidTimeZoneException|\ValueError $e) {
            throw new DomainException('La instantánea observacional tiene fechas incompatibles.', 0, $e);
        }
        $informe = (new Motor)->evaluar($residente->getKey(), $corte, $registros, $versionada ? $paquete : null);
        if (! hash_equals($cabecera->huella_memoria_cognicion ?? '', app(ConservacionObservacional::class)->huella($registros, $informe, $versionada ? $paquete : null))) {
            throw new DomainException('La instantánea observacional no supera su comprobación de integridad.');
        }
        if ($versionada) {
            $this->verificarTrazaVersionada($cabecera->getKey(), $paquete, $informe, $registros);
        } else {
            $this->verificarTraza($cabecera->getKey(), $paquete, $informe, $registros);
        }
        $pagina = max(1, $pagina);
        $seleccion = array_slice($registros, ($pagina - 1) * 10, 10);
        $visibles = array_column($seleccion, 'id');
        $autores = Personal::query()->whereIn('cod_personal', array_column($registros, 'autor'))->get()->keyBy('cod_personal');
        $resultados = array_values(array_filter($informe['resultados'], fn ($r) => in_array($r['fuente']['registro'], $visibles, true)));
        foreach ($resultados as &$r) {
            $autor = $autores->get($r['fuente']['autor']);
            $r['fuente']['nombre_autor'] = $autor ? trim(implode(' ', [$autor->nombres, $autor->apellido_paterno, $autor->apellido_materno])) : 'Responsable no disponible';
            $r['fuente']['fecha_visible'] = (new \DateTimeImmutable($r['fuente']['fecha']))->format('d/m/Y H:i');
        }
        unset($r);

        return array_replace($informe, ['persistido' => true, 'id' => $cabecera->getKey(), 'fecha' => $cabecera->fecha_hora_inicio->format('d/m/Y H:i'),
            'resultados' => $resultados, 'informe_completo' => $informe, 'instantanea' => $registros, 'historial' => $historial,
            'registros' => new LengthAwarePaginator($seleccion, count($registros), 10, $pagina, ['pageName' => 'observaciones', 'path' => LengthAwarePaginator::resolveCurrentPath()])]);
    }

    private function reconstruir(string $evaluacion, string $residente, array $paquete): array
    {
        $registros = $metadatos = [];
        $campos = array_column(Motor::REGLAS, 'campo');
        $mapeos = array_column($paquete['campos'] ?? $paquete['reglas'], 'mapeo', 'campo');
        $evidencias = DB::table('evidencias_evaluacion')->where('cod_evaluacion_experta', $evaluacion)->get()->keyBy('cod_evidencia_evaluacion');
        $filas = DB::table('hechos_instantanea_experta')->where('cod_evaluacion_experta', $evaluacion)->orderBy('orden')->get();
        foreach ($filas as $h) {
            $id = $h->cod_control_cognitivo;
            $campo = $h->concepto_dominio;
            $evidencia = $evidencias->get($h->cod_evidencia_evaluacion);
            $episodio = $h->cod_atencion ? 'atencion:'.$h->cod_atencion : 'control:'.$id;
            if ($h->tipo_origen !== 'CONTROL_COGNITIVO' || ! $id || ! $h->cod_personal_autor || $h->cod_actividad !== null
                || $h->fuente_id_dominio !== 'controles_cognitivos' || $h->origen_id_dominio !== $id
                || $h->episodio_id_dominio !== $episodio || $h->id_hecho_dominio !== $id.':'.$campo
                || ! in_array($campo, [...$campos, 'estado'], true)
                || ! $evidencia || $evidencia->cod_registro_fuente !== $id
                || $evidencia->cod_mapeo_variable_fuente !== ($mapeos[$campo === 'estado' ? 'repite_preguntas' : $campo] ?? null)) {
                throw new DomainException('Origen observacional conservado incompatible.');
            }
            $base = ['id' => $id, 'residente' => $residente, 'autor' => $h->cod_personal_autor,
                'fecha' => Codec::leerFecha('fecha_hecho', $h), 'episodio' => $episodio, 'contexto_coherente' => true];
            $firma = Codec::huella($base);
            if (isset($metadatos[$id]) && $metadatos[$id] !== $firma) {
                throw new DomainException('Los hechos de un mismo control conservan metadatos diferentes.');
            }
            $metadatos[$id] = $firma;
            $registros[$id] ??= $base + ['valores' => []];
            $valor = Codec::leerEscalar('valor', $h);
            if ($campo === 'estado') {
                if (isset($registros[$id]['estado']) || ! is_string($valor)) {
                    throw new DomainException('Estado original duplicado o inválido.');
                }
                $registros[$id]['estado'] = $valor;
            } else {
                if (array_key_exists($campo, $registros[$id]['valores']) || ($valor !== null && ! is_bool($valor))) {
                    throw new DomainException('Indicador observacional duplicado o inválido.');
                }
                $registros[$id]['valores'][$campo] = $valor;
            }
        }
        foreach ($registros as $r) {
            if (! isset($r['estado']) || count($r['valores']) !== 3 || array_diff($campos, array_keys($r['valores']))) {
                throw new DomainException('La instantánea observacional está incompleta.');
            }
        }

        return array_values($registros);
    }

    /** Comprueba conjuntos completos; no acepta reglas nuevas ni enlaces sobrantes en una historia. */
    private function verificarTrazaVersionada(string $id, array $p, array $informe, array $registros): void
    {
        $criterios = DB::table('evaluacion_criterios')->where('cod_evaluacion_experta', $id)->get();
        $trazas = DB::table('trazas_inferencia')->whereIn('cod_evaluacion_criterio', $criterios->pluck('cod_evaluacion_criterio'))->get();
        $reglas = DB::table('evaluaciones_reglas')->whereIn('cod_traza_inferencia', $trazas->pluck('cod_traza_inferencia'))->get();
        $condiciones = DB::table('evaluaciones_condiciones_regla')->whereIn('cod_evaluacion_regla', $reglas->pluck('cod_evaluacion_regla'))->get();
        $soportes = DB::table('evidencias_soporte_condicion')->whereIn('cod_evaluacion_condicion', $condiciones->pluck('cod_evaluacion_condicion'))->get();
        $participaciones = DB::table('evidencias_criterio_evaluacion')->whereIn('cod_evaluacion_criterio', $criterios->pluck('cod_evaluacion_criterio'))->get();
        $resultados = DB::table('resultados_criterio')->whereIn('cod_evaluacion_criterio', $criterios->pluck('cod_evaluacion_criterio'))->get();
        $evidencias = DB::table('evidencias_evaluacion')->where('cod_evaluacion_experta', $id)->get();
        if ($criterios->count() !== count($p['criterios']) || $trazas->count() !== count($p['criterios'])
            || $reglas->count() !== count($p['reglas']) || $condiciones->count() !== count($p['reglas'])
            || $evidencias->count() !== count($registros) * count($p['campos'])) {
            throw new DomainException('La traza observacional versionada está incompleta o duplicada.');
        }
        $literal = (new Motor)->evaluar($informe['residente'], new \DateTimeImmutable($informe['fecha_corte']), $registros);
        $decisiones = [];
        foreach ($literal['resultados'] as $d) {
            $decisiones[$d['fuente']['registro']][$d['condicion']['campo']] = $d;
        }
        $evPorCampo = [];
        foreach ($p['campos'] as $campo => $definicion) {
            foreach ($registros as $r) {
                $es = $evidencias->where('cod_mapeo_variable_fuente', $definicion['mapeo'])->where('cod_registro_fuente', $r['id']);
                $v = $r['valores'][$campo];
                $d = $decisiones[$r['id']][$campo];
                if ($es->count() !== 1 || $es->sole()->cod_valor_semantico !== ($v === null ? null : $p['valores'][$v ? 'SI' : 'NO'])
                    || $es->sole()->estado_representacion !== ($v === null ? 'NO_MAPEADO' : 'MAPEADO')
                    || $es->sole()->estado_admisibilidad !== ($d['estado'] === 'NO_EVALUABLE' ? 'NO_ADMISIBLE' : 'ADMISIBLE')
                    || $es->sole()->motivo_admisibilidad !== $d['condicion']['motivo']) {
                    throw new DomainException('Evidencia y valor original incompatibles.');
                }
                $evPorCampo[$campo][$es->sole()->cod_evidencia_evaluacion] = $d['estado'];
            }
        }
        foreach ($p['criterios'] as $criterio => $definicion) {
            $cs = $criterios->where('cod_nodo_criterio', $criterio);
            if ($cs->count() !== 1) {
                throw new DomainException('Criterio observacional ausente o duplicado.');
            }
            $c = $cs->sole();
            $ts = $trazas->where('cod_evaluacion_criterio', $c->cod_evaluacion_criterio);
            $rs = array_filter($p['reglas'], fn ($r) => $r['criterio'] === $criterio);
            $estados = array_map(fn ($codigo) => $informe['resumen'][$codigo]['estado'], array_keys($rs));
            $estado = in_array('CUMPLE', $estados, true) ? 'CUMPLE'
                : ($estados === [] || in_array('NO_EVALUABLE', $estados, true) ? 'NO_EVALUABLE' : 'NO_CUMPLE');
            if ($ts->count() !== 1 || $ts->sole()->estado_inferencia !== 'FINALIZADA' || $c->tipo_registro !== 'D137'
                || $c->estado_evaluabilidad !== ($estado === 'NO_EVALUABLE' ? 'OBS_NO_EVALUABLE' : 'OBS_EVALUABLE')) {
                throw new DomainException('Estado de criterio o traza incompatible.');
            }
            $t = $ts->sole();
            if ($reglas->where('cod_traza_inferencia', $t->cod_traza_inferencia)->count() !== count($rs)) {
                throw new DomainException('Regla vinculada a otro criterio.');
            }
            $esperadas = $rs === [] ? [] : ($evPorCampo[$definicion['campo']] ?? []);
            $ps = $participaciones->where('cod_evaluacion_criterio', $c->cod_evaluacion_criterio);
            if ($ps->count() !== count($esperadas)) {
                throw new DomainException('Participación de evidencias incompleta o duplicada.');
            }
            foreach ($ps as $vinculo) {
                if ($vinculo->rol_en_criterio !== 'OBSERVACION' || ($esperadas[$vinculo->cod_evidencia_evaluacion] ?? null) !== $vinculo->estado_participacion) {
                    throw new DomainException('Participación observacional incompatible.');
                }
            }
            foreach ($rs as $codigo => $regla) {
                $evaluadas = $reglas->where('cod_traza_inferencia', $t->cod_traza_inferencia)->where('cod_regla_experta', $regla['regla']);
                if ($evaluadas->count() !== 1 || $evaluadas->sole()->estado_regla !== $informe['resumen'][$codigo]['estado']) {
                    throw new DomainException('Estado de regla incompatible con la instantánea.');
                }
                $ds = $condiciones->where('cod_evaluacion_regla', $evaluadas->sole()->cod_evaluacion_regla);
                if ($ds->count() !== 1 || $ds->sole()->cod_condicion_regla !== $regla['condicion'] || $ds->sole()->estado_condicion !== $informe['resumen'][$codigo]['estado']) {
                    throw new DomainException('Condición original incompatible.');
                }
                $guardados = $soportes->where('cod_evaluacion_condicion', $ds->sole()->cod_evaluacion_condicion)->pluck('cod_evidencia_evaluacion')->all();
                $esperados = array_keys($evPorCampo[$regla['campo']] ?? []);
                sort($guardados);
                sort($esperados);
                if ($guardados !== $esperados) {
                    throw new DomainException('Soportes originales incompletos o ajenos.');
                }
            }
            $salidas = $resultados->where('cod_evaluacion_criterio', $c->cod_evaluacion_criterio);
            if ($salidas->count() !== ($estado === 'CUMPLE' ? 1 : 0)
                || ($estado === 'CUMPLE' && ($salidas->sole()->cod_valor_semantico !== $definicion['valorResultado'] || $salidas->sole()->justificacion_original !== $definicion['hallazgo']))) {
                throw new DomainException('Resultado original incompatible.');
            }
        }
    }

    private function verificarTraza(string $id, array $paquete, array $informe, array $registros): void
    {
        $trazas = DB::table('evaluacion_criterios as c')->join('trazas_inferencia as t', 't.cod_evaluacion_criterio', '=', 'c.cod_evaluacion_criterio')
            ->join('evaluaciones_reglas as r', 'r.cod_traza_inferencia', '=', 't.cod_traza_inferencia')
            ->join('evaluaciones_condiciones_regla as d', 'd.cod_evaluacion_regla', '=', 'r.cod_evaluacion_regla')
            ->leftJoin('resultados_criterio as s', 's.cod_evaluacion_criterio', '=', 'c.cod_evaluacion_criterio')
            ->where('c.cod_evaluacion_experta', $id)->get(['c.cod_evaluacion_criterio', 'c.cod_nodo_criterio', 'c.tipo_registro', 'c.estado_evaluabilidad', 't.estado_inferencia',
                'r.cod_regla_experta', 'r.estado_regla', 'd.cod_condicion_regla', 'd.estado_condicion', 'd.cod_evaluacion_condicion',
                's.cod_valor_semantico', 's.justificacion_original']);
        if ($trazas->count() !== 3 || DB::table('evaluacion_criterios')->where('cod_evaluacion_experta', $id)->count() !== 3) {
            throw new DomainException('La traza observacional está incompleta o duplicada.');
        }
        $evidencias = DB::table('evidencias_evaluacion')->where('cod_evaluacion_experta', $id)->get();
        if ($evidencias->count() !== count($registros) * 3) {
            throw new DomainException('Faltan evidencias originales de la ejecución.');
        }
        foreach ($paquete['reglas'] as $codigo => $regla) {
            $estado = $informe['resumen'][$codigo]['estado'];
            $r = $trazas->firstWhere('cod_regla_experta', $regla['regla']);
            if (! $r || $r->estado_regla !== $estado || $r->estado_condicion !== $estado || $r->cod_condicion_regla !== $regla['condicion']
                || $r->cod_nodo_criterio !== $regla['criterio'] || $r->tipo_registro !== 'D137' || $r->estado_inferencia !== 'FINALIZADA'
                || $r->estado_evaluabilidad !== ($estado === 'NO_EVALUABLE' ? 'OBS_NO_EVALUABLE' : 'OBS_EVALUABLE')
                || $r->justificacion_original !== $informe['resumen'][$codigo]['hallazgo']
                || $r->cod_valor_semantico !== ($estado === 'CUMPLE' ? $regla['valorResultado'] : null)) {
                throw new DomainException('La traza original no coincide con los hechos conservados.');
            }
            $esperados = [];
            $participacionEsperada = [];
            foreach ($registros as $registro) {
                $es = $evidencias->where('cod_mapeo_variable_fuente', $regla['mapeo'])->where('cod_registro_fuente', $registro['id']);
                $v = $registro['valores'][$regla['campo']];
                $decision = collect($informe['resultados'])->first(fn ($d) => $d['regla'] === $codigo && $d['fuente']['registro'] === $registro['id']);
                if ($es->count() !== 1 || $es->sole()->cod_valor_semantico !== ($v === null ? null : $paquete['valores'][$v ? 'SI' : 'NO'])
                    || $es->sole()->estado_representacion !== ($v === null ? 'NO_MAPEADO' : 'MAPEADO')
                    || $es->sole()->estado_admisibilidad !== ($decision['estado'] === 'NO_EVALUABLE' ? 'NO_ADMISIBLE' : 'ADMISIBLE')
                    || $es->sole()->motivo_admisibilidad !== $decision['condicion']['motivo']) {
                    throw new DomainException('Evidencia y valor original incompatibles.');
                }
                $esperados[] = $es->sole()->cod_evidencia_evaluacion;
                $participacionEsperada[$es->sole()->cod_evidencia_evaluacion] = $decision['estado'];
            }
            $vinculos = DB::table('evidencias_criterio_evaluacion')->where('cod_evaluacion_criterio', $r->cod_evaluacion_criterio)->get();
            if ($vinculos->count() !== count($participacionEsperada)) {
                throw new DomainException('La participación de las evidencias conservadas está incompleta o duplicada.');
            }
            foreach ($vinculos as $vinculo) {
                if ($vinculo->rol_en_criterio !== 'OBSERVACION'
                    || ($participacionEsperada[$vinculo->cod_evidencia_evaluacion] ?? null) !== $vinculo->estado_participacion) {
                    throw new DomainException('Una evidencia no corresponde al criterio o estado originales.');
                }
            }
            $guardados = DB::table('evidencias_soporte_condicion')->where('cod_evaluacion_condicion', $r->cod_evaluacion_condicion)->pluck('cod_evidencia_evaluacion')->all();
            sort($esperados);
            sort($guardados);
            if ($esperados !== $guardados) {
                throw new DomainException('Los soportes conservados no coinciden con las condiciones originales.');
            }
        }
    }
}
