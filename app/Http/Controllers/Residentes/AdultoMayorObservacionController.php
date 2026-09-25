<?php

namespace App\Http\Controllers\Residentes;

use App\Http\Controllers\Controller;
use App\Models\AdultoMayor;
use App\Models\Atencion;
use App\Models\NotaClinica;
use App\Models\Personal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdultoMayorObservacionController extends Controller
{
    public function index(AdultoMayor $adulto_mayor)
    {
        $query = $adulto_mayor->observaciones();
        if (request('buscar')) {
            $buscar = request('buscar');
            $query->where('contenido', 'ilike', "%{$buscar}%");
        }
        $registros = $query->orderByDesc('fecha_hora')->paginate(15)->withQueryString();

        return view('pages.adultos-mayores.observaciones.index', [
            'adulto_mayor' => $adulto_mayor,
            'observaciones' => $registros,
        ]);
    }

    public function store(Request $request, AdultoMayor $adulto_mayor)
    {
        $data = $request->validate([
            'fecha' => ['nullable', 'date', 'before_or_equal:today'],
            'tipo_obs' => ['nullable', 'string', 'max:50'],
            'descripcion' => 'required|string',
        ]);

        $personal = Personal::where('cod_usuario', auth()->user()?->cod_usuario)->first();
        $codArea = $personal?->asignaciones()
            ->whereIn('estado', ['ACTIVA', 'ACTIVO'])
            ->latest('fecha_asignacion')
            ->value('cod_area');
        abort_unless($personal && $codArea, 422, 'El usuario debe tener personal y área institucional activa.');

        DB::transaction(function () use ($data, $adulto_mayor, $personal, $codArea): void {
            $atencion = Atencion::query()->create([
                'cod_atencion' => 'ATN_' . strtoupper(Str::random(10)),
                'cod_residente' => $adulto_mayor->cod_residente,
                'cod_area' => $codArea,
                'cod_personal' => $personal->cod_personal,
                'tipo_atencion' => 'NOTA_CLINICA',
                'fecha_hora' => ($data['fecha'] ?? today()->toDateString()) . ' ' . now()->format('H:i:s'),
                'estado' => 'FINALIZADA',
                'observacion' => $data['descripcion'],
            ]);

            NotaClinica::create([
                'cod_nota' => 'NOT_' . strtoupper(Str::random(10)),
                'cod_atencion' => $atencion->cod_atencion,
                'cod_residente' => $adulto_mayor->cod_residente,
                'cod_personal' => $personal->cod_personal,
                'tipo_nota' => $data['tipo_obs'] ?? 'GENERAL',
                'contenido' => $data['descripcion'],
                'fecha_hora' => $atencion->fecha_hora,
                'estado' => 'ACTIVA',
            ]);
        });

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'observaciones'])
            ->with('success', 'Observación registrada correctamente.');
    }

    public function update(Request $request, AdultoMayor $adulto_mayor, $observacionId)
    {
        $nota = NotaClinica::where('cod_residente', $adulto_mayor->cod_residente)
            ->where('cod_nota', $observacionId)
            ->firstOrFail();

        $nota->update([
            'contenido' => $request->input('descripcion') ?? $request->input('observacion') ?? $nota->contenido,
            'tipo_nota' => $request->input('tipo_obs') ?? $nota->tipo_nota,
        ]);

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'observaciones'])
            ->with('success', 'Observación actualizada correctamente.');
    }

    public function destroy(AdultoMayor $adulto_mayor, $observacionId)
    {
        $nota = NotaClinica::where('cod_residente', $adulto_mayor->cod_residente)
            ->where('cod_nota', $observacionId)
            ->firstOrFail();

        $nota->update(['estado' => 'ANULADA']);

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'observaciones'])
            ->with('success', 'Observación anulada correctamente.');
    }

    public function restore(AdultoMayor $adulto_mayor, $observacionId)
    {
        $nota = NotaClinica::where('cod_residente', $adulto_mayor->cod_residente)
            ->where('cod_nota', $observacionId)
            ->firstOrFail();

        $nota->update(['estado' => 'ACTIVA']);

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'observaciones'])
            ->with('success', 'Observación restaurada correctamente.');
    }
}
