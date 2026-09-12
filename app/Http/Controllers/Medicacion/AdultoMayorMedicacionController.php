<?php

namespace App\Http\Controllers\Medicacion;

use App\Http\Controllers\Controller;
use App\Http\Requests\Medicacion\StoreMedicacionRequest;
use App\Http\Requests\Medicacion\UpdateMedicacionRequest;
use App\Models\AdultoMayor;
use App\Models\MedicacionAdulto;
use Illuminate\Support\Facades\DB;

class AdultoMayorMedicacionController extends Controller
{
    public function store(StoreMedicacionRequest $request, AdultoMayor $adulto_mayor)
    {
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
        abort_unless($medicacion->cod_am === $adulto_mayor->cod_am, 404);
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
        $this->autorizarOrden($adulto_mayor, $medicacion, ['medicacion.suspender', 'salud.medicacion.suspender']);
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
        $this->autorizarOrden($adulto_mayor, $medicacion, ['medicacion.suspender', 'salud.medicacion.finalizar']);
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
        $this->autorizarOrden($adulto_mayor, $medicacion, ['medicacion.suspender', 'salud.medicacion.anular']);
        $medicacion->update(['estado' => 'ARCHIVADO']);
        $medicacion->delete(); // SoftDelete

        return redirect()
            ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'medicacion'])
            ->with('success', "Medicación '{$medicacion->nombre_medicamento}' archivada.");
    }

    public function restore(AdultoMayor $adulto_mayor, $medicacion)
    {
        $med = MedicacionAdulto::withTrashed()->findOrFail($medicacion);
        $this->autorizarOrden($adulto_mayor, $med, ['medicacion.editar', 'salud.medicacion.editar']);
        $med->restore();
        $med->update(['estado' => 'ACTIVO']);

        return redirect()
            ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'medicacion'])
            ->with('success', "Medicación '{$med->nombre_medicamento}' restaurada.");
    }

    private function autorizarOrden(AdultoMayor $adulto, MedicacionAdulto $medicacion, array $permisos): void
    {
        abort_unless($medicacion->cod_am === $adulto->cod_am, 404);
        abort_if(auth()->user()?->hasRole('ENFERMEROS'), 403, 'Enfermería no puede modificar órdenes médicas.');
        abort_unless(auth()->user()?->canAny($permisos), 403);
    }
}
