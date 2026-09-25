<?php

namespace Tests\Feature;

use App\Frontend\Livewire\Administracion\Identidad\PersonalInstitucionalHorarios;
use App\Models\AreaInstitucional;
use App\Models\AsignacionPersonal;
use App\Models\Personal;
use App\Models\TurnoInstitucional;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PersonalInstitucionalHorariosTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_schedule_rows_and_updates_user_area(): void
    {
        $user = $this->crearUsuarioConRol('ENFERMEROS');
        $area = $this->crearArea('ARE_0004', 'AREA DE ATENCION MEDICA', 'Salud');
        $turno = $this->crearTurno('TUR_0001', 'MANANA', '08:00:00', '16:00:00');

        Livewire::test(PersonalInstitucionalHorarios::class, [
            'usuarioId' => $user->cod_usuario,
        ])
            ->set('f_cod_area', $area->cod_area)
            ->set('f_cod_turno', $turno->cod_turno)
            ->set('f_dias_semana', ['LUNES', 'MARTES'])
            ->set('f_fecha_inicio', '2026-06-08')
            ->set('f_tipo_asignacion', 'ROTATIVO')
            ->set('f_observaciones', 'Cobertura base')
            ->call('guardar')
            ->assertHasNoErrors();

        $personal = $user->personal()->firstOrFail();
        $this->assertCount(2, AsignacionPersonal::where('cod_personal', $personal->cod_personal)->get());
        $this->assertDatabaseHas('asignaciones_personal', [
            'cod_personal' => $personal->cod_personal,
            'cod_area' => $area->cod_area,
            'estado' => 'ACTIVO',
        ]);
    }

    public function test_editing_an_assignment_finalizes_previous_rows_and_creates_a_new_version(): void
    {
        $user = $this->crearUsuarioConRol('ENFERMEROS');
        $area = $this->crearArea('ARE_0004', 'AREA DE ATENCION MEDICA', 'Salud');
        $turnoManana = $this->crearTurno('TUR_0001', 'MANANA', '08:00:00', '16:00:00');
        $turnoTarde = $this->crearTurno('TUR_0002', 'TARDE', '16:00:00', '22:00:00');

        Livewire::test(PersonalInstitucionalHorarios::class, [
            'usuarioId' => $user->cod_usuario,
        ])
            ->set('f_cod_area', $area->cod_area)
            ->set('f_cod_turno', $turnoManana->cod_turno)
            ->set('f_dias_semana', ['LUNES', 'MARTES'])
            ->set('f_fecha_inicio', '2026-06-08')
            ->set('f_tipo_asignacion', 'ROTATIVO')
            ->call('guardar')
            ->assertHasNoErrors();

        $componente = Livewire::test(PersonalInstitucionalHorarios::class, [
            'usuarioId' => $user->cod_usuario,
        ]);

        $asignacion = collect($componente->get('asignaciones'))->first();

        $componente
            ->call('editarAsignacion', $asignacion['cod_asignacion'])
            ->set('f_cod_area', $area->cod_area)
            ->set('f_cod_turno', $turnoTarde->cod_turno)
            ->set('f_dias_semana', ['MIERCOLES', 'JUEVES'])
            ->set('f_fecha_inicio', '2026-06-15')
            ->set('f_tipo_asignacion', 'ROTATIVO')
            ->set('f_observaciones', 'Cambio de jornada')
            ->call('guardar')
            ->assertHasNoErrors();

        $personal = $user->personal()->firstOrFail();
        $this->assertSame(2, AsignacionPersonal::where('cod_personal', $personal->cod_personal)->where('estado', 'ACTIVO')->count());
        $this->assertSame(2, AsignacionPersonal::where('cod_personal', $personal->cod_personal)->where('estado', 'INACTIVO')->count());
        $this->assertDatabaseHas('asignaciones_personal', [
            'cod_personal' => $personal->cod_personal,
            'cod_area' => $area->cod_area,
            'estado' => 'ACTIVO',
        ]);
    }

    private function crearUsuarioConRol(string $rol): User
    {
        Role::findOrCreate($rol, 'web');

        $user = User::factory()->create();
        Personal::query()->create([
            'cod_personal' => 'PER_'.substr($user->cod_usuario, 4),
            'cod_usuario' => $user->cod_usuario,
            'nombres' => 'Carla',
            'apellido_paterno' => 'Valeria',
            'numero_documento' => 'DOC-'.substr($user->cod_usuario, 4),
            'profesion' => 'ENFERMERÍA',
            'estado' => 'ACTIVO',
        ]);

        $user->assignRole($rol);

        return $user;
    }

    private function crearArea(string $codigo, string $nombre, string $tipoArea): AreaInstitucional
    {
        return AreaInstitucional::updateOrCreate(
            ['cod_area' => $codigo],
            [
                'nombre' => $nombre,
                'descripcion' => $tipoArea,
                'estado' => 'ACTIVO',
            ]
        );
    }

    private function crearTurno(string $codigo, string $nombre, string $horaInicio, string $horaFin): TurnoInstitucional
    {
        return TurnoInstitucional::create([
            'cod_turno' => $codigo,
            'nombre' => $nombre,
            'hora_inicio' => $horaInicio,
            'hora_cierre' => $horaFin,
            'orden' => (int) substr($codigo, -1),
            'estado' => 'ACTIVO',
        ]);
    }
}
