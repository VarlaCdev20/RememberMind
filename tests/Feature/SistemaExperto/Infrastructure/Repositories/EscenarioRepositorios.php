<?php

declare(strict_types=1);

namespace Tests\Feature\SistemaExperto\Infrastructure\Repositories;

use App\Models\ControlCognitivo;
use App\Models\EvaluacionExperta;
use App\Models\EvidenciaCriterioEvaluacion;
use App\Models\ModeloBloqueExperto;
use App\Models\VersionModeloExperto;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\InventarioExperto;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\BloquesExpertos;
use MotorExperto\dominio\orion\Entities\Hechos\MemoriaTrabajo;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\DominioCognitivoEnum;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\HallazgoInstrumentalEnum;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades;
use Tests\Unit\SistemaExperto\Infrastructure\Mappers\EscenarioMapeadores as F;

/**
 * TÍTULO: Persistencia de escenarios sintéticos sobre las migraciones existentes.
 * CAPA: Infrastructure - Repositories (pruebas de integración).
 * ¿PARA QUÉ SIRVE? Reutiliza los datos de mappers sin inventar un esquema de prueba.
 * ¿QUÉ INFORMACIÓN RECIBE? Modelos ficticios y la base desechable de Tests\\TestCase.
 * ¿QUÉ HACE? Inserta fixtures explícitas según PK/FK y orden D-137.
 * ¿QUÉ DEVUELVE? Filas reales para consultas Eloquent.
 * ¿QUÉ NO DEBE HACER? No ejecuta inferencia, crea migraciones nuevas ni accede a bases institucionales.
 */
