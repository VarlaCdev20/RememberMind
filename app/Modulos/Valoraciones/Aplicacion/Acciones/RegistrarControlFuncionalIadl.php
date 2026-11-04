<?php

declare(strict_types=1);

namespace App\Modulos\Valoraciones\Aplicacion\Acciones;

use App\Models\Atencion;
use App\Models\ControlFuncionalIadl as Iadl;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\FuentesValoracionOperativa;
use App\Modulos\Clinica\Aplicacion\Acciones\AutorizacionClinicaService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** Una actividad por acto; rectificación append-only, sin puntuación ni inferencia. */
final class RegistrarControlFuncionalIadl
{
    public function validar(User $usuario, Residente $residente, array $entrada, ?string $anterior = null): array
    {
        Gate::forUser($usuario)->authorize($anterior === null ? 'registrar' : 'rectificar', [Iadl::class, $residente]);
        $reglas = ['fecha_hora' => ['required', 'date_format:Y-m-d\TH:i', 'before_or_equal:now'],
            'actividad' => ['required', Rule::in(Iadl::ACTIVIDADES)], 'oportunidad' => ['required', Rule::in(Iadl::OPORTUNIDADES)],
            'desempeno' => ['required', Rule::in(Iadl::DESEMPENOS)], 'tipo_ayuda' => ['required', Rule::in(Iadl::AYUDAS)],
            'causa_ayuda' => ['required', Rule::in(Iadl::CAUSAS)], 'nivel_previo' => ['required', Rule::in(Iadl::NIVELES_PREVIOS)],
            'fuente' => ['required', Rule::in(Iadl::FUENTES)], 'cod_atencion' => ['nullable', 'string', 'max:20'],
            'observacion' => [$anterior === null ? 'nullable' : 'required', 'string', 'max:4000']];
        foreach (['cod_personal', 'cod_usuario_registro', 'cod_residente', 'estado', 'registrado_en', 'cod_control_anterior', 'cod_control_iadl'] as $protegido) {
            $reglas[$protegido] = ['prohibited'];
        }
        $datos = Validator::make($entrada, $reglas, [], [
            'fecha_hora' => 'fecha y hora de la observación', 'tipo_ayuda' => 'ayuda recibida', 'causa_ayuda' => 'motivo de la ayuda',
            'nivel_previo' => 'funcionamiento previo', 'cod_atencion' => 'atención vinculada', 'observacion' => 'observación y motivo de la rectificación',
        ])->validate();
        $datos = array_intersect_key($datos, array_flip(['fecha_hora', 'actividad', 'oportunidad', 'desempeno', 'tipo_ayuda', 'causa_ayuda', 'nivel_previo', 'fuente', 'cod_atencion', 'observacion']));
        $datos['cod_atencion'] = ($datos['cod_atencion'] ?? '') === '' ? null : $datos['cod_atencion'];
        $datos['observacion'] = ($datos['observacion'] ?? '') === '' ? null : $datos['observacion'];
        if ($anterior !== null && trim((string) $datos['observacion']) === '') {
            throw ValidationException::withMessages(['observacion' => 'Explica el motivo de la rectificación.']);
        }
        if ($datos['cod_atencion'] !== null && ! Atencion::query()->whereKey($datos['cod_atencion'])->where('cod_residente', $residente->getKey())->exists()) {
            throw ValidationException::withMessages(['cod_atencion' => 'La atención seleccionada no pertenece a este residente.']);
        }
        $datos['fecha_hora'] = CarbonImmutable::createFromFormat('Y-m-d\TH:i', $datos['fecha_hora'])->startOfMinute()->format('Y-m-d H:i:s');

        return $datos;
    }

    public function registrar(User $usuario, Residente $residente, array $entrada, string $codigo, ?string $anterior = null): Iadl
    {
        return DB::transaction(function () use ($usuario, $residente, $entrada, $codigo, $anterior): Iadl {
            $usuario = User::query()->findOrFail($usuario->getKey());
            $residente = FuentesValoracionOperativa::residentes(Residente::query())->lockForUpdate()->findOrFail($residente->getKey());
            $datos = $this->validar($usuario, $residente, $entrada, $anterior);
            $personal = app(AutorizacionClinicaService::class)->autorizarMutacion($usuario, $residente,
                $anterior === null ? 'controles_funcionales_iadl.crear' : 'controles_funcionales_iadl.rectificar', ['MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A']);
            abort_unless(($personal->profesion === 'MEDICINA' && $usuario->hasRole('MEDICO GENERAL/GERIATRA'))
                || ($personal->profesion === 'PSICOLOGIA' && $usuario->hasRole('PSICOLOGO/A')), 403);
            $contenido = ['cod_residente' => $residente->getKey(), 'cod_personal' => $personal->getKey(), 'cod_usuario_registro' => $usuario->getKey(),
                'cod_control_anterior' => $anterior, 'estado' => $anterior === null ? 'REGISTRADO' : 'RECTIFICACION', ...$datos];
            if ($existente = Iadl::query()->whereKey($codigo)->first()) {
                foreach ($contenido as $campo => $valor) {
                    $guardado = $campo === 'fecha_hora' ? $existente->fecha_hora->format('Y-m-d H:i:s') : $existente->getAttribute($campo);
                    if ($guardado !== $valor) {
                        throw ValidationException::withMessages(['registro' => 'Esta solicitud ya se utilizó. Abre un registro nuevo para conservar otra observación.']);
                    }
                }

                return $existente;
            }
            if ($anterior !== null) {
                $original = Iadl::query()->where('cod_residente', $residente->getKey())->lockForUpdate()->findOrFail($anterior);
                if (! in_array($original->estado, ['REGISTRADO', 'RECTIFICACION'], true) || Iadl::query()->where('cod_control_anterior', $anterior)->exists()) {
                    throw ValidationException::withMessages(['registro' => 'Este registro ya tiene una rectificación. Revisa la versión vigente antes de continuar.']);
                }
            }
            $nuevo = Iadl::query()->create(['cod_control_iadl' => $codigo, ...$contenido, 'registrado_en' => now()]);
            activity('Funcionamiento cotidiano')->performedOn($nuevo)->causedBy($usuario)
                ->withProperties(['operacion' => $anterior === null ? 'REGISTRO' : 'RECTIFICACION', 'cod_control_anterior' => $anterior])
                ->log($anterior === null ? 'Observación funcional registrada' : 'Observación funcional rectificada sin sobrescribir el original');

            return $nuevo;
        });
    }
}
