<?php

namespace App\Backend\Modulos\Clinica\Servicios;

use App\Models\Alerta;
use App\Models\Incidente;
use App\Models\Residente;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class EventosClinicosService
{
    /**
     * Normaliza exclusivamente incidentes persistidos. No completa campos clínicos
     * ausentes ni agrega eventos de demostración.
     */
    public function obtenerEventos(Residente $residente): Collection
    {
        $incidentes = Incidente::query()
            ->where('cod_residente', $residente->cod_residente)
            ->with('registrador.usuario')
            ->latest('fecha_hora')
            ->get()
            ->map(fn (Incidente $incidente) => $this->normalizar($incidente, $residente));

        $codigosIncidente = $incidentes->pluck('id')->all();
        $alertas = Alerta::query()
            ->where('cod_residente', $residente->cod_residente)
            ->with(['responsable.usuario', 'eventos.usuario'])
            ->latest('fecha_hora')
            ->get()
            ->reject(fn (Alerta $alerta) => $alerta->modulo === 'INCIDENTES'
                && in_array($alerta->cod_registro, $codigosIncidente, true))
            ->map(fn (Alerta $alerta) => $this->normalizarAlerta($alerta, $residente));

        return $incidentes
            ->concat($alertas)
            ->sortByDesc('fecha_hora_carbon')
            ->values();
    }

    private function normalizarAlerta(Alerta $alerta, Residente $residente): array
    {
        $tipo = $this->resolverTipo($alerta->tipo);
        $estado = $this->resolverEstado($alerta->estado);
        $fechaHora = $alerta->fecha_hora;
        $eventoCierre = $alerta->eventos
            ->where('tipo_evento', 'CIERRE')
            ->sortByDesc('fecha_hora')
            ->first();
        $profesional = $alerta->responsable?->usuario?->name
            ?? $eventoCierre?->usuario?->name
            ?? 'No registrado';
        $habitacion = $residente->cama?->habitacion?->nombre
            ?? $residente->ocupacionActiva?->cama?->habitacion?->nombre;

        return [
            'id' => 'alerta-'.$alerta->cod_alerta,
            'fecha_hora_carbon' => $fechaHora,
            'fecha' => $fechaHora?->translatedFormat('d M Y') ?? 'Fecha no registrada',
            'hora' => $fechaHora?->format('H:i') ?? '',
            'tipo' => $tipo,
            'tipo_label' => $this->etiquetaTipo($tipo),
            'titulo' => $alerta->titulo ?: 'Alerta clínica',
            'icono' => $this->iconoTipo($tipo),
            'color_dot' => $this->colorPunto($estado),
            'descripcion_resumida' => $alerta->descripcion ?: 'Sin descripción registrada.',
            'descripcion_completa' => $alerta->descripcion ?: 'Sin descripción registrada.',
            'profesional_nombre' => $profesional,
            'profesional_rol' => $alerta->responsable?->profesion ?: 'No registrado',
            'estado' => $estado,
            'estado_badge' => Str::headline(Str::lower($estado)),
            'estado_color' => $this->colorEstado($estado),
            'lugar' => 'No registrado',
            'piso' => $habitacion ? ($residente->cama?->habitacion?->piso ?? 'No registrado') : 'No registrado',
            'habitacion' => $habitacion ?: 'No registrada',
            'severidad' => $alerta->prioridad ?: 'No registrada',
            'conclusion_inicial' => 'Sin conclusión registrada.',
            'proxima_evaluacion_fecha' => $alerta->fecha_hora_limite?->format('d/m/Y H:i') ?? 'No registrada',
            'proxima_evaluacion_responsable' => $profesional,
            'plan_seguimiento' => [],
            'valoracion' => [
                'estado_general' => 'No registrado',
                'nivel_conciencia' => 'No registrado',
                'dolor_eva' => null,
                'signos_vitales' => 'No registrados en esta alerta',
                'movilidad' => 'No registrada',
                'lesiones_encontradas' => 'No registradas',
                'evaluacion_neuro' => 'No registrada',
            ],
            'intervenciones' => $alerta->eventos
                ->whereIn('tipo_evento', ['ATENCION', 'INTERVENCION', 'SEGUIMIENTO'])
                ->map(fn ($evento) => [
                    'hora' => $evento->fecha_hora?->format('H:i') ?? '',
                    'accion' => $evento->descripcion,
                    'profesional' => $evento->usuario?->name ?? 'No registrado',
                ])->values()->all(),
            'seguimiento' => [
                'estado_actual' => Str::headline(Str::lower($estado)),
                'proxima_reevaluacion' => $alerta->fecha_hora_limite?->format('d/m/Y H:i') ?? 'No registrada',
                'responsable' => $profesional,
                'acciones_pendientes' => [],
            ],
            'documentos' => [],
            'trazabilidad' => $alerta->eventos->sortBy('fecha_hora')->map(fn ($evento) => [
                'fecha_hora' => $evento->fecha_hora?->format('d/m/Y H:i') ?? 'Fecha no registrada',
                'usuario' => $evento->usuario?->name ?? 'No registrado',
                'accion' => $evento->descripcion,
            ])->values()->all(),
            'resolucion' => $estado === 'RESUELTO' ? [
                'fecha' => $eventoCierre?->fecha_hora?->format('d/m/Y H:i') ?? '',
                'profesional' => $eventoCierre?->usuario?->name ?? $profesional,
                'descripcion' => $eventoCierre?->descripcion ?: 'Sin detalle de cierre registrado.',
            ] : null,
        ];
    }

    private function normalizar(Incidente $incidente, Residente $residente): array
    {
        $tipo = $this->resolverTipo($incidente->tipo_incidente);
        $estado = $this->resolverEstado($incidente->estado);
        $fechaHora = $incidente->fecha_hora;
        $profesional = $incidente->registrador?->usuario?->name
            ?? $incidente->registrador?->nombre_completo
            ?? 'No registrado';
        $habitacion = $residente->cama?->habitacion?->nombre
            ?? $residente->ocupacionActiva?->cama?->habitacion?->nombre;

        return [
            'id' => $incidente->cod_incidente,
            'fecha_hora_carbon' => $fechaHora,
            'fecha' => $fechaHora?->translatedFormat('d M Y') ?? 'Fecha no registrada',
            'hora' => $fechaHora?->format('H:i') ?? '',
            'tipo' => $tipo,
            'tipo_label' => $this->etiquetaTipo($tipo),
            'titulo' => $incidente->tipo_incidente ?: 'INCIDENTE',
            'icono' => $this->iconoTipo($tipo),
            'color_dot' => $this->colorPunto($estado),
            'descripcion_resumida' => $incidente->descripcion ?: 'Sin descripción registrada.',
            'descripcion_completa' => $incidente->descripcion ?: 'Sin descripción registrada.',
            'profesional_nombre' => $profesional,
            'profesional_rol' => $incidente->registrador?->profesion ?: 'No registrado',
            'estado' => $estado,
            'estado_badge' => Str::headline(Str::lower($estado)),
            'estado_color' => $this->colorEstado($estado),
            'lugar' => $incidente->lugar ?: 'No registrado',
            'piso' => $habitacion ? ($residente->cama?->habitacion?->piso ?? 'No registrado') : 'No registrado',
            'habitacion' => $habitacion ?: 'No registrada',
            'severidad' => $incidente->gravedad ?: 'No registrada',
            'conclusion_inicial' => $incidente->observacion ?: 'Sin conclusión registrada.',
            'proxima_evaluacion_fecha' => 'No registrada',
            'proxima_evaluacion_responsable' => 'No registrado',
            'plan_seguimiento' => [],
            'valoracion' => [
                'estado_general' => 'No registrado',
                'nivel_conciencia' => 'No registrado',
                'dolor_eva' => null,
                'signos_vitales' => 'No registrados en este incidente',
                'movilidad' => 'No registrada',
                'lesiones_encontradas' => 'No registradas',
                'evaluacion_neuro' => 'No registrada',
            ],
            'intervenciones' => $incidente->medida_inmediata ? [[
                'hora' => $fechaHora?->format('H:i') ?? '',
                'accion' => $incidente->medida_inmediata,
                'profesional' => $profesional,
            ]] : [],
            'seguimiento' => [
                'estado_actual' => Str::headline(Str::lower($estado)),
                'proxima_reevaluacion' => 'No registrada',
                'responsable' => 'No registrado',
                'acciones_pendientes' => [],
            ],
            'documentos' => [],
            'trazabilidad' => [[
                'fecha_hora' => $fechaHora?->format('d/m/Y H:i') ?? 'Fecha no registrada',
                'usuario' => $profesional,
                'accion' => 'Registro del incidente clínico.',
            ]],
            'resolucion' => $estado === 'RESUELTO' ? [
                'fecha' => '',
                'profesional' => $profesional,
                'descripcion' => $incidente->observacion ?: 'Sin detalle de cierre registrado.',
            ] : null,
        ];
    }

    private function resolverTipo(?string $tipo): string
    {
        $normalizado = Str::upper(Str::ascii((string) $tipo));

        return match (true) {
            str_contains($normalizado, 'CAID') => 'CAIDA',
            str_contains($normalizado, 'LESION'), str_contains($normalizado, 'HERIDA') => 'LESION',
            str_contains($normalizado, 'COMPLICAC') => 'COMPLICACION',
            $normalizado === 'OTRO' => 'OTRO',
            default => 'INCIDENTE',
        };
    }

    private function resolverEstado(?string $estado): string
    {
        $normalizado = Str::upper((string) $estado);

        return match ($normalizado) {
            'CERRADO', 'CERRADA', 'RESUELTA' => 'RESUELTO',
            'ACTIVA' => 'ACTIVO',
            '', 'PENDIENTE' => 'ABIERTO',
            default => $normalizado,
        };
    }

    private function etiquetaTipo(string $tipo): string
    {
        return match ($tipo) {
            'CAIDA' => 'Caídas',
            'LESION' => 'Lesiones',
            'COMPLICACION' => 'Complicaciones',
            'OTRO' => 'Otros',
            default => 'Incidentes',
        };
    }

    private function iconoTipo(string $tipo): string
    {
        return match ($tipo) {
            'CAIDA' => 'ph-bold ph-person-simple-walk text-rose-600',
            'LESION' => 'ph-bold ph-band-aids text-amber-600',
            'COMPLICACION' => 'ph-bold ph-heartbeat text-rose-600',
            default => 'ph-bold ph-shield-warning text-blue-600',
        };
    }

    private function colorPunto(string $estado): string
    {
        return match ($estado) {
            'RESUELTO' => 'bg-emerald-500',
            'ABIERTO', 'ACTIVO' => 'bg-rose-500',
            default => 'bg-amber-500',
        };
    }

    private function colorEstado(string $estado): string
    {
        return match ($estado) {
            'RESUELTO' => 'bg-emerald-50 text-emerald-800 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800',
            'ABIERTO', 'ACTIVO' => 'bg-rose-50 text-rose-800 border-rose-300 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800',
            default => 'bg-amber-50 text-amber-800 border-amber-300 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800',
        };
    }
}
