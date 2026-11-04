<?php

namespace App\Modulos\Reportes\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use App\Modulos\Reportes\Aplicacion\Acciones\PrepararReporteGerencia;
use App\Modulos\Reportes\Aplicacion\Consultas\ConsultarIndicadoresGerencia;
use App\Modulos\Reportes\Aplicacion\Consultas\DashboardService;
use App\Modulos\Reportes\Infraestructura\Exportaciones\Reportes\ReporteInstitucionalExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
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
            $reporte = app(PrepararReporteGerencia::class)->recuperar(Auth::user(), (string) request('solicitud'));
            $respuesta = Pdf::loadView('reportes::paginas.gerencia.reporte-pdf', compact('reporte'))
                ->setPaper('a4', 'portrait')->setOptions(['isRemoteEnabled' => false, 'defaultFont' => 'DejaVu Sans'])
                ->download('reporte-gerencial-'.now()->format('Y-m-d').'.pdf');
            $this->auditarGerencia('PDF');

            return $respuesta;
        }
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
        $solicitud = (string) request('solicitud');
        $reporte = app(PrepararReporteGerencia::class)->recuperar(Auth::user(), $solicitud);

        return view('reportes::paginas.gerencia.exportacion', compact('reporte', 'solicitud'));
    }

    public function csvGerencia()
    {
        abort_unless($this->esGerencia(), 403);
        $reporte = app(PrepararReporteGerencia::class)->recuperar(Auth::user(), (string) request('solicitud'));
        $this->auditarGerencia('CSV');

        return response()->streamDownload(function () use ($reporte): void {
            $salida = fopen('php://output', 'wb');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, ['Indicador', 'Valor', 'Unidad', 'Referencia', 'Datos consultados', 'Alcance', 'Calidad']);
            foreach ($reporte['filas'] as $fila) {
                $valores = [$fila['indicador'], $fila['valor'], $fila['unidad'], $fila['referencia'],
                    $reporte['consultado_en'], $reporte['ambito'], $fila['calidad']];
                fputcsv($salida, array_map(static fn ($valor) => is_string($valor) && preg_match('/^[=+@\-\t\r]/', $valor) ? "'".$valor : $valor, $valores));
            }
            fclose($salida);
        }, 'reporte-gerencial-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'private, no-store']);
    }

    private function esGerencia(): bool
    {
        $usuario = Auth::user();
        $preview = app(RolePreviewService::class)->activeRole($usuario);

        return $preview === 'GERENTE' || (! $preview && $usuario->hasRole('GERENTE') && ! $usuario->hasRole('SUPERADMINISTRADOR'));
    }

    private function auditarGerencia(string $formato): void
    {
        activity('Reportes')->causedBy(Auth::user())->event('reporte_gerencial_respuesta_preparada')
            ->withProperties(['formato' => $formato, 'reporte' => 'Resumen gerencial', 'alcance' => 'Agregado institucional'])
            ->log('Preparó respuesta de descarga gerencial; no acredita guardado en el dispositivo.');
    }
}
