<?php

namespace App\Modulos\Alertas\Aplicacion\Consultas;

use App\Compartido\Aplicacion\Formato\TextoVisible;
use App\Models\Personal;
use App\Models\User;
use App\Modulos\Alertas\Aplicacion\Formato\LenguajeAlerta;

final class ConsultarResumenEquipoAlertas
{
    public function consultar(User $usuario, string $residente = ''): array
    {
        $foco = app(ExperienciaAlertas::class)->paraUsuario($usuario)['foco'];
        if (! in_array($foco, ['supervision', 'auditoria'], true)) {
            return [];
        }
        $base = app(BandejaAlertas::class)->consulta($usuario)->when($residente !== '', fn ($q) => $q->where('cod_residente', $residente));
        if ($foco === 'supervision') {
            $grupos = $base->abiertas()->select('cod_personal_responsable')->selectRaw('COUNT(*) as total')
                ->groupBy('cod_personal_responsable')->orderByDesc('total')->orderBy('cod_personal_responsable')->limit(8)->get();
            $personas = Personal::whereIn('cod_personal', $grupos->pluck('cod_personal_responsable')->filter())
                ->get(['cod_personal', 'nombres', 'apellido_paterno', 'apellido_materno', 'profesion'])->keyBy('cod_personal');

            return ['responsables' => $grupos->map(fn ($grupo) => [
                'filtro' => $grupo->cod_personal_responsable ? 'PERSONAL:'.$grupo->cod_personal_responsable : 'SIN_RESPONSABLE',
                'nombre' => LenguajeAlerta::responsable($personas->get($grupo->cod_personal_responsable)), 'total' => (int) $grupo->total,
            ])->all()];
        }
        if ($foco === 'auditoria') {
            return ['fuentes' => $base->select('modulo', 'generacion')->selectRaw('COUNT(*) as total')
                ->groupBy('modulo', 'generacion')->orderByDesc('total')->orderBy('modulo')->orderBy('generacion')->limit(8)->get()
                ->map(fn ($grupo) => ['origen' => TextoVisible::sinCodigosInternos(str_replace('_', ' ', $grupo->modulo ?: 'Sin origen registrado')),
                    'generacion' => match ($grupo->generacion) {
                        'MANUAL' => 'Registro manual', 'AUTOMATICA', 'AUTOMATICO' => 'Detección registrada', default => 'Generación sin registrar'
                    },
                    'total' => (int) $grupo->total])->all()];
        }

        return [];
    }
}
