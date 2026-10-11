<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class EstructuraModularTest extends TestCase
{
    public function test_los_doce_modulos_tienen_las_cuatro_capas(): void
    {
        $raiz = dirname(__DIR__, 2);
        foreach (['Identidad','Institucion','Residentes','Admisiones','Clinica','Medicacion','Valoraciones','Cuidados','Social','Alertas','Reportes','ApoyoDecision'] as $modulo) {
            foreach (['Dominio', 'Aplicacion', 'Infraestructura', 'Presentacion'] as $capa) {
                $this->assertDirectoryExists($raiz.'/app/Modulos/'.$modulo.'/'.$capa);
            }
            $this->assertFileExists($raiz.'/app/Modulos/'.$modulo.'/Presentacion/Rutas/web.php');
        }
    }
}
