<?php

namespace App\Livewire\Alertas;

use Livewire\Component;
use App\Models\AdultoMayor;
use Illuminate\Support\Facades\Schema;

class AlertasPendientesPanel extends Component
{
    public $buscar = '';
    public $filtroCategoria = 'todas';

    public function render()
    {
        // Carga con Eager Loading explícito para evitar N+1 query problem
        $adultos = AdultoMayor::with([
            'estado',
            'familiares',
            'documentos',
            'fichasMedicas',
            'medicaciones',
            'signosVitales',
            'valoracionesFuncionales',
            'observaciones',
            'atenciones',
            'actividades'
        ])->get();

        // Carga de Evaluaciones Geriátricas de la Suite consolidada en una sola consulta
        $evaluacionesGeriatricas = \App\Models\EvaluacionGeriatrica::query()
            ->when(Schema::hasColumn('evaluaciones_geriatricas', 'anulado_en'), fn ($q) => $q->whereNull('anulado_en'))
            ->when(Schema::hasColumn('evaluaciones_geriatricas', 'deleted_at'), fn ($q) => $q->whereNull('deleted_at'))
            ->get()
            ->groupBy('cod_am');

        $todasLasAlertas = [];

        foreach ($adultos as $adulto) {
            $nombreCompleto = trim("{$adulto->nombres} {$adulto->ap_paterno} {$adulto->ap_materno}");

            // ── 1. RED DE APOYO ──────────────────────────────────────
            // Sin familiar vinculado
            if ($adulto->familiares->count() === 0) {
                $todasLasAlertas[] = [
                    'adulto_id' => $adulto->cod_am,
                    'nombre' => $nombreCompleto,
                    'categoria' => 'red_de_apoyo',
                    'categoria_label' => 'Red de Apoyo',
                    'nivel' => 'prioritaria',
                    'descripcion' => 'No cuenta con ningún familiar vinculado en su red de apoyo.',
                    'fecha' => null,
                    'icono' => 'ph-users-three',
                    'color_text' => 'text-red-700',
                    'color_bg' => 'bg-red-50',
                    'color_border' => 'border-red-200/50'
                ];
            } else {
                // Sin responsable principal
                $tieneResponsable = $adulto->familiares->contains(function ($f) {
                    return isset($f->pivot) && $f->pivot->es_responsable == 1;
                });
                if (!$tieneResponsable) {
                    $todasLasAlertas[] = [
                        'adulto_id' => $adulto->cod_am,
                        'nombre' => $nombreCompleto,
                        'categoria' => 'red_de_apoyo',
                        'categoria_label' => 'Red de Apoyo',
                        'nivel' => 'preventiva',
                        'descripcion' => 'Falta asignar un familiar responsable principal en su red de apoyo.',
                        'fecha' => null,
                        'icono' => 'ph-user-focus',
                        'color_text' => 'text-amber-700',
                        'color_bg' => 'bg-amber-50',
                        'color_border' => 'border-amber-200/50'
                    ];
                }
            }

            // Sin contacto de emergencia
            if (empty($adulto->contacto_emergencia_nombre) || empty($adulto->contacto_emergencia_celular)) {
                $todasLasAlertas[] = [
                    'adulto_id' => $adulto->cod_am,
                    'nombre' => $nombreCompleto,
                    'categoria' => 'red_de_apoyo',
                    'categoria_label' => 'Red de Apoyo',
                    'nivel' => 'prioritaria',
                    'descripcion' => 'No tiene registrado un contacto de emergencia o número de celular de respaldo.',
                    'fecha' => null,
                    'icono' => 'ph-phone-call',
                    'color_text' => 'text-red-700',
                    'color_bg' => 'bg-red-50',
                    'color_border' => 'border-red-200/50'
                ];
            }

            // ── 2. DOCUMENTACIÓN ─────────────────────────────────────
            // Sin documentos registrados
            if ($adulto->documentos->count() === 0) {
                $todasLasAlertas[] = [
                    'adulto_id' => $adulto->cod_am,
                    'nombre' => $nombreCompleto,
                    'categoria' => 'documentacion',
                    'categoria_label' => 'Documentos',
                    'nivel' => 'preventiva',
                    'descripcion' => 'Sin documentos institucionales o de respaldo digitalizados.',
                    'fecha' => null,
                    'icono' => 'ph-file-warning',
                    'color_text' => 'text-amber-700',
                    'color_bg' => 'bg-amber-50',
                    'color_border' => 'border-amber-200/50'
                ];
            }

            // ── 3. SALUD Y CUIDADOS ──────────────────────────────────
            // Sin ficha médica básica registrada
            if ($adulto->fichasMedicas->count() === 0) {
                $todasLasAlertas[] = [
                    'adulto_id' => $adulto->cod_am,
                    'nombre' => $nombreCompleto,
                    'categoria' => 'salud_y_cuidados',
                    'categoria_label' => 'Salud y Cuidados',
                    'nivel' => 'prioritaria',
                    'descripcion' => 'No cuenta con la ficha médica básica registrada en el sistema.',
                    'fecha' => null,
                    'icono' => 'ph-first-aid',
                    'color_text' => 'text-red-700',
                    'color_bg' => 'bg-red-50',
                    'color_border' => 'border-red-200/50'
                ];
            }

            // Sin signos vitales registrados
            if ($adulto->signosVitales->count() === 0) {
                $todasLasAlertas[] = [
                    'adulto_id' => $adulto->cod_am,
                    'nombre' => $nombreCompleto,
                    'categoria' => 'salud_y_cuidados',
                    'categoria_label' => 'Salud y Cuidados',
                    'nivel' => 'preventiva',
                    'descripcion' => 'Sin signos vitales registrados.',
                    'fecha' => null,
                    'icono' => 'ph-heartbeat',
                    'color_text' => 'text-amber-700',
                    'color_bg' => 'bg-amber-50',
                    'color_border' => 'border-amber-200/50'
                ];
            }

            // Sin valoración funcional registrada
            if ($adulto->valoracionesFuncionales->count() === 0) {
                $todasLasAlertas[] = [
                    'adulto_id' => $adulto->cod_am,
                    'nombre' => $nombreCompleto,
                    'categoria' => 'salud_y_cuidados',
                    'categoria_label' => 'Salud y Cuidados',
                    'nivel' => 'preventiva',
                    'descripcion' => 'Falta registrar la valoración funcional institucional.',
                    'fecha' => null,
                    'icono' => 'ph-person-arms-spread',
                    'color_text' => 'text-amber-700',
                    'color_bg' => 'bg-amber-50',
                    'color_border' => 'border-amber-200/50'
                ];
            }

            // ── 4. EVALUACIONES GERIÁTRICAS ──────────────────────────
            $evalsAdulto = $evaluacionesGeriatricas->get($adulto->cod_am) ?? collect();

            // Sin evaluación geriátrica registrada
            if ($evalsAdulto->isEmpty()) {
                $todasLasAlertas[] = [
                    'adulto_id' => $adulto->cod_am,
                    'nombre' => $nombreCompleto,
                    'categoria' => 'evaluaciones',
                    'categoria_label' => 'Evaluaciones Geriátricas',
                    'nivel' => 'prioritaria',
                    'descripcion' => 'Sin evaluación geriátrica registrada.',
                    'fecha' => null,
                    'icono' => 'ph-brain',
                    'color_text' => 'text-red-700',
                    'color_bg' => 'bg-red-50',
                    'color_border' => 'border-red-200/50'
                ];
            } else {
                // Evaluación con nivel de alerta PREVENTIVO o CRITICO (de la Suite)
                foreach ($evalsAdulto as $eval) {
                    $alertaVal = strtoupper(trim($eval->nivel_alerta));
                    if (in_array($alertaVal, ['PREVENTIVO', 'CRITICO'])) {
                        $esCritico = ($alertaVal === 'CRITICO');
                        $todasLasAlertas[] = [
                            'adulto_id' => $adulto->cod_am,
                            'nombre' => $nombreCompleto,
                            'categoria' => 'evaluaciones',
                            'categoria_label' => 'Evaluaciones Geriátricas',
                            'nivel' => $esCritico ? 'prioritaria' : 'preventiva',
                            'descripcion' => "La evaluación geriátrica del adulto mayor registró nivel de alerta " . $alertaVal . ".",
                            'fecha' => $eval->fecha_eval ? $eval->fecha_eval->format('d/m/Y') : null,
                            'icono' => 'ph-shield-warning',
                            'color_text' => $esCritico ? 'text-red-700' : 'text-amber-700',
                            'color_bg' => $esCritico ? 'bg-red-50' : 'bg-amber-50',
                            'color_border' => $esCritico ? 'border-red-200/50' : 'border-amber-200/50'
                        ];
                    }
                }
            }

            // ── 5. SEGUIMIENTO ───────────────────────────────────────
            // Observaciones de alta importancia o urgencia
            foreach ($adulto->getRelation('observaciones') as $obs) {
                $imp = strtolower($obs->nivel_importancia);
                if (in_array($imp, ['alta', 'urgente', 'prioritaria'])) {
                    $todasLasAlertas[] = [
                        'adulto_id' => $adulto->cod_am,
                        'nombre' => $nombreCompleto,
                        'categoria' => 'seguimiento',
                        'categoria_label' => 'Seguimiento',
                        'nivel' => 'prioritaria',
                        'descripcion' => "Observación de seguimiento registrada con importancia " . strtoupper($obs->nivel_importancia) . ".",
                        'fecha' => $obs->fecha ? $obs->fecha->format('d/m/Y') : null,
                        'icono' => 'ph-warning',
                        'color_text' => 'text-red-700',
                        'color_bg' => 'bg-red-50',
                        'color_border' => 'border-red-200/50'
                    ];
                }
            }

            // Atenciones pendientes
            foreach ($adulto->atenciones as $aten) {
                $est = strtolower($aten->estado);
                if (in_array($est, ['pendiente', 'en proceso', 'programada'])) {
                    $todasLasAlertas[] = [
                        'adulto_id' => $adulto->cod_am,
                        'nombre' => $nombreCompleto,
                        'categoria' => 'seguimiento',
                        'categoria_label' => 'Seguimiento',
                        'nivel' => 'preventiva',
                        'descripcion' => "Atención registrada pendiente de seguimiento o ejecución.",
                        'fecha' => $aten->fecha ? $aten->fecha->format('d/m/Y') : null,
                        'icono' => 'ph-clock-clockwise',
                        'color_text' => 'text-amber-700',
                        'color_bg' => 'bg-amber-50',
                        'color_border' => 'border-amber-200/50'
                    ];
                }
            }

            // Actividades pendientes
            foreach ($adulto->actividades as $act) {
                $est = strtolower($act->estado);
                if (in_array($est, ['pendiente', 'programada'])) {
                    $todasLasAlertas[] = [
                        'adulto_id' => $adulto->cod_am,
                        'nombre' => $nombreCompleto,
                        'categoria' => 'seguimiento',
                        'categoria_label' => 'Seguimiento',
                        'nivel' => 'informativa',
                        'descripcion' => "Participación en actividad institucional registrada como pendiente.",
                        'fecha' => $act->fecha ? $act->fecha->format('d/m/Y') : null,
                        'icono' => 'ph-calendar-blank',
                        'color_text' => 'text-[#2F3E5C]',
                        'color_bg' => 'bg-[#2F3E5C]/5',
                        'color_border' => 'border-[#2F3E5C]/15'
                    ];
                }
            }

            // ── 6. ESTADO INSTITUCIONAL ──────────────────────────────
            // Adulto mayor inactivo o archivado
            $estadoEst = strtolower($adulto->estado?->estado);
            if (in_array($estadoEst, ['inactivo', 'archivado', 'egresado'])) {
                $todasLasAlertas[] = [
                    'adulto_id' => $adulto->cod_am,
                    'nombre' => $nombreCompleto,
                    'categoria' => 'estado_institucional',
                    'categoria_label' => 'Estado Institucional',
                    'nivel' => 'informativa',
                    'descripcion' => "Ficha con estado institucional: " . strtoupper($adulto->estado?->estado) . ".",
                    'fecha' => null,
                    'icono' => 'ph-archive',
                    'color_text' => 'text-slate-600',
                    'color_bg' => 'bg-slate-100',
                    'color_border' => 'border-slate-200'
                ];
            }
        }

        // ── FILTRADO Y BÚSQUEDA ──────────────────────────────────
        $alertasFiltradas = collect($todasLasAlertas);

        if ($this->filtroCategoria !== 'todas') {
            $alertasFiltradas = $alertasFiltradas->where('categoria', $this->filtroCategoria);
        }

        if (!empty($this->buscar)) {
            $busqueda = strtolower($this->buscar);
            $alertasFiltradas = $alertasFiltradas->filter(function ($alerta) use ($busqueda) {
                return str_contains(strtolower($alerta['nombre']), $busqueda)
                    || str_contains(strtolower($alerta['descripcion']), $busqueda)
                    || str_contains(strtolower($alerta['adulto_id']), $busqueda);
            });
        }

        // Conteos globales para indicadores superiores
        $conteos = [
            'total' => count($todasLasAlertas),
            'red_de_apoyo' => collect($todasLasAlertas)->where('categoria', 'red_de_apoyo')->count(),
            'documentacion' => collect($todasLasAlertas)->where('categoria', 'documentacion')->count(),
            'salud_y_cuidados' => collect($todasLasAlertas)->where('categoria', 'salud_y_cuidados')->count(),
            'evaluaciones' => collect($todasLasAlertas)->where('categoria', 'evaluaciones')->count(),
            'seguimiento' => collect($todasLasAlertas)->where('categoria', 'seguimiento')->count(),
            'estado_institucional' => collect($todasLasAlertas)->where('categoria', 'estado_institucional')->count(),
        ];

        return view('livewire.alertas.alertas-pendientes-panel', [
            'alertas' => $alertasFiltradas,
            'conteos' => $conteos
        ])->layout('layouts.sistema');
    }
}
