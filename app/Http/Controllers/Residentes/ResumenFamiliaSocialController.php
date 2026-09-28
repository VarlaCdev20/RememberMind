<?php

namespace App\Http\Controllers\Residentes;

use App\Http\Controllers\Controller;
use App\Models\Residente;
use App\Models\ResidenteContacto;
use App\Models\Visita;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class ResumenFamiliaSocialController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.familia-social.resumen', $this->dashboardData());
    }

    private function dashboardData(): array
    {
        $residentes = Residente::query()
            ->with(['vinculosContacto.contacto'])
            ->orderBy('apellido_paterno')
            ->orderBy('nombres')
            ->get();

        $totalResidentes = $residentes->count();
        $vinculosActivos = ResidenteContacto::query()->where('estado', 'ACTIVO')->get();
        $conRedIds = $vinculosActivos->pluck('cod_residente')->unique();
        $conResponsableIds = $vinculosActivos->where('responsable_principal', true)
            ->pluck('cod_residente')->unique();
        $conEmergenciaIds = $vinculosActivos->where('contacto_emergencia', true)
            ->pluck('cod_residente')->unique();

        $conRed = $conRedIds->count();
        $sinRed = max($totalResidentes - $conRed, 0);
        $visitas = $this->visitasData($totalResidentes);

        // La BDD V2 congelada no define una tabla de ficha social. La pantalla
        // informa esa ausencia sin inventar una estructura paralela.
        $fichaSocial = [
            'table' => null,
            'completas' => 0,
            'pendientes' => $totalResidentes,
            'sin_registro' => $totalResidentes,
            'ultimas' => collect(),
        ];

        $redIncompleta = $residentes->map(function (Residente $residente) use ($conRedIds, $conResponsableIds, $conEmergenciaIds): ?array {
            $faltantes = [];
            if (! $conRedIds->contains($residente->cod_residente)) {
                $faltantes[] = 'Sin contactos vinculados';
            }
            if (! $conResponsableIds->contains($residente->cod_residente)) {
                $faltantes[] = 'Sin responsable principal';
            }
            if (! $conEmergenciaIds->contains($residente->cod_residente)) {
                $faltantes[] = 'Sin contacto de emergencia';
            }
            if ($faltantes === []) {
                return null;
            }

            return [
                'adulto' => $this->nombreResidente($residente),
                'estado' => count($faltantes) === 3 ? 'Red no registrada' : 'Red incompleta',
                'faltante' => implode(' · ', $faltantes),
                'url' => Route::has('admin.adultos-mayores.show')
                    ? route('admin.adultos-mayores.show', ['adulto_mayor' => $residente->cod_residente, 'tab' => 'familiares'])
                    : null,
            ];
        })->filter()->take(8)->values();

        $alertas = $redIncompleta->map(fn (array $item) => [
            'adulto' => $item['adulto'],
            'motivo' => $item['faltante'],
            'estado' => $item['estado'],
            'prioridad' => str_contains($item['faltante'], 'Sin contactos') ? 'Alta' : 'Media',
            'fecha' => now()->format('d/m/Y'),
        ])->take(8)->values();

        $contactosRegistrados = $vinculosActivos->pluck('cod_contacto')->unique()->count();
        $seguimiento = $residentes->filter(fn (Residente $residente) =>
            ! $conResponsableIds->contains($residente->cod_residente)
            || ! $conEmergenciaIds->contains($residente->cod_residente)
        )->count();

        $metricas = [
            $this->metrica('Residentes con red de apoyo', $conRed, 'Con al menos un contacto vinculado', $this->percent($conRed, $totalResidentes).'% cobertura', 'ph-users-three', 'emerald'),
            $this->metrica('Residentes sin red de apoyo', $sinRed, 'Requieren completar red de apoyo', $sinRed > 0 ? 'Atención' : 'Sin pendientes', 'ph-warning-circle', 'amber'),
            $this->metrica('Contactos vinculados', $vinculosActivos->count(), $contactosRegistrados.' contactos registrados', 'Vínculos activos', 'ph-hand-heart', 'salmon'),
            $this->metrica('Visitas registradas', $visitas['total'], 'Registros del módulo de visitas', 'Histórico', 'ph-door-open', 'blue'),
            $this->metrica('Visitas recientes', $visitas['recientes_count'], 'Durante los últimos 30 días', $visitas['recientes_count'] > 0 ? 'Reciente' : 'Sin actividad', 'ph-calendar-check', 'green'),
            $this->metrica('Fichas sociales completas', 0, 'No definidas en la BDD V2', 'Sin tabla operativa', 'ph-clipboard-text', 'violet'),
            $this->metrica('Fichas sociales pendientes', $totalResidentes, 'Requieren definición en otra fase', 'No implementado', 'ph-clipboard', 'rose'),
            $this->metrica('Casos con seguimiento social', $seguimiento, 'Por responsable o contacto pendiente', $seguimiento > 0 ? 'Revisar' : 'Estable', 'ph-heartbeat', 'indigo'),
        ];

        $estadoSocial = [
            'red_apoyo' => $this->percent($conRed, $totalResidentes),
            'sin_red' => $this->percent($sinRed, $totalResidentes),
            'ficha_social' => 0,
            'visitas_recientes' => $visitas['adultos_recientes_porcentaje'],
        ];
        $estadoSocial['nivel'] = $this->nivelSocial($estadoSocial);

        return [
            'metricas' => $metricas,
            'estadoSocial' => $estadoSocial,
            'chartData' => [
                'red' => ['labels' => ['Con red de apoyo', 'Sin red de apoyo'], 'data' => [$conRed, $sinRed]],
                'visitas' => $visitas['mensual'],
                'ficha' => ['labels' => ['Completas', 'Pendientes', 'Sin registro'], 'data' => [0, 0, $totalResidentes], 'available' => false],
            ],
            'alertas' => $alertas,
            'visitasRecientes' => $visitas['recientes'],
            'redIncompleta' => $redIncompleta,
            'fichaSocial' => $fichaSocial,
            'reportesSociales' => $this->reportesSociales(),
            'resumenDatos' => [
                'total_adultos' => $totalResidentes,
                'familiares_registrados' => $contactosRegistrados,
                'adultos_sin_contacto' => $sinRed,
            ],
            'rutasSubmodulos' => [
                'red_apoyo' => Route::has('admin.familia-social.red-apoyo') ? route('admin.familia-social.red-apoyo') : null,
                'visitas' => Route::has('admin.familia-social.visitas') ? route('admin.familia-social.visitas') : null,
                'ficha_social' => Route::has('admin.familia-social.ficha-social') ? route('admin.familia-social.ficha-social') : null,
            ],
        ];
    }

    private function visitasData(int $totalResidentes): array
    {
        $visitas = Visita::query()
            ->with(['residente', 'contacto'])
            ->orderByDesc('fecha_hora_ingreso')
            ->orderByDesc('fecha_hora_programada')
            ->get();
        $desde = now()->subDays(30);
        $recientes = $visitas->filter(function (Visita $visita) use ($desde): bool {
            $fecha = $visita->fecha_hora_ingreso ?? $visita->fecha_hora_programada;
            return $fecha?->greaterThanOrEqualTo($desde) ?? false;
        });

        $porMes = $visitas->filter(fn (Visita $visita) => $visita->fecha_hora_ingreso || $visita->fecha_hora_programada)
            ->groupBy(fn (Visita $visita) => ($visita->fecha_hora_ingreso ?? $visita->fecha_hora_programada)->format('Y-m'));
        $labels = [];
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $fecha = now()->startOfMonth()->subMonths($i);
            $labels[] = $this->mesCorto((int) $fecha->format('n'));
            $data[] = $porMes->get($fecha->format('Y-m'), collect())->count();
        }

        return [
            'total' => $visitas->count(),
            'recientes_count' => $recientes->count(),
            'adultos_recientes_porcentaje' => $this->percent($recientes->pluck('cod_residente')->unique()->count(), $totalResidentes),
            'mensual' => ['labels' => $labels, 'data' => $data, 'available' => array_sum($data) > 0],
            'recientes' => $recientes->take(8)->map(fn (Visita $visita) => [
                'adulto' => $this->nombreResidente($visita->residente),
                'visitante' => $visita->contacto ? trim("{$visita->contacto->nombres} {$visita->contacto->apellido_paterno}") : 'Contacto no disponible',
                'fecha' => ($visita->fecha_hora_ingreso ?? $visita->fecha_hora_programada)?->format('d/m/Y') ?? 'Sin fecha',
                'hora' => ($visita->fecha_hora_ingreso ?? $visita->fecha_hora_programada)?->format('H:i'),
                'motivo' => $visita->motivo ?: $visita->observacion ?: 'Sin motivo registrado',
                'estado' => $visita->estado,
            ])->values(),
        ];
    }

    private function metrica(string $label, int $valor, string $subtitulo, string $badge, string $icono, string $color): array
    {
        return compact('label', 'valor', 'subtitulo', 'badge', 'icono', 'color');
    }

    private function reportesSociales(): array
    {
        return [
            ['titulo' => 'Reporte de red de apoyo', 'descripcion' => 'Resumen de contactos y vínculos activos.', 'estado' => Route::has('admin.reportes.familiares.preview') ? 'Disponible' : 'Preparado', 'url' => Route::has('admin.reportes.familiares.preview') ? route('admin.reportes.familiares.preview') : null, 'permiso' => 'reportes.ver', 'icono' => 'ph-users-three'],
            ['titulo' => 'Reporte de visitas', 'descripcion' => 'Seguimiento de visitas familiares y sociales.', 'estado' => 'Disponible próximamente', 'url' => null, 'permiso' => 'residentes_contactos.ver', 'icono' => 'ph-calendar-check'],
            ['titulo' => 'Fichas sociales pendientes', 'descripcion' => 'La BDD V2 no define una ficha social operativa.', 'estado' => 'No implementado', 'url' => null, 'permiso' => 'residentes_contactos.ver', 'icono' => 'ph-clipboard-text'],
            ['titulo' => 'Reporte social institucional', 'descripcion' => 'Indicadores consolidados para seguimiento directivo.', 'estado' => 'Preparado', 'url' => null, 'permiso' => 'residentes_contactos.ver', 'icono' => 'ph-chart-pie-slice'],
        ];
    }

    private function nombreResidente(?Residente $residente): string
    {
        return $residente
            ? trim("{$residente->nombres} {$residente->apellido_paterno} {$residente->apellido_materno}")
            : 'Residente no disponible';
    }

    private function percent(int $value, int $total): int
    {
        return $total > 0 ? (int) round(($value / $total) * 100) : 0;
    }

    private function nivelSocial(array $estadoSocial): array
    {
        $score = (int) round(($estadoSocial['red_apoyo'] + $estadoSocial['ficha_social'] + $estadoSocial['visitas_recientes']) / 3);
        if ($score >= 70) {
            return ['texto' => 'Estable', 'score' => $score, 'color' => 'emerald'];
        }
        if ($score >= 40) {
            return ['texto' => 'Pendiente', 'score' => $score, 'color' => 'amber'];
        }
        return ['texto' => 'Requiere seguimiento', 'score' => $score, 'color' => 'rose'];
    }

    private function mesCorto(int $month): string
    {
        return [1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'][$month] ?? '';
    }
}
