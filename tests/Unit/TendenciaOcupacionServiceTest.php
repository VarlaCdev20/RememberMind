<?php

namespace Tests\Unit;

use App\Backend\Modulos\Enfermeria\Servicios\TendenciaOcupacionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class TendenciaOcupacionServiceTest extends TestCase
{
    public function test_no_inventa_serie_sin_camas_activas(): void
    {
        $camas = Mockery::mock();
        $camas->shouldReceive('where')->once()->with('estado', 'ACTIVA')->andReturnSelf();
        $camas->shouldReceive('pluck')->once()->with('cod_cama')->andReturn(collect());
        DB::shouldReceive('table')->once()->with('camas')->andReturn($camas);

        $this->assertNull(app(TendenciaOcupacionService::class)->ultimosSieteDias());
    }

    public function test_cuenta_ocupaciones_por_intervalo_y_no_como_total_acumulado(): void
    {
        Carbon::setTestNow('2026-09-29 13:00:00');

        try {
            $camas = Mockery::mock();
            $camas->shouldReceive('where')->once()->with('estado', 'ACTIVA')->andReturnSelf();
            $camas->shouldReceive('pluck')->once()->with('cod_cama')->andReturn(collect(['CAM-A', 'CAM-B']));

            $ocupaciones = Mockery::mock();
            $ocupaciones->shouldReceive('whereIn')->once()->with('cod_cama', ['CAM-A', 'CAM-B'])->andReturnSelf();
            $ocupaciones->shouldReceive('where')->twice()->andReturnSelf();
            $ocupaciones->shouldReceive('get')->once()->andReturn(collect([
                (object) ['cod_cama' => 'CAM-A', 'fecha_hora_asignacion' => '2026-09-25 09:00:00', 'fecha_hora_liberacion' => '2026-09-27 09:00:00'],
                (object) ['cod_cama' => 'CAM-B', 'fecha_hora_asignacion' => '2026-09-28 10:00:00', 'fecha_hora_liberacion' => null],
            ]));
            DB::shouldReceive('table')->once()->with('camas')->andReturn($camas);
            DB::shouldReceive('table')->once()->with('ocupaciones_cama')->andReturn($ocupaciones);

            $serie = app(TendenciaOcupacionService::class)->ultimosSieteDias();

            $this->assertSame(2, $serie['total_camas']);
            $this->assertSame(['23/09', '24/09', '25/09', '26/09', '27/09', '28/09', '29/09'], array_column($serie['dias'], 'etiqueta'));
            $this->assertSame([0, 0, 1, 1, 0, 1, 1], array_column($serie['dias'], 'ocupadas'));
        } finally {
            Carbon::setTestNow();
        }
    }
}
