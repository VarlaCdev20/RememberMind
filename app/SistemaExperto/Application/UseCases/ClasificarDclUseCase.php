<?php

declare(strict_types=1);

namespace App\SistemaExperto\Application\UseCases;

use App\SistemaExperto\Application\DTOs\ResultadoDclDTO;
use App\SistemaExperto\Application\DTOs\ResultadoInferenciaDTO;
use App\SistemaExperto\Application\DTOs\SolicitudInferenciaDTO;
use App\SistemaExperto\Domain\Entities\Dcl\PerfilRequisitosDcl;
use App\SistemaExperto\Domain\Entities\Funcionalidad\VectorFuncionalidad;
use App\SistemaExperto\Domain\Services\Dcl\ClasificadorPetersenService;
use App\SistemaExperto\Domain\ValueObjects\Dcl\EstadoCriterioDclEnum;
use App\SistemaExperto\Domain\ValueObjects\Funcionalidad\EstadoDiferencialEnum;
use DomainException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Application (CU-04).
 * TÍTULO: Clasificación experimental según Petersen.
 * CAPA: Application - Casos de Uso.
 * ¿PARA QUÉ SIRVE? Solicita un patrón candidato o abstención a Domain.
 * ¿QUÉ RECIBE? Inferencia, C01–C05, IADL y revisión diferencial explícitas.
 * ¿QUÉ HACE? Compone las entidades originales y delega al clasificador.
 * ¿QUÉ DEVUELVE? ResultadoDclDTO conservando el mismo perfil cognitivo.
 * ¿QUÉ NO DEBE HACER? No acredita C03 desde BADL ni resuelve C04 por síntomas.
 * RELACIÓN CON O.R.I.O.N.: CU-04 y contrato DCL-PET-20261017-v0.1.
 */
final readonly class ClasificarDclUseCase
{
    /**
     * Recibe un clasificador puro; no activa procesos ni persistencia.
     *
     * @param  ClasificadorPetersenService  $clasificador  Selección contractual en Domain.
     *                                                     No clasifica ni lanza excepciones durante la construcción.
     */
    public function __construct(
        /** Selecciona patrón o abstención en Domain. */ private ClasificadorPetersenService $clasificador = new ClasificadorPetersenService,
    ) {}

    /**
     * Transporta declaraciones externas al contrato de Domain, que valida su coherencia.
     *
     * @param  SolicitudInferenciaDTO  $solicitud  Petición original.
     * @param  ResultadoInferenciaDTO  $inferencia  Perfil producido por CU-03.
     * @param  array  $criterios  Cinco estados explícitos C01–C05.
     * @param  array  $soportes  Hechos originales por criterio, no IDs inventados.
     * @param  array  $justificaciones  Razones de cada requisito.
     * @param  EstadoDiferencialEnum  $diferencial  Revisión externa declarada.
     * @param  EstadoCriterioDclEnum  $independenciaRelativa  Revisión funcional declarada.
     * @param  array  $evidenciasIadl  Evidencias originales con oportunidad y nivel previo.
     * @param  string|null  $revisorSinteticoId  Revisor ficticio de funcionalidad.
     * @param  string  $justificacionFuncional  Razón funcional explícita.
     * @param  string  $versionContrato  Contrato experimental exacto.
     * @return ResultadoDclDTO Resultado original y contexto conservados.
     *
     * @throws DomainException Por peticiones mezcladas o invariantes de Domain incumplidas.
     */
    public function ejecutar(SolicitudInferenciaDTO $solicitud, ResultadoInferenciaDTO $inferencia,
        array $criterios, array $soportes, array $justificaciones, EstadoDiferencialEnum $diferencial,
        EstadoCriterioDclEnum $independenciaRelativa, array $evidenciasIadl, ?string $revisorSinteticoId,
        string $justificacionFuncional, string $versionContrato = PerfilRequisitosDcl::VERSION_CONTRATO): ResultadoDclDTO
    {
        if ($inferencia->solicitud != $solicitud) {
            throw new DomainException('La inferencia DCL pertenece a otra solicitud.');
        }
        $solicitud->exigirContexto($inferencia->perfilCognitivo->memoria);
        $funcionalidad = new VectorFuncionalidad($inferencia->perfilCognitivo->memoria, $independenciaRelativa,
            $evidenciasIadl, $revisorSinteticoId, $justificacionFuncional);
        $perfil = new PerfilRequisitosDcl($inferencia->perfilCognitivo, $funcionalidad, $diferencial,
            $criterios, $soportes, $justificaciones, $versionContrato);

        return new ResultadoDclDTO($solicitud, $this->clasificador->clasificar($perfil));
    }
}
