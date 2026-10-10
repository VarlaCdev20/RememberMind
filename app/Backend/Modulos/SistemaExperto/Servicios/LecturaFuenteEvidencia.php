<?php

namespace App\Backend\Modulos\SistemaExperto\Servicios;

use App\Models\AplicacionInstrumento;
use App\Models\ControlCognitivo;
use App\Models\EvaluacionExperta;
use App\Models\EvidenciaEvaluacion;
use App\Models\PreguntaInstrumento;
use App\Models\Residente;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class LecturaFuenteEvidencia
{
    public function consultar(User $user, Residente $residente, string $evaluacion, string $evidencia): array
    {
        Gate::forUser($user)->authorize('consultarResultados', [EvaluacionExperta::class, $residente]);
        $e = EvidenciaEvaluacion::query()->whereKey($evidencia)->where('cod_evaluacion_experta', $evaluacion)
            ->whereHas('evaluacion', fn ($q) => LecturaTecnicaPruebas::limitar($q->where('cod_residente', $residente->getKey())))
            ->with(['evaluacion', 'mapeoVariableFuente.fuente'])->firstOrFail();
        $mapa = $e->mapeoVariableFuente;
        abort_unless($mapa->cod_version_modelo === $e->evaluacion->cod_version_modelo && $mapa->fuente->cod_version_modelo === $e->evaluacion->cod_version_modelo, 404);
        $tabla = $mapa->fuente->tabla_raiz;
        if ($tabla === 'controles_cognitivos' && $user->checkPermissionTo('controles_cognitivos.ver', 'web')) {
            $campos = ['memoria_reciente', 'memoria_remota', 'repite_preguntas', 'olvida_indicaciones', 'cambio_cognitivo'];
            if (! in_array($mapa->campo_valor, $campos, true)) {
                return ['estado' => 'El campo fuente requiere un contrato específico de consulta.'];
            }
            $registro = ControlCognitivo::query()->whereKey($e->cod_registro_fuente)->where('cod_residente', $residente->getKey())->first();

            return $registro ? ['estado' => 'Dato fuente actual; no es la instantánea utilizada durante la inferencia.', 'fecha' => $registro->fecha_hora?->format('d/m/Y H:i'), 'campo' => $mapa->campo_valor, 'valor' => $registro->getRawOriginal($mapa->campo_valor) ?? 'No registrado'] : ['estado' => 'Registro fuente no disponible para este residente.'];
        }
        if ($tabla === 'aplicaciones_instrumento' && $user->checkPermissionTo('aplicaciones_instrumento.ver', 'web')) {
            $registro = AplicacionInstrumento::query()->whereKey($e->cod_registro_fuente)->where('cod_residente', $residente->getKey())->with(['instrumento', 'evaluador'])->first();
            if (! $registro) {
                return ['estado' => 'Registro fuente no disponible para este residente.'];
            }
            if (LecturaTecnicaPruebas::habilitada() && $mapa->getKey() === 'MAP_INS'
                && $registro->cod_instrumento === 'INST_COMPLETO' && $registro->instrumento?->version === 'TECNICA_1') {
                $preguntas = PreguntaInstrumento::query()->where('cod_instrumento', 'INST_COMPLETO')
                    ->whereIn('cod_pregunta', ['QA_REC', 'QA_REM', 'QA_IND'])->get()->keyBy('cod_pregunta');
                $respuestas = $registro->respuestas->sortBy(fn ($r) => $preguntas->get($r->cod_pregunta)?->orden);
                abort_unless($respuestas->count() === 3 && $respuestas->pluck('cod_pregunta')->unique()->count() === 3
                    && $respuestas->every(fn ($r) => $preguntas->has($r->cod_pregunta)), 404);

                return ['estado' => 'Prueba sintética completa · respuestas actuales de la base de QA, sin validación clínica.',
                    'fecha' => $registro->fecha_hora?->format('d/m/Y H:i'), 'instrumento' => $registro->instrumento->nombre,
                    'autor' => trim(implode(' ', [$registro->evaluador?->nombres, $registro->evaluador?->apellido_paterno, $registro->evaluador?->apellido_materno])),
                    'version' => $registro->instrumento->version, 'aplicacion' => $registro->estado,
                    'respuestas' => $respuestas->map(fn ($r) => ['pregunta' => $preguntas[$r->cod_pregunta]->enunciado, 'valor' => $r->valor_texto])->values()->all()];
            }

            return ['estado' => 'Procedencia actual de la aplicación. Las respuestas y su interpretación requieren un contrato de componente autorizado.', 'fecha' => $registro->fecha_hora?->format('d/m/Y H:i'), 'instrumento' => $registro->instrumento?->nombre ?? 'No disponible'];
        }

        return ['estado' => 'Acceso al dato fuente restringido por permiso o contrato.'];
    }
}
