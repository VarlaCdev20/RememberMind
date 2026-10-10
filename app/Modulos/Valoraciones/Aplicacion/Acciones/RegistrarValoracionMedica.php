<?php

namespace App\Modulos\Valoraciones\Aplicacion\Acciones;

use App\Models\Atencion;
use App\Models\HistorialEstadoResidente;
use App\Models\NotaClinica;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\Clinica\Aplicacion\Acciones\AccesoClinicoTemporalService;
use App\Modulos\Clinica\Aplicacion\Consultas\ContextoClinicoService;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class RegistrarValoracionMedica
{
    public function ejecutar(User $usuario, array $datos): void
    {
        abort_unless($this->puedeRegistrar($usuario), 403);
        $observacion = trim(implode("\n", array_filter([
            $datos['diagnosticosReferidos'] ? 'Diagnósticos referidos: '.$datos['diagnosticosReferidos'] : null,
            $datos['antecedentesRelevantes'] ? 'Antecedentes: '.$datos['antecedentesRelevantes'] : null,
            $datos['medicacionActualResumen'] ? 'Medicación actual: '.$datos['medicacionActualResumen'] : null,
            $datos['condicionMedicaGeneral'] ? 'Condición general: '.$datos['condicionMedicaGeneral'] : null,
            $datos['estadoNeurologicoBasico'] ? 'Estado neurológico: '.$datos['estadoNeurologicoBasico'] : null,
            $datos['nivelDependenciaSugerido'] ? 'Dependencia sugerida: '.$datos['nivelDependenciaSugerido'] : null,
            $datos['resultadoAdmision'] ? 'Resultado admisión: '.$datos['resultadoAdmision'] : null,
            $datos['motivoDecision'] ? 'Motivo: '.$datos['motivoDecision'] : null,
            $datos['recomendacionMedica'] ? 'Recomendación: '.$datos['recomendacionMedica'] : null,
        ])));

        $contextoClinico = app(ContextoClinicoService::class);
        $personal = $contextoClinico->personalActivo($usuario);
        $codPersonal = $personal->cod_personal;
        $codArea = $contextoClinico->areaAtencion($personal)->cod_area;

        $fechaHora = Carbon::parse($datos['fecha'].' '.$datos['hora']);
        $estadoAtencion = $datos['estadoForm'] === 'COMPLETADA' ? 'COMPLETADA' : 'BORRADOR';

        DB::transaction(function () use ($observacion, $codPersonal, $codArea, $fechaHora, $estadoAtencion, $datos, $usuario) {
            if ($datos['editandoId']) {
                $atencion = Atencion::findOrFail($datos['editandoId']);
                $atencion->update([
                    'fecha_hora' => $fechaHora,
                    'estado' => $estadoAtencion,
                    'observacion' => $observacion,
                ]);
            } else {
                $codAtencion = 'ATN_'.strtoupper(Str::random(10));
                $atencion = Atencion::create([
                    'cod_atencion' => $codAtencion,
                    'cod_residente' => $datos['codResidente'],
                    'cod_area' => $codArea,
                    'cod_personal' => $codPersonal,
                    'tipo_atencion' => 'VALORACION_MEDICA',
                    'motivo' => 'Valoración médica de admisión ('.$datos['resultadoAdmision'].')',
                    'fecha_hora' => $fechaHora,
                    'estado' => $estadoAtencion,
                    'observacion' => $observacion,
                ]);

                NotaClinica::create([
                    'cod_nota' => 'NOT_'.strtoupper(Str::random(10)),
                    'cod_atencion' => $codAtencion,
                    'cod_residente' => $datos['codResidente'],
                    'cod_personal' => $codPersonal,
                    'tipo_nota' => 'VALORACION_MEDICA',
                    'contenido' => $observacion,
                    'fecha_hora' => $fechaHora,
                    'estado' => 'REGISTRADA',
                ]);

                if ($datos['estadoForm'] === 'COMPLETADA' && $datos['resultadoAdmision'] === 'ADMITIDO') {
                    $residente = Residente::find($datos['codResidente']);
                    if ($residente) {
                        $estadoAnterior = $residente->estado;
                        $residente->update(['estado' => 'ACTIVO']);
                        HistorialEstadoResidente::create([
                            'cod_historial_estado' => 'HER_'.strtoupper(Str::random(10)),
                            'cod_residente' => $datos['codResidente'],
                            'cod_usuario_registro' => $usuario->cod_usuario,
                            'estado_anterior' => $estadoAnterior,
                            'estado_nuevo' => 'ACTIVO',
                            'fecha_hora' => now(),
                            'motivo' => 'Admisión aprobada en valoración médica: '.$datos['motivoDecision'],
                        ]);
                    }
                }
            }
        });

    }

    public function puedeRegistrar(?User $usuario): bool
    {

        return $usuario?->estado === 'ACTIVO'
            && app(AccesoClinicoTemporalService::class)
                ->tieneRol($usuario, ['MEDICO GENERAL/GERIATRA'])
            && $usuario->can('atenciones.crear')
            && $usuario->can('notas_clinicas.crear')
            && ! app(RolePreviewService::class)->isActive($usuario);
    }
}
