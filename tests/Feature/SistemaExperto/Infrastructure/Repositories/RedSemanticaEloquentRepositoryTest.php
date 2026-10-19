<?php

declare(strict_types=1);

namespace Tests\Feature\SistemaExperto\Infrastructure\Repositories;

use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Mapeadores\MapeadorRedSemantica;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Repositorios\RedSemanticaEloquentRepository;
use DomainException;
use Illuminate\Support\Facades\DB;
use Tests\Unit\SistemaExperto\Infrastructure\Mappers\EscenarioMapeadores as F;

/**
 * TÍTULO: Lectura Eloquent de redes semánticas versionadas.
 * CAPA: Infrastructure - Repositories (pruebas de integración).
 * ¿PARA QUÉ SIRVE? Distingue ausencia de versión, grafo vacío y conocimiento incompatible.
 * ¿QUÉ INFORMACIÓN RECIBE? Filas reales sintéticas en el esquema congelado.
 * ¿QUÉ HACE? Precarga el grafo y ejecuta el mapper real.
 * ¿QUÉ DEVUELVE? Aserciones sobre identidades, versión y ausencia de escrituras.
 * ¿QUÉ NO DEBE HACER? No fabrica aristas ni redefine estados del conocimiento.
 */
final class RedSemanticaEloquentRepositoryTest extends RepositoriosTestCase
{
    /** Conserva identidad y dirección sin consultas por nodo; sin parámetros ni excepciones esperadas. */
    public function test_recupera_la_red_y_sus_extremos_originales(): void
    {
        EscenarioRepositorios::guardarGrafo(F::red());
        DB::connection()->enableQueryLog();
        DB::connection()->flushQueryLog();

        $red = $this->repositorio()->porVersion('SIM-V1');

        self::assertSame('SIM-V1', $red->versionConocimiento);
        self::assertCount(2, $red->nodos);
        self::assertCount(1, $red->relaciones);
        self::assertCount(3, DB::connection()->getQueryLog());
        $this->exigirSoloLecturas();
    }

    /** Distingue versión vacía y versión inexistente sin fallback; sin parámetros ni excepciones esperadas. */
    public function test_una_version_vacia_no_equivale_a_una_version_inexistente(): void
    {
        self::assertSame([], $this->repositorio()->porVersion('SIM-V1')->nodos);
        self::assertNull($this->repositorio()->porVersion('SIM-NO-EXISTE'));
    }

    /** @throws DomainException Rechazo esperado de un estado que Domain no conserva. */
    public function test_no_oculta_un_nodo_inactivo_filtrandolo(): void
    {
        EscenarioRepositorios::guardarGrafo(F::red());
        DB::table('nodos_semanticos')->where('cod_nodo_semantico', 'SIM-N-SUB')->update(['estado' => 'INACTIVO']);

        $this->expectException(DomainException::class);
        $this->repositorio()->porVersion('SIM-V1');
    }

    /** @throws DomainException Rechazo esperado de una modalidad no representable. */
    public function test_propaga_el_rechazo_del_mapper_para_modalidad_no_representable(): void
    {
        EscenarioRepositorios::guardarGrafo(F::red());
        DB::table('relaciones_semanticas')->update(['modalidad_relacion' => 'SIM-MODALIDAD']);

        $this->expectException(DomainException::class);
        $this->repositorio()->porVersion('SIM-V1');
    }

    /** @return RedSemanticaEloquentRepository Repositorio real sobre el destino de prueba; sin parámetros ni excepciones. */
    private function repositorio(): RedSemanticaEloquentRepository
    {
        return new RedSemanticaEloquentRepository(new MapeadorRedSemantica, $this->conexionPruebas());
    }
}
