<?php

declare(strict_types=1);

namespace App\SistemaExperto\Infrastructure\Adapters\FuentesOperativas;

use App\Models\AplicacionInstrumento;
use App\Models\EvaluacionExperta;
use App\Models\Instrumento;
use App\Models\MapeoVariableFuente;
use App\Models\OpcionPregunta;
use App\Models\PreguntaInstrumento;
use App\Models\RespuestaInstrumento;
use App\SistemaExperto\Infrastructure\Adapters\MapeoFuentes\ExtractorVariableMapeada;
use DateTimeImmutable;
use DateTimeZone;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use RuntimeException;

/**
 * TÍTULO:
 * Adaptador de evaluaciones instrumentales.
 *
 * CAPA:
 * Infrastructure - Adapters.
 *
 * ¿PARA QUÉ SIRVE?
 * Recupera datos brutos autorizados sin interpretación clínica.
 *
 * ¿QUÉ INFORMACIÓN RECIBE?
 * Aplicaciones sintéticas y selectores de respuesta registrados por versión del conocimiento.
 *
 * ¿QUÉ HACE?
 * Recupera instrumento/versión, preguntas, respuestas y opciones; comprueba sus FK.
 *
 * ¿QUÉ DEVUELVE?
 * Valores escalares brutos y procedencia consultable, con NULL intacto.
 *
 * ¿QUÉ NO DEBE HACER?
 * No calcula baremos, interpreta totales como dominios ni transforma respuestas en diagnósticos.
 */
