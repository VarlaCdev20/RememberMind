<?php

declare(strict_types=1);

namespace Tests\Feature\SistemaExperto\Infrastructure\Repositories;

use App\SistemaExperto\Domain\Entities\Dcl\ResultadoClasificacionDcl;
use App\SistemaExperto\Domain\Entities\Hechos\MemoriaTrabajo;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\EstadoDominioEnum;
use App\SistemaExperto\Domain\ValueObjects\Dcl\MotivoAbstencionEnum;
use App\SistemaExperto\Infrastructure\Persistence\Mappers\MapeadorResultados;
use App\SistemaExperto\Infrastructure\Persistence\Repositories\EvaluacionExpertaEloquentRepository;
use Closure;
use DateTimeZone;
use DomainException;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades as D;
use Tests\Unit\SistemaExperto\Infrastructure\Mappers\EscenarioMapeadores as F;

/**
 * TÍTULO: Lectura de resultados con memoria original y escrituras cerradas.
 * CAPA: Infrastructure - Repositories (pruebas de integración).
 * ¿PARA QUÉ SIRVE? Verifica el puerto de evaluaciones dentro del alcance representable.
 * ¿QUÉ INFORMACIÓN RECIBE? Modelos y memorias sintéticas explícitas.
 * ¿QUÉ HACE? Consulta criterios/resultados y rechaza instantáneas o procedencias ajenas.
 * ¿QUÉ DEVUELVE? Aserciones sobre el contrato y sus efectos.
 * ¿QUÉ NO DEBE HACER? No guarda diagnósticos, fabrica C01–C05 ni convierte G09 en A/P.
 */
