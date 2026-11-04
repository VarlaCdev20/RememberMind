<?php

namespace App\Modulos\Social\Aplicacion\Acciones;

use App\Models\Actividad;
use App\Models\ParticipanteActividad;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

final class GestionarActividadesHttp
{
    public function store(User $usuario, array $entrada): mixed
    {
        abort_unless($usuario->can('actividades.gestionar'), 403);
        $personal = $usuario->personal()->where('estado', 'ACTIVO')->firstOrFail();
        $datos = Validator::make($entrada, ['cod_area' => ['required', 'exists:areas,cod_area'], 'tipo' => ['required', 'string', 'max:60'], 'nombre' => ['required', 'string', 'max:160'], 'descripcion' => ['nullable', 'string'], 'fecha_hora' => ['required', 'date'], 'duracion_minutos' => ['nullable', 'integer', 'min:1'], 'lugar' => ['nullable', 'string', 'max:120'], 'cupo' => ['nullable', 'integer', 'min:1'], 'observacion' => ['nullable', 'string']])->validate();

        return Actividad::query()->create(['cod_actividad' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('actividades'), 'cod_personal' => $personal->cod_personal, ...$datos, 'estado' => 'PROGRAMADA']);
    }

    public function participante(User $usuario, array $entrada, Actividad $actividad): mixed
    {
        abort_unless($usuario->can('actividades.gestionar'), 403);
        $datos = Validator::make($entrada, ['cod_residente' => ['required', 'exists:residentes,cod_residente'], 'asistencia' => ['nullable', 'string', 'max:30'], 'nivel_participacion' => ['nullable', 'string', 'max:40'], 'desempeno' => ['nullable', 'string', 'max:40'], 'observacion' => ['nullable', 'string']])->validate();
        abort_if(ParticipanteActividad::query()->where('cod_actividad', $actividad->cod_actividad)->where('cod_residente', $datos['cod_residente'])->exists(), 409, 'El residente ya participa en la actividad.');

        return ParticipanteActividad::query()->create(['cod_participante' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('participantes_actividad'), 'cod_actividad' => $actividad->cod_actividad, ...$datos]);
    }

}
