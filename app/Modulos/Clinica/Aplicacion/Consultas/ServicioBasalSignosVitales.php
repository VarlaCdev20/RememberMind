<?php

namespace App\Modulos\Clinica\Aplicacion\Consultas;

use App\Models\SignoVital;
use Carbon\CarbonInterface;

final class ServicioBasalSignosVitales
{
    /** Historial descriptivo; no calcula diagnósticos ni objetivos individuales. */
    public function lecturasRecientes(string $codResidente, int $limite = 5, ?CarbonInterface $fechaMedicion = null): array
    {
        return SignoVital::query()->where('cod_residente', $codResidente)
            ->whereIn('estado', ['ACTIVO', 'VIGENTE'])
            ->where('fecha_hora', '<=', $fechaMedicion ?? now())
            ->orderByDesc('fecha_hora')->limit(min(max($limite, 1), 20))
            ->get(['cod_signo', 'fecha_hora', 'presion_sistolica', 'presion_diastolica',
                'frecuencia_cardiaca', 'frecuencia_respiratoria', 'temperatura',
                'saturacion_oxigeno', 'glucemia'])->toArray();
    }
}
