<?php

namespace App\Modulos\Reportes\Aplicacion\Acciones;

use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use App\Modulos\Reportes\Aplicacion\Consultas\ConsultarIndicadoresGerencia;
use Illuminate\Support\Str;

/** Preparación temporal de descarga; no es un cierre o histórico institucional SQL. */
final class PrepararReporteGerencia
{
    public function __construct(private readonly ConsultarIndicadoresGerencia $consulta) {}

    public function preparar(User $usuario, array $datos): string
    {
        $this->autorizar($usuario);
        $solicitud = (string) Str::uuid();
        $preparaciones = collect(session('reportes_gerencia', []))
            ->filter(fn ($item) => $item['actor'] === $usuario->getKey() && $item['vence'] >= now()->timestamp)
            ->take(-4)->all();
        $preparaciones[$solicitud] = ['actor' => $usuario->getKey(), 'vence' => now()->addMinutes(15)->timestamp, 'datos' => $datos];
        session()->put('reportes_gerencia', $preparaciones);

        return $solicitud;
    }

    public function recuperar(User $usuario, string $solicitud): array
    {
        $this->autorizar($usuario);
        abort_unless(Str::isUuid($solicitud), 404);
        $preparacion = session('reportes_gerencia.'.$solicitud);
        abort_unless($preparacion && $preparacion['actor'] === $usuario->getKey(), 404);
        abort_if($preparacion['vence'] < now()->timestamp, 409, 'La preparación caducó. Vuelve al reporte para actualizar y revisar sus datos.');

        return $preparacion['datos'];
    }

    private function autorizar(User $usuario): void
    {
        $this->consulta->autorizarReporte($usuario);
        abort_if(app(RolePreviewService::class)->isActive($usuario), 403);
    }
}
