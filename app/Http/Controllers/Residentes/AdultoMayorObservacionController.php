<?php

namespace App\Http\Controllers\Residentes;

use App\Http\Controllers\Controller;
use App\Models\AdultoMayor;
use App\Models\ObsAdulto;
use App\Http\Requests\Residentes\StoreObservacionAdultoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdultoMayorObservacionController extends Controller
{
    public function index(AdultoMayor $adulto_mayor)
    {
        $registros = $adulto_mayor->observaciones()->withTrashed()
            ->when(request('buscar'), fn ($q) => $q->whereLike('observacion', '%'.request('buscar').'%'))
            ->latest()->paginate(15)->withQueryString();
        return view('pages.adultos-mayores.observaciones.index', [
            'adulto_mayor' => $adulto_mayor, 'observaciones' => $registros,
        ]);
    }

    public function store(StoreObservacionAdultoRequest $request, AdultoMayor $adulto_mayor)
    {
        $validated = $request->validated();
        
        // Asignar el codigo del adulto mayor y crear el registro
        $validated['cod_am'] = $adulto_mayor->cod_am;
        // Asumiendo que el usuario autenticado está registrando
        $validated['registrado_por'] = auth()->user()?->cod_usu ?? auth()->id();
        $validated['creado_por'] = $validated['registrado_por'];
        $validated['categoria'] = $validated['tipo_obs'] ?? 'GENERAL';
        $validated['observacion'] = $validated['descripcion'] ?? '';
        $validated['nivel_importancia'] = $request->nivel_importancia ?? 'NORMAL';
        $validated['nivel_riesgo'] = $validated['nivel_importancia'];
        
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
        abort_unless($observacion->cod_am === $adulto_mayor->cod_am, 404);
        $request->validate([
            'descripcion' => 'required|string',
            'tipo_obs' => 'required|string',
        ]);

        $data = $request->only(['descripcion', 'tipo_obs', 'nivel_importancia']);
        $data['observacion'] = $data['descripcion'];
        $data['categoria'] = $data['tipo_obs'];
        $data['nivel_riesgo'] = $data['nivel_importancia'] ?? 'NORMAL';

        $observacion->update($data);

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'observaciones'])->with('success', 'Observación actualizada correctamente.');
    }

    public function destroy(AdultoMayor $adulto_mayor, ObsAdulto $observacion)
    {
        abort_unless($observacion->cod_am === $adulto_mayor->cod_am, 404);
        $observacion->delete(); // Soft delete

        activity('Adulto Mayor')
            ->performedOn($adulto_mayor)
            ->log("Se anuló una observación (baja lógica) para la ficha {$adulto_mayor->cod_am}.");

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'observaciones'])->with('success', 'Observación anulada correctamente.');
    }

    public function restore(AdultoMayor $adulto_mayor, $id)
    {
        $observacion = ObsAdulto::withTrashed()->where('cod_am', $adulto_mayor->cod_am)->findOrFail($id);
        $observacion->restore();

        activity('Adulto Mayor')
            ->performedOn($adulto_mayor)
            ->withProperties(['cod_obs' => $id])
            ->log("Se restauró una observación previamente anulada.");

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'observaciones'])
            ->with('success', 'Observación restaurada correctamente.');
    }
}
