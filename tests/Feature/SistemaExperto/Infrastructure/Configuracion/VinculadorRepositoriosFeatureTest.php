<?php

declare(strict_types=1);

namespace Tests\Feature\SistemaExperto\Infrastructure\Configuracion;

use App\Modulos\ApoyoDecision\Aplicacion\Contratos\ConocimientoVersionado;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\ExtraccionFuentesCOGMEM;
use App\Modulos\ApoyoDecision\Dominio\Puertos\FuenteConocimientoOrion;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\SolicitudInferenciaDTO;
use App\Modulos\ApoyoDecision\Aplicacion\CasosDeUso\ExtraerHechosUseCase;
use MotorExperto\dominio\orion\Entities\Hechos\MemoriaTrabajo;
use MotorExperto\dominio\orion\Repositories\Conocimiento\RedSemanticaRepositoryInterface;
use MotorExperto\dominio\orion\Repositories\Conocimiento\ReglaExpertaRepositoryInterface;
use MotorExperto\dominio\orion\Repositories\Evaluaciones\EvaluacionExpertaRepositoryInterface;
use MotorExperto\dominio\orion\Repositories\Hechos\EvidenciaRepositoryInterface;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\EstadoDominioEnum;
use App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\FuentesOperativas\AdaptadorControlesCognitivos;
use App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\FuentesOperativas\AdaptadorInstrumentos;
use App\Modulos\ApoyoDecision\Infraestructura\Configuracion\VinculadorRepositorios;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Repositorios\EvaluacionExpertaEloquentRepository;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Repositorios\EvidenciaEloquentRepository;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Repositorios\RedSemanticaEloquentRepository;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Repositorios\ReglaExpertaEloquentRepository;
use Closure;
use DateTimeZone;
use DomainException;
use Illuminate\Support\Facades\DB;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Feature\SistemaExperto\Infrastructure\Adapters\AdaptadoresTestCase;
use Tests\Feature\SistemaExperto\Infrastructure\Repositories\EscenarioRepositorios;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades;
use Tests\Unit\SistemaExperto\Infrastructure\Mappers\EscenarioMapeadores as F;

/**
 * TÍTULO:
 * Resolución de dependencias y lectura Eloquent en Laravel.
 *
 * CAPA:
 * Infrastructure - Configuracion (pruebas de integración).
 *
 * ¿PARA QUÉ SIRVE?
 * Prueba el vinculador dentro de la aplicación y los puertos reales de Domain.
 *
 * ¿QUÉ INFORMACIÓN RECIBE?
 * Fixtures sintéticas y el destino que Tests\TestCase verifica como desechable.
 *
 * ¿QUÉ HACE?
 * Recorre contenedor, repositorios, extracción, mappers y CU-01 sin mocks del núcleo.
 * Comprueba ApoyoDecision, configuración instrumental y ausencia de escrituras.
 *
 * ¿QUÉ DEVUELVE?
 * Entidades y DTO reales, con contexto y datos NULL conservados.
 *
 * ¿QUÉ NO DEBE HACER?
 * No conecta una base de producción, registra providers ni activa inferencia clínica.
 */
final class VinculadorRepositoriosFeatureTest extends AdaptadoresTestCase
{
    /** El registro explícito no consulta ni altera bindings previos; sin parámetros ni excepciones esperadas. */
    public function test_resuelve_los_cuatro_puertos_sin_duplicar_apoyo_decision(): void
    {
        $previos = $this->app->getBindings();
        foreach ([ReglaExpertaRepositoryInterface::class, RedSemanticaRepositoryInterface::class,
            EvidenciaRepositoryInterface::class, EvaluacionExpertaRepositoryInterface::class] as $puerto) {
            self::assertFalse($this->app->bound($puerto), 'El provider de arranque no debe activar el módulo.');
        }
        DB::connection()->enableQueryLog();
        DB::connection()->flushQueryLog();
        $vinculador = $this->vinculador();
        $vinculador->registrar($this->app);
        foreach ([
            ReglaExpertaRepositoryInterface::class => ReglaExpertaEloquentRepository::class,
            RedSemanticaRepositoryInterface::class => RedSemanticaEloquentRepository::class,
            EvidenciaRepositoryInterface::class => EvidenciaEloquentRepository::class,
            EvaluacionExpertaRepositoryInterface::class => EvaluacionExpertaEloquentRepository::class,
        ] as $puerto => $concreto) {
            self::assertInstanceOf($concreto, $this->app->make($puerto));
            self::assertSame($this->app->make($puerto), $this->app->make($concreto));
        }
        $vinculador->registrar($this->app);
        self::assertSame([], DB::connection()->getQueryLog());
        foreach ([ConocimientoVersionado::class, ExtraccionFuentesCOGMEM::class, FuenteConocimientoOrion::class] as $contrato) {
            self::assertArrayHasKey($contrato, $previos);
            self::assertSame($previos[$contrato], $this->app->getBindings()[$contrato]);
        }
        self::assertFalse(config('sistema_experto.lectura_tecnica_pruebas.habilitada'));
    }

