<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Application\UseCases;

use App\Modulos\ApoyoDecision\Aplicacion\DTOs\InformeExpertoDTO;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\ResultadoDclDTO;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\ResultadoInferenciaDTO;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\SolicitudInferenciaDTO;
use App\Modulos\ApoyoDecision\Aplicacion\CasosDeUso\GenerarInformeUseCase;
use MotorExperto\dominio\orion\Entities\Conocimiento\NodoSemantico;
use MotorExperto\dominio\orion\Entities\Conocimiento\RelacionSemantica;
use MotorExperto\dominio\orion\Entities\Dcl\PerfilRequisitosDcl;
use MotorExperto\dominio\orion\Entities\Dcl\ResultadoClasificacionDcl;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\DominioCognitivoEnum;
use MotorExperto\dominio\orion\ValueObjects\Conocimiento\TipoRelacionSemanticaEnum;
use MotorExperto\dominio\orion\ValueObjects\Dcl\MotivoAbstencionEnum;
use MotorExperto\dominio\orion\ValueObjects\Dcl\SubtipoDclEnum;
use DomainException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades as F;

/**
 * TÍTULO: Ensamblaje de explicaciones y resultados recibidos.
 * CAPA: Application (pruebas unitarias).
 * ¿PARA QUÉ SIRVE? Verifica que CU-05 conserve el contenido y el contexto originales.
 * ¿QUÉ RECIBE? Resultados y explicaciones manuales exclusivamente sintéticos.
 * ¿QUÉ HACE? Ejecuta la modalidad de recepción de explicaciones del caso de uso.
 * ¿QUÉ DEVUELVE? Aserciones sobre el informe y el rechazo de entradas incompatibles.
 * ¿QUÉ NO DEBE HACER? No acredita generación automática de explicación ni clasificación.
 * RELACIÓN CON O.R.I.O.N.: Reglamento de Application, CU-05 y etapa C.
 */
final class GenerarInformeUseCaseTest extends TestCase
{
    /** Conserva un candidato ya declarado y los tres textos recibidos; sin parámetros ni excepciones esperadas. */
    public function test_ensambla_informe_preservando_candidato_textos_versiones_y_trazas(): void
    {
        $perfil = F::perfil();
        $inferencia = self::inferencia($perfil);
        $original = new ResultadoClasificacionDcl($perfil, SubtipoDclEnum::AMNESICO_DOMINIO_UNICO,
            [], 'Candidato declarado manualmente para probar el transporte.');
        $dcl = new ResultadoDclDTO($inferencia->solicitud, $original);
        $exclusion = 'Razón original de exclusión.';

        $informe = (new GenerarInformeUseCase)->ejecutar($inferencia->solicitud, $inferencia, $dcl,
            ' Resumen recibido. ', 'Interpretación recibida.', 'Fundamento recibido.',
            [], ['SIM-EXCLUIDA' => &$exclusion]);
        $exclusion = 'Texto modificado posteriormente.';

        self::assertSame($inferencia, $informe->inferencia);
        self::assertSame($dcl, $informe->dcl);
        self::assertSame($original, $informe->dcl->resultado);
        self::assertSame(SubtipoDclEnum::AMNESICO_DOMINIO_UNICO, $informe->dcl->patronCandidato);
        self::assertSame(' Resumen recibido. ', $informe->resumen);
        self::assertSame('Interpretación recibida.', $informe->interpretacion);
        self::assertSame('Fundamento recibido.', $informe->fundamento);
        self::assertSame($inferencia->trazas, $informe->inferencia->trazas);
        self::assertSame('Razón original de exclusión.', $informe->exclusiones['SIM-EXCLUIDA']);
        self::assertSame(['conocimiento' => 'SIM-V1', 'motor' => 'SIM-MOTOR-V1',
            'contratoPetersen' => $perfil->versionContrato], $informe->versiones);
        self::assertSame(InformeExpertoDTO::ADVERTENCIA_EXPERIMENTAL, $informe->advertencia);
    }

