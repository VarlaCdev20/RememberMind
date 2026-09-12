<?php

namespace App\Http\Controllers\Clinica;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clinica\StoreSignosVitalesRequest;
use App\Models\AdultoMayor;
use App\Models\SignosVitalesAdulto;
use App\Services\Clinica\SignosVitalesService;

class AdultoMayorSignosVitalesController extends Controller
{
    public function store(StoreSignosVitalesRequest $request, AdultoMayor $adulto_mayor)
    {
        try {
            app(SignosVitalesService::class)->registrar($adulto_mayor->cod_am, $request->validated(), auth()->user());

            return redirect()
                ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'signos-vitales'])
                ->with('success', 'Signos vitales registrados correctamente.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al registrar signos vitales: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function update(StoreSignosVitalesRequest $request, AdultoMayor $adulto_mayor, SignosVitalesAdulto $signo)
    {
        abort_unless($signo->cod_am === $adulto_mayor->cod_am, 404);
        try {
            $data = $request->validated();
            app(SignosVitalesService::class)->rectificar($signo, $data, (string) $request->input('motivo_rectificacion'), auth()->user());

            return redirect()
                ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'signos-vitales'])
                ->with('success', 'Rectificación registrada; el control original se conserva.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar signos vitales: ' . $e->getMessage())
                ->withInput();
        }
    }
}
