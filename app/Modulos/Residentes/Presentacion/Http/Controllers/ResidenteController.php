<?php

namespace App\Modulos\Residentes\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Residente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResidenteController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Residente::class);
        if ($request->user()->hasRole('GERENTE') && !$request->user()->hasRole('SUPERADMINISTRADOR')) {
            return $this->directorioGerencia($request);
        }
        $query = Residente::query()->with('ocupacionActiva.cama.habitacion')
            ->when($request->filled('buscar'), function ($consulta) use ($request): void {
                $termino = '%'.trim((string) $request->string('buscar')).'%';
                $consulta->where(fn ($q) => $q->where('nombres', 'like', $termino)
                    ->orWhere('apellido_paterno', 'like', $termino)
                    ->orWhere('numero_documento', 'like', $termino));
            })->orderBy('apellido_paterno');
        $residentes = $query->paginate(20)->withQueryString();

        return $request->expectsJson() ? response()->json($residentes) : view('pages.residentes.index', compact('residentes'));
    }

    private function directorioGerencia(Request $request): View
    {
        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:120'], 'estado' => ['nullable', 'string', 'max:20'],
            'vista' => ['nullable', 'in:tarjetas,tabla'], 'alojamiento' => ['nullable', 'in:con_cama,sin_cama'],
            'piso' => ['nullable', 'string', 'max:30'], 'habitacion' => ['nullable', 'exists:habitaciones,cod_habitacion'],
        ]);
        $verAlojamiento = $request->user()->can('habitaciones.ver') && $request->user()->can('ocupaciones_cama.ver');
        abort_if(!$verAlojamiento && ($request->filled('alojamiento') || $request->filled('piso') || $request->filled('habitacion')), 403);
        $vista = $filtros['vista'] ?? 'tarjetas';
        $consulta = Residente::query()->when($request->filled('buscar'), function ($query) use ($filtros) {
            foreach (preg_split('/\s+/u', trim($filtros['buscar'])) as $palabra) {
                $query->where(fn ($q) => $q->whereLike('nombres', '%'.$palabra.'%')
                    ->orWhereLike('apellido_paterno', '%'.$palabra.'%')->orWhereLike('apellido_materno', '%'.$palabra.'%'));
            }
        })->when($request->filled('estado'), fn ($q) => $q->where('estado', $filtros['estado']));
        if ($verAlojamiento) {
            $consulta->with('ocupacionActiva.cama.habitacion')
                ->when(($filtros['alojamiento'] ?? '') === 'con_cama', fn ($q) => $q->has('ocupacionActiva'))
                ->when(($filtros['alojamiento'] ?? '') === 'sin_cama', fn ($q) => $q->doesntHave('ocupacionActiva'))
                ->when($request->filled('piso'), fn ($q) => $q->whereHas('ocupacionActiva.cama.habitacion', fn ($h) => $h->where('piso', $filtros['piso'])))
                ->when($request->filled('habitacion'), fn ($q) => $q->whereHas('ocupacionActiva.cama', fn ($c) => $c->where('cod_habitacion', $filtros['habitacion'])));
        }
        $resumen = ['total' => (clone $consulta)->count(), 'con_cama' => $verAlojamiento ? (clone $consulta)->has('ocupacionActiva')->count() : null,
            'sin_cama' => $verAlojamiento ? (clone $consulta)->doesntHave('ocupacionActiva')->count() : null];
        $residentes = $consulta->orderBy('apellido_paterno')->orderBy('apellido_materno')->orderBy('nombres')->orderBy('cod_residente')->paginate(12)->withQueryString();
        $estados = Residente::query()->distinct()->orderBy('estado')->pluck('estado');
        $habitaciones = $verAlojamiento ? \App\Models\Habitacion::query()->orderBy('piso')->orderBy('codigo')->get(['cod_habitacion', 'codigo', 'piso']) : collect();
        $pisos = $habitaciones->pluck('piso')->filter()->unique()->values();

        return view('residentes::paginas.gerencia.residentes', compact('residentes', 'estados', 'habitaciones', 'pisos', 'vista', 'resumen', 'verAlojamiento'));
    }

    public function show(Request $request, Residente $residente): View|JsonResponse
    {
        $this->authorize('view', $residente);
        if ($request->user()->hasRole('FAMILIAR')) {
            $informacionFamiliar = $residente->only(['cod_residente', 'nombres', 'apellido_paterno']);

            return $request->expectsJson()
                ? response()->json($informacionFamiliar)
                : view('pages.residentes.show', compact('informacionFamiliar'));
        }
        if ($request->user()->hasRole('GERENTE') && !$request->user()->hasRole('SUPERADMINISTRADOR')) {
            return app(ResidenteAdministrativoController::class)->show($request, $residente);
        }
        $residente->load(['admisiones', 'vinculosContacto.contacto', 'ocupacionActiva.cama.habitacion', 'atenciones.notas', 'prescripciones.medicamento']);

        return $request->expectsJson() ? response()->json($residente) : view('pages.residentes.show', compact('residente'));
    }
}