    /** Conserva la abstención sin añadir un patrón; sin parámetros ni excepciones esperadas. */
    public function test_informe_de_abstencion_conserva_motivos_y_limitaciones(): void
    {
        $perfil = F::perfil(['U', 'P', 'P', 'P', 'P']);
        $inferencia = self::inferencia($perfil);
        $motivos = [MotivoAbstencionEnum::ABSTENCION_INFORMACION_INSUFICIENTE];
        $original = new ResultadoClasificacionDcl($perfil, null, $motivos, 'Información ficticia insuficiente.');
        $dcl = new ResultadoDclDTO($inferencia->solicitud, $original);

        $informe = (new GenerarInformeUseCase)->ejecutar($inferencia->solicitud, $inferencia, $dcl,
            'Resumen manual del caso.', 'Abstención recibida.', $original->justificacion);

        self::assertTrue($informe->dcl->abstencion);
        self::assertNull($informe->dcl->patronCandidato);
        self::assertSame($motivos, $informe->dcl->motivos);
        self::assertSame($original->justificacion, $informe->fundamento);
        self::assertSame($perfil->soportes, $informe->dcl->soportes);
    }

    /** La ausencia de Petersen permanece explícita; sin parámetros ni excepciones esperadas. */
    public function test_informe_sin_clasificacion_no_sintetiza_un_resultado_dcl(): void
    {
        $inferencia = self::inferencia(F::perfil(['U', 'U', 'U', 'U', 'U']));

        $informe = (new GenerarInformeUseCase)->ejecutar($inferencia->solicitud, $inferencia, null,
            'Caso ficticio sin datos interpretables.', 'Perfil desconocido recibido.', 'Limitaciones originales recibidas.');

        self::assertNull($informe->dcl);
        self::assertArrayNotHasKey('contratoPetersen', $informe->versiones);
        self::assertSame($inferencia->perfilCognitivo, $informe->inferencia->perfilCognitivo);
    }

    /** @throws DomainException Rechazo esperado al cambiar la identidad de la petición original. */
    public function test_rechaza_resultado_de_otra_solicitud(): void
    {
        $inferencia = self::inferencia(F::perfil());
        $otra = new SolicitudInferenciaDTO('SIM-OTRA-SOL', 'SIM-EVAL', 'SINTETICO-RES', F::fecha(), 'SIM-V1');

        $this->expectException(DomainException::class);
        (new GenerarInformeUseCase)->ejecutar($otra, $inferencia, null, 'Resumen.', 'Interpretación.', 'Fundamento.');
    }

    /** @throws DomainException Rechazo esperado por el DTO de informe, sin ocultar la incompatibilidad. */
    public function test_propaga_rechazo_de_dcl_de_otro_perfil(): void
    {
        $inferencia = self::inferencia(F::perfil());
        $otroPerfil = F::perfil();
        $dcl = new ResultadoDclDTO($inferencia->solicitud, new ResultadoClasificacionDcl($otroPerfil, null,
            [MotivoAbstencionEnum::SOLO_LABORATORIO_NO_EMITIR], 'Resultado manual de otro perfil.'));

        $this->expectException(DomainException::class);
        (new GenerarInformeUseCase)->ejecutar($inferencia->solicitud, $inferencia, $dcl,
            'Resumen.', 'Interpretación.', 'Fundamento.');
    }

    /**
     * Delega al DTO el rechazo de explicaciones incompletas sin sustituir sus textos.
     *
     * @param  string  $seccion  Nivel que falta en la explicación recibida.
     *
     * @throws DomainException Rechazo esperado del DTO, propagado por el caso de uso.
     */
    #[DataProvider('secciones')]
    public function test_propaga_rechazo_de_una_explicacion_incompleta(string $seccion): void
    {
        $inferencia = self::inferencia(F::perfil());
        $textos = ['resumen' => 'Resumen.', 'interpretacion' => 'Interpretación.', 'fundamento' => 'Fundamento.'];
        $textos[$seccion] = '   ';

        $this->expectException(DomainException::class);
        (new GenerarInformeUseCase)->ejecutar($inferencia->solicitud, $inferencia, null, ...$textos);
    }

