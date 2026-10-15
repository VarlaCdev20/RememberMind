<?php

namespace Tests\Unit\SistemaExperto;

use DomainException;
use PHPUnit\Framework\TestCase;
use Tests\Support\SistemaExperto\LaboratorioCandidatosOrion;

final class LaboratorioCandidatosOrionTest extends TestCase
{
    private function casos(): array
    {
        return require __DIR__.'/../../Fixtures/SistemaExperto/casos_candidatos_orion.php';
    }

    public function test_quince_plantillas_con_and_y_procedencia_sintetica(): void
    {
        $laboratorio=new LaboratorioCandidatosOrion;
        $casos=$this->casos();
        $inventario=$laboratorio->inventario();
        $this->assertCount(15,$casos);
        $this->assertCount(15,$inventario);
        $this->assertSame([3,5,5,2],array_values(array_count_values(array_column($inventario,'criterio'))));
        $this->assertSame(array_keys($casos),array_column($inventario,'subcomponente'));
        foreach ($casos as $codigo=>$fixture) {
            $r=$laboratorio->ejecutar($fixture['entrada']);
            $this->assertSame('COINCIDENCIA_TECNICA_SINTETICA',$r['estado_tecnico'],$codigo);
            $this->assertSame('PRUEBA_SINTETICA_EXCLUSIVA',$r['uso']);
            $this->assertNull($r['resultado_clinico']);
            $this->assertFalse($r['activacion_clinica']);
            $this->assertSame('NINGUNA',$r['persistencia']);
            $this->assertSame($codigo,$r['traza']['subcomponente']);
            $this->assertSame($fixture['criterio_esperado'],$r['traza']['criterio']);
            $this->assertSame($fixture['referencia_esperada'],$r['traza']['referencia_g09_no_ejecutable']);
            $this->assertCount(2,$r['traza']['condiciones_AND']);
            foreach ($r['traza']['condiciones_AND'] as $c) {
                $this->assertTrue($c['cumple_presencia_sintetica']);
                $this->assertCount(1,$c['fuentes_y_episodios']);
            }
            $this->assertSame($r,$laboratorio->ejecutar($fixture['entrada']));
        }
    }

    public function test_and_incompleta_no_coincide_ni_infiere_normalidad(): void
    {
        $caso=$this->casos()['ATE-S01']['entrada'];
        array_pop($caso['hechos']);
        $r=(new LaboratorioCandidatosOrion)->ejecutar($caso);
        $this->assertSame('SIN_COINCIDENCIA_TECNICA',$r['estado_tecnico']);
        $this->assertFalse($r['traza']['condiciones_AND'][1]['cumple_presencia_sintetica']);
        $this->assertNull($r['resultado_clinico']);
        $caso['hechos']=[];
        $this->assertSame('SIN_COINCIDENCIA_TECNICA',(new LaboratorioCandidatosOrion)->ejecutar($caso)['estado_tecnico']);
    }

    public function test_las_compuertas_cero_y_uno_bloquean_incluso_con_dos_hechos(): void
    {
        foreach (['LAB-EV-0','LAB-EV-1'] as $estado) {
            $caso=$this->casos()['EJE-S01']['entrada'];
            $caso['compuerta_simulada']=$estado;
            $r=(new LaboratorioCandidatosOrion)->ejecutar($caso);
            $this->assertSame('NO_EJECUTADO_POR_COMPUERTA_SINTETICA',$r['estado_tecnico']);
            $this->assertNull($r['resultado_clinico']);
        }
    }

    public function test_rechaza_datos_reales_estructuras_clinicas_y_procedencia_no_sintetica(): void
    {
        $base=$this->casos()['LEN-S01']['entrada'];
        $x=$base;$x['origen']='OPERATIVO';$this->rechazado($x);
        $x=$base;$x['actor_sintetico']='RES-001';$this->rechazado($x);
        $x=$base;$x['cod_residente']='RES-001';$this->rechazado($x);
        $x=$base;$x['hechos'][0]['valor_observado']='ALTERADA';$this->rechazado($x);
        $x=$base;$x['hechos'][0]['fuente']='controles_cognitivos';$this->rechazado($x);
    }

    public function test_rechaza_versiones_criterios_y_resultados_clinicos(): void
    {
        $base=$this->casos()['VIS-S02']['entrada'];
        $x=$base;$x['version_laboratorio']='PROD';$this->rechazado($x);
        $x=$base;$x['subcomponente']='COG-MEM';$this->rechazado($x);
        $x=$base;$x['hechos'][0]['codigo']='DIFICULTAD_EVIDENCIADA';$this->rechazado($x);
        $x=$base;$x['compuerta_simulada']='EV-CV-2';$this->rechazado($x);
    }

    public function test_identidades_y_episodios_duplicados_no_se_recuentan(): void
    {
        $base=$this->casos()['ATE-S03']['entrada'];
        $x=$base;$x['hechos'][]=$x['hechos'][0];$this->rechazado($x);
        $x=$base;$otra=$x['hechos'][0];$otra['id']='SYN-EV-ATE-S03-OTRO';
        $x['hechos'][]=$otra;$this->rechazado($x);
    }

    public function test_hechos_de_otro_subcomponente_no_se_reciclan(): void
    {
        $x=$this->casos()['ATE-S01']['entrada'];
        $x['hechos'][1]=$this->casos()['ATE-S02']['entrada']['hechos'][1];
        $this->rechazado($x);
    }

    private function rechazado(array $caso): void
    {
        try {
            (new LaboratorioCandidatosOrion)->ejecutar($caso);
            $this->fail('Se aceptó una entrada externa a laboratorio.');
        } catch (DomainException) {
            $this->addToAssertionCount(1);
        }
    }
}