    /** Los puertos resueltos ejecutan consultas y mappers reales; sin parámetros ni excepciones esperadas. */
    public function test_puertos_del_contenedor_recuperan_reglas_y_red_de_la_version_exacta(): void
    {
        EscenarioRepositorios::guardarGrafo(F::regla());
        EscenarioRepositorios::guardarGrafo(F::red());
        $this->vinculador()->registrar($this->app);
        DB::connection()->enableQueryLog();
        DB::connection()->flushQueryLog();

        $reglas = $this->app->make(ReglaExpertaRepositoryInterface::class)->porVersion('SIM-V1');
        $red = $this->app->make(RedSemanticaRepositoryInterface::class)->porVersion('SIM-V1');

        self::assertCount(1, $reglas);
        self::assertSame('SIM-R', $reglas[0]->id);
        self::assertSame('SIM-V1', $red->versionConocimiento);
        self::assertCount(1, $red->relaciones);
        self::assertNull($this->app->make(ReglaExpertaRepositoryInterface::class)->obtener('SIM-R', 'SIM-OTRA'));
        $this->exigirSoloLecturas();
    }

    /**
     * El contenedor conecta CU-01 con extracción y Domain reales.
     *
     * @param  bool|null  $valor  Booleano físico o ausencia explícita.
     * @return void Sin excepciones esperadas.
     */
    #[DataProvider('valoresOriginales')]
    public function test_caso_de_uso_resuelto_conserva_null_y_false_hasta_domain(?bool $valor): void
    {
        $this->prepararControl(['repite_preguntas' => $valor]);
        DB::table('mapeos_variables_fuente')->update(['campo_valor' => 'repite_preguntas']);
        $this->vinculador()->registrar($this->app);
        DB::connection()->enableQueryLog();
        DB::connection()->flushQueryLog();
        $solicitud = new SolicitudInferenciaDTO('SIM-SOL', 'SIM-EVAL', 'SINTETICO-RES', self::corte(), 'SIM-V1');

        $salida = $this->app->make(ExtraerHechosUseCase::class)->ejecutar($solicitud);

        self::assertSame($valor, $salida->hechos['SIM-EVID']->valor);
        self::assertSame('SIM-CTRL', $salida->referenciasProcedencia['SIM-EVID']['origenId']);
        self::assertSame('SIM-AT', $salida->referenciasProcedencia['SIM-EVID']['episodioId']);
        self::assertSame($solicitud, $salida->solicitud);
        $this->exigirSoloLecturas();
    }

    /** @return iterable<string, array{bool|null}> Dato faltante y booleano conocido; sin parámetros ni excepciones. */
    public static function valoresOriginales(): iterable
    {
        yield 'NULL' => [null];
        yield 'false' => [false];
    }

    /** Conserva selectores instrumentales configurados, sin sustituirlos por un adaptador vacío; sin parámetros ni excepciones esperadas. */
    public function test_binding_conserva_el_selector_instrumental_explicito(): void
    {
        $this->prepararInstrumento(['valor_logico' => false]);
        $instrumentos = $this->selector('valor_logico');
        $this->vinculador(instrumentos: $instrumentos)->registrar($this->app);

        $memoria = $this->app->make(EvidenciaRepositoryInterface::class)
            ->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());