    /** @return iterable<string, array{string}> Los tres niveles obligatorios; sin parámetros ni excepciones. */
    public static function secciones(): iterable
    {
        yield 'nivel 1' => ['resumen'];
        yield 'nivel 2' => ['interpretacion'];
        yield 'nivel 3' => ['fundamento'];
    }

    /** Transporta la relación citada sin omitirla o reconstruirla; sin parámetros ni excepciones esperadas. */
    public function test_conserva_las_relaciones_semanticas_recibidas_con_el_fundamento(): void
    {
        $inferencia = self::inferencia(F::perfil());
        $relacion = self::relacion();

        $informe = (new GenerarInformeUseCase)->ejecutar($inferencia->solicitud, $inferencia, null,
            'Resumen recibido.', 'Interpretación recibida.', 'Fundamento con relación explícita.', [$relacion]);

        self::assertSame([$relacion], $informe->relacionesUtilizadas);
        self::assertSame($relacion->origen, $informe->relacionesUtilizadas[0]->origen);
        self::assertSame($relacion->destino, $informe->relacionesUtilizadas[0]->destino);
        self::assertSame('Fundamento con relación explícita.', $informe->fundamento);
        self::assertSame('SIM-V1', $informe->versiones['conocimiento']);
    }

    /** @throws DomainException Rechazo del DTO esperado al recibir una relación de otra versión. */
    public function test_propaga_rechazo_de_relacion_semantica_de_otra_version(): void
    {
        $inferencia = self::inferencia(F::perfil());
        $relacion = self::relacion('SIM-V2');

        $this->expectException(DomainException::class);
        (new GenerarInformeUseCase)->ejecutar($inferencia->solicitud, $inferencia, null,
            'Resumen.', 'Interpretación.', 'Fundamento.', [$relacion]);
    }

    /**
     * Construye una arista explícita para probar el transporte de la explicación.
     *
     * @param  string  $version  Versión propietaria de nodos y relación.
     * @return RelacionSemantica Referencia ficticia original, sin inferir enlaces.
     *                           Sin excepciones esperadas con los identificadores del escenario.
     */
    private static function relacion(string $version = 'SIM-V1'): RelacionSemantica
    {
        $origen = new NodoSemantico('SIM-N-ORIGEN', $version, 'SIM-C-ORIGEN',
            'Concepto ficticio de origen', 'CONCEPTO', 'Definición ficticia de origen.');
        $destino = new NodoSemantico('SIM-N-DESTINO', $version, 'SIM-C-DESTINO',
            'Concepto ficticio de destino', 'CONCEPTO', 'Definición ficticia de destino.');

        return new RelacionSemantica('SIM-REL', $origen, TipoRelacionSemanticaEnum::RELACIONADA_CON, $destino);
    }

    /**
     * Envuelve un perfil manual sin simular una llamada al motor pendiente.
     *
     * @param  PerfilRequisitosDcl  $perfil  Perfil original del escenario ficticio.
     * @return ResultadoInferenciaDTO Resultado preparado para comprobar orquestación.
     *                                Sin excepciones esperadas con el contexto fijo.
     */
    private static function inferencia(PerfilRequisitosDcl $perfil): ResultadoInferenciaDTO
    {
        $solicitud = new SolicitudInferenciaDTO('SIM-SOL', 'SIM-EVAL', 'SINTETICO-RES', F::fecha(), 'SIM-V1');

        return new ResultadoInferenciaDTO($solicitud, $perfil->cognicion, 'SIM-MOTOR-V1',
            array_fill_keys(array_column(DominioCognitivoEnum::cases(), 'value'), []),
            ['SIM-TRAZA' => 'Registro manual de transporte; no acredita ejecución de inferencia.']);
    }
}
