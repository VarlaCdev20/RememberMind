<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Application\DTOs;

use App\Modulos\ApoyoDecision\Aplicacion\DTOs\InformeExpertoDTO;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\ResultadoDclDTO;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\ResultadoInferenciaDTO;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\SolicitudInferenciaDTO;
use MotorExperto\dominio\orion\Entities\Cognicion\VectorCognitivo;
use MotorExperto\dominio\orion\Entities\Conocimiento\NodoSemantico;
use MotorExperto\dominio\orion\Entities\Conocimiento\RelacionSemantica;
use MotorExperto\dominio\orion\Entities\Dcl\ResultadoClasificacionDcl;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\DominioCognitivoEnum;
use MotorExperto\dominio\orion\ValueObjects\Conocimiento\TipoRelacionSemanticaEnum;
use MotorExperto\dominio\orion\ValueObjects\Dcl\MotivoAbstencionEnum;
use DomainException;
use Error;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades as F;

/**
 * TÍTULO: Informe estructurado y trazabilidad del contexto.
 * CAPA: Application (pruebas unitarias).
 * ¿PARA QUÉ SIRVE? Comprueba los tres niveles, las versiones y la advertencia.
 * ¿QUÉ RECIBE? Explicaciones manuales y resultados ficticios de Domain.
 * ¿QUÉ HACE? Construye el informe sin ejecutar un generador de explicación.
 * ¿QUÉ DEVUELVE? Aserciones de conservación, inmutabilidad y rechazo.
 * ¿QUÉ NO DEBE HACER? No acredita inferencia, diagnóstico ni integración Laravel.
 * RELACIÓN CON O.R.I.O.N.: Reglamento de Application, DTO 05 y etapa B.
 */
final class InformeExpertoDTOTest extends TestCase
{
    /** Conserva las tres secciones sin fabricar Petersen; sin parámetros ni excepciones esperadas. @return void */
    public function test_informe_sin_dcl_conserva_textos_y_advertencia_obligatoria(): void
    {
        $inferencia = self::inferencia(F::perfil(['U', 'U', 'U', 'U', 'U'])->cognicion);
        $dto = new InformeExpertoDTO($inferencia, null, '  Resumen recibido.  ',
            'Perfil ficticio desconocido.', 'Fundamento recibido con limitaciones.');

        self::assertSame('  Resumen recibido.  ', $dto->resumen);
        self::assertSame('Perfil ficticio desconocido.', $dto->interpretacion);
        self::assertSame('Fundamento recibido con limitaciones.', $dto->fundamento);
        self::assertSame($inferencia, $dto->inferencia);
        self::assertSame($inferencia->solicitud, $dto->solicitud);
        self::assertNull($dto->dcl);
        self::assertSame(['conocimiento' => 'SIM-V1', 'motor' => 'SIM-MOTOR-V1'], $dto->versiones);
        self::assertSame('SIMULACIÓN ACADÉMICA — SIN VALIDEZ DIAGNÓSTICA — NO UTILIZAR PARA DECISIONES ASISTENCIALES.', $dto->advertencia);
    }

    /** Conserva abstención, relaciones y exclusiones originales; sin parámetros ni excepciones esperadas. @return void */
    public function test_informe_con_abstencion_conserva_toda_la_trazabilidad(): void
    {
        $perfil = F::perfil(['U', 'P', 'P', 'P', 'P']);
        $inferencia = self::inferencia($perfil->cognicion);
        $resultado = new ResultadoClasificacionDcl($perfil, null,
            [MotivoAbstencionEnum::ABSTENCION_INFORMACION_INSUFICIENTE], 'Faltan soportes ficticios interpretables.');
        $dcl = new ResultadoDclDTO($inferencia->solicitud, $resultado);
        $relacion = self::relacion();
        $razon = 'Exclusión original recibida.';
        $dto = new InformeExpertoDTO($inferencia, $dcl, 'Caso ficticio.', 'Abstención declarada.',
            $resultado->justificacion, [$relacion], ['SIM-EXCLUIDA' => &$razon]);
        $razon = 'Modificada.';

        self::assertSame($resultado, $dto->dcl->resultado);
        self::assertTrue($dto->dcl->abstencion);
        self::assertNull($dto->dcl->patronCandidato);
        self::assertSame($inferencia->trazas, $dto->inferencia->trazas);
        self::assertSame([$relacion], $dto->relacionesUtilizadas);
        self::assertSame('Exclusión original recibida.', $dto->exclusiones['SIM-EXCLUIDA']);
        self::assertSame($perfil->versionContrato, $dto->versiones['contratoPetersen']);
        self::assertSame($perfil->cognicion->memoria, $dto->inferencia->perfilCognitivo->memoria);
    }

    /**
     * Impide un informe con uno de sus niveles obligatorio vacío.
     *
     * @param  string  $seccion  Nombre del nivel incompleto.
     *
     * @throws DomainException Rechazo esperado sin generar un texto sustitutivo.
     */
    #[DataProvider('seccionesObligatorias')]
    public function test_rechaza_seccion_vacia(string $seccion): void
    {
        $textos = ['resumen' => 'Resumen recibido.', 'interpretacion' => 'Interpretación recibida.', 'fundamento' => 'Fundamento recibido.'];
        $textos[$seccion] = '   ';
        $inferencia = self::inferencia(F::perfil()->cognicion);
        $this->expectException(DomainException::class);
        new InformeExpertoDTO($inferencia, null, ...$textos);
    }

