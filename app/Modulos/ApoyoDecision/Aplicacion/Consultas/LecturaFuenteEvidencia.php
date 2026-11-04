<?php

namespace App\Modulos\ApoyoDecision\Aplicacion\Consultas;

use App\Models\AplicacionInstrumento;
use App\Models\ControlCognitivo;
use App\Models\EvaluacionExperta;
use App\Models\EvidenciaEvaluacion;
use App\Models\Residente;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class LecturaFuenteEvidencia
{
    public function consultar(User $user, Residente $residente, string $evaluacion, string $evidencia): array
    {
        Gate::forUser($user)->authorize('consultarResultados', [EvaluacionExperta::class, $residente]);
        $e = EvidenciaEvaluacion::query()->whereKey($evidencia)->where('cod_evaluacion_experta', $evaluacion)
            ->whereHas('evaluacion', fn ($q) => AlcanceLecturaExperta::limitar($q->where('cod_residente', $residente->getKey())))
            ->with(['evaluacion', 'mapeoVariableFuente.fuente'])->firstOrFail();
        $mapa = $e->mapeoVariableFuente;
        abort_unless($mapa->cod_version_modelo === $e->evaluacion->cod_version_modelo && $mapa->fuente->cod_version_modelo === $e->evaluacion->cod_version_modelo, 404);
        $tabla = $mapa->fuente->tabla_raiz;
        if ($tabla === 'controles_cognitivos' && $user->checkPermissionTo('controles_cognitivos.ver', 'web')) {
            $campos = ['memoria_reciente', 'memoria_remota', 'repite_preguntas', 'olvida_indicaciones', 'cambio_cognitivo'];
            if (! in_array($mapa->campo_valor, $campos, true)) {
                return ['estado' => 'El campo fuente requiere un contrato específico de consulta.'];
            }
            $registro = FuentesValoracionOperativa::limitar(ControlCognitivo::query())->whereKey($e->cod_registro_fuente)->where('cod_residente', $residente->getKey())->first();

            return $registro ? ['estado' => 'Dato fuente actual; no es la instantánea utilizada durante la inferencia.', 'fecha' => $registro->fecha_hora?->format('d/m/Y H:i'), 'campo' => $mapa->campo_valor, 'valor' => match ($registro->getAttribute($mapa->campo_valor)) {
                null, '' => 'No registrado', true => 'Marcado en el registro', false => 'No marcado en el registro',
                'CONSERVADA' => 'Conservada', 'ALTERADA' => 'Alterada', 'ALTERACION_LEVE' => 'Alteración leve',
                default => 'Descripción que requiere consulta del registro original',
            }] : ['estado' => 'Registro fuente no disponible para este residente.'];
        }
        if ($tabla === 'aplicaciones_instrumento' && $user->checkPermissionTo('aplicaciones_instrumento.ver', 'web')) {
            $registro = FuentesValoracionOperativa::limitar(AplicacionInstrumento::query())->whereKey($e->cod_registro_fuente)->where('cod_residente', $residente->getKey())->with(['instrumento', 'evaluador'])->first();
            if (! $registro) {
                return ['estado' => 'Registro fuente no disponible para este residente.'];
            }

            return ['estado' => 'Procedencia actual de la aplicación. Las respuestas y su interpretación requieren un contrato de componente autorizado.', 'fecha' => $registro->fecha_hora?->format('d/m/Y H:i'), 'instrumento' => $registro->instrumento?->nombre ?? 'No disponible'];
        }

        return ['estado' => 'Acceso al dato fuente restringido por permiso o contrato.'];
    }
}
