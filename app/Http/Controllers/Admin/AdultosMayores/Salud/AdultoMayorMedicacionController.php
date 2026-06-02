<?php

namespace App\Http\Controllers\Admin\AdultosMayores\Salud;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdultosMayores\Salud\StoreMedicacionRequest;
use App\Http\Requests\Admin\AdultosMayores\Salud\UpdateMedicacionRequest;
use App\Models\AdultoMayor;
use App\Models\MedicacionAdulto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AdultoMayorMedicacionController extends Controller
{
    public function store(StoreMedicacionRequest $request, AdultoMayor $adulto_mayor)
    {
        Gate::authorize('viewClinicalData', $adulto_mayor);

        try {
            DB::beginTransaction();

            $medicacion = MedicacionAdulto::create(array_merge(
                $request->validated(),
                [
                    'cod_am'         => $adulto_mayor->cod_am,
                    'registrado_por' => auth()->user()->cod_usu,
                ]
            ));

            DB::commit();

            return redirect()
                ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'medicacion'])
                ->with('success', "Medicación '{$medicacion->nombre_medicamento}' registrada correctamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al registrar medicación: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function update(UpdateMedicacionRequest $request, AdultoMayor $adulto_mayor, MedicacionAdulto $medicacion)
    {
        Gate::authorize('viewClinicalData', $adulto_mayor);
        abort_if($medicacion->cod_am !== $adulto_mayor->cod_am, 403);

        try {
            DB::beginTransaction();
            $medicacion->update($request->validated());
            DB::commit();

            return redirect()
                ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'medicacion'])
                ->with('success', "Medicación '{$medicacion->nombre_medicamento}' actualizada correctamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al actualizar medicación: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function suspender(AdultoMayor $adulto_mayor, MedicacionAdulto $medicacion)
    {
        Gate::authorize('viewClinicalData', $adulto_mayor);
        abort_if($medicacion->cod_am !== $adulto_mayor->cod_am, 403);

        $medicacion->update(['estado' => 'SUSPENDIDO']);

        activity('Medicación')
            ->causedBy(auth()->user())
            ->performedOn($medicacion)
            ->event('suspended')
            ->withProperties(['nombre_medicamento' => $medicacion->nombre_medicamento])
            ->log("Se suspendió la medicación '{$medicacion->nombre_medicamento}' del adulto mayor {$adulto_mayor->cod_am}.");

        return redirect()
            ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'medicacion'])
            ->with('success', "Medicación '{$medicacion->nombre_medicamento}' suspendida.");
    }

    public function finalizar(AdultoMayor $adulto_mayor, MedicacionAdulto $medicacion)
    {
        Gate::authorize('viewClinicalData', $adulto_mayor);
        abort_if($medicacion->cod_am !== $adulto_mayor->cod_am, 403);

        $medicacion->update([
            'estado'    => 'FINALIZADO',
            'fecha_fin' => now()->toDateString(),
        ]);

        activity('Medicación')
            ->causedBy(auth()->user())
            ->performedOn($medicacion)
            ->event('finalized')
            ->withProperties(['nombre_medicamento' => $medicacion->nombre_medicamento])
            ->log("Se finalizó la medicación '{$medicacion->nombre_medicamento}' del adulto mayor {$adulto_mayor->cod_am}.");

        return redirect()
            ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'medicacion'])
            ->with('success', "Medicación '{$medicacion->nombre_medicamento}' finalizada.");
    }

    public function archivar(AdultoMayor $adulto_mayor, MedicacionAdulto $medicacion)
    {
        Gate::authorize('viewClinicalData', $adulto_mayor);
        abort_if($medicacion->cod_am !== $adulto_mayor->cod_am, 403);

        $medicacion->update(['estado' => 'ARCHIVADO']);
        $medicacion->delete(); // SoftDelete

        return redirect()
            ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'medicacion'])
            ->with('success', "Medicación '{$medicacion->nombre_medicamento}' archivada.");
    }

    public function restore(AdultoMayor $adulto_mayor, $medicacion)
    {
        Gate::authorize('viewClinicalData', $adulto_mayor);

        $med = MedicacionAdulto::withTrashed()->findOrFail($medicacion);
        abort_if($med->cod_am !== $adulto_mayor->cod_am, 403);
        $med->restore();
        $med->update(['estado' => 'ACTIVO']);

        return redirect()
            ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'medicacion'])
            ->with('success', "Medicación '{$med->nombre_medicamento}' restaurada.");
    }
}
