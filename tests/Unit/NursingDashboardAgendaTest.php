<?php

namespace Tests\Unit;

use App\Backend\Modulos\Enfermeria\Servicios\MiTurnoService;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class NursingDashboardAgendaTest extends TestCase
{
    public function test_resumen_prioriza_retrasados_proximos_pendientes_y_luego_realizados(): void
    {
        $agenda = [
            $this->evento('Realizado', 'REALIZADO', 800),
            $this->evento('Pendiente tarde', 'PENDIENTE', 1200),
            $this->evento('Próximo tarde', 'PRÓXIMO', 1100),
            $this->evento('Retrasado tarde', 'RETRASADO', 700),
            $this->evento('Retrasado temprano', 'RETRASADO', 600),
            $this->evento('Próximo temprano', 'PRÓXIMO', 1000),
        ];

        $resumen = app(MiTurnoService::class)->prepararAgendaResumen($agenda);

        $this->assertCount(5, $resumen);
        $this->assertSame([
            'Retrasado temprano', 'Retrasado tarde', 'Próximo temprano', 'Próximo tarde', 'Pendiente tarde',
        ], array_column($resumen, 'title'));
        $this->assertSame('MEDICACION', $resumen[0]['type']);
        $this->assertSame('RETRASADO', $resumen[0]['status']);
    }

    public function test_resumen_excluye_ejecuciones_sin_hora_programada_y_no_inventa_paciente(): void
    {
        $sinHora = $this->evento('Ejecución heredada', 'PENDIENTE', 500);
        $sinHora['tiene_horario_programado'] = false;
        $valido = $this->evento('Dosis', 'PENDIENTE', 600);
        $valido['residente'] = 'Residente asignado';

        $resumen = app(MiTurnoService::class)->prepararAgendaResumen([$sinHora, $valido]);

        $this->assertCount(1, $resumen);
        $this->assertNull($resumen[0]['patient']);
        $this->assertSame('PENDIENTE', $resumen[0]['status']);
    }

    public function test_fila_tiene_estado_textual_y_no_enlace_ficticio(): void
    {
        $html = Blade::render('<x-ui.schedule-item time="10:30" title="Administración" patient="María" type="MEDICACION" status="RETRASADO" />');

        $this->assertStringContainsString('10:30', $html);
        $this->assertStringContainsString('María', $html);
        $this->assertStringContainsString('Retrasado', $html);
        $this->assertStringNotContainsString('href="#"', $html);
    }

    public function test_omision_no_se_presenta_como_dosis_administrada_y_agenda_vacia_no_inventa_eventos(): void
    {
        $html = Blade::render('<x-ui.schedule-item time="10:30" title="Dosis" status="REALIZADO" omission="Rechazada por residente" />');
        $this->assertStringContainsString('Omitido registrado', $html);
        $this->assertStringNotContainsString('ph-check', $html);
        $this->assertSame([], app(MiTurnoService::class)->prepararAgendaResumen([]));
    }

    public function test_graficas_del_turno_usan_indicadores_verificables_sin_inventar_clasificacion_clinica(): void
    {
        $vista = file_get_contents(resource_path('views/livewire/cuidados/dashboard-turno.blade.php'));
        $inicio = strpos($vista, '<x-ui.card class="rm-nursing-dashboard__distribution');
        $fin = strpos($vista, '</x-ui.card>', $inicio);
        $card = substr($vista, $inicio, $fin - $inicio);

        $this->assertStringContainsString('Estado de seguimiento', $card);
        $this->assertStringContainsString('Sin residentes en turno', $card);
        $this->assertStringContainsString('Basado en alertas activas; no equivale a una valoración clínica.', $card);
        $this->assertStringContainsString('nursingFollowupCanvas-', $card);
        $this->assertStringContainsString('aria-label="Detalle del seguimiento"', $card);
        $this->assertStringNotContainsString('Atención alta', $card);
        $this->assertStringContainsString('Cuidados pendientes', $vista);
        $this->assertStringContainsString('Actividad del turno', $vista);
        $this->assertStringNotContainsString('Tendencia de ocupación', $vista);
        $this->assertStringContainsString('Sin acciones para graficar', $vista);
        $this->assertStringContainsString('rm-chart-card rm-chart-glass', $vista);
        $this->assertStringContainsString('window.RMCharts.presets.doughnut', $vista);
        $this->assertStringContainsString('window.RMCharts.presets.barHorizontal', $vista);
        $this->assertStringContainsString('prefers-reduced-motion: reduce', $vista);
        $this->assertStringContainsString('Evolución de incidentes', $vista);
        $this->assertStringNotContainsString('data-nursing-incidents-by-type', $vista);
    }

    private function evento(string $titulo, string $estado, int $momento): array
    {
        return [
            'hora' => '10:00',
            'momento' => $momento,
            'accion' => $titulo,
            'residente' => 'María',
            'tipo' => 'MEDICACION',
            'icono' => 'ph-pill',
            'estado' => $estado,
            'tiene_horario_programado' => true,
        ];
    }
}
