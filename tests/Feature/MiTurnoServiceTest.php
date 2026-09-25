<?php



namespace Tests\Feature;



use App\Models\Area;

use App\Models\AsignacionPersonal;

use App\Models\AsignacionResidenteJornada;

use App\Models\Cama;

use App\Models\Habitacion;

use App\Models\Jornada;

use App\Models\OcupacionCama;

use App\Models\Personal;

use App\Models\Residente;

use App\Models\Turno;

use App\Models\User;

use App\Backend\Modulos\Enfermeria\Servicios\MiTurnoService;

use Carbon\Carbon;

use Illuminate\Foundation\Testing\RefreshDatabase;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Str;

use Livewire\Livewire;

use Spatie\Permission\Models\Role;

use Symfony\Component\HttpKernel\Exception\HttpException;

use Tests\TestCase;



class MiTurnoServiceTest extends TestCase

{

    use RefreshDatabase;



    protected User $enfermeraUser;

    protected Personal $enfermeraPersonal;

    protected Jornada $jornada;

    protected Area $area;

    protected Turno $turno;

    protected Residente $residenteAsignado;

    protected Residente $residenteAjeno;



    protected function setUp(): void

    {

        parent::setUp();

        Carbon::setTestNow(Carbon::today()->setTime(10, 0, 0));



        // 1. Rol ENFERMEROS

        Role::firstOrCreate(['name' => 'ENFERMEROS', 'guard_name' => 'web']);



        // 2. Area

        $this->area = Area::create([

            'cod_area' => 'ARE_' . strtoupper(Str::random(6)),

            'nombre' => 'Sector Geriatría',

            'estado' => 'ACTIVA',

        ]);



        // 3. Turno

        $this->turno = Turno::create([

            'cod_turno' => 'TUR_' . strtoupper(Str::random(6)),

            'nombre' => 'Mañana',

            'hora_inicio' => '07:00:00',

            'hora_fin' => '15:00:00',

            'estado' => 'ACTIVO',

        ]);



        // 4. Usuario Enfermera

        $this->enfermeraUser = User::create([

            'cod_usuario' => 'USU_' . strtoupper(Str::random(6)),

            'correo' => 'elena.salazar@remembermind.test',

            'contrasena' => bcrypt('Password123!'),

            'estado' => 'ACTIVO',

        ]);

        $this->enfermeraUser->assignRole('ENFERMEROS');



        // 5. Personal

        $this->enfermeraPersonal = Personal::create([

            'cod_personal' => 'PER_' . strtoupper(Str::random(6)),

            'cod_usuario' => $this->enfermeraUser->cod_usuario,

            'nombres' => 'Elena',

            'apellido_paterno' => 'Salazar',

            'apellido_materno' => 'Ríos',

            'numero_documento' => '12345678',

            'profesion' => 'Licenciada en Enfermería',

            'estado' => 'ACTIVO',

        ]);



        // 6. Jornada

        $this->jornada = Jornada::create([

            'cod_jornada' => 'JOR_' . strtoupper(Str::random(6)),

            'cod_turno' => $this->turno->cod_turno,

            'fecha_jornada' => Carbon::today()->toDateString(),

            'estado' => 'ACTIVA',

        ]);



        // 7. Asignacion Personal a Jornada

        AsignacionPersonal::create([

            'cod_asignacion_personal' => 'ASP_' . strtoupper(Str::random(6)),

            'cod_jornada' => $this->jornada->cod_jornada,

            'cod_personal' => $this->enfermeraPersonal->cod_personal,

            'cod_area' => $this->area->cod_area,

            'funcion' => 'ENFERMERO',

            'tipo_asignacion' => 'TURNO',

            'fecha_asignacion' => Carbon::today()->toDateString(),

            'estado' => 'ACTIVA',

        ]);



        // 8. Residentes: uno asignado, uno ajeno

        $this->residenteAsignado = Residente::crearDesdeAdmision([

            'cod_residente' => 'RES_' . strtoupper(Str::random(6)),

            'nombres' => 'Carlos',

            'apellido_paterno' => 'Mendoza',

            'apellido_materno' => 'Paredes',

            'fecha_nacimiento' => '1945-05-12',

            'genero' => 'M',

            'estado' => 'ACTIVO',

        ]);



        $this->residenteAjeno = Residente::crearDesdeAdmision([

            'cod_residente' => 'RES_' . strtoupper(Str::random(6)),

            'nombres' => 'Alberto',

            'apellido_paterno' => 'Gómez',

            'apellido_materno' => 'Suárez',

            'fecha_nacimiento' => '1942-03-20',

            'genero' => 'M',

            'estado' => 'ACTIVO',

        ]);



        // 9. Asignacion Residente Jornada (Carlos a Elena)

        AsignacionResidenteJornada::create([

            'cod_asignacion' => 'ARJ_' . strtoupper(Str::random(6)),

            'cod_jornada' => $this->jornada->cod_jornada,

            'cod_personal' => $this->enfermeraPersonal->cod_personal,

            'cod_residente' => $this->residenteAsignado->cod_residente,

            'nivel_supervision' => 'ALTO',

            'fecha_hora' => Carbon::now(),

            'estado' => 'ACTIVO',

        ]);

    }



