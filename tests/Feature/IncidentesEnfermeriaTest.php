<?php

namespace Tests\Feature;

use App\Frontend\Livewire\Enfermeria\Cuidados\IncidentesPanel;
use App\Models\Alerta;
use App\Models\Area;
use App\Models\Contacto;
use App\Models\Derivacion;
use App\Models\EventoAlerta;
use App\Models\Incidente;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\ResidenteContacto;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IncidentesEnfermeriaTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Personal $personal;
    protected Residente $residente;
    protected Area $area;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear permisos y roles
        Permission::firstOrCreate(['name' => 'enfermeria.ver_dashboard', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'SUPERADMINISTRADOR', 'guard_name' => 'web']);
        $roleEnfermero = Role::firstOrCreate(['name' => 'ENFERMEROS', 'guard_name' => 'web']);
        $roleEnfermero->givePermissionTo('enfermeria.ver_dashboard');

        // Crear usuario con Personal automático mediante Factory
        $this->user = User::factory()->create([
            'estado' => 'ACTIVO',
            'nombres' => 'Carla',
            'ap_paterno' => 'Encinas',
            'ap_materno' => 'Test'
        ]);
        $this->user->assignRole($roleEnfermero);

        $this->personal = $this->user->personal;

        // Crear Residente mediante método oficial de admisión
        $this->residente = Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_TEST01',
            'nombres' => 'Juan',
            'apellido_paterno' => 'Perez',
            'apellido_materno' => 'Gomez',
            'fecha_nacimiento' => '1945-05-10',
            'numero_documento' => '1234567',
            'estado' => 'ACTIVO'
        ]);

        // Crear Área institucional
        $this->area = Area::create([
            'cod_area' => 'ARE_MED01',
            'nombre' => 'MEDICINA GENERAL',
            'descripcion' => 'Área médica',
            'estado' => 'ACTIVO'
        ]);
    }

    public function test_usuario_autenticado_puede_acceder_a_vista_incidentes_con_sidebar_completo(): void
    {
        $response = $this->actingAs($this->user)->get(route('admin.enfermeria.incidentes'));
        $response->assertStatus(200);
        $response->assertSee('Incidentes');
        $response->assertSee('Registro y seguimiento de eventos');

        // Verificar que la barra lateral oficial unificada de enfermería está presente
        $response->assertSee('INCIDENTES');
        $response->assertSee('Mis residentes');
        $response->assertSee('Cuidados');
        $response->assertSee('Pase de turno');
    }

    public function test_livewire_incidentes_panel_renderiza_correctamente(): void
    {
        Livewire::actingAs($this->user)
            ->test(IncidentesPanel::class)
            ->assertStatus(200)
            ->assertSee('Listado')
            ->assertSee('Historial')
            ->assertSee('+ Registrar incidencia');
    }

    public function test_registro_incidente_valida_campos_requeridos(): void
    {
        Livewire::actingAs($this->user)
            ->test(IncidentesPanel::class)
            ->call('abrirModalRegistro')
            ->call('registrarIncidente')
            ->assertHasErrors(['cod_residente', 'tipo_incidente', 'descripcion']);
    }

    public function test_registro_incidente_rechaza_fechas_futuras(): void
    {
        $manana = Carbon::tomorrow()->format('Y-m-d');

        Livewire::actingAs($this->user)
            ->test(IncidentesPanel::class)
            ->call('abrirModalRegistro')
            ->set('cod_residente', $this->residente->cod_residente)
            ->set('tipo_incidente', 'CAÍDA')
            ->set('gravedad', 'ALTA')
            ->set('fecha_incidente', $manana)
            ->set('hora_incidente', '10:00')
            ->set('descripcion', 'El paciente intentó levantarse de la cama.')
            ->call('registrarIncidente')
            ->assertHasErrors(['fecha_incidente']);
    }

    public function test_registro_incidente_exitoso_y_normalizacion_de_texto(): void
    {
        $fechaInc = Carbon::now()->subMinutes(15);

        Livewire::actingAs($this->user)
            ->test(IncidentesPanel::class)
            ->call('abrirModalRegistro')
            ->set('cod_residente', $this->residente->cod_residente)
            ->set('tipo_incidente', 'CAÍDA')
            ->set('gravedad', 'ALTA')
            ->set('lugar', 'Pasillo Norte ')
            ->set('fecha_incidente', $fechaInc->format('Y-m-d'))
            ->set('hora_incidente', $fechaInc->format('H:i'))
            ->set('descripcion', 'Paciente resbaló levemente al caminar hacia el baño sin calzado adecuado.')
            ->set('medida_inmediata', 'Ayuda para incorporarse, toma de tensión arterial (120/80) y reposo en sillón.')
            ->set('requiere_medico', true)
            ->set('requiere_derivacion', false)
            ->set('observacion', 'Paciente refiere leve dolor en rodilla izquierda.')
            ->call('registrarIncidente')
            ->assertHasNoErrors()
            ->assertSet('modalExito', true);

        // Verificar inserción en base de datos
        $this->assertDatabaseHas('incidentes', [
            'cod_residente' => $this->residente->cod_residente,
            'cod_personal' => $this->personal->cod_personal,
            'tipo_incidente' => 'CAÍDA',
            'gravedad' => 'ALTA',
            'lugar' => 'Pasillo Norte',
            'requiere_medico' => true,
            'requiere_derivacion' => false,
            'estado' => 'ABIERTO'
        ]);

        $incidente = Incidente::where('cod_residente', $this->residente->cod_residente)->first();
        $this->assertNotNull($incidente);
        $this->assertEquals('Paciente resbaló levemente al caminar hacia el baño sin calzado adecuado.', $incidente->descripcion);
        $this->assertEquals('Ayuda para incorporarse, toma de tensión arterial (120/80) y reposo en sillón.', $incidente->medida_inmediata);
    }

    public function test_solicitar_valoracion_medica_crea_alerta_vinculada_y_evento(): void
    {
        $incidente = Incidente::create([
            'cod_incidente' => 'INC_VAL001',
            'cod_residente' => $this->residente->cod_residente,
            'cod_personal' => $this->personal->cod_personal,
            'tipo_incidente' => 'ALTERACIÓN CONDUCTUAL',
            'gravedad' => 'ALTA',
            'fecha_hora' => Carbon::now(),
            'descripcion' => 'Agitación psicomotriz nocturna persistente.',
            'medida_inmediata' => 'Acompañamiento verbal y contención ambiental.',
            'requiere_medico' => true,
            'requiere_derivacion' => false,
            'estado' => 'ABIERTO'
        ]);

        Livewire::actingAs($this->user)
            ->test(IncidentesPanel::class)
            ->call('solicitarValoracionMedica', $incidente->cod_incidente);

        // Verificar que se creó la alerta asociada
        $this->assertDatabaseHas('alertas', [
            'modulo' => 'INCIDENTES',
            'cod_registro' => $incidente->cod_incidente,
            'cod_residente' => $this->residente->cod_residente,
            'prioridad' => 'ALTA',
            'estado' => 'ACTIVA'
        ]);

        // Verificar que se creó el evento de alerta
        $alerta = Alerta::where('modulo', 'INCIDENTES')->where('cod_registro', $incidente->cod_incidente)->first();
        $this->assertNotNull($alerta);

        $this->assertDatabaseHas('eventos_alerta', [
            'cod_alerta' => $alerta->cod_alerta,
            'cod_usuario' => $this->user->cod_usuario,
            'tipo_evento' => 'CREACION',
            'estado_nuevo' => 'ACTIVA'
        ]);

        // Verificar que el incidente pasa a EN_SEGUIMIENTO
        $incidente->refresh();
        $this->assertEquals('EN_SEGUIMIENTO', $incidente->estado);
    }

    public function test_crear_derivacion_asociada(): void
    {
        $incidente = Incidente::create([
            'cod_incidente' => 'INC_DER001',
            'cod_residente' => $this->residente->cod_residente,
            'cod_personal' => $this->personal->cod_personal,
            'tipo_incidente' => 'SÍNTOMA CLÍNICO',
            'gravedad' => 'MEDIA',
            'fecha_hora' => Carbon::now(),
            'descripcion' => 'Tos persistente con expectoración verdosa.',
            'requiere_medico' => true,
            'requiere_derivacion' => true,
            'estado' => 'EN_SEGUIMIENTO'
        ]);

        Livewire::actingAs($this->user)
            ->test(IncidentesPanel::class)
            ->call('abrirModalDerivacion', $incidente->cod_incidente)
            ->set('derivacion_area_receptora', $this->area->cod_area)
            ->set('derivacion_prioridad', 'ALTA')
            ->set('derivacion_motivo', 'Se requiere evaluación por medicina general por cuadro respiratorio.')
            ->call('crearDerivacion')
            ->assertHasNoErrors()
            ->assertSet('modalDerivacion', false);

        // Verificar que existe en la tabla derivaciones (sin inventar cod_incidente)
        $this->assertDatabaseHas('derivaciones', [
            'cod_residente' => $this->residente->cod_residente,
            'cod_area_receptora' => $this->area->cod_area,
            'prioridad' => 'ALTA',
            'estado' => 'SOLICITADA'
        ]);

        $incidente->refresh();
        $this->assertTrue($incidente->requiere_derivacion);
        $this->assertStringContainsString('Derivación creada', $incidente->observacion);
    }

    public function test_actualizar_estado_trazable(): void
    {
        $incidente = Incidente::create([
            'cod_incidente' => 'INC_EST001',
            'cod_residente' => $this->residente->cod_residente,
            'cod_personal' => $this->personal->cod_personal,
            'tipo_incidente' => 'CAÍDA',
            'gravedad' => 'BAJA',
            'fecha_hora' => Carbon::now(),
            'descripcion' => 'Pérdida de equilibrio sin golpe ni traumatismo.',
            'requiere_medico' => false,
            'requiere_derivacion' => false,
            'estado' => 'ABIERTO'
        ]);

        Livewire::actingAs($this->user)
            ->test(IncidentesPanel::class)
            ->call('abrirModalEstado', $incidente->cod_incidente)
            ->set('nuevo_estado', 'CERRADO')
            ->set('nota_estado', 'El residente se encuentra asintomático tras 24 horas de observación.')
            ->call('actualizarEstado')
            ->assertHasNoErrors()
            ->assertSet('modalEstado', false);

        $incidente->refresh();
        $this->assertEquals('CERRADO', $incidente->estado);
        $this->assertStringContainsString('asintomático tras 24 horas', $incidente->observacion);
        $this->assertStringContainsString('Estado cambiado de ABIERTO a CERRADO', $incidente->observacion);
    }

    public function test_ordenamiento_prioriza_graves_activos_y_en_seguimiento(): void
    {
        // 1. Incidente cerrado
        $incCerrado = Incidente::create([
            'cod_incidente' => 'INC_ORD01',
            'cod_residente' => $this->residente->cod_residente,
            'cod_personal' => $this->personal->cod_personal,
            'tipo_incidente' => 'CAÍDA',
            'gravedad' => 'ALTA',
            'fecha_hora' => Carbon::now()->subHours(5),
            'descripcion' => 'Caída resuelta.',
            'requiere_medico' => false,
            'requiere_derivacion' => false,
            'estado' => 'CERRADO'
        ]);

        // 2. Incidente baja gravedad abierto
        $incBaja = Incidente::create([
            'cod_incidente' => 'INC_ORD02',
            'cod_residente' => $this->residente->cod_residente,
            'cod_personal' => $this->personal->cod_personal,
            'tipo_incidente' => 'RECHAZO DE MEDICACIÓN',
            'gravedad' => 'BAJA',
            'fecha_hora' => Carbon::now()->subMinutes(10),
            'descripcion' => 'Rechaza dosis de la mañana.',
            'requiere_medico' => false,
            'requiere_derivacion' => false,
            'estado' => 'ABIERTO'
        ]);

        // 3. Incidente grave activo (debe ir primero a pesar de ser anterior)
        $incGrave = Incidente::create([
            'cod_incidente' => 'INC_ORD03',
            'cod_residente' => $this->residente->cod_residente,
            'cod_personal' => $this->personal->cod_personal,
            'tipo_incidente' => 'SÍNTOMA CLÍNICO',
            'gravedad' => 'CRITICA',
            'fecha_hora' => Carbon::now()->subHours(2),
            'descripcion' => 'Disnea súbita con cianosis distal.',
            'requiere_medico' => true,
            'requiere_derivacion' => false,
            'estado' => 'ABIERTO'
        ]);

        $test = Livewire::actingAs($this->user)->test(IncidentesPanel::class);
        $incidentesEnVista = $test->viewData('incidentes')->items();

        $this->assertEquals('INC_ORD03', $incidentesEnVista[0]->cod_incidente, 'El incidente crítico activo debe ordenarse en primer lugar.');
    }

    public function test_contacto_de_emergencia_se_muestra_en_modal_ver(): void
    {
        // Crear contacto de emergencia
        $contacto = Contacto::create([
            'cod_contacto' => 'CON_TEST01',
            'nombres' => 'Maria',
            'apellido_paterno' => 'Perez',
            'apellido_materno' => 'Gomez',
            'telefono' => '71234567',
            'celular' => '71234567',
            'estado' => 'ACTIVO'
        ]);

        ResidenteContacto::create([
            'cod_residente_contacto' => 'RCO_TEST01',
            'cod_residente' => $this->residente->cod_residente,
            'cod_contacto' => $contacto->cod_contacto,
            'parentesco' => 'HIJA',
            'contacto_emergencia' => true,
            'responsable_principal' => true,
            'autoriza_informacion' => true,
            'autoriza_salida' => true,
            'estado' => 'ACTIVO'
        ]);

        $incidente = Incidente::create([
            'cod_incidente' => 'INC_VER001',
            'cod_residente' => $this->residente->cod_residente,
            'cod_personal' => $this->personal->cod_personal,
            'tipo_incidente' => 'CAÍDA',
            'gravedad' => 'ALTA',
            'fecha_hora' => Carbon::now(),
            'descripcion' => 'Caída en habitación.',
            'requiere_medico' => true,
            'requiere_derivacion' => false,
            'estado' => 'ABIERTO'
        ]);

        Livewire::actingAs($this->user)
            ->test(IncidentesPanel::class)
            ->call('verIncidente', $incidente->cod_incidente)
            ->assertSet('modalVer', true)
            ->assertSee('Maria Perez')
            ->assertSee('HIJA')
            ->assertSee('71234567');
    }
}
