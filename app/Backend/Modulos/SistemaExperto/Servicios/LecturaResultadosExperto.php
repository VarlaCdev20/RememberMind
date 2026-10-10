<?php

namespace App\Backend\Modulos\SistemaExperto\Servicios;

use App\Backend\Modulos\SistemaExperto\Acciones\CargarConocimientoOrion;
use App\Backend\Modulos\SistemaExperto\Presentacion\LenguajeResultadosExperto as L;
use App\Models\ControlCognitivo;
use App\Models\EvaluacionCriterio;
use App\Models\EvaluacionExperta;
use App\Models\NodoSemantico;
use App\Models\Residente;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

/** Lectura exacta del historial: no ejecuta el motor ni remapea fuentes. */
final class LecturaResultadosExperto
{
    public function consultar(User $usuario, Residente $residente, ?string $id, string $criterio, int $pagina = 1): array
    {
        Gate::forUser($usuario)->authorize('consultarResultados', [EvaluacionExperta::class, $residente]);
        abort_unless(isset(L::CRITERIOS[$criterio]), 422);
        $base = LecturaTecnicaPruebas::limitar(EvaluacionExperta::query()->where('cod_residente', $residente->cod_residente));
        $historial = (clone $base)
            ->orderByDesc('fecha_hora_inicio')->orderByDesc('cod_evaluacion_experta')->paginate(8, ['*'], 'historia', $pagina);
        $id ??= (clone $base)->orderByDesc('fecha_hora_inicio')->orderByDesc('cod_evaluacion_experta')->value('cod_evaluacion_experta');
        $relaciones = [
            'version', 'personalSolicitante', 'evaluacionCriterios.criterio',
            'evidenciasEvaluacion.mapeoVariableFuente.fuente',
            'evidenciasEvaluacion.relacionesEvidenciasEvaluacionPorEvidenciaOrigen',
            'evaluacionCriterios.resultado.valor.dominio',
            'evaluacionCriterios.evidenciasCriterioEvaluacion.evidencia.mapeoVariableFuente.fuente',
            'evaluacionCriterios.evidenciasCriterioEvaluacion.evidencia.mapeoVariableFuente.variable.nodo',
            'evaluacionCriterios.evidenciasCriterioEvaluacion.evidencia.valor.dominio',
            'evaluacionCriterios.evidenciasCriterioEvaluacion.evidencia.relacionesEvidenciasEvaluacionPorEvidenciaOrigen',
            'evaluacionCriterios.evidenciasCriterioEvaluacion.evidencia.relacionesEvidenciasEvaluacionPorEvidenciaDestino',
            'evaluacionCriterios.traza.evaluacionesReglas.regla.condicionesReglaExperta',
            'evaluacionCriterios.traza.evaluacionesReglas.regla.consecuenciasReglaExperta.criterioDominioResultado',
            'evaluacionCriterios.traza.evaluacionesReglas.regla.consecuenciasReglaExperta.valor',
            'evaluacionCriterios.traza.evaluacionesReglas.evaluacionesCondicionesRegla.condicion',
            'evaluacionCriterios.traza.evaluacionesReglas.evaluacionesCondicionesRegla.evidenciasSoporteCondicion',
        ];
        $historial->getCollection()->load($relaciones);
        $evaluacion = $id === null ? null : ($historial->getCollection()->firstWhere('cod_evaluacion_experta', $id)
            ?? (clone $base)->with($relaciones)->whereKey($id)->firstOrFail());
        $perfil = [];
        foreach (L::CRITERIOS as $codigo => $etiqueta) {
            $instancia = $evaluacion?->evaluacionCriterios->first(fn ($ec) => $ec->criterio?->codigo_semantico === $codigo);
            $perfil[$codigo] = $etiqueta + $this->detalle($evaluacion, $instancia, $codigo);
        }
        $nacimiento = $residente->fecha_nacimiento;
        $edad = $nacimiento && $nacimiento->isPast() ? $nacimiento->age : null;
        $historial->through(function ($h) {
            $criterios = [];
            foreach (L::CRITERIOS as $codigo => $etiqueta) {
                $ec = $h->evaluacionCriterios->first(fn ($ec) => $ec->criterio?->codigo_semantico === $codigo);
                if (! $ec) {
                    continue;
                }
                $detalle = $this->detalle($h, $ec, $codigo);
                $criterios[] = ['nombre' => $etiqueta['nombre'], 'estado' => $detalle['estado'], 'evaluabilidad' => $detalle['evaluabilidad']];
            }

            return ['id' => $h->getKey(), 'fecha' => $h->fecha_hora_inicio?->format('d/m/Y H:i'), 'version' => $h->version?->codigo_version, 'criterios' => $criterios];
        });

        return ['residente' => ['codigo' => $residente->cod_residente, 'nombre' => trim(implode(' ', [$residente->nombres, $residente->apellido_paterno, $residente->apellido_materno])), 'edad' => $edad],
            'evaluacion' => $evaluacion ? ['id' => $evaluacion->getKey(), 'fecha' => $evaluacion->fecha_hora_inicio?->format('d/m/Y H:i'), 'version' => $evaluacion->version?->codigo_version ?? 'No disponible', 'estado' => $evaluacion->estado_ejecucion, 'solicitante' => $evaluacion->personalSolicitante ? trim(implode(' ', [$evaluacion->personalSolicitante->nombres, $evaluacion->personalSolicitante->apellido_paterno, $evaluacion->personalSolicitante->apellido_materno])) : null] : null,
            'perfil' => $perfil, 'detalle' => $perfil[$criterio], 'historial' => $historial,
            'prueba_tecnica' => LecturaTecnicaPruebas::habilitada(),
            'informe_tecnico' => $criterio === 'COG-MEM'
                ? LecturaTecnicaPruebas::informe($evaluacion?->getKey(), $residente->getKey()) : null,
            'conocimiento_documental' => ! $evaluacion ? $this->conocimientoDocumental($criterio) : null,
            'preparacion_memoria' => $criterio === 'COG-MEM' && ! $evaluacion
                ? (new PreparacionFuentesCOGMEM)->consultar($usuario, $residente) : null,
            'registros_cognitivos' => $criterio === 'COG-MEM' && ! $evaluacion
                ? $this->registrosCognitivos($residente) : []];
    }

