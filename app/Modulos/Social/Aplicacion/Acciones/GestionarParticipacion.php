<?php

namespace App\Modulos\Social\Aplicacion\Acciones;

use App\Models\Actividad;
use App\Models\ParticipanteActividad;
use App\Models\User;
use App\Modulos\Institucion\Aplicacion\Consultas\ContextoLaboralService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class GestionarParticipacion
{
    public function guardarActividad(User $usuario, array $entrada): void
    {
        abort_unless($usuario->can('actividades.gestionar'), 403);
        Validator::make($entrada, $this->rules($usuario, $entrada))->validate();
        [$personal, $area] = $this->contextoInstitucional($usuario, $entrada);
        $actividad = Actividad::create([
            'cod_actividad' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('actividades'),
            'cod_area' => $area,
            'cod_personal' => $personal,
            'tipo' => strtoupper(trim($entrada['codTipoAct'])),
            'nombre' => Str::headline($entrada['codTipoAct']),
            'descripcion' => $entrada['obs'] ?: null,
            'fecha_hora' => $entrada['fecha'].' '.$entrada['hora'],
            'estado' => strtoupper(trim($entrada['estado'])),
            'observacion' => $entrada['obs'] ?: null,
        ]);
        ParticipanteActividad::create([
            'cod_participante' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('participantes_actividad'),
            'cod_actividad' => $actividad->cod_actividad,
            'cod_residente' => $entrada['codResidente'],
        ]);
    }

    public function actualizarActividad(User $usuario, array $entrada): void
    {
        abort_unless($usuario->can('actividades.gestionar'), 403);
        Validator::make($entrada, $this->rules($usuario, $entrada))->validate();
        $a = Actividad::with('participantes')->findOrFail($entrada['editandoId']);
        $a->update([
            'tipo' => strtoupper(trim($entrada['codTipoAct'])),
            'nombre' => Str::headline($entrada['codTipoAct']),
            'fecha_hora' => $entrada['fecha'].' '.$entrada['hora'],
            'descripcion' => $entrada['obs'] ?: null,
            'observacion' => $entrada['obs'] ?: null,
            'estado' => strtoupper(trim($entrada['estado'])),
        ]);
        $participante = $a->participantes->first();
        if ($participante) {
            $participante->update(['cod_residente' => $entrada['codResidente']]);
        } else {
            ParticipanteActividad::create([
                'cod_participante' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('participantes_actividad'),
                'cod_actividad' => $a->cod_actividad,
                'cod_residente' => $entrada['codResidente'],
            ]);
        }
    }

    public function cancelarActividad(User $usuario, array $entrada, string $id): void
    {
        abort_unless($usuario->can('actividades.gestionar'), 403);
        $a = Actividad::findOrFail($id);
        $a->update(['estado' => 'CANCELADA']);
    }

    private function contextoInstitucional(User $usuario, array $entrada): array
    {
        try {
            [$personal, $area] = app(ContextoLaboralService::class)->resolver($usuario);
        } catch (\LogicException $exception) {
            throw ValidationException::withMessages(['codTipoAct' => $exception->getMessage()]);
        }

        return [$personal->cod_personal, $area->cod_area];
    }


    protected function rules(User $usuario, array $entrada): array
    {
        return [
            'codResidente' => 'required|exists:residentes,cod_residente',
            'codTipoAct' => 'required|string|max:60',
            'fecha' => 'required|date',
            'hora' => 'required',
            'obs' => 'nullable|string|max:2000',
            'estado' => 'required|string|max:50',
        ];
    }
}
