<?php

declare(strict_types=1);

namespace MotorExperto\dominio\orion\Services\Inferencia;

use MotorExperto\dominio\orion\Entities\Cognicion\VectorCognitivo;
use MotorExperto\dominio\orion\Entities\Conocimiento\RedSemantica;
use MotorExperto\dominio\orion\Entities\Conocimiento\ReglaExperta;
use MotorExperto\dominio\orion\Entities\Hechos\HechoExperto;
use MotorExperto\dominio\orion\Entities\Hechos\MemoriaTrabajo;
use MotorExperto\dominio\orion\Services\Compuertas\EvaluadorConfusoresService;
use MotorExperto\dominio\orion\Services\Compuertas\VerificadorEvidenciaService;
use MotorExperto\dominio\orion\Services\Semantica\EvaluadorRedSemanticaService;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\DominioCognitivoEnum;
use MotorExperto\dominio\orion\ValueObjects\Conocimiento\EstadoReglaEnum;
use DomainException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Motor de inferencia (Coordinación pura).
 * TÍTULO: Ejecución experimental versionada de cinco dominios.
 * CAPA: Domain.
 * SUBSISTEMA: Inferencia.
 * RESPONSABILIDAD: Coordinar compuertas, participación semántica, reglas y perfil.
 * ENTRADAS: Memoria sintética, reglas, grafo y contratos de participación explícitos.
 * SALIDAS: Vector, reglas realmente cumplidas, trazas y exclusiones reproducibles.
 * RESTRICCIONES: No consulta SQL, modifica memoria ni encadena consecuencias como hechos.
 * Las reglas descriptivas no son referencias externas A/P. No se reescribe el piloto anterior.
 * TRAZABILIDAD: R1–R10; semántica de soportes D-130 y separación descriptiva D-131/G09.
 */
