<?php

namespace App\Backend\Modulos\Enfermeria\Servicios;

use App\Models\Alerta;
use App\Models\Atencion;
use App\Models\DispositivoClinico;
use App\Models\Personal;
use App\Models\RegistroEliminacion;
use App\Models\RegistroHidratacion;
use App\Models\RegistroIngesta;
use App\Models\RegistroMovilidad;
use App\Models\User;
use App\Models\ValoracionDolor;
use App\Backend\Modulos\Alertas\Servicios\AlertasService;
use App\Backend\Modulos\Enfermeria\Servicios\MiTurnoService;
use App\Backend\Modulos\Enfermeria\Servicios\TurnoEnfermeriaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CuidadosEnfermeriaService
{
    public function __construct(private readonly TurnoEnfermeriaService $turnos, private readonly AlertasService $alertas) {}

    public function registrar(string $codResidente, array $datos, User $usuario): object
    {
        $this->turnos->autorizarMutacionEnfermeria($codResidente, 'atenciones.crear', $usuario);
        $datos = $this->validar($datos);

        $personal = $usuario->personal ?: Personal::where('cod_usuario', $usuario->cod_usuario)->first();
        $codPersonal = $personal?->cod_personal ?: 'PER_' . strtoupper(Str::random(10));
        $codArea = $personal?->cod_area ?: 'ARE_0004';

        $miTurnoService = app(MiTurnoService::class);
        $jornada = $personal ? $miTurnoService->resolverJornadaActual($personal, now()) : null;
        $codJornada = $jornada?->cod_jornada;

        $tipo = strtoupper(trim($datos['tipo']));

        return DB::transaction(function () use ($codResidente, $datos, $usuario, $personal, $codPersonal, $codArea, $codJornada, $tipo) {
            $registro = null;

            if ($tipo === 'ALIMENTACION') {
                $registro = RegistroIngesta::create([
                    'cod_ingesta' => 'ING_' . strtoupper(Str::random(10)),
                    'cod_residente' => $codResidente,
                    'cod_personal' => $codPersonal,
                    'cod_jornada' => $codJornada,
                    'tipo_comida' => $datos['subtipo'] ?? 'PRINCIPAL',
                    'porcentaje_consumido' => isset($datos['porcentaje']) ? (float) $datos['porcentaje'] : null,
                    'apetito' => $datos['estado_general'] ?? null,
                    'tolerancia' => $datos['tolerancia'] ?? null,
                    'dificultad_deglucion' => !empty($datos['presenta_dificultad']),
                    'fecha_hora' => now(),
                    'estado' => 'VIGENTE',
                    'observacion' => $datos['observacion'] ?? $datos['motivo'] ?? 'Registro de alimentación.',
                ]);
            } elseif ($tipo === 'HIDRATACION') {
                $registro = RegistroHidratacion::create([
                    'cod_hidratacion' => 'HID_' . strtoupper(Str::random(10)),
                    'cod_residente' => $codResidente,
                    'cod_personal' => $codPersonal,
                    'cod_jornada' => $codJornada,
                    'tipo_liquido' => $datos['subtipo'] ?? 'AGUA',
                    'cantidad_ml' => (int) ($datos['cantidad_ml'] ?? 200),
                    'via' => 'ORAL',
                    'tolerancia' => $datos['tolerancia'] ?? null,
                    'fecha_hora' => now(),
                    'estado' => 'VIGENTE',
                    'observacion' => $datos['observacion'] ?? $datos['motivo'] ?? 'Registro de hidratación.',
                ]);
            } elseif ($tipo === 'ELIMINACION') {
                $registro = RegistroEliminacion::create([
                    'cod_eliminacion' => 'ELM_' . strtoupper(Str::random(10)),
                    'cod_residente' => $codResidente,
                    'cod_personal' => $codPersonal,
                    'cod_jornada' => $codJornada,
                    'tipo_eliminacion' => $datos['subtipo'] ?? 'ORINA',
                    'consistencia' => $datos['consistencia'] ?? null,
                    'es_continente' => !empty($datos['es_continente']),
                    'usa_dispositivo' => !empty($datos['usa_dispositivo']),
                    'dificultad' => !empty($datos['presenta_dificultad']),
                    'dolor' => !empty($datos['presenta_dolor']),
                    'fecha_hora' => now(),
                    'estado' => 'VIGENTE',
                    'observacion' => $datos['observacion'] ?? $datos['motivo'] ?? 'Registro de eliminación.',
                ]);
            } elseif ($tipo === 'MOVILIDAD') {
                $registro = RegistroMovilidad::create([
                    'cod_movilidad' => 'MOV_' . strtoupper(Str::random(10)),
                    'cod_residente' => $codResidente,
                    'cod_personal' => $codPersonal,
                    'cod_jornada' => $codJornada,
                    'tipo_movilidad' => $datos['subtipo'] ?? 'CAMBIO_POSTURAL',
                    'nivel_ayuda' => $datos['nivel_ayuda'] ?? null,
                    'ayuda_tecnica' => $datos['ayuda_tecnica'] ?? null,
                    'tolerancia' => $datos['tolerancia'] ?? null,
                    'fecha_hora' => now(),
                    'estado' => 'VIGENTE',
                    'observacion' => $datos['observacion'] ?? $datos['motivo'] ?? 'Registro de movilidad.',
                ]);
            } else {
                $registro = Atencion::create([
                    'cod_atencion' => 'ATN_' . strtoupper(Str::random(10)),
                    'cod_residente' => $codResidente,
                    'cod_area' => $codArea,
                    'cod_personal' => $codPersonal,
                    'tipo_atencion' => 'CUIDADO',
                    'motivo' => $tipo . ' - ' . ($datos['subtipo'] ?? 'GENERAL'),
                    'fecha_hora' => now(),
                    'estado' => 'REALIZADA',
                    'observacion' => $datos['observacion'] ?? $datos['motivo'] ?? 'Cuidado asistencial de enfermería.',
                ]);
            }

            if (($datos['cambio_respecto_basal'] ?? null) === 'PEOR' || (($datos['tipo'] ?? null) === 'ALIMENTACION' && ($datos['porcentaje'] ?? 100) < config('enfermeria.porcentaje_baja_ingesta', 50))) {
                $this->alertas->crear($codResidente, [
                    'origen' => 'SEGUIMIENTO',
                    'tipo_alerta' => ($datos['cambio_respecto_basal'] ?? null) === 'PEOR' ? 'CAMBIO RESPECTO AL ESTADO BASAL' : 'BAJA INGESTA',
                    'nivel' => 'MEDIO',
                    'motivo' => 'Alerta clínica: ' . ($datos['motivo'] ?? $datos['observacion'] ?? 'Requiere seguimiento de Enfermería.'),
                ], $usuario);
            }

            return $registro;
        });
    }

    public function registrarDolor(string $codResidente, string $fase, int $intensidad, string $detalle, User $usuario, ?ValoracionDolor $valoracion = null, ?string $resultado = null): ValoracionDolor
    {
        $fase = mb_strtoupper($fase);
        Validator::make(compact('fase', 'intensidad', 'detalle', 'resultado'), [
            'fase' => 'required|in:VALORACION,INTERVENCION,REEVALUACION',
            'intensidad' => 'required|integer|min:0|max:10',
            'detalle' => 'required|string|min:5|max:2000',
            'resultado' => 'required_if:fase,REEVALUACION|nullable|string|min:3|max:200',
        ])->validate();

        $personal = $usuario->personal ?: Personal::where('cod_usuario', $usuario->cod_usuario)->first();
        $codPersonal = $personal?->cod_personal ?: 'PER_' . strtoupper(Str::random(10));

        return ValoracionDolor::create([
            'cod_valoracion_dolor' => 'VD_' . strtoupper(Str::random(10)),
            'cod_residente' => $codResidente,
            'cod_personal' => $codPersonal,
            'fecha_hora' => now(),
            'intensidad' => $intensidad,
            'ubicacion' => 'General',
            'tipo_dolor' => 'SOMATICO',
            'desencadenante' => $fase,
            'intervencion' => $detalle,
            'respuesta' => $resultado,
            'estado' => 'VIGENTE',
        ]);
    }

    public function colocarDispositivo(string $codResidente, array $datos, User $usuario): DispositivoClinico
    {
        $this->turnos->autorizarMutacionEnfermeria($codResidente, 'atenciones.crear', $usuario);
        $datos = Validator::make($datos, [
            'tipo' => 'required|in:OXIGENO,SONDA_URINARIA,OSTOMIA,ALIMENTACION_ENTERAL,OTRO',
            'ubicacion' => 'nullable|string|max:120',
            'indicacion' => 'required|string|min:5|max:1000',
        ])->validate();

        $personal = $usuario->personal ?: Personal::where('cod_usuario', $usuario->cod_usuario)->first();
        $codPersonal = $personal?->cod_personal ?: 'PER_' . strtoupper(Str::random(10));

        return DispositivoClinico::create([
            'cod_dispositivo' => 'DIS_' . strtoupper(Str::random(10)),
            'cod_residente' => $codResidente,
            'cod_personal' => $codPersonal,
            'tipo' => $datos['tipo'],
            'ubicacion' => $datos['ubicacion'] ?? null,
            'fecha_colocacion' => now(),
            'estado' => 'ACTIVO',
            'observacion' => $datos['indicacion'] ?? null,
        ]);
    }

    public function retirarDispositivo(DispositivoClinico $dispositivo, string $motivo, User $usuario): void
    {
        $this->turnos->autorizarMutacionEnfermeria($dispositivo->cod_residente, 'atenciones.crear', $usuario);
        Validator::make(['motivo' => $motivo], ['motivo' => 'required|string|min:5|max:1000'])->validate();
        abort_unless($dispositivo->estado === 'ACTIVO', 409, 'El dispositivo ya fue retirado.');
        $dispositivo->update([
            'estado' => 'RETIRADO',
            'fecha_retiro' => now(),
            'observacion' => trim(($dispositivo->observacion ? $dispositivo->observacion . ' | ' : '') . 'Retiro: ' . $motivo),
        ]);
    }

    private function validar(array $datos): array
    {
        $datos = array_map(fn ($v) => $v === '' ? null : $v, $datos);
        $validados = Validator::make($datos, [
            'tipo' => 'required|in:ALIMENTACION,HIDRATACION,ELIMINACION,HIGIENE,MOVILIDAD,SUENO,VALORACION_RAPIDA,PROCEDIMIENTO,DOLOR,DISPOSITIVO,OBSERVACION',
            'subtipo' => 'required|string|max:50',
            'porcentaje' => 'nullable|integer|in:0,25,50,75,100',
            'cantidad_ml' => 'nullable|integer|min:1|max:10000',
            'dolor' => 'nullable|integer|min:0|max:10',
            'nivel_ayuda' => 'nullable|string|max:30',
            'tolerancia' => 'nullable|string|max:30',
            'resultado' => 'nullable|string|max:200',
            'motivo' => 'nullable|string|max:2000',
            'observacion' => 'nullable|string|max:5000',
            'hora_inicio' => 'nullable|date_format:H:i',
            'hora_fin' => 'nullable|date_format:H:i|after:hora_inicio',
            'cantidad_despertares' => 'nullable|integer|min:0|max:30',
            'fase_dolor' => 'nullable|in:VALORACION,INTERVENCION,REEVALUACION',
            'cambio_respecto_basal' => 'nullable|in:MEJOR,PEOR,SIN_CAMBIOS,NO_EVALUABLE',
            'estado_general' => 'nullable|string|max:30',
            'conciencia' => 'nullable|string|max:30',
            'cognicion' => 'nullable|string|max:30',
            'conducta' => 'nullable|string|max:30',
            'respiracion' => 'nullable|string|max:30',
            'consistencia' => 'nullable|string|max:50',
            'es_continente' => 'nullable|boolean',
            'presenta_dificultad' => 'nullable|boolean',
            'presenta_dolor' => 'nullable|boolean',
            'usa_dispositivo' => 'nullable|boolean',
            'ayuda_tecnica' => 'nullable|string|max:80',
            'calidad' => 'nullable|string|max:30',
            'deambulacion_nocturna' => 'nullable|boolean',
            'agitacion' => 'nullable|boolean',
        ])->validate();

        if ($validados['tipo'] === 'ALIMENTACION' && ($validados['porcentaje'] ?? 100) < config('enfermeria.porcentaje_baja_ingesta', 50)
            && mb_strlen(trim($validados['motivo'] ?? '')) < 3) {
            throw \Illuminate\Validation\ValidationException::withMessages(['motivo' => 'Indique el motivo de la baja ingesta.']);
        }
        if (in_array($validados['tipo'], ['HIGIENE','PROCEDIMIENTO']) && in_array($validados['resultado'] ?? null, ['PARCIAL','NO_REALIZADO','CANCELADO'])
            && mb_strlen(trim($validados['motivo'] ?? '')) < 3) {
            throw \Illuminate\Validation\ValidationException::withMessages(['motivo' => 'El motivo es obligatorio cuando el cuidado no fue completado.']);
        }
        return $validados;
    }
}
