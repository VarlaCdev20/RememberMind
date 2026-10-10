<?php

namespace App\Modulos\Medicacion\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Prescripcion;
use App\Models\Residente;
use App\Modulos\Clinica\Aplicacion\Consultas\ContextoClinicoService;
use App\Modulos\Medicacion\Aplicacion\Acciones\GestionarPrescripcionFichaHttp;
use App\Modulos\Medicacion\Presentacion\Http\Requests\StoreMedicacionRequest;
use App\Modulos\Medicacion\Presentacion\Http\Requests\UpdateMedicacionRequest;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class AdultoMayorMedicacionController extends Controller
{
    public function store(StoreMedicacionRequest $request, Residente $adulto_mayor, ContextoClinicoService $contextoClinico)
    {
        try {
            $nombreMed = app(GestionarPrescripcionFichaHttp::class)->store(auth()->user(), $request->validated(), $adulto_mayor, $contextoClinico);

            return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'medicacion'])->with('success', "Prescripción de '{$nombreMed}' registrada correctamente.");
        } catch (\Throwable $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            report($e);

            return redirect()->back()->with('error', 'No se pudo registrar la medicación. Inténtelo nuevamente.')->withInput();
        }
    }

    public function update(UpdateMedicacionRequest $request, Residente $adulto_mayor, Prescripcion $medicacion)
    {
        abort_unless($medicacion->cod_residente === $adulto_mayor->cod_residente, 404);
        try {
            app(GestionarPrescripcionFichaHttp::class)->update(auth()->user(), $request->validated(), $adulto_mayor, $medicacion);

            return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'medicacion'])->with('success', 'Prescripción actualizada correctamente.');
        } catch (\Throwable $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            report($e);

            return redirect()->back()->with('error', 'No se pudo actualizar la medicación. Inténtelo nuevamente.')->withInput();
        }
    }

    public function suspender(Residente $adulto_mayor, Prescripcion $medicacion)
    {
        app(GestionarPrescripcionFichaHttp::class)->suspender(auth()->user(), [], $adulto_mayor, $medicacion);

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'medicacion'])->with('success', 'Prescripción suspendida.');
    }

    public function finalizar(Residente $adulto_mayor, Prescripcion $medicacion)
    {
        app(GestionarPrescripcionFichaHttp::class)->finalizar(auth()->user(), [], $adulto_mayor, $medicacion);

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'medicacion'])->with('success', 'Prescripción finalizada.');
    }

    public function archivar(Residente $adulto_mayor, Prescripcion $medicacion)
    {
        app(GestionarPrescripcionFichaHttp::class)->archivar(auth()->user(), [], $adulto_mayor, $medicacion);

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'medicacion'])->with('success', 'Prescripción archivada.');
    }

    public function restore(Residente $adulto_mayor, $medicacion)
    {
        app(GestionarPrescripcionFichaHttp::class)->restore(auth()->user(), [], $adulto_mayor, $medicacion);

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'medicacion'])->with('success', 'Prescripción reactivada.');
    }
}
