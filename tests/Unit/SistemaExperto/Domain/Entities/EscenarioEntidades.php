<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Domain\Entities;

use App\SistemaExperto\Domain\Entities\Cognicion\ResultadoDominio;
use App\SistemaExperto\Domain\Entities\Cognicion\VectorCognitivo;
use App\SistemaExperto\Domain\Entities\Dcl\PerfilRequisitosDcl;
use App\SistemaExperto\Domain\Entities\Funcionalidad\EvidenciaIadl;
use App\SistemaExperto\Domain\Entities\Funcionalidad\VectorFuncionalidad;
use App\SistemaExperto\Domain\Entities\Hechos\EvidenciaCognitiva;
use App\SistemaExperto\Domain\Entities\Hechos\HechoExperto;
use App\SistemaExperto\Domain\Entities\Hechos\MemoriaTrabajo;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\DominioCognitivoEnum;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\EstadoDominioEnum;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\EvaluabilidadEnum;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\HallazgoInstrumentalEnum;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\SubcomponenteCognitivoEnum;
use App\SistemaExperto\Domain\ValueObjects\Dcl\CriterioDclEnum;
use App\SistemaExperto\Domain\ValueObjects\Dcl\EstadoCriterioDclEnum;
use App\SistemaExperto\Domain\ValueObjects\Funcionalidad\EstadoDiferencialEnum;
use App\SistemaExperto\Domain\ValueObjects\Funcionalidad\EstadoIadlEnum;
use App\SistemaExperto\Domain\ValueObjects\Funcionalidad\OportunidadIadlEnum;
use DateTimeImmutable;

/**
 * TÍTULO: Escenarios ficticios para constructores de entidades.
 *
 * CAPA: Domain (verificación unitaria).
 *
 * SUBSISTEMA: Fixtures sintéticas.
 *
 * RESPONSABILIDAD:
 * Construir referencias manuales para verificar invariantes, sin interpretar datos.
 *
 * ENTRADAS:
 * Valores explícitos SIM y SINTETICO, con cambios controlados para negativos.
 *
 * SALIDAS:
 * Objetos reales del Domain sin mocks ni dependencias de Laravel.
 *
 * RESTRICCIONES:
 * Los instrumentos, normas y revisores son inventados para esta prueba técnica.
 * No reproducen instrumentos ni acreditan aptitud clínica.
 *
 * TRAZABILIDAD:
 * Reglamento maestro R1–R10 y contrato experimental DCL-PET.
 */
final class EscenarioEntidades
{
    /**
     * Entrega una fecha fija, independiente del reloj o la zona del proceso.
     *
     * @return DateTimeImmutable Corte inclusivo del escenario.
     *
     * No recibe parámetros; no se esperan excepciones con el literal fijo.
     */
    public static function fecha(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-10-17T12:00:00-04:00');
    }

    /**
     * Construye un hecho original con overrides explícitos para casos negativos.
     *
     * @param  array<string, mixed>  $cambios  Parámetros nombrados del constructor.
     * @return HechoExperto Hecho artificial.
     *
     * @throws \DomainException Cuando el override reproduce una entrada inválida.
     */
    public static function hecho(array $cambios = []): HechoExperto
    {
        return new HechoExperto(...array_replace([
            'id' => 'SIM-H-1', 'evaluacionId' => 'SIM-EVAL', 'residenteId' => 'SINTETICO-RES',
            'episodioId' => 'SIM-EP-1', 'fuenteId' => 'OBS_SINTETICA', 'origenId' => 'SIM-OR-1',
            'versionConocimiento' => 'SIM-V1', 'concepto' => 'OBS', 'fecha' => self::fecha(), 'valor' => 'REGISTRO_FICTICIO',
        ], $cambios));
    }

