<?php

namespace Tests\Feature;

use App\Livewire\Cuidados\FichaPaciente;
use App\Models\AdultoMayor;
use App\Models\OcupacionCama;
use App\Models\AsignacionResidenteJornada;
use App\Models\Cama;
use App\Models\DocumentoAdultoMayor;
use App\Models\EstadoAdulto;
use App\Models\Habitacion;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ResultadosEstudiosFichaTest extends TestCase
{
    use RefreshDatabase;

    protected User $enfermero;
    protected AdultoMayor $adulto;
    protected Habitacion $habitacion;
    protected Cama $cama;
    protected TurnoEnfermeria $turno;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            
            RolesAndPermissionsSeeder::class,
        ]);

        $this->enfermero = User::factory()->create([
            'cod_usu' => 'USU_TEST_ENF2',
            'nombres' => 'Laura',
            'ap_paterno' => 'González',
            'ap_materno' => 'Pérez',
            'correo' => 'laura.gonzalez2@test.com',
            'estado' => 'ACTIVO',
        ]);
        $this->enfermero->assignRole('ENFERMEROS');

        $this->turno = TurnoEnfermeria::create([
            'cod_turno' => 'TUR_002',
            'nombre' => 'Mañana Asistencial',
            'hora_inicio' => '07:00:00',
            'hora_fin' => '15:00:00',
            'orden' => 1,
            'estado' => 'ACTIVO',
        ]);

        $this->habitacion = Habitacion::create([
            'cod_habitacion' => 'HAB_204',
            'nombre' => 'Habitación 204',
            'codigo' => 'H-204',
            'numero' => '204',
            'tipo' => 'DOBLE',
            'capacidad' => 2,
            'estado' => 'ACTIVA',
        ]);

        $this->cama = Cama::create([
            'cod_cama' => 'CAM_204A',
            'cod_habitacion' => $this->habitacion->cod_habitacion,
            'codigo' => 'C-204-A',
            'numero' => 'A',
            'estado' => 'OCUPADA',
        ]);

        $this->adulto = AdultoMayor::factory()->create([
            'cod_am' => 'AM_ESTUDIOS_01',
            'nombres' => 'Carmen',
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

        OcupacionCama::create([
            'cod_am' => $this->adulto->cod_am,
            'cod_habitacion' => $this->habitacion->cod_habitacion,
            'cod_cama' => $this->cama->cod_cama,
            'fecha_asignacion' => today()->toDateString(),
            'estado' => 'ACTIVO',
        ]);

        AsignacionResidenteJornada::create([
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

    public function test_pestana_resultados_y_estudios_renderiza_correctamente_y_reemplaza_valoracion_integral(): void
    {
        $this->actingAs($this->enfermero);

        $component = Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_am])
            ->set('tabActivo', 'estudios')
            ->assertSee('Resultados y estudios')
            ->assertSee('RESULTADOS Y ESTUDIOS CLÍNICOS')
            ->assertSee('Seguimiento de pruebas diagnósticas, resultados de laboratorio')
            ->assertDontSee('Valoración integral</span>', false)
            // KPIs
            ->assertSee('Estudios registrados')
            ->assertSee('Resultados normales')
            ->assertSee('Fuera de rango')
            ->assertSee('Requieren seguimiento')
            ->assertSee('Último estudio')
            // Filtros y Subtabs
            ->assertSee('Todos los tipos')
            ->assertSee('Laboratorio')
            ->assertSee('Imágenes')
            ->assertSee('Estudios cardiológicos')
            ->assertSee('Otros estudios')
            // Secciones y Gráfico
            ->assertSee('Evolución de parámetros clave')
            ->assertSee('Valores de referencia')
            ->assertSee('Lista de estudios y resultados')
            ->assertSee('Detalles del resultado');
    }

    public function test_filtrado_por_subtabs_de_estudios(): void
    {
        $this->actingAs($this->enfermero);

        $component = Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_am])
            ->set('tabActivo', 'estudios');

        // Por defecto TODOS
        $component->assertSee('Glucosa en ayunas')
            ->assertSee('Radiografía de tórax (PA)')
            ->assertSee('Electrocardiograma de 12 derivaciones (ECG)');

        // Filtrar por IMAGEN
        $component->call('setSubtabEstudio', 'IMAGEN')
            ->assertSet('subtabEstudio', 'IMAGEN')
            ->assertSee('Radiografía de tórax (PA)')
            ->assertSee('Ecografía abdominal completa');

        $component->assertDontSee('Glucosa en ayunas')
            ->assertDontSee('Electrocardiograma de 12 derivaciones (ECG)');

        // Filtrar por CARDIOLOGICO
        $component->call('setSubtabEstudio', 'CARDIOLOGICO')
            ->assertSet('subtabEstudio', 'CARDIOLOGICO')
            ->assertSee('Electrocardiograma de 12 derivaciones (ECG)')
            ->assertDontSee('Radiografía de tórax (PA)')
            ->assertDontSee('Glucosa en ayunas');

        // Reset a TODOS
        $component->call('setSubtabEstudio', 'TODOS')
            ->assertSet('subtabEstudio', 'TODOS')
            ->assertSee('Glucosa en ayunas')
            ->assertSee('Radiografía de tórax (PA)');
    }

    public function test_busqueda_textual_y_filtro_de_periodo(): void
    {
        $this->actingAs($this->enfermero);

        $component = Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_am])
            ->set('tabActivo', 'estudios');

        // Búsqueda de creatinina
        $component->set('filtroBusquedaEstudio', 'Creatinina')
            ->assertSee('Creatinina sérica')
            ->assertDontSee('Radiografía de tórax (PA)');

        // Búsqueda sin resultados
        $component->set('filtroBusquedaEstudio', 'Inexistente_XYZ_99')
            ->assertSee('No existen resultados registrados para este período.');

        // Limpiar búsqueda
        $component->set('filtroBusquedaEstudio', '')
            ->assertSee('Glucosa en ayunas');

        // Selector de período
        $component->call('setFiltroPeriodoEstudio', '3m')
            ->assertSet('filtroPeriodoEstudio', '3m');
    }

    public function test_seleccion_interactiva_de_estudio_actualiza_panel_lateral_derecho(): void
    {
        $this->actingAs($this->enfermero);

        $component = Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_am])
            ->set('tabActivo', 'estudios');

        // Seleccionar Rx de Tórax
        $component->call('seleccionarEstudio', 'EST_06_RX_TORAX')
            ->assertSet('estudioSeleccionadoId', 'EST_06_RX_TORAX')
            ->assertSee('Radiografía de tórax (PA)')
            ->assertSee('Sin hallazgos agudos')
            ->assertSee('Dr. Fernando Arze')
            ->assertSee('Centro Radiológico Metropolitano')
            ->assertSee('Tórax senil con cambios vasculares degenerativos habituales')
            ->assertSee('Informe_Radiologico_Torax_PA_02092026.pdf')
            ->assertSee('Ver informe');

        // Seleccionar ECG
        $component->call('seleccionarEstudio', 'EST_07_ECG')
            ->assertSet('estudioSeleccionadoId', 'EST_07_ECG')
            ->assertSee('Electrocardiograma de 12 derivaciones (ECG)')
            ->assertSee('Ritmo sinusal regular')
            ->assertSee('Dr. Rodrigo Vega')
            ->assertSee('Unidad Cardiológica Integral')
            ->assertSee('Trazado_ECG_12D_28082026.pdf');
    }

    public function test_conmutacion_de_parametros_clave_actualiza_rangos_de_referencia(): void
    {
        $this->actingAs($this->enfermero);

        $component = Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_am])
            ->set('tabActivo', 'estudios');

        // Por defecto Glucosa
        $component->assertSee('Glucosa en ayunas')
            ->assertSee('70 – 100 mg/dL')
            ->assertSee('Prediabetes (Intolerancia)')
            ->assertSee('Diabetes Mellitus');

        // Cambiar a Hemoglobina
        $component->call('setParametroGraficoEstudio', 'hemoglobina')
            ->assertSet('parametroGraficoEstudio', 'hemoglobina')
            ->assertSee('Hemoglobina (Hb)')
            ->assertSee('12.0 – 16.0 g/dL')
            ->assertSee('Anemia Leve');

        // Cambiar a Creatinina
        $component->call('setParametroGraficoEstudio', 'creatinina')
            ->assertSet('parametroGraficoEstudio', 'creatinina')
            ->assertSee('Creatinina sérica')
            ->assertSee('0.6 – 1.2 mg/dL');

        // Cambiar a Sodio
        $component->call('setParametroGraficoEstudio', 'sodio')
            ->assertSet('parametroGraficoEstudio', 'sodio')
            ->assertSee('Sodio sérico (Na+)')
            ->assertSee('135 – 145 mEq/L');

        // Cambiar a Potasio
        $component->call('setParametroGraficoEstudio', 'potasio')
            ->assertSet('parametroGraficoEstudio', 'potasio')
            ->assertSee('Potasio sérico (K+)')
            ->assertSee('3.5 – 5.0 mEq/L');
    }
}