    protected function tearDown(): void

    {

        Carbon::setTestNow();

        parent::tearDown();

    }



    public function test_resuelve_enfermera_jornada_y_area_correctamente(): void

    {

        $service = app(MiTurnoService::class);

        $data = $service->obtenerDatosDashboard($this->enfermeraUser);



        $this->assertEquals($this->enfermeraUser->cod_usuario, $data['usuario']['cod_usuario']);

        $this->assertEquals($this->enfermeraPersonal->cod_personal, $data['usuario']['cod_personal']);

        $this->assertEquals($this->jornada->cod_jornada, $data['jornada']['cod_jornada']);

        $this->assertEquals('Sector Geriatría', $data['jornada']['area_nombre']);

        $this->assertStringContainsString('Mañana', $data['jornada']['nombre']);

    }



    public function test_filtra_estrictamente_residentes_asignados_a_la_enfermera(): void

    {

        $service = app(MiTurnoService::class);

        $data = $service->obtenerDatosDashboard($this->enfermeraUser);



        $residentes = $data['residentes'];

        $this->assertCount(1, $residentes);

        $this->assertEquals($this->residenteAsignado->cod_residente, $residentes[0]['cod_residente']);

        $this->assertStringContainsString('Carlos Mendoza', $residentes[0]['nombre_completo']);

    }



    public function test_obtiene_ubicacion_real_de_cama_activa_o_indica_sin_ubicacion(): void

    {

        // 1. Inicialmente sin cama asignada

        $service = app(MiTurnoService::class);

        $data = $service->obtenerDatosDashboard($this->enfermeraUser);

        $this->assertEquals('Sin ubicación asignada', $data['residentes'][0]['ubicacion']);



        // 2. Asignar cama real activa

        $habitacion = Habitacion::create([

            'cod_habitacion' => 'HAB_' . strtoupper(Str::random(6)),

            'codigo' => '204',

            'nombre' => 'Habitación 204',

            'tipo' => 'DOBLE',

            'capacidad' => 2,

            'estado' => 'ACTIVA',

        ]);



        $cama = Cama::create([

            'cod_cama' => 'CAM_' . strtoupper(Str::random(6)),

            'cod_habitacion' => $habitacion->cod_habitacion,

            'codigo' => 'B',

            'tipo' => 'INDIVIDUAL',

            'estado' => 'OCUPADA',

        ]);



        $admision = \App\Models\Admision::create([

            'cod_admision' => 'ADM_' . strtoupper(Str::random(6)),

            'cod_residente' => $this->residenteAsignado->cod_residente,

            'cod_usuario_registro' => $this->enfermeraUser->cod_usuario,

            'fecha_hora_admision' => Carbon::now()->subDays(10),

            'tipo_ingreso' => 'DEFINITIVO',

            'motivo_ingreso' => 'Cuidado integral',

            'estado' => 'ACTIVA',

        ]);



        OcupacionCama::create([

            'cod_ocupacion' => 'OCU_' . strtoupper(Str::random(6)),

            'cod_admision' => $admision->cod_admision,

            'cod_usuario_registro' => $this->enfermeraUser->cod_usuario,

            'cod_cama' => $cama->cod_cama,

            'cod_residente' => $this->residenteAsignado->cod_residente,

            'fecha_hora_asignacion' => Carbon::now()->subDays(5),

            'estado' => 'ACTIVA',

        ]);



        $dataWithBed = $service->obtenerDatosDashboard($this->enfermeraUser);

        $this->assertEquals('Hab. 204 · Cama B', $dataWithBed['residentes'][0]['ubicacion']);

    }



