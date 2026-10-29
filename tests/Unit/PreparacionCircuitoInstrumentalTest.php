<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modulos\ApoyoDecision\Aplicacion\Consultas\PreparacionCircuitoInstrumental;
use DomainException;
use MotorExperto\dominio\instrumentos\RegistroMetodosInstrumentales;
use PHPUnit\Framework\TestCase;

final class PreparacionCircuitoInstrumentalTest extends TestCase
{
    public function test_sin_catalogos_o_casos_muestra_condiciones_sin_inventar_perfiles(): void
    {
        $r = (new PreparacionCircuitoInstrumental)->resumir([], []);
        self::assertSame(['I01', 'I02', 'I04'], array_keys($r['familias']));
        self::assertFalse($r['consulta_datos_clinicos']);
        self::assertFalse($r['ejecuta_inferencia']);
        self::assertFalse($r['habilita_perfiles']);
        self::assertSame('PERSONAS_MAYORES_DE_60_ANIOS', $r['poblacion_objetivo']);
        foreach ($r['familias'] as $familia) {
            self::assertNull($familia['edicion_operativa']);
            self::assertNull($familia['perfil_ejecutable']);
            self::assertFalse($familia['autoriza_inferencia']);
            self::assertSame('NO_ACREDITADOS', $familia['derechos_uso_previsto']);
            self::assertNotEmpty($familia['faltantes']);
        }
    }

    public function test_metodos_existentes_y_nombres_de_perfil_no_otorgan_adopcion_o_licencia(): void
    {
        $perfiles = [['id' => 'MoCA', 'derechos' => true], ['id' => 'RUDAS', 'estado' => 'ACTIVO']];
        $original = $perfiles;
        $metodos = (new RegistroMetodosInstrumentales)->metodos();
        $r = (new PreparacionCircuitoInstrumental)->resumir($perfiles, $metodos);
        self::assertSame($original, $perfiles);
        self::assertSame(2, $r['observacion_tecnica']['perfiles_configurados_total']);
        self::assertSame(count($metodos), $r['observacion_tecnica']['metodos_registrados_total']);
        self::assertFalse($r['observacion_tecnica']['conteos_acreditan_derechos_o_aplicabilidad']);
        self::assertSame([false, false, false], array_column($r['familias'], 'autoriza_inferencia'));
        self::assertSame([null, null, null], array_column($r['familias'], 'perfil_ejecutable'));
    }

    public function test_salida_es_reproducible_y_no_publica_configuracion_privada(): void
    {
        $consulta = new PreparacionCircuitoInstrumental;
        $a = $consulta->resumir([['dato_privado' => 'NO_PUBLICAR']], []);
        $b = $consulta->resumir([['dato_privado' => 'OTRO_VALOR']], []);
        self::assertSame($a, $b);
        self::assertStringNotContainsString('NO_PUBLICAR', json_encode($a, JSON_THROW_ON_ERROR));
        self::assertSame($a, $consulta->resumir([[]], []));
    }

    public function test_configuracion_invalida_falla_explicitamente(): void
    {
        $this->expectException(DomainException::class);
        (new PreparacionCircuitoInstrumental)->resumir(['perfil_no_estructurado'], []);
    }
}
