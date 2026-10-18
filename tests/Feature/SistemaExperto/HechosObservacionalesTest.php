<?php

namespace Tests\Feature\SistemaExperto;

use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarHechosObservacionales;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ResultadosExpertoResidente;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Support\SistemaExperto\FixtureLecturaExperta as F;
use Tests\Support\SistemaExperto\PruebaConBaseDesechable;

final class HechosObservacionalesTest extends PruebaConBaseDesechable
{
    private User $medico;
    private Residente $residente;

    protected function setUp(): void
    {
        parent::setUp();
        [$this->medico, $this->residente] = F::crear(false);
        $this->actingAs($this->medico);
    }

    private function insertar(string $id, string $residente, string $estado, $fecha, array $observaciones): void
    {
        DB::table('controles_cognitivos')->insert([
            'cod_control_cognitivo' => $id,
            'cod_residente' => $residente,
            'cod_personal' => 'PER_MED',
            'fecha_hora' => $fecha,
            'estado' => $estado,
            ...$observaciones,
        ]);
    }

    public function test_muestra_hallazgos_descriptivos_con_sus_fuentes_en_la_vista_medica(): void
    {
        $this->insertar('OBS_1', $this->residente->getKey(), 'VIGENTE', now()->subDays(2), [
            'repite_preguntas' => true, 'olvida_indicaciones' => false, 'cambio_cognitivo' => null,
        ]);
        $this->insertar('OBS_2', $this->residente->getKey(), 'VIGENTE', now()->subDay(), [
            'repite_preguntas' => false, 'olvida_indicaciones' => null, 'cambio_cognitivo' => null,
        ]);

        $resultado = app(ConsultarHechosObservacionales::class)->consultar($this->medico, $this->residente);
        $this->assertSame('OBS-20261017-v1', $resultado['version']);
        $this->assertSame(2, $resultado['registros_consultados']);
        $this->assertSame(['CUMPLE', 'NO_CUMPLE', 'NO_EVALUABLE'], array_column($resultado['reglas'], 'estado'));
        $this->assertSame(1, $resultado['reglas'][0]['registros_positivos']);
        $this->assertSame('OBS_1', $resultado['reglas'][0]['soportes'][0]['registro']);
        $this->assertStringContainsString('no demuestra ausencia', $resultado['reglas'][1]['explicacion']);

        $antes = DB::table('evaluaciones_expertas')->count();
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->assertSee('Hallazgos observacionales del seguimiento')
            ->assertSee('Repetición de preguntas')
            ->assertSee('Control OBS_1')
            ->assertSee('Sin datos evaluables');
        $this->assertSame($antes, DB::table('evaluaciones_expertas')->count());
    }

    public function test_omite_registros_anulados_futuros_y_de_otros_residentes(): void
    {
        $otro = F::residenteAdicional('OTRO_OBS');
        $this->insertar('OBS_ANULADO', $this->residente->getKey(), 'ANULADO', now()->subDay(), ['repite_preguntas' => true]);
        $this->insertar('OBS_FUTURO', $this->residente->getKey(), 'VIGENTE', now()->addDay(), ['repite_preguntas' => true]);
        $this->insertar('OBS_AJENO', $otro->getKey(), 'VIGENTE', now()->subDay(), ['repite_preguntas' => true]);
        $this->insertar('OBS_NO', $this->residente->getKey(), 'VIGENTE', now()->subDay(), ['repite_preguntas' => false]);

        $resultado = app(ConsultarHechosObservacionales::class)->consultar($this->medico, $this->residente);
        $this->assertSame(1, $resultado['registros_consultados']);
        $this->assertSame('NO_CUMPLE', $resultado['reglas'][0]['estado']);
        $this->assertSame(0, $resultado['reglas'][0]['registros_positivos']);
        $this->assertSame('NO_EVALUABLE', $resultado['reglas'][1]['estado']);
        $this->assertSame('NO_EVALUABLE', $resultado['reglas'][2]['estado']);
    }

    public function test_no_infiere_ausencia_de_problemas_cuando_no_hay_controles(): void
    {
        $resultado = app(ConsultarHechosObservacionales::class)->consultar($this->medico, $this->residente);
        $this->assertSame(0, $resultado['registros_consultados']);
        $this->assertSame(['NO_EVALUABLE', 'NO_EVALUABLE', 'NO_EVALUABLE'], array_column($resultado['reglas'], 'estado'));
        $this->assertSame([], $resultado['reglas'][0]['soportes']);
    }
}
