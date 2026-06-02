<?php

namespace App\Http\Controllers\Admin\AdultosMayores\Salud;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdultosMayores\Salud\StoreAdministracionMedicacionRequest;
use App\Models\AdultoMayor;
use App\Models\AdministracionMedicacion;
use Illuminate\Support\Facades\Gate;

class AdultoMayorAdministracionMedicacionController extends Controller
{
    public function store(StoreAdministracionMedicacionRequest $request, AdultoMayor $adulto_mayor)
    {
        Gate::authorize('viewClinicalData', $adulto_mayor);

        try {
            $registro = AdministracionMedicacion::create(array_merge(
                $request->validated(),
                [
                    'cod_am'         => $adulto_mayor->cod_am,
                    'registrado_por' => auth()->user()->cod_usu,
                ]
            ));

            $tipoEvento = $registro->administrado
                ? 'Se registró administración de medicación'
                : 'Se registró omisión de medicación';

            activity('Administración Medicación')
                ->causedBy(auth()->user())
                ->performedOn($registro)
                ->event($registro->administrado ? 'administrado' : 'omitido')
                ->withProperties([
                    'cod_med_adulto' => $registro->cod_med_adulto,
                    'administrado'   => $registro->administrado,
                    'fecha'          => $registro->fecha,
                ])
                ->log("{$tipoEvento} para el adulto mayor {$adulto_mayor->cod_am}.");

            return redirect()
                ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'medicacion'])
                ->with('success', $tipoEvento . '.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al registrar administración: ' . $e->getMessage())
                ->withInput();
        }
    }
}