final readonly class AdaptadorInstrumentos
{
    /** Modelo existente del contrato físico. */
    public const MODELO = AplicacionInstrumento::class;

    /** Tabla de aplicaciones del instrumento. */
    public const TABLA = 'aplicaciones_instrumento';

    /** Identidad de la aplicación original. */
    public const PK = 'cod_aplicacion';

    /** Episodio clínico original; no se fabrica si falta. */
    public const EPISODIO = 'cod_atencion';

    /** Una aplicación incompleta o anulada no habilita su extracción. */
    public const ESTADOS = ['COMPLETA'];

    /** @var array<string, string> Los totales son datos brutos y siempre tienen destino HECHO. */
    public const CAMPOS = ['puntaje_total' => 'decimal', 'puntaje_maximo' => 'decimal', 'observacion' => 'texto'];

    /** @var array<string, string> Columnas permitidas de una respuesta, sin sumatorias ni interpretación. */
    private const CAMPOS_RESPUESTA = ['valor_numero' => 'decimal', 'valor_texto' => 'texto', 'valor_logico' => 'booleano',
        'puntaje' => 'decimal', 'cod_opcion' => 'texto'];

    /** @var array<string, array<string, array{instrumento: string, version: string, pregunta: string, campo: string}>> Copia del contrato técnico por versión y selector. */
    private array $selectores;

    /**
     * Registra selectores explícitos; vacío solo habilita columnas brutas directas.
     *
     * @param  array<string, array<string, array{instrumento: string, version: string, pregunta: string, campo: string}>>  $selectoresPorVersion  PK de instrumento/pregunta, versión formal y columna exactas.
     *
     * @throws DomainException Por contrato incompleto, selector inválido o campo no soportado.
     */
    public function __construct(array $selectoresPorVersion = [])
    {
        $copia = [];
        foreach ($selectoresPorVersion as $version => $selectores) {
            ExtractorVariableMapeada::validarIds($version);
            if (! is_array($selectores)) {
                throw new DomainException('La versión requiere un registro explícito de selectores.');
            }
            foreach ($selectores as $clave => $contrato) {
                ExtractorVariableMapeada::validarIds($clave);
                if (! is_array($contrato) || count($contrato) !== 4) {
                    throw new DomainException('El selector requiere instrumento, versión formal, pregunta y campo.');
                }
                $nuevo = [];
                foreach (['instrumento', 'version', 'pregunta', 'campo'] as $campo) {
                    if (! isset($contrato[$campo]) || ! is_string($contrato[$campo])) {
                        throw new DomainException('Contrato instrumental incompleto.');
                    }
                    ExtractorVariableMapeada::validarIds($contrato[$campo]);
                    $nuevo[$campo] = $contrato[$campo];
                }
                if (! isset(self::CAMPOS_RESPUESTA[$nuevo['campo']])) {
                    throw new DomainException('Columna de respuesta no soportada por este adaptador.');
                }
                $copia[$version][$clave] = $nuevo;
            }
        }
        $this->selectores = $copia;
    }

    /**
     * Recupera aplicaciones y dependencias por lotes, sin leer reactivos protegidos.
     *
     * @param  EvaluacionExperta  $evaluacion  Contexto experimental exacto.
     * @param  list<string>  $ids  PK de aplicaciones originales.
     * @param  DateTimeImmutable  $corte  Corte clínico inclusivo.
     * @param  string  $conexion  Destino técnico segregado.
     * @param  DateTimeZone  $zona  Zona SQL explícita.
     * @return Collection<int, AplicacionInstrumento> Instrumento y respuestas cargados para aplicaciones hasta el corte; posteriores solo metadatos.
     *
     * @throws DomainException Por contexto, estado, fecha o FK incoherentes.
     * @throws RuntimeException Por origen o dependencia inexistente.
     */
    public function recuperar(EvaluacionExperta $evaluacion, array $ids, DateTimeImmutable $corte, string $conexion, DateTimeZone $zona): Collection
    {
        $aplicaciones = ExtractorVariableMapeada::recuperarOperativos($this, $evaluacion, $ids, $corte, $conexion, $zona);
        $vigentes = $aplicaciones->filter(static fn ($a): bool => ExtractorVariableMapeada::fechaOriginal($a, $zona) <= $corte);
        if ($vigentes->isEmpty()) {
            return $aplicaciones;
        }
        $instrumentos = Instrumento::on($conexion)->select(['cod_instrumento', 'codigo', 'version', 'estado'])
            ->whereIn('cod_instrumento', $vigentes->pluck('cod_instrumento')->unique()->all())->get()->keyBy('cod_instrumento');
        $respuestas = RespuestaInstrumento::on($conexion)->select(['cod_respuesta', 'cod_aplicacion', 'cod_pregunta',
            'cod_opcion', 'valor_numero', 'valor_texto', 'valor_logico', 'puntaje'])
            ->whereIn('cod_aplicacion', $vigentes->modelKeys())->orderBy('cod_respuesta')->get();
        $preguntas = PreguntaInstrumento::on($conexion)->select(['cod_pregunta', 'cod_instrumento', 'codigo', 'tipo_respuesta', 'estado'])
            ->whereIn('cod_pregunta', $respuestas->pluck('cod_pregunta')->unique()->all())->get()->keyBy('cod_pregunta');
        $opciones = OpcionPregunta::on($conexion)->select(['cod_opcion', 'cod_pregunta', 'valor', 'puntaje', 'estado'])
            ->whereIn('cod_opcion', $respuestas->pluck('cod_opcion')->filter(static fn ($id): bool => $id !== null)->unique()->all())
            ->get()->keyBy('cod_opcion');
        foreach ($vigentes as $aplicacion) {
            $instrumento = $instrumentos[$aplicacion->getRawOriginal('cod_instrumento')] ?? null;
            if (! $instrumento instanceof Instrumento || $instrumento->getRawOriginal('estado') !== 'ACTIVO') {
                throw new DomainException('La aplicación requiere un instrumento activo existente.');
            }
            $aplicacion->setRelation('instrumento', $instrumento);
            $propias = new Collection;
            foreach ($respuestas->where('cod_aplicacion', $aplicacion->getRawOriginal('cod_aplicacion')) as $respuesta) {
                $pregunta = $preguntas[$respuesta->getRawOriginal('cod_pregunta')] ?? null;
                if (! $pregunta instanceof PreguntaInstrumento
                    || $pregunta->getRawOriginal('cod_instrumento') !== $instrumento->getRawOriginal('cod_instrumento')
                    || $pregunta->getRawOriginal('estado') !== 'ACTIVO') {
                    throw new DomainException('La pregunta no pertenece al instrumento activo aplicado.');
                }
                $opcionId = $respuesta->getRawOriginal('cod_opcion');
                $opcion = $opcionId === null ? null : ($opciones[$opcionId] ?? null);
                if ($opcionId !== null && (! $opcion instanceof OpcionPregunta
                    || $opcion->getRawOriginal('cod_pregunta') !== $pregunta->getRawOriginal('cod_pregunta')
                    || $opcion->getRawOriginal('estado') !== 'ACTIVO')) {
                    throw new DomainException('La opción no pertenece a la pregunta original.');
                }
                $respuesta->setRelation('pregunta', $pregunta)->setRelation('opcion', $opcion);
                $propias->push($respuesta);
            }
            $aplicacion->setRelation('respuestas', $propias);
        }

        return $aplicaciones;
    }

    /**
     * Rechaza campos y selectores no registrados antes de leer aplicaciones.
     *
     * @param  MapeoVariableFuente  $mapeo  Contrato persistido.
     * @return void No interpreta puntajes ni crea correspondencias.
     *
     * @throws DomainException Por modo/campo/selector no soportados.
     */
    public function validarMapeo(MapeoVariableFuente $mapeo): void
    {
        if ($mapeo->getRawOriginal('tipo_extraccion') === 'CAMPO_DIRECTO') {
            ExtractorVariableMapeada::campoMapeado($mapeo, self::CAMPOS);
        } else {
            $this->selector($mapeo);
        }
    }

    /**
     * Conserva un total bruto o una columna de una respuesta original.
     *
     * @param  AplicacionInstrumento  $origen  Aplicación con instrumento/respuestas precargados.
     * @param  MapeoVariableFuente  $mapeo  Campo directo o selector de la versión exacta.
     * @return string|int|float|bool|null Valor original; una respuesta existente con NULL continúa NULL.
     *
     * @throws DomainException Por versión formal desconocida/incompatible o referencia incoherente.
     * @throws RuntimeException Por respuesta requerida ausente; no se fabrica un valor normal.
     */
    public function extraer(AplicacionInstrumento $origen, MapeoVariableFuente $mapeo): string|int|float|bool|null
    {
        $this->validarMapeo($mapeo);
        if ($mapeo->getRawOriginal('tipo_extraccion') === 'CAMPO_DIRECTO') {
            $campo = ExtractorVariableMapeada::campoMapeado($mapeo, self::CAMPOS);

            return ExtractorVariableMapeada::valorFisico($origen, $campo, self::CAMPOS[$campo]);
        }
        $contrato = $this->selector($mapeo);
        $instrumento = $origen->relationLoaded('instrumento') ? $origen->getRelation('instrumento') : null;
        if (! $instrumento instanceof Instrumento
            || $origen->getRawOriginal('cod_instrumento') !== $contrato['instrumento']
            || $instrumento->getRawOriginal('cod_instrumento') !== $contrato['instrumento']
            || $instrumento->getRawOriginal('version') !== $contrato['version'] || $instrumento->getRawOriginal('estado') !== 'ACTIVO'
            || ! $origen->relationLoaded('respuestas') || ! $origen->getRelation('respuestas') instanceof Collection) {
            throw new DomainException('El selector requiere instrumento y versión formal exactos, sin defaults.');
        }
        $coinciden = $origen->getRelation('respuestas')->filter(
            static fn ($r): bool => $r->getRawOriginal('cod_pregunta') === $contrato['pregunta']);
        if ($coinciden->count() !== 1) {
            throw new RuntimeException('La respuesta original del selector está ausente o duplicada.');
        }
        $respuesta = $coinciden->first();
        if (! $respuesta instanceof RespuestaInstrumento || $respuesta->getRawOriginal('cod_aplicacion') !== $origen->getRawOriginal('cod_aplicacion')) {
            throw new DomainException('La respuesta pertenece a otra aplicación.');
        }

        return ExtractorVariableMapeada::valorFisico($respuesta, $contrato['campo'], self::CAMPOS_RESPUESTA[$contrato['campo']]);
    }

    /**
     * Conserva la versión formal y las PK sin interpretar su contenido.
     *
     * @param  AplicacionInstrumento  $origen  Aplicación con instrumento/respuestas materializados.
     * @return array{aplicacion: string, instrumento: string, version: string|null, respuestas: list<array{respuesta: string, pregunta: string, opcion: string|null}>} Referencias originales, sin textos de reactivos.
     *
     * @throws DomainException Por relaciones faltantes o versión de tipo inválido.
     */
    public function procedencia(AplicacionInstrumento $origen): array
    {
        if (! $origen->relationLoaded('instrumento') || ! $origen->getRelation('instrumento') instanceof Instrumento
            || ! $origen->relationLoaded('respuestas') || ! $origen->getRelation('respuestas') instanceof Collection) {
            throw new DomainException('Debe recuperarse la procedencia instrumental antes de consultarla.');
        }
        $instrumento = $origen->getRelation('instrumento');
        $version = $instrumento->getRawOriginal('version');
        if ($version !== null && ! is_string($version)) {
            throw new DomainException('La versión formal debe conservarse como texto o NULL.');
        }
        $respuestas = [];
        foreach ($origen->getRelation('respuestas') as $respuesta) {
            $respuestas[] = ['respuesta' => $respuesta->getRawOriginal('cod_respuesta'),
                'pregunta' => $respuesta->getRawOriginal('cod_pregunta'), 'opcion' => $respuesta->getRawOriginal('cod_opcion')];
        }

        return ['aplicacion' => $origen->getRawOriginal('cod_aplicacion'),
            'instrumento' => $instrumento->getRawOriginal('cod_instrumento'), 'version' => $version, 'respuestas' => $respuestas];
    }

    /**
     * Un total nunca se promueve a evidencia de un dominio específico.
     *
     * @param  MapeoVariableFuente  $mapeo  Contrato de extracción explícito.
     * @return string HECHO para campos directos; descriptor cognitivo solo para un selector con propietario documentado.
     *
     * @throws DomainException Por propietario primario incoherente.
     */
    public function tipo(MapeoVariableFuente $mapeo): string
    {
        return $mapeo->getRawOriginal('tipo_extraccion') === 'CAMPO_DIRECTO'
            ? 'HECHO' : ExtractorVariableMapeada::tipoDeclarado($mapeo);
    }

    /**
     * Localiza un selector sin reutilizarlo en otra versión del conocimiento.
     *
     * @param  MapeoVariableFuente  $mapeo  Mapeo persistido.
     * @return array{instrumento: string, version: string, pregunta: string, campo: string} Contrato técnico literal.
     *
     * @throws DomainException Por selector no registrado, modo no soportado o campo incompatible.
     */
    private function selector(MapeoVariableFuente $mapeo): array
    {
        $datos = $mapeo->getAttributes();
        if (($datos['tipo_extraccion'] ?? null) !== 'SELECTOR_COMPONENTE'
            || ! array_key_exists('campo_valor', $datos) || $datos['campo_valor'] !== null
            || ! is_string($datos['clave_selector'] ?? null)
            || ! isset($this->selectores[$datos['cod_version_modelo'] ?? ''][$datos['clave_selector']])) {
            throw new DomainException('Selector instrumental no disponible en esta versión del conocimiento.');
        }

        return $this->selectores[$datos['cod_version_modelo']][$datos['clave_selector']];
    }
}
