<?php

namespace Tests\Feature;

use App\Frontend\Livewire\Enfermeria\Cuidados\SeguimientoDiarioPanel;
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\AsignacionResidenteJornada;
use App\Models\Atencion;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class SeguimientoDiarioPersistenciaTest extends TestCase
{
    use RefreshDatabase;

    private User $enfermera;

    private Residente $residente;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-09-26 10:00:00');
        $this->seed(DatabaseSeeder::class);

        $this->enfermera = User::factory()->create(['cod_usuario' => 'USU_SEG_DIARIO', 'estado' => 'ACTIVO']);
        $this->enfermera->assignRole('ENFERMEROS');
        $personal = Personal::query()->create([
            'cod_personal' => 'PER_SEG_DIARIO', 'cod_usuario' => $this->enfermera->cod_usuario,
            'nombres' => 'Ana', 'apellido_paterno' => 'Prueba', 'numero_documento' => 'SEG-001',
            'profesion' => 'ENFERMERIA', 'estado' => 'ACTIVO',
        ]);
        $turno = Turno::query()->create([
            'cod_turno' => 'TUR_SEG_DIARIO', 'nombre' => 'Mañana', 'hora_inicio' => '07:00:00',
            'hora_cierre' => '15:00:00', 'orden' => 1, 'estado' => 'ACTIVO',
        ]);
        $jornada = Jornada::query()->create([
            'cod_jornada' => 'JOR_SEG_DIARIO', 'cod_turno' => $turno->cod_turno,
            'fecha_jornada' => today(), 'estado' => 'ABIERTA',
        ]);
        $area = Area::query()->firstOrCreate(
            ['cod_area' => 'ARE_SEG_DIARIO'],
            ['nombre' => 'Área seguimiento', 'estado' => 'ACTIVA'],
        );
        AsignacionPersonal::query()->create([
            'cod_asignacion_personal' => 'ASP_SEG_DIARIO', 'cod_jornada' => $jornada->cod_jornada,
            'cod_personal' => $personal->cod_personal, 'cod_area' => $area->cod_area,
            'funcion' => 'ENFERMERO', 'tipo_asignacion' => 'TURNO',
            'fecha_asignacion' => today(), 'estado' => 'ACTIVO',
        ]);
        $this->residente = Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_SEG_DIARIO', 'nombres' => 'Rosa', 'apellido_paterno' => 'Flores',
            'fecha_nacimiento' => '1940-01-01', 'estado' => 'ADMITIDO',
        ]);
        AsignacionResidenteJornada::query()->create([
            'cod_asignacion' => 'ARJ_SEG_DIARIO', 'cod_residente' => $this->residente->cod_residente,
            'cod_jornada' => $jornada->cod_jornada, 'cod_personal' => $personal->cod_personal,
            'nivel_supervision' => 'ESTANDAR', 'fecha_hora' => now(), 'estado' => 'ACTIVA',
        ]);

        $this->actingAs($this->enfermera);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_seguimiento_persiste_datos_clinicos_en_tablas_normalizadas_y_corrige_sin_duplicar(): void
    {
        $componente = Livewire::test(SeguimientoDiarioPanel::class)
            ->call('abrirCrear')
            ->set('codResidente', $this->residente->cod_residente)
            ->set('estadoGeneral', 'VIGILANCIA')
            ->set('tipoComida', 'DESAYUNO')
            ->set('alimentacion', 'PARCIAL')
            ->set('porcentajeAlimentacion', '50')
            ->set('tipoLiquido', 'AGUA')
            ->set('cantidadHidratacionMl', '250')
            ->set('hidratacion', 'ADECUADA')
            ->set('movilidad', 'ASISTIDA')
            ->set('intentoCaminarSolo', true)
            ->set('sueno', 'INTERRUMPIDO')
            ->set('orientacion', 'PARCIALMENTE_ORIENTADO')
            ->set('repitePreguntas', true)
            ->set('conducta', 'ANSIOSO')
            ->set('participacion', 'PARCIAL')
            ->set('observacion', 'Seguimiento clínico realizado durante el turno de la mañana.')
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('atenciones', ['cod_residente' => 'RES_SEG_DIARIO', 'motivo' => 'SEGUIMIENTO_DIARIO:VIGILANCIA']);
        $this->assertDatabaseHas('registros_ingesta', ['tipo_comida' => 'DESAYUNO', 'porcentaje_consumido' => 50]);
        $this->assertDatabaseHas('registros_hidratacion', ['tipo_liquido' => 'AGUA', 'cantidad_ml' => 250]);
        $this->assertDatabaseHas('registros_movilidad', ['marcha' => 'ASISTIDA', 'riesgo_caida' => 'INTENTO_CAMINAR_SOLO']);
        $this->assertDatabaseHas('registros_sueno', ['calidad' => 'INTERRUMPIDO']);
        $this->assertDatabaseHas('controles_cognitivos', ['repite_preguntas' => true]);
        $this->assertDatabaseHas('registros_conductuales', ['estado_animo' => 'ANSIOSO', 'participacion' => 'PARCIAL']);

        $atencion = Atencion::query()->where('cod_residente', 'RES_SEG_DIARIO')->firstOrFail();
        $componente->call('abrirEditar', $atencion->cod_atencion)
            ->set('porcentajeAlimentacion', '75')
            ->set('cantidadHidratacionMl', '300')
            ->set('observacion', 'Corrección verificada del seguimiento clínico del turno.')
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('atenciones', 1);
        $this->assertDatabaseCount('registros_ingesta', 1);
        $this->assertDatabaseCount('registros_hidratacion', 1);
        $this->assertDatabaseHas('registros_ingesta', ['porcentaje_consumido' => 75]);
        $this->assertDatabaseHas('registros_hidratacion', ['cantidad_ml' => 300]);
    }

    public function test_seguimiento_rechaza_hidratacion_sin_cantidad_real(): void
    {
        Livewire::test(SeguimientoDiarioPanel::class)
            ->call('abrirCrear')
            ->set('codResidente', $this->residente->cod_residente)
            ->set('estadoGeneral', 'ESTABLE')
            ->set('tipoComida', 'DESAYUNO')
            ->set('alimentacion', 'COMPLETA')
            ->set('porcentajeAlimentacion', '100')
            ->set('tipoLiquido', 'AGUA')
            ->set('hidratacion', 'ADECUADA')
            ->set('movilidad', 'INDEPENDIENTE')
            ->set('sueno', 'NORMAL')
            ->set('observacion', 'Registro sin cantidad de hidratación para validar el rechazo.')
            ->call('guardar')
            ->assertHasErrors(['cantidadHidratacionMl']);

        $this->assertDatabaseCount('atenciones', 0);
        $this->assertDatabaseCount('registros_hidratacion', 0);
    }
}
