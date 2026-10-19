<?php

declare(strict_types=1);

namespace MotorExperto\dominio\instrumentos;

use DomainException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Adquisición instrumental.
 * Traduce preguntas identificadas explícitamente a medidas del método HVLT-R.
 * Conserva cada bruto y su procedencia; una etiqueta nominal nunca se convierte en un ensayo.
 */
final class ExtractorCapturaHvlt
{
    public const ENTRADAS = ['ensayo_1' => 'ENTERO', 'ensayo_2' => 'ENTERO', 'ensayo_3' => 'ENTERO',
        'recuerdo_diferido' => 'ENTERO', 'demora_minutos' => 'NUMERO', 'escolaridad_anios' => 'ENTERO',
        'espanol_nativo' => 'LOGICO', 'nacimiento_bolivia' => 'LOGICO', 'residencia_bolivia' => 'LOGICO',
        'lectoescritura' => 'LOGICO', 'administracion_estandarizada' => 'LOGICO'];

    public function extraer(array $perfil, array $aplicacion, array $respuestas, ?int $edad): array
    {
        if (($perfil['metodo'] ?? null) !== NormalizadorHvltBolivia2015::METODO
            || ! is_string($perfil['id'] ?? null) || $perfil['id'] === ''
            || ! is_array($perfil['selectores'] ?? null) || array_diff(array_keys($perfil['selectores']), array_keys(self::ENTRADAS))) {
            throw new DomainException('Perfil de captura instrumental fuera de contrato.');
        }
        foreach (['cod_instrumento', 'codigo_instrumento', 'version_instrumento'] as $campo) {
            if (! is_string($perfil[$campo] ?? null) || $perfil[$campo] === '' || ($aplicacion[$campo] ?? null) !== $perfil[$campo]) {
                throw new DomainException('El perfil no identifica exactamente el instrumento y su versión.');
            }
        }
        $captura = ['instrumento' => 'HVLT-R', 'forma' => 5, 'idioma' => 'es', 'protocolo' => $perfil['metodo'], 'edad_anios' => $edad];
        $fuentes = $motivos = $medidas = $preguntas = [];
        foreach (self::ENTRADAS as $entrada => $tipo) {
            $selector = $perfil['selectores'][$entrada] ?? null;
            $medidas[$entrada] = null;
            if ($selector === null) {
                $motivos[] = 'SIN_SELECTOR:'.$entrada;

                continue;
            }
            $campo = $tipo === 'LOGICO' ? 'valor_logico' : 'valor_numero';
            if (! is_array($selector) || array_diff(array_keys($selector), ['cod_pregunta', 'campo']) || count($selector) !== 2
                || ! is_string($selector['cod_pregunta'] ?? null) || $selector['cod_pregunta'] === '' || $selector['campo'] !== $campo
                || isset($preguntas[$selector['cod_pregunta']])) {
                throw new DomainException('Selector ambiguo, repetido o incompatible con la medida.');
            }
            $preguntas[$selector['cod_pregunta']] = true;
            $filas = array_values(array_filter($respuestas, fn ($r) => $r['cod_pregunta'] === $selector['cod_pregunta']));
            if (count($filas) !== 1 || ($filas[0]['coherente'] ?? false) !== true) {
                $motivos[] = 'RESPUESTA_AUSENTE_O_INCOMPATIBLE:'.$entrada;

                continue;
            }
            $r = $filas[0];
            $bruto = $r[$campo] ?? null;
            $tipoPreguntaValido = $tipo === 'LOGICO' ? in_array($r['tipo_respuesta'] ?? null, ['LOGICO', 'BOOLEANO'], true)
                : in_array($r['tipo_respuesta'] ?? null, ['NUMERO', 'NUMERICO', 'NUMERICA'], true);
            $otrosValores = array_diff(['cod_opcion', 'valor_numero', 'valor_logico', 'valor_texto'], [$campo]);
            $ambiguo = count(array_filter($otrosValores, fn ($otro) => ($r[$otro] ?? null) !== null && ($r[$otro] ?? null) !== '')) > 0;
            $valor = $tipoPreguntaValido && ! $ambiguo ? $this->convertir($bruto, $tipo) : null;
            $medidas[$entrada] = $valor;
            $fuentes[$entrada] = ['aplicacion' => $aplicacion['cod_aplicacion'], 'respuesta' => $r['cod_respuesta'],
                'pregunta' => $r['cod_pregunta'], 'campo' => $campo, 'bruto' => $bruto, 'valor' => $valor];
            if ($valor === null) {
                $motivos[] = 'MEDICION_AUSENTE_O_TIPO_INVALIDO:'.$entrada;
            }
        }
        $captura += $medidas;
        $captura['ensayos'] = [$medidas['ensayo_1'], $medidas['ensayo_2'], $medidas['ensayo_3']];
        $normalizacion = (new RegistroMetodosInstrumentales)->ejecutar($perfil['metodo'], $captura);

        return ['perfil' => $perfil['id'], 'metodo' => $perfil['metodo'], 'captura' => $captura,
            'fuentes_entradas' => $fuentes, 'motivos_captura' => $motivos, 'normalizacion' => $normalizacion];
    }

    private function convertir(mixed $bruto, string $tipo): int|float|bool|null
    {
        if ($tipo === 'LOGICO') {
            return match (true) {
                $bruto === true || $bruto === 1 || $bruto === '1' => true,
                $bruto === false || $bruto === 0 || $bruto === '0' => false,
                default => null,
            };
        }
        if ((! is_int($bruto) && ! is_float($bruto) && ! is_string($bruto)) || ! is_numeric($bruto)) {
            return null;
        }
        $numero = (float) $bruto;
        if (! is_finite($numero) || $numero < 0 || $numero > 99999999) {
            return null;
        }

        return $tipo === 'ENTERO' ? (floor($numero) === $numero ? (int) $numero : null) : $numero;
    }
}
