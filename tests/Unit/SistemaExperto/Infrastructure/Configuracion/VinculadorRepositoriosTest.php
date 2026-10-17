<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Infrastructure\Configuracion;

use App\SistemaExperto\Domain\Repositories\Conocimiento\RedSemanticaRepositoryInterface;
use App\SistemaExperto\Domain\Repositories\Conocimiento\ReglaExpertaRepositoryInterface;
use App\SistemaExperto\Domain\Repositories\Evaluaciones\EvaluacionExpertaRepositoryInterface;
use App\SistemaExperto\Domain\Repositories\Hechos\EvidenciaRepositoryInterface;
use App\SistemaExperto\Infrastructure\Adapters\FuentesOperativas\AdaptadorControlesCognitivos;
use App\SistemaExperto\Infrastructure\Adapters\MapeoFuentes\ExtractorVariableMapeada;
use App\SistemaExperto\Infrastructure\Adapters\MapeoFuentes\ResolutorFuenteExperta;
use App\SistemaExperto\Infrastructure\Configuracion\VinculadorRepositorios;
use App\SistemaExperto\Infrastructure\Persistence\Mappers\MapeadorEvidencias;
use App\SistemaExperto\Infrastructure\Persistence\Mappers\MapeadorRedSemantica;
use App\SistemaExperto\Infrastructure\Persistence\Mappers\MapeadorReglas;
use App\SistemaExperto\Infrastructure\Persistence\Mappers\MapeadorResultados;
use App\SistemaExperto\Infrastructure\Persistence\Repositories\EvaluacionExpertaEloquentRepository;
use App\SistemaExperto\Infrastructure\Persistence\Repositories\EvidenciaEloquentRepository;
use App\SistemaExperto\Infrastructure\Persistence\Repositories\RedSemanticaEloquentRepository;
use App\SistemaExperto\Infrastructure\Persistence\Repositories\ReglaExpertaEloquentRepository;
use DateTimeZone;
use DomainException;
use Illuminate\Container\Container;
use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\SQLiteConnection;
use LogicException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;

/**
 * TÍTULO:
 * Registro aislado de dependencias sin abrir una base de datos.
 *
 * CAPA:
 * Infrastructure - Configuracion (pruebas unitarias).
 *
 * ¿PARA QUÉ SIRVE?
 * Comprueba la resolución de los cuatro puertos y la protección de bindings ajenos.
 *
 * ¿QUÉ INFORMACIÓN RECIBE?
 * Contenedor Laravel real y conexión SQLite cuyo inicializador PDO siempre falla.
 *
 * ¿QUÉ HACE?
 * Registra y resuelve el grafo; prueba colisiones, repetición y rechazos de entorno.
 *
 * ¿QUÉ DEVUELVE?
 * Aserciones de identidad, configuración explícita, PHPDoc y ausencia de consultas.
 *
 * ¿QUÉ NO DEBE HACER?
 * No acredita integración SQL ni abre archivos de base o conexiones productivas.
 */
final class VinculadorRepositoriosTest extends TestCase
{
    /** Contenedor global anterior que se restaura después de cada prueba. */
    private Container $contenedorAnterior;

    /** Resolver Eloquent anterior; no se utiliza para consultar. */
    private ?ConnectionResolverInterface $resolverAnterior;

    /** Contenedor real aislado de cada caso. */
    private Container $contenedor;

    /** Prepara únicamente metadatos de conexión y globals reversibles; sin parámetros ni errores esperados. */
    protected function setUp(): void
    {
        parent::setUp();
        $this->contenedorAnterior = Container::getInstance();
        $this->resolverAnterior = Model::getConnectionResolver();
        $this->contenedor = new Container;
        $this->contenedor->instance('env', 'testing');
        Container::setInstance($this->contenedor);
        $resolver = new ConnectionResolver(['orion_test' => self::conexionSinPdo(':memory:')]);
        Model::setConnectionResolver($resolver);
    }

    /** Restaura exactamente el entorno previo; sin parámetros ni errores esperados. */
    protected function tearDown(): void
    {
        Container::setInstance($this->contenedorAnterior);
        if ($this->resolverAnterior === null) {
            Model::unsetConnectionResolver();
        } else {
            Model::setConnectionResolver($this->resolverAnterior);
        }
        parent::tearDown();
    }

