<?php

namespace App\Http\Controllers\Valoraciones;

use App\Http\Controllers\Controller;
use App\Models\AdultoMayor;
use App\Models\AplicacionInstrumento;
use App\Models\Instrumento;
use App\Models\Personal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AdultoMayorEvaluacionController extends Controller
{
    public function index(AdultoMayor $adulto_mayor)
    {
        return new RedirectResponse(route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'evaluaciones']));
    }

    public function store(Request $request, AdultoMayor $adulto_mayor)
    {
        $request->validate([
            'cod_tipo_eval' => 'required|exists:instrumentos,cod_instrumento',
            'fecha_eval' => 'required|date|before_or_equal:today',
            'puntaje_total' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string',
        ]);

        $instrumento = Instrumento::findOrFail($request->cod_tipo_eval);
        $personal = Personal::where('cod_usuario', auth()->user()?->cod_usuario)->first();

        $interpretacion = $this->interpretarPuntaje($instrumento, $request->puntaje_total);

        AplicacionInstrumento::create([
            'cod_aplicacion' => 'API_' . strtoupper(Str::random(10)),
            'cod_instrumento' => $instrumento->cod_instrumento,
            'cod_residente' => $adulto_mayor->cod_residente,
            'cod_personal' => $personal?->cod_personal,
            'fecha_hora' => $request->fecha_eval . ' ' . now()->format('H:i:s'),
            'puntaje_total' => $request->puntaje_total,
            'puntaje_maximo' => $instrumento->puntaje_maximo ?? 30,
            'clasificacion' => $interpretacion['riesgo'],
            'interpretacion' => $interpretacion['resultado'],
            'observacion' => $request->observaciones,
            'estado' => 'ACTIVO',
        ]);

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'evaluaciones'])
            ->with('success', 'Evaluación registrada correctamente.');
    }

    private function interpretarPuntaje($instrumento, $puntaje)
    {
        $resultado = 'Normal';
        $riesgo = 'BAJO';

        $codigo = strtoupper($instrumento->codigo ?? '');
        if (str_contains($codigo, 'MOCA')) {
            if ($puntaje < 26) {
                $resultado = 'Deterioro Cognitivo Leve';
                $riesgo = 'MEDIO';
            }
            if ($puntaje < 18) {
                $resultado = 'Deterioro Severo';
                $riesgo = 'ALTO';
            }
        } elseif (str_contains($codigo, 'MMSE')) {
            if ($puntaje < 24) {
                $resultado = 'Deterioro Cognitivo';
                $riesgo = 'MEDIO';
            }
            if ($puntaje < 12) {
                $resultado = 'Deterioro Severo';
                $riesgo = 'ALTO';
            }
        }

        return ['resultado' => $resultado, 'riesgo' => $riesgo];
    }

    public function show(AdultoMayor $adulto_mayor, $evaluacionId)
    {
        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'evaluaciones']);
    }

    public function destroy(AdultoMayor $adulto_mayor, $id)
    {
        $evaluacion = AplicacionInstrumento::where('cod_residente', $adulto_mayor->cod_residente)
            ->where('cod_aplicacion', $id)
            ->firstOrFail();

        $evaluacion->update(['estado' => 'ANULADO']);

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'evaluaciones'])
            ->with('success', 'Evaluación anulada correctamente.');
    }

    public function restore(AdultoMayor $adulto_mayor, $id)
    {
        $evaluacion = AplicacionInstrumento::where('cod_residente', $adulto_mayor->cod_residente)
            ->where('cod_aplicacion', $id)
            ->firstOrFail();

        $evaluacion->update(['estado' => 'ACTIVO']);

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'evaluaciones'])
            ->with('success', 'Evaluación restaurada correctamente.');
    }
}