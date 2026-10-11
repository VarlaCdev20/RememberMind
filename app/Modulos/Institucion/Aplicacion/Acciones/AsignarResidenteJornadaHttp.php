<?php

namespace App\Modulos\Institucion\Aplicacion\Acciones;

use App\Models\AsignacionResidenteJornada;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\Clinica\Aplicacion\Acciones\AutorizacionClinicaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

final class AsignarResidenteJornadaHttp
{
    private const ROLES_PLAN = ['MEDICO GENERAL/GERIATRA', 'ENFERMEROS', 'PSICOLOGO/A', 'NUTRICIONISTA', 'FISIOTERAPEUTA', 'PEDAGOGO'];

    public function __construct(private readonly AutorizacionClinicaService $autorizacion) {}

    public function asignarJornada(User $usuario, array $entrada, Residente $residente): mixed
    {
        abort_unless($usuario?->hasAnyRole(['SUPERADMINISTRADOR', 'ADMINISTRADOR']), 403, 'La asignación de residentes corresponde a la administración institucional.');
        abort_unless($usuario->can('asignaciones_residente_jornada.gestionar'), 403);
        abort_unless(in_array($residente->estado, ['ACTIVO', 'ADMITIDO', 'EST_001'], true), 409, 'El residente no se encuentra admitido.');
        $datos = Validator::make($entrada, ['cod_jornada' => ['required', 'exists:jornadas,cod_jornada'], 'cod_personal' => ['required', 'exists:personal,cod_personal'], 'nivel_supervision' => ['nullable', 'string', 'max:30'], 'observacion' => ['nullable', 'string']])->validate();
        $jornada = Jornada::query()->whereKey($datos['cod_jornada'])->whereDate('fecha_jornada', today())->whereIn('estado', ['ABIERTA', 'ACTIVA', 'EN_CURSO'])->first();
        abort_unless($jornada, 422, 'La jornada seleccionada no está vigente.');
        $personal = Personal::query()->whereKey($datos['cod_personal'])->where('estado', 'ACTIVO')->first();
        abort_unless($personal, 422, 'El profesional seleccionado no está activo.');
        abort_unless($personal->asignaciones()->where('cod_jornada', $jornada->cod_jornada)->whereIn('estado', ['ACTIVA', 'ACTIVO'])->exists(), 422, 'El profesional no está asignado laboralmente a la jornada seleccionada.');
        $asignacion = DB::transaction(function () use ($residente, $datos, $jornada): AsignacionResidenteJornada {
            Jornada::query()->lockForUpdate()->findOrFail($jornada->cod_jornada);
            $existe = AsignacionResidenteJornada::query()->where('cod_residente', $residente->cod_residente)->where('cod_jornada', $datos['cod_jornada'])->where('cod_personal', $datos['cod_personal'])->whereIn('estado', ['ACTIVA', 'ACTIVO'])->lockForUpdate()->exists();
            abort_if($existe, 409, 'La asignación activa ya existe.');

            return AsignacionResidenteJornada::query()->create(['cod_asignacion' => $this->codigo('ARJ'), 'cod_residente' => $residente->cod_residente, ...$datos, 'fecha_hora' => now(), 'estado' => 'ACTIVA']);
        });

        return $asignacion;
    }

    private function codigo(string $prefijo): string
    {
        return $prefijo.'_'.Str::upper(Str::random(12));
    }
}
