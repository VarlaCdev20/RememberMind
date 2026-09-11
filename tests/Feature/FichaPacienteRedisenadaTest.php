<?php

namespace Tests\Feature;

use App\Livewire\Cuidados\FichaPaciente;
use App\Models\AdministracionMedicacion;
use App\Models\AdultoMayor;
use App\Models\AlertaAdulto;
use App\Models\AsignacionAdultoMayor;
use App\Models\AsignacionTurnoAdulto;
use App\Models\Cama;
use App\Models\Habitacion;
use App\Models\MedicacionAdulto;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use Database\Seeders\EstadoAdultoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FichaPacienteRedisenadaTest extends TestCase
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

        $this->seed([EstadoAdultoSeeder::class, RolesAndPermissionsSeeder::class]);

        $this->enfermero = User::factory()->create([
            'cod_usu' => 'USU_0088',
            'nombres' => 'Carla',
            'ap_paterno' => 'Encinas',
            'ap_materno' => 'Mendoza',
            'estado' => 'ACTIVO',
        ]);
        $this->enfermero->assignRole('ENFERMEROS');

        $this->turno = TurnoEnfermeria::create([
            'nombre' => 'Mañana Asistencial',
            'hora_inicio' => '07:00:00',
            'hora_fin' => '15:00:00',
            'orden' => 1,
            'estado' => 'ACTIVO',
        ]);

        $this->habitacion = Habitacion::create([
            'nombre' => 'Habitación 101',
            'codigo' => 'H-101',
            'numero' => '101',
            'tipo' => 'DOBLE',
            'capacidad' => 2,
            'estado' => 'ACTIVA',
        ]);

        $this->cama = Cama::create([
            'cod_habitacion' => $this->habitacion->cod_habitacion,
            'codigo' => 'C-101-A',
            'numero' => 'A',
            'estado' => 'OCUPADA',
        ]);

        $this->adulto = AdultoMayor::factory()->create([
            'cod_am' => 'AM999',
            'nombres' => 'Rosa Maria',
            'ap_paterno' => 'Gomez',
            'ap_materno' => 'Vaca',
            'ci' => '3344556',
            'fecha_nac' => '1944-05-12',
            'genero' => 'FEMENINO',
            'alergias' => 'Penicilina y Sulfamidas',
            'grupo_sanguineo' => 'O',
            'factor_rh' => '+',
            'seguro_salud' => 'Caja Nacional de Salud',
            'contacto_emergencia_nombre' => 'Elena Gomez',
            'contacto_emergencia_celular' => '77889900',
            'cod_habitacion' => $this->habitacion->cod_habitacion,
            'cod_cama' => $this->cama->cod_cama,
            'cod_est_adul' => 'EST_001',
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
            'motivo_asignacion' => 'Asignación de turno',
            'asignado_por' => $this->enfermero->cod_usu,
            'fecha' => today()->toDateString(),
            'nivel_supervision' => 'ALTO',
            'estado' => 'ACTIVO',
        ]);
    }

    public function test_cabecera_muestra_informacion_completa_y_acciones_rapidas(): void
    {
        $this->actingAs($this->enfermero);

        Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_am])
            ->assertOk()
            ->assertSee('Rosa Maria Gomez Vaca')
            ->assertSee('AM999')
            ->assertSee('82 años')
            ->assertSee('H-101')
            ->assertSee('C-101-A')
            ->assertSee('Penicilina y Sulfamidas')
            ->assertSee('Registrar signos')
            ->assertSee('Administrar medicación')
            ->assertSee('Registrar seguimiento')
            ->assertSee('Reportar incidente');
    }

    public function test_pestanas_navegacion_unificada_y_estructura_de_resumen(): void
    {
        $this->actingAs($this->enfermero);

        $component = Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_am])
            ->assertOk()
            ->assertSee('Resumen clínico')
            ->assertSee('Signos vitales')
            ->assertSee('Medicación')
            ->assertSee('Cuidados')
            ->assertSee('Seguimiento')
            ->assertSee('Alertas')
            ->assertSee('Historial 360°')
            // Resumen en 3 columnas con nuevo enfoque
            ->assertSee('Información Clínica Relevante')
            ->assertSee('Estado Clínico Actual')
            ->assertSee('Tendencias y Próximas Acciones');

        // Comprobar cambio de pestaña a 'cuidado' y 'cuidados'
        $component->call('cambiarTab', 'cuidados')
            ->assertSet('tabActivo', 'cuidado')
            ->assertSee('Plan de Cuidados de Enfermería Vigente')
            ->assertSee('Tareas Programadas y Estado de Ejecución')
            ->assertSee('Hora')
            ->assertSee('Tarea / Actividad')
            ->assertSee('Estado')
            ->assertSee('Acción');
    }

    public function test_pestanas_signos_medicacion_y_alertas_con_reglas_institucionales(): void
    {
        $this->actingAs($this->enfermero);

        // Crear medicación y administraciones
        $med = MedicacionAdulto::create([
            'cod_am' => $this->adulto->cod_am,
            'nombre_medicamento' => 'Losartán 50mg',
            'dosis' => '50 mg',
            'via_administracion' => 'Oral',
            'frecuencia' => 'Cada 12 horas',
            'fecha_inicio' => today()->toDateString(),
            'estado' => 'ACTIVO',
        ]);

        AdministracionMedicacion::create([
            'cod_am' => $this->adulto->cod_am,
            'cod_med_adulto' => $med->cod_med_adulto,
            'fecha' => today()->toDateString(),
            'hora_programada' => '08:00',
            'hora_real' => '08:05',
            'administrado' => true,
            'registrado_por' => $this->enfermero->cod_usu,
        ]);

        AdministracionMedicacion::create([
            'cod_am' => $this->adulto->cod_am,
            'cod_med_adulto' => $med->cod_med_adulto,
            'fecha' => today()->toDateString(),
            'hora_programada' => '20:00',
            'administrado' => false,
            'motivo_omision' => 'Paciente dormida al momento de la toma',
            'registrado_por' => $this->enfermero->cod_usu,
        ]);

        // Crear alerta abierta y cerrada
        AlertaAdulto::create([
            'cod_am' => $this->adulto->cod_am,
            'origen' => 'ENFERMERIA',
            'tipo_alerta' => 'CLINICA',
            'nivel' => 'ALTO',
            'motivo' => 'Hipotensión matutina',
            'estado' => 'ABIERTA',
            'registrado_por' => $this->enfermero->cod_usu,
        ]);

        AlertaAdulto::create([
            'cod_am' => $this->adulto->cod_am,
            'origen' => 'ENFERMERIA',
            'tipo_alerta' => 'CONDUCTUAL',
            'nivel' => 'MEDIO',
            'motivo' => 'Inquietud nocturna previa',
            'estado' => 'CERRADA',
            'observacion_cierre' => 'Se acompaña y tranquiliza satisfactoriamente',
            'registrado_por' => $this->enfermero->cod_usu,
        ]);

        // 1. Probar pestaña Signos
        Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_am])
            ->call('cambiarTab', 'signos')
            ->assertSee('Monitoreo Hemodinámico y Signos Vitales')
            ->assertSee('Evolución Temporal de Parámetros Clínicos')
            ->assertSee('PA')
            ->assertSee('FC')
            ->assertSee('Temp')
            ->assertSee('SpO2')
            ->call('setMetricaSignos', 'GLUCEMIA')
            ->assertSet('metricaSignosSeleccionada', 'GLUCOSA');

        // 2. Probar pestaña Medicación
        Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_am])
            ->call('cambiarTab', 'medicacion')
            ->assertSee('Medicación Activa Prescrita')
            ->assertSee('Losartán 50mg')
            ->assertSee('ADMINISTRADA')
            ->assertSee('OMITIDA')
            ->assertSee('Paciente dormida al momento de la toma');

        // 3. Probar pestaña Alertas (Abiertas primero, después cerradas)
        Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_am])
            ->call('cambiarTab', 'alertas')
            ->assertSee('Alertas Clínicas Activas')
            ->assertSee('Hipotensión matutina')
            ->assertSee('Historial de Alertas Resueltas')
            ->assertSee('Se acompaña y tranquiliza satisfactoriamente');
    }
}