        self::assertFalse($memoria->hechos['SIM-EVID']->valor);
        self::assertSame('SIM-INST', $memoria->evidencias['SIM-EVID']->instrumentoId);
    }

    /** El proveedor original se invoca por contexto, sin fabricar memoria desde las tablas; sin parámetros ni errores sin capturar. */
    public function test_evaluaciones_resueltas_conservan_memoria_y_rechazan_otro_corte(): void
    {
        [, $memoria] = EscenarioRepositorios::resultadosConSoporte();
        $consultasMemoria = [];
        $recuperar = static function (string $evaluacion, string $residente, string $version, \DateTimeImmutable $corte) use ($memoria, &$consultasMemoria): MemoriaTrabajo {
            $consultasMemoria[] = [$evaluacion, $residente, $version, $corte];

            return $memoria;
        };
        $this->vinculador($recuperar)->registrar($this->app);
        $repositorio = $this->app->make(EvaluacionExpertaRepositoryInterface::class);
        $vector = $repositorio->obtener('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());

        self::assertSame($memoria, $vector->memoria);
        self::assertSame(EstadoDominioEnum::NO_INTERPRETABLE, $vector->resultados['COG-MEM']->estado);
        self::assertSame(['SIM-EVAL', 'SINTETICO-RES', 'SIM-V1'], array_slice($consultasMemoria[0], 0, 3));
        self::assertEquals(self::corte(), $consultasMemoria[0][3]);
        try {
            $repositorio->obtener('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte()->modify('+1 second'));
            self::fail('El repositorio debe rechazar una memoria de otro corte.');
        } catch (DomainException $error) {
            self::assertStringContainsString('corte', $error->getMessage());
        }
        self::assertCount(2, $consultasMemoria);
        self::assertSame($repositorio, $this->app->make(EvaluacionExpertaRepositoryInterface::class));
    }

    /** Las restricciones de escritura del puerto permanecen activas tras vincular; sin parámetros ni errores sin capturar. */
    public function test_binding_no_crea_catalogos_de_conveniencia_para_guardar(): void
    {
        $perfil = EscenarioEntidades::perfil(['U', 'U', 'U', 'U', 'U']);
        $this->vinculador()->registrar($this->app);
        DB::connection()->enableQueryLog();
        DB::connection()->flushQueryLog();
        try {
            $this->app->make(EvaluacionExpertaRepositoryInterface::class)->guardar($perfil->cognicion);
            self::fail('El binding no debe habilitar escrituras sin representación física.');
        } catch (DomainException $error) {
            self::assertStringContainsString('DOMINIO:COG-MEM=U', $error->getMessage());
        }
        $this->exigirSoloLecturas();
        self::assertSame(0, DB::table('evaluaciones_expertas')->count());
    }

    /** Rechaza un destino SQLite físico sin crearlo ni abrirlo; sin parámetros ni errores sin capturar. */
    public function test_conexion_no_desechable_no_abre_archivo_ni_registra_servicios(): void
    {
        $ruta = sys_get_temp_dir().'/orion-vinculador-rechazada-'.bin2hex(random_bytes(8)).'.sqlite';
        config(['database.connections.orion_insegura' => ['driver' => 'sqlite', 'database' => $ruta, 'prefix' => '']]);
        $vinculador = new VinculadorRepositorios('orion_insegura', new DateTimeZone('America/La_Paz'), 'RESULTADO', [], [], static fn () => null);
        $antes = $this->app->getBindings();
        try {
            $vinculador->registrar($this->app);
            self::fail('No debe registrarse una base física.');
        } catch (RuntimeException $error) {
            self::assertStringContainsString('desechable', $error->getMessage());
        }
        self::assertFileDoesNotExist($ruta);
        self::assertSame($antes, $this->app->getBindings());
    }

    /** Un binding previo se respeta sin registro parcial; sin parámetros ni errores sin capturar. */
    public function test_contenedor_real_rechaza_una_colision_sin_reemplazarla(): void
    {
        $this->app->bind(EvaluacionExpertaRepositoryInterface::class, static function (): never {
            throw new RuntimeException('La factory ajena no debe ejecutarse.');
        });
        $antes = $this->app->getBindings();
        try {
            $this->vinculador()->registrar($this->app);
            self::fail('Debe informarse la colisión del puerto existente.');
        } catch (LogicException $error) {
            self::assertStringContainsString(EvaluacionExpertaRepositoryInterface::class, $error->getMessage());
        }
        self::assertSame($antes, $this->app->getBindings());
        self::assertFalse($this->app->bound(EvidenciaRepositoryInterface::class));
    }

    /**
     * Construye la configuración explícita del escenario sin elegir reglas clínicas.
     *
     * @param  Closure|null  $memoria  Recuperación de la instantánea original; ausente significa indisponible, nunca inventada.
     * @param  AdaptadorInstrumentos|null  $instrumentos  Selectores instrumentales ficticios.
     * @return VinculadorRepositorios Grafo real para el perfil seguro actual.
     */
    private function vinculador(?Closure $memoria = null, ?AdaptadorInstrumentos $instrumentos = null): VinculadorRepositorios
    {
        return new VinculadorRepositorios($this->conexionPruebas(), new DateTimeZone('America/La_Paz'), 'RESULTADO', ['SIM-SOPORTE'], [
            'COG_MEM_CONTROL' => new AdaptadorControlesCognitivos,
            'SIM-INSTR' => $instrumentos ?? new AdaptadorInstrumentos,
        ], $memoria ?? static function (): never {
            throw new RuntimeException('La memoria original no está disponible en este escenario.');
        });
    }
}
