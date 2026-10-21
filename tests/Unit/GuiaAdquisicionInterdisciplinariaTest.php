<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modulos\ApoyoDecision\Aplicacion\Consultas\GuiaAdquisicionInterdisciplinaria;
use MotorExperto\dominio\instrumentos\CatalogoCapturaCognitiva;
use PHPUnit\Framework\TestCase;

final class GuiaAdquisicionInterdisciplinariaTest extends TestCase
{
    public function test_cada_componente_tiene_un_candidato_trazable_del_registro_vigente(): void
    {
        $filas = (new GuiaAdquisicionInterdisciplinaria)->componentes();

        self::assertCount(19, $filas);
        self::assertSame(array_keys((new CatalogoCapturaCognitiva)->componentes()), array_column($filas, 'componente'));
        foreach ($filas as $fila) {
            self::assertNotEmpty($fila['metodo']);
            self::assertNotEmpty($fila['instrumento']);
            self::assertNotEmpty($fila['entradas']);
            self::assertNotFalse(filter_var($fila['fuente'], FILTER_VALIDATE_URL));
            self::assertArrayNotHasKey('resultado_residente', $fila);
            self::assertArrayNotHasKey('habilitado', $fila);
        }
        self::assertSame('DSB-ES-SABIEX-2021-LONGITUD', $filas[9]['metodo']);
    }
}
