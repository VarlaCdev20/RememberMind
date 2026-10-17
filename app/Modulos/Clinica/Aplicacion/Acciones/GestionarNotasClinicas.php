<?php

namespace App\Modulos\Clinica\Aplicacion\Acciones;

use App\Models\Atencion;
use App\Models\NotaClinica;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

final class GestionarNotasClinicas
{
    public function store(User $usuario, array $entrada, Residente $adulto_mayor): mixed
    {
        $data = Validator::make($entrada, ['fecha' => ['nullable', 'date', 'before_or_equal:today'], 'tipo_obs' => ['nullable', 'string', 'max:50'], 'descripcion' => 'required|string'])->validate();
        $personal = Personal::where('cod_usuario', $usuario?->cod_usuario)->first();
        $codArea = $personal?->asignaciones()->whereIn('estado', ['ACTIVA', 'ACTIVO'])->latest('fecha_asignacion')->value('cod_area');
        abort_unless($personal && $codArea, 422, 'El usuario debe tener personal y área institucional activa.');
        DB::transaction(function () use ($data, $adulto_mayor, $personal, $codArea): void {
            $atencion = Atencion::query()->create(['cod_atencion' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('atenciones'), 'cod_residente' => $adulto_mayor->cod_residente, 'cod_area' => $codArea, 'cod_personal' => $personal->cod_personal, 'tipo_atencion' => 'NOTA_CLINICA', 'fecha_hora' => ($data['fecha'] ?? today()->toDateString()).' '.now()->format('H:i:s'), 'estado' => 'FINALIZADA', 'observacion' => $data['descripcion']]);
            NotaClinica::create(['cod_nota' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('notas_clinicas'), 'cod_atencion' => $atencion->cod_atencion, 'cod_residente' => $adulto_mayor->cod_residente, 'cod_personal' => $personal->cod_personal, 'tipo_nota' => $data['tipo_obs'] ?? 'GENERAL', 'contenido' => $data['descripcion'], 'fecha_hora' => $atencion->fecha_hora, 'estado' => 'ACTIVA']);
        });

        return null;
    }

    public function update(User $usuario, array $entrada, Residente $adulto_mayor, $observacionId): mixed
    {
        $nota = NotaClinica::where('cod_residente', $adulto_mayor->cod_residente)->where('cod_nota', $observacionId)->firstOrFail();
        $nota->update(['contenido' => data_get($entrada, 'descripcion') ?? data_get($entrada, 'observacion') ?? $nota->contenido, 'tipo_nota' => data_get($entrada, 'tipo_obs') ?? $nota->tipo_nota]);

        return null;
    }

    public function destroy(User $usuario, array $entrada, Residente $adulto_mayor, $observacionId): mixed
    {
        $nota = NotaClinica::where('cod_residente', $adulto_mayor->cod_residente)->where('cod_nota', $observacionId)->firstOrFail();
        $nota->update(['estado' => 'ANULADA']);

        return null;
    }

    public function restore(User $usuario, array $entrada, Residente $adulto_mayor, $observacionId): mixed
    {
        $nota = NotaClinica::where('cod_residente', $adulto_mayor->cod_residente)->where('cod_nota', $observacionId)->firstOrFail();
        $nota->update(['estado' => 'ACTIVA']);

        return null;
    }
}
