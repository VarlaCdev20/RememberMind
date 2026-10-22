<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Simulacion;

use App\Compartido\Infraestructura\Persistencia\CatalogoCodigos;
use App\Compartido\Infraestructura\Persistencia\GeneradorCodigo;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\ResultadoInferenciaDTO;
use MotorExperto\soporte\conservacion\CodecInstantanea;
use DomainException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Explicación de decisiones originales.
 * TÍTULO: Traza relacional de reglas de simulación.
 * CAPA: Infraestructura de ApoyoDecision.
 * ¿PARA QUÉ SIRVE? Conserva decisiones y condiciones originales de CU-03.
 * ¿QUÉ INFORMACIÓN RECIBE? Evaluación física y respuesta estructurada del motor.
 * ¿QUÉ HACE? Reutiliza tablas de traza y verifica una huella tipada del significado.
 * ¿QUÉ DEVUELVE? Decisiones originales verificables o rechazo explícito.
 * ¿QUÉ NO DEBE HACER? No reevalúa reglas ni presenta sus salidas descriptivas como A/P.
 */
final readonly class TrazaSimulacionLocal
{
    /** Columnas administrativas incorporadas después de SIM-TRACE-1; no cambian una condición o consecuencia. */
    private const GOBIERNO = ['cod_regla_anterior', 'estado_revision', 'cod_usuario_revision', 'fecha_hora_revision', 'justificacion_revision'];
    public function __construct(private ContextoSimulacionLocal $contexto) {}

    public function guardar(string $evaluacion, ResultadoInferenciaDTO $inferencia): void
    {
        $db = $this->contexto->db();
        $porDominio = [];
        foreach ($inferencia->evaluacionesReglas as $e) {
            $r = $e['regla'];
            $dominio = $r->consecuencia['concepto'];
            $condiciones = $db->table('condiciones_regla_experta')->where('cod_regla_experta', $r->id)->orderBy('cod_condicion_regla')->get();
            if ($condiciones->count() !== 1 || count($e['condiciones']['ramas'] ?? []) !== 1) {
                throw new DomainException('El paquete local requiere una condición literal por regla.');
            }
            $valor = $e['condiciones']['ramas'][0]['valor'];
            $estado = match ($valor) {
                true => 'CUMPLE', false => 'NO_CUMPLE', null => 'DESCONOCIDO'
            };
            $porDominio[$dominio][] = [$r->id, $e['estado']->value, [[$condiciones->sole()->cod_condicion_regla, $estado]], $this->definicion($r->id)];
        }
        foreach ($porDominio as $dominio => $payload) {
            usort($payload, static fn ($a, $b) => strcmp($a[0], $b[0]));
            $criterio = $db->table('evaluacion_criterios as c')->join('nodos_semanticos as n', 'n.cod_nodo_semantico', '=', 'c.cod_nodo_criterio')
                ->where('c.cod_evaluacion_experta', $evaluacion)->where('c.tipo_registro', 'DOMINIO')
                ->where('n.codigo_semantico', 'SNAP:DOMINIO:'.$dominio)->value('c.cod_evaluacion_criterio')
                ?? throw new DomainException('Traza sin dominio conservado.');
            $previa = $this->leer($criterio);
            if ($previa !== null) {
                if (CodecInstantanea::huella($previa['payload']) !== CodecInstantanea::huella($payload)) {
                    throw new DomainException('La traza del reintento difiere de la ejecución conservada.');
                }

                continue;
            }
            $fecha = now()->format('Y-m-d H:i:s');
            $t = $this->insertar('trazas_inferencia', ['cod_evaluacion_criterio' => $criterio, 'estado_inferencia' => 'PRUEBA_FINALIZADA',
                'fecha_hora_inicio' => $fecha, 'fecha_hora_fin' => $fecha]);
            foreach ($payload as [$regla, $estado, $condiciones]) {
                $er = $this->insertar('evaluaciones_reglas', ['cod_traza_inferencia' => $t, 'cod_regla_experta' => $regla,
                    'estado_regla' => $estado, 'fecha_hora_evaluacion' => $fecha]);
                foreach ($condiciones as [$condicion, $estadoCondicion]) {
                    $this->insertar('evaluaciones_condiciones_regla', ['cod_evaluacion_regla' => $er, 'cod_condicion_regla' => $condicion,
                        'estado_condicion' => $estadoCondicion, 'fecha_hora_evaluacion' => $fecha]);
                }
            }
            $db->table('evaluacion_criterios')->where('cod_evaluacion_criterio', $criterio)->update([
                'motivo_evaluabilidad' => 'SIM-TRACE-1 sha256='.CodecInstantanea::huella($payload)
                    .'; decisiones descriptivas de la ejecución original, sin acreditación A/P.']);
        }
    }

    /** Retorna decisiones conservadas y comprueba la huella; no interpreta fuentes actuales. */
    public function leer(string $criterio): ?array
    {
        $db = $this->contexto->db();
        $traza = \App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\BloquesExpertos::consulta('trazas_inferencia', $this->contexto->conexion)->where('cod_evaluacion_criterio', $criterio)->first();
        if ($traza === null) {
            return null;
        }
        if ($traza->estado_inferencia !== 'PRUEBA_FINALIZADA' || $traza->fecha_hora_inicio === null
            || $traza->fecha_hora_fin === null || $traza->fecha_hora_fin < $traza->fecha_hora_inicio) {
            throw new DomainException('Cabecera de traza local no finalizada o incoherente.');
        }
        $motivo = $db->table('evaluacion_criterios')->where('cod_evaluacion_criterio', $criterio)->value('motivo_evaluabilidad');
        if (! is_string($motivo) || ! preg_match('/^SIM-TRACE-1 sha256=([a-f0-9]{64});/', $motivo, $m)) {
            throw new DomainException('Traza local sin contrato íntegro.');
        }
        $reglas = $db->table('evaluaciones_reglas as e')->join('reglas_expertas as r', 'r.cod_regla_experta', '=', 'e.cod_regla_experta')
            ->where('e.cod_traza_inferencia', $traza->cod_traza_inferencia)->orderBy('r.cod_regla_experta')
            ->select(['e.cod_evaluacion_regla', 'r.cod_regla_experta', 'r.codigo_regla', 'r.nombre', 'e.estado_regla'])->get();
        $filas = $db->table('evaluaciones_condiciones_regla as e')->join('evaluaciones_reglas as r', 'r.cod_evaluacion_regla', '=', 'e.cod_evaluacion_regla')
            ->where('r.cod_traza_inferencia', $traza->cod_traza_inferencia)->orderBy('e.cod_condicion_regla')
            ->get(['e.cod_evaluacion_regla', 'e.cod_condicion_regla', 'e.estado_condicion'])->groupBy('cod_evaluacion_regla');
        $payload = $vista = [];
        foreach ($reglas as $r) {
            $condiciones = $filas->get($r->cod_evaluacion_regla, collect())->map(fn ($c) => [$c->cod_condicion_regla, $c->estado_condicion])->all();
            $payload[] = [$r->cod_regla_experta, $r->estado_regla, $condiciones, $this->definicion($r->cod_regla_experta)];
            $vista[] = ['codigo' => $r->codigo_regla, 'nombre' => $r->nombre, 'estado' => $r->estado_regla, 'condiciones' => $condiciones];
        }
        // SIM-TRACE-1 se creó antes de las cinco columnas. Se verifican únicamente las dos formas físicas conocidas.
        $integra = hash_equals($m[1], CodecInstantanea::huella($payload));
        if (! $integra) {
            $originales = $db->table('reglas_expertas')->whereIn('cod_regla_experta', array_column($payload, 0))->get()->keyBy('cod_regla_experta');
            $conGobierno = array_map(static function ($r) use ($originales): array {
                $r[3][0] = (array) $originales->get($r[0]);

                return $r;
            }, $payload);
            $integra = hash_equals($m[1], CodecInstantanea::huella($conGobierno));
        }
        if (! $integra) {
            throw new DomainException('Traza local incompleta o alterada.');
        }

        return ['id' => $traza->cod_traza_inferencia, 'estado' => $traza->estado_inferencia,
            'inicio' => $traza->fecha_hora_inicio, 'fin' => $traza->fecha_hora_fin, 'payload' => $payload, 'reglas' => $vista];
    }

    /** Incluye el significado original en la huella, de modo que editar el catálogo invalida la lectura. */
    private function definicion(string $regla): array
    {
        $db = $this->contexto->db();

        return [array_diff_key((array) $db->table('reglas_expertas')->where('cod_regla_experta', $regla)->sole(), array_flip(self::GOBIERNO)),
            $db->table('condiciones_regla_experta')->where('cod_regla_experta', $regla)->orderBy('cod_condicion_regla')->get()->map(fn ($r) => (array) $r)->all(),
            $db->table('consecuencias_regla_experta')->where('cod_regla_experta', $regla)->orderBy('cod_consecuencia_regla')->get()->map(fn ($r) => (array) $r)->all()];
    }

    private function insertar(string $tabla, array $datos): string
    {
        $pk = CatalogoCodigos::clave($tabla);
        $id = (new GeneradorCodigo)->siguiente($tabla, $this->contexto->conexion);
        \App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\BloquesExpertos::insertar($tabla, [$pk => $id] + $datos, $this->contexto->conexion);

        return $id;
    }
}
