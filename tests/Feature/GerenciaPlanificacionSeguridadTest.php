<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Institucion\Aplicacion\Acciones\GestionarHorariosPersonal;
use App\Modulos\Institucion\Aplicacion\Acciones\GestionarPlazasEnfermeria;
use App\Modulos\Institucion\Aplicacion\Acciones\GestionarTurnosInstitucionales;
use App\Modulos\Institucion\Presentacion\Livewire\Funcionalidades\PersonalInstitucionalHorarios;
use App\Modulos\Institucion\Presentacion\Livewire\Paginas\TurnosAsignacionesPanel;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class GerenciaPlanificacionSeguridadTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    private User $trabajador;

    private Area $area;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->actor = User::factory()->create(['estado' => 'ACTIVO']);
        $this->actor->assignRole('GERENTE');
        $this->trabajador = User::factory()->create(['estado' => 'ACTIVO']);
        $this->trabajador->assignRole('ENFERMEROS');
        Personal::create(['cod_usuario' => $this->trabajador->getKey(), 'nombres' => 'Persona', 'apellido_paterno' => 'Sintetica', 'numero_documento' => '1234567', 'profesion' => 'ENFERMERIA', 'estado' => 'ACTIVO']);
        $this->area = Area::create(['nombre' => 'Área de prueba', 'estado' => 'ACTIVO']);
        $this->actingAs($this->actor);
        app('request')->setMethod('POST');
    }

    public function test_noche_del_dia_anterior_impide_asignacion_superpuesta_y_preserva_sql(): void
    {
        $noche = $this->turno('Noche', '20:00', '08:00');
        $manana = $this->turno('Mañana', '07:00', '15:00');
        $jornada = Jornada::create(['cod_turno' => $noche->getKey(), 'fecha_jornada' => '2026-10-25', 'estado' => 'ABIERTA']);
        AsignacionPersonal::create(['cod_jornada' => $jornada->getKey(), 'cod_personal' => $this->trabajador->personal->getKey(), 'cod_area' => $this->area->getKey(), 'estado' => 'ACTIVA']);
        try {
            app(GestionarHorariosPersonal::class)->guardar($this->entrada($manana));
            $this->fail('Debió rechazar el cruce nocturno.');
        } catch (ValidationException $error) {
            $this->assertArrayHasKey('f_dias_semana', $error->errors());
        }
        $this->assertDatabaseCount('asignaciones_personal', 1);
        $this->assertDatabaseCount('jornadas', 1);
    }

    public function test_fin_de_guardia_contiguo_no_es_solapamiento(): void
    {
        $noche = $this->turno('Noche', '20:00', '08:00');
        $manana = $this->turno('Mañana', '08:00', '16:00');
        $jornada = Jornada::create(['cod_turno' => $noche->getKey(), 'fecha_jornada' => '2026-10-25', 'estado' => 'ABIERTA']);
        AsignacionPersonal::create(['cod_jornada' => $jornada->getKey(), 'cod_personal' => $this->trabajador->personal->getKey(), 'cod_area' => $this->area->getKey(), 'estado' => 'ACTIVO']);
        app(GestionarHorariosPersonal::class)->guardar($this->entrada($manana));
        $this->assertDatabaseCount('asignaciones_personal', 2);
        $this->get(route('admin.cobertura-equipo.index', ['fecha' => '2026-10-25']))
            ->assertOk()->assertSeeText('26/10 08:00')->assertSeeText('Persona Sintetica');
    }

    public function test_calendario_gerencial_inicia_en_semana_y_muestra_estados_activo_y_activa(): void
    {
        $this->travelTo(Carbon::parse('2026-10-26 10:00:00'));
        $turno = $this->turno('Turno nocturno', '20:00', '08:00');
        $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => '2026-10-26', 'estado' => 'ABIERTA']);
        AsignacionPersonal::create(['cod_personal' => $this->trabajador->personal->getKey(), 'cod_area' => $this->area->getKey(), 'cod_jornada' => $jornada->getKey(), 'estado' => 'ACTIVO']);
        $calendario = Livewire::test(TurnosAsignacionesPanel::class)->assertSet('tipoPlanilla', 'general')->assertSet('vistaCalendario', 'semana')
            ->assertSee('Planificación del equipo')->assertSee('Persona Sintetica')->assertSee('27/10 08:00')->assertDontSee('Turnos por cubrir');
        $calendario->call('cambiarVista', 'mes')->assertSee('personas con asignación')->call('seleccionarFecha', '2026-10-26')->assertSet('vistaCalendario', 'dia')->assertSee('Turno nocturno');
        $this->assertDatabaseCount('asignaciones_personal', 1);
    }

    public function test_lectura_de_personal_no_concede_mutar_horarios_y_revocacion_se_revalida(): void
    {
        $turno = $this->turno('Mañana', '08:00', '16:00');
        $componente = Livewire::test(PersonalInstitucionalHorarios::class, ['usuarioId' => $this->trabajador->getKey()]);
        Role::findByName('GERENTE', 'web')->revokePermissionTo('turnos.asignar');
        $componente->call('guardar')->assertForbidden();
        try {
            app(GestionarHorariosPersonal::class)->guardar($this->entrada($turno));
            $this->fail('La Action también debe denegar.');
        } catch (HttpException $error) {
            $this->assertSame(403, $error->getStatusCode());
        }
        $this->assertDatabaseCount('asignaciones_personal', 0);
    }

    public function test_cobertura_requiere_los_dos_permisos_y_no_es_el_catalogo_de_turnos(): void
    {
        $this->get(route('admin.cobertura-equipo.index'))->assertOk()->assertSeeText('Cobertura del equipo')->assertSeeText('Sin asignaciones en esta consulta');
        $this->getJson(route('admin.cobertura-equipo.index', ['fecha' => 'incorrecta']))->assertUnprocessable();
        Role::findByName('GERENTE', 'web')->revokePermissionTo('asignaciones_personal.ver');
        $this->get(route('admin.cobertura-equipo.index'))->assertForbidden();
    }

    private function turno(string $nombre, string $inicio, string $fin): Turno
    {
        return Turno::create(['nombre' => $nombre, 'hora_inicio' => $inicio, 'hora_cierre' => $fin, 'estado' => 'ACTIVO']);
    }

    private function entrada(Turno $turno): array
    {
        return ['usuarioId' => $this->trabajador->getKey(), 'f_cod_area' => $this->area->getKey(),
            'f_cod_turno' => $turno->getKey(), 'f_dias_semana' => ['LUNES'], 'f_fecha_inicio' => '2026-10-26',
            'f_tipo_asignacion' => 'REGULAR', 'f_observaciones' => 'Escenario sintético', 'modoEdicion' => false,
            'codAsignacionEditar' => null, 'asignaciones' => []];
    }

    public function test_jornada_cerrada_no_recibe_asignaciones_ni_eventos(): void
    {
        $turno = $this->turno('Mañana', '08:00', '16:00');
        Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => '2026-10-26', 'estado' => 'CERRADA']);
        try {
            app(GestionarHorariosPersonal::class)->guardar($this->entrada($turno));
            $this->fail('No debe reabrir una jornada cerrada.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('f_fecha_inicio', $e->errors());
        }
        $this->assertDatabaseCount('asignaciones_personal', 0);
        $this->assertDatabaseCount('jornadas', 1);
    }

    public function test_edicion_de_grupo_legacy_conserva_guardia_anterior_en_control_de_solapamiento(): void
    {
        (require base_path('database/propuestas/gerencia/2026_10_26_000002_historia_vinculos_asignaciones.php'))->up();
        $noche = $this->turno('Noche', '20:00', '08:00');
        $manana = $this->turno('Mañana', '07:00', '15:00');
        $domingo = Jornada::create(['cod_turno' => $noche->getKey(), 'fecha_jornada' => '2026-10-25', 'estado' => 'ABIERTA']);
        $lunes = Jornada::create(['cod_turno' => $noche->getKey(), 'fecha_jornada' => '2026-10-26', 'estado' => 'ABIERTA']);
        $meta = json_encode(['grupo' => 'GRUPO_ANTERIOR', 'observaciones' => 'Dato anterior conservado']);
        $previa = AsignacionPersonal::create(['cod_personal' => $this->trabajador->personal->getKey(), 'cod_area' => $this->area->getKey(), 'cod_jornada' => $domingo->getKey(), 'estado' => 'ACTIVO', 'observacion' => $meta]);
        $editar = AsignacionPersonal::create(['cod_personal' => $this->trabajador->personal->getKey(), 'cod_area' => $this->area->getKey(), 'cod_jornada' => $lunes->getKey(), 'estado' => 'ACTIVO', 'observacion' => $meta]);
        try {
            app(GestionarHorariosPersonal::class)->guardar([...$this->entrada($manana), 'modoEdicion' => true, 'codAsignacionEditar' => $editar->getKey()]);
            $this->fail('La noche anterior conservada tiene una hora de solapamiento.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('f_dias_semana', $e->errors());
        }
        $this->assertSame('ACTIVO', $previa->fresh()->estado);
        $this->assertSame('ACTIVO', $editar->fresh()->estado);
        $this->assertDatabaseCount('asignaciones_personal', 2);
        $this->assertDatabaseCount('eventos_asignacion_personal', 0);
    }

    public function test_plaza_tampoco_duplica_una_jornada_cerrada_por_formato_de_fecha(): void
    {
        $this->area->update(['nombre' => 'Enfermería']);
        $turno = $this->turno('TURNO MAÑANA', '08:00', '16:00');
        Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => '2026-10-26', 'estado' => 'CERRADA']);
        try {
            app(GestionarPlazasEnfermeria::class)->guardarAsignacionPlaza($this->actor, [
                'plazaSeleccionada' => '1', 'enfermeroSeleccionado' => $this->trabajador->getKey(),
                'tipoAsignacion' => 'REGULAR', 'fechaSeleccionadaPlaza' => '2026-10-26',
                'motivoAsignacion' => 'Prueba de jornada cerrada', 'turnoCodigo' => 'MANANA',
            ]);
            $this->fail('La plaza tampoco puede abrir una segunda jornada.');
        } catch (HttpException $error) {
            $this->assertSame(422, $error->getStatusCode());
        }
        $this->assertDatabaseCount('asignaciones_personal', 0);
        $this->assertDatabaseCount('jornadas', 1);
    }

    public function test_finalizar_grupo_no_sobrescribe_ocurrencias_anuladas_y_rechaza_origen_inactivo(): void
    {
        (require base_path('database/propuestas/gerencia/2026_10_26_000002_historia_vinculos_asignaciones.php'))->up();
        $turno = $this->turno('Mañana', '08:00', '16:00');
        $meta = json_encode(['grupo' => 'GRUPO_HISTORICO', 'observaciones' => 'Texto original']);
        $filas = collect(['ACTIVO', 'ANULADA'])->map(function ($estado, $indice) use ($turno, $meta) {
            $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => '2026-10-'.(26 + $indice), 'estado' => 'ABIERTA']);

            return AsignacionPersonal::create(['cod_personal' => $this->trabajador->personal->getKey(), 'cod_area' => $this->area->getKey(),
                'cod_jornada' => $jornada->getKey(), 'estado' => $estado, 'observacion' => $meta]);
        });
        $entrada = ['usuarioId' => $this->trabajador->getKey(), 'f_fecha_fin_finalizar' => '2026-10-26', 'codAsignacionFinalizar' => $filas[0]->getKey()];
        app(GestionarHorariosPersonal::class)->confirmarFinalizar($entrada);
        $this->assertSame('INACTIVO', $filas[0]->fresh()->estado);
        $this->assertSame('ANULADA', $filas[1]->fresh()->estado);
        $this->assertSame($meta, $filas[1]->fresh()->observacion);
        $this->assertDatabaseCount('eventos_asignacion_personal', 1);
        $this->assertDatabaseMissing('eventos_asignacion_personal', ['cod_asignacion_personal' => $filas[1]->getKey()]);
        try {
            app(GestionarHorariosPersonal::class)->confirmarFinalizar([...$entrada, 'codAsignacionFinalizar' => $filas[1]->getKey()]);
            $this->fail('Una ocurrencia anulada no puede servir de origen para finalizar.');
        } catch (ValidationException $error) {
            $this->assertArrayHasKey('f_fecha_fin_finalizar', $error->errors());
        }
        $this->assertDatabaseCount('eventos_asignacion_personal', 1);
    }

    public function test_turno_referenciado_conserva_horas_historicas_pero_permite_corregir_nombre(): void
    {
        $turno = $this->turno('Mañana', '08:00:00', '16:00:00');
        $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => '2025-03-01', 'estado' => 'CERRADA']);
        $datos = ['editandoId' => $turno->getKey(), 'nombre' => 'Turno matutino', 'horaInicio' => '09:00', 'horaFin' => '16:00',
            'orden' => 1, 'estado' => 'ACTIVO', 'observacion' => 'Corrección solicitada'];
        $accion = app(GestionarTurnosInstitucionales::class);
        try {
            $accion->guardar($this->actor, $datos);
            $this->fail('Un turno referenciado no permite reescribir las horas anteriores.');
        } catch (ValidationException $error) {
            $this->assertArrayHasKey('horaInicio', $error->errors());
        }
        $this->assertSame('08:00:00', $turno->fresh()->hora_inicio);
        $this->assertSame('Mañana', $turno->fresh()->nombre);
        $accion->guardar($this->actor, [...$datos, 'horaInicio' => '08:00']);
        $this->assertSame('TURNO MATUTINO', $turno->fresh()->nombre);
        $this->assertSame('08:00:00', $turno->fresh()->hora_inicio);
        $this->assertSame($turno->getKey(), $jornada->fresh()->cod_turno);
        $this->assertSame('CERRADA', $jornada->fresh()->estado);
        $this->assertDatabaseCount('turnos', 1);
    }

    public function test_fallo_en_segunda_ocurrencia_revierte_primera_jornada_evento_y_auditoria(): void
    {
        (require base_path('database/propuestas/gerencia/2026_10_26_000002_historia_vinculos_asignaciones.php'))->up();
        $turno = $this->turno('Mañana', '08:00:00', '16:00:00');
        $entrada = [...$this->entrada($turno), 'f_dias_semana' => ['LUNES', 'MARTES']];
        $antes = Activity::count();
        $dispatcher = AsignacionPersonal::getEventDispatcher();
        AsignacionPersonal::setEventDispatcher(clone $dispatcher);
        $intentos = 0;
        try {
            AsignacionPersonal::creating(function () use (&$intentos) {
                if (++$intentos === 2) {
                    throw new \RuntimeException('Fallo sintético en segunda ocurrencia');
                }
            });
            try {
                app(GestionarHorariosPersonal::class)->guardar($entrada);
                $this->fail('No debe conservar una fracción del horario.');
            } catch (\RuntimeException $error) {
                $this->assertSame('Fallo sintético en segunda ocurrencia', $error->getMessage());
            }
        } finally {
            AsignacionPersonal::setEventDispatcher($dispatcher);
        }
        $this->assertDatabaseCount('asignaciones_personal', 0);
        $this->assertDatabaseCount('jornadas', 0);
        $this->assertDatabaseCount('eventos_asignacion_personal', 0);
        $this->assertSame($antes, Activity::count());
        app(GestionarHorariosPersonal::class)->guardar($entrada);
        $this->assertDatabaseCount('asignaciones_personal', 2);
        $this->assertDatabaseCount('jornadas', 2);
        $this->assertDatabaseCount('eventos_asignacion_personal', 2);
    }
}