    public function test_kpis_alertas_y_estado_general_se_derivan_de_alertas_reales(): void

    {

        $service = app(MiTurnoService::class);



        // Sin alertas -> ESTABLE

        $dataSinAlertas = $service->obtenerDatosDashboard($this->enfermeraUser);

        $this->assertEquals('ESTABLE', $dataSinAlertas['estado_general']['badge']);

        $this->assertEquals(0, $dataSinAlertas['kpis']['total_registro']['numero']);



        // Insertar alerta para residente asignado

        DB::table('alertas')->insert([

            'cod_alerta' => 'ALA_' . strtoupper(Str::random(6)),

            'cod_residente' => $this->residenteAsignado->cod_residente,

            'titulo' => 'Riesgo de caída detectado',

            'descripcion' => 'Alerta clínica de alta prioridad',

            'tipo' => 'CLINICA',

            'prioridad' => 'ALTA',

            'generacion' => 'SISTEMA',

            'estado' => 'ABIERTA',

            'fecha_hora' => Carbon::now(),

        ]);



        // Insertar alerta ajena que NO debe contarse

        DB::table('alertas')->insert([

            'cod_alerta' => 'ALA_' . strtoupper(Str::random(6)),

            'cod_residente' => $this->residenteAjeno->cod_residente,

            'titulo' => 'Alerta residente ajeno',

            'descripcion' => 'Alerta ajena no asignada',

            'tipo' => 'CLINICA',

            'prioridad' => 'CRITICA',

            'generacion' => 'SISTEMA',

            'estado' => 'ABIERTA',

            'fecha_hora' => Carbon::now(),

        ]);



        $dataConAlertas = $service->obtenerDatosDashboard($this->enfermeraUser);



        // Solo 1 alerta pertenece a la enfermera

        $this->assertEquals(1, $dataConAlertas['kpis']['total_registro']['numero']);

        $this->assertEquals(1, $dataConAlertas['kpis']['criticas_altas']['numero']);

        $this->assertEquals(1, $dataConAlertas['kpis']['por_atender']['numero']);

        $this->assertContains($dataConAlertas['estado_general']['badge'], ['CRÍTICO', 'VIGILANCIA']);

    }



    public function test_progreso_del_turno_es_null_si_total_es_cero(): void

    {

        $service = app(MiTurnoService::class);

        $data = $service->obtenerDatosDashboard($this->enfermeraUser);



        $this->assertEquals(0, $data['estado_tareas']['total']);

        $this->assertNull($data['estado_tareas']['porcentaje']);

    }



    public function test_aislamiento_de_seguridad_bloquea_residente_ajeno(): void

    {

        $service = app(MiTurnoService::class);



        // Residente asignado: autoriza true

        $this->assertTrue($service->autorizarAccesoResidente($this->enfermeraUser, $this->residenteAsignado->cod_residente));



        // Residente ajeno: lanza 403 HttpException

        $this->expectException(HttpException::class);

        $this->expectExceptionMessage('Acceso denegado: El residente no está asignado a tu guardia actual.');

        $service->autorizarAccesoResidente($this->enfermeraUser, $this->residenteAjeno->cod_residente);

    }



    public function test_dashboard_es_estrictamente_solo_lectura(): void

    {

        Auth::login($this->enfermeraUser);



        $countAlertasBefore = DB::table('alertas')->count();

        $countJornadasBefore = DB::table('jornadas')->count();

        $countResidentesBefore = DB::table('residentes')->count();



        // Render Livewire

        Livewire::test(\App\Frontend\Livewire\Enfermeria\Cuidados\DashboardTurno::class)

            ->assertSee('Elena')

            ->assertSee('Carlos Mendoza Paredes')

            ->assertSee('Mañana');



        $this->assertEquals($countAlertasBefore, DB::table('alertas')->count());

        $this->assertEquals($countJornadasBefore, DB::table('jornadas')->count());

        $this->assertEquals($countResidentesBefore, DB::table('residentes')->count());

    }



    public function test_distribucion_cuidados_usa_categorias_reales_de_planes_y_medicacion(): void

