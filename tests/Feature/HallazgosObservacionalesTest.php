<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Atencion;
use App\Models\ControlCognitivo;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\EjecutarObservaciones;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarConocimientoPetersen;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarHallazgosObservacionales;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarObservacionesConservadas;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\LecturaResultadosExperto;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\TransicionReduccionConservacion;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\PaqueteObservacional;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ResultadosExpertoResidente;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use Database\Seeders\RolesAndPermissionsSeeder;
use DateTimeImmutable;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;
use MotorExperto\dominio\conocimiento\reglas\EvaluadorObservaciones;
use MotorExperto\soporte\conservacion\CodecInstantanea;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\Support\CorromperConservacionParaPrueba;
use Tests\Support\InformeCognitivoFixture;
use Tests\TestCase;

final class HallazgosObservacionalesTest extends TestCase
{
    use CorromperConservacionParaPrueba;
    use RefreshDatabase;

    private User $medico;

    private Personal $personal;

    private Residente $residente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 10, 17)->setTime(12, 0));
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->medico = User::factory()->create(['estado' => 'ACTIVO']);
        $this->medico->assignRole('MEDICO GENERAL/GERIATRA');
        $this->personal = Personal::create(['cod_usuario' => $this->medico->getKey(), 'nombres' => 'Autora',
            'apellido_paterno' => 'Prueba', 'numero_documento' => 'OBS-TEST-1', 'profesion' => 'MEDICINA', 'estado' => 'ACTIVO']);
        $this->residente = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $this->actingAs($this->medico);
    }

    private function control(array $cambios = []): ControlCognitivo
    {
        return ControlCognitivo::create(array_replace(['cod_residente' => $this->residente->getKey(),
            'cod_personal' => $this->personal->getKey(), 'fecha_hora' => now()->subHour(), 'estado' => 'VIGENTE',
            'repite_preguntas' => true, 'olvida_indicaciones' => false, 'cambio_cognitivo' => null], $cambios));
    }

    public function test_ejecuta_tres_reglas_con_fuentes_reales_y_traza_reproducible_sin_escrituras(): void
    {
        $control = $this->control();
        $escrituras = [];
        DB::listen(static function ($query) use (&$escrituras): void {
            if (preg_match('/^\s*(insert|update|delete|alter|create|drop)\b/i', $query->sql)) {
                $escrituras[] = $query->sql;
            }
        });
        $r = app(ConsultarHallazgosObservacionales::class)->consultar($this->medico, $this->residente);
        self::assertSame(['CUMPLE', 'NO_CUMPLE', 'NO_EVALUABLE'], array_column($r['resultados'], 'estado'));
        self::assertSame($control->getKey(), $r['resultados'][0]['fuente']['registro']);
        self::assertSame($this->personal->getKey(), $r['resultados'][0]['fuente']['autor']);
        self::assertSame('Autora Prueba', $r['resultados'][0]['fuente']['nombre_autor']);
        $reproducido = (new EvaluadorObservaciones)->evaluar($this->residente->getKey(), new DateTimeImmutable($r['fecha_corte']), $r['instantanea']);
        self::assertSame(array_column($r['resultados'], 'condicion'), array_column($reproducido['resultados'], 'condicion'));
        self::assertSame([], $escrituras);
        self::assertSame(0, DB::table('evaluaciones_expertas')->count());
        self::assertFalse($r['persistido']);
    }

    public function test_pantalla_integra_hallazgos_y_valores_nulos_sin_inventar_dcl(): void
    {
        $this->control();
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->assertSee('Hallazgos observacionales del seguimiento')
            ->assertSee('Se documentó un episodio de repetición de preguntas.')
            ->assertSee('No cumplió')->assertSee('No evaluable')->assertSee('Autora Prueba')
            ->assertSee('La traza todavía no se conserva como una nueva evaluación.')
            ->assertDontSee('patrón candidato de DCL');
    }

    public function test_senales_observadas_se_vinculan_al_detalle_sin_clasificar_petersen(): void
    {
        $this->control();
        app(RegistrarConocimientoPetersen::class)->ejecutar($this->medico);

        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->assertSee('Señales observadas en el seguimiento')
            ->assertSee('Se documentó un episodio de repetición de preguntas.')
            ->assertSee('Ver controles y reglas de seguimiento')
            ->assertSee('Perfil pendiente de valoración')
            ->assertDontSee('Mapa radial interactivo de cinco áreas')
            ->assertDontSee('Patrón orientativo')
            ->call('irASeccion', 'hallazgos-observacionales')
            ->assertDispatched('orion-navigate', seccion: 'hallazgos-observacionales');

        self::assertDatabaseCount('valoraciones_petersen_originales', 0);
        self::assertSame(0, \App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\BloquesExpertos::consulta('clasificaciones_dcl_instantanea')->count());
    }

    public function test_guardar_es_atomico_idempotente_y_reconstruye_valores_y_autor_originales(): void
    {
        $control = $this->control();
        $solicitud = (string) Str::uuid();
        $id = app(EjecutarObservaciones::class)->ejecutar($this->medico, $this->residente, $solicitud);
        self::assertSame($id, app(EjecutarObservaciones::class)->ejecutar($this->medico, $this->residente, $solicitud));
        self::assertDatabaseCount('evaluaciones_expertas', 1);
        self::assertDatabaseCount('hechos_instantanea_experta', 4);
        self::assertDatabaseCount('evaluaciones_reglas', 3);
        self::assertDatabaseCount('evaluaciones_condiciones_regla', 3);
        self::assertDatabaseCount('evidencias_soporte_condicion', 3);
        $r = app(ConsultarObservacionesConservadas::class)->consultar($this->medico, $this->residente, $id);
        self::assertTrue($r['persistido']);
        self::assertSame(['CUMPLE', 'NO_CUMPLE', 'NO_EVALUABLE'], array_column($r['resultados'], 'estado'));
        self::assertSame([true, false, null], array_column(array_column($r['resultados'], 'condicion'), 'observado'));
        self::assertSame($this->personal->getKey(), $r['resultados'][0]['fuente']['autor']);
        $control->update(['repite_preguntas' => false, 'olvida_indicaciones' => true]);
        $despues = app(ConsultarObservacionesConservadas::class)->consultar($this->medico, $this->residente, $id);
        self::assertSame($r['informe_completo'], $despues['informe_completo']);
        self::assertSame([], config('sistema_experto.lectura_clinica'));
        self::assertSame(0, \App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\BloquesExpertos::consulta('clasificaciones_dcl_instantanea')->count());
    }

    public function test_guardado_incluye_todas_las_paginas_y_distingue_ausencia_de_datos(): void
    {
        for ($i = 0; $i < 13; $i++) {
            $this->control(['fecha_hora' => now()->subHours($i + 1)]);
        }
        $id = app(EjecutarObservaciones::class)->ejecutar($this->medico, $this->residente, (string) Str::uuid());
        $r = app(ConsultarObservacionesConservadas::class)->consultar($this->medico, $this->residente, $id);
        self::assertSame(13, $r['registros']->total());
        self::assertCount(39, $r['informe_completo']['resultados']);
        self::assertDatabaseCount('hechos_instantanea_experta', 52);
        $otra = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $sinDatos = app(EjecutarObservaciones::class)->ejecutar($this->medico, $otra, (string) Str::uuid());
        $vacio = app(ConsultarObservacionesConservadas::class)->consultar($this->medico, $otra, $sinDatos);
        self::assertSame(['NO_EVALUABLE', 'NO_EVALUABLE', 'NO_EVALUABLE'], array_column($vacio['resumen'], 'estado'));
        self::assertSame([], $vacio['informe_completo']['resultados']);
    }

    public function test_psicologia_puede_guardar_la_misma_version_sin_atribuirse_la_fuente_medica(): void
    {
        $this->control();
        $id = app(EjecutarObservaciones::class)->ejecutar($this->medico, $this->residente, (string) Str::uuid());
        $psicologa = User::factory()->create(['estado' => 'ACTIVO']);
        $psicologa->assignRole('PSICOLOGO/A');
        $personal = Personal::create(['cod_usuario' => $psicologa->getKey(), 'nombres' => 'Psicóloga', 'apellido_paterno' => 'Prueba',
            'numero_documento' => 'OBS-PSI-1', 'profesion' => 'PSICOLOGIA', 'estado' => 'ACTIVO']);
        $nuevo = app(EjecutarObservaciones::class)->ejecutar($psicologa, $this->residente, (string) Str::uuid());
        self::assertNotSame($id, $nuevo);
        self::assertDatabaseCount('versiones_modelo_experto', 1);
        self::assertDatabaseHas('evaluaciones_expertas', ['cod_evaluacion_experta' => $nuevo, 'cod_personal_solicitante' => $personal->getKey()]);
        self::assertSame($this->personal->getKey(), app(ConsultarObservacionesConservadas::class)->consultar($psicologa, $this->residente, $nuevo)['resultados'][0]['fuente']['autor']);
    }

    public function test_livewire_guarda_solo_por_accion_explicita_y_recupera_su_historial(): void
    {
        $this->control();
        $componente = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente]);
        self::assertDatabaseCount('evaluaciones_expertas', 0);
        $componente->call('ejecutarObservaciones')->assertHasNoErrors('observaciones')
            ->assertSee('Evaluación observacional guardada con sus fuentes y decisiones originales.')
            ->assertSee('Historial de ejecuciones observacionales')->assertDontSee('La traza todavía no se conserva como una nueva evaluación.');
        $componente->call('ejecutarObservaciones')->assertHasNoErrors('observaciones');
        self::assertDatabaseCount('evaluaciones_expertas', 1);
        $id = $componente->get('evaluacionObservacional');
        $otro = Residente::factory()->create(['estado' => 'ADMITIDO']);
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $otro])->call('seleccionarObservacion', $id)->assertNotFound();
    }

    public function test_instantanea_corrupta_no_se_reemplaza_por_la_fuente_actual(): void
    {
        $this->control();
        $id = app(EjecutarObservaciones::class)->ejecutar($this->medico, $this->residente, (string) Str::uuid());
        $this->corromperConservacion(fn () => DB::table('hechos_instantanea_experta')->where('cod_evaluacion_experta', $id)->where('concepto_dominio', 'repite_preguntas')->update(['valor_logico' => false]));
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->assertSee('No se pudieron consultar los hallazgos observacionales')->assertDontSee('Se documentó un episodio de repetición de preguntas.');
    }

    public function test_fallo_intermedio_revierte_conocimiento_ejecucion_hechos_traza_y_auditoria(): void
    {
        $this->control();
        if (DB::getDriverName() === 'sqlite') {
            DB::unprepared("CREATE TRIGGER obs_fallo_traza BEFORE UPDATE OF cod_traza_inferencia ON evaluacion_criterios WHEN NEW.cod_traza_inferencia IS NOT NULL BEGIN SELECT RAISE(ABORT, 'Fallo de prueba'); END");
        } else {
            DB::unprepared("CREATE FUNCTION obs_fallo_traza_fn() RETURNS trigger LANGUAGE plpgsql AS \$\$ BEGIN RAISE EXCEPTION 'Fallo de prueba'; END; \$\$");
            DB::unprepared('CREATE TRIGGER obs_fallo_traza BEFORE UPDATE OF cod_traza_inferencia ON evaluacion_criterios FOR EACH ROW WHEN (NEW.cod_traza_inferencia IS NOT NULL) EXECUTE FUNCTION obs_fallo_traza_fn()');
        }
        try {
            app(EjecutarObservaciones::class)->ejecutar($this->medico, $this->residente, (string) Str::uuid());
            self::fail('La operación debía fallar antes de confirmar.');
        } catch (QueryException $e) {
            foreach (['versiones_modelo_experto', 'evaluaciones_expertas', 'hechos_instantanea_experta', 'evidencias_evaluacion', 'evaluaciones_reglas'] as $tabla) {
                self::assertDatabaseCount($tabla, 0);
            }
            self::assertFalse(DB::table('activity_log')->where('event', 'observaciones_ejecutadas')->exists());
        }
    }

    public function test_retirar_fk_con_datos_nuevos_se_rechaza_sin_borrar_historia(): void
    {
        $this->control();
        app(EjecutarObservaciones::class)->ejecutar($this->medico, $this->residente, (string) Str::uuid());
        $migracion = require database_path('migrations/2026_10_18_001000_extend_origen_observacional_instantaneas.php');
        try {
            $migracion->down();
            self::fail('No se debe retirar una FK que conserva historia.');
        } catch (\RuntimeException $e) {
            self::assertStringContainsString('instantáneas conservadas', $e->getMessage());
            self::assertDatabaseCount('hechos_instantanea_experta', 4);
        }
    }

    public function test_migracion_reversible_conserva_una_fila_historica_y_sus_valores_tipados(): void
    {
        // Las migraciones históricas se revierten en orden: primero la consolidación posterior.
        $paquete = app(PaqueteObservacional::class)->preparar($this->medico);
        (new \App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\TransicionBloquesExpertos)->bajar();
        $reduccion = new TransicionReduccionConservacion;
        $reduccion->bajar();
        InformeCognitivoFixture::insertar('evaluaciones_expertas', ['cod_evaluacion_experta' => 'LEGACY-OBS-EVAL',
            'cod_version_modelo' => $paquete['version'], 'cod_residente' => $this->residente->getKey(), 'origen_activacion' => 'PRUEBA_TECNICA']);
        DB::table('hechos_instantanea_experta')->insert(['cod_hecho_instantanea' => 'LEGACY-OBS-HECHO', 'cod_evaluacion_experta' => 'LEGACY-OBS-EVAL',
            'id_hecho_dominio' => 'LEGACY', 'fuente_id_dominio' => 'REFERENCIA', 'origen_id_dominio' => 'ORIGINAL', 'episodio_id_dominio' => 'EPISODIO',
            'concepto_dominio' => 'OBSERVACION', 'orden' => 0, 'tipo_origen' => 'REFERENCIA_EXTERNA_SINTETICA']
            + CodecInstantanea::fecha('fecha_hecho', now()->toDateTimeImmutable()) + CodecInstantanea::escalar('valor', false));
        $antes = (array) DB::table('hechos_instantanea_experta')->first();
        $migracion = require database_path('migrations/2026_10_18_001000_extend_origen_observacional_instantaneas.php');
        $migracion->down();
        $migracion->up();
        self::assertEqualsCanonicalizing($antes, (array) DB::table('hechos_instantanea_experta')->first());
        self::assertSame(false, CodecInstantanea::leerEscalar('valor', DB::table('hechos_instantanea_experta')->first()));
    }

    public function test_fk_y_check_del_origen_exigen_control_y_autor_originales(): void
    {
        $this->control();
        $id = app(EjecutarObservaciones::class)->ejecutar($this->medico, $this->residente, (string) Str::uuid());
        $fila = (array) DB::table('hechos_instantanea_experta')->where('cod_evaluacion_experta', $id)->first();
        $fila['cod_hecho_instantanea'] = 'OBS-INVALIDO';
        $fila['id_hecho_dominio'] = 'OBS-INVALIDO';
        $fila['orden'] = 99;
        $fila['cod_personal_autor'] = null;
        $this->expectException(QueryException::class);
        DB::table('hechos_instantanea_experta')->insert($fila);
    }

    public function test_conservacion_observacional_no_reemplaza_la_lectura_cognitiva(): void
    {
        $this->control();
        app(EjecutarObservaciones::class)->ejecutar($this->medico, $this->residente, (string) Str::uuid());
        $lectura = app(LecturaResultadosExperto::class)
            ->consultar($this->medico, $this->residente, null, 'COG-MEM');
        self::assertNull($lectura['evaluacion']);
        self::assertSame(0, $lectura['historial']->total());
    }

    public function test_corrupcion_de_condiciones_se_detecta_aunque_los_hechos_no_hayan_cambiado(): void
    {
        $this->control();
        $id = app(EjecutarObservaciones::class)->ejecutar($this->medico, $this->residente, (string) Str::uuid());
        DB::table('evaluaciones_condiciones_regla')->where('estado_condicion', 'CUMPLE')->update(['estado_condicion' => 'NO_CUMPLE']);
        $this->expectException(DomainException::class);
        app(ConsultarObservacionesConservadas::class)->consultar($this->medico, $this->residente, $id);
    }

    public static function corrupcionesParticipacion(): array
    {
        return ['vinculo eliminado' => ['eliminar'], 'estado alterado' => ['estado'], 'rol alterado' => ['rol'], 'evidencia ajena' => ['ajena']];
    }

    #[DataProvider('corrupcionesParticipacion')]
    public function test_participacion_corrupta_rechaza_la_lectura_sin_fallback(string $caso): void
    {
        $this->control();
        $id = app(EjecutarObservaciones::class)->ejecutar($this->medico, $this->residente, (string) Str::uuid());
        $vinculo = DB::table('evidencias_criterio_evaluacion')->orderBy('cod_evidencia_criterio')->first();
        $q = DB::table('evidencias_criterio_evaluacion')->where('cod_evidencia_criterio', $vinculo->cod_evidencia_criterio);
        if ($caso === 'ajena') {
            $otro = Residente::factory()->create(['estado' => 'ADMITIDO']);
            $this->control(['cod_residente' => $otro->getKey()]);
            $otraEvaluacion = app(EjecutarObservaciones::class)->ejecutar($this->medico, $otro, (string) Str::uuid());
            $otraEvidencia = DB::table('evidencias_evaluacion')->where('cod_evaluacion_experta', $otraEvaluacion)->value('cod_evidencia_evaluacion');
        }
        $this->corromperConservacion(fn () => match ($caso) {
            'eliminar' => $q->delete(),
            'estado' => $q->update(['estado_participacion' => 'NO_EVALUABLE']),
            'rol' => $q->update(['rol_en_criterio' => 'OTRO']),
            'ajena' => $q->update(['cod_evidencia_evaluacion' => $otraEvidencia]),
        });
        $this->expectException(DomainException::class);
        app(ConsultarObservacionesConservadas::class)->consultar($this->medico, $this->residente, $id);
    }

    public function test_huella_incluye_estado_original_aunque_no_cambie_el_resultado_derivado(): void
    {
        $this->control(['estado' => 'ANULADO']);
        $id = app(EjecutarObservaciones::class)->ejecutar($this->medico, $this->residente, (string) Str::uuid());
        $this->corromperConservacion(fn () => DB::table('hechos_instantanea_experta')->where('cod_evaluacion_experta', $id)->where('concepto_dominio', 'estado')->update(['valor_texto' => 'RECTIFICADO']));
        $this->expectException(DomainException::class);
        app(ConsultarObservacionesConservadas::class)->consultar($this->medico, $this->residente, $id);
    }

    public function test_origen_nuevo_no_pierde_los_checks_escalares_anteriores(): void
    {
        $this->control();
        $id = app(EjecutarObservaciones::class)->ejecutar($this->medico, $this->residente, (string) Str::uuid());
        $this->expectException(QueryException::class);
        DB::table('hechos_instantanea_experta')->where('cod_evaluacion_experta', $id)->update(['valor_tipo' => 'INVALIDO']);
    }

    public static function restriccionesGuardado(): array
    {
        return ['cuenta inactiva' => ['cuenta'], 'permiso revocado' => ['permiso'], 'preview de rol' => ['preview'],
            'personal inactivo' => ['personal'], 'residente egresado' => ['residente']];
    }

    #[DataProvider('restriccionesGuardado')]
    public function test_guardado_reautoriza_estado_permiso_y_contexto_sin_escrituras(string $caso): void
    {
        $this->control();
        match ($caso) {
            'cuenta' => $this->medico->update(['estado' => 'INACTIVO']),
            'permiso' => Role::findByName('MEDICO GENERAL/GERIATRA', 'web')->revokePermissionTo('evaluaciones_expertas.ejecutar_observaciones'),
            'preview' => session()->put(RolePreviewService::SESSION_KEY, 'ENFERMEROS'),
            'personal' => $this->personal->update(['estado' => 'INACTIVO']),
            'residente' => DB::table('residentes')->where('cod_residente', $this->residente->getKey())->update(['estado' => 'EGRESADO']),
        };
        try {
            app(EjecutarObservaciones::class)->ejecutar($this->medico, $this->residente, (string) Str::uuid());
            self::fail('Se esperaba denegación antes del guardado.');
        } catch (AuthorizationException $e) {
            self::assertDatabaseCount('versiones_modelo_experto', 0);
            self::assertDatabaseCount('evaluaciones_expertas', 0);
        }
    }

    public static function actoresSinGuardado(): array
    {
        return ['Enfermería' => ['ENFERMEROS', 'MEDICINA'], 'Superadministración' => ['SUPERADMINISTRADOR', 'MEDICINA'],
            'Familiar' => ['FAMILIAR', 'MEDICINA'], 'competencia incorrecta' => ['MEDICO GENERAL/GERIATRA', 'PSICOLOGIA']];
    }

    #[DataProvider('actoresSinGuardado')]
    public function test_lectura_o_permiso_aislado_no_conceden_guardado(string $rol, string $profesion): void
    {
        $this->medico->syncRoles([$rol]);
        $this->medico->givePermissionTo('evaluaciones_expertas.ejecutar_observaciones');
        $this->personal->update(['profesion' => $profesion]);
        try {
            app(EjecutarObservaciones::class)->ejecutar($this->medico, $this->residente, (string) Str::uuid());
            self::fail('La competencia debía impedir la ejecución.');
        } catch (AuthorizationException $e) {
            self::assertDatabaseCount('evaluaciones_expertas', 0);
            self::assertDatabaseCount('versiones_modelo_experto', 0);
        }
    }

    public function test_registro_anulado_no_produce_hallazgo_y_otros_residentes_o_futuros_no_entran(): void
    {
        $this->control(['estado' => 'ANULADO']);
        $this->control(['cod_residente' => Residente::factory()->create(['estado' => 'ADMITIDO'])->getKey()]);
        $this->control(['fecha_hora' => now()->addDay()]);
        $r = app(ConsultarHallazgosObservacionales::class)->consultar($this->medico, $this->residente);
        self::assertSame(1, $r['registros']->total());
        self::assertSame(['NO_EVALUABLE', 'NO_EVALUABLE', 'NO_EVALUABLE'], array_column($r['resultados'], 'estado'));
    }

    public function test_autoria_historica_identificable_no_se_borra_por_baja_posterior_del_profesional(): void
    {
        $this->control();
        $this->personal->update(['estado' => 'INACTIVO']);
        // Un segundo profesional autorizado consulta la fuente histórica; el autor original permanece.
        $otro = User::factory()->create(['estado' => 'ACTIVO']);
        $otro->assignRole('MEDICO GENERAL/GERIATRA');
        Personal::create(['cod_usuario' => $otro->getKey(), 'nombres' => 'Lector', 'apellido_paterno' => 'Prueba',
            'numero_documento' => 'OBS-TEST-2', 'profesion' => 'MEDICINA', 'estado' => 'ACTIVO']);
        $r = app(ConsultarHallazgosObservacionales::class)->consultar($otro, $this->residente);
        self::assertSame('CUMPLE', $r['resultados'][0]['estado']);
        self::assertSame($this->personal->getKey(), $r['resultados'][0]['fuente']['autor']);
    }

    public function test_paginacion_declara_alcance_sin_omitir_el_total_ni_inventar_ventana_clinica(): void
    {
        for ($i = 0; $i < 13; $i++) {
            $this->control(['fecha_hora' => now()->subHours($i + 1)]);
        }
        $primera = app(ConsultarHallazgosObservacionales::class)->consultar($this->medico, $this->residente);
        $segunda = app(ConsultarHallazgosObservacionales::class)->consultar($this->medico, $this->residente, 2);
        self::assertSame(13, $primera['registros']->total());
        self::assertCount(30, $primera['resultados']);
        self::assertCount(9, $segunda['resultados']);
        self::assertSame([], array_intersect(array_column($primera['instantanea'], 'id'), array_column($segunda['instantanea'], 'id')));
    }

    public static function contextosAtencion(): array
    {
        return ['atencion propia' => [false, 'CUMPLE'], 'atencion ajena' => [true, 'NO_EVALUABLE']];
    }

    #[DataProvider('contextosAtencion')]
    public function test_campo_atencion_no_oculta_la_relacion_y_se_valida_el_residente(bool $ajena, string $estado): void
    {
        $area = Area::create(['nombre' => 'Área observacional de prueba', 'descripcion' => 'Fixture', 'estado' => 'ACTIVO']);
        $atencion = Atencion::create(['cod_residente' => $ajena ? Residente::factory()->create(['estado' => 'ADMITIDO'])->getKey() : $this->residente->getKey(),
            'cod_personal' => $this->personal->getKey(), 'cod_area' => $area->getKey(), 'tipo_atencion' => 'CONTROL',
            'fecha_hora' => now()->subHour(), 'estado' => 'REGISTRADA']);
        if ($ajena) {
            // La integridad física vigente impide crear este registro incoherente.
            $this->expectException(QueryException::class);
        }
        $this->control(['cod_atencion' => $atencion->getKey(), 'atencion' => 'DISMINUIDA']);
        $r = app(ConsultarHallazgosObservacionales::class)->consultar($this->medico, $this->residente);
        self::assertSame($estado, $r['resultados'][0]['estado']);
        self::assertSame('atencion:'.$atencion->getKey(), $r['resultados'][0]['fuente']['episodio']);
    }

    public static function denegaciones(): array
    {
        return ['inactivo' => ['inactivo'], 'sin permiso' => ['permiso'], 'familiar' => ['familiar']];
    }

    #[DataProvider('denegaciones')]
    public function test_acceso_sin_autorizacion_no_lee_las_fuentes(string $caso): void
    {
        $this->control();
        if ($caso === 'inactivo') {
            $this->medico->update(['estado' => 'INACTIVO']);
        } elseif ($caso === 'permiso') {
            $this->medico->removeRole('MEDICO GENERAL/GERIATRA');
        } else {
            $this->medico->syncRoles(['FAMILIAR']);
        }
        $lecturas = [];
        DB::listen(static function ($q) use (&$lecturas): void {
            if (str_contains($q->sql, 'controles_cognitivos')) {
                $lecturas[] = $q->sql;
            }
        });
        try {
            app(ConsultarHallazgosObservacionales::class)->consultar($this->medico, $this->residente);
            self::fail('Se esperaba denegación contextual.');
        } catch (AuthorizationException $e) {
            self::assertSame([], $lecturas);
        }
    }
}