    private function conocimientoDocumental(string $criterio): ?array
    {
        $nodo = NodoSemantico::query()->with('version')
            ->where('cod_version_modelo', CargarConocimientoOrion::VERSION)->where('codigo_semantico', $criterio)->first();

        return $nodo ? ['version' => $nodo->version->codigo_version, 'nombre' => $nodo->nombre,
            'definicion' => $nodo->definicion, 'procedencia' => $nodo->observacion] : null;
    }

    /** Datos guardados de la fuente operativa; nunca equivalen a una inferencia. */
    private function registrosCognitivos(Residente $residente): array
    {
        $campos = ['memoria_reciente' => 'Memoria reciente', 'memoria_remota' => 'Memoria remota',
            'repite_preguntas' => 'Repetición de preguntas', 'olvida_indicaciones' => 'Olvido de indicaciones',
            'cambio_cognitivo' => 'Cambio cognitivo registrado'];

        return ControlCognitivo::query()->where('cod_residente', $residente->getKey())
            ->where('estado', 'VIGENTE')->where('fecha_hora', '<=', now())
            ->with('personal:cod_personal,nombres,apellido_paterno,apellido_materno')
            ->orderByDesc('fecha_hora')->orderByDesc('cod_control_cognitivo')->limit(5)->get()
            ->map(function (ControlCognitivo $control) use ($campos): array {
                $mediciones = [];
                foreach ($campos as $campo => $nombre) {
                    $valor = $control->getAttribute($campo);
                    $mediciones[] = ['nombre' => $nombre, 'valor' => match (true) {
                        $valor === null || $valor === '' => 'No registrado',
                        is_bool($valor) => $valor ? 'Marcado en el registro' : 'No marcado en el registro',
                        default => match ($valor) {
                            'CONSERVADA' => 'Conservada', 'ALTERACION_LEVE' => 'Alteración leve',
                            default => (string) $valor,
                        },
                    }];
                }

                return ['codigo' => $control->getKey(), 'fecha' => $control->fecha_hora->format('d/m/Y H:i'),
                    'autor' => $control->personal ? trim(implode(' ', [$control->personal->nombres,
                        $control->personal->apellido_paterno, $control->personal->apellido_materno])) : 'Autor no disponible',
                    'mediciones' => $mediciones, 'observacion' => $control->observacion];
            })->all();
    }

