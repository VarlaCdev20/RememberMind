<?php

declare(strict_types=1);

namespace Tests\Feature\SistemaExperto\Infrastructure\Repositories;

use App\SistemaExperto\Domain\ValueObjects\Conocimiento\OperadorCondicionEnum;
use App\SistemaExperto\Infrastructure\Persistence\Mappers\MapeadorReglas;
use App\SistemaExperto\Infrastructure\Persistence\Repositories\ReglaExpertaEloquentRepository;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\Unit\SistemaExperto\Infrastructure\Mappers\EscenarioMapeadores as F;

/**
 * TÍTULO: Lectura real de reglas por ID y versión.
 * CAPA: Infrastructure - Repositories (pruebas de integración).
 * ¿PARA QUÉ SIRVE? Verifica la implementación del puerto de reglas.
 * ¿QUÉ INFORMACIÓN RECIBE? Fixtures sintéticas sobre las migraciones reales.
 * ¿QUÉ HACE? Consulta por parámetros y convierte con el mapper real.
 * ¿QUÉ DEVUELVE? Aserciones sobre entidades, contexto y consultas.
 * ¿QUÉ NO DEBE HACER? No ejecuta reglas ni acredita conocimiento clínico.
 */
final class ReglaExpertaEloquentRepositoryTest extends RepositoriosTestCase
{
    /** Conserva condiciones y consecuencia exactas; sin parámetros ni excepciones esperadas. */
    public function test_obtiene_la_regla_con_dependencias_precargadas(): void
    {
        EscenarioRepositorios::guardarGrafo(F::regla());
        DB::connection()->enableQueryLog();
        DB::connection()->flushQueryLog();

        $regla = $this->reglas()->obtener('SIM-R', 'SIM-V1');

        self::assertSame('SIM-R', $regla->id);
        self::assertSame('SIM-V1', $regla->versionConocimiento);
        self::assertTrue($regla->habilitada);
        self::assertSame(OperadorCondicionEnum::Y, $regla->condiciones['operador']);
        self::assertCount(2, $regla->condiciones['condiciones']);
        self::assertSame('0', $regla->condiciones['condiciones'][0]['valor']);
        self::assertSame('DIFICULTAD_EVIDENCIADA', $regla->consecuencia['valor']);
        $this->exigirSoloLecturas();
    }

    /** No busca una regla ajena cuando la versión solicitada no la contiene; sin parámetros ni excepciones esperadas. */
    public function test_ausencia_y_version_distinta_no_tienen_fallback(): void
    {
        EscenarioRepositorios::guardarGrafo(F::regla());
        EscenarioRepositorios::version('SIM-V2');

        self::assertNull($this->reglas()->obtener('SIM-NO-EXISTE', 'SIM-V1'));
        self::assertNull($this->reglas()->obtener('SIM-R', 'SIM-V2'));
        self::assertSame([], $this->reglas()->porVersion('SIM-V2'));
    }

    /** Lista reglas inactivas sin consultas adicionales por regla; sin parámetros ni excepciones esperadas. */
    public function test_orden_habilitacion_y_numero_de_consultas_no_dependen_de_la_cantidad_de_reglas(): void
    {
        $primera = F::regla();
        EscenarioRepositorios::guardarGrafo($primera);
        DB::connection()->enableQueryLog();
        DB::connection()->flushQueryLog();
        $this->reglas()->porVersion('SIM-V1');
        $cantidad = count(DB::connection()->getQueryLog());

        $segunda = clone $primera;
        $segunda->setRawAttributes(array_replace($segunda->getAttributes(), ['cod_regla_experta' => 'SIM-R-2', 'estado' => 'INACTIVO']));
        foreach (['condicionesReglaExperta' => 'cod_condicion_regla', 'consecuenciasReglaExperta' => 'cod_consecuencia_regla'] as $relacion => $pk) {
            $copias = [];
            foreach ($primera->getRelation($relacion) as $hijo) {
                $copia = clone $hijo;
                $copia->setRawAttributes(array_replace($copia->getAttributes(), [$pk => $copia->getAttributes()[$pk].'-2', 'cod_regla_experta' => 'SIM-R-2']));
                $copias[] = $copia;
            }
            $segunda->setRelation($relacion, new Collection($copias));
        }
        EscenarioRepositorios::guardarGrafo($segunda);
        DB::connection()->flushQueryLog();
        $reglas = $this->reglas()->porVersion('SIM-V1');

        self::assertSame(['SIM-R', 'SIM-R-2'], array_column($reglas, 'id'));
        self::assertTrue($reglas[0]->habilitada);
        self::assertFalse($reglas[1]->habilitada);
        self::assertCount($cantidad, DB::connection()->getQueryLog());
        $this->exigirSoloLecturas();
    }

    /** @throws DomainException Rechazo esperado del mapper para una regla incompleta. */
    public function test_no_devuelve_una_regla_parcial_sin_consecuencia(): void
    {
        EscenarioRepositorios::guardarGrafo(F::regla());
        DB::table('consecuencias_regla_experta')->delete();

        $this->expectException(DomainException::class);
        $this->reglas()->porVersion('SIM-V1');
    }

    /** Los códigos se enlazan como parámetros y no como SQL; sin parámetros ni excepciones esperadas. */
    public function test_una_version_con_texto_sql_no_amplia_la_consulta(): void
    {
        EscenarioRepositorios::guardarGrafo(F::regla());

        self::assertSame([], $this->reglas()->porVersion("SIM-V1'/**/OR/**/'1'='1"));
    }

    /** @throws RuntimeException El fallo real de lectura no se transforma en una lista vacía. */
    public function test_propaga_fallo_de_lectura_sin_simular_ausencia(): void
    {
        config(['database.connections.orion_vacia' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        $repo = new ReglaExpertaEloquentRepository(
            new MapeadorReglas('RESULTADO'), 'orion_vacia');

        $this->expectException(RuntimeException::class);
        $repo->porVersion('SIM-V1');
    }
}
