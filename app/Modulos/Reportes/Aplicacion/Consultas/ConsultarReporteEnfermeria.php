<?php

namespace App\Modulos\Reportes\Aplicacion\Consultas;

use App\Models\AdministracionMedicacion;
use App\Models\Alerta;
use App\Models\EjecucionCuidado;
use App\Models\Incidente;
use App\Models\SignoVital;
use App\Models\User;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class ConsultarReporteEnfermeria
{
    public const TIPOS = ['TODOS' => 'Todos los registros', 'CUIDADOS' => 'Cuidados', 'CONTROLES' => 'Controles de signos vitales',
        'MEDICACION' => 'Administración de medicación', 'INCIDENTES' => 'Incidentes', 'ALERTAS' => 'Alertas'];

    public function autorizar(?User $usuario, bool $exportar = false): void
    {
        abort_unless($usuario && $usuario->estado === 'ACTIVO' && $usuario->can('enfermeria.ver_dashboard'), 403);
        abort_unless($usuario->hasRole('SUPERADMINISTRADOR') || ($usuario->personal && in_array($usuario->personal->estado, ['ACTIVO', 'ACTIVA'], true)), 403);
        if ($exportar) {
            abort_unless($usuario->can('enfermeria.ver_ficha_paciente'), 403);
        }
        foreach (['ejecuciones_cuidado.ver', 'signos_vitales.ver', 'administraciones_medicacion.ver', 'incidentes.ver', 'alertas.ver'] as $permiso) {
            abort_unless($usuario->can($permiso), 403);
        }
    }

    public function validar(User $usuario, array $entrada): array
    {
        $this->autorizar($usuario);
        $datos = Validator::make($entrada, [
            'desde' => ['required', 'date_format:Y-m-d'],
            'hasta' => ['required', 'date_format:Y-m-d', 'after_or_equal:desde', 'before_or_equal:'.today()->toDateString()],
            'codResidente' => ['nullable', 'string', 'max:20'], 'tipo' => ['required', Rule::in(array_keys(self::TIPOS))],
        ], ['hasta.after_or_equal' => 'La fecha final debe ser igual o posterior a la inicial.', 'hasta.before_or_equal' => 'Selecciona una fecha final hasta hoy.'],
            ['desde' => 'fecha inicial', 'hasta' => 'fecha final', 'codResidente' => 'residente', 'tipo' => 'tipo de registro'])->validate();
        $datos['codResidente'] ??= '';
        if ($datos['codResidente'] !== '') {
            app(TurnoEnfermeriaService::class)->autorizarConsultaResidente($datos['codResidente'], $usuario);
        }

        return $datos;
    }

    public function residentes(User $usuario): Collection
    {
        $this->autorizar($usuario);

        return app(TurnoEnfermeriaService::class)->obtenerPacientesAsignadosQuery($usuario)
            ->orderBy('apellido_paterno')->orderBy('nombres')->get(['cod_residente', 'nombres', 'apellido_paterno', 'apellido_materno']);
    }

    public function consultar(User $usuario, array $entrada): array
    {
        $filtros = $this->validar($usuario, $entrada);
        $residentes = $this->residentes($usuario);
        $ids = $filtros['codResidente'] !== '' ? [$filtros['codResidente']] : $residentes->modelKeys();
        $inicio = CarbonImmutable::parse($filtros['desde'])->startOfDay();
        $fin = CarbonImmutable::parse($filtros['hasta'])->addDay()->startOfDay();
        $filas = collect();
        $agregar = function (string $tipo, string $etiqueta, $modelo, string $fecha, array $relaciones, callable $detalle, callable $estado) use (&$filas, $ids, $inicio, $fin, $filtros): void {
            if ($filtros['tipo'] !== 'TODOS' && $filtros['tipo'] !== $tipo) {
                return;
            }
            $registros = $modelo::query()->with($relaciones)->whereIn('cod_residente', $ids)
                ->whereNotIn('estado', ['ANULADA', 'ANULADO'])->whereRaw($fecha.' >= ?', [$inicio])
                ->whereRaw($fecha.' < ?', [$fin])->orderByRaw($fecha)->orderBy((new $modelo)->getKeyName())->get();
            foreach ($registros as $registro) {
                $momento = match ($tipo) {
                    'CUIDADOS' => $registro->fecha_hora_ejecucion ?? $registro->fecha_hora_programada,
                    'MEDICACION' => $registro->fecha_hora_administracion ?? $registro->fecha_hora_programada,
                    default => $registro->fecha_hora,
                };
                $filas->push(['tipo' => $etiqueta, 'categoria' => $tipo, 'fecha' => $momento,
                    'fecha_tipo' => match ($tipo) {
                        'CUIDADOS' => $registro->fecha_hora_ejecucion ? 'Ejecución registrada' : 'Programada',
                        'MEDICACION' => $registro->fecha_hora_administracion ? 'Administración registrada' : 'Programada',
                        default => 'Registro',
                    },
                    'residente' => self::nombre($registro->residente), 'profesional' => self::nombre($tipo === 'ALERTAS' ? $registro->responsable : $registro->personal),
                    'estado' => self::legible($estado($registro)), 'detalle' => $detalle($registro)]);
            }
        };
        $agregar('CUIDADOS', 'Cuidado', EjecucionCuidado::class, 'COALESCE(fecha_hora_ejecucion, fecha_hora_programada)', ['residente', 'personal', 'intervencion'],
            fn ($r) => ($r->intervencion?->nombre ?? 'Cuidado').($r->observacion ? ': '.$r->observacion : '').($r->motivo_omision ? ' · Motivo: '.$r->motivo_omision : ''), fn ($r) => $r->estado);
        $agregar('CONTROLES', 'Control de signos vitales', SignoVital::class, 'fecha_hora', ['residente', 'personal'], function ($r) {
            $partes = [];
            foreach (['presion_sistolica' => ['Presión sistólica', 'mmHg'], 'presion_diastolica' => ['Presión diastólica', 'mmHg'],
                'frecuencia_cardiaca' => ['Frecuencia cardíaca', 'lpm'], 'frecuencia_respiratoria' => ['Frecuencia respiratoria', 'rpm'],
                'temperatura' => ['Temperatura', '°C'], 'saturacion_oxigeno' => ['Saturación', '%'], 'glucemia' => ['Glucemia', 'mg/dL']] as $campo => [$nombre, $unidad]) {
                if ($r->$campo !== null) {
                    $partes[] = $nombre.': '.$r->$campo.' '.$unidad;
                }
            }

            return implode(' · ', $partes) ?: 'Sin valores numéricos registrados';
        }, fn ($r) => $r->estado);
        $agregar('MEDICACION', 'Medicación', AdministracionMedicacion::class, 'COALESCE(fecha_hora_administracion, fecha_hora_programada)', ['residente', 'personal', 'prescripcion.medicamento'],
            fn ($r) => ($r->prescripcion?->medicamento?->nombre_generico ?? 'Medicamento registrado')
                .($r->dosis_administrada !== null ? ' · Dosis administrada: '.$r->dosis_administrada.' '.($r->prescripcion?->unidad_dosis ?? '(unidad no consignada)') : '')
                .($r->observacion ? ': '.$r->observacion : '').($r->motivo_omision ? ' · Motivo: '.$r->motivo_omision : '')
                .($r->efecto_observado ? ' · Efecto observado: '.$r->efecto_observado : '')
                .($r->reaccion_adversa ? ' · Reacción adversa: '.$r->reaccion_adversa : ''), fn ($r) => $r->resultado);
        $agregar('INCIDENTES', 'Incidente', Incidente::class, 'fecha_hora', ['residente', 'personal'],
            fn ($r) => self::legible($r->tipo_incidente).': '.$r->descripcion, fn ($r) => $r->estado);
        $agregar('ALERTAS', 'Alerta', Alerta::class, 'fecha_hora', ['residente', 'responsable'],
            fn ($r) => $r->titulo.($r->descripcion ? ': '.$r->descripcion : ''), fn ($r) => $r->estado);
        $filas = $filas->sortByDesc(fn ($fila) => $fila['fecha']->format('Y-m-d H:i:s.u'))->values();
        $totales = collect(self::TIPOS)->except('TODOS')->map(fn ($nombre, $tipo) => ['nombre' => $nombre, 'cantidad' => $filas->where('categoria', $tipo)->count()]);
        $dias = $filas->groupBy(fn ($fila) => $fila['fecha']->format('Y-m-d'))->sortKeys()
            ->map(fn ($registros, $dia) => ['fecha' => $dia, 'cantidad' => $registros->count()])->values();
        $seleccion = $residentes->firstWhere('cod_residente', $filtros['codResidente']);

        return ['filtros' => $filtros, 'residentes' => $residentes, 'alcance' => $seleccion ? self::nombre($seleccion) : 'Todos los residentes autorizados',
            'filas' => $filas, 'totales' => $totales, 'dias' => $dias, 'generado' => now(), 'autor' => $usuario->name];
    }

    private static function nombre($persona): string
    {
        return $persona ? trim(implode(' ', [$persona->nombres, $persona->apellido_paterno, $persona->apellido_materno])) : 'No consignado';
    }

    private static function legible(?string $valor): string
    {
        return $valor ? mb_convert_case(str_replace('_', ' ', $valor), MB_CASE_TITLE, 'UTF-8') : 'No consignado';
    }
}
