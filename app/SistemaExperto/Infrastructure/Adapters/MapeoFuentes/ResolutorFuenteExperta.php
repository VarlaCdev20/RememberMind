<?php

declare(strict_types=1);

namespace App\SistemaExperto\Infrastructure\Adapters\MapeoFuentes;

use App\Models\FuenteDatoExperta;
use App\SistemaExperto\Infrastructure\Adapters\FuentesOperativas\AdaptadorControlesCognitivos;
use App\SistemaExperto\Infrastructure\Adapters\FuentesOperativas\AdaptadorIadlExperimental;
use App\SistemaExperto\Infrastructure\Adapters\FuentesOperativas\AdaptadorInstrumentos;
use App\SistemaExperto\Infrastructure\Adapters\FuentesOperativas\AdaptadorSeguimientosPedagogicos;
use App\SistemaExperto\Infrastructure\Adapters\FuentesOperativas\AdaptadorValoracionesFuncionales;
use DomainException;
use RuntimeException;

/**
 * TÍTULO:
 * Resolución de fuentes autorizadas.
 *
 * CAPA:
 * Infrastructure - Adapters.
 *
 * ¿PARA QUÉ SIRVE?
 * Recupera datos brutos autorizados sin interpretación clínica.
 *
 * ¿QUÉ INFORMACIÓN RECIBE?
 * Conexión segregada y registro explícito clave_adaptador → instancia conocida.
 *
 * ¿QUÉ HACE?
 * Consulta la tabla 07 por versión exacta y valida tabla, PK, campos y estado.
 *
 * ¿QUÉ DEVUELVE?
 * La fuente física original y su adaptador registrado; ausencia como error explícito.
 *
 * ¿QUÉ NO DEBE HACER?
 * No interpreta tabla_raiz como permiso SQL ni instancia clases desde cadenas de la BDD.
 */
