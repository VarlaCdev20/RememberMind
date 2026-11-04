<?php

namespace App\Modulos\Valoraciones\Aplicacion\Acciones;

use App\Models\Atencion;
use App\Models\HistorialEstadoResidente;
use App\Models\NotaClinica;
use App\Models\SignoVital;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class RegistrarValoracionMedicaAdmision
{
    public function ejecutar(User $usuario, array $datos): void
    {
        [$personal, $codArea] = $this->validarContexto($usuario);

        DB::transaction(function () use ($usuario, $datos, $personal, $codArea) {
            $estadoAnterior = $datos['adulto']->estado;
            $datos['adulto']->update(['estado' => 'DECISION_ADMISION']);

            HistorialEstadoResidente::create([
                'cod_residente' => $datos['adulto']->cod_residente,
                'cod_usuario_registro' => $usuario->getKey(),
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => 'DECISION_ADMISION',
                'motivo' => 'Valoración médica general completada.',
            ]);

            $atencion = Atencion::create([
                'cod_residente' => $datos['adulto']->cod_residente,
                'cod_area' => $codArea,
                'cod_personal' => $personal->cod_personal,
                'tipo_atencion' => 'VALORACION_MEDICA_ADMISION',
                'motivo' => 'Valoración médica para decisión de admisión',
                'fecha_hora' => now(),
                'estado' => 'FINALIZADA',
                'observacion' => $datos['observacion_medica'],
            ]);

            NotaClinica::create([
                'cod_nota' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('notas_clinicas'),
                'cod_atencion' => $atencion->cod_atencion,
                'cod_residente' => $datos['adulto']->cod_residente,
                'cod_personal' => $atencion->cod_personal,
                'tipo_nota' => 'VALORACION_MEDICA_ADMISION',
                'contenido' => $datos['observacion_medica'],
                'fecha_hora' => now(),
                'estado' => 'VIGENTE',
            ]);

            if ($datos['pa_sistolica'] && $datos['fc'] && $datos['temp']) {
                SignoVital::create([
                    'cod_residente' => $datos['adulto']->cod_residente,
                    'cod_personal' => $atencion->cod_personal,
                    'cod_atencion' => $atencion->cod_atencion,
                    'fecha_hora' => now(),
                    'presion_sistolica' => $datos['pa_sistolica'],
                    'presion_diastolica' => $datos['pa_diastolica'],
                    'frecuencia_cardiaca' => $datos['fc'],
                    'frecuencia_respiratoria' => $datos['fr'],
                    'temperatura' => $datos['temp'],
                    'saturacion_oxigeno' => $datos['sato2'],
                    'estado' => 'VIGENTE',
                    'observacion' => 'Signos vitales de valoración médica de admisión.',
                ]);
            }

            // Registrar Log
            activity('Medico')
                ->causedBy($usuario)
                ->performedOn($datos['adulto'])
                ->withProperties([
                    'condicion_medica' => $datos['condicion_medica_general'],
                    'estado_cognitivo' => $datos['estado_cognitivo_aparente'],
                    'nivel_dependencia' => $datos['nivel_dependencia'],
                    'signos_vitales' => [
                        'PA' => $datos['pa_sistolica'].'/'.$datos['pa_diastolica'],
                        'FC' => $datos['fc'],
                        'FR' => $datos['fr'],
                        'Temp' => $datos['temp'],
                        'SatO2' => $datos['sato2'],
                    ],
                    'observacion' => $datos['observacion_medica'],
                ])
                ->log('Completó valoración médica general.');

        });
    }

    public function validarContexto(User $usuario): array
    {
        $personal = $usuario->personal;
        $codArea = $personal?->asignaciones()->whereIn('estado', ['ACTIVA', 'ACTIVO'])
            ->latest('fecha_asignacion')->value('cod_area');
        abort_unless($personal && $codArea, 422, 'El usuario debe tener personal y área institucional asignados.');

        return [$personal, $codArea];
    }
}