    /**
     * Declara evidencia ficticia, con referencias externas solo para los dos SIM.
     *
     * @param  string  $id  Identidad del hecho.
     * @param  DominioCognitivoEnum  $dominio  Eje propietario.
     * @param  HallazgoInstrumentalEnum  $hallazgo  Descriptor explícito.
     * @param  SubcomponenteCognitivoEnum|null  $subcomponente  Alcance específico opcional.
     * @param  array<string, mixed>  $cambiosHecho  Overrides de procedencia y contexto.
     * @param  string  $justificacion  Motivo explícito de la evidencia ficticia, sin interpretación automática.
     * @return EvidenciaCognitiva Evidencia original.
     *
     * @throws \DomainException Si el caso solicitado viola un invariante.
     */
    public static function evidencia(
        string $id = 'SIM-EV-MEM',
        DominioCognitivoEnum $dominio = DominioCognitivoEnum::MEMORIA,
        HallazgoInstrumentalEnum $hallazgo = HallazgoInstrumentalEnum::HALLAZGO_DESCRIPTIVO,
        ?SubcomponenteCognitivoEnum $subcomponente = null,
        array $cambiosHecho = [],
        string $justificacion = 'Referencia completamente ficticia.',
    ): EvidenciaCognitiva {
        $sim = in_array($hallazgo, [HallazgoInstrumentalEnum::ALTERACION_OBJETIVA_SIMULADA, HallazgoInstrumentalEnum::RENDIMIENTO_ESPERADO_SIMULADO], true);
        $hecho = self::hecho(array_replace([
            'id' => $id, 'episodioId' => 'SIM-EP-'.$id, 'origenId' => 'SIM-OR-'.$id,
            'fuenteId' => $sim ? 'REFERENCIA_EXTERNA_SINTETICA' : 'OBS_SINTETICA',
            'concepto' => $subcomponente?->value ?? $dominio->value, 'valor' => $hallazgo->value,
        ], $cambiosHecho));
        $instrumento = 'SIM-INSTR-'.$id;
        $referencia = $sim ? [
            'instrumento' => $instrumento, 'edicion' => 'SIM-EDICION', 'subprueba' => 'SIM-TAREA',
            'norma' => 'SIM-NORMA', 'idioma' => 'SIM-IDIOMA', 'poblacion' => 'SIM-POBLACION', 'revisor' => 'SIM-REVISOR',
        ] : [];

        return new EvidenciaCognitiva($hecho, $dominio, $subcomponente, $hallazgo, $instrumento, $justificacion, $referencia);
    }

    /**
     * Construye una memoria del caso fijo con hechos adicionales explícitos.
     *
     * @param  list<EvidenciaCognitiva>  $evidencias  Evidencias originales.
     * @param  list<HechoExperto>  $hechos  Hechos adicionales sin duplicar los anteriores.
     * @return MemoriaTrabajo Instantánea real del Domain.
     *
     * @throws \DomainException Por contexto o duplicación inválidos.
     */
    public static function memoria(array $evidencias = [], array $hechos = []): MemoriaTrabajo
    {
        return new MemoriaTrabajo('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::fecha(),
            [...$hechos, ...array_map(static fn (EvidenciaCognitiva $e): HechoExperto => $e->hecho, $evidencias)], $evidencias);
    }

    /**
     * Construye una IADL ficticiamente revisada, nunca una medición real.
     *
     * @param  array<string, mixed>  $cambios  Parámetros nombrados para escenarios negativos.
     * @return EvidenciaIadl Evidencia contextual.
     *
     * @throws \DomainException Si la variante viola oportunidad o revisión.
     */
    public static function iadl(array $cambios = []): EvidenciaIadl
    {
        return new EvidenciaIadl(...array_replace([
            'hecho' => self::hecho(['id' => 'SIM-IADL', 'origenId' => 'SIM-OR-IADL',
                'episodioId' => 'SIM-EP-IADL', 'fuenteId' => 'REFERENCIA_IADL_SINTETICA', 'concepto' => 'SIM-ACTIVIDAD']),
            'actividadId' => 'SIM-ACTIVIDAD', 'estado' => EstadoIadlEnum::INDEPENDIENTE,
            'oportunidad' => OportunidadIadlEnum::REAL_Y_ADECUADA, 'nivelPrevio' => EstadoIadlEnum::INDEPENDIENTE,
            'causaAyuda' => null, 'atribucionCognitivaSimulada' => false, 'revisorSinteticoId' => 'SIM-REVISOR',
        ], $cambios));
    }

