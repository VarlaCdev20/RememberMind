<?php

namespace Tests\Feature;

use App\Frontend\Livewire\Enfermeria\Cuidados\MisPacientes;
use App\Models\Admision;
use App\Models\Residente;
use App\Models\Alerta;
use App\Models\Alergia;
use App\Models\Area;
use App\Models\AsignacionResidenteJornada;
use App\Models\Atencion;
use App\Models\Cama;
use App\Models\Habitacion;
use App\Models\HorarioPrescripcion;
use App\Models\IndicacionClinica;
use App\Models\Jornada;
use App\Models\Medicamento;
use App\Models\OcupacionCama;
use App\Models\Personal;
use App\Models\PlanCuidado;
use App\Models\Prescripcion;
use App\Models\SignoVital;
use App\Models\Turno;
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

    private Turno $turno;

    private Residente $residenteEstable;

    private Residente $residenteCritico;

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

        $this->turno = Turno::create([
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
        $this->residenteEstable = Residente::factory()->create([
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
        $this->residenteCritico = Residente::factory()->create([
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
        $this->assertStringContainsString('Buscar entre mis residentes...', $vista->html());
        $this->assertStringContainsString('Con alertas (1)', $vista->html());
        $this->assertStringContainsString('1 alerta prioritaria', $vista->html());
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

    public function test_chips_de_alertas_filtran_residentes_asignados_y_reflejan_el_total(): void
    {
        $this->actingAs($this->enfermero);

        Livewire::test(MisPacientes::class)
            ->assertSee('Todos (2)')
            ->assertSee('Con alertas (1)')
            ->set('filtroRapido', 'CON_ALERTAS')
            ->assertSee('Luisa Morales')
            ->assertDontSee('Pedro Gomez')
            ->assertSee('Mostrando 1 de 2')
            ->call('limpiarFiltros')
            ->assertSet('filtroRapido', 'TODOS')
            ->assertSee('Pedro Gomez');
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
            ->assertSee('No encontramos residentes con estos filtros.')
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

    public function test_selector_conecta_controles_y_acciones_con_pantallas_existentes(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);
        $antes = SignoVital::query()->count();
        $vista = Livewire::test(MisPacientes::class)
            ->call('seleccionarResidente', $this->residenteEstable->cod_residente)
            ->call('mostrarSelectorRegistro');
        $acciones = $this->accionesDelSelector($vista->html());

        foreach (['cognicion' => 'Cognición', 'conducta' => 'Conducta', 'sueno' => 'Sueño', 'heridas' => 'Heridas / Curaciones'] as $clave => $label) {
            $opcion = \App\Backend\Modulos\Enfermeria\Servicios\NavegacionCuidadosService::opcion($clave);
            $url = route($opcion['route'], array_merge($opcion['parameters'] ?? [], ['adulto' => $this->residenteEstable->cod_residente, 'cuidado' => $clave]));
            $this->assertSame('a', $acciones[$label]['tag']);
            $this->assertStringContainsString('href="'.e($url).'"', $acciones[$label]['attributes']);
            $this->assertStringNotContainsString('wire:click', $acciones[$label]['attributes']);
        }
        $this->assertCount(16, $acciones);
        $this->assertSame('a', $acciones['Sistema experto']['tag']);
        foreach (['Seguimiento diario', 'Ejecución de cuidado', 'Registrar incidente', 'Administración de medicación'] as $label) {
            $this->assertSame('a', $acciones[$label]['tag']);
            $this->assertStringNotContainsString('disabled', $acciones[$label]['attributes']);
        }
        foreach (['Signos vitales', 'Dolor', 'Ingesta', 'Hidratación', 'Eliminación', 'Movilidad', 'Procedimiento'] as $label) {
            $this->assertSame('button', $acciones[$label]['tag']);
            $this->assertStringContainsString('wire:click', $acciones[$label]['attributes']);
            $this->assertStringNotContainsString('disabled', $acciones[$label]['attributes']);
        }
        $this->assertSame($antes, SignoVital::query()->count());
    }

    public function test_selector_critico_ofrece_atencion_sin_eliminar_el_bloqueo_clinico(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);
        $resultado = app(\App\Backend\Modulos\Clinica\Servicios\SignosVitalesService::class)
            ->registrarConEvaluacion($this->residenteEstable->cod_residente, ['frecuencia_cardiaca' => 135], $this->enfermero);
        $antes = \App\Models\ValoracionDolor::query()->count();
        $vista = Livewire::test(MisPacientes::class)
            ->call('seleccionarResidente', $this->residenteEstable->cod_residente)
            ->call('mostrarSelectorRegistro');
        $acciones = $this->accionesDelSelector($vista->html());
        $url = route('admin.enfermeria.alertas', ['alerta' => $resultado->alerta->cod_alerta, 'adulto' => $this->residenteEstable->cod_residente]);
        foreach (['Dolor', 'Cognición', 'Conducta', 'Ingesta', 'Hidratación', 'Eliminación', 'Movilidad'] as $label) {
            $this->assertSame('a', $acciones[$label]['tag']);
            $this->assertStringContainsString('href="'.e($url).'"', $acciones[$label]['attributes']);
            $this->assertStringContainsString('Atender alerta para habilitar este control', $acciones[$label]['content']);
        }
        $this->assertSame('button', $acciones['Signos vitales']['tag']);
        $this->assertStringContainsString('tipo=SUENO', $acciones['Sueño']['attributes']);
        $this->assertStringContainsString('seccion=HERIDAS', $acciones['Heridas / Curaciones']['attributes']);
        $vista->call('abrirFormularioRegistro', 'dolor')->assertHasErrors('continuidad_signos');
        $this->assertSame($antes, \App\Models\ValoracionDolor::query()->count());
        $this->assertDatabaseHas('alertas', ['cod_alerta' => $resultado->alerta->cod_alerta, 'estado' => 'ABIERTA']);
    }

    public function test_selector_no_expone_accesos_sin_los_permisos_del_destino(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->enfermero->roles->first()->revokePermissionTo(['registros_sueno.ver', 'atenciones.ver', 'ejecuciones_cuidado.ver', 'incidentes.crear',
            'registros_hidratacion.crear', 'registros_hidratacion.ver']);
        $this->actingAs($this->enfermero);
        $vista = Livewire::test(MisPacientes::class)
            ->call('seleccionarResidente', $this->residenteEstable->cod_residente)
            ->call('mostrarSelectorRegistro');
        $acciones = $this->accionesDelSelector($vista->html());
        foreach (['Sueño', 'Cognición', 'Conducta', 'Hidratación', 'Heridas / Curaciones', 'Seguimiento diario', 'Ejecución de cuidado', 'Registrar incidente'] as $label) {
            $this->assertArrayNotHasKey($label, $acciones);
        }
        $this->assertArrayHasKey('Dolor', $acciones);
    }

    private function accionesDelSelector(string $html): array
    {
        preg_match_all('/<(a|button)\b([^>]*\bclass="rm-quick-register__action [^"]*"[^>]*)>(.*?)<\/\1>/s', $html, $matches, PREG_SET_ORDER);
        $acciones = [];
        foreach ($matches as $match) {
            preg_match('/<span>(.*?)<\/span>/s', $match[3], $label);
            $acciones[html_entity_decode($label[1])] = ['tag' => $match[1], 'attributes' => $match[2], 'content' => $match[3]];
        }
        return $acciones;
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
            ->assertSee('Estado actual')
            ->assertSee('Información importante')
            ->assertSee('Seguimiento reciente')
            ->assertDontSee('Estado de salud')
            ->assertSee('Registrar')
            ->assertSee('Ver ficha');

        $html = $vista->html();
        $this->assertSame(1, substr_count($html, 'class="rm-drawer-backdrop"'));
        preg_match('/<header class="rm-resident-summary__identity">(.*?)<\/header>/s', $html, $identidad);
        $this->assertNotEmpty($identidad);
        $this->assertStringNotContainsString('<img', $identidad[1]);
        $this->assertSame(1, substr_count($html, 'class="rm-drawer-footer'));
        $this->assertSame(1, substr_count($html, 'aria-labelledby="resident-register-titulo"'));
        $this->assertSame(1, substr_count($html, 'aria-labelledby="clinical-operation-result-titulo"'));
        $this->assertStringContainsString(route('admin.enfermeria.pacientes.ficha', ['adulto' => $this->residenteEstable->cod_residente]), $html);
        $this->assertLessThan(strpos($html, 'Registrar</button>'), strpos($html, '<footer class="rm-drawer-footer'));

        $vista->call('mostrarSelectorRegistro')
            ->assertSet('mostrarPanelDetalle', false)
            ->assertSet('mostrarSelectorModal', true)
            ->assertSet('drawerPaso', 'register-selector')
            ->assertSee('Registrar para Pedro Gomez Paredes')
            ->assertSee('Selecciona qué deseas registrar')
            ->assertSee('Signos vitales')
            ->assertSee('Dolor')
            ->assertSee('Ingesta')
            ->assertSee('Eliminación')
            ->assertSee('Movilidad')
            ->assertSee('Administración de medicación')
            ->assertSee('Procedimiento')
            ->assertSet('tieneMedicacionProgramadaPendiente', false)
            ->assertSee('Sin dosis pendiente');

        $this->assertStringContainsString('class="rm-modal-panel__drag-handle"', $vista->html());
        $this->assertStringContainsString('role="dialog" aria-modal="true" aria-labelledby="resident-register-titulo"', $vista->html());
        $this->assertStringContainsString('x-trap.noscroll="show"', $vista->html());
        $this->assertStringContainsString('x-on:keydown.escape.window="if (show', $vista->html());
        $this->assertStringContainsString('$nextTick(() => requestAnimationFrame', $vista->html());
        $this->assertSame(3, substr_count($vista->html(), 'class="rm-quick-register__grid'));
        $this->assertTrue(strpos($vista->html(), "abrirFormularioRegistro('signos')") < strpos($vista->html(), "abrirFormularioRegistro('dolor')"));
        $this->assertStringNotContainsString('Sin formulario directo aquí', $vista->html());
        $this->assertStringNotContainsString('href="#"', $vista->html());
        $this->assertSame(1, substr_count($vista->html(), 'class="rm-modal-footer'));

        $vista->call('cerrarSelectorRegistro')
            ->assertSet('mostrarSelectorModal', false)
            ->assertSet('mostrarPanelDetalle', true)
            ->assertSet('drawerPaso', 'resident-summary');
    }

    public function test_resumen_usa_alergias_indicaciones_y_seguimiento_reales_con_sus_permisos(): void
    {
        $atencion = Atencion::where('cod_residente', $this->residenteEstable->cod_residente)->firstOrFail();
        Alergia::create([
            'cod_alergia' => 'ALE_RESUMEN_01',
            'cod_residente' => $this->residenteEstable->cod_residente,
            'cod_personal' => $this->personal->cod_personal,
            'sustancia' => 'Penicilina',
            'fecha_hora' => now(),
            'estado' => 'ACTIVA',
        ]);
        IndicacionClinica::create([
            'cod_indicacion' => 'IND_RESUMEN_01',
            'cod_residente' => $this->residenteEstable->cod_residente,
            'cod_atencion' => $atencion->cod_atencion,
            'cod_personal' => $this->personal->cod_personal,
            'tipo_indicacion' => 'CUIDADO',
            'descripcion' => 'Registrar tolerancia alimentaria durante el turno.',
            'fecha_hora' => now(),
            'estado' => 'ACTIVA',
        ]);
        $this->actingAs($this->enfermero);

        Livewire::test(MisPacientes::class)
            ->call('seleccionarResidente', $this->residenteEstable->cod_residente)
            ->assertSee('Penicilina')
            ->assertSee('Registrar tolerancia alimentaria durante el turno.')
            ->assertSee('Control de signos vitales');

        \Spatie\Permission\Models\Role::findByName('ENFERMEROS')->revokePermissionTo('alergias.ver', 'indicaciones_clinicas.ver');
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($this->enfermero->fresh());
        Livewire::test(MisPacientes::class)
            ->call('seleccionarResidente', $this->residenteEstable->cod_residente)
            ->assertDontSee('Penicilina');
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
            ->assertSee('Selecciona qué deseas registrar')
            ->assertDontSee('Resumen clínico')
            ->call('abrirFormularioRegistro', 'signos')
            ->assertSet('drawerPaso', 'register-form')
            ->assertSet('mostrarSelectorModal', true)
            ->assertSet('mostrarPanelDetalle', false)
            ->assertSee('Presión arterial')
            ->assertSee('Confirmar y registrar')
            ->assertSee('Volver al selector de registros')
            ->call('volverPanelDetalle')
            ->assertSet('drawerPaso', 'register-selector')
            ->assertSet('mostrarSelectorModal', true)
            ->assertSet('mostrarPanelDetalle', false)
            ->call('abrirFormularioRegistro', 'signos')
            ->set('signoSis', '120')
            ->call('volverPanelDetalle')
            ->assertSet('confirmarDescarte', true)
            ->assertSee('¿Salir sin guardar?')
            ->assertSee('El historial guardado permanecerá intacto.')
            ->assertSee('Seguir editando')
            ->assertSee('Salir sin guardar')
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

    public function test_descarte_repetido_no_lanza_409_ni_cambia_el_destino(): void
    {
        $this->actingAs($this->enfermero);
        $signosAntes = SignoVital::query()->count();
        $alertasAntes = Alerta::query()->count();

        foreach (['volverPanelDetalle' => 'register-selector', 'cerrarSelectorRegistro' => 'resident-summary'] as $salida => $destino) {
            $formulario = $this->formularioSignos()->set('signoFC', '75')
                ->call($salida)->assertSet('confirmarDescarte', true)
                ->call('descartarCambios')->assertSet('confirmarDescarte', false)
                ->assertSet('drawerPaso', $destino);

            $formulario->call('descartarCambios')->assertStatus(200)
                ->assertSet('drawerPaso', $destino)->assertSet('signoFC', '');
        }

        $this->assertSame($signosAntes, SignoVital::query()->count());
        $this->assertSame($alertasAntes, Alerta::query()->count());
    }

    public function test_descarte_atrasado_tras_seguir_editando_conserva_la_lectura_critica(): void
    {
        $this->actingAs($this->enfermero);
        $signosAntes = SignoVital::query()->count();
        $alertasAntes = Alerta::query()->count();

        $formulario = $this->formularioSignos()->set('signoFC', '135')
            ->call('cerrarSelectorRegistro')->assertSet('confirmarDescarte', true)
            ->call('cancelarDescarte')->assertSet('confirmarDescarte', false);

        $formulario->call('descartarCambios')->assertStatus(200)
            ->assertSet('mostrarSelectorModal', true)
            ->assertSet('drawerPaso', 'register-form')->assertSet('signoFC', '135');

        $this->assertSame($signosAntes, SignoVital::query()->count());
        $this->assertSame($alertasAntes, Alerta::query()->count());
    }

    public function test_selector_muestra_el_residente_elegido_y_solo_sus_alertas_reales(): void
    {
        $this->residenteCritico->update(['foto' => 'residentes/fotos/luisa.jpg']);
        $this->actingAs($this->enfermero);

        $vista = Livewire::test(MisPacientes::class)
            ->call('seleccionarResidente', $this->residenteCritico->cod_residente)
            ->call('mostrarSelectorRegistro')
            ->assertSet('residente', $this->residenteCritico->cod_residente)
            ->assertSee('Registrar para Luisa Morales Rios');

        $this->assertStringNotContainsString('rm-resident-directory__register-avatar', $vista->html());
        $this->assertStringNotContainsString('Registrar para Pedro Gomez Paredes', $vista->html());

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
            'fecha_hora_prescripcion' => today()->subDay()->setTime(7, 0),
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

        Carbon::setTestNow(today()->setTime(7, 0));
        try {
            Livewire::test(MisPacientes::class)->assertSee('Medicamento · Medicamento de prueba');
        } finally {
            Carbon::setTestNow();
        }

        Livewire::test(MisPacientes::class)
            ->call('seleccionarResidente', $this->residenteEstable->cod_residente)
            ->call('mostrarSelectorRegistro')
            ->assertSet('tieneMedicacionProgramadaPendiente', true)
            ->assertSet('cantidadMedicacionProgramadaPendiente', 1)
            ->assertSee('Administración de medicación')
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
        $this->assertStringContainsString('width: min(var(--rm-modal-xl,900px), calc(100vw - 32px))', $css);
        $this->assertStringContainsString('max-height: 82dvh', $css);
        $this->assertStringContainsString('grid-template-columns: repeat(4, minmax(0, 1fr))', $css);
        $this->assertStringContainsString('@media (max-width: 480px)', $css);
        $this->assertStringContainsString('width: calc(100vw - 16px)', $css);
        $this->assertStringContainsString('grid-template-columns: repeat(2, minmax(0, 1fr))', $css);
        $this->assertStringContainsString('min-height: 76px', $css);
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

    public function test_no_se_puede_guardar_signos_fuera_del_formulario_de_registro(): void
    {
        $this->actingAs($this->enfermero);
        $antes = SignoVital::query()->count();

        Livewire::test(MisPacientes::class)
            ->set('modalCodResidente', $this->residenteEstable->cod_residente)
            ->set('signoFC', '135')
            ->call('guardarSignos')
            ->assertStatus(403);

        $this->assertSame($antes, SignoVital::query()->count());
    }

    public function test_el_menu_del_residente_abre_el_formulario_de_control_completo(): void
    {
        $this->actingAs($this->enfermero);

        Livewire::test(MisPacientes::class)
            ->call('abrirControlDesdeMenu', $this->residenteEstable->cod_residente)
            ->assertSet('drawerPaso', 'register-form')
            ->assertSet('registroTipo', 'signos')
            ->assertSet('mostrarSelectorModal', true)
            ->assertSee('Interpretación y acción');
    }

    public function test_entrada_anterior_de_signos_tambien_genera_alerta_critica_atomica(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);

        $signo = app(\App\Backend\Modulos\Clinica\Servicios\SignosVitalesService::class)
            ->registrar($this->residenteEstable->cod_residente, ['frecuencia_cardiaca' => 135], $this->enfermero);

        $this->assertDatabaseHas('alertas', [
            'cod_residente' => $this->residenteEstable->cod_residente,
            'cod_registro' => $signo->cod_signo,
            'modulo' => 'SIGNOS',
            'estado' => 'ABIERTA',
        ]);
    }

    public function test_servicio_de_signos_rechaza_atribuir_registro_a_otro_usuario(): void
    {
        $this->actingAs($this->enfermero);
        $otroUsuario = User::factory()->create(['estado' => 'ACTIVO']);
        $antes = SignoVital::query()->count();

        try {
            app(\App\Backend\Modulos\Clinica\Servicios\SignosVitalesService::class)
                ->registrar($this->residenteEstable->cod_residente, ['frecuencia_cardiaca' => 72], $otroUsuario);
            $this->fail('Se permitió atribuir el registro a otro usuario.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }

        $this->assertSame($antes, SignoVital::query()->count());
    }

    public function test_guardar_signos_desde_el_modal_actualiza_el_resumen_sin_abrir_otro_panel(): void
    {
        // El registro inicial del fixture es de las 08:30; el nuevo debe ser posterior
        // incluso cuando la suite se ejecuta poco después de medianoche.
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);

        $formulario = Livewire::test(MisPacientes::class)
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
            ->assertSet('drawerPaso', 'register-result')
            ->assertSet('mostrarSelectorModal', true)
            ->assertSee('Registro guardado correctamente')
            ->assertSee('Los datos ingresados se validaron y quedaron guardados')
            ->assertSee('rm-resident-directory__register-modal--signos-result', false)
            ->assertSee('124/78 mmHg')
            ->assertSee('Ver registro');

        $formulario->call('volverResidenteDesdeSignos')
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
            ->assertSet('signosResultadoRegistro', [])
            ->assertSee('Revisa el valor ingresado. Esta medición requiere un número entero.');
    }

    public function test_formulario_signos_muestra_contexto_real_historial_y_errores_accesibles(): void
    {
        $this->actingAs($this->enfermero);

        $formulario = $this->formularioSignos()
            ->assertDontSee('Sin objetivo individual configurado.')
            ->assertSee('Interpretación y acción')
            ->assertSee('Ver gráfica')
            ->assertSee('Mover gráfica: arrastra o usa las flechas; Inicio restablece la ventana')
            ->assertSee('Ajustar tamaño: arrastra o usa las flechas')
            ->assertSee('Ajustar borde izquierdo de la gráfica')
            ->assertSee('Ajustar borde derecho de la gráfica')
            ->assertSee('Ajustar borde superior de la gráfica')
            ->assertSee('Ajustar borde inferior de la gráfica')
            ->assertSee('aria-labelledby="signos-grafica-title"', false)
            ->assertSee('popover="manual"', false)
            ->assertSee('aria-modal="false"', false)
            ->assertSee('Registro clínico')
            ->assertDontSee('type="date"')
            ->assertDontSee('type="time"')
            ->assertSet('signosIntentoGuardar', false);

        $this->assertStringContainsString('aria-describedby="signos-sat-error"', $formulario->html());
        $this->assertStringNotContainsString('Revisa algunos datos', $formulario->html());
        $this->assertStringNotContainsString('Rango objetivo 92', $formulario->html());
        $this->assertStringNotContainsString('Revisar mediciones', $formulario->html());
        $this->assertStringNotContainsString('bpm', $formulario->html());
        $this->assertStringContainsString('rmSignosRegistro(', $formulario->html());

        $formulario->set('signoSat', '101')->call('guardarSignos')
            ->assertHasErrors('saturacion_oxigeno')
            ->assertSee('No se puede continuar')
            ->assertSee('La saturación de oxígeno no puede superar 100 %.')
            ->assertSet('mostrarSelectorModal', true);
        $this->assertStringContainsString('aria-describedby="signos-sat-error signos-sat-server-error"', $formulario->html());
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

    public function test_formulario_vacio_no_crea_registro_clinico(): void
    {
        $this->actingAs($this->enfermero);
        $antes = SignoVital::query()->count();

        $this->formularioSignos()->call('guardarSignos')
            ->assertHasErrors('mediciones')
            ->assertSet('drawerPaso', 'register-form')
            ->assertSee('Registra al menos una medición antes de confirmar.');

        $this->assertSame($antes, SignoVital::query()->count());
    }

    public function test_puede_registrar_solo_pulso_y_conserva_el_historial_previo(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);
        $anteriores = SignoVital::query()->where('cod_residente', $this->residenteEstable->cod_residente)->count();

        $this->formularioSignos()->set('signoFC', '72')->call('guardarSignos')
            ->assertHasNoErrors()->assertSet('signosConfirmacionPendiente', false);

        $this->assertSame($anteriores + 1, SignoVital::query()
            ->where('cod_residente', $this->residenteEstable->cod_residente)->count());
        $this->assertDatabaseHas('signos_vitales', [
            'cod_residente' => $this->residenteEstable->cod_residente,
            'frecuencia_cardiaca' => 72,
            'temperatura' => null,
        ]);
    }

    public function test_temperatura_invalida_bloquea_pero_lectura_clinicamente_alta_puede_registrarse(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);

        $this->formularioSignos()->set('signoTemp', '6')->call('guardarSignos')
            ->assertHasErrors('temperatura')
            ->assertSee('Revisa el valor ingresado. Está fuera del intervalo admitido');

        $formulario = $this->formularioSignos()->set('signoTemp', '39.4')
            ->assertSet('signosIntentoGuardar', false);
        $formulario->call('guardarSignos')->assertHasNoErrors();
        if ($formulario->get('signosConfirmacionPendiente')) {
            $formulario->call('guardarSignos')->assertHasNoErrors();
            if ($formulario->get('signosPasoConfirmacion') === 'final') {
                $formulario->call('guardarSignos')->assertHasNoErrors();
            }
        }
        $this->assertDatabaseHas('signos_vitales', [
            'cod_residente' => $this->residenteEstable->cod_residente,
            'temperatura' => 39.4,
        ]);
    }

    public function test_advertencia_de_pulso_no_exige_segunda_confirmacion(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);

        $this->formularioSignos()->set('signoFC', '96')->call('guardarSignos')
            ->assertHasNoErrors()->assertSet('signosConfirmacionPendiente', false)
            ->assertSet('drawerPaso', 'register-result');
    }

    public function test_glucemia_aislada_alta_sugiere_revision_sin_alerta_automatica(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);
        $alertasAntes = Alerta::query()->count();

        $this->formularioSignos()->set('signoGlucosa', '251')
            ->assertSee('Revisar el contexto; esta lectura aislada no genera alerta automática.')
            ->call('guardarSignos')->assertHasNoErrors()
            ->assertSet('signosConfirmacionPendiente', false);

        $this->assertSame($alertasAntes, Alerta::query()->count());
        $this->assertDatabaseHas('signos_vitales', [
            'cod_residente' => $this->residenteEstable->cod_residente,
            'glucemia' => 251,
        ]);
    }

    public function test_presion_atipica_pide_confirmacion_contextual_sin_borrar_la_lectura(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);

        $formulario = $this->formularioSignos()->set('signoSis', '80')->set('signoDia', '90')
            ->call('guardarSignos')
            ->assertSet('signosConfirmacionPendiente', true)
            ->assertSee('Presión arterial atípica');
        $this->assertDatabaseMissing('signos_vitales', [
            'cod_residente' => $this->residenteEstable->cod_residente,
            'presion_sistolica' => 80,
            'presion_diastolica' => 90,
        ]);

        $formulario->call('guardarSignos')->assertHasNoErrors();
        $this->assertDatabaseHas('signos_vitales', [
            'cod_residente' => $this->residenteEstable->cod_residente,
            'presion_sistolica' => 80,
            'presion_diastolica' => 90,
        ]);
    }

    public function test_lectura_critica_exige_dialogo_de_confirmacion_antes_de_guardar_y_generar_alerta(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);
        $signosAntes = SignoVital::query()->count();
        $alertasAntes = Alerta::query()->count();

        $formulario = $this->formularioSignos()->set('signoFC', '135')
            ->assertSee('data-tone="danger"', false)
            ->assertSee('1 MEDICIÓN CRÍTICA')
            ->assertSee('Revisar lectura crítica')
            ->call('guardarSignos')
            ->assertSet('signosConfirmacionPendiente', true)
            ->assertSee('Revisión de medición crítica')
            ->assertSet('signosPasoConfirmacion', 'revision')
            ->assertSet('drawerPaso', 'register-form');

        $this->assertSame($signosAntes, SignoVital::query()->count());
        $this->assertSame($alertasAntes, Alerta::query()->count());

        $formulario->set('signoFC', '136')
            ->assertSet('signosConfirmacionPendiente', false)
            ->call('guardarSignos')
            ->assertSet('signosConfirmacionPendiente', true);

        $formulario->call('guardarSignos')
            ->assertSet('signosPasoConfirmacion', 'final');
        $this->assertSame($signosAntes, SignoVital::query()->count());
        $this->assertSame($alertasAntes, Alerta::query()->count());

        $formulario->call('guardarSignos')
            ->assertHasNoErrors()
            ->assertSet('drawerPaso', 'register-result')
            ->assertSee('Registro guardado · Atención requerida')
            ->assertSet('signosResultadoRegistro.hay_critico', true)
            ->assertSee('Alerta crítica generada');

        $this->assertSame($signosAntes + 1, SignoVital::query()->count());
        $this->assertSame($alertasAntes + 1, Alerta::query()->count());
        $alerta = Alerta::findOrFail($formulario->get('signosResultadoRegistro')['cod_alerta']);
        $this->assertSame('ABIERTA', $alerta->estado);
        $this->assertStringContainsString('136 lpm', $alerta->descripcion);
        $this->assertStringContainsString('Umbral utilizado:', $alerta->descripcion);
        $this->assertStringContainsString('Recomendación:', $alerta->descripcion);
        $this->assertDatabaseHas('eventos_alerta', [
            'cod_alerta' => $alerta->cod_alerta,
            'tipo_evento' => 'CREACION',
        ]);
        $this->assertSame($alerta->cod_alerta, $formulario->get('signosResultadoRegistro')['cod_alerta']);
        $this->get(route('admin.enfermeria.alertas', ['adulto' => $this->residenteEstable->cod_residente, 'alerta' => $alerta->cod_alerta]))
            ->assertOk()
            ->assertSee('Registro que originó la alerta')
            ->assertSee('136 lpm');
        $this->travelTo(today()->setTime(10, 10));
        SignoVital::create([
            'cod_residente' => $this->residenteEstable->cod_residente,
            'cod_personal' => $this->personal->cod_personal,
            'fecha_hora' => now(),
            'frecuencia_cardiaca' => 70,
            'estado' => 'VIGENTE',
        ]);
        $this->assertGreaterThan(1, SignoVital::query()->where('cod_residente', $this->residenteEstable->cod_residente)
            ->whereIn('estado', ['VIGENTE', 'ACTIVO'])->where('fecha_hora', '>=', $alerta->fecha_hora)->count());
        $this->get(route('admin.enfermeria.alertas', ['adulto' => $this->residenteEstable->cod_residente, 'alerta' => $alerta->cod_alerta]))
            ->assertOk()
            ->assertSee('Evolución desde la alerta')
            ->assertSee('70 lpm');
        Livewire::test(\App\Frontend\Livewire\Compartido\Alertas\AlertasPanel::class)
            ->call('verDetalle', $alerta->cod_alerta)
            ->set('accion', 'Se revisó al residente y se realizó una nueva medición.')
            ->call('guardarAccion')->assertHasNoErrors()
            ->assertSee('Registrar nueva medición');
        $this->assertSame('EN_ATENCION', $alerta->fresh()->estado);
        $formulario->call('guardarSignos')->assertStatus(409);
        $this->assertSame($alertasAntes + 1, Alerta::query()->count());
    }

    public function test_lectura_critica_se_descarta_solo_tras_confirmacion_sin_persistir(): void
    {
        $this->actingAs($this->enfermero);
        $signosAntes = SignoVital::query()->count();
        $alertasAntes = Alerta::query()->count();

        $formulario = $this->formularioSignos()->set('signoFC', '135')
            ->call('cerrarSelectorRegistro')
            ->assertSet('confirmarDescarte', true)
            ->assertSee('¿Salir sin guardar?')->assertSee('Salir sin guardar');

        $formulario->call('cancelarDescarte')->assertSet('signoFC', '135')
            ->call('descartarCambios')->assertSet('mostrarSelectorModal', true)
            ->assertSet('signoFC', '135')->call('cerrarSelectorRegistro');
        $formulario->call('descartarCambios')
            ->assertHasNoErrors()->assertSet('mostrarSelectorModal', false);
        $this->assertSame($signosAntes, SignoVital::query()->count());
        $this->assertSame($alertasAntes, Alerta::query()->count());

        $this->assertSame($signosAntes, SignoVital::query()->count());
        $this->assertSame($alertasAntes, Alerta::query()->count());
    }

    public function test_enlace_desde_alerta_abre_nuevo_formulario_para_el_mismo_residente(): void
    {
        $this->actingAs($this->enfermero);

        $this->get(route('admin.enfermeria.pacientes', [
            'residente' => $this->residenteEstable->cod_residente,
            'registrar' => 'signos',
        ]))->assertOk()->assertSee('Interpretación y acción');
    }

    public function test_enfermeria_no_puede_abrir_alerta_de_residente_fuera_de_su_asignacion(): void
    {
        $this->actingAs($this->enfermero);
        $externo = Residente::factory()->create(['cod_residente' => 'RES_SIN_ASIGNACION']);
        $alerta = Alerta::create([
            'cod_residente' => $externo->cod_residente,
            'tipo' => 'SIGNOS_VITALES_CRITICOS',
            'modulo' => 'SIGNOS',
            'descripcion' => 'Lectura crítica de otro residente.',
            'estado' => 'ABIERTA',
        ]);

        $this->get(route('admin.enfermeria.alertas', ['alerta' => $alerta->cod_alerta]))->assertForbidden();
        Livewire::test(\App\Frontend\Livewire\Compartido\Alertas\AlertasPanel::class)
            ->call('verDetalle', $alerta->cod_alerta)->assertForbidden();
    }

    public function test_saturacion_fuera_de_0_a_100_y_numericos_invalidos_se_rechazan(): void
    {
        $this->actingAs($this->enfermero);

        foreach ([['101', 'La saturación de oxígeno no puede superar 100 %.'], ['-1', 'La saturación de oxígeno debe ser mayor que 0 %.'], ['0', 'La saturación de oxígeno debe ser mayor que 0 %.']] as [$valor, $mensaje]) {
            $this->formularioSignos()->set('signoSat', $valor)->call('guardarSignos')
                ->assertHasErrors('saturacion_oxigeno')
                ->assertSee($mensaje);
        }

        $this->formularioSignos()->set('signoFC', 'abc')->call('guardarSignos')
            ->assertHasErrors('frecuencia_cardiaca')
            ->assertSee('Revisa el valor ingresado. Esta medición requiere un número entero.');
        $this->formularioSignos()->set('signoGlucosa', '-0.1')->call('guardarSignos')
            ->assertHasErrors('glucemia');
        $this->formularioSignos()->set('signoTemp', '46')->call('guardarSignos')
            ->assertHasErrors('temperatura');
        $this->formularioSignos()->set('signoFC', '301')->call('guardarSignos')
            ->assertHasErrors('frecuencia_cardiaca');
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
            ->set('signoSat', '92')->set('signoGlucosa', '90.25')
            ->set('signoObs', '  '.str_repeat('x', 5000).'  ')
            ->call('guardarSignos')->assertHasNoErrors();

        $signo = SignoVital::query()->latest('fecha_hora')->firstOrFail();
        $this->assertSame($this->personal->cod_personal, $signo->cod_personal);
        $this->assertSame('JOR_MIS_PACIENTES', $signo->cod_jornada);
        $this->assertSame(36.5, $signo->temperatura);
        $this->assertSame(92.0, $signo->saturacion_oxigeno);
        $this->assertSame(90.25, $signo->glucemia);
        $this->assertSame(str_repeat('x', 5000), $signo->observacion);
    }

    public function test_la_evaluacion_previa_respeta_la_precision_y_limpia_el_error_al_corregir(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);

        $formulario = $this->formularioSignos()->set('signoTemp', '36.55')
            ->assertSet('signosEvaluacion.resultados', [])
            ->assertSet('signosEvaluacion.severidad_global', null)
            ->call('guardarSignos')
            ->assertHasErrors('temperatura');

        $formulario->set('signoTemp', '36.5')
            ->assertHasNoErrors('temperatura')
            ->assertSee('data-tone="success"', false);
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

    private function crearDosisProgramada(Residente $residente, string $codigo = 'A', string $estado = 'ACTIVA'): array
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

        foreach (['', '-1', '11', '2.5'] as $eva) {
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
        $formulario->assertSee('Intensidad EVA')
            ->assertDontSee('wire:model="dolorEvaPosterior"');

        $servicio = app(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class);
        $base = [
            'intensidad' => 4,
        ];
        foreach ([
            ['tipo_dolor' => 'TIPO_INVENTADO'],
            ['eva_posterior' => 2, 'hora_reevaluacion' => now()->subHour()->format('Y-m-d\TH:i')],
            ['observacion' => 'Campo no soportado'],
            ['respuesta' => 'No permitida en este flujo'],
            ['cod_personal' => 'PER_AJENO'],
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
            ->assertSet('dolorFechaHora', now()->format('Y-m-d\TH:i:s'))
            ->assertSee('Intensidad EVA')
            ->set('dolorEva', '6')
            ->set('dolorUbicacion', '  Rodilla derecha  ')
            ->set('dolorDuracionValor', '30')
            ->set('dolorDuracionUnidad', 'minutos')
            ->set('dolorDesencadenante', '  Al caminar  ')
            ->set('dolorIntervencion', '  Reposo y aviso a enfermería  ')
            ->call('guardarDolor')->assertHasNoErrors()
            ->assertSet('drawerPaso', 'register-result')
            ->assertSee('Valoración de dolor registrada')
            ->assertSet('dolorResultado.mediciones.0.valor', '6 / 10')
            ->call('volverResidenteDesdeDolor')->assertSet('drawerPaso', 'resident-summary');

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

    public function test_dolor_error_conserva_captura_y_no_emite_exito(): void
    {
        $this->actingAs($this->enfermero);
        $this->formularioDolor()
            ->set('dolorEva', '5')->set('dolorUbicacion', 'Rodilla derecha')
            ->set('dolorDuracionValor', '30')->set('dolorDuracionUnidad', '')
            ->call('guardarDolor')->assertHasErrors(['duracion_unidad'])
            ->assertSet('dolorEva', '5')->assertSet('dolorUbicacion', 'Rodilla derecha')
            ->assertSeeHtml('id="dolor-unidad-error"')
            ->assertSeeHtml('aria-invalid="true"')
            ->assertNotDispatched('dolor-registrado');
        $this->assertDatabaseCount('valoraciones_dolor', 0);
    }

    public function test_dolor_sucesivo_conserva_valoracion_anterior(): void
    {
        $this->actingAs($this->enfermero);
        $this->formularioDolor()->set('dolorEva', '5')->set('dolorUbicacion', 'Rodilla derecha')
            ->call('guardarDolor')->assertHasNoErrors()->assertDispatched('dolor-registrado');
        $this->formularioDolor()->set('dolorEva', '2')->set('dolorUbicacion', 'Rodilla derecha')
            ->call('guardarDolor')->assertHasNoErrors()->assertDispatched('dolor-registrado');
        $this->assertDatabaseCount('valoraciones_dolor', 2);
        $this->assertDatabaseHas('valoraciones_dolor', ['intensidad' => 5, 'estado' => 'VIGENTE']);
        $this->assertDatabaseHas('valoraciones_dolor', ['intensidad' => 2, 'estado' => 'VIGENTE']);
    }

    public function test_dolor_fecha_del_cliente_es_rechazada_y_el_registro_usa_el_reloj_del_servidor(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);
        $servicio = app(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class);
        try {
            $servicio->registrarValoracionDolor($this->residenteEstable->cod_residente,
                ['intensidad' => 6, 'fecha_hora' => '2000-01-01T00:00'], $this->enfermero);
            $this->fail('Se aceptó una fecha suministrada por el cliente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('fecha_hora', $e->errors());
        }
        $this->assertDatabaseCount('valoraciones_dolor', 0);
        $formulario = $this->formularioDolor();
        $this->travel(2)->minutes();
        $formulario->set('dolorEva', '6')->call('guardarDolor')->assertHasNoErrors();
        $registro = \App\Models\ValoracionDolor::firstOrFail();
        $this->assertTrue($registro->fecha_hora->equalTo(now()));
        $this->assertSame($this->personal->cod_personal, $registro->cod_personal);
        $formulario->call('guardarDolor')->assertStatus(403);
        $this->assertDatabaseCount('valoraciones_dolor', 1);
    }

    public function test_dolor_fecha_livewire_esta_bloqueada(): void
    {
        $this->actingAs($this->enfermero);
        $this->expectException(\Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException::class);
        $this->formularioDolor()->set('dolorFechaHora', '2000-01-01T00:00');
    }

    public function test_dolor_historial_plano_del_residente_vigente_limitado_y_con_permiso(): void
    {
        $this->actingAs($this->enfermero);
        foreach (range(1, 9) as $index) {
            \App\Models\ValoracionDolor::create([
                'cod_valoracion_dolor' => 'VD_DTO_'.$index,
                'cod_residente' => $this->residenteEstable->cod_residente,
                'cod_personal' => $this->personal->cod_personal,
                'fecha_hora' => now()->subHours($index), 'intensidad' => $index,
                'ubicacion' => 'Rodilla derecha', 'estado' => 'VIGENTE',
            ]);
        }
        \App\Models\ValoracionDolor::create([
            'cod_valoracion_dolor' => 'VD_DTO_ANULADO', 'cod_residente' => $this->residenteEstable->cod_residente,
            'cod_personal' => $this->personal->cod_personal, 'fecha_hora' => now(),
            'intensidad' => 10, 'estado' => 'ANULADO',
        ]);
        $vista = $this->formularioDolor();
        $historial = $vista->get('dolorHistorial');
        $this->assertCount(7, $historial);
        $this->assertSame(1, $historial[0]['intensidad']);
        $this->assertSame(['codigo', 'origen', 'fecha_hora', 'fecha', 'intensidad', 'ubicacion', 'duracion', 'frecuencia', 'desencadenante', 'factores_alivio', 'intervencion', 'respuesta'], array_keys($historial[0]));
        \Spatie\Permission\Models\Role::findByName('ENFERMEROS')->revokePermissionTo('valoraciones_dolor.ver');
        $this->enfermero->unsetRelation('roles')->unsetRelation('permissions');
        $this->formularioDolor()->assertSet('dolorHistorial', [])->assertDontSee('Ver evolución');
    }

    public function test_dolor_duracion_unidad_sola_y_longitud_total_invalidas(): void
    {
        $this->actingAs($this->enfermero);
        $this->formularioDolor()->set('dolorEva', '4')->set('dolorDuracionUnidad', 'minutos')
            ->call('guardarDolor')->assertHasErrors('duracion_valor');
        $this->formularioDolor()->set('dolorEva', '4')->set('dolorDuracionValor', '-1')
            ->set('dolorDuracionUnidad', 'minutos')->call('guardarDolor')->assertHasErrors('duracion_valor');
        $this->formularioDolor()->set('dolorEva', '4')->set('dolorDuracionValor', str_repeat('1', 25))
            ->set('dolorDuracionUnidad', str_repeat('x', 60))->call('guardarDolor')->assertHasErrors('duracion_valor');
        $this->assertDatabaseCount('valoraciones_dolor', 0);
    }

    public function test_dolor_descarte_protege_captura_y_resetea_historial_sin_persistir(): void
    {
        $this->actingAs($this->enfermero);
        $vista = $this->formularioDolor()->set('dolorUbicacion', 'Rodilla derecha, Zona lumbar')
            ->call('cerrarSelectorRegistro')->assertSet('confirmarDescarte', true)
            ->assertSet('dolorUbicacion', 'Rodilla derecha, Zona lumbar')
            ->call('cancelarDescarte')->assertSet('drawerPaso', 'register-form')
            ->call('volverPanelDetalle')->assertSet('confirmarDescarte', true)
            ->call('descartarCambios')->assertSet('drawerPaso', 'register-selector');
        $this->assertDatabaseCount('valoraciones_dolor', 0);
    }

    public function test_dolor_eva_diez_no_crea_severidad_ni_alerta_automatica(): void
    {
        $this->actingAs($this->enfermero);
        $alertas = Alerta::count();
        $this->formularioDolor()->set('dolorEva', '10')->call('guardarDolor')
            ->assertHasNoErrors()->assertSee('Valoración de dolor registrada');
        $this->assertDatabaseHas('valoraciones_dolor', ['intensidad' => 10, 'respuesta' => null]);
        $this->assertSame($alertas, Alerta::count());
    }

    public function test_dolor_fallo_de_persistencia_conserva_captura_y_ofrece_reintento(): void
    {
        $this->actingAs($this->enfermero);
        $formulario = $this->formularioDolor()->set('dolorEva', '6')
            ->set('dolorUbicacion', 'Rodilla derecha')->set('dolorIntervencion', 'Reposo');
        $this->partialMock(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class,
            function ($mock) {
                $mock->shouldReceive('registrarValoracionDolor')->once()->andThrow(
                    new \Illuminate\Database\QueryException('sqlite', 'insert test', [], new \RuntimeException('Synthetic persistence failure'))
                );
            });
        $formulario->call('guardarDolor')->assertHasErrors('dolor_guardado')
            ->assertSet('drawerPaso', 'register-form')->assertSet('dolorEva', '6')
            ->assertSet('dolorUbicacion', 'Rodilla derecha')->assertSet('dolorIntervencion', 'Reposo')
            ->assertSee('Conservamos los datos')->assertDontSee('Synthetic persistence failure')
            ->assertNotDispatched('dolor-registrado')->assertNotDispatched('rm-toast');
        $this->assertDatabaseCount('valoraciones_dolor', 0);
    }

    public function test_dolor_revalida_cuenta_y_asignacion_antes_de_persistir(): void
    {
        $this->actingAs($this->enfermero);
        $formulario = $this->formularioDolor()->set('dolorEva', '6');
        $this->enfermero->update(['estado' => 'INACTIVO']);
        $formulario->call('guardarDolor')->assertStatus(403);
        $this->assertDatabaseCount('valoraciones_dolor', 0);
        $this->enfermero->update(['estado' => 'ACTIVO']);
        $formulario = $this->formularioDolor()->set('dolorEva', '6');
        AsignacionResidenteJornada::where('cod_residente', $this->residenteEstable->cod_residente)
            ->update(['estado' => 'INACTIVA']);
        $formulario->call('guardarDolor')->assertStatus(403);
        $this->assertDatabaseCount('valoraciones_dolor', 0);
    }

    public function test_dolor_v2_episodio_8_5_3_conserva_origen_y_autoria_sin_sobrescribir(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);
        $vista = $this->formularioDolor()->set('dolorEva', '8')->set('dolorUbicacion', 'Rodilla derecha')
            ->set('dolorFrecuencia', '  Intermitente  ')->set('dolorFactoresAlivio', '  Reposo  ')
            ->set('dolorIntervencion', 'Cambio de posición')->call('guardarDolor')->assertHasNoErrors();
        $a = \App\Models\ValoracionDolor::firstOrFail();
        $original = $a->getAttributes();
        $this->assertNull($a->cod_valoracion_origen);
        $this->assertNull($a->respuesta);
        $this->assertNull($a->tipo_dolor);
        $this->assertSame('Intermitente', $a->frecuencia);
        $this->assertSame('Reposo', $a->factores_alivio);
        $this->travel(10)->minutes();
        $vista->call('abrirReevaluacionDolor', $a->getKey())
            ->assertSet('dolorCodOrigen', $a->getKey())->assertSet('dolorEva', '')
            ->assertSee('Reevaluación del dolor')->assertSee('Episodio iniciado')
            ->set('dolorEva', '5')->set('dolorRespuesta', 'Refiere menor intensidad')
            ->call('guardarDolor')->assertHasNoErrors()->assertSee('Reevaluación registrada');
        $b = \App\Models\ValoracionDolor::where('intensidad', 5)->firstOrFail();
        $this->assertSame($a->getKey(), $b->cod_valoracion_origen);
        $this->assertSame('Refiere menor intensidad', $b->respuesta);
        $this->travel(10)->minutes();
        $vista->call('abrirReevaluacionDolor', $b->getKey())->assertSet('dolorCodOrigen', $a->getKey())
            ->set('dolorEva', '3')->call('guardarDolor')->assertHasNoErrors();
        $c = \App\Models\ValoracionDolor::where('intensidad', 3)->firstOrFail();
        $this->assertSame($a->getKey(), $c->cod_valoracion_origen);
        $this->assertNull($c->respuesta);
        $this->assertSame($original, $a->fresh()->getAttributes());
        $this->assertSame($this->personal->getKey(), $c->cod_personal);
        $this->assertTrue($c->fecha_hora->equalTo(now()));
        $this->assertCount(2, $a->reevaluaciones);
        $this->assertTrue($b->origen->is($a));
        $this->assertDatabaseCount('valoraciones_dolor', 3);
    }

    public function test_dolor_v2_origen_ajeno_inexistente_futuro_anulado_y_ciclico_no_persisten(): void
    {
        $this->actingAs($this->enfermero);
        $servicio = app(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class);
        foreach (['AJENO', 'FUTURO', 'ANULADO', 'CICLO'] as $caso) {
            \App\Models\ValoracionDolor::create([
                'cod_valoracion_dolor' => 'VD_'.$caso,
                'cod_residente' => $caso === 'AJENO' ? $this->residenteCritico->getKey() : $this->residenteEstable->getKey(),
                'cod_personal' => $this->personal->getKey(), 'intensidad' => 4,
                'fecha_hora' => $caso === 'FUTURO' ? now()->addHour() : now()->subHour(),
                'estado' => $caso === 'ANULADO' ? 'ANULADO' : 'VIGENTE',
            ]);
        }
        \App\Models\ValoracionDolor::whereKey('VD_CICLO')->update(['cod_valoracion_origen' => 'VD_CICLO']);
        foreach (['VD_AJENO', 'VD_FUTURO', 'VD_ANULADO', 'VD_CICLO', 'VD_INEXISTENTE'] as $codigo) {
            try {
                $servicio->registrarValoracionDolor($this->residenteEstable->getKey(), ['intensidad' => 2, 'cod_valoracion_origen' => $codigo], $this->enfermero);
                $this->fail('No puede aceptar un origen inválido.');
            } catch (\Illuminate\Validation\ValidationException $exception) {
                $this->assertArrayHasKey('cod_valoracion_origen', $exception->errors());
            }
        }
        $this->assertDatabaseCount('valoraciones_dolor', 4);
    }

    public function test_dolor_v2_origen_locked_y_respuesta_inicial_prohibida(): void
    {
        $this->actingAs($this->enfermero);
        $vista = $this->formularioDolor()->set('dolorEva', '4')->set('dolorRespuesta', 'Texto sin episodio')
            ->call('guardarDolor')->assertHasErrors('respuesta');
        $this->assertDatabaseCount('valoraciones_dolor', 0);
        $this->expectException(\Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException::class);
        $vista->set('dolorCodOrigen', 'VD_INVENTADO');
    }

    public function test_dolor_v2_textos_nuevos_validan_y_participan_en_descarte(): void
    {
        $this->actingAs($this->enfermero);
        $this->formularioDolor()->set('dolorEva', '4')->set('dolorFrecuencia', str_repeat('a', 41))
            ->call('guardarDolor')->assertHasErrors('frecuencia')->assertSet('dolorEva', '4');
        foreach (['dolorFrecuencia', 'dolorFactoresAlivio', 'dolorRespuesta'] as $campo) {
            $this->formularioDolor()->set($campo, 'Texto')->call('cerrarSelectorRegistro')
                ->assertSet('confirmarDescarte', true)->call('cancelarDescarte')->assertSet($campo, 'Texto');
        }
        $this->assertDatabaseCount('valoraciones_dolor', 0);
    }

    public function test_dolor_v2_reevaluar_protege_captura_y_conserva_residente(): void
    {
        $this->actingAs($this->enfermero);
        $servicio = app(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class);
        $origen = $servicio->registrarValoracionDolor($this->residenteEstable->getKey(), ['intensidad' => 4], $this->enfermero);
        $vista = $this->formularioDolor()->set('dolorEva', '6')->set('dolorFactoresAlivio', 'Reposo')
            ->call('abrirReevaluacionDolor', $origen->getKey())->assertSet('confirmarDescarte', true)
            ->call('cancelarDescarte')->assertSet('dolorEva', '6')->assertSet('dolorFactoresAlivio', 'Reposo')
            ->call('abrirReevaluacionDolor', $origen->getKey())->call('descartarCambios')
            ->assertSet('dolorCodOrigen', $origen->getKey())->assertSet('dolorEva', '')
            ->assertSet('modalCodResidente', $this->residenteEstable->getKey())
            ->set('dolorEva', '4')->call('guardarDolor')->assertHasNoErrors()
            ->assertSet('dolorResultado.mediciones.3.valor', '+0 puntos');
        $this->assertDatabaseCount('valoraciones_dolor', 2);
    }

    public function test_dolor_v2_reevaluacion_revalida_permiso_lectura_y_asignacion(): void
    {
        $this->actingAs($this->enfermero);
        $servicio = app(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class);
        $origen = $servicio->registrarValoracionDolor($this->residenteEstable->getKey(), ['intensidad' => 4], $this->enfermero);
        $vista = $this->formularioDolor()->call('abrirReevaluacionDolor', $origen->getKey())->set('dolorEva', '3');
        \Spatie\Permission\Models\Role::findByName('ENFERMEROS')->revokePermissionTo('valoraciones_dolor.ver');
        $this->enfermero->unsetRelation('roles')->unsetRelation('permissions');
        $vista->call('guardarDolor')->assertForbidden();
        $this->assertDatabaseCount('valoraciones_dolor', 1);
    }

    public function test_dolor_v2_fk_impide_vinculo_ajeno_incluso_por_sql(): void
    {
        $this->actingAs($this->enfermero);
        $origen = app(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class)
            ->registrarValoracionDolor($this->residenteEstable->getKey(), ['intensidad' => 4], $this->enfermero);
        try {
            \Illuminate\Support\Facades\DB::transaction(fn () => \Illuminate\Support\Facades\DB::table('valoraciones_dolor')->insert([
                'cod_valoracion_dolor' => 'VD_CRUCE_SQL', 'cod_valoracion_origen' => $origen->getKey(),
                'cod_residente' => $this->residenteCritico->getKey(), 'cod_personal' => $this->personal->getKey(),
                'fecha_hora' => now(), 'intensidad' => 3, 'estado' => 'VIGENTE',
            ]));
            $this->fail('La FK debe impedir otro residente incluso por SQL directo.');
        } catch (\Illuminate\Database\QueryException $exception) {
            $this->assertNotEmpty($exception->getCode());
        }
        $this->assertDatabaseCount('valoraciones_dolor', 1);
    }

    public function test_dolor_v2_contexto_no_puede_cambiar_silenciosamente_de_residente(): void
    {
        $this->actingAs($this->enfermero);
        $vista = $this->formularioDolor()->set('dolorEva', '4')
            ->call('seleccionarResidente', $this->residenteCritico->getKey())
            ->assertSet('confirmarDescarte', true)->assertSet('modalCodResidente', $this->residenteEstable->getKey())
            ->call('cancelarDescarte');
        $detail = $vista->get('detalleResidente');
        $detail['cod_residente'] = $this->residenteCritico->getKey();
        $vista->set('detalleResidente', $detail)->set('modalCodResidente', $this->residenteCritico->getKey())
            ->call('guardarDolor')->assertForbidden();
        $this->assertDatabaseCount('valoraciones_dolor', 0);
    }

    public function test_dolor_v2_no_tiene_guardado_alternativo_fuera_de_la_captura(): void
    {
        $this->actingAs($this->enfermero);
        $this->formularioDolor()->set('dolorEva', '4')
            ->set('mostrarSelectorModal', false)->set('drawerPaso', 'resident-summary')
            ->call('guardarDolor')->assertForbidden();
        $this->assertDatabaseCount('valoraciones_dolor', 0);
    }

    public function test_dolor_v2_fk_restringe_borrado_y_origen_inexistente(): void
    {
        $this->actingAs($this->enfermero);
        $servicio = app(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class);
        $a = $servicio->registrarValoracionDolor($this->residenteEstable->getKey(), ['intensidad' => 8], $this->enfermero);
        $b = $servicio->registrarValoracionDolor($this->residenteEstable->getKey(), ['intensidad' => 5, 'cod_valoracion_origen' => $a->getKey()], $this->enfermero);
        foreach (['delete', 'missing'] as $case) {
            try {
                \Illuminate\Support\Facades\DB::transaction(function () use ($case, $a, $b) {
                    if ($case === 'delete') {
                        \Illuminate\Support\Facades\DB::table('valoraciones_dolor')->where('cod_valoracion_dolor', $a->getKey())->delete();
                    } else {
                        \Illuminate\Support\Facades\DB::table('valoraciones_dolor')->where('cod_valoracion_dolor', $b->getKey())->update(['cod_valoracion_origen' => 'VD_NO_EXISTE']);
                    }
                });
                $this->fail('La FK debía rechazar la operación.');
            } catch (\Illuminate\Database\QueryException $exception) {
                $this->assertNotEmpty($exception->getCode());
            }
        }
        $this->assertDatabaseCount('valoraciones_dolor', 2);
        $this->assertSame($a->getKey(), $b->fresh()->cod_valoracion_origen);
    }

    private function formularioHidratacionClinica()
    {
        $this->travelTo(today()->setTime(10, 0));
        \App\Models\AsignacionPersonal::create([
            'cod_asignacion_personal' => 'ASP_HID_UI_TEST', 'cod_jornada' => 'JOR_MIS_PACIENTES',
            'cod_personal' => $this->personal->cod_personal, 'cod_area' => $this->area->cod_area,
            'tipo_asignacion' => 'TURNO', 'fecha_asignacion' => now(), 'estado' => 'ACTIVA',
        ]);
        $this->actingAs($this->enfermero);
        return Livewire::withQueryParams(['cuidado' => 'hidratacion', 'tipo' => 'HIDRATACION'])
            ->test(\App\Frontend\Livewire\Enfermeria\Cuidados\RegistrosEnfermeria::class, ['codResidente' => $this->residenteEstable->cod_residente]);
    }

    public function test_hidratacion_clinica_error_preserva_tipo_de_liquido_y_no_guarda(): void
    {
        $this->formularioHidratacionClinica()->assertSee('Sin registros de hidratación')
            ->set('subtipo', 'Agua')->call('guardarCuidado')->assertHasErrors('cantidadMl')
            ->assertSet('subtipo', 'Agua')->assertSeeHtml('id="hidratacion-volumen-error"')
            ->assertNotDispatched('swal');
        $this->assertDatabaseCount('registros_hidratacion', 0);
    }

    public function test_hidratacion_clinica_sucesiva_conserva_historial_y_autoria(): void
    {
        $vista = $this->formularioHidratacionClinica();
        $vista->set('subtipo', 'Agua')->set('cantidadMl', 250)->set('tolerancia', 'ADECUADA')
            ->call('guardarCuidado')->assertHasNoErrors()->assertDispatched('swal')
            ->assertSee('250.00')->assertSet('cantidadMl', null);
        $vista->set('subtipo', 'Infusión')->set('cantidadMl', 180)
            ->call('guardarCuidado')->assertHasNoErrors()->assertSee('180.00')->assertSee('250.00');
        $this->assertDatabaseCount('registros_hidratacion', 2);
        $this->assertDatabaseHas('registros_hidratacion', ['cod_residente' => $this->residenteEstable->cod_residente,
            'cod_personal' => $this->personal->cod_personal, 'cod_jornada' => 'JOR_MIS_PACIENTES', 'cantidad_ml' => 250]);
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
        // Fixture de captura dentro de la jornada; no depender del reloj de ejecución.
        $this->travelTo(today()->setTime(10, 0));
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
            ->assertSee('Tipo de comida')
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
                ->set('ingestaRegistrarLiquidos', true)
                ->set('ingestaCantidadMl', $volumen)
                ->call('guardarCuidado')->assertHasErrors('cantidad_ml');
        }
        $this->assertDatabaseCount('registros_hidratacion', 0);
    }

    public function test_alimentacion_guarda_ingesta_e_hidratacion_reales_y_muestra_resultado(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);
        $this->formularioAlimentacion()
            ->assertSee('Tipo de comida')
            ->assertSee('Asistencia')
            ->assertSee('Deglución')
            ->set('ingestaTipoComida', 'ALMUERZO')
            ->set('ingestaPorcentaje', '87.50')
            ->set('ingestaRegistrarLiquidos', true)
                ->set('ingestaCantidadMl', '220.25')
            ->set('ingestaTolerancia', 'BUENA')
            ->set('ingestaDificultadDeglucion', true)
            ->set('ingestaObservacion', '  Necesitó ayuda para comer.  ')
            ->call('guardarCuidado')->assertHasNoErrors()
            ->assertSet('drawerPaso', 'register-result')
            ->assertSee('Ingesta registrada')
            ->call('registrarOtraIngesta')->assertSet('drawerPaso', 'register-form')
            ->assertSet('ingestaTipoComida', '')->assertSet('ingestaDificultadDeglucion', null);

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
            ->set('ingestaDificultadDeglucion', false)
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
            ->set('ingestaDificultadDeglucion', false)
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

    public function test_ingesta_v2_exige_respuesta_explicita_y_conserva_captura_invalida(): void
    {
        $this->actingAs($this->enfermero);
        $this->formularioAlimentacion()->assertSet('ingestaDificultadDeglucion', null)
            ->set('ingestaTipoComida', 'CENA')->set('ingestaObservacion', 'Dato capturado')
            ->call('guardarCuidado')->assertHasErrors('dificultad_deglucion')
            ->assertSet('drawerPaso', 'register-form')->assertSet('ingestaObservacion', 'Dato capturado')
            ->assertSet('ingestaResultado', [])->assertNotDispatched('cuidado-registrado');
        $this->assertDatabaseCount('registros_ingesta', 0);
    }

    public function test_ingesta_v2_catalogo_nulos_autoria_y_hora_son_reales(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);
        $this->formularioAlimentacion();
        $servicio = app(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class);
        $alertas = Alerta::count();
        foreach ($servicio::TIPOS_COMIDA as $comida) {
            $registro = $servicio->registrarAlimentacion($this->residenteEstable->cod_residente, [
                'tipo_comida' => $comida, 'dificultad_deglucion' => false, 'observacion' => '  ',
                'cod_personal' => 'AUTOR_FALSO', 'fecha_hora' => '2000-01-01', 'apetito' => 'INVENTADO',
            ], $this->enfermero);
            $this->assertNull($registro->porcentaje_consumido);
            $this->assertNull($registro->tolerancia);
            $this->assertNull($registro->apetito);
            $this->assertNull($registro->observacion);
            $this->assertFalse($registro->dificultad_deglucion);
            $this->assertSame($this->personal->cod_personal, $registro->cod_personal);
            $this->assertTrue($registro->fecha_hora->equalTo(now()));
        }
        $this->assertSame($alertas, Alerta::count());
        $this->assertDatabaseCount('registros_hidratacion', 0);
    }

    public function test_ingesta_v2_extremos_decimales_y_umbral_configurado(): void
    {
        $this->actingAs($this->enfermero);
        config(['enfermeria.porcentaje_baja_ingesta' => 70]);
        $this->formularioAlimentacion();
        $servicio = app(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class);
        $alertas = Alerta::count();
        foreach (['0', '69.99', '70', '72.50', '100.00'] as $porcentaje) {
            $registro = $servicio->registrarAlimentacion($this->residenteEstable->cod_residente, [
                'tipo_comida' => 'ALMUERZO', 'porcentaje_consumido' => $porcentaje,
                'dificultad_deglucion' => true, 'cantidad_ml' => '0',
            ], $this->enfermero);
            $this->assertEquals((float) $porcentaje, $registro->porcentaje_consumido);
            // La alerta activa se deduplica por residente y tipo; el segundo registro no la duplica.
            $this->assertSame($alertas + 1, Alerta::count());
        }
        $this->assertDatabaseHas('alertas', ['tipo' => 'BAJA INGESTA', 'prioridad' => 'MEDIO', 'modulo' => 'SEGUIMIENTO']);
        $this->assertDatabaseCount('registros_hidratacion', 5);
        $ingesta = \App\Models\RegistroIngesta::first();
        $hidratacion = \App\Models\RegistroHidratacion::first();
        foreach (['cod_residente', 'cod_personal', 'cod_jornada'] as $campo) {
            $this->assertSame($ingesta->$campo, $hidratacion->$campo);
        }
        $this->assertTrue($ingesta->fecha_hora->equalTo($hidratacion->fecha_hora));
    }

    public function test_ingesta_v2_valida_observacion_y_limites_sin_truncar(): void
    {
        $this->actingAs($this->enfermero);
        $this->formularioAlimentacion();
        $servicio = app(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class);
        foreach ([['porcentaje_consumido' => '100.01'], ['cantidad_ml' => '999999.999'],
            ['cantidad_ml' => '1000000'], ['observacion' => str_repeat('a', 5001)],
            ['dificultad_deglucion' => 'quizá']] as $dato) {
            try {
                $servicio->registrarAlimentacion($this->residenteEstable->cod_residente,
                    array_merge(['tipo_comida' => 'CENA', 'dificultad_deglucion' => false], $dato), $this->enfermero);
                $this->fail('Se aceptó un valor inválido.');
            } catch (\Illuminate\Validation\ValidationException $exception) {
                $this->assertArrayHasKey(array_key_first($dato), $exception->errors());
            }
        }
        $this->assertDatabaseCount('registros_ingesta', 0);
        $registro = $servicio->registrarAlimentacion($this->residenteEstable->cod_residente,
            ['tipo_comida' => 'CENA', 'dificultad_deglucion' => false,
                'observacion' => str_repeat('á', 5000), 'cantidad_ml' => '999999.99'], $this->enfermero);
        $this->assertSame(5000, mb_strlen($registro->observacion));
    }

    public function test_ingesta_v2_toggle_limpia_y_no_guarda_cantidad_oculta(): void
    {
        $this->actingAs($this->enfermero);
        $this->formularioAlimentacion()->set('ingestaTipoComida', 'CENA')
            ->set('ingestaDificultadDeglucion', false)->set('ingestaRegistrarLiquidos', true)
            ->set('ingestaCantidadMl', '220.25')->set('ingestaRegistrarLiquidos', false)
            ->assertSet('ingestaCantidadMl', '')->set('ingestaCantidadMl', '220.25')
            ->call('guardarCuidado')->assertHasNoErrors()->assertSet('drawerPaso', 'register-result');
        $this->assertDatabaseCount('registros_hidratacion', 0);
    }

    public function test_ingesta_v2_no_concede_hidratacion_por_manipulacion(): void
    {
        $this->actingAs($this->enfermero);
        $formulario = $this->formularioAlimentacion()->set('ingestaTipoComida', 'CENA')->set('ingestaDificultadDeglucion', false);
        \Spatie\Permission\Models\Role::findByName('ENFERMEROS')->revokePermissionTo('registros_hidratacion.crear');
        $this->enfermero->unsetRelation('roles')->unsetRelation('permissions');
        $formulario->set('ingestaRegistrarLiquidos', true)->set('ingestaCantidadMl', '0')
            ->call('guardarCuidado')->assertStatus(403);
        $this->assertDatabaseCount('registros_ingesta', 0);
        $this->assertDatabaseCount('registros_hidratacion', 0);
    }

    public function test_ingesta_v2_fallo_hidratacion_revierte_transaccion_y_conserva_datos(): void
    {
        $this->actingAs($this->enfermero);
        $formulario = $this->formularioAlimentacion()->set('ingestaTipoComida', 'CENA')
            ->set('ingestaDificultadDeglucion', false)->set('ingestaPorcentaje', '10')
            ->set('ingestaRegistrarLiquidos', true)->set('ingestaCantidadMl', '220.25');
        $alertas = Alerta::count();
        \App\Models\RegistroHidratacion::creating(static function (): void {
            throw new \Illuminate\Database\QueryException('sqlite', 'insert synthetic', [], new \RuntimeException('Synthetic persistence failure'));
        });
        $formulario->call('guardarCuidado')->assertHasErrors('ingesta_guardado')
            ->assertSet('drawerPaso', 'register-form')->assertSet('ingestaPorcentaje', '10')
            ->assertSet('ingestaCantidadMl', '220.25')->assertSet('ingestaResultado', [])
            ->assertNotDispatched('cuidado-registrado')->assertSee('Conservamos los datos')
            ->assertDontSee('Synthetic persistence failure');
        $this->assertDatabaseCount('registros_ingesta', 0);
        $this->assertDatabaseCount('registros_hidratacion', 0);
        $this->assertSame($alertas, Alerta::count());
    }

    public function test_ingesta_v2_historial_acotado_a_residente_vigencia_y_fecha(): void
    {
        $this->actingAs($this->enfermero);
        $this->formularioAlimentacion();
        $base = ['cod_residente' => $this->residenteEstable->cod_residente,
            'cod_personal' => $this->personal->cod_personal, 'cod_jornada' => 'JOR_MIS_PACIENTES',
            'tipo_comida' => 'CENA', 'estado' => 'VIGENTE'];
        for ($i = 0; $i < 12; $i++) {
            \App\Models\RegistroIngesta::create($base + ['cod_ingesta' => 'ING_HIST_'.$i,
                'fecha_hora' => now()->subMinutes($i + 1), 'porcentaje_consumido' => $i === 0 ? null : 0]);
        }
        foreach (['ANULADA', 'FUTURA', 'OTRO'] as $caso) {
            \App\Models\RegistroIngesta::create(array_merge($base,
                ['cod_ingesta' => 'ING_'.$caso, 'fecha_hora' => $caso === 'FUTURA' ? now()->addHour() : now(),
                    'estado' => $caso === 'ANULADA' ? 'ANULADO' : 'VIGENTE',
                    'cod_residente' => $caso === 'OTRO' ? $this->residenteCritico->cod_residente : $this->residenteEstable->cod_residente]));
        }
        $formulario = $this->formularioAlimentacion();
        $historial = $formulario->get('ingestaHistorial');
        $this->assertCount(10, $historial);
        $this->assertSame('ING_HIST_0', $historial[0]['codigo']);
        $this->assertNull($historial[0]['porcentaje']);
        $this->assertEquals(0, $historial[1]['porcentaje']);
    }

    public function test_ingesta_v2_resultado_impide_reenvio_y_vuelve_al_mismo_residente(): void
    {
        $this->actingAs($this->enfermero);
        $formulario = $this->formularioAlimentacion()->set('ingestaTipoComida', 'CENA')
            ->set('ingestaDificultadDeglucion', false)->call('guardarCuidado')->assertHasNoErrors();
        $formulario->call('volverResidenteDesdeIngesta')->assertSet('drawerPaso', 'resident-summary')
            ->assertSet('detalleResidente.cod_residente', $this->residenteEstable->cod_residente);
        $this->assertDatabaseCount('registros_ingesta', 1);
        $this->formularioAlimentacion()->set('ingestaTipoComida', 'CENA')
            ->set('ingestaDificultadDeglucion', false)->call('guardarCuidado')->assertHasNoErrors()
            ->call('guardarCuidado')->assertStatus(403);
        $this->assertDatabaseCount('registros_ingesta', 2);
    }

    public function test_ingesta_v2_revalida_cuenta_personal_jornada_y_asignacion_al_guardar(): void
    {
        $this->actingAs($this->enfermero);
        $this->formularioAlimentacion();
        $servicio = app(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class);
        foreach (['cuenta', 'personal', 'jornada', 'asignacion'] as $caso) {
            if ($caso === 'cuenta') $this->enfermero->update(['estado' => 'INACTIVO']);
            if ($caso === 'personal') {
                $this->personal->update(['estado' => 'INACTIVO']);
                $this->enfermero->unsetRelation('personal');
            }
            if ($caso === 'jornada') Jornada::whereKey('JOR_MIS_PACIENTES')->update(['estado' => 'FINALIZADA']);
            if ($caso === 'asignacion') AsignacionResidenteJornada::where('cod_residente', $this->residenteEstable->cod_residente)->update(['estado' => 'FINALIZADA']);
            try {
                $servicio->registrarAlimentacion($this->residenteEstable->cod_residente,
                    ['tipo_comida' => 'CENA', 'dificultad_deglucion' => false], $this->enfermero);
                $this->fail('Se aceptó un contexto inválido: '.$caso);
            } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
                $this->assertSame(403, $exception->getStatusCode(), $caso);
            }
            $this->enfermero->update(['estado' => 'ACTIVO']);
            $this->personal->update(['estado' => 'ACTIVO']);
            $this->enfermero->unsetRelation('personal');
            Jornada::whereKey('JOR_MIS_PACIENTES')->update(['estado' => 'ACTIVA']);
            AsignacionResidenteJornada::where('cod_residente', $this->residenteEstable->cod_residente)->update(['estado' => 'ACTIVA']);
        }
        $this->assertDatabaseCount('registros_ingesta', 0);
    }

    public function test_ingesta_v2_fallo_alerta_revierte_ingesta_e_hidratacion(): void
    {
        $this->actingAs($this->enfermero);
        $formulario = $this->formularioAlimentacion()->set('ingestaTipoComida', 'CENA')
            ->set('ingestaDificultadDeglucion', false)->set('ingestaPorcentaje', '10')
            ->set('ingestaRegistrarLiquidos', true)->set('ingestaCantidadMl', '100');
        $alertas = Alerta::count();
        $this->partialMock(\App\Backend\Modulos\Alertas\Servicios\AlertasService::class, function ($mock) {
            $mock->shouldReceive('crear')->once()->andThrow(
                new \Illuminate\Database\QueryException('sqlite', 'insert synthetic', [], new \RuntimeException('Synthetic alert persistence failure')));
        });
        $formulario->call('guardarCuidado')->assertHasErrors('ingesta_guardado')
            ->assertSet('ingestaPorcentaje', '10')->assertSet('ingestaResultado', [])
            ->assertNotDispatched('cuidado-registrado');
        $this->assertDatabaseCount('registros_ingesta', 0);
        $this->assertDatabaseCount('registros_hidratacion', 0);
        $this->assertSame($alertas, Alerta::count());
    }

    public function test_ingesta_v2_descartar_o_limpiar_conserva_historial_y_contexto(): void
    {
        $this->actingAs($this->enfermero);
        $formulario = $this->formularioAlimentacion()->set('ingestaDificultadDeglucion', false)
            ->call('cerrarSelectorRegistro')->assertSet('confirmarDescarte', true)
            ->call('cancelarDescarte')->assertSet('ingestaDificultadDeglucion', false)
            ->call('solicitarLimpiezaRegistro')->assertSet('confirmarLimpiezaRegistro', true)
            ->call('limpiarCamposRegistro')->assertSet('ingestaDificultadDeglucion', null)
            ->assertSet('ingestaRegistrarLiquidos', false)->assertSet('ingestaCantidadMl', '')
            ->assertSet('ingestaResidenteContexto', $this->residenteEstable->cod_residente);
        $this->assertDatabaseCount('registros_ingesta', 0);
        $formulario->set('ingestaRegistrarLiquidos', true)->call('volverPanelDetalle')
            ->assertSet('confirmarDescarte', true)->call('descartarCambios')
            ->assertSet('drawerPaso', 'register-selector');
    }

    public function test_ingesta_v2_sin_permiso_lectura_no_expone_historial_ni_finge_ausencia(): void
    {
        $this->actingAs($this->enfermero);
        \Spatie\Permission\Models\Role::findByName('ENFERMEROS')->revokePermissionTo('registros_ingesta.ver');
        $this->enfermero->unsetRelation('roles')->unsetRelation('permissions');
        $this->formularioAlimentacion()->assertSet('ingestaHistorial', [])
            ->assertSee('Sin permiso para consultar las ingestas anteriores.')
            ->assertDontSee('Último registro guardado')->assertDontSee('id="ingesta-grafica-popup"', false);
    }

    private function formularioEliminacion()
    {
        $this->travelTo(today()->setTime(10, 0));
        \App\Models\AsignacionPersonal::firstOrCreate(['cod_asignacion_personal' => 'ASP_ELIM_TEST'], [
            'cod_jornada' => 'JOR_MIS_PACIENTES', 'cod_personal' => $this->personal->cod_personal,
            'cod_area' => $this->area->cod_area, 'tipo_asignacion' => 'TURNO', 'fecha_asignacion' => now(), 'estado' => 'ACTIVA']);
        return Livewire::test(MisPacientes::class)->call('seleccionarResidente', $this->residenteEstable->cod_residente)
            ->call('mostrarSelectorRegistro')->call('abrirFormularioRegistro', 'eliminacion')->assertStatus(200);
    }

    public function test_eliminacion_exige_tipo_y_rechaza_tipo_fuera_del_formulario(): void
    {
        $this->actingAs($this->enfermero);
        $this->formularioEliminacion()->assertSee('Eliminación urinaria')->assertSee('Eliminación intestinal')
            ->call('guardarEliminacion')->assertHasErrors('tipo_eliminacion');
        $this->formularioEliminacion()->set('elimTipo', 'AMBAS')->call('guardarEliminacion')->assertHasErrors('tipo_eliminacion');
        $this->assertDatabaseCount('registros_eliminacion', 0);
    }

    public function test_eliminacion_cambiar_tipo_limpia_campos_urinarios_e_intestinales(): void
    {
        $this->actingAs($this->enfermero);
        $formulario = $this->formularioEliminacion()->call('solicitarTipoEliminacion', 'URINARIA')
            ->set('elimDatos.volumen_ml', '250')->set('elimDatos.color_orina', 'AMBAR')
            ->set('elimDatos.cantidad_cualitativa', 'HABITUAL')->set('elimDatos.observacion', 'Asistencia observada')
            ->call('solicitarTipoEliminacion', 'INTESTINAL')->assertSet('elimTipo', 'URINARIA')
            ->assertSet('elimTipoPendiente', 'INTESTINAL')->call('cancelarTipoEliminacion')->assertSet('elimDatos.volumen_ml','250')
            ->call('solicitarTipoEliminacion','INTESTINAL')->call('confirmarTipoEliminacion')
            ->assertSet('elimTipo','INTESTINAL')->assertSet('elimDatos.volumen_ml','')->assertSet('elimDatos.color_orina','')
            ->assertSet('elimDatos.cantidad_cualitativa','HABITUAL')->assertSet('elimDatos.observacion','Asistencia observada');
        $formulario->set('elimDatos.tipo_bristol',4)->set('elimDatos.presencia_moco',false)
            ->call('solicitarTipoEliminacion','URINARIA')->call('confirmarTipoEliminacion')
            ->assertSet('elimDatos.tipo_bristol','')->assertSet('elimDatos.presencia_moco',null);
    }

    public function test_eliminacion_no_persiste_campos_ocultos_aunque_se_manipulen(): void
    {
        $this->actingAs($this->enfermero);
        $this->formularioEliminacion()->call('solicitarTipoEliminacion','INTESTINAL')->set('elimDatos.tipo_bristol',4)
            ->set('elimDatos.volumen_ml','999')->call('guardarEliminacion')->assertHasErrors('volumen_ml');
        $this->assertDatabaseCount('registros_eliminacion',0);
    }

    public function test_eliminacion_rechaza_cantidad_negativa_y_continencia_invalida(): void
    {
        $this->actingAs($this->enfermero);
        $this->formularioEliminacion()->call('solicitarTipoEliminacion','URINARIA')->set('elimDatos.volumen_ml','-1')
            ->call('guardarEliminacion')->assertHasErrors('volumen_ml');
        $this->formularioEliminacion()->call('solicitarTipoEliminacion','INTESTINAL')->set('elimDatos.continencia','INCONTINENCIA_URINARIA')
            ->call('guardarEliminacion')->assertHasErrors('continencia');
        $this->assertDatabaseCount('registros_eliminacion',0);
    }

    public function test_eliminacion_guarda_campos_reales_con_autoria_y_observaciones(): void
    {
        $this->actingAs($this->enfermero);
        $this->formularioEliminacion()->call('solicitarTipoEliminacion','URINARIA')->set('elimDatos.volumen_ml','250.50')
            ->set('elimDatos.color_orina','AMBAR')->set('elimDatos.continencia','CONTINENTE')
            ->set('elimDatos.observacion','  Requirió asistencia.  ')->call('guardarEliminacion')->assertHasNoErrors()
            ->assertSet('drawerPaso','register-result');
        $this->assertDatabaseHas('registros_eliminacion',['cod_residente'=>$this->residenteEstable->cod_residente,
            'cod_personal'=>$this->personal->cod_personal,'cod_jornada'=>'JOR_MIS_PACIENTES','tipo_eliminacion'=>'URINARIA',
            'volumen_ml'=>'250.50','color_orina'=>'AMBAR','continencia'=>'CONTINENTE','cantidad'=>null,'caracteristica'=>null,
            'observacion'=>'Requirió asistencia.','estado'=>'VIGENTE']);
    }

    public function test_eliminacion_rechaza_residente_no_asignado_y_usuario_sin_permiso(): void
    {
        $this->actingAs($this->enfermero); $this->formularioEliminacion();
        AsignacionResidenteJornada::where('cod_residente',$this->residenteCritico->cod_residente)->update(['estado'=>'FINALIZADA']);
        try { app(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class)
            ->registrarEliminacion($this->residenteCritico->cod_residente,['tipo_eliminacion'=>'URINARIA'],$this->enfermero);
            $this->fail('Se aceptó un residente no asignado.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame(403,$e->getStatusCode()); }
        \Spatie\Permission\Models\Role::findByName('ENFERMEROS')->revokePermissionTo('registros_eliminacion.crear');
        Livewire::test(MisPacientes::class)->call('seleccionarResidente',$this->residenteEstable->cod_residente)
            ->call('mostrarSelectorRegistro')->call('abrirFormularioRegistro','eliminacion')->assertStatus(403);
        $this->assertDatabaseCount('registros_eliminacion',0);
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
            ->assertSee('Capacidad observada')
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
            ->assertSet('drawerPaso', 'register-result')->assertSee('Movilidad registrada');

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

    public function test_signo_critico_confirmado_crea_alerta_y_evento_en_la_misma_operacion(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);
        $registro = app(\App\Backend\Modulos\Clinica\Servicios\SignosVitalesService::class)
            ->registrarConEvaluacion($this->residenteEstable->cod_residente,
                ['frecuencia_cardiaca' => 135], $this->enfermero);

        $this->assertSame('CRITICO', $registro->evaluacion->severidadGlobal()?->value);
        $this->assertNotNull($registro->alerta);
        $this->assertSame($registro->signo->cod_signo, $registro->alerta->cod_registro);
        $this->assertSame($this->personal->cod_personal, $registro->signo->cod_personal);
        $this->assertDatabaseHas('eventos_alerta', [
            'cod_alerta' => $registro->alerta->cod_alerta,
            'cod_usuario' => $this->enfermero->cod_usuario,
            'tipo_evento' => 'CREACION',
        ]);
    }

    public function test_advertencia_y_preevaluacion_no_crean_alertas_ni_registros_anticipados(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);
        $servicio = app(\App\Backend\Modulos\Clinica\Servicios\SignosVitalesService::class);
        $signosAntes = SignoVital::query()->count();
        $alertasAntes = Alerta::query()->count();

        $evaluacion = $servicio->preEvaluar(['frecuencia_cardiaca' => 135],
            $this->residenteEstable->cod_residente, $this->enfermero);
        $this->assertSame('CRITICO', $evaluacion->severidadGlobal()?->value);
        $this->assertSame($signosAntes, SignoVital::query()->count());
        $this->assertSame($alertasAntes, Alerta::query()->count());

        $registro = $servicio->registrarConEvaluacion($this->residenteEstable->cod_residente,
            ['frecuencia_cardiaca' => 45], $this->enfermero);
        $this->assertSame('ADVERTENCIA', $registro->evaluacion->severidadGlobal()?->value);
        $this->assertNull($registro->alerta);
        $this->assertSame($alertasAntes, Alerta::query()->count());
    }

    public function test_falla_al_crear_alerta_revierte_el_signo_critico(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);
        $signosAntes = SignoVital::query()->count();
        $alertasAntes = Alerta::query()->count();
        Alerta::creating(static function (): void {
            throw new \RuntimeException('Fallo de persistencia simulado en prueba.');
        });

        try {
            app(\App\Backend\Modulos\Clinica\Servicios\SignosVitalesService::class)
                ->registrarConEvaluacion($this->residenteEstable->cod_residente,
                    ['frecuencia_cardiaca' => 135], $this->enfermero);
            $this->fail('La transacción debía fallar.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Fallo de persistencia simulado en prueba.', $exception->getMessage());
        }

        $this->assertSame($signosAntes, SignoVital::query()->count());
        $this->assertSame($alertasAntes, Alerta::query()->count());
    }

    public function test_objetivo_medico_individual_se_aplica_al_registro_de_enfermeria(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $medico = User::factory()->create(['estado' => 'ACTIVO']);
        $medico->assignRole('MEDICO GENERAL/GERIATRA');
        Personal::create([
            'cod_personal' => 'PER_MED_SIGNOS', 'cod_usuario' => $medico->cod_usuario,
            'nombres' => 'Médica', 'apellido_paterno' => 'Prueba',
            'numero_documento' => 'MED-SIGNOS-01', 'profesion' => 'MEDICINA', 'estado' => 'ACTIVO',
        ]);
        $this->actingAs($medico);
        app(\App\Backend\Modulos\Clinica\Acciones\DefinirObjetivoSignoVitalAction::class)
            ->ejecutar($this->residenteEstable->cod_residente, [
                'parametro' => 'saturacion_oxigeno', 'min_objetivo' => 88,
                'max_objetivo' => 92, 'min_critico' => 85, 'max_critico' => null,
                'motivo' => 'Rango individual indicado tras valoración médica.',
            ], $medico);

        $this->actingAs($this->enfermero);
        $this->assertSame(['sat' => ['min' => 88.0, 'max' => 92.0]], $this->formularioSignos()->get('signosBandasObjetivo'));
        $registro = app(\App\Backend\Modulos\Clinica\Servicios\SignosVitalesService::class)
            ->registrarConEvaluacion($this->residenteEstable->cod_residente,
                ['saturacion_oxigeno' => 84], $this->enfermero);

        $this->assertSame('OBJETIVO_MEDICO', $registro->evaluacion->resultados[0]->fuenteEvaluacion);
        $this->assertSame('CRITICO', $registro->evaluacion->severidadGlobal()?->value);
        $this->assertNotNull($registro->alerta);
    }

    public function test_formulario_muestra_preevaluacion_sin_persistirla(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);
        $signosAntes = SignoVital::query()->count();
        $alertasAntes = Alerta::query()->count();

        $formulario = $this->formularioSignos()->set('signoFC', '135');
        $this->assertSame('CRITICO', $formulario->get('signosEvaluacion')['severidad_global']);
        $formulario->assertSee('data-tone="danger"', false)
            ->assertSee('rm-signos__card--fc', false)
            ->assertSee('wire:model.live.debounce.350ms="signoFC"', false)
            ->assertSee('wire:loading.class="rm-signos__card--evaluating"', false)
            ->assertSee('Crítico')
            ->assertSee('Verificar la lectura')
            ->assertSee('Confirmar y registrar')
            ->assertSee('Documentar la atención')
            ->assertSee('Registra la intervención en esa alerta para habilitar los otros controles.');
        $formulario->set('signoGlucosa', '68')->set('signoSat', '90')
            ->assertSee('data-tone="warning"', false)
            ->assertSee('Advertencia')
            ->assertSee('Sin objetivo médico')
            ->assertSee('Clasificación pendiente · Sin objetivo médico vigente');
        $this->assertSame($signosAntes, SignoVital::query()->count());
        $this->assertSame($alertasAntes, Alerta::query()->count());
    }
    public function test_critico_con_otro_campo_invalido_protege_salida_hasta_confirmacion(): void
    {
        $this->actingAs($this->enfermero);
        $formulario = $this->formularioSignos()->set('signoFC', '135')->set('signoTemp', '6')
            ->assertSet('signosEvaluacion.severidad_global', 'CRITICO')
            ->call('cerrarSelectorRegistro')->assertSet('confirmarDescarte', true)
            ->call('cancelarDescarte')->call('seleccionarResidente', $this->residenteCritico->cod_residente)
            ->assertSet('confirmarDescarte', true)
            ->assertSet('modalCodResidente', $this->residenteEstable->cod_residente);
    }

    public function test_limpiar_campos_vacia_captura_y_errores_sin_cambiar_contexto_ni_historia(): void
    {
        $this->travelTo(today()->setTime(10, 17));
        $this->actingAs($this->enfermero);
        $signosAntes = SignoVital::query()->count();
        $alertasAntes = Alerta::query()->count();
        $formulario = $this->formularioSignos();
        $fecha = $formulario->get('signoFechaHora');
        $historia = $formulario->get('signosHistorial');
        $formulario->set('signoSat', '98')->call('solicitarLimpiezaSignos')
            ->assertSee('Confirmar limpieza')->assertSee('Seguir editando')
            ->assertSee('id="signos-sat"', false)
            ->set('confirmarLimpiezaSignos', false)->assertSet('signoSat', '98');
        $formulario->set('signoFC', '72')->set('signoSat', '98')->set('signoObs', 'Nota temporal')
            ->set('signoTemp', '6')->call('guardarSignos')->assertHasErrors('temperatura');
        $this->travel(5)->minutes();
        $formulario->call('limpiarCamposSignos')->assertHasNoErrors()
            ->assertDispatched('signos-campos-limpiados')
            ->assertSet('signoFechaHora', $fecha)
            ->assertSet('signosHistorial', $historia)
            ->assertSet('modalCodResidente', $this->residenteEstable->cod_residente)
            ->assertSet('drawerPaso', 'register-form')->assertSet('mostrarSelectorModal', true)
            ->assertSet('confirmarLimpiezaSignos', false)
            ->assertSet('signosConfirmacionPendiente', false)->assertSet('signosIntentoGuardar', false);
        foreach (['signoSis', 'signoDia', 'signoFC', 'signoFR', 'signoTemp', 'signoSat', 'signoGlucosa', 'signoObs'] as $campo) {
            $formulario->assertSet($campo, '');
        }
        $formulario->call('cerrarSelectorRegistro')->assertSet('confirmarDescarte', false)
            ->assertSet('mostrarSelectorModal', false);
        $this->assertSame($signosAntes, SignoVital::query()->count());
        $this->assertSame($alertasAntes, Alerta::query()->count());
    }

    public function test_limpieza_confirmada_descarta_critico_sin_registro_ni_alerta(): void
    {
        $this->actingAs($this->enfermero);
        $signosAntes = SignoVital::query()->count();
        $alertasAntes = Alerta::query()->count();
        $formulario = $this->formularioSignos()->set('signoFC', '135')->set('signoTemp', '6')
            ->set('signoObs', 'Lectura pendiente')->call('solicitarLimpiezaRegistro')
            ->assertSet('confirmarLimpiezaRegistro', true)->assertSee('Confirmar limpieza')
            ->assertSet('signoFC', '135')->call('limpiarCamposRegistro')->assertHasNoErrors()
            ->assertSet('signoFC', '')->assertSet('signoTemp', '')->assertSet('signoObs', '')
            ->assertSet('mostrarSelectorModal', true)->assertDispatched('signos-campos-limpiados');
        $this->assertDatabaseMissing('signos_vitales', ['frecuencia_cardiaca' => 135]);
        $this->assertSame($signosAntes, SignoVital::query()->count());
        $this->assertSame($alertasAntes, Alerta::query()->count());
    }

    public function test_limpiar_campos_revalida_permiso_y_contexto_del_formulario(): void
    {
        $this->actingAs($this->enfermero);
        Livewire::test(MisPacientes::class)->call('limpiarCamposSignos')->assertForbidden();
        $formulario = $this->formularioSignos()->set('signoFC', '72');
        \Spatie\Permission\Models\Role::findByName('ENFERMEROS')->revokePermissionTo('signos_vitales.crear');
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $this->enfermero->unsetRelation('roles')->unsetRelation('permissions');
        $formulario->call('limpiarCamposSignos')->assertForbidden();
    }

    public function test_limpieza_comun_cubre_capturas_y_exige_confirmacion_sin_perder_contexto(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);
        $this->crearDosisProgramada($this->residenteEstable);
        foreach (['dolor' => 'dolorUbicacion', 'alimentacion' => 'ingestaObservacion',
            'eliminacion' => 'elimDatos.observacion', 'movilidad' => 'movObservacion',
            'seguimiento' => 'segObs', 'procedimiento' => 'procDetalle', 'medicacion' => 'medObservacion'] as $tipo => $campo) {
            $formulario = Livewire::test(MisPacientes::class)
                ->call('seleccionarResidente', $this->residenteEstable->cod_residente)
                ->call('mostrarSelectorRegistro')->call('abrirFormularioRegistro', $tipo)
                ->assertSee('Limpiar campos')->set($campo, 'Captura sin guardar');
            $formulario->call('solicitarLimpiezaRegistro')->assertSet('confirmarLimpiezaRegistro', true)
                ->call('cancelarLimpiezaRegistro')->assertSet($campo, 'Captura sin guardar')
                ->call('solicitarLimpiezaRegistro')->call('limpiarCamposRegistro')->assertHasNoErrors()
                ->assertSet($campo, '')->assertSet('registroTipo', $tipo)
                ->assertSet('modalCodResidente', $this->residenteEstable->cod_residente)
                ->assertSet('drawerPaso', 'register-form')->assertSet('mostrarSelectorModal', true)
                ->assertSet('confirmarLimpiezaRegistro', false)->assertDispatched('registro-campos-limpiados')
                ->call('cerrarSelectorRegistro')->assertSet('confirmarDescarte', false)
                ->assertSet('mostrarSelectorModal', false);
        }
        $this->formularioDolor()->set('dolorEva', '7')->call('limpiarCamposRegistro')->assertStatus(409);
    }

    public function test_cambio_de_residente_protege_capturas_del_selector_ademas_de_signos_y_dolor(): void
    {
        $this->actingAs($this->enfermero);
        Livewire::test(MisPacientes::class)
            ->call('seleccionarResidente', $this->residenteEstable->cod_residente)
            ->call('mostrarSelectorRegistro')->call('abrirFormularioRegistro', 'alimentacion')
            ->set('ingestaObservacion', 'Captura pendiente')
            ->call('seleccionarResidente', $this->residenteCritico->cod_residente)
            ->assertSet('confirmarDescarte', true)
            ->assertSet('modalCodResidente', $this->residenteEstable->cod_residente)
            ->call('cancelarDescarte')->assertSet('ingestaObservacion', 'Captura pendiente')
            ->call('cerrarSelectorRegistro')->call('descartarCambios')
            ->assertSet('mostrarSelectorModal', false);
    }

    public function test_limpieza_comun_revalida_permiso_y_no_actua_fuera_del_formulario(): void
    {
        $this->actingAs($this->enfermero);
        Livewire::test(MisPacientes::class)->call('solicitarLimpiezaRegistro')->assertForbidden();
        $formulario = $this->formularioDolor()->set('dolorEva', '7')->call('solicitarLimpiezaRegistro');
        \Spatie\Permission\Models\Role::findByName('ENFERMEROS')->revokePermissionTo('valoraciones_dolor.crear');
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $this->enfermero->unsetRelation('roles')->unsetRelation('permissions');
        $formulario->call('limpiarCamposRegistro')->assertForbidden();
    }

    public function test_formulario_fija_fecha_hora_del_servidor_y_conserva_el_momento_al_guardar(): void
    {
        $this->travelTo(today()->setTime(10, 17));
        $this->actingAs($this->enfermero);
        $fecha = now()->format('Y-m-d\TH:i');
        $formulario = $this->formularioSignos()->set('signoFC', '72')
            ->assertSet('signoFechaHora', $fecha)
            ->assertSee('Fecha y hora automáticas · No editables')
            ->assertDontSee('type="datetime-local"', false)
            ->assertDontSee('wire:model.live="signoFechaHora"', false);
        $this->travel(10)->minutes();
        $formulario->call('guardarSignos')->assertHasNoErrors()
            ->assertSet('drawerPaso', 'register-result');
        $this->assertDatabaseHas('signos_vitales', ['cod_residente' => $this->residenteEstable->cod_residente,
            'frecuencia_cardiaca' => 72, 'fecha_hora' => Carbon::parse($fecha)]);
    }

    public function test_fecha_hora_no_admite_manipulacion_del_cliente_ni_genera_registros(): void
    {
        $this->actingAs($this->enfermero);
        $signosAntes = SignoVital::query()->count();
        $alertasAntes = Alerta::query()->count();
        foreach ([-20, 20] as $minutos) {
            $formulario = $this->formularioSignos()->set('signoFC', '72');
            try {
                $formulario->set('signoFechaHora', now()->addMinutes($minutos)->format('Y-m-d\TH:i'));
                $this->fail('La fecha automática no puede modificarse desde el cliente.');
            } catch (\Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException $exception) {
                $this->assertStringContainsString('signoFechaHora', $exception->getMessage());
            }
        }
        $this->assertSame($signosAntes, SignoVital::query()->count());
        $this->assertSame($alertasAntes, Alerta::query()->count());
    }

    private function formularioHidratacionV2()
    {
        $this->formularioHidratacionClinica();
        return Livewire::test(MisPacientes::class)
            ->call('seleccionarResidente', $this->residenteEstable->cod_residente)
            ->call('mostrarSelectorRegistro')->call('abrirFormularioRegistro', 'hidratacion');
    }

    public function test_hidratacion_v2_captura_vacia_y_validacion_preservan_datos(): void
    {
        $vista = $this->formularioHidratacionV2()->assertSee('Volumen del aporte')->assertSet('hidratacionCantidad', '')
            ->assertSee('Fecha y hora automáticas')->assertDontSee('Cambio respecto al estado habitual')
            ->set('hidratacionTipo', 'Agua')->call('guardarHidratacion')->assertHasErrors('cantidad_ml')
            ->assertSet('drawerPaso', 'register-form')->assertSet('hidratacionTipo', 'Agua');
        foreach (['0', '-1', '10001', '250.5', 'NaN'] as $cantidad) {
            $vista->set('hidratacionCantidad', $cantidad)->call('guardarHidratacion')->assertHasErrors('cantidad_ml');
        }
        $this->assertDatabaseCount('registros_hidratacion', 0);
    }

    public function test_hidratacion_v2_guarda_opcionales_nulos_autoria_hora_y_resultado_reales(): void
    {
        $vista = $this->formularioHidratacionV2()->set('hidratacionCantidad', '250')
            ->set('hidratacionTipo', '   ')->set('hidratacionObservacion', '   ')
            ->call('guardarHidratacion')->assertHasNoErrors()->assertSet('drawerPaso', 'register-result')
            ->assertSee('Hidratación registrada')->assertSee('250.00 mL')->assertSee('Registrar otro aporte');
        $registro = \App\Models\RegistroHidratacion::sole();
        $this->assertNull($registro->tipo_liquido);
        $this->assertNull($registro->tolerancia);
        $this->assertNull($registro->observacion);
        $this->assertSame($this->personal->cod_personal, $registro->cod_personal);
        $this->assertTrue(now()->equalTo($registro->fecha_hora));
        $vista->call('guardarHidratacion')->assertForbidden();
        $this->assertDatabaseCount('registros_hidratacion', 1);
    }

    public function test_hidratacion_v2_continuidad_acumulado_jornada_y_historial_por_residente(): void
    {
        $vista = $this->formularioHidratacionV2();
        $service = app(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class);
        foreach ([150,250,200,350] as $cantidad) {
            $ultimo = $service->registrarHidratacion($this->residenteEstable->cod_residente, ['cantidad_ml' => $cantidad], $this->enfermero);
            $this->travel(1)->minutes();
        }
        $anulado = $service->registrarHidratacion($this->residenteEstable->cod_residente, ['cantidad_ml' => 500], $this->enfermero);
        $anulado->update(['estado' => 'ANULADO']);
        $service->registrarHidratacion($this->residenteCritico->cod_residente, ['cantidad_ml' => 500], $this->enfermero);
        $vista->call('cerrarSelectorRegistro')->call('mostrarSelectorRegistro')->call('abrirFormularioRegistro', 'hidratacion')
            ->assertSet('hidratacionContinuidad', ['aportes' => 4, 'acumulado' => 950.0])
            ->assertSet('hidratacionHistorial.0.codigo', $ultimo->getKey());
        $this->assertCount(4, $vista->get('hidratacionHistorial'));
    }

    public function test_hidratacion_v2_tolerancia_limites_texto_y_trim_backend(): void
    {
        $vista = $this->formularioHidratacionV2()->set('hidratacionCantidad', '1');
        foreach (['BUENA', 'VOMITO', 'MALA', 'inventado'] as $tolerancia) {
            $vista->set('hidratacionTolerancia', $tolerancia)->call('guardarHidratacion')->assertHasErrors('tolerancia');
        }
        $vista->set('hidratacionTolerancia', 'ADECUADA')->set('hidratacionTipo', str_repeat('a',61))
            ->set('hidratacionObservacion', str_repeat('b',5001))->call('guardarHidratacion')
            ->assertHasErrors(['tipo_liquido','observacion']);
        $vista->set('hidratacionCantidad', '10000')->set('hidratacionTipo', ' Agua ')->set('hidratacionObservacion', ' Aporte observado ')
            ->call('guardarHidratacion')->assertHasNoErrors();
        $this->assertDatabaseHas('registros_hidratacion', ['cantidad_ml' => 10000, 'tipo_liquido' => 'Agua', 'observacion' => 'Aporte observado']);
    }

    public function test_hidratacion_v2_limpiar_descartar_y_contexto_bloqueado(): void
    {
        $vista = $this->formularioHidratacionV2()->set('hidratacionCantidad','250')->set('hidratacionTipo','Agua')
            ->call('cerrarSelectorRegistro')->assertSet('confirmarDescarte', true)->assertSet('hidratacionCantidad','250')
            ->call('cancelarDescarte')->call('solicitarLimpiezaRegistro')->call('limpiarCamposRegistro')
            ->assertSet('hidratacionCantidad', '')->assertSet('hidratacionTipo','')
            ->assertSet('hidratacionResidenteContexto', $this->residenteEstable->cod_residente)
            ->assertDispatched('registro-campos-limpiados');
        $vista->set('hidratacionCantidad','350')->set('modalCodResidente', $this->residenteCritico->cod_residente)
            ->call('guardarHidratacion')->assertForbidden();
        $this->assertDatabaseCount('registros_hidratacion',0);
    }

    public function test_hidratacion_v2_servicio_canonico_no_usa_via_ni_campos_ajenos(): void
    {
        $this->formularioHidratacionV2();
        $service = app(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class);
        $registro = $service->registrar($this->residenteEstable->cod_residente, ['tipo' => 'HIDRATACION', 'cantidad_ml' => 250], $this->enfermero);
        $this->assertSame('250.00', $registro->cantidad_ml);
        $this->assertArrayNotHasKey('via', $registro->getAttributes());
        foreach (['via','cod_personal','fecha_hora','cod_jornada'] as $campo) {
            try {
                $service->registrarHidratacion($this->residenteEstable->cod_residente, ['cantidad_ml' => 350, $campo => 'manipulado'], $this->enfermero);
                $this->fail('El servicio aceptó un campo ajeno.');
            } catch (\Illuminate\Validation\ValidationException $exception) {
                $this->assertArrayHasKey('datos', $exception->errors());
            }
        }
        $this->assertDatabaseCount('registros_hidratacion',1);
    }

    public function test_hidratacion_v2_fallo_real_db_preserva_formulario_y_no_finge_exito(): void
    {
        $vista = $this->formularioHidratacionV2()->set('hidratacionCantidad','250')->set('hidratacionTipo','Agua');
        \Illuminate\Support\Facades\DB::unprepared("CREATE TRIGGER hydration_test_failure BEFORE INSERT ON registros_hidratacion BEGIN SELECT RAISE(ABORT, 'controlled hydration failure'); END");
        try {
            $vista->call('guardarHidratacion')->assertHasErrors('hidratacion_guardado')
                ->assertSet('drawerPaso','register-form')->assertSet('hidratacionCantidad','250')
                ->assertSet('hidratacionTipo','Agua')->assertSet('hidratacionResultado',[])
                ->assertNotDispatched('cuidado-registrado');
            $this->assertDatabaseCount('registros_hidratacion',0);
        } finally {
            \Illuminate\Support\Facades\DB::unprepared('DROP TRIGGER hydration_test_failure');
        }
        $vista->call('guardarHidratacion')->assertHasNoErrors()->assertSet('drawerPaso','register-result');
    }

    public function test_hidratacion_v2_reautoriza_permiso_cuenta_y_asignacion_al_guardar(): void
    {
        $vista = $this->formularioHidratacionV2()->set('hidratacionCantidad','250');
        \Spatie\Permission\Models\Role::findByName('ENFERMEROS')->revokePermissionTo('registros_hidratacion.crear');
        $this->enfermero->unsetRelation('roles')->unsetRelation('permissions');
        $vista->call('guardarHidratacion')->assertForbidden();
        $this->assertDatabaseCount('registros_hidratacion',0);
    }

    public function test_hidratacion_v2_no_expone_historial_sin_permiso_de_lectura(): void
    {
        $this->formularioHidratacionV2();
        app(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class)
            ->registrarHidratacion($this->residenteEstable->cod_residente, ['cantidad_ml'=>250], $this->enfermero);
        \Spatie\Permission\Models\Role::findByName('ENFERMEROS')->revokePermissionTo('registros_hidratacion.ver');
        $this->enfermero->unsetRelation('roles')->unsetRelation('permissions');
        Livewire::withQueryParams([])->test(MisPacientes::class)->call('seleccionarResidente', $this->residenteEstable->cod_residente)
            ->call('mostrarSelectorRegistro')->call('abrirFormularioRegistro', 'hidratacion')
            ->assertSet('hidratacionHistorial',[])->assertSet('hidratacionContinuidad',[])
            ->assertSee('Sin permiso para consultar los aportes anteriores.')->assertDontSee('Historial reciente');
    }

    public function test_hidratacion_v2_servicio_deniega_cuenta_inactiva_personal_sin_jornada_y_residente_fuera_de_asignacion(): void
    {
        $this->formularioHidratacionV2();
        $service=app(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class);
        $denegar=function () use ($service) {
            try {
                $service->registrarHidratacion($this->residenteEstable->cod_residente,['cantidad_ml'=>250],$this->enfermero);
                $this->fail('Se aceptó un contexto no autorizado.');
            } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
                $this->assertSame(403,$exception->getStatusCode());
            }
            $this->assertDatabaseCount('registros_hidratacion',0);
        };
        $this->enfermero->estado='INACTIVO'; $denegar(); $this->enfermero->estado='ACTIVO';
        $this->enfermero->personal->estado='INACTIVO'; $denegar(); $this->enfermero->personal->estado='ACTIVO';
        AsignacionResidenteJornada::where('cod_residente',$this->residenteEstable->cod_residente)->update(['estado'=>'FINALIZADA']);
        $denegar();
        AsignacionResidenteJornada::where('cod_residente',$this->residenteEstable->cod_residente)->update(['estado'=>'ACTIVA']);
        Jornada::where('cod_jornada','JOR_MIS_PACIENTES')->update(['estado'=>'CERRADA']);
        $denegar();
    }

    public function test_hidratacion_v2_acumulado_no_trunca_decimales_ni_incluye_futuros_otras_jornadas_o_anulados(): void
    {
        $vista=$this->formularioHidratacionV2();
        $row=app(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class)
            ->registrarHidratacion($this->residenteEstable->cod_residente,['cantidad_ml'=>250],$this->enfermero);
        // La captura nueva es entera; aportes decimales existentes desde Ingesta conservan su precisión.
        $row->update(['cantidad_ml'=>'250.25']);
        $otra=Jornada::findOrFail('JOR_MIS_PACIENTES')->replicate();$otra->cod_jornada='JOR_HID_OTRA';$otra->save();
        foreach ([['cod_jornada'=>$otra->getKey()],['fecha_hora'=>now()->addDay()],['estado'=>'ANULADO']] as $cambio) {
            $copia=$row->replicate();$copia->cod_hidratacion='HID_'.\Illuminate\Support\Str::random(10);$copia->fill($cambio)->save();
        }
        $vista->call('cerrarSelectorRegistro')->call('mostrarSelectorRegistro')->call('abrirFormularioRegistro','hidratacion')
            ->assertSet('hidratacionContinuidad',['aportes'=>1,'acumulado'=>250.25])->assertSee('250,25');
        $this->assertCount(2,$vista->get('hidratacionHistorial')); // La otra jornada permanece en el historial longitudinal.
    }

    public function test_eliminacion_v2_minimos_cero_y_booleanos_nullable_se_distinguen(): void
    {
        $this->actingAs($this->enfermero); $this->formularioEliminacion();
        $service=app(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class);
        foreach ([null,false,true] as $value) {
            $row=$service->registrarEliminacion($this->residenteEstable->cod_residente,['tipo_eliminacion'=>'INTESTINAL',
                'presencia_sangre'=>$value,'presencia_moco'=>$value,'molestia_eliminacion'=>$value],$this->enfermero);
            $this->assertSame($value,$row->fresh()->presencia_sangre);
            $this->assertSame($value,$row->fresh()->presencia_moco);
            $this->assertSame($value,$row->fresh()->molestia_eliminacion);
        }
        $zero=$service->registrarEliminacion($this->residenteEstable->cod_residente,['tipo_eliminacion'=>'URINARIA','volumen_ml'=>'0.00'],$this->enfermero);
        $empty=$service->registrarEliminacion($this->residenteEstable->cod_residente,['tipo_eliminacion'=>'URINARIA'],$this->enfermero);
        $this->assertSame('0.00',$zero->fresh()->volumen_ml); $this->assertNull($empty->fresh()->volumen_ml);
        $this->assertNull($zero->cantidad); $this->assertNull($zero->caracteristica);
    }

    public function test_eliminacion_v2_validacion_condicional_catalogos_y_escala_completa(): void
    {
        $this->actingAs($this->enfermero); $this->formularioEliminacion();
        $service=app(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class);
        $cases=[];
        foreach (\App\Models\RegistroEliminacion::OPCIONES as $field=>$options) $cases[]=[$field,in_array($field,['color_heces','esfuerzo_defecacion'])?'INTESTINAL':'URINARIA','MANIPULADO'];
        foreach (['-1','1000000','1.001','NaN'] as $value) $cases[]=['volumen_ml','URINARIA',$value];
        foreach ([0,8,'abc'] as $value) $cases[]=['tipo_bristol','INTESTINAL',$value];
        foreach (\App\Models\RegistroEliminacion::URINARIOS as $field) $cases[]=[$field,'INTESTINAL',$field==='volumen_ml'?0:'VALOR'];
        foreach (\App\Models\RegistroEliminacion::INTESTINALES as $field) $cases[]=[$field,'URINARIA',$field==='presencia_moco'?false:1];
        foreach ($cases as [$field,$type,$value]) {
            $before=\App\Models\RegistroEliminacion::count();
            try { $service->registrarEliminacion($this->residenteEstable->cod_residente,['tipo_eliminacion'=>$type,$field=>$value],$this->enfermero); $this->fail('Aceptó '.$field); }
            catch (\Illuminate\Validation\ValidationException $e) { $this->assertArrayHasKey($field,$e->errors()); }
            $this->assertSame($before,\App\Models\RegistroEliminacion::count());
        }
        foreach (range(1,7) as $bristol) {
            $row=$service->registrarEliminacion($this->residenteEstable->cod_residente,['tipo_eliminacion'=>'INTESTINAL','tipo_bristol'=>$bristol,'color_heces'=>'MARRON','esfuerzo_defecacion'=>'SIN_ESFUERZO'],$this->enfermero);
            $this->assertSame($bristol,$row->fresh()->tipo_bristol);
        }
        $full=$service->registrarEliminacion($this->residenteEstable->cod_residente,['tipo_eliminacion'=>'URINARIA','cantidad_cualitativa'=>'HABITUAL',
            'volumen_ml'=>'350.00','color_orina'=>'AMARILLO_CLARO','aspecto_orina'=>'CLARO','olor_orina'=>'HABITUAL','tipo_miccion'=>'ESPONTANEA',
            'presencia_sangre'=>false,'molestia_eliminacion'=>false,'continencia'=>'CONTINENTE'],$this->enfermero);
        $this->assertSame('350.00',$full->fresh()->volumen_ml);
        $this->assertSame('AMARILLO_CLARO',$full->color_orina);
        $this->assertSame(0,\App\Models\Alerta::where('tipo','ELIMINACION')->count());
    }

    public function test_eliminacion_v2_descripcion_trim_limites_y_campos_ajenos(): void
    {
        $this->actingAs($this->enfermero); $this->formularioEliminacion();
        $service=app(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class);
        foreach ([false,null] as $value) {
            $row=$service->registrarEliminacion($this->residenteEstable->cod_residente,['tipo_eliminacion'=>'URINARIA','molestia_eliminacion'=>$value,'descripcion_molestia'=>'Dato anterior'],$this->enfermero);
            $this->assertNull($row->descripcion_molestia);
        }
        $row=$service->registrarEliminacion($this->residenteEstable->cod_residente,['tipo_eliminacion'=>'URINARIA','molestia_eliminacion'=>true,'descripcion_molestia'=>'  Molestia referida  ','observacion'=>'  Asistencia  '],$this->enfermero);
        $this->assertSame('Molestia referida',$row->descripcion_molestia);$this->assertSame('Asistencia',$row->observacion);
        foreach (['descripcion_molestia'=>str_repeat('a',251),'observacion'=>str_repeat('a',5001),'cantidad'=>'350','cod_personal'=>'OTRO','fecha_hora'=>'2020-01-01','cod_jornada'=>'OTRA','via'=>'ORAL'] as $field=>$value) {
            try {$service->registrarEliminacion($this->residenteEstable->cod_residente,['tipo_eliminacion'=>'URINARIA','molestia_eliminacion'=>true,$field=>$value],$this->enfermero);$this->fail('Aceptó '.$field);}
            catch (\Illuminate\Validation\ValidationException $e) {$this->assertNotEmpty($e->errors());}
        }
    }

    public function test_eliminacion_v2_historia_legacy_continuidad_y_filtros_preservan_datos(): void
    {
        $this->actingAs($this->enfermero); $this->formularioEliminacion();
        $legacy=\App\Models\RegistroEliminacion::create(['cod_eliminacion'=>'ELI_OLD','cod_residente'=>$this->residenteEstable->cod_residente,'cod_personal'=>$this->personal->cod_personal,
            'cod_jornada'=>'JOR_MIS_PACIENTES','fecha_hora'=>now()->subMinutes(20),'tipo_eliminacion'=>'URINARIA','cantidad'=>'350','caracteristica'=>'Amarillo claro','continencia'=>'CONTINENTE','estado'=>'VIGENTE']);
        $service=app(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class);
        $service->registrarEliminacion($this->residenteEstable->cod_residente,['tipo_eliminacion'=>'INTESTINAL','tipo_bristol'=>4],$this->enfermero);
        $future=$legacy->replicate()->fill(['cod_eliminacion'=>'ELI_FUT','fecha_hora'=>now()->addDay()]);$future->save();
        $other=$legacy->replicate()->fill(['cod_eliminacion'=>'ELI_OTHER','cod_residente'=>$this->residenteCritico->cod_residente]);$other->save();
        $anulado=$legacy->replicate()->fill(['cod_eliminacion'=>'ELI_ANU','estado'=>'ANULADO']);$anulado->save();
        $this->formularioEliminacion()->assertSet('elimContinuidad.URINARIA',1)->assertSet('elimContinuidad.INTESTINAL',1)
            ->assertSet('elimHistorial',fn($rows)=>count($rows)===2 && $rows[1]['legacy'] && $rows[1]['campos'][1]['valor']==='350');
        $this->assertSame('350',$legacy->fresh()->cantidad);$this->assertNull($legacy->fresh()->volumen_ml);
        $this->assertTrue($legacy->resumenOperacional()['legacy']);
    }

    public function test_eliminacion_v2_resultado_replay_limpiar_y_permiso_revocado(): void
    {
        $this->actingAs($this->enfermero);
        $form=$this->formularioEliminacion()->call('solicitarTipoEliminacion','URINARIA')->set('elimDatos.volumen_ml','0')
            ->call('solicitarLimpiezaRegistro')->call('limpiarCamposRegistro')->assertSet('elimTipo','')->assertSet('elimDatos.volumen_ml','')
            ->call('solicitarTipoEliminacion','URINARIA')->call('guardarEliminacion')->assertHasNoErrors()->assertSet('drawerPaso','register-result')
            ->assertSee('Eliminación registrada')->call('guardarEliminacion')->assertStatus(403);
        $this->assertDatabaseCount('registros_eliminacion',1);
        $this->formularioEliminacion()->call('solicitarTipoEliminacion','URINARIA')->set('elimDatos.observacion','Conservar')
            ->call('cerrarSelectorRegistro')->assertSet('confirmarDescarte',true)->call('cancelarDescarte')->assertSet('elimDatos.observacion','Conservar');
        $form=$this->formularioEliminacion()->call('solicitarTipoEliminacion','URINARIA');
        \Spatie\Permission\Models\Role::findByName('ENFERMEROS')->revokePermissionTo('registros_eliminacion.crear');
        $form->call('guardarEliminacion')->assertStatus(403);$this->assertDatabaseCount('registros_eliminacion',1);
    }

    public function test_eliminacion_v2_migracion_aditiva_rollback_y_reaplicar_conservan_historia(): void
    {
        $this->actingAs($this->enfermero);$this->formularioEliminacion();
        $columns=['cantidad_cualitativa','volumen_ml','color_orina','aspecto_orina','olor_orina','tipo_miccion','tipo_bristol','color_heces','esfuerzo_defecacion','presencia_sangre','presencia_moco','molestia_eliminacion','descripcion_molestia'];
        $schema=\Illuminate\Support\Facades\Schema::getColumns('registros_eliminacion');
        foreach ($columns as $name) {$column=collect($schema)->firstWhere('name',$name);$this->assertNotNull($column);$this->assertTrue($column['nullable']);}
        $row=\App\Models\RegistroEliminacion::create(['cod_eliminacion'=>'ELI_OLD_SCHEMA','cod_residente'=>$this->residenteEstable->cod_residente,
            'cod_personal'=>$this->personal->cod_personal,'cod_jornada'=>'JOR_MIS_PACIENTES','fecha_hora'=>now(),'tipo_eliminacion'=>'URINARIA',
            'cantidad'=>'350','caracteristica'=>'Amarillo claro','estado'=>'VIGENTE']);
        $tables=\Illuminate\Support\Facades\Schema::getTableListing();
        $migration=require database_path('migrations/2026_10_09_000200_extend_registros_eliminacion_v2.php');
        $migration->down();
        $this->assertSame(count($schema)-13,count(\Illuminate\Support\Facades\Schema::getColumns('registros_eliminacion')));
        $this->assertDatabaseHas('registros_eliminacion',['cod_eliminacion'=>$row->cod_eliminacion,'cantidad'=>'350','caracteristica'=>'Amarillo claro']);
        $migration->up();$this->assertSame($tables,\Illuminate\Support\Facades\Schema::getTableListing());
        $this->assertNull($row->fresh()->volumen_ml);$this->assertSame(count($schema),count(\Illuminate\Support\Facades\Schema::getColumns('registros_eliminacion')));
    }

    public function test_eliminacion_v2_fallo_real_db_preserva_captura_y_no_finge_exito(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {$this->markTestSkipped('Trigger de fallo controlado SQLite; QA de timeout PostgreSQL se ejecuta separadamente.');}
        $this->actingAs($this->enfermero);$form=$this->formularioEliminacion()->call('solicitarTipoEliminacion','URINARIA')->set('elimDatos.volumen_ml','350');
        \Illuminate\Support\Facades\DB::unprepared("CREATE TRIGGER eliminacion_qa_failure BEFORE INSERT ON registros_eliminacion BEGIN SELECT RAISE(ABORT, 'qa insert failure'); END");
        try {$form->call('guardarEliminacion')->assertHasErrors('eliminacion_guardado')->assertSet('elimDatos.volumen_ml','350')
            ->assertSet('drawerPaso','register-form')->assertSet('elimResultado',[]);$this->assertDatabaseCount('registros_eliminacion',0);}
        finally {\Illuminate\Support\Facades\DB::unprepared('DROP TRIGGER eliminacion_qa_failure');}
        $form->call('guardarEliminacion')->assertHasNoErrors()->assertSet('drawerPaso','register-result');$this->assertDatabaseCount('registros_eliminacion',1);
    }

    public function test_eliminacion_v2_autorizacion_personal_jornada_y_roles_no_conceden_escritura(): void
    {
        $this->actingAs($this->enfermero);$this->formularioEliminacion();
        $service=app(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class);
        foreach (['cuenta','personal','jornada'] as $case) {
            if ($case==='cuenta') $this->enfermero->update(['estado'=>'INACTIVO']);
            if ($case==='personal') $this->personal->update(['estado'=>'INACTIVO']);
            if ($case==='jornada') \App\Models\Jornada::where('cod_jornada','JOR_MIS_PACIENTES')->update(['estado'=>'CERRADA']);
            try {$service->registrarEliminacion($this->residenteEstable->cod_residente,['tipo_eliminacion'=>'URINARIA'],$this->enfermero->fresh());$this->fail('Aceptó '.$case);}
            catch (\Symfony\Component\HttpKernel\Exception\HttpException|\Illuminate\Validation\ValidationException $e) {$this->assertNotEmpty($e->getMessage());}
            $this->enfermero->update(['estado'=>'ACTIVO']);$this->personal->update(['estado'=>'ACTIVO']);
            \App\Models\Jornada::where('cod_jornada','JOR_MIS_PACIENTES')->update(['estado'=>'ABIERTA']);
        }
        foreach (['ADMINISTRADOR','SUPERADMINISTRADOR'] as $role) {
            $account=\App\Models\User::factory()->create(['estado'=>'ACTIVO']);$account->assignRole($role);$account->givePermissionTo('registros_eliminacion.crear');
            try {$service->registrarEliminacion($this->residenteEstable->cod_residente,['tipo_eliminacion'=>'URINARIA'],$account);$this->fail('Escritura automática '.$role);}
            catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {$this->assertSame(403,$e->getStatusCode());}
        }
        $this->assertDatabaseCount('registros_eliminacion',0);
    }

    public function test_eliminacion_v2_ultimo_global_por_tipo_y_historial_sin_permiso(): void
    {
        $this->actingAs($this->enfermero);$this->formularioEliminacion();$service=app(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class);
        $old=$service->registrarEliminacion($this->residenteEstable->cod_residente,['tipo_eliminacion'=>'URINARIA','volumen_ml'=>'0'],$this->enfermero);
        $old->update(['fecha_hora'=>now()->subMinutes(90)]);
        for ($i=0;$i<41;$i++) $service->registrarEliminacion($this->residenteEstable->cod_residente,['tipo_eliminacion'=>'INTESTINAL'],$this->enfermero);
        $this->formularioEliminacion()->assertSet('elimHistorial',fn($rows)=>count($rows)===40)
            ->assertSet('elimUltimos.URINARIA.codigo',$old->cod_eliminacion)->assertSet('elimContinuidad.URINARIA',1)->assertSet('elimContinuidad.INTESTINAL',41);
        \Spatie\Permission\Models\Role::findByName('ENFERMEROS')->revokePermissionTo('registros_eliminacion.ver');
        Livewire::withQueryParams([])->test(MisPacientes::class)->call('seleccionarResidente',$this->residenteEstable->cod_residente)
            ->call('mostrarSelectorRegistro')->call('abrirFormularioRegistro','eliminacion')
            ->assertSet('elimHistorial',[])->assertSet('elimUltimos',[])->assertSet('elimContinuidad',[])->assertDontSee('Ver historial');
    }

    public function test_movilidad_v2_persiste_todos_los_campos_y_contexto_sin_incidentes(): void
    {
        $this->travelTo(today()->setTime(10, 0)); $this->actingAs($this->enfermero);
        $ui = $this->formularioMovilidad()->set('movMarcha', 'ASISTIDA')->set('movTraslado', 'AYUDA_UNA_PERSONA')
            ->set('movTipoApoyo', 'PARCIAL')->set('movEquilibrio', 'ESTABLE')->set('movFatiga', 'LEVE')
            ->set('movRiesgoCaida', 'BAJO')->set('movDatos', ['motivo_registro' => 'CONTROL_DIARIO',
                'actividad_realizada' => 'CAMINAR_PASILLO', 'dispositivo' => 'ANDADOR', 'distancia_metros' => '10.25',
                'tolerancia_movilidad' => 'BUENA', 'cambio_habitual' => 'SIN_CAMBIOS', 'dolor_movilidad' => false,
                'mareo' => false, 'disnea' => false, 'debilidad' => false])->call('guardarCuidado')
            ->assertHasNoErrors()->assertSet('drawerPaso', 'register-result')->assertSee('Movilidad registrada');
        $record = \App\Models\RegistroMovilidad::sole();
        $this->assertSame('ANDADOR', $record->dispositivo); $this->assertSame('10.25', $record->distancia_metros);
        $this->assertSame('JOR_MIS_PACIENTES', $record->cod_jornada);
        $this->assertSame($this->personal->cod_personal, $record->cod_personal);
        $this->assertTrue($record->fecha_hora->equalTo(now())); $this->assertSame('VIGENTE', $record->estado);
        $this->assertSame('CONTROL_DIARIO', $record->motivo_registro); $this->assertSame('SIN_CAMBIOS', $record->cambio_habitual);
        $this->assertSame('BUENA', $record->tolerancia_movilidad); $this->assertFalse($record->dolor_movilidad);
        $this->assertDatabaseCount('incidentes', 0); $this->assertDatabaseCount('alertas', 1);
        $ui->call('guardarCuidado')->assertStatus(403); $this->assertDatabaseCount('registros_movilidad', 1);
    }

    public function test_movilidad_v2_rechaza_todos_los_catalogos_y_payload_server_owned(): void
    {
        $this->travelTo(today()->setTime(10,0)); $this->actingAs($this->enfermero); $this->formularioMovilidad();
        $service = app(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class);
        foreach (array_keys(\App\Models\RegistroMovilidad::catalogos()) as $field) {
            try { $service->registrarMovilidad($this->residenteEstable->cod_residente, array_merge(['marcha'=>'INDEPENDIENTE'],[$field=>'INVENTADO']),$this->enfermero); $this->fail($field); }
            catch (\Illuminate\Validation\ValidationException $e) { $this->assertArrayHasKey($field,$e->errors()); }
        }
        foreach (['cod_residente','cod_personal','cod_jornada','fecha_hora','estado','cod_atencion','campo_inventado'] as $field) {
            try { $service->registrarMovilidad($this->residenteEstable->cod_residente,['marcha'=>'INDEPENDIENTE',$field=>'FALSO'],$this->enfermero);$this->fail($field); }
            catch (\Illuminate\Validation\ValidationException $e) { $this->assertArrayHasKey('datos',$e->errors()); }
        }
        $this->assertDatabaseCount('registros_movilidad',0);
    }

    public function test_movilidad_v2_distancia_decimal_triestados_y_dispositivo_ninguno(): void
    {
        $this->travelTo(today()->setTime(10,0)); $this->actingAs($this->enfermero); $this->formularioMovilidad();
        $service = app(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class);
        foreach ([null,false,true] as $value) {
            $record=$service->registrarMovilidad($this->residenteEstable->cod_residente,['marcha'=>'INDEPENDIENTE','dispositivo'=>'NINGUNO',
                'dolor_movilidad'=>$value,'mareo'=>$value,'disnea'=>$value,'debilidad'=>$value],$this->enfermero)->fresh();
            foreach (['dolor_movilidad','mareo','disnea','debilidad'] as $field) $this->assertSame($value,$record->{$field});
            $this->assertSame('NINGUNO',$record->dispositivo); $this->assertNull($record->distancia_metros);
        }
        foreach ([null,0,5,'10.25'] as $value) {
            $record=$service->registrarMovilidad($this->residenteEstable->cod_residente,['marcha'=>'INDEPENDIENTE',
                'actividad_realizada'=>'CAMINAR_PASILLO','distancia_metros'=>$value],$this->enfermero)->fresh();
            $this->assertSame($value===null?null:number_format((float)$value,2,'.',''),$record->distancia_metros);
        }
        foreach ([-1,100000,'1.234','10 m'] as $value) {
            try {$service->registrarMovilidad($this->residenteEstable->cod_residente,['marcha'=>'INDEPENDIENTE','actividad_realizada'=>'CAMINAR_PASILLO','distancia_metros'=>$value],$this->enfermero);$this->fail('Distancia inválida');}
            catch (\Illuminate\Validation\ValidationException $e) {$this->assertArrayHasKey('distancia_metros',$e->errors());}
        }
        try {$service->registrarMovilidad($this->residenteEstable->cod_residente,['marcha'=>'ENCAMADO','actividad_realizada'=>'CAMBIO_POSTURAL','distancia_metros'=>0],$this->enfermero);$this->fail('Distancia incompatible');}
        catch (\Illuminate\Validation\ValidationException $e) {$this->assertArrayHasKey('distancia_metros',$e->errors());}
        $this->assertDatabaseCount('registros_movilidad',7);
    }

    public function test_movilidad_otro_exige_detalles_y_preserva_captura_sin_persistir(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);
        $ui = $this->formularioMovilidad()->set('movMarcha', 'ASISTIDA')
            ->set('movDatos.motivo_registro', 'OTRO')->set('movDatos.dispositivo', 'OTRO')
            ->set('movDatos.motivo_otro', '   ')->set('movObservacion', 'Datos conservados')
            ->call('guardarCuidado')->assertHasErrors(['motivo_otro', 'dispositivo_otro'])
            ->assertSet('drawerPaso', 'register-form')->assertSet('movObservacion', 'Datos conservados');
        $this->assertDatabaseCount('registros_movilidad', 0);

        $ui->set('movDatos.motivo_otro', '  Revisión tras descanso  ')
            ->set('movDatos.dispositivo_otro', '  Apoyo de antebrazo  ')
            ->call('guardarCuidado')->assertHasNoErrors()->assertSet('drawerPaso', 'register-result');
        $record = \App\Models\RegistroMovilidad::sole();
        $this->assertSame('OTRO', $record->motivo_registro);
        $this->assertSame('OTRO', $record->dispositivo);
        $this->assertSame("Otro motivo: Revisión tras descanso\nOtro dispositivo: Apoyo de antebrazo\nDatos conservados", $record->observacion);
        $this->assertArrayNotHasKey('motivo_otro', $record->getAttributes());
        $this->assertArrayNotHasKey('dispositivo_otro', $record->getAttributes());
    }

    public function test_movilidad_otro_backend_rechaza_detalles_incompatibles_malformados_y_excesivos(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);
        $this->formularioMovilidad();
        $service = app(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class);
        foreach ([
            [['motivo_registro' => 'OTRO'], 'motivo_otro'],
            [['dispositivo' => 'OTRO'], 'dispositivo_otro'],
            [['motivo_registro' => 'CONTROL_DIARIO', 'motivo_otro' => 'Residual'], 'motivo_otro'],
            [['dispositivo' => 'BASTON', 'dispositivo_otro' => 'Residual'], 'dispositivo_otro'],
            [['motivo_registro' => 'OTRO', 'motivo_otro' => ['No es texto']], 'motivo_otro'],
            [['dispositivo' => 'OTRO', 'dispositivo_otro' => str_repeat('x', 501)], 'dispositivo_otro'],
            [['motivo_registro' => 'OTRO', 'motivo_otro' => 'Detalle', 'observacion' => str_repeat('x', 5000)], 'observacion'],
            [['dolor_movilidad' => 'quizás'], 'dolor_movilidad'],
            [['observacion' => str_repeat('x', 5001)], 'observacion'],
        ] as [$datos, $error]) {
            try {
                $service->registrarMovilidad($this->residenteEstable->cod_residente, ['marcha' => 'ASISTIDA'] + $datos, $this->enfermero);
                $this->fail('Debe rechazar '.$error);
            } catch (\Illuminate\Validation\ValidationException $exception) {
                $this->assertArrayHasKey($error, $exception->errors());
            }
        }
        $this->assertDatabaseCount('registros_movilidad', 0);
    }

    public function test_movilidad_al_cambiar_otro_o_limpiar_no_quedan_detalles_residuales(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);
        $this->formularioMovilidad()->set('movDatos.motivo_registro', 'OTRO')
            ->set('movDatos.motivo_otro', 'Detalle')->set('movDatos.motivo_registro', 'CONTROL_DIARIO')
            ->assertSet('movDatos.motivo_otro', '')->set('movDatos.dispositivo', 'OTRO')
            ->set('movDatos.dispositivo_otro', 'Detalle')->set('movDatos.dispositivo', 'ANDADOR')
            ->assertSet('movDatos.dispositivo_otro', '')
            ->set('movDatos.motivo_registro', 'OTRO')->set('movDatos.motivo_otro', 'Detalle')
            ->set('movDatos.dispositivo', 'OTRO')->set('movDatos.dispositivo_otro', 'Detalle')
            ->call('solicitarLimpiezaRegistro')->assertSet('confirmarLimpiezaRegistro', true)
            ->call('limpiarCamposRegistro')->assertStatus(200)->assertSet('movDatos.motivo_otro', '')
            ->assertSet('movDatos.dispositivo_otro', '')->assertSet('movDatos.dispositivo', '')
            ->assertSet('movResidenteContexto', $this->residenteEstable->cod_residente);
    }

    public function test_movilidad_otro_en_consumidor_generico_exige_descripcion_y_la_guarda(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $this->actingAs($this->enfermero);
        $this->formularioMovilidad();
        Livewire::test(\App\Frontend\Livewire\Enfermeria\Cuidados\RegistrosEnfermeria::class, ['codResidente' => $this->residenteEstable->cod_residente])
            ->set('tipo', 'MOVILIDAD')->set('subtipo', 'ASISTIDA')->set('ayudaTecnica', 'OTRO')
            ->call('guardarCuidado')->assertHasErrors('dispositivoOtro')
            ->set('dispositivoOtro', 'Apoyo sintético')->set('ayudaTecnica', 'ANDADOR')
            ->assertSet('dispositivoOtro', '')->set('ayudaTecnica', 'OTRO')
            ->set('dispositivoOtro', 'Apoyo sintético')->call('guardarCuidado')->assertHasNoErrors()
            ->assertSet('dispositivoOtro', '');
        $record = \App\Models\RegistroMovilidad::sole();
        $this->assertSame('OTRO', $record->dispositivo);
        $this->assertSame('Otro dispositivo: Apoyo sintético', $record->observacion);
    }

    public function test_movilidad_v2_historial_residente_jornada_y_continuidad(): void
    {
        $this->travelTo(today()->setTime(10,0));$this->actingAs($this->enfermero);$ui=$this->formularioMovilidad();
        $ui->assertSet('movHistorial',[])->assertSee('Sin registros previos de movilidad.');
        $service=app(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class);
        $older=$service->registrarMovilidad($this->residenteEstable->cod_residente,['marcha'=>'ASISTIDA','fatiga'=>'LEVE'],$this->enfermero);
        $this->travel(1)->minutes();
        $latest=$service->registrarMovilidad($this->residenteEstable->cod_residente,['marcha'=>'INDEPENDIENTE','actividad_realizada'=>'CAMINAR_HABITACION','distancia_metros'=>5],$this->enfermero);
        $service->registrarMovilidad($this->residenteCritico->cod_residente,['marcha'=>'ENCAMADO'],$this->enfermero);
        $ui=$this->formularioMovilidad()->assertSet('movContinuidad',['registros'=>2,'deambulacion'=>1,'fatiga'=>1]);
        $this->assertSame([$latest->cod_movilidad,$older->cod_movilidad],array_column($ui->get('movHistorial'),'codigo'));
        $ui->set('movMarcha','ASISTIDA')->call('cerrarSelectorRegistro')->assertSet('confirmarDescarte',true)
            ->call('cancelarDescarte')->assertSet('movMarcha','ASISTIDA')->call('solicitarLimpiezaRegistro')
            ->call('limpiarCamposRegistro')->assertSet('movMarcha','')->assertSet('movResidenteContexto',$this->residenteEstable->cod_residente);
    }

    public function test_movilidad_v2_generic_es_canonico_y_no_acepta_spoofing(): void
    {
        $this->travelTo(today()->setTime(10,0));$this->actingAs($this->enfermero);$this->formularioMovilidad();
        $service=app(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class);
        $record=$service->registrar($this->residenteEstable->cod_residente,['tipo'=>'MOVILIDAD','subtipo'=>'ASISTIDA','ayuda_tecnica'=>'ANDADOR','nivel_ayuda'=>'PARCIAL'],$this->enfermero);
        $this->assertSame('ANDADOR',$record->dispositivo);$this->assertSame('ASISTIDA',$record->marcha);
        try {$service->registrar($this->residenteEstable->cod_residente,['tipo'=>'MOVILIDAD','subtipo'=>'ASISTIDA','cod_personal'=>'OTRO'],$this->enfermero);$this->fail('Aceptó autor');}
        catch (\Illuminate\Validation\ValidationException $e) {$this->assertArrayHasKey('datos',$e->errors());}
        $this->assertDatabaseCount('registros_movilidad',1);
        $painCount = \App\Models\ValoracionDolor::count();
        Livewire::test(\App\Frontend\Livewire\Enfermeria\Cuidados\RegistrosEnfermeria::class, ['codResidente' => $this->residenteEstable->cod_residente])
            ->set('tipo', 'MOVILIDAD')->set('subtipo', 'ASISTIDA')->set('ayudaTecnica', 'BASTON')
            ->set('dolor', 5)->call('guardarCuidado')->assertHasNoErrors();
        $this->assertDatabaseCount('registros_movilidad', 2);
        $this->assertDatabaseHas('registros_movilidad', ['cod_residente' => $this->residenteEstable->cod_residente, 'dispositivo' => 'BASTON', 'marcha' => 'ASISTIDA']);
        $this->assertSame($painCount, \App\Models\ValoracionDolor::count());
    }
    public function test_movilidad_v2_deniega_cuenta_personal_inactivos_sin_jornada_y_roles_no_competentes(): void
    {
        config(['remembermind.superadmin_clinical_write' => false]);
        $this->travelTo(today()->setTime(10,0));$this->actingAs($this->enfermero);$this->formularioMovilidad();
        $service=app(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class);
        foreach (['cuenta','personal','jornada','ADMINISTRADOR','SUPERADMINISTRADOR'] as $condition) {
            \Illuminate\Support\Facades\DB::beginTransaction();
            try {
                $user=$this->enfermero->fresh();
                if ($condition==='cuenta') $user->update(['estado'=>'INACTIVO']);
                elseif ($condition==='personal') $user->personal->update(['estado'=>'INACTIVO']);
                elseif ($condition==='jornada') \App\Models\AsignacionPersonal::where('cod_personal',$this->personal->cod_personal)->update(['estado'=>'FINALIZADA']);
                else $user->syncRoles([$condition]);
                $service->registrarMovilidad($this->residenteEstable->cod_residente,['marcha'=>'INDEPENDIENTE'],$user);
                $this->fail('Se aceptó '.$condition);
            } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {$this->assertSame(403,$e->getStatusCode());}
            catch (\Illuminate\Validation\ValidationException $e) {$this->assertSame('jornada',$condition);$this->assertArrayHasKey('jornada',$e->errors());}
            finally {\Illuminate\Support\Facades\DB::rollBack();}
        }
        $this->assertDatabaseCount('registros_movilidad',0);
    }

    public function test_movilidad_v2_fallo_real_preserva_formulario_y_no_finge_exito(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {$this->markTestSkipped('El trigger sintético está escrito para SQLite. PostgreSQL se verifica en QA aislado con timeout de bloqueo.');}
        $this->travelTo(today()->setTime(10,0));$this->actingAs($this->enfermero);
        \Illuminate\Support\Facades\DB::unprepared("CREATE TRIGGER movilidad_qa_failure BEFORE INSERT ON registros_movilidad BEGIN SELECT RAISE(ABORT, 'qa insert failure'); END");
        try {
            $this->formularioMovilidad()->set('movMarcha','ASISTIDA')->set('movDatos.dispositivo','ANDADOR')
                ->set('movObservacion','Captura que debe conservarse')->call('guardarCuidado')
                ->assertHasErrors('movilidad_guardado')->assertSet('drawerPaso','register-form')
                ->assertSet('mostrarSelectorModal',true)->assertSet('movMarcha','ASISTIDA')
                ->assertSet('movDatos.dispositivo','ANDADOR')->assertSet('movObservacion','Captura que debe conservarse')
                ->assertSet('movResultado',[])->assertSee('Conservamos los datos')->assertDontSee('qa insert failure');
            $this->assertDatabaseCount('registros_movilidad',0);
        } finally {\Illuminate\Support\Facades\DB::unprepared('DROP TRIGGER movilidad_qa_failure');}
    }
}