    private function detalle(?EvaluacionExperta $evaluacion, ?EvaluacionCriterio $ec, string $codigo): array
    {
        $d = ['estado' => 'Sin evaluación disponible', 'activacion' => 'Conocimiento no activado', 'evaluabilidad' => null, 'modificador' => null, 'motivo' => null,
            'resultado' => null, 'interpretacion' => 'Todavía no hay una evaluación guardada para esta área. No se ha emitido una conclusión.', 'evidencias' => [], 'reglas' => [], 'integridad' => [], 'traza' => null, 'autorizado' => false];
        if (! $ec) {
            return $d;
        }
        $d['evaluabilidad'] = L::evaluabilidad($ec->estado_evaluabilidad);
        $d['modificador'] = $ec->codigo_modificador_interpretacion === 'MOD-CM-1' ? 'Resultado con factores que limitan su interpretación' : ($ec->codigo_modificador_interpretacion ? 'Modificador sin contrato de presentación' : null);
        $d['motivo'] = $ec->motivo_evaluabilidad;
        $contrato = LecturaTecnicaPruebas::contrato($evaluacion->cod_version_modelo);
        $autorizado = in_array($codigo, $contrato['criterios'] ?? [], true)
            && in_array($evaluacion->estado_ejecucion, $contrato['estados_ejecucion'] ?? [], true);
        $d['autorizado'] = $autorizado;
        $d['activacion'] = $autorizado ? (LecturaTecnicaPruebas::habilitada() ? 'Resultado de prueba técnica' : 'Lectura histórica autorizada') : 'Conocimiento no activado';
        $d['estado'] = 'Publicación clínica no habilitada';
        $d['interpretacion'] = 'El conocimiento de esta área y versión no tiene un contrato de publicación clínica habilitado. No se publica una interpretación.';
        if (! in_array($ec->estado_evaluabilidad, ['EV-CM-0', 'EV-CM-1', 'EV-CM-2'], true)) {
            $d['integridad'][] = 'El estado de evaluabilidad no tiene un contrato reconocido.';
        }
        $traza = $ec->traza;
        if ($ec->criterio?->cod_version_modelo !== $evaluacion->cod_version_modelo) {
            $d['integridad'][] = 'El criterio no pertenece a la versión de la evaluación.';

            return $this->cerrarDetalle($d);
        }
        $evidencias = [];
        foreach ($ec->evidenciasCriterioEvaluacion as $participacion) {
            $e = $participacion->evidencia;
            $mapa = $e?->mapeoVariableFuente;
            if (! $e || $e->cod_evaluacion_experta !== $evaluacion->getKey() || $mapa?->cod_version_modelo !== $evaluacion->cod_version_modelo
                || $mapa->variable?->cod_version_modelo !== $evaluacion->cod_version_modelo || $mapa->fuente?->cod_version_modelo !== $evaluacion->cod_version_modelo
                || ($e->valor && $e->valor->cod_dominio_valores !== $mapa->variable->cod_dominio_valores)) {
                $d['integridad'][] = 'Existe una evidencia incompatible con la evaluación o versión.';

                continue;
            }
            $evidencias[$e->getKey()] = $e;
            $d['evidencias'][$e->getKey()] = ['id' => $e->getKey(), 'nombre' => L::evidencia($mapa->variable?->nodo?->codigo_semantico, $mapa->variable?->nodo?->nombre),
                'fuente' => $mapa->fuente?->nombre ?? 'Fuente sin descripción', 'tabla' => $mapa->fuente?->tabla_raiz, 'campo' => $mapa->campo_valor,
                'registro' => $e->cod_registro_fuente, 'mapeo' => $mapa->getKey(), 'variable' => $mapa->cod_variable_experta,
                'valor' => $e->valor?->codigo_valor, 'fecha' => $e->fecha_hora_incorporacion?->format('d/m/Y H:i'),
                'admisibilidad' => $e->estado_admisibilidad, 'motivo' => $e->motivo_admisibilidad, 'representacion' => $e->estado_representacion,
                'rol' => $participacion->rol_en_criterio, 'participacion' => $participacion->estado_participacion, 'justificacion' => $participacion->justificacion,
                'papel' => $participacion->rol_en_criterio === 'CONTEXTUAL' ? 'Contexto' : 'No utilizada como soporte del resultado'];
        }
        if ($traza) {
            $d['traza'] = ['id' => $traza->getKey(), 'estado' => $traza->estado_inferencia, 'inicio' => $traza->fecha_hora_inicio?->format('d/m/Y H:i'), 'fin' => $traza->fecha_hora_fin?->format('d/m/Y H:i')];
            $consecuencias = [];
            $rutasResultado = [];
            foreach ($traza->evaluacionesReglas as $er) {
                $regla = $er->regla;
                if ($regla?->cod_version_modelo !== $evaluacion->cod_version_modelo) {
                    $d['integridad'][] = 'Una regla no pertenece a la versión conservada.';

                    continue;
                }
                $condiciones = [];
                $soportesRegla = [];
                if (! in_array($er->estado_regla, ['CUMPLE', 'NO_CUMPLE'], true)) {
                    $d['integridad'][] = 'Una regla tiene un estado de cumplimiento desconocido.';
                }
                foreach ($er->evaluacionesCondicionesRegla as $condicionEvaluada) {
                    $c = $condicionEvaluada->condicion;
                    $soportes = [];
                    if ($c?->cod_regla_experta !== $regla->getKey()) {
                        $d['integridad'][] = 'Una condición no corresponde a su regla.';

                        continue;
                    }
                    if (! in_array($condicionEvaluada->estado_condicion, ['CUMPLE', 'NO_CUMPLE'], true)
                        || ($condicionEvaluada->estado_condicion !== 'CUMPLE' && $condicionEvaluada->evidenciasSoporteCondicion->isNotEmpty())) {
                        $d['integridad'][] = 'Una condición conserva un estado desconocido o soporte incompatible con su estado.';
                    }
                    foreach ($condicionEvaluada->evidenciasSoporteCondicion as $s) {
                        $e = $evidencias[$s->cod_evidencia_evaluacion] ?? null;
                        if (! $e || ! in_array($e->estado_admisibilidad, ['ADMISIBLE', 'ADMISIBLE_CON_ADVERTENCIA'], true) || $e->estado_representacion !== 'MAPEADO'
                            || $e->mapeoVariableFuente->cod_variable_experta !== $c->cod_variable_experta || $e->cod_valor_semantico !== $c->cod_valor_semantico) {
                            $d['integridad'][] = 'Un soporte no coincide con la evidencia, variable o valor registrados.';

                            continue;
                        }
                        $participacion = $d['evidencias'][$e->getKey()];
                        if ($participacion['rol'] === 'CONTEXTUAL' || ($autorizado &&
                            (! in_array($participacion['rol'], $contrato['roles_soporte'] ?? [], true)
                            || ! in_array($participacion['participacion'], $contrato['estados_participacion_soporte'] ?? [], true)))) {
                            $d['integridad'][] = 'Una condición utiliza contexto o participación no autorizada como soporte directo.';

                            continue;
                        }
                        $soportes[] = $e->getKey();
                    }
                    if ($condicionEvaluada->estado_condicion === 'CUMPLE' && ! $soportes) {
                        $d['integridad'][] = 'Una condición satisfecha no conserva su soporte.';
                    }
                    if ($er->estado_regla === 'CUMPLE' && $condicionEvaluada->estado_condicion !== 'CUMPLE') {
                        $d['integridad'][] = 'Una regla satisfecha contiene condiciones no satisfechas.';
                    }
                    $soportesRegla = array_merge($soportesRegla, $soportes);
                    $condiciones[] = ['id' => $c->getKey(), 'estado' => $condicionEvaluada->estado_condicion, 'variable' => $c->cod_variable_experta, 'valor' => $c->cod_valor_semantico, 'soportes' => $soportes];
                }
                if (count($condiciones) !== $regla->condicionesReglaExperta->count() || ! $condiciones) {
                    $d['integridad'][] = 'La traza no conserva todas las condiciones de una regla evaluada.';
                } elseif (($er->estado_regla === 'CUMPLE') !== collect($condiciones)->every(fn ($c) => $c['estado'] === 'CUMPLE')) {
                    $d['integridad'][] = 'El estado de una regla no coincide con sus condiciones conservadas.';
                }
                $cs = [];
                foreach ($regla->consecuenciasReglaExperta as $consecuencia) {
                    $asociacion = $consecuencia->criterioDominioResultado;
                    if ($asociacion?->cod_nodo_criterio !== $ec->cod_nodo_criterio) {
                        continue;
                    }
                    if ($consecuencia->valor?->cod_dominio_valores !== $asociacion->cod_dominio_valores
                        || $ec->resultado?->valor?->cod_dominio_valores !== $asociacion->cod_dominio_valores
                        || ! in_array($consecuencia->valor?->codigo_valor, ['DIFICULTAD_EVIDENCIADA', 'SIN_DIFICULTAD_EVIDENCIADA'], true)) {
                        $d['integridad'][] = 'Una consecuencia usa un dominio de resultado incompatible.';

                        continue;
                    }
                    $cs[] = $consecuencia->valor->codigo_valor;
                }
                if ($er->estado_regla === 'CUMPLE' && $cs) {
                    $consecuencias = array_merge($consecuencias, $cs);
                    foreach ($cs as $valorResultado) {
                        $rutasResultado[$valorResultado][] = array_unique($soportesRegla);
                    }
                    foreach ($soportesRegla as $eid) {
                        $d['evidencias'][$eid]['papel'] = 'Soporte de inferencia';
                    }
                }
                $d['reglas'][] = ['id' => $regla->getKey(), 'nombre' => $regla->nombre ?? 'Regla registrada', 'estado' => $er->estado_regla, 'condiciones' => $condiciones, 'consecuencias' => $cs];
            }
            if ($autorizado && ! in_array($traza->estado_inferencia, $contrato['estados_inferencia'] ?? [], true)) {
                $d['integridad'][] = 'El estado de la traza no tiene un contrato de publicación autorizado.';
            }
        } elseif ($ec->estado_evaluabilidad === 'EV-CM-2') {
            $d['integridad'][] = 'No hay traza conservada para este criterio.';
        }
        if ($codigo === 'COG-MEM') {
            $r = $ec->resultado;
            $valor = $r?->valor;
            if ($ec->estado_evaluabilidad !== 'EV-CM-2' && $r) {
                $d['integridad'][] = 'Existe un resultado sin la compuerta de evaluabilidad requerida.';
            } elseif ($ec->estado_evaluabilidad === 'EV-CM-2') {
                $especifica = collect($evidencias)->contains(fn ($e) => $e->mapeoVariableFuente->variable->nodo?->codigo_semantico === 'VAR-MEM-INST-01'
                    && $e->cod_valor_semantico !== null && $e->valor !== null
                    && $e->estado_representacion === 'MAPEADO' && in_array($e->estado_admisibilidad, ['ADMISIBLE', 'ADMISIBLE_CON_ADVERTENCIA'], true)
                    && $e->mapeoVariableFuente->fuente->tabla_raiz === 'aplicaciones_instrumento'
                    && filled($e->mapeoVariableFuente->clave_selector)
                    && $d['evidencias'][$e->getKey()]['rol'] !== 'CONTEXTUAL'
                    && (! $autorizado || (in_array($e->cod_mapeo_variable_fuente, $contrato['componentes_memoria'] ?? [], true)
                        && in_array($d['evidencias'][$e->getKey()]['rol'], $contrato['roles_soporte'] ?? [], true)
                        && in_array($d['evidencias'][$e->getKey()]['participacion'], $contrato['estados_participacion_soporte'] ?? [], true))));
                if (! $especifica) {
                    $d['integridad'][] = 'No se conserva evidencia específica de un componente de memoria autorizado.';
                }
                $cs = array_values(array_unique($consecuencias ?? []));
                $resultadoCompatible = count($cs) === 1 ? ($valor?->codigo_valor === $cs[0])
                    : ($valor?->codigo_valor === 'HALLAZGOS_MIXTOS' && count($cs) === 2 && in_array('DIFICULTAD_EVIDENCIADA', $cs, true) && in_array('SIN_DIFICULTAD_EVIDENCIADA', $cs, true));
                if ($valor?->codigo_valor === 'HALLAZGOS_MIXTOS' && ! $this->soportesIndependientes($rutasResultado ?? [], $evaluacion)) {
                    $d['integridad'][] = 'Los hallazgos opuestos no conservan soporte independiente verificable.';
                }
                if (! $valor || ! isset(L::RESULTADOS[$valor->codigo_valor]) || $valor->dominio?->codigo_dominio !== 'DOM-RESULTADO-COG-MEM'
                    || $valor->dominio?->cod_version_modelo !== $evaluacion->cod_version_modelo || ! ($consecuencias ?? [])) {
                    $d['integridad'][] = 'El resultado carece de dominio compatible o de soporte conservado.';
                } elseif (! $resultadoCompatible) {
                    $d['integridad'][] = 'El resultado no coincide con las consecuencias conservadas de reglas satisfechas.';
                } elseif ($autorizado) {
                    $d['resultado'] = L::RESULTADOS[$valor->codigo_valor] + ['codigo' => $valor->codigo_valor];
                    $d['interpretacion'] = LecturaTecnicaPruebas::habilitada()
                        ? 'Resultado semántico de la ejecución artificial: '.$valor->codigo_valor.'. Comprueba el recorrido del motor, la evidencia y la presentación; no determina el estado cognitivo de una persona ni acredita validación profesional.'
                        : $d['resultado']['interpretacion'];
                    $d['estado'] = $d['resultado']['titulo'];
                }
            } elseif ($autorizado) {
                $d['estado'] = $ec->estado_evaluabilidad === 'EV-CM-0' ? 'Información insuficiente' : 'Información preliminar';
                $d['interpretacion'] = 'La información disponible no permite emitir una conclusión integrada válida sobre esta área cognitiva.';
            }
        }

        return $this->cerrarDetalle($d);
    }

