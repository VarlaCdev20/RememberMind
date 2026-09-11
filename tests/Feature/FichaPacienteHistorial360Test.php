<?php

namespace Tests\Feature;

use App\Livewire\Cuidados\FichaPaciente;
use App\Models\AdministracionMedicacion;
use App\Models\AdultoMayor;
use App\Models\AlertaAdulto;
use App\Models\AsignacionTurnoAdulto;
use App\Models\MedicacionAdulto;
use App\Models\PaseTurno;
use App\Models\PlanCuidado;
use App\Models\SeguimientoDiario;
use App\Models\SignosVitalesAdulto;
use App\Models\TareaPlanCuidado;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use App\Models\ValoracionFuncionalAdulto;
use Database\Seeders\EstadoAdultoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FichaPacienteHistorial360Test extends TestCase
{
    use RefreshDatabase;

    private User $enfermero;
    private User $enfermeroReceptor;
    private AdultoMayor $adulto;
    private TurnoEnfermeria $turno;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([EstadoAdultoSeeder::class, RolesAndPermissionsSeeder::class]);

        $this->turno = TurnoEnfermeria::create([
            'orden' => 1,
            'nombre' => 'Turno Mañana',
            'hora_inicio' => '07:00:00',
            'hora_fin' => '15:00:00',
            'estado' => 'ACTIVO',
        ]);

        $this->enfermero = User::factory()->create([
            'nombres' => 'Carla',
            'ap_paterno' => 'Mendoza',
            'estado' => 'ACTIVO',
        ]);
        $this->enfermero->assignRole('ENFERMEROS');

        $this->enfermeroReceptor = User::factory()->create([
            'nombres' => 'Roberto',
            'ap_paterno' => 'Ríos',
            'estado' => 'ACTIVO',
        ]);
        $this->enfermeroReceptor->assignRole('ENFERMEROS');

        $this->adulto = AdultoMayor::factory()->create([
            'nombres' => 'Elena',
            'ap_paterno' => 'Vargas',
            'cod_est_adul' => 'EST_001',
        ]);

        AsignacionTurnoAdulto::create([
            'cod_am' => $this->adulto->cod_am,
            'cod_turno' => $this->turno->cod_turno,
            'cod_usu_enfermero' => $this->enfermero->cod_usu,
            'fecha_inicio' => today()->toDateString(),
            'nivel_supervision' => 'ESTANDAR',
            'estado' => 'ACTIVA',
            'motivo_asignacion' => 'Asignación de turno matutino',
            'asignado_por' => $this->enfermero->cod_usu,
        ]);

        $this->actingAs($this->enfermero);
    }

    public function test_historial_360_muestra_eventos_reales_con_estructura_clinica(): void
    {
        // 1. Signos vitales
        SignosVitalesAdulto::create([
            'cod_am' => $this->adulto->cod_am,
            'profesional_id' => $this->enfermero->cod_usu,
            'fecha' => today()->toDateString(),
            'hora' => '08:15:00',
            'presion_arterial' => '128/76',
            'frecuencia_cardiaca' => 72,
            'saturacion' => 97,
            'temperatura' => 36.6,
        ]);

        // 2. Medicación administrada
        $med = MedicacionAdulto::create([
            'cod_am' => $this->adulto->cod_am,
            'nombre_medicamento' => 'Enalapril',
            'dosis' => '10 mg',
            'frecuencia' => 'Cada 12 horas',
            'via_administracion' => 'Oral',
            'fecha_inicio' => today()->toDateString(),
            'estado' => 'ACTIVA',
        ]);

        AdministracionMedicacion::create([
            'cod_med_adulto' => $med->cod_med_adulto,
            'cod_am' => $this->adulto->cod_am,
            'fecha' => today()->toDateString(),
            'hora_programada' => '09:30:00',
            'hora_real' => '09:30:00',
            'administrado' => true,
            'registrado_por' => $this->enfermero->cod_usu,
        ]);

        // 3. Seguimiento diario
        SeguimientoDiario::create([
            'cod_am' => $this->adulto->cod_am,
            'cod_turno' => $this->turno->cod_turno,
            'enfermero_id' => $this->enfermero->cod_usu,
            'fecha' => today()->toDateString(),
            'hora_inicio' => '11:20:00',
            'hora_fin' => '11:30:00',
            'estado_general' => 'ESTABLE',
            'alimentacion' => 'COMPLETA',
            'porcentaje_alimentacion' => 100,
            'hidratacion' => 'ADECUADA',
            'movilidad' => 'INDEPENDIENTE',
            'sueno' => 'NORMAL',
            'incidente' => false,
            'requiere_medico' => false,
            'observacion' => 'Orientada, apetito conservado, sin incidente',
        ]);

        // 4. Alerta clínica
        AlertaAdulto::create([
            'cod_am' => $this->adulto->cod_am,
            'tipo_alerta' => 'PRESION_ARTERIAL',
            'nivel' => 'ALTO',
            'estado' => 'CERRADA',
            'origen' => 'ENFERMERIA',
            'motivo' => 'Presión elevada',
            'descripcion' => 'Presión elevada, atendida y controlada',
            'generada_por' => $this->enfermero->cod_usu,
            'responsable_id' => $this->enfermero->cod_usu,
            'atendido_por' => $this->enfermero->cod_usu,
            'cerrado_por' => $this->enfermero->cod_usu,
            'observacion_cierre' => 'Parámetros normalizados tras reposo',
        ]);

        $component = Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_am])
            ->call('cambiarTab', 'historial')
            ->assertSee('Historial 360°')
            ->assertSee('Trayectoria clínica y de cuidados del residente')
            ->assertSee('08:15')
            ->assertSee('128/76')
            ->assertSee('09:30')
            ->assertSee('Enalapril 10 mg administrado')
            ->assertSee('Carla Mendoza');

        $cronologia = $component->instance()->historialCronologico;
        $this->assertCount(4, $cronologia);

        $resumen = $component->instance()->resumenLongitudinal;
        $this->assertEquals(1, $resumen['alertas_total']);
        $this->assertEquals(1, $resumen['alertas_cerradas']);
        $this->assertEquals(0, $resumen['alertas_activas']);
        $this->assertEquals(100, $resumen['adherencia_pct']);
        $this->assertEquals(1, $resumen['total_seguimientos']);
    }

    public function test_historial_360_aplica_filtros_por_tipo_y_fechas(): void
    {
        // Crear signos hoy
        SignosVitalesAdulto::create([
            'cod_am' => $this->adulto->cod_am,
            'profesional_id' => $this->enfermero->cod_usu,
            'fecha' => today()->toDateString(),
            'hora' => '08:00:00',
            'presion_arterial' => '120/80',
            'frecuencia_cardiaca' => 70,
        ]);

        // Crear signos hace 10 días
        SignosVitalesAdulto::create([
            'cod_am' => $this->adulto->cod_am,
            'profesional_id' => $this->enfermero->cod_usu,
            'fecha' => today()->subDays(10)->toDateString(),
            'hora' => '08:00:00',
            'presion_arterial' => '130/85',
            'frecuencia_cardiaca' => 75,
        ]);

        // Crear alerta hoy
        AlertaAdulto::create([
            'cod_am' => $this->adulto->cod_am,
            'tipo_alerta' => 'CAIDA',
            'nivel' => 'CRITICO',
            'estado' => 'ABIERTA',
            'origen' => 'ENFERMERIA',
            'motivo' => 'Caída en habitación',
            'descripcion' => 'Caída en habitación asistida de inmediato',
            'generada_por' => $this->enfermero->cod_usu,
        ]);

        $component = Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_am])
            ->call('cambiarTab', 'historial');

        // Total sin filtrar: 3 eventos
        $this->assertCount(3, $component->instance()->historialFiltrado);

        // Filtrar solo signos
        $component->call('setHistorialFiltro', 'SIGNOS');
        $this->assertCount(2, $component->instance()->historialFiltrado);

        // Filtrar solo alertas
        $component->call('setHistorialFiltro', 'ALERTAS');
        $this->assertCount(1, $component->instance()->historialFiltrado);

        // Filtrar incidentes
        $component->call('setHistorialFiltro', 'INCIDENTES');
        $this->assertCount(1, $component->instance()->historialFiltrado);

        // Filtrar por rango de fechas (solo hoy)
        $component->call('setHistorialFiltro', 'TODOS')
            ->set('historialFechaDesde', today()->toDateString())
            ->set('historialFechaHasta', today()->toDateString());

        $this->assertCount(2, $component->instance()->historialFiltrado);

        // Restablecer filtros
        $component->call('limpiarFiltrosHistorial');
        $this->assertCount(3, $component->instance()->historialFiltrado);
    }

    public function test_resumen_longitudinal_extrae_valoraciones_reales_sin_deterioro_automatico(): void
    {
        ValoracionFuncionalAdulto::create([
            'cod_am' => $this->adulto->cod_am,
            'registrado_por' => $this->enfermero->cod_usu,
            'fecha_valoracion' => today()->toDateString(),
            'indice_barthel' => 90,
            'barthel_total' => 90,
            'katz_total' => 6,
            'nivel_dependencia' => 'INDEPENDENCIA',
            'riesgo_caida' => 'BAJO',
        ]);

        $component = Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_am])
            ->call('cambiarTab', 'historial')
            ->assertSee('Índice de Barthel')
            ->assertSee('90/100');

        $resumen = $component->instance()->resumenLongitudinal;
        $this->assertNotNull($resumen['ultima_valoracion']);
        $this->assertEquals('Índice de Barthel', $resumen['ultima_valoracion']['instrumento']);
        $this->assertStringContainsString('90/100', $resumen['ultima_valoracion']['resultado']);
    }
}
