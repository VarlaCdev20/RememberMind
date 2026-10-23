<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Consultas;

use App\Models\EvaluacionExperta;
use App\Models\NodoSemantico;
use App\Models\RelacionSemantica;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\GrafoInferenciaHistorica;
use Illuminate\Support\Facades\Gate;

/** Lecturas autorizadas y payloads planos para explorar conocimiento e historia sin inferencia. */
final class ConsultarExploracionOrion
{
    public function consultar(User $usuario, Residente $residente, array $lectura, string $criterio): array
    {
        $usuario = User::query()->findOrFail($usuario->getKey());
        Gate::forUser($usuario)->authorize('consultarTrazabilidad', [EvaluacionExperta::class, $residente]);
        // La lectura se obtiene de nuevo en el límite de confianza; no se aceptan resultados del cliente.
        $lectura = app(LecturaResultadosExperto::class)->consultar($usuario, $residente, $lectura['evaluacion']['id'] ?? null, $criterio);
        $evaluacion = $lectura['evaluacion'] ? EvaluacionExperta::query()->where('cod_residente', $residente->getKey())
            ->findOrFail($lectura['evaluacion']['id']) : null;
        $raiz = NodoSemantico::query()->with('version')->where('codigo_semantico', $criterio);
        if ($evaluacion) {
            $raiz->where('cod_version_modelo', $evaluacion->cod_version_modelo);
        } else {
            // Catálogo documental ya usado por el lector; no se elige una versión por estar activa.
            $raiz->whereHas('version', fn ($q) => $q->where('codigo_version', 'ORION-V1-20261008'));
        }
        $raiz = $raiz->first();
        $semantico = ['nodos' => [], 'aristas' => [], 'version' => $raiz?->version?->codigo_version,
            'aviso' => 'Estructura documental del conocimiento. Sus relaciones no acreditan una inferencia ejecutada para el residente.'];
        if ($raiz) {
            $nodos = NodoSemantico::query()->where('cod_version_modelo', $raiz->cod_version_modelo)
                ->with('variableExperta')->orderBy('codigo_semantico')->get()->keyBy('cod_nodo_semantico');
            $relaciones = RelacionSemantica::query()->where('cod_version_modelo', $raiz->cod_version_modelo)
                ->orderBy('cod_relacion_semantica')->get();
            $ids = [$raiz->getKey() => 0];
            // Recorrido solo por aristas reales; las relaciones transversales permanecen explícitas.
            for ($paso = 0; $paso < $nodos->count(); $paso++) {
                $cambio = false;
                foreach ($relaciones as $r) {
                    if (! $nodos->has($r->cod_nodo_origen) || ! $nodos->has($r->cod_nodo_destino)) {
                        continue;
                    }
                    if (isset($ids[$r->cod_nodo_origen]) && ! isset($ids[$r->cod_nodo_destino])) {
                        $ids[$r->cod_nodo_destino] = min(4, $ids[$r->cod_nodo_origen] + 1);
                        $cambio = true;
                    }
                    if (isset($ids[$r->cod_nodo_destino]) && ! isset($ids[$r->cod_nodo_origen])) {
                        $ids[$r->cod_nodo_origen] = min(4, $ids[$r->cod_nodo_destino] + 1);
                        $cambio = true;
                    }
                }
                if (! $cambio) {
                    break;
                }
            }
            foreach ($ids as $id => $nivel) {
                $n = $nodos[$id];
                $semantico['nodos'][] = ['id' => $id, 'tipo' => $n->tipo_nodo ?? 'Nodo semántico', 'nombre' => $n->nombre,
                    'columna' => $nivel, 'estado' => 'Conocimiento documental', 'version' => $semantico['version'],
                    'datos' => ['Código semántico' => $n->codigo_semantico, 'Definición' => $n->definicion ?? 'No registrada',
                        'Variable experta' => $n->variableExperta?->getKey() ?? 'No asociada']];
            }
            foreach ($relaciones as $r) {
                if (isset($ids[$r->cod_nodo_origen], $ids[$r->cod_nodo_destino])) {
                    $semantico['aristas'][] = ['origen' => $r->cod_nodo_origen, 'destino' => $r->cod_nodo_destino,
                        'relacion' => $r->tipo_relacion ?? 'Relación registrada'];
                }
            }
        }

        return ['semantico' => $semantico, 'inferencia' => (new GrafoInferenciaHistorica)->presentar($lectura,
            $usuario->checkPermissionTo('aplicaciones_instrumento.ver', 'web'))];
    }
}
