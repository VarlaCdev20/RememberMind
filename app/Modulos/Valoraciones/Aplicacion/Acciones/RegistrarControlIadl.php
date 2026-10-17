<?php

namespace App\Modulos\Valoraciones\Aplicacion\Acciones;

use App\Models\ControlFuncionalIadl;
use App\Models\Residente;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** Escritura append-only con control de autoría, contexto y coherencia IADL. */
final class RegistrarControlIadl
{
    public function ejecutar(User $usuario, Residente $residente, array $entrada): ControlFuncionalIadl
    {
        $rectifica = !empty($entrada['cod_control_anterior']);
        abort_unless(
            $usuario->can($rectifica ? 'controles_funcionales_iadl.rectificar' : 'controles_funcionales_iadl.crear'),
            403
        );
        abort_if($usuario->hasRole('SUPERADMINISTRADOR'), 403, 'Superadministración solo consulta IADL.');
        $personal = $usuario->personal;
        abort_unless($personal && $personal->estado === 'ACTIVO', 403, 'Se requiere personal activo vinculado.');

        $datos = Validator::make($entrada, [
            'fecha_hora' => ['required', 'date', 'before_or_equal:now'],
            'actividad' => ['required', Rule::in(array_keys(ControlFuncionalIadl::ACTIVIDADES))],
            'oportunidad' => ['required', Rule::in(ControlFuncionalIadl::OPORTUNIDADES)],
            'desempeno' => ['required', Rule::in(ControlFuncionalIadl::DESEMPENOS)],
            'tipo_ayuda' => ['required', Rule::in(ControlFuncionalIadl::AYUDAS)],
            'causa_ayuda' => ['required', Rule::in(ControlFuncionalIadl::CAUSAS)],
            'nivel_previo' => ['required', Rule::in(ControlFuncionalIadl::NIVELES_PREVIOS)],
            'fuente' => ['required', Rule::in(ControlFuncionalIadl::FUENTES)],
            'observacion' => ['nullable', 'string', 'max:2000'],
            'cod_control_anterior' => ['nullable', 'string', 'max:20'],
            'cod_atencion' => ['nullable', 'string', 'max:20', Rule::exists('atenciones', 'cod_atencion')
                ->where('cod_residente', $residente->cod_residente)],
        ])->after(static function ($validador) use ($entrada): void {
            $o = $entrada['oportunidad'] ?? null;
            $d = $entrada['desempeno'] ?? null;
            $tipo = $entrada['tipo_ayuda'] ?? null;
            $causa = $entrada['causa_ayuda'] ?? null;
            $fuente = $entrada['fuente'] ?? null;
            $sinOportunidad = in_array($o, ['SIN_OPORTUNIDAD', 'NO_HABITUAL'], true);
            if ($sinOportunidad && ($d !== 'NO_EVALUABLE' || $tipo !== 'NO_APLICA' || $causa !== 'NO_APLICA')) {
                $validador->errors()->add('desempeno',
                    'Sin oportunidad o actividad no habitual no permite inferir incapacidad ni independencia.');
            }
            if (!$sinOportunidad && $d === 'NO_EVALUABLE') {
                $validador->errors()->add('desempeno', 'Registrar por qué la tarea no pudo evaluarse como sin oportunidad.');
            }
            if ($d === 'INDEPENDIENTE' && ($tipo !== 'NINGUNA' || $causa !== 'NINGUNA')) {
                $validador->errors()->add('tipo_ayuda', 'Una tarea independiente no puede tener ayuda atribuida.');
            }
            if ($d === 'CON_APOYO' && (
                !in_array($tipo, ['RECORDATORIO','SUPERVISION','SUSTITUCION'], true)
                || !in_array($causa, ['COGNITIVA','FISICA','SENSORIAL','INSTITUCIONAL','MIXTA','NO_DETERMINADA'], true)
                || trim((string) ($entrada['observacion'] ?? '')) === ''
            )) {
                $validador->errors()->add('causa_ayuda',
                    'Documentar tipo, causa y descripción de toda ayuda, sin presuponer deterioro cognitivo.');
            }
            if ($o === 'OBSERVADA' && $fuente !== 'OBSERVACION_DIRECTA') {
                $validador->errors()->add('fuente', 'La observación directa debe tener fuente profesional directa.');
            }
            if ($o === 'REFERIDA' && $fuente === 'OBSERVACION_DIRECTA') {
                $validador->errors()->add('fuente', 'Una referencia indirecta no es observación directa.');
            }
        })->validate();

        return DB::transaction(function () use ($usuario, $residente, $personal, $datos): ControlFuncionalIadl {
            $actual = Residente::query()
                ->where('cod_residente', $residente->cod_residente)
                ->lockForUpdate()->firstOrFail();
            abort_unless($actual->estado === 'ADMITIDO', 422, 'Solo se registran IADL de residentes admitidos.');

            $previo = null;
            if (!empty($datos['cod_control_anterior'])) {
                $previo = ControlFuncionalIadl::query()
                    ->where('cod_residente', $actual->cod_residente)
                    ->where('cod_control_iadl', $datos['cod_control_anterior'])
                    ->lockForUpdate()->firstOrFail();

                if ($previo->actividad !== $datos['actividad']
                    || $previo->fecha_hora->format('Y-m-d H:i') !==
                        Carbon::parse($datos['fecha_hora'])->format('Y-m-d H:i')
                    || ControlFuncionalIadl::query()
                        ->where('cod_control_anterior', $previo->cod_control_iadl)->exists()) {
                    throw ValidationException::withMessages([
                        'cod_control_anterior' => 'La rectificación debe ser del mismo residente, tarea y momento, y no puede ramificarse.',
                    ]);
                }
            }

            return ControlFuncionalIadl::query()->create([
                'cod_residente' => $actual->cod_residente,
                'cod_personal' => $personal->cod_personal,
                'cod_usuario_registro' => $usuario->cod_usuario,
                'cod_atencion' => $datos['cod_atencion'] ?? null,
                'cod_control_anterior' => $previo?->cod_control_iadl,
                'fecha_hora' => Carbon::parse($datos['fecha_hora']),
                'actividad' => $datos['actividad'],
                'oportunidad' => $datos['oportunidad'],
                'desempeno' => $datos['desempeno'],
                'tipo_ayuda' => $datos['tipo_ayuda'],
                'causa_ayuda' => $datos['causa_ayuda'],
                'nivel_previo' => $datos['nivel_previo'],
                'fuente' => $datos['fuente'],
                'observacion' => $datos['observacion'] ?? null,
                'estado' => $previo ? 'RECTIFICACION' : 'REGISTRADO',
                'registrado_en' => now(),
            ]);
        });
    }
}
