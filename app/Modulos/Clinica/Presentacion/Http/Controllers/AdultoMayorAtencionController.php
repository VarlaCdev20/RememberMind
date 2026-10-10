<?php

namespace App\Modulos\Clinica\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Residente;
use App\Modulos\Clinica\Aplicacion\Acciones\GestionarAtenciones;
use Illuminate\Http\Request;

class AdultoMayorAtencionController extends Controller
{
    protected function getTiposAtencion()
    {
        return collect([(object) ['cod_tipo_aten' => 'MEDICA', 'nombre' => 'Médica', 'tipo' => 'Médica'], (object) ['cod_tipo_aten' => 'ENFERMERIA', 'nombre' => 'Enfermería', 'tipo' => 'Enfermería'], (object) ['cod_tipo_aten' => 'PSICOLOGIA', 'nombre' => 'Psicológica', 'tipo' => 'Psicológica'], (object) ['cod_tipo_aten' => 'NUTRICION', 'nombre' => 'Nutricional', 'tipo' => 'Nutricional'], (object) ['cod_tipo_aten' => 'FISIOTERAPIA', 'nombre' => 'Fisioterapia', 'tipo' => 'Fisioterapia'], (object) ['cod_tipo_aten' => 'SOCIAL', 'nombre' => 'Trabajo Social', 'tipo' => 'Trabajo Social']]);
    }

    public function index(Residente $adulto_mayor)
    {
        $query = $adulto_mayor->atenciones();
        if (request('buscar')) {
            $buscar = request('buscar');
            $query->where(function ($q) use ($buscar) {
                $q->where('observacion', 'ilike', "%{$buscar}%")->orWhere('tipo_atencion', 'ilike', "%{$buscar}%")->orWhere('motivo', 'ilike', "%{$buscar}%");
            });
        }
        $registros = $query->orderByDesc('fecha_hora')->paginate(15)->withQueryString();

        return view('pages.adultos-mayores.atenciones.index', ['adulto_mayor' => $adulto_mayor, 'atenciones' => $registros, 'tiposAtencion' => $this->getTiposAtencion()]);
    }

    public function store(Request $request, Residente $adulto_mayor)
    {
        app(GestionarAtenciones::class)->store(auth()->user(), $request->all(), $adulto_mayor);

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'atenciones'])->with('success', 'Atención registrada correctamente.');
    }

    public function update(Request $request, Residente $adulto_mayor, $atencionId)
    {
        app(GestionarAtenciones::class)->update(auth()->user(), $request->all(), $adulto_mayor, $atencionId);

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'atenciones'])->with('success', 'Atención actualizada correctamente.');
    }

    public function destroy(Residente $adulto_mayor, $atencionId)
    {
        app(GestionarAtenciones::class)->destroy(auth()->user(), [], $adulto_mayor, $atencionId);

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'atenciones'])->with('success', 'Atención anulada correctamente.');
    }

    public function restore(Residente $adulto_mayor, $atencionId)
    {
        app(GestionarAtenciones::class)->restore(auth()->user(), [], $adulto_mayor, $atencionId);

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'atenciones'])->with('success', 'Atención restaurada correctamente.');
    }
}