    {

        $service = app(MiTurnoService::class);



        // Crear plan de cuidado real para Carlos

        $plan = \App\Models\PlanCuidado::create([

            'cod_plan' => 'PLC_' . strtoupper(Str::random(6)),

            'cod_residente' => $this->residenteAsignado->cod_residente,

            'cod_usuario_creador' => $this->enfermeraUser->cod_usuario,

            'titulo' => 'Plan de Prevención de Caídas',

            'tipo_plan' => 'PREVENCION',

            'fecha_inicio' => Carbon::today()->toDateString(),

            'estado' => 'ACTIVO',

        ]);



        $data = $service->obtenerDatosDashboard($this->enfermeraUser);

        $dist = $data['distribucion_cuidados'];



        $this->assertNotEmpty($dist);

        $labels = array_column($dist, 'label');

        $this->assertContains('Medicación', $labels);

        $this->assertContains('Prevencion', $labels);

    }



    public function test_agenda_unificada_y_estados_calculados_en_memoria(): void

    {

        $service = app(MiTurnoService::class);



        $medicamento = \App\Models\Medicamento::create([

            'cod_medicamento' => 'MED_' . strtoupper(Str::random(6)),

            'nombre_generico' => 'Paracetamol',

            'nombre_comercial' => 'Paracetamol 500mg',

            'concentracion' => '500mg',

            'forma_farmaceutica' => 'TABLETA',

            'control_especial' => false,

            'estado' => 'ACTIVO',

        ]);



        $atencion = \App\Models\Atencion::create([

            'cod_atencion' => 'ATE_' . strtoupper(Str::random(6)),

            'cod_residente' => $this->residenteAsignado->cod_residente,

            'cod_area' => $this->area->cod_area,

            'cod_personal' => $this->enfermeraPersonal->cod_personal,

            'tipo_atencion' => 'MEDICA',

            'motivo' => 'Consulta general',

            'fecha_hora' => Carbon::now(),

            'estado' => 'REALIZADA',

        ]);



        $prescripcion = \App\Models\Prescripcion::create([

            'cod_prescripcion' => 'PRE_' . strtoupper(Str::random(6)),

            'cod_residente' => $this->residenteAsignado->cod_residente,

            'cod_atencion' => $atencion->cod_atencion,

            'cod_medicamento' => $medicamento->cod_medicamento,

            'cod_personal' => $this->enfermeraPersonal->cod_personal,

            'dosis' => '1',

            'unidad_dosis' => 'TABLETA',

            'via_administracion' => 'ORAL',

            'frecuencia' => 'Cada 8 horas',

            'segun_necesidad' => false,

            'fecha_hora_prescripcion' => Carbon::now(),

            'estado' => 'ACTIVA',

        ]);



        $horario = \App\Models\HorarioPrescripcion::create([

            'cod_horario_prescripcion' => 'HPR_' . strtoupper(Str::random(6)),

            'cod_prescripcion' => $prescripcion->cod_prescripcion,

            'hora_programada' => '08:00:00',

            'dosis_programada' => '1 tableta',

            'estado' => 'ACTIVO',

        ]);



        $data = $service->obtenerDatosDashboard($this->enfermeraUser);

        $agenda = $data['agenda'];



        $this->assertCount(1, $agenda);

        $this->assertEquals('MEDICACION', $agenda[0]['tipo']);

        $this->assertEquals('08:00', $agenda[0]['hora']);

        $this->assertContains($agenda[0]['estado'], ['REALIZADO', 'NORMAL', 'PRÓXIMO', 'RETRASADO']);

    }



    public function test_dashboard_retorna_sin_jornada_activa_si_no_esta_asignada_al_profesional(): void

