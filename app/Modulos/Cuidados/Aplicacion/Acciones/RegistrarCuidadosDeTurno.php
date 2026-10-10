<?php

namespace App\Modulos\Cuidados\Aplicacion\Acciones;

use App\Models\Alerta;
use App\Models\CuracionHerida;
use App\Models\EjecucionCuidado;
use App\Models\Herida;
use App\Models\IntervencionCuidado;
use App\Models\RegistroIngesta;
use App\Models\RegistroSueno;
use App\Models\Residente;
use App\Models\User;
use App\Models\ValoracionDolor;
use App\Modulos\Cuidados\Aplicacion\Consultas\MiTurnoService;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class RegistrarCuidadosDeTurno
{
    public function guardarSeguimientoLesion(User $usuario, array $entrada): void
    {
        abort_unless($usuario?->can('curaciones_herida.crear'), 403);
        $this->autorizarMutacion($usuario, $entrada['codResidente']);
        Validator::make($entrada, ['lesionId' => 'required|string|max:20', 'largoLesion' => 'nullable|numeric|min:0|max:99999', 'anchoLesion' => 'nullable|numeric|min:0|max:99999', 'profundidadLesion' => 'nullable|numeric|min:0|max:99999', 'dolorLesion' => 'nullable|integer|min:0|max:10', 'aspectoLesion' => 'required|string|min:5|max:1000', 'accionLesion' => 'required|string|min:5|max:2000', 'observacionLesion' => 'nullable|string|max:2000'])->validate();
        $personal = $usuario?->personal;
        abort_unless($personal, 403, 'Personal clínico no vinculado.');
        $herida = Herida::query()->where('cod_residente', $entrada['codResidente'])->where('estado', 'ACTIVA')->findOrFail($entrada['lesionId']);
        $miTurnoService = app(MiTurnoService::class);
        $jornada = $miTurnoService->resolverJornadaActual($personal, now());
        abort_unless($jornada, 409, 'No existe una jornada profesional activa para firmar la curación.');
        CuracionHerida::create(['cod_curacion' => 'CUR_'.strtoupper(Str::random(10)), 'cod_herida' => $herida->cod_herida, 'cod_personal' => $personal->cod_personal, 'cod_jornada' => $jornada->cod_jornada, 'fecha_hora' => now(), 'longitud' => $entrada['largoLesion'] !== null ? (float) $entrada['largoLesion'] : null, 'ancho' => $entrada['anchoLesion'] !== null ? (float) $entrada['anchoLesion'] : null, 'profundidad' => $entrada['profundidadLesion'] !== null ? (float) $entrada['profundidadLesion'] : null, 'tejido' => $entrada['aspectoLesion'] ?: null, 'exudado' => $entrada['exudadoLesion'] ?: null, 'dolor' => $entrada['dolorLesion'] !== null ? (string) $entrada['dolorLesion'] : null, 'procedimiento' => $entrada['accionLesion'], 'observacion' => $entrada['observacionLesion'] ?: null]);
    }

    public function cerrarLesion(User $usuario, array $entrada, string $codigo): void
    {
        $this->autorizarMutacion($usuario, $entrada['codResidente']);
        Validator::make($entrada, ['resultadoCierreLesion' => 'required|string|min:5|max:1000', 'motivoCierreLesion' => 'required|string|min:5|max:1000'])->validate();
        $herida = Herida::where('cod_residente', $entrada['codResidente'])->where('estado', 'ACTIVA')->findOrFail($codigo);
        $herida->update(['estado' => 'CERRADA', 'fecha_hora_cierre' => now(), 'observacion' => trim($herida->observacion."\n[Cierre: ".$entrada['resultadoCierreLesion'].' - '.$entrada['motivoCierreLesion'].']')]);
    }

    public function rectificarCuidado(User $usuario, array $entrada): void
    {
        $this->autorizarMutacion($usuario, $entrada['codResidente']);
        Validator::make($entrada, ['registroRectificarId' => 'required|string|max:20', 'motivoRectificacion' => 'required|string|min:10|max:2000'])->validate();
        $original = EjecucionCuidado::where('cod_residente', $entrada['codResidente'])->findOrFail($entrada['registroRectificarId']);
        $original->update(['observacion' => trim($original->observacion.' [Rectificación '.now()->format('d/m/Y H:i').' por '.$usuario->personal->cod_personal.': '.$entrada['motivoRectificacion'].']')]);
    }

    private function autorizarMutacion(User $usuario, string $codResidente): void
    {
        app(TurnoEnfermeriaService::class)->autorizarMutacionPaciente($codResidente, 'atenciones.crear', $usuario);
    }

    public function guardarCuidado(User $usuario, array $entrada): void
    {
        $this->autorizarMutacion($usuario, $entrada['codResidente']);
        $permiso = match ($entrada['tipo']) {
            'ALIMENTACION' => 'registros_ingesta.crear',
            'HIDRATACION' => 'registros_hidratacion.crear',
            'ELIMINACION' => 'registros_eliminacion.crear',
            'MOVILIDAD' => 'registros_movilidad.crear',
            'SUENO' => 'registros_sueno.crear',
            default => 'atenciones.crear',
        };
        abort_unless($usuario?->can($permiso), 403);
        $user = $usuario;
        $personal = $user?->personal;
        abort_unless($personal, 403, 'Personal clínico no vinculado.');
        $miTurnoService = app(MiTurnoService::class);
        $jornada = $miTurnoService->resolverJornadaActual($personal, now());
        if (! $jornada) {
            throw ValidationException::withMessages(['jornada' => 'No existe una jornada activa asignada al personal clínico.']);
        }
        $tipoNorm = strtoupper(trim($entrada['tipo']));
        DB::transaction(function () use ($personal, $jornada, $tipoNorm, $entrada) {
            if ($tipoNorm === 'ALIMENTACION') {
                RegistroIngesta::create(['cod_ingesta' => 'ING_'.strtoupper(Str::random(10)), 'cod_residente' => $entrada['codResidente'], 'cod_personal' => $personal->cod_personal, 'cod_jornada' => $jornada->cod_jornada, 'tipo_comida' => $entrada['subtipo'], 'porcentaje_consumido' => $entrada['porcentaje'] !== null ? (float) $entrada['porcentaje'] : null, 'apetito' => $entrada['estadoGeneral'] ?: null, 'tolerancia' => $entrada['tolerancia'] ?: null, 'dificultad_deglucion' => (bool) $entrada['presentaDificultad'], 'fecha_hora' => now(), 'estado' => 'VIGENTE', 'observacion' => ($entrada['observacion'] ?: $entrada['motivo']) ?: null]);
            } elseif ($tipoNorm === 'SUENO') {
                RegistroSueno::create(['cod_registro_sueno' => 'RSU_'.strtoupper(Str::random(10)), 'cod_residente' => $entrada['codResidente'], 'cod_personal' => $personal->cod_personal, 'cod_jornada' => $jornada->cod_jornada, 'fecha' => today()->toDateString(), 'despertares' => $entrada['cantidadDespertares'] !== null ? (int) $entrada['cantidadDespertares'] : null, 'insomnio' => (bool) $entrada['agitacion'], 'calidad' => $entrada['calidad'] ?: null, 'estado' => 'VIGENTE', 'observacion' => ($entrada['observacion'] ?: $entrada['motivo']) ?: null]);
            } else {
                // HIGIENE, PROCEDIMIENTO y cuidados generales
                // Validar: intervencion -> plan -> plan.cod_residente == residente actual
                $queryIntervencion = IntervencionCuidado::whereIn('estado', ['ACTIVA', 'ACTIVO', 'VIGENTE'])->whereHas('plan', function ($q) use ($entrada) {
                    $q->where('cod_residente', $entrada['codResidente'])->whereIn('estado', ['ACTIVO', 'ACTIVA', 'VIGENTE']);
                });
                $intervencion = $queryIntervencion->where('cod_intervencion', $entrada['intervencionId'])->first();
                if (! $intervencion) {
                    $intervencionOtroResidente = IntervencionCuidado::where('cod_intervencion', $entrada['intervencionId'])->first();
                    if ($intervencionOtroResidente) {
                        abort(422, 'La intervención seleccionada no pertenece al plan de cuidados activo del residente.');
                    }
                    abort(422, 'Intervención de cuidado no válida o no activa.');
                }
                $codIntervencion = $intervencion->cod_intervencion;
                EjecucionCuidado::create(['cod_intervencion' => $codIntervencion, 'cod_ejecucion' => 'EJC_'.strtoupper(Str::random(10)), 'cod_residente' => $entrada['codResidente'], 'cod_personal' => $personal->cod_personal, 'cod_jornada' => $jornada->cod_jornada, 'fecha_hora_programada' => now(), 'fecha_hora_ejecucion' => now(), 'resultado' => $entrada['resultado'] ?: 'REALIZADA', 'estado' => 'REALIZADA', 'observacion' => trim(($entrada['subtipo'] ? $entrada['subtipo'].': ' : '').(($entrada['observacion'] ?: $entrada['motivo']) ?: 'Cuidado firmado en turno.'))]);
            }
            if ($entrada['dolor'] !== null) {
                ValoracionDolor::create(['cod_valoracion_dolor' => 'VDL_'.strtoupper(Str::random(10)), 'cod_residente' => $entrada['codResidente'], 'cod_personal' => $personal->cod_personal, 'fecha_hora' => now(), 'intensidad' => (int) $entrada['dolor'], 'ubicacion' => null, 'tipo_dolor' => null, 'estado' => 'ACTIVA', 'observacion' => null]);
            }
            if ($entrada['cambioBasal'] === 'PEOR' || $tipoNorm === 'ALIMENTACION' && $entrada['porcentaje'] !== null && $entrada['porcentaje'] < 50) {
                Alerta::create(['cod_alerta' => 'ALE_'.strtoupper(Str::random(10)), 'cod_residente' => $entrada['codResidente'], 'cod_personal_responsable' => $personal->cod_personal, 'origen' => 'CUIDADO', 'tipo' => $entrada['cambioBasal'] === 'PEOR' ? 'CAMBIO RESPECTO AL ESTADO BASAL' : 'BAJA INGESTA', 'prioridad' => 'MEDIA', 'titulo' => 'Alerta clínica generada desde cuidado', 'descripcion' => $entrada['motivo'] ?: 'Alerta automática por registro de cuidado.', 'fecha_hora' => now(), 'generacion' => 'AUTOMATICA', 'estado' => 'ABIERTA']);
            }
        });
    }
}
