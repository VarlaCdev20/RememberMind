<?php

namespace Tests\Feature;

use App\Livewire\Cuidados\FichaPaciente;
use App\Models\AdultoMayor;
use App\Models\AsignacionAdultoMayor;
use App\Models\AsignacionTurnoAdulto;
use App\Models\Cama;
use App\Models\EstadoAdulto;
use App\Models\Habitacion;
use App\Models\IncidenteResidente;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use Database\Seeders\EstadoAdultoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EventosClinicosFichaTest extends TestCase
{
    use RefreshDatabase;

    protected User $enfermero;
    protected AdultoMayor $adulto;
    protected TurnoEnfermeria $turno;
    protected Habitacion $habitacion;
    protected Cama $cama;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            EstadoAdultoSeeder::class,
            RolesAndPermissionsSeeder::class,
        ]);

        $this->enfermero = User::factory()->create([
            'cod_usu' => 'USU_TEST_ENF',
            'nombres' => 'Laura',
            'ap_paterno' => 'González',
            'ap_materno' => 'Pérez',
            'correo' => 'laura.gonzalez@test.com',
            'estado' => 'ACTIVO',
        ]);
        $this->enfermero->assignRole('ENFERMEROS');

        $this->turno = TurnoEnfermeria::create([
            'cod_turno' => 'TUR_001',
            'nombre' => 'Mañana Asistencial',
            'hora_inicio' => '07:00:00',
            'hora_fin' => '15:00:00',
            'orden' => 1,
            'estado' => 'ACTIVO',
        ]);

        $this->habitacion = Habitacion::create([
            'cod_habitacion' => 'HAB_101',
            'nombre' => 'Habitación 101',
            'codigo' => 'H-101',
            'numero' => '101',
            'tipo' => 'DOBLE',
            'capacidad' => 2,
            'estado' => 'ACTIVA',
        ]);

        $this->cama = Cama::create([
            'cod_cama' => 'CAM_101A',
            'cod_habitacion' => $this->habitacion->cod_habitacion,
            'codigo' => 'C-101-A',
            'numero' => 'A',
            'estado' => 'OCUPADA',
        ]);

        $this->adulto = AdultoMayor::factory()->create([
            'cod_am' => 'AM_EVENTOS_01',
            'nombres' => 'Elena',
            'ap_paterno' => 'Mendoza',
            'ap_materno' => 'Ramos',
            'ci' => '4455667',
            'fecha_nac' => '1942-03-15',
            'genero' => 'FEMENINO',
            'alergias' => 'Sin alergias conocidas',
            'grupo_sanguineo' => 'A',
            'factor_rh' => '+',
            'seguro_salud' => 'Caja Nacional',
            'contacto_emergencia_nombre' => 'Carlos Mendoza',
            'contacto_emergencia_celular' => '78901234',
            'cod_habitacion' => $this->habitacion->cod_habitacion,
            'cod_cama' => $this->cama->cod_cama,
            'cod_est_adul' => EstadoAdulto::first()->cod_est_adul,
        ]);

        AsignacionAdultoMayor::create([
            'cod_am' => $this->adulto->cod_am,
            'cod_habitacion' => $this->habitacion->cod_habitacion,
            'cod_cama' => $this->cama->cod_cama,
            'fecha_asignacion' => today()->toDateString(),
            'estado' => 'ACTIVO',
        ]);

        AsignacionTurnoAdulto::create([
            'cod_am' => $this->adulto->cod_am,
            'cod_turno' => $this->turno->cod_turno,
            'cod_usu_enfermero' => $this->enfermero->cod_usu,
            'cod_usu' => $this->enfermero->cod_usu,
            'fecha_inicio' => today()->toDateString(),
            'motivo_asignacion' => 'Asignación asistencial',
            'asignado_por' => $this->enfermero->cod_usu,
            'fecha' => today()->toDateString(),
            'nivel_supervision' => 'ALTO',
            'estado' => 'ACTIVO',
        ]);
    }

    public function test_modulo_eventos_clinicos_renderiza_kpis_graficos_y_layout_dos_columnas(): void
    {
        $this->actingAs($this->enfermero);

        Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_am])
            ->set('tabActivo', 'eventos')
            ->assertSee('Eventos clínicos')
            ->assertSee('Historial de incidentes, caídas, lesiones y eventos clínicos relevantes del residente.')
            ->assertSee('Registrar evento')
            // 4 KPIs
            ->assertSee('Eventos activos')
            ->assertSee('Requieren seguimiento')
            ->assertSee('En seguimiento')
            ->assertSee('En evaluación')
            ->assertSee('Resueltos')
            ->assertSee('Sin pendientes')
            ->assertSee('Críticos')
            ->assertSee('Últimos 30 días')
            // 2 Gráficos
            ->assertSee('Eventos por mes')
            ->assertSee('Últimos 6 meses')
            ->assertSee('Eventos por tipo')
            ->assertSee('Distribución clínica')
            ->assertSee('eventos')
            // Filtros
            ->assertSee('Buscar eventos...')
            ->assertSee('Todos')
            ->assertSee('Caídas')
            ->assertSee('Lesiones')
            ->assertSee('Incidentes')
            ->assertSee('Complicaciones')
            ->assertSee('Otros')
            ->assertSee('Período:')
            // Layout dos columnas
            ->assertSee('Línea de tiempo de eventos')
            ->assertSee('Información básica')
            ->assertSee('Descripción del evento')
            ->assertSee('Ubicación')
            ->assertSee('Próxima evaluación')
            ->assertSee('Plan de seguimiento');
    }

    public function test_seleccion_interactiva_y_tabs_de_detalle_de_evento(): void
    {
        $this->actingAs($this->enfermero);

        $component = Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_am])
            ->set('tabActivo', 'eventos')
            ->assertSee('CAÍDA')
            ->assertSee('FIEBRE LEVE')
            ->assertSee('Resumen')
            ->assertSee('Valoración')
            ->assertSee('Intervenciones')
            ->assertSee('Seguimiento')
            ->assertSee('Documentos')
            ->assertSee('Trazabilidad');

        // Seleccionar evento 2 (Fiebre leve)
        $component->call('seleccionarEvento', 'EV_GOLDEN_02')
            ->assertSet('eventoSeleccionadoId', 'EV_GOLDEN_02')
            ->assertSee('FIEBRE LEVE')
            ->assertSee('Ana Torres');

        // Cambiar tabs internas de detalle
        $component->call('setTabDetalleEvento', 'valoracion')
            ->assertSet('tabDetalleEvento', 'valoracion')
            ->assertSee('Valoración clínica estructurada')
            ->assertSee('Estado general')
            ->assertSee('Nivel de conciencia')
            ->assertSee('Dolor (Escala EVA)');

        $component->call('setTabDetalleEvento', 'intervenciones')
            ->assertSet('tabDetalleEvento', 'intervenciones')
            ->assertSee('Intervenciones asistenciales realizadas');

        $component->call('setTabDetalleEvento', 'seguimiento')
            ->assertSet('tabDetalleEvento', 'seguimiento')
            ->assertSee('Acciones de seguimiento en curso');

        $component->call('setTabDetalleEvento', 'documentos')
            ->assertSet('tabDetalleEvento', 'documentos')
            ->assertSee('Documentos y reportes clínicos');

        $component->call('setTabDetalleEvento', 'trazabilidad')
            ->assertSet('tabDetalleEvento', 'trazabilidad')
            ->assertSee('Trazabilidad y auditoría clínica');
    }

    public function test_filtros_por_categoria_y_busqueda(): void
    {
        $this->actingAs($this->enfermero);

        $component = Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_am])
            ->set('tabActivo', 'eventos');

        // Filtrar por Caídas
        $component->call('setFiltroTipoEvento', 'CAIDA')
            ->assertSet('filtroTipoEvento', 'CAIDA')
            ->assertSee('CAÍDA')
            ->assertDontSee('FIEBRE LEVE');

        // Filtrar por Incidentes
        $component->call('setFiltroTipoEvento', 'INCIDENTE')
            ->assertSet('filtroTipoEvento', 'INCIDENTE')
            ->assertSee('FIEBRE LEVE');

        // Reset a Todos
        $component->call('setFiltroTipoEvento', 'TODOS')
            ->assertSet('filtroTipoEvento', 'TODOS')
            ->assertSee('CAÍDA')
            ->assertSee('FIEBRE LEVE');

        // Búsqueda por texto
        $component->set('filtroBusquedaEvento', 'Paracetamol')
            ->assertSee('FIEBRE LEVE')
            ->assertDontSee('Inestabilidad durante la marcha en pasillo');
    }

    public function test_registro_de_nuevo_evento_clinico_se_guarda_en_bd_y_aparece_en_timeline(): void
    {
        $this->actingAs($this->enfermero);

        $component = Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_am])
            ->set('tabActivo', 'eventos');

        // Abrir modal
        $component->call('abrirModalRegistrarEvento')
            ->assertSet('modalRegistrarEvento', true)
            ->set('nuevoEventoTipo', 'CAIDA')
            ->set('nuevoEventoFechaHora', now()->format('Y-m-d\TH:i'))
            ->set('nuevoEventoLugar', 'Comedor sector oeste')
            ->set('nuevoEventoSeveridad', 'MODERADA')
            ->set('nuevoEventoDescripcion', 'Tropiezo involuntario con silla de comensal vecino. Se asiste de inmediato.')
            ->set('nuevoEventoDolor', 3)
            ->set('nuevoEventoLesion', false)
            ->set('nuevoEventoPresenciado', true)
            ->set('nuevoEventoTestigo', 'Laura González')
            ->call('guardarNuevoEvento')
            ->assertSet('modalRegistrarEvento', false);

        // Verificar persistencia en base de datos
        $this->assertDatabaseHas('incidentes_residente', [
            'cod_am' => $this->adulto->cod_am,
            'tipo' => 'CAIDA',
            'lugar' => 'Comedor sector oeste',
            'dolor' => 3,
            'lesion' => false,
            'registrado_por' => $this->enfermero->cod_usu,
        ]);

        // Verificar presencia en la vista y timeline
        $component->assertSee('Comedor sector oeste')
            ->assertSee('Tropiezo involuntario con silla de comensal vecino');
    }
}
