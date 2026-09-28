<?php

namespace App\Http\Controllers\Identidad;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Turno;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InstitucionalController extends Controller
{
    public function usuarios(): JsonResponse
    {
        return response()->json(User::query()->with(['personal', 'roles'])->orderBy('correo')->paginate(25));
    }

    public function guardarUsuario(Request $request): JsonResponse
    {
        $datos = $request->validate(['correo' => ['required', 'email', 'max:120', 'unique:usuarios,correo'], 'password' => ['required', 'string', 'min:12'], 'estado' => ['required', 'in:ACTIVO,INACTIVO'], 'rol' => ['required', 'exists:roles,name']]);
        $usuario = User::query()->create(['cod_usuario' => $this->codigo('USU'), 'correo' => $datos['correo'], 'contrasena' => Hash::make($datos['password']), 'estado' => $datos['estado']]);
        $usuario->assignRole($datos['rol']);
        return response()->json($usuario->load('roles'), 201);
    }

    public function guardarPersonal(Request $request): JsonResponse
    {
        $datos = $request->validate(['cod_usuario' => ['required', 'exists:usuarios,cod_usuario', 'unique:personal,cod_usuario'], 'nombres' => ['required', 'string', 'max:100'], 'apellido_paterno' => ['required', 'string', 'max:80'], 'apellido_materno' => ['nullable', 'string', 'max:80'], 'numero_documento' => ['required', 'string', 'max:30', 'unique:personal,numero_documento'], 'profesion' => ['required', 'string', 'max:80'], 'especialidad' => ['nullable', 'string', 'max:120'], 'matricula_profesional' => ['nullable', 'string', 'max:50'], 'telefono' => ['nullable', 'string', 'max:30'], 'estado' => ['required', 'in:ACTIVO,INACTIVO']]);
        return response()->json(Personal::query()->create(['cod_personal' => $this->codigo('PER'), ...$datos]), 201);
    }

    public function guardarArea(Request $request): JsonResponse
    {
        $datos = $request->validate(['nombre' => ['required', 'string', 'max:80', 'unique:areas,nombre'], 'descripcion' => ['nullable', 'string'], 'estado' => ['required', 'in:ACTIVA,INACTIVA']]);
        return response()->json(Area::query()->create(['cod_area' => $this->codigo('ARE'), ...$datos]), 201);
    }

    public function guardarTurno(Request $request): JsonResponse
    {
        $datos = $request->validate(['nombre' => ['required', 'string', 'max:50', 'unique:turnos,nombre'], 'hora_inicio' => ['required', 'date_format:H:i'], 'hora_cierre' => ['required', 'date_format:H:i'], 'orden' => ['required', 'integer', 'min:1'], 'estado' => ['required', 'in:ACTIVO,INACTIVO'], 'observacion' => ['nullable', 'string']]);
        return response()->json(Turno::query()->create(['cod_turno' => $this->codigo('TUR'), ...$datos]), 201);
    }

    public function abrirJornada(Request $request): JsonResponse
    {
        $datos = $request->validate(['cod_turno' => ['required', 'exists:turnos,cod_turno'], 'fecha_jornada' => ['required', 'date']]);
        return response()->json(Jornada::query()->create(['cod_jornada' => $this->codigo('JOR'), ...$datos, 'cod_usuario_apertura' => $request->user()->cod_usuario, 'estado' => 'ABIERTA']), 201);
    }

    public function asignarPersonal(Request $request, Jornada $jornada): JsonResponse
    {
        $datos = $request->validate(['cod_personal' => ['required', 'exists:personal,cod_personal'], 'cod_area' => ['required', 'exists:areas,cod_area'], 'funcion' => ['nullable', 'string', 'max:80'], 'tipo_asignacion' => ['required', 'string', 'max:30']]);
        return response()->json(AsignacionPersonal::query()->create(['cod_asignacion_personal' => $this->codigo('ASP'), 'cod_jornada' => $jornada->cod_jornada, ...$datos, 'fecha_asignacion' => now(), 'estado' => 'ACTIVA']), 201);
    }

    private function codigo(string $prefijo): string { return $prefijo.'_'.Str::upper(Str::random(12)); }
}
