<?php

declare(strict_types=1);

namespace Tests\Feature\SistemaExperto\Infrastructure\Adapters;

use App\Models\EvidenciaEvaluacion;
use App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\FuentesOperativas\AdaptadorIadlExperimental;
use App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\MapeoFuentes\ExtractorVariableMapeada;
use DateTimeZone;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Feature\SistemaExperto\Infrastructure\Repositories\EscenarioRepositorios;

/**
 * TÍTULO:
 * Resolución versionada y rechazo de fuentes no autorizadas.
 *
 * CAPA:
 * Infrastructure - Adapters.
 *
 * ¿PARA QUÉ SIRVE?
 * Recupera datos brutos autorizados sin interpretación clínica.
 *
 * ¿QUÉ INFORMACIÓN RECIBE?
 * Tablas 07 y 08 reales con cambios de fixtures sintéticas.
 *
 * ¿QUÉ HACE?
 * Verifica metadatos, registros explícitos, contexto y ausencia de IADL.
 *
 * ¿QUÉ DEVUELVE?
 * Fuentes exactas o errores explícitos sin efectos sobre datos asistenciales.
 *
 * ¿QUÉ NO DEBE HACER?
 * No habilita fuentes desde nombres de tabla o clases arbitrarias.
 */
final class MapeoFuentesTest extends AdaptadoresTestCase
{
    /** Resuelve por código, PK y versión sin fallback; sin parámetros ni excepciones esperadas. */
    public function test_resuelve_solo_la_fuente_de_la_version_exacta(): void
    {
        $this->prepararControl();
        $resuelta = $this->resolver()->resolver('SIM-V1', 'SIM-CONTROLES');

        self::assertSame('SIM-FU', $resuelta['fuente']->getRawOriginal('cod_fuente_dato_experta'));
        self::assertSame('controles_cognitivos', $resuelta['adaptador']::TABLA);
        self::assertSame(['SIM-FU'], array_keys($this->resolver()->porIds(['SIM-FU'], 'SIM-V1')));
    }

    /** @throws RuntimeException Una fuente ausente en la versión no se recupera desde otra. */
    public function test_fuente_de_otra_version_no_se_reutiliza(): void
    {
        $this->prepararControl();
        EscenarioRepositorios::version('SIM-V2');
        $this->expectException(RuntimeException::class);
        $this->resolver()->resolver('SIM-V2', 'SIM-CONTROLES');
    }

    /**
     * Una tabla permitida no basta si los otros metadatos no coinciden.
     *
     * @param  string  $columna  Metadato manipulado.
     * @param  string|null  $valor  Dato incompatible.
     *
     * @throws DomainException Rechazo esperado antes de consultar fuentes operativas.
     */
    #[DataProvider('metadatosInvalidos')]
    public function test_rechaza_metadatos_fisicos_incompatibles(string $columna, ?string $valor): void
    {
        $this->prepararControl();
        DB::table('fuentes_datos_expertas')->update([$columna => $valor]);
        $this->expectException(DomainException::class);
        $this->resolver()->resolver('SIM-V1', 'SIM-CONTROLES');
    }

    /** @return iterable<string, array{string, string|null}> Incompatibilidades independientes; sin parámetros ni excepciones. */
    public static function metadatosInvalidos(): iterable
    {
        yield 'tabla arbitraria' => ['tabla_raiz', 'usuarios'];
        yield 'PK ajena' => ['campo_pk_raiz', 'cod_usuario'];
        yield 'sin residente' => ['campo_residente', null];
        yield 'fecha técnica' => ['campo_temporal', 'fecha_hora_incorporacion'];
        yield 'sin autor' => ['campo_personal', null];
        yield 'estado ajeno' => ['campo_estado', 'estado_inventado'];
        yield 'fuente inactiva' => ['estado', 'INACTIVO'];
    }

