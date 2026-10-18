<?php

declare(strict_types=1);

namespace App\SistemaExperto\Application\UseCases;

use App\SistemaExperto\Application\DTOs\InformeExpertoDTO;
use App\SistemaExperto\Application\DTOs\SolicitudInferenciaDTO;
use App\SistemaExperto\Domain\ValueObjects\Dcl\EstadoCriterioDclEnum;
use App\SistemaExperto\Domain\ValueObjects\Funcionalidad\EstadoDiferencialEnum;
use DomainException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Application (CU-06, coordinación).
 * TÍTULO: Análisis secuencial de un caso experimental.
 * CAPA: Application - Casos de Uso.
 * ¿PARA QUÉ SIRVE? Ejecuta los cinco casos individuales sobre la misma petición.
 * ¿QUÉ RECIBE? Solicitud y declaraciones sintéticas explícitas, sin datos clínicos reales.
 * ¿QUÉ HACE? Extrae, prepara memoria, infiere, clasifica y solicita el informe, en ese orden.
 * ¿QUÉ DEVUELVE? InformeExpertoDTO íntegro con fuentes y motivos de abstención.
 * ¿QUÉ NO DEBE HACER? No interpreta datos, acredita requisitos, guarda parcialmente ni oculta errores.
 * RELACIÓN CON O.R.I.O.N.: CU-06; ningún paso habilita activación asistencial.
 */
final readonly class AnalizarCasoExperimentalUseCase
{
    /**
     * Conecta exactamente los cinco casos; no ejecuta trabajo durante la construcción.
     *
     * @param  ExtraerHechosUseCase  $extraer  CU-01, adquisición mediante un puerto de Domain.
     * @param  PrepararMemoriaTrabajoUseCase  $preparar  CU-02, preparación sin reinterpretación.
     * @param  EjecutarInferenciaUseCase  $inferir  CU-03, ejecución real del motor.
     * @param  ClasificarDclUseCase  $clasificar  CU-04, patrón candidato o abstención.
     * @param  GenerarInformeUseCase  $informar  CU-05, explicación producida por Domain.
     *                                           No consulta fuentes ni lanza excepciones durante la construcción.
     */
    public function __construct(
        /** CU-01: recuperación por puerto de Domain. */ private ExtraerHechosUseCase $extraer,
        /** CU-02: memoria original preparada. */ private PrepararMemoriaTrabajoUseCase $preparar,
        /** CU-03: ejecución pura con conocimiento versionado. */ private EjecutarInferenciaUseCase $inferir,
        /** CU-04: patrón candidato o abstención. */ private ClasificarDclUseCase $clasificar,
        /** CU-05: explicación producida por Domain. */ private GenerarInformeUseCase $informar,
    ) {}

    /**
     * Propaga cualquier fallo y no ejecuta etapas posteriores después de un error.
     * Los objetos de soporte/IADL conservan sus hechos originales al preparar otra memoria.
     *
     * @param  SolicitudInferenciaDTO  $solicitud  Contexto sintético original.
     * @param  array{criterios: array, soportes: array, justificaciones: array, diferencial: EstadoDiferencialEnum, independenciaRelativa: EstadoCriterioDclEnum, evidenciasIadl: array, revisorSinteticoId: ?string, justificacionFuncional: string, versionContrato?: string}  $entradaDcl  Declaraciones externas; pendientes deben ser explícitos.
     * @param  array  $dominiosReglas  Adscripción original de reglas.
     * @param  array  $contratosRelaciones  Contratos originales de aristas.
     * @param  array  $habilitaciones  Habilitaciones técnicas de aristas.
     * @param  array  $procedencias  Hechos originales de procedencia.
     * @param  array  $contexto  Hechos originales de revisión contextual.
     * @return InformeExpertoDTO Resultado de los cinco pasos reales.
     *
     * @throws DomainException Por forma de entrada, mezcla de contexto o invariantes de Domain.
     * @throws \RuntimeException Por fallo técnico de un repositorio, sin éxito ficticio.
     */
    public function ejecutar(SolicitudInferenciaDTO $solicitud, array $entradaDcl, array $dominiosReglas,
        array $contratosRelaciones = [], array $habilitaciones = [], array $procedencias = [], array $contexto = []): InformeExpertoDTO
    {
        $obligatorias = ['criterios', 'soportes', 'justificaciones', 'diferencial', 'independenciaRelativa',
            'evidenciasIadl', 'revisorSinteticoId', 'justificacionFuncional'];
        if (array_diff($obligatorias, array_keys($entradaDcl))
            || array_diff(array_keys($entradaDcl), [...$obligatorias, 'versionContrato'])) {
            throw new DomainException('El coordinador requiere declaraciones DCL explícitas con forma cerrada.');
        }
        $entrada = $this->extraer->ejecutar($solicitud);
        $memoria = $this->preparar->ejecutar($solicitud, $entrada);
        $inferencia = $this->inferir->ejecutar($solicitud, $memoria, $dominiosReglas,
            $contratosRelaciones, $habilitaciones, $procedencias, $contexto);
        $dcl = $this->clasificar->ejecutar($solicitud, $inferencia, ...$entradaDcl);

        return $this->informar->desdeResultados($solicitud, $inferencia, $dcl);
    }
}
