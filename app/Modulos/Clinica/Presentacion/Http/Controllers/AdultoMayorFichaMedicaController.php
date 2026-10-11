<?php

namespace App\Modulos\Clinica\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Residente;
use App\Modulos\Clinica\Aplicacion\Acciones\FichaMedicaService;
use App\Modulos\Clinica\Aplicacion\Acciones\RegistrarEventoFichaMedica;
use App\Modulos\Clinica\Presentacion\Http\Requests\StoreFichaMedicaRequest;
use App\Modulos\Clinica\Presentacion\Http\Requests\UpdateFichaMedicaRequest;

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
                ->with('error', 'Error al registrar ficha médica: '.$e->getMessage())
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
                ->with('error', 'Error al actualizar ficha médica: '.$e->getMessage())
                ->withInput();
        }
    }

    public function archivar(Residente $adulto_mayor, $ficha = null)
    {
        app(RegistrarEventoFichaMedica::class)->archivar(auth()->user(), $adulto_mayor);

        return redirect()
            ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'ficha-medica'])
            ->with('success', 'Ficha médica archivada correctamente.');
    }

    public function restore(Residente $adulto_mayor, $ficha = null)
    {
        app(RegistrarEventoFichaMedica::class)->restore(auth()->user(), $adulto_mayor);

        return redirect()
            ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'ficha-medica'])
            ->with('success', 'Ficha médica restaurada correctamente.');
    }
}
