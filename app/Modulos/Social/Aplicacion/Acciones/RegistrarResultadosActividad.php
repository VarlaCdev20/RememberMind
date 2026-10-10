<?php

namespace App\Modulos\Social\Aplicacion\Acciones;

use App\Models\Actividad;
use Illuminate\Support\Facades\Validator;

final class RegistrarResultadosActividad
{
    public function marcarRealizada(array $entrada, string $id): void
    {
        Actividad::findOrFail($id)->update(['estado' => 'REALIZADA']);
    }

    public function marcarCancelada(array $entrada, string $id): void
    {
        Actividad::findOrFail($id)->update(['estado' => 'CANCELADA']);
    }

    public function guardarResultado(array $entrada): void
    {
        Validator::make($entrada, $this->rules($entrada))->validate();
        Actividad::findOrFail($entrada['editandoId'])->update([
            'estado' => strtoupper(trim($entrada['estado'])),
            'observacion' => $entrada['obs'] ?: null,
            'descripcion' => $entrada['obs'] ?: null,
        ]);
    }

    protected function rules(array $entrada): array
    {
        return [
            'estado' => 'required|string|max:50',
            'obs' => 'nullable|string|max:2000',
        ];
    }
}
