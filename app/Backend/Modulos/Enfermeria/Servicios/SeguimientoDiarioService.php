<?php

namespace App\Backend\Modulos\Enfermeria\Servicios;

use App\Backend\Modulos\Alertas\Servicios\AlertasService;
use App\Models\AsignacionPersonal;
use App\Models\Atencion;
use App\Models\ControlCognitivo;
use App\Models\RegistroConductual;
use App\Models\RegistroEliminacion;
use App\Models\RegistroHidratacion;
use App\Models\RegistroIngesta;
use App\Models\RegistroMovilidad;
use App\Models\RegistroSueno;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class SeguimientoDiarioService
{
    public function __construct(
        private readonly TurnoEnfermeriaService $turnos,
        private readonly MiTurnoService $miTurno,
        private readonly AlertasService $alertas,
    ) {}

    public function guardar(array $datos, User $usuario, ?Atencion $atencion = null): Atencion
    {
        $permiso = $atencion ? 'atenciones.editar' : 'atenciones.crear';
        $this->turnos->autorizarMutacionEnfermeria($datos['cod_residente'], $permiso, $usuario);

        $personal = $usuario->personal;
        if (! $personal || ! in_array($personal->estado, ['ACTIVO', 'ACTIVA'], true)) {
            throw ValidationException::withMessages(['codResidente' => 'El usuario no tiene personal clínico activo asociado.']);
        }

        $jornada = $this->miTurno->resolverJornadaActual($personal, now());
        if (! $jornada) {
            throw ValidationException::withMessages(['codTurno' => 'No existe una jornada clínica activa para el usuario.']);
        }

        $asignacion = AsignacionPersonal::query()
            ->where('cod_personal', $personal->cod_personal)
            ->where('cod_jornada', $jornada->cod_jornada)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
            ->whereNotNull('cod_area')
            ->latest('fecha_asignacion')
            ->first();

        if (! $asignacion) {
            throw ValidationException::withMessages(['codTurno' => 'La jornada activa no tiene un área institucional asignada.']);
        }

        if ($atencion && $atencion->cod_residente !== $datos['cod_residente']) {
            throw ValidationException::withMessages(['codResidente' => 'La corrección no puede cambiar de residente.']);
        }

        $fechaHora = $atencion?->fecha_hora
            ?? Carbon::createFromFormat('Y-m-d H:i', $datos['fecha'].' '.$datos['hora_inicio']);

        return DB::transaction(function () use ($datos, $usuario, $personal, $jornada, $asignacion, $fechaHora, $atencion): Atencion {
            $resumen = $this->construirResumen($datos);
            $valoresAtencion = [
                'cod_residente' => $datos['cod_residente'],
                'cod_area' => $asignacion->cod_area,
                'cod_personal' => $personal->cod_personal,
                'tipo_atencion' => 'SEGUIMIENTO_DIARIO',
                'motivo' => 'SEGUIMIENTO_DIARIO:'.$datos['estado_general'],
                'fecha_hora' => $fechaHora,
                'estado' => 'FINALIZADA',
                'observacion' => $resumen,
            ];

            if ($atencion) {
                $atencion->update($valoresAtencion);
            } else {
                $atencion = Atencion::create($valoresAtencion);
            }

            $comunes = [
                'cod_residente' => $datos['cod_residente'],
                'cod_personal' => $personal->cod_personal,
                'cod_jornada' => $jornada->cod_jornada,
            ];

            $this->actualizarOCrear(
                RegistroIngesta::query()->where($comunes)->where('fecha_hora', $fechaHora),
                ['cod_ingesta' => 'ING_'.Str::upper(Str::random(10))] + $comunes,
                [
                    'fecha_hora' => $fechaHora,
                    'tipo_comida' => $datos['tipo_comida'],
                    'porcentaje_consumido' => $datos['porcentaje_alimentacion'],
                    'apetito' => $datos['alimentacion'],
                    'tolerancia' => $datos['tolerancia_ingesta'] ?: null,
                    'dificultad_deglucion' => $datos['dificultad_deglucion'],
                    'observacion' => $datos['observacion'],
                    'estado' => 'VIGENTE',
                ],
            );

            $this->actualizarOCrear(
                RegistroHidratacion::query()->where($comunes)->where('fecha_hora', $fechaHora),
                ['cod_hidratacion' => 'HID_'.Str::upper(Str::random(10))] + $comunes,
                [
                    'fecha_hora' => $fechaHora,
                    'cantidad_ml' => $datos['cantidad_hidratacion_ml'],
                    'tipo_liquido' => $datos['tipo_liquido'],
                    'tolerancia' => $datos['hidratacion'],
                    'observacion' => $datos['observacion'],
                    'estado' => 'VIGENTE',
                ],
            );

            $this->actualizarOCrear(
                RegistroMovilidad::query()->where('cod_atencion', $atencion->cod_atencion),
                ['cod_movilidad' => 'MOV_'.Str::upper(Str::random(10)), 'cod_atencion' => $atencion->cod_atencion] + $comunes,
                [
                    'fecha_hora' => $fechaHora,
                    'marcha' => $datos['movilidad'],
                    'equilibrio' => $datos['equilibrio'] ?: null,
                    'traslado' => $datos['traslado'] ?: null,
                    'tipo_apoyo' => $datos['tipo_apoyo'] ?: ($datos['movilidad'] === 'INDEPENDIENTE' ? null : $datos['movilidad']),
                    'dispositivo' => $datos['dispositivo'] ?: null,
                    'fatiga' => $datos['fatiga'] ?: null,
                    'riesgo_caida' => $datos['riesgo_caida'] ?: ($datos['intento_caminar_solo'] ? 'INTENTO_CAMINAR_SOLO' : null),
                    'observacion' => $datos['observacion'],
                    'estado' => 'VIGENTE',
                ]
            );

            $this->actualizarOCrear(
                RegistroSueno::query()->where($comunes)->whereDate('fecha', $fechaHora->toDateString()),
                ['cod_registro_sueno' => 'RSU_'.Str::upper(Str::random(10))] + $comunes,
                [
                    'fecha' => $fechaHora->toDateString(),
                    'insomnio' => $datos['sueno'] === 'INSOMNIO',
                    'somnolencia_diurna' => $datos['sueno'] === 'SOMNOLENCIA',
                    'horas_sueno' => $datos['horas_sueno'] !== '' ? $datos['horas_sueno'] : null,
                    'despertares' => $datos['despertares'] !== '' ? $datos['despertares'] : null,
                    'agitacion_nocturna' => $datos['agitacion_nocturna'],
                    'calidad' => $datos['sueno'],
                    'observacion' => $datos['observacion'],
                    'estado' => 'VIGENTE',
                ],
            );

            if ($datos['orientacion'] || $datos['orientacion_lugar'] || $datos['orientacion_tiempo']
                || $datos['memoria_reciente'] || $datos['memoria_remota'] || $datos['atencion_cognitiva']
                || $datos['comprension'] || $datos['lenguaje'] || $datos['sigue_instrucciones']
                || $datos['repite_preguntas'] || $datos['olvida_indicaciones'] || $datos['reconoce_personas']
                || $datos['reconoce_entorno'] || $datos['confusion_observable'] || $datos['cambio_cognitivo']) {
                $orientacion = $datos['orientacion'] ?: null;
                $this->actualizarOCrear(
                    ControlCognitivo::query()->where('cod_atencion', $atencion->cod_atencion),
                    ['cod_control_cognitivo' => 'CCO_'.Str::upper(Str::random(10)), 'cod_atencion' => $atencion->cod_atencion] + $comunes,
                    [
                        'fecha_hora' => $fechaHora,
                        'orientacion_persona' => $orientacion,
                        'orientacion_lugar' => $datos['orientacion_lugar'] ?: $orientacion,
                        'orientacion_tiempo' => $datos['orientacion_tiempo'] ?: $orientacion,
                        'memoria_reciente' => $datos['memoria_reciente'] ?: null,
                        'memoria_remota' => $datos['memoria_remota'] ?: null,
                        'atencion' => $datos['atencion_cognitiva'] ?: null,
                        'comprension' => $datos['comprension'] ?: null,
                        'lenguaje' => $datos['lenguaje'] ?: null,
                        'sigue_instrucciones' => $datos['sigue_instrucciones'],
                        'repite_preguntas' => $datos['repite_preguntas'],
                        'olvida_indicaciones' => $datos['olvida_indicaciones'],
                        'reconoce_personas' => $datos['reconoce_personas'],
                        'reconoce_entorno' => $datos['reconoce_entorno'],
                        'confusion' => $datos['confusion_observable'],
                        'cambio_cognitivo' => $datos['cambio_cognitivo'] || $datos['confusion_observable'],
                        'observacion' => $datos['observacion'],
                        'estado' => 'VIGENTE',
                    ]
                );
            }

            if ($datos['conducta'] || $datos['participacion'] || $datos['cambio_conducta']
                || $datos['apatia'] || $datos['agitacion'] || $datos['agresividad']
                || $datos['ansiedad'] || $datos['aislamiento'] || $datos['deambulacion']) {
                $this->actualizarOCrear(
                    RegistroConductual::query()->where('cod_atencion', $atencion->cod_atencion),
                    ['cod_registro_conductual' => 'RCO_'.Str::upper(Str::random(10)), 'cod_atencion' => $atencion->cod_atencion] + $comunes,
                    [
                        'fecha_hora' => $fechaHora,
                        'estado_animo' => $datos['conducta'] ?: null,
                        'apatia' => $datos['apatia'] || $datos['conducta'] === 'APATICO',
                        'agitacion' => $datos['agitacion'] || $datos['conducta'] === 'AGITADO',
                        'agresividad' => $datos['agresividad'],
                        'ansiedad' => $datos['ansiedad'] || $datos['conducta'] === 'ANSIOSO',
                        'aislamiento' => $datos['aislamiento'],
                        'deambulacion' => $datos['deambulacion'],
                        'participacion' => $datos['participacion'] ?: null,
                        'cambio_conducta' => $datos['cambio_conducta'],
                        'descripcion' => $datos['observacion'],
                        'intervencion' => $datos['intervencion_conducta'] ?: null,
                        'respuesta' => $datos['respuesta_conducta'] ?: null,
                        'estado' => 'VIGENTE',
                    ]
                );
            }

            if ($datos['tipo_eliminacion']) {
                $this->actualizarOCrear(
                    RegistroEliminacion::query()->where($comunes)->where('fecha_hora', $fechaHora),
                    ['cod_eliminacion' => 'ELI_'.Str::upper(Str::random(10))] + $comunes,
                    [
                        'fecha_hora' => $fechaHora,
                        'tipo_eliminacion' => $datos['tipo_eliminacion'],
                        'cantidad' => $datos['cantidad_eliminacion'] ?: null,
                        'caracteristica' => $datos['caracteristica_eliminacion'] ?: null,
                        'continencia' => $datos['continencia'] ?: null,
                        'observacion' => $datos['observacion'],
                        'estado' => 'VIGENTE',
                    ],
                );
            }

            if ($datos['incidente']) {
                $this->alertas->crear($datos['cod_residente'], [
                    'origen' => 'INCIDENTE',
                    'tipo_alerta' => 'INCIDENTE REPORTADO EN SEGUIMIENTO',
                    'nivel' => 'ALTO',
                    'motivo' => $datos['observacion'],
                ], $usuario);
            }

            if ($datos['requiere_medico']) {
                $this->alertas->crear($datos['cod_residente'], [
                    'origen' => 'SOLICITUD_MEDICA',
                    'tipo_alerta' => 'EVALUACION MEDICA REQUERIDA',
                    'nivel' => 'ALTO',
                    'motivo' => $datos['observacion'],
                ], $usuario);
            }

            return $atencion->refresh();
        });
    }

    public function datosEdicion(Atencion $atencion): array
    {
        $ingesta = RegistroIngesta::query()->where('cod_residente', $atencion->cod_residente)
            ->where('cod_personal', $atencion->cod_personal)->where('fecha_hora', $atencion->fecha_hora)->first();
        $hidratacion = RegistroHidratacion::query()->where('cod_residente', $atencion->cod_residente)
            ->where('cod_personal', $atencion->cod_personal)->where('fecha_hora', $atencion->fecha_hora)->first();
        $movilidad = RegistroMovilidad::query()->where('cod_atencion', $atencion->cod_atencion)->first();
        $sueno = RegistroSueno::query()->where('cod_residente', $atencion->cod_residente)
            ->where('cod_personal', $atencion->cod_personal)->whereDate('fecha', $atencion->fecha_hora->toDateString())->first();
        $cognitivo = ControlCognitivo::query()->where('cod_atencion', $atencion->cod_atencion)->first();
        $conductual = RegistroConductual::query()->where('cod_atencion', $atencion->cod_atencion)->first();
        $eliminacion = RegistroEliminacion::query()->where('cod_residente', $atencion->cod_residente)
            ->where('cod_personal', $atencion->cod_personal)->where('fecha_hora', $atencion->fecha_hora)->first();

        return [
            'estado_general' => Str::after((string) $atencion->motivo, 'SEGUIMIENTO_DIARIO:'),
            'tipo_comida' => $ingesta?->tipo_comida,
            'alimentacion' => $ingesta?->apetito,
            'porcentaje_alimentacion' => $ingesta?->porcentaje_consumido,
            'tolerancia_ingesta' => $ingesta?->tolerancia,
            'dificultad_deglucion' => (bool) $ingesta?->dificultad_deglucion,
            'tipo_liquido' => $hidratacion?->tipo_liquido,
            'cantidad_hidratacion_ml' => $hidratacion?->cantidad_ml,
            'hidratacion' => $hidratacion?->tolerancia,
            'movilidad' => $movilidad?->marcha,
            'equilibrio' => $movilidad?->equilibrio,
            'traslado' => $movilidad?->traslado,
            'tipo_apoyo' => $movilidad?->tipo_apoyo,
            'dispositivo' => $movilidad?->dispositivo,
            'fatiga' => $movilidad?->fatiga,
            'riesgo_caida' => $movilidad?->riesgo_caida === 'INTENTO_CAMINAR_SOLO' ? null : $movilidad?->riesgo_caida,
            'intento_caminar_solo' => $movilidad?->riesgo_caida === 'INTENTO_CAMINAR_SOLO',
            'sueno' => $sueno?->calidad,
            'horas_sueno' => $sueno?->horas_sueno,
            'despertares' => $sueno?->despertares,
            'agitacion_nocturna' => (bool) $sueno?->agitacion_nocturna,
            'orientacion' => $cognitivo?->orientacion_persona,
            'orientacion_lugar' => $cognitivo?->orientacion_lugar,
            'orientacion_tiempo' => $cognitivo?->orientacion_tiempo,
            'memoria_reciente' => $cognitivo?->memoria_reciente,
            'memoria_remota' => $cognitivo?->memoria_remota,
            'atencion_cognitiva' => $cognitivo?->atencion,
            'comprension' => $cognitivo?->comprension,
            'lenguaje' => $cognitivo?->lenguaje,
            'sigue_instrucciones' => (bool) $cognitivo?->sigue_instrucciones,
            'repite_preguntas' => (bool) $cognitivo?->repite_preguntas,
            'olvida_indicaciones' => (bool) $cognitivo?->olvida_indicaciones,
            'reconoce_personas' => (bool) $cognitivo?->reconoce_personas,
            'reconoce_entorno' => (bool) $cognitivo?->reconoce_entorno,
            'confusion_observable' => (bool) $cognitivo?->confusion,
            'cambio_cognitivo' => (bool) $cognitivo?->cambio_cognitivo,
            'conducta' => $conductual?->estado_animo,
            'apatia' => (bool) $conductual?->apatia,
            'agitacion' => (bool) $conductual?->agitacion,
            'agresividad' => (bool) $conductual?->agresividad,
            'ansiedad' => (bool) $conductual?->ansiedad,
            'aislamiento' => (bool) $conductual?->aislamiento,
            'deambulacion' => (bool) $conductual?->deambulacion,
            'cambio_conducta' => (bool) $conductual?->cambio_conducta,
            'intervencion_conducta' => $conductual?->intervencion,
            'respuesta_conducta' => $conductual?->respuesta,
            'participacion' => $conductual?->participacion,
            'tipo_eliminacion' => $eliminacion?->tipo_eliminacion,
            'cantidad_eliminacion' => $eliminacion?->cantidad,
            'caracteristica_eliminacion' => $eliminacion?->caracteristica,
            'continencia' => $eliminacion?->continencia,
            'observacion' => $ingesta?->observacion ?? $atencion->observacion,
        ];
    }

    private function actualizarOCrear($consulta, array $atributosCreacion, array $valores): void
    {
        $registro = $consulta->lockForUpdate()->first();
        if ($registro) {
            $registro->update($valores);

            return;
        }

        $registro = $consulta->getModel();
        $registro->fill($atributosCreacion + $valores);
        $registro->save();
    }

    private function construirResumen(array $datos): string
    {
        $campos = [
            'Estado general: '.$datos['estado_general'],
            'Alimentación: '.$datos['alimentacion'].' ('.$datos['porcentaje_alimentacion'].'%)',
            'Hidratación: '.$datos['cantidad_hidratacion_ml'].' ml de '.$datos['tipo_liquido'].'; tolerancia '.$datos['hidratacion'],
            'Movilidad: '.$datos['movilidad'],
            'Sueño: '.$datos['sueno'],
        ];

        foreach (['higiene' => 'Higiene', 'orientacion' => 'Orientación', 'conducta' => 'Conducta', 'participacion' => 'Participación'] as $clave => $etiqueta) {
            if ($datos[$clave]) {
                $campos[] = $etiqueta.': '.$datos[$clave];
            }
        }

        if ($datos['repite_preguntas']) {
            $campos[] = 'Repite preguntas: sí';
        }
        if ($datos['confusion_observable']) {
            $campos[] = 'Confusión observable: sí';
        }
        if ($datos['intento_caminar_solo']) {
            $campos[] = 'Intentó caminar sin ayuda: sí';
        }
        if ($datos['incidente']) {
            $campos[] = 'Alerta de incidente generada: sí';
        }
        if ($datos['requiere_medico']) {
            $campos[] = 'Evaluación médica requerida: sí';
        }

        return implode("\n", $campos)."\n\nObservación de enfermería:\n".trim($datos['observacion']);
    }
}
