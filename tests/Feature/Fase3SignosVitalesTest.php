<?php

namespace Tests\Feature;

use App\Backend\Modulos\Clinica\Servicios\SignosVitalesService;
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\AsignacionResidenteJornada;
use App\Models\EventoAlerta;
use App\Models\Jornada;
use App\Models\Residente;
use App\Models\SignoVital;
use App\Models\Turno;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class Fase3SignosVitalesTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;

    private Residente $residente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-10-06 10:00:00'));
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->usuario = User::factory()->create(['nombres' => 'Autora sintética']);
        $this->usuario->assignRole('ENFERMEROS');
        $this->residente = Residente::factory()->create();
        $area = Area::create(['cod_area' => 'ARE_F3_SV', 'nombre' => 'Enfermería sintética', 'estado' => 'ACTIVA']);
        $turno = Turno::create(['cod_turno' => 'TUR_F3_SV', 'nombre' => 'Mañana', 'orden' => 1,
            'hora_inicio' => '07:00:00', 'hora_cierre' => '15:00:00', 'estado' => 'ACTIVO']);
        $jornada = Jornada::create(['cod_jornada' => 'JOR_F3_SV', 'cod_turno' => $turno->cod_turno,
            'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
        AsignacionPersonal::create(['cod_asignacion_personal' => 'ASP_F3_SV', 'cod_personal' => $this->usuario->personal->cod_personal,
            'cod_area' => $area->cod_area, 'cod_jornada' => $jornada->cod_jornada,
            'tipo_asignacion' => 'RESPONSABLE', 'fecha_asignacion' => now(), 'estado' => 'ACTIVA']);
        AsignacionResidenteJornada::create(['cod_residente' => $this->residente->cod_residente,
            'cod_personal' => $this->usuario->personal->cod_personal, 'cod_jornada' => $jornada->cod_jornada,
            'nivel_supervision' => 'DIRECTA', 'fecha_hora' => now(), 'estado' => 'ACTIVA']);
        $this->actingAs($this->usuario);
    }

    public static function sinSigno(): array
    {
        return ['peso' => [['peso' => 65]], 'dolor' => [['dolor' => 4]], 'ambos' => [['peso' => 65, 'dolor' => 4]]];
    }

    #[DataProvider('sinSigno')]
    public function test_peso_o_dolor_no_crea_signo_vacio_en_servicio(array $datos): void
    {
        try {
            app(SignosVitalesService::class)->registrar($this->residente->cod_residente, $datos, $this->usuario);
            $this->fail('Peso y dolor no son las mediciones persistibles de este registro.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('general', $e->errors());
        }
        $this->assertDatabaseCount('signos_vitales', 0);
        $this->assertDatabaseCount('alertas', 0);
        $this->assertDatabaseCount('eventos_alerta', 0);
    }

    #[DataProvider('sinSigno')]
    public function test_request_no_acepta_peso_o_dolor_como_minimo(array $datos): void
    {
        $this->postJson(route('admin.adultos-mayores.signos-vitales.store', $this->residente), $datos)
            ->assertUnprocessable()->assertJsonValidationErrors('signos');
        $this->assertDatabaseCount('signos_vitales', 0);
    }

    public static function signosValidos(): array
    {
        return ['temperatura' => [['temperatura' => 36.8]], 'glucemia' => [['glucosa' => 100]],
            'presion' => [['presion_sistolica' => 120, 'presion_diastolica' => 80]],
            'varios' => [['temperatura' => 36.8, 'frecuencia_cardiaca' => 75, 'saturacion' => 96]]];
    }

    #[DataProvider('signosValidos')]
    public function test_medicion_parcial_valida_persiste_con_autor_propio_y_conserva_historia(array $datos): void
    {
        $servicio = app(SignosVitalesService::class);
        $anterior = $servicio->registrar($this->residente->cod_residente, ['temperatura' => 36.7], $this->usuario);
        $nuevo = $servicio->registrar($this->residente->cod_residente, $datos + ['cod_personal' => 'PERSONAL_AJENO'], $this->usuario);
        $this->assertSame($this->usuario->personal->cod_personal, $nuevo->cod_personal);
        $this->assertDatabaseCount('signos_vitales', 2);
        $this->assertSame(36.7, $anterior->fresh()->temperatura);
        $this->assertNotSame($anterior->cod_signo, $nuevo->cod_signo);
    }

    public function test_presion_incompleta_no_persiste(): void
    {
        try {
            app(SignosVitalesService::class)->registrar($this->residente->cod_residente, ['presion_sistolica' => 120], $this->usuario);
            $this->fail('La presión requiere ambos componentes.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('presion_arterial', $e->errors());
        }
        $this->assertDatabaseCount('signos_vitales', 0);
    }

    public function test_fallo_en_evento_critico_revierte_signo_alerta_y_evento_con_historia_intacta(): void
    {
        $servicio = app(SignosVitalesService::class);
        $anterior = $servicio->registrar($this->residente->cod_residente, ['temperatura' => 36.7], $this->usuario);
        EventoAlerta::creating(fn () => throw new \RuntimeException('Fallo técnico sintético de evento'));
        try {
            $servicio->registrar($this->residente->cod_residente, ['presion_sistolica' => 210, 'presion_diastolica' => 120], $this->usuario);
            $this->fail('Debe fallar la persistencia del evento crítico.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Fallo técnico sintético de evento', $e->getMessage());
        }
        $this->assertDatabaseCount('signos_vitales', 1);
        $this->assertDatabaseCount('alertas', 0);
        $this->assertDatabaseCount('eventos_alerta', 0);
        $this->assertSame(36.7, $anterior->fresh()->temperatura);
    }

    public function test_excepcion_tecnica_http_se_reporta_sin_exponer_detalle(): void
    {
        Exceptions::fake();
        SignoVital::creating(fn () => throw new \RuntimeException('SQLSTATE sintético /srv/privado.php credencial_sintetica'));
        $this->post(route('admin.adultos-mayores.signos-vitales.store', $this->residente), ['temperatura' => 36.8])
            ->assertRedirect()->assertSessionHas('error', 'No se pudieron registrar los signos vitales. Inténtelo nuevamente.');
        $this->assertDatabaseCount('signos_vitales', 0);
        $this->assertStringNotContainsString('SQLSTATE', session('error'));
        $this->assertStringNotContainsString('credencial_sintetica', session('error'));
        Exceptions::assertReported(fn (\RuntimeException $e) => str_contains($e->getMessage(), 'credencial_sintetica'));
    }
    public function test_hora_real_se_conserva_y_auditoria_registra_el_momento_tecnico(): void
    {
        $fecha = now()->subHours(2)->format('Y-m-d\TH:i');
        $registro = app(SignosVitalesService::class)->registrarConEvaluacion($this->residente->cod_residente,
            ['frecuencia_cardiaca' => 72, 'fecha_hora' => $fecha], $this->usuario);
        $this->assertSame($fecha, $registro->signo->fresh()->fecha_hora->format('Y-m-d\TH:i'));
        $actividad = \Spatie\Activitylog\Models\Activity::query()->where('event', 'registro_signos_vitales')
            ->where('subject_id', $registro->signo->cod_signo)->firstOrFail();
        $this->assertSame($this->usuario->cod_usuario, $actividad->causer_id);
        $this->assertTrue($actividad->created_at->gt($registro->signo->fecha_hora));
        $this->assertEquals($registro->signo->fecha_hora, Carbon::parse($actividad->properties['fecha_medicion']));
    }

    public function test_hora_futura_no_persiste_ni_genera_alerta(): void
    {
        $antes = SignoVital::count();
        try {
            app(SignosVitalesService::class)->registrarConEvaluacion($this->residente->cod_residente,
                ['frecuencia_cardiaca' => 135, 'fecha_hora' => now()->addMinute()->format('Y-m-d\TH:i')], $this->usuario);
            $this->fail('La hora futura debe rechazarse.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('fecha_hora', $e->errors());
        }
        $this->assertSame($antes, SignoVital::count());
        $this->assertDatabaseCount('alertas', 0);
    }

    public function test_captura_invalida_no_oculta_otra_medicion_critica(): void
    {
        $evaluacion = app(SignosVitalesService::class)->preEvaluar(
            ['frecuencia_cardiaca' => 135, 'temperatura' => '6'],
            $this->residente->cod_residente, $this->usuario, capturaParcial: true);
        $this->assertSame('CRITICO', $evaluacion->severidadGlobal()->value);
        $this->assertArrayHasKey('temperatura', $evaluacion->erroresCaptura);
        $this->assertCount(1, $evaluacion->resultados);
        $this->assertDatabaseCount('signos_vitales', 0);
        $this->assertDatabaseCount('alertas', 0);
    }

    public function test_fecha_clinica_usa_version_historica_del_objetivo_y_excluye_lecturas_posteriores(): void
    {
        $medico = User::factory()->create();
        $medico->assignRole('MEDICO GENERAL/GERIATRA');
        \App\Models\Personal::create(['cod_personal' => 'PER_PRI_MED', 'cod_usuario' => $medico->cod_usuario,
            'nombres' => 'Médico', 'apellido_paterno' => 'Sintético', 'numero_documento' => 'PRI-MED-01',
            'profesion' => 'MEDICINA', 'estado' => 'ACTIVO']);
        $datos = ['cod_residente' => $this->residente->cod_residente, 'cod_personal' => $medico->personal->cod_personal,
            'parametro' => 'frecuencia_cardiaca', 'min_objetivo' => 60, 'max_objetivo' => 80,
            'vigente_desde' => now()->subDays(2), 'vigente_hasta' => now()->subHour(), 'estado' => 'REEMPLAZADO', 'motivo' => 'Objetivo previo sintético'];
        \App\Models\ObjetivoSignoVital::create($datos);
        \App\Models\ObjetivoSignoVital::create(array_replace($datos, ['min_objetivo' => 100, 'max_objetivo' => 110,
            'vigente_desde' => now()->subHour(), 'vigente_hasta' => null, 'estado' => 'VIGENTE']));
        SignoVital::create(['cod_residente' => $this->residente->cod_residente, 'cod_personal' => $this->usuario->personal->cod_personal,
            'fecha_hora' => now()->subMinutes(30), 'frecuencia_cardiaca' => 105, 'estado' => 'ACTIVO']);
        $evaluador = app(\App\Backend\Modulos\Clinica\SignosVitales\EvaluadorSignosVitales::class);
        $historica = $evaluador->evaluar(['frecuencia_cardiaca' => 72, 'fecha_hora' => now()->subHours(2)], $this->residente->cod_residente);
        $this->assertSame('OBJETIVO_PERSONALIZADO', $historica->severidadGlobal()->value);
        $this->assertSame([], $historica->contextoHistorico);
        $actual = $evaluador->evaluar(['frecuencia_cardiaca' => 72], $this->residente->cod_residente);
        $this->assertSame('ADVERTENCIA', $actual->severidadGlobal()->value);
        $this->assertCount(1, $actual->contextoHistorico);
    }

    public function test_otro_control_se_bloquea_hasta_intervencion_real_y_historia_sigue_critica(): void
    {
        $registro = app(SignosVitalesService::class)->registrarConEvaluacion($this->residente->cod_residente,
            ['frecuencia_cardiaca' => 135], $this->usuario);
        $turnos = app(\App\Backend\Modulos\Enfermeria\Servicios\TurnoEnfermeriaService::class);
        foreach (['ABIERTA', 'RECONOCIDA', 'ASIGNADA', 'PENDIENTE'] as $estado) {
            $registro->alerta->update(['estado' => $estado]);
            try {
                $turnos->autorizarMutacionEnfermeria($this->residente->cod_residente, 'valoraciones_dolor.crear', $this->usuario);
                $this->fail('No debe iniciar otro control sin atención.');
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('continuidad_signos', $e->errors());
            }
        }
        $turnos->autorizarMutacionEnfermeria($this->residente->cod_residente, 'signos_vitales.crear', $this->usuario);
        app(\App\Backend\Modulos\Alertas\Servicios\AlertasService::class)->registrarIntervencion(
            $registro->alerta, 'Se revisó al residente y se comunicó la lectura al médico.', $this->usuario);
        $this->assertSame('EN_ATENCION', $registro->alerta->fresh()->estado);
        $turnos->autorizarMutacionEnfermeria($this->residente->cod_residente, 'valoraciones_dolor.crear', $this->usuario);
        $this->assertDatabaseHas('eventos_alerta', ['cod_alerta' => $registro->alerta->cod_alerta, 'tipo_evento' => 'INTERVENCION']);
        $this->assertSame(135.0, $registro->signo->fresh()->frecuencia_cardiaca);
        $this->assertSame('CRITICO', $registro->evaluacion->severidadGlobal()->value);
        $this->assertSame('CRITICO', app(\App\Backend\Modulos\Clinica\SignosVitales\EvaluadorSignosVitales::class)
            ->evaluar(['frecuencia_cardiaca' => $registro->signo->frecuencia_cardiaca], $this->residente->cod_residente)->severidadGlobal()->value);
    }

    public function test_valoracion_dolor_alternativa_no_elude_alerta_critica_pendiente(): void
    {
        $registro = app(SignosVitalesService::class)->registrarConEvaluacion($this->residente->cod_residente,
            ['frecuencia_cardiaca' => 135], $this->usuario);
        $antes = \App\Models\ValoracionDolor::count();
        try {
            app(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::class)->registrarDolor(
                $this->residente->cod_residente, 'VALORACION', 3, 'Registro sintético de valoración.', $this->usuario);
            $this->fail('La ruta alternativa no debe eludir la continuidad.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('continuidad_signos', $e->errors());
        }
        $this->assertSame($antes, \App\Models\ValoracionDolor::count());
        $this->assertSame('ABIERTA', $registro->alerta->fresh()->estado);
    }

    public function test_panel_permite_intervencion_en_alertas_reconocidas_asignadas_y_pendientes(): void
    {
        $registro = app(SignosVitalesService::class)->registrarConEvaluacion($this->residente->cod_residente,
            ['frecuencia_cardiaca' => 135], $this->usuario);
        foreach (['RECONOCIDA', 'ASIGNADA', 'PENDIENTE'] as $estado) {
            $registro->alerta->update(['estado' => $estado]);
            \Livewire\Livewire::test(\App\Frontend\Livewire\Compartido\Alertas\AlertasPanel::class)
                ->call('verDetalle', $registro->alerta->cod_alerta)->assertSee('Registrar intervención')
                ->set('accion', 'Se valoró al residente y se comunicó la lectura al médico.')
                ->call('guardarAccion')->assertHasNoErrors();
            $this->assertSame('EN_ATENCION', $registro->alerta->fresh()->estado);
            $this->assertDatabaseHas('eventos_alerta', ['cod_alerta' => $registro->alerta->cod_alerta,
                'tipo_evento' => 'INTERVENCION', 'estado_anterior' => $estado, 'cod_usuario' => $this->usuario->cod_usuario]);
        }
        \Livewire\Livewire::test(\App\Frontend\Livewire\Enfermeria\Cuidados\MisPacientes::class)
            ->call('seleccionarResidente', $this->residente->cod_residente)->call('mostrarSelectorRegistro')
            ->call('abrirFormularioRegistro', 'signos')
            ->assertSet('signosHistorial.0.resultados.0.severidad', 'CRITICO')
            ->assertSet('signosHistorial.0.alerta_estado', 'EN_ATENCION');
    }

}
