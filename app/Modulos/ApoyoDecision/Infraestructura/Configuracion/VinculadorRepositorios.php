<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Configuracion;

use MotorExperto\dominio\orion\Entities\Hechos\MemoriaTrabajo;
use MotorExperto\dominio\orion\Repositories\Conocimiento\RedSemanticaRepositoryInterface;
use MotorExperto\dominio\orion\Repositories\Conocimiento\ReglaExpertaRepositoryInterface;
use MotorExperto\dominio\orion\Repositories\Evaluaciones\EvaluacionExpertaRepositoryInterface;
use MotorExperto\dominio\orion\Repositories\Hechos\EvidenciaRepositoryInterface;
use App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\FuentesOperativas\AdaptadorControlesCognitivos;
use App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\FuentesOperativas\AdaptadorIadlExperimental;
use App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\FuentesOperativas\AdaptadorInstrumentos;
use App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\FuentesOperativas\AdaptadorSeguimientosPedagogicos;
use App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\FuentesOperativas\AdaptadorValoracionesFuncionales;
use App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\MapeoFuentes\ExtractorVariableMapeada;
use App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\MapeoFuentes\ResolutorFuenteExperta;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Mapeadores\MapeadorEvidencias;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Mapeadores\MapeadorRedSemantica;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Mapeadores\MapeadorReglas;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Mapeadores\MapeadorResultados;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Repositorios\EvaluacionExpertaEloquentRepository;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Repositorios\EvidenciaEloquentRepository;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Repositorios\RedSemanticaEloquentRepository;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Repositorios\ReglaExpertaEloquentRepository;
use Closure;
use DateTimeImmutable;
use DateTimeZone;
use DomainException;
use Illuminate\Container\Container;
use LogicException;
use RuntimeException;

/**
 * TÍTULO:
 * Configuración de dependencias del sistema experto.
 *
 * CAPA:
 * Infrastructure - Configuracion.
 *
 * ¿PARA QUÉ SIRVE?
 * Enlaza los cuatro puertos de Domain con las implementaciones Eloquent de la PoC.
 *
 * ¿QUÉ INFORMACIÓN RECIBE?
 * Conexión experimental, zona SQL, tipo de regla, roles de soporte, registro
 * explícito de adaptadores y recuperador de la memoria original del caso.
 *
 * ¿QUÉ HACE?
 * Construye el grafo compatible y registra catorce bindings compartidos en Laravel.
 * Valida testing y el destino desechable antes de alterar el contenedor; detecta
 * colisiones antes de registrar y conserva los contratos propios de ApoyoDecision.
 *
 * ¿QUÉ DEVUELVE?
 * Un contenedor capaz de resolver interfaces, repositorios, mappers y extracción.
 * Repetir el registro con esta misma instancia no sustituye dependencias.
 *
 * ¿QUÉ NO DEBE HACER?
 * No registra providers de arranque, consulta tablas, ejecuta inferencia ni habilita
 * atención clínica. No reconstruye una memoria desde datos incompletos ni guarda
 * resultados entre casos. Las instantáneas V2 se recuperan desde SQL; el Closure
 * original es opcional y se utiliza únicamente para evaluaciones anteriores.
 */
