<?php

namespace App\Modulos\Social\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Actividad;
use App\Models\Residente;
use App\Modulos\Social\Aplicacion\Acciones\GestionarActividadResidenteHttp;
use App\Modulos\Social\Presentacion\Http\Requests\StoreActividadAdultoRequest;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AdultoMayorActividadController extends Controller
{
    public function store(StoreActividadAdultoRequest $request, Residente $adulto_mayor): RedirectResponse
    {
        app(GestionarActividadResidenteHttp::class)->store(auth()->user(), $request->validated(), $adulto_mayor);

        return $this->volver($adulto_mayor)->with('success', 'Actividad registrada correctamente.');
    }

    public function update(Request $request, Residente $adulto_mayor, Actividad $actividad): RedirectResponse
    {
        app(GestionarActividadResidenteHttp::class)->update(auth()->user(), $request->all(), $adulto_mayor, $actividad);

        return $this->volver($adulto_mayor)->with('success', 'Actividad actualizada correctamente.');
    }

    public function destroy(Residente $adulto_mayor, Actividad $actividad): RedirectResponse
    {
        app(GestionarActividadResidenteHttp::class)->destroy(auth()->user(), [], $adulto_mayor, $actividad);

        return $this->volver($adulto_mayor)->with('success', 'Actividad anulada correctamente.');
    }

    public function restore(Residente $adulto_mayor, string $actividad): RedirectResponse
    {
        app(GestionarActividadResidenteHttp::class)->restore(auth()->user(), [], $adulto_mayor, $actividad);

        return $this->volver($adulto_mayor)->with('success', 'Actividad restaurada correctamente.');
    }

    private function volver(Residente $adulto): RedirectResponse
    {
        return new RedirectResponse(route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto->cod_residente, 'tab' => 'actividades']));
    }
}
