<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Application\DTOs;

use App\Modulos\ApoyoDecision\Aplicacion\DTOs\ResultadoInferenciaDTO;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\SolicitudInferenciaDTO;
use MotorExperto\dominio\orion\Entities\Conocimiento\ReglaExperta;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\DominioCognitivoEnum as D;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\EstadoDominioEnum as E;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\EvaluabilidadEnum as V;
use MotorExperto\dominio\orion\ValueObjects\Conocimiento\OperadorCondicionEnum as O;
use DomainException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades as F;

/**
 * TÍTULO: Transporte del perfil de cinco dominios.
 * CAPA: Application (pruebas unitarias).
 * ¿PARA QUÉ SIRVE? Verifica conservación de estados, reglas, versiones y trazas.
 * ¿QUÉ RECIBE? Perfiles declarados manualmente y reglas sintéticas.
 * ¿QUÉ HACE? Construye el DTO con objetos reales del Domain.
 * ¿QUÉ DEVUELVE? Aserciones de conservación y rechazo estructural.
 * ¿QUÉ NO DEBE HACER? No afirma ejecución de un motor ni validación clínica.
 * RELACIÓN CON O.R.I.O.N.: Reglamento de Application, DTO 03 y etapa B.
 */
final class ResultadoInferenciaDTOTest extends TestCase
{
    /** Conserva los cinco estados diferenciados; sin parámetros ni excepciones esperadas. @return void */
    public function test_transporta_cinco_dominios_sin_reinterpretarlos(): void
    {
        $perfil = F::perfil(['A', 'P', 'U', 'I', 'X'])->cognicion;
        $regla = self::regla();
        $reglas = self::reglasVacias();
        $reglas[D::MEMORIA->value] = [$regla];
        $dto = new ResultadoInferenciaDTO(self::solicitud(), $perfil, 'SIM-MOTOR-V1', $reglas,
            ['SIM-TRAZA' => 'Registro artificial declarado para probar transporte.']);

        self::assertSame($perfil, $dto->perfilCognitivo);
        self::assertSame($perfil->resultados, $dto->dominios);
        self::assertSame([E::ALTERACION, E::RENDIMIENTO_ESPERADO, E::DESCONOCIDO, E::NO_INTERPRETABLE, E::CONTRADICTORIO],
            array_map(static fn ($r) => $r->estado, array_values($dto->dominios)));
        self::assertSame(V::NO_EVALUABLE, $dto->dominios[D::FUNCION_EJECUTIVA->value]->evaluabilidad);
        self::assertSame([$regla], $dto->reglasPorDominio[D::MEMORIA->value]);
        self::assertSame([D::VISUOESPACIAL->value => $perfil->resultado(D::VISUOESPACIAL)->justificacion], $dto->contradicciones);
        self::assertSame('SIM-MOTOR-V1', $dto->versionMotor);
        self::assertFalse(property_exists($dto, 'puntuacionGlobal'));
    }

    /** Una traza ausente no se sustituye por éxito; sin parámetros ni excepciones esperadas. @return void */
    public function test_conserva_ausencia_de_trazas_y_perfil_desconocido(): void
    {
        $perfil = F::perfil(['U', 'U', 'U', 'U', 'U'])->cognicion;
        $dto = new ResultadoInferenciaDTO(self::solicitud(), $perfil, 'SIM-MOTOR-V1', self::reglasVacias(), []);

        self::assertSame([], $dto->trazas);
        self::assertSame([], $dto->advertencias);
        foreach ($dto->dominios as $resultado) {
            self::assertSame(E::DESCONOCIDO, $resultado->estado);
            self::assertSame([], $resultado->evidencias);
        }
    }

    /** Protege listas anidadas y textos de referencias PHP; sin parámetros ni excepciones esperadas. @return void */
    public function test_copia_reglas_anidadas_trazas_y_advertencias(): void
    {
        $regla = self::regla();
        $original = $regla;
        $traza = 'Traza original.';
        $advertencia = 'Advertencia original.';
        $reglas = self::reglasVacias();
        $reglas[D::MEMORIA->value] = [&$regla];
        $dto = new ResultadoInferenciaDTO(self::solicitud(), F::perfil()->cognicion, 'SIM-MOTOR-V1', $reglas,
            ['SIM-TRAZA' => &$traza], ['SIM-AVISO' => &$advertencia]);
        $regla = self::regla('SIM-R-OTRA');
        $traza = 'Modificada.';
        $advertencia = 'Modificada.';

        self::assertSame($original, $dto->reglasPorDominio[D::MEMORIA->value][0]);
        self::assertSame('Traza original.', $dto->trazas['SIM-TRAZA']);
        self::assertSame('Advertencia original.', $dto->advertencias['SIM-AVISO']);
    }

