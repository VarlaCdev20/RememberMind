<?php

namespace Tests\Feature;

use App\Modulos\Cuidados\Infraestructura\Persistencia\RegistrosConsumo;
use App\Modulos\Institucion\Presentacion\Livewire\Funcionalidades\PersonalInstitucionalHorarios;
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\Personal;
use App\Models\Turno;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PersonalInstitucionalHorariosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (require base_path('database/propuestas/gerencia/2026_10_26_000002_historia_vinculos_asignaciones.php'))->up();
        \Tests\Support\PrepararHistoriaLaboral::instalar();
        \Spatie\Permission\Models\Permission::findOrCreate('turnos.asignar', 'web');
        $actor = User::factory()->create(['estado' => 'ACTIVO']);
        $actor->givePermissionTo('turnos.asignar');
        $this->actingAs($actor);
    }

    public function test_creates_schedule_rows_and_updates_user_area(): void
    {
        $user = $this->crearUsuarioConRol('ENFERMEROS');
        $area = $this->crearArea('ARE_0004', 'AREA DE ATENCION MEDICA', 'Salud');
        $turno = $this->crearTurno('COD_TUR_000001', 'MANANA', '08:00:00', '16:00:00');

        Livewire::test(PersonalInstitucionalHorarios::class, [
            'usuarioId' => $user->cod_usuario,
        ])
            ->set('f_cod_area', $area->cod_area)
            ->set('f_cod_turno', $turno->cod_turno)
            ->set('f_dias_semana', ['LUNES', 'MARTES'])
            ->set('f_fecha_inicio', '2026-06-08')
            ->set('f_tipo_asignacion', 'ROTATIVO')
            ->set('f_observaciones', 'Cobertura base')
            ->call('previsualizarHorario')
            ->assertHasNoErrors()
            ->assertSet('vistaPreviaHorario.0.horario', '08:00–16:00')
            ->assertSet('vistaPreviaHorario.0.descanso', false)
            ->call('seleccionarDias', 'laborables')
            ->assertSet('f_dias_semana', ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES'])
            ->assertSet('vistaPreviaHorario', [])
            ->set('f_dias_semana', ['LUNES', 'MARTES'])
            ->call('guardar')
            ->assertHasNoErrors();

        $personal = $user->personal()->firstOrFail();
        $this->assertCount(2, AsignacionPersonal::where('cod_personal', $personal->cod_personal)->get());
        $this->assertDatabaseHas('asignaciones_jornada', ['tipo_evento' => 'PERSONAL',
            'cod_personal' => $personal->cod_personal,
            'cod_area' => $area->cod_area,
            'estado' => 'ACTIVO',
        ]);
    }

    public function test_horario_fijo_de_enfermeria_previsualiza_y_guarda_los_dias_hasta_la_fecha_inclusive(): void
    {
        $user = $this->crearUsuarioConRol('ENFERMEROS');
        $area = $this->crearArea('ARE_0004', 'AREA DE ATENCION MEDICA', 'Salud');
        $turno = $this->crearTurno('COD_TUR_000001', 'MANANA', '08:00:00', '16:00:00');

        $formulario = Livewire::test(PersonalInstitucionalHorarios::class, [
            'usuarioId' => $user->getKey(),
        ])
            ->call('abrirFormNuevo')
            ->assertSet('formAbierto', true)
            ->assertSee('Fecha de inicio')
            ->assertDontSee('Repetir hasta')
            ->set('f_cod_area', $area->getKey())
            ->set('f_cod_turno', $turno->getKey())
            ->set('f_tipo_asignacion', 'FIJO')
            ->assertSet('f_tipo_asignacion', 'FIJO')
            ->assertSee('Repetir hasta')
            ->set('f_fecha_inicio', '2026-11-16')
            ->set('f_dias_semana', ['LUNES', 'MIERCOLES'])
            ->set('f_fecha_fin', '2026-11-15')
            ->call('solicitarGuardar')
            ->assertHasErrors(['f_fecha_fin'])
            ->assertNotDispatched('confirmarGuardadoAsignacion')
            ->set('f_fecha_fin', '2026-11-25')
            ->call('solicitarGuardar')
            ->assertHasNoErrors()
            ->assertDispatched('confirmarGuardadoAsignacion', fn ($evento, $datos) => $datos[0]['fechas'] === [
                '16/11/2026', '18/11/2026', '23/11/2026', '25/11/2026',
            ]);

        $this->assertSame(0, RegistrosConsumo::consulta('asignaciones_personal')->count());
        $formulario->call('guardar')->assertHasNoErrors();

        $asignaciones = AsignacionPersonal::where('cod_personal', $user->personal->getKey())->with('jornada')->get();
        $this->assertSame(['2026-11-16', '2026-11-18', '2026-11-23', '2026-11-25'], $asignaciones
            ->map(fn (AsignacionPersonal $asignacion) => $asignacion->jornada->fecha_jornada->toDateString())->sort()->values()->all());
        $this->assertSame(['ABIERTA'], $asignaciones->pluck('jornada.estado')->unique()->values()->all());
    }

    public function test_edicion_posterior_conserva_ocurrencias_anteriores_y_registra_nuevas_fechas(): void
    {
        $user = $this->crearUsuarioConRol('ENFERMEROS');
        $area = $this->crearArea('ARE_0004', 'AREA DE ATENCION MEDICA', 'Salud');
        $turnoManana = $this->crearTurno('COD_TUR_000001', 'MANANA', '08:00:00', '16:00:00');
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
        $this->assertSame(4, AsignacionPersonal::where('cod_personal', $personal->cod_personal)->where('estado', 'ACTIVO')->count());
        $this->assertSame(0, AsignacionPersonal::where('cod_personal', $personal->cod_personal)->where('estado', 'INACTIVO')->count());
        $this->assertSame(2,AsignacionPersonal::where('cod_personal',$personal->cod_personal)->whereHas('jornada',fn($q)=>$q->where('fecha_jornada','<','2026-06-15'))->where('estado','ACTIVO')->count());
        $this->assertDatabaseHas('asignaciones_jornada', ['tipo_evento' => 'PERSONAL',
            'cod_personal' => $personal->cod_personal,
            'cod_area' => $area->cod_area,
            'estado' => 'ACTIVO',
        ]);
    }

    public function test_displays_both_active_assignment_states_and_preserves_finalized_state(): void
    {
        $user = $this->crearUsuarioConRol('ENFERMEROS');
        $area = $this->crearArea('ARE_0004', 'AREA DE ATENCION MEDICA', 'Salud');
        $turno = $this->crearTurno('COD_TUR_000001', 'MANANA', '08:00:00', '16:00:00');

        Livewire::test(PersonalInstitucionalHorarios::class, ['usuarioId' => $user->cod_usuario])
            ->set('f_cod_area', $area->cod_area)
            ->set('f_cod_turno', $turno->cod_turno)
            ->set('f_dias_semana', ['LUNES'])
            ->set('f_fecha_inicio', '2026-06-08')
            ->set('f_tipo_asignacion', 'ROTATIVO')
            ->call('guardar')
            ->assertHasNoErrors();

        $asignacion = AsignacionPersonal::where('cod_personal', $user->personal->cod_personal)->sole();

        foreach (['ACTIVO' => 'ACTIVA', 'ACTIVA' => 'ACTIVA', 'INACTIVO' => 'FINALIZADA'] as $guardado => $visible) {
            $asignacion->update(['estado' => $guardado]);

            Livewire::test(PersonalInstitucionalHorarios::class, ['usuarioId' => $user->cod_usuario])
                ->assertSet('asignaciones.0.estado', $visible);

            $this->assertSame($guardado, $asignacion->fresh()->estado);
        }
    }

    public function test_el_formulario_administrativo_ofrece_jornadas_de_ocho_horas(): void
    {
        $usuario = $this->crearUsuarioConRol('ADMINISTRADOR');
        $ordinario = $this->crearTurno('TUR_0001', 'JORNADA DIARIA', '08:00:00', '16:00:00');
        $this->crearTurno('TUR_0002', 'GUARDIA ESPECIAL', '20:00:00', '08:00:00');

        Livewire::test(PersonalInstitucionalHorarios::class, ['usuarioId' => $usuario->getKey()])
            ->assertSet('turnos', fn ($turnos) => count($turnos) === 1 && $turnos[0]['cod_turno'] === $ordinario->getKey());
        $this->assertDatabaseCount('turnos', 2);
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

    private function crearArea(string $codigo, string $nombre, string $tipoArea): Area
    {
        return Area::updateOrCreate(
            ['cod_area' => $codigo],
            [
                'nombre' => $nombre,
                'descripcion' => $tipoArea,
                'estado' => 'ACTIVO',
            ]
        );
    }

    private function crearTurno(string $codigo, string $nombre, string $horaInicio, string $horaFin): Turno
    {
        return Turno::create([
            'cod_turno' => $codigo,
            'nombre' => $nombre,
            'hora_inicio' => $horaInicio,
            'hora_cierre' => $horaFin,
            'orden' => (int) substr($codigo, -1),
            'estado' => 'ACTIVO',
        ]);
    }
}
