<?php

namespace Tests\Feature;

use App\Frontend\Livewire\Enfermeria\Cuidados\MisPacientes;
use App\Models\Admision;
use App\Models\AdultoMayor;
use App\Models\Alerta;
use App\Models\Area;
use App\Models\AsignacionResidenteJornada;
use App\Models\Atencion;
use App\Models\Cama;
use App\Models\Habitacion;
use App\Models\HorarioPrescripcion;
use App\Models\Jornada;
use App\Models\Medicamento;
use App\Models\OcupacionCama;
use App\Models\Personal;
use App\Models\PlanCuidado;
use App\Models\Prescripcion;
use App\Models\SignoVital;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MisPacientesRedisenadaTest extends TestCase
{
    use RefreshDatabase;

    private User $enfermero;

    private Area $area;

    private Personal $personal;

    private TurnoEnfermeria $turno;

    private AdultoMayor $residenteEstable;

    private AdultoMayor $residenteCritico;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolesAndPermissionsSeeder::class]);

        $this->enfermero = User::factory()->create([
            'cod_usuario' => 'USU_ENF01',
            'nombres' => 'Elena',
            'ap_paterno' => 'Vargas',
            'estado' => 'ACTIVO',
        ]);
        $this->enfermero->assignRole('ENFERMEROS');

        $this->area = Area::create([
            'cod_area' => 'ARE_ENF_TEST',
            'nombre' => 'Enfermería de prueba',
            'estado' => 'ACTIVA',
        ]);
        $this->personal = $this->enfermero->personal()->firstOrFail();

        $this->turno = TurnoEnfermeria::create([
            'cod_turno' => 'TUR_MANANA',
            'nombre' => 'Turno Mañana',
            'hora_inicio' => '07:00:00',
            'hora_fin' => '15:00:00',
            'tipo' => 'MANANA',
            'orden' => 1,
            'estado' => 'ACTIVO',
            'fecha' => today()->toDateString(),
            'activo' => true,
        ]);

        $hab101 = Habitacion::create([
            'nombre' => 'Habitación 101',
            'numero' => '101',
            'codigo' => 'HAB-101',
            'tipo' => 'DOBLE',
            'estado' => 'ACTIVA',
            'capacidad' => 2,
        ]);

        $camaA = Cama::create([
            'cod_habitacion' => $hab101->cod_habitacion,
            'numero' => '1',
            'codigo' => 'CAMA-101A',
            'estado' => 'OCUPADA',
        ]);

        $hab102 = Habitacion::create([
            'nombre' => 'Habitación 102',
            'numero' => '102',
            'codigo' => 'HAB-102',
            'tipo' => 'DOBLE',
            'estado' => 'ACTIVA',
            'capacidad' => 2,
        ]);

        $camaB = Cama::create([
            'cod_habitacion' => $hab102->cod_habitacion,
            'numero' => '2',
            'codigo' => 'CAMA-102B',
            'estado' => 'OCUPADA',
        ]);

        // Residente 1: Estable
        $this->residenteEstable = AdultoMayor::factory()->create([
            'cod_residente' => 'AM_ESTABLE',
            'nombres' => 'Pedro',
            'ap_paterno' => 'Gomez',
            'ap_materno' => 'Paredes',
            'ci' => '1234567',
            'fecha_nac' => Carbon::now()->subYears(75)->toDateString(),
            'genero' => 'MASCULINO',
            'cod_est_adul' => 'EST_001',
        ]);

        $admisionEstable = Admision::create([
            'cod_admision' => 'ADM_PAC_EST',
            'cod_residente' => $this->residenteEstable->cod_residente,
            'cod_usuario_registro' => $this->enfermero->cod_usuario,
            'fecha_hora_admision' => now(),
            'motivo_ingreso' => 'Preparación del escenario clínico',
            'estado' => 'ACTIVA',
        ]);

        OcupacionCama::create([
            'cod_residente' => $this->residenteEstable->cod_residente,
            'cod_cama' => $camaA->cod_cama,
            'cod_admision' => $admisionEstable->cod_admision,
            'cod_usuario_registro' => $this->enfermero->cod_usuario,
            'fecha_hora_asignacion' => now(),
            'estado' => 'ACTIVO',
        ]);

        PlanCuidado::create([
            'cod_residente' => $this->residenteEstable->cod_residente,
            'cod_area' => $this->area->cod_area,
            'cod_personal' => $this->personal->cod_personal,
            'prioridad' => 'MODERADO',
            'estado' => 'ACTIVO',
            'fecha_hora_apertura' => today()->subMonth(),
        ]);

        // Residente 2: Requiere Atención (tiene alerta crítica)
        $this->residenteCritico = AdultoMayor::factory()->create([
            'cod_residente' => 'AM_CRITICO',
            'nombres' => 'Luisa',
            'ap_paterno' => 'Morales',
            'ap_materno' => 'Rios',
            'ci' => '7654321',
            'fecha_nac' => Carbon::now()->subYears(82)->toDateString(),
            'genero' => 'FEMENINO',
            'cod_est_adul' => 'EST_001',
        ]);

        $admisionCritico = Admision::create([
            'cod_admision' => 'ADM_PAC_CRI',
            'cod_residente' => $this->residenteCritico->cod_residente,
            'cod_usuario_registro' => $this->enfermero->cod_usuario,
            'fecha_hora_admision' => now(),
            'motivo_ingreso' => 'Preparación del escenario clínico',
            'estado' => 'ACTIVA',
        ]);

        OcupacionCama::create([
            'cod_residente' => $this->residenteCritico->cod_residente,
            'cod_cama' => $camaB->cod_cama,
            'cod_admision' => $admisionCritico->cod_admision,
            'cod_usuario_registro' => $this->enfermero->cod_usuario,
            'fecha_hora_asignacion' => now(),
            'estado' => 'ACTIVO',
        ]);

        Alerta::create([
            'cod_residente' => $this->residenteCritico->cod_residente,
            'cod_turno' => $this->turno->cod_turno,
            'tipo_alerta' => 'SIGNOS',
            'nivel' => 'CRITICO',
            'origen' => 'SIGNOS',
            'motivo' => 'Presión arterial descompensada severa.',
            'estado' => 'ABIERTA',
            'cod_personal_responsable' => $this->personal->cod_personal,
        ]);

        // Asignar ambos al enfermero en su turno
        $jornada = Jornada::create([
            'cod_jornada' => 'JOR_MIS_PACIENTES',
            'cod_turno' => $this->turno->cod_turno,
            'fecha_jornada' => today(),
            'estado' => 'ABIERTA',
        ]);
        AsignacionResidenteJornada::create([
            'cod_residente' => $this->residenteEstable->cod_residente,
            'cod_jornada' => $jornada->cod_jornada,
            'cod_personal' => $this->personal->cod_personal,
            'fecha_hora' => now(),
            'nivel_supervision' => 'ESTANDAR',
            'estado' => 'ACTIVA',
            'observacion' => 'Asignación de turno de prueba',
        ]);

        AsignacionResidenteJornada::create([
            'cod_residente' => $this->residenteCritico->cod_residente,
            'cod_jornada' => $jornada->cod_jornada,
            'cod_personal' => $this->personal->cod_personal,
            'fecha_hora' => now(),
            'nivel_supervision' => 'ESTANDAR',
            'estado' => 'ACTIVA',
            'observacion' => 'Asignación de turno de prueba',
        ]);

        // Signos para el residente estable
        SignoVital::create([
            'cod_residente' => $this->residenteEstable->cod_residente,
            'cod_personal' => $this->personal->cod_personal,
            'fecha' => today()->toDateString(),
            'hora' => '08:30:00',
            'presion_arterial' => '120/80',
            'frecuencia_cardiaca' => 72,
            'temperatura' => 36.5,
            'saturacion' => 98,
            'registrado_por' => $this->enfermero->cod_usuario,
            'estado' => 'VIGENTE',
        ]);

        // Seguimiento para el residente estable
        Atencion::create([
            'cod_residente' => $this->residenteEstable->cod_residente,
            'cod_area' => $this->area->cod_area,
            'cod_personal' => $this->personal->cod_personal,
            'cod_turno' => $this->turno->cod_turno,
            'fecha' => today()->toDateString(),
            'hora' => '09:00:00',
            'estado_general' => 'ESTABLE',
            'alimentacion' => 'COMPLETA',
            'movilidad' => 'INDEPENDIENTE',
            'sueno' => 'NORMAL',
            'incidente' => false,
            'requiere_medico' => false,
        ]);
    }

    public function test_cabecera_institucional_y_vista_por_defecto_tarjetas(): void
    {
        $this->actingAs($this->enfermero);

        $vista = Livewire::test(MisPacientes::class)
            ->assertSee('Mis residentes')
            ->assertSee('Personas asignadas a tu cuidado en esta jornada.')
            ->assertSee('Pedro Gomez')
            ->assertSee('Luisa Morales')
            ->assertSee('HAB-101')
            ->assertSee('HAB-102')
            ->assertSee('75 años')
            ->assertSee('82 años')
            ->assertSee('Ver resumen')
            ->assertSee('Próximo cuidado')
            ->assertSet('vistaModo', 'tarjetas');

        $this->assertStringContainsString('Ver resumen de Pedro Gomez Paredes', $vista->html());
        $this->assertStringNotContainsString(
            route('admin.enfermeria.pacientes.ficha', ['adulto' => $this->residenteEstable->cod_residente]),
            $vista->html()
        );
    }

    public function test_filtro_por_estado_clinico(): void
    {
        $this->actingAs($this->enfermero);

        // Al filtrar REQUIERE_ATENCION, solo Luisa debe aparecer
        Livewire::test(MisPacientes::class)
            ->set('filtroEstado', 'REQUIERE_ATENCION')
            ->assertSee('Luisa Morales')
            ->assertDontSee('Pedro Gomez');

        // Al filtrar ESTABLE, solo Pedro debe aparecer
        Livewire::test(MisPacientes::class)
            ->set('filtroEstado', 'ESTABLE')
            ->assertSee('Pedro Gomez')
            ->assertDontSee('Luisa Morales');
    }

    public function test_busqueda_por_nombre_o_habitacion(): void
    {
        $this->actingAs($this->enfermero);

        Livewire::test(MisPacientes::class)
            ->set('search', 'Luisa')
            ->assertSee('Luisa Morales')
            ->assertDontSee('Pedro Gomez')
            ->set('search', 'Pedro Gomez')
            ->assertSee('Pedro Gomez')
            ->assertDontSee('Luisa Morales')
            ->set('search', '101')
            ->assertSee('Pedro Gomez')
            ->assertDontSee('Luisa Morales');
    }

    public function test_conmutador_de_vista_lista_y_tarjetas(): void
    {
        $this->actingAs($this->enfermero);

        Livewire::test(MisPacientes::class)
            ->assertSet('vistaModo', 'tarjetas')
            ->set('vistaModo', 'tabla')
            ->assertSee('rm-resident-directory__item--row')
            ->set('vistaModo', 'tarjetas')
            ->assertSee('Pedro Gomez')
            ->assertSee('Luisa Morales')
            ->assertSee('Ver resumen')
            ->assertSee('rm-resident-directory__item--card');
    }

    public function test_foto_persistida_y_fallback_de_iniciales_se_muestran_sin_fotos_genericas(): void
    {
        $this->residenteCritico->update(['foto' => 'residentes/fotos/luisa.jpg']);
        $this->actingAs($this->enfermero);

        $html = Livewire::test(MisPacientes::class)->html();

        $this->assertStringContainsString(asset('storage/residentes/fotos/luisa.jpg'), $html);
        $this->assertStringContainsString('alt="Foto de Luisa Morales Rios"', $html);
        $this->assertStringContainsString('rm-resident-directory__avatar--initials', $html);
        $this->assertStringContainsString('>PG</span>', $html);
        $this->assertStringNotContainsString('href="#"', $html);
    }

    public function test_filtro_de_habitacion_y_ordenamiento_usan_datos_reales(): void
    {
        $this->actingAs($this->enfermero);

        Livewire::test(MisPacientes::class)
            ->set('filtroHabitacion', 'HAB-101')
            ->assertSee('Pedro Gomez')
            ->assertDontSee('Luisa Morales')
            ->set('filtroHabitacion', '')
            ->set('orden', 'HAB_DESC')
            ->assertSeeInOrder(['Luisa Morales', 'Pedro Gomez'])
            ->set('filtroEstado', 'REQUIERE_ATENCION')
            ->set('filtroHabitacion', 'HAB-102')
            ->assertSee('Luisa Morales')
            ->assertDontSee('Pedro Gomez')
            ->set('vistaModo', 'tarjetas')
            ->assertSee('Luisa Morales')
            ->set('search', 'sin coincidencias')
            ->assertSee('No encontramos residentes con esos criterios')
            ->call('limpiarFiltros')
            ->assertSet('search', '')
            ->assertSet('filtroHabitacion', '')
            ->assertSee('Pedro Gomez');
    }

    public function test_sin_asignaciones_muestra_estado_vacio_sin_inventar_residentes(): void
    {
        AsignacionResidenteJornada::query()->update(['estado' => 'INACTIVA']);
        $this->actingAs($this->enfermero);

        Livewire::test(MisPacientes::class)
            ->assertSee('No tienes residentes asignados')
            ->assertSee('Consultar información de mi turno')
            ->assertDontSee('Pedro Gomez')
            ->assertDontSee('Luisa Morales');
    }

    public function test_sin_turno_activo_no_muestra_residentes_ni_badge_de_turno(): void
    {
        Jornada::query()->where('cod_jornada', 'JOR_MIS_PACIENTES')->update(['estado' => 'CERRADA']);
        $this->actingAs($this->enfermero);

        Livewire::test(MisPacientes::class)
            ->assertSet('esModoConsulta', true)
            ->assertSee('No tienes residentes asignados')
            ->assertDontSee('Mi turno activo')
            ->assertDontSee('Pedro Gomez')
            ->assertDontSee('Luisa Morales');
    }

    public function test_apertura_de_modales_de_accion_rapida(): void
    {
        $this->actingAs($this->enfermero);

        Livewire::test(MisPacientes::class)
            ->call('abrirRegistrarSignos', $this->residenteEstable->cod_residente)
            ->assertSet('modalSignos', true)
            ->assertSet('modalCodResidente', $this->residenteEstable->cod_residente)
            ->call('abrirRegistrarSeguimiento', $this->residenteEstable->cod_residente)
            ->assertSet('modalSeguimiento', true)
            ->assertSet('segEstado', '')
            ->assertSet('segAlimentacion', '')
            ->assertSet('segMovilidad', '')
            ->assertSet('segSueno', '')
            ->call('abrirAdministrarMed', $this->residenteEstable->cod_residente)
            ->assertSet('modalMed', true)
            ->call('abrirReportarAlerta', $this->residenteEstable->cod_residente)
            ->assertSet('modalAlerta', true)
            ->call('abrirRegistrarCuidado', $this->residenteEstable->cod_residente, 'ALIMENTACION')
            ->assertSet('modalCuidado', true)
            ->call('abrirRegistrarDolor', $this->residenteEstable->cod_residente)
            ->assertSet('modalDolor', true)
            ->call('abrirRegistrarProcedimiento', $this->residenteEstable->cod_residente, 'CURACION')
            ->assertSet('modalProcedimiento', true);
    }

    public function test_ficha_rapida_muestra_acciones_en_footer_y_un_solo_drawer(): void
    {
        $this->actingAs($this->enfermero);

        $vista = Livewire::test(MisPacientes::class)
            ->call('seleccionarResidente', $this->residenteEstable->cod_residente)
            ->assertSet('mostrarPanelDetalle', true)
            ->assertSet('drawerPaso', 'resident-summary')
            ->assertSee('Resumen del residente')
            ->assertSee('Resumen clínico')
            ->assertSee('Registrar')
            ->assertSee('Ver ficha');

        $html = $vista->html();
        $this->assertSame(1, substr_count($html, 'class="rm-drawer-backdrop"'));
        $this->assertSame(1, substr_count($html, 'class="rm-drawer-footer'));
        $this->assertSame(1, substr_count($html, 'class="rm-modal-shell fixed inset-0'));
        $this->assertStringContainsString(route('admin.enfermeria.pacientes.ficha', ['adulto' => $this->residenteEstable->cod_residente]), $html);
        $this->assertLessThan(strpos($html, 'Registrar</button>'), strpos($html, '<footer class="rm-drawer-footer'));

        $vista->call('mostrarSelectorRegistro')
            ->assertSet('mostrarPanelDetalle', false)
            ->assertSet('mostrarSelectorModal', true)
            ->assertSet('drawerPaso', 'register-selector')
            ->assertSee('Nuevo registro')
            ->assertSee('Volver a la vista rápida')
            ->assertSee('Registro del residente seleccionado')
            ->assertSee('Signos vitales')
            ->assertSee('Medicación programada')
            ->assertSee('Valoración de dolor')
            ->assertSee('Cuidado de alimentación')
            ->assertSee('Cuidado de eliminación')
            ->assertSee('Cuidado de movilidad')
            ->assertSee('Seguimiento diario')
            ->assertSee('Curación o procedimiento')
            ->assertSet('tieneMedicacionProgramadaPendiente', false)
            ->assertSee('No hay prescripciones vigentes.');

        $this->assertStringNotContainsString('class="rm-modal-panel__drag-handle"', $vista->html());
        $this->assertStringContainsString('role="dialog" aria-modal="true" aria-labelledby="resident-register-titulo"', $vista->html());
        $this->assertStringContainsString('x-trap.noscroll="show"', $vista->html());
        $this->assertStringContainsString('x-on:keydown.escape.window="if (show', $vista->html());
        $this->assertStringContainsString('$nextTick(() => requestAnimationFrame', $vista->html());
        preg_match('/<div class="rm-resident-directory__register-options">(.*?)<\/div>/s', $vista->html(), $opciones);
        $this->assertNotEmpty($opciones);
        $this->assertSame(8, substr_count($opciones[1], 'class="rm-resident-directory__register-option '));
        $this->assertTrue(strpos($opciones[1], "abrirFormularioRegistro('signos')") < strpos($opciones[1], "abrirFormularioRegistro('medicacion')"));
        $this->assertTrue(strpos($opciones[1], "abrirFormularioRegistro('medicacion')") < strpos($opciones[1], "abrirFormularioRegistro('dolor')"));
        $this->assertTrue(strpos($opciones[1], "abrirFormularioRegistro('dolor')") < strpos($opciones[1], "abrirFormularioRegistro('alimentacion')"));
        $this->assertTrue(strpos($opciones[1], "abrirFormularioRegistro('alimentacion')") < strpos($opciones[1], "abrirFormularioRegistro('eliminacion')"));
        $this->assertTrue(strpos($opciones[1], "abrirFormularioRegistro('eliminacion')") < strpos($opciones[1], "abrirFormularioRegistro('movilidad')"));
        $this->assertTrue(strpos($opciones[1], "abrirFormularioRegistro('movilidad')") < strpos($opciones[1], "abrirFormularioRegistro('seguimiento')"));
        $this->assertTrue(strpos($opciones[1], "abrirFormularioRegistro('seguimiento')") < strpos($opciones[1], "abrirFormularioRegistro('procedimiento')"));
        $this->assertStringNotContainsString('Alerta clínica', $opciones[1]);
        $this->assertMatchesRegularExpression('/wire:click="abrirFormularioRegistro\(\x27medicacion\x27\)"[^>]*disabled/', $opciones[1]);
        $this->assertStringNotContainsString('href="#"', $opciones[1]);
        $this->assertStringNotContainsString('type="submit"', $opciones[1]);
        $this->assertSame(0, substr_count($vista->html(), 'class="rm-modal-footer'));

        $vista->call('cerrarSelectorRegistro')
            ->assertSet('mostrarSelectorModal', false)
            ->assertSet('mostrarPanelDetalle', true)
            ->assertSet('drawerPaso', 'resident-summary');
    }

    public function test_ruta_de_mis_residentes_no_atrapa_el_drawer_en_un_ancestro_transformado(): void
    {
        $this->actingAs($this->enfermero);

        $respuesta = $this->get(route('admin.enfermeria.pacientes'))->assertOk();

        $this->assertStringNotContainsString('pt-4 sm:pt-5 animate-fade-in-up', $respuesta->getContent());
        $this->get(route('admin.enfermeria.pacientes.ficha', [
            'adulto' => $this->residenteEstable->cod_residente,
        ]))->assertOk();
    }

    public function test_registro_navega_dentro_del_modal_y_protege_cambios_sin_guardar(): void
    {
        $this->actingAs($this->enfermero);

        Livewire::test(MisPacientes::class)
            ->call('seleccionarResidente', $this->residenteEstable->cod_residente)
            ->call('mostrarSelectorRegistro')
            ->assertSet('drawerPaso', 'register-selector')
            ->assertSet('mostrarSelectorModal', true)
            ->assertSet('mostrarPanelDetalle', false)
            ->assertSee('Selecciona el tipo de registro que deseas realizar:')
            ->assertDontSee('Resumen clínico')
            ->call('abrirFormularioRegistro', 'signos')
            ->assertSet('drawerPaso', 'register-form')
            ->assertSet('mostrarSelectorModal', true)
            ->assertSet('mostrarPanelDetalle', false)
            ->assertSee('Presión arterial')
            ->assertSee('Guardar registro')
            ->assertSee('Volver al selector de registros')
            ->call('volverPanelDetalle')
            ->assertSet('drawerPaso', 'register-selector')
            ->assertSet('mostrarSelectorModal', true)
            ->assertSet('mostrarPanelDetalle', false)
            ->call('abrirFormularioRegistro', 'signos')
            ->set('signoSis', '120')
            ->call('volverPanelDetalle')
            ->assertSet('confirmarDescarte', true)
            ->assertSee('Cambios sin guardar')
            ->assertSee('Tienes información que todavía no se ha guardado.')
            ->assertSee('Continuar editando')
            ->assertSee('Descartar cambios')
            ->call('cancelarDescarte')
            ->assertSet('confirmarDescarte', false)
            ->assertSet('drawerPaso', 'register-form')
            ->call('cerrarSelectorRegistro')
            ->assertSet('confirmarDescarte', true)
            ->call('descartarCambios')
            ->assertSet('mostrarPanelDetalle', true)
            ->assertSet('mostrarSelectorModal', false)
            ->assertSet('drawerPaso', 'resident-summary');
    }

    public function test_selector_muestra_el_residente_elegido_y_solo_sus_alertas_reales(): void
    {
        $this->residenteCritico->update(['foto' => 'residentes/fotos/luisa.jpg']);
        $this->actingAs($this->enfermero);

        $vista = Livewire::test(MisPacientes::class)
            ->call('seleccionarResidente', $this->residenteCritico->cod_residente)
            ->call('mostrarSelectorRegistro')
            ->assertSet('residente', $this->residenteCritico->cod_residente)
            ->assertSee('Luisa Morales Rios')
            ->assertSee('HAB-102')
            ->assertSee('CAMA-102B')
            ->assertSee('Presión arterial descompensada severa.');

        $this->assertStringContainsString(asset('storage/residentes/fotos/luisa.jpg'), $vista->html());
        $this->assertStringNotContainsString('Pedro Gomez Paredes</strong>', $vista->html());

        $vista->call('cerrarSelectorRegistro')->assertSet('mostrarPanelDetalle', true);
    }

    public function test_medicacion_sin_programacion_pendiente_no_puede_abrirse_ni_por_llamada_directa(): void
    {
        $this->actingAs($this->enfermero);

        Livewire::test(MisPacientes::class)
            ->call('seleccionarResidente', $this->residenteEstable->cod_residente)
            ->call('mostrarSelectorRegistro')
            ->assertSet('tieneMedicacionProgramadaPendiente', false)
            ->call('abrirFormularioRegistro', 'medicacion')
            ->assertStatus(422);
    }

    public function test_medicacion_con_prescripcion_y_horario_pendientes_se_habilita_para_el_residente_correcto(): void
    {
        $medicamento = Medicamento::create([
            'cod_medicamento' => 'MED_SELECTOR_TEST',
            'nombre_generico' => 'Medicamento de prueba',
            'nombre_comercial' => 'Medicamento de prueba',
            'concentracion' => '10 mg',
            'forma_farmaceutica' => 'TABLETA',
            'control_especial' => false,
            'estado' => 'ACTIVO',
        ]);
        $atencion = Atencion::create([
            'cod_atencion' => 'ATE_SELECTOR_TEST',
            'cod_residente' => $this->residenteEstable->cod_residente,
            'cod_area' => $this->area->cod_area,
            'cod_personal' => $this->personal->cod_personal,
            'tipo_atencion' => 'MEDICA',
            'motivo' => 'Prescripción de prueba',
            'fecha_hora' => now(),
            'estado' => 'REALIZADA',
        ]);
        $prescripcion = Prescripcion::create([
            'cod_prescripcion' => 'PRE_SELECTOR_TEST',
            'cod_residente' => $this->residenteEstable->cod_residente,
            'cod_atencion' => $atencion->cod_atencion,
            'cod_medicamento' => $medicamento->cod_medicamento,
            'cod_personal' => $this->personal->cod_personal,
            'dosis' => '10',
            'unidad_dosis' => 'mg',
            'via_administracion' => 'ORAL',
            'frecuencia' => 'Cada 24 horas',
            'segun_necesidad' => false,
            'fecha_hora_prescripcion' => now()->subMinute(),
            'estado' => 'ACTIVA',
        ]);
        HorarioPrescripcion::create([
            'cod_horario_prescripcion' => 'HPR_SELECTOR_TEST',
            'cod_prescripcion' => $prescripcion->cod_prescripcion,
            'hora_programada' => '08:00:00',
            'dosis_programada' => 10,
            'estado' => 'ACTIVO',
        ]);
        $this->actingAs($this->enfermero);

        Livewire::test(MisPacientes::class)
            ->call('seleccionarResidente', $this->residenteEstable->cod_residente)
            ->call('mostrarSelectorRegistro')
            ->assertSet('tieneMedicacionProgramadaPendiente', true)
            ->assertSet('cantidadMedicacionProgramadaPendiente', 1)
            ->assertSee('1 pendiente')
            ->call('abrirFormularioRegistro', 'medicacion')
            ->assertSet('drawerPaso', 'register-form')
            ->assertSet('mostrarSelectorModal', true)
            ->assertSet('medCodMed', null)
            ->assertSee('Selecciona la dosis programada')
            ->call('seleccionarOcurrenciaMed', 'HPR_SELECTOR_TEST')
            ->assertSet('medCodMed', $prescripcion->cod_prescripcion)
            ->assertSee('Medicamento de prueba')
            ->assertSee('Confirmar administración')
            ->assertSet('modalCodResidente', $this->residenteEstable->cod_residente);
    }

    public function test_opcion_sin_permiso_se_oculta_y_el_metodo_rechaza_la_apertura(): void
    {
        \Spatie\Permission\Models\Role::findByName('ENFERMEROS')->revokePermissionTo('signos_vitales.crear');
        $this->actingAs($this->enfermero);

        $vista = Livewire::test(MisPacientes::class)
            ->call('seleccionarResidente', $this->residenteEstable->cod_residente)
            ->call('mostrarSelectorRegistro');

        $this->assertStringNotContainsString('abrirFormularioRegistro(\'signos\')', $vista->html());
        $vista->call('abrirFormularioRegistro', 'signos')->assertStatus(403);
    }

    public function test_regreso_con_cambios_descartados_mantiene_el_modal_y_limpia_el_formulario(): void
    {
        $this->actingAs($this->enfermero);

        Livewire::test(MisPacientes::class)
            ->call('seleccionarResidente', $this->residenteEstable->cod_residente)
            ->call('mostrarSelectorRegistro')
            ->call('abrirFormularioRegistro', 'signos')
            ->set('signoSis', '120')
            ->call('volverPanelDetalle')
            ->assertSet('accionDescarte', 'volver')
            ->call('descartarCambios')
            ->assertSet('confirmarDescarte', false)
            ->assertSet('mostrarSelectorModal', true)
            ->assertSet('mostrarPanelDetalle', false)
            ->assertSet('drawerPaso', 'register-selector')
            ->assertSet('signoSis', '');
    }

    public function test_modal_de_registro_declara_medidas_responsivas_y_foco_accesible(): void
    {
        $css = file_get_contents(resource_path('frontend/styles/design-system/patterns/resident-directory.css'));

        $this->assertStringContainsString('.rm-resident-directory__register-modal--selector .rm-modal-panel', $css);
        $this->assertStringContainsString('max-width: 520px', $css);
        $this->assertStringContainsString('width: calc(100vw - 32px)', $css);
        $this->assertStringContainsString('max-height: calc(100dvh - 32px)', $css);
        $this->assertStringContainsString('grid-template-columns: repeat(2, minmax(0, 1fr))', $css);
        $this->assertStringContainsString('@media (max-width: 480px)', $css);
        $this->assertStringContainsString('width: calc(100vw - 16px)', $css);
        $this->assertStringContainsString('grid-template-columns: 1fr', $css);
        $this->assertStringContainsString('height: 54px', $css);
        $this->assertStringContainsString('outline: 2px solid var(--rm-focus)', $css);

        $this->actingAs($this->enfermero);
        $html = Livewire::test(MisPacientes::class)
            ->call('seleccionarResidente', $this->residenteEstable->cod_residente)
            ->call('mostrarSelectorRegistro')
            ->html();

        $this->assertStringContainsString('x-trap.noscroll="show"', $html);
        $this->assertStringContainsString('x-on:keydown.escape.window="if (show', $html);
        $this->assertStringContainsString("querySelector('#resident-register-trigger')?.focus()", $html);
    }

    public function test_guardar_signos_desde_el_modal_actualiza_el_resumen_sin_abrir_otro_panel(): void
    {
        // El registro inicial del fixture es de las 08:30; el nuevo debe ser posterior
        // incluso cuando la suite se ejecuta poco después de medianoche.
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);

        Livewire::test(MisPacientes::class)
            ->call('seleccionarResidente', $this->residenteEstable->cod_residente)
            ->call('mostrarSelectorRegistro')
            ->call('abrirFormularioRegistro', 'signos')
            ->assertDontSee('type="date"')
            ->assertDontSee('type="time"')
            ->set('signoSis', '124')
            ->set('signoDia', '78')
            ->set('signoFC', '76')
            ->set('signoFR', '18')
            ->set('signoTemp', '36.6')
            ->set('signoSat', '97')
            ->call('guardarSignos')
            ->assertHasNoErrors()
            ->assertSet('drawerPaso', 'resident-summary')
            ->assertSet('mostrarPanelDetalle', true)
            ->assertSet('mostrarSelectorModal', false)
            ->assertSee('PA 124/78');

        $this->assertDatabaseHas('signos_vitales', [
            'cod_residente' => $this->residenteEstable->cod_residente,
            'presion_sistolica' => 124,
            'presion_diastolica' => 78,
        ]);
        $this->assertSame('10:00', SignoVital::query()->latest('fecha_hora')->firstOrFail()->fecha_hora->format('H:i'));
    }

    public function test_error_de_validacion_permanece_en_el_formulario_del_modal(): void
    {
        $this->actingAs($this->enfermero);

        Livewire::test(MisPacientes::class)
            ->call('seleccionarResidente', $this->residenteEstable->cod_residente)
            ->call('mostrarSelectorRegistro')
            ->call('abrirFormularioRegistro', 'signos')
            ->set('signoSis', 'sin formato')
            ->call('guardarSignos')
            ->assertHasErrors('presion_sistolica')
            ->assertSet('drawerPaso', 'register-form')
            ->assertSet('mostrarSelectorModal', true)
            ->assertSee('Ingresa un número entero válido.');
    }

    public function test_formulario_signos_muestra_contexto_real_historial_y_errores_accesibles(): void
    {
        $this->actingAs($this->enfermero);

        $formulario = $this->formularioSignos()
            ->assertSee('Objetivos clínicos indicados por médico')
            ->assertSee('Sin objetivo individual configurado.')
            ->assertSee('Tendencia en vivo')
            ->assertSee('Registro clínico')
            ->assertDontSee('type="date"')
            ->assertDontSee('type="time"')
            ->assertSet('signosIntentoGuardar', false);

        $this->assertStringContainsString('aria-describedby="signos-sat-error signos-sat-server-error"', $formulario->html());
        $this->assertStringNotContainsString('Revisa algunos datos', $formulario->html());

        $formulario->set('signoSat', '101')->call('guardarSignos')
            ->assertHasErrors('saturacion_oxigeno')
            ->assertSee('Revisa algunos datos')
            ->assertSee('La saturación no puede superar el 100 %.')
            ->assertSet('mostrarSelectorModal', true);
    }

    public function test_tendencia_aparece_solo_con_tres_lecturas_reales_del_residente(): void
    {
        $this->actingAs($this->enfermero);
        $this->assertCount(1, $this->formularioSignos()->get('signosHistorial'));

        foreach ([['07:30:00', 68], ['09:30:00', 75]] as [$hora, $pulso]) {
            SignoVital::create([
                'cod_residente' => $this->residenteEstable->cod_residente,
                'cod_personal' => $this->personal->cod_personal,
                'fecha_hora' => today()->setTimeFromTimeString($hora),
                'frecuencia_cardiaca' => $pulso,
                'estado' => 'VIGENTE',
            ]);
        }

        $formulario = $this->formularioSignos();
        $this->assertSame([75.0, 72.0, 68.0], array_map(
            fn (array $registro) => (float) $registro['fc'],
            $formulario->get('signosHistorial'),
        ));
        $this->assertStringContainsString('rm-signos__chart', $formulario->html());
        $this->assertStringNotContainsString('rm-signos__sparkline', $formulario->html());
    }

    private function formularioSignos()
    {
        return Livewire::test(MisPacientes::class)
            ->call('seleccionarResidente', $this->residenteEstable->cod_residente)
            ->call('mostrarSelectorRegistro')
            ->call('abrirFormularioRegistro', 'signos');
    }

    public function test_presion_incompleta_se_rechaza_en_ambos_sentidos(): void
    {
        $this->actingAs($this->enfermero);

        $this->formularioSignos()->set('signoSis', '120')->call('guardarSignos')
            ->assertHasErrors('presion_diastolica')
            ->assertSee('Completa también la presión diastólica.');

        $this->formularioSignos()->set('signoDia', '80')->call('guardarSignos')
            ->assertHasErrors('presion_sistolica')
            ->assertSee('Completa también la presión sistólica.');
    }

    public function test_saturacion_fuera_de_0_a_100_y_numericos_invalidos_se_rechazan(): void
    {
        $this->actingAs($this->enfermero);

        foreach ([['101', 'La saturación no puede superar el 100 %.'], ['-1', 'La saturación no puede ser menor a 0 %.']] as [$valor, $mensaje]) {
            $this->formularioSignos()->set('signoSat', $valor)->call('guardarSignos')
                ->assertHasErrors('saturacion_oxigeno')
                ->assertSee($mensaje);
        }

        $this->formularioSignos()->set('signoFC', 'abc')->call('guardarSignos')
            ->assertHasErrors('frecuencia_cardiaca')
            ->assertSee('Ingresa un número entero válido.');
        $this->formularioSignos()->set('signoGlucosa', '-0.1')->call('guardarSignos')
            ->assertHasErrors('glucemia');
    }

    public function test_temperatura_decimal_y_observaciones_se_guardan_sin_redondear_ni_truncar(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);

        $this->formularioSignos()->set('signoTemp', '36.55')->call('guardarSignos')
            ->assertHasErrors('temperatura');
        $this->formularioSignos()->set('signoFC', '70')->set('signoObs', str_repeat('x', 5001))
            ->call('guardarSignos')->assertHasErrors('observacion');

        $this->formularioSignos()->set('signoSis', '120')->set('signoDia', '80')
            ->set('signoFC', '70')->set('signoFR', '18')->set('signoTemp', '36.5')
            ->set('signoSat', '0')->set('signoGlucosa', '90.25')
            ->set('signoObs', '  '.str_repeat('x', 5000).'  ')
            ->call('guardarSignos')->assertHasNoErrors();

        $signo = SignoVital::query()->latest('fecha_hora')->firstOrFail();
        $this->assertSame($this->personal->cod_personal, $signo->cod_personal);
        $this->assertSame('JOR_MIS_PACIENTES', $signo->cod_jornada);
        $this->assertSame(36.5, $signo->temperatura);
        $this->assertSame(0.0, $signo->saturacion_oxigeno);
        $this->assertSame(90.25, $signo->glucemia);
        $this->assertSame(str_repeat('x', 5000), $signo->observacion);
    }

    public function test_residente_sin_asignacion_no_admite_registro_de_signos(): void
    {
        $this->actingAs($this->enfermero);
        AsignacionResidenteJornada::query()
            ->where('cod_residente', $this->residenteCritico->cod_residente)
            ->update(['estado' => 'FINALIZADA']);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(\App\Backend\Modulos\Clinica\Servicios\SignosVitalesService::class)
            ->registrarDesdeNuevoRegistro($this->residenteCritico->cod_residente, [
                'fecha' => today()->toDateString(), 'hora' => now()->format('H:i'),
                'frecuencia_cardiaca' => '70',
            ], $this->enfermero);
    }

    public function test_fecha_y_hora_manipuladas_no_reemplazan_la_hora_del_servidor(): void
    {
        $this->travelTo(today()->setTime(10, 17));
        $this->actingAs($this->enfermero);

        app(\App\Backend\Modulos\Clinica\Servicios\SignosVitalesService::class)
            ->registrarDesdeNuevoRegistro($this->residenteEstable->cod_residente, [
                'fecha' => 'ayer',
                'hora' => '25:80',
                'frecuencia_cardiaca' => '70',
            ], $this->enfermero);

        $this->assertSame('10:17', SignoVital::query()->latest('fecha_hora')->firstOrFail()->fecha_hora->format('H:i'));
    }

    private function crearDosisProgramada(AdultoMayor $residente, string $codigo = 'A', string $estado = 'ACTIVA'): array
    {
        $medicamento = Medicamento::create([
            'cod_medicamento' => 'MED_PROG_'.$codigo,
            'nombre_generico' => 'Paracetamol '.$codigo,
            'nombre_comercial' => 'Paracetamol '.$codigo,
            'concentracion' => '500 mg',
            'forma_farmaceutica' => 'TABLETA',
            'estado' => 'ACTIVO',
            'control_especial' => false,
        ]);
        $atencion = Atencion::create([
            'cod_atencion' => 'ATE_PROG_'.$codigo,
            'cod_residente' => $residente->cod_residente,
            'cod_area' => $this->area->cod_area,
            'cod_personal' => $this->personal->cod_personal,
            'tipo_atencion' => 'MEDICA',
            'motivo' => 'Orden programada de prueba',
            'fecha_hora' => now(),
            'estado' => 'REALIZADA',
        ]);
        $prescripcion = Prescripcion::create([
            'cod_prescripcion' => 'PRE_PROG_'.$codigo,
            'cod_residente' => $residente->cod_residente,
            'cod_atencion' => $atencion->cod_atencion,
            'cod_medicamento' => $medicamento->cod_medicamento,
            'cod_personal' => $this->personal->cod_personal,
            'dosis' => '500.000',
            'unidad_dosis' => 'mg',
            'via_administracion' => 'ORAL',
            'frecuencia' => 'Cada 24 horas',
            'segun_necesidad' => false,
            'fecha_hora_prescripcion' => now()->subMinute(),
            'estado' => $estado,
        ]);
        $horario = HorarioPrescripcion::create([
            'cod_horario_prescripcion' => 'HPR_PROG_'.$codigo,
            'cod_prescripcion' => $prescripcion->cod_prescripcion,
            'hora_programada' => '08:00:00',
            'dosis_programada' => '500.000',
            'estado' => 'ACTIVO',
        ]);

        return [$prescripcion, $horario];
    }

    private function formularioMedicacion(string $codHorario = 'HPR_PROG_A')
    {
        return Livewire::test(MisPacientes::class)
            ->call('seleccionarResidente', $this->residenteEstable->cod_residente)
            ->call('mostrarSelectorRegistro')
            ->call('abrirFormularioRegistro', 'medicacion')
            ->call('seleccionarOcurrenciaMed', $codHorario);
    }

    public function test_administracion_programada_guarda_dosis_hora_y_autoria_reales(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);
        [$prescripcion, $horario] = $this->crearDosisProgramada($this->residenteEstable);

        $this->formularioMedicacion()
            ->assertSee('Paracetamol A')->assertSee('500 mg')->assertSee('TABLETA')
            ->assertSee('ORAL')->assertSee('Cada 24 horas')->assertSee('08:00')
            ->assertSet('medFechaHoraReal', now()->format('Y-m-d\TH:i'))
            ->set('medDosisAdministrada', '250.125')
            ->set('medObservacion', '  Dosis tolerada  ')
            ->call('guardarMed')->assertHasNoErrors()
            ->assertSet('drawerPaso', 'resident-summary');

        $this->assertDatabaseHas('administraciones_medicacion', [
            'cod_prescripcion' => $prescripcion->cod_prescripcion,
            'cod_horario_prescripcion' => $horario->cod_horario_prescripcion,
            'cod_residente' => $this->residenteEstable->cod_residente,
            'cod_personal' => $this->personal->cod_personal,
            'resultado' => 'ADMINISTRADA',
            'dosis_administrada' => 250.125,
            'observacion' => 'Dosis tolerada',
            'estado' => 'REGISTRADA',
        ]);
    }

    public function test_estado_hora_y_motivo_condicional_se_validan(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);
        $this->crearDosisProgramada($this->residenteEstable);

        $this->formularioMedicacion()->set('medResultado', 'INVENTADA')
            ->call('guardarMed')->assertHasErrors('medResultado');
        $this->formularioMedicacion()->set('medFechaHoraReal', '')
            ->call('guardarMed')->assertHasErrors('medFechaHoraReal');
        $this->formularioMedicacion()->set('medDosisAdministrada', '500.1234')
            ->call('guardarMed')->assertHasErrors('medDosisAdministrada');

        $this->formularioMedicacion()->set('medResultado', 'OMITIDA')
            ->call('guardarMed')->assertHasErrors('medMotivoOmision');
        $this->formularioMedicacion()->set('medResultado', 'OMITIDA')
            ->set('medMotivoOmision', 'Residente rechazó la dosis')
            ->set('medResultado', 'ADMINISTRADA')
            ->assertSet('medMotivoOmision', '')
            ->assertDontSee('Explica por qué no se administró');
    }

    public function test_omision_guarda_motivo_y_no_guarda_dosis_ni_hora_de_administracion(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);
        $this->crearDosisProgramada($this->residenteEstable);

        $this->formularioMedicacion()->set('medResultado', 'OMITIDA')
            ->set('medMotivoOmision', '  Residente rechazó la dosis  ')
            ->call('guardarMed')->assertHasNoErrors();

        $registro = \App\Models\AdministracionMedicacion::query()->firstOrFail();
        $this->assertSame('OMITIDA', $registro->resultado);
        $this->assertSame('Residente rechazó la dosis', $registro->motivo_omision);
        $this->assertNull($registro->fecha_hora_administracion);
        $this->assertNull($registro->dosis_administrada);
    }

    public function test_no_acepta_medicacion_de_otro_residente_ni_programacion_manipulada(): void
    {
        $this->actingAs($this->enfermero);
        $this->crearDosisProgramada($this->residenteEstable, 'A');
        $this->crearDosisProgramada($this->residenteCritico, 'B');

        Livewire::test(MisPacientes::class)
            ->call('seleccionarResidente', $this->residenteEstable->cod_residente)
            ->call('mostrarSelectorRegistro')
            ->call('abrirFormularioRegistro', 'medicacion')
            ->call('seleccionarOcurrenciaMed', 'HPR_PROG_B')
            ->assertStatus(422);

        $this->formularioMedicacion()->set('medCodMed', 'PRE_PROG_B')
            ->call('guardarMed')->assertHasErrors('medOcurrenciaSeleccionada');
    }

    public function test_prescripcion_inexistente_suspendida_y_horario_invalido_se_rechazan(): void
    {
        $this->actingAs($this->enfermero);
        $this->crearDosisProgramada($this->residenteEstable, 'A');
        $servicio = app(\App\Backend\Modulos\Medicacion\Servicios\RegistrarAdministracionMedicacionService::class);

        foreach (['PRE_INEXISTENTE', 'PRE_PROG_A'] as $codigo) {
            if ($codigo === 'PRE_PROG_A') {
                Prescripcion::query()->whereKey($codigo)->update(['estado' => 'SUSPENDIDA']);
            }
            try {
                $servicio->registrarProgramada($this->enfermero, $this->residenteEstable->cod_residente,
                    $codigo, '08:00', true, codHorario: 'HPR_PROG_A');
                $this->fail('La prescripción no vigente fue aceptada.');
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->errors());
            }
        }
        Prescripcion::query()->whereKey('PRE_PROG_A')->update(['estado' => 'ACTIVA']);
        try {
            $servicio->registrarProgramada($this->enfermero, $this->residenteEstable->cod_residente,
                'PRE_PROG_A', '08:00', true, codHorario: 'HPR_INEXISTENTE');
            $this->fail('La programación inválida fue aceptada.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertNotEmpty($e->errors());
        }
    }

    public function test_doble_administracion_y_usuario_sin_permiso_se_rechazan(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);
        $this->crearDosisProgramada($this->residenteEstable);
        $this->formularioMedicacion()->call('guardarMed')->assertHasNoErrors();
        $servicio = app(\App\Backend\Modulos\Medicacion\Servicios\RegistrarAdministracionMedicacionService::class);

        try {
            $servicio->registrarProgramada($this->enfermero, $this->residenteEstable->cod_residente,
                'PRE_PROG_A', '08:00', true, codHorario: 'HPR_PROG_A');
            $this->fail('Se aceptó una doble administración.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertNotEmpty($e->errors());
        }
        $this->assertDatabaseCount('administraciones_medicacion', 1);

        \Spatie\Permission\Models\Role::findByName('ENFERMEROS')->revokePermissionTo('administraciones_medicacion.crear');
        Livewire::test(MisPacientes::class)
            ->call('seleccionarResidente', $this->residenteEstable->cod_residente)
            ->call('mostrarSelectorRegistro')
            ->call('abrirFormularioRegistro', 'medicacion')
            ->assertStatus(403);
    }

    public function test_horario_de_otro_dia_no_se_ofrece_para_administrar_hoy(): void
    {
        $this->actingAs($this->enfermero);
        [, $horario] = $this->crearDosisProgramada($this->residenteEstable);
        $horario->update(['dias_semana' => (string) ((now()->dayOfWeekIso % 7) + 1)]);

        Livewire::test(MisPacientes::class)
            ->call('seleccionarResidente', $this->residenteEstable->cod_residente)
            ->call('mostrarSelectorRegistro')
            ->assertSet('tieneMedicacionProgramadaPendiente', false)
            ->call('abrirFormularioRegistro', 'medicacion')
            ->assertStatus(422);
    }

    private function formularioDolor()
    {
        return Livewire::test(MisPacientes::class)
            ->call('seleccionarResidente', $this->residenteEstable->cod_residente)
            ->call('mostrarSelectorRegistro')
            ->call('abrirFormularioRegistro', 'dolor');
    }

    public function test_eva_inicial_rechaza_menos_uno_once_y_decimal(): void
    {
        $this->actingAs($this->enfermero);

        foreach (['-1', '11', '2.5'] as $eva) {
            $this->formularioDolor()->set('dolorEva', $eva)
                ->call('guardarDolor')->assertHasErrors('intensidad');
        }
        $this->assertDatabaseCount('valoraciones_dolor', 0);
    }

    public function test_localizacion_excedida_y_duracion_no_positiva_se_rechazan(): void
    {
        $this->actingAs($this->enfermero);

        $this->formularioDolor()->set('dolorEva', '4')
            ->set('dolorUbicacion', str_repeat('x', 121))
            ->call('guardarDolor')->assertHasErrors('ubicacion');
        $this->formularioDolor()->set('dolorEva', '4')
            ->set('dolorDuracionValor', '0')->set('dolorDuracionUnidad', 'minutos')
            ->call('guardarDolor')->assertHasErrors('duracion_valor');
        $this->formularioDolor()->set('dolorEva', '4')
            ->set('dolorDuracionValor', '5')
            ->call('guardarDolor')->assertHasErrors('duracion_unidad');
    }

    public function test_tipo_sin_catalogo_y_reevaluacion_no_soportada_se_rechazan(): void
    {
        $this->actingAs($this->enfermero);
        $formulario = $this->formularioDolor();
        $formulario->assertSee('Reevaluación')
            ->assertDontSee('wire:model="dolorEvaPosterior"');

        $servicio = app(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class);
        $base = [
            'fecha_hora' => now()->format('Y-m-d\TH:i'),
            'intensidad' => 4,
        ];
        foreach ([
            ['tipo_dolor' => 'TIPO_INVENTADO'],
            ['eva_posterior' => 2, 'hora_reevaluacion' => now()->subHour()->format('Y-m-d\TH:i')],
        ] as $noSoportado) {
            try {
                $servicio->registrarValoracionDolor($this->residenteEstable->cod_residente,
                    $base + $noSoportado, $this->enfermero);
                $this->fail('Se aceptó un campo no soportado por el formulario.');
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->errors());
            }
        }
        $this->assertDatabaseCount('valoraciones_dolor', 0);
    }

    public function test_valoracion_de_dolor_guarda_campos_reales_y_autoria(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);

        $this->formularioDolor()
            ->assertSet('dolorEva', '')
            ->assertSet('dolorFechaHora', now()->format('Y-m-d\TH:i'))
            ->assertSee('Escala EVA inicial, de 0 a 10')
            ->set('dolorEva', '6')
            ->set('dolorUbicacion', '  Rodilla derecha  ')
            ->set('dolorDuracionValor', '30')
            ->set('dolorDuracionUnidad', 'minutos')
            ->set('dolorDesencadenante', '  Al caminar  ')
            ->set('dolorIntervencion', '  Reposo y aviso a enfermería  ')
            ->call('guardarDolor')->assertHasNoErrors()
            ->assertSet('drawerPaso', 'resident-summary');

        $this->assertDatabaseHas('valoraciones_dolor', [
            'cod_residente' => $this->residenteEstable->cod_residente,
            'cod_personal' => $this->personal->cod_personal,
            'intensidad' => 6,
            'ubicacion' => 'Rodilla derecha',
            'duracion' => '30 minutos',
            'desencadenante' => 'Al caminar',
            'intervencion' => 'Reposo y aviso a enfermería',
            'respuesta' => null,
            'estado' => 'VIGENTE',
        ]);
    }

    public function test_eva_cero_no_exige_intervencion_ni_reevaluacion(): void
    {
        $this->actingAs($this->enfermero);
        $this->formularioDolor()->set('dolorEva', '0')->call('guardarDolor')
            ->assertHasNoErrors();
        $this->assertDatabaseHas('valoraciones_dolor', ['intensidad' => 0, 'intervencion' => null]);
    }

    public function test_valoracion_rechaza_usuario_sin_permiso_y_residente_no_asignado(): void
    {
        $this->actingAs($this->enfermero);
        AsignacionResidenteJornada::query()->where('cod_residente', $this->residenteCritico->cod_residente)
            ->update(['estado' => 'FINALIZADA']);

        try {
            app(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class)
                ->registrarValoracionDolor($this->residenteCritico->cod_residente, [
                    'fecha_hora' => now()->format('Y-m-d\TH:i'), 'intensidad' => 5,
                ], $this->enfermero);
            $this->fail('Se aceptó un residente no asignado.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        \Spatie\Permission\Models\Role::findByName('ENFERMEROS')->revokePermissionTo('valoraciones_dolor.crear');
        $this->formularioDolor()->assertStatus(403);
    }

    private function formularioAlimentacion()
    {
        \App\Models\AsignacionPersonal::firstOrCreate(
            ['cod_asignacion_personal' => 'ASP_INGESTA_TEST'],
            [
                'cod_jornada' => 'JOR_MIS_PACIENTES',
                'cod_personal' => $this->personal->cod_personal,
                'cod_area' => $this->area->cod_area,
                'tipo_asignacion' => 'TURNO',
                'fecha_asignacion' => now(),
                'estado' => 'ACTIVA',
            ]
        );
        return Livewire::test(MisPacientes::class)
            ->call('seleccionarResidente', $this->residenteEstable->cod_residente)->assertStatus(200)
            ->call('mostrarSelectorRegistro')->assertStatus(200)
            ->call('abrirFormularioRegistro', 'alimentacion')->assertStatus(200);
    }

    public function test_alimentacion_rechaza_otro_sin_catalogo_y_tolerancia_invalida(): void
    {
        $this->actingAs($this->enfermero);
        $this->formularioAlimentacion()
            ->assertSee('Comida')
            ->assertDontSee('wire:model="ingestaEspecificar"')
            ->set('ingestaTipoComida', 'OTRO')
            ->call('guardarCuidado')->assertHasErrors('tipo_comida');

        $this->formularioAlimentacion()
            ->set('ingestaTipoComida', 'ALMUERZO')
            ->set('ingestaTolerancia', 'INVENTADA')
            ->call('guardarCuidado')->assertHasErrors('tolerancia');
        $this->assertDatabaseCount('registros_ingesta', 0);
    }

    public function test_alimentacion_rechaza_porcentaje_fuera_de_rango_y_no_numerico(): void
    {
        $this->actingAs($this->enfermero);
        foreach (['-1', '101', 'texto', '45.123'] as $porcentaje) {
            $this->formularioAlimentacion()
                ->set('ingestaTipoComida', 'ALMUERZO')
                ->set('ingestaPorcentaje', $porcentaje)
                ->call('guardarCuidado')->assertHasErrors('porcentaje_consumido');
        }
        $this->assertDatabaseCount('registros_ingesta', 0);
    }

    public function test_alimentacion_rechaza_volumen_negativo_y_no_numerico(): void
    {
        $this->actingAs($this->enfermero);
        foreach (['-1', 'texto'] as $volumen) {
            $this->formularioAlimentacion()
                ->set('ingestaTipoComida', 'ALMUERZO')
                ->set('ingestaCantidadMl', $volumen)
                ->call('guardarCuidado')->assertHasErrors('cantidad_ml');
        }
        $this->assertDatabaseCount('registros_hidratacion', 0);
    }

    public function test_alimentacion_guarda_ingesta_e_hidratacion_reales_y_limpia_formulario(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);
        $this->formularioAlimentacion()
            ->assertSee('Ingesta')
            ->assertSee('Asistencia')
            ->assertSee('Tolerancia y dificultades')
            ->set('ingestaTipoComida', 'ALMUERZO')
            ->set('ingestaPorcentaje', '87.50')
            ->set('ingestaCantidadMl', '220.25')
            ->set('ingestaTolerancia', 'BUENA')
            ->set('ingestaDificultadDeglucion', true)
            ->set('ingestaObservacion', '  Necesitó ayuda para comer.  ')
            ->call('guardarCuidado')->assertHasNoErrors()
            ->assertSet('drawerPaso', 'resident-summary')
            ->assertSet('ingestaTipoComida', '');

        $this->assertDatabaseHas('registros_ingesta', [
            'cod_residente' => $this->residenteEstable->cod_residente,
            'cod_personal' => $this->personal->cod_personal,
            'cod_jornada' => 'JOR_MIS_PACIENTES',
            'tipo_comida' => 'ALMUERZO',
            'porcentaje_consumido' => 87.50,
            'tolerancia' => 'BUENA',
            'dificultad_deglucion' => true,
            'observacion' => 'Necesitó ayuda para comer.',
            'estado' => 'VIGENTE',
        ]);
        $this->assertDatabaseHas('registros_hidratacion', [
            'cod_residente' => $this->residenteEstable->cod_residente,
            'cantidad_ml' => 220.25,
            'cod_jornada' => 'JOR_MIS_PACIENTES',
        ]);
    }

    public function test_alimentacion_sin_liquidos_no_crea_hidratacion(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);
        $this->formularioAlimentacion()
            ->set('ingestaTipoComida', 'CENA')
            ->set('ingestaPorcentaje', '100')
            ->call('guardarCuidado')->assertHasNoErrors();
        $this->assertDatabaseCount('registros_hidratacion', 0);
    }

    public function test_alimentacion_baja_ingesta_conserva_alerta_institucional(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);
        $this->formularioAlimentacion()
            ->set('ingestaTipoComida', 'DESAYUNO')
            ->set('ingestaPorcentaje', '49.50')
            ->call('guardarCuidado')->assertHasNoErrors();

        $this->assertDatabaseHas('registros_ingesta', [
            'cod_residente' => $this->residenteEstable->cod_residente,
            'porcentaje_consumido' => 49.50,
        ]);
        $this->assertDatabaseHas('alertas', [
            'cod_residente' => $this->residenteEstable->cod_residente,
            'tipo' => 'BAJA INGESTA',
        ]);
    }

    public function test_alimentacion_rechaza_residente_no_asignado_y_usuario_sin_permiso(): void
    {
        $this->actingAs($this->enfermero);
        AsignacionResidenteJornada::query()->where('cod_residente', $this->residenteCritico->cod_residente)
            ->update(['estado' => 'FINALIZADA']);

        try {
            app(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class)
                ->registrarAlimentacion($this->residenteCritico->cod_residente, [
                    'tipo_comida' => 'CENA', 'dificultad_deglucion' => false,
                ], $this->enfermero);
            $this->fail('Se aceptó un residente no asignado.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        \Spatie\Permission\Models\Role::findByName('ENFERMEROS')->revokePermissionTo('registros_ingesta.crear');
        Livewire::test(MisPacientes::class)
            ->call('seleccionarResidente', $this->residenteEstable->cod_residente)
            ->call('mostrarSelectorRegistro')
            ->call('abrirFormularioRegistro', 'alimentacion')
            ->assertStatus(403);
    }

    private function formularioEliminacion()
    {
        \App\Models\AsignacionPersonal::firstOrCreate(
            ['cod_asignacion_personal' => 'ASP_ELIM_TEST'],
            [
                'cod_jornada' => 'JOR_MIS_PACIENTES',
                'cod_personal' => $this->personal->cod_personal,
                'cod_area' => $this->area->cod_area,
                'tipo_asignacion' => 'TURNO',
                'fecha_asignacion' => now(),
                'estado' => 'ACTIVA',
            ]
        );

        return Livewire::test(MisPacientes::class)
            ->call('seleccionarResidente', $this->residenteEstable->cod_residente)
            ->call('mostrarSelectorRegistro')
            ->call('abrirFormularioRegistro', 'eliminacion')->assertStatus(200);
    }

    public function test_eliminacion_exige_tipo_y_rechaza_tipo_fuera_del_formulario(): void
    {
        $this->actingAs($this->enfermero);
        $this->formularioEliminacion()
            ->assertSee('Urinaria')
            ->assertSee('Intestinal')
            ->call('guardarCuidado')->assertHasErrors('tipo_eliminacion');

        $this->formularioEliminacion()
            ->set('elimTipo', 'AMBAS')
            ->call('guardarCuidado')->assertHasErrors('tipo_eliminacion');
        $this->assertDatabaseCount('registros_eliminacion', 0);
    }

    public function test_eliminacion_cambiar_tipo_limpia_campos_urinarios_e_intestinales(): void
    {
        $this->actingAs($this->enfermero);
        $formulario = $this->formularioEliminacion()
            ->set('elimTipo', 'URINARIA')
            ->set('elimCantidadUrinaria', '250')
            ->set('elimCaracteristicaUrinaria', 'Ámbar')
            ->set('elimContinenciaUrinaria', 'CONTINENTE')
            ->set('elimTipo', 'INTESTINAL')
            ->assertSet('elimCantidadUrinaria', '')
            ->assertSet('elimCaracteristicaUrinaria', '')
            ->assertSet('elimContinenciaUrinaria', '');

        $formulario->set('elimCantidadIntestinal', '1')
            ->set('elimCaracteristicaIntestinal', 'Blanda')
            ->set('elimContinenciaIntestinal', 'INCONTINENCIA_FECAL')
            ->set('elimTipo', 'URINARIA')
            ->assertSet('elimCantidadIntestinal', '')
            ->assertSet('elimCaracteristicaIntestinal', '')
            ->assertSet('elimContinenciaIntestinal', '');
    }

    public function test_eliminacion_no_persiste_campos_ocultos_aunque_se_manipulen(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);
        $this->formularioEliminacion()
            ->set('elimTipo', 'INTESTINAL')
            ->set('elimCantidadIntestinal', '2')
            ->set('elimCaracteristicaIntestinal', 'Blanda')
            ->set('elimCantidadUrinaria', '999')
            ->set('elimCaracteristicaUrinaria', 'Valor oculto')
            ->set('elimContinenciaUrinaria', 'INCONTINENCIA_URINARIA')
            ->call('guardarCuidado')->assertHasNoErrors();

        $this->assertDatabaseHas('registros_eliminacion', [
            'cod_residente' => $this->residenteEstable->cod_residente,
            'tipo_eliminacion' => 'INTESTINAL',
            'cantidad' => '2',
            'caracteristica' => 'Blanda',
            'continencia' => null,
        ]);
        $this->assertDatabaseMissing('registros_eliminacion', ['cantidad' => '999']);
    }

    public function test_eliminacion_rechaza_cantidad_negativa_y_continencia_invalida(): void
    {
        $this->actingAs($this->enfermero);
        $this->formularioEliminacion()
            ->set('elimTipo', 'URINARIA')
            ->set('elimCantidadUrinaria', '-1')
            ->call('guardarCuidado')->assertHasErrors('cantidad');

        $this->formularioEliminacion()
            ->set('elimTipo', 'INTESTINAL')
            ->set('elimContinenciaIntestinal', 'INCONTINENCIA_URINARIA')
            ->call('guardarCuidado')->assertHasErrors('continencia');
        $this->assertDatabaseCount('registros_eliminacion', 0);
    }

    public function test_eliminacion_guarda_campos_reales_con_autoria_y_observaciones(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);
        $this->formularioEliminacion()
            ->set('elimTipo', 'URINARIA')
            ->set('elimCantidadUrinaria', '250.5')
            ->set('elimCaracteristicaUrinaria', '  Color ámbar  ')
            ->set('elimContinenciaUrinaria', 'CONTINENTE')
            ->set('elimObservacion', '  Requirió asistencia.  ')
            ->call('guardarCuidado')->assertHasNoErrors()
            ->assertSet('drawerPaso', 'resident-summary');

        $this->assertDatabaseHas('registros_eliminacion', [
            'cod_residente' => $this->residenteEstable->cod_residente,
            'cod_personal' => $this->personal->cod_personal,
            'cod_jornada' => 'JOR_MIS_PACIENTES',
            'tipo_eliminacion' => 'URINARIA',
            'cantidad' => '250.5',
            'caracteristica' => 'Color ámbar',
            'continencia' => 'CONTINENTE',
            'observacion' => 'Requirió asistencia.',
            'estado' => 'VIGENTE',
        ]);
    }

    public function test_eliminacion_rechaza_residente_no_asignado_y_usuario_sin_permiso(): void
    {
        $this->actingAs($this->enfermero);
        AsignacionResidenteJornada::query()->where('cod_residente', $this->residenteCritico->cod_residente)
            ->update(['estado' => 'FINALIZADA']);

        try {
            app(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class)
                ->registrarEliminacion($this->residenteCritico->cod_residente, [
                    'tipo_eliminacion' => 'URINARIA',
                ], $this->enfermero);
            $this->fail('Se aceptó un residente no asignado.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        \Spatie\Permission\Models\Role::findByName('ENFERMEROS')->revokePermissionTo('registros_eliminacion.crear');
        Livewire::test(MisPacientes::class)
            ->call('seleccionarResidente', $this->residenteEstable->cod_residente)
            ->call('mostrarSelectorRegistro')
            ->call('abrirFormularioRegistro', 'eliminacion')
            ->assertStatus(403);
    }

    private function formularioMovilidad()
    {
        \App\Models\AsignacionPersonal::firstOrCreate(
            ['cod_asignacion_personal' => 'ASP_MOV_TEST'],
            [
                'cod_jornada' => 'JOR_MIS_PACIENTES',
                'cod_personal' => $this->personal->cod_personal,
                'cod_area' => $this->area->cod_area,
                'tipo_asignacion' => 'TURNO',
                'fecha_asignacion' => now(),
                'estado' => 'ACTIVA',
            ]
        );

        return Livewire::test(MisPacientes::class)
            ->call('seleccionarResidente', $this->residenteEstable->cod_residente)
            ->call('mostrarSelectorRegistro')
            ->call('abrirFormularioRegistro', 'movilidad')->assertStatus(200);
    }

    public function test_movilidad_exige_estado_observado_del_catalogo_real(): void
    {
        $this->actingAs($this->enfermero);
        $this->formularioMovilidad()
            ->assertSee('Actividad')
            ->assertSee('Movilidad observada')
            ->call('guardarCuidado')->assertHasErrors('marcha');

        $this->formularioMovilidad()
            ->set('movMarcha', 'CAMINATA_INVENTADA')
            ->call('guardarCuidado')->assertHasErrors('marcha');
        $this->assertDatabaseCount('registros_movilidad', 0);
    }

    public function test_movilidad_rechaza_catalogos_invalidos_de_traslado_ayuda_y_resultado(): void
    {
        $this->actingAs($this->enfermero);
        foreach ([
            ['movTraslado', 'traslado'],
            ['movTipoApoyo', 'tipo_apoyo'],
            ['movEquilibrio', 'equilibrio'],
            ['movFatiga', 'fatiga'],
            ['movRiesgoCaida', 'riesgo_caida'],
        ] as [$propiedad, $error]) {
            $this->formularioMovilidad()
                ->set('movMarcha', 'ASISTIDA')
                ->set($propiedad, 'FUERA_CATALOGO')
                ->call('guardarCuidado')->assertHasErrors($error);
        }
        $this->assertDatabaseCount('registros_movilidad', 0);
    }

    public function test_movilidad_no_muestra_campos_sin_persistencia_ni_crea_incidente(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);
        $this->formularioMovilidad()
            ->assertSee('Registrar incidente')
            ->assertDontSee('wire:model="movOrigen"')
            ->assertDontSee('wire:model="movDestino"')
            ->assertDontSee('wire:model="movDuracion"')
            ->set('movMarcha', 'ASISTIDA')
            ->set('movRiesgoCaida', 'ALTO')
            ->call('guardarCuidado')->assertHasNoErrors();
        $this->assertDatabaseCount('incidentes', 0);
    }

    public function test_movilidad_rechaza_campos_sin_persistencia_en_peticion_de_servicio(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);
        \App\Models\AsignacionPersonal::create([
            'cod_asignacion_personal' => 'ASP_MOV_DIRECT',
            'cod_jornada' => 'JOR_MIS_PACIENTES',
            'cod_personal' => $this->personal->cod_personal,
            'cod_area' => $this->area->cod_area,
            'tipo_asignacion' => 'TURNO',
            'fecha_asignacion' => now(),
            'estado' => 'ACTIVA',
        ]);

        try {
            app(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class)
                ->registrarMovilidad($this->residenteEstable->cod_residente, [
                'marcha' => 'INDEPENDIENTE',
                'origen' => 'CAMA',
                'destino' => 'BAÑO',
                'duracion_minutos' => -5,
                'dispositivo' => 'CATALOGO_INVENTADO',
                'incidencia' => true,
                ], $this->enfermero);
            $this->fail('Se aceptaron campos sin persistencia soportada.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('datos', $e->errors());
        }

        $this->assertDatabaseCount('registros_movilidad', 0);
        $this->assertDatabaseCount('incidentes', 0);
    }

    public function test_movilidad_guarda_campos_reales_y_autoria(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);
        $this->formularioMovilidad()
            ->set('movMarcha', 'ASISTIDA')
            ->set('movTraslado', 'AYUDA_UNA_PERSONA')
            ->set('movTipoApoyo', 'UNA_PERSONA')
            ->set('movEquilibrio', 'ESTABLE')
            ->set('movFatiga', 'LEVE')
            ->set('movRiesgoCaida', 'MEDIO')
            ->set('movObservacion', '  Usó andador con supervisión.  ')
            ->call('guardarCuidado')->assertHasNoErrors()
            ->assertSet('drawerPaso', 'resident-summary');

        $this->assertDatabaseHas('registros_movilidad', [
            'cod_residente' => $this->residenteEstable->cod_residente,
            'cod_personal' => $this->personal->cod_personal,
            'cod_jornada' => 'JOR_MIS_PACIENTES',
            'marcha' => 'ASISTIDA',
            'traslado' => 'AYUDA_UNA_PERSONA',
            'tipo_apoyo' => 'UNA_PERSONA',
            'equilibrio' => 'ESTABLE',
            'fatiga' => 'LEVE',
            'riesgo_caida' => 'MEDIO',
            'observacion' => 'Usó andador con supervisión.',
            'estado' => 'VIGENTE',
        ]);
    }

    public function test_movilidad_rechaza_residente_no_asignado_y_usuario_sin_permiso(): void
    {
        $this->actingAs($this->enfermero);
        AsignacionResidenteJornada::query()->where('cod_residente', $this->residenteCritico->cod_residente)
            ->update(['estado' => 'FINALIZADA']);

        try {
            app(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class)
                ->registrarMovilidad($this->residenteCritico->cod_residente, ['marcha' => 'ASISTIDA'], $this->enfermero);
            $this->fail('Se aceptó un residente no asignado.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        \Spatie\Permission\Models\Role::findByName('ENFERMEROS')->revokePermissionTo('registros_movilidad.crear');
        Livewire::test(MisPacientes::class)
            ->call('seleccionarResidente', $this->residenteEstable->cod_residente)
            ->call('mostrarSelectorRegistro')
            ->call('abrirFormularioRegistro', 'movilidad')
            ->assertStatus(403);
    }
}
