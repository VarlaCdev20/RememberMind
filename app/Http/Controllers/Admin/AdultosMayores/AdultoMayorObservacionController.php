<?php

namespace App\Http\Controllers\Admin\AdultosMayores;

use App\Http\Controllers\Controller;
use App\Models\AdultoMayor;
use App\Models\ObsAdulto;
use App\Http\Requests\Admin\AdultosMayores\StoreObservacionAdultoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdultoMayorObservacionController extends Controller
{
    public function index(AdultoMayor $adulto_mayor)
    {
        $observaciones = $adulto_mayor->observaciones()->latest()->get();
        return view('admin.adultos-mayores.observaciones.index', compact('adulto_mayor', 'observaciones'));
    }

    public function store(StoreObservacionAdultoRequest $request, AdultoMayor $adulto_mayor)
    {
        $validated = $request->validated();
        
        // Asignar el codigo del adulto mayor y crear el registro
        $validated['cod_am'] = $adulto_mayor->cod_am;
        // Asumiendo que el usuario autenticado está registrando
        $validated['registrado_por'] = auth()->id();
        $validated['nivel_importancia'] = $request->nivel_importancia ?? 'NORMAL'; 
        
        $observacion = null;
        
        DB::transaction(function () use ($validated, &$observacion, $adulto_mayor) {
            $observacion = ObsAdulto::create($validated);
            
            activity('adulto_mayor')
                ->causedBy(auth()->user())
                ->performedOn($observacion) 
                ->event('observacion_creada')
                ->log("Se registró una observación de tipo {$observacion->tipo_obs} para el adulto mayor {$adulto_mayor->nombres} con ficha {$adulto_mayor->cod_am}.");
        });

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'observaciones'])
            ->with('success', 'Observación registrada correctamente.');
    }

    public function update(Request $request, AdultoMayor $adulto_mayor, ObsAdulto $observacion)
    {
        $request->validate([
            'descripcion' => 'required|string',
            'tipo_obs' => 'required|string',
        ]);

        $observacion->update($request->only(['descripcion', 'tipo_obs', 'nivel_importancia']));

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'observaciones'])->with('success', 'Observación actualizada correctamente.');
    }

    public function destroy(AdultoMayor $adulto_mayor, ObsAdulto $observacion)
    {
        $observacion->delete(); // Soft delete

        activity('Adulto Mayor')
            ->performedOn($adulto_mayor)
            ->log("Se anuló una observación (baja lógica) para la ficha {$adulto_mayor->cod_am}.");

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'observaciones'])->with('success', 'Observación anulada correctamente.');
    }

    public function restore(AdultoMayor $adulto_mayor, $id)
    {
        $observacion = ObsAdulto::withTrashed()->findOrFail($id);
        $observacion->restore();

        activity('Adulto Mayor')
            ->performedOn($adulto_mayor)
            ->withProperties(['cod_obs' => $id])
            ->log("Se restauró una observación previamente anulada.");

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'observaciones'])
            ->with('success', 'Observación restaurada correctamente.');
    }
}