final class EvaluacionExpertaEloquentRepositoryTest extends RepositoriosTestCase
{
    /** Conserva la memoria original y los cinco U explícitos; sin parámetros ni excepciones esperadas. */
    public function test_recupera_el_vector_con_los_cinco_dominios_originales(): void
    {
        [$modelo, $memoria] = F::resultados(['U', 'U', 'U', 'U', 'U']);
        EscenarioRepositorios::guardarGrafo($modelo);
        DB::connection()->enableQueryLog();
        DB::connection()->flushQueryLog();

        $vector = $this->repositorio(static fn (): MemoriaTrabajo => $memoria)
            ->obtener('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());

        self::assertSame($memoria, $vector->memoria);
        self::assertCount(5, $vector->resultados);
        foreach ($vector->resultados as $resultado) {
            self::assertSame(EstadoDominioEnum::DESCONOCIDO, $resultado->estado);
        }
        $this->exigirSoloLecturas();
    }

    /** Lee un I previamente declarado en el fixture, sin interpretarlo; sin parámetros ni excepciones esperadas. */
    public function test_preserva_soporte_y_procedencia_originales_del_resultado(): void
    {
        [, $memoria] = EscenarioRepositorios::resultadosConSoporte();
        $vector = $this->repositorio(static fn (): MemoriaTrabajo => $memoria)
            ->obtener('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());

        $resultado = $vector->resultados['COG-MEM'];
        self::assertSame(EstadoDominioEnum::NO_INTERPRETABLE, $resultado->estado);
        self::assertSame($memoria->evidencias['SIM-EVID'], $resultado->evidencias[0]);
    }

    /** @throws DomainException Una PK de evidencia coincidente no permite sustituir su origen físico. */
    public function test_rechaza_soporte_cuyo_registro_fisico_no_coincide_con_la_memoria(): void
    {
        [, $memoria] = EscenarioRepositorios::resultadosConSoporte();
        DB::table('evidencias_evaluacion')->update(['cod_registro_fuente' => 'SIM-OTRO-ORIGEN']);
        $this->expectException(DomainException::class);
        $this->repositorio(static fn (): MemoriaTrabajo => $memoria)
            ->obtener('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());
    }

    /** La ausencia física no invoca la recuperación de memoria; sin parámetros ni excepciones esperadas. */
    public function test_devuelve_null_solo_para_evaluacion_inexistente(): void
    {
        $repo = $this->repositorio(static function (): never {
            throw new RuntimeException('No debe invocarse.');
        });

        self::assertNull($repo->obtener('SIM-NO-EXISTE', 'SINTETICO-RES', 'SIM-V1', self::corte()));
        self::assertNull($repo->obtenerClasificacion('SIM-NO-EXISTE', 'SINTETICO-RES', 'SIM-V1', self::corte(), 'SIM-CONTRATO'));
    }

    /**
     * Rechaza instantáneas sin identidad temporal exacta.
     *
     * @param  string  $caso  Incompatibilidad independiente.
     *
     * @throws DomainException Rechazo esperado de contexto o corte ajeno.
     */
    #[DataProvider('contextosAjenos')]
    public function test_no_sustituye_memoria_original_por_otro_contexto(string $caso): void
    {
        [$modelo, $original] = F::resultados(['U', 'U', 'U', 'U', 'U']);
        EscenarioRepositorios::guardarGrafo($modelo);
        $memoria = new MemoriaTrabajo('SIM-EVAL', $caso === 'residente' ? 'SINTETICO-OTRO' : 'SINTETICO-RES',
            'SIM-V1', $caso === 'corte' ? self::corte()->modify('+1 second') : $original->fechaCorte);
        $this->expectException(DomainException::class);
        $this->repositorio(static fn (): MemoriaTrabajo => $memoria)
            ->obtener('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());
    }

    /** @return iterable<string, array{string}> Contextos ajenos; sin parámetros ni excepciones. */
    public static function contextosAjenos(): iterable
    {
        yield 'residente' => ['residente'];
        yield 'corte' => ['corte'];
    }

    /** @throws RuntimeException La memoria no persistida por D-137 debe estar disponible por el colaborador explícito. */
    public function test_memoria_no_disponible_es_error_y_no_ausencia_de_evaluacion(): void
    {
        [$modelo] = F::resultados(['U', 'U', 'U', 'U', 'U']);
        EscenarioRepositorios::guardarGrafo($modelo);
        $this->expectException(RuntimeException::class);
        $this->repositorio(static fn (): null => null)->obtener('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());
    }

    /** @throws DomainException El mapper rechaza los resultados G09 sin equivalencia literal. */
    public function test_no_convierte_resultados_g09_en_estados_cognitivos(): void
    {
        [$modelo, $memoria] = F::resultados(['U', 'U', 'U', 'U', 'U']);
        EscenarioRepositorios::guardarGrafo($modelo);
        DB::table('valores_semanticos')->where('cod_valor_semantico', 'SIM-VS-COG-MEM')->update(['codigo_valor' => 'DIFICULTAD_EVIDENCIADA']);

        $this->expectException(DomainException::class);
        $this->repositorio(static fn (): MemoriaTrabajo => $memoria)
            ->obtener('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());
    }

    /** @throws DomainException Una fila de resultado ausente no se reemplaza por U ni P. */
    public function test_no_completa_resultados_ausentes(): void
    {
        [$modelo, $memoria] = F::resultados(['U', 'U', 'U', 'U', 'U']);
        EscenarioRepositorios::guardarGrafo($modelo);
        DB::table('resultados_criterio')->where('cod_resultado_criterio', 'SIM-RES-COG-MEM')->delete();

        $this->expectException(DomainException::class);
        $this->repositorio(static fn (): MemoriaTrabajo => $memoria)
            ->obtener('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());
    }

    /** Escrituras no soportadas fallan sin emitir SQL ni alterar el perfil; sin parámetros ni excepciones sin capturar. */
    public function test_las_escrituras_sin_catalogo_no_generan_efectos_ni_exito_falso(): void
    {
        $perfil = D::perfil(['U', 'U', 'U', 'U', 'U']);
        $clasificacion = new ResultadoClasificacionDcl($perfil, null, [MotivoAbstencionEnum::SOLO_LABORATORIO_NO_EMITIR], 'Salida ficticia de prueba.');
        $repo = $this->repositorio(static fn (): MemoriaTrabajo => $perfil->cognicion->memoria);
        DB::connection()->enableQueryLog();
        DB::connection()->flushQueryLog();
        foreach ([fn () => $repo->guardar($perfil->cognicion), fn () => $repo->guardarClasificacion($clasificacion)] as $operacion) {
            try {
                $operacion();
                self::fail('La escritura no debe confirmarse.');
            } catch (DomainException $error) {
                self::assertNotSame('', $error->getMessage());
            }
        }
        $this->exigirSoloLecturas();
        self::assertSame(0, DB::table('evaluaciones_expertas')->count());
        self::assertSame(0, DB::table('resultados_criterio')->count());
    }

    /** @throws RuntimeException Una evaluación existente no implica que su contrato DCL sea reconstruible. */
    public function test_clasificacion_no_representable_no_se_devuelve_como_falso_null(): void
    {
        [$modelo, $memoria] = F::resultados(['U', 'U', 'U', 'U', 'U']);
        EscenarioRepositorios::guardarGrafo($modelo);
        $this->expectException(RuntimeException::class);
        $this->repositorio(static fn (): MemoriaTrabajo => $memoria)
            ->obtenerClasificacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte(), 'SIM-CONTRATO');
    }

    /** @param Closure $memoria Recuperación de la instantánea. @return EvaluacionExpertaEloquentRepository Repositorio real. */
    private function repositorio(Closure $memoria): EvaluacionExpertaEloquentRepository
    {
        return new EvaluacionExpertaEloquentRepository(new MapeadorResultados(new DateTimeZone('America/La_Paz'), ['SIM-SOPORTE']),
            $memoria, $this->conexionPruebas());
    }
}