    /** Resuelve todos los contratos sin ejecutar el inicializador PDO; sin parámetros ni excepciones esperadas. */
    public function test_registra_los_cuatro_puertos_y_el_grafo_sin_abrir_pdo(): void
    {
        $this->vinculador()->registrar($this->contenedor);
        $puertos = [
            ReglaExpertaRepositoryInterface::class => ReglaExpertaEloquentRepository::class,
            RedSemanticaRepositoryInterface::class => RedSemanticaEloquentRepository::class,
            EvidenciaRepositoryInterface::class => EvidenciaEloquentRepository::class,
            EvaluacionExpertaRepositoryInterface::class => EvaluacionExpertaEloquentRepository::class,
        ];
        foreach ($puertos as $puerto => $concreto) {
            self::assertInstanceOf($concreto, $this->contenedor->make($puerto));
            self::assertSame($this->contenedor->make($puerto), $this->contenedor->make($concreto));
        }
        foreach ([MapeadorReglas::class, MapeadorRedSemantica::class, MapeadorEvidencias::class,
            MapeadorResultados::class, ResolutorFuenteExperta::class, ExtractorVariableMapeada::class] as $clase) {
            self::assertInstanceOf($clase, $this->contenedor->make($clase));
            self::assertSame($this->contenedor->make($clase), $this->contenedor->make($clase));
        }
        self::assertCount(14, $this->contenedor->getBindings());
    }

    /** El registro repetido conserva instancias y factories exactas; sin parámetros ni excepciones esperadas. */
    public function test_registro_repetido_no_reemplaza_ni_duplica_dependencias(): void
    {
        $vinculador = $this->vinculador();
        $vinculador->registrar($this->contenedor);
        $antes = $this->contenedor->getBindings();
        $repositorio = $this->contenedor->make(EvidenciaRepositoryInterface::class);
        $vinculador->registrar($this->contenedor);

        self::assertSame($antes, $this->contenedor->getBindings());
        self::assertSame($repositorio, $this->contenedor->make(EvidenciaRepositoryInterface::class));
    }

    /** Una colisión se detecta antes de registrar o ejecutar una factory ajena; sin parámetros ni errores sin capturar. */
    public function test_colision_no_ejecuta_una_factory_ni_deja_registros_parciales(): void
    {
        $this->contenedor->bind(EvaluacionExpertaRepositoryInterface::class, static function (): never {
            throw new RuntimeException('Esta factory ajena no debe ejecutarse.');
        });
        $antes = $this->contenedor->getBindings();
        try {
            $this->vinculador()->registrar($this->contenedor);
            self::fail('El registro debe rechazar la colisión.');
        } catch (LogicException $error) {
            self::assertStringContainsString(EvaluacionExpertaRepositoryInterface::class, $error->getMessage());
        }
        self::assertSame($antes, $this->contenedor->getBindings());
        self::assertFalse($this->contenedor->bound(ReglaExpertaRepositoryInterface::class));
    }

    /** Los aliases ajenos también se conservan; sin parámetros ni errores sin capturar. */
    public function test_no_sobrescribe_un_alias_preexistente(): void
    {
        $this->contenedor->alias('SIM-PUERTO-AJENO', EvidenciaRepositoryInterface::class);
        try {
            $this->vinculador()->registrar($this->contenedor);
            self::fail('No debe reemplazarse un alias ajeno.');
        } catch (LogicException $error) {
            self::assertStringContainsString(EvidenciaRepositoryInterface::class, $error->getMessage());
        }
        self::assertSame('SIM-PUERTO-AJENO', $this->contenedor->getAlias(EvidenciaRepositoryInterface::class));
        self::assertSame([], $this->contenedor->getBindings());
    }

