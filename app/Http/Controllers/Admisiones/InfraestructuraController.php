<?php

namespace App\Http\Controllers\Admisiones;

use App\Http\Controllers\Controller;
use App\Models\Cama;
use App\Models\Habitacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InfraestructuraController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Habitacion::query()->with(['camas.ocupacionActiva.residente'])->orderBy('codigo')->get());
    }

    public function habitacion(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'codigo' => ['required', 'string', 'max:30', 'unique:habitaciones,codigo'], 'nombre' => ['nullable', 'string', 'max:80'],
            'tipo' => ['nullable', 'string', 'max:40'], 'piso' => ['nullable', 'string', 'max:30'],
            'capacidad' => ['required', 'integer', 'min:1'], 'observacion' => ['nullable', 'string'],
        ]);
        return response()->json(Habitacion::query()->create([
            'cod_habitacion' => $this->codigo('HAB'), ...$datos, 'estado' => 'ACTIVA',
        ]), 201);
    }

    public function cama(Request $request, Habitacion $habitacion): JsonResponse
    {
        abort_if($habitacion->camas()->count() >= $habitacion->capacidad, 422, 'La habitación alcanzó su capacidad.');
        $datos = $request->validate([
            'codigo' => ['required', 'string', 'max:30', 'unique:camas,codigo'], 'tipo' => ['nullable', 'string', 'max:40'],
            'observacion' => ['nullable', 'string'],
        ]);
        return response()->json(Cama::query()->create([
            'cod_cama' => $this->codigo('CAM'), 'cod_habitacion' => $habitacion->cod_habitacion,
            ...$datos, 'estado' => 'ACTIVA',
        ]), 201);
    }

    private function codigo(string $prefijo): string
    {
        return $prefijo.'_'.Str::upper(Str::random(12));
    }
}
