<?php

namespace App\Backend\Modulos\SistemaExperto\Servicios;

use App\Backend\Modulos\SistemaExperto\Conocimiento\PaqueteInvestigadoCOGMEM as P;
use App\Models\AplicacionInstrumento;
use App\Models\ControlCognitivo;
use App\Models\EvaluacionExperta;
use App\Models\Instrumento;
use App\Models\Residente;
use App\Models\User;
use App\Models\VersionModeloExperto;
use Illuminate\Support\Facades\Gate;

/** Inventario autorizado de fuentes; no remapea, ejecuta reglas ni asigna EV-CM. */
final class PreparacionFuentesCOGMEM
{
    public function consultar(User $usuario, Residente $residente): ?array
    {
        Gate::forUser($usuario)->authorize('consultarResultados', [EvaluacionExperta::class, $residente]);
        $version = VersionModeloExperto::query()->find(P::VERSION);
        if (! $version) {
            return null;
        }
        $corte = now();
        $controles = ControlCognitivo::query()->where('cod_residente', $residente->getKey())
            ->where('estado', 'VIGENTE')->where('fecha_hora', '<=', $corte);
        $cantidadAplicaciones = null;
        $instrumentos = [];
        if ($usuario->checkPermissionTo('aplicaciones_instrumento.ver', 'web')) {
            $aplicaciones = AplicacionInstrumento::query()->where('cod_residente', $residente->getKey())
                ->where('estado', 'VIGENTE')->where('fecha_hora', '<=', $corte);
            $cantidadAplicaciones = $aplicaciones->count();
            $instrumentos = Instrumento::query()->whereIn('cod_instrumento', $aplicaciones->select('cod_instrumento'))
                ->orderBy('cod_instrumento')->get(['nombre', 'version'])
                ->map(fn ($i) => $i->nombre.' · '.$i->version)->all();
        }

        return ['version' => $version->codigo_version, 'estado' => 'PREPARACION_NO_INFERENCIA',
            'controles' => $controles->count(), 'aplicaciones' => $cantidadAplicaciones, 'instrumentos' => $instrumentos,
            'puede_emitir_resultado' => false, 'resultado' => null,
            'pendientes' => ['Aplicar y registrar un componente específico de aprendizaje y memoria con versión y método autorizados.',
                'Documentar las condiciones de aplicación y revisar factores que puedan alterar la interpretación.',
                'Revisar profesionalmente los mapeos propuestos y formalizar las reglas completas de esta versión.']];
    }
}
