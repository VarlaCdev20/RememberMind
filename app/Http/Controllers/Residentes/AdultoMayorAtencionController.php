<?php

namespace App\Http\Controllers\Residentes;

use App\Http\Controllers\Controller;
use App\Models\AdultoMayor;
use App\Models\Atencion;
use App\Models\Personal;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdultoMayorAtencionController extends Controller
{
    protected function getTiposAtencion()
    {
        return collect([
            (object)['cod_tipo_aten' => 'MEDICA', 'nombre' => 'Médica', 'tipo' => 'Médica'],
            (object)['cod_tipo_aten' => 'ENFERMERIA', 'nombre' => 'Enfermería', 'tipo' => 'Enfermería'],
            (object)['cod_tipo_aten' => 'PSICOLOGIA', 'nombre' => 'Psicológica', 'tipo' => 'Psicológica'],
            (object)['cod_tipo_aten' => 'NUTRICION', 'nombre' => 'Nutricional', 'tipo' => 'Nutricional'],
            (object)['cod_tipo_aten' => 'FISIOTERAPIA', 'nombre' => 'Fisioterapia', 'tipo' => 'Fisioterapia'],
            (object)['cod_tipo_aten' => 'SOCIAL', 'nombre' => 'Trabajo Social', 'tipo' => 'Trabajo Social'],
        ]);
    }

    public function index(AdultoMayor $adulto_mayor)
    {
        $query = $adulto_mayor->atenciones();
        if (request('buscar')) {
            $buscar = request('buscar');
            $query->where(function ($q) use ($buscar) {
                $q->where('observacion', 'ilike', "%{$buscar}%")
                  ->orWhere('tipo_atencion', 'ilike', "%{$buscar}%")
                  ->orWhere('motivo', 'ilike', "%{$buscar}%");
            });
        }
        $registros = $query->orderByDesc('fecha_hora')->paginate(15)->withQueryString();

        return view('pages.adultos-mayores.atenciones.index', [
            'adulto_mayor' => $adulto_mayor,
            'atenciones' => $registros,
            'tiposAtencion' => $this->getTiposAtencion(),
        ]);
    }

    public function store(Request $request, AdultoMayor $adulto_mayor)
    {
        $request->validate([
            'fecha' => 'required|date',
            'hora' => 'required',
            'cod_tipo_aten' => 'required',
        ]);

        $fechaHora = $request->input('fecha') . ' ' . $request->input('hora');
        $personal = Personal::where('cod_usuario', auth()->user()?->cod_usuario)->first();

        Atencion::create([
            'cod_atencion' => 'ATN_' . strtoupper(Str::random(10)),
            'cod_residente' => $adulto_mayor->cod_residente,
            'cod_personal' => $personal?->cod_personal,
            'tipo_atencion' => $request->input('cod_tipo_aten'),
            'motivo' => $request->input('motivo') ?? 'Atención programada',
            'fecha_hora' => $fechaHora,
            'estado' => $request->input('estado', 'PENDIENTE'),
            'observacion' => $request->input('obs'),
        ]);

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'atenciones'])
            ->with('success', 'Atención registrada correctamente.');
    }

    public function update(Request $request, AdultoMayor $adulto_mayor, $atencionId)
    {
        $atencion = Atencion::where('cod_residente', $adulto_mayor->cod_residente)
            ->where('cod_atencion', $atencionId)
            ->firstOrFail();

        $atencion->update([
            'estado' => $request->input('estado', $atencion->estado),
            'observacion' => $request->input('obs', $atencion->observacion),
        ]);

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'atenciones'])
            ->with('success', 'Atención actualizada correctamente.');
    }

    public function destroy(AdultoMayor $adulto_mayor, $atencionId)
    {
        $atencion = Atencion::where('cod_residente', $adulto_mayor->cod_residente)
            ->where('cod_atencion', $atencionId)
            ->firstOrFail();

        $atencion->update(['estado' => 'ANULADO']);

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'atenciones'])
            ->with('success', 'Atención anulada correctamente.');
    }

    public function restore(AdultoMayor $adulto_mayor, $atencionId)
    {
        $atencion = Atencion::where('cod_residente', $adulto_mayor->cod_residente)
            ->where('cod_atencion', $atencionId)
            ->firstOrFail();

        $atencion->update(['estado' => 'PENDIENTE']);

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'atenciones'])
            ->with('success', 'Atención restaurada correctamente.');
    }
}