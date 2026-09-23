<?php

namespace Tests\Feature;

use App\Livewire\Cuidados\AgendaEnfermeria;
use App\Livewire\Cuidados\FichaPaciente;
use App\Livewire\Cuidados\RegistrosEnfermeria;
use App\Models\Alerta;
use App\Models\AsignacionPersonal;
use App\Models\AsignacionResidenteJornada;
use App\Models\CuracionHerida;
use App\Models\EjecucionCuidado;
use App\Models\Herida;
use App\Models\HistorialEstadoResidente;
use App\Models\Incidente;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\PlanCuidado;
use App\Models\Prescripcion;
use App\Models\RegistroIngesta;
use App\Models\Residente;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use App\Services\Enfermeria\AgendaTurnoService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class ModuloEnfermeriaIntegralTest extends TestCase
{
    use RefreshDatabase;

    private User $enfermero;
    private Personal $personal;
    private Residente $residente;
    private TurnoEnfermeria $turno;
    private Jornada $jornada;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-09-11 10:00:00');
        $this->seed(DatabaseSeeder::class);

        $this->enfermero = User::factory()->create([
            'cod_usuario' => 'USU_TEST_INT',
            'estado' => 'ACTIVO',
        ]);
        $this->enfermero->assignRole('ENFERMEROS');

        $this->personal = Personal::create([
            'cod_personal' => 'PER_TEST_INT',
            'cod_usuario' => $this->enfermero->cod_usuario,
            'nombres' => 'Enfermero',
            'apellido_paterno' => 'Test',
            'numero_documento' => 'DOC_TEST_INT',
            'profesion' => 'Enfermero',
            'estado' => 'ACTIVO',
        ]);

        $this->turno = TurnoEnfermeria::create([
            'cod_turno' => 'TUR_001',
            'orden' => 1,
            'nombre' => 'Mañana',
            'hora_inicio' => '07:00',
            'hora_fin' => '15:00',
            'estado' => 'ACTIVO',
        ]);

        $this->jornada = Jornada::create([
            'cod_jornada' => 'JOR_TEST_INT',
            'cod_turno' => $this->turno->cod_turno,
            'cod_usuario_apertura' => $this->enfermero->cod_usuario,
            'fecha_jornada' => today(),
            'estado' => 'ABIERTA',
        ]);

        $area = \App\Models\AreaInstitucional::first();
        AsignacionPersonal::create([
            'cod_asignacion_personal' => 'ASP_TEST_INT',
            'cod_jornada' => $this->jornada->cod_jornada,
            'cod_personal' => $this->personal->cod_personal,
            'cod_area' => $area?->cod_area ?: 'ARE_0001',
            'funcion' => 'ENFERMERO',
            'tipo_asignacion' => 'TURNO',
            'fecha_asignacion' => today(),
            'estado' => 'ACTIVO',
        ]);

        $this->residente = Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_TEST_INT',
            'nombres' => 'Rosa',
            'apellido_paterno' => 'Mamani',
            'fecha_nacimiento' => '1945-05-10',
            'estado' => 'ADMITIDO',
        ]);

        AsignacionResidenteJornada::create([
            'cod_asignacion' => 'ARJ_TEST_INT',
            'cod_residente' => $this->residente->cod_residente,
            'cod_jornada' => $this->jornada->cod_jornada,
            'cod_personal' => $this->personal->cod_personal,
            'nivel_supervision' => 'ALTO',
            'fecha_hora' => now(),
            'estado' => 'ACTIVA',
        ]);

        $this->actingAs($this->enfermero);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_cuidado_firmado_valida_baja_ingesta_y_genera_alerta_por_cambio_basal(): void
    {
        $componente = Livewire::test(RegistrosEnfermeria::class, ['codResidente' => $this->residente->cod_residente])
            ->set('codResidente', $this->residente->cod_residente)
            ->set('tipo', 'ALIMENTACION')
            ->set('subtipo', 'DESAYUNO')
            ->set('porcentaje', 25)
            ->call('guardarCuidado')
            ->assertHasErrors(['motivo']);

        $componente->set('motivo', 'Rechazo persistente de alimentos.')
            ->set('cambioBasal', 'PEOR')
            ->call('guardarCuidado')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('registros_ingesta', [
            'cod_residente' => $this->residente->cod_residente,
            'porcentaje_consumido' => 25,
            'estado' => 'VIGENTE',
        ]);

        $this->assertDatabaseHas('alertas', [
            'cod_residente' => $this->residente->cod_residente,
            'tipo' => 'CAMBIO RESPECTO AL ESTADO BASAL',
            'estado' => 'ABIERTA',
        ]);
    }

    public function test_caida_crea_incidente_lesion_y_alerta_con_datos_obligatorios(): void
    {
        Livewire::test(RegistrosEnfermeria::class, ['codResidente' => $this->residente->cod_residente])
            ->set('codResidente', $this->residente->cod_residente)
            ->set('tipoIncidente', 'CAIDA')
            ->set('lugarIncidente', 'Baño')
            ->set('descripcionIncidente', 'Residente encontrado en el piso durante la higiene.')
            ->set('presenciado', true)
            ->set('testigo', 'Auxiliar de turno')
            ->set('hayLesion', true)
            ->set('tipoLesion', 'HEMATOMA')
            ->set('zonaLesion', 'Brazo izquierdo')
            ->set('medicoInformado', true)
            ->call('guardarIncidente')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('incidentes', [
            'cod_residente' => $this->residente->cod_residente,
            'tipo_incidente' => 'CAIDA',
            'requiere_medico' => true,
        ]);

        $this->assertDatabaseHas('heridas', [
            'cod_residente' => $this->residente->cod_residente,
            'tipo_herida' => 'HEMATOMA',
            'ubicacion' => 'Brazo izquierdo',
        ]);

        $this->assertDatabaseHas('alertas', [
            'cod_residente' => $this->residente->cod_residente,
            'tipo' => 'CAIDA',
        ]);

        $herida = Herida::where('cod_residente', $this->residente->cod_residente)->firstOrFail();

        Livewire::test(RegistrosEnfermeria::class, ['codResidente' => $this->residente->cod_residente])
            ->set('lesionId', $herida->cod_herida)
            ->set('largoLesion', 3.2)
            ->set('anchoLesion', 1.5)
            ->set('aspectoLesion', 'Hematoma violáceo sin sangrado activo.')
            ->set('accionLesion', 'Aplicación de frío local y vigilancia.')
            ->call('guardarSeguimientoLesion')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('curaciones_herida', [
            'cod_herida' => $herida->cod_herida,
            'longitud' => 3.2,
            'ancho' => 1.5,
        ]);
    }
}
