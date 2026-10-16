<?php

namespace Tests\Unit\SistemaExperto;

use MotorExperto\dominio\conocimiento\versiones\ContratosFormalesCuatroCriterios;
use PHPUnit\Framework\TestCase;

final class ContratosFormalesCuatroCriteriosTest extends TestCase
{
    public function test_catalogo_se_consulta_sin_publicar_conocimiento_clinico(): void
    {
        $contratos = (new ContratosFormalesCuatroCriterios)->consultar();
        $this->assertSame(ContratosFormalesCuatroCriterios::VERSION, $contratos['version']);
        $this->assertFalse($contratos['conocimiento_clinico_activo']);
        $this->assertFalse($contratos['autorizacion_inferencial']);
        $this->assertSame(
            ['COG-ATE', 'COG-EJE', 'COG-LEN', 'COG-VIS'],
            array_column($contratos['criterios'], 'codigo')
        );
        foreach ($contratos['criterios'] as $criterio) {
            $this->assertSame('INACTIVO', $criterio['estado_tecnico']);
            $this->assertSame([], $criterio['resultados_clinicos']);
        }
    }
}
