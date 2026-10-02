<?php

namespace App\Http\Controllers\Residentes;

use App\Http\Controllers\Controller;
use App\Models\Residente;
use App\Models\SignoVital;
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
        $usuario = $request->user();
        $relaciones = ['ocupacionActiva.cama.habitacion'];
        if (! $usuario->hasRole('FAMILIAR') && $usuario->can('residentes_contactos.ver')) {
            $relaciones[] = 'vinculosContacto.contacto';
        }
        $residente->load($relaciones);
        $vitales = $usuario->can('signos_vitales.ver')
            ? SignoVital::query()->where('cod_residente', $residente->cod_residente)->latest('fecha_hora')->limit(50)->get()
            : collect();
        $definiciones = [
            ['label' => 'Presión arterial', 'fields' => ['presion_sistolica', 'presion_diastolica'], 'unit' => 'mmHg'],
            ['label' => 'Frecuencia cardiaca', 'fields' => ['frecuencia_cardiaca'], 'unit' => 'lpm'],
            ['label' => 'Frecuencia respiratoria', 'fields' => ['frecuencia_respiratoria'], 'unit' => 'rpm'],
            ['label' => 'Temperatura', 'fields' => ['temperatura'], 'unit' => '°C'],
            ['label' => 'Saturación de oxígeno', 'fields' => ['saturacion_oxigeno'], 'unit' => '%'],
            ['label' => 'Glucemia', 'fields' => ['glucemia'], 'unit' => 'mg/dL'],
        ];
        $vitalStats = collect($definiciones)->map(function (array $definicion) use ($vitales): ?array {
            $registros = $vitales->filter(fn (SignoVital $registro) => collect($definicion['fields'])
                ->every(fn (string $campo) => $registro->{$campo} !== null))->take(2)->values();
            if ($registros->isEmpty()) {
                return null;
            }
            $valor = fn (SignoVital $registro) => implode(' / ', array_map(fn (string $campo) => $registro->{$campo}, $definicion['fields']));
            return [...$definicion, 'current' => $valor($registros[0]),
                'previous' => isset($registros[1]) ? $valor($registros[1]) : null,
                'recordedAt' => $registros[0]->fecha_hora];
        })->filter()->values();

        return $request->expectsJson() ? response()->json($residente) : view('pages.residentes.show', compact('residente', 'vitalStats'));
    }
}
