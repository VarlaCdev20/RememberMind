<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\FuentesOperativas;

use App\Models\ControlFuncionalIadl;
use App\Models\EvaluacionExperta;
use App\Models\MapeoVariableFuente;
use App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\MapeoFuentes\ExtractorVariableMapeada;
use DateTimeImmutable;
use DateTimeZone;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use RuntimeException;

/**
 * TÍTULO: Adaptador descriptivo de funcionalidad instrumental.
 * CAPA: Infrastructure - Adapters.
 * ¿PARA QUÉ SIRVE? Recupera la fuente IADL aprobada sin acreditar independencia.
 * ¿QUÉ INFORMACIÓN RECIBE? Evaluación sintética, PK originales, corte, conexión y mapeo.
 * ¿QUÉ HACE? Contrasta el esquema y transporta columnas fijas hasta el corte.
 * ¿QUÉ DEVUELVE? Originales y escalares nullable, siempre como hechos descriptivos.
 * ¿QUÉ NO DEBE HACER? No traduce oportunidad a interpretabilidad, causa a atribución
 * cognitiva ni una observación o BADL a C03. No inventa atención para una fila sin vínculo.
 */
final readonly class AdaptadorIadlExperimental
{
    /** Modelo original de la extensión aprobada. */
    public const MODELO = ControlFuncionalIadl::class;

    /** Tabla del diccionario aprobado PR #16. */
    public const TABLA = 'controles_funcionales_iadl';

    /** PK original, sin identidades paralelas. */
    public const PK = 'cod_control_iadl';

    /** Atención nullable: su ausencia se conserva, no se fabrica episodio Domain. */
    public const EPISODIO = 'cod_atencion';

    /** Estados físicos de historia append-only. */
    public const ESTADOS = ['REGISTRADO', 'RECTIFICACION'];

    /** Columnas descriptivas autorizadas; no incluye atributos de autorización ni relaciones. */
    public const CAMPOS = ['actividad' => 'texto', 'oportunidad' => 'texto', 'desempeno' => 'texto',
        'tipo_ayuda' => 'texto', 'causa_ayuda' => 'texto', 'nivel_previo' => 'texto', 'fuente' => 'texto', 'observacion' => 'texto'];

    /**
     * Comprueba la tabla y todas sus columnas sin afirmar disponibilidad de datos.
     *
     * @param  string  $conexion  Destino técnico desechable.
     * @return array{disponible: bool, motivo: string} Disponibilidad del contrato físico.
     *
     * @throws RuntimeException Fuera de testing/destino autorizado o ante fallo SQL.
     */
    public function disponibilidad(string $conexion): array
    {
        $esquema = ExtractorVariableMapeada::conexionSegura($conexion)->getSchemaBuilder();
        $columnas = [self::PK, 'cod_residente', 'cod_personal', 'cod_usuario_registro', self::EPISODIO,
            'cod_control_anterior', 'fecha_hora', 'estado', 'registrado_en', ...array_keys(self::CAMPOS)];
        $disponible = $esquema->hasTable(self::TABLA) && $esquema->hasColumns(self::TABLA, $columnas);

        return ['disponible' => $disponible, 'motivo' => $disponible
            ? 'Fuente IADL descriptiva disponible; no acredita C03.'
            : 'La extensión IADL aprobada no está instalada íntegramente en el destino experimental.'];
    }

    /**
     * Lee únicamente las PK explícitas del mismo residente.
     *
     * @param  EvaluacionExperta  $evaluacion  Cabecera sintética original.
     * @param  list<string>  $ids  Identidades originales solicitadas.
     * @param  DateTimeImmutable  $corte  Instante clínico inclusivo.
     * @param  string  $conexion  Conexión segregada.
     * @param  DateTimeZone  $zona  Zona SQL explícita.
     * @return Collection<int, ControlFuncionalIadl> Originales; futuros solo incluyen metadatos.
     *
     * @throws DomainException Por contexto, estado o atención incompatibles.
     * @throws RuntimeException Por esquema/origen indisponible o conexión no autorizada.
     */
    public function recuperar(EvaluacionExperta $evaluacion, array $ids, DateTimeImmutable $corte, string $conexion, DateTimeZone $zona): Collection
    {
        $disponibilidad = $this->disponibilidad($conexion);
        if (! $disponibilidad['disponible']) {
            throw new RuntimeException($disponibilidad['motivo']);
        }

        return ExtractorVariableMapeada::recuperarOperativos($this, $evaluacion, $ids, $corte, $conexion, $zona);
    }

    /** @param MapeoVariableFuente $mapeo Campo directo. Rechaza selectores y columnas ajenas con DomainException, sin consultas. */
    public function validarMapeo(MapeoVariableFuente $mapeo): void
    {
        ExtractorVariableMapeada::campoMapeado($mapeo, self::CAMPOS);
    }

    /** @param ControlFuncionalIadl $origen Original cargado. @param MapeoVariableFuente $mapeo Campo explícito. @return string|int|float|bool|null Dato sin interpretación; lanza DomainException si no se cargó. */
    public function extraer(ControlFuncionalIadl $origen, MapeoVariableFuente $mapeo): string|int|float|bool|null
    {
        $campo = ExtractorVariableMapeada::campoMapeado($mapeo, self::CAMPOS);

        return ExtractorVariableMapeada::valorFisico($origen, $campo, self::CAMPOS[$campo]);
    }

    /** @param MapeoVariableFuente $mapeo Mapeo validado. @return string HECHO descriptivo: nunca evidencia A/P o acreditación C03. */
    public function tipo(MapeoVariableFuente $mapeo): string
    {
        $this->validarMapeo($mapeo);

        return 'HECHO';
    }
}