final class EscenarioRepositorios
{
    /** Inserta referencias artificiales de FK; sin parámetros, retorno ni excepciones esperadas. */
    public static function preparar(): void
    {
        DB::table('usuarios')->insert(['cod_usuario' => 'SIM-USU', 'correo' => 'sim-repositorios@example.invalid',
            'contrasena' => password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT), 'estado' => 'INACTIVO']);
        DB::table('residentes')->insert(['cod_residente' => 'SINTETICO-RES', 'nombres' => 'Caso sintético',
            'apellido_paterno' => 'Prueba', 'fecha_nacimiento' => '1940-01-01', 'estado' => 'ADMITIDO']);
        DB::table('personal')->insert(['cod_personal' => 'SIM-PER', 'cod_usuario' => 'SIM-USU',
            'nombres' => 'Autor ficticio', 'apellido_paterno' => 'Prueba', 'numero_documento' => 'SIM-DOC',
            'profesion' => 'PRUEBA_TECNICA', 'estado' => 'ACTIVO']);
        DB::table('areas')->insert(['cod_area' => 'SIM-AREA', 'nombre' => 'Área ficticia', 'estado' => 'ACTIVA']);
        DB::table('atenciones')->insert(['cod_atencion' => 'SIM-AT', 'cod_residente' => 'SINTETICO-RES',
            'cod_personal' => 'SIM-PER', 'cod_area' => 'SIM-AREA', 'tipo_atencion' => 'PRUEBA_TECNICA',
            'fecha_hora' => '2026-10-17 12:00:00', 'estado' => 'VIGENTE']);
        self::version('SIM-V1');
    }

    /**
     * Inserta una versión documental sin activar un servicio clínico.
     *
     * @param  string  $id  PK artificial.
     * @return void Sin excepciones esperadas para una PK nueva.
     */
    public static function version(string $id): void
    {
        self::insertar(F::modelo(VersionModeloExperto::class, ['cod_version_modelo' => $id, 'estado' => 'ACTIVO']));
    }

    /**
     * Conserva el grafo del fixture en orden FK, sin copiar tablas ni columnas.
     *
     * @param  Model  $raiz  Escenario Eloquent no persistido.
     * @return void Inserta únicamente filas ficticias.
     *
     * @throws QueryException Por fixture que viole el esquema real.
     */
    public static function guardarGrafo(Model $raiz): void
    {
        $filas = $visitados = [];
        self::reunir($raiz, $filas, $visitados);
        $orden = InventarioExperto::TABLAS;
        array_splice($orden, array_search('fuentes_datos_expertas', $orden, true), 0, ['variables_expertas']);
        array_splice($orden, array_search('evaluaciones_reglas', $orden, true), 0, ['resultados_criterio', 'trazas_inferencia']);
        foreach ($orden as $tabla) {
            foreach ($filas[$tabla] ?? [] as $fila) {
                self::insertar($fila);
            }
        }
    }

    /**
     * Prepara evidencia y registro fuente reales en el destino de prueba.
     *
     * @param  array<string, mixed>  $cambios  Valores físicos explícitos para la fuente.
     * @return ControlCognitivo Original recuperado por Eloquent.
     *
     * @throws QueryException Por fixture incompatible.
     */
    public static function evidencia(array $cambios = []): ControlCognitivo
    {
        [$fila, $origen] = F::evidencia();
        self::guardarGrafo($fila);
        DB::table('controles_cognitivos')->insert(array_replace($origen->getAttributes(),
            ['cod_personal' => 'SIM-PER', 'memoria_reciente' => 'OBSERVACION_FICTICIA'], $cambios));

        return ControlCognitivo::query()->whereKey('SIM-CTRL')->firstOrFail();
    }

    /**
     * Declara un I exclusivamente sintético, con referencia original íntegra.
     *
     * @return array{EvaluacionExperta, MemoriaTrabajo} Filas y memoria originales.
     *                                                  No acredita un catálogo instalado ni interpreta el control operativo.
     */
    public static function resultadosConSoporte(): array
    {
        self::evidencia();
        [$modelo] = F::resultados(['U', 'U', 'U', 'U', 'U']);
        $evidencia = EscenarioEntidades::evidencia('SIM-EVID',
            DominioCognitivoEnum::MEMORIA,
            HallazgoInstrumentalEnum::NO_INTERPRETABLE,
            cambiosHecho: ['fuenteId' => 'SIM-CONTROLES', 'origenId' => 'SIM-CTRL']);
        $memoria = EscenarioEntidades::memoria([$evidencia]);
        $criterio = $modelo->getRelation('evaluacionCriterios')[0];
        $criterio->setRawAttributes(array_replace($criterio->getAttributes(), [
            'estado_evaluabilidad' => 'EV-CM-2', 'cod_nodo_criterio' => 'SIM-N-MEM',
        ]));
        // La variable y el resultado comparten el mismo concepto físico COG-MEM.
        $nodo = $criterio->getRelation('criterio');
        $nodo->setRawAttributes(array_replace($nodo->getAttributes(), ['cod_nodo_semantico' => 'SIM-N-MEM']));
        $contrato = $nodo->getRelation('criterioDominioResultado');
        $contrato->setRawAttributes(array_replace($contrato->getAttributes(), ['cod_nodo_criterio' => 'SIM-N-MEM']));
        $valor = $criterio->getRelation('resultado')->getRelation('valor');
        $valor->setRawAttributes(array_replace($valor->getAttributes(), ['codigo_valor' => 'I']));
        $vinculo = F::modelo(EvidenciaCriterioEvaluacion::class, [
            'cod_evidencia_criterio' => 'SIM-VINCULO', 'cod_evaluacion_criterio' => 'SIM-EC-COG-MEM',
            'cod_evidencia_evaluacion' => 'SIM-EVID', 'rol_en_criterio' => 'SIM-SOPORTE', 'estado_participacion' => 'PRUEBA_TECNICA',
        ]);
        $criterio->setRelation('evidenciasCriterioEvaluacion', new Collection([$vinculo]));
        self::guardarGrafo($modelo);

        return [$modelo, $memoria];
    }

    /**
     * Reúne objetos con ciclos sin guardar un hijo antes de sus padres.
     *
     * @param  Model  $modelo  Fila ficticia.
     * @param  array<string, array<string, Model>>  $filas  Filas por tabla/PK.
     * @param  array<int, bool>  $visitados  Identidad de objeto ya inspeccionada.
     * @return void No modifica los modelos.
     */
    private static function reunir(Model $modelo, array &$filas, array &$visitados): void
    {
        if (isset($visitados[spl_object_id($modelo)])) {
            return;
        }
        $visitados[spl_object_id($modelo)] = true;
        $tabla = $modelo instanceof ModeloBloqueExperto ? $modelo->entidadLogica() : $modelo->getTable();
        $filas[$tabla][$modelo->getAttributes()[$modelo->getKeyName()]] = $modelo;
        foreach ($modelo->getRelations() as $relacion) {
            if ($relacion instanceof Model) {
                self::reunir($relacion, $filas, $visitados);
            } elseif ($relacion instanceof Collection) {
                foreach ($relacion as $elemento) {
                    self::reunir($elemento, $filas, $visitados);
                }
            }
        }
    }

    /**
     * Completa solo metadatos sintéticos requeridos por las migraciones vigentes.
     *
     * @param  Model  $modelo  Fila no persistida.
     * @return void Inserta la PK exacta; una fila ya preparada se reutiliza por esa PK.
     *
     * @throws QueryException Por inconsistencia del escenario.
     */
    private static function insertar(Model $modelo): void
    {
        $tabla = $modelo instanceof ModeloBloqueExperto ? $modelo->entidadLogica() : $modelo->getTable();
        $id = $modelo->getAttributes()[$modelo->getKeyName()];
        if (BloquesExpertos::consulta($tabla)->where($modelo->getKeyName(), $id)->exists()) {
            return;
        }
        $comunes = ['cod_usuario_creacion' => 'SIM-USU', 'fecha_hora_creacion' => '2026-10-16 12:00:00',
            'nombre' => 'Fixture '.$id, 'descripcion' => 'Descripción artificial de prueba.'];
        $extras = match ($tabla) {
            'versiones_modelo_experto' => ['codigo_version' => $id],
            'relaciones_semanticas' => ['significado' => 'Relación artificial de prueba.'],
            'dominios_valores_expertos' => ['codigo_dominio' => $id, 'tipo_dominio' => 'PRUEBA_TECNICA'],
            'valores_semanticos' => ['definicion_semantica' => 'Valor artificial de prueba.'],
            'variables_expertas' => ['tipo_semantico' => 'OBSERVACION', 'papel_inferencial' => 'CONTEXTUAL'],
            'fuentes_datos_expertas' => ['tipo_fuente' => 'PRUEBA_TECNICA', 'clave_adaptador' => 'SIM-CONTROL'],
            'mapeos_variables_fuente' => ['tipo_extraccion' => 'CAMPO_DIRECTO', 'campo_valor' => 'memoria_reciente'],
            'reglas_expertas' => ['codigo_regla' => $id],
            'evaluaciones_expertas' => ['fecha_hora_inicio' => '2026-10-18 12:00:00', 'estado_ejecucion' => 'PRUEBA_FINALIZADA'],
            'evidencias_evaluacion' => ['estado_representacion' => 'PRUEBA_TECNICA', 'cod_mapeo_variable_fuente' => 'SIM-MAP',
                'cod_registro_fuente' => $id, 'fecha_hora_incorporacion' => '2026-10-18 12:00:00'],
            'evidencias_criterio_evaluacion' => ['fecha_hora_vinculacion' => '2026-10-18 12:00:00'],
            default => [],
        };
        // Solo se completan metadatos de fixtures, nunca valores clínicos faltantes.
        // El filtro evita añadir timestamps u otras columnas ajenas al esquema.
        $datos = array_intersect_key(array_replace($comunes, $extras, $modelo->getAttributes()),
            array_flip(isset(BloquesExpertos::DEFINICIONES[$tabla]) ? array_keys(BloquesExpertos::DEFINICIONES[$tabla]['campos']) : Schema::getColumnListing($tabla)));
        BloquesExpertos::insertar($tabla, $datos);
    }
}
