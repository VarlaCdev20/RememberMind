<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\GestionarConocimientoExperimental as Gestor;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\IdentidadesConocimiento;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Eloquent\CargadorConocimiento;
use Database\Seeders\RolesAndPermissionsSeeder;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use MotorExperto\adaptadores\entrada\AdaptadorHechosCOGMEM;
use MotorExperto\adaptadores\entrada\EjemploTecnicoCOGMEM;
use MotorExperto\dominio\conocimiento\EditorPaqueteExperimental as Editor;
use MotorExperto\dominio\conocimiento\PaqueteConocimiento;
use MotorExperto\dominio\inferencia\MemoriaTrabajo;
use MotorExperto\dominio\objetos_valor\ContextoEjecucion;
use MotorExperto\dominio\objetos_valor\FuenteBrutaCOGMEM;
use Tests\Support\InformeCognitivoFixture;
use Tests\TestCase;

final class GestionConocimientoExperimentalTest extends TestCase
{
    use RefreshDatabase;

    private function preparar(): array
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $autor = User::factory()->create();
        $autor->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($autor);
        InformeCognitivoFixture::conocimiento($autor);
        config(['sistema_experto.lectura_clinica' => []]);
        $contrato = EjemploTecnicoCOGMEM::paquete();
        $paquete = new PaqueteConocimiento('VER_TEST', app(CargadorConocimiento::class)->instantanea('VER_TEST'),
            $contrato->contratosRelaciones, true, 'RESULTADO', $contrato->contratosExtraccion);

