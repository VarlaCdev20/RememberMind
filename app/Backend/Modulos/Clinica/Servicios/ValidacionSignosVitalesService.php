<?php

namespace App\Backend\Modulos\Clinica\Servicios;

use Illuminate\Contracts\Validation\Validator;

class ValidacionSignosVitalesService
{
    /** Límites de captura del registro nuevo; se entregan también al formulario. */
    public static function limitesRegistro(): array
    {
        return [
            'sis' => ['min' => self::PAS_MIN, 'max' => self::PAS_MAX, 'decimales' => 0],
            'dia' => ['min' => self::PAD_MIN, 'max' => self::PAD_MAX, 'decimales' => 0],
            'fc' => ['min' => self::FC_MIN, 'max' => self::FC_MAX, 'decimales' => 0],
            'fr' => ['min' => self::FR_MIN, 'max' => self::FR_MAX, 'decimales' => 0],
            'temp' => ['min' => self::TEMP_MIN, 'max' => self::TEMP_MAX, 'decimales' => 1],
            'sat' => ['min' => 1, 'max' => self::SPO2_MAX, 'decimales' => 2],
            'glucosa' => ['min' => 1, 'max' => 999999.99, 'decimales' => 2],
        ];
    }

    public static function reglasRegistro(): array
    {
        $campos = [
            'presion_sistolica' => 'sis', 'presion_diastolica' => 'dia',
            'frecuencia_cardiaca' => 'fc', 'frecuencia_respiratoria' => 'fr',
            'temperatura' => 'temp', 'saturacion_oxigeno' => 'sat', 'glucemia' => 'glucosa',
        ];
        $limites = self::limitesRegistro();
        $reglas = [];
        foreach ($campos as $campo => $clave) {
            $limite = $limites[$clave];
            $reglas[$campo] = ['nullable', $limite['decimales'] === 0 ? 'integer' : 'numeric'];
            if ($limite['decimales'] > 0) {
                $reglas[$campo][] = 'decimal:0,'.$limite['decimales'];
            }
            $reglas[$campo][] = 'min:'.$limite['min'];
            $reglas[$campo][] = 'max:'.$limite['max'];
        }
        $reglas['observacion'] = ['nullable', 'string', 'max:5000'];
        $reglas['fecha_hora'] = ['sometimes', 'required', 'date', 'before_or_equal:now'];

        return $reglas;
    }

    public static function mensajesRegistro(): array
    {
        return [
            'fecha_hora.required' => 'Indica cuándo se realizó la medición.',
            'fecha_hora.date' => 'Introduce una fecha y hora válidas para la medición.',
            'fecha_hora.before_or_equal' => 'La medición no puede tener una fecha u hora futura.',
            'integer' => 'Revisa el valor ingresado. Esta medición requiere un número entero.',
            'numeric' => 'Revisa el valor ingresado. Introduce un número válido.',
            'decimal' => 'Revisa el valor ingresado. Comprueba los decimales de esta medición.',
            'min' => 'Revisa el valor ingresado. Está por debajo del intervalo técnico admitido para esta medición.',
            'max' => 'Revisa el valor ingresado. Está por encima del intervalo técnico admitido para esta medición.',
            'temperatura.min' => 'Revisa el valor ingresado. Está fuera del intervalo admitido para una medición de temperatura corporal. Comprueba que no falte un dígito.',
            'temperatura.max' => 'Revisa el valor ingresado. Está fuera del intervalo admitido para una medición de temperatura corporal.',
            'saturacion_oxigeno.min' => 'La saturación de oxígeno debe ser mayor que 0 %.',
            'saturacion_oxigeno.max' => 'La saturación de oxígeno no puede superar 100 %.',
            'glucemia.min' => 'Revisa el valor ingresado. La glucemia debe ser mayor que cero.',
            'glucemia.max' => 'Revisa el valor ingresado. La glucemia está fuera del intervalo técnico admitido.',
            'observacion.max' => 'Las observaciones no pueden superar 5000 caracteres.',
        ];
    }
    // Límites técnicos de captura definidos para Enfermería. Los umbrales
    // clínicos de alerta se evalúan por separado y no deben impedir registrar
    // una medición real tomada al residente.
    public const PAS_MIN = 1;

    public const PAS_MAX = 400;

    public const PAD_MIN = 1;

    public const PAD_MAX = 400;

    public const FC_MIN = 1;

    public const FC_MAX = 300;

    public const FR_MIN = 1;

    public const FR_MAX = 100;

    public const TEMP_MIN = 25.0;

    public const TEMP_MAX = 45.0;

    public const SPO2_MIN = 0;

    public const SPO2_MAX = 100;

    public const GLUCOSA_MIN = 0.0;

    public const PESO_MIN = 20.0;

    public const PESO_MAX = 300.0;

    public const TALLA_CM_MIN = 50.0;

    public const TALLA_CM_MAX = 240.0;

    public const DOLOR_MIN = 0;

    public const DOLOR_MAX = 10;

    /**
     * Normaliza la talla canónica en centímetros (redondeo a 1 decimal).
     */
    public static function normalizarTalla(?float $talla): ?float
    {
        if ($talla === null || $talla <= 0) {
            return null;
        }

        return round($talla, 1);
    }

