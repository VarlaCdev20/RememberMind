<?php

namespace App\Modulos\Medicacion\Aplicacion\Consultas;

use App\Models\AdministracionMedicacion;
use App\Models\Prescripcion;
use App\Models\User;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use App\Modulos\Medicacion\Aplicacion\Contratos\ImagenesMedicamentos;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

final class ConsultarAdministracionMedicacion
{
    public function autorizar(User $usuario, string $residente, string $permiso): void
    {
        $usuario = $usuario->fresh();
        abort_unless($usuario?->estado === 'ACTIVO' && $usuario->can($permiso), 403);
        app(TurnoEnfermeriaService::class)->autorizarConsultaResidente($residente, $usuario);
    }

    public function prescripcion(User $usuario, string $residente, string $codigo): array
    {
        $this->autorizar($usuario, $residente, 'prescripciones.ver');
        $this->autorizar($usuario, $residente, 'horarios_prescripcion.ver');

        return $this->orden(Prescripcion::with(['medicamento', 'personal', 'horarios'])->where('cod_residente', $residente)->findOrFail($codigo));
    }

    public function orden(Prescripcion $orden): array
    {
        $medicamento = $orden->medicamento;

        return [
            'codigo' => $orden->getKey(), 'medicamento' => $medicamento?->nombre_comercial ?: $medicamento?->nombre_generico,
            'presentacion' => trim(($medicamento?->concentracion ?? '').' · '.($medicamento?->forma_farmaceutica ?? ''), ' ·'),
            'imagen' => $medicamento ? app(ImagenesMedicamentos::class)->paraCodigo($medicamento->getKey()) : null,
            'dosis' => (string) $orden->dosis, 'unidad' => $orden->unidad_dosis, 'via' => $orden->via_administracion,
            'frecuencia' => $orden->frecuencia, 'indicacion' => $orden->indicacion, 'estado' => $orden->estado,
            'inicio' => $orden->fecha_hora_prescripcion?->format('d/m/Y H:i'), 'fin' => $orden->fecha_hora_suspension?->format('d/m/Y H:i'),
            'prescriptor' => trim(($orden->personal?->nombres ?? '').' '.($orden->personal?->apellido_paterno ?? '').' '.($orden->personal?->apellido_materno ?? '')),
            'horarios' => $orden->horarios->map(fn ($h) => ['hora' => Carbon::parse($h->hora_programada)->format('H:i'), 'dosis' => $h->dosis_programada, 'dias' => $h->dias_semana, 'estado' => $h->estado])->all(),
        ];
    }

