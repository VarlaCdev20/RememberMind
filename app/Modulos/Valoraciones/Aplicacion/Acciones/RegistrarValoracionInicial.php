<?php

namespace App\Modulos\Valoraciones\Aplicacion\Acciones;

use App\Compartido\Aplicacion\Contratos\CodigosEntidad;
use App\Models\Preadmision;
use App\Models\User;
use App\Modulos\Clinica\Aplicacion\Acciones\AccesoClinicoTemporalService;
use App\Modulos\Valoraciones\Infraestructura\Persistencia\BloqueValoracionInicial as Bloque;
use Illuminate\Support\Facades\DB;

final class RegistrarValoracionInicial
{
    public function ejecutar(User $usuario, array $datos): string
    {
        $user = $usuario;
        if (! $user || $user->estado !== 'ACTIVO') {
            abort(403, 'Usuario no autenticado o inactivo.');
        }

        if (! app(AccesoClinicoTemporalService::class)
            ->tieneRol($user, ['ENFERMEROS'])) {
            abort(403, 'El usuario no posee el rol institucional de ENFERMEROS.');
        }

        $personal = $user->personal;
        if (! $personal) {
            abort(403, 'El usuario no tiene un profesional institucional asociado.');
        }

        if ($personal->estado !== 'ACTIVO') {
            abort(403, 'El profesional asociado no se encuentra activo.');
        }

        abort_if(empty($datos['preadmision']), 422, 'Se requiere una preadmisión.');

        return DB::transaction(function () use ($datos, $user, $personal): string {
            $preadmision = Preadmision::query()->lockForUpdate()->findOrFail($datos['preadmision']->getKey());
            abort_unless($preadmision->estado === 'PENDIENTE', 422, 'La preadmisión debe estar pendiente.');

            $codigo = $preadmision->getAttribute(Bloque::CODIGO);
            $esEdicion = $codigo !== null;
            $permiso = $esEdicion ? 'valoracion_enfermeria.editar' : 'valoracion_enfermeria.registrar';
            abort_unless($user->can($permiso), 403, 'No posee el permiso requerido para la valoración.');
            abort_unless($codigo === ($datos['valoracionId'] ?? null), 409, 'La valoración cambió. Abra el formulario de nuevo.');

            $registroOriginal = $preadmision->valoracionEnfermeriaRegistro();

            $orientacionCombinada = "Persona: {$datos['orientacion_persona']}, Tiempo: {$datos['orientacion_tiempo']}, Espacio: {$datos['orientacion_espacio']}";
            $valoracion = [
                'fecha_hora' => $esEdicion ? $registroOriginal->getRawOriginal('fecha_hora') : now()->toDateTimeString(),
                'estado_general' => $datos['estado_general'],
                'nivel_conciencia' => $datos['nivel_conciencia'],
                'orientacion_persona' => $datos['orientacion_persona'],
                'orientacion_tiempo' => $datos['orientacion_tiempo'],
                'orientacion_espacio' => $datos['orientacion_espacio'],
                'comunicacion' => $datos['comunicacion'],
                'hay_dolor' => (bool) $datos['hay_dolor'],
                'intensidad_dolor' => $datos['hay_dolor'] ? $datos['intensidad_dolor'] : null,
                'ubicacion_dolor' => $datos['hay_dolor'] ? $datos['ubicacion_dolor'] : null,
                'movilidad' => $datos['movilidad'],
                'apoyo_movilidad' => $datos['apoyo_movilidad'],
                'riesgo_caida' => $datos['riesgo_caida'],
                'piel_estado' => $datos['piel_estado'],
                'hay_heridas' => (bool) $datos['hay_heridas'],
                'ubicacion_heridas' => $datos['hay_heridas'] ? $datos['ubicacion_heridas'] : null,
                'higiene_ingreso' => $datos['higiene_ingreso'],
                'continencia_basica' => $datos['continencia_basica'],
                'alimentacion_aparente' => $datos['alimentacion_aparente'],
                'pa_sistolica' => $datos['pa_sistolica'],
                'pa_diastolica' => $datos['pa_diastolica'],
                'frecuencia_cardiaca' => $datos['frecuencia_cardiaca'],
                'frecuencia_respiratoria' => $datos['frecuencia_respiratoria'],
                'temperatura' => $datos['temperatura'],
                'saturacion_oxigeno' => $datos['saturacion_oxigeno'],
                'peso' => $datos['peso'],
                'talla' => $datos['talla'],
                'antecedentes_relevantes' => $datos['antecedentes_relevantes'],
                'medicacion_referida' => $datos['medicacion_referida'],
                'alergias_referidas' => $datos['alergias_referidas'],
                'dependencia_funcional' => $datos['dependencia_funcional'],
                'riesgo_nutricional' => $datos['riesgo_nutricional'],
                'riesgo_cognitivo' => $datos['riesgo_cognitivo'],
                'tipo_evaluacion_cognitiva' => 'CRIBADO_OBSERVACIONAL_ENFERMERIA',
                'necesidad_apoyo_inmediato' => $datos['necesidad_apoyo_inmediato'],
                'prioridad_sugerida' => $datos['prioridad_sugerida'],
                'confirmacion_documentacion' => (bool) $datos['confirmacion_documentacion'],
                'comentarios_adicionales' => $datos['comentarios_adicionales'],
                'recomendacion_enfermeria' => $datos['recomendacion_enfermeria'],
                'cod_personal_valorador' => $esEdicion ? $registroOriginal->cod_personal_valorador : $personal->cod_personal,
                'cod_usuario_registro' => $esEdicion ? $registroOriginal->cod_usuario_registro : $user->cod_usuario,
            ];

            $valoracion[Bloque::CODIGO] = $codigo ?? app(CodigosEntidad::class)->siguiente(Bloque::ORIGEN);
            $preadmision->forceFill(['prioridad' => $datos['prioridad_sugerida']] + Bloque::atributos($valoracion))->save();

            activity('Enfermeria')
                ->causedBy($user)
                ->performedOn($preadmision)
                ->withProperties([
                    'cod_preadmision' => $preadmision->cod_preadmision,
                    'cod_valoracion_enfermeria' => $valoracion[Bloque::CODIGO],
                    'cod_personal_valorador' => $personal->cod_personal,
                    'cod_usuario_registro' => $user->cod_usuario,
                    'estado_general' => $datos['estado_general'],
                    'orientacion' => $orientacionCombinada,
                    'movilidad' => $datos['movilidad'],
                    'riesgo_caida' => $datos['riesgo_caida'],
                    'hay_dolor' => $datos['hay_dolor'],
                    'recomendacion' => $datos['recomendacion_enfermeria'],
                ])
                ->log($esEdicion ? 'Editó la valoración inicial de Enfermería.' : 'Registró la valoración inicial de Enfermería.');

            return $valoracion[Bloque::CODIGO];
        });
    }
}
