<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\GestionarConocimientoExperimental;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\GestionarEjecucionesExperimentales as Gestor;
use Database\Seeders\RolesAndPermissionsSeeder;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use MotorExperto\dominio\conocimiento\EditorPaqueteExperimental;
use MotorExperto\investigacion\dcl\CasoSinteticoCincoDominios as Caso;
use MotorExperto\investigacion\g09\PaqueteSemanticoG09Experimental;
use Tests\TestCase;

final class ArchivoPerfilExperimentalTest extends TestCase
{
    use RefreshDatabase;

    private function autor(): User
    {
        Storage::fake('local');
        $this->seed(RolesAndPermissionsSeeder::class);
        $autor = User::factory()->create();
        $autor->assignRole('SUPERADMINISTRADOR');

        return $autor;
    }

    public function test_conserva_consulta_historial_y_reproduce_exactamente_sin_crear_historia_institucional(): void
    {
        $autor = $this->autor();
        $gestor = app(Gestor::class);
        $caso = Caso::crear(['COG-MEM' => 'A', 'COG-ATE' => 'P', 'COG-EJE' => 'P', 'COG-LEN' => 'P', 'COG-VIS' => 'P']);
        $r = $gestor->ejecutar($autor, $caso);
        self::assertSame('PET-01', $r['resultado']['petersen']['patron_candidato']);
        self::assertFalse($r['conservacion_bdd_institucional']);
        self::assertSame($r, $gestor->ejecutar($autor, $caso));
        self::assertSame($r, $gestor->consultar($autor, 'SINTETICO-01', 'SIM-INTEGRAL-01'));
        self::assertSame($r['resultado'], $gestor->reproducir($autor, 'SINTETICO-01', 'SIM-INTEGRAL-01'));
        self::assertCount(1, $gestor->historial($autor, 'SINTETICO-01'));
        self::assertSame(0, DB::table('evaluaciones_expertas')->count());
        self::assertSame(0, DB::table('residentes')->count());
        self::assertTrue(DB::table('activity_log')->where('causer_id', $autor->getKey())->where('event', 'ejecucion_laboratorio_conservada')->exists());
        $contenido = Storage::disk('local')->get('laboratorio-experto/SINTETICO-01/SIM-INTEGRAL-01.enc');
        self::assertStringNotContainsString('FALLO_SIMULADO', $contenido);
        self::assertStringNotContainsString('PET-01', $contenido);
    }

    public function test_identidad_reutilizada_con_otros_datos_no_sobrescribe_entrada_salida_o_historia(): void
    {
        $autor = $this->autor();
        $gestor = app(Gestor::class);
        $caso = Caso::crear();
        $original = $gestor->ejecutar($autor, $caso);
        $caso['capturas']['respuestas_instrumento'][0]['valor_texto'] = null;
        try {
            $gestor->ejecutar($autor, $caso);
            self::fail('Se sobrescribió el archivo original.');
        } catch (DomainException) {
            self::assertSame($original, $gestor->consultar($autor, 'SINTETICO-01', 'SIM-INTEGRAL-01'));
        }
        self::assertCount(1, $gestor->historial($autor, 'SINTETICO-01'));
    }

    public function test_archivo_manipulado_no_se_acepta_como_resultado_historico(): void
    {
        $autor = $this->autor();
        app(Gestor::class)->ejecutar($autor, Caso::crear());
        Storage::disk('local')->put('laboratorio-experto/SINTETICO-01/SIM-INTEGRAL-01.enc', 'CONTENIDO_ALTERADO');
        $this->expectException(DecryptException::class);
        app(Gestor::class)->consultar($autor, 'SINTETICO-01', 'SIM-INTEGRAL-01');
    }

    public function test_cuenta_revocada_no_consulta_archivo_aunque_tenga_un_objeto_anterior_activo(): void
    {
        $autor = $this->autor();
        app(Gestor::class)->ejecutar($autor, Caso::crear());
        DB::table('usuarios')->where('cod_usuario', $autor->getKey())->update(['estado' => 'INACTIVO']);
        $this->expectException(AuthorizationException::class);
        app(Gestor::class)->consultar($autor, 'SINTETICO-01', 'SIM-INTEGRAL-01');
    }

    public function test_fuera_de_testing_no_crea_archivos_ni_valoraciones(): void
    {
        $autor = $this->autor();
        $this->app->instance('env', 'local');
        try {
            app(Gestor::class)->ejecutar($autor, Caso::crear());
            self::fail('Se habilitó el archivo sobre desarrollo.');
        } catch (DomainException) {
            self::assertSame([], Storage::disk('local')->allFiles());
            self::assertSame(0, DB::table('evaluaciones_expertas')->count());
        }
    }

    public function test_no_entrega_otro_caso_ni_acepta_rutas_manipuladas(): void
    {
        $autor = $this->autor();
        app(Gestor::class)->ejecutar($autor, Caso::crear());
        foreach (['SINTETICO-OTRO', '../SINTETICO-01'] as $caso) {
            try {
                app(Gestor::class)->consultar($autor, $caso, 'SIM-INTEGRAL-01');
                self::fail('Se leyó un archivo ajeno a la identidad solicitada.');
            } catch (DomainException) {
                self::assertSame(0, DB::table('evaluaciones_expertas')->count());
            }
        }
    }

