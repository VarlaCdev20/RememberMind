<?php

namespace Tests\Feature;

use App\Modulos\Cuidados\Infraestructura\Persistencia\RegistrosConsumo;
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\Jornada;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Institucion\Aplicacion\Acciones\GestionarHorariosPersonal;
use App\Modulos\Institucion\Aplicacion\Acciones\GestionarPlazasEnfermeria;
use App\Modulos\Institucion\Aplicacion\Consultas\ConsultarAvisosPlanificacion;
use App\Modulos\Institucion\Presentacion\Livewire\Funcionalidades\PersonalInstitucionalHorarios;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RotacionEnfermeriaTest extends TestCase
{
    use RefreshDatabase;

    private User $trabajador;

    private Area $area;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('turnos.asignar', 'web');
        $actor = User::factory()->create();
        $actor->givePermissionTo('turnos.asignar');
        $this->actingAs($actor);
        app('request')->setMethod('POST');
        Role::findOrCreate('ENFERMEROS', 'web');
        $this->trabajador = User::factory()->create(['nombres' => 'Profesional', 'apellido_paterno' => 'Prueba']);
        $this->trabajador->assignRole('ENFERMEROS');
        $this->area = Area::create(['nombre' => 'Área de prueba', 'estado' => 'ACTIVO']);
        foreach (GestionarHorariosPersonal::TURNOS_ROTACION as $definicion) {
            Turno::create([
                'nombre' => $definicion['catalogo'][0], 'hora_inicio' => $definicion['inicio'].':00',
                'hora_cierre' => $definicion['fin'].':00', 'estado' => 'ACTIVO',
            ]);
        }
    }

    private function entrada(array $cambios = []): array
    {
        return array_replace([
            'usuarioId' => $this->trabajador->getKey(), 'f_cod_area' => $this->area->getKey(),
            'f_tipo_asignacion' => 'ROTATIVO', 'f_rotacion_automatica' => true,
            'f_turno_inicial' => 'NOCHE', 'f_fecha_inicio' => '2026-11-16', 'f_fecha_fin' => '2026-11-22',
            'modoEdicion' => false,
        ], $cambios);
    }

    public function test_iniciar_en_noche_previsualiza_descanso_y_guarda_el_ciclo_en_mis_horarios(): void
    {
        $formulario = Livewire::test(PersonalInstitucionalHorarios::class, ['usuarioId' => $this->trabajador->getKey()])
            ->call('abrirFormNuevo')
            ->assertSet('f_rotacion_automatica', true)
            ->assertSee('Turno inicial')
            ->assertDontSee('Días laborales')
            ->set('f_cod_area', $this->area->getKey())
            ->set('f_turno_inicial', 'NOCHE')
            ->set('f_fecha_inicio', '2026-11-16')
            ->set('f_fecha_fin', '2026-11-22')
            ->call('previsualizarHorario')
            ->assertHasNoErrors()
            ->assertSet('vistaPreviaHorario.0.nombre', 'Noche')
            ->assertSet('vistaPreviaHorario.0.cruza_dia', true)
            ->assertSet('vistaPreviaHorario.1.descanso', true)
            ->set('f_turno_inicial', 'MANANA')
            ->assertSet('vistaPreviaHorario', [])
            ->set('f_turno_inicial', 'NOCHE')
            ->call('solicitarGuardar')
            ->assertHasNoErrors()
            ->assertDispatched('confirmarGuardadoAsignacion', fn ($evento, $datos) => $datos[0]['fechas'] === [
                '16/11/2026 · NOCHE', '17/11/2026 · DESCANSO · 07:00–07:00 (+1 día)', '18/11/2026 · MAÑANA',
                '19/11/2026 · TARDE', '20/11/2026 · NOCHE', '21/11/2026 · DESCANSO · 07:00–07:00 (+1 día)', '22/11/2026 · MAÑANA',
            ]);
        $this->assertSame(0, RegistrosConsumo::consulta('asignaciones_personal')->count());
        $this->exportarFormulario($formulario->html());
        $formulario->call('guardar')->assertHasNoErrors();
        $this->assertSame(5, RegistrosConsumo::consulta('asignaciones_personal')->count());
        $this->assertDatabaseCount('jornadas', 5);

        $this->travelTo(CarbonImmutable::parse('2026-11-16 06:00:00'));
        $propios = app(ConsultarAvisosPlanificacion::class)->horariosPropios($this->trabajador)->items();
        $this->assertSame(['TURNO NOCHE', 'TURNO MAÑANA', 'TURNO TARDE', 'TURNO NOCHE', 'TURNO MAÑANA'], array_column($propios, 'turno'));
        $this->assertSame('2026-11-17 07:00', $propios[0]['fin']->format('Y-m-d H:i'));
        $this->assertSame(24.0, $propios[0]['fin']->diffInHours($propios[1]['inicio']));
    }

    public function test_el_ciclo_tambien_puede_iniciar_en_manana_o_tarde(): void
    {
        $accion = app(GestionarHorariosPersonal::class);
        $this->assertSame(['MANANA', 'TARDE', 'NOCHE', null, 'MANANA', 'TARDE', 'NOCHE'], array_values($accion->previsualizarRotacion($this->entrada(['f_turno_inicial' => 'MANANA']))));
        $this->assertSame(['TARDE', 'NOCHE', null, 'MANANA', 'TARDE', 'NOCHE', null], array_values($accion->previsualizarRotacion($this->entrada(['f_turno_inicial' => 'TARDE']))));
        $this->assertSame(0, RegistrosConsumo::consulta('asignaciones_personal')->count());
    }

    public function test_otro_horario_no_puede_invadir_el_descanso_posterior_a_la_noche(): void
    {
        $accion = app(GestionarHorariosPersonal::class);
        $accion->guardar($this->entrada(['f_fecha_fin' => '2026-11-16']));
        $manana = Turno::where('nombre', 'TURNO MAÑANA')->sole();
        try {
            $accion->guardar($this->entrada([
                'f_rotacion_automatica' => false, 'f_tipo_asignacion' => 'FIJO', 'f_cod_turno' => $manana->getKey(),
                'f_dias_semana' => ['MARTES'], 'f_fecha_inicio' => '2026-11-17', 'f_fecha_fin' => null,
            ]));
            $this->fail('Debe conservar las 24 horas completas de descanso.');
        } catch (ValidationException $error) {
            $this->assertStringContainsString('24 horas de descanso', $error->errors()['f_dias_semana'][0]);
        }
        $this->assertSame(1, RegistrosConsumo::consulta('asignaciones_personal')->count());
    }

    public function test_un_turno_existente_en_el_descanso_rechaza_la_rotacion_completa(): void
    {
        $manana = Turno::where('nombre', 'TURNO MAÑANA')->sole();
        $jornada = Jornada::create(['cod_turno' => $manana->getKey(), 'fecha_jornada' => '2026-11-21', 'estado' => 'ABIERTA']);
        AsignacionPersonal::create(['cod_jornada' => $jornada->getKey(), 'cod_personal' => $this->trabajador->personal->getKey(), 'cod_area' => $this->area->getKey(), 'estado' => 'ACTIVO']);
        try {
            app(GestionarHorariosPersonal::class)->guardar($this->entrada());
            $this->fail('Debe rechazar la rotación que invade un descanso.');
        } catch (ValidationException $error) {
            $this->assertArrayHasKey('f_rotacion_automatica', $error->errors());
        }
        $this->assertSame(1, RegistrosConsumo::consulta('asignaciones_personal')->count());
        $this->assertDatabaseCount('jornadas', 1);
    }

    public function test_asignar_una_plaza_tambien_respeta_el_descanso_de_la_rotacion(): void
    {
        $this->area->update(['nombre' => 'Enfermería de prueba']);
        app(GestionarHorariosPersonal::class)->guardar($this->entrada(['f_fecha_fin' => '2026-11-16']));
        try {
            app(GestionarPlazasEnfermeria::class)->guardarAsignacionPlaza(auth()->user(), [
                'plazaSeleccionada' => '1', 'enfermeroSeleccionado' => $this->trabajador->getKey(),
                'tipoAsignacion' => 'REGULAR', 'fechaSeleccionadaPlaza' => '2026-11-17',
                'motivoAsignacion' => 'Prueba sintética', 'turnoCodigo' => 'MANANA',
            ]);
            $this->fail('Una plaza tampoco puede ocupar el descanso posterior a la noche.');
        } catch (HttpException $error) {
            $this->assertSame(422, $error->getStatusCode());
            $this->assertStringContainsString('24 horas de descanso', $error->getMessage());
        }
        $this->assertSame(1, RegistrosConsumo::consulta('asignaciones_personal')->count());
    }

    public function test_fallo_en_una_jornada_posterior_revierte_toda_la_rotacion(): void
    {
        $tarde = Turno::where('nombre', 'TURNO TARDE')->sole();
        Jornada::create(['cod_turno' => $tarde->getKey(), 'fecha_jornada' => '2026-11-19', 'estado' => 'CERRADA']);
        try {
            app(GestionarHorariosPersonal::class)->guardar($this->entrada());
            $this->fail('Debe revertir también las jornadas anteriores al fallo.');
        } catch (ValidationException $error) {
            $this->assertStringContainsString('19/11/2026', $error->errors()['f_fecha_inicio'][0]);
        }
        $this->assertSame(0, RegistrosConsumo::consulta('asignaciones_personal')->count());
        $this->assertDatabaseCount('jornadas', 1);
    }

    public function test_la_rotacion_exige_enfermeria_fecha_final_y_turno_inicial_validos(): void
    {
        $accion = app(GestionarHorariosPersonal::class);
        foreach ([['f_fecha_fin' => null], ['f_fecha_fin' => '2026-11-15'], ['f_turno_inicial' => 'MANIPULADO']] as $cambio) {
            try {
                $accion->guardar($this->entrada($cambio));
                $this->fail('Debe rechazar los datos incompletos o inválidos.');
            } catch (ValidationException $error) {
                $this->assertArrayHasKey(array_key_first($cambio), $error->errors());
            }
        }
        Role::findOrCreate('ADMINISTRADOR', 'web');
        $this->trabajador->syncRoles(['ADMINISTRADOR']);
        try {
            $accion->guardar($this->entrada());
            $this->fail('La rotación de Enfermería exige el rol correspondiente.');
        } catch (ValidationException $error) {
            $this->assertArrayHasKey('f_rotacion_automatica', $error->errors());
        }
        $this->assertSame(0, RegistrosConsumo::consulta('asignaciones_personal')->count());
    }

    private function exportarFormulario(string $html): void
    {
        $directorio = getenv('RM_ROTACION_QA_DIR');
        if ($directorio) {
            if (! is_dir($directorio)) {
                mkdir($directorio, 0700, true);
            }
            file_put_contents($directorio.'/rotacion.html', $html);
        }
    }
}
