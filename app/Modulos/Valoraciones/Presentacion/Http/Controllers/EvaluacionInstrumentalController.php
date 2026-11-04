<?php

declare(strict_types=1);

namespace App\Modulos\Valoraciones\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Instrumento;
use App\Models\Residente;
use App\Modulos\Valoraciones\Aplicacion\Acciones\AnularAplicacionInstrumento;
use App\Modulos\Valoraciones\Aplicacion\Acciones\AplicarInstrumento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Las rutas existentes comparten la captura institucional; no interpretan totales por nombre de prueba. */
final class EvaluacionInstrumentalController extends Controller
{
    public function index(Residente $adulto_mayor): RedirectResponse
    {
        return redirect()->route('admin.salud-seguimiento.evaluaciones-geriatricas', ['adulto' => $adulto_mayor->getKey()]);
    }

    public function store(Request $request, Residente $adulto_mayor): RedirectResponse
    {
        $datos = $request->validate(['cod_instrumento' => ['required', 'string', 'exists:instrumentos,cod_instrumento']]);
        app(AplicarInstrumento::class)->aplicar($request->user(),
            $request->only(['fecha_hora', 'cod_atencion', 'respuestas', 'observacion']),
            Instrumento::query()->findOrFail($datos['cod_instrumento']), $adulto_mayor);

        return $this->index($adulto_mayor)->with('success', 'Mediciones registradas correctamente.');
    }

    public function show(Residente $adulto_mayor, string $evaluacion): RedirectResponse
    {
        return $this->index($adulto_mayor);
    }

    public function destroy(Request $request, Residente $adulto_mayor, string $evaluacion): RedirectResponse
    {
        app(AnularAplicacionInstrumento::class)->anular($request->user(), $adulto_mayor, $evaluacion,
            $request->only('motivo_anulacion'));

        return $this->index($adulto_mayor)->with('success', 'Aplicación anulada; sus registros se conservan.');
    }

    public function restore(): never
    {
        abort(409, 'Una aplicación anulada conserva su historia. La restauración requiere un contrato de rectificación definido.');
    }
}
