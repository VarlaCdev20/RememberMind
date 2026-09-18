<?php

namespace App\Http\Controllers\Valoraciones;

use App\Http\Controllers\Controller;
use App\Models\Atencion;
use App\Models\MedicionAntropometrica;
use App\Models\ParticipanteActividad;
use App\Models\Residente;
use App\Models\SeguimientoPedagogico;
use App\Models\ValoracionFuncional;
use App\Models\ValoracionNutricional;
use App\Models\ValoracionPsicologica;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ValoracionProfesionalController extends Controller
{
    public function registrar(Request $request, Residente $residente, string $tipo): JsonResponse
    {
        $this->authorize('view', $residente);
        $definicion = $this->definiciones()[$tipo] ?? abort(404);
        abort_unless($request->user()->can($definicion['permiso']), 403);
        $personal = $request->user()->personal()->where('estado', 'ACTIVO')->firstOrFail();
        $datos = $request->validate($definicion['reglas']);
        if (isset($datos['cod_atencion'])) {
            abort_unless(Atencion::query()->whereKey($datos['cod_atencion'])->where('cod_residente', $residente->cod_residente)->exists(), 422, 'La atención no pertenece al residente.');
        }
        if (isset($datos['cod_medicion'])) {
            abort_unless(MedicionAntropometrica::query()->whereKey($datos['cod_medicion'])->where('cod_residente', $residente->cod_residente)->exists(), 422, 'La medición no pertenece al residente.');
        }
        if (isset($datos['cod_actividad'])) {
            abort_unless(ParticipanteActividad::query()->where('cod_actividad', $datos['cod_actividad'])->where('cod_residente', $residente->cod_residente)->exists(), 422, 'El residente no participa en la actividad.');
        }
        /** @var class-string<Model> $modelo */
        $modelo = $definicion['modelo'];
        $registro = $modelo::query()->create([$definicion['pk'] => $this->codigo($definicion['prefijo']), 'cod_residente' => $residente->cod_residente, 'cod_personal' => $personal->cod_personal, ...$datos, 'fecha_hora' => now(), 'estado' => 'VIGENTE']);
        return response()->json($registro, 201);
    }

    private function definiciones(): array
    {
        $base = ['cod_atencion' => ['required', 'exists:atenciones,cod_atencion']];
        $texto = ['nullable', 'string'];
        return [
            'psicologia' => ['modelo' => ValoracionPsicologica::class, 'pk' => 'cod_valoracion_psicologica', 'prefijo' => 'VPS', 'permiso' => 'valoraciones_psicologicas.crear', 'reglas' => $base + ['estado_animo' => ['nullable', 'string', 'max:50'], 'afecto' => ['nullable', 'string', 'max:50'], 'ansiedad' => ['nullable', 'string', 'max:50'], 'apatia' => ['nullable', 'string', 'max:50'], 'percepcion' => $texto, 'conducta' => $texto, 'comunicacion' => $texto, 'interaccion_social' => $texto, 'impresion_cognitiva' => $texto, 'conclusion' => $texto, 'recomendacion' => $texto]],
            'nutricion' => ['modelo' => ValoracionNutricional::class, 'pk' => 'cod_valoracion_nutricional', 'prefijo' => 'VNU', 'permiso' => 'valoraciones_nutricionales.crear', 'reglas' => $base + ['cod_medicion' => ['nullable', 'exists:mediciones_antropometricas,cod_medicion'], 'estado_nutricional' => ['nullable', 'string', 'max:60'], 'apetito' => ['nullable', 'string', 'max:40'], 'deglucion' => ['nullable', 'string', 'max:40'], 'riesgo_desnutricion' => ['nullable', 'string', 'max:40'], 'necesidad_asistencia' => ['nullable', 'string', 'max:40'], 'requerimiento_hidrico' => ['nullable', 'numeric', 'min:0'], 'restricciones_alimentarias' => $texto, 'conclusion' => $texto, 'recomendacion' => $texto]],
            'funcional' => ['modelo' => ValoracionFuncional::class, 'pk' => 'cod_valoracion_funcional', 'prefijo' => 'VFU', 'permiso' => 'valoraciones_funcionales.crear', 'reglas' => $base + ['marcha' => ['nullable', 'string', 'max:40'], 'equilibrio' => ['nullable', 'string', 'max:40'], 'traslado' => ['nullable', 'string', 'max:40'], 'fuerza_funcional' => ['nullable', 'string', 'max:40'], 'resistencia' => ['nullable', 'string', 'max:40'], 'alimentacion_autonoma' => ['nullable', 'string', 'max:30'], 'bano_autonomo' => ['nullable', 'string', 'max:30'], 'vestido_autonomo' => ['nullable', 'string', 'max:30'], 'higiene_autonoma' => ['nullable', 'string', 'max:30'], 'continencia' => ['nullable', 'string', 'max:30'], 'movilidad_autonoma' => ['nullable', 'string', 'max:30'], 'necesita_supervision' => ['nullable', 'boolean'], 'nivel_dependencia' => ['nullable', 'string', 'max:40'], 'conclusion' => $texto, 'recomendacion' => $texto]],
            'pedagogia' => ['modelo' => SeguimientoPedagogico::class, 'pk' => 'cod_seguimiento_pedagogico', 'prefijo' => 'SPE', 'permiso' => 'seguimientos_pedagogicos.crear', 'reglas' => ['cod_actividad' => ['nullable', 'exists:actividades,cod_actividad'], 'atencion' => ['nullable', 'string', 'max:40'], 'comprension_instrucciones' => ['nullable', 'string', 'max:40'], 'ejecucion_tarea' => ['nullable', 'string', 'max:40'], 'reconocimiento' => ['nullable', 'string', 'max:40'], 'orientacion' => ['nullable', 'string', 'max:40'], 'participacion' => ['nullable', 'string', 'max:40'], 'interaccion' => ['nullable', 'string', 'max:40'], 'cambio_desempeno' => ['nullable', 'boolean'], 'observacion' => $texto]],
        ];
    }

    private function codigo(string $prefijo): string { return $prefijo.'_'.Str::upper(Str::random(12)); }
}