    /** @throws RuntimeException Una clave no registrada no se convierte en nombre de clase ejecutable. */
    public function test_clave_adaptador_desconocida_no_se_instancia(): void
    {
        $this->prepararControl();
        DB::table('fuentes_datos_expertas')->update(['clave_adaptador' => 'App\\Models\\User']);
        $this->expectException(RuntimeException::class);
        $this->resolver()->resolver('SIM-V1', 'SIM-CONTROLES');
    }

    /** La extensión aprobada es disponible como fuente descriptiva, sin crear requisitos. */
    public function test_iadl_aprobada_disponible_no_fabrica_requisitos(): void
    {
        $antes = DB::connection()->getSchemaBuilder()->getTableListing();
        $disponibilidad = (new AdaptadorIadlExperimental)->disponibilidad($this->conexionPruebas());

        self::assertTrue($disponibilidad['disponible']);
        self::assertStringContainsString('no acredita C03', $disponibilidad['motivo']);
        self::assertSame($antes, DB::connection()->getSchemaBuilder()->getTableListing());
    }

    /** @throws DomainException Una fuente IADL con PK de otra tabla continúa siendo inválida. */
    public function test_resolucion_iadl_rechaza_metadatos_incompatibles(): void
    {
        $this->prepararControl();
        DB::table('fuentes_datos_expertas')->update(['clave_adaptador' => 'SIM-IADL', 'tabla_raiz' => 'controles_funcionales_iadl']);
        $this->expectException(DomainException::class);
        $this->resolver()->resolver('SIM-V1', 'SIM-CONTROLES');
    }

    /** @throws DomainException Un mapeo inactivo no se habilita por encontrar su fuente. */
    public function test_mapeo_inactivo_no_se_extrae(): void
    {
        $this->prepararControl();
        DB::table('mapeos_variables_fuente')->update(['estado' => 'INACTIVO']);
        $filas = EvidenciaEvaluacion::on($this->conexionPruebas())->where('cod_evaluacion_experta', 'SIM-EVAL')->get();
        $extractor = new ExtractorVariableMapeada($this->resolver(), new DateTimeZone('America/La_Paz'));
        $this->expectException(DomainException::class);
        $extractor($filas, self::corte(), $this->conexionPruebas());
    }

    /** @throws DomainException La extracción rechaza identidades de dos evaluaciones en un solo lote. */
    public function test_no_mezcla_evaluaciones_en_el_lote(): void
    {
        $this->prepararControl();
        $fila = EvidenciaEvaluacion::on($this->conexionPruebas())->whereKey('SIM-EVID')->firstOrFail();
        $otra = clone $fila;
        $otra->setRawAttributes(array_replace($fila->getAttributes(), ['cod_evidencia_evaluacion' => 'SIM-EVID-2', 'cod_evaluacion_experta' => 'SIM-OTRA']), true);
        $extractor = new ExtractorVariableMapeada($this->resolver(), new DateTimeZone('America/La_Paz'));
        $this->expectException(DomainException::class);
        $extractor(new Collection([$fila, $otra]), self::corte(), $this->conexionPruebas());
    }

    /** @throws RuntimeException La guarda actúa antes de consultar contratos o fuentes. */
    public function test_adaptadores_fuera_de_testing_no_consultan(): void
    {
        $this->app->instance('env', 'production');
        try {
            $this->expectException(RuntimeException::class);
            $this->resolver()->resolver('SIM-V1', 'SIM-CONTROLES');
        } finally {
            $this->app->instance('env', 'testing');
        }
    }

    /** @throws RuntimeException Una conexión física no autorizada se rechaza antes de abrir el archivo. */
    public function test_no_se_abre_una_base_fisica_para_fuentes(): void
    {
        config(['database.connections.orion_fuente_insegura' => ['driver' => 'sqlite', 'database' => 'SIM-NO-ABRIR.sqlite', 'prefix' => '']]);
        $this->expectException(RuntimeException::class);
        ExtractorVariableMapeada::conexionSegura('orion_fuente_insegura');
    }
}
