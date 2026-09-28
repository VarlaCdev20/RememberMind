<?php

namespace App\Http\Controllers\Clinica;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clinica\StoreSignosVitalesRequest;
use App\Models\Residente;
use App\Models\SignoVital;
use App\Backend\Modulos\Clinica\Servicios\SignosVitalesService;

class AdultoMayorSignosVitalesController extends Controller
{
    public function store(StoreSignosVitalesRequest $request, Residente $adulto_mayor)
    {
        try {
            app(SignosVitalesService::class)->registrar($adulto_mayor->cod_residente, $request->validated(), auth()->user());

            return redirect()
                ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'signos-vitales'])
                ->with('success', 'Signos vitales registrados correctamente.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al registrar signos vitales: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function update(StoreSignosVitalesRequest $request, Residente $adulto_mayor, SignoVital $signo)
    {
        abort_unless($signo->cod_residente === $adulto_mayor->cod_residente, 404);
        try {
            app(SignosVitalesService::class)->rectificar(
                $signo,
                $request->validated(),
                $request->input('motivo_rectificacion', 'Rectificación solicitada desde expediente clínico'),
                auth()->user()
            );

            return redirect()
                ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'signos-vitales'])
                ->with('success', 'Signos vitales rectificados correctamente.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al rectificar signos vitales: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function anular(Residente $adulto_mayor, SignoVital $signo)
    {
        abort_unless($signo->cod_residente === $adulto_mayor->cod_residente, 404);
        $signo->update(['estado' => 'ANULADO']);

        activity('Signos Vitales')
            ->causedBy(auth()->user())
            ->performedOn($signo)
            ->event('anulado')
            ->log("Se anuló el registro de signos vitales {$signo->cod_signo} del residente {$adulto_mayor->cod_residente}.");

        return redirect()
            ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'signos-vitales'])
            ->with('success', 'Signos vitales anulados correctamente.');
    }
}
