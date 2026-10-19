<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\MapeoFuentes;

use App\Models\EvaluacionExperta;
use App\Models\EvidenciaEvaluacion;
use App\Models\MapeoVariableFuente;
use App\Models\NodoSemantico;
use App\Models\VariableExperta;
use App\Modulos\ApoyoDecision\Dominio\Reglas\BaseTecnicaDesechable;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\DominioCognitivoEnum;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\SubcomponenteCognitivoEnum;
use App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\FuentesOperativas\AdaptadorControlesCognitivos;
use App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\FuentesOperativas\AdaptadorIadlExperimental;
use App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\FuentesOperativas\AdaptadorInstrumentos;
use App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\FuentesOperativas\AdaptadorSeguimientosPedagogicos;
use App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\FuentesOperativas\AdaptadorValoracionesFuncionales;
use DateTimeImmutable;
use DateTimeZone;
use DomainException;
use Illuminate\Container\Container;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

/**
 * TÍTULO:
 * Extracción de variables conforme al modelo experto.
 *
 * CAPA:
 * Infrastructure - Adapters.
 *
 * ¿PARA QUÉ SIRVE?
 * Recupera datos brutos autorizados sin interpretación clínica.
 *
 * ¿QUÉ INFORMACIÓN RECIBE?
 * Evidencias del repositorio, corte explícito, resolución de fuentes y zona SQL.
 *
 * ¿QUÉ HACE?
 * Consulta el contrato 08 por lote, comprueba FK/versiones y delega en adaptadores conocidos.
 *
 * ¿QUÉ DEVUELVE?
 * El contrato de extracción del repositorio: origen original, valor escalar nullable y destino técnico.
 *
 * ¿QUÉ NO DEBE HACER?
 * No ejecuta SQL desde campos configurables, crea defaults, interpreta instrumentos ni consulta personas reales.
 */
