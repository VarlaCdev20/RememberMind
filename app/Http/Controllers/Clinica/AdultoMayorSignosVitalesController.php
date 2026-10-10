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

        } catch (\Throwable $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException
                || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            report($e);
            return redirect()->back()
                ->with('error', 'No se pudieron registrar los signos vitales. Inténtelo nuevamente.')
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

        } catch (\Throwable $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException
                || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            report($e);
            return redirect()->back()
                ->with('error', 'No se pudieron rectificar los signos vitales. Inténtelo nuevamente.')
                ->withInput();
        }
    }

    public function anular(Residente $adulto_mayor, SignoVital $signo)
    {
        abort_unless($signo->cod_residente === $adulto_mayor->cod_residente, 404);
        app(\App\Backend\Modulos\Enfermeria\Servicios\TurnoEnfermeriaService::class)
            ->autorizarMutacionPaciente($adulto_mayor, 'signos_vitales.anular', auth()->user());

        \Illuminate\Support\Facades\DB::transaction(function () use ($signo, $adulto_mayor) {
            $signo = SignoVital::query()->lockForUpdate()->findOrFail($signo->getKey());
            abort_unless(in_array($signo->estado, ['ACTIVO', 'VIGENTE'], true), 409);
            $signo->update(['estado' => 'ANULADO']);

        activity('Signos Vitales')
            ->causedBy(auth()->user())
            ->performedOn($signo)
            ->event('anulado')
            ->log("Se anuló el registro de signos vitales {$signo->cod_signo} del residente {$adulto_mayor->cod_residente}.");
        });

        return redirect()
            ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'signos-vitales'])
            ->with('success', 'Signos vitales anulados correctamente.');
    }
}
