<?php

namespace App\Modulos\Reportes\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use App\Modulos\Reportes\Aplicacion\Acciones\PrepararReporteGerencia;
use App\Modulos\Reportes\Aplicacion\Consultas\ConsultarIndicadoresGerencia;
use App\Modulos\Reportes\Aplicacion\Consultas\ConsultarReportePorVistaGerencia;
use App\Modulos\Reportes\Aplicacion\Consultas\DashboardService;
use App\Modulos\Reportes\Infraestructura\Exportaciones\Reportes\ReporteInstitucionalExport;
use App\Modulos\Reportes\Presentacion\Reportes\GraficasGerencia;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class ReporteInstitucionalController extends Controller
{
    public function __construct(protected DashboardService $service) {}

    private function obtenerDatos(): array
    {
        $usuario = Auth::user();

        return [
            'generadoEn' => now()->format('d/m/Y H:i'),
            'usuario' => $this->service->obtenerSaludoUsuario($usuario),
            'kpis' => $this->service->obtenerKpisInstitucionales(),
            'salud' => $this->service->obtenerResumenSalud(),
            'redFamiliar' => $this->service->obtenerRedFamiliar(),
            'equipo' => $this->service->obtenerEquipoInstitucional(),
            'alertas' => $this->service->obtenerAlertasEstructuradas(auth()->user()),
            'bitacora' => array_slice($this->service->obtenerBitacoraAuditoria(), 0, 5),
        ];
    }

    public function preview()
    {
        if ($this->esGerencia()) {
            $consulta = app(ConsultarIndicadoresGerencia::class);
            $consulta->autorizarReporte(Auth::user());
            $entrada = request()->validate(['seccion' => ['nullable', 'in:resumen,personal,ocupacion,alertas']]);
            $reporte = $consulta->consultar(Auth::user(), $entrada['seccion'] ?? 'resumen');
            $solicitud = app(RolePreviewService::class)->isActive(Auth::user()) ? null
                : app(PrepararReporteGerencia::class)->preparar(Auth::user(), $reporte);

            return view('reportes::paginas.gerencia.reportes', compact('reporte', 'solicitud'));
        }
        $datos = $this->obtenerDatos();
        $datos['esPdf'] = false;

        return view('pages.reportes.institucional.general', $datos);
    }

    public function pdf()
    {
        if ($this->esGerencia()) {
            $reporte = $this->contenidoSeleccionado(app(PrepararReporteGerencia::class)->recuperar(Auth::user(), $this->solicitudGerencia()));
            $respuesta = Pdf::loadView('reportes::paginas.gerencia.reporte-pdf', compact('reporte'))
                ->setPaper('a4', 'portrait')->setOptions(['isRemoteEnabled' => false, 'defaultFont' => 'DejaVu Sans'])
                ->download($this->nombreReporteGerencia($reporte, 'pdf'))
                ->header('Cache-Control', 'private, no-store');
            $this->auditarGerencia('PDF', $reporte);

            return $respuesta;
        }
        abort_if(request()->has('solicitud'), 403);
        $datos = $this->obtenerDatos();
        $datos['esPdf'] = true;

        $pdf = Pdf::loadView('pages.reportes.institucional.general', $datos)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isRemoteEnabled' => false,
                'isHtml5ParserEnabled' => true,
                'chroot' => public_path(),
                'dpi' => 96,
            ]);

        $nombre = 'reporte-institucional-'.now()->format('Y-m-d').'.pdf';

        return $pdf->download($nombre);
    }

    public function excel()
    {
        abort_if($this->esGerencia(), 403, 'Gerencia dispone de PDF y CSV de indicadores agregados.');
        $datos = $this->obtenerDatos();
        $nombre = 'reporte-institucional-'.now()->format('Y-m-d').'.xlsx';

        return Excel::download(new ReporteInstitucionalExport($datos), $nombre);
    }

    public function prepararGerencia()
    {
        abort_unless($this->esGerencia(), 403);
        $preparador = app(PrepararReporteGerencia::class);
        if (request()->has('vista')) {
            $filtro = request()->validate(['vista' => ['required', 'string', 'max:30'], 'solicitud' => ['prohibited']]);
            $reporte = app(ConsultarReportePorVistaGerencia::class)->consultar(Auth::user(), $filtro['vista'],
                request()->only('fecha', 'area', 'turno', 'estado', 'modo', 'periodo'));
            $solicitud = $preparador->preparar(Auth::user(), $reporte);
        } else {
            $solicitud = $this->solicitudGerencia();
            $reporte = $preparador->recuperar(Auth::user(), $solicitud);
        }

        if (request()->expectsJson()) {
            return response()->json([
                'solicitud' => $solicitud,
                'titulo' => $reporte['titulo'] ?? 'Resumen gerencial',
                'referencia' => $reporte['referencia'],
                'ambito' => $reporte['ambito'],
                'graficas' => app(GraficasGerencia::class)->series($reporte),
                'graficas_url' => route('admin.reportes.institucional.graficas', absolute: false),
                'partes' => collect($reporte['filas'])->map(fn ($fila, $indice) => [
                    'id' => (string) $indice, 'etiqueta' => $fila['indicador'],
                    'valor' => $fila['valor'], 'unidad' => $fila['unidad'],
                ])->values()->all(),
                'pdf' => route('admin.reportes.institucional.pdf', absolute: false),
                'csv' => route('admin.reportes.institucional.csv', absolute: false),
            ])->header('Cache-Control', 'private, no-store');
        }

        return response()->view('reportes::paginas.gerencia.exportacion', compact('reporte', 'solicitud'))
            ->header('Cache-Control', 'private, no-store');
    }

    public function csvGerencia()
    {
        abort_unless($this->esGerencia(), 403);
        $reporte = $this->contenidoSeleccionado(app(PrepararReporteGerencia::class)->recuperar(Auth::user(), $this->solicitudGerencia()));
        $this->auditarGerencia('CSV', $reporte);
        $generadoEn = now()->toIso8601String();

        return response()->streamDownload(function () use ($reporte, $generadoEn): void {
            $salida = fopen('php://output', 'wb');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, ['Indicador', 'Valor', 'Unidad', 'Referencia', 'Datos consultados', 'Alcance', 'Calidad', 'Reporte', 'Generado'], escape: '');
            foreach ($reporte['filas'] as $fila) {
                $valores = [$fila['indicador'], $fila['valor'], $fila['unidad'], $fila['referencia'],
                    $reporte['consultado_en'], $reporte['ambito'], $fila['calidad'], $reporte['titulo'] ?? 'Resumen gerencial', $generadoEn];
                fputcsv($salida, array_map(static fn ($valor) => is_string($valor) && preg_match('/^[=+@\-\t\r]/', $valor) ? "'".$valor : $valor, $valores), escape: '');
            }
            fclose($salida);
        }, $this->nombreReporteGerencia($reporte, 'csv'), ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'private, no-store']);
    }

    public function graficasGerencia()
    {
        abort_unless($this->esGerencia(), 403);
        if (request()->has('solicitud')) {
            $reporte = app(PrepararReporteGerencia::class)->recuperar(Auth::user(), $this->solicitudGerencia());
        } else {
            $entrada = request()->validate(['vista' => ['required', 'string', 'max:30']]);
            $reporte = app(ConsultarReportePorVistaGerencia::class)->consultar(Auth::user(), $entrada['vista'],
                request()->only('fecha', 'area', 'turno', 'estado', 'modo', 'periodo'));
        }
        $reporte = $this->contenidoSeleccionado($reporte);

        return response()->json(['titulo' => $reporte['titulo'] ?? 'Resumen gerencial',
            'referencia' => $reporte['referencia'], 'ambito' => $reporte['ambito'],
            'graficas' => app(GraficasGerencia::class)->series($reporte)])
            ->header('Cache-Control', 'private, no-store');
    }

    private function esGerencia(): bool
    {
        $usuario = Auth::user();
        $preview = app(RolePreviewService::class)->activeRole($usuario);

        return $preview === 'GERENTE' || (! $preview && $usuario->hasRole('GERENTE') && ! $usuario->hasRole('SUPERADMINISTRADOR'));
    }

    private function contenidoSeleccionado(array $reporte): array
    {
        $entrada = request()->validate([
            'partes' => ['sometimes', 'required', 'array', 'min:1', 'max:'.count($reporte['filas'])],
            'partes.*' => ['required', 'integer', 'distinct', Rule::in(array_keys($reporte['filas']))],
            'tipo_grafica' => ['sometimes', Rule::in(GraficasGerencia::TIPOS)],
        ], [
            'partes.*' => 'Selecciona al menos una parte disponible del reporte.',
            'partes.*.*' => 'La selección de contenido no es válida. Abre de nuevo la exportación.',
        ]);
        if (isset($entrada['partes'])) {
            $reporte['filas'] = array_values(array_intersect_key($reporte['filas'], array_flip($entrada['partes'])));
        }
        $reporte['tipo_grafica'] = $entrada['tipo_grafica'] ?? 'auto';

        return $reporte;
    }

    private function nombreReporteGerencia(array $reporte, string $extension): string
    {
        $origen = isset($reporte['vista_origen']) ? '-'.$reporte['vista_origen'] : '';

        return 'reporte-gerencial'.$origen.'-'.now()->format('Y-m-d').'.'.$extension;
    }

    private function solicitudGerencia(): string
    {
        $solicitud = request()->query('solicitud');

        return is_string($solicitud) ? $solicitud : '';
    }

    private function auditarGerencia(string $formato, array $reporte): void
    {
        activity('Reportes')->causedBy(Auth::user())->event('reporte_gerencial_respuesta_preparada')
            ->withProperties(['formato' => $formato, 'reporte' => $reporte['titulo'] ?? 'Resumen gerencial',
                'vista' => $reporte['vista_origen'] ?? null, 'indicadores' => array_column($reporte['filas'], 'indicador'),
                'alcance' => 'Agregado institucional'])
            ->log('Preparó respuesta de descarga gerencial; no acredita guardado en el dispositivo.');
    }
}