    {

        $otroUser = User::create([

            'cod_usuario' => 'USU_' . strtoupper(Str::random(6)),

            'correo' => 'otro.enfermero@remembermind.test',

            'contrasena' => bcrypt('Password123!'),

            'estado' => 'ACTIVO',

        ]);

        $otroUser->assignRole('ENFERMEROS');



        Personal::create([

            'cod_personal' => 'PER_' . strtoupper(Str::random(6)),

            'cod_usuario' => $otroUser->cod_usuario,

            'nombres' => 'Juan',

            'apellido_paterno' => 'Perez',

            'numero_documento' => '87654321',

            'profesion' => 'Enfermero',

            'estado' => 'ACTIVO',

        ]);



        $service = app(MiTurnoService::class);

        $data = $service->obtenerDatosDashboard($otroUser);



        $this->assertEquals('FUERA_DE_TURNO', $data['modo']);

        $this->assertEquals('FUERA DE TURNO', $data['modo_label']);

        $this->assertEquals('MODO CONSULTA / SOLO LECTURA', $data['submodo_label']);

        $this->assertTrue($data['es_modo_consulta']);

        $this->assertNotEmpty($data['residentes']);

        $this->assertEquals($this->residenteAsignado->cod_residente, $data['residentes'][0]['cod_residente']);

        $this->assertEquals('A cargo de: Enf. Elena Salazar', $data['residentes'][0]['responsable_texto']);

    }



    public function test_dashboard_retorna_sin_jornada_activa_si_hora_actual_fuera_del_turno(): void

    {

        $service = app(MiTurnoService::class);



        // Turno mañana es de 07:00 a 15:00. Simulamos 16:30 (fuera de horario)

        Carbon::setTestNow(Carbon::today()->setTime(16, 30, 0));



        $data = $service->obtenerDatosDashboard($this->enfermeraUser);



        $this->assertEquals('FUERA_DE_TURNO', $data['modo']);

        $this->assertTrue($data['es_modo_consulta']);

        $this->assertNull($data['jornada']);

        $this->assertEmpty($data['residentes']);

        $this->assertEmpty($data['agenda']);

        $this->assertEquals(0, $data['alertas']);

        $this->assertNull($data['progreso']);

    }



    public function test_dashboard_soporta_turnos_nocturnos_donde_hora_cierre_menor_a_hora_inicio(): void

    {

        $service = app(MiTurnoService::class);



        // Turno nocturno: 22:00 a 06:00

        $turnoNoche = Turno::create([

            'cod_turno' => 'TUR_' . strtoupper(Str::random(6)),

            'nombre' => 'Noche',

            'hora_inicio' => '22:00:00',

            'hora_cierre' => '06:00:00',

            'estado' => 'ACTIVO',

        ]);



        // Jornada que inició ayer a las 22:00

        $jornadaNoche = Jornada::create([

            'cod_jornada' => 'JOR_' . strtoupper(Str::random(6)),

            'cod_turno' => $turnoNoche->cod_turno,

            'fecha_jornada' => Carbon::yesterday()->toDateString(),

            'estado' => 'ACTIVA',

        ]);



        // Desactivar asignación anterior y asignar jornada noche

        AsignacionPersonal::where('cod_personal', $this->enfermeraPersonal->cod_personal)->update(['estado' => 'INACTIVO']);



        AsignacionPersonal::create([

            'cod_asignacion_personal' => 'ASP_' . strtoupper(Str::random(6)),

            'cod_jornada' => $jornadaNoche->cod_jornada,

            'cod_personal' => $this->enfermeraPersonal->cod_personal,

            'cod_area' => $this->area->cod_area,

            'funcion' => 'ENFERMERO',

            'tipo_asignacion' => 'TURNO',

            'fecha_asignacion' => Carbon::yesterday()->toDateString(),

            'estado' => 'ACTIVA',

        ]);



        // Simular las 03:00 de la madrugada de hoy (dentro del turno que cruzó medianoche)

        Carbon::setTestNow(Carbon::today()->setTime(3, 0, 0));



        $data = $service->obtenerDatosDashboard($this->enfermeraUser);



        $this->assertEquals('ACTIVA', $data['estado']);

        $this->assertNotNull($data['jornada']);

        $this->assertEquals($jornadaNoche->cod_jornada, $data['jornada']['cod_jornada']);

    }



    public function test_dashboard_no_reutiliza_jornadas_anteriores_ni_futuras(): void

    {

        $service = app(MiTurnoService::class);



        // Simular fecha de mañana

        Carbon::setTestNow(Carbon::tomorrow()->setTime(10, 0, 0));



        // La jornada registrada es de hoy, no de mañana

        $data = $service->obtenerDatosDashboard($this->enfermeraUser);



        $this->assertEquals('FUERA_DE_TURNO', $data['modo']);

        $this->assertTrue($data['es_modo_consulta']);

        $this->assertNull($data['jornada']);

        $this->assertEmpty($data['residentes']);

        $this->assertEmpty($data['agenda']);

        $this->assertEquals(0, $data['alertas']);

        $this->assertNull($data['progreso']);

    }