    private function cerrarDetalle(array $d): array
    {
        if ($d['integridad']) {
            $d['resultado'] = null;
            $d['estado'] = 'Error de inferencia';
            $d['interpretacion'] = 'No se publica una conclusión clínica: el registro conservado requiere revisar su integridad.';
            foreach ($d['evidencias'] as &$e) {
                if ($e['papel'] === 'Soporte de inferencia') {
                    $e['papel'] = 'Vínculo conservado; integridad no verificada';
                }
            }
        } elseif (! $d['autorizado']) {
            foreach ($d['evidencias'] as &$e) {
                if ($e['papel'] === 'Soporte de inferencia') {
                    $e['papel'] = 'Soporte registrado; publicación no habilitada';
                }
            }
        }

        return $d;
    }

    private function soportesIndependientes(array $rutas, EvaluacionExperta $evaluacion): bool
    {
        $grupos = [];
        foreach ($evaluacion->evidenciasEvaluacion as $e) {
            $mapa = $e->mapeoVariableFuente;
            if (! $mapa || $mapa->cod_version_modelo !== $evaluacion->cod_version_modelo || ! $mapa->fuente) {
                return false;
            }
            $grupos[$e->getKey()] = serialize([$mapa->fuente->tabla_raiz, $e->cod_registro_fuente, $mapa->campo_valor ?? $mapa->clave_selector]);
        }
        foreach ($evaluacion->evidenciasEvaluacion as $e) {
            foreach ($e->relacionesEvidenciasEvaluacionPorEvidenciaOrigen as $r) {
                if ($r->tipo_relacion !== 'MISMO_EPISODIO_CLINICO') {
                    continue;
                }
                if (! isset($grupos[$r->cod_evidencia_origen], $grupos[$r->cod_evidencia_destino])) {
                    return false;
                }
                $a = $grupos[$r->cod_evidencia_origen];
                $b = $grupos[$r->cod_evidencia_destino];
                $canonico = strcmp($a, $b) <= 0 ? $a : $b;
                foreach ($grupos as &$g) {
                    if ($g === $a || $g === $b) {
                        $g = $canonico;
                    }
                }
                unset($g);
            }
        }
        $conjuntos = [];
        foreach (['DIFICULTAD_EVIDENCIADA', 'SIN_DIFICULTAD_EVIDENCIADA'] as $codigo) {
            $ids = array_merge(...($rutas[$codigo] ?? []));
            $efectivos = array_values(array_unique(array_map(fn ($id) => $grupos[$id], $ids)));
            sort($efectivos, SORT_STRING);
            $conjuntos[] = $efectivos;
        }

        return (bool) $conjuntos[0] && (bool) $conjuntos[1] && $conjuntos[0] !== $conjuntos[1];
    }
}
