<?php

namespace App\Modulos\Reportes\Aplicacion\Consultas;

use App\Models\AdministracionMedicacion;
use App\Models\Alerta;
use App\Models\Atencion;
use App\Models\ControlCognitivo;
use App\Models\CuracionHerida;
use App\Models\DispositivoClinico;
use App\Models\EjecucionCuidado;
use App\Models\Herida;
use App\Models\Incidente;
use App\Models\PaseTurno;
use App\Models\RegistroConductual;
use App\Models\RegistroEliminacion;
use App\Models\RegistroHidratacion;
use App\Models\RegistroIngesta;
use App\Models\RegistroMovilidad;
use App\Models\RegistroSueno;
use App\Models\SignoVital;
use App\Models\User;
use App\Models\ValoracionDolor;
use App\Modulos\Cuidados\Aplicacion\Acciones\PaseTurnoService;
use App\Modulos\Cuidados\Aplicacion\Consultas\ConsultaCentralCuidados;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class ConsultarReporteEnfermeria
{
    public const TIPOS = ['TODOS' => 'Todos los registros', 'CUIDADOS' => 'Cuidados', 'CONTROLES' => 'Controles de signos vitales',
        'MEDICACION' => 'Administración de medicación', 'INCIDENTES' => 'Incidentes', 'ALERTAS' => 'Alertas',
        'DOLOR' => 'Dolor', 'INGESTA' => 'Alimentación', 'HIDRATACION' => 'Hidratación', 'ELIMINACION' => 'Eliminación',
        'MOVILIDAD' => 'Movilidad', 'SUENO' => 'Sueño', 'CONDUCTA' => 'Conducta', 'COGNICION' => 'Observación cognitiva',
        'HERIDAS' => 'Heridas y curaciones', 'CURACIONES' => 'Curaciones', 'DISPOSITIVOS' => 'Dispositivos clínicos',
        'SEGUIMIENTO' => 'Seguimiento diario', 'PASES' => 'Pase de turno'];

    public const PERMISOS = ['CUIDADOS' => 'ejecuciones_cuidado.ver', 'CONTROLES' => 'signos_vitales.ver',
        'MEDICACION' => 'administraciones_medicacion.ver', 'INCIDENTES' => 'incidentes.ver', 'ALERTAS' => 'alertas.ver',
        'DOLOR' => 'valoraciones_dolor.ver', 'INGESTA' => 'registros_ingesta.ver', 'HIDRATACION' => 'registros_hidratacion.ver',
        'ELIMINACION' => 'registros_eliminacion.ver', 'MOVILIDAD' => 'registros_movilidad.ver', 'SUENO' => 'registros_sueno.ver',
        'CONDUCTA' => 'registros_conductuales.ver', 'COGNICION' => 'controles_cognitivos.ver', 'HERIDAS' => 'heridas.ver', 'CURACIONES' => 'curaciones_herida.ver',
        'DISPOSITIVOS' => 'dispositivos_clinicos.ver', 'SEGUIMIENTO' => 'atenciones.ver', 'PASES' => 'pases_turno.ver'];

    public static function tipoDeArea(string $area): string
    {
        return ['signos' => 'CONTROLES', 'alimentacion' => 'INGESTA', 'medicacion' => 'MEDICACION', 'heridas' => 'HERIDAS'][$area]
            ?? (isset(self::PERMISOS[strtoupper($area)]) ? strtoupper($area) : 'TODOS');
    }

    public function autorizar(?User $usuario, bool $exportar = false): void
    {
        abort_unless($usuario && $usuario->estado === 'ACTIVO' && $usuario->can('enfermeria.ver_dashboard'), 403);
        abort_unless($usuario->hasRole('SUPERADMINISTRADOR') || ($usuario->personal && in_array($usuario->personal->estado, ['ACTIVO', 'ACTIVA'], true)), 403);
        if ($exportar) {
            abort_unless($usuario->can('enfermeria.ver_ficha_paciente'), 403);
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
        $permisos = $datos['tipo'] === 'TODOS' ? self::PERMISOS : [$datos['tipo'] => self::PERMISOS[$datos['tipo']]];
        foreach ($permisos as $permiso) {
            abort_unless($usuario->can($permiso), 403);
        }
        if (in_array($datos['tipo'], ['HERIDAS', 'CURACIONES', 'TODOS'], true)) {
            abort_unless($usuario->can('heridas.ver'), 403);
            abort_unless($usuario->can('curaciones_herida.ver'), 403);
        }
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
        $agregar = function (string $tipo, string $etiqueta, $modelo, string $fecha, array $relaciones, callable $detalle, callable $estado) use (&$filas, $ids, $inicio, $fin, $filtros, $usuario): void {
            if ($filtros['tipo'] !== 'TODOS' && $filtros['tipo'] !== $tipo && ! ($filtros['tipo'] === 'HERIDAS' && $tipo === 'CURACIONES')) {
                return;
            }
            $consulta = $modelo::query()->with($relaciones);
            if ($tipo === 'PASES') {
                $consulta = app(PaseTurnoService::class)->acotarConsultaPases($consulta, $usuario);
            }
            if ($tipo === 'SEGUIMIENTO') {
                $consulta->where('tipo_atencion', 'SEGUIMIENTO_DIARIO');
            }
            if ($tipo === 'CURACIONES') {
                $consulta->whereHas('herida', fn ($q) => $q->whereIn('cod_residente', $ids)->whereNotIn('estado', ['ANULADA', 'ANULADO']));
            } else {
                $consulta->whereIn('cod_residente', $ids)->whereNotIn('estado', ['ANULADA', 'ANULADO']);
            }
            $registros = $consulta->whereRaw($fecha.' >= ?', [$inicio])->whereRaw($fecha.' < ?', [$fin])
                ->orderByRaw($fecha)->orderBy((new $modelo)->getKeyName())->get();
            foreach ($registros as $registro) {
                $momento = match ($tipo) {
                    'CUIDADOS' => $registro->fecha_hora_ejecucion ?? $registro->fecha_hora_programada,
                    'MEDICACION' => $registro->fecha_hora_administracion ?? $registro->fecha_hora_programada,
                    'SUENO' => $registro->fecha,
                    'HERIDAS' => $registro->fecha_hora_identificacion,
                    'DISPOSITIVOS' => $registro->fecha_retiro ?? $registro->fecha_colocacion,
                    default => $registro->fecha_hora,
                };
                $filas->push(['tipo' => $etiqueta, 'categoria' => $tipo, 'fecha' => $momento,
                    'fecha_tipo' => match ($tipo) {
                        'CUIDADOS' => $registro->fecha_hora_ejecucion ? 'Ejecución registrada' : 'Programada',
                        'MEDICACION' => $registro->fecha_hora_administracion ? 'Administración registrada' : 'Programada',
                        'SUENO' => 'Fecha de descanso · hora no registrada',
                        'DISPOSITIVOS' => $registro->fecha_retiro ? 'Retiro registrado' : 'Colocación registrada',
                        default => 'Registro',
                    },
                    'residente' => self::nombre($tipo === 'CURACIONES' ? $registro->herida->residente : $registro->residente), 'profesional' => self::nombre(match ($tipo) {
                        'ALERTAS' => $registro->responsable, 'PASES' => $registro->personalSaliente, default => $registro->personal,
                    }),
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

            return (implode(' · ', $partes) ?: 'Sin valores numéricos registrados').($r->observacion ? ' · Observación: '.$r->observacion : '');
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
        $agregar('SEGUIMIENTO', 'Seguimiento diario', Atencion::class, 'fecha_hora', ['residente', 'personal'],
            fn ($r) => trim(($r->motivo ? $r->motivo.' · ' : '').$r->observacion) ?: 'Sin nota consignada', fn ($r) => $r->estado);
        $agregar('DISPOSITIVOS', 'Dispositivo clínico', DispositivoClinico::class, 'COALESCE(fecha_retiro, fecha_colocacion)', ['residente', 'personal'],
            fn ($r) => self::legible($r->tipo).' · '.($r->ubicacion ?: 'Ubicación no consignada')
                .($r->fecha_colocacion ? ' · Colocación: '.$r->fecha_colocacion->format('d/m/Y H:i') : '')
                .($r->fecha_retiro ? ' · Retiro: '.$r->fecha_retiro->format('d/m/Y H:i') : '')
                .($r->descripcion ? ' · '.$r->descripcion : '').($r->observacion ? ' · '.$r->observacion : ''), fn ($r) => $r->estado);
        $agregar('PASES', 'Pase de turno', PaseTurno::class, 'fecha_hora', ['residente', 'personalSaliente', 'personalEntrante'],
            fn ($r) => 'Estado general: '.self::legible($r->estado_general).' · Resumen: '.$r->resumen
                .($r->pendientes ? ' · Pendientes: '.$r->pendientes : '').($r->vigilancia ? ' · Vigilancia: '.$r->vigilancia : '')
                .($r->recomendacion ? ' · Recomendación: '.$r->recomendacion : '')
                .' · Recibe: '.self::nombre($r->personalEntrante)
                .($r->fecha_hora_recepcion ? ' · Recibido: '.$r->fecha_hora_recepcion->format('d/m/Y H:i') : '')
                .($r->observacion_recepcion ? ' · Recepción: '.$r->observacion_recepcion : ''), fn ($r) => $r->estado);
        $fuentes = [
            'DOLOR' => [ValoracionDolor::class, 'dolor', 'fecha_hora'],
            'INGESTA' => [RegistroIngesta::class, 'ingesta', 'fecha_hora'],
            'HIDRATACION' => [RegistroHidratacion::class, 'hidratacion', 'fecha_hora'],
            'ELIMINACION' => [RegistroEliminacion::class, 'eliminacion', 'fecha_hora'],
            'MOVILIDAD' => [RegistroMovilidad::class, 'movilidad', 'fecha_hora'],
            'SUENO' => [RegistroSueno::class, 'sueno', 'fecha'],
            'CONDUCTA' => [RegistroConductual::class, 'conducta', 'fecha_hora'],
            'COGNICION' => [ControlCognitivo::class, 'cognicion', 'fecha_hora'],
            'HERIDAS' => [Herida::class, 'heridas', 'fecha_hora_identificacion'],
            'CURACIONES' => [CuracionHerida::class, 'curaciones', 'fecha_hora'],
        ];
        foreach ($fuentes as $tipo => [$modelo, $origen, $fecha]) {
            $agregar($tipo, $tipo === 'HERIDAS' ? 'Herida identificada' : self::TIPOS[$tipo], $modelo, $fecha, $tipo === 'CURACIONES' ? ['herida.residente', 'personal'] : ['residente', 'personal'],
                fn ($r) => collect(app(ConsultaCentralCuidados::class)->camposPresentacion($origen, $r))
                    ->map(fn ($campo) => $campo['nombre'].': '.$campo['valor'])->implode(' · ') ?: 'Sin detalle consignado',
                fn ($r) => $tipo === 'CURACIONES' ? 'Guardado' : $r->estado);
        }
        $filas = $filas->sortByDesc(fn ($fila) => $fila['fecha']->format('Y-m-d H:i:s.u'))->values();
        $totales = collect(self::TIPOS)->except('TODOS')->when($filtros['tipo'] !== 'TODOS', fn ($c) => $c->only($filtros['tipo'] === 'HERIDAS' ? ['HERIDAS', 'CURACIONES'] : [$filtros['tipo']]))
            ->map(fn ($nombre, $tipo) => ['nombre' => $tipo === 'HERIDAS' ? 'Heridas identificadas' : $nombre, 'cantidad' => $filas->where('categoria', $tipo)->count()]);
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