    public function test_kpi_no_cuenta_alertas_historicas_cerradas(): void

    {

        $service = app(MiTurnoService::class);



        // 1. Alerta cerrada antigua (de ayer): NO debe contarse en el turno

        DB::table('alertas')->insert([

            'cod_alerta' => 'ALA_OLD_' . strtoupper(Str::random(4)),

            'cod_residente' => $this->residenteAsignado->cod_residente,

            'titulo' => 'Alerta cerrada de ayer',

            'descripcion' => 'Alerta histórica resuelta previamente',

            'tipo' => 'CLINICA',

            'prioridad' => 'ALTA',

            'generacion' => 'SISTEMA',

            'estado' => 'CERRADA',

            'fecha_hora' => Carbon::yesterday()->setTime(10, 0, 0),

        ]);



        // 2. Alerta persistente que venía activa desde ayer: SÍ debe contarse en el turno

        DB::table('alertas')->insert([

            'cod_alerta' => 'ALA_ACT_' . strtoupper(Str::random(4)),

            'cod_residente' => $this->residenteAsignado->cod_residente,

            'titulo' => 'Alerta persistente activa',

            'descripcion' => 'Alerta que estaba activa al inicio del turno',

            'tipo' => 'CLINICA',

            'prioridad' => 'MEDIA',

            'generacion' => 'SISTEMA',

            'estado' => 'ABIERTA',

            'fecha_hora' => Carbon::yesterday()->setTime(20, 0, 0),

        ]);



        // 3. Alerta nueva creada durante la jornada de hoy: SÍ debe contarse

        DB::table('alertas')->insert([

            'cod_alerta' => 'ALA_NOW_' . strtoupper(Str::random(4)),

            'cod_residente' => $this->residenteAsignado->cod_residente,

            'titulo' => 'Alerta creada en el turno',

            'descripcion' => 'Alerta ocurrida hoy durante el horario del turno',

            'tipo' => 'CLINICA',

            'prioridad' => 'ALTA',

            'generacion' => 'SISTEMA',

            'estado' => 'EN_ATENCION',

            'fecha_hora' => Carbon::today()->setTime(9, 0, 0),

        ]);



        $data = $service->obtenerDatosDashboard($this->enfermeraUser);



        // De las 3 alertas del residente, solo 2 corresponden a la jornada actual

        $this->assertEquals(2, $data['kpis']['total_registro']['numero']);

        $this->assertEquals(1, $data['kpis']['por_atender']['numero']);

        $this->assertEquals(1, $data['kpis']['en_atencion']['numero']);

        $this->assertEquals(0, $data['kpis']['resueltas']['numero']);

    }



    public function test_agenda_incluye_pendientes_aunque_todavia_no_tengan_ejecucion(): void

