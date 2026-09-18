<?php

namespace App\Http\Controllers\Medicacion;

use App\Http\Controllers\Controller;
use App\Models\AdministracionMedicacion;
use App\Models\Atencion;
use App\Models\HorarioPrescripcion;
use App\Models\Prescripcion;
use App\Models\Residente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MedicacionController extends Controller
{
    public function index(Request $request, Residente $residente): JsonResponse
    {
        $this->authorize('view', $residente);
        return response()->json(Prescripcion::query()->where('cod_residente', $residente->cod_residente)->with(['medicamento', 'horarios', 'administraciones'])->latest('fecha_hora_prescripcion')->get());
    }

    public function prescribir(Request $request, Residente $residente): JsonResponse
    {
        $this->authorize('view', $residente);
        $this->authorize('create', Prescripcion::class);
        $personal = $request->user()->personal()->where('estado', 'ACTIVO')->firstOrFail();
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
        $prescripcion = Prescripcion::query()->create(['cod_prescripcion' => $this->codigo('PRE'), 'cod_residente' => $residente->cod_residente, 'cod_personal' => $personal->cod_personal, ...$datos, 'fecha_hora_prescripcion' => now(), 'estado' => 'ACTIVA']);
        foreach ($horarios as $horario) {
            HorarioPrescripcion::query()->create(['cod_horario_prescripcion' => $this->codigo('HPR'), 'cod_prescripcion' => $prescripcion->cod_prescripcion, ...$horario, 'estado' => 'ACTIVO']);
        }
        return response()->json($prescripcion->load('horarios'), 201);
    }

    public function suspender(Request $request, Prescripcion $prescripcion): JsonResponse
    {
        $this->authorize('view', $prescripcion->residente()->firstOrFail());
        $this->authorize('update', $prescripcion);
        $personal = $request->user()->personal()->where('estado', 'ACTIVO')->firstOrFail();
        $datos = $request->validate(['motivo_suspension' => ['required', 'string']]);
        $prescripcion->update([...$datos, 'cod_personal_suspension' => $personal->cod_personal, 'fecha_hora_suspension' => now(), 'estado' => 'SUSPENDIDA']);
        return response()->json($prescripcion->fresh());
    }

    public function administrar(Request $request, Prescripcion $prescripcion): JsonResponse
    {
        $this->authorize('view', $prescripcion->residente()->firstOrFail());
        $this->authorize('create', AdministracionMedicacion::class);
        abort_if($prescripcion->estado !== 'ACTIVA', 409, 'La prescripción no está activa.');
        $personal = $request->user()->personal()->where('estado', 'ACTIVO')->firstOrFail();
        $datos = $request->validate([
            'cod_horario_prescripcion' => ['nullable', 'exists:horarios_prescripcion,cod_horario_prescripcion'],
            'cod_jornada' => ['required', 'exists:jornadas,cod_jornada'], 'fecha_hora_programada' => ['nullable', 'date'],
            'fecha_hora_administracion' => ['nullable', 'date'], 'resultado' => ['required', 'string', 'max:40'],
            'dosis_administrada' => ['nullable', 'numeric', 'min:0'], 'motivo_omision' => ['nullable', 'required_if:resultado,OMITIDA', 'string'],
            'efecto_observado' => ['nullable', 'string'], 'reaccion_adversa' => ['nullable', 'string'], 'observacion' => ['nullable', 'string'],
        ]);
        if (! empty($datos['cod_horario_prescripcion'])) {
            abort_unless(HorarioPrescripcion::query()->whereKey($datos['cod_horario_prescripcion'])->where('cod_prescripcion', $prescripcion->cod_prescripcion)->exists(), 422, 'El horario no corresponde a la prescripción.');
        }
        $registro = AdministracionMedicacion::query()->create(['cod_administracion' => $this->codigo('ADM'), 'cod_prescripcion' => $prescripcion->cod_prescripcion, 'cod_residente' => $prescripcion->cod_residente, 'cod_personal' => $personal->cod_personal, ...$datos, 'estado' => 'REGISTRADA']);
        return response()->json($registro, 201);
    }

    private function codigo(string $prefijo): string { return $prefijo.'_'.Str::upper(Str::random(12)); }
}