    public function test_comandos_archivan_consultan_historial_y_reproducen_sin_php_adicional(): void
    {
        $autor = $this->autor();
        $archivo = tempnam(sys_get_temp_dir(), 'rm-archivo-');
        self::assertNotFalse($archivo);
        try {
            file_put_contents($archivo, json_encode(Caso::crear(), JSON_THROW_ON_ERROR));
            $this->artisan('experto:archivo-perfil', ['operacion' => 'ejecutar', '--autor' => $autor->getKey(), '--entrada' => $archivo])->assertSuccessful();
            foreach (['consultar', 'historial', 'reproducir'] as $operacion) {
                $this->artisan('experto:archivo-perfil', ['operacion' => $operacion, '--autor' => $autor->getKey(),
                    '--caso' => 'SINTETICO-01', '--evaluacion' => 'SIM-INTEGRAL-01'])->assertSuccessful();
            }
            file_put_contents($archivo, '{"contexto":null}');
            $this->artisan('experto:archivo-perfil', ['operacion' => 'ejecutar', '--autor' => $autor->getKey(), '--entrada' => $archivo])->assertFailed();
        } finally {
            unlink($archivo);
        }
        $autor->syncRoles(['MEDICO GENERAL/GERIATRA']);
        $this->artisan('experto:archivo-perfil', ['operacion' => 'consultar', '--autor' => $autor->getKey(),
            '--caso' => 'SINTETICO-01', '--evaluacion' => 'SIM-INTEGRAL-01'])->assertFailed();
        self::assertSame(0, DB::table('evaluaciones_expertas')->count());
    }

    public function test_ciclo_completo_con_conocimiento_sql_nueva_regla_sucesora_retiro_e_historia_intacta(): void
    {
        $autor = $this->autor();
        $conocimiento = app(GestionarConocimientoExperimental::class);
        $archivo = app(Gestor::class);
        $p = PaqueteSemanticoG09Experimental::paraConservacion();
        $v1 = $conocimiento->crear($autor, $p, 'TEST-G09-ORIGINAL', 'Paquete sintético para aceptación integral.');
        $caso = Caso::crear();
        $caso['capturas']['respuestas_instrumento'][1]['valor_texto'] = 'SIN_HALLAZGO_SIMULADO';
        $original = $archivo->ejecutarConRevision($autor, $caso, $v1);
        self::assertSame([], $original['resultado']['subcomponentes']['ATE-S02']['resultado']);
        $regla = ['id' => 'SIM-REGLA-DOS', 'codigo' => 'SIM-DOS-TAREAS', 'nombre' => 'Conjunción ficticia entre dos tareas',
            'descripcion' => 'Regla sintética de aceptación de software.', 'fundamento' => 'Software sin interpretación clínica.',
            'condiciones' => [['variable' => 'VAR-R-ATE-S01-P01-C1', 'valor' => 'VAL-SIM-TRUE'],
                ['variable' => 'VAR-R-ATE-S03-P01-C1', 'valor' => 'VAL-SIM-TRUE']],
            'criterio_dominio' => 'CR-ATE', 'valor_consecuencia' => 'VAL-RES-R-ATE-S02-P01'];
        $p2 = (new EditorPaqueteExperimental)->revisar($p, 'SIM-G09-SUCESORA', [$regla], ['R-ATE-S02-P01']);
        $v2 = $conocimiento->crear($autor, $p2, 'TEST-G09-SUCESORA', 'Sucesora con condición nueva probada sobre las mismas tareas.', $v1);
        $caso['contexto']['evaluacion'] = 'SIM-INTEGRAL-02';
        $nuevo = $archivo->ejecutarConRevision($autor, $caso, $v2);
        self::assertSame('CUMPLE', $nuevo['resultado']['reglas_examinadas']['g09']['SIM-DOS-TAREAS']['estado']);
        self::assertNotEmpty($nuevo['resultado']['subcomponentes']['ATE-S02']['resultado']);
        self::assertNotEmpty($nuevo['resultado']['reglas_examinadas']['g09']['SIM-DOS-TAREAS']['soportes_originales']);
        $conocimiento->retirar($autor, $v1, 'Sustituida en nuevas ejecuciones técnicas; conservar originales.');
        self::assertSame($original, $archivo->consultar($autor, 'SINTETICO-01', 'SIM-INTEGRAL-01'));
        self::assertSame($original['resultado'], $archivo->reproducir($autor, 'SINTETICO-01', 'SIM-INTEGRAL-01'));
        self::assertCount(2, $archivo->historial($autor, 'SINTETICO-01'));
        self::assertSame(0, DB::table('evaluaciones_expertas')->count());
        $caso['contexto']['evaluacion'] = 'SIM-INTEGRAL-03';
        $copiaRetirada = $conocimiento->leer($autor, $v1, $p)->declaracion();
        $entradaLibre = $caso + ['conocimientoG09' => $copiaRetirada];
        try {
            $archivo->ejecutar($autor, $entradaLibre);
            self::fail('La declaración libre eludió el retiro SQL.');
        } catch (DomainException) {
            self::assertCount(2, $archivo->historial($autor, 'SINTETICO-01'));
        }
        try {
            $archivo->ejecutarConRevision($autor, $caso, $v1);
            self::fail('Se volvió a ejecutar una revisión retirada.');
        } catch (DomainException) {
            self::assertCount(2, $archivo->historial($autor, 'SINTETICO-01'));
        }
    }
}
