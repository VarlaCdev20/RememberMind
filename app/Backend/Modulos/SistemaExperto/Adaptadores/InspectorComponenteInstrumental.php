<?php

namespace App\Backend\Modulos\SistemaExperto\Adaptadores;

use DomainException;

/** D-144: inspección estructural; no consume totales/clasificación ni texto de dominio. */
final class InspectorComponenteInstrumental
{
    /** Patrones exactos de un paquete técnico. No almacena contenido clínico ni ejecuta fórmulas. */
    public function inspeccionar(array $aplicacion, array $instrumento, array $preguntas, array $opciones, array $respuestas, array $paquete): array
    {
        if (($paquete['solo_pruebas_tecnicas'] ?? false) !== true || ! $paquete['preguntas_requeridas'] || ! $paquete['patrones']) {
            throw new DomainException('Paquete instrumental no validado para ejecución técnica.');
        }
        if (($instrumento['version'] ?? null) === null || $instrumento['version'] === '' || ($paquete['version_instrumento'] ?? null) === null || $paquete['version_instrumento'] === ''
            || $paquete['instrumento'] !== $instrumento['cod_instrumento'] || $aplicacion['cod_instrumento'] !== $instrumento['cod_instrumento']
            || $paquete['version_instrumento'] !== ($instrumento['version'] ?? null) || $paquete['componente'] === '') {
            return ['verificado' => false, 'motivos' => ['INSTRUMENTO_VERSION_COMPONENTE_NO_AUTORIZADOS'], 'valor' => null];
        }
        $porPregunta = [];
        foreach ($respuestas as $r) {
            $id = $r['cod_pregunta'];
            if ($r['cod_aplicacion'] !== $aplicacion['cod_aplicacion'] || isset($porPregunta[$id])) {
                throw new DomainException('Respuesta duplicada o de otra aplicación.');
            }
            $pregunta = $preguntas[$id] ?? throw new DomainException('Pregunta ausente.');
            if ($pregunta['cod_instrumento'] !== $instrumento['cod_instrumento']) {
                throw new DomainException('Pregunta ajena al instrumento.');
            }
            if (($r['cod_opcion'] ?? null) !== null && (($opciones[$r['cod_opcion']]['cod_pregunta'] ?? null) !== $id)) {
                throw new DomainException('Opción ajena a la pregunta.');
            }
            $porPregunta[$id] = $r;
        }
        $valores = [];
        foreach ($paquete['preguntas_requeridas'] as $id => $contrato) {
            if (! isset($porPregunta[$id])) {
                return ['verificado' => false, 'motivos' => ['COMPONENTE_INCOMPLETO'], 'valor' => null];
            }
            $campo = $contrato['campo'];
            if (! in_array($campo, ['valor_texto', 'valor_numero', 'valor_logico', 'cod_opcion'], true)) {
                throw new DomainException('Campo de respuesta fuera de contrato.');
            }
            if ($preguntas[$id]['tipo_respuesta'] !== $contrato['tipo_respuesta']) {
                return ['verificado' => false, 'motivos' => ['TIPO_PREGUNTA_INCOMPATIBLE'], 'valor' => null];
            }
            $valor = $porPregunta[$id][$campo] ?? null;
            if (get_debug_type($valor) !== $contrato['tipo'] || ! in_array($valor, $contrato['valores_permitidos'], true)) {
                return ['verificado' => false, 'motivos' => ['RESPUESTA_NO_INTERPRETABLE'], 'valor' => null];
            }
            $valores[$id] = $valor;
        }
        ksort($valores, SORT_STRING);
        ksort($porPregunta, SORT_STRING);
        $coincidencias = [];
        foreach ($paquete['patrones'] as $patron) {
            $patronValores = $patron['respuestas'];
            ksort($patronValores, SORT_STRING);
            if ($patronValores === $valores) {
                $coincidencias[] = $patron;
            }
        }
        if (count($coincidencias) > 1) {
            throw new DomainException('Método instrumental ambiguo.');
        }
        if (! $coincidencias) {
            return ['verificado' => false, 'motivos' => ['SIN_METODO_EXACTO_AUTORIZADO'], 'valor' => null];
        }

        return ['verificado' => true, 'motivos' => [], 'valor' => $coincidencias[0]['valor_fuente'],
            'procedencia' => ['instrumento' => $instrumento['cod_instrumento'], 'version' => $instrumento['version'], 'componente' => $paquete['componente'],
                'aplicacion' => $aplicacion['cod_aplicacion'], 'preguntas' => array_keys($valores), 'metodo' => $paquete['metodo'],
                'respuestas' => array_column($porPregunta, 'cod_respuesta')]];
    }
}