    {

        $service = app(MiTurnoService::class);



        // Medicación programada sin administración

        $medicamento = \App\Models\Medicamento::create([

            'cod_medicamento' => 'MED_' . strtoupper(Str::random(6)),

            'nombre_generico' => 'Enalapril',

            'nombre_comercial' => 'Enalapril 10mg',

            'concentracion' => '10mg',

            'forma_farmaceutica' => 'TABLETA',

            'control_especial' => false,

            'estado' => 'ACTIVO',

        ]);



        $atencion = \App\Models\Atencion::create([

            'cod_atencion' => 'ATE_' . strtoupper(Str::random(6)),

            'cod_residente' => $this->residenteAsignado->cod_residente,

            'cod_area' => $this->area->cod_area,

            'cod_personal' => $this->enfermeraPersonal->cod_personal,

            'tipo_atencion' => 'MEDICA',

            'motivo' => 'Control HTA',

            'fecha_hora' => Carbon::now(),

            'estado' => 'REALIZADA',

        ]);



        $prescripcion = \App\Models\Prescripcion::create([

            'cod_prescripcion' => 'PRE_' . strtoupper(Str::random(6)),

            'cod_residente' => $this->residenteAsignado->cod_residente,

            'cod_atencion' => $atencion->cod_atencion,

            'cod_medicamento' => $medicamento->cod_medicamento,

            'cod_personal' => $this->enfermeraPersonal->cod_personal,

            'dosis' => '1',

            'unidad_dosis' => 'TABLETA',

            'via_administracion' => 'ORAL',

            'frecuencia' => 'Cada 12 horas',

            'segun_necesidad' => false,

            'fecha_hora_prescripcion' => Carbon::now(),

            'estado' => 'ACTIVA',

        ]);



        \App\Models\HorarioPrescripcion::create([

            'cod_horario_prescripcion' => 'HPR_' . strtoupper(Str::random(6)),

            'cod_prescripcion' => $prescripcion->cod_prescripcion,

            'hora_programada' => '12:00:00',

            'dosis_programada' => '1 tableta',

            'estado' => 'ACTIVO',

        ]);



        // Cuidado programado sin ejecución

        $plan = \App\Models\PlanCuidado::create([

            'cod_plan' => 'PLC_' . strtoupper(Str::random(6)),

            'cod_residente' => $this->residenteAsignado->cod_residente,

            'cod_area' => $this->area->cod_area,

            'cod_personal' => $this->enfermeraPersonal->cod_personal,

            'tipo_plan' => 'HIGIENE',

            'nombre' => 'Higiene y confort',

            'fecha_hora_apertura' => Carbon::now()->subDays(2),

            'estado' => 'ACTIVO',

        ]);



        $intervencion = \App\Models\IntervencionCuidado::create([

            'cod_intervencion' => 'INT_' . strtoupper(Str::random(6)),

            'cod_plan' => $plan->cod_plan,

            'nombre' => 'Baño en cama y cambio de sábanas',

            'descripcion' => 'Descripción de cuidado',

            'prioridad' => 'MEDIA',

            'estado' => 'ACTIVO',

        ]);



        \App\Models\ProgramacionCuidado::create([

            'cod_programacion' => 'PRC_' . strtoupper(Str::random(6)),

            'cod_intervencion' => $intervencion->cod_intervencion,

            'cod_turno' => $this->turno->cod_turno,

            'hora_programada' => '11:00:00',

            'frecuencia' => 'DIARIA',

            'fecha_activacion' => Carbon::today()->toDateString(),

            'estado' => 'ACTIVO',

        ]);



        $data = $service->obtenerDatosDashboard($this->enfermeraUser);

        $agenda = $data['agenda'];



        // Ambas tareas deben figurar en la agenda aunque NO tengan ejecuciones todavía

        $this->assertCount(2, $agenda);

        $this->assertContains($agenda[0]['estado'], ['PENDIENTE', 'PRÓXIMO']);

        $this->assertContains($agenda[1]['estado'], ['PENDIENTE', 'PRÓXIMO']);

        $this->assertEquals(2, $data['estado_tareas']['pendientes']);

        $this->assertEquals(0, $data['estado_tareas']['realizadas']);

    }



    public function test_ejecucion_cuidado_funciona_sin_cod_programacion(): void

    {

        $service = app(MiTurnoService::class);



        $plan = \App\Models\PlanCuidado::create([

            'cod_plan' => 'PLC_' . strtoupper(Str::random(6)),

            'cod_residente' => $this->residenteAsignado->cod_residente,

            'cod_area' => $this->area->cod_area,

            'cod_personal' => $this->enfermeraPersonal->cod_personal,

            'tipo_plan' => 'MOVILIZACION',

            'nombre' => 'Plan de Movilización Asistida',

            'fecha_hora_apertura' => Carbon::now()->subDays(1),

            'estado' => 'ACTIVO',

        ]);



        $intervencion = \App\Models\IntervencionCuidado::create([

            'cod_intervencion' => 'INT_' . strtoupper(Str::random(6)),

            'cod_plan' => $plan->cod_plan,

            'nombre' => 'Cambio postural decúbito lateral',

            'descripcion' => 'Descripción de cuidado',

            'prioridad' => 'ALTA',

            'estado' => 'ACTIVO',

        ]);



        \App\Models\ProgramacionCuidado::create([

            'cod_programacion' => 'PRC_' . strtoupper(Str::random(6)),

            'cod_intervencion' => $intervencion->cod_intervencion,

            'cod_turno' => $this->turno->cod_turno,

            'hora_programada' => '08:00:00',

            'frecuencia' => 'DIARIA',

            'fecha_activacion' => Carbon::today()->toDateString(),

            'estado' => 'ACTIVO',

        ]);



        // Registrar EjecucionCuidado vinculada por cod_intervencion + cod_residente + cod_jornada (SIN cod_programacion)

        \App\Models\EjecucionCuidado::create([

            'cod_ejecucion' => 'EJC_' . strtoupper(Str::random(6)),

            'cod_intervencion' => $intervencion->cod_intervencion,

            'cod_residente' => $this->residenteAsignado->cod_residente,

            'cod_jornada' => $this->jornada->cod_jornada,

            'cod_personal' => $this->enfermeraPersonal->cod_personal,

            'fecha_hora_programada' => Carbon::today()->setTime(8, 0, 0),

            'fecha_hora_ejecucion' => Carbon::today()->setTime(8, 5, 0),

            'resultado' => 'REALIZADA',

            'estado' => 'REALIZADA',

            'observacion' => 'Cambio postural efectuado según protocolo sin novedades',

        ]);



        $data = $service->obtenerDatosDashboard($this->enfermeraUser);

        $agenda = $data['agenda'];



        $this->assertCount(1, $agenda);

        $this->assertEquals('REALIZADO', $agenda[0]['estado']);

        $this->assertEquals(1, $data['estado_tareas']['realizadas']);

        $this->assertEquals(0, $data['estado_tareas']['pendientes']);

    }



