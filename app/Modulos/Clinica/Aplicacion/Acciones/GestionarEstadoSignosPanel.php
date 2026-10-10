<?php

namespace App\Modulos\Clinica\Aplicacion\Acciones;

use App\Models\SignoVital;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

final class GestionarEstadoSignosPanel
{
    public function confirmarAnular(User $usuario, array $entrada): void
    {

        abort_if($usuario?->hasRole('ENFERMEROS'), 403, 'Los registros firmados se rectifican; no se eliminan ni restauran desde Enfermería.');
        abort_if(! $entrada['adulto'], 403, 'Paciente no seleccionado.');
        abort_if(! $usuario->can('signos_vitales.anular'), 403);

        Validator::make($entrada, [
            'motivoAnulacion' => 'required|string|min:10|max:500',
        ], [
            'motivoAnulacion.required' => 'El motivo de anulación es obligatorio.',
            'motivoAnulacion.min' => 'El motivo debe tener al menos 10 caracteres.',
        ])->validate();

        $signo = SignoVital::findOrFail($entrada['signoIdAnular']);
        abort_if($signo->cod_residente !== $entrada['adulto']->cod_residente, 403);

        $signo->descripcionLog = "Anuló signos vitales del adulto mayor {$entrada['adulto']->cod_residente}.";
        $signo->update([
            'estado' => 'ANULADO',
            'motivo_anulacion' => $entrada['motivoAnulacion'],
            'anulado_por' => $usuario->cod_usuario,
            'fecha_anulacion' => now(),
        ]);

    }

    public function restaurarRegistro(User $usuario, array $entrada, string $id): void
    {

        abort_if($usuario?->hasRole('ENFERMEROS'), 403, 'Los registros firmados se rectifican; no se eliminan ni restauran desde Enfermería.');
        abort_if(! $entrada['adulto'], 403, 'Paciente no seleccionado.');
        abort_if(! $usuario->can('signos_vitales.anular'), 403);

        $signo = SignoVital::findOrFail($id);
        abort_if($signo->cod_residente !== $entrada['adulto']->cod_residente, 403);

        $signo->descripcionLog = "Restauró signos vitales del adulto mayor {$entrada['adulto']->cod_residente}.";
        $signo->update([
            'estado' => 'VIGENTE',
            'motivo_anulacion' => null,
            'anulado_por' => null,
            'fecha_anulacion' => null,
        ]);

    }

    public function anularConMotivo(User $usuario, array $entrada, string $id, string $motivo): void
    {
        abort_if($usuario->hasRole('ENFERMEROS'), 403, 'Los registros firmados se rectifican; no se eliminan ni restauran desde Enfermería.');
        abort_if(! $entrada['adulto'], 403, 'Paciente no seleccionado.');
        abort_unless($usuario->can('signos_vitales.anular'), 403);
        abort_if(mb_strlen(trim($motivo)) < 10, 422);
        $signo = SignoVital::findOrFail($id);
        abort_if($signo->cod_residente !== $entrada['adulto']->cod_residente, 403);

        $signo->descripcionLog = "Anuló signos vitales del adulto mayor {$entrada['adulto']->cod_residente}.";
        $signo->update([
            'estado' => 'ANULADO',
            'motivo_anulacion' => $motivo,
            'anulado_por' => $usuario->cod_usuario,
            'fecha_anulacion' => now(),
        ]);

    }
}
