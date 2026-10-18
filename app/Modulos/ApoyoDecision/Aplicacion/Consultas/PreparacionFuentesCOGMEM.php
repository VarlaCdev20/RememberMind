<?php

namespace App\Modulos\ApoyoDecision\Aplicacion\Consultas;

use App\Models\AplicacionInstrumento;
use App\Models\ControlCognitivo;
use App\Models\EvaluacionExperta;
use App\Models\Instrumento;
use App\Models\Residente;
use App\Models\User;
use App\Models\VersionModeloExperto;
use Illuminate\Support\Facades\Gate;
use MotorExperto\dominio\conocimiento\versiones\PaqueteInvestigadoCOGMEM as P;
use MotorExperto\dominio\instrumentos\RegistroMetodosInstrumentales;

/** Inventario autorizado de fuentes; no remapea, ejecuta reglas ni asigna EV-CM. */
final class PreparacionFuentesCOGMEM
{
    public function consultar(User $usuario, Residente $residente): ?array
    {
        $usuario = User::query()->findOrFail($usuario->getKey());
        Gate::forUser($usuario)->authorize('consultarResultados', [EvaluacionExperta::class, $residente]);
        $version = VersionModeloExperto::query()->where('codigo_version', P::CODIGO)->first();
        if (! $version) {
            return null;
        }
        $corte = now();
        $controles = FuentesValoracionOperativa::limitar(ControlCognitivo::query())->where('cod_residente', $residente->getKey())
            ->where('estado', 'VIGENTE')->where('fecha_hora', '<=', $corte);
        $cantidadAplicaciones = null;
        $instrumentos = [];
        if ($usuario->checkPermissionTo('aplicaciones_instrumento.ver', 'web')) {
            $aplicaciones = FuentesValoracionOperativa::limitar(AplicacionInstrumento::query())->where('cod_residente', $residente->getKey())
                ->where('estado', 'COMPLETA')->where('fecha_hora', '<=', $corte);
            $cantidadAplicaciones = $aplicaciones->count();
            $instrumentos = Instrumento::query()->whereIn('cod_instrumento', $aplicaciones->select('cod_instrumento'))
                ->orderBy('cod_instrumento')->get(['nombre', 'version'])
                ->map(fn ($i) => $i->nombre.' · '.$i->version)->all();
        }

        return ['version' => $version->codigo_version, 'estado' => 'PREPARACION_NO_INFERENCIA',
            'controles' => $controles->count(), 'aplicaciones' => $cantidadAplicaciones, 'instrumentos' => $instrumentos,
            'metodos_instrumentales' => $usuario->checkPermissionTo('aplicaciones_instrumento.ver', 'web')
                ? (new RegistroMetodosInstrumentales)->metodos() : [],
            'puede_emitir_resultado' => false, 'resultado' => null,
            'pendientes' => ['Identificar instrumento, forma, idioma y mediciones originales de cada componente. Las etiquetas nominales no se convierten en puntajes.',
                'Documentar las condiciones de aplicación y revisar factores que puedan alterar la interpretación.',
                'Registrar la valoración profesional después de calcular y conservar el resultado con sus soportes.']];
    }
}