final readonly class MotorInferenciaService
{
    /** Versión del productor técnico, independiente de la versión de conocimiento. */
    public const VERSION = 'ORION-DOMAIN-EXPERIMENTAL-1';

    /**
     * Recibe colaboradores puros sin efectos ni estado por caso.
     *
     * @param  EvaluadorReglasService  $reglas  Evaluador de condiciones, no de puntuaciones.
     * @param  IntegradorDominiosService  $integrador  Integra referencias con compuertas.
     * @param  EvaluadorRedSemanticaService  $semantica  Valida contratos de participación.
     *                                                   No ejecuta inferencia ni produce excepciones durante la construcción.
     */
    public function __construct(
        /** Evaluación lógica trivalente. */ private EvaluadorReglasService $reglas = new EvaluadorReglasService,
        /** Integración por referencias completas. */ private IntegradorDominiosService $integrador = new IntegradorDominiosService,
        /** Contratos del grafo, sin transitividad implícita. */ private EvaluadorRedSemanticaService $semantica = new EvaluadorRedSemanticaService,
    ) {}

    /**
     * Ejecuta una pasada declarativa sin convertir sus consecuencias en nuevas evidencias clínicas.
     *
     * @param  MemoriaTrabajo  $memoria  Caso sintético original.
     * @param  list<ReglaExperta>  $reglas  Reglas únicas de la misma versión, incluso inactivas.
     * @param  RedSemantica  $red  Grafo de la misma versión.
     * @param  array<string, DominioCognitivoEnum>  $dominiosReglas  Adscripción explícita por regla.
     * @param  list<array>  $contratosRelaciones  Contratos tipados requeridos por EvaluadorRedSemanticaService.
     * @param  array<string, ?bool>  $habilitaciones  Habilitación explícita de aristas por ID.
     * @param  list<HechoExperto>  $procedencias  Revisiones originales para todas las candidatas.
     * @param  list<HechoExperto>  $contexto  Revisiones originales COG-*:campo.
     * @return array{perfil: VectorCognitivo, versionMotor: string, reglasPorDominio: array, trazas: array<string, string>, advertencias: array<string, string>, exclusiones: array<string, string>, relacionesUtilizadas: array, evaluacionesReglas: array, compuertas: array}
     *
     * @throws DomainException Por caso real, versión/contrato inválido o soporte reutilizado.
     */
    public function inferir(MemoriaTrabajo $memoria, array $reglas, RedSemantica $red, array $dominiosReglas,
        array $contratosRelaciones = [], array $habilitaciones = [], array $procedencias = [], array $contexto = []): array
    {
        if (! str_starts_with($memoria->evaluacionId, 'SIM-') || strlen($memoria->evaluacionId) <= 4
            || ! str_starts_with($memoria->residenteId, 'SINTETICO-') || strlen($memoria->residenteId) <= 10
            || $red->versionConocimiento !== $memoria->versionConocimiento || ! array_is_list($reglas)) {
            throw new DomainException('El motor solo admite un caso sintético y conocimiento de su versión.');
        }
        $validadas = $this->semantica->validarRed($red, $contratosRelaciones);
        foreach ($habilitaciones as $id => $valor) {
            if (! isset($red->relaciones[$id]) || ($valor !== null && ! is_bool($valor))) {
                throw new DomainException('Habilitación semántica inválida o de una arista inexistente.');
            }
        }
        $indiceReglas = [];
        foreach ($reglas as $regla) {
            if (! $regla instanceof ReglaExperta || $regla->versionConocimiento !== $memoria->versionConocimiento
                || isset($indiceReglas[$regla->id]) || ! ($dominiosReglas[$regla->id] ?? null) instanceof DominioCognitivoEnum) {
                throw new DomainException('Regla duplicada, de otra versión o sin adscripción de dominio explícita.');
            }
            $indiceReglas[$regla->id] = $regla;
        }
        if (array_diff(array_keys($dominiosReglas), array_keys($indiceReglas))) {
            throw new DomainException('Adscripción de una regla que no pertenece al paquete.');
        }
        ksort($indiceReglas, SORT_STRING);
        // Rechazar declaraciones no consumidas evita ignorar silenciosamente otro dominio o procedencia.
        $conceptosContexto = [];
        foreach (DominioCognitivoEnum::cases() as $dominio) {
            foreach (EvaluadorConfusoresService::CAMPOS_CONTEXTO as $campo) {
                $conceptosContexto[] = $dominio->value.':'.$campo;
            }
        }
        $verificador = new VerificadorEvidenciaService;
        $verificador->leerDeclaraciones($memoria, $contexto, $conceptosContexto);
        $verificador->leerDeclaraciones($memoria, $procedencias,
            array_map(static fn (string $id): string => 'VERIFICACION_PROCEDENCIA:'.$id, array_keys($memoria->evidencias)));
        $resultados = $compuertas = $reglasPorDominio = $trazas = $advertencias = $exclusiones = $evaluaciones = $relaciones = [];
        foreach (DominioCognitivoEnum::cases() as $dominio) {
            $candidatas = $memoria->evidenciasParaDominio($dominio);
            $ids = array_map(static fn ($e): string => 'VERIFICACION_PROCEDENCIA:'.$e->hecho->id, $candidatas);
            $p = array_values(array_filter($procedencias, static fn (HechoExperto $h): bool => in_array($h->concepto, $ids, true)));
            $c = array_values(array_filter($contexto, static fn (HechoExperto $h): bool => str_starts_with($h->concepto, $dominio->value.':')));
            $integracion = $this->integrador->integrar($memoria, $dominio, $candidatas, $p, $c);
            $resultados[] = $integracion['resultado'];
            $compuertas[$dominio->value] = $integracion['compuertas'];
            $gate = $integracion['compuertas'];
            $trazas['COMPUERTA:'.$dominio->value] = $integracion['resultado']->justificacion
                .'; faltantes='.implode(',', $gate['faltantes']);
            $reglasPorDominio[$dominio->value] = [];
            foreach ($gate['verificacion']['excluidas'] as $excluida) {
                $exclusiones[$excluida['evidencia']->hecho->id] = implode('; ', $excluida['motivos']);
            }
            if ($gate['motivos'] !== []) {
                $advertencias[$dominio->value] = implode('; ', $gate['motivos']);
            }
            foreach ($gate['verificacion']['dependencias'] as $n => $dependencia) {
                $trazas['DEPENDENCIA:'.$dominio->value.':'.$n] = $dependencia['primera']->hecho->id.' / '
                    .$dependencia['segunda']->hecho->id.': '.implode('; ', $dependencia['motivos']);
            }
            foreach ($indiceReglas as $regla) {
                if ($dominiosReglas[$regla->id] !== $dominio) {
                    continue;
                }
                $vinculos = $this->participacion($regla, $validadas, $habilitaciones);
                if (! $regla->habilitada || $vinculos === null) {
                    $evaluacion = ['regla' => $regla, 'memoria' => $memoria, 'estado' => EstadoReglaEnum::BLOQUEADA,
                        'condiciones' => null, 'soportes' => [], 'consecuencia' => null];
                    $trazas['REGLA:'.$regla->id] = $regla->id.' BLOQUEADA: '
                        .(! $regla->habilitada ? 'REGLA_NO_HABILITADA' : 'PARTICIPACION_SEMANTICA_NO_ACREDITADA');
                } else {
                    $evaluacion = $this->reglas->evaluar($regla, $memoria,
                        array_map(static fn ($e): HechoExperto => $e->hecho, $gate['verificacion']['admitidas']));
                    foreach ($vinculos as $vinculo) {
                        $relaciones[$vinculo->id] = $vinculo;
                    }
                    $trazas['REGLA:'.$regla->id] = $regla->id.' '.$evaluacion['estado']->value.'; fuente='.$regla->fuente
                        .'; versión='.$regla->versionConocimiento.'; '.$this->textoCondicion($evaluacion['condiciones']);
                    if ($evaluacion['estado'] === EstadoReglaEnum::CUMPLE) {
                        $reglasPorDominio[$dominio->value][] = $regla;
                        $trazas['CONSECUENCIA:'.$regla->id] = json_encode($regla->consecuencia, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)
                            .'; salida declarativa, no acredita A/P.';
                    }
                }
                $evaluaciones[$regla->id] = $evaluacion;
            }
        }
        ksort($relaciones, SORT_STRING);

        return ['perfil' => new VectorCognitivo($resultados), 'versionMotor' => self::VERSION,
            'reglasPorDominio' => $reglasPorDominio, 'trazas' => $trazas, 'advertencias' => $advertencias,
            'exclusiones' => $exclusiones, 'relacionesUtilizadas' => array_values($relaciones),
            'evaluacionesReglas' => $evaluaciones, 'compuertas' => $compuertas];
    }

    /**
     * Exige para cada variable un vínculo directo habilitado al concepto de consecuencia.
     *
     * @param  ReglaExperta  $regla  Regla cuyo árbol se inspecciona como datos.
     * @param  array  $validadas  Aristas originales y contratos tipados.
     * @param  array<string, ?bool>  $habilitaciones  Declaraciones de participación.
     * @return ?array Lista de aristas utilizadas, o null ante participación no acreditada.
     *                No crea caminos, invierte predicados ni aprueba relaciones por su sola existencia.
     */
    private function participacion(ReglaExperta $regla, array $validadas, array $habilitaciones): ?array
    {
        $variables = $this->variables($regla->condiciones);
        $usadas = [];
        foreach ($variables as $variable) {
            $encontrada = false;
            foreach ($validadas as $validada) {
                $r = $validada['relacion'];
                if ($r->origen->codigo === $variable && $r->destino->codigo === $regla->consecuencia['concepto']
                    && $validada['contrato']['habilitaParticipacion'] && ($habilitaciones[$r->id] ?? null) === true) {
                    $usadas[$r->id] = $r;
                    $encontrada = true;
                }
            }
            if (! $encontrada) {
                return null;
            }
        }

        return array_values($usadas);
    }

    /** @param array $nodo Árbol validado. @return list<string> Variables únicas, sin ejecutar texto ni lanzar excepciones. */
    private function variables(array $nodo): array
    {
        if (isset($nodo['variable'])) {
            return [$nodo['variable']];
        }
        $variables = [];
        foreach (isset($nodo['condicion']) ? [$nodo['condicion']] : $nodo['condiciones'] as $hija) {
            $variables = [...$variables, ...$this->variables($hija)];
        }

        return array_values(array_unique($variables));
    }

    /** @param array $nodo Condición realmente evaluada. @return string Traza completa con originales; no altera valores ni estados. */
    private function textoCondicion(array $nodo): string
    {
        $fuentes = [];
        foreach ($nodo['soportes'] as $h) {
            $fuentes[] = $h->id.'@'.$h->fuenteId.'/'.$h->origenId.'; episodio='.$h->episodioId
                .'; fecha='.$h->fecha->format('Y-m-d\TH:i:s.uP').'; valor='.json_encode($h->valor, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_UNICODE);
        }
        $ramas = array_map(fn (array $hija): string => $this->textoCondicion($hija), $nodo['ramas']);

        return $nodo['operador']->value.'('.($nodo['variable'] ?? '').'; esperado='
            .json_encode($nodo['esperado'], JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_UNICODE)
            .'; resultado='.match ($nodo['valor']) {
                true => 'CUMPLE', false => 'NO_CUMPLE', null => 'DESCONOCIDO'
            }
        .'; '.$nodo['motivo'].'; soportes=['.implode(' | ', $fuentes).']; ramas=['.implode(' | ', $ramas).'])';
    }
}