final readonly class ExtractorVariableMapeada
{
    /**
     * Recibe resolución y zona horaria sin registrar bindings ni ejecutar consultas.
     *
     * @param  ResolutorFuenteExperta  $fuentes  Registro explícito y conexión segregada.
     * @param  DateTimeZone  $zonaAlmacenamiento  Zona de fechas clínicas SQL.
     */
    public function __construct(
        /** Fuente/implementación de la versión exacta. */ private ResolutorFuenteExperta $fuentes,
        /** Zona explícita, nunca inferida del reloj global. */ private DateTimeZone $zonaAlmacenamiento,
    ) {}

    /**
     * Proporciona directamente el callable requerido por EvidenciaEloquentRepository.
     *
     * @param  Collection<int, EvidenciaEvaluacion>  $filas  Evidencias existentes de una sola evaluación.
     * @param  DateTimeImmutable  $corte  Corte original inclusivo.
     * @param  string  $conexion  Nombre exacto esperado por el repositorio.
     * @return array<string, array{origen: Model, valor: string|int|float|bool|null, tipo: 'HECHO'|'EVIDENCIA_COGNITIVA'}> Lote por PK de evidencia; las filas posteriores al corte solo llevan metadatos para que el repositorio las excluya.
     *
     * @throws DomainException Por mezcla de casos/versiones, selector o tipo incompatible.
     * @throws RuntimeException Por fuente, mapeo u origen no disponibles o base no autorizada.
     */
    public function __invoke(Collection $filas, DateTimeImmutable $corte, string $conexion): array
    {
        self::validarIds($conexion);
        if ($conexion !== $this->fuentes->conexion()) {
            throw new DomainException('La extracción requiere la misma conexión explícita que el repositorio.');
        }
        if ($filas->isEmpty()) {
            return [];
        }
        $evaluacionId = null;
        $ids = $mapeosIds = [];
        foreach ($filas as $fila) {
            if (! $fila instanceof EvidenciaEvaluacion || ! $fila->exists || $fila->getConnectionName() !== $conexion) {
                throw new DomainException('La extracción recibe evidencias existentes de la misma conexión.');
            }
            $datos = $fila->getAttributes();
            self::validarIds($datos['cod_evidencia_evaluacion'], $datos['cod_evaluacion_experta'],
                $datos['cod_mapeo_variable_fuente'], $datos['cod_registro_fuente']);
            $evaluacionId ??= $datos['cod_evaluacion_experta'];
            if ($evaluacionId !== $datos['cod_evaluacion_experta'] || isset($ids[$datos['cod_evidencia_evaluacion']])
                || ! in_array($datos['estado_admisibilidad'], ['ADMISIBLE', 'ADMISIBLE_CON_ADVERTENCIA'], true)) {
                throw new DomainException('No se mezclan evaluaciones, duplicados o evidencias no admitidas.');
            }
            $ids[$datos['cod_evidencia_evaluacion']] = true;
            $mapeosIds[] = $datos['cod_mapeo_variable_fuente'];
        }
        $evaluacion = EvaluacionExperta::on($conexion)->select(['cod_evaluacion_experta', 'cod_residente', 'cod_version_modelo', 'origen_activacion'])
            ->whereKey($evaluacionId)->first() ?? throw new RuntimeException('La evaluación original no está disponible.');
        self::contextoSintetico($evaluacion);
        $version = $evaluacion->getRawOriginal('cod_version_modelo');
        $mapeos = MapeoVariableFuente::on($conexion)->whereIn('cod_mapeo_variable_fuente', array_unique($mapeosIds))
            ->where('cod_version_modelo', $version)->with(['variable.nodo', 'variable.propietarioPrimario'])->get()->keyBy('cod_mapeo_variable_fuente');
        if ($mapeos->count() !== count(array_unique($mapeosIds))) {
            throw new RuntimeException('Un mapeo no está disponible en la versión del caso.');
        }
        $resueltas = $this->fuentes->porIds($mapeos->pluck('cod_fuente_dato_experta')->unique()->values()->all(), $version);
        $grupos = [];
        foreach ($filas as $fila) {
            $mapeo = $mapeos[$fila->getRawOriginal('cod_mapeo_variable_fuente')];
            self::validarMapeo($mapeo, $version);
            $resuelta = $resueltas[$mapeo->getRawOriginal('cod_fuente_dato_experta')];
            $mapeo->setRelation('fuente', $resuelta['fuente']);
            $adaptador = $resuelta['adaptador'];
            $adaptador->validarMapeo($mapeo);
            $clave = spl_object_id($adaptador);
            $grupos[$clave]['adaptador'] = $adaptador;
            $grupos[$clave]['filas'][] = [$fila, $mapeo];
        }
        $respuesta = [];
        foreach ($grupos as $grupo) {
            $adaptador = $grupo['adaptador'];
            $origenIds = array_values(array_unique(array_map(static fn ($par): string => $par[0]->getRawOriginal('cod_registro_fuente'), $grupo['filas'])));
            $originales = $adaptador->recuperar($evaluacion, $origenIds, $corte, $conexion, $this->zonaAlmacenamiento)->keyBy($adaptador::PK);
            foreach ($grupo['filas'] as [$fila, $mapeo]) {
                $original = $originales[$fila->getRawOriginal('cod_registro_fuente')]
                    ?? throw new RuntimeException('El adaptador no entregó un registro original solicitado.');
                $posterior = self::fechaOriginal($original, $this->zonaAlmacenamiento) > $corte;
                $respuesta[$fila->getRawOriginal('cod_evidencia_evaluacion')] = ['origen' => $original,
                    // No se lee ni interpreta el valor de filas posteriores. El
                    // repositorio comprueba la fecha original y descarta la fila.
                    'valor' => $posterior ? null : $adaptador->extraer($original, $mapeo),
                    'tipo' => $posterior ? 'HECHO' : $adaptador->tipo($mapeo)];
            }
        }

        return $respuesta;
    }

    /**
     * Recupera columnas del adaptador por lotes; los nombres vienen de constantes.
     *
     * @param  AdaptadorControlesCognitivos|AdaptadorSeguimientosPedagogicos|AdaptadorValoracionesFuncionales|AdaptadorInstrumentos  $adaptador  Instancia final conocida.
     * @param  EvaluacionExperta  $evaluacion  Contexto que se contrasta con su cabecera persistida.
     * @param  list<string>  $ids  PK exactas solicitadas.
     * @param  DateTimeImmutable  $corte  Corte inclusivo.
     * @param  string  $conexion  Destino técnico explícito.
     * @param  DateTimeZone  $zona  Zona SQL explícita.
     * @return Collection<int, Model> Originales hasta el corte; posteriores únicamente con metadatos.
     *
     * @throws DomainException Por contexto, episodio, estado o fecha inválidos.
     * @throws RuntimeException Por base no autorizada u origen no disponible.
     */
    public static function recuperarOperativos(AdaptadorControlesCognitivos|AdaptadorSeguimientosPedagogicos|AdaptadorValoracionesFuncionales|AdaptadorInstrumentos|AdaptadorIadlExperimental $adaptador,
        EvaluacionExperta $evaluacion, array $ids, DateTimeImmutable $corte, string $conexion, DateTimeZone $zona): Collection
    {
        self::conexionSegura($conexion);
        self::contextoSintetico($evaluacion);
        self::validarIds(...$ids);
        if (! array_is_list($ids) || count(array_unique($ids)) !== count($ids)) {
            throw new DomainException('Se requiere una lista de PK únicas para el lote.');
        }
        $cabecera = EvaluacionExperta::on($conexion)->select(['cod_evaluacion_experta', 'cod_residente', 'cod_version_modelo', 'origen_activacion'])
            ->whereKey($evaluacion->getRawOriginal('cod_evaluacion_experta'))->first()
            ?? throw new RuntimeException('La evaluación propietaria no existe.');
        foreach ($cabecera->getAttributes() as $campo => $valor) {
            if (($evaluacion->getAttributes()[$campo] ?? null) !== $valor) {
                throw new DomainException('El contexto recibido difiere de la cabecera persistida.');
            }
        }
        $clase = $adaptador::MODELO;
        $metadatos = [$adaptador::PK, 'cod_residente', 'cod_personal', $adaptador::EPISODIO, 'fecha_hora', 'estado'];
        if ($adaptador instanceof AdaptadorInstrumentos) {
            $metadatos[] = 'cod_instrumento';
        }
        $residentId = $cabecera->getRawOriginal('cod_residente');
        $originales = $clase::on($conexion)->select($metadatos)->where('cod_residente', $residentId)->whereIn($adaptador::PK, $ids)
            ->orderBy($adaptador::PK)->get();
        if ($originales->count() !== count($ids)) {
            throw new RuntimeException('Un origen no existe o no pertenece al caso sintético solicitado.');
        }
        $hastaCorte = [];
        foreach ($originales as $original) {
            if (! in_array($original->getRawOriginal('estado'), $adaptador::ESTADOS, true)) {
                throw new DomainException('El registro fuente no conserva un estado admitido para su tipo.');
            }
            if (self::fechaOriginal($original, $zona) <= $corte) {
                $hastaCorte[] = $original->getRawOriginal($adaptador::PK);
            }
        }
        if ($hastaCorte === []) {
            return $originales;
        }
        $columnas = array_values(array_unique([...$metadatos, ...array_keys($adaptador::CAMPOS)]));
        $consulta = $clase::on($conexion)->select($columnas)->where('cod_residente', $residentId)
            ->whereIn($adaptador::PK, $hastaCorte)->where('fecha_hora', '<=', $corte->setTimezone($zona)->format('Y-m-d H:i:s'));
        if ($adaptador::EPISODIO === 'cod_atencion') {
            $consulta->with('atencion:cod_atencion,cod_residente');
        }
        $completos = $consulta->get()->keyBy($adaptador::PK);
        foreach ($originales as $indice => $original) {
            $completo = $completos[$original->getRawOriginal($adaptador::PK)] ?? null;
            if ($completo === null) {
                if (in_array($original->getRawOriginal($adaptador::PK), $hastaCorte, true)) {
                    throw new RuntimeException('La fuente cambió durante su lectura; no se publica una instantánea parcial.');
                }

                continue;
            }
            foreach ($metadatos as $campo) {
                if ($completo->getRawOriginal($campo) !== $original->getRawOriginal($campo)) {
                    throw new RuntimeException('Los metadatos originales cambiaron durante la lectura del lote.');
                }
            }
            $episodio = $completo->getRawOriginal($adaptador::EPISODIO);
            // IADL admite una observación sin atención. No se fabrica un episodio
            // para convertirla después en HechoExperto.
            if ($adaptador instanceof AdaptadorIadlExperimental && $episodio === null) {
                $originales[$indice] = $completo;

                continue;
            }
            if (! is_string($episodio) || $episodio === '') {
                throw new DomainException('El registro no tiene su episodio original; no se fabrica uno.');
            }
            if ($adaptador::EPISODIO === 'cod_atencion') {
                $atencion = $completo->getRelation('atencion');
                if ($atencion === null || $atencion->getRawOriginal('cod_residente') !== $residentId
                    || $atencion->getRawOriginal('cod_atencion') !== $episodio) {
                    throw new DomainException('La atención del registro pertenece a otro residente o no existe.');
                }
            }
            $originales[$indice] = $completo;
        }

        return $originales;
    }

    /**
     * Restringe toda lectura experimental antes de abrir el PDO.
     *
     * @param  string  $nombre  Conexión nombrada explícitamente.
     * @return Connection Destino autorizado por la guarda institucional existente.
     *
     * @throws DomainException Por nombre vacío o inválido.
     * @throws RuntimeException Fuera de testing o de bases técnicas desechables.
     */
    public static function conexionSegura(string $nombre): Connection
    {
        self::validarIds($nombre);
        $contenedor = Container::getInstance();
        if (! $contenedor->bound('env') || $contenedor->make('env') !== 'testing') {
            throw new RuntimeException('Los adaptadores experimentales solo funcionan en testing.');
        }
        $conexion = Model::resolveConnection($nombre);
        if (! BaseTecnicaDesechable::permite($conexion->getDriverName(), $conexion->getDatabaseName(),
            $conexion->getConfig('username'), $conexion->getConfig('host'))) {
            throw new RuntimeException('La fuente requiere una base técnica desechable autorizada.');
        }

        return $conexion;
    }

    /**
     * Comprueba la forma de los identificadores sin normalizarlos ni interpolarlos.
     *
     * @param  string  ...$ids  Códigos del contrato exacto.
     * @return void No consulta ni fabrica valores.
     *
     * @throws DomainException Por cadena vacía o con espacios/control.
     */
    public static function validarIds(string ...$ids): void
    {
        foreach ($ids as $id) {
            if ($id === '' || preg_match('/[\x00-\x20\x7F]/', $id) === 1) {
                throw new DomainException('La extracción requiere identificadores explícitos.');
            }
        }
    }

    /**
     * Resuelve un campo directo únicamente contra una lista cerrada del adaptador.
     *
     * @param  MapeoVariableFuente  $mapeo  Contrato persistido.
     * @param  array<string, string>  $permitidos  Constantes de columnas físicas del adaptador.
     * @return string Nombre físico validado, utilizado solo para leer atributos cargados.
     *
     * @throws DomainException Por modo, campo o selector no soportados.
     */
    public static function campoMapeado(MapeoVariableFuente $mapeo, array $permitidos): string
    {
        $datos = $mapeo->getAttributes();
        if (($datos['tipo_extraccion'] ?? null) !== 'CAMPO_DIRECTO'
            || ! array_key_exists('clave_selector', $datos) || $datos['clave_selector'] !== null
            || ! is_string($datos['campo_valor'] ?? null) || ! isset($permitidos[$datos['campo_valor']])) {
            throw new DomainException('Campo/método de extracción no disponible en la lista explícita del adaptador.');
        }

        return $datos['campo_valor'];
    }

    /**
     * Lee atributos físicos sin accessors que completen ausencias.
     *
     * @param  Model  $modelo  Original materializado con la columna seleccionada.
     * @param  string  $campo  Columna ya permitida por el adaptador.
     * @param  string  $tipo  Tipo físico de la lista cerrada.
     * @return string|bool|null Texto, decimal transportado como texto, booleano o NULL.
     *
     * @throws DomainException Por columna ausente o valor incompatible con el tipo.
     */
    public static function valorFisico(Model $modelo, string $campo, string $tipo): string|bool|null
    {
        $datos = $modelo->getAttributes();
        if (! array_key_exists($campo, $datos)) {
            throw new DomainException('La columna requerida no fue recuperada; no se sustituye por NULL.');
        }
        $valor = $datos[$campo];
        if ($valor === null) {
            return null;
        }
        if ($tipo === 'texto' && is_string($valor)) {
            return $valor;
        }
        if ($tipo === 'booleano') {
            if (in_array($valor, [true, 1, '1'], true)) {
                return true;
            }
            if (in_array($valor, [false, 0, '0'], true)) {
                return false;
            }
        }
        if ($tipo === 'decimal' && (is_string($valor) || is_int($valor) || (is_float($valor) && is_finite($valor)))
            && preg_match('/^-?\d+(?:\.\d+)?$/D', (string) $valor) === 1) {
            // PostgreSQL entrega DECIMAL como texto; SQLite puede materializar
            // números nativos. No se redondea, aplica baremo ni cambia de escala.
            return (string) $valor;
        }

        throw new DomainException('El valor físico no tiene el tipo explícitamente soportado por el contrato.');
    }

    /**
     * Identifica el propietario documental, sin convertir datos en A/P.
     *
     * @param  MapeoVariableFuente  $mapeo  Variable con propietario primario precargado.
     * @return string Destino técnico HECHO o EVIDENCIA_COGNITIVA descriptiva.
     *
     * @throws DomainException Por propietario de otra versión o no cargado.
     */
    public static function tipoDeclarado(MapeoVariableFuente $mapeo): string
    {
        if (! $mapeo->relationLoaded('variable')) {
            throw new DomainException('Debe cargarse la variable original del mapeo.');
        }
        $variable = $mapeo->getRelation('variable');
        if (! $variable instanceof VariableExperta || ! $variable->relationLoaded('propietarioPrimario')) {
            throw new DomainException('Debe cargarse el propietario primario documental.');
        }
        $propietario = $variable->getRelation('propietarioPrimario');
        if ($variable->getRawOriginal('cod_nodo_propietario_primario') === null) {
            return 'HECHO';
        }
        if (! $propietario instanceof NodoSemantico || $propietario->getRawOriginal('cod_nodo_semantico') !== $variable->getRawOriginal('cod_nodo_propietario_primario')
            || $propietario->getRawOriginal('cod_version_modelo') !== $mapeo->getRawOriginal('cod_version_modelo')
            || $propietario->getRawOriginal('estado') !== 'ACTIVO') {
            throw new DomainException('El propietario primario no es válido en esta versión.');
        }
        $codigo = $propietario->getRawOriginal('codigo_semantico');

        if (is_string($codigo) && (DominioCognitivoEnum::tryFrom($codigo) !== null || SubcomponenteCognitivoEnum::tryFrom($codigo) !== null)) {
            $tipoEsperado = DominioCognitivoEnum::tryFrom($codigo) !== null ? 'CRITERIO' : 'SUBCOMPONENTE';
            if ($propietario->getRawOriginal('tipo_nodo') !== $tipoEsperado) {
                throw new DomainException('El tipo de nodo no corresponde al propietario cognitivo declarado.');
            }

            return 'EVIDENCIA_COGNITIVA';
        }

        return 'HECHO';
    }

    /**
     * Valida el instante original sin aceptar normalizaciones de calendario.
     *
     * @param  Model  $original  Modelo con fecha_hora física seleccionada.
     * @param  DateTimeZone  $zona  Zona explícita del almacenamiento.
     * @return DateTimeImmutable Fecha real del registro original.
     *
     * @throws DomainException Por dato ausente, fecha o formato inválidos.
     */
    public static function fechaOriginal(Model $original, DateTimeZone $zona): DateTimeImmutable
    {
        $valor = $original->getRawOriginal('fecha_hora');
        if (! is_string($valor)) {
            throw new DomainException('La fuente necesita una fecha clínica original.');
        }
        $fecha = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $valor, $zona);
        $errores = DateTimeImmutable::getLastErrors();
        if ($fecha === false || ($errores !== false && ($errores['warning_count'] > 0 || $errores['error_count'] > 0))
            || $fecha->format('Y-m-d H:i:s') !== $valor) {
            throw new DomainException('La fecha clínica de la fuente es inválida.');
        }

        return $fecha;
    }

    /**
     * Contrasta variable/nodo y versión del mapeo antes de consultar una fuente.
     *
     * @param  MapeoVariableFuente  $mapeo  Contrato con variable/nodo cargados.
     * @param  string  $version  Versión única de la evaluación.
     * @return void No activa conocimiento ni selecciona otra variable.
     *
     * @throws DomainException Por estado o FK/versión incompatibles.
     */
    private static function validarMapeo(MapeoVariableFuente $mapeo, string $version): void
    {
        $variable = $mapeo->getRelation('variable');
        $nodo = $variable?->getRelation('nodo');
        if (! $variable instanceof VariableExperta || ! $nodo instanceof NodoSemantico
            || $mapeo->getRawOriginal('estado') !== 'ACTIVO'
            || $mapeo->getRawOriginal('cod_variable_experta') !== $variable->getRawOriginal('cod_variable_experta')
            || $variable->getRawOriginal('cod_nodo_semantico') !== $nodo->getRawOriginal('cod_nodo_semantico')
            || $nodo->getRawOriginal('tipo_nodo') !== 'VARIABLE') {
            throw new DomainException('El mapeo no identifica una variable y nodo válidos.');
        }
        foreach ([$mapeo, $variable, $nodo] as $fila) {
            if ($fila->getRawOriginal('cod_version_modelo') !== $version || $fila->getRawOriginal('estado') !== 'ACTIVO') {
                throw new DomainException('No se extrae desde conocimiento inactivo o de otra versión.');
            }
        }
    }

    /**
     * Impide consultar fuentes asistenciales a través de la PoC.
     *
     * @param  EvaluacionExperta  $evaluacion  Cabecera original de evaluación.
     * @return void No consulta otro residente ni inventa una cabecera.
     *
     * @throws DomainException Por evaluación/residente no sintéticos o modo asistencial.
     */
    private static function contextoSintetico(EvaluacionExperta $evaluacion): void
    {
        $datos = $evaluacion->getAttributes();
        foreach (['cod_evaluacion_experta', 'cod_residente', 'cod_version_modelo', 'origen_activacion'] as $campo) {
            if (! isset($datos[$campo]) || ! is_string($datos[$campo])) {
                throw new DomainException('La cabecera necesita identidades y origen de activación explícitos.');
            }
        }
        self::validarIds($datos['cod_evaluacion_experta'], $datos['cod_residente'], $datos['cod_version_modelo']);
        if (! str_starts_with($datos['cod_evaluacion_experta'], 'SIM-') || strlen($datos['cod_evaluacion_experta']) <= 4
            || ! str_starts_with($datos['cod_residente'], 'SINTETICO-') || strlen($datos['cod_residente']) <= 10
            || $datos['origen_activacion'] !== 'PRUEBA_TECNICA') {
            throw new DomainException('La extracción solo admite evaluaciones y residentes sintéticos explícitos.');
        }
    }
}