    /**
     * Rechaza atribuciones de reglas sin un contrato estructural coherente.
     *
     * @param  string  $caso  Alteración artificial de las reglas recibidas.
     *
     * @throws DomainException Rechazo esperado, sin ejecutar ninguna regla.
     */
    #[DataProvider('reglasInvalidas')]
    public function test_rechaza_reglas_incoherentes(string $caso): void
    {
        $reglas = self::reglasVacias();
        $regla = self::regla();
        switch ($caso) {
            case 'dominio faltante': unset($reglas[D::MEMORIA->value]);
                break;
            case 'dominio ajeno': unset($reglas[D::MEMORIA->value]);
                $reglas['COG-AJENO'] = [];
                break;
            case 'lista sin tipar': $reglas[D::MEMORIA->value] = ['SIM-R'];
                break;
            case 'duplicada': $reglas[D::MEMORIA->value] = [$regla, $regla];
                break;
            case 'otra versión': $reglas[D::MEMORIA->value] = [self::regla(version: 'SIM-V2')];
                break;
            case 'deshabilitada': $reglas[D::MEMORIA->value] = [self::regla(habilitada: false)];
                break;
            case 'mapa en lugar de lista': $reglas[D::MEMORIA->value] = ['SIM-R' => $regla];
                break;
        }
        $perfil = F::perfil()->cognicion;
        $this->expectException(DomainException::class);
        new ResultadoInferenciaDTO(self::solicitud(), $perfil, 'SIM-MOTOR-V1', $reglas, []);
    }

    /** @return iterable<string, array{string}> Formas inválidas; sin parámetros ni excepciones. */
    public static function reglasInvalidas(): iterable
    {
        foreach (['dominio faltante', 'dominio ajeno', 'lista sin tipar', 'duplicada', 'otra versión',
            'deshabilitada', 'mapa en lugar de lista'] as $caso) {
            yield $caso => [$caso];
        }
    }

    /** @return void @throws DomainException Rechazo esperado de una versión ausente. */
    public function test_version_del_motor_no_puede_inventarse_por_defecto(): void
    {
        $perfil = F::perfil()->cognicion;
        $this->expectException(DomainException::class);
        new ResultadoInferenciaDTO(self::solicitud(), $perfil, '', self::reglasVacias(), []);
    }

    /** @return void @throws DomainException Rechazo esperado de un perfil de otra evaluación. */
    public function test_rechaza_perfil_de_otra_solicitud(): void
    {
        $solicitud = new SolicitudInferenciaDTO('SIM-SOL', 'SIM-OTRA', 'SINTETICO-RES', F::fecha(), 'SIM-V1');
        $perfil = F::perfil()->cognicion;
        $this->expectException(DomainException::class);
        new ResultadoInferenciaDTO($solicitud, $perfil, 'SIM-MOTOR-V1', self::reglasVacias(), []);
    }

    /**
     * Comprueba que una traza o advertencia requiera texto identificable.
     *
     * @param  string  $campo  Metadato a sustituir.
     * @param  array<mixed>  $datos  Entrada inválida.
     *
     * @throws DomainException Rechazo estructural esperado.
     */
    #[DataProvider('textosInvalidos')]
    public function test_rechaza_textos_de_traza_y_advertencia_invalidos(string $campo, array $datos): void
    {
        $perfil = F::perfil()->cognicion;
        $argumentos = ['trazas' => [], 'advertencias' => []];
        $argumentos[$campo] = $datos;
        $this->expectException(DomainException::class);
        new ResultadoInferenciaDTO(self::solicitud(), $perfil, 'SIM-MOTOR-V1', self::reglasVacias(), ...$argumentos);
    }

    /** @return iterable<string, array{string, array<mixed>}> Metadatos inválidos; sin parámetros ni excepciones. */
    public static function textosInvalidos(): iterable
    {
        foreach (['trazas', 'advertencias'] as $campo) {
            yield $campo.' sin identificación' => [$campo, ['texto sin clave']];
            yield $campo.' vacío' => [$campo, ['SIM-ID' => ' ']];
            yield $campo.' no textual' => [$campo, ['SIM-ID' => null]];
        }
    }

    /** @return SolicitudInferenciaDTO Petición ficticia; sin parámetros ni excepciones esperadas. */
    private static function solicitud(): SolicitudInferenciaDTO
    {
        return new SolicitudInferenciaDTO('SIM-SOL', 'SIM-EVAL', 'SINTETICO-RES', F::fecha(), 'SIM-V1');
    }

    /** @return array<string, list<ReglaExperta>> Cinco listas explícitas; sin parámetros ni excepciones. */
    private static function reglasVacias(): array
    {
        return array_fill_keys(array_column(D::cases(), 'value'), []);
    }

    /**
     * Declara conocimiento artificial para comprobar transporte y versiones.
     *
     * @param  string  $id  Identidad de regla.
     * @param  string  $version  Versión de conocimiento.
     * @param  bool  $habilitada  Habilitación técnica, nunca clínica.
     * @return ReglaExperta Regla original, sin ejecutar.
     *                      Sin excepciones esperadas con los valores de estos escenarios.
     */
    private static function regla(string $id = 'SIM-R', string $version = 'SIM-V1', bool $habilitada = true): ReglaExperta
    {
        return new ReglaExperta($id, $version, ['operador' => O::IGUAL_A, 'variable' => 'SIM-VAR', 'valor' => true],
            ['concepto' => 'SIM-HALLAZGO', 'valor' => true], $habilitada, 'SIM-FUENTE');
    }
}
