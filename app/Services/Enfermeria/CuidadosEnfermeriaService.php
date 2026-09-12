<?php

namespace App\Services\Enfermeria;

use App\Models\DispositivoResidente;
use App\Models\RegistroCuidado;
use App\Models\User;
use App\Services\Alertas\AlertasService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CuidadosEnfermeriaService
{
    public function __construct(private readonly TurnoEnfermeriaService $turnos, private readonly AlertasService $alertas) {}

    public function registrar(string $codAm, array $datos, User $usuario): RegistroCuidado
    {
        $this->turnos->autorizarMutacionEnfermeria($codAm, 'seguimiento.crear', $usuario);
        $datos = $this->validar($datos);
        $registro = RegistroCuidado::create($datos + [
            'cod_am' => $codAm,
            'cod_turno' => $this->turnos->obtenerTurnoActivo($usuario)?->cod_turno,
            'registrado_por' => $usuario->cod_usu,
            'fecha_hora_evento' => now(),
            'estado' => 'FIRMADO',
        ]);
        if (($datos['cambio_respecto_basal'] ?? null) === 'PEOR' || (($datos['tipo'] ?? null) === 'ALIMENTACION' && ($datos['porcentaje'] ?? 100) < config('enfermeria.porcentaje_baja_ingesta', 50))) {
            $this->alertas->crear($codAm, [
                'origen' => 'SEGUIMIENTO',
                'tipo_alerta' => ($datos['cambio_respecto_basal'] ?? null) === 'PEOR' ? 'CAMBIO RESPECTO AL ESTADO BASAL' : 'BAJA INGESTA',
                'nivel' => 'MEDIO',
                'motivo' => '[registros_cuidados:'.$registro->getKey().'] '.($datos['motivo'] ?? $datos['observacion'] ?? 'Requiere seguimiento de Enfermería.'),
            ], $usuario);
        }
        return $registro;
    }

    public function rectificar(RegistroCuidado $original, array $datos, string $motivo, User $usuario): RegistroCuidado
    {
        Validator::make(['motivo' => $motivo], ['motivo' => 'required|string|min:10|max:2000'])->validate();
        $this->turnos->autorizarMutacionEnfermeria($original->cod_am, 'seguimiento.crear', $usuario);
        $datos = $this->validar($datos);
        return DB::transaction(function () use ($original, $datos, $motivo, $usuario) {
            $bloqueado = RegistroCuidado::lockForUpdate()->findOrFail($original->getKey());
            abort_unless($bloqueado->estado === 'FIRMADO' && $bloqueado->rectifica_a === null, 409, 'El registro ya no admite rectificación.');
            $bloqueado->update(['estado' => 'RECTIFICADO']);
            return RegistroCuidado::create($datos + [
                'cod_am' => $bloqueado->cod_am,
                'cod_turno' => $this->turnos->obtenerTurnoActivo($usuario)?->cod_turno,
                'registrado_por' => $usuario->cod_usu,
                'fecha_hora_evento' => now(),
                'estado' => 'FIRMADO',
                'rectifica_a' => $bloqueado->getKey(),
                'motivo' => trim($motivo),
            ]);
        });
    }

    public function registrarDolor(string $codAm, string $fase, int $intensidad, string $detalle, User $usuario, ?RegistroCuidado $valoracion = null, ?string $resultado = null): RegistroCuidado
    {
        $fase = mb_strtoupper($fase);
        Validator::make(compact('fase', 'intensidad', 'detalle', 'resultado'), [
            'fase' => 'required|in:VALORACION,INTERVENCION,REEVALUACION',
            'intensidad' => 'required|integer|min:0|max:10',
            'detalle' => 'required|string|min:5|max:2000',
            'resultado' => 'required_if:fase,REEVALUACION|nullable|string|min:3|max:200',
        ])->validate();
        if ($fase !== 'VALORACION') {
            abort_unless($valoracion && $valoracion->cod_am === $codAm && $valoracion->tipo === 'DOLOR' && $valoracion->fase_dolor === 'VALORACION', 422, 'La intervención y reevaluación deben vincularse a una valoración de dolor.');
        }
        return $this->registrar($codAm, [
            'tipo' => 'DOLOR', 'subtipo' => $fase, 'fase_dolor' => $fase,
            'dolor' => $intensidad, 'observacion' => $detalle,
            'resultado' => $resultado, 'relacionado_a' => $valoracion?->getKey(),
        ], $usuario);
    }

    public function colocarDispositivo(string $codAm, array $datos, User $usuario): DispositivoResidente
    {
        $this->turnos->autorizarMutacionEnfermeria($codAm, 'seguimiento.crear', $usuario);
        $datos = Validator::make($datos, [
            'tipo' => 'required|in:OXIGENO,SONDA_URINARIA,OSTOMIA,ALIMENTACION_ENTERAL,OTRO',
            'ubicacion' => 'nullable|string|max:120', 'indicacion' => 'required|string|min:5|max:1000',
        ])->validate();
        return DispositivoResidente::create($datos + ['cod_am' => $codAm, 'fecha_colocacion' => now(), 'estado' => 'ACTIVO', 'registrado_por' => $usuario->cod_usu]);
    }

    public function retirarDispositivo(DispositivoResidente $dispositivo, string $motivo, User $usuario): void
    {
        $this->turnos->autorizarMutacionEnfermeria($dispositivo->cod_am, 'seguimiento.crear', $usuario);
        Validator::make(['motivo' => $motivo], ['motivo' => 'required|string|min:5|max:1000'])->validate();
        abort_unless($dispositivo->estado === 'ACTIVO', 409, 'El dispositivo ya fue retirado.');
        $dispositivo->update(['estado' => 'RETIRADO', 'fecha_retiro' => now(), 'observacion' => trim($motivo)]);
    }

    private function validar(array $datos): array
    {
        $datos = array_map(fn ($v) => $v === '' ? null : $v, $datos);
        $validados = Validator::make($datos, [
            'tipo' => 'required|in:ALIMENTACION,HIDRATACION,ELIMINACION,HIGIENE,MOVILIDAD,SUENO,VALORACION_RAPIDA,PROCEDIMIENTO,DOLOR,DISPOSITIVO,OBSERVACION',
            'subtipo' => 'required|string|max:50', 'porcentaje' => 'nullable|integer|in:0,25,50,75,100',
            'cantidad_ml' => 'nullable|integer|min:1|max:10000', 'dolor' => 'nullable|integer|min:0|max:10',
            'nivel_ayuda' => 'nullable|string|max:30', 'tolerancia' => 'nullable|string|max:30',
            'resultado' => 'nullable|string|max:200', 'motivo' => 'nullable|string|max:2000', 'observacion' => 'nullable|string|max:5000',
            'hora_inicio' => 'nullable|date_format:H:i', 'hora_fin' => 'nullable|date_format:H:i|after:hora_inicio',
            'cantidad_despertares' => 'nullable|integer|min:0|max:30', 'fase_dolor' => 'nullable|in:VALORACION,INTERVENCION,REEVALUACION',
            'relacionado_a' => 'nullable|exists:registros_cuidados,cod_registro_cuidado',
            'cambio_respecto_basal' => 'nullable|in:MEJOR,PEOR,SIN_CAMBIOS,NO_EVALUABLE',
            'estado_general' => 'nullable|string|max:30', 'conciencia' => 'nullable|string|max:30',
            'cognicion' => 'nullable|string|max:30', 'conducta' => 'nullable|string|max:30', 'respiracion' => 'nullable|string|max:30',
            'consistencia' => 'nullable|string|max:50', 'es_continente' => 'nullable|boolean',
            'presenta_dificultad' => 'nullable|boolean', 'presenta_dolor' => 'nullable|boolean',
            'usa_dispositivo' => 'nullable|boolean', 'ayuda_tecnica' => 'nullable|string|max:80',
            'calidad' => 'nullable|string|max:30', 'deambulacion_nocturna' => 'nullable|boolean', 'agitacion' => 'nullable|boolean',
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
