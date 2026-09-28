<?php

namespace App\Http\Controllers\Residentes;

use App\Http\Controllers\Controller;
use App\Models\Residente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResidenteController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
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

    public function show(Request $request, Residente $residente): View|JsonResponse
    {
        $this->authorize('view', $residente);
        $residente->load(['admisiones', 'vinculosContacto.contacto', 'ocupacionActiva.cama.habitacion', 'atenciones.notas', 'prescripciones.medicamento']);

        return $request->expectsJson() ? response()->json($residente) : view('pages.residentes.show', compact('residente'));
    }
}
