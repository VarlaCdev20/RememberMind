<?php

namespace Tests\Feature;

use App\Frontend\Livewire\Enfermeria\Cuidados\DashboardTurno;
use App\Frontend\Livewire\Enfermeria\Cuidados\MisPacientes;
use App\Models\Residente;
use App\Models\Alerta;
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\AsignacionResidenteJornada;
use App\Models\Cama;
use App\Models\Habitacion;
use App\Models\Jornada;
use App\Models\OcupacionCama;
use App\Models\Admision;
use App\Models\Personal;
use App\Models\PlanCuidado;
use App\Models\SignoVital;
use App\Models\Turno;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class MisResidentesNavegacionTest extends TestCase
{
    use RefreshDatabase;

    private User $enfermero;
    private Personal $personal;
    private Turno $turnoInst;
    private Turno $turno;
    private Jornada $jornada;
    private Area $area;
    private Residente $residenteAsignado;
    private Residente $residenteAjeno;

    public function test_cuidado_signos_abre_formulario_del_residente_sin_persistir_al_navegar(): void
    {
        $this->travelTo(Carbon::today()->setTime(10, 0));
        $signosAntes = SignoVital::count();
        Livewire::actingAs($this->enfermero)->test(MisPacientes::class, [
            'cuidado' => 'signos', 'residente' => $this->residenteAsignado->cod_residente,
        ])->assertSee('Cuidados · Signos')->call('abrirCuidadoSeleccionado')
            ->assertSet('registroTipo', 'signos')->assertSet('mostrarSelectorModal', true);
        $this->assertDatabaseCount('signos_vitales', $signosAntes);
    }

    public function test_cuidado_no_es_accesible_con_permiso_revocado(): void
    {
        $this->enfermero->roles->first()->revokePermissionTo('signos_vitales.ver');
        Livewire::actingAs($this->enfermero)->test(MisPacientes::class, ['cuidado' => 'signos'])->assertForbidden();
    }

    public function test_lectura_de_sueno_aprobada_no_permite_escritura_individual(): void
    {
        $this->travelTo(Carbon::today()->setTime(10, 0));
        Livewire::actingAs($this->enfermero)->test(\App\Frontend\Livewire\Enfermeria\Cuidados\RegistrosEnfermeria::class, [
            'codResidente' => $this->residenteAsignado->cod_residente, 'cuidado' => 'sueno', 'tipo' => 'SUENO',
        ])->assertSee('Sin registros de sueño')->call('guardarCuidado')->assertForbidden();
        $this->assertDatabaseCount('registros_sueno', 0);
    }

    public function test_cuidado_heridas_no_admite_curacion_con_permiso_revocado(): void
    {
        $this->travelTo(Carbon::today()->setTime(10, 0));
        $this->enfermero->roles->first()->revokePermissionTo('curaciones_herida.crear');
        Livewire::actingAs($this->enfermero)->test(\App\Frontend\Livewire\Enfermeria\Cuidados\RegistrosEnfermeria::class, [
            'codResidente' => $this->residenteAsignado->cod_residente, 'seccion' => 'HERIDAS',
        ])->call('guardarSeguimientoLesion')->assertForbidden();
        $this->assertDatabaseCount('curaciones_herida', 0);
    }

    public function test_cuidado_desconocido_no_se_convierte_en_un_formulario(): void
    {
        Livewire::actingAs($this->enfermero)->test(MisPacientes::class, ['cuidado' => 'prescribir'])->assertNotFound();
    }

    public function test_cuidado_heridas_conserva_residente_en_el_destino_y_verifica_alcance(): void
    {
        $this->travelTo(Carbon::today()->setTime(10, 0));
        $this->actingAs($this->enfermero)->get(route('admin.enfermeria.pacientes', [
            'cuidado' => 'heridas', 'residente' => $this->residenteAsignado->cod_residente,
        ]))->assertOk()->assertSee(route('admin.enfermeria.registros', [
            'seccion' => 'HERIDAS', 'adulto' => $this->residenteAsignado->cod_residente, 'cuidado' => 'heridas',
        ]));
        Livewire::actingAs($this->enfermero)->test(\App\Frontend\Livewire\Enfermeria\Cuidados\RegistrosEnfermeria::class, [
            'seccion' => 'HERIDAS', 'codResidente' => $this->residenteAsignado->cod_residente,
        ])->assertSee('Sin heridas registradas')->set('codResidente', $this->residenteAjeno->cod_residente)->assertForbidden();
        $this->actingAs($this->enfermero)->get(route('admin.enfermeria.registros', [
            'cuidado' => 'heridas', 'seccion' => 'HERIDAS', 'adulto' => $this->residenteAsignado->cod_residente,
        ]))->assertOk()->assertSee('openSection: 2', false)
            ->assertSee('class="rm-sidebar__subitem rm-nav-item is-active"', false);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        // Crear enfermero con rol y personal institucional
        $this->enfermero = User::factory()->create([
            'cod_usuario' => 'USU_ENF_NAV01',
            'nombres' => 'Mario',
            'ap_paterno' => 'Gutierrez',
            'ap_materno' => 'Ramos',
            'estado' => 'ACTIVO',
        ]);
        $this->enfermero->assignRole('ENFERMEROS');

        $this->personal = $this->enfermero->personal ?? Personal::where('cod_usuario', $this->enfermero->cod_usuario)->first();
        if (!$this->personal) {
            $this->personal = Personal::create([
                'cod_personal' => 'PER_NAV01',
                'cod_usuario' => $this->enfermero->cod_usuario,
                'nombres' => 'Mario',
                'apellido_paterno' => 'Gutierrez',
                'apellido_materno' => 'Ramos',
                'numero_documento' => '10203040',
                'profesion' => 'LICENCIATURA EN ENFERMERIA',
                'estado' => 'ACTIVO',
            ]);
        }

        $this->area = Area::create([
            'cod_area' => 'ARE_NAV01',
            'nombre' => 'Enfermería General',
            'estado' => 'ACTIVA',
        ]);

        $this->turno = Turno::create([
            'cod_turno' => 'TUR_NAV_MANANA',
            'nombre' => 'Turno Mañana',
            'hora_inicio' => '07:00:00',
            'hora_fin' => '15:00:00',
            'tipo' => 'MANANA',
            'orden' => 1,
            'estado' => 'ACTIVO',
            'fecha' => today()->toDateString(),
            'activo' => true,
        ]);

        $this->jornada = Jornada::create([
            'cod_jornada' => 'JOR_NAV_MANANA',
            'cod_turno' => $this->turno->cod_turno,
            'fecha_jornada' => today()->toDateString(),
            'estado' => 'ACTIVA',
        ]);

        AsignacionPersonal::create([
            'cod_asignacion_personal' => 'ASP_NAV01',
            'cod_jornada' => $this->jornada->cod_jornada,
            'cod_personal' => $this->personal->cod_personal,
            'cod_area' => $this->area->cod_area,
            'tipo_asignacion' => 'TURNO',
            'funcion' => 'ENFERMERO',
            'fecha_asignacion' => today()->setTime(22, 0),
            'funcion' => 'ENFERMERO',
            'tipo_asignacion' => 'TURNO',
            'fecha_asignacion' => today()->setTime(7, 0, 0),
            'estado' => 'ACTIVA',
        ]);

        $hab = Habitacion::create([
            'nombre' => 'Habitación 201',
            'numero' => '201',
            'codigo' => 'HAB-201',
            'tipo' => 'DOBLE',
            'estado' => 'ACTIVA',
            'capacidad' => 2,
        ]);

        $cama = Cama::create([
            'cod_habitacion' => $hab->cod_habitacion,
            'numero' => '1',
            'codigo' => 'CAMA-201A',
            'estado' => 'OCUPADA',
        ]);

        // Residente 1: Asignado a este enfermero
        $this->residenteAsignado = Residente::factory()->create([
            'cod_residente' => 'AM_NAV_001',
            'cod_residente' => 'AM_NAV_001',
            'nombres' => 'Carlos',
            'ap_paterno' => 'Mendoza',
            'ap_materno' => 'Salas',
            'ci' => '4455667',
            'fecha_nac' => Carbon::now()->subYears(78)->toDateString(),
            'genero' => 'MASCULINO',
            'cod_est_adul' => 'EST_001',
        ]);

        $admision = Admision::create([
            'cod_admision' => 'ADM_NAV_001',
            'cod_residente' => $this->residenteAsignado->cod_residente,
            'cod_usuario_registro' => $this->enfermero->cod_usuario,
            'fecha_hora_admision' => now(),
            'motivo_ingreso' => 'Preparación del escenario clínico',
            'estado' => 'ACTIVA',
        ]);

        OcupacionCama::create([
            'cod_residente' => $this->residenteAsignado->cod_residente,
            'cod_cama' => $cama->cod_cama,
            'cod_admision' => $admision->cod_admision,
            'cod_usuario_registro' => $this->enfermero->cod_usuario,
            'fecha_hora_asignacion' => now(),
            'estado' => 'ACTIVO',
        ]);

        PlanCuidado::create([
            'cod_residente' => $this->residenteAsignado->cod_residente,
            'cod_area' => $this->area->cod_area,
            'cod_personal' => $this->personal->cod_personal,
            'prioridad' => 'MODERADO',
            'estado' => 'ACTIVO',
            'fecha_hora_apertura' => today()->subMonth(),
        ]);

        SignoVital::create([
            'cod_residente' => $this->residenteAsignado->cod_residente,
            'cod_personal' => $this->personal->cod_personal,
            'fecha' => today()->toDateString(),
            'hora' => '08:00:00',
            'fecha_hora' => now()->subHours(2),
            'presion_arterial' => '120/80',
            'frecuencia_cardiaca' => 75,
            'temperatura' => 36.6,
            'saturacion' => 98,
            'registrado_por' => $this->enfermero->cod_usuario,
            'estado' => 'VIGENTE',
        ]);

        // Asignación operativa tanto en Jornada como en Turno
        AsignacionResidenteJornada::create([
            'cod_asignacion' => 'ARJ_NAV_01',
            'cod_jornada' => $this->jornada->cod_jornada,
            'cod_residente' => $this->residenteAsignado->cod_residente,
            'cod_personal' => $this->personal->cod_personal,
            'fecha_hora' => now(),
            'nivel_supervision' => 'ESTANDAR',
            'observacion' => 'Asignación de turno activo',
            'estado' => 'ACTIVA',
        ]);

        // Residente 2: Ajeno (no asignado a este enfermero)
        $this->residenteAjeno = Residente::factory()->create([
            'cod_residente' => 'AM_NAV_AJENO',
            'cod_residente' => 'AM_NAV_AJENO',
            'nombres' => 'Benito',
            'ap_paterno' => 'Juarez',
            'ap_materno' => 'Paz',
            'ci' => '9988776',
            'fecha_nac' => Carbon::now()->subYears(85)->toDateString(),
            'genero' => 'MASCULINO',
            'cod_est_adul' => 'EST_001',
        ]);
    }

    /**
     * Requisito 1 & 2: La fila y flecha del residente en Mi Turno apuntan a Mis Residentes con el parámetro residente.
     */
    public function test_clic_y_resolucion_de_residente_desde_mi_turno_navega_a_mis_residentes(): void
    {
        Carbon::setTestNow(today()->setTime(9, 0));

        $response = Livewire::actingAs($this->enfermero)
            ->test(DashboardTurno::class);

        $expectedUrl = route('admin.enfermeria.pacientes', ['residente' => $this->residenteAsignado->cod_residente]);

        $response->assertStatus(200)
            ->assertSee($this->residenteAsignado->nombres)
            ->assertSee($expectedUrl, false);
    }

    /**
     * Requisito 8: "Ver todos" navega a Mis Residentes sin selección.
     */
    public function test_ver_todos_en_mi_turno_navega_a_mis_residentes_sin_preseleccion(): void
    {
        Carbon::setTestNow(today()->setTime(9, 0));

        $response = Livewire::actingAs($this->enfermero)
            ->test(DashboardTurno::class);

        $expectedUrl = route('admin.enfermeria.pacientes');

        $response->assertStatus(200)
            ->assertSee('Ver todos')
            ->assertSee($expectedUrl, false);

        // Al montar MisPacientes sin parámetro, no hay preselección
        $pacientesComp = Livewire::actingAs($this->enfermero)
            ->test(MisPacientes::class);

        $this->assertNull($pacientesComp->get('residente'));
        $this->assertNull($pacientesComp->get('detalleResidente'));
    }

    /**
     * Requisito 3, 4 y 5: cod_residente válido se selecciona automáticamente, abre panel contextual con datos reales y botón + Registrar en turno.
     */
    public function test_cod_residente_valido_queda_seleccionado_automaticamente_y_muestra_datos_reales(): void
    {
        Carbon::setTestNow(today()->setTime(9, 0));

        $component = Livewire::actingAs($this->enfermero)
            ->test(MisPacientes::class, ['residente' => $this->residenteAsignado->cod_residente]);

        $component->assertStatus(200);

        // Estado del componente
        $this->assertEquals($this->residenteAsignado->cod_residente, $component->get('residente'));
        $this->assertFalse($component->get('esModoConsulta'));
        $this->assertTrue($component->get('esResidenteAsignado'));

        // Datos reales cargados en el panel
        $detalle = $component->get('detalleResidente');
        $this->assertNotNull($detalle);
        $this->assertStringContainsString('Carlos Mendoza Salas', $detalle['nombre_completo']);
        $this->assertStringContainsString('201', $detalle['ubicacion_formateada']);
        $this->assertEquals('120/80', $detalle['ultimos_signos']['pa']);

        // En turno y asignado: se muestra el botón + Registrar y Ver ficha clínica
        $component->assertSee('+ Registrar');
        $component->assertSee('Ver ficha clínica');
    }

    /**
     * Requisito 3 y 7: Residente no permitido / ajeno no puede abrirse (403 Forbidden).
     */
    public function test_residente_no_permitido_no_puede_abrirse(): void
    {
        Carbon::setTestNow(today()->setTime(9, 0));

        // Intento de montaje directo con residente no asignado
        Livewire::actingAs($this->enfermero)
            ->test(MisPacientes::class, ['residente' => $this->residenteAjeno->cod_residente])
            ->assertForbidden();

        // Intento de llamar al método seleccionarResidente con residente no asignado
        $comp = Livewire::actingAs($this->enfermero)->test(MisPacientes::class);
        $comp->call('seleccionarResidente', $this->residenteAjeno->cod_residente)
            ->assertForbidden();
    }

    /**
     * Requisito 6: Fuera de turno abre en solo lectura, muestra responsable actual, sin botón + Registrar y bloquea mutaciones.
     */
    public function test_fuera_de_turno_abre_en_solo_lectura_y_bloquea_mutaciones(): void
    {
        // Simulamos que el enfermero está fuera de su turno (a las 23:00)
        Carbon::setTestNow(today()->setTime(23, 0));

        // Crear otro enfermero de guardia nocturna activa y asignarle al residente
        $enfGuardia = User::factory()->create([
            'cod_usuario' => 'USU_GUARDIA_NOC',
            'nombres' => 'Patricia',
            'ap_paterno' => 'Rojas',
            'estado' => 'ACTIVO',
        ]);
        $enfGuardia->assignRole('ENFERMEROS');

        $perGuardia = $enfGuardia->personal ?? Personal::where('cod_usuario', $enfGuardia->cod_usuario)->first();
        if (!$perGuardia) {
            $perGuardia = Personal::create([
                'cod_personal' => 'PER_GUARDIA_NOC',
                'cod_usuario' => $enfGuardia->cod_usuario,
                'nombres' => 'Patricia',
                'apellido_paterno' => 'Rojas',
                'numero_documento' => '77665544',
                'profesion' => 'LICENCIATURA EN ENFERMERIA',
                'estado' => 'ACTIVO',
            ]);
        }

        $turnoNocturno = Turno::create([
            'cod_turno' => 'TUR_INST_NOC',
            'nombre' => 'Turno Nocturno',
            'hora_inicio' => '22:00:00',
            'hora_cierre' => '06:00:00',
            'tipo' => 'NOCHE',
            'estado' => 'ACTIVO',
        ]);

        $jornadaNocturna = Jornada::create([
            'cod_jornada' => 'JOR_NOC_01',
            'cod_turno' => $turnoNocturno->cod_turno,
            'fecha_jornada' => today()->toDateString(),
            'estado' => 'ACTIVA',
        ]);

        AsignacionPersonal::create([
            'cod_asignacion_personal' => 'ASP_NOC_01',
            'cod_area' => $this->area->cod_area,
            'tipo_asignacion' => 'TURNO',
            'funcion' => 'ENFERMERO',
            'fecha_asignacion' => today()->setTime(22, 0),
            'cod_jornada' => $jornadaNocturna->cod_jornada,
            'cod_personal' => $perGuardia->cod_personal,
            'estado' => 'ACTIVA',
        ]);

        AsignacionResidenteJornada::create([
            'cod_asignacion' => 'ARJ_NOC_01',
            'cod_jornada' => $jornadaNocturna->cod_jornada,
            'cod_personal' => $perGuardia->cod_personal,
            'cod_residente' => $this->residenteAsignado->cod_residente,
            'estado' => 'ACTIVA',
        ]);

        // El enfermero 1 (Mario, fuera de turno) abre a Carlos Mendoza
        $comp = Livewire::actingAs($this->enfermero)
            ->test(MisPacientes::class, ['residente' => $this->residenteAsignado->cod_residente]);

        $comp->assertStatus(200);

        // Verificaciones de modo solo lectura
        $this->assertTrue($comp->get('esModoConsulta'));
        $this->assertFalse($comp->get('esResidenteAsignado'));
        $comp->assertSee('MODO CONSULTA / SOLO LECTURA');
        $comp->assertDontSee('+ Registrar');
        $comp->assertSee('Ver ficha clínica');

        // Muestra responsable actual de guardia (Patricia Rojas)
        $detalle = $comp->get('detalleResidente');
        $this->assertStringContainsString('Patricia Rojas', $detalle['responsable_texto']);

        // Intentos de mutación arrojan 403 Forbidden en backend
        Livewire::actingAs($this->enfermero)
            ->test(MisPacientes::class, ['residente' => $this->residenteAsignado->cod_residente])
            ->call('abrirRegistrarSignos', $this->residenteAsignado->cod_residente)
            ->assertForbidden();

        Livewire::actingAs($this->enfermero)
            ->test(MisPacientes::class, ['residente' => $this->residenteAsignado->cod_residente])
            ->call('guardarSignos')
            ->assertForbidden();

        Livewire::actingAs($this->enfermero)
            ->test(MisPacientes::class, ['residente' => $this->residenteAsignado->cod_residente])
            ->call('guardarSeguimiento')
            ->assertForbidden();

        Livewire::actingAs($this->enfermero)
            ->test(MisPacientes::class, ['residente' => $this->residenteAsignado->cod_residente])
            ->call('guardarMed')
            ->assertForbidden();

        Livewire::actingAs($this->enfermero)
            ->test(MisPacientes::class, ['residente' => $this->residenteAsignado->cod_residente])
            ->call('guardarAlerta')
            ->assertForbidden();

        Livewire::actingAs($this->enfermero)
            ->test(MisPacientes::class, ['residente' => $this->residenteAsignado->cod_residente])
            ->call('guardarCuidado')
            ->assertForbidden();
    }

    /**
     * Requisito 11: Manipulación manual del parámetro residente no permite acceso indebido.
     */
    public function test_manipulacion_manual_de_cod_residente_bloquea_acceso_indebido(): void
    {
        Carbon::setTestNow(today()->setTime(9, 0));

        // Código inexistente / inventado
        Livewire::actingAs($this->enfermero)
            ->test(MisPacientes::class, ['residente' => 'RES_INVENTADO_999'])
            ->assertForbidden();

        // Manipulación hacia un residente ajeno
        Livewire::actingAs($this->enfermero)
            ->test(MisPacientes::class, ['residente' => $this->residenteAjeno->cod_residente])
            ->assertForbidden();
    }
}
