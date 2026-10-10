<?php

namespace Tests\Unit\SistemaExperto;

use App\Backend\Modulos\SistemaExperto\Conocimiento\PaquetesCandidatosCognitivos;
use App\Backend\Modulos\SistemaExperto\Servicios\DemostracionCOGMEM;
use DomainException;
use PHPUnit\Framework\TestCase;

class DemostracionExpertaTest extends TestCase
{
    public function test_demostracion_ejecuta_conocimiento_artificial_y_conserva_rutas(): void
    {
        $s = new DemostracionCOGMEM;
        foreach (['dificultad' => 'DIFICULTAD_EVIDENCIADA', 'preservacion' => 'SIN_DIFICULTAD_EVIDENCIADA', 'mixto' => 'HALLAZGOS_MIXTOS'] as $caso => $codigo) {
            $r = $s->ejecutar($caso);
            $this->assertSame($codigo, $r['resultado']['codigo']);
            $this->assertNotEmpty($r['traza']['rutas']);
            $this->assertSame($r, $s->ejecutar($caso));
            $this->assertSame('PRUEBA_TECNICA', $r['uso']);
        }
        $r = $s->ejecutar('contexto');
        $this->assertNull($r['resultado']);
        $this->assertSame('EV-CM-1', $r['evaluabilidad']['estado']);
    }

    public function test_cobertura_inconsistente_no_emite_resultado(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('inconsistencia de cobertura');
        (new DemostracionCOGMEM)->ejecutar('cobertura');
    }

    public function test_candidatos_no_tienen_contenido_inferencial_por_analogia(): void
    {
        $paquetes = (new PaquetesCandidatosCognitivos)->todos();
        $this->assertSame(['COG-ATE', 'COG-EJE', 'COG-LEN', 'COG-VIS'], array_column($paquetes, 'codigo'));
        $this->assertSame([3, 5, 5, 2], array_map(fn ($p) => count($p['subcomponentes_candidatos']), $paquetes));
        foreach ($paquetes as $p) {
            $this->assertSame('INACTIVO', $p['estado_tecnico']);
            foreach (['reglas', 'mapeos', 'compuertas', 'dominios_resultado'] as $campo) {
                $this->assertSame([], $p[$campo]);
            }
        }
    }
}