        return [$autor, $paquete];
    }

    public function test_crear_suceder_retirar_y_recuperar_contenido_original(): void
    {
        [$autor, $p] = $this->preparar();
        $gestor = app(Gestor::class);
        $v1 = $gestor->crear($autor, $p, 'TEST-REVISION-1', 'Primera revisión exclusivamente sintética.');
        $antes = app(CargadorConocimiento::class)->instantanea($v1);
        $nueva = ['id' => 'SIM-REGLA-NUEVA', 'codigo' => 'SIM-NUEVA', 'nombre' => 'Conjunción de prueba',
            'descripcion' => 'Regla ficticia creada mediante datos.', 'fundamento' => 'Fixture de ingeniería; sin interpretación clínica.',
            'condiciones' => [['variable' => 'VAR_INS', 'valor' => 'VAL_A']], 'criterio_dominio' => 'CR_DOM', 'valor_consecuencia' => 'RES_D'];
        $p2 = (new Editor)->revisar($p, 'REVISION_2', [$nueva], ['RULE_B']);
        $v2 = $gestor->crear($autor, $p2, 'TEST-REVISION-2', 'Sucesora sintética para probar nuevas condiciones.', $v1);
        $leida = $gestor->leer($autor, $v2, $p2);
        self::assertCount(3, $leida->tabla('reglas_expertas'));
        self::assertSame($v1, DB::table('versiones_modelo_experto')->where('cod_version_modelo', $v2)->value('cod_version_anterior'));
        self::assertArrayHasKey('SIM-NUEVA', (new Editor)->comparar($gestor->leer($autor, $v1, $p), $leida));
        $gestor->retirar($autor, $v1, 'Retirar el paquete anterior de nuevas pruebas.');
        self::assertSame($antes, app(CargadorConocimiento::class)->instantanea($v1));
        self::assertSame([], (new Editor)->comparar($gestor->leer($autor, $v1, $p), $gestor->leer($autor, $v1, $p)));
        self::assertSame('INACTIVO', DB::table('versiones_modelo_experto')->where('cod_version_modelo', $v1)->value('estado'));
        self::assertTrue(DB::table('activity_log')->where('event', 'revision_tecnica_retirada')->where('causer_id', $autor->getKey())->exists());
        $memoria = new MemoriaTrabajo(new ContextoEjecucion(
            'SIM-PRUEBA', 'SINTETICO-01', $v1, new \DateTimeImmutable('2026-10-18')));
        $this->expectException(DomainException::class);
        $gestor->probar($autor, $v1, $p, $memoria, 'MEM');
    }

    public function test_duplicado_no_sobrescribe_y_fallo_intermedio_revierte_conocimiento_y_auditoria(): void
    {
        [$autor, $p] = $this->preparar();
        $gestor = app(Gestor::class);
        $id = $gestor->crear($autor, $p, 'TEST-ORIGINAL', 'Contenido de referencia para prueba.');
        $antes = app(CargadorConocimiento::class)->instantanea($id);
        try {
            $gestor->crear($autor, $p, 'TEST-ORIGINAL', 'Intento de sobrescribir el original.');
            self::fail('Se sobrescribió una revisión.');
        } catch (DomainException) {
            self::assertSame($antes, app(CargadorConocimiento::class)->instantanea($id));
        }
        $filas = array_map(fn ($tabla) => DB::table($tabla)->count(), ['versiones_modelo_experto', 'nodos_semanticos', 'activity_log', 'secuencias_codigos']);
        $this->mock(IdentidadesConocimiento::class)
            ->shouldReceive('traducir')->once()->andThrow(new DomainException('Fallo técnico de traducción.'));
        try {
            $gestor->crear($autor, $p, 'TEST-FALLO', 'Debe revertirse sin conocimiento parcial.');
            self::fail('Se ignoró un fallo técnico.');
        } catch (DomainException) {
            self::assertSame($filas, array_map(fn ($tabla) => DB::table($tabla)->count(), ['versiones_modelo_experto', 'nodos_semanticos', 'activity_log', 'secuencias_codigos']));
        }
    }

    public function test_medico_no_administra_conocimiento_aunque_pueda_ejecutar_observaciones(): void
    {
        [$autor, $p] = $this->preparar();
        $autor->syncRoles(['MEDICO GENERAL/GERIATRA']);
        $antes = DB::table('versiones_modelo_experto')->count();
        try {
            app(Gestor::class)->crear($autor, $p, 'TEST-DENEGADO', 'No autorizar edición por rol clínico.');
            self::fail('Se amplió la competencia clínica.');
        } catch (AuthorizationException) {
            self::assertSame($antes, DB::table('versiones_modelo_experto')->count());
        }
    }

    public function test_entorno_local_no_habilita_el_gestor_con_datos_ficticios(): void
    {
        [$autor, $p] = $this->preparar();
        $this->app->instance('env', 'local');
        $this->expectException(DomainException::class);
        app(Gestor::class)->crear($autor, $p, 'TEST-NO-LOCAL', 'No escribir en una base de desarrollo.');
    }

    public function test_prueba_usa_version_almacenada_y_no_un_paquete_suministrado_por_el_cliente(): void
    {
        [$autor, $p] = $this->preparar();
        $gestor = app(Gestor::class);
        $id = $gestor->crear($autor, $p, 'TEST-EJECUTABLE', 'Revisión técnica para ejecutar condiciones.');
        $memoria = new MemoriaTrabajo(new ContextoEjecucion(
            'SIM-PRUEBA', 'SINTETICO-01', $id, new \DateTimeImmutable('2026-10-18')));
        $nodo = DB::table('nodos_semanticos')->where('cod_version_modelo', $id)->where('codigo_semantico', 'COG-MEM')->value('cod_nodo_semantico');
        $r = $gestor->probar($autor, $id, $p, $memoria, $nodo);
        self::assertCount(2, $r['reglas']);
        self::assertFalse($r['persistido']);
        $autor->estado = 'INACTIVO';
        $autor->save();
        $this->expectException(AuthorizationException::class);
        $gestor->probar($autor, $id, $p, $memoria, $nodo);
    }

    public function test_consola_crea_y_retira_una_revision_declarativa_sin_editar_php(): void
    {
        [$autor, $p] = $this->preparar();
        $tablas = [];
        foreach (PaqueteConocimiento::CLAVES as $tabla => $pk) {
            $tablas[$tabla] = $p->tabla($tabla);
        }
        $archivo = tempnam(sys_get_temp_dir(), 'rm-conocimiento-');
        self::assertNotFalse($archivo);
        try {
            file_put_contents($archivo, json_encode(['version' => $p->version, 'tablas' => $tablas,
                'contratos_relaciones' => $p->contratosRelaciones, 'contratos_extraccion' => $p->contratosExtraccion, 'tipo_regla_resultado' => $p->tipoReglaResultado], JSON_THROW_ON_ERROR));
            $salida = Artisan::call('experto:gestionar-conocimiento', ['operacion' => 'crear', '--autor' => $autor->getKey(),
                '--codigo' => 'TEST-CONSOLA', '--motivo' => 'Prueba de creación declarativa por consola.', '--paquete' => $archivo]);
            self::assertSame(0, $salida, Artisan::output());
            $version = DB::table('versiones_modelo_experto')->where('codigo_version', 'TEST-CONSOLA')->value('cod_version_modelo');
            $this->artisan('experto:gestionar-conocimiento', ['operacion' => 'comparar', '--autor' => $autor->getKey(),
                '--revision' => $version, '--paquete' => $archivo])->assertSuccessful();
            $this->artisan('experto:gestionar-conocimiento', ['operacion' => 'retirar', '--autor' => $autor->getKey(),
                '--revision' => $version, '--motivo' => 'Se conserva historia y se retira el paquete.'])->assertSuccessful();
            self::assertNotNull(DB::table('versiones_modelo_experto')->where('cod_version_modelo', $version)->value('fecha_hora_retiro'));
        } finally {
            unlink($archivo);
        }
    }

    public function test_impacto_usa_las_revisiones_guardadas_y_conserva_las_fuentes_aunque_cambien_las_pk(): void
    {
        [$autor, $p] = $this->preparar();
        $gestor = app(Gestor::class);
        $v1 = $gestor->crear($autor, $p, 'TEST-IMPACTO-1', 'Primera versión para comparar la misma batería.');
        $p2 = (new Editor)->revisar($p, 'VER_SUCESORA', retiradas: ['RULE_A']);
        $v2 = $gestor->crear($autor, $p2, 'TEST-IMPACTO-2', 'Sucesora sin la regla positiva A.', $v1);
        $casos = [];
        foreach (['literal_A', 'literal_B', null] as $i => $valor) {
            $par = [];
            foreach (['anterior' => $v1, 'sucesora' => $v2] as $lado => $version) {
                $leida = $gestor->leer($autor, $version, $p);
                $map = array_values(array_filter($leida->tabla('mapeos_variables_fuente'),
                    static fn ($r) => $r['clave_selector'] === 'COMP_TEST'))[0];
                $criterio = array_values(array_filter($leida->tabla('nodos_semanticos'),
                    static fn ($n) => $n['codigo_semantico'] === 'COG-MEM'))[0]['cod_nodo_semantico'];
                $m = new MemoriaTrabajo(new ContextoEjecucion('SIM-IMPACTO-'.$i, 'SINTETICO-01', $version, new \DateTimeImmutable('2026-10-18')));
                $f = new FuenteBrutaCOGMEM($map['cod_fuente_dato_experta'], 'aplicaciones_instrumento', 'COMP_TEST',
                    'SIM-APP', 'SINTETICO-01', new \DateTimeImmutable('2026-10-17'), $valor, $valor, 'SIM-AUTOR', true, true,
                    contexto: ['componente_instrumental_verificado' => true]);
                $e = (new AdaptadorHechosCOGMEM)->adaptar($leida, $m, $f, $map['cod_mapeo_variable_fuente']);
                $m->vincular($criterio, $e->id, 'INSTRUMENTAL');
                $par[$lado] = $m;
                $par['criterio_'.$lado] = $criterio;
            }
            $casos[] = $par;
        }
        $r = $gestor->compararImpacto($autor, $v1, $v2, $p, $casos);
        self::assertCount(3, $r['casos']);
        self::assertSame(['CUMPLE', 'NO_CUMPLE'], array_column($r['casos']['SIM-IMPACTO-0']['anterior'], 'estado'));
        self::assertSame(['NO_CUMPLE'], array_column($r['casos']['SIM-IMPACTO-0']['sucesora'], 'estado'));
        self::assertFalse($r['persistido']);
        $gestor->retirar($autor, $v1, 'La anterior ya no admite pruebas nuevas.');
        $this->expectException(DomainException::class);
        $gestor->compararImpacto($autor, $v1, $v2, $p, $casos);
    }
}