    /**
     * Calcula estrictamente el IMC en servidor convirtiendo la talla canonica (cm) a metros (kg/m²).
     */
    public static function calcularImc(?float $peso, ?float $talla): ?float
    {
        if (! $peso || ! $talla || $peso < self::PESO_MIN || $peso > self::PESO_MAX) {
            return null;
        }

        $tallaCm = self::normalizarTalla($talla);
        if (! $tallaCm || $tallaCm < self::TALLA_CM_MIN || $tallaCm > self::TALLA_CM_MAX) {
            return null;
        }

        $tallaM = $tallaCm / 100.0;

        return round($peso / ($tallaM * $tallaM), 1);
    }

    /**
     * Devuelve las reglas de validación comunes para signos vitales.
     */
    public static function reglas(): array
    {
        return [
            'presion_sistolica' => 'nullable|integer|min:'.self::PAS_MIN.'|max:'.self::PAS_MAX,
            'presion_diastolica' => 'nullable|integer|min:'.self::PAD_MIN.'|max:'.self::PAD_MAX,
            'frecuencia_cardiaca' => 'nullable|integer|min:'.self::FC_MIN.'|max:'.self::FC_MAX,
            'frecuencia_respiratoria' => 'nullable|integer|min:'.self::FR_MIN.'|max:'.self::FR_MAX,
            'temperatura' => 'nullable|numeric|min:'.self::TEMP_MIN.'|max:'.self::TEMP_MAX,
            'saturacion' => 'nullable|integer|min:'.self::SPO2_MIN.'|max:'.self::SPO2_MAX,
            'glucosa' => 'nullable|numeric|min:'.self::GLUCOSA_MIN,
            'peso' => 'nullable|numeric|min:'.self::PESO_MIN.'|max:'.self::PESO_MAX,
            'talla' => 'nullable|numeric|min:'.self::TALLA_CM_MIN.'|max:'.self::TALLA_CM_MAX,
            'dolor' => 'nullable|integer|min:'.self::DOLOR_MIN.'|max:'.self::DOLOR_MAX,
            'observacion' => 'nullable|string|max:5000',
        ];
    }

    /**
     * Mensajes descriptivos en español para los signos vitales.
     */
    public static function mensajes(): array
    {
        return [
            'presion_sistolica.min' => 'La presión sistólica debe ser de al menos '.self::PAS_MIN.' mmHg.',
            'presion_sistolica.max' => 'La presión sistólica no puede exceder '.self::PAS_MAX.' mmHg.',
            'presion_diastolica.min' => 'La presión diastólica debe ser de al menos '.self::PAD_MIN.' mmHg.',
            'presion_diastolica.max' => 'La presión diastólica no puede exceder '.self::PAD_MAX.' mmHg.',
            'frecuencia_cardiaca.min' => 'La frecuencia cardíaca debe ser de al menos '.self::FC_MIN.' bpm.',
            'frecuencia_cardiaca.max' => 'La frecuencia cardíaca no puede exceder '.self::FC_MAX.' bpm.',
            'frecuencia_respiratoria.min' => 'La frecuencia respiratoria debe ser de al menos '.self::FR_MIN.' rpm.',
            'frecuencia_respiratoria.max' => 'La frecuencia respiratoria no puede exceder '.self::FR_MAX.' rpm.',
            'temperatura.min' => 'La temperatura debe ser de al menos '.self::TEMP_MIN.' °C.',
            'temperatura.max' => 'La temperatura no puede exceder '.self::TEMP_MAX.' °C.',
            'saturacion.min' => 'La saturación de oxígeno debe ser de al menos '.self::SPO2_MIN.'%.',
            'saturacion.max' => 'La saturación de oxígeno no puede exceder '.self::SPO2_MAX.'%.',
            'glucosa.min' => 'La glucosa debe ser de al menos '.self::GLUCOSA_MIN.' mg/dL.',
            'peso.min' => 'El peso debe ser de al menos '.self::PESO_MIN.' kg.',
            'peso.max' => 'El peso no puede exceder '.self::PESO_MAX.' kg.',
            'talla.min' => 'La talla debe ser de al menos '.self::TALLA_CM_MIN.' cm.',
            'talla.max' => 'La talla no puede exceder '.self::TALLA_CM_MAX.' cm.',
            'dolor.min' => 'La escala de dolor va de 0 a 10.',
            'dolor.max' => 'La escala de dolor va de 0 a 10.',
        ];
    }

    /**
     * Valida reglas cruzadas:
     * 1. Si viene sistólica o diastólica, deben venir ambas.
     * 2. Una presión atípica requiere confirmación explícita.
     * 3. Al menos un signo vital debe estar presente.
     */
    public static function validarIntegridadCruzada(
        Validator $validator,
        ?int $sis,
        ?int $dia,
        array $mediciones,
        string $campoErrorPa = 'presion_sistolica',
        string $campoErrorGeneral = 'general',
        bool $presionAtipicaConfirmada = false
    ): void {
        // Pares de PA
        if (($sis !== null && $dia === null) || ($sis === null && $dia !== null)) {
            $validator->errors()->add(
                $campoErrorPa,
                'La presión arterial requiere registrar tanto la sistólica como la diastólica.'
            );
        } elseif ($sis !== null && $dia !== null && $sis <= $dia && ! $presionAtipicaConfirmada) {
            $validator->errors()->add(
                $campoErrorPa,
                'La presión sistólica es menor o igual a la diastólica. Verifique la medición y confirme expresamente si el valor es correcto.'
            );
        }

        // Al menos una medición real
        $tieneAlMenosUna = collect($mediciones)->contains(function ($valor) {
            return $valor !== null && $valor !== '';
        });

        if (! $tieneAlMenosUna) {
            $validator->errors()->add(
                $campoErrorGeneral,
                'Debe registrar al menos un signo vital o medición real.'
            );
        }
    }
}
