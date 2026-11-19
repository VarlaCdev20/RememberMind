<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\GuardarResultadoInstitucional;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarConocimientoInstitucional;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarConocimientoPetersen;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarResultadoInstitucional;
use Carbon\CarbonImmutable;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\PermisoEjecucionInstitucionalSeeder;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Support\EsquemaSinTransaccion;
use Tests\TestCase;

final class ResultadoInstitucionalConservadoTest extends TestCase
{
    use EsquemaSinTransaccion;
    private User $actor;
    private Residente $residente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-11-04T12:00:00-04:00'));
        (require base_path('database/propuestas/experto/2026_11_04_000001_conservar_resultado_institucional.php'))->up();
        $this->seed([RolesAndPermissionsSeeder::class, PermisoEjecucionInstitucionalSeeder::class]);
        $this->actor = User::factory()->create(['estado' => 'ACTIVO']);
        $this->actor->assignRole('PSICOLOGO/A');
        Personal::create(['cod_usuario' => $this->actor->getKey(), 'nombres' => 'Profesional', 'apellido_paterno' => 'Sintético',
            'numero_documento' => 'SQL-TEST', 'profesion' => 'PSICOLOGIA', 'estado' => 'ACTIVO']);
        $this->residente = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $this->actingAs($this->actor);
        app(RegistrarConocimientoInstitucional::class)->ejecutar($this->actor);
        app(RegistrarConocimientoPetersen::class)->ejecutar($this->actor);
    }

    private function guardar(?string $uuid = null): array
    {
        return app(GuardarResultadoInstitucional::class)->ejecutar($this->actor, $this->residente, $uuid ?? (string) Str::uuid());
    }

    public function test_guarda_abstencion_y_recupera_sin_recalcular_originales(): void
    {
        $guardado = $this->guardar();
        self::assertTrue($guardado['persistido']);
        self::assertFalse($guardado['replay_completo']);
        self::assertSame('ABSTENCION_INFORMACION_INSUFICIENTE', $guardado['contenido']['resultado']);
        self::assertNull($guardado['contenido']['patron']);
        self::assertSame('U', $guardado['contenido']['requisitos']['C02']);
        self::assertCount(6, $guardado['contenido']['criterios']);
        self::assertNotEmpty($guardado['contenido']['criterios'][0]['reglas']);
        $lecturasClinicas = 0;
        DB::listen(function ($q) use (&$lecturasClinicas): void {
            foreach (['aplicaciones_instrumento', 'respuestas_instrumento', 'valoraciones_petersen_originales'] as $tabla) {
                if (str_contains($q->sql, 'from "'.$tabla.'"')) { $lecturasClinicas++; }
            }
        });
        $recuperado = app(ConsultarResultadoInstitucional::class)->consultar($this->actor, $this->residente, $guardado['contenido']['id']);
        self::assertSame($guardado, $recuperado);
        self::assertSame(0, $lecturasClinicas);
        $this->assertDatabaseCount('evaluaciones_expertas', 1);
    }

    public function test_solicitud_repetida_es_idempotente(): void
    {
        $uuid = (string) Str::uuid();
        self::assertSame($this->guardar($uuid), $this->guardar($uuid));
        $this->assertDatabaseCount('evaluaciones_expertas', 1);
    }

    public function test_falta_permiso_no_escribe_resultado(): void
    {
        $this->actor->roles()->first()->revokePermissionTo('evaluaciones_expertas.ejecutar_institucional');
        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);
        try { $this->guardar(); } finally { $this->assertDatabaseCount('evaluaciones_expertas', 0); }
    }

    public function test_lectura_de_otro_residente_se_deniega(): void
    {
        $d = $this->guardar();
        $otro = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        app(ConsultarResultadoInstitucional::class)->consultar($this->actor, $otro, $d['contenido']['id']);
    }

    public function test_corrupcion_no_se_publica_como_resultado_valido(): void
    {
        $d = $this->guardar();
        DB::table('evaluaciones_expertas')->where('cod_evaluacion_experta', $d['contenido']['id'])->update(['institucional_c01' => 'S']);
        $this->expectException(DomainException::class);
        app(ConsultarResultadoInstitucional::class)->consultar($this->actor, $this->residente, $d['contenido']['id']);
    }

    public function test_reversion_rechaza_historia_poblada(): void
    {
        $this->guardar();
        $this->expectException(\RuntimeException::class);
        (require base_path('database/propuestas/experto/2026_11_04_000001_conservar_resultado_institucional.php'))->down();
    }

    public function test_originales_se_conservan_con_null_falso_y_autoria(): void
    {
        $area = \App\Models\Area::create(['nombre' => 'Área sintética SQL', 'estado' => 'ACTIVA']);
        $atencion = \App\Models\Atencion::create(['cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->actor->personal->getKey(),
            'cod_area' => $area->getKey(), 'tipo_atencion' => 'VALORACION', 'fecha_hora' => now()->subDay(), 'estado' => 'FINALIZADA']);
        $v = app(\App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarValoracionPetersenOriginal::class)->ejecutar($this->actor, $this->residente,
            ['solicitud_uuid' => (string) Str::uuid(), 'elemento' => 'C04', 'estado_original' => 'N', 'fecha_hora' => now()->subDay()->format('Y-m-d H:i:s'),
                'justificacion' => 'Diferencial sin resolver en caso sintético.', 'cod_atencion' => $atencion->getKey(), 'tipo_registro' => 'REGISTRO', 'soportes' => [], 'impedimento_no_resuelto' => true]);
        $d = $this->guardar();
        self::assertSame('U', $d['contenido']['requisitos']['C04'], 'Un juicio sin soporte sigue excluido aunque conserve una negación original.');
        $fuentes = collect($d['contenido']['hechos']);
        self::assertTrue($fuentes->every(fn ($h) => $h['valoracion'] === $v->getKey() && $h['autor'] === $this->actor->personal->getKey()));
        self::assertSame('N', $fuentes->firstWhere('concepto', 'estado')['valor']);
        self::assertNull($fuentes->firstWhere('concepto', 'revision_explicita')['valor']);
        self::assertTrue($fuentes->firstWhere('concepto', 'impedimento_no_resuelto')['valor']);
    }

    public function test_medico_y_psicologia_revisan_por_separado_sin_cambiar_resultado(): void
    {
        $d = $this->guardar();
        $med = User::factory()->create(['estado' => 'ACTIVO']); $med->assignRole('MEDICO GENERAL/GERIATRA');
        Personal::create(['cod_usuario' => $med->getKey(), 'nombres' => 'Medicina', 'apellido_paterno' => 'Sintética', 'numero_documento' => 'MED-SQL', 'profesion' => 'MEDICINA', 'estado' => 'ACTIVO']);
        $revisar = app(\App\Modulos\ApoyoDecision\Aplicacion\Acciones\RevisarValoracionExperta::class);
        $disc = \App\Modulos\ApoyoDecision\Dominio\Revision\DisciplinaRevision::class;
        $revisar->ejecutar($this->actor, $d['contenido']['id'], $disc::PSICOLOGIA, 'CONFIRMADA', 'Abstención correctamente fundada, caso técnico.');
        $revisar->ejecutar($med, $d['contenido']['id'], $disc::MEDICINA, 'RECHAZADA', 'Discrepancia independiente documentada en caso técnico.');
        $this->assertDatabaseCount('revisiones_evaluacion_experta', 2);
        self::assertSame($d, app(ConsultarResultadoInstitucional::class)->consultar($this->actor, $this->residente, $d['contenido']['id']));
    }

    public function test_pantalla_guarda_recupera_por_url_y_registra_revision(): void
    {
        $pagina = \Livewire\Livewire::test(\App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ResultadosExpertoResidente::class, ['residente' => $this->residente]);
        $pagina->assertSee('Evaluar y guardar resultado')->call('guardarResultadoInstitucional')->assertHasNoErrors()->assertSee('Evaluación guardada.');
        $id = $pagina->get('resultadoInstitucionalId');
        self::assertNotNull($id);
        $pagina->call('prepararRevisionInstitucional', 'PSICOLOGIA')->set('decisionRevision', 'CONFIRMADA')->set('justificacionRevision', 'Resultado insuficiente, correcto para caso sintético.')
            ->call('guardarRevision')->assertHasNoErrors()->assertSee('Revisión de Psicología registrada.');
        $this->get(route('admin.psicologia.residente.resultados-experto', $this->residente).'?resultadoGuardado='.urlencode($id))->assertOk()->assertSee('Evaluación guardada')->assertSee('Valoración confirmada');
        $this->assertDatabaseCount('evaluaciones_expertas', 1);
    }

    public function test_fallo_en_traza_revierte_el_resultado_y_sus_hechos(): void
    {
        DB::connection()->beforeExecuting(function (string $sql): void {
            if (str_starts_with($sql, 'insert into "evaluaciones_reglas"')) { throw new DomainException('Fallo técnico provocado en prueba.'); }
        });
        try { $this->guardar(); self::fail('Debía fallar'); } catch (DomainException $e) { self::assertSame('Fallo técnico provocado en prueba.', $e->getMessage()); }
        $this->assertDatabaseCount('evaluaciones_expertas', 0);
        $this->assertDatabaseCount('hechos_instantanea_experta', 0);
        $this->assertDatabaseCount('evaluacion_criterios', 0);
    }

    public function test_personal_inactivo_no_ejecuta(): void
    {
        $this->actor->personal->update(['estado' => 'INACTIVO']);
        self::assertFalse(\Illuminate\Support\Facades\Gate::forUser($this->actor->fresh())->allows('ejecutarInstitucional', [\App\Models\EvaluacionExperta::class, $this->residente]));
        $this->assertDatabaseCount('evaluaciones_expertas', 0);
    }

    public function test_permiso_no_se_concede_a_superadministracion_y_fk_son_restrictivas(): void
    {
        $u = User::factory()->create(['estado' => 'ACTIVO']); $u->assignRole('SUPERADMINISTRADOR');
        self::assertFalse($u->checkPermissionTo('evaluaciones_expertas.ejecutar_institucional', 'web'));
        self::assertFalse(\Illuminate\Support\Facades\Gate::forUser($u)->allows('ejecutarInstitucional', [\App\Models\EvaluacionExperta::class, $this->residente]));
        foreach (['evaluaciones_expertas' => ['cod_version_petersen', 'cod_usuario_ejecucion'], 'hechos_instantanea_experta' => ['cod_respuesta', 'cod_aplicacion', 'cod_valoracion_petersen']] as $tabla => $columnas) {
            $fks = \Illuminate\Support\Facades\Schema::getForeignKeys($tabla);
            foreach ($columnas as $columna) {
                $fk = collect($fks)->first(fn ($f) => $f['columns'] === [$columna]);
                self::assertNotNull($fk, $columna);
                self::assertSame('restrict', strtolower($fk['on_delete']));
            }
        }
    }

    public function test_creacion_tardia_del_permiso_y_reintento_respetan_exclusividad(): void
    {
        \Spatie\Permission\Models\Permission::findByName('evaluaciones_expertas.ejecutar_institucional', 'web')->delete();
        $this->seed(PermisoEjecucionInstitucionalSeeder::class);
        $p = \Spatie\Permission\Models\Permission::findByName('evaluaciones_expertas.ejecutar_institucional', 'web');
        self::assertSame(['MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A'], $p->roles()->orderBy('name')->pluck('name')->all());
        // La instalación idempotente corrige exclusivamente una concesión indebida de este permiso.
        \Spatie\Permission\Models\Role::findByName('SUPERADMINISTRADOR', 'web')->givePermissionTo($p);
        $this->seed(PermisoEjecucionInstitucionalSeeder::class);
        self::assertSame(['MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A'], $p->roles()->orderBy('name')->pluck('name')->all());
    }

    public function test_historial_completo_se_pagina_sin_recortar_resultados_antiguos(): void
    {
        $ids = [];
        foreach (range(1, 11) as $n) { $ids[] = $this->guardar()['contenido']['id']; }
        $lector = app(ConsultarResultadoInstitucional::class);
        $p1 = $lector->historial($this->actor, $this->residente, 1); $p2 = $lector->historial($this->actor, $this->residente, 2);
        self::assertSame(11, $p1['total']); self::assertSame(2, $p1['ultima']);
        self::assertCount(10, $p1['items']); self::assertCount(1, $p2['items']);
        $encontrados = [...array_column($p1['items'], 'id'), ...array_column($p2['items'], 'id')]; sort($encontrados); sort($ids);
        self::assertSame($ids, $encontrados);
        $pagina = \Livewire\Livewire::test(\App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ResultadosExpertoResidente::class, ['residente' => $this->residente]);
        $pagina->call('cambiarPaginaHistoriaInstitucional', 2)->assertSee('Página 2 de 2');
        $pagina->call('abrirResultadoInstitucional', $p2['items'][0]['id'])->assertHasNoErrors()->assertSee('Evaluación guardada');
    }

    public function test_resultado_corrupto_se_muestra_como_error_y_no_admite_revision(): void
    {
        $d = $this->guardar();
        DB::table('evaluaciones_expertas')->where('cod_evaluacion_experta', $d['contenido']['id'])->update(['institucional_c01' => 'S']);
        \Livewire\Livewire::test(\App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->set('resultadoInstitucionalId', $d['contenido']['id'])->assertSee('No se pudo verificar el resultado guardado')->assertDontSee('Revisar valoración');
        $this->assertDatabaseCount('revisiones_evaluacion_experta', 0);
    }

    public function test_extension_conserva_filas_previas_y_revierte_sin_resultados(): void
    {
        $ddl = require base_path('database/propuestas/experto/2026_11_04_000001_conservar_resultado_institucional.php');
        $tablas = \Illuminate\Support\Facades\Schema::getTableListing();
        $ddl->down();
        $cod = app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('evaluaciones_expertas');
        DB::table('evaluaciones_expertas')->insert(['cod_evaluacion_experta' => $cod, 'cod_residente' => $this->residente->getKey(),
            'cod_version_modelo' => DB::table('versiones_modelo_experto')->value('cod_version_modelo'), 'cod_personal_solicitante' => $this->actor->personal->getKey(),
            'origen_activacion' => 'HISTORICO', 'estado_ejecucion' => 'FINALIZADA', 'fecha_hora_inicio' => now(), 'fecha_hora_fin' => now()]);
        $antes = (array) DB::table('evaluaciones_expertas')->where('cod_evaluacion_experta', $cod)->sole();
        $ddl->up();
        $despues = (array) DB::table('evaluaciones_expertas')->where('cod_evaluacion_experta', $cod)->sole();
        self::assertSame($antes, array_intersect_key($despues, $antes));
        self::assertCount(16, array_diff_key($despues, $antes));
        self::assertSame([null], array_values(array_unique(array_diff_key($despues, $antes), SORT_REGULAR)));
        self::assertSame($tablas, \Illuminate\Support\Facades\Schema::getTableListing());
        $ddl->down();
        self::assertSame($antes, (array) DB::table('evaluaciones_expertas')->where('cod_evaluacion_experta', $cod)->sole());
    }

    #[\PHPUnit\Framework\Attributes\Group('postgresql')]
    public function test_dos_procesos_postgresql_conservan_una_sola_solicitud(): void
    {
        if (DB::getDriverName() !== 'pgsql') { $this->markTestSkipped('Requiere PostgreSQL y procesos independientes.'); }
        $dir = sys_get_temp_dir().'/remembermind-resultado-race-'.bin2hex(random_bytes(8));
        \Illuminate\Support\Facades\File::makeDirectory($dir, 0700, true);
        $contexto = $dir.'/contexto.json';
        file_put_contents($contexto, json_encode(['conexion' => DB::connection()->getConfig(), 'usuario' => $this->actor->getKey(),
            'residente' => $this->residente->getKey(), 'solicitud' => (string) Str::uuid(), 'directorio' => $dir], JSON_THROW_ON_ERROR));
        $b = fopen($dir.'/barrera', 'c+'); flock($b, LOCK_EX);
        $procesos = [];
        try {
            foreach ([1, 2] as $n) {
                $p = new \Symfony\Component\Process\Process([PHP_BINARY, base_path('tests/Support/resultado_institucional_worker.php'), $contexto, (string) $n], base_path());
                $p->setTimeout(90)->start(); $procesos[] = $p;
            }
            $limite = microtime(true) + 20;
            while ((! is_file($dir.'/listo-1') || ! is_file($dir.'/listo-2')) && microtime(true) < $limite) { clearstatcache(); usleep(10000); }
            self::assertFileExists($dir.'/listo-1'); self::assertFileExists($dir.'/listo-2'); flock($b, LOCK_UN);
            $salidas = [];
            foreach ($procesos as $p) { self::assertSame(0, $p->wait(), $p->getErrorOutput()); $salidas[] = json_decode($p->getOutput(), true, flags: JSON_THROW_ON_ERROR); }
            self::assertSame($salidas[0], $salidas[1]);
            $this->assertDatabaseCount('evaluaciones_expertas', 1);
            self::assertSame(6, DB::table('evaluacion_criterios')->count());
        } finally {
            flock($b, LOCK_UN); fclose($b);
            foreach ($procesos as $p) { if ($p->isRunning()) { $p->stop(); } }
            \Illuminate\Support\Facades\File::deleteDirectory($dir);
        }
    }
}
