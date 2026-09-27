<?php

namespace App\Backend\Modulos\Enfermeria\Servicios;

use App\Backend\Modulos\Alertas\Servicios\AlertasService;
use App\Models\AsignacionPersonal;
use App\Models\Atencion;
use App\Models\ControlCognitivo;
use App\Models\RegistroConductual;
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
                    'tipo_apoyo' => $datos['movilidad'] === 'INDEPENDIENTE' ? null : $datos['movilidad'],
                    'riesgo_caida' => $datos['intento_caminar_solo'] ? 'INTENTO_CAMINAR_SOLO' : null,
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
                    'calidad' => $datos['sueno'],
                    'observacion' => $datos['observacion'],
                    'estado' => 'VIGENTE',
                ],
            );

            if ($datos['orientacion'] || $datos['repite_preguntas'] || $datos['confusion_observable']) {
                $orientacion = $datos['orientacion'] ?: null;
                $this->actualizarOCrear(
                    ControlCognitivo::query()->where('cod_atencion', $atencion->cod_atencion),
                    ['cod_control_cognitivo' => 'CCO_'.Str::upper(Str::random(10)), 'cod_atencion' => $atencion->cod_atencion] + $comunes,
                    [
                        'fecha_hora' => $fechaHora,
                        'orientacion_persona' => $orientacion,
                        'orientacion_lugar' => $orientacion,
                        'orientacion_tiempo' => $orientacion,
                        'repite_preguntas' => $datos['repite_preguntas'],
                        'confusion' => $datos['confusion_observable'],
                        'cambio_cognitivo' => $datos['confusion_observable'],
                        'observacion' => $datos['observacion'],
                        'estado' => 'VIGENTE',
                    ]
                );
            }

            if ($datos['conducta'] || $datos['participacion']) {
                $this->actualizarOCrear(
                    RegistroConductual::query()->where('cod_atencion', $atencion->cod_atencion),
                    ['cod_registro_conductual' => 'RCO_'.Str::upper(Str::random(10)), 'cod_atencion' => $atencion->cod_atencion] + $comunes,
                    [
                        'fecha_hora' => $fechaHora,
                        'estado_animo' => $datos['conducta'] ?: null,
                        'apatia' => $datos['conducta'] === 'APATICO',
                        'agitacion' => $datos['conducta'] === 'AGITADO',
                        'ansiedad' => $datos['conducta'] === 'ANSIOSO',
                        'participacion' => $datos['participacion'] ?: null,
                        'descripcion' => $datos['observacion'],
                        'estado' => 'VIGENTE',
                    ]
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

        return [
            'estado_general' => Str::after((string) $atencion->motivo, 'SEGUIMIENTO_DIARIO:'),
            'tipo_comida' => $ingesta?->tipo_comida,
            'alimentacion' => $ingesta?->apetito,
            'porcentaje_alimentacion' => $ingesta?->porcentaje_consumido,
            'tipo_liquido' => $hidratacion?->tipo_liquido,
            'cantidad_hidratacion_ml' => $hidratacion?->cantidad_ml,
            'hidratacion' => $hidratacion?->tolerancia,
            'movilidad' => $movilidad?->marcha,
            'intento_caminar_solo' => $movilidad?->riesgo_caida === 'INTENTO_CAMINAR_SOLO',
            'sueno' => $sueno?->calidad,
            'orientacion' => $cognitivo?->orientacion_persona,
            'repite_preguntas' => (bool) $cognitivo?->repite_preguntas,
            'confusion_observable' => (bool) $cognitivo?->confusion,
            'conducta' => $conductual?->estado_animo,
            'participacion' => $conductual?->participacion,
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