final readonly class VinculadorRepositorios
{
    /** @var array<class-string, object> Servicios sin estado de evaluación; las interfaces comparten la instancia concreta. */
    private array $servicios;

    /** @var array<class-string, Closure(): object> Identidades propias para detectar registro repetido y conflictos sin ejecutar factories ajenas. */
    private array $fabricas;

    /**
     * Prepara dependencias sin abrir PDO, consultar BDD ni registrar servicios globales.
     *
     * @param  string  $conexionExperimental  Nombre explícito de una conexión técnica segregada.
     * @param  DateTimeZone  $zonaAlmacenamiento  Zona única para repositorio, extractor y mappers.
     * @param  string  $tipoReglaResultado  Tipo literal del contrato versionado, sin default clínico.
     * @param  list<string>  $rolesSoporte  Roles explícitos; vacío no autoriza soportes.
     * @param  array<string, AdaptadorControlesCognitivos|AdaptadorSeguimientosPedagogicos|AdaptadorValoracionesFuncionales|AdaptadorInstrumentos|AdaptadorIadlExperimental>  $adaptadores  Clave contractual a instancia conocida; los selectores instrumentales se configuran en su instancia.
     * @param  Closure(string, string, string, DateTimeImmutable): MemoriaTrabajo  $recuperarMemoria  Instantánea original por evaluación, residente, versión y corte; no se almacena ni inventa aquí.
     *
     * @throws DomainException Por conexión, tipo, rol o registro de adaptadores inválidos.
     */
    public function __construct(
        /** Destino explícito de todos los repositorios. */ private string $conexionExperimental,
        DateTimeZone $zonaAlmacenamiento,
        string $tipoReglaResultado,
        array $rolesSoporte,
        array $adaptadores,
        ?Closure $recuperarMemoria = null,
    ) {
        $reglas = new MapeadorReglas($tipoReglaResultado);
        $red = new MapeadorRedSemantica;
        $evidencias = new MapeadorEvidencias($zonaAlmacenamiento);
        $resultados = new MapeadorResultados($zonaAlmacenamiento, $rolesSoporte);
        $fuentes = new ResolutorFuenteExperta($conexionExperimental, $adaptadores);
        $extraer = new ExtractorVariableMapeada($fuentes, $zonaAlmacenamiento);
        $repositorioReglas = new ReglaExpertaEloquentRepository($reglas, $conexionExperimental);
        $repositorioRed = new RedSemanticaEloquentRepository($red, $conexionExperimental);
        $repositorioEvidencias = new EvidenciaEloquentRepository($evidencias, Closure::fromCallable($extraer), $zonaAlmacenamiento, $conexionExperimental);
        $repositorioEvaluaciones = new EvaluacionExpertaEloquentRepository($resultados, $recuperarMemoria, $conexionExperimental, Closure::fromCallable($extraer), $zonaAlmacenamiento);
        $this->servicios = [
            MapeadorReglas::class => $reglas,
            MapeadorRedSemantica::class => $red,
            MapeadorEvidencias::class => $evidencias,
            MapeadorResultados::class => $resultados,
            ResolutorFuenteExperta::class => $fuentes,
            ExtractorVariableMapeada::class => $extraer,
            ReglaExpertaEloquentRepository::class => $repositorioReglas,
            RedSemanticaEloquentRepository::class => $repositorioRed,
            EvidenciaEloquentRepository::class => $repositorioEvidencias,
            EvaluacionExpertaEloquentRepository::class => $repositorioEvaluaciones,
            ReglaExpertaRepositoryInterface::class => $repositorioReglas,
            RedSemanticaRepositoryInterface::class => $repositorioRed,
            EvidenciaRepositoryInterface::class => $repositorioEvidencias,
            EvaluacionExpertaRepositoryInterface::class => $repositorioEvaluaciones,
        ];
        $fabricas = [];
        foreach ($this->servicios as $tipo => $servicio) {
            $fabricas[$tipo] = static fn (): object => $servicio;
        }
        $this->fabricas = $fabricas;
    }

    /**
     * Registra el grafo exclusivamente en el contenedor experimental solicitado.
     *
     * @param  Container  $contenedor  Contenedor Laravel en testing; no se sustituye el global.
     * @return void Conserva instancias y bindings ajenos; no ejecuta consultas SQL.
     *
     * @throws RuntimeException Por entorno o conexión no desechables.
     * @throws LogicException Por binding, alias o instancia preexistentes de otra configuración.
     */
    public function registrar(Container $contenedor): void
    {
        if (! $contenedor->bound('env') || $contenedor->make('env') !== 'testing') {
            throw new RuntimeException('El vinculador experimental solo se registra en testing.');
        }
        // Esta guarda inspecciona el destino configurado sin abrir su PDO. Los
        // repositorios y adaptadores vuelven a comprobarlo en cada lectura real.
        ExtractorVariableMapeada::conexionSegura($this->conexionExperimental);
        $existentes = $contenedor->getBindings();
        foreach ($this->fabricas as $tipo => $fabrica) {
            if ($contenedor->bound($tipo) || $contenedor->resolved($tipo)) {
                if ($contenedor->isAlias($tipo) || ($existentes[$tipo]['concrete'] ?? null) !== $fabrica
                    || ($existentes[$tipo]['shared'] ?? false) !== true) {
                    throw new LogicException('Ya existe una dependencia de otra configuración: '.$tipo.'.');
                }
            }
        }
        // Solo se inspeccionan instancias propias después de descartar todas las
        // factories ajenas. Una colisión nunca ejecuta su código ni lo sobrescribe.
        foreach ($this->servicios as $tipo => $servicio) {
            if ($contenedor->resolved($tipo) && $contenedor->make($tipo) !== $servicio) {
                throw new LogicException('La instancia registrada fue sustituida: '.$tipo.'.');
            }
        }
        foreach ($this->fabricas as $tipo => $fabrica) {
            if (! $contenedor->bound($tipo)) {
                $contenedor->singleton($tipo, $fabrica);
            }
        }
    }
}
