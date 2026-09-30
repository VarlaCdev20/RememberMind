<?php

namespace Tests\Feature;

use App\Backend\Modulos\Enfermeria\Servicios\DashboardComplementosService;
use App\Models\{Area, Jornada, Personal, Residente, Turno, User};
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Blade, DB};
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardComplementosTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;
    private Personal $personal;
    private Residente $residente;
    private Residente $ajeno;
    private array $contexto;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-09-28 10:00:00');
        $this->usuario = User::factory()->create();
        foreach (['ejecuciones_cuidado.ver', 'ocupaciones_cama.ver', 'registros_conductuales.ver'] as $permiso) {
            $this->usuario->givePermissionTo(Permission::findOrCreate($permiso, 'web'));
        }
        $this->personal = Personal::create(['cod_personal' => 'PER_TEST', 'cod_usuario' => $this->usuario->cod_usuario, 'nombres' => 'Ana', 'apellido_paterno' => 'Prueba', 'numero_documento' => 'TEST-123', 'profesion' => 'Enfermería', 'estado' => 'ACTIVO']);
        Area::create(['cod_area' => 'ARE_TEST', 'nombre' => 'Área prueba', 'estado' => 'ACTIVA']);
        Turno::create(['cod_turno' => 'TUR_TEST', 'nombre' => 'Mañana', 'hora_inicio' => '07:00:00', 'hora_fin' => '15:00:00', 'estado' => 'ACTIVO']);
        foreach (['JOR_TEST', 'JOR_OTRA'] as $codigo) {
            Jornada::create(['cod_jornada' => $codigo, 'cod_turno' => 'TUR_TEST', 'fecha_jornada' => '2026-09-28', 'estado' => 'ACTIVA']);
        }
        $this->residente = Residente::factory()->create();
        $this->ajeno = Residente::factory()->create();
        $this->contexto = ['estado' => 'EN_TURNO', 'modo' => 'EN_TURNO', 'jornada' => ['cod_jornada' => 'JOR_TEST'], 'usuario' => ['cod_personal' => 'PER_TEST'], 'residentes' => [
            ['cod_residente' => $this->residente->cod_residente, 'nombre_completo' => 'Residente Prueba', 'foto' => null, 'alertas_count' => 2],
        ]];
        DB::table('planes_cuidado')->insert(['cod_plan' => 'PLA_TEST', 'cod_residente' => $this->residente->cod_residente, 'cod_area' => 'ARE_TEST', 'cod_personal' => 'PER_TEST', 'tipo_plan' => 'GERIATRICO', 'nombre' => 'Plan prueba', 'objetivo_general' => 'Cuidado', 'fecha_hora_apertura' => now(), 'estado' => 'ACTIVO']);
        DB::table('intervenciones_cuidado')->insert(['cod_intervencion' => 'INT_TEST', 'cod_plan' => 'PLA_TEST', 'nombre' => 'Intervención registrada', 'descripcion' => 'Descripción', 'prioridad' => 'ALTA', 'estado' => 'ACTIVA']);
        DB::table('planes_cuidado')->insert(['cod_plan' => 'PLA_AJENO', 'cod_residente' => $this->ajeno->cod_residente, 'cod_area' => 'ARE_TEST', 'cod_personal' => 'PER_TEST', 'tipo_plan' => 'GERIATRICO', 'nombre' => 'Plan ajeno', 'objetivo_general' => 'Cuidado', 'fecha_hora_apertura' => now(), 'estado' => 'ACTIVO']);
        DB::table('intervenciones_cuidado')->insert(['cod_intervencion' => 'INT_AJENA', 'cod_plan' => 'PLA_AJENO', 'nombre' => 'Intervención ajena', 'descripcion' => 'Descripción', 'estado' => 'ACTIVA']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function resumen(?array $contexto = null): array
    {
        return app(DashboardComplementosService::class)->resumir($contexto ?? $this->contexto, $this->usuario);
    }

    private function tarea(string $codigo, array $cambios = []): void
    {
        DB::table('ejecuciones_cuidado')->insert(array_replace(['cod_ejecucion' => $codigo, 'cod_intervencion' => 'INT_TEST', 'cod_residente' => $this->residente->cod_residente, 'cod_jornada' => 'JOR_TEST', 'cod_personal' => 'PER_TEST', 'fecha_hora_programada' => now(), 'estado' => 'PENDIENTE'], $cambios));
    }

    private function nota(string $codigo, array $cambios = []): void
    {
        DB::table('registros_conductuales')->insert(array_replace(['cod_registro_conductual' => $codigo, 'cod_residente' => $this->residente->cod_residente, 'cod_personal' => 'PER_TEST', 'cod_jornada' => 'JOR_TEST', 'fecha_hora' => now()->subMinutes(5), 'descripcion' => 'Observación registrada', 'estado' => 'VIGENTE'], $cambios));
    }

    public function test_tareas_reales_aislamiento_limite_y_progreso_verificado(): void
    {
        for ($i = 0; $i < 5; $i++) $this->tarea('EJE_'.$i);
        $this->tarea('EJE_OK', ['estado' => 'REALIZADA', 'fecha_hora_ejecucion' => now()]);
        $this->tarea('EJE_SIN_FECHA', ['estado' => 'REALIZADA']);
        $this->tarea('EJE_OMITIDA', ['estado' => 'OMITIDA', 'fecha_hora_ejecucion' => now()]);
        $this->tarea('EJE_AJENA', ['cod_residente' => $this->ajeno->cod_residente, 'cod_intervencion' => 'INT_AJENA']);
        $this->tarea('EJE_OTRO_TURNO', ['cod_jornada' => 'JOR_OTRA']);
        $r = $this->resumen()['tareas'];
        $this->assertSame(8, $r['total']);
        $this->assertSame(1, $r['completadas']);
        $this->assertSame(13, $r['progreso']);
        $this->assertCount(5, $r['items']);
        $this->assertSame('Intervención registrada', $r['items'][0]['title']);
        $this->assertSame('PENDIENTE', $r['items'][0]['status']);
        $this->assertSame('ALTA', $r['items'][0]['priority']);
        $this->assertSame('Ana Prueba', $r['items'][0]['responsible']);
    }

    public function test_sin_turno_no_consulta_tablas_clinicas_y_no_simula_progreso(): void
    {
        $contexto = array_replace($this->contexto, ['estado' => 'SIN_JORNADA_ACTIVA', 'jornada' => null]);
        DB::enableQueryLog();
        $r = $this->resumen($contexto);
        $consultas = collect(DB::getQueryLog())->pluck('query')->implode(' ');
        DB::disableQueryLog();
        $this->assertFalse($r['activo']);
        $this->assertNull($r['tareas']['progreso']);
        $this->assertSame([], $r['notas']['items']);
        foreach (['ejecuciones_cuidado', 'ocupaciones_cama', 'registros_conductuales'] as $tabla) $this->assertStringNotContainsString($tabla, $consultas);
    }

    public function test_sin_tareas_o_permisos_no_inventa_datos(): void
    {
        $this->assertSame(0, $this->resumen()['tareas']['total']);
        $this->assertNull($this->resumen()['tareas']['progreso']);
        $this->usuario->revokePermissionTo(['ejecuciones_cuidado.ver', 'ocupaciones_cama.ver', 'registros_conductuales.ver']);
        $this->tarea('EJE_OCULTA');
        $this->nota('NOT_OCULTA');
        $r = $this->resumen();
        foreach (['tareas', 'ubicacion', 'notas'] as $tipo) {
            $this->assertFalse($r[$tipo]['disponible']);
            $this->assertSame([], $r[$tipo]['items']);
        }
    }

    public function test_modo_equipo_solo_incluye_ejecuciones_de_enfermeria_en_jornadas_activas(): void
    {
        $this->usuario->assignRole(Role::findOrCreate('ENFERMEROS', 'web'));
        $this->tarea('EJE_EQUIPO');
        $contexto = array_replace($this->contexto, ['modo' => 'CONSULTA_EQUIPO']);
        $r = $this->resumen($contexto)['tareas'];
        $this->assertSame(1, $r['total']);
        $this->assertSame('EJE_EQUIPO', $r['items'][0]['id']);
    }

    public function test_notas_recientes_reales_orden_limite_sin_emociones_inferidas(): void
    {
        for ($i = 1; $i <= 4; $i++) $this->nota('NOT_'.$i, ['fecha_hora' => now()->subMinutes($i), 'descripcion' => 'Texto menciona ansiedad sin estado estructurado']);
        $this->nota('NOT_ANTIGUA', ['fecha_hora' => now()->subHours(25)]);
        $this->nota('NOT_FUTURA', ['fecha_hora' => now()->addMinute()]);
        $this->nota('NOT_AJENA', ['cod_residente' => $this->ajeno->cod_residente]);
        $this->nota('NOT_ANULADA', ['estado' => 'ANULADO']);
        $this->nota('NOT_VACIA', ['descripcion' => '  ', 'estado_animo' => '  ']);
        $r = $this->resumen()['notas']['items'];
        $this->assertSame(['NOT_1', 'NOT_2', 'NOT_3'], array_column($r, 'id'));
        $this->assertNull($r[0]['mood']);
        $this->assertNull($r[0]['avatar']);
        $this->assertSame('RP', $r[0]['initials']);
        $this->assertSame('28/09 09:59', $r[0]['time']);
        $this->assertArrayNotHasKey('importance', $r[0]);
    }

    public function test_nota_con_estado_estructurado_y_cambio_no_requiere_texto_inventado(): void
    {
        $this->nota('NOT_REAL', ['estado_animo' => 'TRANQUILO', 'descripcion' => null, 'cambio_conducta' => true]);
        $r = $this->resumen()['notas']['items'][0];
        $this->assertSame('TRANQUILO', $r['mood']);
        $this->assertSame('', $r['text']);
        $this->assertTrue($r['change']);
    }

    private function ocupar(string $codigo, string $residente, array $cambios = []): void
    {
        DB::table('habitaciones')->insert(['cod_habitacion' => 'HAB_'.$codigo, 'codigo' => 'H-'.$codigo, 'capacidad' => 1, 'estado' => 'ACTIVA']);
        DB::table('camas')->insert(['cod_cama' => 'CAM_'.$codigo, 'cod_habitacion' => 'HAB_'.$codigo, 'codigo' => 'C-'.$codigo, 'estado' => 'ACTIVA']);
        DB::table('admisiones')->insert(['cod_admision' => 'ADM_'.$codigo, 'cod_residente' => $residente, 'cod_usuario_registro' => $this->usuario->cod_usuario, 'fecha_hora_admision' => now()->subDay(), 'motivo_ingreso' => 'Prueba', 'estado' => 'ACTIVA']);
        DB::table('ocupaciones_cama')->insert(array_replace(['cod_ocupacion' => 'OCU_'.$codigo, 'cod_residente' => $residente, 'cod_cama' => 'CAM_'.$codigo, 'cod_admision' => 'ADM_'.$codigo, 'cod_usuario_registro' => $this->usuario->cod_usuario, 'fecha_hora_asignacion' => now()->subHour(), 'estado' => 'ACTIVA'], $cambios));
    }

    public function test_ubicacion_verificable_cama_habitacion_y_alertas_del_contexto(): void
    {
        $this->ocupar('UNO', $this->residente->cod_residente);
        $this->ocupar('AJENA', $this->ajeno->cod_residente);
        $r = $this->resumen()['ubicacion'];
        $this->assertCount(1, $r['items']);
        $this->assertSame('H-UNO', $r['items'][0]['label']);
        $this->assertSame('C-UNO', $r['items'][0]['bed']);
        $this->assertSame('alert', $r['items'][0]['status']);
        $this->assertSame(2, $r['items'][0]['alerts']);
        $this->assertSame(0, $r['sin_ubicacion']);
    }

    public function test_ubicacion_liberada_futura_o_ambigua_no_elige_cama_arbitraria(): void
    {
        $this->ocupar('LIBERADA', $this->residente->cod_residente, ['estado' => 'LIBERADA', 'fecha_hora_liberacion' => now()]);
        $this->assertSame([], $this->resumen()['ubicacion']['items']);
        $this->assertSame(1, $this->resumen()['ubicacion']['sin_ubicacion']);
        $this->ocupar('FUTURA', $this->residente->cod_residente, ['fecha_hora_asignacion' => now()->addDay()]);
        $this->assertSame([], $this->resumen()['ubicacion']['items']);
        DB::table('ocupaciones_cama')->where('cod_ocupacion', 'OCU_FUTURA')->update(['estado' => 'LIBERADA', 'fecha_hora_liberacion' => now()->addDays(2)]);
        $this->ocupar('A', $this->residente->cod_residente);
        $this->assertCount(1, $this->resumen()['ubicacion']['items']);
    }

    public function test_componentes_textuales_sin_checkbox_ni_enlaces_ficticios(): void
    {
        $this->tarea('EJE_COMPONENTE');
        $this->nota('NOT_COMPONENTE');
        $this->ocupar('REAL', $this->residente->cod_residente);
        $r = $this->resumen();
        $task = Blade::render('<x-ui.task-item :task="$item" />', ['item' => $r['tareas']['items'][0]]);
        $location = Blade::render('<x-ui.location-cell :location="$item" />', ['item' => $r['ubicacion']['items'][0]]);
        $note = Blade::render('<x-ui.behavior-note :note="$item" />', ['item' => $r['notas']['items'][0]]);
        $this->assertStringContainsString('Pendiente', $task);
        $this->assertStringNotContainsString('checkbox', $task);
        $this->assertStringContainsString('H-REAL', $location);
        $this->assertStringContainsString('2 alertas', $location);
        $this->assertStringContainsString('RP', $note);
        $this->assertStringContainsString('Observación registrada', $note);
        foreach ([$task, $location, $note] as $html) $this->assertStringNotContainsString('href="#"', $html);
        $href = route('admin.enfermeria.pacientes.ficha', ['adulto' => $this->residente->cod_residente, 'tab' => 'seguimiento']);
        $linked = Blade::render('<x-ui.behavior-note :note="$item" :href="$href" />', ['item' => $r['notas']['items'][0], 'href' => $href]);
        $this->assertStringContainsString('tab=seguimiento', $linked);
    }
}
