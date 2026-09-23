<?php

namespace App\Livewire\Alertas;

use Livewire\Component;
use App\Models\Residente;
use App\Models\AplicacionInstrumento;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AlertasPendientesPanel extends Component
{
    public $buscar = '';
    public $filtroCategoria = 'todas';

    public function render()
    {
        $adultos = Residente::with([
            'contactos',
            'documentos',
            'atenciones',
            'prescripciones',
            'signosVitales',
            'valoracionesFuncionales',
            'observaciones',
            'actividades'
        ])->get();

        $evaluaciones = AplicacionInstrumento::with('instrumento')
            ->where('estado', 'ACTIVO')
            ->get()
            ->groupBy('cod_residente');

        $todasLasAlertas = [];

        foreach ($adultos as $adulto) {
            $nombreCompleto = $adulto->nombre_completo;
            $codRes = $adulto->cod_residente;

            // ── 1. RED DE APOYO ──────────────────────────────────────
            $contactos = $adulto->contactos;
            if ($contactos->isEmpty()) {
                $todasLasAlertas[] = [
                    'adulto_id' => $codRes,
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
                $tieneResponsable = $contactos->contains(function ($c) {
                    return isset($c->pivot) && $c->pivot->responsable_principal;
                });
                if (!$tieneResponsable) {
                    $todasLasAlertas[] = [
                        'adulto_id' => $codRes,
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

            // ── 2. DOCUMENTACIÓN ─────────────────────────────────────
            $documentos = $adulto->documentos;
            if ($documentos->isEmpty()) {
                $todasLasAlertas[] = [
                    'adulto_id' => $codRes,
                    'nombre' => $nombreCompleto,
                    'categoria' => 'documentacion',
                    'categoria_label' => 'Documentación',
                    'nivel' => 'prioritaria',
                    'descripcion' => 'No cuenta con ningún documento digitalizado en su expediente.',
                    'fecha' => null,
                    'icono' => 'ph-file-x',
                    'color_text' => 'text-red-700',
                    'color_bg' => 'bg-red-50',
                    'color_border' => 'border-red-200/50'
                ];
            }

            // ── 3. SALUD Y CUIDADOS ──────────────────────────────────
            if ($adulto->atenciones->isEmpty()) {
                $todasLasAlertas[] = [
                    'adulto_id' => $codRes,
                    'nombre' => $nombreCompleto,
                    'categoria' => 'salud_y_cuidados',
                    'categoria_label' => 'Salud y Cuidados',
                    'nivel' => 'preventiva',
                    'descripcion' => 'Sin atención clínica inicial registrada.',
                    'fecha' => null,
                    'icono' => 'ph-first-aid',
                    'color_text' => 'text-amber-700',
                    'color_bg' => 'bg-amber-50',
                    'color_border' => 'border-amber-200/50'
                ];
            }

            if ($adulto->signosVitales->isEmpty()) {
                $todasLasAlertas[] = [
                    'adulto_id' => $codRes,
                    'nombre' => $nombreCompleto,
                    'categoria' => 'salud_y_cuidados',
                    'categoria_label' => 'Salud y Cuidados',
                    'nivel' => 'preventiva',
                    'descripcion' => 'Sin registro de signos vitales en el expediente.',
                    'fecha' => null,
                    'icono' => 'ph-heartbeat',
                    'color_text' => 'text-amber-700',
                    'color_bg' => 'bg-amber-50',
                    'color_border' => 'border-amber-200/50'
                ];
            }

            // ── 4. EVALUACIONES GERIÁTRICAS ──────────────────────────
            $evalsAdulto = $evaluaciones->get($codRes, collect());
            if ($evalsAdulto->isEmpty()) {
                $todasLasAlertas[] = [
                    'adulto_id' => $codRes,
                    'nombre' => $nombreCompleto,
                    'categoria' => 'evaluaciones',
                    'categoria_label' => 'Evaluaciones Geriátricas',
                    'nivel' => 'prioritaria',
                    'descripcion' => 'Sin evaluación de instrumentos registrada.',
                    'fecha' => null,
                    'icono' => 'ph-brain',
                    'color_text' => 'text-red-700',
                    'color_bg' => 'bg-red-50',
                    'color_border' => 'border-red-200/50'
                ];
            } else {
                foreach ($evalsAdulto as $eval) {
                    $alertaVal = strtoupper(trim($eval->clasificacion ?? ''));
                    if (in_array($alertaVal, ['ALTO', 'MEDIO', 'CRITICO', 'PREVENTIVO'])) {
                        $esCritico = in_array($alertaVal, ['ALTO', 'CRITICO']);
                        $todasLasAlertas[] = [
                            'adulto_id' => $codRes,
                            'nombre' => $nombreCompleto,
                            'categoria' => 'evaluaciones',
                            'categoria_label' => 'Evaluaciones Geriátricas',
                            'nivel' => $esCritico ? 'prioritaria' : 'preventiva',
                            'descripcion' => "La evaluación del instrumento {$eval->instrumento?->nombre} registró nivel {$alertaVal}.",
                            'fecha' => $eval->fecha_hora ? $eval->fecha_hora->format('d/m/Y') : null,
                            'icono' => 'ph-shield-warning',
                            'color_text' => $esCritico ? 'text-red-700' : 'text-amber-700',
                            'color_bg' => $esCritico ? 'bg-red-50' : 'bg-amber-50',
                            'color_border' => $esCritico ? 'border-red-200/50' : 'border-amber-200/50'
                        ];
                    }
                }
            }

            // ── 5. SEGUIMIENTO ───────────────────────────────────────
            foreach ($adulto->observaciones as $obs) {
                if ($obs->tipo_nota === 'URGENCIA') {
                    $todasLasAlertas[] = [
                        'adulto_id' => $codRes,
                        'nombre' => $nombreCompleto,
                        'categoria' => 'seguimiento',
                        'categoria_label' => 'Seguimiento',
                        'nivel' => 'prioritaria',
                        'descripcion' => "Nota clínica urgente: " . \Illuminate\Support\Str::limit($obs->contenido, 60),
                        'fecha' => $obs->fecha_hora ? $obs->fecha_hora->format('d/m/Y') : null,
                        'icono' => 'ph-warning',
                        'color_text' => 'text-red-700',
                        'color_bg' => 'bg-red-50',
                        'color_border' => 'border-red-200/50'
                    ];
                }
            }

            foreach ($adulto->atenciones as $aten) {
                if (in_array($aten->estado, ['PENDIENTE', 'PROGRAMADA'])) {
                    $todasLasAlertas[] = [
                        'adulto_id' => $codRes,
                        'nombre' => $nombreCompleto,
                        'categoria' => 'seguimiento',
                        'categoria_label' => 'Seguimiento',
                        'nivel' => 'preventiva',
                        'descripcion' => "Atención registrada pendiente de seguimiento o ejecución.",
                        'fecha' => $aten->fecha_hora ? $aten->fecha_hora->format('d/m/Y') : null,
                        'icono' => 'ph-clock-clockwise',
                        'color_text' => 'text-amber-700',
                        'color_bg' => 'bg-amber-50',
                        'color_border' => 'border-amber-200/50'
                    ];
                }
            }

            // ── 6. ESTADO INSTITUCIONAL ──────────────────────────────
            if (in_array($adulto->estado, ['INACTIVO', 'BAJA', 'FALLECIDO'])) {
                $todasLasAlertas[] = [
                    'adulto_id' => $codRes,
                    'nombre' => $nombreCompleto,
                    'categoria' => 'estado_institucional',
                    'categoria_label' => 'Estado Institucional',
                    'nivel' => 'informativa',
                    'descripcion' => "Ficha con estado institucional: " . strtoupper($adulto->estado) . ".",
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