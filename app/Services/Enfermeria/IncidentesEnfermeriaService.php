<?php

namespace App\Services\Enfermeria;

use App\Models\Incidente;
use App\Models\Personal;
use App\Models\User;
use App\Services\Alertas\AlertasService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class IncidentesEnfermeriaService
{
    public function __construct(
        private readonly TurnoEnfermeriaService $turnos,
        private readonly AlertasService $alertas,
        private readonly LesionesEnfermeriaService $lesiones
    ) {}

    public function registrar(string $codResidente, array $datos, User $usuario): Incidente
    {
        $this->turnos->autorizarMutacionEnfermeria($codResidente, 'seguimiento.crear', $usuario);
        $datos = Validator::make($datos, [
            'tipo' => 'required|in:CAIDA,GOLPE,ERROR_MEDICACION,LESION,CAMBIO_CLINICO,OTRO',
            'lugar' => 'required|string|min:2|max:120',
            'actividad_previa' => 'nullable|string|max:200',
            'fue_presenciado' => 'required|boolean',
            'testigo' => 'required_if:fue_presenciado,true|nullable|string|max:200',
            'descripcion' => 'required|string|min:10|max:3000',
            'dolor' => 'nullable|integer|min:0|max:10',
            'lesion' => 'required|boolean',
            'movilidad_posterior' => 'nullable|string|max:50',
            'cambio_cognitivo' => 'required|boolean',
            'medico_informado' => 'required|boolean',
            'familiar_informado' => 'required|boolean',
            'requiere_seguimiento' => 'required|boolean',
            'tipo_lesion' => 'required_if:lesion,true|nullable|string|max:50',
            'zona_lesion' => 'required_if:lesion,true|nullable|string|max:120',
            'lateralidad' => 'nullable|string|max:20',
        ])->validate();

        $personal = $usuario->personal ?: Personal::where('cod_usuario', $usuario->cod_usuario)->first();
        $codPersonal = $personal?->cod_personal ?: 'PER_' . strtoupper(Str::random(10));

        $miTurnoService = app(MiTurnoService::class);
        $jornada = $personal ? $miTurnoService->resolverJornadaActual($personal, now()) : null;

        return DB::transaction(function () use ($codResidente, $datos, $usuario, $codPersonal, $jornada) {
            $tipoNorm = strtoupper(trim($datos['tipo']));
            $gravedad = $tipoNorm === 'CAIDA' ? 'ALTA' : ($datos['lesion'] ? 'MODERADA' : 'LEVE');

            $incidente = Incidente::create([
                'cod_incidente' => 'INC_' . strtoupper(Str::random(10)),
                'cod_residente' => $codResidente,
                'cod_personal' => $codPersonal,
                'cod_jornada' => $jornada?->cod_jornada,
                'tipo_incidente' => $tipoNorm,
                'gravedad' => $gravedad,
                'lugar' => $datos['lugar'] ?? null,
                'fecha_hora' => now(),
                'descripcion' => $datos['descripcion'],
                'medida_inmediata' => $datos['movilidad_posterior'] ?? 'Atención inmediata brindada por enfermería.',
                'requiere_medico' => (bool) ($datos['medico_informado'] ?? false),
                'requiere_derivacion' => false,
                'estado' => 'ABIERTO',
                'observacion' => !empty($datos['testigo']) ? 'Presenciado por: ' . $datos['testigo'] : null,
            ]);

            if ($datos['lesion']) {
                $this->lesiones->crearDesdeIncidente($incidente, [
                    'tipo' => $datos['tipo_lesion'] ?? 'HERIDA',
                    'zona_corporal' => $datos['zona_lesion'] ?? 'General',
                    'lateralidad' => $datos['lateralidad'] ?? null,
                ], $usuario);
            }

            $this->alertas->crear($codResidente, [
                'origen' => 'INCIDENTE',
                'tipo_alerta' => $tipoNorm,
                'nivel' => $tipoNorm === 'CAIDA' ? 'ALTO' : 'MEDIO',
                'motivo' => '[incidentes:' . $incidente->getKey() . '] ' . $datos['descripcion'],
            ], $usuario);

            return $incidente;
        });
    }

    public function registrarSeguimiento(Incidente $incidente, string $accion, User $usuario): Incidente
    {
        $this->turnos->autorizarMutacionEnfermeria($incidente->cod_residente, 'seguimiento.crear', $usuario);
        Validator::make(['accion' => $accion], ['accion' => 'required|string|min:10|max:5000'])->validate();

        return DB::transaction(function () use ($incidente, $accion) {
            $bloqueado = Incidente::lockForUpdate()->findOrFail($incidente->getKey());
            abort_unless(in_array($bloqueado->estado, ['ABIERTO', 'EN_SEGUIMIENTO', 'REPORTADO']), 409, 'El incidente está cerrado.');
            $historial = trim(collect([$bloqueado->observacion, '[' . now()->format('Y-m-d H:i') . '] ' . trim($accion)])->filter()->implode("\n"));
            $bloqueado->update([
                'estado' => 'EN_SEGUIMIENTO',
                'observacion' => $historial,
            ]);
            return $bloqueado->refresh();
        });
    }

    public function cerrar(Incidente $incidente, string $evaluacion, string $resultado, User $usuario): Incidente
    {
        $this->turnos->autorizarMutacionEnfermeria($incidente->cod_residente, 'seguimiento.crear', $usuario);
        Validator::make(compact('evaluacion', 'resultado'), [
            'evaluacion' => 'required|string|min:10|max:5000',
            'resultado' => 'required|string|min:5|max:5000',
        ])->validate();

        return DB::transaction(function () use ($incidente, $evaluacion, $resultado) {
            $bloqueado = Incidente::lockForUpdate()->findOrFail($incidente->getKey());
            abort_unless(in_array($bloqueado->estado, ['ABIERTO', 'EN_SEGUIMIENTO', 'REPORTADO']), 409, 'El incidente ya está cerrado.');
            $cierreTexto = "Cierre: {$evaluacion} | Resultado: {$resultado}";
            $bloqueado->update([
                'estado' => 'CERRADO',
                'observacion' => trim(($bloqueado->observacion ? $bloqueado->observacion . "\n" : '') . $cierreTexto),
            ]);
            return $bloqueado->refresh();
        });
    }
}
