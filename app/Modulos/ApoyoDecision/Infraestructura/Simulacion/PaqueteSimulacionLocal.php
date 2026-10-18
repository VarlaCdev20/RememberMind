<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Simulacion;

use App\Compartido\Infraestructura\Persistencia\CatalogoCodigos;
use App\Compartido\Infraestructura\Persistencia\GeneradorCodigo;
use App\Models\User;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\DominioCognitivoEnum as Dominio;
use App\SistemaExperto\Domain\ValueObjects\Conocimiento\TipoRelacionSemanticaEnum;
use App\SistemaExperto\Infrastructure\Persistence\Conservacion\CatalogoConservacionExperta;
use DomainException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Base de conocimiento de simulación.
 * TÍTULO: Paquete literal SIM-ORION-LOCAL-1.
 * CAPA: Infraestructura de ApoyoDecision.
 * ¿PARA QUÉ SIRVE? Ejercita reglas y red reales con conocimiento descriptivo explícito.
 * ¿QUÉ INFORMACIÓN RECIBE? Capacidad local y cuenta responsable existente.
 * ¿QUÉ HACE? Instala ocho reglas literales sin cambiar las versiones anteriores.
 * ¿QUÉ DEVUELVE? Identidad exacta del paquete, idempotentemente y sin sobrescritura.
 * ¿QUÉ NO DEBE HACER? No introduce baremos, pesos, umbrales ni acreditación A/P o C01–C05.
 */
