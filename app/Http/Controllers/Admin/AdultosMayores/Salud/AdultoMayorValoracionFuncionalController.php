<?php

namespace App\Http\Controllers\Admin\AdultosMayores\Salud;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdultosMayores\Salud\StoreValoracionFuncionalRequest;
use App\Models\AdultoMayor;
use App\Models\ValoracionFuncionalAdulto;
use Illuminate\Support\Facades\Gate;

class AdultoMayorValoracionFuncionalController extends Controller
{
    public function store(StoreValoracionFuncionalRequest $request, AdultoMayor $adulto_mayor)
    {
        Gate::authorize('viewClinicalData', $adulto_mayor);

        try {
            $valoracion = ValoracionFuncionalAdulto::create(array_merge(
                $request->validated(),
                [
                    'cod_am'         => $adulto_mayor->cod_am,
                    'registrado_por' => auth()->user()->cod_usu,
                ]
            ));

            return redirect()
                ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'valoracion-funcional'])
                ->with('success', "Valoración funcional registrada — nivel: {$valoracion->nivel_dependencia}.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al registrar valoración funcional: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function update(StoreValoracionFuncionalRequest $request, AdultoMayor $adulto_mayor, ValoracionFuncionalAdulto $valoracion)
    {
        Gate::authorize('viewClinicalData', $adulto_mayor);
        abort_if($valoracion->cod_am !== $adulto_mayor->cod_am, 403);

        try {
            $valoracion->update($request->validated());

            return redirect()
                ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'valoracion-funcional'])
                ->with('success', 'Valoración funcional actualizada correctamente.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar valoración funcional: ' . $e->getMessage())
                ->withInput();
        }
    }
}
