<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ControlCognitivo;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\EjecutarObservaciones;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\GestionarReglasExpertas as Gestor;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarHallazgosObservacionales;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarObservacionesConservadas as Lector;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\ConocimientoObservacional;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\ConservacionObservacional;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ResultadosExpertoResidente;
use Carbon\CarbonImmutable;
use Database\Seeders\RolesAndPermissionsSeeder;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;
use MotorExperto\dominio\conocimiento\reglas\EvaluadorObservaciones as Motor;
use Tests\TestCase;

final class SucesionObservacionalTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    private Residente $residente;

    private ControlCognitivo $control;

    private array $paquete;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 10, 18)->setTime(12, 0));
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->actor = User::factory()->create(['estado' => 'ACTIVO']);
        $this->actor->assignRole('MEDICO GENERAL/GERIATRA');
        $personal = Personal::create(['cod_usuario' => $this->actor->getKey(), 'nombres' => 'Profesional',
            'apellido_paterno' => 'Sintético', 'numero_documento' => 'OBS-SUCESION', 'profesion' => 'MEDICINA', 'estado' => 'ACTIVO']);
        $this->actingAs($this->actor);
        $this->residente = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $this->control = ControlCognitivo::create(['cod_residente' => $this->residente->getKey(), 'cod_personal' => $personal->getKey(),
            'fecha_hora' => now()->subHour(), 'estado' => 'VIGENTE', 'repite_preguntas' => true, 'olvida_indicaciones' => false, 'cambio_cognitivo' => null]);
        $this->paquete = app(ConocimientoObservacional::class)->preparar($this->actor);
    }

    private function ejecutar(?string $solicitud = null): array
    {
        $id = app(EjecutarObservaciones::class)->ejecutar($this->actor, $this->residente, $solicitud ?? (string) Str::uuid());

        return app(Lector::class)->consultar($this->actor, $this->residente, $id);
    }

    private function nuevaRegla(): array
    {
        $r = $this->paquete['reglas']['OBS-MEM-01'];

        return ['codigo' => 'OBS-MEM-NUEVA', 'nombre' => 'Nueva revisión de la observación', 'descripcion' => 'Conservar la observación literal.',
            'fundamento' => 'Cambio técnico de regla sin modificar el significado del indicador.',
            'condiciones' => [['variable' => $r['variable'], 'valor' => $this->paquete['valores']['SI']]],
            'criterio_dominio' => $this->paquete['criterios'][$r['criterio']]['dominio'], 'valor_consecuencia' => $r['valorResultado']];
    }

    public function test_archivo_modifica_la_nueva_ejecucion_y_preserva_historia_y_reintento(): void
    {
        $uuid = (string) Str::uuid();
        $antes = $this->ejecutar($uuid);
        $version = app(Gestor::class)->crearRevision($this->actor, $this->paquete['version'], 'ORION-OBS-ARCHIVO',
            'Archivar repetición conservando los resultados anteriores.', retiradas: [$this->paquete['reglas']['OBS-MEM-01']['regla']]);
        $this->control->update(['repite_preguntas' => false, 'olvida_indicaciones' => true]);
        self::assertSame($antes['id'], $this->ejecutar($uuid)['id']);
        self::assertSame($antes['informe_completo'], app(Lector::class)->consultar($this->actor, $this->residente, $antes['id'])['informe_completo']);
        $despues = $this->ejecutar();
        self::assertSame($version, DB::table('evaluaciones_expertas')->where('cod_evaluacion_experta', $despues['id'])->value('cod_version_modelo'));
        self::assertArrayNotHasKey('OBS-MEM-01', $despues['resumen']);
        self::assertSame('CUMPLE', $despues['resumen']['OBS-MEM-02']['estado']);
        self::assertCount(2, $despues['resumen']);
        self::assertSame('ORION-OBS-ARCHIVO', app(ConsultarHallazgosObservacionales::class)->consultar($this->actor, $this->residente)['version']);
        self::assertDatabaseCount('clasificaciones_dcl_instantanea', 0);
    }

    public function test_rechazo_posterior_no_reinterpreta_la_ejecucion_anterior(): void
    {
        $antes = $this->ejecutar();
        app(Gestor::class)->revisar($this->actor, $this->paquete['reglas']['OBS-MEM-01']['regla'], 'RECHAZADA', 'Retirar esta definición de las ejecuciones futuras.');
        self::assertSame($antes['informe_completo'], app(Lector::class)->consultar($this->actor, $this->residente, $antes['id'])['informe_completo']);
        self::assertArrayNotHasKey('OBS-MEM-01', $this->ejecutar()['resumen']);
        self::assertCount(3, app(Lector::class)->consultar($this->actor, $this->residente, $antes['id'])['resumen']);
    }

    public function test_dos_reglas_comparten_evidencia_sin_duplicar_hechos_ni_consecuencia(): void
    {
        app(Gestor::class)->crearRevision($this->actor, $this->paquete['version'], 'ORION-OBS-COMPARTIDA', 'Añadir otra definición sobre el mismo indicador.', nueva: $this->nuevaRegla());
        $resultado = $this->ejecutar();
        self::assertCount(4, $resultado['resumen']);
        self::assertSame('CUMPLE', $resultado['resumen']['OBS-MEM-NUEVA']['estado']);
        self::assertDatabaseCount('hechos_instantanea_experta', 4);
        self::assertDatabaseCount('evidencias_evaluacion', 3);
        self::assertDatabaseCount('evaluacion_criterios', 3);
        self::assertDatabaseCount('resultados_criterio', 1);
        self::assertDatabaseCount('evaluaciones_reglas', 4);
        self::assertDatabaseCount('evidencias_soporte_condicion', 4);
        self::assertSame('OBSERVACIONAL', DB::table('reglas_expertas')->where('codigo_regla', 'OBS-MEM-NUEVA')->value('tipo_regla'));
    }

    public function test_sustituir_usa_la_nueva_definicion_y_su_antecesora_original(): void
    {
        app(Gestor::class)->crearRevision($this->actor, $this->paquete['version'], 'ORION-OBS-SUSTITUCION', 'Sustituir la definición manteniendo su trazabilidad.',
            nueva: $this->nuevaRegla(), sustituye: $this->paquete['reglas']['OBS-MEM-01']['regla']);
        $r = $this->ejecutar();
        self::assertCount(3, $r['resumen']);
        self::assertArrayNotHasKey('OBS-MEM-01', $r['resumen']);
        self::assertSame('CUMPLE', $r['resumen']['OBS-MEM-NUEVA']['estado']);
        self::assertSame($this->paquete['reglas']['OBS-MEM-01']['regla'], DB::table('reglas_expertas')->where('codigo_regla', 'OBS-MEM-NUEVA')->value('cod_regla_anterior'));
    }

    public function test_archivar_todas_las_reglas_conserva_fuentes_sin_inventar_hallazgos(): void
    {
        app(Gestor::class)->crearRevision($this->actor, $this->paquete['version'], 'ORION-OBS-SIN-REGLAS', 'Retirar todas las definiciones de esta revisión.',
            retiradas: array_column($this->paquete['reglas'], 'regla'));
        $r = $this->ejecutar();
        self::assertSame([], $r['resumen']);
        self::assertSame([], $r['resultados']);
        self::assertDatabaseCount('hechos_instantanea_experta', 4);
        self::assertDatabaseCount('evidencias_evaluacion', 3);
        self::assertDatabaseCount('evaluaciones_reglas', 0);
        self::assertDatabaseCount('resultados_criterio', 0);
    }

    public function test_no_publica_una_condicion_negativa_como_hallazgo_afirmativo(): void
    {
        $auditoriaAntes = DB::table('activity_log')->count();
        $nueva = $this->nuevaRegla();
        $nueva['condiciones'][0]['valor'] = $this->paquete['valores']['NO'];
        try {
            app(Gestor::class)->crearRevision($this->actor, $this->paquete['version'], 'ORION-OBS-INCOMPATIBLE', 'Esta condición no corresponde a la consecuencia.', nueva: $nueva);
            self::fail('Debe rechazar la definición incompatible.');
        } catch (DomainException $e) {
            self::assertStringContainsString('afirmativo', $e->getMessage());
        }
        self::assertDatabaseCount('versiones_modelo_experto', 1);
        self::assertDatabaseCount('reglas_expertas', 3);
        self::assertDatabaseCount('activity_log', $auditoriaAntes);
    }

    public function test_lector_del_contrato_anterior_admite_retiro_posterior_de_su_version(): void
    {
        $lectura = app(ConsultarHallazgosObservacionales::class)->instantaneaCompleta($this->actor, $this->residente, CarbonImmutable::now());
        $informe = (new Motor)->evaluar($this->residente->getKey(), now()->toDateTimeImmutable(), $lectura['instantanea']);
        $paqueteAntiguo = app(ConocimientoObservacional::class)->verificar($this->paquete['version']);
        $id = DB::transaction(fn () => app(ConservacionObservacional::class)->guardar($this->residente->getKey(), $this->control->cod_personal,
            (string) Str::uuid(), $paqueteAntiguo, $lectura['instantanea'], $informe));
        self::assertSame('OBS-SNAP-1', DB::table('evaluaciones_expertas')->where('cod_evaluacion_experta', $id)->value('version_conservacion'));
        $antes = app(Lector::class)->consultar($this->actor, $this->residente, $id);
        app(Gestor::class)->crearRevision($this->actor, $this->paquete['version'], 'ORION-OBS-HISTORIA', 'Retiro posterior que no cambia la historia conservada.');
        self::assertSame($antes['informe_completo'], app(Lector::class)->consultar($this->actor, $this->residente, $id)['informe_completo']);
    }

    public function test_detecta_cambio_de_conocimiento_sin_reinterpretar_la_historia(): void
    {
        $antes = $this->ejecutar();
        DB::table('reglas_expertas')->where('cod_regla_experta', $this->paquete['reglas']['OBS-MEM-01']['regla'])->update(['nombre' => 'Nombre adulterado']);
        $this->expectException(DomainException::class);
        app(Lector::class)->consultar($this->actor, $this->residente, $antes['id']);
    }

    public function test_pantalla_presenta_la_regla_sustituta_desde_su_ejecucion_conservada(): void
    {
        app(Gestor::class)->crearRevision($this->actor, $this->paquete['version'], 'ORION-OBS-PANTALLA', 'Conectar la regla sustituta a la consulta autorizada.',
            nueva: $this->nuevaRegla(), sustituye: $this->paquete['reglas']['OBS-MEM-01']['regla']);
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->assertSee('Nueva revisión de la observación')->assertSee('ORION-OBS-PANTALLA')
            ->call('ejecutarObservaciones')->assertHasNoErrors()->assertSee('Evaluación observacional guardada')
            ->assertSee('Nueva revisión de la observación')->assertDontSee('No se pudo consultar la evaluación');
        self::assertDatabaseCount('evaluaciones_expertas', 1);
        self::assertDatabaseCount('clasificaciones_dcl_instantanea', 0);
    }

    public function test_sucesion_ambigua_se_rechaza_sin_crear_evaluacion(): void
    {
        $hija = app(Gestor::class)->crearRevision($this->actor, $this->paquete['version'], 'ORION-OBS-UNICA', 'Primera sucesora de la familia observacional.');
        $otra = (array) DB::table('versiones_modelo_experto')->where('cod_version_modelo', $hija)->sole();
        $otra['cod_version_modelo'] = 'OBS-RAMA-AJENA';
        $otra['codigo_version'] = 'ORION-OBS-RAMA';
        DB::table('versiones_modelo_experto')->insert($otra);
        try {
            $this->ejecutar();
            self::fail('No debe seleccionar una rama arbitraria.');
        } catch (DomainException $e) {
            self::assertStringContainsString('ambigua', $e->getMessage());
        }
        self::assertDatabaseCount('evaluaciones_expertas', 0);
        self::assertDatabaseCount('hechos_instantanea_experta', 0);
    }

    public function test_prueba_observacional_distingue_sin_dato_de_no_y_no_guarda_evaluaciones(): void
    {
        $regla = $this->paquete['reglas']['OBS-MEM-01'];
        $antes = DB::table('activity_log')->count();
        $sinDato = app(Gestor::class)->probar($this->actor, $regla['regla'], []);
        $negativa = app(Gestor::class)->probar($this->actor, $regla['regla'], [$regla['variable'] => $this->paquete['valores']['NO']]);
        self::assertSame('NO_EVALUABLE', $sinDato['estado']);
        self::assertFalse($sinDato['cumple']);
        self::assertSame('NO_CUMPLE', $negativa['estado']);
        self::assertFalse($negativa['cumple']);
        self::assertDatabaseCount('evaluaciones_expertas', 0);
        self::assertDatabaseCount('activity_log', $antes);
    }
}
