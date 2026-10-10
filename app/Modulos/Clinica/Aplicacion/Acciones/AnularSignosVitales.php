<?php

namespace App\Modulos\Clinica\Aplicacion\Acciones;

use App\Models\Residente;
use App\Models\SignoVital;
use App\Models\User;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use Illuminate\Support\Facades\DB;

final class AnularSignosVitales
{
    public function anular(User $usuario, array $entrada, Residente $adulto_mayor, SignoVital $signo): mixed
    {
        abort_unless($signo->cod_residente === $adulto_mayor->cod_residente, 404);
        app(TurnoEnfermeriaService::class)->autorizarMutacionPaciente($adulto_mayor, 'signos_vitales.anular', $usuario);
        DB::transaction(function () use ($signo, $adulto_mayor, $usuario) {
            $signo = SignoVital::query()->lockForUpdate()->findOrFail($signo->getKey());
            abort_unless(in_array($signo->estado, ['ACTIVO', 'VIGENTE'], true), 409);
            $signo->update(['estado' => 'ANULADO']);
            activity('Signos Vitales')->causedBy($usuario)->performedOn($signo)->event('anulado')->log("Se anuló el registro de signos vitales {$signo->cod_signo} del residente {$adulto_mayor->cod_residente}.");
        });

        return null;
    }
}
