<?php

namespace App\Modulos\Valoraciones\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Residente;
use App\Models\ValoracionFuncional;
use App\Modulos\Valoraciones\Aplicacion\Acciones\GestionarValoracionFuncionalHttp;
use App\Modulos\Valoraciones\Presentacion\Http\Requests\StoreValoracionFuncionalRequest;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AdultoMayorValoracionFuncionalController extends Controller
{
    public function store(StoreValoracionFuncionalRequest $request, Residente $adulto_mayor): RedirectResponse
    {
        $valoracion = app(GestionarValoracionFuncionalHttp::class)->store(auth()->user(), $request->validated(), $adulto_mayor);

        return $this->volver($adulto_mayor)->with('success', "Valoración funcional registrada — nivel: {$valoracion->nivel_dependencia}.");
    }

    public function update(StoreValoracionFuncionalRequest $request, Residente $adulto_mayor, ValoracionFuncional $valoracion): RedirectResponse
    {
        app(GestionarValoracionFuncionalHttp::class)->update(auth()->user(), $request->validated(), $adulto_mayor, $valoracion);

        return $this->volver($adulto_mayor)->with('success', 'Valoración funcional actualizada correctamente.');
    }

    private function volver(Residente $adulto): RedirectResponse
    {
        return new RedirectResponse(route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto->cod_residente, 'tab' => 'valoracion-funcional']));
    }
}
