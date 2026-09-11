<?php

namespace App\Http\Controllers\Residentes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Residentes\StoreAtencionAdultoRequest;
use App\Models\AdultoMayor;
use App\Models\AtencionAdulto;
use App\Models\TipoAtencionAdulto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdultoMayorAtencionController extends Controller
{
    public function index(AdultoMayor $adulto_mayor)
    {
        $registros = $adulto_mayor->atenciones()->with('tipoAtencion')
            ->when(request('buscar'), fn ($q) => $q->whereLike('observacion', '%'.request('buscar').'%'))
            ->latest()->paginate(15)->withQueryString();
        return view('pages.adultos-mayores.atenciones.index', [
            'adulto_mayor' => $adulto_mayor, 'atenciones' => $registros,
            'tiposAtencion' => \App\Models\TipoAtencionAdulto::all(),
        ]);
    }

    public function store(StoreAtencionAdultoRequest $request, AdultoMayor $adulto_mayor)
    {
        $atencion = null;

        DB::transaction(function () use ($request, $adulto_mayor, &$atencion) {
            $data = $request->validated();
            $data['cod_am'] = $adulto_mayor->cod_am;
            $data['registrado_por'] = auth()->id();

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
        abort_unless($atencion->cod_am === $adulto_mayor->cod_am, 404);
        $request->validate([
            'obs' => 'nullable|string',
            'estado' => 'required|string',
        ]);

        $atencion->update($request->only(['obs', 'estado']));

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'atenciones'])->with('success', 'Atención actualizada correctamente.');
    }

    public function destroy(AdultoMayor $adulto_mayor, AtencionAdulto $atencion)
    {
        abort_unless($atencion->cod_am === $adulto_mayor->cod_am, 404);
        $atencion->update(['estado' => 'ANULADO']);

        activity('Adulto Mayor')
            ->performedOn($adulto_mayor)
            ->log("Se anuló una atención (baja lógica) para la ficha {$adulto_mayor->cod_am}.");

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'atenciones'])->with('success', 'Atención anulada correctamente.');
    }

    public function restore(AdultoMayor $adulto_mayor, $id)
    {
        $atencion = AtencionAdulto::where('cod_am', $adulto_mayor->cod_am)->findOrFail($id);
        $atencion->update(['estado' => 'PENDIENTE']);

        activity('Adulto Mayor')
            ->performedOn($adulto_mayor)
            ->withProperties(['cod_aten' => $id])
            ->log("Se restauró una atención médica previamente anulada.");

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'atenciones'])
            ->with('success', 'Atención restaurada correctamente.');
    }
}