final readonly class ResolutorFuenteExperta
{
    /** @var array<string, AdaptadorControlesCognitivos|AdaptadorSeguimientosPedagogicos|AdaptadorValoracionesFuncionales|AdaptadorInstrumentos|AdaptadorIadlExperimental> Registro copiado, sin referencias mutables externas. */
    private array $adaptadores;

    /**
     * Registra únicamente instancias finales conocidas, sin inventar claves de catálogo.
     *
     * @param  string  $conexionExperimental  Nombre del destino técnico.
     * @param  array<string, AdaptadorControlesCognitivos|AdaptadorSeguimientosPedagogicos|AdaptadorValoracionesFuncionales|AdaptadorInstrumentos|AdaptadorIadlExperimental>  $adaptadores  Claves literales del contrato versionado; no se registran bindings globales.
     *
     * @throws DomainException Por clave o instancia ajenas al registro soportado.
     */
    public function __construct(
        /** Destino explícito; jamás usa la base institucional por defecto. */ private string $conexionExperimental,
        array $adaptadores,
    ) {
        ExtractorVariableMapeada::validarIds($conexionExperimental);
        $registro = [];
        foreach ($adaptadores as $clave => $adaptador) {
            if (! is_string($clave)) {
                throw new DomainException('La clave del adaptador debe ser textual y explícita.');
            }
            ExtractorVariableMapeada::validarIds($clave);
            if (! $adaptador instanceof AdaptadorControlesCognitivos && ! $adaptador instanceof AdaptadorSeguimientosPedagogicos
                && ! $adaptador instanceof AdaptadorValoracionesFuncionales && ! $adaptador instanceof AdaptadorInstrumentos
                && ! $adaptador instanceof AdaptadorIadlExperimental) {
                throw new DomainException('Adaptador no soportado por el registro explícito.');
            }
            $registro[$clave] = $adaptador;
        }
        $this->adaptadores = $registro;
    }

    /**
     * Comprueba el destino antes de consultar contratos o fuentes.
     *
     * @return string Nombre de conexión segregada.
     *
     * @throws RuntimeException Por entorno o base no autorizados.
     */
    public function conexion(): string
    {
        ExtractorVariableMapeada::conexionSegura($this->conexionExperimental);

        return $this->conexionExperimental;
    }

    /**
     * Resuelve un código de fuente en una versión, sin fallback.
     *
     * @param  string  $versionConocimiento  Versión propietaria exacta.
     * @param  string  $codigoFuente  Código semántico de fuente exacto.
     * @return array{fuente: FuenteDatoExperta, adaptador: object} Fila original y adaptador conocido.
     *
     * @throws DomainException Por metadatos incoherentes o estado inactivo.
     * @throws RuntimeException Por fuente inexistente, no registrada o no disponible.
     */
    public function resolver(string $versionConocimiento, string $codigoFuente): array
    {
        ExtractorVariableMapeada::validarIds($versionConocimiento, $codigoFuente);
        $filas = FuenteDatoExperta::on($this->conexion())->where('cod_version_modelo', $versionConocimiento)
            ->where('codigo_fuente', $codigoFuente)->get();
        if ($filas->count() !== 1) {
            throw new RuntimeException('La fuente solicitada no está disponible en la versión exacta.');
        }

        return $this->validar($filas->first(), $versionConocimiento);
    }

    /**
     * Resuelve un lote de FK sin una consulta por evidencia.
     *
     * @param  list<string>  $ids  PK de fuentes originales.
     * @param  string  $versionConocimiento  Versión única de la evaluación.
     * @return array<string, array{fuente: FuenteDatoExperta, adaptador: object}> Fuentes por PK.
     *
     * @throws DomainException Por metadatos incompatibles.
     * @throws RuntimeException Por una fuente ausente, no registrada o no habilitada.
     */
    public function porIds(array $ids, string $versionConocimiento): array
    {
        if (! array_is_list($ids)) {
            throw new DomainException('La resolución por lote requiere una lista de PK de fuentes.');
        }
        ExtractorVariableMapeada::validarIds($versionConocimiento, ...$ids);
        $ids = array_values(array_unique($ids));
        $filas = FuenteDatoExperta::on($this->conexion())->where('cod_version_modelo', $versionConocimiento)
            ->whereIn('cod_fuente_dato_experta', $ids)->orderBy('cod_fuente_dato_experta')->get();
        if ($filas->count() !== count($ids)) {
            throw new RuntimeException('Alguna fuente no existe en la versión solicitada.');
        }
        $salida = [];
        foreach ($filas as $fila) {
            $salida[$fila->getRawOriginal('cod_fuente_dato_experta')] = $this->validar($fila, $versionConocimiento);
        }

        return $salida;
    }

    /**
     * Contrasta metadatos con constantes del código, nunca con SQL configurable.
     *
     * @param  FuenteDatoExperta  $fuente  Fuente recuperada del destino segregado.
     * @param  string  $version  Versión exacta.
     * @return array{fuente: FuenteDatoExperta, adaptador: object} Dependencias originales.
     *
     * @throws DomainException Por versión, estado, tabla o campos incompatibles.
     * @throws RuntimeException Por clave desconocida o IADL no disponible.
     */
    private function validar(FuenteDatoExperta $fuente, string $version): array
    {
        $datos = $fuente->getAttributes();
        if ($datos['cod_version_modelo'] !== $version || $datos['estado'] !== 'ACTIVO'
            || ! is_string($datos['codigo_fuente'] ?? null) || $datos['codigo_fuente'] === '') {
            throw new DomainException('La fuente no está activa o no corresponde a la versión solicitada.');
        }
        $adaptador = $this->adaptadores[$datos['clave_adaptador'] ?? ''] ?? null;
        if ($adaptador === null) {
            throw new RuntimeException('La clave de adaptador no tiene una implementación registrada y autorizada.');
        }
        if ($adaptador instanceof AdaptadorIadlExperimental) {
            if ($datos['tabla_raiz'] !== 'controles_funcionales_iadl') {
                throw new DomainException('El adaptador IADL no corresponde a la tabla declarada.');
            }
            $disponibilidad = $adaptador->disponibilidad($this->conexionExperimental);
            if (! $disponibilidad['disponible']) {
                throw new RuntimeException($disponibilidad['motivo']);
            }
        }
        if ($datos['tabla_raiz'] !== $adaptador::TABLA || $datos['campo_pk_raiz'] !== $adaptador::PK
            || ($datos['campo_residente'] ?? null) !== 'cod_residente' || ($datos['campo_temporal'] ?? null) !== 'fecha_hora'
            || ($datos['campo_personal'] ?? null) !== 'cod_personal' || ($datos['campo_estado'] ?? null) !== 'estado') {
            throw new DomainException('La fuente no coincide con los campos físicos del adaptador registrado.');
        }

        return ['fuente' => $fuente, 'adaptador' => $adaptador];
    }
}
