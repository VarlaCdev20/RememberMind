<?php

namespace App\Modulos\Social\Aplicacion\Acciones;

use App\Models\Actividad;
use App\Models\ParticipanteActividad;
use App\Models\Residente;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

final class GestionarActividadResidenteHttp
{
    public function store(User $usuario, array $entrada, Residente $adulto_mayor): mixed
    {
        abort_unless($usuario->can('actividades.gestionar'), 403);
        $data = $entrada;
        [$personal, $codArea] = $this->contextoPersonal($usuario);
        DB::transaction(function () use ($data, $adulto_mayor, $personal, $codArea): void {
            $actividad = Actividad::query()->create(['cod_actividad' => 'ACT_'.Str::upper(Str::random(10)), 'cod_area' => $codArea, 'cod_personal' => $personal->cod_personal, 'tipo' => Str::upper($data['cod_tipo_act']), 'nombre' => Str::title(str_replace('_', ' ', $data['cod_tipo_act'])), 'descripcion' => $data['obs'] ?? null, 'fecha_hora' => $data['fecha'].' '.$data['hora'], 'estado' => Str::upper($data['estado']), 'observacion' => $data['obs'] ?? null]);
            ParticipanteActividad::query()->create(['cod_participante' => 'PAR_'.Str::upper(Str::random(10)), 'cod_actividad' => $actividad->cod_actividad, 'cod_residente' => $adulto_mayor->cod_residente, 'asistencia' => 'PROGRAMADA', 'observacion' => $data['obs'] ?? null]);
        });

        return null;
    }

    public function update(User $usuario, array $entrada, Residente $adulto_mayor, Actividad $actividad): mixed
    {
        abort_unless($usuario->can('actividades.gestionar'), 403);
        $data = Validator::make($entrada, ['obs' => ['nullable', 'string', 'max:2000'], 'estado' => ['required', 'in:PROGRAMADA,EN_CURSO,REALIZADA,FINALIZADA,CANCELADA,ANULADA,ACTIVA']])->validate();
        $this->autorizarRelacion($adulto_mayor, $actividad);
        $actividad->update(['observacion' => $data['obs'] ?? null, 'descripcion' => $data['obs'] ?? $actividad->descripcion, 'estado' => $data['estado']]);

        return null;
    }

    public function destroy(User $usuario, array $entrada, Residente $adulto_mayor, Actividad $actividad): mixed
    {
        abort_unless($usuario->can('actividades.gestionar'), 403);
        $this->autorizarRelacion($adulto_mayor, $actividad);
        $actividad->update(['estado' => 'ANULADA']);

        return null;
    }

    public function restore(User $usuario, array $entrada, Residente $adulto_mayor, string $actividad): mixed
    {
        abort_unless($usuario->can('actividades.gestionar'), 403);
        $registro = Actividad::query()->findOrFail($actividad);
        $this->autorizarRelacion($adulto_mayor, $registro);
        $registro->update(['estado' => 'PROGRAMADA']);

        return null;
    }

    private function contextoPersonal(User $usuario): array
    {
        $personal = $usuario?->personal;
        $codArea = $personal?->asignaciones()->whereIn('estado', ['ACTIVA', 'ACTIVO'])->latest('fecha_asignacion')->value('cod_area');
        abort_unless($personal && $codArea, 422, 'El usuario debe tener personal y área institucional activa.');

        return [$personal, $codArea];
    }

    private function autorizarRelacion(Residente $adulto, Actividad $actividad): void
    {
        abort_unless(ParticipanteActividad::query()->where('cod_actividad', $actividad->cod_actividad)->where('cod_residente', $adulto->cod_residente)->exists(), 404);
    }
}
