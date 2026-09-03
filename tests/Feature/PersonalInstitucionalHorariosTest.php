<?php

namespace Tests\Feature;

use App\Livewire\Admin\PersonalInstitucional\Partials\PersonalInstitucionalHorarios;
use App\Models\AreaInstitucional;
use App\Models\HorarioPersonalSalud;
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
            'usuarioId' => $user->cod_usu,
        ])
            ->set('f_cod_area', $area->cod_area)
            ->set('f_cod_turno', $turno->cod_turno)
            ->set('f_dias_semana', ['LUNES', 'MARTES'])
            ->set('f_fecha_inicio', '2026-06-08')
            ->set('f_tipo_asignacion', 'ROTATIVO')
            ->set('f_observaciones', 'Cobertura base')
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertSame($area->cod_area, $user->fresh()->cod_area);
        $this->assertCount(2, HorarioPersonalSalud::where('cod_usu', $user->cod_usu)->get());
        $this->assertDatabaseHas('horarios_personal_salud', [
            'cod_usu' => $user->cod_usu,
            'dia_semana' => 'LUNES',
            'turno' => 'MANANA',
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
            'usuarioId' => $user->cod_usu,
        ])
            ->set('f_cod_area', $area->cod_area)
            ->set('f_cod_turno', $turnoManana->cod_turno)
            ->set('f_dias_semana', ['LUNES', 'MARTES'])
            ->set('f_fecha_inicio', '2026-06-08')
            ->set('f_tipo_asignacion', 'ROTATIVO')
            ->call('guardar')
            ->assertHasNoErrors();

        $componente = Livewire::test(PersonalInstitucionalHorarios::class, [
            'usuarioId' => $user->cod_usu,
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

        $this->assertSame(2, HorarioPersonalSalud::where('cod_usu', $user->cod_usu)->where('estado', 'ACTIVO')->count());
        $this->assertSame(2, HorarioPersonalSalud::where('cod_usu', $user->cod_usu)->where('estado', 'INACTIVO')->count());
        $this->assertDatabaseHas('horarios_personal_salud', [
            'cod_usu' => $user->cod_usu,
            'dia_semana' => 'MIERCOLES',
            'turno' => 'TARDE',
            'estado' => 'ACTIVO',
        ]);
    }

    private function crearUsuarioConRol(string $rol): User
    {
        Role::findOrCreate($rol, 'web');

        $user = User::create([
            'nombres' => 'Carla',
            'ap_paterno' => 'Valeria',
            'correo' => fake()->unique()->safeEmail(),
            'password' => 'password',
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
                'slug' => strtolower($codigo),
                'tipo_area' => $tipoArea,
                'estado' => 'ACTIVA',
                'orden' => 1,
            ]
        );
    }

    private function crearTurno(string $codigo, string $nombre, string $horaInicio, string $horaFin): TurnoInstitucional
    {
        return TurnoInstitucional::create([
            'cod_turno' => $codigo,
            'nombre' => $nombre,
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
            'estado' => 'ACTIVO',
            'color' => '#63775B',
        ]);
    }
}