    /** Una instancia ajena inyectada después del registro se detecta; sin parámetros ni errores sin capturar. */
    public function test_registro_repetido_detecta_una_instancia_sustituida(): void
    {
        $vinculador = $this->vinculador();
        $vinculador->registrar($this->contenedor);
        $ajena = new MapeadorRedSemantica;
        $this->contenedor->instance(MapeadorRedSemantica::class, $ajena);
        try {
            $vinculador->registrar($this->contenedor);
            self::fail('Debe detectarse la sustitución de instancia.');
        } catch (LogicException $error) {
            self::assertStringContainsString(MapeadorRedSemantica::class, $error->getMessage());
        }
        self::assertSame($ajena, $this->contenedor->make(MapeadorRedSemantica::class));
    }

    /** @throws RuntimeException Producción se rechaza antes de registrar servicios. */
    public function test_no_se_registra_en_entorno_productivo(): void
    {
        $this->contenedor->instance('env', 'production');
        $this->expectException(RuntimeException::class);
        try {
            $this->vinculador()->registrar($this->contenedor);
        } finally {
            self::assertSame([], $this->contenedor->getBindings());
        }
    }

    /** Una base física se rechaza inspeccionando configuración, sin abrir su PDO; sin parámetros ni errores sin capturar. */
    public function test_base_no_desechable_no_deja_bindings(): void
    {
        Model::setConnectionResolver(new ConnectionResolver(['orion_test' => self::conexionSinPdo('BASE_INSTITUCIONAL.sqlite')]));
        try {
            $this->vinculador()->registrar($this->contenedor);
            self::fail('Debe rechazarse un destino físico.');
        } catch (RuntimeException $error) {
            self::assertStringContainsString('desechable', $error->getMessage());
        }
        self::assertSame([], $this->contenedor->getBindings());
    }

    /** @throws DomainException No se autoriza el contexto como rol de soporte. */
    public function test_configuracion_invalida_falla_antes_de_registrar(): void
    {
        $this->expectException(DomainException::class);
        new VinculadorRepositorios('orion_test', new DateTimeZone('America/La_Paz'), 'RESULTADO', ['CONTEXTUAL'], [], static fn () => null);
    }

    /** Comprueba estrictos, inmutabilidad y PHPDoc exigido; sin parámetros ni errores de lectura esperados. */
    public function test_vinculador_esta_documentado_y_tipado(): void
    {
        $r = new ReflectionClass(VinculadorRepositorios::class);
        self::assertTrue($r->isFinal());
        self::assertTrue($r->isReadOnly());
        self::assertStringStartsWith("<?php\n\ndeclare(strict_types=1);", str_replace("\r\n", "\n", file_get_contents($r->getFileName())));
        foreach (['TÍTULO:', 'CAPA:', '¿PARA QUÉ SIRVE?', '¿QUÉ INFORMACIÓN RECIBE?',
            '¿QUÉ HACE?', '¿QUÉ DEVUELVE?', '¿QUÉ NO DEBE HACER?'] as $seccion) {
            self::assertStringContainsString($seccion, $r->getDocComment());
        }
        foreach ($r->getMethods() as $metodo) {
            self::assertNotFalse($metodo->getDocComment());
            foreach ($metodo->getParameters() as $parametro) {
                self::assertNotNull($parametro->getType());
                self::assertStringContainsString('$'.$parametro->getName(), $metodo->getDocComment());
            }
        }
    }

    /** @return VinculadorRepositorios Configuración explícita sin memoria inventada ni consultas; sin parámetros ni errores esperados. */
    private function vinculador(): VinculadorRepositorios
    {
        return new VinculadorRepositorios('orion_test', new DateTimeZone('America/La_Paz'), 'RESULTADO', [],
            ['COG_MEM_CONTROL' => new AdaptadorControlesCognitivos], static function (): never {
                throw new RuntimeException('La memoria original no está disponible en este escenario unitario.');
            });
    }

    /**
     * Construye metadatos reales de conexión con apertura PDO prohibida.
     *
     * @param  string  $database  Nombre de destino que se somete a la guarda.
     * @return SQLiteConnection Conexión sin abrir; falla inmediatamente si se intenta consultar.
     */
    private static function conexionSinPdo(string $database): SQLiteConnection
    {
        return new SQLiteConnection(static function (): never {
            throw new RuntimeException('Una prueba unitaria de bindings no debe abrir PDO.');
        }, $database, '', ['driver' => 'sqlite', 'database' => $database]);
    }
}