    /**
     * Prepara cinco dominios y C01–C05 desde declaraciones sintéticas manuales.
     *
     * Los estados nunca se calculan a partir de puntuaciones o reglas G09.
     *
     * @param  list<string>  $estados  Cinco estados explícitos A/P/U/I/X.
     * @param  array<string, EstadoCriterioDclEnum>  $cambiosCriterios  Requisitos sobrescritos.
     * @param  EstadoDiferencialEnum  $diferencial  Revisión diferencial declarada.
     * @param  EstadoCriterioDclEnum  $independencia  Declaración funcional ficticia.
     * @param  array<string, string>  $razonesEvidencia  Motivos originales por dominio para verificar su explicación.
     * @return PerfilRequisitosDcl Perfil completo con fuentes conservadas.
     *
     * @throws \DomainException Por variante deliberadamente incoherente.
     */
    public static function perfil(
        array $estados = ['A', 'P', 'P', 'P', 'P'],
        array $cambiosCriterios = [],
        EstadoDiferencialEnum $diferencial = EstadoDiferencialEnum::COMPLETADO_SIN_IMPEDIMENTO,
        EstadoCriterioDclEnum $independencia = EstadoCriterioDclEnum::ACREDITADO,
        array $razonesEvidencia = [],
    ): PerfilRequisitosDcl {
        $evidencias = $idsPorDominio = [];
        foreach (DominioCognitivoEnum::cases() as $i => $dominio) {
            $estado = EstadoDominioEnum::from($estados[$i]);
            $hallazgos = match ($estado) {
                EstadoDominioEnum::ALTERACION => [HallazgoInstrumentalEnum::ALTERACION_OBJETIVA_SIMULADA],
                EstadoDominioEnum::RENDIMIENTO_ESPERADO => [HallazgoInstrumentalEnum::RENDIMIENTO_ESPERADO_SIMULADO],
                EstadoDominioEnum::CONTRADICTORIO => [HallazgoInstrumentalEnum::ALTERACION_OBJETIVA_SIMULADA, HallazgoInstrumentalEnum::RENDIMIENTO_ESPERADO_SIMULADO],
                EstadoDominioEnum::NO_INTERPRETABLE => [HallazgoInstrumentalEnum::NO_INTERPRETABLE],
                EstadoDominioEnum::DESCONOCIDO => [],
            };
            $idsPorDominio[$dominio->value] = [];
            foreach ($hallazgos as $n => $hallazgo) {
                $e = self::evidencia('SIM-EV-'.$dominio->value.'-'.$n, $dominio, $hallazgo,
                    justificacion: $razonesEvidencia[$dominio->value] ?? 'Referencia completamente ficticia.');
                $evidencias[] = $e;
                $idsPorDominio[$dominio->value][] = $e->hecho->id;
            }
        }
        $iadl = $independencia === EstadoCriterioDclEnum::NO_ACREDITADO
            ? self::iadl(['estado' => EstadoIadlEnum::CON_AYUDA, 'causaAyuda' => 'Atribución exclusivamente ficticia.', 'atribucionCognitivaSimulada' => true])
            : self::iadl();
        $criterios = $soportes = $razones = [];
        $hechos = [$iadl->hecho];
        foreach (CriterioDclEnum::cases() as $criterio) {
            $estado = $cambiosCriterios[$criterio->value] ?? EstadoCriterioDclEnum::ACREDITADO;
            $criterios[$criterio->value] = $estado;
            $razones[$criterio->value] = 'Declaración artificial independiente.';
            $hecho = self::hecho(['id' => 'SIM-'.$criterio->value, 'origenId' => 'SIM-OR-'.$criterio->value,
                'episodioId' => 'SIM-EP-REVISION', 'fuenteId' => 'REFERENCIA_CRITERIO_SINTETICA',
                'concepto' => $criterio->value, 'valor' => $estado->value]);
            $hechos[] = $hecho;
            $soportes[$criterio->value] = in_array($estado, [EstadoCriterioDclEnum::ACREDITADO, EstadoCriterioDclEnum::NO_ACREDITADO], true) ? [$hecho] : [];
        }
        $memoria = self::memoria($evidencias, $hechos);
        $resultados = [];
        foreach (DominioCognitivoEnum::cases() as $i => $dominio) {
            $estado = EstadoDominioEnum::from($estados[$i]);
            $resultados[] = new ResultadoDominio($dominio, $estado,
                $estado === EstadoDominioEnum::DESCONOCIDO ? EvaluabilidadEnum::NO_EVALUABLE : EvaluabilidadEnum::EVALUACION_INTEGRADA,
                $memoria, [], $idsPorDominio[$dominio->value], 'Estado ficticio declarado, sin cálculo.');
        }
        $vector = new VectorCognitivo($resultados);
        $funcional = new VectorFuncionalidad($memoria, $independencia, [$iadl], 'SIM-REVISOR', 'Revisión artificial, no clínica.');

        return new PerfilRequisitosDcl($vector, $funcional, $diferencial, $criterios, $soportes, $razones);
    }
}
