<?php

namespace App\Http\Controllers\Admin\AdultosMayores;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdultosMayores\StoreActividadAdultoRequest;
use App\Models\AdultoMayor;
use App\Models\ActividadAdulto;
use Illuminate\Http\Request;

class AdultoMayorActividadController extends Controller
{
    public function store(StoreActividadAdultoRequest $request, AdultoMayor $adulto_mayor)
    {
        $adulto_mayor->actividades()->create($request->validated());

        activity('Adulto Mayor')
            ->performedOn($adulto_mayor)
            ->log("Se registró una actividad para el adulto mayor: {$adulto_mayor->nombres}");

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'actividades'])->with('success', 'Actividad registrada correctamente.');
    }

    public function update(Request $request, AdultoMayor $adulto_mayor, ActividadAdulto $actividad)
    {
        $request->validate([
            'obs' => 'nullable|string',
            'estado' => 'required|string',
        ]);

        $actividad->update($request->only(['obs', 'estado']));

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'actividades'])->with('success', 'Actividad actualizada correctamente.');
    }

    public function destroy(AdultoMayor $adulto_mayor, ActividadAdulto $actividad)
    {
        $actividad->delete(); // Soft delete

        activity('Adulto Mayor')
            ->performedOn($adulto_mayor)
            ->log("Se anuló una actividad (baja lógica) para la ficha {$adulto_mayor->cod_am}.");

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'actividades'])->with('success', 'Actividad anulada correctamente.');
    }

    public function restore(AdultoMayor $adulto_mayor, $id)
    {
        $actividad = ActividadAdulto::withTrashed()->findOrFail($id);
        $actividad->restore();

        activity('Adulto Mayor')
            ->performedOn($adulto_mayor)
            ->withProperties(['cod_act' => $id])
            ->log("Se restauró una actividad previamente anulada.");

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'actividades'])
            ->with('success', 'Actividad restaurada correctamente.');
    }
}
