<?php

namespace App\Modulos\Clinica\Dominio\Reglas;

final class ClasificacionSignosPanel
{
    public function distribucionPresionSistolica(iterable $signos): array
    {
        $conteos = [
            'Hipotensión (<90)' => 0,
            'Normal (90-139)' => 0,
            'Pre-HTA (140-159)' => 0,
            'HTA I (160-179)' => 0,
            'HTA II (≥180)' => 0,
        ];

        foreach ($signos as $signo) {
            $presion = $signo->presion_sistolica;
            if ($presion > 0 && $presion < 90) {
                $conteos['Hipotensión (<90)']++;
            }
            if ($presion >= 90 && $presion < 140) {
                $conteos['Normal (90-139)']++;
            }
            if ($presion >= 140 && $presion < 160) {
                $conteos['Pre-HTA (140-159)']++;
            }
            if ($presion >= 160 && $presion < 180) {
                $conteos['HTA I (160-179)']++;
            }
            if ($presion >= 180) {
                $conteos['HTA II (≥180)']++;
            }
        }

        return $conteos;
    }

    public function clasificarPresion($sistolica, $diastolica): array
    {
        if ($sistolica === null || $sistolica === '' || $diastolica === null || $diastolica === '') {
            return ['key' => 'sin_datos', 'label' => 'Sin datos', 'message' => 'Presión arterial no registrada.'];
        }

        $sis = (int) $sistolica;
        $dia = (int) $diastolica;

        if ($sis <= $dia) {
            return ['key' => 'requiere_revision', 'label' => 'Confirmación requerida', 'message' => 'La sistólica es menor o igual a la diastólica. Repita la toma y confirme el valor antes de guardarlo.'];
        }

        if ($sis >= 160 || $dia >= 100 || $sis < 90 || $dia < 60) {
            return ['key' => 'requiere_revision', 'label' => 'Requiere revisión', 'message' => 'Valor bajo o elevado. Requiere revisión del personal de salud.'];
        }

        if ($sis >= 140 || $dia >= 90) {
            return ['key' => 'fuera_rango', 'label' => 'Fuera de rango', 'message' => 'Valor elevado según rango referencial.'];
        }

        if (($sis >= 130 && $sis <= 139) || ($dia >= 85 && $dia <= 89)) {
            return ['key' => 'observacion', 'label' => 'En observación', 'message' => 'Valor en observación. Confirmar tendencia.'];
        }

        if ($sis >= 90 && $sis <= 129 && $dia >= 60 && $dia <= 84) {
            return ['key' => 'normal', 'label' => 'Normal', 'message' => 'Presión arterial dentro de rango referencial.'];
        }

        return ['key' => 'fuera_rango', 'label' => 'Fuera de rango', 'message' => 'Valor fuera del rango referencial.'];
    }

    public function clasificarNumero($valor, ?float $normalMin, ?float $normalMax, ?float $observacionMin, ?float $observacionMax, string $unidad): array
    {
        if ($valor === null || $valor === '') {
            return ['key' => 'sin_datos', 'label' => 'Sin datos', 'message' => 'Parámetro no registrado.'];
        }

        $numero = (float) $valor;

        if ($unidad === '%' && $numero < 92) {
            return ['key' => 'requiere_revision', 'label' => 'Requiere revisión', 'message' => 'Saturación baja. Requiere revisión del personal de salud.'];
        }

        if ($unidad === '°C' && ($numero < 36.0 || $numero > 37.5)) {
            return [
                'key' => $numero >= 38.0 ? 'requiere_revision' : 'fuera_rango',
                'label' => $numero >= 38.0 ? 'Requiere revisión' : 'Fuera de rango',
                'message' => 'Temperatura fuera del rango referencial.',
            ];
        }

        if ($unidad === 'lpm' && ($numero < 60 || $numero > 100)) {
            return [
                'key' => ($numero < 50 || $numero > 120) ? 'requiere_revision' : 'fuera_rango',
                'label' => ($numero < 50 || $numero > 120) ? 'Requiere revisión' : 'Fuera de rango',
                'message' => 'Frecuencia cardíaca fuera del rango referencial.',
            ];
        }

        if ($unidad === 'rpm' && ($numero < 12 || $numero > 20)) {
            return [
                'key' => ($numero < 10 || $numero > 24) ? 'requiere_revision' : 'fuera_rango',
                'label' => ($numero < 10 || $numero > 24) ? 'Requiere revisión' : 'Fuera de rango',
                'message' => 'Frecuencia respiratoria fuera del rango referencial.',
            ];
        }

        if ($normalMin !== null && $normalMax !== null && $numero >= $normalMin && $numero <= $normalMax) {
            return ['key' => 'normal', 'label' => 'Normal', 'message' => 'Valor dentro del rango referencial.'];
        }

        if ($normalMin !== null && $normalMax === null && $numero >= $normalMin) {
            return ['key' => 'normal', 'label' => 'Normal', 'message' => 'Valor dentro del rango referencial.'];
        }

        if ($observacionMin !== null && $observacionMax !== null && $numero >= $observacionMin && $numero <= $observacionMax) {
            return ['key' => 'observacion', 'label' => 'En observación', 'message' => 'Valor en observación. Confirmar tendencia.'];
        }

        return ['key' => 'fuera_rango', 'label' => 'Fuera de rango', 'message' => 'Valor fuera del rango referencial.'];
    }

    public function agregarEvaluacion(array &$evaluaciones, array $estado, string $campo): void
    {
        if ($estado['key'] === 'sin_datos') {
            return;
        }

        $estado['campo'] = $campo;
        $evaluaciones[] = $estado;
    }

    public function agregarAlerta(array &$alertas, array $estado, string $campo): void
    {
        if (in_array($estado['key'], ['observacion', 'fuera_rango', 'requiere_revision'], true)) {
            $alertas[] = [
                'campo' => $campo,
                'mensaje' => $estado['message'],
            ];
        }
    }
}