    public function resultados(User $usuario, string $residente, array $filtros, int $pagina = 1, bool $evolucion = false): array
    {
        $this->autorizar($usuario, $residente, 'administraciones_medicacion.ver');
        $datos = Validator::make($filtros, [
            'periodo' => 'required|in:7,30,90,personalizado', 'desde' => 'nullable|date_format:Y-m-d', 'hasta' => 'nullable|date_format:Y-m-d',
            'resultado' => 'required|in:TODAS,ADMINISTRADA,OMITIDA', 'agrupacion' => 'required|in:dia,semana,mes', 'prescripcion' => 'nullable|string',
        ])->validate();
        if ($datos['periodo'] === 'personalizado') {
            Validator::make($datos, ['desde' => 'required|date_format:Y-m-d', 'hasta' => 'required|date_format:Y-m-d|after_or_equal:desde|before_or_equal:today'])->validate();
            $desde = Carbon::parse($datos['desde'])->startOfDay();
            $hasta = Carbon::parse($datos['hasta'])->endOfDay();
            Validator::make(['rango' => $desde->diffInDays($hasta)], ['rango' => 'numeric|max:366'])->validate();
        } else {
            $desde = today()->subDays((int) $datos['periodo'] - 1);
            $hasta = today()->endOfDay();
        }
        $query = AdministracionMedicacion::query()->where('cod_residente', $residente)->whereBetween('fecha_hora_programada', [$desde, $hasta])
            ->when(filled($datos['prescripcion'] ?? null), fn ($q) => $q->where('cod_prescripcion', $datos['prescripcion']))
            ->when($datos['resultado'] !== 'TODAS', fn ($q) => $q->where('resultado', $datos['resultado']));
        if ($evolucion) {
            // Agregación portable en SQL; semanas/meses se agrupan desde los días, sin un denominador inventado.
            $diarios = (clone $query)->where(function ($q) {
                $q->where(fn ($q) => $q->where('resultado', 'ADMINISTRADA')->whereIn('estado', ['REGISTRADA', 'ADMINISTRADA']))
                    ->orWhere(fn ($q) => $q->where('resultado', 'OMITIDA')->whereIn('estado', ['REGISTRADA', 'OMITIDA']));
            })->selectRaw('DATE(fecha_hora_programada) as fecha, resultado, COUNT(*) as cantidad')
                ->whereIn('resultado', ['ADMINISTRADA', 'OMITIDA'])->groupByRaw('DATE(fecha_hora_programada), resultado')->orderBy('fecha')->get();
            $series = [];
            foreach ($diarios as $fila) {
                $fecha = Carbon::parse($fila->fecha);
                $clave = match ($datos['agrupacion']) {
                    'semana' => $fecha->startOfWeek()->toDateString(), 'mes' => $fecha->startOfMonth()->toDateString(), default => $fecha->toDateString()
                };
                $series[$clave] ??= ['fecha' => $clave, 'administradas' => 0, 'omitidas' => 0];
                $series[$clave][$fila->resultado === 'ADMINISTRADA' ? 'administradas' : 'omitidas'] += (int) $fila->cantidad;
            }
            $series = array_values($series);

            return ['series' => $series, 'administradas' => array_sum(array_column($series, 'administradas')),
                'omitidas' => array_sum(array_column($series, 'omitidas')), 'desde' => $desde->toDateString(), 'hasta' => $hasta->toDateString(),
                'agrupacion' => $datos['agrupacion'], 'prescripcion' => $datos['prescripcion'] ?? null];
        }
        $contadores = (clone $query)->reorder()->selectRaw('resultado, COUNT(*) as cantidad')->groupBy('resultado')->pluck('cantidad', 'resultado')->map(fn ($n) => (int) $n)->all();
        $resultados = $query->with(['prescripcion.medicamento', 'personal'])->orderByDesc('fecha_hora_programada')->orderByDesc('cod_administracion')
            ->paginate(15, ['*'], 'historial', max(1, $pagina));

        return ['registros' => $resultados->getCollection()->map(fn ($registro) => $this->registro($registro))->all(),
            'pagina' => $resultados->currentPage(), 'paginas' => $resultados->lastPage(), 'total' => $resultados->total(), 'contadores' => $contadores];
    }

    public function detalle(User $usuario, string $residente, string $codigo): array
    {
        $this->autorizar($usuario, $residente, 'administraciones_medicacion.ver');

        return $this->registro(AdministracionMedicacion::with(['prescripcion.medicamento', 'personal'])->where('cod_residente', $residente)->findOrFail($codigo));
    }

    private function registro(AdministracionMedicacion $registro): array
    {
        $orden = $registro->prescripcion;

        return ['codigo' => $registro->getKey(), 'fecha' => $registro->fecha_hora_programada?->format('d/m/Y'),
            'programada' => $registro->fecha_hora_programada?->format('d/m/Y H:i'), 'hora' => $registro->fecha_hora_programada?->format('H:i'),
            'real' => $registro->fecha_hora_administracion?->format('d/m/Y H:i'),
            'medicamento' => $orden?->medicamento?->nombre_comercial ?: $orden?->medicamento?->nombre_generico,
            'dosis' => $registro->dosis_administrada, 'unidad' => $orden?->unidad_dosis, 'via' => $orden?->via_administracion,
            'resultado' => $registro->resultado, 'estado' => $registro->estado, 'motivo' => $registro->motivo_omision,
            'efecto' => $registro->efecto_observado, 'reaccion' => $registro->reaccion_adversa, 'observacion' => $registro->observacion,
            'profesional' => trim(($registro->personal?->nombres ?? '').' '.($registro->personal?->apellido_paterno ?? '').' '.($registro->personal?->apellido_materno ?? ''))];
    }
}
