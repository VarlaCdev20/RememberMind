<?php

namespace App\Modulos\Clinica\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Residente;
use App\Models\SignoVital;
use App\Modulos\Clinica\Aplicacion\Acciones\AnularSignosVitales;
use App\Modulos\Clinica\Aplicacion\Acciones\SignosVitalesService;
use App\Modulos\Clinica\Presentacion\Http\Requests\StoreSignosVitalesRequest;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class AdultoMayorSignosVitalesController extends Controller
{
    public function store(StoreSignosVitalesRequest $request, Residente $adulto_mayor)
    {
        try {
            app(SignosVitalesService::class)->registrar($adulto_mayor->cod_residente, $request->validated(), auth()->user());

            return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'signos-vitales'])->with('success', 'Signos vitales registrados correctamente.');
        } catch (\Throwable $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            report($e);

            return redirect()->back()->with('error', 'No se pudieron registrar los signos vitales. Inténtelo nuevamente.')->withInput();
        }
    }

    public function update(StoreSignosVitalesRequest $request, Residente $adulto_mayor, SignoVital $signo)
    {
        abort_unless($signo->cod_residente === $adulto_mayor->cod_residente, 404);
        try {
            app(SignosVitalesService::class)->rectificar($signo, $request->validated(), $request->input('motivo_rectificacion', 'Rectificación solicitada desde expediente clínico'), auth()->user());

            return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'signos-vitales'])->with('success', 'Signos vitales rectificados correctamente.');
        } catch (\Throwable $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            report($e);

            return redirect()->back()->with('error', 'No se pudieron rectificar los signos vitales. Inténtelo nuevamente.')->withInput();
        }
    }

    public function anular(Residente $adulto_mayor, SignoVital $signo)
    {
        app(AnularSignosVitales::class)->anular(auth()->user(), [], $adulto_mayor, $signo);

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'signos-vitales'])->with('success', 'Signos vitales anulados correctamente.');
    }
}
