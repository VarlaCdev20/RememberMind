<?php

declare(strict_types=1);

namespace App\Modulos\Valoraciones\Aplicacion\Acciones;

use App\Compartido\Aplicacion\Contratos\CodigosEntidad;
use App\Models\Atencion;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\Clinica\Aplicacion\Acciones\AutorizacionClinicaService;
use App\Modulos\Valoraciones\Aplicacion\Consultas\ContextoAtencionCognitiva;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

/** Conserva una sesión y el contexto recabado en ese episodio; sin interpretación instrumental. */
final class RegistrarSesionPsicologica
{
    public function registrar(?User $usuario, Residente $residente, array $entrada): Atencion
    {
        return DB::transaction(function () use ($usuario, $residente, $entrada): Atencion {
            $usuario = $usuario ? User::query()->find($usuario->getKey()) : null;
            abort_unless($usuario?->estado === 'ACTIVO' && ! $usuario->hasRole('FAMILIAR'), 403);
            // Mismo primer bloqueo que la gestión de red de apoyo: serializa cambios del vínculo del residente.
            $residente = Residente::query()->lockForUpdate()->findOrFail($residente->getKey());
            Gate::forUser($usuario)->authorize('view', $residente);
            $autorizacion = app(AutorizacionClinicaService::class);
            $personal = $autorizacion->autorizarMutacion($usuario, $residente, 'atenciones.crear', ['PSICOLOGO/A']);
            abort_unless($usuario->hasRole('PSICOLOGO/A') && $personal->profesion === 'PSICOLOGIA', 403);
            $area = $autorizacion->areaActiva($personal);

            foreach ([...Atencion::CONTEXTO_ESCOLARIDAD, ...Atencion::CONTEXTO_IDIOMA_INFORMANTE] as $campo) {
                if (is_string($entrada[$campo] ?? null)) {
                    $entrada[$campo] = trim($entrada[$campo]);
                }
                if (($entrada[$campo] ?? null) === '') {
                    $entrada[$campo] = null;
                }
            }
            $reglas = ['tipo_atencion' => ['required', 'string', 'max:60'],
                'motivo' => ['required', 'string', 'min:3', 'max:255'],
                'observacion' => ['nullable', 'string', 'max:1000'],
                'escolaridad_anios' => ['nullable', 'integer', 'min:0', 'max:32767'],
                'puede_leer' => ['nullable', 'boolean'], 'puede_escribir' => ['nullable', 'boolean'],
                'idioma_principal_evaluacion' => ['nullable', 'string', 'max:80', "regex:/^[\\p{L}\\p{M}][\\p{L}\\p{M} .'’()\\-]*$/u"],
                'cod_residente_contacto_informante' => ['nullable', 'string', 'max:20'],
                'informante_conoce_nivel_previo' => ['nullable', 'boolean']];
            foreach (['cod_atencion', 'cod_residente', 'cod_personal', 'cod_usuario_registro', 'cod_area', 'fecha_hora', 'estado'] as $protegido) {
                $reglas[$protegido] = ['prohibited'];
            }
            $datos = Validator::make($entrada, $reglas, [], ['escolaridad_anios' => 'años de escolaridad informados',
                'puede_leer' => 'capacidad referida para leer', 'puede_escribir' => 'capacidad referida para escribir',
                'idioma_principal_evaluacion' => 'idioma principal de la evaluación',
                'cod_residente_contacto_informante' => 'informante principal',
                'informante_conoce_nivel_previo' => 'conocimiento del nivel previo'])->validate();
            if (isset($datos['informante_conoce_nivel_previo']) && ! isset($datos['cod_residente_contacto_informante'])) {
                throw \Illuminate\Validation\ValidationException::withMessages(['informante_conoce_nivel_previo' => 'Selecciona primero al informante que aportó esta información.']);
            }
            if (isset($datos['cod_residente_contacto_informante'])) {
                app(ContextoAtencionCognitiva::class)->validarInformante($usuario, $residente, $datos['cod_residente_contacto_informante']);
            }
            $atencion = Atencion::create([
                'cod_atencion' => app(CodigosEntidad::class)->siguiente('atenciones'),
                'cod_residente' => $residente->getKey(), 'cod_personal' => $personal->getKey(), 'cod_area' => $area->getKey(),
                'tipo_atencion' => $datos['tipo_atencion'], 'motivo' => $datos['motivo'],
                'observacion' => ($datos['observacion'] ?? '') === '' ? null : $datos['observacion'],
                'fecha_hora' => now(), 'estado' => 'FINALIZADA',
                'escolaridad_anios' => isset($datos['escolaridad_anios']) ? (int) $datos['escolaridad_anios'] : null,
                'puede_leer' => isset($datos['puede_leer']) ? (bool) $datos['puede_leer'] : null,
                'puede_escribir' => isset($datos['puede_escribir']) ? (bool) $datos['puede_escribir'] : null,
                'idioma_principal_evaluacion' => $datos['idioma_principal_evaluacion'] ?? null,
                'cod_residente_contacto_informante' => $datos['cod_residente_contacto_informante'] ?? null,
                'informante_conoce_nivel_previo' => isset($datos['informante_conoce_nivel_previo']) ? (bool) $datos['informante_conoce_nivel_previo'] : null,
            ]);
            activity('valoraciones')->causedBy($usuario)->performedOn($atencion)->event('sesion_psicologica_registrada')
                ->log('Sesión psicológica conservada con autoría profesional y contexto del episodio.');

            return $atencion;
        });
    }
}
