<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\AsignacionResidenteJornada;
use App\Models\Jornada;
use App\Models\PaseTurno;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use App\Services\Enfermeria\PaseTurnoService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class PaseTurnoReconstruidoTest extends TestCase
{
    use RefreshDatabase;

    protected User $userSaliente;
    protected Personal $personalSaliente;
    protected User $userEntrante1;
    protected Personal $personalEntrante1;
    protected User $userEntrante2;
    protected Personal $personalEntrante2;
    protected User $userAjeno;
    protected Personal $personalAjeno;

    protected Turno $turnoManana;
    protected Turno $turnoTarde;
    protected Turno $turnoNoche;

    protected Jornada $jornadaSaliente;
    protected Jornada $jornadaEntrante;

    protected Residente $residente1;
    protected Residente $residente2;
    protected Residente $residente3;

    protected Area $area;

    protected function setUp(): void
    {
        parent::setUp();

        // Permisos y Roles
        Permission::firstOrCreate(['name' => 'enfermeria.ver_dashboard', 'guard_name' => 'web']);
        $rolEnfermero = Role::firstOrCreate(['name' => 'ENFERMEROS', 'guard_name' => 'web']);
        $rolEnfermero->givePermissionTo('enfermeria.ver_dashboard');

        // Área
        $this->area = Area::create([
            'cod_area' => 'ARE_ENF_001',
            'nombre' => 'ÁREA DE ENFERMERÍA Y CUIDADOS CONTINUOS',
            'descripcion' => 'Atención clínica y cuidados continuos',
            'estado' => 'ACTIVA',
        ]);

        // Turnos ordenados: Mañana (1), Tarde (2), Noche (3)
        $this->turnoManana = Turno::create([
            'cod_turno' => 'TUR_0001',
            'nombre' => 'TURNO MAÑANA',
            'hora_inicio' => '07:00:00',
            'hora_cierre' => '15:00:00',
            'orden' => 1,
            'estado' => 'ACTIVO',
        ]);

        $this->turnoTarde = Turno::create([
            'cod_turno' => 'TUR_0002',
            'nombre' => 'TURNO TARDE',
            'hora_inicio' => '15:00:00',
            'hora_cierre' => '23:00:00',
            'orden' => 2,
            'estado' => 'ACTIVO',
        ]);

        $this->turnoNoche = Turno::create([
            'cod_turno' => 'TUR_0003',
            'nombre' => 'TURNO NOCHE',
            'hora_inicio' => '23:00:00',
            'hora_cierre' => '07:00:00',
            'orden' => 3,
            'estado' => 'ACTIVO',
        ]);

        $hoy = Carbon::today()->toDateString();

        // Jornada Saliente (Mañana)
        $this->jornadaSaliente = Jornada::create([
            'cod_jornada' => 'JOR_SAL_001',
            'cod_turno' => $this->turnoManana->cod_turno,
            'fecha_jornada' => $hoy,
            'estado' => 'ABIERTA',
        ]);

        // Jornada Entrante (Tarde)
        $this->jornadaEntrante = Jornada::create([
            'cod_jornada' => 'JOR_ENT_001',
            'cod_turno' => $this->turnoTarde->cod_turno,
            'fecha_jornada' => $hoy,
            'estado' => 'PLANIFICADA',
        ]);

        // Usuarios y Personal (creados mediante factory que vincula Personal automáticamente)
        $this->userSaliente = User::factory()->create([
            'nombres' => 'Rosa',
            'ap_paterno' => 'Mamani',
            'ap_materno' => 'Flores',
            'correo' => 'rosa@remembermind.test',
            'estado' => 'ACTIVO',
        ]);
        $this->userSaliente->assignRole($rolEnfermero);
        $this->personalSaliente = $this->userSaliente->personal;

        $this->userEntrante1 = User::factory()->create([
            'nombres' => 'Elena',
            'ap_paterno' => 'Vargas',
            'ap_materno' => 'Rojas',
            'correo' => 'elena@remembermind.test',
            'estado' => 'ACTIVO',
        ]);
        $this->userEntrante1->assignRole($rolEnfermero);
        $this->personalEntrante1 = $this->userEntrante1->personal;

        $this->userEntrante2 = User::factory()->create([
            'nombres' => 'Ana',
            'ap_paterno' => 'Torres',
            'ap_materno' => 'Paz',
            'correo' => 'ana@remembermind.test',
            'estado' => 'ACTIVO',
        ]);
        $this->userEntrante2->assignRole($rolEnfermero);
        $this->personalEntrante2 = $this->userEntrante2->personal;

        $this->userAjeno = User::factory()->create([
            'nombres' => 'Carlos',
            'ap_paterno' => 'Perez',
            'ap_materno' => 'Soto',
            'correo' => 'carlos@remembermind.test',
            'estado' => 'ACTIVO',
        ]);
        $this->userAjeno->assignRole($rolEnfermero);
        $this->personalAjeno = $this->userAjeno->personal;

        // Asignaciones de personal a la jornada saliente
        AsignacionPersonal::create([
            'cod_asignacion_personal' => 'ASP_SAL_001',
            'cod_personal' => $this->personalSaliente->cod_personal,
            'cod_jornada' => $this->jornadaSaliente->cod_jornada,
            'cod_area' => $this->area->cod_area,
            'tipo_asignacion' => 'RESPONSABLE',
            'fecha_asignacion' => now(),
            'estado' => 'PRESENTE',
        ]);

        // Asignaciones de personal a la jornada entrante
        AsignacionPersonal::create([
            'cod_asignacion_personal' => 'ASP_ENT_001',
            'cod_personal' => $this->personalEntrante1->cod_personal,
            'cod_jornada' => $this->jornadaEntrante->cod_jornada,
            'cod_area' => $this->area->cod_area,
            'tipo_asignacion' => 'RESPONSABLE',
            'fecha_asignacion' => now(),
            'estado' => 'PLANIFICADO',
        ]);

        AsignacionPersonal::create([
            'cod_asignacion_personal' => 'ASP_ENT_002',
            'cod_personal' => $this->personalEntrante2->cod_personal,
            'cod_jornada' => $this->jornadaEntrante->cod_jornada,
            'cod_area' => $this->area->cod_area,
            'tipo_asignacion' => 'RESPONSABLE',
            'fecha_asignacion' => now(),
            'estado' => 'PLANIFICADO',
        ]);

        // Residentes
        $this->residente1 = Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_0001',
            'nombres' => 'Mario',
            'apellido_paterno' => 'Gutiérrez',
            'apellido_materno' => 'Suárez',
            'numero_documento' => '1234567',
            'fecha_nacimiento' => '1945-05-10',
            'estado' => 'ACTIVO',
        ]);

        $this->residente2 = Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_0002',
            'nombres' => 'Carmen',
            'apellido_paterno' => 'López',
            'apellido_materno' => 'Vega',
            'numero_documento' => '7654321',
            'fecha_nacimiento' => '1950-08-20',
            'estado' => 'ACTIVO',
        ]);

        $this->residente3 = Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_0003',
            'nombres' => 'Julio',
            'apellido_paterno' => 'Morales',
            'apellido_materno' => 'Rios',
            'numero_documento' => '9988776',
            'fecha_nacimiento' => '1948-03-15',
            'estado' => 'ACTIVO',
        ]);

        // Asignaciones Residente - Jornada Saliente:
        // Rosa (Saliente) tiene asignados a residente1, residente2 y residente3
        AsignacionResidenteJornada::create([
            'cod_asignacion' => 'ARJ_0001',
            'cod_residente' => $this->residente1->cod_residente,
            'cod_jornada' => $this->jornadaSaliente->cod_jornada,
            'cod_personal' => $this->personalSaliente->cod_personal,
            'estado' => 'ACTIVO',
        ]);
        AsignacionResidenteJornada::create([
            'cod_asignacion' => 'ARJ_0002',
            'cod_residente' => $this->residente2->cod_residente,
            'cod_jornada' => $this->jornadaSaliente->cod_jornada,
            'cod_personal' => $this->personalSaliente->cod_personal,
            'estado' => 'ACTIVO',
        ]);
        AsignacionResidenteJornada::create([
            'cod_asignacion' => 'ARJ_0003',
            'cod_residente' => $this->residente3->cod_residente,
            'cod_jornada' => $this->jornadaSaliente->cod_jornada,
            'cod_personal' => $this->personalSaliente->cod_personal,
            'estado' => 'ACTIVO',
        ]);

        // Asignaciones Residente - Jornada Entrante:
        // Elena recibe residente1
        // Ana recibe residente2
        // residente3 queda sin asignación en la jornada entrante (pendiente)
        AsignacionResidenteJornada::create([
            'cod_asignacion' => 'ARJ_0004',
            'cod_residente' => $this->residente1->cod_residente,
            'cod_jornada' => $this->jornadaEntrante->cod_jornada,
            'cod_personal' => $this->personalEntrante1->cod_personal,
            'estado' => 'ACTIVO',
        ]);
        AsignacionResidenteJornada::create([
            'cod_asignacion' => 'ARJ_0005',
            'cod_residente' => $this->residente2->cod_residente,
            'cod_jornada' => $this->jornadaEntrante->cod_jornada,
            'cod_personal' => $this->personalEntrante2->cod_personal,
            'estado' => 'ACTIVO',
        ]);
    }

    /**
     * Criterio 1: Jornada se obtiene automáticamente para el profesional autenticado.
     */
    public function test_jornada_saliente_se_obtiene_automaticamente(): void
    {
        $service = app(PaseTurnoService::class);
        $jornada = $service->resolverJornadaSaliente($this->userSaliente);

        $this->assertNotNull($jornada);
        $this->assertEquals($this->jornadaSaliente->cod_jornada, $jornada->cod_jornada);
    }

    /**
     * Criterio 2: Residentes se cargan desde asignaciones_residente_jornada.
     */
    public function test_residentes_se_cargan_desde_asignaciones_residente_jornada(): void
    {
        $service = app(PaseTurnoService::class);
        $residentes = $service->obtenerResidentesAEntregar($this->userSaliente, $this->jornadaSaliente);

        $this->assertCount(3, $residentes);
        $ids = $residentes->pluck('cod_residente')->all();
        $this->assertContains($this->residente1->cod_residente, $ids);
        $this->assertContains($this->residente2->cod_residente, $ids);
        $this->assertContains($this->residente3->cod_residente, $ids);
    }

    /**
     * Criterio 4 & 5: Saliente = usuario autenticado y jornada entrante se resuelve por turnos.orden.
     */
    public function test_jornada_entrante_se_resuelve_correctamente(): void
    {
        $service = app(PaseTurnoService::class);
        $jornadaEntrante = $service->resolverJornadaEntrante($this->jornadaSaliente);

        $this->assertNotNull($jornadaEntrante);
        $this->assertEquals($this->jornadaEntrante->cod_jornada, $jornadaEntrante->cod_jornada);
    }

    /**
     * Criterio 6 & 7: Receptor se resuelve por residente y puede quedar NULL si no hay asignación.
     */
    public function test_receptores_se_resuelven_individualmente_por_residente(): void
    {
        $service = app(PaseTurnoService::class);
        $residentes = $service->obtenerResidentesAEntregar($this->userSaliente, $this->jornadaSaliente);

        $r1 = $residentes->firstWhere('cod_residente', $this->residente1->cod_residente);
        $r2 = $residentes->firstWhere('cod_residente', $this->residente2->cod_residente);
        $r3 = $residentes->firstWhere('cod_residente', $this->residente3->cod_residente);

        // Residente 1 va a Elena
        $this->assertEquals($this->personalEntrante1->cod_personal, $r1['personal_entrante']->cod_personal);
        $this->assertStringContainsString('Elena', $r1['nombre_personal_entrante']);

        // Residente 2 va a Ana
        $this->assertEquals($this->personalEntrante2->cod_personal, $r2['personal_entrante']->cod_personal);
        $this->assertStringContainsString('Ana', $r2['nombre_personal_entrante']);

        // Residente 3 queda NULL ("Pendiente de asignación")
        $this->assertNull($r3['personal_entrante']);
        $this->assertEquals('Pendiente de asignación', $r3['nombre_personal_entrante']);
    }

    /**
     * Criterio 3: Residente no puede elegirse arbitrariamente (validación backend).
     */
    public function test_no_se_puede_preparar_pase_para_residente_no_asignado(): void
    {
        $residenteAjeno = Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_0999',
            'nombres' => 'Desconocido',
            'apellido_paterno' => 'SinAsignar',
            'apellido_materno' => 'Test',
            'numero_documento' => '0000000',
            'fecha_nacimiento' => '1955-01-01',
            'estado' => 'ACTIVO',
        ]);

        $service = app(PaseTurnoService::class);

        $this->expectException(HttpException::class);
        $this->expectExceptionCode(403);

        $service->guardarBorrador($this->userSaliente, [
            'cod_residente' => $residenteAjeno->cod_residente,
            'resumen' => 'Intento ilegal',
        ]);
    }

    /**
     * Criterio 8: Resumen es obligatorio para confirmar entrega.
     */
    public function test_resumen_es_obligatorio_para_confirmar_entrega(): void
    {
        $service = app(PaseTurnoService::class);

        $this->expectException(ValidationException::class);

        $service->confirmarEntrega($this->userSaliente, [
            'cod_residente' => $this->residente1->cod_residente,
            'resumen' => '', // vacío
        ]);
    }

    /**
     * Criterios 9 & 10: Ciclo BORRADOR -> ENTREGADO, fecha_hora registrada, edición ordinaria bloqueada.
     */
    public function test_ciclo_borrador_a_entregado_y_bloqueo_edicion(): void
    {
        $service = app(PaseTurnoService::class);

        // 1. Guardar Borrador
        $pase = $service->guardarBorrador($this->userSaliente, [
            'cod_residente' => $this->residente1->cod_residente,
            'resumen' => 'Borrador inicial de evolución',
            'estado_general' => 'Lúcido, cooperador',
        ]);

        $this->assertEquals(PaseTurno::ESTADO_BORRADOR, $pase->estado);
        $this->assertTrue($pase->puedeEditarse());
        $this->assertFalse($pase->esEntregado());

        // 2. Confirmar Entrega
        $paseEntregado = $service->confirmarEntrega($this->userSaliente, [
            'cod_pase' => $pase->cod_pase,
            'cod_residente' => $this->residente1->cod_residente,
            'resumen' => 'Evolución final completa del turno',
            'estado_general' => 'Estable hemodinámicamente',
            'vigilancia' => 'Monitoreo de PA cada 4h',
        ]);

        $this->assertEquals(PaseTurno::ESTADO_ENTREGADO, $paseEntregado->estado);
        $this->assertNotNull($paseEntregado->fecha_hora);
        $this->assertFalse($paseEntregado->puedeEditarse());
        $this->assertTrue($paseEntregado->esEntregado());

        // Intentar editar pase entregado debe fallar
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(409);
        $service->guardarBorrador($this->userSaliente, [
            'cod_pase' => $paseEntregado->cod_pase,
            'cod_residente' => $this->residente1->cod_residente,
            'resumen' => 'Intento de modificar pase entregado',
        ]);
    }

    /**
     * Criterios 11 & 12: Receptor incorrecto no puede recibir (403), receptor correcto sí.
     */
    public function test_solo_receptor_valido_puede_recibir_pase(): void
    {
        $service = app(PaseTurnoService::class);

        // Rosa entrega pase de residente1 (cuyo receptor asignado en la tarde es Elena)
        $pase = $service->confirmarEntrega($this->userSaliente, [
            'cod_residente' => $this->residente1->cod_residente,
            'resumen' => 'Pase listo para Elena',
        ]);

        // Intento de recepción por profesional ajeno (Carlos)
        try {
            $service->confirmarRecepcion($this->userAjeno, $pase->cod_pase, 'Recibido por error');
            $this->fail('Un profesional no asignado no debería poder recibir el pase');
        } catch (HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }

        // Intento de recepción por Ana (que está asignada a residente2, no a residente1)
        try {
            $service->confirmarRecepcion($this->userEntrante2, $pase->cod_pase, 'Recibido por Ana');
            $this->fail('Ana no tiene asignado a residente1 y no debe poder recibirlo');
        } catch (HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }

        // Recepción correcta por Elena (personalEntrante1)
        $paseRecibido = $service->confirmarRecepcion($this->userEntrante1, $pase->cod_pase, 'Todo claro, recibo conforme');
        $this->assertEquals(PaseTurno::ESTADO_RECIBIDO, $paseRecibido->estado);
        $this->assertEquals('Todo claro, recibo conforme', $paseRecibido->observacion_recepcion);
        $this->assertNotNull($paseRecibido->fecha_hora_recepcion);
    }

    /**
     * Criterio 14: observacion_recepcion es opcional.
     */
    public function test_observacion_recepcion_es_opcional(): void
    {
        $service = app(PaseTurnoService::class);

        $pase = $service->confirmarEntrega($this->userSaliente, [
            'cod_residente' => $this->residente2->cod_residente,
            'resumen' => 'Pase para Ana',
        ]);

        // Ana recibe sin observación
        $paseRecibido = $service->confirmarRecepcion($this->userEntrante2, $pase->cod_pase, null);
        $this->assertEquals(PaseTurno::ESTADO_RECIBIDO, $paseRecibido->estado);
        $this->assertNull($paseRecibido->observacion_recepcion);
        $this->assertNotNull($paseRecibido->fecha_hora_recepcion);
    }

    /**
     * Criterio 16: Evitar doble recepción.
     */
    public function test_evitar_doble_recepcion(): void
    {
        $service = app(PaseTurnoService::class);

        $pase = $service->confirmarEntrega($this->userSaliente, [
            'cod_residente' => $this->residente2->cod_residente,
            'resumen' => 'Pase para Ana',
        ]);

        // Primera recepción exitosa
        $service->confirmarRecepcion($this->userEntrante2, $pase->cod_pase);

        // Segunda recepción debe fallar con 409 Conflict
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(409);
        $service->confirmarRecepcion($this->userEntrante2, $pase->cod_pase);
    }

    /**
     * Criterio 17: Evitar duplicar pase para la misma transición y residente.
     */
    public function test_evitar_duplicar_pase_para_misma_transicion(): void
    {
        $service = app(PaseTurnoService::class);

        $service->confirmarEntrega($this->userSaliente, [
            'cod_residente' => $this->residente1->cod_residente,
            'resumen' => 'Primer pase',
        ]);

        // Intentar crear un nuevo pase sin especificar el existente
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(409);
        $service->guardarBorrador($this->userSaliente, [
            'cod_residente' => $this->residente1->cod_residente,
            'resumen' => 'Segundo pase duplicado',
        ]);
    }

    /**
     * Criterio 18: No borrado físico (anulación lógica).
     */
    public function test_no_borrado_fisico_anulacion_logica(): void
    {
        $service = app(PaseTurnoService::class);

        $pase = $service->confirmarEntrega($this->userSaliente, [
            'cod_residente' => $this->residente1->cod_residente,
            'resumen' => 'Pase que será anulado',
        ]);

        $paseAnulado = $service->anularPase($this->userSaliente, $pase->cod_pase, 'Error en el reporte clínico');
        $this->assertEquals(PaseTurno::ESTADO_ANULADO, $paseAnulado->estado);

        // Registro aún existe en la base de datos
        $this->assertDatabaseHas('pases_turno', [
            'cod_pase' => $pase->cod_pase,
            'estado' => PaseTurno::ESTADO_ANULADO,
        ]);
    }

    /**
     * Criterio 19: Componente Livewire PaseTurnoPanel funciona de extremo a extremo sin selects manuales.
     */
    public function test_livewire_panel_flujo_completo(): void
    {
        // Actuamos como Rosa (Saliente)
        $this->actingAs($this->userSaliente);

        $testable = Livewire::test(\App\Livewire\Cuidados\PaseTurnoPanel::class);

        // Verificar datos contextuales automáticos en el componente
        $testable->assertSet('tabActivo', 'entrega')
            ->assertSee('Pases de turno')
            ->assertSee('Continuidad de cuidados y comunicación entre jornadas')
            ->assertSee('Mario Gutiérrez')
            ->assertSee('Carmen López')
            ->assertSee('Elena Vargas')
            ->assertSee('Ana Torres')
            ->assertSee('Pendiente de asignación');

        // Abrir modal de preparación para Residente 1
        $testable->call('abrirPrepararPase', $this->residente1->cod_residente)
            ->assertSet('modalPreparar', true)
            ->assertSet('codResidenteSeleccionado', $this->residente1->cod_residente)
            ->set('resumenTurno', 'Paciente tranquilo, medicación completada')
            ->set('estadoGeneral', 'Estable')
            ->call('confirmarEntrega')
            ->assertHasNoErrors()
            ->assertSet('modalPreparar', false);

        // Verificar en BDD que el pase fue creado y entregado
        $pase = PaseTurno::where('cod_residente', $this->residente1->cod_residente)
            ->where('cod_jornada_saliente', $this->jornadaSaliente->cod_jornada)
            ->first();

        $this->assertNotNull($pase);
        $this->assertEquals(PaseTurno::ESTADO_ENTREGADO, $pase->estado);
        $this->assertEquals('Paciente tranquilo, medicación completada', $pase->resumen);

        // Ahora actuamos como Elena (Entrante)
        $this->actingAs($this->userEntrante1);

        $testableReceptor = Livewire::test(\App\Livewire\Cuidados\PaseTurnoPanel::class);

        // Elena ve en la sección de "Pases pendientes de recibir" el pase de Mario Gutiérrez
        $testableReceptor->assertSee('Mario Gutiérrez')
            ->assertSee('Rosa Mamani')
            ->call('abrirRevisarPase', $pase->cod_pase)
            ->assertSet('modalRevisar', true)
            ->set('observacionRecepcion', 'Recibido en buen estado general')
            ->call('confirmarRecepcion')
            ->assertHasNoErrors()
            ->assertSet('modalRevisar', false);

        $pase->refresh();
        $this->assertEquals(PaseTurno::ESTADO_RECIBIDO, $pase->estado);
        $this->assertEquals('Recibido en buen estado general', $pase->observacion_recepcion);
        $this->assertNotNull($pase->fecha_hora_recepcion);
    }
}
