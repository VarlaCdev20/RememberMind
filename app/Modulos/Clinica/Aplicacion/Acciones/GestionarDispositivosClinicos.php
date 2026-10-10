<?php

namespace App\Modulos\Clinica\Aplicacion\Acciones;

use App\Models\DispositivoClinico;
use App\Models\User;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

final class GestionarDispositivosClinicos
{
    public function guardarDispositivo(User $usuario, array $entrada): void
    {
        $this->autorizarMutacion($usuario, $entrada['codResidente']);
        Validator::make($entrada, ['tipoDispositivo' => 'required|in:OXIGENO,SONDA_URINARIA,OSTOMIA,ALIMENTACION_ENTERAL,OTRO,SONDA_VESICAL', 'ubicacionDispositivo' => 'nullable|string|max:120', 'indicacionDispositivo' => 'required|string|min:5|max:1000'], ['indicacionDispositivo.required' => 'Registre la indicación clínica del dispositivo.'])->validate();
        $personal = $usuario?->personal;
        abort_unless($personal, 403, 'Personal clínico no vinculado.');
        DispositivoClinico::create(['cod_dispositivo' => 'DIS_'.strtoupper(Str::random(10)), 'cod_residente' => $entrada['codResidente'], 'cod_personal' => $personal->cod_personal, 'tipo' => $entrada['tipoDispositivo'], 'ubicacion' => $entrada['ubicacionDispositivo'] ?: null, 'descripcion' => $entrada['indicacionDispositivo'] ?: null, 'fecha_colocacion' => now(), 'estado' => 'ACTIVO', 'observacion' => null]);
    }

    public function retirarDispositivo(User $usuario, array $entrada, string $codigo): void
    {
        $this->autorizarMutacion($usuario, $entrada['codResidente']);
        Validator::make($entrada, ['motivoRetiroDispositivo' => 'required|string|min:5|max:1000'], ['motivoRetiroDispositivo.required' => 'El motivo del retiro es obligatorio.'])->validate();
        $dispositivo = DispositivoClinico::where('cod_residente', $entrada['codResidente'])->findOrFail($codigo);
        abort_unless($dispositivo->estado === 'ACTIVO', 409, 'El dispositivo ya fue retirado.');
        $dispositivo->update(['estado' => 'RETIRADO', 'fecha_retiro' => now(), 'observacion' => trim(($dispositivo->observacion ? $dispositivo->observacion.' | ' : '').'Retiro: '.$entrada['motivoRetiroDispositivo'])]);
    }

    private function autorizarMutacion(User $usuario, string $codResidente): void
    {
        app(TurnoEnfermeriaService::class)->autorizarMutacionPaciente($codResidente, 'atenciones.crear', $usuario);
    }
}
