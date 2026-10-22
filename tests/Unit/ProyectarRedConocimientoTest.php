<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modulos\ApoyoDecision\Aplicacion\DTOs\ProyectarRedConocimiento;
use DomainException;
use PHPUnit\Framework\TestCase;

final class ProyectarRedConocimientoTest extends TestCase
{
    private function conocimiento(int $cantidad = 3): array
    {
        $nodos = [];
        for ($i = 0; $i < $cantidad; $i++) {
            $nodos['N'.$i] = ['cod_nodo_semantico' => 'N'.$i, 'cod_version_modelo' => 'V1', 'codigo_semantico' => 'COD'.$i,
                'nombre' => 'Concepto '.$i, 'tipo_nodo' => $i === 0 ? 'CRITERIO' : 'VARIABLE', 'estado' => 'INACTIVO'];
        }

        return ['nodos' => $nodos, 'experimental' => false, 'relaciones' => [
            ['cod_relacion_semantica' => 'R1', 'cod_version_modelo' => 'V1', 'cod_nodo_origen' => 'N0', 'cod_nodo_destino' => 'N1', 'tipo_relacion' => 'DECLARA'],
        ]];
    }

    public function test_filtros_paginacion_y_relaciones_sin_inventar_nodos(): void
    {
        $dto = new ProyectarRedConocimiento;
        $datos = $this->conocimiento(80);
        $original = $datos;
        $r = $dto->presentar($datos, 'V1', [], 999);
        self::assertSame(3, $r['pagina']);
        self::assertCount(8, $r['nodos']);
        self::assertSame([], $r['aristas']);
        $r = $dto->presentar($datos, 'V1', ['criterio' => 'COD0']);
        self::assertCount(2, $r['nodos']);
        self::assertSame('DECLARA', $r['aristas'][0]['relacion']);
        self::assertSame('Concepto 1', $dto->presentar($datos, 'V1', ['buscar' => 'Concepto 1', 'tipo' => 'VARIABLE'])['nodos'][0]['nombre']);
        self::assertSame([], $dto->presentar($datos, 'V1', ['estado' => 'ACTIVO'])['nodos']);
        self::assertSame($original, $datos);
    }

    public function test_red_vacia_no_crea_conceptos_ni_metricas(): void
    {
        $r = (new ProyectarRedConocimiento)->presentar(['nodos' => [], 'relaciones' => [], 'experimental' => false], 'V1');
        self::assertSame(0, $r['total']);
        self::assertSame([], $r['aristas']);
        self::assertSame(1, $r['paginas']);
    }

    public function test_fuente_procede_del_mapeo_de_variable_y_no_de_proximidad(): void
    {
        $c = $this->conocimiento();
        $c['tablas'] = [
            'variables_expertas' => [['cod_variable_experta' => 'VAR1', 'cod_nodo_semantico' => 'N1', 'cod_nodo_propietario_primario' => 'N0']],
            'mapeos_variables_fuente' => [['cod_mapeo_variable_fuente' => 'MAP1', 'cod_variable_experta' => 'VAR1', 'cod_fuente_dato_experta' => 'FU1']],
            'fuentes_datos_expertas' => [['cod_fuente_dato_experta' => 'FU1', 'nombre' => 'Fuente sintética declarada']],
        ];
        $dto = new ProyectarRedConocimiento;
        self::assertSame('Fuente sintética declarada', $dto->fuentesNodo($c, 'N1')[0]['fuente']);
        self::assertSame('MAP1', $dto->fuentesNodo($c, 'N0')[0]['mapeo']);
        self::assertSame([], $dto->fuentesNodo($c, 'N2'));
    }

    public function test_relacion_huerfana_no_llega_al_lienzo(): void
    {
        $c = $this->conocimiento();
        $c['relaciones'][0]['cod_nodo_destino'] = 'AJENO';
        $this->expectException(DomainException::class);
        (new ProyectarRedConocimiento)->presentar($c, 'V1');
    }

    public function test_nodo_de_otra_version_se_rechaza(): void
    {
        $c = $this->conocimiento();
        $c['nodos']['N1']['cod_version_modelo'] = 'V2';
        $this->expectException(DomainException::class);
        (new ProyectarRedConocimiento)->presentar($c, 'V1');
    }
}
