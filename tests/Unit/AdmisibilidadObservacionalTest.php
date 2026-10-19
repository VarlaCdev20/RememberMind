<?php

declare(strict_types=1);

namespace Tests\Unit;

use MotorExperto\adaptadores\entrada\AdaptadorHechosCOGMEM;
use MotorExperto\adaptadores\entrada\EjemploTecnicoCOGMEM as Ejemplo;
use MotorExperto\dominio\inferencia\MemoriaTrabajo;
use MotorExperto\dominio\objetos_valor\FuenteBrutaCOGMEM;
use PHPUnit\Framework\TestCase;

final class AdmisibilidadObservacionalTest extends TestCase
{
    public function test_indicador_verificado_no_convierte_catalogo_observacional_en_prueba_instrumental(): void
    {
        $p = Ejemplo::paquete();
        $contexto = Ejemplo::contexto();
        $m = new MemoriaTrabajo($contexto);
        $f = new FuenteBrutaCOGMEM('FU_INST', 'aplicaciones_instrumento', 'COMP_TEST', 'SIM-APP', $contexto->residente,
            $contexto->fechaCorte->modify('-1 day'), 'literal_A', 'literal_A', 'SIM-AUTOR', true, true,
            contexto: ['componente_instrumental_verificado' => true, 'instrumento' => ['tipo' => 'OBSERVACIONAL']]);
        $e = (new AdaptadorHechosCOGMEM)->adaptar($p, $m, $f, 'MAP_INS');
        self::assertSame('NO_ADMISIBLE', $e->admisibilidad);
        self::assertContains('OBSERVACION_NO_ACREDITA_COMPONENTE_INSTRUMENTAL', $e->motivos);
        self::assertSame('literal_A', $e->fuente->valorOriginal);
        self::assertFalse($e->utilizable());
    }
}