    /** @return iterable<string, array{string}> Niveles obligatorios; sin parámetros ni excepciones. */
    public static function seccionesObligatorias(): iterable
    {
        yield 'nivel 1' => ['resumen'];
        yield 'nivel 2' => ['interpretacion'];
        yield 'nivel 3' => ['fundamento'];
    }

    /** @return void @throws DomainException Rechazo esperado al mezclar perfiles de distinta identidad. */
    public function test_rechaza_dcl_de_otra_instantanea_aunque_el_contexto_coincida(): void
    {
        $inferencia = self::inferencia(F::perfil()->cognicion);
        $otra = F::perfil();
        $dcl = new ResultadoDclDTO($inferencia->solicitud, new ResultadoClasificacionDcl($otra, null,
            [MotivoAbstencionEnum::SOLO_LABORATORIO_NO_EMITIR], 'Salida declarada para otro perfil.'));

        $this->expectException(DomainException::class);
        new InformeExpertoDTO($inferencia, $dcl, 'Resumen.', 'Interpretación.', 'Fundamento.');
    }

    /** @return void @throws DomainException Rechazo esperado al combinar peticiones diferentes. */
    public function test_rechaza_resultados_con_distinto_identificador_de_solicitud(): void
    {
        $perfil = F::perfil();
        $inferencia = self::inferencia($perfil->cognicion);
        $otraSolicitud = new SolicitudInferenciaDTO('SIM-SOL-OTRA', 'SIM-EVAL', 'SINTETICO-RES', F::fecha(), 'SIM-V1');
        $dcl = new ResultadoDclDTO($otraSolicitud, new ResultadoClasificacionDcl($perfil, null,
            [MotivoAbstencionEnum::SOLO_LABORATORIO_NO_EMITIR], 'Salida de otra petición.'));

        $this->expectException(DomainException::class);
        new InformeExpertoDTO($inferencia, $dcl, 'Resumen.', 'Interpretación.', 'Fundamento.');
    }

    /**
     * Rechaza referencias de explicación sin identidad o versión compatible.
     *
     * @param  string  $caso  Error artificial de la lista de relaciones.
     *
     * @throws DomainException Rechazo estructural esperado sin evaluar la red.
     */
    #[DataProvider('relacionesInvalidas')]
    public function test_rechaza_relaciones_incompatibles(string $caso): void
    {
        $relacion = self::relacion();
        $relaciones = match ($caso) {
            'duplicada' => [$relacion, $relacion],
            'otra versión' => [self::relacion('SIM-V2')],
            'sin tipar' => ['SIM-REL'],
            'sin lista' => ['SIM-REL' => $relacion],
        };
        $inferencia = self::inferencia(F::perfil()->cognicion);
        $this->expectException(DomainException::class);
        new InformeExpertoDTO($inferencia, null, 'Resumen.', 'Interpretación.', 'Fundamento.', $relaciones);
    }

    /** @return iterable<string, array{string}> Relaciones inválidas; sin parámetros ni excepciones. */
    public static function relacionesInvalidas(): iterable
    {
        foreach (['duplicada', 'otra versión', 'sin tipar', 'sin lista'] as $caso) {
            yield $caso => [$caso];
        }
    }

    /** @return void @throws DomainException Rechazo esperado de una exclusión sin motivo. */
    public function test_rechaza_exclusion_sin_razon(): void
    {
        $inferencia = self::inferencia(F::perfil()->cognicion);
        $this->expectException(DomainException::class);
        new InformeExpertoDTO($inferencia, null, 'Resumen.', 'Interpretación.', 'Fundamento.', [], ['SIM-EXCLUIDA' => ' ']);
    }

    /** @return void @throws Error Rechazo esperado al intentar retirar la advertencia. */
    public function test_advertencia_no_puede_suprimirse(): void
    {
        $dto = new InformeExpertoDTO(self::inferencia(F::perfil()->cognicion), null, 'Resumen.', 'Interpretación.', 'Fundamento.');
        $this->expectException(Error::class);
        $dto->advertencia = '';
    }

    /**
     * Envuelve un perfil manual, sin simular una ejecución del motor.
     *
     * @param  VectorCognitivo  $perfil  Perfil sintético original.
     * @return ResultadoInferenciaDTO Resultado de transporte para el escenario.
     *                                Sin excepciones esperadas con el contexto fijo.
     */
    private static function inferencia(VectorCognitivo $perfil): ResultadoInferenciaDTO
    {
        $solicitud = new SolicitudInferenciaDTO('SIM-SOL', 'SIM-EVAL', 'SINTETICO-RES', F::fecha(), 'SIM-V1');

        return new ResultadoInferenciaDTO($solicitud, $perfil, 'SIM-MOTOR-V1',
            array_fill_keys(array_column(DominioCognitivoEnum::cases(), 'value'), []),
            ['SIM-TRAZA' => 'Texto manual de transporte; no acredita ejecución.']);
    }

    /**
     * Declara una relación semántica artificial, sin inventar enlaces inferidos.
     *
     * @param  string  $version  Versión propietaria de los nodos y de la arista.
     * @return RelacionSemantica Relación original del escenario.
     *                           Sin excepciones esperadas con las identidades fijas.
     */
    private static function relacion(string $version = 'SIM-V1'): RelacionSemantica
    {
        $a = new NodoSemantico('SIM-N-A', $version, 'SIM-C-A', 'Concepto A', 'CONCEPTO', 'Definición ficticia A.');
        $b = new NodoSemantico('SIM-N-B', $version, 'SIM-C-B', 'Concepto B', 'CONCEPTO', 'Definición ficticia B.');

        return new RelacionSemantica('SIM-REL', $a, TipoRelacionSemanticaEnum::RELACIONADA_CON, $b);
    }
}
