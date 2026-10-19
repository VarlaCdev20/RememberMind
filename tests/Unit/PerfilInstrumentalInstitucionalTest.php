<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\ArchivoConocimientoOrion;
use DomainException;
use MotorExperto\dominio\conocimiento\PerfilConceptualCognitivo;
use MotorExperto\dominio\instrumentos\PerfilInstrumentalCognitivo;
use PHPUnit\Framework\TestCase;

final class PerfilInstrumentalInstitucionalTest extends TestCase
{
    public function test_reutiliza_red_documental_con_19_pertenencias_primarias_sin_reglas_sinteticas(): void
    {
        $red = (new PerfilConceptualCognitivo)->construir((new ArchivoConocimientoOrion)->leer());
        self::assertCount(24, $red['nodos']);
        self::assertCount(19, $red['relaciones']);
        self::assertSame(['COG-MEM', 'COG-ATE', 'COG-EJE', 'COG-LEN', 'COG-VIS'], array_keys($red['dominios']));
        self::assertSame('COG-EJE', $red['dominios']['COG-EJE']['componentes']['EJE-S03']['dominio']);
        self::assertArrayNotHasKey('EJE-S03', $red['dominios']['COG-MEM']['componentes']);
        self::assertFalse($red['ejecutable_clinico']);
        foreach ($red['dominios'] as $dominio) {
            foreach ($dominio['componentes'] as $componente) {
                self::assertArrayNotHasKey('reglas', $componente);
                self::assertNotEmpty($componente['concepto']['procedencia']);
            }
        }
    }

    public function test_componente_ausente_o_segundo_propietario_no_se_aceptan(): void
    {
        $fuente = (new ArchivoConocimientoOrion)->leer();
        $fuente['relaciones'][] = ['codigo' => 'SEGUNDA', 'origen' => 'EJE-S03', 'destino' => 'COG-MEM', 'relacion' => 'SUBCOMPONENTE_DE'];
        $this->expectException(DomainException::class);
        (new PerfilConceptualCognitivo)->construir($fuente);
    }

    public function test_perfil_vacio_conserva_19_desconocidos_sin_declarar_normalidad_ni_guardado(): void
    {
        $red = (new PerfilConceptualCognitivo)->construir((new ArchivoConocimientoOrion)->leer());
        $r = (new PerfilInstrumentalCognitivo)->construir($red, ['disponible' => true, 'capturas' => []]);
        self::assertFalse($r['persistido']);
        self::assertNull($r['diagnostico']);
        $total = 0;
        foreach ($r['dominios'] as $dominio) {
            self::assertNull($dominio['resultado_clinico']);
            self::assertSame('NO_INTERPRETADO', $dominio['estado']);
            foreach ($dominio['componentes'] as $componente) {
                $total++;
                self::assertNull($componente['resultado_clinico']);
                self::assertSame('SIN_MEDICION_NORMALIZADA', $componente['estado_medicion']);
            }
        }
        self::assertSame(19, $total);
    }
}
