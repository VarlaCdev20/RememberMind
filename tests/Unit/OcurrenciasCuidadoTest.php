<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\EjecucionCuidado;
use App\Models\ProgramacionCuidado;
use App\Models\Turno;
use App\Modulos\Cuidados\Aplicacion\Consultas\OcurrenciasCuidado;
use Carbon\Carbon;
use Tests\TestCase;

final class OcurrenciasCuidadoTest extends TestCase
{
    public function test_respeta_dia_turno_fechas_estado_y_hora_explicita(): void
    {
        $servicio = new OcurrenciasCuidado;
        $fecha = Carbon::parse('2026-10-20 09:00:00');
        $turno = new Turno(['cod_turno' => 'T-TEST']);
        $base = ['estado' => 'ACTIVA', 'hora_programada' => '10:00', 'dias_semana' => 'MARTES',
            'cod_turno' => 'T-TEST', 'fecha_activacion' => '2026-10-20', 'fecha_desactivacion' => '2026-10-20'];
        self::assertSame('2026-10-20 10:00:00', $servicio->momento(new ProgramacionCuidado($base), $fecha, $turno)->toDateTimeString());
        foreach ([['hora_programada' => null], ['hora_programada' => '25:00'], ['estado' => 'INACTIVA'],
            ['dias_semana' => 'LUNES'], ['cod_turno' => 'OTRO'], ['fecha_activacion' => '2026-10-21'],
            ['fecha_desactivacion' => '2026-10-19']] as $cambio) {
            self::assertNull($servicio->momento(new ProgramacionCuidado($cambio + $base), $fecha, $turno));
        }
    }

    public function test_ocurrencias_nocturnas_se_distinguen_por_fecha_exacta_y_estado(): void
    {
        $servicio = new OcurrenciasCuidado;
        $ejecucion = new EjecucionCuidado(['cod_residente' => 'R-TEST', 'cod_intervencion' => 'I-TEST',
            'cod_jornada' => 'MISMA-GUARDIA', 'fecha_hora_programada' => '2026-10-21 02:00:00', 'estado' => 'EN_PROCESO']);
        self::assertTrue($servicio->corresponde($ejecucion, 'R-TEST', 'I-TEST', Carbon::parse('2026-10-21 02:00:00')));
        self::assertFalse($servicio->corresponde($ejecucion, 'R-TEST', 'I-TEST', Carbon::parse('2026-10-20 02:00:00')));
        self::assertFalse($servicio->corresponde($ejecucion, 'OTRO', 'I-TEST', Carbon::parse('2026-10-21 02:00:00')));
        self::assertSame('PENDIENTE', $servicio->estado($ejecucion));
        $ejecucion->estado = 'NO_REALIZADA';
        self::assertSame('NO_REALIZADA', $servicio->estado($ejecucion));
        $ejecucion->estado = 'EJECUTADA';
        self::assertSame('REALIZADA', $servicio->estado($ejecucion));
        $ejecucion->estado = 'CANCELADA';
        self::assertSame('CANCELADA', $servicio->estado($ejecucion));
    }
}
