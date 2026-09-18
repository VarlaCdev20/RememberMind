<?php

namespace App\Http\Controllers\Clinica;

use App\Http\Controllers\Controller;
use App\Models\Atencion;
use App\Models\ComponenteEstudio;
use App\Models\Derivacion;
use App\Models\DocumentoClinico;
use App\Models\EstudioClinico;
use App\Models\InformeEstudio;
use App\Models\Residente;
use App\Models\ResultadoEstudio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EstudioClinicoController extends Controller
{
    public function index(Residente $residente): JsonResponse
    {
        $this->authorize('view', $residente);

        return response()->json(EstudioClinico::query()
            ->where('cod_residente', $residente->cod_residente)
            ->with(['tipo.componentes', 'resultados', 'informes'])
            ->latest('fecha_solicitud')->get());
    }

    public function solicitar(Request $request, Residente $residente): JsonResponse
    {
        $this->authorize('view', $residente);
        abort_unless($request->user()->can('estudios_clinicos.crear'), 403);
        $personal = $request->user()->personal()->where('estado', 'ACTIVO')->firstOrFail();
        $datos = $request->validate([
            'cod_atencion' => ['required', 'exists:atenciones,cod_atencion'],
            'cod_tipo_estudio' => ['required', 'exists:tipos_estudio_clinico,cod_tipo_estudio'],
            'motivo' => ['nullable', 'string'], 'prioridad' => ['nullable', 'string', 'max:20'],
            'centro_medico' => ['nullable', 'string', 'max:160'], 'observacion' => ['nullable', 'string'],
        ]);
        $this->validarAtencion($datos['cod_atencion'], $residente);
        $estudio = EstudioClinico::query()->create([
            'cod_estudio' => $this->codigo('EST'), 'cod_residente' => $residente->cod_residente,
            'cod_personal' => $personal->cod_personal, ...$datos,
            'fecha_solicitud' => now(), 'estado' => 'SOLICITADO',
        ]);

        return response()->json($estudio, 201);
    }

    public function resultados(Request $request, EstudioClinico $estudio): JsonResponse
    {
        $residente = $estudio->residente()->firstOrFail();
        $this->authorize('view', $residente);
        abort_unless($request->user()->can('resultados_estudio.crear'), 403);
        $datos = $request->validate([
            'resultados' => ['required', 'array', 'min:1'],
            'resultados.*.cod_componente' => ['required', 'distinct', 'exists:componentes_estudio,cod_componente'],
            'resultados.*.valor_numerico' => ['nullable', 'numeric'], 'resultados.*.valor_texto' => ['nullable', 'string'],
            'resultados.*.unidad' => ['nullable', 'string', 'max:40'], 'resultados.*.rango_referencia' => ['nullable', 'string', 'max:120'],
            'resultados.*.clasificacion' => ['nullable', 'string', 'max:30'], 'resultados.*.observacion' => ['nullable', 'string'],
        ]);
        $componentes = ComponenteEstudio::query()->where('cod_tipo_estudio', $estudio->cod_tipo_estudio)
            ->pluck('cod_componente')->all();
        foreach ($datos['resultados'] as $fila) {
            abort_unless(in_array($fila['cod_componente'], $componentes, true), 422, 'El componente no pertenece al tipo de estudio.');
            abort_if(! array_key_exists('valor_numerico', $fila) && blank($fila['valor_texto'] ?? null), 422, 'Cada resultado requiere un valor.');
        }

        $registros = DB::transaction(function () use ($estudio, $datos): array {
            $creados = [];
            foreach ($datos['resultados'] as $fila) {
                $creados[] = ResultadoEstudio::query()->updateOrCreate(
                    ['cod_estudio' => $estudio->cod_estudio, 'cod_componente' => $fila['cod_componente']],
                    ['cod_resultado_estudio' => $this->codigo('RES'), ...$fila]
                );
            }
            $estudio->update(['fecha_realizacion' => now(), 'estado' => 'REALIZADO']);
            return $creados;
        });

        return response()->json($registros, 201);
    }

    public function informar(Request $request, EstudioClinico $estudio): JsonResponse
    {
        $this->authorize('view', $estudio->residente()->firstOrFail());
        abort_unless($request->user()->can('informes_estudio.crear'), 403);
        $personal = $request->user()->personal()->where('estado', 'ACTIVO')->first();
        $datos = $request->validate([
            'hallazgos' => ['nullable', 'string'], 'conclusion' => ['nullable', 'string'],
            'recomendacion' => ['nullable', 'string'], 'origen' => ['required', 'in:INTERNO,EXTERNO'],
            'profesional_externo' => ['nullable', 'required_if:origen,EXTERNO', 'string', 'max:160'],
        ]);
        $informe = InformeEstudio::query()->create([
            'cod_informe_estudio' => $this->codigo('INF'), 'cod_estudio' => $estudio->cod_estudio,
            'cod_personal' => $personal?->cod_personal, ...$datos, 'fecha_hora' => now(), 'estado' => 'VIGENTE',
        ]);

        return response()->json($informe, 201);
    }

    public function documento(Request $request, Residente $residente): JsonResponse
    {
        $this->authorize('view', $residente);
        abort_unless($request->user()->can('documentos_clinicos.crear'), 403);
        $personal = $request->user()->personal()->where('estado', 'ACTIVO')->first();
        $datos = $request->validate([
            'archivo' => ['required', 'file', 'max:20480'], 'cod_estudio' => ['nullable', 'exists:estudios_clinicos,cod_estudio'],
            'cod_atencion' => ['nullable', 'exists:atenciones,cod_atencion'], 'tipo_documento' => ['required', 'string', 'max:60'],
            'titulo' => ['required', 'string', 'max:180'], 'descripcion' => ['nullable', 'string'],
            'origen' => ['nullable', 'string', 'max:20'], 'observacion' => ['nullable', 'string'],
        ]);
        if (isset($datos['cod_estudio'])) {
            abort_unless(EstudioClinico::query()->whereKey($datos['cod_estudio'])->where('cod_residente', $residente->cod_residente)->exists(), 422, 'El estudio no pertenece al residente.');
        }
        $this->validarAtencion($datos['cod_atencion'] ?? null, $residente);
        $archivo = $request->file('archivo');
        unset($datos['archivo']);
        $codigo = $this->codigo('DCL');
        $ruta = $archivo->storeAs('documentos-clinicos/'.$residente->cod_residente, $codigo.'.'.$archivo->extension(), 'local');
        $documento = DocumentoClinico::query()->create([
            'cod_documento_clinico' => $codigo, 'cod_residente' => $residente->cod_residente,
            'cod_personal' => $personal?->cod_personal, ...$datos, 'ruta_archivo' => $ruta,
            'formato' => $archivo->getMimeType() ?: 'application/octet-stream', 'tamano_bytes' => $archivo->getSize(),
            'hash_archivo' => hash_file('sha256', $archivo->getRealPath()), 'fecha_hora' => now(), 'estado' => 'VIGENTE',
        ]);

        return response()->json($documento, 201);
    }

    public function descargar(DocumentoClinico $documentoClinico): StreamedResponse
    {
        $this->authorize('view', $documentoClinico->residente()->firstOrFail());
        abort_unless(request()->user()->can('documentos_clinicos.ver'), 403);
        abort_unless(Storage::disk('local')->exists($documentoClinico->ruta_archivo), 404);

        return Storage::disk('local')->download($documentoClinico->ruta_archivo, $documentoClinico->titulo);
    }

    public function derivar(Request $request, Residente $residente): JsonResponse
    {
        $this->authorize('view', $residente);
        abort_unless($request->user()->can('derivaciones.crear'), 403);
        $personal = $request->user()->personal()->where('estado', 'ACTIVO')->firstOrFail();
        $datos = $request->validate([
            'cod_area_solicitante' => ['required', 'exists:areas,cod_area'], 'cod_area_receptora' => ['required', 'different:cod_area_solicitante', 'exists:areas,cod_area'],
            'cod_personal_receptor' => ['nullable', 'exists:personal,cod_personal'], 'cod_atencion' => ['nullable', 'exists:atenciones,cod_atencion'],
            'motivo' => ['required', 'string'], 'prioridad' => ['nullable', 'string', 'max:20'],
        ]);
        $this->validarAtencion($datos['cod_atencion'] ?? null, $residente);
        return response()->json(Derivacion::query()->create([
            'cod_derivacion' => $this->codigo('DER'), 'cod_residente' => $residente->cod_residente,
            'cod_personal_solicitante' => $personal->cod_personal, ...$datos,
            'fecha_hora' => now(), 'estado' => 'PENDIENTE',
        ]), 201);
    }

    private function validarAtencion(?string $codigo, Residente $residente): void
    {
        if ($codigo !== null) {
            abort_unless(Atencion::query()->whereKey($codigo)->where('cod_residente', $residente->cod_residente)->exists(), 422, 'La atención no pertenece al residente.');
        }
    }

    private function codigo(string $prefijo): string
    {
        return $prefijo.'_'.Str::upper(Str::random(12));
    }
}