final readonly class PaqueteSimulacionLocal
{
    public const TIPO_REGLA = 'SIM_RESULTADO';

    /** campo, literal de intercambio, dominio, salida descriptiva; no son reglas diagnósticas. */
    public const REGLAS = [
        'SIM-MEM-01' => ['memoria_reciente', 'ALTERACION_LEVE', 'COG-MEM', 'DIFICULTAD_DESCRIPTIVA'],
        'SIM-MEM-02' => ['memoria_reciente', 'ALTERADA', 'COG-MEM', 'DIFICULTAD_DESCRIPTIVA'],
        'SIM-MEM-03' => ['repite_preguntas', 'SIM_TRUE', 'COG-MEM', 'REPETICION_REGISTRADA'],
        'SIM-MEM-04' => ['olvida_indicaciones', 'SIM_TRUE', 'COG-MEM', 'OLVIDO_REGISTRADO'],
        'SIM-ATE-01' => ['atencion', 'DISMINUIDA', 'COG-ATE', 'DIFICULTAD_DESCRIPTIVA'],
        'SIM-ATE-02' => ['atencion', 'FLUCTUANTE', 'COG-ATE', 'DIFICULTAD_DESCRIPTIVA'],
        'SIM-LEN-01' => ['lenguaje', 'ALTERADO', 'COG-LEN', 'DIFICULTAD_DESCRIPTIVA'],
        'SIM-LEN-02' => ['comprension', 'ALTERADA', 'COG-LEN', 'DIFICULTAD_DESCRIPTIVA'],
    ];

    public function __construct(private ContextoSimulacionLocal $contexto) {}

    /** Carga explícita e idempotente. APROBADO aquí significa fixture técnico, nunca clínica. */
    public function preparar(User $autor): string
    {
        $db = $this->contexto->db();
        if ($autor->getConnectionName() !== null && $autor->getConnectionName() !== $this->contexto->conexion) {
            throw new DomainException('Actor de otra conexión.');
        }
        if ($autor->estado !== 'ACTIVO' || ! $autor->hasRole('SUPERADMINISTRADOR') || ! $autor->checkPermissionTo('auditoria.ver', 'web')) {
            throw new DomainException('La carga técnica exige Superadministración activa y auditoria.ver.');
        }

        return $db->transaction(function () use ($autor): string {
            $sello = ['estado' => 'ACTIVO', 'fecha_hora_creacion' => '2026-10-17 12:00:00', 'cod_usuario_creacion' => $autor->getKey(),
                'observacion' => 'SIMULACIÓN LOCAL AUTORIZADA. Fixture técnico; sin aprobación clínica.'];
            $version = $this->fila('versiones_modelo_experto', ['codigo_version' => ContextoSimulacionLocal::VERSION], [
                'nombre' => 'O.R.I.O.N. · simulación local', 'descripcion' => 'Reglas descriptivas y referencias independientes CAS-01/02/08/10/20 de 03A.',
                'fecha_hora_vigencia' => null, 'fecha_hora_retiro' => null, 'motivo_cambio' => 'Integración experimental autorizada por la propietaria.',
                'cod_version_anterior' => null] + $sello);
            $base = ['cod_version_modelo' => $version] + $sello;
            $domObservado = $this->fila('dominios_valores_expertos', ['cod_version_modelo' => $version, 'codigo_dominio' => 'SIM-OBS'],
                ['nombre' => 'Valores literales de prueba', 'tipo_dominio' => 'CATEGORICO', 'descripcion' => 'Identidad textual/booleana; no aplica norma clínica.'] + $sello);
            $domSalida = $this->fila('dominios_valores_expertos', ['cod_version_modelo' => $version, 'codigo_dominio' => 'SIM-DESCRIPCION'],
                ['nombre' => 'Salidas descriptivas de simulación', 'tipo_dominio' => 'CATEGORICO', 'descripcion' => 'No acredita estados A/P ni patrones PET.'] + $sello);
            $fuente = $this->fila('fuentes_datos_expertas', ['cod_version_modelo' => $version, 'codigo_fuente' => 'SIM-CONTROL-COG'],
                ['nombre' => 'Controles ficticios autorizados', 'tipo_fuente' => 'OPERATIVA', 'tabla_raiz' => 'controles_cognitivos',
                    'campo_pk_raiz' => 'cod_control_cognitivo', 'campo_residente' => 'cod_residente', 'campo_temporal' => 'fecha_hora',
                    'campo_personal' => 'cod_personal', 'campo_estado' => 'estado', 'clave_adaptador' => 'SIM_CONTROL_LITERAL'] + $sello);
            $nodos = $criterios = [];
            foreach (Dominio::cases() as $dominio) {
                $nodos[$dominio->value] = $this->fila('nodos_semanticos', ['cod_version_modelo' => $version, 'codigo_semantico' => $dominio->value],
                    ['nombre' => $dominio->value, 'tipo_nodo' => 'CRITERIO', 'definicion' => 'Dominio de la simulación, sin publicación clínica.'] + $sello);
                $criterios[$dominio->value] = $this->fila('criterios_dominios_resultado', ['cod_nodo_criterio' => $nodos[$dominio->value]],
                    ['cod_dominio_valores' => $domSalida] + $sello);
            }
            $variables = [];
            foreach (self::REGLAS as $codigo => [$campo, $literal, $dominio, $salida]) {
                $concepto = 'SIM-OBS:'.$campo;
                $nodo = $this->fila('nodos_semanticos', ['cod_version_modelo' => $version, 'codigo_semantico' => $concepto],
                    ['nombre' => $campo.' observado', 'tipo_nodo' => 'VARIABLE', 'definicion' => 'Literal del control ficticio, sin interpretación de dominio.'] + $sello);
                $variable = $this->fila('variables_expertas', ['cod_version_modelo' => $version, 'cod_nodo_semantico' => $nodo],
                    ['cod_nodo_propietario_primario' => $nodos[$dominio], 'cod_dominio_valores' => $domObservado,
                        'tipo_semantico' => 'OBSERVACIONAL', 'papel_inferencial' => 'DESCRIPTIVO'] + $sello);
                $variables[$campo] = $variable;
                $mapa = $this->fila('mapeos_variables_fuente', ['cod_version_modelo' => $version, 'cod_variable_experta' => $variable, 'cod_fuente_dato_experta' => $fuente],
                    ['tipo_extraccion' => 'CAMPO_DIRECTO', 'campo_valor' => $campo, 'clave_selector' => null] + $sello);
                $valor = $this->valor($domObservado, $literal, $sello);
                $esBool = in_array($campo, ['repite_preguntas', 'olvida_indicaciones'], true);
                $this->fila('mapeos_valores_fuente', ['cod_mapeo_variable_fuente' => $mapa, 'tipo_valor_fuente' => $esBool ? 'bool' : 'string',
                    'valor_fuente_exacto' => $esBool ? 'true' : $literal], ['cod_valor_semantico' => $valor, 'estado_aprobacion' => 'APROBADO',
                        'fecha_hora_vigencia_desde' => null, 'fecha_hora_vigencia_hasta' => null] + $sello);
                if ($esBool) {
                    $falso = $this->valor($domObservado, 'SIM_FALSE', $sello);
                    $this->fila('mapeos_valores_fuente', ['cod_mapeo_variable_fuente' => $mapa, 'tipo_valor_fuente' => 'bool', 'valor_fuente_exacto' => 'false'],
                        ['cod_valor_semantico' => $falso, 'estado_aprobacion' => 'APROBADO', 'fecha_hora_vigencia_desde' => null, 'fecha_hora_vigencia_hasta' => null] + $sello);
                }
                $r = $this->fila('reglas_expertas', ['cod_version_modelo' => $version, 'codigo_regla' => $codigo],
                    ['nombre' => $codigo.' · '.$campo, 'tipo_regla' => self::TIPO_REGLA,
                        'descripcion' => 'Si el literal coincide, registrar '.$salida.'. No acredita alteración objetiva.'] + $sello);
                $this->fila('condiciones_regla_experta', ['cod_regla_experta' => $r, 'cod_variable_experta' => $variable, 'cod_valor_semantico' => $valor], []);
                $this->fila('consecuencias_regla_experta', ['cod_regla_experta' => $r], ['cod_criterio_dominio_resultado' => $criterios[$dominio],
                    'cod_valor_semantico' => $this->valor($domSalida, $salida, $sello)]);
                $this->fila('relaciones_semanticas', ['cod_version_modelo' => $version, 'cod_nodo_origen' => $nodo,
                    'cod_nodo_destino' => $nodos[$dominio], 'tipo_relacion' => 'EVIDENCIA_DE'],
                    ['modalidad_relacion' => null, 'significado' => 'Participación descriptiva explícita exclusivamente en la simulación.'] + $sello);
            }
            CatalogoConservacionExperta::preparar($this->contexto->conexion, $version, $autor->getKey(), $this->contexto);

            return $version;
        });
    }

    private function valor(string $dominio, string $codigo, array $sello): string
    {
        return $this->fila('valores_semanticos', ['cod_dominio_valores' => $dominio, 'codigo_valor' => $codigo],
            ['nombre' => $codigo, 'definicion_semantica' => 'Literal técnico ficticio, sin baremo ni aprobación clínica.', 'estado_aprobacion' => 'APROBADO'] + $sello);
    }

    /** Ningún reintento modifica el paquete anterior; divergencia exige otra versión. */
    private function fila(string $tabla, array $clave, array $datos): string
    {
        $db = $this->contexto->db();
        $pk = CatalogoCodigos::ENTIDADES[$tabla][0];
        $fila = $db->table($tabla)->where($clave)->lockForUpdate()->first();
        $esperados = $clave + $datos;
        if ($fila !== null) {
            foreach ($esperados as $campo => $valor) {
                if ($valor !== $fila->$campo) {
                    throw new DomainException('Paquete de simulación alterado: '.$tabla.'.'.$campo);
                }
            }

            return $fila->$pk;
        }
        $id = (new GeneradorCodigo)->siguiente($tabla, $this->contexto->conexion);
        $db->table($tabla)->insert([$pk => $id] + $esperados);

        return $id;
    }

    public static function contratoRed(string $version): array
    {
        return [['versionConocimiento' => $version, 'tipo' => TipoRelacionSemanticaEnum::EVIDENCIA_DE,
            'tipoOrigen' => 'VARIABLE', 'tipoDestino' => 'CRITERIO', 'habilitaParticipacion' => true]];
    }
}
