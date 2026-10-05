<?php

namespace App\Backend\Modulos\Clinica\SignosVitales;

use App\Backend\Modulos\Clinica\SignosVitales\Resultados\EvaluacionSignosVitales;
use App\Backend\Modulos\Clinica\SignosVitales\Tipos\ComportamientoAlerta;
use App\Models\Alerta;
use App\Models\SignoVital;
use App\Models\User;
use Illuminate\Support\Str;

final class ServicioDecisionAlertaClinica
{
    /** Debe ejecutarse dentro de la transacción del registro. */
    public function crearSiCorresponde(SignoVital $signo, EvaluacionSignosVitales $evaluacion, User $autor): ?Alerta
    {
        $criticos = array_values(array_filter($evaluacion->resultados,
            fn ($resultado) => $resultado->comportamientoAlerta === ComportamientoAlerta::AUTOMATICA_AL_CONFIRMAR));
        if ($criticos === []) {
            return null;
        }

        $existente = Alerta::query()->where('modulo', 'SIGNOS')
            ->where('cod_registro', $signo->cod_signo)->first();
        if ($existente) {
            return $existente;
        }

        $detalle = implode("\n", array_map(function ($resultado): string {
            return implode(' ', array_filter([
                str_replace('_', ' ', ucfirst($resultado->variable)).': '.$resultado->valor.' '.$resultado->unidad.'.',
                $resultado->rangoOUmbral ? 'Umbral utilizado: '.$resultado->rangoOUmbral.'.' : null,
                $resultado->referenciaUtilizada ? 'Referencia: '.$resultado->referenciaUtilizada.'.' : null,
                $resultado->explicacion,
                $resultado->recomendacion ? 'Recomendación: '.$resultado->recomendacion : null,
            ]));
        }, $criticos));
        $alerta = Alerta::create([
            'cod_residente' => $signo->cod_residente,
            'cod_personal_responsable' => $signo->cod_personal,
            'tipo' => 'SIGNOS_VITALES_CRITICOS',
            'prioridad' => 'CRITICO',
            'modulo' => 'SIGNOS',
            'cod_registro' => $signo->cod_signo,
            'titulo' => 'Signos vitales: valor crítico registrado',
            'descripcion' => $detalle,
            'fecha_hora' => $signo->fecha_hora,
            'generacion' => 'AUTOMATICA',
            'estado' => 'ABIERTA',
        ]);
        $alerta->eventos()->create([
            'cod_evento_alerta' => 'EVA_'.strtoupper(Str::random(10)),
            'cod_usuario' => $autor->cod_usuario,
            'tipo_evento' => 'CREACION',
            'estado_nuevo' => 'ABIERTA',
            'fecha_hora' => now(),
            'descripcion' => 'Alerta automática vinculada al registro '.$signo->cod_signo.'.',
        ]);

        return $alerta;
    }
}
