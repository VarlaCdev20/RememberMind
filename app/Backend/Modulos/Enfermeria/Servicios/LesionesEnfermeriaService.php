<?php

namespace App\Backend\Modulos\Enfermeria\Servicios;

use App\Backend\Modulos\Alertas\Servicios\AlertasService;
use App\Backend\Modulos\Clinica\Servicios\ContextoClinicoService;
use App\Models\CuracionHerida;
use App\Models\Herida;
use App\Models\Incidente;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LesionesEnfermeriaService
{
    public function __construct(
        private readonly TurnoEnfermeriaService $turnos,
        private readonly AlertasService $alertas,
        private readonly ContextoClinicoService $contextoClinico,
        private readonly MiTurnoService $miTurno,
    ) {}

    public function registrar(string $codResidente, array $datos, User $usuario): Herida
    {
        $this->turnos->autorizarMutacionEnfermeria($codResidente, 'heridas.crear', $usuario);
        $datos = $this->validar($datos);

        $personal = $this->contextoClinico->personalActivo($usuario);
        $codPersonal = $personal->cod_personal;

        return DB::transaction(function () use ($codResidente, $datos, $usuario, $codPersonal) {
            $herida = Herida::create([
                'cod_herida' => 'HER_' . strtoupper(Str::random(10)),
                'cod_residente' => $codResidente,
                'cod_personal' => $codPersonal,
                'tipo_herida' => $datos['tipo'],
                'ubicacion' => $datos['zona_corporal'],
                'causa' => $datos['causa_probable'] ?? null,
                'clasificacion' => $datos['estadio_upp'] ?? null,
                'fecha_hora_identificacion' => now(),
                'estado' => 'ACTIVA',
                'observacion' => $datos['aspecto_inicial'] ?? null,
            ]);

            $this->alertas->crear($codResidente, [
                'origen' => 'INCIDENTE',
                'tipo_alerta' => 'LESION DETECTADA',
                'nivel' => 'MEDIO',
                'motivo' => '[heridas:' . $herida->getKey() . '] ' . $datos['tipo'] . ' en ' . $datos['zona_corporal'],
            ], $usuario);

            return $herida;
        });
    }

    public function crearDesdeIncidente(Incidente $incidente, array $datos, User $usuario): Herida
    {
        return $this->registrar($incidente->cod_residente, $datos + [
            'causa_probable' => 'Incidente: ' . $incidente->tipo_incidente,
        ], $usuario);
    }

    public function registrarSeguimiento(Herida $herida, array $datos, User $usuario): CuracionHerida
    {
        $this->turnos->autorizarMutacionEnfermeria($herida->cod_residente, 'heridas.crear', $usuario);
        $datos = Validator::make($datos, [
            'largo_cm' => 'nullable|numeric|min:0.1|max:100',
            'ancho_cm' => 'nullable|numeric|min:0.1|max:100',
            'aspecto' => 'nullable|string|min:3|max:1000',
            'accion_realizada' => 'required|string|min:3|max:2000',
        ])->validate();

        $personal = $this->contextoClinico->personalActivo($usuario);
        $jornada = $this->miTurno->resolverJornadaActual($personal, now());
        if (! $jornada) {
            throw ValidationException::withMessages([
                'jornada' => 'No existe una jornada activa asignada al personal clínico.',
            ]);
        }

        return DB::transaction(function () use ($herida, $datos, $personal, $jornada): CuracionHerida {
            $bloqueada = Herida::query()->lockForUpdate()->findOrFail($herida->getKey());
            abort_unless($bloqueada->estado === 'ACTIVA', 409, 'La lesión ya no admite seguimientos.');

            return CuracionHerida::create([
                'cod_curacion' => 'CUR_' . strtoupper(Str::random(10)),
                'cod_herida' => $bloqueada->getKey(),
                'cod_personal' => $personal->cod_personal,
                'cod_jornada' => $jornada->cod_jornada,
                'fecha_hora' => now(),
                'longitud' => $datos['largo_cm'] ?? null,
                'ancho' => $datos['ancho_cm'] ?? null,
                'procedimiento' => $datos['accion_realizada'],
                'observacion' => $datos['aspecto'] ?? null,
            ]);
        });
    }

    public function cerrar(Herida $herida, string $resultado, string $motivo, User $usuario): Herida
    {
        $this->turnos->autorizarMutacionEnfermeria($herida->cod_residente, 'heridas.crear', $usuario);
        Validator::make(compact('resultado', 'motivo'), [
            'resultado' => 'required|string|max:50',
            'motivo' => 'required|string|min:5|max:2000',
        ])->validate();

        return DB::transaction(function () use ($herida, $resultado, $motivo): Herida {
            $bloqueada = Herida::query()->lockForUpdate()->findOrFail($herida->getKey());
            abort_unless($bloqueada->estado === 'ACTIVA', 409, 'La lesión ya está cerrada.');
            $bloqueada->update([
                'estado' => 'CERRADA',
                'fecha_hora_cierre' => now(),
                'observacion' => trim(($bloqueada->observacion ? $bloqueada->observacion . ' | ' : '') . "Cierre: {$resultado} - {$motivo}"),
            ]);

            return $bloqueada->refresh();
        });
    }

    private function validar(array $datos): array
    {
        return Validator::make($datos, [
            'tipo' => 'required|string|max:60',
            'zona_corporal' => 'required|string|max:120',
            'lateralidad' => 'nullable|string|max:20',
            'causa_probable' => 'nullable|string|max:120',
            'estadio_upp' => 'nullable|string|max:60',
            'aspecto_inicial' => 'nullable|string|max:1000',
        ])->validate();
    }
}
