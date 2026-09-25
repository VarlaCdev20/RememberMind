<?php

namespace App\Http\Controllers\Cuidados;

use App\Http\Controllers\Controller;
use App\Models\AsignacionResidenteJornada;
use App\Models\ControlCognitivo;
use App\Models\CuracionHerida;
use App\Models\EjecucionCuidado;
use App\Models\Herida;
use App\Models\IntervencionCuidado;
use App\Models\PaseTurno;
use App\Models\PlanCuidado;
use App\Models\ProgramacionCuidado;
use App\Models\RegistroConductual;
use App\Models\RegistroEliminacion;
use App\Models\RegistroHidratacion;
use App\Models\RegistroIngesta;
use App\Models\RegistroMovilidad;
use App\Models\RegistroSueno;
use App\Models\Residente;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CuidadoController extends Controller
{
    public function index(Request $request, Residente $residente): JsonResponse
    {
        $this->authorize('view', $residente);
        return response()->json([
            'asignaciones' => AsignacionResidenteJornada::query()->where('cod_residente', $residente->cod_residente)->get(),
            'controles_cognitivos' => ControlCognitivo::query()->where('cod_residente', $residente->cod_residente)->latest('fecha_hora')->get(),
            'conducta' => RegistroConductual::query()->where('cod_residente', $residente->cod_residente)->latest('fecha_hora')->get(),
            'sueno' => RegistroSueno::query()->where('cod_residente', $residente->cod_residente)->latest('fecha')->get(),
            'ingesta' => RegistroIngesta::query()->where('cod_residente', $residente->cod_residente)->latest('fecha_hora')->get(),
            'hidratacion' => RegistroHidratacion::query()->where('cod_residente', $residente->cod_residente)->latest('fecha_hora')->get(),
            'eliminacion' => RegistroEliminacion::query()->where('cod_residente', $residente->cod_residente)->latest('fecha_hora')->get(),
            'movilidad' => RegistroMovilidad::query()->where('cod_residente', $residente->cod_residente)->latest('fecha_hora')->get(),
            'heridas' => Herida::query()->where('cod_residente', $residente->cod_residente)->with('curaciones')->latest('fecha_hora_identificacion')->get(),
            'planes' => PlanCuidado::query()->where('cod_residente', $residente->cod_residente)->with('intervenciones')->get(),
            'ejecuciones' => EjecucionCuidado::query()->where('cod_residente', $residente->cod_residente)->latest('fecha_hora_programada')->get(),
            'pases' => PaseTurno::query()->where('cod_residente', $residente->cod_residente)->latest('fecha_hora')->get(),
        ]);
    }

    public function registrar(Request $request, Residente $residente, string $tipo): JsonResponse
    {
        $this->authorize('view', $residente);
        $definicion = $this->definiciones()[$tipo] ?? abort(404);
        abort_unless($request->user()->can($definicion['permiso']), 403);
        $personal = $request->user()->personal()->where('estado', 'ACTIVO')->firstOrFail();
        $datos = $request->validate($definicion['reglas']);
        $this->validarAtencion($datos['cod_atencion'] ?? null, $residente);
        /** @var class-string<Model> $modelo */
        $modelo = $definicion['modelo'];
        $registro = $modelo::query()->create([$definicion['pk'] => $this->codigo($definicion['prefijo']), 'cod_residente' => $residente->cod_residente, 'cod_personal' => $personal->cod_personal, ...$datos, ...$definicion['valores']]);
        return response()->json($registro, 201);
    }

    public function crearPlan(Request $request, Residente $residente): JsonResponse
    {
        $this->authorize('view', $residente);
        abort_unless($request->user()->can('planes_cuidado.crear'), 403);
        $personal = $request->user()->personal()->where('estado', 'ACTIVO')->firstOrFail();
        $datos = $request->validate(['cod_area' => ['required', 'exists:areas,cod_area'], 'tipo_plan' => ['required', 'string', 'max:50'], 'nombre' => ['required', 'string', 'max:160'], 'objetivo_general' => ['required', 'string'], 'prioridad' => ['nullable', 'string', 'max:20'], 'observacion' => ['nullable', 'string']]);
        return response()->json(PlanCuidado::query()->create(['cod_plan' => $this->codigo('PLA'), 'cod_residente' => $residente->cod_residente, 'cod_personal' => $personal->cod_personal, ...$datos, 'fecha_hora_apertura' => now(), 'estado' => 'ACTIVO']), 201);
    }

    public function crearIntervencion(Request $request, PlanCuidado $plan): JsonResponse
    {
        abort_unless($request->user()->can('planes_cuidado.crear'), 403);
        $this->authorize('view', $plan->residente()->firstOrFail());
        $datos = $request->validate(['nombre' => ['required', 'string', 'max:160'], 'descripcion' => ['required', 'string'], 'objetivo_especifico' => ['nullable', 'string'], 'prioridad' => ['nullable', 'string', 'max:20']]);
        return response()->json(IntervencionCuidado::query()->create(['cod_intervencion' => $this->codigo('INT'), 'cod_plan' => $plan->cod_plan, ...$datos, 'estado' => 'ACTIVA']), 201);
    }

    public function programar(Request $request, IntervencionCuidado $intervencion): JsonResponse
    {
        abort_unless($request->user()->can('planes_cuidado.crear'), 403);
        $this->authorize('view', $intervencion->plan()->firstOrFail()->residente()->firstOrFail());
        $datos = $request->validate(['cod_turno' => ['nullable', 'exists:turnos,cod_turno'], 'frecuencia' => ['required', 'string', 'max:60'], 'dias_semana' => ['nullable', 'string', 'max:50'], 'hora_programada' => ['nullable', 'date_format:H:i'], 'fecha_activacion' => ['required', 'date'], 'fecha_desactivacion' => ['nullable', 'date', 'after_or_equal:fecha_activacion']]);
        return response()->json(ProgramacionCuidado::query()->create(['cod_programacion' => $this->codigo('PRC'), 'cod_intervencion' => $intervencion->cod_intervencion, ...$datos, 'estado' => 'ACTIVA']), 201);
    }

    public function asignarJornada(Request $request, Residente $residente): JsonResponse
    {
        $this->authorize('view', $residente);
        abort_unless($request->user()->can('asignaciones_residente_jornada.gestionar'), 403);
        $datos = $request->validate([
            'cod_jornada' => ['required', 'exists:jornadas,cod_jornada'],
            'cod_personal' => ['required', 'exists:personal,cod_personal'],
            'nivel_supervision' => ['nullable', 'string', 'max:30'],
            'observacion' => ['nullable', 'string'],
        ]);
        $existe = AsignacionResidenteJornada::query()->where('cod_residente', $residente->cod_residente)
            ->where('cod_jornada', $datos['cod_jornada'])->where('cod_personal', $datos['cod_personal'])
            ->where('estado', 'ACTIVA')->exists();
        abort_if($existe, 409, 'La asignación activa ya existe.');
        return response()->json(AsignacionResidenteJornada::query()->create([
            'cod_asignacion' => $this->codigo('ARJ'), 'cod_residente' => $residente->cod_residente,
            ...$datos, 'fecha_hora' => now(), 'estado' => 'ACTIVA',
        ]), 201);
    }

    public function curarHerida(Request $request, Herida $herida): JsonResponse
    {
        $this->authorize('view', $herida->residente()->firstOrFail());
        abort_unless($request->user()->can('curaciones_herida.crear'), 403);
        $personal = $request->user()->personal()->where('estado', 'ACTIVO')->firstOrFail();
        $datos = $request->validate([
            'cod_jornada' => ['nullable', 'exists:jornadas,cod_jornada'], 'longitud' => ['nullable', 'numeric', 'min:0'],
            'ancho' => ['nullable', 'numeric', 'min:0'], 'profundidad' => ['nullable', 'numeric', 'min:0'],
            'tejido' => ['nullable', 'string', 'max:80'], 'exudado' => ['nullable', 'string', 'max:80'],
            'olor' => ['nullable', 'string', 'max:80'], 'dolor' => ['nullable', 'string', 'max:40'],
            'procedimiento' => ['required', 'string'], 'materiales' => ['nullable', 'string'],
            'respuesta' => ['nullable', 'string'], 'observacion' => ['nullable', 'string'],
        ]);
        return response()->json(CuracionHerida::query()->create([
            'cod_curacion' => $this->codigo('CUR'), 'cod_herida' => $herida->cod_herida,
            'cod_personal' => $personal->cod_personal, ...$datos, 'fecha_hora' => now(),
        ]), 201);
    }

    public function registrarPase(Request $request, Residente $residente): JsonResponse
    {
        $this->authorize('view', $residente);
        abort_unless($request->user()->can('pases_turno.crear'), 403);
        $personal = $request->user()->personal()->where('estado', 'ACTIVO')->firstOrFail();
        $datos = $request->validate([
            'cod_jornada_saliente' => ['required', 'exists:jornadas,cod_jornada'],
            'cod_jornada_entrante' => ['required', 'different:cod_jornada_saliente', 'exists:jornadas,cod_jornada'],
            'cod_personal_entrante' => ['nullable', 'exists:personal,cod_personal'],
            'estado_general' => ['nullable', 'string'], 'resumen' => ['required', 'string'],
            'pendientes' => ['nullable', 'string'], 'vigilancia' => ['nullable', 'string'],
            'recomendacion' => ['nullable', 'string'],
        ]);
        return response()->json(PaseTurno::query()->create([
            'cod_pase' => $this->codigo('PAS'), 'cod_residente' => $residente->cod_residente,
            'cod_personal_saliente' => $personal->cod_personal, ...$datos,
            'fecha_hora' => now(), 'estado' => 'EMITIDO',
        ]), 201);
    }

    public function ejecutar(Request $request, IntervencionCuidado $intervencion): JsonResponse
    {
        $plan = $intervencion->plan()->firstOrFail();
        $residente = $plan->residente()->firstOrFail();
        $this->authorize('view', $residente);
        abort_unless($request->user()->can('ejecuciones_cuidado.crear'), 403);
        $personal = $request->user()->personal()->where('estado', 'ACTIVO')->firstOrFail();
        $datos = $request->validate([
            'cod_jornada' => ['required', 'exists:jornadas,cod_jornada'],
            'fecha_hora_programada' => ['nullable', 'date'], 'fecha_hora_ejecucion' => ['nullable', 'date'],
            'resultado' => ['nullable', 'string', 'max:60'], 'motivo_omision' => ['nullable', 'string'],
            'estado' => ['required', 'in:PENDIENTE,EJECUTADA,OMITIDA,CANCELADA'], 'observacion' => ['nullable', 'string'],
        ]);
        abort_if($datos['estado'] === 'OMITIDA' && blank($datos['motivo_omision'] ?? null), 422, 'Una omisión requiere motivo.');
        return response()->json(EjecucionCuidado::query()->create([
            'cod_ejecucion' => $this->codigo('EJC'), 'cod_intervencion' => $intervencion->cod_intervencion,
            'cod_residente' => $residente->cod_residente, 'cod_personal' => $personal->cod_personal, ...$datos,
        ]), 201);
    }

    private function definiciones(): array
    {
        $texto = ['nullable', 'string'];
        $jornada = ['required', 'exists:jornadas,cod_jornada'];
        return [
            'control-cognitivo' => ['modelo' => ControlCognitivo::class, 'pk' => 'cod_control_cognitivo', 'prefijo' => 'CCO', 'permiso' => 'controles_cognitivos.crear', 'reglas' => ['cod_jornada' => ['nullable', 'exists:jornadas,cod_jornada'], 'cod_atencion' => ['nullable', 'exists:atenciones,cod_atencion'], 'orientacion_persona' => ['nullable', 'string', 'max:30'], 'orientacion_lugar' => ['nullable', 'string', 'max:30'], 'orientacion_tiempo' => ['nullable', 'string', 'max:30'], 'memoria_reciente' => ['nullable', 'string', 'max:30'], 'memoria_remota' => ['nullable', 'string', 'max:30'], 'atencion' => ['nullable', 'string', 'max:30'], 'comprension' => ['nullable', 'string', 'max:30'], 'lenguaje' => ['nullable', 'string', 'max:30'], 'sigue_instrucciones' => ['nullable', 'boolean'], 'confusion' => ['nullable', 'boolean'], 'cambio_cognitivo' => ['nullable', 'boolean'], 'observacion' => $texto], 'valores' => ['fecha_hora' => now(), 'estado' => 'VIGENTE']],
            'conducta' => ['modelo' => RegistroConductual::class, 'pk' => 'cod_registro_conductual', 'prefijo' => 'RCO', 'permiso' => 'registros_conductuales.crear', 'reglas' => ['cod_jornada' => ['nullable', 'exists:jornadas,cod_jornada'], 'cod_atencion' => ['nullable', 'exists:atenciones,cod_atencion'], 'estado_animo' => ['nullable', 'string', 'max:40'], 'apatia' => ['nullable', 'boolean'], 'agitacion' => ['nullable', 'boolean'], 'agresividad' => ['nullable', 'boolean'], 'ansiedad' => ['nullable', 'boolean'], 'aislamiento' => ['nullable', 'boolean'], 'deambulacion' => ['nullable', 'boolean'], 'descripcion' => $texto, 'intervencion' => $texto, 'respuesta' => $texto], 'valores' => ['fecha_hora' => now(), 'estado' => 'VIGENTE']],
            'sueno' => ['modelo' => RegistroSueno::class, 'pk' => 'cod_registro_sueno', 'prefijo' => 'RSU', 'permiso' => 'registros_sueno.crear', 'reglas' => ['cod_jornada' => ['nullable', 'exists:jornadas,cod_jornada'], 'fecha' => ['required', 'date'], 'horas_sueno' => ['nullable', 'numeric', 'between:0,24'], 'despertares' => ['nullable', 'integer', 'min:0'], 'insomnio' => ['nullable', 'boolean'], 'somnolencia_diurna' => ['nullable', 'boolean'], 'agitacion_nocturna' => ['nullable', 'boolean'], 'calidad' => ['nullable', 'string', 'max:30'], 'observacion' => $texto], 'valores' => ['estado' => 'VIGENTE']],
            'ingesta' => ['modelo' => RegistroIngesta::class, 'pk' => 'cod_ingesta', 'prefijo' => 'ING', 'permiso' => 'registros_ingesta.crear', 'reglas' => ['cod_jornada' => $jornada, 'tipo_comida' => ['required', 'string', 'max:40'], 'porcentaje_consumido' => ['nullable', 'numeric', 'between:0,100'], 'apetito' => ['nullable', 'string', 'max:30'], 'tolerancia' => ['nullable', 'string', 'max:30'], 'dificultad_deglucion' => ['nullable', 'boolean'], 'observacion' => $texto], 'valores' => ['fecha_hora' => now(), 'estado' => 'VIGENTE']],
            'hidratacion' => ['modelo' => RegistroHidratacion::class, 'pk' => 'cod_hidratacion', 'prefijo' => 'HID', 'permiso' => 'registros_hidratacion.crear', 'reglas' => ['cod_jornada' => $jornada, 'cantidad_ml' => ['required', 'numeric', 'min:0'], 'tipo_liquido' => ['nullable', 'string', 'max:60'], 'tolerancia' => ['nullable', 'string', 'max:30'], 'observacion' => $texto], 'valores' => ['fecha_hora' => now(), 'estado' => 'VIGENTE']],
            'eliminacion' => ['modelo' => RegistroEliminacion::class, 'pk' => 'cod_eliminacion', 'prefijo' => 'ELI', 'permiso' => 'registros_eliminacion.crear', 'reglas' => ['cod_jornada' => $jornada, 'tipo_eliminacion' => ['required', 'string', 'max:30'], 'cantidad' => ['nullable', 'string', 'max:40'], 'caracteristica' => ['nullable', 'string', 'max:120'], 'continencia' => ['nullable', 'string', 'max:30'], 'observacion' => $texto], 'valores' => ['fecha_hora' => now(), 'estado' => 'VIGENTE']],
            'movilidad' => ['modelo' => RegistroMovilidad::class, 'pk' => 'cod_movilidad', 'prefijo' => 'MOV', 'permiso' => 'registros_movilidad.crear', 'reglas' => ['cod_jornada' => ['nullable', 'exists:jornadas,cod_jornada'], 'cod_atencion' => ['nullable', 'exists:atenciones,cod_atencion'], 'marcha' => ['nullable', 'string', 'max:40'], 'equilibrio' => ['nullable', 'string', 'max:40'], 'traslado' => ['nullable', 'string', 'max:40'], 'tipo_apoyo' => ['nullable', 'string', 'max:60'], 'dispositivo' => ['nullable', 'string', 'max:80'], 'fatiga' => ['nullable', 'string', 'max:30'], 'riesgo_caida' => ['nullable', 'string', 'max:30'], 'observacion' => $texto], 'valores' => ['fecha_hora' => now(), 'estado' => 'VIGENTE']],
            'herida' => ['modelo' => Herida::class, 'pk' => 'cod_herida', 'prefijo' => 'HER', 'permiso' => 'heridas.crear', 'reglas' => ['tipo_herida' => ['required', 'string', 'max:60'], 'ubicacion' => ['required', 'string', 'max:120'], 'causa' => ['nullable', 'string', 'max:120'], 'clasificacion' => ['nullable', 'string', 'max:60'], 'observacion' => $texto], 'valores' => ['fecha_hora_identificacion' => now(), 'estado' => 'ACTIVA']],
        ];
    }

    private function validarAtencion(?string $codigo, Residente $residente): void
    {
        if ($codigo !== null) {
            abort_unless(\App\Models\Atencion::query()->whereKey($codigo)->where('cod_residente', $residente->cod_residente)->exists(), 422, 'La atención no pertenece al residente.');
        }
    }

    private function codigo(string $prefijo): string { return $prefijo.'_'.Str::upper(Str::random(12)); }
}
