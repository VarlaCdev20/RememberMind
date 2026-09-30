<?php

namespace Tests\Unit;

use App\Backend\Modulos\Enfermeria\Servicios\MiTurnoService;
use App\Models\Alerta;
use App\Models\Residente;
use Carbon\Carbon;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class NursingDashboardCardsTest extends TestCase
{
    public function test_alertas_abiertas_se_ordenan_por_prioridad_y_antiguedad_y_se_limitan_a_cuatro(): void
    {
        $alertas = collect([
            $this->alerta('MEDIA', 'ABIERTA', 4, 'M1'),
            $this->alerta('ALTA', 'EN_ATENCION', 5, 'A1'),
            $this->alerta('CRITICA', 'ABIERTA', 1, 'C1'),
            $this->alerta('ALTA', 'ABIERTA', 2, 'A2'),
            $this->alerta('CRITICA', 'ABIERTA', 3, 'C2'),
            $this->alerta('BAJA', 'ABIERTA', 6, 'B1'),
        ]);

        $resultado = (new \ReflectionMethod(MiTurnoService::class, 'formatearAlertasPrioritarias'))
            ->invoke(app(MiTurnoService::class), $alertas);

        $this->assertSame(['C1', 'C2', 'A2', 'M1'], array_column($resultado, 'cod_alerta'));
        $this->assertCount(4, $resultado);
        $this->assertNull($resultado[0]['habitacion']);
        $this->assertSame('ABIERTA', $resultado[0]['estado']);
    }

    public function test_pacientes_usan_proxima_atencion_futura_pendiente_y_solo_muestran_cinco(): void
    {
        $ahora = Carbon::parse('2026-09-28 09:00:00');
        $pacientes = [];
        foreach (range(1, 6) as $numero) {
            $pacientes[] = ['cod_residente' => "R{$numero}", 'nombre_completo' => "Paciente {$numero}", 'alertas_count' => $numero === 3 ? 2 : 0];
        }
        $agenda = [
            ['cod_residente' => 'R1', 'momento' => $ahora->copy()->subHour()->timestamp, 'hora' => '08:00', 'estado' => 'RETRASADO', 'accion' => 'Pasado'],
            ['cod_residente' => 'R2', 'momento' => $ahora->copy()->addHours(2)->timestamp, 'hora' => '11:00', 'estado' => 'REALIZADO', 'accion' => 'Hecho'],
            ['cod_residente' => 'R3', 'momento' => $ahora->copy()->addHours(2)->timestamp, 'hora' => '11:00', 'estado' => 'PENDIENTE', 'accion' => 'Cuidado'],
            ['cod_residente' => 'R4', 'momento' => $ahora->copy()->addHour()->timestamp, 'hora' => '10:00', 'estado' => 'PRÓXIMO', 'accion' => 'Control'],
            ['cod_residente' => 'R4', 'momento' => $ahora->copy()->addMinutes(30)->timestamp, 'hora' => '09:30', 'estado' => 'PENDIENTE', 'accion' => 'Medicación'],
        ];

        $resultado = (new \ReflectionMethod(MiTurnoService::class, 'prepararPacientesTurno'))
            ->invoke(app(MiTurnoService::class), $pacientes, $agenda, $ahora);

        $this->assertCount(5, $resultado);
        $this->assertSame('R4', $resultado[0]['cod_residente']);
        $this->assertSame('09:30', $resultado[0]['proxima_atencion']['hora']);
        $this->assertSame('R3', $resultado[1]['cod_residente']);
        $this->assertNull($resultado[1]['estado_cognitivo']);
        $this->assertNull($resultado[1]['riesgo']);
        $this->assertNull($resultado[2]['proxima_atencion']);
    }

    public function test_componentes_no_generan_enlaces_ficticios_y_muestran_fallbacks_claros(): void
    {
        $alerta = Blade::render('<x-ui.priority-alert :alert="$alerta" />', [
            'alerta' => ['paciente' => 'María', 'prioridad' => 'ALTA', 'descripcion' => 'Medicación vencida', 'estado' => 'ABIERTA'],
        ]);
        $paciente = Blade::render('<x-ui.patient-turn-row :patient="$paciente" />', [
            'paciente' => ['nombre_completo' => 'Rosa', 'iniciales' => 'RO'],
        ]);

        $this->assertStringNotContainsString('href="#"', $alerta.$paciente);
        $this->assertStringContainsString('Medicación vencida', $alerta);
        $this->assertStringContainsString('Sin evaluación', $paciente);
        $this->assertStringContainsString('Sin clasificar', $paciente);
        $this->assertStringContainsString('Sin atención pendiente', $paciente);
        $this->assertStringContainsString('RO', $paciente);
    }

    private function alerta(string $prioridad, string $estado, int $minutos, string $codigo): Alerta
    {
        $alerta = new Alerta([
            'cod_alerta' => $codigo,
            'prioridad' => $prioridad,
            'estado' => $estado,
            'tipo' => 'CLINICA',
            'titulo' => 'Atención',
            'descripcion' => 'Revisar residente',
            'fecha_hora' => Carbon::parse('2026-09-28 08:00:00')->addMinutes($minutos),
        ]);
        $residente = new Residente;
        $residente->setRelation('ocupacionesCama', collect());
        $alerta->setRelation('residente', $residente);

        return $alerta;
    }
}
