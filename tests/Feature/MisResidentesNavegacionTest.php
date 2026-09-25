<?php

namespace Tests\Feature;

use App\Frontend\Livewire\Enfermeria\Cuidados\DashboardTurno;
use App\Frontend\Livewire\Enfermeria\Cuidados\MisPacientes;
use App\Models\AdultoMayor;
use App\Models\Alerta;
use App\Models\AreaInstitucional;
use App\Models\AsignacionPersonal;
use App\Models\AsignacionResidenteJornada;
use App\Models\Cama;
use App\Models\Habitacion;
use App\Models\Jornada;
use App\Models\OcupacionCama;
use App\Models\Personal;
use App\Models\PlanCuidado;
use App\Models\SignoVital;
use App\Models\TurnoEnfermeria;
use App\Models\TurnoInstitucional;
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
    private TurnoInstitucional $turnoInst;
    private TurnoEnfermeria $turno;
    private Jornada $jornada;
    private AreaInstitucional $area;
    private AdultoMayor $residenteAsignado;
    private AdultoMayor $residenteAjeno;

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

        $this->area = AreaInstitucional::create([
            'cod_area' => 'ARE_NAV01',
            'nombre' => 'Enfermería General',
            'estado' => 'ACTIVA',
        ]);

        $this->turno = TurnoEnfermeria::create([
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
        $this->residenteAsignado = AdultoMayor::factory()->create([
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

        OcupacionCama::create([
            'cod_residente' => $this->residenteAsignado->cod_residente,
            'cod_habitacion' => $hab->cod_habitacion,
            'cod_cama' => $cama->cod_cama,
            'fecha_asignacion' => today()->toDateString(),
            'estado' => 'ACTIVO',
        ]);

        PlanCuidado::create([
            'cod_residente' => $this->residenteAsignado->cod_residente,
            'nivel_cuidado' => 'MODERADO',
            'estado' => 'ACTIVO',
            'fecha_inicio' => today()->subMonth()->toDateString(),
            'creado_por' => $this->enfermero->cod_usuario,
        ]);

        SignoVital::create([
            'cod_residente' => $this->residenteAsignado->cod_residente,
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

        // Asignación operativa tanto en Jornada como en TurnoEnfermeria
        AsignacionResidenteJornada::create([
            'cod_asignacion' => 'ARJ_NAV_01',
            'cod_jornada' => $this->jornada->cod_jornada,
            'cod_turno' => $this->turno->cod_turno,
            'cod_residente' => $this->residenteAsignado->cod_residente,
            'cod_usu_enfermero' => $this->enfermero->cod_usuario,
            'cod_personal' => $this->personal->cod_personal,
            'fecha_inicio' => today()->toDateString(),
            'nivel_supervision' => 'ESTANDAR',
            'motivo_asignacion' => 'Asignación de turno activo',
            'estado' => 'ACTIVA',
        ]);

        // Residente 2: Ajeno (no asignado a este enfermero)
        $this->residenteAjeno = AdultoMayor::factory()->create([
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

        $turnoNocturno = TurnoInstitucional::create([
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