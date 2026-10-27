<?php

namespace App\Modulos\Social\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Residente;
use App\Modulos\Social\Aplicacion\Acciones\GestionarVinculoFamiliarHttp;
use App\Modulos\Social\Presentacion\Http\Requests\StoreFamiliarAdultoRequest;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AdultoMayorFamiliarController extends Controller
{
    public function index(Residente $adulto_mayor): RedirectResponse
    {
        return $this->volver($adulto_mayor);
    }

    public function store(StoreFamiliarAdultoRequest $request, Residente $adulto_mayor): RedirectResponse
    {
        app(GestionarVinculoFamiliarHttp::class)->store(auth()->user(), $request->validated(), $adulto_mayor);

        return $this->volver($adulto_mayor)->with('success', 'Contacto vinculado correctamente.');
    }

    public function update(Request $request, Residente $adulto_mayor, string $familiar): RedirectResponse
    {
        app(GestionarVinculoFamiliarHttp::class)->update(auth()->user(), $request->all(), $adulto_mayor, $familiar);

        return $this->volver($adulto_mayor)->with('success', 'Vínculo actualizado correctamente.');
    }

    public function destroy(Residente $adulto_mayor, string $familiar): RedirectResponse
    {
        app(GestionarVinculoFamiliarHttp::class)->destroy(auth()->user(), request()->only('motivo'), $adulto_mayor, $familiar);

        return $this->volver($adulto_mayor)->with('success', 'Vínculo desactivado correctamente.');
    }

    public function restore(Residente $adulto_mayor, string $familiar): RedirectResponse
    {
        app(GestionarVinculoFamiliarHttp::class)->restore(auth()->user(), request()->only('motivo'), $adulto_mayor, $familiar);

        return $this->volver($adulto_mayor)->with('success', 'Vínculo restaurado correctamente.');
    }

    private function volver(Residente $adulto): RedirectResponse
    {
        return new RedirectResponse(route('admin.familia-social.red-apoyo', ['residente' => $adulto->getKey()]));
    }
}
