<?php

declare(strict_types=1);

namespace Tests\Unit;

use DateTimeImmutable;
use MotorExperto\dominio\orion\Entities\Conocimiento\ReglaExperta;
use MotorExperto\dominio\orion\Services\Dcl\ClasificadorPetersenService;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\EstadoDominioEnum;
use MotorExperto\dominio\orion\ValueObjects\Conocimiento\OperadorCondicionEnum;
use MotorExperto\soporte\conservacion\CodecInstantanea;
use PHPUnit\Framework\TestCase;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades;

final class CodecIdentidadHistoricaTest extends TestCase
{
    public function test_traslado_de_infraestructura_conserva_la_identidad_canonica_anterior(): void
    {
        // Huella obtenida antes del traslado: identidad del enum, instante, zona, microsegundos, NULL y cero.
        $entrada = ['estado' => EstadoDominioEnum::ALTERACION, 'fecha' => new DateTimeImmutable('2026-10-18T12:00:00.123456-04:00'), 'valor' => null, 'cero' => 0];
        self::assertSame('987115ecfd4b7c1676732652b9b7d758d7eadb3a668fa2a259ada4fcf66874a0', CodecInstantanea::huella($entrada));
    }

    public function test_memoria_y_perfil_dcl_conservan_huellas_previas_al_traslado_del_dominio(): void
    {
        $memoria = EscenarioEntidades::memoria([EscenarioEntidades::evidencia()]);
        self::assertSame('222d52d2fa091916f063b6706d741caa7b7565906f6fa7a9520b7fc1099ffe2d', CodecInstantanea::huella($memoria));
        self::assertSame('a7a0d3a5f80e26f9191cd9499ddbcc149851ac2378aac9ce4b28a3020ce06d89', CodecInstantanea::huella(EscenarioEntidades::perfil()));
    }

    public function test_vector_clasificacion_y_abstencion_conservan_huellas_originales(): void
    {
        $perfil = EscenarioEntidades::perfil();
        $clasificador = new ClasificadorPetersenService;
        self::assertSame('e53b01d37c6671fd889b0921697d8222ac535915e9a1f65c307f15fe1c1409f8', CodecInstantanea::huella($perfil->cognicion));
        self::assertSame('569aab7dc0f32e273da8a0bb76b9048d6d871dea882a43f43d264e21f3254c2e', CodecInstantanea::huella($clasificador->clasificar($perfil)));
        $incompleto = EscenarioEntidades::perfil(['U', 'U', 'U', 'U', 'U']);
        self::assertSame('1150a0e34218a79a345a6240114b7a33dbadf943923ad244c2ea422e013af8aa', CodecInstantanea::huella($clasificador->clasificar($incompleto)));
    }

    public function test_ast_anidado_con_operadores_conserva_la_huella_del_codec_y_clases_originales(): void
    {
        $op = OperadorCondicionEnum::class;
        $regla = new ReglaExperta('SIM-REG-GOLD', 'SIM-V1', ['operador' => $op::Y,
            'condiciones' => [['operador' => $op::IGUAL_A, 'variable' => 'SIM-X', 'valor' => false],
                ['operador' => $op::NO, 'condicion' => ['operador' => $op::DISTINTO_DE, 'variable' => 'SIM-Y', 'valor' => 0]]]],
            ['concepto' => 'SIM-OUT', 'valor' => 'SIM-VAL'], true, 'Fuente sintética de regresión.');
        // Calculada cargando ReglaExperta, OperadorCondicionEnum y Codec originales del commit anterior en otro proceso, sin Composer.
        self::assertSame('a35f4e690953581d6ef24a2994a6fe58e63d430c607870468d813bced06de56f', CodecInstantanea::huella($regla));
    }
}
