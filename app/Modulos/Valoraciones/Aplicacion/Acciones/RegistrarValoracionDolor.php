<?php

namespace App\Modulos\Valoraciones\Aplicacion\Acciones;

use App\Models\User;
use App\Models\ValoracionDolor;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

final class RegistrarValoracionDolor
{
    public function guardarDolor(User $usuario, array $entrada): void
    {
        $this->autorizarMutacion($usuario, $entrada['codResidente']);
        Validator::make($entrada, ['faseDolor' => 'required|in:VALORACION,INTERVENCION,REEVALUACION', 'intensidadDolor' => 'required|integer|min:0|max:10', 'detalleDolor' => 'required|string|min:5|max:2000', 'resultadoDolor' => 'required_if:faseDolor,REEVALUACION|nullable|string|min:3|max:1000'])->validate();
        $personal = $usuario?->personal;
        abort_unless($personal, 403, 'Personal clínico no vinculado.');
        ValoracionDolor::create(['cod_valoracion_dolor' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('valoraciones_dolor'), 'cod_residente' => $entrada['codResidente'], 'cod_personal' => $personal->cod_personal, 'fecha_hora' => now(), 'intensidad' => (int) $entrada['intensidadDolor'], 'ubicacion' => $entrada['detalleDolor'], 'intervencion' => $entrada['resultadoDolor'] ?: null, 'estado' => 'ACTIVA', 'observacion' => null]);
    }

    private function autorizarMutacion(User $usuario, string $codResidente): void
    {
        app(TurnoEnfermeriaService::class)->autorizarMutacionPaciente($codResidente, 'atenciones.crear', $usuario);
    }
}
