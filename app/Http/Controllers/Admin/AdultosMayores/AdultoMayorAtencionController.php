<?php

namespace App\Http\Controllers\Admin\AdultosMayores;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdultosMayores\StoreAtencionAdultoRequest;
use App\Models\AdultoMayor;
use App\Models\AtencionAdulto;
use App\Models\TipoAtencionAdulto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdultoMayorAtencionController extends Controller
{
    public function index(AdultoMayor $adulto_mayor)
    {
        $atenciones = $adulto_mayor->atenciones()->with('tipo')->latest()->get();
        return view('admin.adultos-mayores.atenciones.index', compact('adulto_mayor', 'atenciones'));
    }

    public function store(StoreAtencionAdultoRequest $request, AdultoMayor $adulto_mayor)
    {
        $atencion = null;

        DB::transaction(function () use ($request, $adulto_mayor, &$atencion) {
            $data = $request->validated();
            $data['cod_am'] = $adulto_mayor->cod_am;

            $atencion = AtencionAdulto::create($data);

            // Obtener el nombre del tipo de atención para la bitácora
            $tipoNombre = optional(TipoAtencionAdulto::find($data['cod_tipo_aten']))->tipo ?? 'No especificado';

            // Registrar en bitácora de Spatie Activitylog
            activity('Adulto Mayor')
                ->causedBy(auth()->user())
                ->performedOn($adulto_mayor)
                ->event('atencion_registrada')
                ->withProperties([
                    'cod_aten_adul' => $atencion->cod_aten_adul,
                    'tipo'          => $tipoNombre,
                    'fecha'         => $data['fecha'],
                    'hora'          => $data['hora'],
                    'estado'        => $data['estado'],
                ])
                ->log("Se registró una atención de tipo {$tipoNombre} para la ficha {$adulto_mayor->cod_am}.");
        });

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'atenciones'])
            ->with('success', 'Atención registrada correctamente.');
    }

    public function update(Request $request, AdultoMayor $adulto_mayor, AtencionAdulto $atencion)
    {
        $request->validate([
            'obs' => 'nullable|string',
            'estado' => 'required|string',
        ]);

        $atencion->update($request->only(['obs', 'estado']));

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'atenciones'])->with('success', 'Atención actualizada correctamente.');
    }

    public function destroy(AdultoMayor $adulto_mayor, AtencionAdulto $atencion)
    {
        $atencion->delete(); // Soft delete

        activity('Adulto Mayor')
            ->performedOn($adulto_mayor)
            ->log("Se anuló una atención (baja lógica) para la ficha {$adulto_mayor->cod_am}.");

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'atenciones'])->with('success', 'Atención anulada correctamente.');
    }

    public function restore(AdultoMayor $adulto_mayor, $id)
    {
        $atencion = AtencionAdulto::withTrashed()->findOrFail($id);
        $atencion->restore();

        activity('Adulto Mayor')
            ->performedOn($adulto_mayor)
            ->withProperties(['cod_aten' => $id])
            ->log("Se restauró una atención médica previamente anulada.");

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'atenciones'])
            ->with('success', 'Atención restaurada correctamente.');
    }
}
