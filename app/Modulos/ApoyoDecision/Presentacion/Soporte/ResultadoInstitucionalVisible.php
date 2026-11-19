<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Presentacion\Soporte;

use App\Modulos\ApoyoDecision\Aplicacion\Contratos\ResultadoInstitucionalConservado as R;
use MotorExperto\conservacion\CodecInstantanea;

/** Lenguaje clínico de pantalla sin alterar el documento ni los valores originales guardados. */
final class ResultadoInstitucionalVisible
{
    public static function narrativa(string $original): string
    {
        return preg_replace_callback('/^(Limitación de [^:\n]+: |Motivo concurrente de abstención: |Registro excluido en la adquisición: )([A-Z][A-Z0-9_]+(?::[^\n.]+)?)\.$/m',
            fn ($m) => $m[1].MotivoPetersenVisible::explicar($m[2]), $original);
    }

    public static function valor(array $h): string
    {
        $v = $h['valor'];
        if ($v === null) { return 'Sin valor registrado'; }
        if (is_bool($v)) { return $v ? 'Sí' : 'No'; }
        if ($h['concepto'] === 'elemento') { return R::NOMBRES[$v] ?? (string) $v; }
        if ($h['concepto'] === 'estado') { return R::ESTADOS[$v] ?? (string) $v; }
        return (string) $v;
    }

    /** Solo presentación del resultado verificado: no reinterpreta ni puntúa estados. */
    public static function resumen(array $ri): array
    {
        $grupos = ['A' => 'alterado', 'P' => 'esperado', 'U' => 'insuficiente', 'I' => 'no_interpretable', 'X' => 'contradictorio'];
        $areas = [];
        foreach (R::DOMINIOS as $codigo => $_) {
            $estado = $ri['dominios'][$codigo];
            $areas[] = ['codigo' => $codigo, 'nombre' => R::NOMBRES[$codigo], 'estado' => R::ESTADOS[$estado],
                'grupo' => $grupos[$estado]];
        }
        $alteradas = array_values(array_column(array_filter($areas, fn ($a) => $a['grupo'] === 'alterado'), 'nombre'));
        $esperadas = count(array_filter($areas, fn ($a) => $a['grupo'] === 'esperado'));
        $requisitos = [];
        foreach ($ri['requisitos'] as $codigo => $estado) {
            $requisitos[] = ['codigo' => $codigo, 'nombre' => R::NOMBRES[$codigo], 'estado' => R::ESTADOS[$estado],
                'grupo' => $estado === 'S' ? 'esperado' : ($estado === 'N' ? 'alterado' : ($grupos[$estado] ?? 'insuficiente'))];
        }
        $tipos = ['RESPUESTA_INSTRUMENTAL' => 'Respuesta instrumental', 'APLICACION_INSTRUMENTAL' => 'Aplicación instrumental', 'JUICIO_PETERSEN' => 'Valoración original'];
        $hechos = $fuentes = [];
        foreach ($ri['hechos'] as $h) {
            if (str_starts_with($h['concepto'], 'lectura:')) {
                continue;
            }
            $tipo = $tipos[$h['tipo']] ?? 'Fuente original';
            $fuentes[$h['tipo']][$h['origen']] = true;
            $hechos[] = ['id' => $h['id'], 'tipo' => $tipo, 'dato' => ucfirst(str_replace('_', ' ', $h['concepto'])),
                'valor' => self::valor($h), 'fecha' => CodecInstantanea::leerFecha('fecha', (object) $h['fecha'])->format('d/m/Y H:i')];
        }
        $reglas = array_merge(...array_column($ri['criterios'], 'reglas'));
        return ['areas' => $areas, 'alteradas' => $alteradas, 'esperadas' => $esperadas,
            'sin_conclusion' => count($areas) - count($alteradas) - $esperadas, 'requisitos' => $requisitos,
            'hechos' => $hechos, 'tipos_hechos' => array_values(array_unique(array_column($hechos, 'tipo'))),
            'aplicaciones' => count($fuentes['APLICACION_INSTRUMENTAL'] ?? []),
            'respuestas' => count($fuentes['RESPUESTA_INSTRUMENTAL'] ?? []), 'juicios' => count($fuentes['JUICIO_PETERSEN'] ?? []),
            'reglas' => count($reglas)];
    }
}
