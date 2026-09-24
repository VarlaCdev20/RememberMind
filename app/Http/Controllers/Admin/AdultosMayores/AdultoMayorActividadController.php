<?php

namespace App\Http\Controllers\Admin\AdultosMayores;

use App\Http\Controllers\Controller;
use App\Http\Requests\Residentes\StoreActividadAdultoRequest;
use App\Models\Actividad;
use App\Models\AdultoMayor;
use App\Models\ParticipanteActividad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AdultoMayorActividadController extends Controller
{
    public function store(StoreActividadAdultoRequest $request, AdultoMayor $adulto_mayor): RedirectResponse
    {
        $data = $request->validated();
        [$personal, $codArea] = $this->contextoPersonal();

        DB::transaction(function () use ($data, $adulto_mayor, $personal, $codArea): void {
            $actividad = Actividad::query()->create([
                'cod_actividad' => 'ACT_' . Str::upper(Str::random(10)),
                'cod_area' => $codArea,
                'cod_personal' => $personal->cod_personal,
                'tipo' => Str::upper($data['cod_tipo_act']),
                'nombre' => Str::title(str_replace('_', ' ', $data['cod_tipo_act'])),
                'descripcion' => $data['obs'] ?? null,
                'fecha_hora' => $data['fecha'] . ' ' . $data['hora'],
                'estado' => Str::upper($data['estado']),
                'observacion' => $data['obs'] ?? null,
            ]);

            ParticipanteActividad::query()->create([
                'cod_participante' => 'PAR_' . Str::upper(Str::random(10)),
                'cod_actividad' => $actividad->cod_actividad,
                'cod_residente' => $adulto_mayor->cod_residente,
                'asistencia' => 'PROGRAMADA',
                'observacion' => $data['obs'] ?? null,
            ]);
        });

        return $this->volver($adulto_mayor)->with('success', 'Actividad registrada correctamente.');
    }

    public function update(Request $request, AdultoMayor $adulto_mayor, Actividad $actividad): RedirectResponse
    {
        $data = $request->validate([
            'obs' => ['nullable', 'string', 'max:2000'],
            'estado' => ['required', 'in:PROGRAMADA,EN_CURSO,REALIZADA,FINALIZADA,CANCELADA,ANULADA,ACTIVA'],
        ]);
        $this->autorizarRelacion($adulto_mayor, $actividad);

        $actividad->update([
            'observacion' => $data['obs'] ?? null,
            'descripcion' => $data['obs'] ?? $actividad->descripcion,
            'estado' => $data['estado'],
        ]);

        return $this->volver($adulto_mayor)->with('success', 'Actividad actualizada correctamente.');
    }

    public function destroy(AdultoMayor $adulto_mayor, Actividad $actividad): RedirectResponse
    {
        $this->autorizarRelacion($adulto_mayor, $actividad);
        $actividad->update(['estado' => 'ANULADA']);

        return $this->volver($adulto_mayor)->with('success', 'Actividad anulada correctamente.');
    }

    public function restore(AdultoMayor $adulto_mayor, string $actividad): RedirectResponse
    {
        $registro = Actividad::query()->findOrFail($actividad);
        $this->autorizarRelacion($adulto_mayor, $registro);
        $registro->update(['estado' => 'PROGRAMADA']);

        return $this->volver($adulto_mayor)->with('success', 'Actividad restaurada correctamente.');
    }

    private function contextoPersonal(): array
    {
        $personal = auth()->user()?->personal;
        $codArea = $personal?->asignaciones()
            ->whereIn('estado', ['ACTIVA', 'ACTIVO'])
            ->latest('fecha_asignacion')
            ->value('cod_area');
        abort_unless($personal && $codArea, 422, 'El usuario debe tener personal y área institucional activa.');

        return [$personal, $codArea];
    }

    private function autorizarRelacion(AdultoMayor $adulto, Actividad $actividad): void
    {
        abort_unless(
            ParticipanteActividad::query()
                ->where('cod_actividad', $actividad->cod_actividad)
                ->where('cod_residente', $adulto->cod_residente)
                ->exists(),
            404
        );
    }

    private function volver(AdultoMayor $adulto): RedirectResponse
    {
        return new RedirectResponse(route('admin.adultos-mayores.show', [
            'adulto_mayor' => $adulto->cod_residente,
            'tab' => 'actividades',
        ]));
    }
}