    public function test_tarea_vencida_cambia_estado_general_a_vigilancia(): void

    {

        $service = app(MiTurnoService::class);



        // Sin tareas ni alertas -> ESTABLE

        $dataInicial = $service->obtenerDatosDashboard($this->enfermeraUser);

        $this->assertEquals('ESTABLE', $dataInicial['estado_general']['badge']);



        // Crear una prescripción con horario a las 08:00 (en setUp son las 10:00, vencida por > 60 min)

        $medicamento = \App\Models\Medicamento::create([

            'cod_medicamento' => 'MED_' . strtoupper(Str::random(6)),

            'nombre_generico' => 'Amlodipino',

            'nombre_comercial' => 'Amlodipino 5mg',

            'concentracion' => '5mg',

            'forma_farmaceutica' => 'TABLETA',

            'control_especial' => false,

            'estado' => 'ACTIVO',

        ]);



        $atencion = \App\Models\Atencion::create([

            'cod_atencion' => 'ATE_' . strtoupper(Str::random(6)),

            'cod_residente' => $this->residenteAsignado->cod_residente,

            'cod_area' => $this->area->cod_area,

            'cod_personal' => $this->enfermeraPersonal->cod_personal,

            'tipo_atencion' => 'MEDICA',

            'motivo' => 'Control HTA',

            'fecha_hora' => Carbon::now(),

            'estado' => 'REALIZADA',

        ]);



        $prescripcion = \App\Models\Prescripcion::create([

            'cod_prescripcion' => 'PRE_' . strtoupper(Str::random(6)),

            'cod_residente' => $this->residenteAsignado->cod_residente,

            'cod_atencion' => $atencion->cod_atencion,

            'cod_medicamento' => $medicamento->cod_medicamento,

            'cod_personal' => $this->enfermeraPersonal->cod_personal,

            'dosis' => '1',

            'unidad_dosis' => 'TABLETA',

            'via_administracion' => 'ORAL',

            'frecuencia' => 'Cada 24 horas',

            'segun_necesidad' => false,

            'fecha_hora_prescripcion' => Carbon::now(),

            'estado' => 'ACTIVA',

        ]);



        \App\Models\HorarioPrescripcion::create([

            'cod_horario_prescripcion' => 'HPR_' . strtoupper(Str::random(6)),

            'cod_prescripcion' => $prescripcion->cod_prescripcion,

            'hora_programada' => '08:00:00',

            'dosis_programada' => '1 tableta',

            'estado' => 'ACTIVO',

        ]);



        $dataConVencida = $service->obtenerDatosDashboard($this->enfermeraUser);



        // La tarea está retrasada

        $this->assertEquals(1, $dataConVencida['estado_tareas']['retrasadas']);

        // El estado general NO debe ser ESTABLE, sino VIGILANCIA

        $this->assertNotEquals('ESTABLE', $dataConVencida['estado_general']['badge']);

        $this->assertEquals('VIGILANCIA', $dataConVencida['estado_general']['badge']);

    }

}
