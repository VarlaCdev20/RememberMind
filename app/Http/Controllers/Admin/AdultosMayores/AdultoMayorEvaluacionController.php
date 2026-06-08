<?php

namespace App\Http\Controllers\Admin\AdultosMayores;

use App\Http\Controllers\Controller;
use App\Models\AdultoMayor;
use App\Models\EvaluacionGeriatrica;
use App\Models\InstrumentoGeriatrico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdultoMayorEvaluacionController extends Controller
{
    public function index(AdultoMayor $adulto_mayor)
    {
        $evaluaciones = $adulto_mayor->evaluacionesGeriatricas()->with(['instrumento', 'registrador'])->latest()->get();
        return view('admin.adultos-mayores.evaluaciones.index', compact('adulto_mayor', 'evaluaciones'));
    }

    public function store(Request $request, AdultoMayor $adulto_mayor)
    {
        $request->validate([
            'cod_tipo_eval' => 'required|exists:instrumentos_geriatricos,cod_instrumento',
            'fecha_eval' => 'required|date|before_or_equal:today',
            'puntaje_total' => 'required|numeric|min:0|max:30',
            'observaciones' => 'nullable|string',
        ], [
            'cod_tipo_eval.required' => 'Debe seleccionar un tipo de evaluación.',
            'cod_tipo_eval.exists' => 'El tipo de evaluación no es válido.',
            'fecha_eval.required' => 'La fecha de aplicación es obligatoria.',
            'fecha_eval.before_or_equal' => 'La fecha no puede ser futura.',
            'puntaje_total.required' => 'Debe ingresar el puntaje total.',
            'puntaje_total.min' => 'El puntaje mínimo es 0.',
            'puntaje_total.max' => 'El puntaje máximo es 30.',
        ]);

        $tipo = InstrumentoGeriatrico::findOrFail($request->cod_tipo_eval);

        DB::transaction(function () use ($request, $adulto_mayor, $tipo) {
            $interpretacion = $this->interpretarPuntaje($tipo, $request->puntaje_total);

            EvaluacionGeriatrica::create([
                'cod_am' => $adulto_mayor->cod_am,
                'cod_instrumento' => $request->cod_tipo_eval,
                'registrado_por' => auth()->id(),
                'evaluador_id' => auth()->id(),
                'evaluador_tipo' => \App\Models\User::class,
                'fecha_eval' => $request->fecha_eval,
                'hora_eval' => now()->format('H:i:s'),
                'puntaje_total' => $request->puntaje_total,
                'categoria_resultado' => $interpretacion['resultado'],
                'nivel_riesgo' => $interpretacion['riesgo'],
                'nivel_alerta' => $interpretacion['riesgo'] === 'ALTO' ? 'CRITICO' : 'NORMAL',
                'observaciones' => $request->observaciones,
                'estado_eval' => 'ACTIVO',
            ]);

            activity('adulto_mayor')
                ->performedOn($adulto_mayor)
                ->log("Se registró una evaluación geriátrica {$tipo->nombre} para {$adulto_mayor->nombres}");
        });

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'evaluaciones'])->with('success', 'Evaluación registrada correctamente.');
    }

    private function interpretarPuntaje($tipo, $puntaje)
    {
        $resultado = 'Normal';
        $riesgo = 'BAJO';

        $siglas = strtoupper($tipo->siglas ?? '');
        if ($siglas === 'MOCA') {
            if ($puntaje < 26) {
                $resultado = 'Deterioro Cognitivo Leve';
                $riesgo = 'MEDIO';
            }
            if ($puntaje < 18) {
                $riesgo = 'ALTO';
            }
        } elseif ($siglas === 'MMSE') {
            if ($puntaje < 24) {
                $resultado = 'Deterioro Cognitivo';
                $riesgo = 'MEDIO';
            }
            if ($puntaje < 12) {
                $riesgo = 'ALTO';
            }
        }

        return ['resultado' => $resultado, 'riesgo' => $riesgo];
    }

    public function destroy(AdultoMayor $adulto_mayor, $id)
    {
        $evaluacion = EvaluacionGeriatrica::findOrFail($id);
        
        $evaluacion->update([
            'estado_eval' => 'ANULADO',
            'motivo_anulacion' => 'Anulación desde panel de evaluaciones',
            'anulado_por' => auth()->id(),
            'anulado_en' => now()
        ]);
        
        $evaluacion->delete(); // Soft delete

        activity('Adulto Mayor')
            ->performedOn($adulto_mayor)
            ->log("Se anuló una evaluación cognitiva (baja lógica) para la ficha {$adulto_mayor->cod_am}.");

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'evaluaciones'])->with('success', 'Evaluación anulada correctamente.');
    }

    public function restore(AdultoMayor $adulto_mayor, $id)
    {
        $evaluacion = EvaluacionGeriatrica::withTrashed()->findOrFail($id);
        
        $evaluacion->update([
            'estado_eval' => 'ACTIVO',
            'motivo_anulacion' => null,
            'anulado_por' => null,
            'anulado_en' => null
        ]);
        
        $evaluacion->restore();

        activity('Adulto Mayor')
            ->performedOn($adulto_mayor)
            ->withProperties(['cod_eval' => $id])
            ->log("Se restauró una evaluación cognitiva previamente anulada.");

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'evaluaciones'])
            ->with('success', 'Evaluación restaurada correctamente.');
    }
}
