<?php

namespace Tests\Feature;

use App\Models\ObjetivoSignoVital;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\SignoVital;
use App\Models\User;
use App\Modulos\Clinica\Aplicacion\Acciones\EvaluadorSignosVitales;
use App\Modulos\Clinica\Aplicacion\Consultas\InterpretacionSignosVitales;
use App\Modulos\Clinica\Aplicacion\Consultas\AnalisisSignosPanel;
use App\Modulos\Clinica\Aplicacion\Consultas\ResumenLongitudinalResidente;
use App\Modulos\Clinica\Presentacion\Adaptadores\ClasificacionSignosVitalesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InterpretacionSignosUnificadaTest extends TestCase
{
    use RefreshDatabase;

    private Residente $residente;
    private string $personal;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 10, 17)->setTime(12, 0));
        $this->residente = Residente::factory()->create();
        $this->personal = Personal::create(['cod_personal' => 'PER_INTER_TEST',
            'cod_usuario' => User::factory()->create()->cod_usuario, 'nombres' => 'Profesional',
            'apellido_paterno' => 'Sintético', 'numero_documento' => 'INTER-TEST-01',
            'profesion' => 'MEDICINA', 'estado' => 'ACTIVO'])->cod_personal;
    }

    private function signo(array $datos = []): SignoVital
    {
        return SignoVital::create($datos + ['cod_residente' => $this->residente->cod_residente,
            'cod_personal' => $this->personal, 'fecha_hora' => now()->subHour(), 'estado' => 'VIGENTE']);
    }

    private function grafica(): array
    {
        return app(ResumenLongitudinalResidente::class)->consultar($this->residente->fresh())['grafica_signos'];
    }

    public function test_tarjetas_historial_y_puntos_usan_la_misma_interpretacion_del_motor(): void
    {
        $signo = $this->signo(['frecuencia_cardiaca' => 55, 'saturacion_oxigeno' => 89, 'temperatura' => 37.6]);
        $grafica = $this->grafica();
        $esperado = app(EvaluadorSignosVitales::class)->evaluar($signo->only([
            'fecha_hora', 'frecuencia_cardiaca', 'saturacion_oxigeno', 'temperatura',
        ]), $signo->cod_residente, collect(), false)->toArray()['resultados'];

        $this->assertSame('En referencia', $grafica['metricas_info']['FC']['estado']);
        $this->assertSame('Sin interpretación automática', $grafica['metricas_info']['SPO2']['estado']);
        $this->assertSame('En referencia', $grafica['metricas_info']['TEMP']['estado']);
        $this->assertSame($esperado, $grafica['registros_historicos'][0]['resultados']);
        $this->assertSame($grafica['metricas_info']['FC']['estado'], $grafica['interpretaciones'][0]['FC']['label']);
        $this->assertSame($esperado[1]['explicacion'], $grafica['interpretaciones'][0]['TEMP']['message']);
    }

    public function test_historial_sin_mediciones_no_se_presenta_como_normal(): void
    {
        $this->signo();
        $grafica = $this->grafica();
        $this->assertSame('Sin datos', $grafica['registros_historicos'][0]['estado']);
        $this->assertSame('Sin datos', $grafica['metricas_info']['PA']['estado']);
    }

    public function test_objetivo_historico_se_respeta_en_ficha_y_panel_aunque_fue_reemplazado(): void
    {
        ObjetivoSignoVital::create(['cod_residente' => $this->residente->cod_residente,
            'cod_personal' => $this->personal, 'parametro' => 'saturacion_oxigeno',
            'min_objetivo' => 88, 'max_objetivo' => 92, 'min_critico' => 85,
            'vigente_desde' => now()->subDays(2), 'vigente_hasta' => now()->subMinutes(30),
            'estado' => 'REEMPLAZADO', 'motivo' => 'Objetivo sintético previo.']);
        ObjetivoSignoVital::create(['cod_residente' => $this->residente->cod_residente,
            'cod_personal' => $this->personal, 'parametro' => 'saturacion_oxigeno',
            'min_objetivo' => 94, 'max_objetivo' => 98, 'min_critico' => 90,
            'vigente_desde' => now()->subMinutes(30), 'estado' => 'VIGENTE', 'motivo' => 'Objetivo sintético actual.']);
        $signo = $this->signo(['saturacion_oxigeno' => 89]);
        // La relación de conveniencia carga sólo VIGENTE: no sirve para una toma histórica.
        $signo->load('residente.objetivosSignosVitales');
        $this->assertSame('normal', ClasificacionSignosVitalesService::evaluarRegistro($signo)['saturacion']);
        $this->assertSame('En objetivo médico', $this->grafica()['metricas_info']['SPO2']['estado']);
        $this->assertSame(89.0, $signo->fresh()->saturacion_oxigeno);
        $this->assertSame([], app(AnalisisSignosPanel::class)->detectarAlertas(
            ['saturacion' => 89, 'fecha_hora' => 'fecha inválida'], $signo->cod_residente));
    }

    public function test_critico_general_no_se_diluye_por_otra_medicion_normal_o_un_objetivo(): void
    {
        ObjetivoSignoVital::create(['cod_residente' => $this->residente->cod_residente,
            'cod_personal' => $this->personal, 'parametro' => 'frecuencia_cardiaca',
            'min_objetivo' => 30, 'max_objetivo' => 140, 'vigente_desde' => now()->subDay(),
            'estado' => 'VIGENTE', 'motivo' => 'Objetivo sintético amplio.']);
        $this->signo(['presion_sistolica' => 110, 'presion_diastolica' => 70, 'frecuencia_cardiaca' => 131]);
        $grafica = $this->grafica();
        $this->assertSame('Crítico', $grafica['registros_historicos'][0]['estado']);
        $this->assertSame('Crítico', $grafica['metricas_info']['FC']['estado']);
        $this->assertSame('CRITICO', $grafica['interpretaciones'][0]['FC']['severidad']);
        $this->assertSame(0, DB::table('alertas')->count(), 'Consultar no genera alertas ni modifica historia.');
    }

    public function test_lectura_alta_aislada_sugiere_revision_sin_inventar_alerta(): void
    {
        $this->signo(['glucemia' => 251]);
        $grafica = $this->grafica();
        $this->assertSame('Revisión sugerida', $grafica['metricas_info']['GLUCOSA']['estado']);
        $this->assertNull($grafica['interpretaciones'][0]['GLUCOSA']['severidad']);
        $this->assertSame(0, DB::table('alertas')->count());
    }

    public function test_precarga_de_objetivos_es_constante_para_varios_controles(): void
    {
        foreach (range(1, 10) as $i) {
            $this->signo(['frecuencia_cardiaca' => 70 + $i]);
        }
        $signos = SignoVital::all();
        DB::enableQueryLog();
        DB::flushQueryLog();
        $resultados = app(InterpretacionSignosVitales::class)->coleccion($signos);
        $this->assertCount(10, $resultados);
        $this->assertCount(1, DB::getQueryLog());
        DB::disableQueryLog();
    }

    public function test_cambiar_configuracion_del_motor_no_deja_umbrales_alternativos_en_paneles(): void
    {
        config(['signos_vitales.pulso.normal_alta' => 80]);
        $signo = $this->signo(['frecuencia_cardiaca' => 85]);
        $analisis = app(AnalisisSignosPanel::class);
        $analisis->preparar(collect([$signo]));
        $this->assertSame('observacion', $analisis->analizarRegistro($signo)['key']);
        $this->assertSame('Advertencia', $analisis->construirMetricas(collect([$signo]))[1]['status_label']);
        $this->assertSame('Advertencia', $this->grafica()['metricas_info']['FC']['estado']);
        $this->assertSame('advertencia', ClasificacionSignosVitalesService::evaluarRegistro($signo)['fc']);
        $this->assertCount(1, $analisis->detectarAlertas(['frecuencia_cardiaca' => 85], $signo->cod_residente));
        $this->assertCount(0, $analisis->detectarAlertas(['saturacion' => 89], $signo->cod_residente));
    }

    public function test_el_periodo_no_inventa_controles_y_los_anulados_solo_se_conservan_en_historial(): void
    {
        $this->signo(['fecha_hora' => now()->subHours(25), 'frecuencia_cardiaca' => 70]);
        $this->signo(['fecha_hora' => now()->subMinutes(30), 'frecuencia_cardiaca' => 135, 'estado' => 'ANULADO']);
        $vigente = $this->signo(['frecuencia_cardiaca' => 75]);
        $grafica = app(ResumenLongitudinalResidente::class)->consultar($this->residente->fresh(), '24h')['grafica_signos'];
        $this->assertSame([75.0], $grafica['fc']);
        $this->assertCount(1, $grafica['interpretaciones']);
        $this->assertCount(3, $grafica['registros_historicos']);
        $this->assertSame('Anulado', $grafica['registros_historicos'][0]['estado']);
        $this->assertSame($vigente->cod_signo, $grafica['ultimo']->cod_signo);
        $this->assertSame('En referencia', $grafica['metricas_info']['FC']['estado']);
    }
}
