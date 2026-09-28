<?php

namespace App\Http\Controllers\Medicacion;

use App\Backend\Modulos\Clinica\Servicios\AutorizacionClinicaService;
use App\Backend\Modulos\Medicacion\Servicios\RegistrarAdministracionMedicacionService;
use App\Http\Controllers\Controller;
use App\Models\Atencion;
use App\Models\HorarioPrescripcion;
use App\Models\Prescripcion;
use App\Models\Residente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MedicacionController extends Controller
{
    public function __construct(
        private readonly AutorizacionClinicaService $autorizacion,
        private readonly RegistrarAdministracionMedicacionService $administraciones,
    ) {}

    public function index(Request $request, Residente $residente): JsonResponse
    {
        $this->authorize('view', $residente);
        return response()->json(Prescripcion::query()->where('cod_residente', $residente->cod_residente)->with(['medicamento', 'horarios', 'administraciones'])->latest('fecha_hora_prescripcion')->get());
    }

    public function prescribir(Request $request, Residente $residente): JsonResponse
    {
        $this->authorize('create', Prescripcion::class);
        $personal = $this->autorizacion->autorizarMutacion(
            $request->user(), $residente, 'prescripciones.crear', ['MEDICO GENERAL/GERIATRA'],
        );
        $datos = $request->validate([
            'cod_atencion' => ['required', 'exists:atenciones,cod_atencion'], 'cod_medicamento' => ['required', 'exists:medicamentos,cod_medicamento'],
            'dosis' => ['nullable', 'numeric', 'min:0'], 'unidad_dosis' => ['nullable', 'string', 'max:30'],
            'via_administracion' => ['required', 'string', 'max:60'], 'frecuencia' => ['nullable', 'string', 'max:80'],
            'indicacion' => ['nullable', 'string'], 'segun_necesidad' => ['required', 'boolean'], 'observacion' => ['nullable', 'string'],
            'horarios' => ['nullable', 'array'], 'horarios.*.hora_programada' => ['required_with:horarios', 'date_format:H:i'],
            'horarios.*.dosis_programada' => ['nullable', 'numeric', 'min:0'], 'horarios.*.dias_semana' => ['nullable', 'string', 'max:50'],
        ]);
        abort_unless(Atencion::query()->whereKey($datos['cod_atencion'])->where('cod_residente', $residente->cod_residente)->exists(), 422, 'La atención no pertenece al residente.');
        $horarios = $datos['horarios'] ?? [];
        unset($datos['horarios']);
        abort_if(! $datos['segun_necesidad'] && $horarios === [], 422,
            'Una prescripción programada requiere al menos un horario.');
        $prescripcion = DB::transaction(function () use ($residente, $personal, $datos, $horarios): Prescripcion {
            $prescripcion = Prescripcion::query()->create(['cod_prescripcion' => $this->codigo('PRE'), 'cod_residente' => $residente->cod_residente, 'cod_personal' => $personal->cod_personal, ...$datos, 'fecha_hora_prescripcion' => now(), 'estado' => 'ACTIVA']);
            foreach ($horarios as $horario) {
                HorarioPrescripcion::query()->create(['cod_horario_prescripcion' => $this->codigo('HPR'), 'cod_prescripcion' => $prescripcion->cod_prescripcion, ...$horario, 'estado' => 'ACTIVO']);
            }

            return $prescripcion;
        });
        return response()->json($prescripcion->load('horarios'), 201);
    }

    public function suspender(Request $request, Prescripcion $prescripcion): JsonResponse
    {
        $residente = $prescripcion->residente()->firstOrFail();
        $this->authorize('update', $prescripcion);
        $personal = $this->autorizacion->autorizarMutacion(
            $request->user(), $residente, 'prescripciones.suspender', ['MEDICO GENERAL/GERIATRA'],
        );
        $datos = $request->validate(['motivo_suspension' => ['required', 'string', 'min:5', 'max:10000']]);
        $prescripcion = DB::transaction(function () use ($prescripcion, $personal, $datos): Prescripcion {
            $bloqueada = Prescripcion::query()->lockForUpdate()->findOrFail($prescripcion->getKey());
            abort_unless(in_array($bloqueada->estado, ['ACTIVA', 'ACTIVO'], true), 409,
                'La prescripción ya no se encuentra activa.');
            $bloqueada->update([...$datos, 'cod_personal_suspension' => $personal->cod_personal, 'fecha_hora_suspension' => now(), 'estado' => 'SUSPENDIDA']);

            return $bloqueada;
        });
        return response()->json($prescripcion->fresh());
    }

    public function administrar(Request $request, Prescripcion $prescripcion): JsonResponse
    {
        $residente = $prescripcion->residente()->firstOrFail();
        $this->autorizacion->autorizarMutacion(
            $request->user(), $residente, 'administraciones_medicacion.crear', ['ENFERMEROS'],
        );
        $datos = $request->validate([
            'cod_horario_prescripcion' => ['nullable', 'exists:horarios_prescripcion,cod_horario_prescripcion'],
            'resultado' => ['required', 'in:ADMINISTRADA,OMITIDA'],
            'dosis_administrada' => ['nullable', 'numeric', 'min:0'], 'motivo_omision' => ['nullable', 'required_if:resultado,OMITIDA', 'string', 'min:5'],
            'efecto_observado' => ['nullable', 'string'], 'reaccion_adversa' => ['nullable', 'string'], 'observacion' => ['nullable', 'string'],
            'motivo_clinico' => ['nullable', 'required_without:cod_horario_prescripcion', 'string', 'min:5'],
            'valoracion_previa' => ['nullable', 'required_without:cod_horario_prescripcion', 'string', 'min:5'],
            'intensidad_previa' => ['nullable', 'required_without:cod_horario_prescripcion', 'integer', 'between:0,10'],
        ]);
        if (empty($datos['cod_horario_prescripcion'])) {
            abort_unless($prescripcion->segun_necesidad && $datos['resultado'] === 'ADMINISTRADA', 422,
                'Una administración sin horario solo es válida para una prescripción PRN activa.');
            $registro = $this->administraciones->registrarPrn(
                $request->user(),
                $residente->cod_residente,
                $prescripcion->cod_prescripcion,
                $datos['motivo_clinico'],
                $datos['valoracion_previa'],
                $datos['intensidad_previa'],
                $datos['efecto_observado'] ?? null,
            );
        } else {
            $horario = HorarioPrescripcion::query()
                ->whereKey($datos['cod_horario_prescripcion'])
                ->where('cod_prescripcion', $prescripcion->cod_prescripcion)
                ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
                ->first();
            abort_unless($horario, 422, 'El horario no corresponde a la prescripción activa.');
            $registro = $this->administraciones->registrarProgramada(
                $request->user(),
                $residente->cod_residente,
                $prescripcion->cod_prescripcion,
                $horario->hora_programada,
                $datos['resultado'] === 'ADMINISTRADA',
                $datos['motivo_omision'] ?? null,
                $datos['observacion'] ?? null,
                $datos['efecto_observado'] ?? null,
                $datos['dosis_administrada'] ?? null,
                $datos['reaccion_adversa'] ?? null,
            );
        }

        return response()->json($registro, 201);
    }

    private function codigo(string $prefijo): string { return $prefijo.'_'.Str::upper(Str::random(12)); }
}
