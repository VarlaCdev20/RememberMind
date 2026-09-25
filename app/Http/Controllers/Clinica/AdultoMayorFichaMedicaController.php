<?php

namespace App\Http\Controllers\Clinica;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clinica\StoreFichaMedicaRequest;
use App\Http\Requests\Clinica\UpdateFichaMedicaRequest;
use App\Models\Residente;
use App\Backend\Modulos\Clinica\Servicios\FichaMedicaService;
use Illuminate\Support\Facades\DB;

class AdultoMayorFichaMedicaController extends Controller
{
    public function store(StoreFichaMedicaRequest $request, Residente $adulto_mayor)
    {
        try {
            $service = app(FichaMedicaService::class);
            $service->guardarFicha($adulto_mayor->cod_residente, $request->validated(), auth()->user()->cod_usuario);

            return redirect()
                ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'ficha-medica'])
                ->with('success', 'Ficha médica registrada correctamente.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al registrar ficha médica: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function update(UpdateFichaMedicaRequest $request, Residente $adulto_mayor, $ficha = null)
    {
        try {
            $service = app(FichaMedicaService::class);
            $service->guardarFicha($adulto_mayor->cod_residente, $request->validated(), auth()->user()->cod_usuario);

            return redirect()
                ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'ficha-medica'])
                ->with('success', 'Ficha médica actualizada correctamente.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar ficha médica: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function archivar(Residente $adulto_mayor, $ficha = null)
    {
        activity('Ficha Médica')
            ->causedBy(auth()->user())
            ->performedOn($adulto_mayor)
            ->event('archived')
            ->log("Se archivó la ficha médica del residente {$adulto_mayor->cod_residente}.");

        return redirect()
            ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'ficha-medica'])
            ->with('success', 'Ficha médica archivada correctamente.');
    }

    public function restore(Residente $adulto_mayor, $ficha = null)
    {
        activity('Ficha Médica')
            ->causedBy(auth()->user())
            ->performedOn($adulto_mayor)
            ->event('restored')
            ->log("Se restauró la ficha médica del residente {$adulto_mayor->cod_residente}.");

        return redirect()
            ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'ficha-medica'])
            ->with('success', 'Ficha médica restaurada correctamente.');
    }
}