<?php

namespace Tests\Feature;

use App\Livewire\Cuidados\DashboardTurno;
use App\Models\AdministracionMedicacion;
use App\Models\AdultoMayor;
use App\Models\Alerta;
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\AsignacionResidenteJornada;
use App\Models\Atencion;
use App\Models\Cama;
use App\Models\EjecucionCuidado;
use App\Models\Habitacion;
use App\Models\HorarioPrescripcion;
use App\Models\Jornada;
use App\Models\Medicamento;
use App\Models\OcupacionCama;
use App\Models\Personal;
use App\Models\PlanCuidado;
use App\Models\Prescripcion;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use App\Services\Enfermeria\MiTurnoService;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MiTurnoFueraDeJornadaTest extends TestCase
{
    use RefreshDatabase;

    protected User $elenaUser;
    protected Personal $elenaPersonal;

    protected User $mariaUser;
    protected Personal $mariaPersonal;

    protected User $robertoUser;
    protected Personal $robertoPersonal;

    protected Area $area;
    protected Turno $turnoManana;
    protected Turno $turnoTarde;
    protected Jornada $jornadaManana;
    protected Residente $residenteCarlos;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::today()->setTime(10, 0, 0));

        $this->seed(RolesAndPermissionsSeeder::class);

        $permisosEnfermeria = [
            'dashboard.enfermero',
            'dashboard.ver',
            'medicacion.administrar',
            'signos_vitales.crear',
            'seguimiento_clinico.crear',
            'alertas.gestionar',
            'alertas.atender',
            'residentes.ver',
            'turnos.ver',
        ];
        foreach ($permisosEnfermeria as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $rolEnfermero = Role::firstOrCreate(['name' => 'ENFERMEROS', 'guard_name' => 'web']);
        $rolEnfermero->givePermissionTo($permisosEnfermeria);

        $this->area = Area::create([
            'cod_area' => 'ARE_' . strtoupper(Str::random(6)),
            'nombre' => 'Pabellon Geriatria A',
            'estado' => 'ACTIVA',
        ]);

        $this->turnoManana = Turno::create([
            'cod_turno' => 'TUR_MANANA_' . strtoupper(Str::random(4)),
            'nombre' => 'Turno Manana',
            'hora_inicio' => '07:00:00',
            'hora_fin' => '15:00:00',
            'estado' => 'ACTIVO',
        ]);

        $this->turnoTarde = Turno::create([
            'cod_turno' => 'TUR_TARDE_' . strtoupper(Str::random(4)),
            'nombre' => 'Turno Tarde',
            'hora_inicio' => '15:00:00',
            'hora_fin' => '23:00:00',
            'estado' => 'ACTIVO',
        ]);

        // Elena (Enfermera de Turno Manana)
        $this->elenaUser = User::factory()->create([
            'nombres' => 'Elena',
            'ap_paterno' => 'Gomez',
            'ap_materno' => 'Perez',
            'correo' => 'elena@remembermind.test',
            'estado' => 'ACTIVO',
        ]);
        $this->elenaUser->assignRole('ENFERMEROS');
        $this->elenaPersonal = $this->elenaUser->personal;

        // Maria (Segunda Enfermera del Turno Manana)
        $this->mariaUser = User::factory()->create([
            'nombres' => 'Maria',
            'ap_paterno' => 'Rodriguez',
            'ap_materno' => 'Lopez',
            'correo' => 'maria@remembermind.test',
            'estado' => 'ACTIVO',
        ]);
        $this->mariaUser->assignRole('ENFERMEROS');
        $this->mariaPersonal = $this->mariaUser->personal;

        // Roberto (Enfermero Fuera de Turno hoy en la manana)
        $this->robertoUser = User::factory()->create([
            'nombres' => 'Roberto',
            'ap_paterno' => 'Vasquez',
            'ap_materno' => 'Torres',
            'correo' => 'roberto@remembermind.test',
            'estado' => 'ACTIVO',
        ]);
        $this->robertoUser->assignRole('ENFERMEROS');
        $this->robertoPersonal = $this->robertoUser->personal;

        // Jornada Manana ACTIVA hoy
        $this->jornadaManana = Jornada::create([
            'cod_jornada' => 'JOR_MANANA_' . strtoupper(Str::random(4)),
            'cod_turno' => $this->turnoManana->cod_turno,
            'fecha_jornada' => Carbon::today()->toDateString(),
            'estado' => 'ACTIVA',
        ]);

        // Asignacion de Elena y Maria a la jornada activa de la manana
        AsignacionPersonal::create([
            'cod_asignacion_personal' => 'ASP_ELENA_' . strtoupper(Str::random(4)),
            'cod_jornada' => $this->jornadaManana->cod_jornada,
            'cod_personal' => $this->elenaPersonal->cod_personal,
            'cod_area' => $this->area->cod_area,
            'funcion' => 'ENFERMERO',
            'tipo_asignacion' => 'TURNO',
            'fecha_asignacion' => Carbon::today()->toDateString(),
            'estado' => 'ACTIVA',
        ]);

        AsignacionPersonal::create([
            'cod_asignacion_personal' => 'ASP_MARIA_' . strtoupper(Str::random(4)),
            'cod_jornada' => $this->jornadaManana->cod_jornada,
            'cod_personal' => $this->mariaPersonal->cod_personal,
            'cod_area' => $this->area->cod_area,
            'funcion' => 'ENFERMERO',
            'tipo_asignacion' => 'TURNO',
            'fecha_asignacion' => Carbon::today()->toDateString(),
            'estado' => 'ACTIVA',
        ]);

        // Residente Carlos Mendoza
        $this->residenteCarlos = Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_CARLOS_' . strtoupper(Str::random(4)),
            'nombres' => 'Carlos',
            'apellido_paterno' => 'Mendoza',
            'apellido_materno' => 'Silva',
            'fecha_nacimiento' => '1948-03-15',
            'genero' => 'M',
            'estado' => 'ACTIVO',
        ]);

        // Asignacion de Carlos a Elena en la jornada activa
        AsignacionResidenteJornada::create([
            'cod_asignacion' => 'ARJ_ELENA_' . strtoupper(Str::random(4)),
            'cod_jornada' => $this->jornadaManana->cod_jornada,
            'cod_personal' => $this->elenaPersonal->cod_personal,
            'cod_residente' => $this->residenteCarlos->cod_residente,
            'nivel_supervision' => 'ALTA',
            'fecha_hora' => Carbon::now(),
            'estado' => 'ACTIVO',
        ]);

        // Habitacion 204 y Cama A
        $habitacion = Habitacion::create([
            'cod_habitacion' => 'HAB_204_' . strtoupper(Str::random(4)),
            'codigo' => '204',
            'nombre' => 'Habitacion 204',
            'tipo' => 'DOBLE',
            'capacidad' => 2,
            'estado' => 'ACTIVA',
        ]);

        $cama = Cama::create([
            'cod_cama' => 'CAM_A_' . strtoupper(Str::random(4)),
            'cod_habitacion' => $habitacion->cod_habitacion,
            'codigo' => 'A',
            'tipo' => 'INDIVIDUAL',
            'estado' => 'OCUPADA',
        ]);

        $admision = \App\Models\Admision::create([
            'cod_admision' => 'ADM_' . strtoupper(Str::random(6)),
            'cod_residente' => $this->residenteCarlos->cod_residente,
            'cod_usuario_registro' => $this->elenaUser->cod_usuario,
            'fecha_hora_admision' => Carbon::now()->subDays(10),
            'tipo_ingreso' => 'DEFINITIVO',
            'motivo_ingreso' => 'Cuidado integral',
            'estado' => 'ACTIVA',
        ]);

        OcupacionCama::create([
            'cod_ocupacion' => 'OCU_' . strtoupper(Str::random(4)),
            'cod_cama' => $cama->cod_cama,
            'cod_usuario_registro' => $this->elenaUser->cod_usuario,
            'cod_residente' => $this->residenteCarlos->cod_residente,
            'cod_admision' => $admision->cod_admision,
            'fecha_hora_asignacion' => Carbon::now()->subMonths(2),
            'estado' => 'ACTIVA',
        ]);
    }

    /**
     * 1. Enfermero fuera de turno entra en modo consulta:
     * - No deja el dashboard vacio.
     * - Muestra 'FUERA DE TURNO' y 'MODO CONSULTA / SOLO LECTURA'.
     * - Muestra residentes cubiertos por las jornadas de enfermeria activas.
     */
    public function test_enfermero_fuera_de_turno_entra_en_modo_consulta(): void
    {
        $this->actingAs($this->robertoUser);

        $service = app(MiTurnoService::class);
        $datos = $service->obtenerDatosDashboard($this->robertoUser);

        $this->assertEquals('FUERA_DE_TURNO', $datos['modo']);
        $this->assertEquals('FUERA DE TURNO', $datos['modo_label']);
        $this->assertEquals('MODO CONSULTA / SOLO LECTURA', $datos['submodo_label']);
        $this->assertTrue($datos['es_modo_consulta']);

        // No esta vacio: contiene al residente cubierto por el turno activo
        $this->assertNotEmpty($datos['residentes']);
        $cods = collect($datos['residentes'])->pluck('cod_residente')->toArray();
        $this->assertContains($this->residenteCarlos->cod_residente, $cods);

        // Livewire frontend aprobado
        Livewire::test(DashboardTurno::class)
            ->assertSee('FUERA DE TURNO')
            ->assertSee('MODO CONSULTA / SOLO LECTURA')
            ->assertSee('Carlos Mendoza')
            ->assertDontSee('MI TURNO / ACTIVO');
    }

    /**
     * 2. Puede ver responsables actuales:
     * - Nombre, edad, habitacion y cama, nivel de supervision, estado operacional, turno actual.
     * - Si existen varios responsables activos, se muestran TODOS sin elegir uno arbitrariamente.
     */
    public function test_puede_ver_responsables_actuales_completos_y_datos_del_residente(): void
    {
        // Asignamos tambien a Maria al residente Carlos en la misma jornada activa
        AsignacionResidenteJornada::create([
            'cod_asignacion' => 'ARJ_MARIA_' . strtoupper(Str::random(4)),
            'cod_jornada' => $this->jornadaManana->cod_jornada,
            'cod_personal' => $this->mariaPersonal->cod_personal,
            'cod_residente' => $this->residenteCarlos->cod_residente,
            'nivel_supervision' => 'ALTA',
            'fecha_hora' => Carbon::now(),
            'estado' => 'ACTIVO',
        ]);

        $this->actingAs($this->robertoUser);

        $service = app(MiTurnoService::class);
        $datos = $service->obtenerDatosDashboard($this->robertoUser);

        $cardCarlos = collect($datos['residentes'])->firstWhere('cod_residente', $this->residenteCarlos->cod_residente);
        $this->assertNotNull($cardCarlos);

        // Datos del residente
        $this->assertEquals('Carlos Mendoza Silva', $cardCarlos['nombre_completo']);
        $this->assertEquals('Hab. 204 · Cama A', $cardCarlos['ubicacion']);
        $this->assertEquals('Supervisión alta', $cardCarlos['supervision_label']);
        $this->assertEquals('ACTIVO', $cardCarlos['estado_operacional']);
        $this->assertEquals('Turno Manana', $cardCarlos['turno_actual']);

        // Responsables: deben figurar Elena Y Maria sin omitir a ninguna
        $this->assertStringContainsString('A cargo de:', $cardCarlos['responsable_texto']);
        $this->assertStringContainsString('Enf. Elena Gomez', $cardCarlos['responsable_texto']);
        $this->assertStringContainsString('Enf. Maria Rodriguez', $cardCarlos['responsable_texto']);
    }

    /**
     * 3. No se presentan asignaciones antiguas como actuales:
     * - Si Roberto tuvo a Carlos asignado ayer, hoy fuera de turno no se le atribuye a el.
     */
    public function test_no_se_presentan_asignaciones_antiguas_como_actuales(): void
    {
        // Jornada de ayer
        $jornadaAyer = Jornada::create([
            'cod_jornada' => 'JOR_AYER_' . strtoupper(Str::random(4)),
            'cod_turno' => $this->turnoManana->cod_turno,
            'fecha_jornada' => Carbon::yesterday()->toDateString(),
            'estado' => 'FINALIZADA',
        ]);

        // Asignacion antigua de Roberto a Carlos
        AsignacionResidenteJornada::create([
            'cod_asignacion' => 'ARJ_AYER_ROB_' . strtoupper(Str::random(4)),
            'cod_jornada' => $jornadaAyer->cod_jornada,
            'cod_personal' => $this->robertoPersonal->cod_personal,
            'cod_residente' => $this->residenteCarlos->cod_residente,
            'nivel_supervision' => 'ESTANDAR',
            'fecha_hora' => Carbon::yesterday()->setTime(10, 0, 0),
            'estado' => 'ACTIVO',
        ]);

        $this->actingAs($this->robertoUser);
        $service = app(MiTurnoService::class);
        $datos = $service->obtenerDatosDashboard($this->robertoUser);

        $cardCarlos = collect($datos['residentes'])->firstWhere('cod_residente', $this->residenteCarlos->cod_residente);
        $this->assertNotNull($cardCarlos);

        // El responsable actual es Elena (de la jornada activa de hoy), NO Roberto
        $this->assertStringContainsString('Enf. Elena Gomez', $cardCarlos['responsable_texto']);
        $this->assertStringNotContainsString('Roberto', $cardCarlos['responsable_texto']);
    }

    /**
     * 4. Graficas corresponden al turno actualmente activo:
     * - Muestra 'Progreso del turno en curso' y 'Solo lectura'.
     * - NO lo llama 'Tu progreso'.
     * - Calcula el progreso con los residentes asignados a las jornadas activas.
     */
    public function test_graficas_corresponden_al_turno_activo_en_modo_solo_lectura(): void
    {
        $this->actingAs($this->robertoUser);

        // Medicamento, Atencion y Prescripcion
        $medicamento = Medicamento::create([
            'cod_medicamento' => 'MED_' . strtoupper(Str::random(6)),
            'nombre_generico' => 'Paracetamol',
            'nombre_comercial' => 'Paracetamol 500mg',
            'concentracion' => '500mg',
            'forma_farmaceutica' => 'TABLETA',
            'control_especial' => false,
            'estado' => 'ACTIVO',
        ]);

        $atencion = Atencion::create([
            'cod_atencion' => 'ATE_' . strtoupper(Str::random(6)),
            'cod_residente' => $this->residenteCarlos->cod_residente,
            'cod_area' => $this->area->cod_area,
            'cod_personal' => $this->elenaPersonal->cod_personal,
            'tipo_atencion' => 'MEDICA',
            'motivo' => 'Consulta general',
            'fecha_hora' => Carbon::now(),
            'estado' => 'REALIZADA',
        ]);

        $prescripcion = Prescripcion::create([
            'cod_prescripcion' => 'PRE_' . strtoupper(Str::random(6)),
            'cod_residente' => $this->residenteCarlos->cod_residente,
            'cod_atencion' => $atencion->cod_atencion,
            'cod_medicamento' => $medicamento->cod_medicamento,
            'cod_personal' => $this->elenaPersonal->cod_personal,
            'dosis' => 500,
            'unidad_dosis' => 'mg',
            'via_administracion' => 'ORAL',
            'frecuencia' => 'Cada 8 horas',
            'segun_necesidad' => false,
            'fecha_hora_prescripcion' => Carbon::now(),
            'estado' => 'ACTIVA',
        ]);

        HorarioPrescripcion::create([
            'cod_horario_prescripcion' => 'HPR_' . strtoupper(Str::random(6)),
            'cod_prescripcion' => $prescripcion->cod_prescripcion,
            'hora_programada' => '08:00:00',
            'dosis_programada' => 500,
            'estado' => 'ACTIVO',
        ]);

        // Registrar como administrada por Elena
        AdministracionMedicacion::create([
            'cod_administracion' => 'ADM_' . strtoupper(Str::random(6)),
            'cod_prescripcion' => $prescripcion->cod_prescripcion,
            'cod_residente' => $this->residenteCarlos->cod_residente,
            'cod_personal' => $this->elenaPersonal->cod_personal,
            'cod_jornada' => $this->jornadaManana->cod_jornada,
            'fecha_hora_programada' => Carbon::today()->setTime(8, 0, 0),
            'fecha_hora_administracion' => Carbon::today()->setTime(8, 10, 0),
            'resultado' => 'ADMINISTRADA',
            'estado' => 'ACTIVA',
        ]);

        $service = app(MiTurnoService::class);
        $datos = $service->obtenerDatosDashboard($this->robertoUser);

        $this->assertEquals(1, $datos['estado_tareas']['realizadas']);
        $this->assertEquals(1, $datos['estado_tareas']['total']);
        $this->assertEquals(100, $datos['estado_tareas']['porcentaje']);

        Livewire::test(DashboardTurno::class)
            ->assertSee('Progreso del turno en curso')
            ->assertSee('Solo lectura')
            ->assertSee('Actividad del turno en curso')
            ->assertDontSee('Tu progreso');
    }

    /**
     * 5. Enfermero fuera de turno no puede escribir:
     * - Backend y metodos Livewire bloquean con 403 Forbidden.
     * - No puede administrar medicacion, ni registrar signos, ni seguimiento, ni tareas, ni alertas.
     */
    public function test_enfermero_fuera_de_turno_bloqueado_de_escribir_por_backend_y_livewire(): void
    {
        $this->actingAs($this->robertoUser);

        // 1. Intentar registrar signos
        Livewire::test(DashboardTurno::class)
            ->call('abrirRegistrarSignos', $this->residenteCarlos->cod_residente)
            ->assertForbidden();

        // 2. Intentar registrar seguimiento
        Livewire::test(DashboardTurno::class)
            ->call('abrirRegistrarSeguimiento', $this->residenteCarlos->cod_residente)
            ->assertForbidden();

        // 3. Intentar administrar medicacion
        Livewire::test(DashboardTurno::class)
            ->call('administrarMed', 'PRE_FAKE', $this->residenteCarlos->cod_residente, '08:00')
            ->assertForbidden();

        // 4. Intentar completar tarea
        Livewire::test(DashboardTurno::class)
            ->call('completarTarea', 'EJE_FAKE')
            ->assertForbidden();

        // 5. Intentar atender alerta
        Livewire::test(DashboardTurno::class)
            ->call('abrirAtenderAlerta', 'ALE_FAKE')
            ->assertForbidden();
    }

    /**
     * 6. Policy AdministracionMedicacionPolicy bloquea tambien la autorizacion de creacion
     * cuando el enfermero esta fuera de turno.
     */
    public function test_policy_administracion_medicacion_rechaza_a_enfermero_fuera_de_turno(): void
    {
        // Elena esta en turno activo: permitida
        $this->assertTrue(Gate::forUser($this->elenaUser)->allows('create', AdministracionMedicacion::class));

        // Roberto esta fuera de turno: rechazada por Policy
        $this->assertFalse(Gate::forUser($this->robertoUser)->allows('create', AdministracionMedicacion::class));
    }

    /**
     * 7. Al comenzar la jornada del usuario, cambia automaticamente a modo EN TURNO:
     * - Muestra unicamente sus residentes asignados.
     * - Habilita las operaciones autorizadas.
     */
    public function test_al_iniciar_su_jornada_pasa_automaticamente_a_modo_en_turno(): void
    {
        // Jornada Tarde activa hoy
        $jornadaTarde = Jornada::create([
            'cod_jornada' => 'JOR_TARDE_' . strtoupper(Str::random(4)),
            'cod_turno' => $this->turnoTarde->cod_turno,
            'fecha_jornada' => Carbon::today()->toDateString(),
            'estado' => 'ACTIVA',
        ]);

        // Asignacion de Roberto al turno tarde
        AsignacionPersonal::create([
            'cod_asignacion_personal' => 'ASP_TARDE_' . strtoupper(Str::random(4)),
            'cod_jornada' => $jornadaTarde->cod_jornada,
            'cod_personal' => $this->robertoPersonal->cod_personal,
            'cod_area' => $this->area->cod_area,
            'funcion' => 'ENFERMERO',
            'tipo_asignacion' => 'TURNO',
            'fecha_asignacion' => Carbon::today()->toDateString(),
            'estado' => 'ACTIVA',
        ]);

        // Residente Beatriz asignada exclusivamente a Roberto en la tarde
        $residenteBeatriz = Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_BEA_' . strtoupper(Str::random(4)),
            'nombres' => 'Beatriz',
            'apellido_paterno' => 'Castro',
            'fecha_nacimiento' => '1948-02-10',
            'genero' => 'F',
            'estado' => 'ACTIVO',
        ]);

        AsignacionResidenteJornada::create([
            'cod_asignacion' => 'ARJ_TARDE_' . strtoupper(Str::random(4)),
            'cod_jornada' => $jornadaTarde->cod_jornada,
            'cod_personal' => $this->robertoPersonal->cod_personal,
            'cod_residente' => $residenteBeatriz->cod_residente,
            'nivel_supervision' => 'ESTANDAR',
            'fecha_hora' => Carbon::now(),
            'estado' => 'ACTIVO',
        ]);

        // Simulamos que el reloj avanza a las 16:00 (dentro del turno tarde 15:00 - 23:00)
        Carbon::setTestNow(Carbon::today()->setTime(16, 0, 0));

        $this->actingAs($this->robertoUser);
        $service = app(MiTurnoService::class);
        $datos = $service->obtenerDatosDashboard($this->robertoUser);

        // Transicion automatica a EN_TURNO
        $this->assertEquals('EN_TURNO', $datos['modo']);
        $this->assertEquals('MI TURNO / ACTIVO', $datos['modo_label']);
        $this->assertFalse($datos['es_modo_consulta']);

        // Solo ve a sus residentes asignados (Beatriz, NO Carlos)
        $cods = collect($datos['residentes'])->pluck('cod_residente')->toArray();
        $this->assertContains($residenteBeatriz->cod_residente, $cods);
        $this->assertNotContains($this->residenteCarlos->cod_residente, $cods);

        // Operaciones habilitadas por Policy
        $this->assertTrue(Gate::forUser($this->robertoUser)->allows('create', AdministracionMedicacion::class));

        Livewire::test(DashboardTurno::class)
            ->assertSee('MI TURNO / ACTIVO')
            ->assertSee('Beatriz Castro')
            ->assertDontSee('Carlos Mendoza')
            ->assertSee('Progreso del turno')
            ->assertDontSee('FUERA DE TURNO');
    }

    /**
     * 8. Manipulacion del modo Livewire no habilita escritura:
     * - Aunque se intente alterar el payload o invocar metodos de mutacion,
     *   asegurarModoOperativo valida independientemente en backend y aborta con 403.
     */
    public function test_manipulacion_del_modo_livewire_no_habilita_escritura(): void
    {
        $this->actingAs($this->robertoUser);

        Livewire::test(DashboardTurno::class)
            ->call('abrirRegistrarSignos', $this->residenteCarlos->cod_residente)
            ->assertForbidden();

        Livewire::test(DashboardTurno::class)
            ->set('signoCodAm', $this->residenteCarlos->cod_residente)
            ->set('signoPresion', '120/80')
            ->call('guardarSignos')
            ->assertForbidden();
    }

    /**
     * 9. Fin de turno mientras dashboard esta abierto bloquea siguiente accion:
     * - Elena comienza en turno a las 10:00 (puede operar).
     * - El tiempo avanza a las 15:30 (turno manana finalizo a las 15:00).
     * - La siguiente accion de mutacion devuelve 403.
     */
    public function test_fin_de_turno_mientras_dashboard_esta_abierto_bloquea_siguiente_accion(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(10, 0, 0));
        $this->actingAs($this->elenaUser);

        // En turno: puede abrir modal de registro de signos
        Livewire::test(DashboardTurno::class)
            ->call('abrirRegistrarSignos', $this->residenteCarlos->cod_residente)
            ->assertSuccessful();

        // El turno de la manana cierra a las 15:00. Simulamos las 15:30:
        Carbon::setTestNow(Carbon::today()->setTime(15, 30, 0));

        // Con el dashboard abierto, la siguiente mutacion es bloqueada por el backend en tiempo real
        Livewire::test(DashboardTurno::class)
            ->call('abrirRegistrarSignos', $this->residenteCarlos->cod_residente)
            ->assertForbidden();
    }

    /**
     * 10. Personal no ENFERMEROS no aparece como "Enf.":
     * - Un medico asignado al residente no recibe el prefijo "Enf.".
     * - Si solo hay profesionales no enfermeros, muestra "Sin enfermero asignado".
     */
    public function test_personal_no_enfermeros_no_aparece_como_enf(): void
    {
        Role::firstOrCreate(['name' => 'MEDICO GENERAL/GERIATRA', 'guard_name' => 'web']);

        $medicoUser = User::factory()->create([
            'nombres' => 'Juan',
            'ap_paterno' => 'Perez',
            'correo' => 'juan.medico@remembermind.test',
            'estado' => 'ACTIVO',
        ]);
        $medicoUser->assignRole('MEDICO GENERAL/GERIATRA');
        $medicoPersonal = $medicoUser->personal;

        // Asignacion de Juan a la jornada con funcion "ENFERMERO" para probar que la funcion/cargo NO otorga autoridad si carece del rol Spatie ENFERMEROS
        AsignacionPersonal::create([
            'cod_asignacion_personal' => 'ASP_MED_' . strtoupper(Str::random(4)),
            'cod_jornada' => $this->jornadaManana->cod_jornada,
            'cod_personal' => $medicoPersonal->cod_personal,
            'cod_area' => $this->area->cod_area,
            'funcion' => 'ENFERMERO',
            'tipo_asignacion' => 'TURNO',
            'fecha_asignacion' => Carbon::today()->toDateString(),
            'estado' => 'ACTIVA',
        ]);

        // Residente Manuel asignado SOLO al medico Juan
        $residenteManuel = Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_MAN_' . strtoupper(Str::random(4)),
            'nombres' => 'Manuel',
            'apellido_paterno' => 'Ortiz',
            'fecha_nacimiento' => '1940-01-01',
            'genero' => 'M',
            'estado' => 'ACTIVO',
        ]);

        AsignacionResidenteJornada::create([
            'cod_asignacion' => 'ARJ_MED_MAN_' . strtoupper(Str::random(4)),
            'cod_jornada' => $this->jornadaManana->cod_jornada,
            'cod_personal' => $medicoPersonal->cod_personal,
            'cod_residente' => $residenteManuel->cod_residente,
            'nivel_supervision' => 'ESTANDAR',
            'fecha_hora' => Carbon::now(),
            'estado' => 'ACTIVO',
        ]);

        $this->actingAs($this->robertoUser);
        $service = app(MiTurnoService::class);
        $datos = $service->obtenerDatosDashboard($this->robertoUser);

        $cardManuel = collect($datos['residentes'])->firstWhere('cod_residente', $residenteManuel->cod_residente);
        $this->assertNotNull($cardManuel);

        // NO debe aparecer como "Enf. Juan"
        $this->assertStringNotContainsString('Enf. Juan', $cardManuel['responsable_texto']);
        $this->assertEquals('Sin enfermero asignado', $cardManuel['responsable_texto']);
    }

    /**
     * 11. Dos enfermeros para un residente no duplican progreso:
     * - Si Elena y Maria estan asignadas a Carlos, la misma tarea y medicacion se cuenta una sola vez.
     */
    public function test_dos_enfermeros_para_un_residente_no_duplican_progreso(): void
    {
        // Asignar tambien a Maria al residente Carlos
        AsignacionResidenteJornada::create([
            'cod_asignacion' => 'ARJ_MARIA_DUP_' . strtoupper(Str::random(4)),
            'cod_jornada' => $this->jornadaManana->cod_jornada,
            'cod_personal' => $this->mariaPersonal->cod_personal,
            'cod_residente' => $this->residenteCarlos->cod_residente,
            'nivel_supervision' => 'ALTA',
            'fecha_hora' => Carbon::now(),
            'estado' => 'ACTIVO',
        ]);

        // Crear una unica medicacion programada para Carlos
        $medicamento = Medicamento::create([
            'cod_medicamento' => 'MED_DUP_' . strtoupper(Str::random(4)),
            'nombre_generico' => 'Enalapril',
            'nombre_comercial' => 'Enalapril 10mg',
            'concentracion' => '10mg',
            'forma_farmaceutica' => 'TABLETA',
            'control_especial' => false,
            'estado' => 'ACTIVO',
        ]);

        $atencion = Atencion::create([
            'cod_atencion' => 'ATE_DUP_' . strtoupper(Str::random(4)),
            'cod_residente' => $this->residenteCarlos->cod_residente,
            'cod_area' => $this->area->cod_area,
            'cod_personal' => $this->elenaPersonal->cod_personal,
            'tipo_atencion' => 'MEDICA',
            'motivo' => 'Control',
            'fecha_hora' => Carbon::now(),
            'estado' => 'REALIZADA',
        ]);

        $prescripcion = Prescripcion::create([
            'cod_prescripcion' => 'PRE_DUP_' . strtoupper(Str::random(4)),
            'cod_residente' => $this->residenteCarlos->cod_residente,
            'cod_atencion' => $atencion->cod_atencion,
            'cod_medicamento' => $medicamento->cod_medicamento,
            'cod_personal' => $this->elenaPersonal->cod_personal,
            'dosis' => 10,
            'unidad_dosis' => 'mg',
            'via_administracion' => 'ORAL',
            'frecuencia' => 'Cada 24 horas',
            'segun_necesidad' => false,
            'fecha_hora_prescripcion' => Carbon::now(),
            'estado' => 'ACTIVA',
        ]);

        HorarioPrescripcion::create([
            'cod_horario_prescripcion' => 'HPR_DUP_' . strtoupper(Str::random(4)),
            'cod_prescripcion' => $prescripcion->cod_prescripcion,
            'hora_programada' => '08:00:00',
            'dosis_programada' => 10,
            'estado' => 'ACTIVO',
        ]);

        // Administrada por Elena
        AdministracionMedicacion::create([
            'cod_administracion' => 'ADM_DUP_' . strtoupper(Str::random(4)),
            'cod_prescripcion' => $prescripcion->cod_prescripcion,
            'cod_residente' => $this->residenteCarlos->cod_residente,
            'cod_personal' => $this->elenaPersonal->cod_personal,
            'cod_jornada' => $this->jornadaManana->cod_jornada,
            'fecha_hora_programada' => Carbon::today()->setTime(8, 0, 0),
            'fecha_hora_administracion' => Carbon::today()->setTime(8, 5, 0),
            'resultado' => 'ADMINISTRADA',
            'estado' => 'ACTIVA',
        ]);

        $this->actingAs($this->robertoUser);
        $service = app(MiTurnoService::class);
        $datos = $service->obtenerDatosDashboard($this->robertoUser);

        // Debe contar exactamente 1 tarea (no 2)
        $this->assertEquals(1, $datos['estado_tareas']['total']);
        $this->assertEquals(1, $datos['estado_tareas']['realizadas']);
        $this->assertEquals(100, $datos['estado_tareas']['porcentaje']);

        // Distribucion de cuidados: Medicacion = 1 (no 2)
        $dist = collect($datos['distribucion_cuidados'])->first(fn($i) => stripos($i['label'] ?? '', 'medic') !== false);
        $this->assertEquals(1, $dist['total'] ?? 0);
    }

    /**
     * 12. Estado institucional y estado de seguimiento permanecen separados:
     * - estado_institucional: ADMITIDO / ACTIVO (administrativo).
     * - estado_seguimiento: ESTABLE / VIGILANCIA / CRITICO (clinico).
     */
    public function test_estado_institucional_y_estado_de_seguimiento_permanecen_separados(): void
    {
        // Creamos una alerta critica para Carlos
        Alerta::create([
            'cod_alerta' => 'ALE_CRIT_' . strtoupper(Str::random(4)),
            'cod_residente' => $this->residenteCarlos->cod_residente,
            'tipo_alerta' => 'CLINICA',
            'prioridad' => 'CRITICA',
            'descripcion' => 'Desaturacion severa',
            'fecha_hora_alerta' => Carbon::now()->subMinutes(15),
            'estado' => 'ABIERTA',
        ]);

        $this->actingAs($this->robertoUser);
        $service = app(MiTurnoService::class);
        $datos = $service->obtenerDatosDashboard($this->robertoUser);

        $cardCarlos = collect($datos['residentes'])->firstWhere('cod_residente', $this->residenteCarlos->cod_residente);
        $this->assertNotNull($cardCarlos);

        // Estado institucional debe ser administrativo (ACTIVO)
        $this->assertEquals('ACTIVO', $cardCarlos['estado_institucional']);

        // Estado de seguimiento debe ser clinico (CRITICO)
        $this->assertEquals('CRÍTICO', $cardCarlos['estado_seguimiento']);

        // No son iguales ni se mezclan
        $this->assertNotEquals($cardCarlos['estado_institucional'], $cardCarlos['estado_seguimiento']);

        Livewire::test(DashboardTurno::class)
            ->assertSee('Institucional: ACTIVO')
            ->assertSee('CRÍTICO');
    }

    /**
     * 13. Superadministrador sin competencia clinica puede leer,
     * pero no puede ejecutar una mutacion clinica solo por su rol.
     */
    public function test_superadministrador_sin_competencia_clinica_puede_leer_pero_no_mutar(): void
    {
        Role::firstOrCreate(['name' => 'SUPERADMINISTRADOR', 'guard_name' => 'web']);

        $superUser = User::factory()->create([
            'nombres' => 'Super',
            'ap_paterno' => 'Admin',
            'correo' => 'superadmin@remembermind.test',
            'estado' => 'ACTIVO',
        ]);
        $superUser->assignRole('SUPERADMINISTRADOR');

        $this->actingAs($superUser);

        // 1. Lectura global permitida
        $service = app(MiTurnoService::class);
        $datos = $service->obtenerDatosDashboard($superUser);
        $this->assertNotEmpty($datos);

        Livewire::test(DashboardTurno::class)
            ->assertSuccessful();

        // 2. Mutaciones clinicas BLOQUEADAS con 403 (sin bypass de Superadministrador)
        Livewire::test(DashboardTurno::class)
            ->call('abrirRegistrarSignos', $this->residenteCarlos->cod_residente)
            ->assertForbidden();

        Livewire::test(DashboardTurno::class)
            ->call('administrarMed', 'PRE_FAKE', $this->residenteCarlos->cod_residente, '08:00')
            ->assertForbidden();

        Livewire::test(DashboardTurno::class)
            ->call('completarTarea', 'EJE_FAKE')
            ->assertForbidden();

        Livewire::test(DashboardTurno::class)
            ->call('abrirAtenderAlerta', 'ALE_FAKE')
            ->assertForbidden();
    }
}
