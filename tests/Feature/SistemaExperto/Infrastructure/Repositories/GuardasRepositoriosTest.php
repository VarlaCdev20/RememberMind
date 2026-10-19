<?php

declare(strict_types=1);

namespace Tests\Feature\SistemaExperto\Infrastructure\Repositories;

use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Mapeadores\MapeadorEvidencias;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Mapeadores\MapeadorRedSemantica;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Mapeadores\MapeadorReglas;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Mapeadores\MapeadorResultados;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Repositorios\EvaluacionExpertaEloquentRepository;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Repositorios\EvidenciaEloquentRepository;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Repositorios\RedSemanticaEloquentRepository;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Repositorios\ReglaExpertaEloquentRepository;
use DateTimeZone;
use DomainException;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\Unit\SistemaExperto\Infrastructure\Mappers\EscenarioMapeadores as F;

/**
 * TÍTULO: Guardas de ámbito y conexión de los cuatro repositorios.
 * CAPA: Infrastructure - Repositories (pruebas de integración).
 * ¿PARA QUÉ SIRVE? Impide consultas fuera de la PoC segregada.
 * ¿QUÉ INFORMACIÓN RECIBE? Modelos y memorias sintéticas explícitas.
 * ¿QUÉ HACE? Intenta lecturas reales fuera de testing o contra destinos no permitidos.
 * ¿QUÉ DEVUELVE? Aserciones sobre el contrato y sus efectos.
 * ¿QUÉ NO DEBE HACER? No simula autorización institucional ni publica el módulo.
 */
final class GuardasRepositoriosTest extends RepositoriosTestCase
{
    /** Rechaza producción antes de ejecutar SQL; sin parámetros ni excepciones sin capturar. */
    public function test_los_cuatro_repositorios_rechazan_entorno_no_testing(): void
    {
        DB::connection()->enableQueryLog();
        DB::connection()->flushQueryLog();
        $this->app->instance('env', 'production');
        try {
            foreach ($this->lecturas($this->conexionPruebas()) as $leer) {
                try {
                    $leer();
                    self::fail('Fuera de testing no se permite consultar.');
                } catch (RuntimeException $error) {
                    self::assertStringContainsString('testing', $error->getMessage());
                }
            }
        } finally {
            $this->app->instance('env', 'testing');
        }
        self::assertSame([], DB::connection()->getQueryLog());
    }

    /** Rechaza una base SQLite física sin abrirla ni crearla; sin parámetros ni excepciones sin capturar. */
    public function test_rechaza_bases_no_desechables_sin_abrir_su_pdo(): void
    {
        $ruta = sys_get_temp_dir().'/orion-rechazada-'.bin2hex(random_bytes(8)).'.sqlite';
        config(['database.connections.orion_rechazada' => ['driver' => 'sqlite', 'database' => $ruta, 'prefix' => '']]);
        foreach ($this->lecturas('orion_rechazada') as $leer) {
            try {
                $leer();
                self::fail('Una base física no está autorizada.');
            } catch (RuntimeException $error) {
                self::assertStringContainsString('desechable', $error->getMessage());
            }
        }
        self::assertFileDoesNotExist($ruta);
    }

    /** No hereda la conexión predeterminada ni en relaciones precargadas; sin parámetros ni excepciones esperadas. */
    public function test_usa_la_conexion_nombrada_tambien_para_las_relaciones(): void
    {
        EscenarioRepositorios::guardarGrafo(F::regla());
        $conexion = $this->conexionPruebas();
        config(['database.connections.orion_otra' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        DB::setDefaultConnection('orion_otra');
        try {
            $repo = new ReglaExpertaEloquentRepository(new MapeadorReglas('RESULTADO'), $conexion);
            self::assertSame('SIM-R', $repo->obtener('SIM-R', 'SIM-V1')->id);
        } finally {
            DB::setDefaultConnection($conexion);
        }
    }

    /** Identidades inválidas se rechazan antes de Eloquent; sin parámetros ni excepciones sin capturar. */
    public function test_rechaza_identidades_vacias_y_casos_asistenciales_antes_de_consultar(): void
    {
        DB::connection()->enableQueryLog();
        DB::connection()->flushQueryLog();
        $conexion = $this->conexionPruebas();
        $zona = new DateTimeZone('America/La_Paz');
        $operaciones = [
            fn () => $this->reglas()->obtener('', 'SIM-V1'),
            fn () => (new RedSemanticaEloquentRepository(new MapeadorRedSemantica, $conexion))->porVersion(''),
            fn () => $this->evidencias()->paraEvaluacion('SIM-EVAL', 'RES-REAL', 'SIM-V1', self::corte()),
            fn () => (new EvaluacionExpertaEloquentRepository(new MapeadorResultados($zona), static fn () => null, $conexion))
                ->obtener('EVAL-REAL', 'SINTETICO-RES', 'SIM-V1', self::corte()),
        ];
        foreach ($operaciones as $operacion) {
            try {
                $operacion();
                self::fail('Una identidad inválida no debe consultar.');
            } catch (DomainException $error) {
                self::assertNotSame('', $error->getMessage());
            }
        }
        self::assertSame([], DB::connection()->getQueryLog());
    }

    /**
     * Prepara una lectura por cada puerto, sin ejecutar aún consultas.
     *
     * @param  string  $conexion  Nombre explícito a comprobar.
     * @return list<\Closure> Operaciones reales con contexto válido.
     */
    private function lecturas(string $conexion): array
    {
        $zona = new DateTimeZone('America/La_Paz');

        return [
            fn () => (new ReglaExpertaEloquentRepository(new MapeadorReglas('RESULTADO'), $conexion))->porVersion('SIM-V1'),
            fn () => (new RedSemanticaEloquentRepository(new MapeadorRedSemantica, $conexion))->porVersion('SIM-V1'),
            fn () => (new EvidenciaEloquentRepository(new MapeadorEvidencias($zona), static fn () => [], $zona, $conexion))
                ->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte()),
            fn () => (new EvaluacionExpertaEloquentRepository(new MapeadorResultados($zona), static fn () => null, $conexion))
                ->obtener('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte()),
        ];
    }
}
