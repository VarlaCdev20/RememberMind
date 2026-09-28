<?php

namespace Tests\Feature;

use App\Backend\Modulos\Enfermeria\Servicios\PaseTurnoService;
use App\Models\Jornada;
use App\Models\Turno;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PaseTurnoIntegridadTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_crea_turnos_ni_jornadas_si_el_personal_no_tiene_contexto_operativo(): void
    {
        $usuario = User::factory()->create([
            'nombres' => 'Rosa',
            'ap_paterno' => 'Mamani',
            'estado' => 'ACTIVO',
        ]);

        try {
            app(PaseTurnoService::class)->resolverJornadaSaliente($usuario);
            $this->fail('Se esperaba una validación por falta de turno y jornada asignados.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('jornada', $exception->errors());
        }

        $this->assertDatabaseCount('turnos', 0);
        $this->assertDatabaseCount('jornadas', 0);
    }

    public function test_no_crea_la_jornada_entrante_si_no_fue_planificada(): void
    {
        $turno = Turno::create([
            'cod_turno' => 'TUR_INTEGRIDAD',
            'nombre' => 'Turno de integridad',
            'hora_inicio' => '07:00:00',
            'hora_cierre' => '15:00:00',
            'orden' => 1,
            'estado' => 'ACTIVO',
        ]);

        $jornada = Jornada::create([
            'cod_jornada' => 'JOR_INTEGRIDAD',
            'cod_turno' => $turno->cod_turno,
            'fecha_jornada' => today(),
            'estado' => 'ABIERTA',
        ]);

        try {
            app(PaseTurnoService::class)->resolverJornadaEntrante($jornada);
            $this->fail('Se esperaba una validación por falta de jornada entrante planificada.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('jornada_entrante', $exception->errors());
        }

        $this->assertDatabaseCount('turnos', 1);
        $this->assertDatabaseCount('jornadas', 1);
    }
}
